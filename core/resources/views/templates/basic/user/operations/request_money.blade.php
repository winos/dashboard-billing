@extends($activeTemplate.'layouts.user_master')

@section('content')
<div class="col-xl-10">
    <div class="card style--two">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-center">
            <div class="bank-icon  me-2">
                <i class="las la-credit-card"></i>
            </div>
            <h4 class="fw-normal">@lang('Request Money')</h4>
        </div>
        <div class="card-body p-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form  method="POST" id="form">
                        @csrf
                        <input type="hidden" name="charge_id" value="{{$transferCharge->id}}">
                        <div class="d-widget">
                            <div class="d-widget__header">
                                <h6>@lang('Request Details')</h4>
                            </div>
                            <div class="d-widget__content px-5">
                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('Select Wallet')</label>
                                            <select class="select style--two currency select2" name="wallet_id" required>
                                                <option value="" selected>@lang('Select Wallet')</option>
                                                @foreach ($wallets as $wallet)
                                                <option value="{{$wallet->id}}" data-code="{{$wallet->currency->currency_code}}" data-rate="{{$wallet->currency->rate}}" data-type="{{$wallet->currency->currency_type}}">{{$wallet->currency->currency_code}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <label class="charge" data-charge="{{$transferCharge}}"> 
                                           @lang('Total Charge : ') <span class="total_charge">0.00</span>
                                        </label>
                                    </div><!-- row end -->
                                </div>
                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-6 form-group">
                                            <label class="mb-0">@lang('Amount to Request')<span class="text--danger">*</span> </label>
                                            <input type="number" step="any" class="form--control style--two amount" disabled name="amount" placeholder="0.00" required value="{{old('amount')}}">
                                            <label> 
                                                <span class="text--warning min">@lang('Min: '){{getAmount($transferCharge->min_limit)}} {{gs('cur_text')}} --</span>
                                                <span class="text--warning max">@lang('Max: '){{getAmount($transferCharge->max_limit)}} {{gs('cur_text')}}</span>
                                             </label>
                                        </div>
                                        <div class="col-lg-6 form-group">
                                            <label class="mb-0">@lang('Request to.')<span class="text--danger">*</span></label>
                                            <input type="text" class="form--control style--two checkUser" name="user" placeholder="@lang('Username / E-mail')" value="{{old('user')}}" required>
                                            <label class="exist text-end"></label>
                                        </div>
                                    </div><!-- row end -->
                                </div>
                                <div class="form-group">
                                    <label>@lang('Note for recipient')</label>
                                    <textarea class="form--control" name="note"></textarea>
                                </div>
                            </div>
                        </div>  
                        <div class="text-center">
                            <button type="submit" class="btn btn-md btn--base mt-4 request w-100">@lang('Request Now')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered " role="document">
    <div class="modal-content"> 
          <div class="modal-header">
            <h6 class="modal-title">@lang('Request Money Preview')</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
            <div class="modal-body text-center p-0">
                <div class="d-widget border-start-0 shadow-sm">
                    <div class="d-widget__content">
                        <ul class="cmn-list-two text-center mt-4">
                            <li class="list-group-item">@lang('Request Amount'): <strong class="req_amount"></strong></li>
                            <li class="list-group-item">@lang('Total Charge'): <strong class="charge"></strong></li> 
                            <li class="list-group-item">@lang('You will get'): <strong class="will_get"></strong></li> 
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
    'use strict';
    (function ($) {
        
        var precision;

        $('.amount').on('input',function () {
            if($(this).val() == ''){
                $('.total_charge').text('0.00')
            } else {
                var selected = $('.currency option:selected')
                if(selected.val()!=''){
                    var rate = selected.data('rate')
                    var code = selected.data('code')
                    var chargeData = $('.charge').data('charge')
                    var amount = $('.amount').val()
                    chargeCalc(amount,chargeData,rate,code)
                }

                }
        })

        $('.currency').on('change', function () {
            var selected = $('.currency option:selected')

            if(selected.val() == ''){
                $('.amount').attr('disabled',true)
                $('.total_charge').text('0.00')
                return false
            }

            $('.amount').attr('disabled',false)
            var rate = selected.data('rate')
            var code = selected.data('code')
            var type = selected.data('type')
            var chargeData = $('.charge').data('charge')
            var amount = $('.amount').val()
            chargeCalc(amount,chargeData,rate,code)

            var min_limit = '{{getAmount($transferCharge->min_limit)}}'
            var max_limit = '{{getAmount($transferCharge->max_limit)}}'

            var min = min_limit/rate
            var max = max_limit/rate

            if(type == 1){
                precision = 2
            } else {
                precision = 8
            }

            $('.min').text("@lang('Min'): "+min.toFixed(precision)+' '+code+' -- ')
            $('.max').text("@lang('Max'): "+max.toFixed(precision)+' '+code)
        });

        function chargeCalc(amount,chargeData,rate,code,$request = false) { 
            var percentCharge = amount * chargeData.percent_charge/100;
            var cap = chargeData.cap/rate;
            var fixedCharge = chargeData.fixed_charge/rate;
            var totalCharge = fixedCharge+percentCharge;

            if(cap != -1 && totalCharge > cap){
                totalCharge = cap
            }

            if($request){
                $('#confirm').find('.req_amount').text(amount+' '+code)
                $('#confirm').find('.charge').text(totalCharge.toFixed(precision)+' '+code)
                $('#confirm').find('.will_get').text((amount - totalCharge).toFixed(precision)+' '+code)
                $('#confirm').modal('show')
            } else {
                $('.total_charge').text(totalCharge.toFixed(precision)+' '+code)
            }
        }

        $('#form').on('submit',function () { 
            return partial();
        })

        function partial(){
            var selected = $('.currency option:selected')

            if(selected.val() =='' || $('.amount').val() ==''){
                $('.total_charge').text('0.00')
                notify('error','Please fill up the fields first.')
                return false
            }

            var rate = selected.data('rate')
            var code = selected.data('code')
            var chargeData = $('.charge').data('charge')
            var amount = $('.amount').val()

            chargeCalc(amount,chargeData,rate,code,true)
        }

        $('.checkUser').on('focusout',function(e){
            var url = '{{ route('user.check.exist') }}';
            var value = $(this).val();
            var token = '{{ csrf_token() }}';

            if(!value){
                $('.exist').text('');
                return false;
            }
        
            if ($(this).attr('name') == 'user') {
                var data = {user:value,_token:token}
            }

            $.post(url,data,function(response) {
                if(response.own){
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exist').addClass('text--danger').text(response.own);               
                    return false
                }
                if(response['data'] != null){
                    if($('.exist').hasClass('text--danger')){
                        $('.exist').removeClass('text--danger');
                    }
                    $('.exist').text(`Valid user for transaction.`).addClass('text--success');
                } else {
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exist').text('User doesn\'t  exists.').addClass('text--danger');
                    
                }
            });

        });

        $('#form').on('submit', function(){

            var confirmMdoal = $('#confirm');

            if(!confirmMdoal.is(':visible')){
                partial();
                confirmMdoal.modal('show');
                return false;
            }

        });

        var old = @json(session()->getOldInput());
        if(old.length != 0){
            $('input[name=user]').val(old.user);
            $('select[name=wallet_id]').val(old.wallet_id).change();
            $('input[name=amount]').val(old.amount);
            $('textarea[name=note]').val(old.note);
        }

    })(jQuery);
</script>
@endpush