@extends($activeTemplate.'layouts.merchant_master')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">                
                <div class="card-header card-header-bg d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="button-title-badge d-flex flex-wrap align-items-center gap-2">  
                        <div class="button-badge d-flex flex-wrap align-items-center justify-content-between">
                            @php echo $myTicket->statusBadge; @endphp
                            <div class="d-block d-sm-none">
                                @if ($myTicket->status != 3 && $myTicket->merchant)
                                    <button class="btn btn-danger close-button btn-sm confirmationBtn" type="button"
                                        data-question="@lang('Are you sure to close this ticket?')" data-action="{{ route('ticket.close', $myTicket->id) }}"><i
                                            class="las la-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <h5 class="card-title mt-0">
                            [@lang('Ticket')#{{ $myTicket->ticket }}] {{ $myTicket->subject }}
                        </h5> 
                    </div>
                    <div class="d-sm-block d-none">
                        @if ($myTicket->status != 3 && $myTicket->merchant)
                            <button class="btn btn-danger close-button btn-sm confirmationBtn" type="button"
                                data-question="@lang('Are you sure to close this ticket?')" data-action="{{ route('ticket.close', $myTicket->id) }}"><i
                                    class="las la-times"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if ($myTicket->status != 4)
                        <form method="post" class="disableSubmission" action="{{ route('ticket.reply', $myTicket->id) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="replayTicket" value="1">
                            <div class="row justify-content-between">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <textarea name="message" class="form-control form-control-lg" id="inputMessage" placeholder="@lang('Your Reply')" rows="4" cols="10">
                                        </textarea>
                                    </div>
                                </div>
                            </div>
                          
                            <div class="row justify-content-between">
                                <div class="col-md-9">
                                    <button type="button" class="btn btn-dark btn-sm addAttachment my-2"> <i class="fas fa-plus"></i> @lang('Add Attachment') </button>
                                    <p class="mb-2"><span class="text--info">@lang('Max 5 files can be uploaded | Maximum upload size is '.convertToReadableSize(ini_get('upload_max_filesize')) .' | Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx')</span></p>
                                    <div class="row fileUploadsContainer">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn--base w-100 my-2" type="submit"><i class="la la-fw la-lg la-reply"></i> @lang('Reply')
                                    </button>
                                </div>
                            </div>

                        </form>
                    @endif
                    <div class="row">
                        <div class="col-md-12 mt-4 mt-lg-0 mt-md-0">
                            <div class="card">
                                <div class="card-body">
                                    @foreach ($messages as $message)
                                        @if ($message->admin_id == 0)
                                            <div class="row border border-primary border-radius-3 my-3 py-3 mx-2">
                                                <div class="col-md-3 border-right text-right">
                                                    <h5 class="my-3">{{ $message->ticket->name }}</h5>
                                                </div>
                                                <div class="col-md-9">
                                                    <p class="text-muted font-weight-bold my-3">
                                                        @lang('Posted on')
                                                        {{ $message->created_at->format('l, dS F Y @ H:i') }}</p>
                                                    <p>{{ $message->message }}</p>
                                                    @if ($message->attachments()->count() > 0)
                                                        <div class="mt-2">
                                                            @foreach ($message->attachments as $k => $image)
                                                                <a href="{{ route('ticket.download', encrypt($image->id)) }}"
                                                                    class="mr-3"><i class="fa fa-file"></i>
                                                                    @lang('Attachment') {{ ++$k }} </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="row border border-warning border-radius-3 my-3 py-3 mx-2"
                                                style="background-color: #ffd96729">
                                                <div class="col-md-3 border-right text-right">
                                                    <h5 class="my-3">{{ $message->admin->name }}</h5>
                                                    <p class="lead text-muted">@lang('Staff')</p>
                                                </div>
                                                <div class="col-md-9">
                                                    <p class="text-muted font-weight-bold my-3">
                                                        @lang('Posted on')
                                                        {{ $message->created_at->format('l, dS F Y @ H:i') }}</p>
                                                    <p>{{ $message->message }}</p>
                                                    @if ($message->attachments()->count() > 0)
                                                        <div class="mt-2">
                                                            @foreach ($message->attachments as $k => $image)
                                                                <a href="{{ route('ticket.download', encrypt($image->id)) }}"
                                                                    class="mr-3"><i class="fa fa-file"></i>
                                                                    @lang('Attachment') {{ ++$k }} </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-user-confirmation-modal></x-user-confirmation-modal>
@endsection

@push('style')
    <style>
        .input-group-text:focus {
            box-shadow: none !important;
        }
        @media (max-width: 575px) {
            .button-badge {
                   width: 100%;  
            }
        }
        @media (max-width: 575px) {
            .button-title-badge {
                   width: 100%;  
            }
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
