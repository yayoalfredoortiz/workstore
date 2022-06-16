<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>@lang('app.invoice')</title>
    <style>
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #0087C3;
            text-decoration: none;
        }

        body {
            position: relative;
            width: 100%;
            height: auto;
            margin: 0 auto;
            color: #555555;
            background: #FFFFFF;
            font-size: 14px;
            font-family: Verdana, Arial, Helvetica, sans-serif;
        }

        h2 {
            font-weight: normal;
        }

        header {
            padding: 10px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #AAAAAA;
        }

        #logo {
            float: left;
            margin-top: 11px;
        }

        #logo img {
            height: 33px;
            margin-bottom: 15px;
        }

        #details {
            margin-bottom: 50px;
        }

        #client {
            padding-left: 6px;
            float: left;
        }

        #client .to {
            color: #777777;
        }

        h2.name {
            font-size: 1.2em;
            font-weight: normal;
            margin: 0;
        }

        #invoice {}

        #invoice h1 {
            color: #0087C3;
            font-size: 1.4em;
            line-height: 1em;
            font-weight: normal;
            margin: 0 0 10px 0;
        }

        #invoice .date {
            font-size: 1.1em;
            color: #777777;
        }

        table {
            width: 100%;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        table th,
        table td {
            padding: 5px 10px 7px 10px;
            background: #EEEEEE;
            text-align: center;
            border-bottom: 1px solid #FFFFFF;
        }

        table th {
            white-space: nowrap;
            font-weight: normal;
        }

        table td {
            text-align: right;
        }

        table td.desc h3,
        table td.qty h3 {
            color: #57B223;
            font-size: 1.2em;
            font-weight: normal;
            margin: 0 0 0 0;
        }

        table .no {
            color: #FFFFFF;
            font-size: 1.6em;
            background: #57B223;
            width: 10%;
        }

        table .desc {
            text-align: left;
        }

        table .unit {
            background: #DDDDDD;
        }


        table .total {
            background: #57B223;
            color: #FFFFFF;
        }

        table td.unit,
        table td.qty,
        table td.total {
            font-size: 1.2em;
            text-align: center;
        }

        table td.unit {
            width: 35%;
        }

        table td.desc {
            width: 45%;
        }

        table td.qty {
            width: 5%;
        }

        .status {
            margin-top: 15px;
            padding: 1px 8px 5px;
            font-size: 1.3em;
            width: 80px;
            color: #fff;
            float: right;
            text-align: center;
            display: inline-block;
        }

        .status.unpaid {
            background-color: #E7505A;
        }

        .status.paid {
            background-color: #26C281;
        }

        .status.cancelled {
            background-color: #95A5A6;
        }

        .status.error {
            background-color: #F4D03F;
        }

        table tr.tax .desc {
            text-align: right;
            color: #1BA39C;
        }

        table tr.discount .desc {
            text-align: right;
            color: #E43A45;
        }

        table tr.subtotal .desc {
            text-align: right;
            color: #1d0707;
        }

        table tbody tr:last-child td {
            border: none;
        }

        table tfoot td {
            padding: 10px 10px 20px 10px;
            background: #FFFFFF;
            border-bottom: none;
            font-size: 1.2em;
            white-space: nowrap;
            border-bottom: 1px solid #AAAAAA;
        }

        table tfoot tr:first-child td {
            border-top: none;
        }

        table tfoot tr td:first-child {
            border: none;
        }

        #thanks {
            font-size: 2em;
            margin-bottom: 50px;
        }

        #notices {
            padding-left: 6px;
            border-left: 6px solid #0087C3;
        }

        #notices .notice {
            font-size: 1.2em;
        }

        footer {
            color: #777777;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #AAAAAA;
            padding: 8px 0;
            text-align: center;
        }

        table.billing td {
            background-color: #fff;
        }

        table td div#invoiced_to {
            text-align: left;
        }

        #notes {
            color: #767676;
            font-size: 11px;
        }

        .item-summary {
            font-size: 12px
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .logo {
            text-align: right;
        }

        .logo img {
            max-width: 33px !important;
        }

        .page_break {
            page-break-before: always;
        }

        .h3-border {
            border-bottom: 1px solid #AAAAAA;
        }

        table td.text-center {
            text-align: center;
        }

    </style>
</head>

<body>
    <header class="clearfix">

        <table cellpadding="0" cellspacing="0" class="billing">
            <tr>
                <td>
                    <div id="invoiced_to">
                        <small>@lang("modules.invoices.billedTo"):</small>
                        <div class="mb-3">
                            <b>{{ ucwords($proposal->lead->client_name) }}</b>
                            <div>{{ $proposal->lead->company_name }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div id="company">
                        <div class="logo">
                            <img src="{{ invoice_setting()->logo_url }}" alt="home" class="dark-logo" />
                        </div>
                        <small>@lang("modules.invoices.generatedBy"):</small>
                        <h3 class="name">{{ ucwords($global->company_name) }}</h3>
                        @if (!is_null($settings))
                            <div>{!! nl2br($global->address) !!}</div>
                            <div>{{ $global->company_phone }}</div>
                        @endif
                        @if ($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                            <div>@lang('app.gstIn'): {{ $invoiceSetting->gst_number }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </header>
    <main>
        <div id="details" class="clearfix">

            <div id="invoice">
                <h1>@lang('modules.lead.proposal')#{{ $proposal->id }}</h1>
                <div class="">@lang('app.status'): {{ ucwords($proposal->status) }}</div>
                <div class="">@lang('modules.estimates.validTill'):
                    {{ $proposal->valid_till->format($global->date_format) }}</div>
            </div>

        </div>
        @if ($proposal->description)
            <div>{!! strip_tags($proposal->description, ['p', 'b', 'strong', 'a']) !!}
            </div>
        @endif
        <table border="0" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="no">#</th>
                    <th class="desc">@lang("modules.invoices.item")</th>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <th class="qty">@lang("app.hsnSac")</th>
                    @endif
                    <th class="qty">@lang("modules.invoices.qty")</th>
                    <th class="qty">@lang("modules.invoices.unitPrice") ({!! htmlentities($proposal->currency->currency_code) !!})</th>
                    <th class="unit">@lang("modules.invoices.price") ({!! htmlentities($proposal->currency->currency_code) !!})</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 0; ?>
                @foreach ($proposal->items as $item)
                    @if ($item->type == 'item')
                        <tr style="page-break-inside: avoid;">
                            <td class="no">{{ ++$count }}</td>
                            <td class="desc">
                                <h3>{{ ucfirst($item->item_name) }}</h3>
                                @if (!is_null($item->item_summary))
                                    <p class="item-summary">{!! nl2br(strip_tags($item->item_summary)) !!}</p>
                                @endif
                                @if ($item->proposalItemImage)
                                    <p class="mt-2">
                                        <img src="{{ $item->proposalItemImage->file_url }}" width="80" height="80" class="img-thumbnail">
                                    </p>
                                @endif
                            </td>
                            @if ($invoiceSetting->hsn_sac_code_show)
                                <td class="qty">
                                    <h3>{{ $item->hsn_sac_code ? $item->hsn_sac_code : '--' }}</h3>
                                </td>
                            @endif
                            <td class="qty">
                                <h3>{{ $item->quantity }}</h3>
                            </td>
                            <td class="qty">
                                <h3>{{ number_format((float) $item->unit_price, 2, '.', '') }}</h3>
                            </td>
                            <td class="unit">{{ number_format((float) $item->amount, 2, '.', '') }}</td>
                        </tr>
                    @endif
                @endforeach
                <tr style="page-break-inside: avoid;" class="subtotal">
                    <td class="no">&nbsp;</td>
                    <td class="qty">&nbsp;</td>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <td class="qty">&nbsp;</td>
                    @endif
                    <td class="qty">&nbsp;</td>
                    <td class="desc">@lang("modules.invoices.subTotal")</td>
                    <td class="unit">{{ number_format((float) $proposal->sub_total, 2, '.', '') }}</td>
                </tr>
                @if ($discount != 0 && $discount != '')
                    <tr style="page-break-inside: avoid;" class="discount">
                        <td class="no">&nbsp;</td>
                        <td class="qty">&nbsp;</td>
                        @if ($invoiceSetting->hsn_sac_code_show)
                            <td class="qty">&nbsp;</td>
                        @endif
                        <td class="qty">&nbsp;</td>
                        <td class="desc">@lang("modules.invoices.discount")</td>
                        <td class="unit">{{ number_format((float) $discount, 2, '.', '') }}</td>
                    </tr>
                @endif
                @foreach ($taxes as $key => $tax)
                    <tr style="page-break-inside: avoid;" class="tax">
                        <td class="no">&nbsp;</td>
                        <td class="qty">&nbsp;</td>
                        @if ($invoiceSetting->hsn_sac_code_show)
                            <td class="qty">&nbsp;</td>
                        @endif
                        <td class="qty">&nbsp;</td>
                        <td class="desc">{{ strtoupper($key) }}</td>
                        <td class="unit">{{ number_format((float) $tax, 2, '.', '') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr dontbreak="true">
                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '5' : '4' }}">
                        @lang("modules.invoices.subTotal")</td>
                    <td style="text-align: center">{{ number_format((float) $proposal->sub_total, 2, '.', '') }}</td>
                </tr>
                <tr dontbreak="true">
                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '5' : '4' }}">
                        @lang("modules.invoices.discount")</td>
                    <td style="text-align: center">{{ number_format((float) $discount, 2, '.', '') }}</td>
                </tr>
                <tr dontbreak="true">
                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '5' : '4' }}">
                        @lang("modules.invoices.total")</td>
                    <td style="text-align: center">{{ number_format((float) $proposal->total, 2, '.', '') }}</td>
                </tr>
            </tfoot>
        </table>
        <p>&nbsp;</p>
        <hr>
        <p id="notes" class="d-flex justify-content-between">
        </p>

        <div width="100%">
            <p style="text-align: left; width:50%; float: left;"><br />
                @lang('app.note')<br>
                {!! nl2br($proposal->note) !!}
            </p>
            <p style="text-align: right; width: 50%; float: left">
                @lang('modules.invoiceSettings.invoiceTerms')<br>
                {!! nl2br($invoiceSetting->invoice_terms) !!}
            </p>
        </div>
        @if (isset($taxes) && invoice_setting()->tax_calculation_msg == 1)
        <div width="100%">
           <p class="text-dark-grey">
                @if ($proposal->calculate_tax == 'after_discount')
                    @lang('messages.calculateTaxAfterDiscount')
                @else
                    @lang('messages.calculateTaxBeforeDiscount')
                @endif
            </p>
        </div>
        @endif
        <p>
            @if ($proposal->signature)
                <h5 style="margin-bottom: 20px;">@lang('app.signature')</h5>
                <img src="{{ $proposal->signature->signature }}" style="width: 200px;">
                <p>({{ $proposal->signature->full_name }})</p>
            @endif
        </p>
    </main>
</body>

</html>
