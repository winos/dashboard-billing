@extends('admin.layouts.app')
@section('panel')
<div class="row justify-content-center">
    @if(gs('qr_code_template'))
        <div class="col-xl-5 form-group">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body p-0">
                    <div class="p-3 bg--white">
                        <div class="">
                            <img src="{{ getImage(getFilePath('qr_code_template') . '/' . gs('qr_code_template'), '2480x3508') }}" class="b-radius--10 w-100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="col-xl-{{ gs('qr_code_template') ? 4 : 6 }} form-group">
        <div class="custom">
            <div class="file_upload">
                <h5 class="text-center">@lang('Upload QR Code Template')</h5>
                <form class="form-data border-primary" enctype="multipart/form-data" id="form" method="post">
                    @csrf
                    <input class="file-input form-control" type="file" name="qr_code_template" id="file-input" hidden accept=".png, .jpg, .jpeg">
                    <i class="fas fa-cloud-upload-alt text--primary"></i>
                    <p class="text--dark">@lang('Click here to upload')</p>
                    <small class="text-center mb-2">(@lang('Supported files: jpeg, jpg., .png Image will be resized into') {{ getFileSize('qr_code_template') }})</small>
                </form>
                <section class="progress-area"></section> 
                <section class="uploaded-area"></section>
                <div class="form-group">
                    <button type="submit" class="btn btn--primary w-100 h-45 submitBtn">@lang('Submit')</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function ($) {
        "use strict";

        var fileType = null;
        var fileName = null;
        var form = $('.form-data'); 
        var uploadedArea = $('.uploaded-area');
        var body = document.querySelector('body');
        var fileInput = document.querySelector('.file-input');

        form.on('click', function(){
            fileInput.click(); 
        });

        $('.submitBtn').on('click', function(){
            form.submit();
        });

        form.on('change', function({ target }){
            upload(target.files);
        });

        function upload(file){

            fileName = file[0].name;
            fileType = fileName.split('.').pop().toLowerCase();

            if(fileName.length >= 12){  
                var splitName = fileName.split('.');
                fileName = splitName[0].substring(0, 15) + "...." + splitName[1];
            }

            var uploadedHTML = `
                        <li class="row">
                            <div class="content upload">
                                <i class="las la-image"></i>
                                <div class="details">
                                    <span class="name">${fileName}</span>
                                </div>
                            </div>
                        </li>`;
            uploadedArea.html(uploadedHTML);
        }
        
    })(jQuery);
</script>
@endpush

@push('style')
    <style>
    .custom .file_upload { 
        background: #fff;
        border-radius: 5px;
        padding: 20px;
        box-shadow: 0px 0px 7px #b2d4d1;
    }

    .custom .file_upload header {
        font-size: 27px;
        font-weight: 600;
        text-align: center;
    }

    .custom .file_upload form {
        height: 167px;
        display: flex;
        cursor: pointer;
        margin: 30px 0;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        border-radius: 5px;
        border: 2px dashed black;
    }

    .custom form :where(i, p) {
        color: #6990F2;
    }

    .custom form i {
        font-size: 50px;
    }

    .custom form p {
        margin-top: 15px;
        font-size: 16px;
    }

    .custom section .row {
        margin-bottom: 10px;
        background: #E9F0FF;
        list-style: none;
        padding: 15px 20px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .custom section .row i {
        color: #6990F2;
        font-size: 30px;
    }

    .custom section .details span {
        font-size: 14px;
    }

    .custom .progress-area .row .content {
        width: 100%;
        margin-left: -10px;  
    }

    .custom .progress-area .details {
        display: flex;
        align-items: center;
        margin-bottom: 7px;
        justify-content: space-between;
    }

    .custom .progress-area .content .progress-bar {
        height: 6px;
        width: 100%;
        margin-bottom: 4px;
        background: #fff;
        border-radius: 30px;
    }

    .custom .content .progress-bar .progress {
        height: 100%;
        width: 0%;
        background: #6990F2;
        border-radius: inherit;
    }

    .custom .uploaded-area {
        max-height: 232px;
        overflow-y: scroll;
    }

    .custom .uploaded-area.onprogress {
        max-height: 150px;
    }

    .custom .uploaded-area::-webkit-scrollbar {
        width: 0px;
    }

    .custom .uploaded-area .row .content {
        display: flex;
        align-items: center;
        margin-left: 12px;
    }

    .custom .uploaded-area .row .details {
        display: flex;
        margin-left: 15px;
        flex-direction: column;
    }

    .custom .uploaded-area .row .details .size {
        color: #404040;
        font-size: 11px;
    }

    .custom .uploaded-area i.fa-check {
        font-size: 16px;
    }

    .custom .uploaded-area i.fa-times {
        font-size: 16px;
    }

    .custom .fa-check, .fa-times{
        margin-right: 20px;
    }

    .custom .form-control:focus, .form-control:active, .form-control:visited, .form-control:focus-within, input:focus, input:active, input:visited, input:focus-within{
        border-color: none;
        box-shadow: none;
    }

    .custom .fz-5{
        font-size: 5px !important;
    }

    .custom .c-pointer{
        cursor: pointer;
    }
</style> 
@endpush