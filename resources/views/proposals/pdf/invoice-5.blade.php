<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Template CSS -->
    <title>@lang('modules.lead.proposal') - {{ $proposal->id }}</title>
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

        .f-12 {
            font-size: 12px;
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
            width: 100px;
            text-align: center;
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
            max-width: 175px;
            word-wrap: break-word;
            word-break: break-all;
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
                <td align="right" class="f-21 text-black font-weight-700 text-uppercase">@lang('modules.lead.proposal')
                </td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td>
                    <p class="line-height mt-1 mb-0 f-14 text-black">
                        {{ ucwords($global->company_name) }}<br>
                        @if (!is_null($settings))
                            {!! nl2br($global->address) !!}<br>
                        @endif
                        @if ($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                            <br>@lang('app.gstIn'): {{ $invoiceSetting->gst_number }}
                        @endif
                    </p>
                </td>
                <td>
                    <table class="text-black mt-1 f-13 b-collapse rightaligned">
                        <tr>
                            <td class="heading-table-left">@lang('modules.lead.proposal')</td>
                            <td class="heading-table-right">#{{ $proposal->id }}</td>
                        </tr>
                        <tr>
                            <td class="heading-table-left">@lang('modules.estimates.validTill')</td>
                            <td class="heading-table-right">{{ $proposal->valid_till->format($global->date_format) }}
                            </td>
                        </tr>
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
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td class="f-14 text-black">

                                <p class="line-height mb-0">
                                    <span
                                        class="text-grey text-capitalize">@lang("modules.invoices.billedTo")</span><br>
                                    {{ ucwords($proposal->lead->client_name) }}<br>
                                    {{ $proposal->lead->company_name }}<br>
                                    {!! nl2br($proposal->lead->address) !!}
                                </p>
                            </td>

                            <td align="right">
                                <div class="text-uppercase bg-white unpaid rightaligned">
                                    @lang('modules.proposal.'. $proposal->status)</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="20"></td>
            </tr>
        </tbody>
    </table>

    @if ($proposal->description)
        <div class="f-13">{!! strip_tags($proposal->description, ['p', 'b', 'strong', 'a', 'ul', 'li', 'ol', 'i', 'u', 'em', 'blockquote', 'img']) !!}</div>
    @endif

    <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
        <tr>
            <td height="20"></td>
        </tr>
        <!-- Table Row Start -->
        <tr class="main-table-heading text-grey">
            <td>@lang('app.description')</td>
            @if ($invoiceSetting->hsn_sac_code_show)
                <td align="right">@lang("app.hsnSac")</td>
            @endif
            <td align="right">@lang("modules.invoices.qty")</td>
            <td align="right">@lang("modules.invoices.unitPrice")
                ({{ $proposal->currency->currency_code }})</td>
            <td align="right">@lang("modules.invoices.amount")
                ({{ $proposal->currency->currency_code }})</td>
        </tr>
        <!-- Table Row End -->
        @foreach ($proposal->items as $item)
            @if ($item->type == 'item')
                <!-- Table Row Start -->
                <tr class="main-table-items text-black f-14">
                    <td>{{ ucfirst($item->item_name) }}</td>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <td align="right">{{ $item->hsn_sac_code ? $item->hsn_sac_code : '--' }}</td>
                    @endif
                    <td align="right">{{ $item->quantity }}</td>
                    <td align="right">{{ number_format((float) $item->unit_price, 2, '.', '') }}</td>
                    <td align="right">{{ number_format((float) $item->amount, 2, '.', '') }}</td>
                </tr>
                @if ($item->item_summary != '' || $item->proposalItemImage)
                    </table>
                    <div class="f-13 summary">
                        {!! nl2br(strip_tags($item->item_summary)) !!}
                        @if ($item->proposalItemImage)
                            <p class="mt-2">
                                <img src="{{ $item->proposalItemImage->file_url }}" width="80" height="80"
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
                            {{ number_format((float) $proposal->sub_total, 2, '.', '') }}</td>
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
                            {{ number_format((float) $proposal->total, 2, '.', '') }}</td>
                    </tr>
                    <!-- Table Row End -->

                </table>
            </td>
        </tr>
        <!-- Table Row End -->
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
                <td width="50%" class="f-14 line-height word-break">{!! $proposal->note ? nl2br($proposal->note) : '--' !!}</td>
                <td width="50%" style="text-align: right" class="f-14 line-height">{!! nl2br($invoiceSetting->invoice_terms) !!}</td>
            </tr>
            <!-- Table Row End -->
            @if (isset($taxes) && invoice_setting()->tax_calculation_msg == 1)
                <!-- Table Row Start -->
                <tr class="text-grey">
                    <td width="100%" class="f-14 line-height">
                        <p class="text-dark-grey">
                            @if ($proposal->calculate_tax == 'after_discount')
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
            <!-- Table Row Start -->
            <tr class="text-grey">
                <td colspan="2" class="f-14 line-height">
                    @if ($proposal->signature)
                        <h5 style="margin-bottom: 20px;">@lang('app.signature')</h5>
                        <img src="{{ $proposal->signature->signature }}" style="width: 200px;">
                        <p>({{ $proposal->signature->full_name }})</p>
                    @endif
                </td>
            </tr>
            <!-- Table Row End -->
        </tbody>
    </table>

</body>

</html>
