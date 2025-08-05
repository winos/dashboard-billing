@extends($activeTemplate . 'layouts.user_master')
@section('content')
    <div class="col-xl-10">
        <div class="card style--two">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-center">
                <div class="bank-icon has--plus me-2">
                    <i class="las la-university"></i>
                </div>
                <h4 class="fw-normal">{{ __($pageTitle) }}</h4>
            </div>
            <div class="card-body p-4">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <form action="{{ route('user.withdraw.method.add') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="d-widget mb-4">
                                <div class="d-widget__header">
                                    <h6 class="">@lang('Enter Details')</h4>
                                </div>
                                <div class="d-widget__content">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <label>@lang('Select Method')</label>
                                            <select class="select_method select select2" name="method_id" data-minimum-results-for-search="-1" required>
                                                <option value="">@lang('Select')</option>
                                                @foreach ($withdrawMethod as $method)
                                                    <option value="{{ $method->id }}" data-userdata='<x-viser-form identifier="id" identifierValue="{{ @$method->form_id }}" />' data-currencies="{{ $method->currency() }}" data-description="{{ $method->description }}">
                                                        @lang($method->name)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <label>@lang('Select Currency')</label>
                                            <select class="select currency select2" name="currency_id" required>
                                                <option value="">@lang('Select Currency')</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-widget">
                                <div class="d-widget__header">
                                    <h6 class="">@lang('Enter Details')</h4>
                                </div>
                                <div class="d-widget__content">
                                    <div class="description mb-2"></div>
                                    <div class="form-group">
                                        <label>@lang('Provide a nick name')<span class="text-danger">*</span> </label>
                                        <input class="form--control" type="text" name="name">
                                    </div>
                                    <div class="fields"></div>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-md btn--base mt-4 w-100">@lang('Add withdraw method')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';
        (function($) {
            $('.select_method').on('change', function() {
                var userData = $('.select_method option:selected').data('userdata')
                var description = $('.select_method option:selected').data('description')
                var currencies = $('.select_method option:selected').data('currencies')
                var options = `<option value="">@lang('Select Currency')</option>`
                $('.currency').children().remove();

                $('.fields').html(userData ?? null);
                $('.description').html(description ?? null);

                $.each(currencies, function(i, val) {
                    options += `<option value="${i}">${val}</option>`
                });
                $('.currency').append(options);

                let tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                let tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            })
        })(jQuery);
    </script>
@endpush
