@extends($activeTemplate.'layouts.'.strtolower(userGuard()['type']).'_master')

@php
    if (userGuard()['type'] == 'AGENT' || userGuard()['type'] == 'MERCHANT'){
        $class = 'mt-5 ';
    } 
@endphp

@section('content')
<div class="{{ @$class }} justify-content-center d-flex flex-wrap">
    <div class="col-xl-6 col-lg-6 col-md-8">
        <div class="d-widget shadow-sm">
            <div class="d-widget__header text-center">
                <h6>{{__($deposit->gateway->name)}}</h6>
            </div>
            <div class="d-widget__content">
                <ul class="cmn-list-two text-center mt-4">
                    <li>
                        @lang('Please Pay'):
                        <strong>{{showAmount($deposit->final_amount,getCurrency($deposit->method_currency), currencyFormat: false)}} {{__($deposit->method_currency)}} </strong>
                    </li>
                    <li>
                        @lang('To Get'):
                        <strong>{{showAmount($deposit->amount,$deposit->convertedCurrency, currencyFormat: false)}}  {{@$deposit->convertedCurrency->currency_code}}</strong>
                    </li>
                </ul>
            </div>
            <div class="d-widget__footer text-center border-0 pb-3">
                <button type="button" class="btn btn--base w-100" id="btn-confirm">@lang('Pay Now')</button>
                <form action="{{ route('ipn.'.$deposit->gateway->alias) }}" method="POST" class="text-center">
                    <script
                        src="//js.paystack.co/v1/inline.js"
                        data-key="{{ $data->key }}"
                        data-email="{{ $data->email }}"
                        data-amount="{{$data->amount}}"
                        data-currency="{{$data->currency}}"
                        data-ref="{{ $data->ref }}"
                        data-custom-button="btn-confirm"
                    >
                    </script>
                </form>
            </div>
        </div><!-- d-widget end -->
    </div>
</div>
@endsection


 