@extends('admin.layouts.app')

@php
    $agent = $user;
@endphp

@section('panel')
    <div class="row">
        <div class="col-12">
            <div class="row gy-4">

                <div class="col-xxl-3 col-sm-6">
                    <div class="widget-two style--two box--shadow2 b-radius--5 bg--primary">
                        <div class="widget-two__icon b-radius--5 bg--primary">
                            <i class="las la-wallet"></i>
                        </div>
                        <div class="widget-two__content">
                            <h3 class="text-white">{{ showAmount($totalDeposit) }}</h3>
                            <p class="text-white">@lang('Deposits')</p>
                        </div>
                        <a href="{{ route('admin.deposit.list', $agent->id) }}?user_type=AGENT" class="widget-two__btn">@lang('View All')</a>
                    </div>
                </div>
                <!-- dashboard-w1 end -->

                <div class="col-xxl-3 col-sm-6">
                    <div class="widget-two style--two box--shadow2 b-radius--5 bg--1">
                        <div class="widget-two__icon b-radius--5 bg--primary">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="widget-two__content">
                            <h3 class="text-white">{{ showAmount($totalWithdrawals) }}</h3>
                            <p class="text-white">@lang('Withdrawals')</p>
                        </div>
                        <a href="{{ route('admin.withdraw.data.all', $agent->id) }}?user_type=AGENT" class="widget-two__btn">@lang('View All')</a>
                    </div>
                </div>
                <!-- dashboard-w1 end -->

                <div class="col-xxl-3 col-sm-6">
                    <div class="widget-two style--two box--shadow2 b-radius--5 bg--17">
                        <div class="widget-two__icon b-radius--5 bg--primary">
                            <i class="las la-exchange-alt"></i>
                        </div>
                        <div class="widget-two__content">
                            <h3 class="text-white">{{ $totalTransaction }}</h3>
                            <p class="text-white">@lang('Transactions')</p>
                        </div>
                        <a href="{{ route('admin.report.transaction', $agent->id) }}?user_type=AGENT" class="widget-two__btn">@lang('View All')</a>
                    </div>
                </div>
                <!-- dashboard-w1 end -->

                <div class="col-xxl-3 col-sm-6">
                    <div class="widget-two style--two box--shadow2 b-radius--5 bg--19">
                        <div class="widget-two__icon b-radius--5 bg--primary">
                            <i class="las la-money-bill-wave-alt"></i>
                        </div>
                        <div class="widget-two__content">
                            <h3 class="text-white">{{ showAmount($totalMoneyIn) }}</h3>
                            <p class="text-white">@lang('Total Money In')</p>
                        </div>
                        <a href="{{ route('admin.report.transaction', $agent->id) }}?user_type=AGENT&remark=money_in" class="widget-two__btn">@lang('View All')</a>
                    </div>
                </div>
                <!-- dashboard-w1 end -->

            </div>

            <div class="d-flex flex-wrap gap-3 mt-4">
                <div class="flex-fill">
                    <button data-bs-toggle="modal" data-bs-target="#addSubModal" class="btn btn--success btn--shadow w-100 btn-lg bal-btn" data-act="add">
                        <i class="las la-plus-circle"></i> @lang('Balance')
                    </button>
                </div>

                <div class="flex-fill">
                    <button data-bs-toggle="modal" data-bs-target="#addSubModal" class="btn btn--danger btn--shadow w-100 btn-lg bal-btn" data-act="sub">
                        <i class="las la-minus-circle"></i> @lang('Balance')
                    </button>
                </div>

                <div class="flex-fill">
                    <a href="{{ route('admin.report.login.history', ['user_type' => 'AGENT', 'search' => $agent->username]) }}" class="btn btn--primary btn--shadow w-100 btn-lg">
                        <i class="las la-list-alt"></i>@lang('Logins')
                    </a>
                </div>

                <div class="flex-fill">
                    <a href="{{ route('admin.agents.notification.log', $agent->id) }}" class="btn btn--secondary btn--shadow w-100 btn-lg">
                        <i class="las la-bell"></i>@lang('Notifications')
                    </a>
                </div>

                @if ($agent->kyc_data)
                    <div class="flex-fill">
                        <a href="{{ route('admin.agents.kyc.details', $agent->id) }}" target="_blank" class="btn btn--dark btn--shadow w-100 btn-lg">
                            <i class="las la-user-check"></i>@lang('KYC Data')
                        </a>
                    </div>
                @endif

                <div class="flex-fill">
                    @if ($agent->status == 1)
                        <button type="button" class="btn btn--warning btn--shadow w-100 btn-lg userStatus" data-bs-toggle="modal" data-bs-target="#userStatusModal">
                            <i class="las la-ban"></i>@lang('Ban Agent')
                        </button>
                    @else
                        <button type="button" class="btn btn--success btn--shadow w-100 btn-lg userStatus" data-bs-toggle="modal" data-bs-target="#userStatusModal">
                            <i class="las la-undo"></i>@lang('Unban Agent')
                        </button>
                    @endif
                </div>
            </div>


            <div class="row mt-30 g-4">
                <div class="col-lg-5 col-xxl-4 col-md-5 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">@lang('Wallets')</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @forelse ($wallets as $wallet)
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        @lang($wallet->currency->currency_code)
                                        <span class="font-weight-bold">{{ showAmount($wallet->balance, $wallet->currency, currencyFormat: false) }} {{ __($wallet->currency->currency_code) }}</span>
                                    </li>
                                @empty
                                    <li class="list-group-item d-flex justify-content-center align-items-center flex-wrap">
                                        @lang('No wallets found')
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-xxl-8 col-md-7 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">@lang('Information of') {{ $agent->fullname }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.agents.update', [$agent->id]) }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group ">
                                            <label>@lang('First Name')</label>
                                            <input class="form-control" type="text" name="firstname" required value="{{ $agent->firstname }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label">@lang('Last Name')</label>
                                            <input class="form-control" type="text" name="lastname" required value="{{ $agent->lastname }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Email') </label>
                                            <input class="form-control" type="email" name="email" value="{{ $agent->email }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Mobile Number') </label>
                                            <div class="input-group ">
                                                <span class="input-group-text mobile-code"></span>
                                                <input type="number" name="mobile" value="{{ old('mobile') }}" id="mobile" class="form-control checkUser" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="form-group ">
                                            <label>@lang('Address')</label>
                                            <input class="form-control" type="text" name="address" value="{{ @$agent->address->address }}">
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-md-6">
                                        <div class="form-group">
                                            <label>@lang('City')</label>
                                            <input class="form-control" type="text" name="city" value="{{ @$agent->address->city }}">
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-md-6">
                                        <div class="form-group ">
                                            <label>@lang('State')</label>
                                            <input class="form-control" type="text" name="state" value="{{ @$agent->address->state }}">
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-md-6">
                                        <div class="form-group ">
                                            <label>@lang('Zip/Postal')</label>
                                            <input class="form-control" type="text" name="zip" value="{{ @$agent->address->zip }}">
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-md-6">
                                        <div class="form-group ">
                                            <label>@lang('Country')</label>
                                            <select name="country" class="form-control select2">
                                                @foreach ($countries as $key => $country)
                                                    <option data-mobile_code="{{ $country->dial_code }}" value="{{ $key }}">{{ __($country->country) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                             
                                    <div class="form-group  col-xl-4 col-md-6 col-12">
                                        <label>@lang('Email Verification')</label>
                                        <input type="checkbox" data-width="100%" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Verified')" data-off="@lang('Unverified')" name="ev" @if ($agent->ev) checked @endif>

                                    </div>

                                    <div class="form-group  col-xl-4 col-md-6 col-12">
                                        <label>@lang('Mobile Verification')</label>
                                        <input type="checkbox" data-width="100%" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Verified')" data-off="@lang('Unverified')" name="sv" @if ($agent->sv) checked @endif>

                                    </div>
                                    <div class="form-group col-xl-4 col-md-4 col-12">
                                        <label>@lang('2FA Verification') </label>
                                        <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Enable')" data-off="@lang('Disable')" name="ts" @if ($agent->ts) checked @endif>
                                    </div>
                                    <div class="form-group col-xl-4 col-md-4 col-12">
                                        <label>@lang('KYC') </label>
                                        <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Verified')" data-off="@lang('Unverified')" name="kv" @if ($agent->kv == 1) checked @endif>
                                    </div>
                                </div>


                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>



    {{-- Add Sub Balance MODAL --}}
    <div id="addSubModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span class="type"></span> <span>@lang('Balance')</span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.agents.add.sub.balance', $agent->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="act">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Select Wallet')</label>
                            <select name="wallet_id" required class="form-control select2">
                                <option value="">@lang('Select One')</option>
                                @foreach ($wallets as $wallet)
                                    <option value="{{ $wallet->id }}">{{ $wallet->currency->currency_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('Amount')</label>
                            <div class="input-group">
                                <input type="number" step="any" name="amount" class="form-control" placeholder="@lang('Please provide positive amount')" required>
                                <div class="input-group-text">{{ __(gs('cur_text')) }}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Remark')</label>
                            <textarea class="form-control" placeholder="@lang('Remark')" name="remark" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary h-45 w-100">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="userStatusModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if ($agent->status == 1)
                            <span>@lang('Ban Agent')</span>
                        @else
                            <span>@lang('Unban Agent')</span>
                        @endif
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.agents.status', $agent->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if ($agent->status == 1)
                            <h6 class="mb-2">@lang('If you ban this agent he/she won\'t able to access his/her dashboard.')</h6>
                            <div class="form-group">
                                <label>@lang('Reason')</label>
                                <textarea class="form-control" name="reason" rows="4" required></textarea>
                            </div>
                        @else
                            <p><span>@lang('Ban reason was'):</span></p>
                            <p>{{ $agent->ban_reason }}</p>
                            <h4 class="text-center mt-3">@lang('Are you sure to unban this agent?')</h4>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if ($agent->status == 1)
                            <button type="submit" class="btn btn--primary h-45 w-100">@lang('Submit')</button>
                        @else
                            <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('No')</button>
                            <button type="submit" class="btn btn--primary">@lang('Yes')</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.agents.login', $agent->id) }}" target="_blank" class="btn btn-sm btn-outline--primary"><i class="las la-sign-in-alt"></i>@lang('Login as Agent')</a>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict"
            $('.bal-btn').click(function() {
                var act = $(this).data('act');
                $('#addSubModal').find('input[name=act]').val(act);
                if (act == 'add') {
                    $('.type').text('Add');
                } else {
                    $('.type').text('Subtract');
                }
            });
            let mobileElement = $('.mobile-code');
            $('select[name=country]').change(function() {
                mobileElement.text(`+${$('select[name=country] :selected').data('mobile_code')}`);
            });

            $('select[name=country]').val('{{ @$agent->country_code }}');
            let dialCode = $('select[name=country] :selected').data('mobile_code');
            let mobileNumber = `{{ $agent->mobile }}`;
            mobileNumber = mobileNumber.replace(dialCode, '');
            $('input[name=mobile]').val(mobileNumber);
            mobileElement.text(`+${dialCode}`);

        })(jQuery);
    </script>
@endpush
