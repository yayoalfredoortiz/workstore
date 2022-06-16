<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@lang('app.invoice') - {{ $invoice->invoice_number }}</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ $global->favicon_url }}">
    <meta name="theme-color" content="#ffffff">

    <style>
        body {
            margin: 0;
            font-family: Verdana, Arial, Helvetica, sans-serif;
        }

        .bg-grey {
            background-color: #F2F4F7;
        }

        .bg-white {
            background-color: #fff;
        }

        .border-radius-25 {
            border-radius: 0.25rem;
        }

        .p-25 {
            padding: 1.25rem;
        }

        .f-13 {
            font-size: 13px;
        }

        .f-14 {
            font-size: 14px;
        }

        .f-15 {
            font-size: 15px;
        }

        .f-21 {
            font-size: 21px;
        }

        .text-black {
            color: #28313c;
        }

        .text-grey {
            color: #616e80;
        }

        .font-weight-700 {
            font-weight: 700;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .text-capitalize {
            text-transform: capitalize;
        }

        .line-height {
            line-height: 24px;
        }

        .mt-1 {
            margin-top: 1rem;
        }

        .mb-0 {
            margin-bottom: 0px;
        }

        .b-collapse {
            border-collapse: collapse;
        }

        .heading-table-left {
            padding: 6px;
            border: 1px solid #DBDBDB;
            font-weight: bold;
            background-color: #f1f1f3;
            border-right: 0;
        }

        .heading-table-right {
            padding: 6px;
            border: 1px solid #DBDBDB;
            border-left: 0;
        }

        .unpaid {
            color: #000000;
            border: 1px solid #000000;
            position: relative;
            padding: 11px 22px;
            font-size: 15px;
            border-radius: 0.25rem;
            width: 120px;
            text-align: center;
            margin-top: 50px;
        }

        .main-table-heading {
            border: 1px solid #DBDBDB;
            background-color: #f1f1f3;
            font-weight: 700;
        }

        .main-table-heading td {
            padding: 11px 10px;
            border: 1px solid #DBDBDB;
        }

        .main-table-items td {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
        }

        .total-box {
            border: 1px solid #e7e9eb;
            padding: 0px;
            border-bottom: 0px;
        }

        .subtotal {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
        }

        .subtotal-amt {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-right: 0;
        }

        .total {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            font-weight: 700;
            border-left: 0;
        }

        .total-amt {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-right: 0;
            font-weight: 700;
        }

        .balance {
            font-size: 16px;
            font-weight: bold;
            background-color: #f1f1f3;
        }

        .balance-left {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
        }

        .balance-right {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-right: 0;
        }

        .centered {
            margin: 0 auto;
        }

        .rightaligned {
            margin-right: 0;
            margin-left: auto;
        }

        .leftaligned {
            margin-left: 0;
            margin-right: auto;
        }

        .page_break {
            page-break-before: always;
        }

        #logo {
            height: 33px;
        }

        .word-break {
            max-width:175px;
            word-wrap:break-word;
        }

        .summary {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
        }


    </style>
</head>

