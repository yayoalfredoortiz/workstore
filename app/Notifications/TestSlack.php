<?php

namespace App\Notifications;

use App\Models\SlackSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TestSlack extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    // phpcs:ignore
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    // phpcs:ignore
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line(__('email.notificationIntro'))
            ->action(__('email.notificationAction'), url('/'))
            ->line(__('email.thankyouNote'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
//phpcs:ignore
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function toSlack($notifiable)
    {
        $slack = SlackSetting::setting();

        if(count($notifiable->employee) > 0 && !is_null($notifiable->employee[0]->slack_username)){
            return (new SlackMessage())
                ->from(config('app.name'))
                ->image(asset_url('slack-logo/'.$slack->slack_logo))
                ->to('@' . $notifiable->employee[0]->slack_username)
                ->content('This is a test notification.');
        }

        return (new SlackMessage())
            ->from(config('app.name'))
            ->image(asset_url('slack-logo/'.$slack->slack_logo))
            ->content('This is a redirected notification. Add slack username for *'.ucwords($notifiable->name).'*');
    }

}
