@extends($activeTemplate.'layouts.common_auth')
@php
    $content = getContent('merchant_login.content',true)->data_values;
@endphp
@section('content')

<section class="account-section" >
    <div class="left">
        <div class="left-inner w-100">
          <div class="text-center">
              <a class="site-logo" href="{{route('home')}}"><img src="{{siteLogo('dark')}}" alt="@lang('logo')"></a>
              <p class="font-size--14px mt-1">@lang('Please set your new password')</p>
          </div>
          <form class="account-form mt-3" method="POST" action="{{ route('merchant.password.update') }}">
              @csrf
              <input type="hidden" name="email" value="{{ $email }}">
              <input type="hidden" name="token" value="{{ $token }}">
              <div class="form-group">
                  <label for="password">@lang('Password')</label>
                  <div class="form-group">
                      <input 
                          id="password" 
                          type="password"
                          class="form--control"
                          name="password" 
                          placeholder="@lang('Enter new password')"
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
              <div class="form-group">
                  <label for="password-confirm">@lang('Confirm Password')</label>
                  <input 
                      id="password-confirm" 
                      type="password" 
                      class="form--control"
                      name="password_confirmation" 
                      placeholder="@lang('Enter confirm password')"
                      required
                  >
              </div>
              <div class="form-group">
                  <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
              </div>
          </form>
        </div>
      </div>
    <div class="right bg_img" style="background-image: url('{{frontendImage('merchant_login',@$content->background_image,'1920x1280')}}');"></div>
</section>
@endsection

@if(gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif