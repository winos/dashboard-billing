@extends($activeTemplate.'layouts.merchant_master')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="custom--card">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <h6>@lang($pageTitle)</h6>
                </div>
            </div>
            <div class="table-responsive--sm">
                <form class="transaction-top-form mb-4"  method="GET">
                    <div class="custom-select-search-box">
                        <input type="text" name="search" class="form--control" value="{{ request()->search }}"
                            placeholder="@lang('Search by transactions')">
                        <button type="submit" class="search-box-btn">
                            <i class="las la-search"></i>
                        </button>
                    </div>
                </form>
                <table class="table custom--table">
                    <thead>
                        <tr>
                            <th>@lang('Gateway | Trx')</th>
                            <th>@lang('Initiated')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdraws as $withdraw)
                            <tr>
                                <td>
                                    <span class="fw-bold">
                                        <span class="text-primary">{{ __(@$withdraw->method->name) }}</span>
                                    </span>
                                    <br>
                                    <small>{{ $withdraw->trx }}</small>
                                </td>
                                <td>
                                    {{ showDateTime($withdraw->created_at) }} <br>
                                    {{ diffForHumans($withdraw->created_at) }}
                                </td>
                                <td>
                                    {{ __($withdraw->curr->currency_symbol) }}{{ showAmount($withdraw->amount, currencyFormat: false) }} - <span
                                        class="text-danger" title="@lang('charge')">{{ __($withdraw->curr->currency_symbol) }}{{ showAmount($withdraw->charge, currencyFormat: false) }}
                                    </span>
                                    <br>
                                    <strong title="@lang('Amount after charge')">
                                        {{ showAmount($withdraw->amount - $withdraw->charge, currencyFormat: false) }} {{ __($withdraw->curr->currency_code) }}
                                    </strong>
                                </td>
                                <td>
                                    @php echo $withdraw->statusBadge @endphp
                                </td>
                                <td>
                                    <button class="btn btn--dark btn-sm detailBtn"
                                        data-user_data="{{ json_encode($withdraw->withdraw_information) }}"
                                        @if ($withdraw->status == 3) 
                                            data-admin_feedback="{{ $withdraw->admin_feedback }}" 
                                        @endif
                                    >
                                        <i class="fa fa-desktop"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-muted text-center not-found" colspan="100%">{{ __($emptyMessage) }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($withdraws->hasPages())
                <div class="pt-4 pb-2">
                    {{ paginatelinks($withdraws) }}
                </div>
            @endif
        </div>
    </div><!-- custom--card end -->

    <div id="detailModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush userData mb-2">
                    </ul>
                    <div class="feedback"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
        $('.detailBtn').on('click', function() {
                var modal = $('#detailModal');
                var userData = $(this).data('user_data');
                var html = ``;
                userData.forEach(element => {
                    if (element.type != 'file') {
                        html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>${element.name}</span>
                            <span">${element.value}</span>
                        </li>`;
                    }
                });
                modal.find('.userData').html(html);

                if ($(this).data('admin_feedback') != undefined) {
                    var adminFeedback = `
                        <div class="my-3 ms-3">
                            <strong>@lang('Admin Feedback')</strong>
                            <p>${$(this).data('admin_feedback')}</p>
                        </div>
                    `;
                } else {
                    var adminFeedback = '';
                }

                modal.find('.feedback').html(adminFeedback);

                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
