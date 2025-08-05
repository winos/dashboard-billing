<title> {{ gs()->sitename(__($pageTitle)) }}</title>
<link rel="shortcut icon" href="{{ siteFavicon() }}" type="image/x-icon">
<style>
    @import url(https://fonts.googleapis.com/css?family=Roboto:100,300,400,900,700,500,300,100);

    @media screen,
    print {

        * {
            margin: 0;
            box-sizing: border-box;

        }

        body {
            background: #E0E0E0;
            font-family: 'Roboto', sans-serif;
            background-image: url('');
            background-repeat: repeat-y;
            background-size: 100%;
            -moz-print-color-adjust: exact !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        ::selection {
            background: #f31544;
            color: #FFF;
        }

        ::moz-selection {
            background: #f31544;
            color: #FFF;
        }

        h1 {
            font-size: 1.5em;
            color: #222;
        }

        h2 {
            font-size: .9em;
        }

        h3 {
            font-size: 1.2em;
            font-weight: 300;
            line-height: 2em;
        }

        p {
            font-size: 12px;
            color: #666;
            line-height: 1.2em;
        }

        #invoiceholder {
            width: 100%;
            padding-top: 50px;
        }

        #invoice {
            position: relative;
            margin: 0 auto;
            width: 700px;
            background: #FFF;
        }

        [id*='invoice-'] {
            border-bottom: 1px solid #EEE;
            padding: 20px;
        }

        #invoice-top {
            min-height: 110px;
            background-color: unset;
            box-shadow: inset 0 0 0 1000px #002046;
            color: #fff;
        }

        #invoice-mid {
            min-height: 120px;
        }

        #invoice-bot {
            min-height: 250px;
        }

        .logo {
            float: left;
            height: 60px;
            width: 60px;
        }

        .logo img {
            max-width: 190px;
        }

        .info {
            display: block;
            float: left;
        }

        .title {
            float: right;
        }

        .title p {
            text-align: right;
        }

        #project {
            margin-left: 52%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 5px 0 5px 15px;
            border: 1px solid #EEE
        }

        .tabletitle {
            padding: 5px;
            background: #EEE;
        }

        .service {
            border: 1px solid #EEE;
        }

        .item {
            width: 50px;
        }

        .itemtext {
            font-size: .9em;
        }

        #legalcopy {
            margin-top: 30px;
        }

        #legalcopy .btn {
            display: block;
            width: 100%;
            text-align: center;
            padding: 15px 15px;
            border-radius: 5px;
        }

        #invoice-bot .select {
            padding: 0.625rem 1.25rem;
            width: 100%;
            border: 1px solid #cacaca;
            cursor: pointer;
            color: #464646;
            background-color: #fff;
            height: 3.125rem;
            border-radius: 4px;

        }

        #invoice-bot label {
            color: #535353;
            margin-bottom: 10px;
            font-size: 0.8125rem;
            font-weight: 500;
            display: block;

        }

        #invoice-bot .select:focus {
            outline: none;
        }

        #invoice-bot .select option {
            padding: 0.625rem 0;
            display: block;
            border-top: 1px solid #e5e5e5;
        }


        .pay-btn-wrapper {
            margin-top: 30px;
            margin-bottom: 30px;
            text-align: right;
        }


        .effect2 {
            position: relative;
        }

        .effect2:before,
        .effect2:after {
            z-index: -1;
            position: absolute;
            content: "";
            bottom: 15px;
            left: 10px;
            width: 50%;
            top: 80%;
            max-width: 300px;
            background: #777;
            -webkit-box-shadow: 0 15px 10px #777;
            -moz-box-shadow: 0 15px 10px #777;
            box-shadow: 0 15px 10px #777;
            -webkit-transform: rotate(-3deg);
            -moz-transform: rotate(-3deg);
            -o-transform: rotate(-3deg);
            -ms-transform: rotate(-3deg);
            transform: rotate(-3deg);
        }

        .effect2:after {
            -webkit-transform: rotate(3deg);
            -moz-transform: rotate(3deg);
            -o-transform: rotate(3deg);
            -ms-transform: rotate(3deg);
            transform: rotate(3deg);
            right: 10px;
            left: auto;
        }

        .legal {
            width: 70%;
        }

        .btn {
            background-color: unset;
            box-shadow: inset 0 0 0 1000px #4582ff;
            color: #fff !important;
            font-size: 14px;
            padding: 8px 15px;
            text-decoration: none;
        }

        .btn-dwn {
            background-color: unset;
            box-shadow: inset 0 0 0 1000px #28c76f;
            color: #fff !important;
            font-size: 14px;
            padding: 8px 15px;
            text-decoration: none;
        }

        .unpaid,
        .paid {
            padding: 5px 10px;
            display: inline-block;
            font-size: 14px;
            color: #fff;
        }

        .unpaid {
            background-color: #f31544;
        }

        .paid {
            background-color: #28c76f;
        }
    }
