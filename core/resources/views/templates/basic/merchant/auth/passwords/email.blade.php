@extends($activeTemplate . 'layouts.common_auth')
@php
    $content = getContent('merchant_login.content', true)->data_values;
@endphp
@section('content')
    <section class="account-section">
        <div class="left">
            <div class="left-inner w-100">
                <div class="text-center">
                    <a class="site-logo" href="{{ route('home') }}"><img src="{{ siteLogo('dark') }}" alt="@lang('logo')"></a>
                    <p class="font-size--14px">@lang('Enter your email and we’ll help you create a new password.')</p>
                </div>
                <form class="account-form mt-3" method="POST" action="{{ route('merchant.password.email') }}">
                    @csrf
                    <div class="form-group">
                        <label class="my_value"></label>
                        <input type="text" class="form--control" name="value" value="{{ old('value') }}" autofocus="off" placeholder="@lang('Enter username or email address')" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="right bg_img" style="background-image: url('{{ frontendImage('merchant_login', @$content->background_image, '1920x1280') }}');">
        </div>
    </section>
@endsection
