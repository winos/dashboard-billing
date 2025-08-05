@extends($activeTemplate.'layouts.common_auth')
@php
    $content = getContent('merchant_login.content',true)->data_values;
    $policies = getContent('policy_pages.element', false, null, true);
@endphp
@section('content')

<section class="account-section style--two">
    <div class="left">
      <div class="left-inner w-100">
        <div class="text-center">
          <a class="site-logo" href="{{route('home')}}"><img src="{{siteLogo('dark')}}" alt="logo"></a>
        </div>
        <form class="account-form mt-5 disableSubmission" action="{{ route('merchant.register') }}" method="POST">
            <div class="row">
                @csrf
                @if(session()->get('reference') != null)
                    <div class="form-group">
                        <label for="referenceBy">@lang('Reference By')</label>
                        <input type="text" name="referBy" id="referenceBy" class="form--control" value="{{session()->get('reference')}}" readonly>
                    </div>
                @endif
                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="username">{{ __('Username') }}</label>
                    <input id="username" type="text" class="form--control checkUser" placeholder="@lang('Username')" name="username" value="{{ old('username') }}" required>
                    <small class="text-danger usernameExist"></small>
                </div>
                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="email">@lang('E-Mail Address')</label>
                    <input id="email" type="email" class="form--control checkUser" placeholder="@lang('Email Address')" name="email" value="{{ old('email') }}" required>
                </div>
                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="country">{{ __('Country') }}</label>
                    <select name="country" id="country" class="form--control select2">
                        @foreach($countries as $key => $country)
                            <option data-mobile_code="{{ $country->dial_code }}" value="{{ $country->country }}" data-code="{{ $key }}">{{ __($country->country) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="mobile">@lang('Mobile')</label>
                    <div class="input-group">
                        <span class="input-group-text mobile-code">
                        </span>
                        <input type="hidden" name="mobile_code">
                        <input type="hidden" name="country_code"> 
                        <input type="text" name="mobile" id="mobile" value="{{ old('mobile') }}" class="form--control checkUser" placeholder="@lang('Your Phone Number')">
                    </div>
                    <small class="text-danger mobileExist"></small>
                </div>
                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="password">@lang('Password')</label>
                    <div class="form-group">
                        <input 
                            id="password" 
                            type="password"
                            class="form--control"
                            name="password" 
                            placeholder="@lang('Enter password')"
                            required
                        >
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
                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="password-confirm">@lang('Confirm Password')</label>
                        <input id="password-confirm" type="password" class="form--control" placeholder="@lang('Confirm Password')" name="password_confirmation" required autocomplete="new-password">
                </div>

                <x-captcha></x-captcha>

                @if (gs('agree'))
                    <div class="form-group">
                        <div class="form-check d-flex align-items-center">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                name="agree"
                                id="termsAndConditions"
                                required
                                @checked(old('agree'))
                            >
                            <label class="form-check-label mb-0 ms-2" for="termsAndConditions">
                                @lang('I agree with')
                                @foreach ($policies as $policy)
                                    <a href="{{ route('policy.pages', $policy->slug) }}" target="_blank">
                                        {{ __($policy->data_values->title) }}
                                    </a>@if(!$loop->last), @endif
                                @endforeach
                            </label>
                        </div>
                    </div>
                @endif
                <div class="form-group col-xl-12">
                    <button type="submit" class="btn btn--base w-100">@lang('Register')</button>
                </div>
            </div>
        </form>
        <p class="font-size--14px text-center">@lang('Have an account?') <a href="{{route('merchant.login')}}">@lang('Login Here').</a></p>
      </div>
    </div>
    <div class="right bg_img" style="background-image: url('{{frontendImage('merchant_login',@$content->background_image,'768x1200')}}');">
    </div>
  </section>


<div class="modal fade" id="existModalCenter" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
            <div class="modal-body text-center">
                <i class="las la-exclamation-circle text-secondary display-2 mb-15"></i>
                <h6 class="text-center">@lang('You already have an account please Sign in ')</h6>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn--dark btn-sm" data-bs-dismiss="modal">@lang('Close')</button>
                <a href="{{ route('merchant.login') }}" class="btn btn--base btn-sm">@lang('Sign In')</a>
            </div>
      </div>
    </div>
</div>
@endsection

@push('style')
<style>
    .country-code .input-group-prepend .input-group-text{
        background: #fff !important;
    }
    .country-code select{
        border: none;
    }
    .country-code select:focus{
        border: none;
        outline: none;
    }
</style>
@endpush

@if(gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

@push('script')
    <script>
      "use strict";
        (function ($) {
            
            @if($mobileCode)
                $(`option[data-code={{ $mobileCode }}]`).attr('selected','');
            @endif
 
            $('select[name=country]').change(function(){
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
                $('.mobile-code').text('+'+$('select[name=country] :selected').data('mobile_code'));
            });
            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            $('.mobile-code').text('+'+$('select[name=country] :selected').data('mobile_code'));

            $('.checkUser').on('focusout',function(e){
                var url = '{{ route('merchant.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';

                if ($(this).attr('name') == 'mobile') {
                    var mobile = `${$('.mobile-code').text().substr(1)}${value}`;
                    var data = {mobile:mobile,_token:token} 
                }
                if ($(this).attr('name') == 'email') {
                    var data = {email:value,_token:token}
                }
                if ($(this).attr('name') == 'username') {
                    var data = {username:value,_token:token}
                }
                $.post(url,data,function(response) {  
                  if (response.data != false && response.type == 'email') {
                    $('#existModalCenter').modal('show');
                  }else if(response.data != false){ 
                    $(`.${response.type}Exist`).text(`${response.type} already exist`);
                  }else{
                    $(`.${response.type}Exist`).text('');
                  }
                });
            });

        })(jQuery);
    </script>
@endpush
