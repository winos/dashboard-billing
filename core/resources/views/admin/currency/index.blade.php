@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th scope="col">@lang('Currency Name | Code')</th>
                                    <th scope="col">@lang('Currency Symbol')</th>
                                    <th scope="col">@lang('Currency')</th>
                                    <th scope="col">@lang('Default')</th>
                                    <th scope="col">@lang('Status')</th>
                                    <th scope="col">@lang('Action')</th>
                                </tr>
                            </thead> 
                            <tbody> 
                                @forelse($currencies as $currency)
                                    <tr class="{{$currency->is_default == 1 ? 'bg--active': null }}">
                                        <td>
                                            <span class="fw-bold">{{ $currency->currency_fullname }}</span>
                                            <br>
                                            <span class="small">
                                                {{ $currency->currency_code }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $currency->currency_symbol }}</span>
                                        </td>
                                        <td>
                                            @php echo $currency->showCurrencyType; @endphp
                                            <br>1 {{ $currency->currency_code }} = {{ number_format($currency->rate, 8) }}
                                            {{ defaultCurrency() }}
                                        </td>
                                        <td>
                                            @php echo $currency->showDefaultBadge; @endphp
                                        </td>
                                        <td>
                                            @php echo $currency->showStatusBadge; @endphp
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline--primary ms-1 editBtn"
                                                data-currency="{{ $currency }}">
                                                <i class="la la-pen"></i> @lang('Edit')
                                            </button>
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
                @if ($currencies->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($currencies) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- NEW MODAL --}}
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="createModalLabel"> @lang('Add New Currency')</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i
                            class="las la-times"></i></button>
                </div>
                <form class="form-horizontal" method="post" action="{{ route('admin.currency.add') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row form-group">
                            <label>@lang('Currency Name')</label>
                            <div class="col-sm-12">
                                <input class="form-control" type="text" name="currency_fullname" required
                                    value="{{ old('currency_fullname') }}">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label>@lang('Currency Code')</label>
                            <div class="col-sm-12">
                                <input class="form-control" type="text" name="currency_code" required
                                    value="{{ old('currency_code') }}">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label>@lang('Currency Symbol')</label>
                            <div class="col-sm-12">
                                <input class="form-control" type="text" name="currency_symbol" required
                                    value="{{ old('currency_symbol') }}">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label>@lang('Currency Rate')</label>
                            <div class="col-sm-12">
                                <div class="input-group has_append">
                                    <div class="input-group-text cur_code"></div>
                                    <input type="number" step="any" class="form-control" name="rate"
                                        value="{{ old('rate') }}" />
                                    <div class="input-group-text">{{ gs('cur_text') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label>@lang('Currency Type')</label>
                            <div class="col-sm-12">
                                <select class="form-control select2" name="currency_type" data-minimum-results-for-search="-1" required>
                                    <option value="">@lang('Select One')</option>
                                    <option value="1">@lang('FIAT')</option>
                                    <option value="2">@lang('CRYPTO')</option>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-md-12">
                                <label>@lang('Default Currency') </label>
                                <input type="checkbox" data-width="100%" data-height="40px" data-onstyle="-success"
                                    data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('SET')"
                                    data-off="@lang('UNSET')" name="is_default">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45" id="btn-save"
                            value="add">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="createModalLabel"> @lang('Update Currency')</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i
                            class="las la-times"></i></button>
                </div>
                <form class="form-horizontal" method="post" action="{{ route('admin.currency.update') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="currency_id">
                        <div class="row form-group">
                            <label>@lang('Currency Name')</label>
                            <div class="col-sm-12">
                                <input class="form-control" type="text" name="currency_fullname" required
                                    value="{{ old('currency_fullname') }}">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label>@lang('Currency Code')</label>
                            <div class="col-sm-12">
                                <input class="form-control" type="text" name="currency_code" required
                                    value="{{ old('currency_code') }}">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label>@lang('Currency Symbol')</label>
                            <div class="col-sm-12">
                                <input class="form-control" type="text" name="currency_symbol" required
                                    value="{{ old('currency_symbol') }}">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label>@lang('Currency Rate')</label>
                            <div class="col-sm-12">
                                <div class="input-group has_append">
                                    <div class="input-group-text cur_code cur_code_edit"></div>
                                    <input type="number" step="any" class="form-control" name="rate"
                                        value="{{ old('rate') }}" />
                                    <div class="input-group-text">{{ gs('cur_text') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label>@lang('Currency Type')</label>
                            <div class="col-sm-12">
                                <select class="form-control select2" name="currency_type" data-minimum-results-for-search="-1" required>
                                    <option value="">@lang('Select One')</option>
                                    <option value="1">@lang('FIAT')</option>
                                    <option value="2">@lang('CRYPTO')</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row form-group">
                                    <div class="col-md-12">
                                        <label>@lang('Default Currency') </label>
                                        <input type="checkbox" data-width="100%" data-height="40px"
                                            data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle"
                                            data-on="@lang('Set')" data-off="@lang('Unset')" name="is_default">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row form-group">
                                    <div class="col-md-12">
                                        <label>@lang('Status') </label>
                                        <input type="checkbox" data-width="100%" data-height="40px"
                                            data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle"
                                            data-on="@lang('Enable')" data-off="@lang('Disable')" name="status">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45" id="btn-save"
                            value="add">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- CURRENCY MODAL --}}
    <div class="modal fade" id="currencyApiModal" tabindex="-1" aria-labelledby="currencyApiModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="currencyApiModalLabel">@lang('Currency Api Key')</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i></button>
                </div>
                <form action="{{ route('admin.currency.api.update') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row form-group">
                            <div class="justify-content-between d-flex flex-wrap">
                                <label>@lang('Fiat Currency Rate Api Key')</label>
                                <div>
                                    <small>@lang('For the api key') : </small> 
                                    <u><a target="_blank" class="text--primary" href="https://currencylayer.com/">@lang('Currency Layer')</a></u>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <input class="form-control" type="text" name="fiat_api_key" required value="{{gs('fiat_currency_api')}}">
                            </div>
                           
                        </div>
                        <div class="row form-group">
                            <div class="justify-content-between d-flex flex-wrap">
                                <label>@lang('Crypto Currency Rate Api Key')</label>
                                <div>
                                    <small>@lang('For the api key') : </small> 
                                    <u><a target="_blank" class="text--primary" href="https://coinmarketcap.com/">@lang('CoinMarketCap')</a></u>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <input class="form-control" type="text" name="crypto_api_key" required value="{{gs('crypto_currency_api')}}">
                            </div>
                           
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45" id="btn-save"
                            value="add">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <div class="d-flex flex-wrap justify-content-end">
        <button class="btn btn-outline--dark h-45 me-2 mb-2" data-bs-toggle="modal" data-bs-target="#currencyApiModal">
            <i class="las la-key"></i>@lang('Currency Api Key')
        </button>
        <button class="btn btn-outline--primary h-45 me-2 mb-2" data-bs-toggle="modal" data-bs-target="#createModal"><i
                class="las la-plus"></i>@lang('Add New')</button>
        <div class="d-inline">
            <x-search-form placeholder="Currency Name / Code" />
        </div>
    </div>
@endpush

@push('script')
    <script>
        'use strict';
        (function($) {

            $('.editBtn').on('click', function() {

                var modal = $('#editModal').modal('show')
                var currency = $(this).data('currency')

                modal.find('input[name=currency_id]').val(currency.id)
                modal.find('input[name=currency_fullname]').val(currency.currency_fullname)
                modal.find('input[name=currency_code]').val(currency.currency_code)
                modal.find('.cur_code_edit').text(1 + ' ' + currency.currency_code + ' =')
                modal.find('input[name=currency_symbol]').val(currency.currency_symbol)
                modal.find('input[name=rate]').val(currency.rate)
                modal.find('select[name=currency_type]').val(currency.currency_type).trigger('change');

                if (currency.is_default == 1) {
                    modal.find('input[name=is_default]').bootstrapToggle('on')
                } else {
                    modal.find('input[name=is_default]').bootstrapToggle('off')
                }

                if (currency.status == 1) {
                    modal.find('input[name=status]').bootstrapToggle('on')
                } else {
                    modal.find('input[name=status]').bootstrapToggle('off')
                }

                modal.modal('show')
            })

            $('input[name=currency_code]').on('input', function() {
                var code = $(this).val().toUpperCase()
                $('.cur_code').text(1 + ' ' + code + ' =')
            })

            $('#currency_code').on('input', function() {
                var code = $(this).val().toUpperCase()
                $('.cur_code_edit').text(1 + ' ' + code + ' =')
            })

            $('.copyCronLink').on('click', function(){
   
                var copyText = document.createElement("input");
                copyText.value = $('.'+$(this).data('class')).text();

                copyText.select();
                copyText.setSelectionRange(0, 99999); 

                navigator.clipboard.writeText(copyText.value);
                notify('success', copyText.value);
            });

        })(jQuery);
    </script>
@endpush
