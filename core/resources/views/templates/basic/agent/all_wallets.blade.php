@extends($activeTemplate.'layouts.agent_master')

@section('content')

        <div class="row mb-5 mt-4 gy-4">
            <div class="col-md-12">
                <h5>@lang('Wallets')</h5>
            </div>
            @foreach ($wallets as $wallet)
                <div class="col-lg-4 col-md-6">
                    <div class="d-widget curve--shape">
                        <div class="d-widget__content">
                            <i class="las la-wallet"></i>
                            <h2 class="d-widget__amount fw-normal">
                                {{ $wallet->currency->currency_symbol }}{{showAmount($wallet->balance,$wallet->currency, currencyFormat:false)}} 
                                {{$wallet->currency->currency_code}}
                            </h2>
                        </div>
                      
                    </div><!-- d-widget end -->
                </div>
            @endforeach
        </div><!-- row end -->

@endsection

