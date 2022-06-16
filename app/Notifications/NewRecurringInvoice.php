<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\RecurringInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewRecurringInvoice extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $invoice;
    private $emailSetting;

    public function __construct(RecurringInvoice $invoice)
    {
        $this->invoice = $invoice;
        $this->emailSetting = EmailNotificationSetting::where('slug', 'invoice-createupdate-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ($notifiable->email_notifications && $notifiable->email != '') ? ['mail', 'database'] : ['database'];
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
            ->subject(__('email.newInvoiceRecurring.subject'))
            ->greeting(__('email.hello') . ' ' . ucwords($notifiable->name) . ',')
            ->line(__('email.newInvoiceRecurring.subject') . '.')
            ->line(__('email.newInvoiceRecurring.text'))
            ->line(__('app.total') . ': ' . $this->invoice->currency->currency_symbol . $this->invoice->total)
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
        return [
            'id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number
        ];
    }

}
