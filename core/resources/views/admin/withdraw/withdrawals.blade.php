@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        @if(request()->routeIs('admin.withdraw.data.all') || request()->routeIs('admin.withdraw.method'))
            <div class="col-12">
                @include('admin.withdraw.widget')
            </div>
        @endif

        <div class="col-md-12">
            <div class="show-filter mb-3 text-end">
                <button type="button" class="btn btn-outline--primary showFilterBtn btn-sm"><i class="las la-filter"></i> @lang('Filter')</button>
            </div>
            <div class="card responsive-filter-card mb-4">
                <div class="card-body">
                    <form >
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

        <div class="col-lg-12">
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
                                @forelse($withdrawals as $withdraw)
                                    @php
                                        $details = $withdraw->withdraw_information != null ? json_encode($withdraw->withdraw_information) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="fw-bold"><a href="{{ appendQuery('method', @$withdraw->method->id) }}"> {{ __(@$withdraw->method->name) }}</a></span>
                                            <br>
                                            <small>{{ $withdraw->trx }}</small>
                                        </td>
                                        <td>
                                            {{ showDateTime($withdraw->created_at) }} <br> {{ diffForHumans($withdraw->created_at) }}
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ __($withdraw->user_type) }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $withdraw->getUser->fullname }}</span>
                                            <br>
                                            <span class="small"> <a href="{{ appendQuery('search', @$withdraw->getUser->username) }}"><span>@</span>{{ $withdraw->getUser->username }}</a> </span>
                                        </td>

                                        <td>
                                            {{ showAmount($withdraw->amount, currencyFormat:false) }} {{ __($withdraw->curr->currency_code) }} - <span class="text--danger" title="@lang('charge')">{{ showAmount($withdraw->charge, currencyFormat:false) }} {{ __($withdraw->curr->currency_code) }} </span>
                                            <br>
                                            <strong title="@lang('Amount after charge')">
                                                {{ showAmount($withdraw->amount - $withdraw->charge, currencyFormat:false) }} {{ __($withdraw->curr->currency_code) }}
                                            </strong>
                                        </td>

                                        <td>
                                            @php echo $withdraw->statusBadge @endphp
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.withdraw.data.details', $withdraw->id) }}" class="btn btn-sm btn-outline--primary ms-1">
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
                @if ($withdrawals->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($withdrawals) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form dateSearch='yes' placeholder='Username / TRX' />
@endpush
