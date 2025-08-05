@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        @if (request()->routeIs('admin.deposit.list') || request()->routeIs('admin.deposit.method'))
            <div class="col-12">
                @include('admin.deposit.widget')
            </div>
        @endif

        <div class="col-md-12">
            <div class="show-filter mb-3 text-end">
                <button type="button" class="btn btn-outline--primary showFilterBtn btn-sm"><i class="las la-filter"></i> @lang('Filter')</button>
            </div>
            <div class="card responsive-filter-card mb-4">
                <div class="card-body">
                    <form>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="flex-grow-1">
                                <label>@lang('TRX/Username')</label>
                                <input type="text" name="search" value="{{ request()->search }}" class="form-control">
                            </div>
                            <div class="flex-grow-1">
                                <label>@lang('User Type')</label>
                                <x-select-user-type title="All" />
                            </div>
                            <div class="flex-grow-1">
                                <label>@lang('Currency')</label>
                                <x-select-currency title="All" />
                            </div>
                            <div class="flex-grow-1">
                                <label>@lang('Date')</label>
                                <x-search-date-field :icon="false" />
                            </div>
                            <div class="flex-grow-1 align-self-end">
                                <button class="btn btn--primary w-100 h-45"><i class="fas fa-filter"></i> @lang('Filter')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Gateway | Transaction')</th>
                                    <th>@lang('Initiated')</th>
                                    <th>@lang('User Type')</th>
                                    <th>@lang('User')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                    @php
                                        $details = $deposit->detail ? json_encode($deposit->detail) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="fw-bold">
                                                <a href="{{ appendQuery('method', $deposit->method_code < 5000 ? @$deposit->gateway->alias : $deposit->method_code) }}">
                                                    @if ($deposit->method_code < 5000)
                                                        {{ __(@$deposit->gateway->name) }}
                                                    @else
                                                        @lang('Google Pay')
                                                    @endif
                                                </a>
                                            </span>
                                            <br>
                                            <small> {{ $deposit->trx }} </small>
                                        </td>

                                        <td>
                                            {{ showDateTime($deposit->created_at) }}<br>{{ diffForHumans($deposit->created_at) }}
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ __($deposit->user_type) }}</span>
                                        </td> 
                                        <td>
                                            <span class="fw-bold">{{ @$deposit->getUser->fullname }}</span>
                                            <br>
                                            <span class="small">
                                                <a href="{{ appendQuery('search', @$deposit->getUser->username) }}"><span>@</span>{{ @$deposit->getUser->username }}</a>
                                            </span>
                                        </td>
                                        <td>
                                            {{ showAmount($deposit->amount, currencyFormat:false) }} {{ __($deposit->method_currency) }} + <span class="text--danger" title="@lang('charge')">{{ showAmount($deposit->charge, currencyFormat:false) }} {{ __($deposit->method_currency) }} </span>
                                            <br>
                                            <strong title="@lang('Amount with charge')">
                                                {{ showAmount($deposit->amount + $deposit->charge, currencyFormat:false) }} {{ __($deposit->method_currency) }}
                                            </strong>
                                        </td>
                                        <td>
                                            @php echo $deposit->statusBadge @endphp
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.deposit.details', $deposit->id) }}" class="btn btn-sm btn-outline--primary ms-1">
                                                <i class="la la-desktop"></i> @lang('Details')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($deposits->hasPages())
                    <div class="card-footer py-4">
                        @php echo paginateLinks($deposits) @endphp
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
@endsection
