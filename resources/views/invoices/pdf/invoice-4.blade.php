<!DOCTYPE html>
<!--
  Invoice template by invoicebus.com
  To customize this template consider following this guide https://invoicebus.com/how-to-create-invoice-template/
  This template is under Invoicebus Template License, see https://invoicebus.com/templates/license/
-->
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@lang('app.invoice')</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="Invoice">

    <style>
        /*! Invoice Templates @author: Invoicebus @email: info@invoicebus.com @web: https://invoicebus.com @version: 1.0.0 @updated: 2015-03-27 14:03:24 @license: Invoicebus */
        /* Reset styles */
        /*@import url("https://fonts.googleapis.com/css?family=Open+Sans:400,400italic,700&subset=cyrillic,cyrillic-ext,latin,greek-ext,greek,latin-ext,vietnamese");*/
        /*@import url("https://fonts.googleapis.com/css?family=Sanchez&subset=latin,latin-ext");*/
        html, body, div, span, applet, object, iframe,
        h1, h2, h3, h4, h5, h6, p, blockquote, pre,
        a, abbr, acronym, address, big, cite, code,
        del, dfn, em, img, ins, kbd, q, s, samp,
        small, strike, strong, sub, sup, tt, var,
        b, u, i, center,
        dl, dt, dd, ol, ul, li,
        fieldset, form, label, legend,
        table, caption, tbody, tfoot, thead, tr, th, td,
        article, aside, canvas, details, embed,
        figure, figcaption, footer, header, hgroup,
        menu, nav, output, ruby, section, summary,
        time, mark, audio, video {
            margin: 0;
            padding: 0;
            border: 0;
            font: inherit;
            font-size: 12px;
            vertical-align: baseline;
            font-family: Verdana, Arial, Helvetica, sans-serif;
        }

        html {
            line-height: 1;
        }

        ol, ul {
            list-style: none;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        caption, th, td {
            text-align: left;
            font-weight: normal;
            vertical-align: middle;
        }

        q, blockquote {
            quotes: none;
        }
        q:before, q:after, blockquote:before, blockquote:after {
            content: "";
            content: none;
        }

        a img {
            border: none;
        }

        article, aside, details, figcaption, figure, footer, header, hgroup, main, menu, nav, section, summary {
            display: block;
        }

        /* Invoice styles */
        /**
         * DON'T override any styles for the <html> and <body> tags, as this may break the layout.
         * Instead wrap everything in one main <div id="container"> element where you may change
         * something like the font or the background of the invoice
         */
        html, body {
            /* MOVE ALONG, NOTHING TO CHANGE HERE! */
        }

        /**
         * IMPORTANT NOTICE: DON'T USE '!important' otherwise this may lead to broken print layout.
         * Some browsers may require '!important' in oder to work properly but be careful with it.
         */
        .clearfix {
            display: block;
            clear: both;
        }

        .x-hidden {
            display: none !important;
        }

        .hidden {
            display: none;
        }

        b, strong, .bold {
            font-weight: bold;
        }

        #container {
            margin: 0 auto;
            min-height: 1158px;
            position: relative;
        }

        .right-invoice {
            padding: 40px 30px;
        }

        #memo .company-info {
            float: left;
        }
        #memo .company-info div {
            font-size: 12px;
            text-transform: uppercase;
            min-width: 20px;
            line-height: 1em;
        }
        #memo .company-info span {
            font-size: 12px;
            display: inline-block;
            min-width: 20px;
        }
        #memo .logo {
            float: right;
            margin-left: 15px;
        }
        #memo .logo img {
            height: 33px;
        }
        #memo:after {
            content: '';
            display: block;
            clear: both;
        }

        #invoice-title-number {
            margin: 50px 0 20px 0;
            display: inline-block;
            float: left;
        }
        #invoice-title-number .title-top {
            font-size: 15px;
            margin-bottom: 5px;
        }
        #invoice-title-number .title-top span {
            display: inline-block;
            min-width: 20px;
        }
        #invoice-title-number .title-top #number {
            text-align: right;
            float: right;
            color: #858585;
        }
        #invoice-title-number .title-top:after {
            content: '';
            display: block;
            clear: both;
        }
        #invoice-title-number #title {
            display: inline-block;
            background: #415472;
            color: white;
            font-size: 25px !important;
            padding: 8px 13px;
        }

        #client-info {
            text-align: right;
            min-width: 220px;
            line-height: 21px;
            font-size: 12px;
        }
        .client-name {
            font-weight: bold !important;
            font-size: 15px !important;
            text-transform: uppercase;
            margin: 7px 0;
        }
        #client-info > div {
            margin-bottom: 3px;
            min-width: 20px;
        }
        #client-info span {
            display: block;
            min-width: 20px;
        }
        #client-info > span {
            text-transform: uppercase;
            color: #858585;
            font-size: 15px;
        }

        table {
            table-layout: fixed;
        }
        table th, table td {
            vertical-align: top;
            word-break: keep-all;
            word-wrap: break-word;
        }

        #invoice-info {
            float: left;
            margin-top: 10px;
            line-height: 18px;
        }
        #invoice-info div {
            margin-bottom: 3px;
        }
        #invoice-info div span {
            display: inline-block;
            min-width: 20px;
            min-height: 18px;
        }
        #invoice-info div span:first-child {
            font-weight: bold;
            margin-right: 10px;
        }

        .currency {
            margin-top: 20px;
            text-align: right;
            color: #858585;
            font-style: italic;
            font-size: 12px;
        }
        .currency span {
            display: inline-block;
            min-width: 20px;
        }

        #items {
            margin-top: 10px;
        }
        #items .first-cell, #items table th:first-child, #items table td:first-child {
            width: 18px;
            text-align: right;
        }
        #items table {
            border-collapse: separate;
            width: 100%;
        }
        #items table th {
            font-size: 12px;
            padding: 5px 3px;
            text-align: center;
            background: #b0b4b3;
            color: white;
        }
        #items table th:nth-child(2) {
            width: 30%;
            text-align: left;
        }
        #items table th:last-child {
            /*text-align: right;*/
        }
        #items table td {
            padding: 10px 3px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        #items table td:first-child {
            text-align: left;
        }
        #items table td:nth-child(2) {
            text-align: left;
        }

        #sums {
            margin: 25px 30px 0 0;
            width: 100%;
        }
        #sums table {
            width: 70%;
            float: right;
        }
        #sums table tr th, #sums table tr td {
            min-width: 100px;
            padding: 9px 8px;
            text-align: right;
        }
        #sums table tr th {
            width: 70%;
            font-weight: bold;
        }
        #sums table tr td.last {
            min-width: 0 !important;
            max-width: 0 !important;
            width: 0 !important;
            padding: 0 !important;
            border: none !important;
        }

        #sums table tr.amount-total td, #sums table tr.amount-total th {
            font-size: 20px !important;
        }
        #sums table tr.due-amount th, #sums table tr.due-amount td {
            font-weight: bold;
        }

        #sums:after {
            content: '';
            display: block;
            clear: both;
        }

        #terms {
            margin-top: 20px !important;
            font-size: 12px;
        }
        #terms > span {
            font-weight: bold;
            display: inline-block;
            min-width: 20px;
        }
        #terms > div {
            min-height: 50px;
            min-width: 50px;
        }

        #terms .notes {
            min-height: 30px;
            min-width: 50px;
        }

        .item-summary{
            font-size: 12px
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        /**
         * If the printed invoice is not looking as expected you may tune up
         * the print styles (you can use !important to override styles)
         */
        @media print {
            /* Here goes your print styles */
        }
        .page_break { page-break-before: always; }
        .h3-border {
            border-bottom: 1px solid #AAAAAA;
        }
        table td.text-center
        {
            text-align: center;
        }

        #itemsPayment {
            margin-top: 10px;
        }
        #itemsPayment .first-cell, #itemsPayment table th:first-child, #itemsPayment table td:first-child {
            width: 18px;
            text-align: right;
        }
        #itemsPayment table {
            border-collapse: separate;
            width: 100%;
        }
        #itemsPayment table th {
            font-size: 12px;
            padding: 5px 3px;
            text-align: center;
            background: #b0b4b3;
            color: white;
        }
        #itemsPayment table th:nth-child(2) {
            width: 30%;
            text-align: left;
        }
        #itemsPayment table th:last-child {
            /*text-align: right;*/
        }
        #itemsPayment table td {
            padding: 10px 3px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        #itemsPayment table td:first-child {
            text-align: left;
        }
        #itemsPayment table td:nth-child(2) {
            text-align: left;
        }
        #itemsPayment, .box-title  {
            margin: 25px 30px 0 30px;
        }

        .word-break {
            word-wrap:break-word;
        }
    </style>

