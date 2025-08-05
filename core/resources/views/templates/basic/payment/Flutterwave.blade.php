@extends($activeTemplate . 'layouts.' . strtolower(userGuard()['type']) . '_master')

@php
    if (userGuard()['type'] == 'AGENT' || userGuard()['type'] == 'MERCHANT') {
        $class = 'mt-5 ';
    }
@endphp


@section('content')
    <div class="{{ @$class }} justify-content-center d-flex flex-wrap">
        <div class="col-xl-6 col-lg-6 col-md-8">
            <div class="d-widget shadow-sm">
                <div class="d-widget__header text-center">
                    <h6>{{ __($deposit->gateway->name) }}</h6>
                </div>
                <div class="d-widget__content">
                    <ul class="cmn-list-two text-center mt-4">
                        <li>
                            @lang('Please Pay'):
                            <strong>{{ showAmount($deposit->final_amo, getCurrency($deposit->method_currency), currencyFormat: false) }} {{ __($deposit->method_currency) }} </strong>
                        </li>
                        <li>
                            @lang('To Get'):
                            <strong>{{ showAmount($deposit->amount, $deposit->convertedCurrency, currencyFormat: false) }} {{ @$deposit->convertedCurrency->currency_code }}</strong>
                        </li>
                    </ul>
                </div>
                <div class="d-widget__footer text-center border-0 pb-3">
                    <button type="button" class="btn btn--base w-100" id="btn-confirm" onClick="payWithRave()">@lang('Pay Now')</button>

                </div>
            </div><!-- d-widget end -->
        </div>
    </div>
@endsection

@push('script')
    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
    <script>
        "use strict"
        var btn = document.querySelector("#btn-confirm");
        btn.setAttribute("type", "button");
        const API_publicKey = "{{ $data->API_publicKey }}";

        function payWithRave() {
            var x = getpaidSetup({
                PBFPubKey: API_publicKey,
                customer_email: "{{ $data->customer_email }}",
                amount: "{{ $data->amount }}",
                customer_phone: "{{ $data->customer_phone }}",
                currency: "{{ $data->currency }}",
                txref: "{{ $data->txref }}",
                onclose: function() {},
                callback: function(response) {
                    var txref = response.tx.txRef;
                    var status = response.tx.status;
                    var chargeResponse = response.tx.chargeResponseCode;
                    if (chargeResponse == "00" || chargeResponse == "0") {
                        window.location = '{{ url('ipn/flutterwave') }}/' + txref + '/' + status;
                    } else {
                        window.location = '{{ url('ipn/flutterwave') }}/' + txref + '/' + status;
                    }
                    // x.close(); // use this to close the modal immediately after payment.
                }
            });
        }
    </script>
@endpush
