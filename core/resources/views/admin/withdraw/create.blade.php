@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ route('admin.withdraw.method.store') }}" class="disableSubmission" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="payment-method-item">
                            <div class="gateway-body mb-4">
                                <div class="gateway-content">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>@lang('Name')</label>
                                                <input type="text" class="form-control" name="name" value="{{ old('name') }}" required/>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>@lang('Currencies')</label>
                                                <select name="currencies[]" class="form-control select2"  multiple="multiple" required>
                                                    @foreach($currencies as $curr)
                                                        <option value="{{ $curr->id }}">{{ __($curr->currency_code) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>@lang('Enable For')</label>
                                                <select name="user_guards[]" class="form-control select2"  multiple="multiple" required>
                                                    <option value="1">@lang('User')</option>
                                                    <option value="2">@lang('Agent')</option>
                                                    <option value="3">@lang('Merchant')</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="payment-method-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="card border border--primary mb-2">
                                            <h5 class="card-header bg--primary">@lang('Range')</h5>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>@lang('Minimum Amount')</label>
                                                    <div class="input-group">
                                                        <input type="number" step="any" class="form-control" name="min_limit" value="{{ old('min_limit') }}" required/>
                                                        <div class="input-group-text"> {{ __(gs('cur_text')) }} </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>@lang('Maximum Amount')</label>
                                                    <div class="input-group">
                                                        <input type="number" step="any" class="form-control" name="max_limit" value="{{ old('max_limit') }}" required/>
                                                        <div class="input-group-text"> {{ __(gs('cur_text')) }} </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card border border--primary">
                                            <h5 class="card-header bg--primary">@lang('Charge')</h5>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>@lang('Fixed Charge')</label>
                                                    <div class="input-group">
                                                        <input type="number" step="any" class="form-control" name="fixed_charge" value="{{ old('fixed_charge') }}" required/>
                                                        <div class="input-group-text"> {{ __(gs('cur_text')) }} </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>@lang('Percent Charge')</label>
                                                    <div class="input-group">
                                                        <input type="number" step="any" class="form-control" name="percent_charge" value="{{ old('percent_charge') }}" required>
                                                        <div class="input-group-text">%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="card border border--primary my-2">

                                            <h5 class="card-header bg--primary">@lang('Withdraw Instruction') </h5>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <textarea rows="5" class="form-control border-radius-5 nicEdit" name="instruction">{{ old('instruction') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="submitRequired bg--warning form-change-alert d-none mt-3"><i class="fas fa-exclamation-triangle"></i> @lang('You\'ve to click on the submit button to apply the changes')</div>
                                        <div class="card border border--primary mt-3">
                                            <div class="card-header bg--primary d-flex justify-content-between">
                                                <h5 class="text-white">@lang('User Data')</h5>
                                                <button type="button" class="btn btn-sm btn-outline-light float-end form-generate-btn"> <i class="la la-fw la-plus"></i>@lang('Add New')</button>
                                            </div>
                                            <div class="card-body">
                                                <x-generated-form />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div><!-- card end -->
        </div>
    </div>

<x-form-generator-modal />
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.withdraw.method.index') }}" />
@endpush

@push('style')
    <style>
        .gateway-content{
            width: 100%;
            padding-left: 0px;
        }
    </style>
@endpush

@push('script')
    <script>
        (function ($) {
            "use strict";
            $('input[name=currency]').on('input', function () {
                $('.currency_symbol').text($(this).val());
            });
            $('.addUserData').on('click', function () {
                var html = `
                    <div class="col-md-12 user-data">
                        <div class="form-group">
                            <div class="input-group mb-md-0 mb-4">
                                <div class="col-md-4">
                                    <input name="field_name[]" class="form-control" type="text" required>
                                </div>
                                <div class="col-md-3 mt-md-0 mt-2">
                                    <select name="type[]" class="form-control" required>
                                        <option value="text" > @lang('Input Text') </option>
                                        <option value="textarea" > @lang('Textarea') </option>
                                        <option value="file"> @lang('File') </option>
                                    </select>
                                </div>
                                <div class="col-md-3 mt-md-0 mt-2">
                                    <select name="validation[]"
                                            class="form-control" required>
                                        <option value="required"> @lang('Required') </option>
                                        <option value="nullable">  @lang('Optional') </option>
                                    </select>
                                </div>
                                <div class="col-md-2 mt-md-0 mt-2 text-end">
                                    <span class="input-group-btn">
                                        <button class="btn btn--danger btn-lg removeBtn w-100" type="button">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>`;

                $('.addedField').append(html);
            });

            $(document).on('click', '.removeBtn', function () {
                $(this).closest('.user-data').remove();
            });
            @if(old('currency'))
            $('input[name=currency]').trigger('input');
            @endif
        })(jQuery);

    </script>
@endpush


