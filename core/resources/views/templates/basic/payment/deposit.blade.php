@extends($activeTemplate . 'layouts.' . strtolower(userGuard()['type']) . '_master')

@php
    $class = '';
    if (userGuard()['type'] == 'AGENT' || userGuard()['type'] == 'MERCHANT') {
        $class = 'mt-5';
    }
@endphp

@section('content')
    <form action="{{ route(strtolower(userGuard()['type']) . '.deposit.insert') }}" method="POST" id="form">
        @csrf
        <div class="row justify-content-center gy-4 {{ $class }}">
            <div class="col-lg-6">
                <div class="add-money-card">
                    <h4 class="title"><i class="las la-plus-circle"></i> @lang('Add Money')</h4>
                    <div class="form-group">
                        <label>@lang('Select Your Wallet')</label>
                        <input type="hidden" name="currency">
                        <input type="hidden" name="currency_id">
                        <select class="select select2" name="wallet_id" id="wallet" required>
                            <option>@lang('Select Wallet')</option>

                            @foreach (userGuard()['user']->wallets as $wallet)
                                <option value="{{ $wallet->id }}" data-code="{{ $wallet->currency->currency_code }}"
                                    data-sym="{{ $wallet->currency->currency_symbol }}"
                                    data-rate="{{ $wallet->currency->rate }}"
                                    data-currency="{{ $wallet->currency->id }}"
                                    data-type="{{ $wallet->currency->currency_type }}"
                                    data-gateways="{{ $wallet->gateways() }}"
                                >
                                    @lang($wallet->currency->currency_code)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>@lang('Select Gateway')</label>
                        <select class="select gateway select2" name="method_code" disabled required>
                            <option value="">@lang('Select Gateway')</option>
                        </select>
                        <code class="text--danger gateway-msg"></code>
                    </div>
                    <div class="form-group mb-0">
                        <label>@lang('Amount')</label>
                        <div class="input-group">
                            <input class="form--control amount" type="number" step="any" name="amount" disabled
                                placeholder="Enter Amount" required>
                            <span class="input-group-text curr_code"></span>
                        </div>
                        <p><code class="text--warning limit">@lang('limit') : 0.00 <span class="curr_code"></span></code>
                        </p>
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
                                <div class="value">
                                    <span class="sym">{{ gs('cur_sym') }}</span><span class="show-amount">0.00</span>
                                </div>
                            </li>
                            <li>
                                <span class="caption">@lang('Charge')</span>
                                <div class="value"> 
                                    <span class="sym">{{ gs('cur_sym') }}</span><span class="charge">0.00</span> 
                                </div>
                            </li>
                        </ul>
                        <div class="add-money-details-bottom">
                            <span class="caption">@lang('Payable')</span>
                            <div class="value">
                                <span class="sym">{{ gs('cur_sym') }}</span><span class="payable">0.00</span> 
                            </div>
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
        (function($) {

            var wallet = null;

            $('#wallet').on('change', function() {

                if ($(this).find('option:selected').val() == '') {
                    return false
                }

                wallet  =  $(this);

                var gateways = $(this).find('option:selected').data('gateways')
                var sym = $(this).find('option:selected').data('sym')
                var code = $(this).find('option:selected').data('code')
                var rate = $(this).find('option:selected').data('rate')

                console.log(gateways);
                
                $('.curr_code').text(code)
                $('.sym').text(sym)
                $('input[name=currency]').val(code)
                $('input[name=currency_id]').val($(this).find('option:selected').data('currency'))

                $('.gateway').removeAttr('disabled')
                $('.gateway').children().remove()
                var html = `<option value="">@lang('Select Gateway')</option>`;

                if (gateways.length > 0) {
                    $.each(gateways, function(i, val) {
                        html +=
                            ` <option data-max="${val.max_amount}" data-min="${val.min_amount}" data-fixcharge = "${val.fixed_charge}" data-percent="${val.percent_charge}" data-rate="${rate}" value="${val.method_code}">${val.name}</option>`
                    });
                    $('.gateway').append(html)
                    $('.gateway-msg').text('')

                } else {
                    $('.gateway').attr('disabled', true)
                    $('.gateway').append(html)
                    $('.gateway-msg').text('No gateway found with this currency.')
                }

            })

            $('.gateway').on('change', function() {
                
                if ($('.gateway option:selected').val() == '') {
                    $('.amount').attr('disabled', true)
                    $('.charge').text('0.00')
                    $('.payable').text(parseFloat($('.amount').val()))
                    $('.limit').text('limit : 0.00 USD')
                    return false
                }
        
                $('.amount').removeAttr('disabled')
                var amount = $('.amount').val() ? parseFloat($('.amount').val()) : 0;
                var code = $(wallet).find('option:selected').data('code')

                var type = $(wallet).find('option:selected').data('type')

                var rate = parseFloat($('.gateway option:selected').data('rate'))
                var min = parseFloat($('.gateway option:selected').data('min'))
                var max = parseFloat($('.gateway option:selected').data('max'))

                min = min/rate;
                max = max/rate;

                var fixed = parseFloat($('.gateway option:selected').data('fixcharge'))
                var pCharge = parseFloat($('.gateway option:selected').data('percent'))
                var percent = (amount * parseFloat($('.gateway option:selected').data('percent'))) / 100
             
                var totalCharge = fixed + percent
                var totalAmount = amount + totalCharge
                var precesion = 0;

                if (type == 1) {
                    precesion = 2;
                } else {
                    precesion = 8;
                }
                
                $('.charge').text(totalCharge.toFixed(precesion))
                $('.payable').text(totalAmount.toFixed(precesion))
                $('.limit').text('limit : ' + min.toFixed(precesion) + ' ~ ' + max.toFixed(precesion) + ' ' + code)

                $('.f_charge').text(fixed)
                $('.p_charge').text(pCharge)

            })

            $('.amount').on('keyup', function() {
                var amount = parseFloat($(this).val())

                var type = $(wallet).find('option:selected').data('type')
                var code = $(wallet).find('option:selected').data('code')
                var fixed = parseFloat($('.gateway option:selected').data('fixcharge'))

                var percent = (amount * parseFloat($('.gateway option:selected').data('percent'))) / 100
                var totalCharge = fixed + percent
                var totalAmount = amount + totalCharge
                var precesion = 0;

                if (type == 1) {
                    precesion = 2;
                } else {
                    precesion = 8;
                }

                if (!isNaN(amount)) {
                    $('.show-amount').text(amount.toFixed(precesion))
                    $('.charge').text(totalCharge.toFixed(precesion))
                    $('.payable').text(totalAmount.toFixed(precesion))
                } else {
                    $('.show-amount').text('0.00')
                    $('.charge').text('0.00')
                    $('.payable').text('0.00')

                }
            })

            $('.req_confirm').on('click', function() {
                if ($('.amount').val() == '' || $('.gateway option:selected').val() == '' || $(wallet).find('option:selected').val() == '') {
                    notify('error', 'All fields are required')
                    return false
                }
                $('#form').submit()
                $(this).attr('disabled', true)
            })

        })(jQuery);
    </script>
@endpush
