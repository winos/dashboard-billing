@extends($activeTemplate .'layouts.common_auth')

@php
    $content = getContent('agent_login.content',true)->data_values;
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
                    <h5 class="pb-3 text-center border-bottom">@lang('2FA Verification')</h5>
                    <form action="{{route('agent.2fa.verify')}}" method="POST" class="submit-form">
                        @csrf

                        @include($activeTemplate.'partials.verification_code')

                        <div class="mb-3">
                            <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
      </div>
  </div>
  <div class="right bg_img" style="background-image: url('{{frontendImage('agent_login', @$content->background_image,'1920x1280')}}');">
  </div>
</section>
@endsection
