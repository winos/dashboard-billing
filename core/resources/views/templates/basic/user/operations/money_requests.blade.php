@extends($activeTemplate . 'layouts.user_master')

@section('content')
    <div class="custom--card">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <h6>@lang($pageTitle)</h6>
                </div>
                <div class="col-6 text-end">

                </div>
            </div>
            <div class="table-responsive--md">
                <table class="table custom--table">
                    <thead>
                        <tr>
                            <th>@lang('Request From')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Wallet Currency')</th>
                            <th>@lang('Sender Note')</th>
                            <th>@lang('Sent at')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            <tr>
                                <td>{{ $request->sender->fullname }}</td>
                                <td>
                                    {{ showAmount($request->request_amount, $request->currency, currencyFormat: false) }}
                                    {{ $request->currency->currency_code }}
                                </td>
                                <td>{{ $request->currency->currency_code }}</td>
                                <td><button class="btn--base btn-sm note"
                                        data-note="{{ $request->note }}">@lang('See')</button>
                                </td>
                                <td>
                                    {{ showDateTime($request->created_at, 'd M Y @g : i a') }}
                                </td>
                                <td>
                                    <a href="javascript:void(0)" class="btn btn-sm icon-btn btn--danger reject"
                                    data-id="{{ $request->id }}"><i class="las la-ban"></i></a>
                                    <a href="javascript:void(0)" class="btn btn-sm icon-btn btn--primary accept"
                                        data-id="{{ $request->id }}"
                                        data-amount="{{ getAmount($request->request_amount) }}"
                                        data-curr="{{ $request->currency->currency_code }}"
                                    >
                                        <i class="las la-check-double"></i>
                                    </a>
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
            @if($requests->hasPages())
                <div class="pt-4 pb-2">
                    {{ paginateLinks($requests) }}
                </div>
            @endif
        </div>


        {{-- Request confirm --}}
        <div class="modal fade" id="confirm" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered " role="document">
                <form action="{{ route('user.request.accept') }}" method="POST">
                    @csrf
                    <input type="hidden" name="request_id">
                    <div class="modal-content">

                        <div class="modal-body text-center p-4">
                            <i class="las la-exclamation-circle text-secondary display-2 mb-15"></i>
                            <p class="mb-15 warning"></p>
                            <h6 class="text--base mb-15">@lang('Are you sure want to confirm?')</h6>

                            @if (gs('otp_verification') && (gs('en') || gs('sn') || auth()->user()->ts))
                                <div class="form-group text-start mt-3">
                                    @include($activeTemplate . 'partials.otp_select')
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn--dark btn-sm"
                                data-bs-dismiss="modal">@lang('Close')</button>
                            <button type="submit" class="btn btn--base btn-sm del">@lang('Confirm')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Reject confirm --}}
        <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered " role="document">
                <form action="{{ route('user.request.reject') }}" method="POST">
                    @csrf
                    <input type="hidden" name="request_id">
                    <div class="modal-content">

                        <div class="modal-body text-center p-4">
                            <i class="las la-exclamation-circle text-danger display-2 mb-15"></i>
                            <h6 class="text--base mb-15">@lang('Are you sure want to reject?')</h6>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn--dark btn-sm"
                                data-bs-dismiss="modal">@lang('Close')</button>
                            <button type="submit" class="btn btn--danger btn-sm del">@lang('Reject')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        {{-- See note --}}
        <div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered " role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('Note')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div>
                            <textarea class="form--control" id="note" cols="30" disabled></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- custom--card end -->
@endsection

@push('style')
    <style>
        .select2-dropdown{
            z-index: 999999;
        }
    </style>
@endpush

@push('script')
    <script>
        'use strict';
        (function($) {

            $('.accept').on('click', function() {
                var id = $(this).data('id')
                var amount = $(this).data('amount')
                var curr = $(this).data('curr')
                $('#confirm').find('input[name=request_id]').val(id)
                $('#confirm').find('.warning').text(amount + ' ' + curr + ' will be reduced from your ' + curr +
                    ' wallet.')
                $('#confirm').modal('show')
            });

            $('.reject').on('click', function() {
                var id = $(this).data('id')
                $('#rejectModal').find('input[name=request_id]').val(id)
                $('#rejectModal').modal('show')
            });

            $('.note').on('click', function() {
                var note = $(this).data('note')
                $('#noteModal').find('#note').text(note)
                $('#noteModal').modal('show')
            });
            
        })(jQuery);
    </script>
@endpush
