@extends($activeTemplate . 'layouts.user_master')

@section('content')
    <div class="custom--card p-0">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-6">
                    <h6>@lang($pageTitle)</h6>
                </div>
                <div class="col-6 text-end">
                    <a class="btn btn--base btn-sm" href="{{ route('user.voucher.create') }}"> <i class="las la-plus"></i> @lang('Create New')</a>
                </div>
            </div>
            <div class="table-responsive--sm">
                <table class="table custom--table">
                    <thead>
                        <tr>
                            <th>@lang('Voucher Code')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Use Status')</th>
                            <th>@lang('Created at')</th>
                            <th>@lang('Used at')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vouchers as $voucher)
                            <tr>
                                <td><span class="fw-bold">{{ $voucher->voucher_code }}</span></td>
                                <td>{{ showAmount($voucher->amount, $voucher->currency, currencyFormat: false) }}
                                    {{ $voucher->currency->currency_code }}</td> 
                                <td>
                                   @php echo $voucher->showUsedBadge; @endphp
                                </td>
                                <td data-label="@lang('Created at')">{{ showDateTime($voucher->created_at, 'd M Y') }}</td>
                                <td>
                                   {{ $voucher->usedTime() }}  
                                </td>
                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="100%" class="text-center not-found">{{ __($emptyMessage) }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> 
            @if($vouchers->hasPages())
                <div class="card-footer bg-transparent pt-4 pb-2">
                    {{ paginatelinks($vouchers) }}
                </div>
            @endif
        </div>
    </div><!-- custom--card end -->
@endsection

