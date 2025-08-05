@extends($activeTemplate.'layouts.'.strtolower(userGuard()['type']).'_master')



@section('content')
<form action="{{route(strtolower(userGuard()['type']).'.deposit.insert')}}" method="POST" id="form">
  @csrf
  <div class="row justify-content-center gy-4">
    <div class="col-lg-6">
      <div class="add-money-card">
        <h4 class="title"><i class="las la-plus-circle"></i> @lang('Invoice Payment')</h4>
          <div class="form-group">
            <label>@lang('Select Your Wallet')</label>
            <input type="hidden" name="currency" value="{{$wallet->currency->currency_code}}">
            <input type="hidden" name="currency_id" value="{{$wallet->currency->id}}">
            <select class="select" name="wallet_id" id="wallet"  required>
              <option value="{{$wallet->id}}"
                data-code="{{$wallet->currency->currency_code}}" 
                data-sym="{{$wallet->currency->currency_symbol}}" 
                data-currency="{{$wallet->currency->id}}" 
                data-rate="{{$wallet->currency->rate}}" 
                data-type="{{$wallet->currency->currency_type}}" {{$invoice ? $invoice->currency_id == $wallet->currency->id ? 'selected':'':''}} 
                data-gateways="{{$wallet->gateways()}}"
              >
              @lang($wallet->currency->currency_code)
              </option>
           </select>
          </div>
          <div class="form-group">
             <label>@lang('Select Gateway')</label>
              <select class="select gateway" name="method_code" required>
                   <option value="" selected>@lang('Select Gateway')</option>
                  @foreach ($gateways as $item)
                    <option data-max="{{$item->max_amount}}" 
                      data-min="{{$item->min_amount}}" 
                      data-fixcharge = "{{$item->fixed_charge}}" 
                      data-percent="{{$item->percent_charge}}"  
                      value="{{$item->method_code}}"
                      >{{$item->name}}
                    </option>
                  @endforeach
              </select>
              <code class="text--danger gateway-msg"></code>
          </div>
          <div class="form-group mb-0">
            <label>@lang('Amount')</label>
            <div class="input-group">
              <input class="form--control amount" type="text" name="amount" readonly value="{{getAmount($invoice->total_amount,2) }}" required>
              <span class="input-group-text curr_code">{{$code}}</span>
            </div>
            <code class="text--warning limit">@lang('limit') : 0.00 {{$code}}</code>
          </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="add-money-card style--two">
        <h4 class="title"><i class="lar la-file-alt"></i> @lang('Summary')</h4>
        <div class="add-moeny-card-middle">
          <ul class="add-money-details-list">
            <li>
              <span class="caption">@lang('Amount')</span>
              <div class="value"><span class="sym">{{$invoice->currency->currency_symbol}}</span> <span class="show-amount">{{showAmount($invoice->total_amount,$invoice->currency, currencyFormat: false)}}</span></div>
            </li>
            <li>
              <span class="caption">@lang('Charge')</span>
              <div class="value"> <span class="sym">{{$invoice->currency->currency_symbol}}</span> <span class="charge">0.00</span> </div>
            </li>
          </ul>
          <div class="add-money-details-bottom">
            <span class="caption">@lang('Payable')</span>
            <div class="value"><span class="sym">{{$invoice->currency->currency_symbol}}</span> <span class="payable">{{showAmount($invoice->total_amount,$invoice->currency, currencyFormat: false)}}</span> </div>
          </div>
        </div>
        <button type="submit" class="btn btn-md btn--base w-100 mt-3 req_confirm">@lang('Proceed')</button>
      </div>
    </div>
  </div>    
</form>
@endsection

@push('script')
     <script>
            'use strict';
            (function ($) {
              
                $('.gateway').on('change',function () { 
                   if($('.gateway option:selected').val() == ''){
                      $('.charge').text('0.00')
                      $('.payable').text(parseFloat($('.amount').val()))
                      $('.limit').text('limit : 0.00 USD')
                      return false
                    } 
                    var amount = $('.amount').val() ?  parseFloat($('.amount').val()):0; 
  
                    var code = $('#wallet option:selected').data('code')
                   
                    var type = $('#wallet option:selected').data('type')
                    var min = $('.gateway option:selected').data('min')
                    var max = $('.gateway option:selected').data('max')

                    var fixed = parseFloat($('.gateway option:selected').data('fixcharge'))
                    var percent = (amount * parseFloat($('.gateway option:selected').data('percent')))/100
                    var totalCharge = fixed + percent
                    var totalAmount = amount+totalCharge
                    var precesion = 0;
                   
                    if(type == 1 ){
                      precesion = 2;
                    } else {
                      precesion = 8;
                    }
                    $('.charge').text(totalCharge.toFixed(precesion))
                    $('.payable').text(totalAmount.toFixed(precesion))
                    $('.limit').text('limit : ' +min.toFixed(precesion) +' ~ '+ max.toFixed(precesion)+' '+code)

                })
                $('.req_confirm').on('click',function () { 
                    if($('.amount').val() == '' || $('.gateway option:selected').val() == ''|| $('#wallet option:selected').val() == ''){
                    notify('error','All fields are required')
                    return false
                    }
                 $('#form').submit()
                 $(this).attr('disabled',true)
              })
            })(jQuery);
     </script>
@endpush