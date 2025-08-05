@extends($activeTemplate.'layouts.merchant_master')
@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-12">
        <div class="card style--two">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-center">
                <div class="bank-icon  me-2">
                    <i class="las la-key"></i>
                </div>
                <h4 class="fw-normal">@lang('Business Api key')</h4>
            </div>
            <div class="card-body p-4">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <form action="{{route('merchant.generate.key')}}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="InputFirstname" class="col-form-label">@lang('Public Key'):</label>
                                    <div class="input-group">
                                        <input type="text" class="form--control" id="publicKey"  value="{{merchant()->public_api_key}}"readonly>
                                        <button type="button" class="input-group-text public">@lang('Copy')</button>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="lastname" class="col-form-label">@lang('Secret Key'):</label>
                                    <div class="input-group">
                                        <input type="text" class="form--control" id="secretKey" value="{{merchant()->secret_api_key}}" readonly>
                                        <button type="button" class="input-group-text secret">@lang('Copy')</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-5">
                                <div class="col-sm-12 text-center">
                                    <button type="button" class="btn btn--base w-100" data-bs-toggle="modal" data-bs-target="#confirm">@lang('Generate Api Key')</button>
                                </div>
                            </div>
                            <div class="modal fade" id="confirm" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-body text-center">
                                        <i class="las la-exclamation-circle text-danger display-2 mb-15"></i>
                                        <h4 class="text--secondary mb-1">@lang('Are sure want generate new api key?')</h4>
                                        <p>@lang('It may cause interrupt with your existing api request operations.')</p>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn--dark btn-sm" data-bs-dismiss="modal">@lang('Close')</button>
                                        <button type="submit"  class="btn btn--base btn-sm">@lang('Confirm')</button>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>
@endsection


@push('script')
    <script>
        (function($){
            "use strict";

            $('.public').on('click',function(){
                var copyText = document.getElementById("publicKey");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                document.execCommand("copy");
                notify('success', 'Copied: ' + copyText.value);
            });
            $('.secret').on('click',function(){
                var copyText = document.getElementById("secretKey");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                document.execCommand("copy");
                notify('success', 'Copied: ' + copyText.value);
            });
        })(jQuery);
    </script>
@endpush

