@extends($activeTemplate.'layouts.user_master')

@section('content')
<div class="col-xl-10">
    <div class="card style--two">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-center">
            <div class="bank-icon  me-2">
                <i class="las la-wallet"></i>
            </div>
            <h4 class="fw-normal">@lang($pageTitle)</h4>
        </div>
        <div class="card-body p-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form  method="POST" id="form">
                        @csrf
                        <div class="d-widget">
                            <div class="d-widget__header">
                                <h6>@lang('Provide Details')</h4>
                            </div>
                            <div class="d-widget__content px-5">
                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('Select Wallet')</label>
                                            <select class="select style--two currency select2" name="wallet_id"  required>
                                                <option value="">@lang('Select Wallet')</option>
                                                @foreach ($wallets as $wallet)
                                                    <option value="{{$wallet->id}}" 
                                                        data-code="{{$wallet->currency->currency_code}}" 
                                                        data-rate="{{$wallet->currency->rate}}" 
                                                        data-type="{{$wallet->currency->currency_type}}"
                                                    >
                                                        {{$wallet->currency->currency_code}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div><!-- row end -->
                                </div>
                                <div class="p-4 border mb-4"> 
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('Amount')<span class="text--danger">*</span> </label>
                                            <input type="number" step="any" class="form--control style--two amount" name="amount" placeholder="0.00" required value="{{old('amount')}}">
                                        </div>
                                        <label>
                                            <span class="text--warning min">@lang('Min: ')
                                                {{getAmount($voucherCharge->min_limit)}} {{defaultCurrency()}} --
                                            </span>
                                            <span class="text--warning max">@lang('Max: ')
                                                {{getAmount($voucherCharge->max_limit)}} {{defaultCurrency()}}
                                            </span>
                                         </label>
                                    </div><!-- row end -->
                                </div>
                                <input type="hidden" class="charge" data-fixcharge="{{$voucherCharge->fixed_charge}}" data-percentcharge="{{$voucherCharge->percent_charge}}" data-cap ="{{$voucherCharge->cap}}">
                                <input type="hidden" class="commission">
                                @if(gs('otp_verification') && (gs('en') || gs('sn') || auth()->user()->ts))
                                    <div class="p-4 border mb-4">
                                        <div class="row">
                                            <div class="col-lg-12 form-group">
                                                @include($activeTemplate.'partials.otp_select')
                                            </div>
                                        </div><!-- row end -->
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-md btn--base mt-4 create w-100" >@lang('Create Voucher')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
          <div class="modal-header">
            <h6 class="modal-title">@lang('Please Confirm to Create Voucher')</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
            <div class="modal-body text-center p-0">
                <div class="d-widget border-start-0 shadow-sm">

                    <div class="d-widget__content">
                        <ul class="cmn-list-two text-center mt-4">
                            <li class="list-group-item">@lang('Amount'): <b class="m-amount"></b></li>
                            <li class="list-group-item">@lang('Charge'): <b class="m-charge"></b></li>
                            <li class="list-group-item">@lang('Payable') : <b class="m-payable"></b></li>
                        </ul>
                    </div>
                    <div class="d-widget__footer text-center border-0 pb-3">
                        <button type="submit" class="btn btn-md w-100 d-block btn--base req_confirm" form="form">
                            @lang('Confirm') <i class="las la-long-arrow-alt-right"></i>
                        </button>
                    </div>
                </div>
            </div>
       </div>
    </div> 
</div>
@endsection

@push('script')
<script>
    (function ($) {
        'use strict';

        function partial(check = false){
        
            if(check){
                if($('.amount').val() == ''){
                    notify('error','Please provide the amount first.')
                    return false
                }

                if($('.currency option:selected').val() == ''){
                    notify('error','Please select a wallet.')
                    return false
                }
            }

            var rate  =  $('.currency option:selected').data('rate')
            var currCode  =  $('.currency option:selected').data('code')
            var amount = parseFloat($('.amount').val());
            var fixedCharge = parseFloat($('.charge').data('fixcharge'))/parseFloat(rate)
            var percentCharge = parseFloat($('.charge').data('percentcharge'))
            var cap = parseFloat($('.charge').data('cap'))/parseFloat(rate);

            var totalCharge = fixedCharge + (amount*percentCharge/100)

            if(cap != -1 && totalCharge > cap){
                totalCharge = cap
            }

            var totalAmount = amount + totalCharge;
            var modal = $('#confirm')

            modal.find('.m-amount').text(amount+' '+currCode)
            modal.find('.m-charge').text(totalCharge.toFixed(2)+' '+currCode)
            modal.find('.m-payable').text(totalAmount.toFixed(2)+' '+currCode)

            if(check){
                modal.modal('show')
            }

        }

        $('#form').on('submit', function(){

            var confirmMdoal = $('#confirm');

            if(!confirmMdoal.is(':visible')){
                partial(true);
                confirmMdoal.modal('show');
                return false;
            }

        });

        $('.currency').on('change',function () {
        
            if(typeof(rate) == 'undefined'){ 
                partial();
            }

            var rate  =  $('.currency option:selected').data('rate')
            var currCode  =  $('.currency option:selected').data('code')
            var type  =  $('.currency option:selected').data('type')
            var min_limit = '{{getAmount($voucherCharge->min_limit)}}'
            var max_limit = '{{getAmount($voucherCharge->max_limit)}}'

            var min = min_limit/rate
            var max = max_limit/rate

            if(type==1){
                var precision = 2
            } else {
                var precision = 8
            }
            
            $('.min').text("@lang('Min'): "+min.toFixed(precision)+' '+currCode+' -- ')
            $('.max').text("@lang('Max'): "+max.toFixed(precision)+' '+currCode)

        })

        var old = @json(session()->getOldInput());
        if(old.length != 0){
            $('select[name=wallet_id]').val(old.wallet_id).change();
            $('input[name=amount]').val(old.amount);
            $('select[name=otp_type]').val(old.otp_type);
        }
        
    })(jQuery);
</script>
@endpush

