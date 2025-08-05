@extends($activeTemplate . 'layouts.frontend')

@php
    $content = getContent('login.content', true)->data_values;
    $policies = getContent('policy_pages.element', false, null, true);
@endphp

@section('content')
    <section class="pt-100 pb-100 d-flex flex-wrap align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="account-wrapper">
                        <div class="left bg_img" style="background-image: url('{{ frontendImage('login', @$content->background_image, '768x1200') }}');">
                        </div>
                        <div class="right">
                            <div class="inner">
                                <div class="text-center">
                                    <h2 class="title">{{ __($pageTitle) }}</h2>
                                    <p class="font-size--14px mt-1 fw-bold">@lang('Start your journey with') {{ __(gs('site_name')) }}.</p>
                                </div>

                                <div class="mt-4">
                                    @include($activeTemplate . 'partials.social_login')
                                </div>

                                <form action="{{ route('user.register') }}" method="POST" class="verify-gcaptcha account-form mt-5 disableSubmission">
                                    @csrf
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div class="user-account-check text-center">
                                                <input class="form-check-input exclude" type="radio" value="personal" name="accountRadioCheck" id="personalAccount" checked>
                                                <label class="form-check-label" for="personalAccount">
                                                    <i class="las la-user"></i>
                                                    <span>@lang('Personal Account')</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="user-account-check text-center">
                                                <input class="form-check-input exclude" type="radio" value="company" name="accountRadioCheck" id="companyAccount">
                                                <label class="form-check-label" for="companyAccount">
                                                    <i class="las la-briefcase"></i>
                                                    <span>@lang('Company Account')</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group company-name d-none">
                                        <label for="company-name">@lang('Legal Name of Company')</label>
                                        <input id="company-name" type="text" class="form--control" name="company_name" placeholder="@lang('Legal name of company')" value="{{ old('company_name') }}" disabled>
                                    </div>
                                    @if (session()->has('reference'))
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="referenceBy" class="form-label">@lang('Reference by')</label>
                                                <input type="text" name="referBy" id="referenceBy" class="form-control form--control" value="{{ session()->get('reference') }}" placeholder="@lang('Reference')" readonly>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="form-group col-sm-6">
                                            <label for="InputFirstname" class="col-form-label">@lang('First Name')</label>
                                            <input type="text" class="form--control" id="InputFirstname" name="firstname" placeholder="@lang('First Name')" value="{{ old('firstname') }}" required>
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label for="lastname" class="col-form-label">@lang('Last Name')</label>
                                            <input type="text" class="form--control" id="lastname" name="lastname" placeholder="@lang('Last Name')" value="{{ old('lastname') }}" required>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label for="email">@lang('E-Mail Address')</label>
                                        <input id="email" type="email" class="form--control checkUser" placeholder="@lang('Email address')" name="email" value="{{ old('email') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="password">@lang('Password')</label>
                                        <div class="form-group">
                                            <input id="password" type="password" class="form--control" name="password" placeholder="@lang('Enter password')" required>
                                            @if (gs('secure_password'))
                                                <div class="input-popup">
                                                    <p class="error lower">@lang('1 small letter minimum')</p>
                                                    <p class="error capital">@lang('1 capital letter minimum')</p>
                                                    <p class="error number">@lang('1 number minimum')</p>
                                                    <p class="error special">@lang('1 special character minimum')</p>
                                                    <p class="error minimum">@lang('6 character password')</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="password-confirm">@lang('Confirm Password')</label>
                                        <input id="password-confirm" type="password" class="form--control" placeholder="@lang('Confirm password')" name="password_confirmation" required autocomplete="new-password">
                                    </div>

                                    <x-captcha />

                                    @if (gs('agree'))
                                        <div class="form-group">
                                            <div class="form-check d-flex align-items-center">
                                                <input class="form-check-input" type="checkbox" name="agree" id="termsAndConditions" required @checked(old('agree'))>
                                                <label class="form-check-label mb-0 ms-2" for="termsAndConditions">
                                                    @lang('I agree with')
                                                    @foreach ($policies as $policy)
                                                        <a href="{{ route('policy.pages', $policy->slug) }}" target="_blank">
                                                            {{ __($policy->data_values->title) }}
                                                        </a>
                                                        @if (!$loop->last)
                                                            ,
                                                        @endif
                                                    @endforeach
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <button type="submit" class="btn btn--base w-100">@lang('Register')</button>
                                    </div>
                                </form>
                                <p class="font-size--14px text-center">@lang('Have an account?')
                                    <a href="{{ route('user.login') }}">@lang('Login Here').</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="existModalCenter" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <i class="las la-exclamation-circle text-secondary display-2 mb-15"></i>
                    <h6 class="text-center">@lang('You already have an account. Please login')</h6>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn--dark btn-sm" data-bs-dismiss="modal">@lang('Close')</button>
                    <a href="{{ route('user.login') }}" class="btn btn--base btn-sm">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .country-code .input-group-text {
            background: #fff !important;
        }

        .country-code select {
            border: none;
        }

        .country-code select:focus {
            border: none;
            outline: none;
        }
    </style>
@endpush

@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

@push('script')
    <script>
        "use strict";
        (function($) {

            $('.checkUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';

                var data = {
                    email: value,
                    _token: token
                }

                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $('#existModalCenter').modal('show');
                    }
                });
            });

            $('#companyAccount').on('click', function() {
                $('.company-name').removeClass('d-none')
                $('.company-name').find('input[name=company_name]').removeAttr('disabled').attr('required', 'required')
                $('.firstname').text('@lang('Representative First Name')')
                $('.lastname').text('@lang('Representative Last Name')')
            });

            $('#personalAccount').on('click', function() {
                $('.company-name').addClass('d-none')
                $('.company-name').find('input[name=company_name]').attr('disabled', true)
                $('.firstname').text('@lang('First Name')')
                $('.lastname').text('@lang('Last Name')')
            });

            var old = @json(session()->getOldInput());
            if (old.length != 0) {
                $("input[name=accountRadioCheck][value=" + old.accountRadioCheck + "]").attr('checked', 'checked');
                $('input[name=username]').val(old.username);
            }

        })(jQuery);
    </script>
@endpush
