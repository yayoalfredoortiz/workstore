<?php

namespace App\Observers;

use App\Models\Estimate;
use App\Events\EstimateDeclinedEvent;
use App\Events\NewEstimateEvent;
use App\Helper\Files;
use App\Models\EstimateItem;
use App\Models\EstimateItemImage;
use App\Models\Notification;
use App\Models\UniversalSearch;

class EstimateObserver
{

    public function saving(Estimate $estimate)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if (\user()) {
                $estimate->last_updated_by = user()->id;
            }

            if (request()->has('calculate_tax')) {
                $estimate->calculate_tax = request()->calculate_tax;
            }
        }

    }

    public function creating(Estimate $estimate)
    {
        $estimate->hash = \Illuminate\Support\Str::random(32);

        if (\user()) {
            $estimate->added_by = user()->id;
        }

        if (request()->type && (request()->type == 'save' || request()->type == 'draft')) {
            $estimate->send_status = 0;
        }

        if (request()->type == 'draft') {
            $estimate->status = 'draft';
        }

        $estimate->estimate_number = Estimate::lastEstimateNumber() + 1;
    }

    public function created(Estimate $estimate)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (!empty(request()->item_name)) {

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
                        $estimateItem = EstimateItem::create(
                            [
                                'estimate_id' => $estimate->id,
                                'item_name' => $item,
                                'item_summary' => $itemsSummary[$key],
                                'type' => 'item',
                                'hsn_sac_code' => (isset($hsn_sac_code[$key]) && !is_null($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null,
                                'quantity' => $quantity[$key],
                                'unit_price' => round($cost_per_item[$key], 2),
                                'amount' => round($amount[$key], 2),
                                'taxes' => $tax ? array_key_exists($key, $tax) ? json_encode($tax[$key]) : null : null
                            ]
                        );

                        /* Invoice file save here */
                        if(isset($invoice_item_image[$key]) || isset($invoice_item_image_url[$key])){

                            EstimateItemImage::create(
                                [
                                    'estimate_item_id' => $estimateItem->id,
                                    'filename' => !isset($invoice_item_image_url[$key]) ? $invoice_item_image[$key]->getClientOriginalName() : '',
                                    'hashname' => !isset($invoice_item_image_url[$key]) ? Files::uploadLocalOrS3($invoice_item_image[$key], 'estimate-files/' . $estimateItem->id . '/') : '',
                                    'size' => !isset($invoice_item_image_url[$key]) ? $invoice_item_image[$key]->getSize() : '',
                                    'external_link' => isset($invoice_item_image_url[$key]) ? $invoice_item_image_url[$key] : ''
                                ]
                            );
                        }

                    }

                endforeach;
            }

            if (request()->type != 'save' && request()->type != 'draft') {
                event(new NewEstimateEvent($estimate));
            }
        }
    }

    public function updated(Estimate $estimate)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if($estimate->status == 'declined'){
                event(new EstimateDeclinedEvent($estimate));
            }
        }
    }

    public function deleting(Estimate $estimate)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $estimate->id)->where('module_type', 'estimate')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifiData = ['App\Notifications\NewEstimate'];

        Notification::whereIn('type', $notifiData)
            ->where('data', 'like', '{"id":'.$estimate->id.',%')
            ->whereNull('read_at')
            ->delete();
    }

}
