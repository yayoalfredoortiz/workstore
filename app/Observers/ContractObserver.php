<?php

namespace App\Observers;

use App\Models\Contract;
use App\Events\NewContractEvent;
use App\Models\GoogleCalendarModule;
use App\Models\Notification;
use App\Models\User;
use App\Services\Google;

class ContractObserver
{

    public function saving(Contract $contract)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $contract->last_updated_by = user()->id;
            }

            /* Add/Update google calendar event */
            if($contract && !is_null($contract->end_date)) {
                $contract->event_id = $this->googleCalendarEvent($contract);
            }
        }
    }

    public function creating(Contract $contract)
    {
        $contract->hash = \Illuminate\Support\Str::random(32);

        if (user()) {
            $contract->added_by = user()->id;
        }
    }

    // Notify client when new contract is created
    public function created(Contract $contract)
    {
        event(new NewContractEvent($contract));
    }

    public function deleting(Contract $contract)
    {
        $notifiData = ['App\Notifications\NewContract', 'App\Notifications\ContractSigned'];

        Notification::whereIn('type', $notifiData)
            ->whereNull('read_at')
            ->where('data', 'like', '{"id":'.$contract->id.',%')
            ->delete();

        /* Start of deleting event from google calendar */
        $google = new Google();
        $googleAccount = global_setting();

        if ($googleAccount) {
            $google->connectUsing($googleAccount->token);
            try {
                if ($contract->event_id) {
                    $google->service('Calendar')->events->delete('primary', $contract->event_id);
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

        if (global_setting()->google_calendar_status == 'active' && global_setting()->google_calendar_verification_status == 'verified' && $module->contract_status == 1) {

            $google = new Google();
            $attendiesData = [];

            $attendees = User::where('id', $event->client_id)->first();

            if (!is_null($event->due_date) && !is_null($attendees)) {
                $attendiesData[] = ['email' => $attendees->email];
            }

            $googleAccount = global_setting();

            if ($googleAccount) {

                // Create event
                $google = $google->connectUsing($googleAccount->token);

                $eventData = new \Google_Service_Calendar_Event(array(
                    'summary' => $event->subject,
                    'location' => '',
                    'description' => '',
                    'colorId' => 2,
                    'start' => array(
                        'dateTime' => $event->start_date,
                        'timeZone' => global_setting()->timezone,
                    ),
                    'end' => array(
                        'dateTime' => $event->end_date,
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
