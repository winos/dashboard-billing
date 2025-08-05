@extends($activeTemplate.'layouts.merchant_master')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-12">
        <div class="card style--two">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-center">
                <div class="bank-icon me-2">
                    <i class="las la-key"></i>
                </div>
                <h4 class="fw-normal">@lang('Change Password')</h4>
            </div>
            <div class="card-body p-4">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <form  method="post" class="register">
                            @csrf 
                            <div class="form-group">
                                <label for="password">@lang('Current Password')</label>
                                <input type="password" class="form--control" name="current_password" required autocomplete="current-password" placeholder="@lang('Current Password')">
                            </div>
                            <div class="form-group">
                                <label for="password">@lang('Password')</label>
                                <div class="form-group">
                                    <input id="password" 
                                        type="password" 
                                        class="form--control" 
                                        name="password" 
                                        required 
                                        autocomplete="current-password" 
                                        placeholder="@lang('New Password')"
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
                                <label for="confirm_password">@lang('Confirm Password')</label>
                                <input id="password_confirmation" type="password" class="form--control" name="password_confirmation" required autocomplete="current-password" placeholder="@lang('Confirm Password')">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="mt-4 btn btn--base w-100">@lang('Submit')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if(gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif