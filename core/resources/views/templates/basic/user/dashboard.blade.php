@extends($activeTemplate . 'layouts.user_master')
@section('content')
    @php
        $kyc = getContent('kyc.content', true);
    @endphp

    <div class="col-lg-9 mt-lg-0 mt-5">

        <div class="notice"></div>

        @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
            <div class='card mb-4'>
                <div class="d-user-notification d-flex flex-wrap align-items-center">
                    <div class="icon text--danger">
                        <i class="las la-times-circle"></i>
                    </div>
                    <div class="content">
                        <p class="text-white fw--bold">@lang('KYC Documents Rejected')</p>
                    </div>
                </div>
                <div class='card-body'>
                    <i>{{ __(@$kyc->data_values->reject) }}
                        <a href="javascript::void(0)" class="link-color" data-bs-toggle="modal" data-bs-target="#kycRejectionReason">@lang('Click here')</a> @lang('to show the reason').

                        <a href="{{ route('user.kyc.form') }}" class="link-color">@lang('Click Here')</a> @lang('to Re-submit Documents').
                        <a href="{{ route('user.kyc.data') }}" class="link-color">@lang('See KYC Data')</a>
                    </i>
                </div>
            </div>
        @elseif ($user->kv == Status::KYC_UNVERIFIED)
            <div class='card mb-4'>
                <div class="d-user-notification d-flex flex-wrap align-items-center">
                    <div class="icon text--info">
                        <i class="las la-exclamation-circle"></i>
                    </div>
                    <div class="content">
                        <p class="text-white fw--bold">@lang('KYC Verification Required')</p>
                    </div>
                </div>
                <div class='card-body'>
                    <i>{{ __(@$kyc->data_values->required) }} <a href="{{ route('user.kyc.form') }}" class="link-color">@lang('Click here')</a> @lang('to submit KYC information').</i>
                </div>
            </div>
        @elseif($user->kv == Status::KYC_PENDING)
            <div class='card mb-4'>
                <div class="d-user-notification d-flex flex-wrap align-items-center">
                    <div class="icon text--warning">
                        <i class="las la-hourglass-half"></i>
                    </div>
                    <div class="content">
                        <p class="text-white fw--bold">@lang('KYC Verification Pending')</p>
                    </div>
                </div>
                <div class='card-body'>
                    <i>{{ __(@$kyc->data_values->pending) }} <a href="{{ route('user.kyc.data') }}" class="link-color">@lang('Click here')</a> @lang('to see your submitted information')</i>
                </div>
            </div>
        @endif

        <div class="d-lg-flex d-none justify-content-between">
            <h6 class="mb-3">@lang('Wallets')</h6>
            <a href="{{ route('user.wallets') }}" class="font-size--14px text--base">@lang('More Wallets')
                <i class="las la-long-arrow-alt-right"></i>
            </a>
        </div>
        <div class="row mb-5 gy-4 d-lg-flex d-none">
            @foreach ($wallets as $wallet)
                <div class="col-lg-4 col-md-6">
                    <div class="d-widget curve--shape">
                        <div class="d-widget__content">
                            <i class="las la-wallet"></i>
                            <h2 class="d-widget__amount fw-normal amount__responsive">
                                {{ $wallet->currency->currency_symbol }}{{ showAmount($wallet->balance, $wallet->currency, currencyFormat: false) }}
                                {{ $wallet->currency->currency_code }}
                            </h2>
                        </div>
                        @if (module('transfer_money', $module)->status)
                            <div class="d-widget__footer d-flex flex-wrap justify-content-between">
                                <a href="{{ route('user.transfer', ['wallet' => $wallet->currency->currency_code]) }}" class="font-size--14px">
                                    @lang('Transfer Money')<i class="las la-long-arrow-alt-right"></i>
                                </a>
                            </div>
                        @endif
                    </div><!-- d-widget end -->
                </div>
            @endforeach
        </div><!-- row end -->

        <div class="custom--card mb-5 d-lg-block d-none">
            <div class="card-body">
                <div class="row align-items-center mb-3">
                    <div class="col-6">
                        <h6>@lang('Quick Links')</h6>
                    </div>
                </div>
                <div class="row justify-content-center gy-4">
                    @include($activeTemplate . 'user.partials.quick_links')
                </div><!-- row end -->
            </div>
        </div><!-- custom--card end -->

        <div class="card custom--card border-0">
            <div class="card-header border">
                <div class="row align-items-center">
                    <div class="col-8 py-3">
                        <h6>@lang('Latest Transactions')</h6>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="accordion table--acordion" id="transactionAccordion">
                    @forelse ($histories as $history)
                        <div class="accordion-item transaction-item {{ $history->trx_type == '-' ? 'sent-item' : 'rcv-item' }}">
                            <h2 class="accordion-header" id="h-{{ $loop->iteration }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c-{{ $loop->iteration }}" aria-expanded="false" aria-controls="c-1">
                                    <div class="col-lg-3 col-sm-4 col-6 order-1 icon-wrapper">
                                        <div class="left">
                                            <div class="icon">
                                                <i class="las la-long-arrow-alt-right"></i>
                                            </div>
                                            <div class="content">
                                                <h6 class="trans-title">
                                                    {{ __(ucwords(str_replace('_', ' ', $history->remark))) }}
                                                </h6>
                                                <span class="text-muted font-size--14px mt-2">{{ showDateTime($history->created_at, 'M d Y @g:i:a') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-5 col-12 order-sm-2 order-3 content-wrapper mt-sm-0 mt-3">
                                        <p class="text-muted font-size--14px">
                                            <b>{{ __($history->details) }} {{ $history->receiver ? @$history->receiver->username : '' }}</b>
                                        </p>
                                    </div>
                                    <div class="col-lg-3 col-sm-3 col-6 order-sm-3 order-2 text-end amount-wrapper">
                                        <p>
                                            <b>{{ showAmount($history->amount, $history->currency, currencyFormat:false) }} {{ $history->currency->currency_code }}</b>
                                        </p>
                                    </div>
                                </button>
                            </h2>
                            <div id="c-{{ $loop->iteration }}" class="accordion-collapse collapse" aria-labelledby="h-1" data-bs-parent="#transactionAccordion">
                                <div class="accordion-body">
                                    <ul class="caption-list">
                                        <li>
                                            <span class="caption">@lang('Transaction ID')</span>
                                            <span class="value">{{ $history->trx }}</span>
                                        </li>
                                        <li>
                                            <span class="caption">@lang('Wallet')</span>
                                            <span class="value">{{ $history->currency->currency_code }}</span>
                                        </li>

                                        @if ($history->charge > 0)
                                            <li>
                                                <span class="caption">@lang('Before Charge')</span>
                                                <span class="value">{{ showAmount($history->before_charge, $history->currency, currencyFormat: false) }}
                                                    {{ $history->currency->currency_code }}
                                                </span>
                                            </li>

                                            <li>
                                                <span class="caption">@lang('Charge')</span>
                                                <span class="value">{{ $history->charge_type }}{{ showAmount($history->charge, $history->currency, currencyFormat: false) }}
                                                    {{ $history->currency->currency_code }}
                                                </span>
                                            </li>
                                        @endif

                                        <li>
                                            <span class="caption">@lang('Transacted Amount')</span>
                                            <span class="value">{{ showAmount($history->amount, $history->currency, currencyFormat: false) }}
                                                {{ $history->currency->currency_code }}
                                            </span>
                                        </li>
                                        <li>
                                            <span class="caption">@lang('Remaining Balance')</span>
                                            <span class="value">{{ showAmount($history->post_balance, $history->currency, currencyFormat: false) }}
                                                {{ $history->currency->currency_code }}
                                            </span>

                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div><!-- transaction-item end -->
                    @empty
                        <div class="accordion-body text-center">
                            <h4 class="text--muted">{{ __($emptyMessage) }}</h4>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="reasonModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6>@lang('Reasons')</h6>
                </div>
                <div class="modal-body text-center my-4">
                    <p class="reason"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm" data-bs-dismiss="modal">
                        @lang('Close')
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
        <div class="modal fade" id="kycRejectionReason">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('KYC Document Rejection Reason')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ auth()->user()->kyc_rejection_reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script')
    <script>
        'use strict';
        (function($) {
            $('.reason').on('click', function() {
                $('#reasonModal').find('.reason').text($(this).data('reasons'))
                $('#reasonModal').modal('show')
            });
        })(jQuery);
    </script>
@endpush
