<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\GoogleCalendarModule;
use App\Services\Google;

class EventObserver
{

    public function saving(Event $event)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $event->last_updated_by = user()->id;

            // Add/Update event to google calendar
            $event->event_id = $this->googleCalendarEvent($event);
        }
    }

    public function creating(Event $event)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $event->added_by = user()->id;
        }
    }

    public function deleting(Event $event)
    {
        /* Start of deleting event from google calendar */
        $google = new Google();
        $googleAccount = global_setting();

        if ($googleAccount) {
            $google->connectUsing($googleAccount->token);
            try {
                if ($event->event_id) {
                    $google->service('Calendar')->events->delete('primary', $event->event_id);
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

        if (global_setting()->google_calendar_status == 'active' && global_setting()->google_calendar_verification_status == 'verified' && $module->event_status == 1)
        {
            $google = new Google();
            $attendiesData = [];
            $googleAccount = global_setting();

            $attendees = EventAttendee::with(['user'])->where('event_id', $event->id)->get();

            foreach($attendees as $attend) {
                if(!is_null($attend->user) && !is_null($attend->user->email)) {
                    $attendiesData[] = ['email' => $attend->user->email];
                }
            }

            if ($googleAccount)
            {
                $description = $event->event_name . ' :- ' . __('email.newEvent.subject');
                $description = $event->event_name . ' :- ' . $description . ' ' . $event->description;

                // Create event
                $google = $google->connectUsing($googleAccount->token);

                $eventData = new \Google_Service_Calendar_Event(array(
                    'summary' => $event->invoice_number.' '.$description,
                    'location' => global_setting()->address,
                    'description' => $description,
                    'colorId' => 3,
                    'start' => array(
                        'dateTime' => $event->start_date_time,
                        'timeZone' => global_setting()->timezone,
                    ),
                    'end' => array(
                        'dateTime' => $event->end_date_time,
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

                try
                {
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
