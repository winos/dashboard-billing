@extends('admin.layouts.app')

@section('panel')
    <div class="row">

        @if (request()->routeIs('admin.report.login.history'))
            <div class="col-md-12">
                <div class="show-filter mb-3 text-end">
                    <button type="button" class="btn btn-outline--primary showFilterBtn btn-sm"><i class="las la-filter"></i> @lang('Filter')</button>
                </div>
                <div class="card responsive-filter-card mb-4">
                    <div class="card-body">
                        <form >
                            <div class="d-flex flex-wrap gap-4">
                                <div class="flex-grow-1">
                                    <label>@lang('Username')</label>
                                    <input type="text" name="search" value="{{ request()->search }}" class="form-control">
                                </div>
                                <div class="flex-grow-1">
                                    <label>@lang('User Type')</label>
                                    <x-select-user-type strUpper="{{ false }}" title="All" />
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
        @endif

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">

                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    <th>@lang('User Type')</th>
                                    <th>@lang('Login at')</th>
                                    <th>@lang('IP')</th>
                                    <th>@lang('Location')</th>
                                    <th>@lang('Browser | OS')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loginLogs as $log)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">
                                                {{ @$log->showUser->fullname }}
                                            </span>
                                            <br>
                                            <span class="small">
                                                @php echo $log->goToUserProfile; @endphp
                                            </span>
                                        </td>
                                        <td>
                                            @php echo $log->userType; @endphp
                                        </td>
                                        <td>
                                            {{ showDateTime($log->created_at) }} <br> {{ diffForHumans($log->created_at) }}
                                        </td>
                                        <td>
                                            <span class="fw-bold">
                                                <a href="{{ route('admin.report.login.ipHistory', [$log->user_ip]) }}">{{ $log->user_ip }}</a>
                                            </span>
                                        </td>
                                        <td>{{ __($log->city) }} <br> {{ __($log->country) }}</td>
                                        <td>
                                            {{ __($log->browser) }} <br> {{ __($log->os) }}
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
                @if ($loginLogs->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($loginLogs) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
@endsection


@if (request()->routeIs('admin.report.login.ipHistory'))
    @push('breadcrumb-plugins')
        <a href="https://www.ip2location.com/{{ $ip }}" target="_blank" class="btn btn-outline--primary">@lang('Lookup IP') {{ $ip }}</a>
    @endpush
@endif
