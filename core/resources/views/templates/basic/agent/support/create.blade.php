@extends($activeTemplate.'layouts.agent_master')
@section('content')
<div class="row mt-5 justify-content-center">
    <div class="col-xl-10">
        <div class="card box-shadow">
            <div class="card-header d-flex justify-content-between flex-wrap gap-2 align-items-center">
                <h6>@lang('Open New Ticket')</h6>
                <a href="{{ route('ticket.index') }}" class="btn btn-sm btn--base text-end">
                    <i class="las la-backward"></i>
                    @lang('Support Tickets')
                </a>
            </div>
            <div class="card-body"> 
                <form action="{{ route('ticket.store') }}" class="disableSubmission" method="post" enctype="multipart/form-data"
                    onsubmit="return submitUserForm();">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="name">@lang('Name')</label>
                            <input type="text" name="name" value="{{ @$user->firstname . ' ' . @$user->lastname }}"
                                class="form--control " placeholder="@lang('Enter your name')" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email">@lang('Email address')</label>
                            <input type="email" name="email" value="{{ @$user->email }}" class="form--control"
                                placeholder="@lang('Enter your email')" readonly>
                        </div> 
                        <div class="form-group col-md-6">
                            <label>@lang('Subject')</label>
                            <input type="text" name="subject" value="{{ old('subject') }}" class="form--control"
                                placeholder="@lang('Subject')">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="priority">@lang('Priority')</label>
                            <select name="priority" class="form--control select2" data-minimum-results-for-search="-1">
                                <option value="3">@lang('High')</option>
                                <option value="2">@lang('Medium')</option>
                                <option value="1">@lang('Low')</option>
                            </select>
                        </div>
                        <div class="col-12 form-group">
                            <label for="inputMessage">@lang('Message')</label>
                            <textarea name="message" id="inputMessage" rows="6" class="form--control" placeholder="@lang('Message')">{{ old('message') }}</textarea>
                        </div>

                        <div class="col-md-9">
                            <button type="button" class="btn btn-dark btn-sm addAttachment my-2"> <i class="fas fa-plus"></i> @lang('Add Attachment') </button>
                            <p class="mb-2"><span class="text--info">@lang('Max 5 files can be uploaded | Maximum upload size is '.convertToReadableSize(ini_get('upload_max_filesize')) .' | Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx')</span></p>
                            <div class="row fileUploadsContainer">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn--base w-100 my-2" type="submit"><i class="las la-paper-plane"></i> @lang('Submit')
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
    <style>
        .input-group-text:focus {
            box-shadow: none !important;
        }
    </style>
@endpush


@push('script')
    <script>
        (function ($) {
            "use strict";
            var fileAdded = 0;
            $('.addAttachment').on('click',function(){
                fileAdded++;
                if (fileAdded == 5) {
                    $(this).attr('disabled',true)
                }
                $(".fileUploadsContainer").append(`
                    <div class="col-lg-4 col-md-12 removeFileInput">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="file" name="attachments[]" class="form-control" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" required>
                                <button type="button" class="input-group-text removeFile text-white bg--danger border--danger"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                `)
            });
            $(document).on('click','.removeFile',function(){
                $('.addAttachment').removeAttr('disabled',true)
                fileAdded--;
                $(this).closest('.removeFileInput').remove();
            });
        })(jQuery);
    </script>
@endpush