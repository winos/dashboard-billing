@extends($activeTemplate.'layouts.user_master')

@section('content')
    <div class="col-xl-6 col-lg-6 col-md-8">
       <form  method="POST" class="disableSubmission">
           @csrf
            <div class="d-widget shadow-sm">
                <div class="d-widget__header text-center">
                    <h6>{{__($withdraw->method->name)}}</h6>
                </div>
                <div class="d-widget__content">
                    <div class="w-50 mx-auto">
                     
                    </div>
                    <ul class="cmn-list-two text-center">
                        <li>
                            @lang('Requested Amount '):
                            <strong>{{showAmount($withdraw->amount,$withdraw->curr, currencyFormat: false)}} </strong> {{$withdraw->curr->currency_code}}
                        </li>
                        <li>
                            @lang('Withdraw Charge '):
                            <strong>{{showAmount($withdraw->charge,$withdraw->curr, currencyFormat: false)}}</strong> {{$withdraw->curr->currency_code}}
                        </li>
                        <li>
                            @lang('You will get '): <strong> {{showAmount($withdraw->final_amount,$withdraw->curr, currencyFormat: false)}}</strong> {{$withdraw->curr->currency_code}}
                        </li>
                        <li>
                            @lang('Your balance will be '): <strong> {{showAmount(@$withdraw->wallet->balance - $withdraw->amount,$withdraw->curr, currencyFormat: false)}}</strong> {{$withdraw->curr->currency_code}}
                        </li>
                    </ul>
                    @if(gs('otp_verification') && (gs('en') || gs('sn') || auth()->user()->ts))
                    <div class="p-4 border mt-4">
                        <div class="row">
                            <div class="col-lg-12 form-group">
                                @include($activeTemplate.'partials.otp_select')
                            </div>
                        </div><!-- row end -->
                    </div>
                    @endif
                </div>
                <div class="d-widget__footer text-center border-0 pb-3">
                    <button type="submit" class="btn btn-md w-100 d-block btn--base">@lang('Confirm') <i class="las la-long-arrow-alt-right"></i></button>
                </div>
            </div><!-- d-widget end -->
       </form>
    </div>
@endsection

