<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Template CSS -->
    <!-- <link type="text/css" rel="stylesheet" media="all" href="css/main.css"> -->

    <title>@lang('modules.contracts.contractNumber') - #{{ $contract->id }}</title>
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

        .logo {
            height: 33px;
        }

    </style>
</head>

<body class="content-wrapper">
    <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
        <tbody>
            <!-- Table Row Start -->
            <tr>
                <td><img src="{{ invoice_setting()->logo_url }}" alt="{{ ucwords($global->company_name) }}"
                        class="logo" /></td>
                <td align="right" class="f-21 text-black font-weight-700 text-uppercase">@lang('app.menu.contract')</td>
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

                    </p>
                </td>
                <td>
                    <table class="text-black mt-1 f-13 b-collapse rightaligned">
                        <tr>
                            <td class="heading-table-left">@lang('modules.contracts.contractNumber')</td>
                            <td class="heading-table-right">#{{ $contract->id }}</td>
                        </tr>
                        <tr>
                            <td class="heading-table-left">@lang('modules.projects.startDate')</td>
                            <td class="heading-table-right">{{ $contract->start_date->format($global->date_format) }}
                            </td>
                        </tr>
                        @if ($contract->end_date != null)
                            <tr>
                                <td class="heading-table-left">@lang('modules.contracts.endDate')</td>
                                <td class="heading-table-right">
                                    {{ $contract->end_date->format($global->date_format) }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="heading-table-left">@lang('modules.contracts.contractType')</td>
                            <td class="heading-table-right">{{ $contract->contractType->name }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td height="30"></td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td colspan="2">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td class="f-14 text-black">

                                <p class="line-height mb-0">
                                    <span class="text-grey text-capitalize">@lang("app.client")</span><br>
                                    {{ ucwords($contract->client->name) }}<br>
                                    {{ ucwords($contract->client->clientDetails->company_name) }}
                                    {!! nl2br($contract->client->clientDetails->address) !!}
                                </p>

                            </td>

                            <td align="right">
                                @if ($contract->company_logo)
                                    <div class="text-uppercase bg-white unpaid rightaligned">
                                        <img src="{{ $contract->image_url }}"
                                            alt="{{ ucwords($contract->client->clientDetails->company_name) }}"
                                            class="logo" />
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>


            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td height="20" colspan="2"></td>
            </tr>
            <!-- Table Row End -->

        </tbody>
    </table>

    
    <div>
        <h5 class="text-grey text-capitalize">@lang('app.subject')</h5>
        <p class="f-15">{{ $contract->subject }}</p>

        <h5 class="text-grey text-capitalize">@lang('app.description')</h5>
        <p>{!! strip_tags($contract->contract_detail, ['p', 'b', 'strong', 'a']) !!}</p>

        @if ($contract->amount != 0)
            <div class="text-right pt-3 border-top">
                <h4>@lang('modules.contracts.contractValue'):
                    {{ $contract->amount.' '.$contract->currency->currency_code }}</h4>
            </div>
        @endif
    </div>
    
    @if ($contract->signature)
        <div style="text-align: right; margin-top: 10px;">
            <h2 class="name" style="margin-bottom: 20px;">@lang('app.signature')</h2>
            {!! Html::image($contract->signature->signature, '', ['class' => '', 'height' => '75px']) !!}
            <p>({{ $contract->signature->full_name }})</p>
        </div>
    @endif

</body>

</html>
