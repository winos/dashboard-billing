@extends($activeTemplate . 'layouts.user_master')
@section('content')
    <div class="col-xl-5 col-lg-6 mb-30">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-2 text-center">{{ @$user->fullname }}</h4>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-1">
                        <span class="fw--bold"><i class="las la-user base--color"></i> @lang('Username')</span> <span>{{ @$user->username }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-1">
                        <span class="fw--bold"><i class="las la-envelope base--color"></i> @lang('Email')</span> <span>[{{ $user->email }}]</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-1">
                        <span class="fw--bold"><i class="las la-phone base--color"></i> @lang('Mobile')</span> <span>[{{ $user->mobile }}]</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-1">
                        <span class="fw--bold"><i class="las la-globe base--color"></i> @lang('Country')</span> <span>{{ @$user->country_name }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-7 col-lg-6">
        <div class="card style--two">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-center">
                <h5 class="card-title">@lang('Profile Setting')</h5>
            </div>
            <div class="card-body p-4">
                <form class="register prevent-double-click" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="InputFirstname" class="col-form-label">@lang('First Name')</label>
                                <input type="text" class="form--control" id="InputFirstname" name="firstname" placeholder="@lang('First Name')" value="{{ $user->firstname }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastname" class="col-form-label">@lang('Last Name')</label>
                                <input type="text" class="form--control" id="lastname" name="lastname" placeholder="@lang('Last Name')" value="{{ $user->lastname }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address" class="col-form-label">@lang('Address')</label>
                                <input type="text" class="form--control" id="address" name="address" placeholder="@lang('Address')" value="{{ @$user->address }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state" class="col-form-label">@lang('State')</label>
                                <input type="text" class="form--control" id="state" name="state" placeholder="@lang('state')" value="{{ @$user->state }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zip" class="col-form-label">@lang('Zip Code')</label>
                                <input type="text" class="form--control" id="zip" name="zip" placeholder="@lang('Zip Code')" value="{{ @$user->zip }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city" class="col-form-label">@lang('City')</label>
                                <input type="text" class="form--control" id="city" name="city" placeholder="@lang('City')" value="{{ @$user->city }}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">@lang('Profile Image')</label>
                                <input class="form-control form-control-lg" type="file" name="image">
                                <pre class="text--base mt-1">@lang('Image size') {{ @getFileSize('userProfile') }}</pre>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group row mt-2">
                                <div class="col-sm-12 text-center">
                                    <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .card {
            box-shadow: 1px 1px 9px #00000012;
        }

        .card-body {
            background-color: #fff;
        }

        .card.style--two .card-header {
            background-color: white;
        }

        .profile-disabled {
            border-left: 1px solid #dddddddb;
        }

        .list-group-item+.list-group-item span.fw-bold {
            font-size: 14px;
        }
    </style>
@endpush
