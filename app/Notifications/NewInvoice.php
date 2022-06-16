<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Http\Controllers\InvoiceController;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;

class NewInvoice extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $invoice;
    private $emailSetting;

    public function __construct(Invoice $invoice)
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

        if ($this->emailSetting->send_push == 'yes') {
            array_push($via, OneSignalChannel::class);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage|void
     */
    public function toMail($notifiable)
    {
        $url = route('front.invoice', $this->invoice->hash);

        if (($this->invoice->project && !is_null($this->invoice->project->client)) || !is_null($this->invoice->client_id)) {
            // For Sending pdf to email
            $invoiceController = new InvoiceController();

            if ($pdfOption = $invoiceController->domPdfObjectForDownload($this->invoice->id)) {
                $pdf = $pdfOption['pdf'];
                $filename = $pdfOption['fileName'];

                return (new MailMessage)
                    ->subject(__('email.invoice.subject') . ' - ' . config('app.name') . '.')
                    ->greeting(__('email.hello') . ' ' . $notifiable->name . '!')
                    ->line(__('email.invoice.text'))
                    ->action(__('email.viewInvoice'), $url)
                    ->line(__('email.thankyouNote'))
                    ->attachData($pdf->output(), $filename . '.pdf');
            }
        }
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
