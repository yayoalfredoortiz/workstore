@php
$notificationUser = \App\Models\Invoice::findOrFail($notification->data['id']);
@endphp

<x-cards.notification :notification="$notification"  :link="route('invoices.show', $notification->data['id'])"
    :image="$notificationUser->client->image_url" :title="__('email.invoices.paymentReceivedForInvoice')"
    :text="$notification->data['invoice_number']" :time="$notification->created_at" />
