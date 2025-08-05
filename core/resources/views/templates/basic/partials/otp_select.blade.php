<label class="mb-0">@lang('Select OTP Type')</label>
<select class="select style--two currency select2" name="otp_type" data-minimum-results-for-search="-1" required>
    <option value="" selected>@lang('Select OTP Type')</option>
    @if(gs('en'))
        <option value="email">@lang('Email')</option>
    @endif
    @if(gs('sn'))
        <option value="sms">@lang('SMS')</option>
    @endif
    @if (@userGuard()['user']->ts == 1)
        <option value="2fa">@lang('2FA')</option>
    @endif
</select>
 