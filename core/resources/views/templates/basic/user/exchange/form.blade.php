@extends($activeTemplate . 'layouts.user_master')
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
                                <h6>@lang('Exchange')</h4>
                            </div>
                            <div class="d-widget__content px-5">
                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('Amount')<span class="text--danger">*</span>
                                            </label>
                                            <input type="number" step="any" class="form--control style--two amount" name="amount"
                                                placeholder="0.00" required value="{{ old('amount') }}">
                                        </div>
                                    </div><!-- row end -->
                                </div>

                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-6 form-group">
                                            <label class="mb-0">@lang('From Currency')<span
                                                    class="text--danger">*</span></label>
                                            <select class="select style--two from_currency select2" name="from_wallet_id" required>
                                                <option value="">@lang('From Currency')</option>
                                                @foreach ($user->wallets()->where('balance', '>', 0)->get() as $fromWallet)
                                                    <option value="{{ $fromWallet->id }}"
                                                        data-code="{{ $fromWallet->currency->currency_code }}"
                                                        data-rate="{{ $fromWallet->currency->rate }}"
                                                        data-type="{{ $fromWallet->currency->currency_type }}"
                                                    >
                                                        {{ $fromWallet->currency->currency_code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-6 form-group">
                                            <label class="mb-0">@lang('To Currency')<span
                                                    class="text--danger">*</span></label>
                                            <select class="select style--two to_currency select2" name="to_wallet_id" required>
                                                <option value="">@lang('To Currency')</option>
                                                @foreach ($user->wallets()->get() as $toWallet)
                                                    <option value="{{ $toWallet->id }}"
                                                        data-code="{{ $toWallet->currency->currency_code }}"
                                                        data-rate="{{ $toWallet->currency->rate }}"
                                                        data-type="{{ $toWallet->currency->currency_type }}"
                                                    >
                                                        {{ $toWallet->currency->currency_code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div><!-- row end -->
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-md btn--base mt-4 exchange w-100">@lang('Exchange')</button>
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
                <h6 class="modal-title">@lang('Exchange Calculation')</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <div class="d-widget border-start-0 shadow-sm">
                    <div class="d-widget__content">
                        <ul class="cmn-list-two text-center mt-4">
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center">
                                <strong class="from_curr"> </strong>
                                <strong class="text--base">@lang('TO')</strong>
                                <strong class="to_curr"></strong>
                            </li>
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="from_curr_val"></span>
                                <strong>---------------------------------------------------</strong>
                                <span class="to_curr_val"></span>
                            </li>
                        </ul>
                    </div>
                    <div class="d-widget__footer text-center border-0 pb-3">
                        <button type="submit" class="btn btn-md w-100 d-block btn--base req_confirm" form="form">@lang('Confirm')
                            <i class="las la-long-arrow-alt-right"></i>
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
        (function($) {
            $('.to_currency').on('change', function() {
                var fromCurr = $('.from_currency option:selected').val()
                if ($('.to_currency option:selected').val() == fromCurr) {
                    notify('error', 'Can\'t exchange within same wallet.')
                    $('.exchange').attr('disabled', true);
                } else {
                    $('.exchange').attr('disabled', false);
                }

            })

            $('#form').on('submit', function(){

                var confirmMdoal = $('#confirm');

                if(!confirmMdoal.is(':visible')){

                    var amount = $('.amount').val();
                    if (amount == '') {
                        notify('error', 'Please provide the amount first.')
                        return false
                    }
                    var fromCurr = $('.from_currency option:selected').data('code')
                    var toCurr = $('.to_currency option:selected').data('code')
                    if (!fromCurr || !toCurr) {
                        notify('error', 'Please select the currencies.')
                        return false
                    }
                    var toCurrType = $('.to_currency option:selected').data('type')
                    var fromCurrRate = parseFloat($('.from_currency option:selected').data('rate'))
                    var baseCurrAmount = amount * fromCurrRate;
                    var toCurrRate = parseFloat($('.to_currency option:selected').data('rate'))
                    
                    if (toCurrType == 1) {
                        var toCurrAmount = (baseCurrAmount / toCurrRate).toFixed(2);
                    }else{
                        var toCurrAmount = (baseCurrAmount / toCurrRate).toFixed(8);
                    }
                    
                    $('#confirm').find('.from_curr').text(fromCurr)
                    $('#confirm').find('.to_curr').text(toCurr)
                    $('#confirm').find('.from_curr_val').text(parseFloat(amount))
                    $('#confirm').find('.to_curr_val').text(toCurrAmount)
                    $('#confirm').modal('show')

                    confirmMdoal.modal('show');
                    return false;
                }

            });

            var old = @json(session()->getOldInput());
            if(old.length != 0){
                $('select[name=from_wallet_id]').val(old.from_wallet_id);
                $('select[name=to_wallet_id]').val(old.to_wallet_id);
            }
            
        })(jQuery);
    </script>
@endpush
