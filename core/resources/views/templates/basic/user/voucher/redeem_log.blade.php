@extends($activeTemplate . 'layouts.user_master')

@section('content')
    <div class="custom--card">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-9">
                    <h6>@lang($pageTitle)</h6>
                </div>
                <div class="col-3 text-end">
                    <a href="{{ route('user.voucher.redeem') }}" class="btn btn--base btn-sm me-2 ">@lang('Back') </a>
                </div>
            </div>
            <div class="table-responsive--sm">
                <table class="table custom--table">
                    <thead>
                        <tr>
                            <th>@lang('Voucher Code')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Used At')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->voucher_code }}</td>
                                <td>{{ showAmount($log->amount, $log->currency, currencyFormat: false) }}
                                    {{ $log->currency->currency_code }}</td>
                                <td>{{ showDateTime($log->updated_at, 'd M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center not-found">@lang('No Log Found')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ paginateLinks($logs) }}
        </div>
    </div><!-- custom--card end -->
@endsection
