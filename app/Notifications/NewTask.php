<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\SlackSetting;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class NewTask extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $task;
    private $emailSetting;

    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->emailSetting = EmailNotificationSetting::userAssignTask();
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

        if ($this->emailSetting->send_slack == 'yes' && slack_setting()->status == 'active') {
            array_push($via, 'slack');
        }

        if ($this->emailSetting->send_push == 'yes') {
            array_push($via, OneSignalChannel::class);
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
        $url = url('/');
        $dueDate = (!is_null($this->task->due_date)) ? $this->task->due_date->format(global_setting()->date_format) : null;

        $content = ucfirst($this->task->heading) . ' #' . $this->task->id . '<p>
            <b style="color: green">' . __('app.dueDate') . ': ' . $dueDate . '</b>
        </p>';

        return (new MailMessage)
            ->subject(__('email.newTask.subject') . ' #' . $this->task->id . ' - ' . config('app.name') . '.')
            ->greeting(__('email.hello') . ' ' . ucwords($notifiable->name) . ',')
            ->markdown('mail.task.created', ['url' => $url, 'content' => $content]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->task->id,
            'created_at' => $this->task->created_at->format('Y-m-d H:i:s'),
            'heading' => $this->task->heading
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $slack = SlackSetting::setting();
        $dueDate = (!is_null($this->task->due_date)) ? $this->task->due_date->format(global_setting()->date_format) : null;

        if (count($notifiable->employee) > 0 && (!is_null($notifiable->employee[0]->slack_username) && ($notifiable->employee[0]->slack_username != ''))) {
            return (new SlackMessage())
                ->from(config('app.name'))
                ->image($slack->slack_logo_url)
                ->to('@' . $notifiable->employee[0]->slack_username)
                ->content('*' . __('email.newTask.subject') . '*' . "\n" . '<'.route('tasks.show', $this->task->id).'|'.ucfirst($this->task->heading).'>' . "\n" . ' #' . $this->task->id . "\n" . __('app.dueDate') . ': ' . $dueDate . (!is_null($this->task->project) ? "\n" . __('app.project') . ' - ' . ucfirst($this->task->project->project_name) : ''));
        }

        return (new SlackMessage())
            ->from(config('app.name'))
            ->image($slack->slack_logo_url)
            ->content('This is a redirected notification. Add slack username for *' . ucwords($notifiable->name) . '*');
    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->subject(__('email.newTask.subject'))
            ->body($this->task->heading);
    }

}
