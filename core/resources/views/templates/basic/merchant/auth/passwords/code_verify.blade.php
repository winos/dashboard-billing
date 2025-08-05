@extends($activeTemplate.'layouts.common_auth')
@php
    $content = getContent('merchant_login.content',true)->data_values;
@endphp
@section('content')

<section class="verification-page account-section">
  <div class="left"> 
    <div class="left-inner w-100">
        <div class="text-center mb-5">
            <a class="site-logo" href="{{route('home')}}"><img src="{{siteLogo('dark')}}" alt="logo"></a>
        </div>
      <div class="d-flex justify-content-center">
          <div class="verification-code-wrapper">
              <div class="verification-area">
                  <h5 class="pb-3 text-center border-bottom">@lang('Verify Email Address')</h5>
                  <form action="{{ route('merchant.password.verify.code') }}" method="POST" class="submit-form">
                      @csrf
                      <input type="hidden" name="email" value="{{ $email }}">
                      <p class="verification-text mt-3">@lang('A 6 digit verification code sent to your email address'): {{ showEmailAddress($email) }} </p>

                      @include($activeTemplate.'partials.verification_code')

                      <div class="mb-3">
                          <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                      </div>

                      <div class="mb-3">
                          <p>
                              @lang('Please check including your Junk/Spam Folder. if not found, you can'), <a href="{{ route('merchant.password.request') }}"> @lang('Try to send again')</a>
                          </p>
                      </div>
                  </form>
              </div>
          </div>
      </div>
    </div>
</div>
  <div class="right bg_img" style="background-image: url('{{frontendImage('merchant_login',@$content->background_image,'1920x1280')}}');"></div>
</section>

@endsection
@push('script')
<script>
    (function($){
        "use strict";
        $('#code').on('input change', function () {
          var xx = document.getElementById('code').value;
          $(this).val(function (index, value) {
             value = value.substr(0,7);
              return value.replace(/\W/gi, '').replace(/(.{3})/g, '$1 ');
          });
      });
    })(jQuery)
</script>
@endpush