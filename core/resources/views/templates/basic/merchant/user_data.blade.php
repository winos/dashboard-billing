@extends($activeTemplate . 'layouts.frontend')

@section('content')
    <div class="pt-50 pb-50">
        <div class="container">
            <div class="col-md-12">
                <div class="card style--two">
                    <div class="card-body p-4">
                        <div class="row justify-content-center">
                            <div class="col-lg-10">
                                <form class="register prevent-double-click" action="{{ route('merchant.data.submit') }}"
                                    method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="form-group col-sm-6">
                                            <label for="InputFirstname" class="col-form-label">@lang('First Name')</label>
                                            <input type="text" class="form--control" id="InputFirstname" name="firstname"
                                                placeholder="@lang('First Name')" value="{{ old('firstname') }}" required>
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label for="lastname" class="col-form-label">@lang('Last Name')</label>
                                            <input type="text" class="form--control" id="lastname" name="lastname"
                                                placeholder="@lang('Last Name')" value="{{ old('lastname') }}" required>
                                        </div>
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
