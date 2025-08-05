@extends($activeTemplate . 'layouts.user_master')

@section('content')
    <div class="col-xl-10">
        <div class="card style--two">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div class="bank-icon  me-2 mb-2">
                    <h4 class="fw-normal">@lang($pageTitle)</h4>
                </div>
                <div class="form-group">
                    <a href="{{ route('user.voucher.redeem.log') }}" class="btn btn--base btn-sm me-2 "> <i class="lab la-telegram"></i> @lang('Redeem Log') </a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <form  method="POST" id="form">
                            @csrf
                            <div class="d-widget">
                                <div class="d-widget__header">
                                    <h6>@lang('Provide Vocuher Code')</h4>
                                </div>
                                <div class="d-widget__content px-5">
                                    <div class="p-4 border mb-4">
                                        <div class="row">
                                            <div class="col-lg-12 form-group">
                                                <label class="mb-0">@lang('Voucher Code')<span class="text--danger">*</span>
                                                </label>
                                                <input type="text" class="form--control style--two code" name="code"
                                                    placeholder="@lang('Place voucher code here')" required>
                                            </div>
                                        </div><!-- row end -->
                                    </div>

                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-md btn--base mt-4 req_confirm w-100">@lang('Redeem')
                                </button>
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
            $('.req_confirm').on('click', function() {
                if ($('.code').val() == ''){
                    notify('error', 'Provide the voucher code first')
                    return false;
                }
                $('#form').submit()
                $(this).attr('disabled', true)
            })

            var old = @json(session()->getOldInput());
            if(old.length != 0){
                $('input[name=code]').val(old.code);
            }

        })(jQuery);
    </script>
@endpush
