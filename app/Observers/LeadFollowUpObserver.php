<?php

namespace App\Observers;

use App\Models\LeadAgent;
use App\Models\LeadFollowUp;
use App\Services\Google;

class LeadFollowUpObserver
{

    public function saving(LeadFollowUp $leadFollowUp)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $leadFollowUp->last_updated_by = user()->id;
        }
    }

    public function creating(LeadFollowUp $leadFollowUp)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $leadFollowUp->added_by = user()->id;
        }
    }

    public function created(LeadFollowUp $leadFollowUp)
    {
        if (!isRunningInConsoleOrSeeding()) {

            /* Add google calendar event */
            if (!is_null($leadFollowUp->next_follow_up_date)) {
                $leadFollowUp->event_id = $this->googleCalendarEvent($leadFollowUp);
            }
        }
    }

    public function updating(LeadFollowUp $leadFollowUp)
    {
        if (!isRunningInConsoleOrSeeding()) {

            /* Update google calendar event */
            if (!is_null($leadFollowUp->next_follow_up_date)) {
                $leadFollowUp->event_id = $this->googleCalendarEvent($leadFollowUp);
            }
        }
    }

    public function deleting(LeadFollowUp $leadFollowUp)
    {
        /* Start of deleting event from google calendar */
        $google = new Google();
        $googleAccount = global_setting();

        if ($googleAccount) {
            $google->connectUsing($googleAccount->token);
            try {
                if ($leadFollowUp->event_id) {
                    $google->service('Calendar')->events->delete('primary', $leadFollowUp->event_id);
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
        if (global_setting()->google_calendar_status == 'active' && global_setting()->google_calendar_verification_status == 'verified') {

            $google = new Google();
            $attendiesData = [];
            $googleAccount = global_setting();

            $attendees = LeadAgent::with(['user', 'lead'])->where('user_id', $event->lead->id)->get();

            foreach($attendees as $attend){
                if(!is_null($attend->user) && !is_null($attend->user->email)) {
                    $attendiesData[] = ['email' => $attend->user->email];
                }
            }

            if ($googleAccount) {
                // Create event
                $google = $google->connectUsing($googleAccount->token);

                $eventData = new \Google_Service_Calendar_Event(array(
                    'summary' => $event->remark,
                    'location' => '',
                    'description' => '',
                    'colorId' => 5,
                    'start' => array(
                        'dateTime' => $event->next_follow_up_date,
                        'timeZone' => global_setting()->timezone,
                    ),
                    'end' => array(
                        'dateTime' => $event->next_follow_up_date,
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