</head>
<body>
<div id="container">

    <div class="right-invoice">
        <section id="memo">
            <div class="company-info">
                <div>
                    {{ ucwords($global->company_name) }}
                </div>
                <br>
                <span>{!! nl2br($invoice->address->address) !!}</span>
                <br>
                <span>{{ $global->company_phone }}</span>

            </div>

            <div class="logo">
                <img src="{{ invoice_setting()->logo_url }}" />
            </div>
        </section>

        <section id="invoice-title-number">

            <div class="title-top">
                <span>@lang('modules.invoices.invoiceDate'):</span>
                <span>{{ $invoice->issue_date->format($global->date_format) }}</span>
            </div>

            <div id="title">{{ $invoice->invoice_number }}</div>

        </section>
        @if(!is_null($invoice->project) && !is_null($invoice->project->client))
            <section id="client-info">
                <span>@lang('modules.invoices.billedTo'):</span>
                <div class="client-name">
                    <strong>{{ ucwords($invoice->project->client->name) }}</strong>
                </div>

                @if($invoice->project->client->clientDetails)
                <div>
                    <span>{{ ucwords($invoice->project->client->clientDetails->company_name) }}</span>
                </div>

                <div class="mb-3">
                    <span>
                        {!! nl2br($invoice->project->client->clientDetails->address) !!}
                    </span>
                </div>
                @if ($invoice->show_shipping_address === 'yes')
                    <div>
                        <span>
                            <b>@lang('app.shippingAddress') :</b><br>
                            {!! nl2br($invoice->project->client->clientDetails->shipping_address) !!}
                        </span>
                    </div>
                @endif
                @endif

                <div>
                    <span>{{ $invoice->project->client->email }}</span>
                </div>
                @if($invoiceSetting->show_gst == 'yes' && !is_null($invoice->project->client->clientDetails) && !is_null($invoice->project->client->clientDetails->gst_number))
                    <div>
                        <span> @lang('app.gstIn'): {{ $invoice->project->client->clientDetails->gst_number }} </span>
                    </div>
                @endif
            </section>
        @elseif(!is_null($invoice->client_id))
            <section id="client-info">
                <span>@lang('modules.invoices.billedTo'):</span>
                <div class="client-name">
                    <strong>{{ ucwords($invoice->client->name) }}</strong>
                </div>

                @if($invoice->clientdetails)
                <div>
                    <span>{{ ucwords($invoice->clientdetails->company_name) }}</span>
                </div>
                <div class="mb-3">
                    <span>
                        <b>@lang('app.address') :</b><br>
                        {!! nl2br($invoice->clientdetails->address) !!}
                    </span>
                </div>
                @if ($invoice->show_shipping_address === 'yes')
                    <div>
                        <span>
                            <b>@lang('app.shippingAddress') :</b><br>
                            {!! nl2br($invoice->clientdetails->shipping_address) !!}
                        </span>
                    </div>
                @endif
                @endif

                <div>
                    <span>{{ $invoice->client->email }}</span>
                </div>
                @if($invoiceSetting->show_gst == 'yes' && !is_null($invoice->clientdetails) && !is_null($invoice->clientdetails->gst_number))
                    <div>
                        <span> @lang('app.gstIn'): {{ $invoice->clientdetails->gst_number }} </span>
                    </div>
                @endif
            </section>
        @endif

        @if(is_null($invoice->project) && !is_null($invoice->estimate) && !is_null($invoice->estimate->client))
            <section id="client-info">
                <span>@lang('modules.invoices.billedTo'):</span>
                <div class="client-name">
                    <strong>{{ ucwords($invoice->estimate->client->name) }}</strong>
                </div>

                <div>
                    <span>{{ ucwords($invoice->estimate->client->clientDetails->company_name) }}</span>
                </div>
                <div class="mb-3">
                    <span>
                        <b>@lang('app.address') :</b><br>
                        {!! nl2br($invoice->estimate->client->clientDetails->address) !!}
                    </span>
                </div>
                @if ($invoice->show_shipping_address === 'yes')
                    <div>
                        <span>
                            <b>@lang('app.shippingAddress') :</b><br>
                            {!! nl2br($invoice->estimate->client->clientDetails->shipping_address) !!}
                        </span>
                    </div>
                @endif
                <div>
                    <span>{{ $invoice->estimate->client->email }}</span>
                </div>
                @if($invoiceSetting->show_gst == 'yes' && !is_null($invoice->estimate->client->clientDetails->gst_number))
                    <div>
                        <span> @lang('app.gstIn'): {{ $invoice->estimate->client->clientDetails->gst_number }} </span>
                    </div>
                @endif
            </section>
        @endif

        <div class="clearfix"></div>


        <section id="invoice-info">
            @if($invoice->status == 'unpaid')
                <div>
                    <span>@lang('app.dueDate'):</span> <span>{{ $invoice->due_date->format($global->date_format) }}</span>
                </div>
            @endif
            <div>
                <span>@lang('app.status'):</span> <span>{{ ucwords($invoice->status) }}</span>
            </div>
            @if($creditNote)
                <div>
                    <span>@lang('app.credit-note'):</span> <span>{{ $creditNote->cn_number }}</span>
                </div>
            @endif

        </section>

        <div class="clearfix"></div>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>

        <section id="items">

            <table cellpadding="0" cellspacing="0">

                <tr>
                    <th>#</th> <!-- Dummy cell for the row number and row commands -->
                    <th>@lang("modules.invoices.item")</th>
                    @if($invoiceSetting->hsn_sac_code_show)
                        <th>@lang("app.hsnSac")</th>
                    @endif
                    <th>@lang("modules.invoices.qty")</th>
                    <th>@lang("modules.invoices.unitPrice") ({!! htmlentities($invoice->currency->currency_code)  !!})</th>
                    <th>@lang("modules.invoices.price") ({!! htmlentities($invoice->currency->currency_code)  !!})</th>
                </tr>

                <?php $count = 0; ?>
                @foreach($invoice->items as $item)
                    @if($item->type == 'item')
                        <tr data-iterate="item">
                            <td>{{ ++$count }}</td> <!-- Don't remove this column as it's needed for the row commands -->
                            <td>
                                {{ ucfirst($item->item_name) }}
                                @if(!is_null($item->item_summary))
                                    <p class="item-summary">{!! nl2br(strip_tags($item->item_summary)) !!}</p>
                                @endif
                                @if ($item->invoiceItemImage)
                                    <p class="mt-2">
                                        <img src="{{ $item->invoiceItemImage->file_url }}" width="80" height="80" class="img-thumbnail">
                                    </p>
                                @endif
                            </td>
                            @if($invoiceSetting->hsn_sac_code_show)
                                <td>{{ $item->hsn_sac_code ? $item->hsn_sac_code : '--' }}</td>
                            @endif
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format((float)$item->unit_price, 2, '.', '') }}</td>
                            <td>{{ number_format((float)$item->amount, 2, '.', '') }}</td>
                        </tr>
                    @endif
                @endforeach

            </table>

        </section>

        <section id="sums">

            <table cellpadding="0" cellspacing="0">
                <tr>
                    <th>@lang("modules.invoices.subTotal"):</th>
                    <td>{{ number_format((float)$invoice->sub_total, 2, '.', '') }}</td>
                </tr>
                @if($discount != 0 && $discount != '')
                <tr data-iterate="tax">
                    <th>@lang("modules.invoices.discount"):</th>
                    <td>{{ number_format((float)$discount, 2, '.', '') }}</td>
                </tr>
                @endif
                @foreach($taxes as $key=>$tax)
                    <tr data-iterate="tax">
                        <th>{{ strtoupper($key) }}:</th>
                        <td>{{ number_format((float)$tax, 2, '.', '') }}</td>
                    </tr>
                @endforeach
                <tr class="amount-total">
                    <th>@lang("modules.invoices.total"):</th>
                    <td>{{ number_format((float)$invoice->total, 2, '.', '') }}</td>
                </tr>
                @if ($invoice->creditNotes()->count() > 0)
                    <tr>
                        <th>
                            @lang('modules.invoices.appliedCredits'):</th>
                        <td>
                            {{ number_format((float)$invoice->appliedCredits(), 2, '.', '') }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <th>@lang("modules.invoices.total") @lang("modules.invoices.paid"):</th>
                    <td> {{ number_format((float)$invoice->getPaidAmount(), 2, '.', '') }}</td>
                </tr>
                <tr>
                    <th>@lang("modules.invoices.total") @lang("modules.invoices.due"):</th>
                    <td> {{ number_format((float)$invoice->amountDue(), 2, '.', '') }}</td>
                </tr>
            </table>

        </section>

        <div class="clearfix"></div>

        <section id="terms">

            <div class="notes word-break">
                @if(!is_null($invoice->note))
                    <br> {!! nl2br($invoice->note) !!}
                @endif
                @if($invoice->status == 'unpaid')
                   <br><br>{!! nl2br($invoiceSetting->invoice_terms) !!}
                @endif
            </div>

        </section>

    </div>
    </div>
    @if (count($payments) > 0)
    <div class="page_break"></div>
    <div id="container">
        <div class="invoice-body right-invoice b-all m-b-20">
            <h3 class="box-title m-t-20 text-center h3-border">@lang('app.menu.payments')</h3>
            <div class="row">
                <div class="col-sm-12">
                    <div class="table-responsive m-t-40" id="itemsPayment" style="clear: both;">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">@lang("modules.payments.amount")</th>
                                <th class="text-center">@lang("modules.invoices.paymentMethod")</th>
                                <th class="text-center">@lang("modules.invoices.paidOn")</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $count = 0; ?>
                            @forelse($payments as $key => $payment)
                                <tr>
                                    <td class="text-center">{{ $key+1 }}</td>
                                    <td class="text-center">{{ number_format((float)$payment->amount, 2, '.', '') }} {!! $invoice->currency->currency_code !!} </td>
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
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if (isset($taxes) && invoice_setting()->tax_calculation_msg == 1)
    <div class="clearfix"></div>
    <section>
        <div>
            @if ($invoice->calculate_tax == 'after_discount')
                @lang('messages.calculateTaxAfterDiscount')
            @else
                @lang('messages.calculateTaxBeforeDiscount')
            @endif
        </div>
    </section>
@endif
</body>
</html>
