@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    <th>@lang('Trx')</th>
                                    <th>@lang('Transacted')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Currency')</th>
                                    <th>@lang('Remark')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">
                                                @php echo @$log->showUserType; @endphp
                                            </span>
                                            <br>
                                            <span class="small">
                                                @php echo @$log->goToUserProfile; @endphp
                                            </span>
                                        </td>

                                        <td>
                                            <span class="fw-bold">{{ $log->trx }}</span>
                                        </td>
                                        <td>
                                            {{ showDateTime($log->created_at) }}<br>{{ diffForHumans($log->created_at) }}
                                        </td>
                                        <td>
                                            <strong class=" {{ $log->remark != null ? 'text--danger' : 'text--success' }}">
                                                {{ $log->currency->currency_symbol }} {{ showAmount($log->amount, currencyFormat:false) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <strong>{{ $log->currency->currency_code }}</strong>
                                        </td>
                                        <td>
                                            <strong class="text--primary">
                                                {{ ucwords(str_replace('_', ' ', $log->remark)) }}
                                            </strong> <br>
                                            <span class="{{ $log->remark ? 'text--danger' : 'text--success' }} ">
                                                {{ $log->remark ? str_replace('_', ' ', $log->remark) : 'Profit' }}
                                            </span>
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
                <div class="card-footer d-flex flex-wrap justify-content-between py-4">
                    <div>
                        @if (request()->routeIs('admin.profit.all'))
                            <strong class="text--{{ $totalProfit >= 0 ? 'success' : 'danger' }} mb-4 mb-md-0">
                                <span class="text--dark">
                                    @if ($totalProfit >= 0)
                                        @lang('Total Profit'):
                                    @else
                                        @lang('Total Loss'):
                                    @endif
                                </span>
                                {{ showAmount($totalProfit) }}
                                <span class="text--{{ $totalProfit >= 0 ? 'success' : 'danger' }}">*</span>
                            </strong>
                        @endif
                    </div>
                    {{ paginateLinks($logs) }}
                </div>
            </div><!-- card end -->
        </div>
    </div>
@endsection

@php
    $scope = null;
    if (request()->routeIs('admin.profit.all')) {
        $scope = 'all';
    } elseif (request()->routeIs('admin.profit.only')) {
        $scope = 'profit';
    } elseif (request()->routeIs('admin.profit.commission')) {
        $scope = 'commission';
    }
@endphp

@push('breadcrumb-plugins')
    <div class="profit-breadcrumb">
        <form class="row g-2 form">
            <div class="col-md-5 order-md-3">
                <x-search-date-field />
            </div>
            <div class="col-6 col-md-4 order-md-2">
                <select class="form-control select2" name="currency_code">
                    <option value="">@lang('Select Currency')</option>
                    @foreach ($currencies as $item)
                        <option value="{{ $item->currency_code }}" @selected(request()->currency_code == $item->currency_code)>{{ $item->currency_code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 order-md-1">
                <div class="input-group">
                    <a href="{{ route('admin.profit.export.csv', ['scope' => $scope, request()->getQueryString()]) }}"
                        class="btn btn-outline--primary h-45 w-100">
                        <i class="las la-file-csv"></i>@lang('Export CSV')
                    </a>
                </div>
            </div>
        </form>
    </div>
@endpush

@push('style')
    <style>
        @media screen and (min-width: 1600px) {
            .profit-breadcrumb {
                max-width: 768px;
                margin-left: auto;
                width: 100%;
            }
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('select[name=currency_code]').val('{{ request()->currency_code }}');
            $('select[name=currency_code]').on('change', function() {
                $('.form').submit();
            });
        })(jQuery)
    </script>
@endpush
