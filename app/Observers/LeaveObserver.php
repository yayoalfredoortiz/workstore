<?php

namespace App\Observers;

use App\Models\Leave;
use App\Events\LeaveEvent;
use App\Models\GoogleCalendarModule;
use App\Models\User;
use App\Services\Google;

class LeaveObserver
{

    public function saving(Leave $leave)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $leave->last_updated_by = user()->id;
        }
    }

    public function creating(Leave $leave)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $leave->added_by = user()->id;
        }
    }

    public function created(Leave $leave)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (request()->duration == 'multiple') {
                if (session()->has('leaves_duration')) {
                    event(new LeaveEvent($leave, 'created', request()->multi_date));
                }
            } else {
                event(new LeaveEvent($leave, 'created'));
            }

            /* Add google calendar event */
            if (!is_null($leave->leave_date) && !is_null($leave->user)) {
                $leave->event_id = $this->googleCalendarEvent($leave);
            }

        }
    }

    public function updated(Leave $leave)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if ($leave->isDirty('status')) {
                event(new LeaveEvent($leave, 'statusUpdated'));
            }
            else {
                event(new LeaveEvent($leave, 'updated'));
            }

            /* update google calendar event */
            if (!is_null($leave->leave_date) && !is_null($leave->user)) {
                $leave->event_id = $this->googleCalendarEvent($leave);
            }

        }
    }

    public function deleting(Leave $leave)
    {
        /* Start of deleting event from google calendar */
        $google = new Google();
        $googleAccount = global_setting();

        if ($googleAccount) {
            $google->connectUsing($googleAccount->token);
            try {
                if ($leave->event_id) {
                    $google->service('Calendar')->events->delete('primary', $leave->event_id);
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

    protected function googleCalendarEvent($leave)
    {
        $module = GoogleCalendarModule::first();

        if (global_setting()->google_calendar_status == 'active' && global_setting()->google_calendar_verification_status == 'verified' && $module->leave_status == 1) {

            $google = new Google();
            $attendiesData = [];
            $googleAccount = global_setting();

            $user = User::where('id', $leave->user_id)->first();
            $attendiesData[] = ['email' => $user->email];

            if ($googleAccount)
            {
                $description = __('email.newContract.subject');
                $description .= $user->name.' '. __('app.leave');

                // Create event
                $google->connectUsing($googleAccount->token);

                $eventData = new \Google_Service_Calendar_Event(array(
                    'summary' => $user->name,
                    'location' => ' ',
                    'description' => $description,
                    'colorId' => 6,
                    'start' => array(
                        'dateTime' => $leave->leave_date,
                        'timeZone' => global_setting()->timezone,
                    ),
                    'end' => array(
                        'dateTime' => $leave->leave_date,
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
                    if ($leave->event_id) {
                        $results = $google->service('Calendar')->events->patch('primary', $leave->event_id, $eventData);
                    }
                    else {
                        $results = $google->service('Calendar')->events->insert('primary', $eventData);
                    }

                    return $results->id;
                }
                catch (\Google\Service\Exception $error) {
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

            return $leave->event_id;
        }
    }

}
