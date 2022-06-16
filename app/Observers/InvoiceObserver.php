<?php

namespace App\Observers;

use App\Models\Estimate;
use App\Events\NewInvoiceEvent;
use App\Helper\Files;
use App\Models\CompanyAddress;
use App\Models\GoogleCalendarModule;
use App\Models\Invoice;
use App\Models\InvoiceItemImage;
use App\Models\InvoiceItems;
use App\Models\Notification;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Services\Google;

class InvoiceObserver
{

    public function saving(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $invoice->last_updated_by = user()->id;

                if (request()->has('calculate_tax')) {
                    $invoice->calculate_tax = request()->calculate_tax;
                }
            }

            if (is_null($invoice->company_address_id)) {
                $defaultCompanyAddress = CompanyAddress::where('is_default', 1)->first();
                $invoice->company_address_id = $defaultCompanyAddress->id;
            }
        }
    }

    public function creating(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding()) {

            $invoice->hash = \Illuminate\Support\Str::random(32);

            if ((request()->type && request()->type == 'send') || !is_null($invoice->invoice_recurring_id)) {
                $invoice->send_status = 1;
            }
            else {
                $invoice->send_status = 0;
            }

            if (request()->type && request()->type == 'draft') {
                $invoice->status = 'draft';
            }

            if (!is_null($invoice->estimate_id)) {
                $estimate = Estimate::findOrFail($invoice->estimate_id);

                if ($estimate->status == 'accepted') {
                    $invoice->send_status = 1;
                }
            }

            /* If it is a order invoice, then send_status will be always 1 so that it could be visible to clients */
            if (isset($invoice->order_id)) {
                $invoice->send_status = 1;
            }

            $invoice->added_by = user() ? user()->id : null;
        }

    }

    public function created(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding())
        {
            if (!empty(request()->item_name) && is_array(request()->item_name)) {

                $itemsSummary = request()->item_summary;
                $cost_per_item = request()->cost_per_item;
                $hsn_sac_code = request()->hsn_sac_code;
                $quantity = request()->quantity;
                $amount = request()->amount;
                $tax = request()->taxes;
                $invoice_item_image = request()->invoice_item_image;
                $invoice_item_image_url = request()->invoice_item_image_url;

                foreach (request()->item_name as $key => $item) :
                    if (!is_null($item)) {
                        $invoiceItem = InvoiceItems::create(
                            [
                                'invoice_id' => $invoice->id,
                                'item_name' => $item,
                                'item_summary' => $itemsSummary[$key] ? $itemsSummary[$key] : '',
                                'type' => 'item',
                                'hsn_sac_code' => (isset($hsn_sac_code[$key]) && !is_null($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null,
                                'quantity' => $quantity[$key],
                                'unit_price' => round($cost_per_item[$key], 2),
                                'amount' => round($amount[$key], 2),
                                'taxes' => ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null)
                            ]
                        );

                        /* Invoice file save here */
                        if(isset($invoice_item_image[$key]) || isset($invoice_item_image_url[$key])){

                            InvoiceItemImage::create(
                                [
                                    'invoice_item_id' => $invoiceItem->id,
                                    'filename' => !isset($invoice_item_image_url[$key]) ? $invoice_item_image[$key]->getClientOriginalName() : '',
                                    'hashname' => !isset($invoice_item_image_url[$key]) ? Files::uploadLocalOrS3($invoice_item_image[$key], 'invoice-files/' . $invoiceItem->id . '/') : '',
                                    'size' => !isset($invoice_item_image_url[$key]) ? $invoice_item_image[$key]->getSize() : '',
                                    'external_link' => isset($invoice_item_image_url[$key]) ? $invoice_item_image_url[$key] : ''
                                ]
                            );
                        }

                    }

                endforeach;
            }

            if (($invoice->project && $invoice->project->client_id != null) || $invoice->client_id != null)
            {
                $clientId = ($invoice->project && $invoice->project->client_id != null) ? $invoice->project->client_id : $invoice->client_id;
                // Notify client
                $notifyUser = User::withoutGlobalScope('active')->findOrFail($clientId);

                if (request()->type && request()->type == 'send') {
                    event(new NewInvoiceEvent($invoice, $notifyUser));
                }
            }

            // Add event to google calendar
            if (request()->type && request()->type == 'send') {
                if (!is_null($invoice->due_date)) {
                    $invoice->event_id = $this->googleCalendarEvent($invoice);
                }
            }

        }
    }

    public function updating(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (request()->type && request()->type == 'send') {
                $invoice->send_status = 1;
                $invoice->status = 'unpaid';
            }

            // Update event to google calendar
            if($invoice && !is_null($invoice->due_date)){
                $invoice->event_id = $this->googleCalendarEvent($invoice);
            }

        }
    }

    public function updated(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            /*
                Step1 - Delete all invoice items which are not avaialable
                Step2 - Find old invoices items, update it and check if images are newer or older
                Step3 - Insert new invoices items with images
            */

            $request = request();

            $items = $request->item_name;
            $itemsSummary = $request->item_summary;
            $hsn_sac_code = $request->hsn_sac_code;
            $tax = $request->taxes;
            $quantity = $request->quantity;
            $cost_per_item = $request->cost_per_item;
            $amount = $request->amount;
            $invoice_item_image = $request->invoice_item_image;
            $invoice_item_image_url = $request->invoice_item_image_url;
            $item_ids = $request->item_ids;

            if (!empty($request->item_name) && is_array($request->item_name))
            {
                // Step1 - Delete all invoice items which are not avaialable
                if(!empty($item_ids)) {
                    InvoiceItems::whereNotIn('id', $item_ids)->where('invoice_id', $invoice->id)->delete();
                }

                // Step2&3 - Find old invoices items, update it and check if images are newer or older
                foreach ($items as $key => $item)
                {
                    $invoice_item_id = isset($item_ids[$key]) ? $item_ids[$key] : 0;

                    $invoiceItem = InvoiceItems::find($invoice_item_id);

                    if($invoiceItem === null) {
                        $invoiceItem = new InvoiceItems();
                    }

                    $invoiceItem->invoice_id = $invoice->id;
                    $invoiceItem->item_name = $item;
                    $invoiceItem->item_summary = $itemsSummary[$key];
                    $invoiceItem->type = 'item';
                    $invoiceItem->hsn_sac_code = (isset($hsn_sac_code[$key]) && !is_null($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null;
                    $invoiceItem->quantity = $quantity[$key];
                    $invoiceItem->unit_price = round($cost_per_item[$key], 2);
                    $invoiceItem->amount = round($amount[$key], 2);
                    $invoiceItem->taxes = ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null);
                    $invoiceItem->save();

                    /* Invoice file save here */
                    if((isset($invoice_item_image[$key]) && $request->hasFile('invoice_item_image.'.$key)) || isset($invoice_item_image_url[$key]))
                    {

                        /* Delete previous uploaded file if it not a product (because product images cannot be deleted) */
                        if(!isset($invoice_item_image_url[$key]) && $invoiceItem && $invoiceItem->invoiceItemImage){
                            Files::deleteFile($invoiceItem->invoiceItemImage->hashname, 'invoice-files/' . $invoiceItem->id . '/');
                        }

                        InvoiceItemImage::updateOrCreate(
                            [
                                'invoice_item_id' => $invoiceItem->id,
                            ],
                            [
                                'filename' => !isset($invoice_item_image_url[$key]) ? $invoice_item_image[$key]->getClientOriginalName() : '',
                                'hashname' => !isset($invoice_item_image_url[$key]) ? Files::uploadLocalOrS3($invoice_item_image[$key], 'invoice-files/' . $invoiceItem->id . '/') : '',
                                'size' => !isset($invoice_item_image_url[$key]) ? $invoice_item_image[$key]->getSize() : '',
                                'external_link' => isset($invoice_item_image_url[$key]) ? $invoice_item_image_url[$key] : ''
                            ]
                        );
                    }
                }
            }
        }
    }

    public function deleting(Invoice $invoice)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $invoice->id)->where('module_type', 'invoice')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifiData = ['App\Notifications\InvoicePaymentReceived', 'App\Notifications\InvoiceReminder','App\Notifications\NewInvoice','App\Notifications\NewPayment'];

        Notification::whereIn('type', $notifiData)
            ->whereNull('read_at')
            ->where('data', 'like', '{"id":'.$invoice->id.',%')
            ->delete();

        /* Delete invoice item files */
        $invoiceItems = InvoiceItems::where('invoice_id', $invoice->id)->get();

        if($invoiceItems){
            foreach ($invoiceItems as $invoiceItem) {
                Files::deleteDirectory('invoice-files/' . $invoiceItem->id);
            }
        }

        /* Start of deleting event from google calendar */
        $google = new Google();
        $googleAccount = global_setting();

        if ($googleAccount) {
            $google->connectUsing($googleAccount->token);
            try {
                if ($invoice->event_id) {
                    $google->service('Calendar')->events->delete('primary', $invoice->event_id);
                }
            } catch (\Google\Service\Exception $error) {
                if(is_null($error->getErrors())) {
                    // Delete google calendar connection data i.e. token, name, google_id
                    $googleAccount->name = '';
                    $googleAccount->token = '';
                    $googleAccount->google_id = '';
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }
        }

        /* End of deleting event from google calendar */
    }

    protected function googleCalendarEvent($event)
    {
        $module = GoogleCalendarModule::first();

        if (global_setting()->google_calendar_status == 'active' && global_setting()->google_calendar_verification_status == 'verified' && $module->invoice_status == 1) {

            $google = new Google();
            $attendiesData = [];
            $googleAccount = global_setting();

            $attendees = User::where('id', $event->client_id)->first();

            if (!is_null($event->due_date) && !is_null($attendees)) {
                $attendiesData[] = ['email' => $attendees->email];
            }

            if ($googleAccount) {

                $description = __('messages.invoiceDueOn');

                // Create event
                $google = $google->connectUsing($googleAccount->token);

                $eventData = new \Google_Service_Calendar_Event(array(
                    'summary' => $event->invoice_number.' '.$description,
                    'location' => global_setting()->address,
                    'description' => $description,
                    'colorId' => 4,
                    'start' => array(
                        'dateTime' => $event->issue_date,
                        'timeZone' => global_setting()->timezone,
                    ),
                    'end' => array(
                        'dateTime' => $event->due_date,
                        'timeZone' => global_setting()->timezone,
                    ),
                    'attendees' => $attendiesData,
                    'reminders' => array(
                        'useDefault' => false,
                        'overrides' => array(
                            array('method' => 'email', 'minutes' => 24 * 60),
                            array('method' => 'popup', 'minutes' => 10),
                        ),
                    ),
                ));

                try {
                    if ($event->event_id) {
                        $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                    }
                    else {
                        $results = $google->service('Calendar')->events->insert('primary', $eventData);
                    }

                    return $results->id;
                } catch (\Google\Service\Exception $error) {
                    if(is_null($error->getErrors())) {
                        // Delete google calendar connection data i.e. token, name, google_id
                        $googleAccount->name = '';
                        $googleAccount->token = '';
                        $googleAccount->google_id = '';
                        $googleAccount->google_calendar_verification_status = 'non_verified';
                        $googleAccount->save();
                    }
                }
            }

            return $event->event_id;
        }
    }

}
