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
                    <h5 class="pb-3 text-center border-bottom">@lang('Verify Email Address')</h5>
                    <form action="{{route('agent.verify.email')}}" method="POST" class="submit-form">
                        @csrf
                        <p class="verification-text mt-3">@lang('A 6 digit verification code sent to your email address'):  {{ showEmailAddress(agent()->email) }}</p>

                        @include($activeTemplate.'partials.verification_code')

                        <div class="mb-3">
                            <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                        </div>

                        <div class="mb-3">
                            <p>
                                @lang('If you don\'t get any code'), <span class="countdown-wrapper">@lang('try again after') <span id="countdown" class="fw-bold">--</span> @lang('seconds')</span> <a href="{{route('agent.send.verify.code', 'email')}}" class="try-again-link d-none"> @lang('Try again')</a>
                            </p>
                            <a href="{{ route('agent.logout') }}">@lang('Logout')</a>
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

@push('script')
    <script>
        var distance =Number("{{@$user->ver_code_send_at->addMinutes(2)->timestamp-time()}}");
        var x = setInterval(function() {
            distance--;
            document.getElementById("countdown").innerHTML = distance;
            if (distance <= 0) {
                clearInterval(x);
                document.querySelector('.countdown-wrapper').classList.add('d-none');
                document.querySelector('.try-again-link').classList.remove('d-none');
            }
        }, 1000);
    </script>
@endpush
