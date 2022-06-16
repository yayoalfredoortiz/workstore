<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Leave;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveStatusApprove extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $leave;
    private $emailSetting;

    public function __construct(Leave $leave)
    {
        $this->leave = $leave;
        $this->emailSetting = EmailNotificationSetting::where('slug', 'new-leave-application')->first();

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $user = $notifiable;
        $url = url('/');

        return (new MailMessage)
            ->subject(__('email.leaves.statusSubject') . ' - ' . config('app.name'))
            ->greeting(__('email.hello') . ' ' . ucwords($user->name) . '!')
            ->line(__('email.leave.approve') . ':- ')
            ->line(__('app.date') . ': ' . $this->leave->leave_date->format(global_setting()->date_format))
            ->line(__('app.status') . ': ' . ucwords($this->leave->status))
            ->action(__('email.loginDashboard'), $url)
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
        return $this->leave->toArray();
    }

}