<body class="content-wrapper">
    <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
        <tbody>
            <!-- Table Row Start -->
            <tr>
                <td><img src="{{ invoice_setting()->logo_url }}" alt="{{ ucwords($global->company_name) }}"
                        id="logo" /></td>
                <td align="right" class="f-21 text-black font-weight-700 text-uppercase">@lang('app.invoice')</td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td>
                    <p class="line-height mt-1 mb-0 f-14 text-black">
                        {{ ucwords($global->company_name) }}<br>
                        @if (!is_null($settings) && $invoice->address)
                            {!! nl2br($invoice->address->address) !!}<br>
                        @endif
                        @if ($invoiceSetting->show_gst == 'yes' && $invoice->address)
                            <br>{{ $invoice->address->tax_name }}: {{ $invoice->address->tax_number }}
                        @endif
                    </p>
                </td>
                <td>
                    <table class="text-black mt-1 f-13 b-collapse rightaligned">
                        <tr>
                            <td class="heading-table-left">@lang('modules.invoices.invoiceNumber')</td>
                            <td class="heading-table-right">{{ $invoice->invoice_number }}</td>
                        </tr>
                        @if ($creditNote)
                            <tr>
                                <td class="heading-table-left">@lang('app.credit-note')</td>
                                <td class="heading-table-right">{{ $creditNote->cn_number }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="heading-table-left">@lang('modules.invoices.invoiceDate')</td>
                            <td class="heading-table-right">{{ $invoice->issue_date->format($global->date_format) }}
                            </td>
                        </tr>
                        @if($invoice->status == 'unpaid')
                            <tr>
                                <td class="heading-table-left">@lang('app.dueDate')</td>
                                <td class="heading-table-right">{{ $invoice->due_date->format($global->date_format) }}
                                </td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td height="10"></td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td colspan="2">
                    @if (!is_null($invoice->project) && !is_null($invoice->project->client) && !is_null($invoice->project->client->clientDetails))
                        @php
                            $client = $invoice->project->client;
                        @endphp
                    @elseif(!is_null($invoice->client_id) && !is_null($invoice->clientdetails))
                        @php
                            $client = $invoice->client;
                        @endphp
                    @endif
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td class="f-14 text-black">

                                <p class="line-height mb-0">
                                    <span
                                        class="text-grey text-capitalize">@lang("modules.invoices.billedTo")</span><br>
                                    {{ ucfirst($client->name) }}<br>
                                    {{ ucwords($client->clientDetails->company_name) }}<br>
                                    {!! nl2br($client->clientDetails->address) !!}
                                </p>

                                @if ($invoiceSetting->show_gst == 'yes' && !is_null($client->clientDetails->gst_number))
                                    <br>@lang('app.gstIn'):
                                    {{ $client->clientDetails->gst_number }}
                                @endif
                            </td>
                            <td class="f-14 text-black">
                                @if ($invoice->show_shipping_address == 'yes')
                                    <p class="line-height"><span
                                            class="text-grey text-capitalize">@lang('app.shippingAddress')</span><br>
                                        {!! nl2br($client->clientDetails->shipping_address) !!}</p>
                                @endif
                            </td>
                            <td align="right">
                                <br />
                                <div class="text-uppercase bg-white unpaid rightaligned">
                                    @if ($invoice->credit_note)
                                        @lang('app.credit-note')
                                    @else
                                        @lang('modules.invoices.'.$invoice->status)
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <table width="100%" class="f-14 b-collapse">
        <tr>
            <td height="30" colspan="2"></td>
        </tr>
        <!-- Table Row Start -->
        <tr class="main-table-heading text-grey">
            <td>@lang('app.description')</td>
            @if($invoiceSetting->hsn_sac_code_show)
                <td align="right">@lang("app.hsnSac")</td>
            @endif
            <td align="right">@lang("modules.invoices.qty")</td>
            <td align="right">@lang("modules.invoices.unitPrice")
                ({{ $invoice->currency->currency_code }})</td>
            <td align="right">@lang("modules.invoices.amount")
                ({{ $invoice->currency->currency_code }})</td>
        </tr>
        <!-- Table Row End -->
        @foreach ($invoice->items as $item)
            @if ($item->type == 'item')
                <!-- Table Row Start -->
                <tr class="main-table-items text-black">
                    <td>
                        {{ ucfirst($item->item_name) }}
                    </td>
                    @if($invoiceSetting->hsn_sac_code_show)
                        <td align="right">{{ $item->hsn_sac_code ? $item->hsn_sac_code : '--' }}</td>
                    @endif
                    <td align="right">{{ $item->quantity }}</td>
                    <td align="right">{{ number_format((float) $item->unit_price, 2, '.', '') }}</td>
                    <td align="right">{{ number_format((float) $item->amount, 2, '.', '') }}</td>
                </tr>
                <!-- Table Row End -->
                @if ($item->item_summary != '' || $item->invoiceItemImage)
                    </table>
                    <div class="f-13 summary">
                        {!! nl2br(strip_tags($item->item_summary)) !!}
                        @if ($item->invoiceItemImage)
                            <p class="mt-2">
                                <img src="{{ $item->invoiceItemImage->file_url }}" width="80" height="80"
                                    class="img-thumbnail">
                            </p>
                        @endif
                    </div>
                    <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                @endif
            @endif
        @endforeach
        <!-- Table Row Start -->
        <tr>
            <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '3' : '2' }}"></td>
            <td colspan="2" class="total-box">
                <table width="100%" class="b-collapse">
                    <!-- Table Row Start -->
                    <tr align="right" class="text-grey">
                        <td width="50%" class="subtotal">@lang("modules.invoices.subTotal")</td>
                        <td class="subtotal-amt">
                            {{ number_format((float) $invoice->sub_total, 2, '.', '') }}</td>
                    </tr>
                    <!-- Table Row End -->
                    @if ($discount != 0 && $discount != '')
                        <!-- Table Row Start -->
                        <tr align="right" class="text-grey">
                            <td width="50%" class="subtotal">@lang("modules.invoices.discount")
                            </td>
                            <td class="subtotal-amt">
                                {{ number_format((float) $discount, 2, '.', '') }}</td>
                        </tr>
                        <!-- Table Row End -->
                    @endif
                    @foreach ($taxes as $key => $tax)
                        <!-- Table Row Start -->
                        <tr align="right" class="text-grey">
                            <td width="50%" class="subtotal">{{ strtoupper($key) }}</td>
                            <td class="subtotal-amt">{{ number_format((float) $tax, 2, '.', '') }}
                            </td>
                        </tr>
                        <!-- Table Row End -->
                    @endforeach
                    <!-- Table Row Start -->
                    <tr align="right" class="text-grey">
                        <td width="50%" class="total">@lang("modules.invoices.total")</td>
                        <td class="total-amt f-15">
                            {{ number_format((float) $invoice->total, 2, '.', '') }}</td>
                    </tr>
                    <!-- Table Row End -->
                    <!-- Table Row Start -->
                    <tr align="right" class="balance text-black">
                        <td width="50%" class="balance-left">@lang("modules.invoices.total")
                            @lang("modules.invoices.due")</td>
                        <td class="balance-right">
                            {{ number_format((float) $invoice->amountDue(), 2, '.', '') }}
                            {{ $invoice->currency->currency_code }}</td>
                    </tr>
                    <!-- Table Row End -->

                </table>
            </td>
        </tr>
    </table>

    <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
        <tbody>
            <!-- Table Row Start -->
            <tr>
                <td height="10"></td>
            </tr>
            <tr>
                <td width="50%" class="f-14">@lang('app.note')</td>
                <td width="50%" style="text-align: right" class="f-14">
                    @lang('modules.invoiceSettings.invoiceTerms')</td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr class="text-grey">
                <td width="50%" class="f-14 line-height word-break">{!! $invoice->note ? nl2br($invoice->note) : '--' !!}</td>
                <td width="50%" style="text-align: right" class="f-14 line-height">{!! nl2br($invoiceSetting->invoice_terms) !!}</td>
            </tr>
            <!-- Table Row End -->
            @if (isset($taxes) && invoice_setting()->tax_calculation_msg == 1)
                <!-- Table Row Start -->
                <tr class="text-grey">
                    <td width="100%" class="f-14 line-height">
                        <p class="text-dark-grey">
                            @if ($invoice->calculate_tax == 'after_discount')
                                @lang('messages.calculateTaxAfterDiscount')
                            @else
                                @lang('messages.calculateTaxBeforeDiscount')
                            @endif
                        </p>
                    </td>
                </tr>
                <!-- Table Row End -->
            @endif
            <!-- Table Row End -->
        </tbody>
    </table>


    @if (count($payments) > 0)
        <div class="page_break"></div>
        <h3>@lang('app.menu.payments') ({{ $invoice->invoice_number }})</h3>
        <table class="f-14 b-collapse" width="100%">
            <tr class="main-table-heading text-grey">
                <td class="text-center">#</td>
                <td class="text-center">@lang("modules.invoices.price")</td>
                <td class="text-center">@lang("modules.invoices.paymentMethod")</td>
                <td class="text-center">@lang("modules.invoices.paidOn")</td>
            </tr>

            @forelse($payments as $key => $payment)
                <tr class="main-table-items">
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td class="text-center">{{ number_format((float) $payment->amount, 2, '.', '') }}
                        {{ $invoice->currency->currency_code }}</td>
                    <td class="text-center">
                        @php
                            $method = '--';

                            if(!is_null($payment->offline_method_id)) {
                                $method = $payment->offlineMethod->name;
                            }
                            elseif(isset($payment->gateway)){
                                $method = $payment->gateway;
                            }
                        @endphp

                        {{ $method }}
                    </td>
                    <td class="text-center"> {{ $payment->paid_on->format($global->date_format) }} </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">@lang('messages.noRecordFound') </td>
                </tr>
            @endforelse
        </table>
    @endif

</body>

</html>
