<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@lang('app.invoice') - {{ $creditNote->cn_number }}</title>
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
            height: 55px;
        }

        .word-break {
            max-width:175px;
            word-wrap:break-word;
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
                <td align="right" class="f-21 text-black font-weight-700 text-uppercase">@lang('app.credit-note')</td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td>
                    <p class="line-height mt-1 mb-0 f-14 text-black">
                        {{ ucwords($global->company_name) }}<br>
                        @if (!is_null($settings))
                            {!! nl2br($global->address) !!}<br>
                            {{ $global->company_phone }}
                        @endif
                        @if ($creditNoteSetting->show_gst == 'yes' && !is_null($creditNoteSetting->gst_number))
                            <br>@lang('app.gstIn'): {{ $creditNoteSetting->gst_number }}
                        @endif
                    </p>
                </td>
                <td>
                    <table class="text-black mt-1 f-13 b-collapse rightaligned">
                        <tr>
                            <td class="heading-table-left">@lang('app.credit-note')</td>
                            <td class="heading-table-right">{{ $creditNote->cn_number }}</td>
                        </tr>
                        <tr>
                            <td class="heading-table-left">@lang('app.credit-note') @lang('app.date')</td>
                            <td class="heading-table-right">
                                {{ $creditNote->issue_date->format($global->date_format) }}
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
                    @if (!is_null($creditNote->project_id) && !is_null($creditNote->project->clientdetails))
                        @php
                            $client = $creditNote->project->client;
                        @endphp
                    @elseif(!is_null($creditNote->client_id))
                        @php
                            $client = $creditNote->client;
                        @endphp
                    @endif
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td class="f-14 text-black">

                                <p class="line-height mb-0">
                                    <span
                                        class="text-grey text-capitalize">@lang("modules.invoices.billedTo")</span><br>
                                    {{ ucwords($client->name) }}<br>
                                    {{ ucwords($client->clientDetails->company_name) }}<br>
                                    {!! nl2br($client->clientDetails->address) !!}
                                </p>

                                @if ($creditNoteSetting->show_gst == 'yes' && !is_null($client->clientDetails->gst_number))
                                    <br>@lang('app.gstIn'):
                                    {{ $client->clientDetails->gst_number }}
                                @endif
                            </td>
                            <td align="right">
                                <div class="text-uppercase bg-white unpaid rightaligned">
                                    @lang('modules.credit-notes.'.$creditNote->status)</div>
                            </td>

                        </tr>
                    </table>
                </td>


            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td height="30" colspan="2"></td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td colspan="2">
                    <table width="100%" class="f-14 b-collapse">
                        <!-- Table Row Start -->
                        <tr class="main-table-heading text-grey">
                            <td>@lang('app.description')</td>
                            @if($invoiceSetting->hsn_sac_code_show)
                                <td align="right">@lang("app.hsnSac")</td>
                            @endif
                            <td align="right">@lang("modules.invoices.qty")</td>
                            <td align="right">@lang("modules.invoices.unitPrice")
                                ({{ $creditNote->currency->currency_code }})</td>
                            <td align="right">@lang("modules.invoices.amount")
                                ({{ $creditNote->currency->currency_code }})</td>
                        </tr>
                        <!-- Table Row End -->
                        @foreach ($creditNote->items as $item)
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
                                <tr class="main-table-items text-black f-13">
                                    <td colspan="4 word-break">
                                        {!! nl2br(strip_tags($item->item_summary)) !!}
                                        @if ($item->creditNoteItemImage)
                                            <p class="mt-2">
                                                <img src="{{ $item->creditNoteItemImage->file_url }}" width="80" height="80" class="img-thumbnail">
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        <!-- Table Row Start -->
                        <tr>
                            <td colspan="1"></td>
                            <td colspan="4" class="total-box">
                                <table width="100%" class="b-collapse">
                                    <!-- Table Row Start -->
                                    <tr align="right" class="text-grey">
                                        <td width="50%" class="subtotal">@lang("modules.invoices.subTotal")</td>
                                        <td class="subtotal-amt">
                                            {{ number_format((float) $creditNote->sub_total, 2, '.', '') }}</td>
                                    </tr>
                                    <!-- Table Row End -->
                                    @if ($discount != 0 && $discount != '')
                                        <!-- Table Row Start -->
                                        <tr align="right" class="text-grey">
                                            <td width="50%" class="subtotal">@lang("modules.invoices.discount")</td>
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
                                            {{ number_format((float) $creditNote->total, 2, '.', '') }}</td>
                                    </tr>
                                    <!-- Table Row End -->
                                    <!-- Table Row Start -->
                                    <tr align="right" class="text-grey">
                                        <td width="50%" class="subtotal">@lang('modules.credit-notes.creditAmountUsed')</td>
                                        <td class="subtotal-amt">{{ number_format((float)$creditNote->creditAmountUsed(), 2, '.', '') }}
                                            {{ $creditNote->currency->currency_code }}
                                        </td>
                                    </tr>
                                    <!-- Table Row End -->
                                    <!-- Table Row Start -->
                                    <tr align="right" class="text-grey">
                                        <td width="50%" class="subtotal">@lang('app.adjustment') @lang('app.amount')</td>
                                        <td class="subtotal-amt">{{ number_format((float) $creditNote->adjustment_amount, 2, '.', '') }}
                                            {{ $creditNote->currency->currency_code }}
                                        </td>
                                    </tr>
                                    <!-- Table Row End -->
                                    <!-- Table Row Start -->
                                    <tr align="right" class="balance text-black">
                                        <td width="50%" class="balance-left">@lang('modules.credit-notes.creditAmountRemaining')</td>
                                        <td class="balance-right">
                                            {{ number_format((float) $creditNote->creditAmountRemaining(), 2, '.', '') }}
                                            {{ $creditNote->currency->currency_code }}</td>
                                    </tr>
                                    <!-- Table Row End -->
                                </table>
                            </td>
                        </tr>
                        <!-- Table Row End -->
                        <!-- Table Row Start -->
                        <tr>
                            <td height="30" colspan="2"></td>
                        </tr>
                        <!-- Table Row End -->
                        <!-- Table Row Start -->
                        <tr>
                            <td width="50%" class="f-14">@lang('app.note')</td>
                            <td width="50%" style="text-align: right" class="f-14">@lang('modules.invoiceSettings.invoiceTerms')</td>
                        </tr>
                        <!-- Table Row End -->
                        <!-- Table Row Start -->
                        <tr class="text-grey">
                            <td width="50%" class="f-14 line-height word-break">{!! nl2br($creditNote->note) !!}</td>
                            <td width="50%" style="text-align: right" class="f-14 line-height">{!! nl2br($invoiceSetting->invoice_terms) !!}</td>
                        </tr>
                        <!-- Table Row End -->
                    </table>
                </td>
            </tr>
            <!-- Table Row End -->


        </tbody>
    </table>


</body>

</html>
