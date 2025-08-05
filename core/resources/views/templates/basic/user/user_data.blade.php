@extends($activeTemplate . 'layouts.frontend')

@section('content')
    <div class="pt-50 pb-50">
        <div class="container">
            <div class="col-md-12">
                <div class="card style--two">
                    <div class="card-body p-4">
                        <div class="row justify-content-center">
                            <div class="col-lg-10">
                                <form class="register prevent-double-click" action="{{ route('user.data.submit') }}"
                                    method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label for="username">@lang('Username')</label>
                                        <input id="username" type="text" class="form--control checkUser"
                                            placeholder="@lang('Username')" name="username" value="{{ old('username') }}"
                                            required>
                                        <small class="text-danger usernameExist"></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="country">@lang('Country')</label>
                                        <select name="country" id="country" class="form--control select2">
                                            @foreach ($countries as $key => $country)
                                                <option data-mobile_code="{{ $country->dial_code }}"
                                                    value="{{ $country->country }}" data-code="{{ $key }}">
                                                    {{ __($country->country) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="mobile">@lang('Mobile')</label>
                                        <div class="input-group">
                                            <span class="input-group-text mobile-code"></span>
                                            <input type="hidden" name="mobile_code">
                                            <input type="hidden" name="country_code">
                                            <input type="number" name="mobile" id="mobile" value="{{ old('mobile') }}"
                                                class="form--control checkUser" placeholder="@lang('Your phone number')">
                                        </div>
                                        <small class="text-danger mobileExist"></small>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-sm-6">
                                            <label for="address" class="col-form-label">@lang('Address')</label>
                                            <input type="text" class="form--control" id="address" name="address"
                                                placeholder="@lang('Address')" value="{{ old('address') }}">
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label for="state" class="col-form-label">@lang('State')</label>
                                            <input type="text" class="form--control" id="state" name="state"
                                                placeholder="@lang('state')" value="{{ old('state') }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-sm-6">
                                            <label for="zip" class="col-form-label">@lang('Zip Code')</label>
                                            <input type="text" class="form--control" id="zip" name="zip"
                                                placeholder="@lang('Zip Code')" value="{{ old('zip') }}">
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label for="city" class="col-form-label">@lang('City')</label>
                                            <input type="text" class="form--control" id="city" name="city"
                                                placeholder="@lang('City')" value="{{ old('city') }}">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <div class="col-sm-12 text-center">
                                            <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script>
        "use strict";
        (function($) {

            @if ($mobileCode)
                $(`option[data-code={{ $mobileCode }}]`).attr('selected', '');
            @endif

            $('.select2').select2();

            $('select[name=country]').on('change', function() {
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
                $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));
                var value = $('[name=mobile]').val();
                var name = 'mobile';
                checkUser(value, name);
            });

            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));


            $('.checkUser').on('focusout', function(e) {
                var value = $(this).val();
                var name = $(this).attr('name')
                checkUser(value, name);
            });

            function checkUser(value, name) {
                var url = '{{ route('user.checkUser') }}';
                var token = '{{ csrf_token() }}';

                if (name == 'mobile') {
                    var mobile = `${value}`;
                    var data = {
                        mobile: mobile,
                        mobile_code: $('.mobile-code').text().substr(1),
                        _token: token
                    }
                }
                if (name == 'username') {
                    var data = {
                        username: value,
                        _token: token
                    }
                }
                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $(`.${response.type}Exist`).text(`${response.field} already exist`);
                    } else {
                        $(`.${response.type}Exist`).text('');
                    }
                });
            }
        })(jQuery);
    </script>
@endpush
