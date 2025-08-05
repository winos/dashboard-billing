@extends($activeTemplate . 'layouts.frontend')

@php
    $content = @getContent('login.content', true)->data_values;
@endphp

@section('content')
    <section class="pt-100 pb-100 d-flex flex-wrap align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="account-wrapper">
                        <div 
                            class="left bg_img"
                            style="background-image: url('{{ frontendImage('login', @$content->background_image, '768x1200') }}');"
                        >
                        </div>
                        <div class="right">
                            <div class="inner">
                                <div class="text-center">
                                    <h2 class="title">{{ __($pageTitle) }}</h2>
                                    <p class="font-size--14px mt-1">@lang('Please set your new password')</p>
                                </div>
                                <form class="account-form mt-5" method="POST" action="{{ route('user.password.update') }}">
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
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('style')
<style>
    .account-wrapper{
        overflow: inherit;
    }
</style> 
@endpush

@if(gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif