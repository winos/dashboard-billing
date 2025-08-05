@extends($activeTemplate . 'layouts.' . strtolower(userGuard()['type']) . '_master')

@php
    $class = '';
    if (userGuard()['type'] == 'AGENT' || userGuard()['type'] == 'MERCHANT') {
        $class = 'mt-5';
    }
@endphp

@section('content')
    <div class="row justify-content-center {{ $class }}">
        <div class="col-md-8">
            <div class="card style--two">
                <div class="card-header">
                    <h5 class="card-title">@lang('Stripe Payment')</h5>
                </div>
                <div class="card-body card-body-deposit">
                    <div class="card-wrapper"></div>
                    <br><br>

                    <form role="form" class="disableSubmission payment appPayment" id="payment-form" method="{{ $data->method }}" action="{{ $data->url }}">
                        @csrf
                        <input type="hidden" value="{{ $data->track }}" name="track">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name">@lang('Name on Card')</label>
                                <div class="input-group">
                                    <input type="text" class="form--control custom-input" name="name" placeholder="@lang('Name on Card')" autocomplete="off" autofocus />
                                    <span class="input-group-text"><i class="fas fa-font"></i></span>

                                </div>

                            </div>
                            <div class="col-md-6">
                                <label for="cardNumber">@lang('Card Number')</label>
                                <div class="input-group">
                                    <input type="tel" class="form--control custom-input" name="cardNumber" placeholder="@lang('Valid Card Number')" autocomplete="off" required autofocus />
                                    <span class="input-group-text"><i class="fas fa-credit-card"></i></span>

                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label for="cardExpiry">@lang('Expiration Date')</label>
                                <input type="tel" class="form--control input-sz custom-input" name="cardExpiry" placeholder="@lang('MM / YYYY')" autocomplete="off" required />
                            </div>
                            <div class="col-md-6 ">
                                <label for="cardCVC">@lang('CVC Code')</label>
                                <input type="tel" class="form--control input-sz custom-input" name="cardCVC" placeholder="@lang('CVC')" autocomplete="off" required />
                            </div>
                        </div>
                        <br>
                        <button class="btn btn--base w-100" type="submit"> @lang('PAY NOW')
                        </button>

                    </form>


                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script src="{{ asset('assets/global/js/card.js') }}"></script>

    <script>
        (function($) {
            "use strict";
            var card = new Card({
                form: '#payment-form',
                container: '.card-wrapper',
                formSelectors: {
                    numberInput: 'input[name="cardNumber"]',
                    expiryInput: 'input[name="cardExpiry"]',
                    cvcInput: 'input[name="cardCVC"]',
                    nameInput: 'input[name="name"]'
                }
            });

            @if ($deposit->from_api)
                $('.appPayment').on('submit', function() {
                    $(this).find('[type=submit]').html('<i class="las la-spinner fa-spin"></i>');
                })
            @endif

        })(jQuery);
    </script>
@endpush