</style>


<div id="invoiceholder">
    <div id="invoice" class="effect2">

        <div id="invoice-top">
            <div class="logo"><img src="{{ siteLogo() }}" alt="image"></div>
            <div class="title">
                <h1 style="color: #fff;">@lang('Invoice') #{{ $invoice->invoice_num }}</h1>
                <p style="color: #f1f1f1;">@lang('Issued'): {{ showDateTime($invoice->created_at, 'd M Y') }}</p>
            </div>
            <!--End Title-->
        </div>
        <!--End InvoiceTop-->

        <div id="invoice-mid">
            <div class="info">
                <h2>{{ $invoice->invoice_to }}</h2>
                <p>{{ $invoice->email }}</br>
                    {{ $invoice->address }}</br>
            </div>

            <div id="project" style="text-align: right;">
                <h2>@lang('Total Amount')</h2>
                <h3>{{ showAmount($invoice->total_amount, $invoice->currency, currencyFormat: false) }} {{ $invoice->currency->currency_code }}
                </h3>
                <span class="{{ $invoice->pay_status == 1 ? 'paid' : 'unpaid' }}">{{ $invoice->pay_status == 1 ? 'PAID' : 'UNPAID' }}</span>
            </div>

        </div>
        <!--End Invoice Mid-->

        <div id="invoice-bot">

            <div id="table">
                <table>
                    <thead>
                        <tr class="tabletitle">
                            <th class="item">
                                <h2>@lang('Sr. No')</h2>
                            </th>
                            <th class="Hours">
                                <h2>@lang('Description')</h2>
                            </th>
                            <th class="subtotal">
                                <h2>@lang('Amount')</h2>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($invoice->items as $item)
                            <tr class="service">
                                <td class="tableitem">
                                    <p class="itemtext">{{ $loop->iteration }}</p>
                                </td>
                                <td class="tableitem">
                                    <p class="itemtext">{{ $item->item_name }}</p>
                                </td>
                                <td class="tableitem" style="text-align: right; padding-right:10px;">
                                    <p class="itemtext">
                                        {{ $invoice->currency->currency_symbol }}{{ showAmount($item->amount, $invoice->currency, currencyFormat: false) }}
                                        {{ $invoice->currency->currency_code }}</p>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="tabletitle">
                            <td></td>
                            <td class="Rate" style="text-align: right;">
                                <h2>@lang('Total')</h2>
                            </td>
                            <td class="payment">
                                <h2>{{ $invoice->currency->currency_symbol }}{{ showAmount($invoice->total_amount, $invoice->currency, currencyFormat:false) }}
                                    {{ $invoice->currency->currency_code }}</h2>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <!--End Table-->

            <div class="pay-btn-wrapper">
                <a href="javascript:void(0)" class="btn-dwn" id="dwn">@lang('Download')</a>
            </div>

            <div class="hidden">
                @if ($invoice->pay_status == 0 && $invoice->status != 2)
                    <form action="{{ route('user.invoice.payment.confirm', $invoiceNum) }}" method="get">
                        <div class="form-group text-start mt-3">
                            @if (gs('otp_verification') && (gs('en') || gs('sn') || auth()->user()->ts))
                                @include($activeTemplate . 'partials.otp_select')
                            @endif
                        </div>
                        <div id="legalcopy">
                            <button type="submit" class="btn w-100" style="border: none; cursor: pointer;">@lang('Pay Now')</button>
                        </div>
                    </form>
                @endif
            </div>

        </div>
        <!--End InvoiceBot-->
    </div>
    <!--End Invoice-->
</div><!-- End Invoice Holder-->

<!-- jQuery library -->
<script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>

<script>
    var dwnldBtn = document.getElementById('dwn')
    dwnldBtn.addEventListener('click', function() {
        window.print();
    })

    var beforePrint = function() {
        $('.hidden').css('display', 'none');
    };

    var afterPrint = function() {
        $('.hidden').css('display', 'block');
    };

    if (window.matchMedia) {
        var mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(mql) {
            if (mql.matches) {
                beforePrint();
            } else {
                afterPrint();
            }
        });
    }

    window.onbeforeprint = beforePrint;
    window.onafterprint = afterPrint;
</script>
