@extends($activeTemplate.'layouts.agent_master')

@section('content')
<div class="card custom--card border-0 mt-5">
    <div class="card-body p-4">
        <form  method="POST" id="form">
            <div class="row justify-content-center">
                <div class="col-lg-8 ">
                    @csrf
                    <div class="d-widget shadow-sm">
                        <div class="d-widget__header">
                            <h6>@lang('Money In Detail')</h4>
                        </div>
                        <div class="d-widget__content px-5">
                            <div class="p-4 border mb-4">
                                <div class="row">
                                    <div class="col-lg-12 form-group">
                                        <label class="mb-0">@lang('Select Wallet')<span class="text--danger">*</span></label>
                                        <select class="select style--two currency select2" name="wallet_id" required>
                                            <option value="" selected>@lang('Select Wallet')</option>
                                            @foreach ($wallets as $wallet)
                                                <option 
                                                    value="{{$wallet->id}}" 
                                                    data-code="{{$wallet->currency->currency_code}}" 
                                                    data-rate="{{$wallet->currency->rate}}" 
                                                    data-type="{{$wallet->currency->currency_type}}"
                                                >
                                                    {{$wallet->currency->currency_code}}
                                                </option>
                                            @endforeach
                                        </select>
                                        </div>
                                    <span class="charge" data-charge="{{$moneyInCharge}}"></span>
                                </div><!-- row end -->
                            </div>
                            <div class="p-4 border mb-4">
                                <div class="row">
                                    <div class="col-lg-12 form-group">
                                        <label class="mb-0">@lang('User Username/E-mail')<span class="text--danger">*</span> </label>
                                        <input type="text" class="form--control style--two checkUser" name="user" placeholder="@lang('User Username/E-mail')" required value="{{old('user')}}">
                                    </div>
                                    <label class="exist text-end"></label>
                                </div><!-- row end -->
                            </div>
                            <div class="p-4 border mb-4">
                                <div class="row">
                                    <div class="col-lg-12 form-group">
                                        <label class="mb-0">@lang('Amount')<span class="text--danger">*</span> </label>
                                        <input type="number" step="any" class="form--control style--two amount" name="amount" placeholder="@lang('Amount')" required value="{{old('amount')}}">
                                    </div>
                                    <label>
                                        <span class="text--warning min">@lang('Min: '){{getAmount($moneyInCharge->min_limit)}} {{defaultCurrency()}} --</span>
                                        <span class="text--warning max">@lang('Max: '){{getAmount($moneyInCharge->max_limit)}} {{defaultCurrency()}}</span>
                                    </label>
                                </div><!-- row end -->
                            </div> 
                            @if(gs('otp_verification') && (gs('en') || gs('sn') || agent()->ts))
                                <div class="p-4 border mt-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            @include($activeTemplate.'partials.otp_select')
                                        </div>
                                    </div><!-- row end -->
                                </div>
                            @endif
                            <button type="submit" class="btn btn-md btn--base mt-4 money_in w-100">@lang('Money In')</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
    <script>
        'use strict';
        (function ($) {
            $('.checkUser').on('focusout',function(e){
                var url = '{{ route('agent.user.check.exist') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';
                var data = {user:value,_token:token}

                if(!value){
                    $('.exist').text('');
                    return false;
                }

                $.post(url,data,function(response) {
                    if(response['data'] != null){
                        if($('.exist').hasClass('text--danger')){
                            $('.exist').removeClass('text--danger');
                        }
                        $('.exist').text(`Valid user to money in.`).addClass('text--success');
                    } else {
                        if($('.exist').hasClass('text--success')){
                            $('.exist').removeClass('text--success');
                        }
                        $('.exist').text('User not found.').addClass('text--danger');
                    }
                });
            });

            $('.currency').on('change', function () {
                var selected = $('.currency option:selected')
                if(selected.val()== ''){
                    return false;
                }
                var rate = selected.data('rate')
                var code = selected.data('code')
                var type = selected.data('type')

                var min_limit = '{{getAmount($moneyInCharge->min_limit)}}'
                var max_limit = '{{getAmount($moneyInCharge->max_limit)}}'

                var min = min_limit/rate
                var max = max_limit/rate
                if(type==1){
                    var precision = 2
                } else {
                    var precision = 8
                }
                $('.min').text("@lang('Min'): "+min.toFixed(precision)+' '+code+' -- ')
                $('.max').text("@lang('Max'): "+max.toFixed(precision)+' '+code)

            });

            $('.req_confirm').on('click',function () {
                $('#form').submit()
                $(this).attr('disabled',true)
            })
            var old = @json(session()->getOldInput());
            if(old.length != 0){
                $('select[name=wallet_id]').val(old.wallet_id).change();
                $('input[name=user]').val(old.user);
                $('input[name=amount]').val(old.amount);
                $('select[name=otp_type]').val(old.otp_type);
            }
        })(jQuery);
    </script>
@endpush
