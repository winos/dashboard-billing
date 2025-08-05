@extends($activeTemplate . 'layouts.' . strtolower(userGuard()['type']) . '_master')
@section('content')
    @php
        $class = '';
        if (userGuard()['type'] == 'AGENT' || userGuard()['type'] == 'MERCHANT') {
            $class = 'mt-5';
        }
    @endphp
    <div class="row justify-content-center {{ $class }}">
        <div class="col-md-6">
            <div class="card style--two">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-center">
                    <div class="bank-icon  me-2">
                        <i class="las la-code"></i>
                    </div>
                    <h4 class="fw-normal">{{ __($pageTitle) }}</h4>
                </div>
                <div class="card-body p-4">
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <div class="d-widget">
                                <div class="d-widget__content px-5">
                                    @if ($userAction->type == 'email' || $userAction->type == 'sms')
                                        <div class="text-center">
                                            @if ($userAction->type == 'email')
                                                <p>@lang('Please check your email to get a six digit OTP')</p>
                                            @else
                                                <p>@lang('Please check your mobile to get a six digit OTP')</p>
                                            @endif
                                            @php
                                                $startTime = \Carbon\Carbon::now();
                                                $finishTime = \Carbon\Carbon::parse($userAction->expired_at);
                                                
                                                $totalDuration = round(abs($finishTime->diffInSeconds($startTime)));
                                                if ($startTime > $finishTime) {
                                                    $totalDuration = 0;
                                                }
                                            @endphp
                                            <p class="mt-2">@lang('OTP will be expired in the next')</p>
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            <div
                                                class="expired-time-circle @if ($totalDuration == 0) danger-border @endif">
                                                <div class="exp-time">{{ $totalDuration }}</div>
                                                @lang('Seconds')
                                                <div class="animation-circle"></div>
                                            </div>

                                            <div class="border-circle"></div>
                                        </div>

                                        <div class="try-btn-wrapper d-none mt-2 text-center">
                                            <p class="text-danger ">@lang('Your OTP has been expired') </p>
                                            <a class="" href="{{ route('verify.otp.resend') }}">@lang('Resend OTP')</a>
                                        </div>
                                    @endif
                                    <div class="form-area">
                                        <form action="{{ route('verify.otp.submit') }}" method="post">
                                            @csrf
                                            <div class="form-group">
                                                <label>@lang('Verification Code')</label>
                                                <input type="text" name="code" id="code" class="form--control"
                                                    required autocomplete="off">
                                            </div>
                                            <button type="submit" class="btn btn--base w-100">@lang('Verify')</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('style')
    @isset($totalDuration)
        <style type="text/css">
            .animation-circle {
                position: absolute;
                top: 0;
                left: 0;
                border: 8px solid #f44336;
                height: 100%;
                width: 100%;
                border-radius: 150px;
                box-shadow: 1px 1px 1px 1px rgba(255, 0, 0, 0.5);
                transform: rotateY(180deg);
                animation-name: clipCircle;
                animation-duration: {{ $totalDuration }}s;
                animation-iteration-count: 1;
                animation-timing-function: cubic-bezier(0, 0, 1, 1);
                z-index: 1;
            }
        </style>
    @endpush
@endisset
@push('script')
    <script>
        'use strict';
        (function($) {
            @isset($totalDuration)
                let seconds = Number($('.exp-time').text());

                setInterval(function() {
                    seconds = Number($('.exp-time').text());
                    if (seconds == 0) {
                        $('.try-btn-wrapper').removeClass('d-none');
                        $('.expired-time-circle').addClass('danger-border')
                        $('.form-area').addClass('d-none');
                    }

                    $(".exp-time").load(window.location.href + " .exp-time");

                }, 1000);
            @endisset

            $('#code').on('input change', function() {
                var xx = document.getElementById('code').value;

                $(this).val(function(index, value) {
                    value = value.substr(0, 7);
                    return value.replace(/\W/gi, '').replace(/(.{3})/g, '$1 ');
                });

            });

        })(jQuery)
    </script>
@endpush
