@extends($activeTemplate . 'layouts.user_master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card style--two">
                <div class="card-header justify-content-center d-flex bg--primary">
                    <h5 class="card-title text-white">@lang('KYC Documents')</h5>
                </div>
                <div class="">
                    @if ($user->kyc_data) 
                        <ul class="list-group list-group-flush">
                            @foreach ($user->kyc_data as $val)
                                @continue(!$val->value)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="fw--bold">{{ __($val->name) }}</span>
                                    <span>
                                        @if ($val->type == 'checkbox')
                                            {{ implode(',', $val->value) }}
                                        @elseif($val->type == 'file')
                                            <a href="{{ route('user.download.attachment', encrypt(getFilePath('verify') . '/' . $val->value)) }}"><i class="fa fa-file"></i> @lang('Attachment') </a>
                                        @else
                                            <p>{{ __($val->value) }}</p>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <h5 class="text-center mt-4 mb-4">@lang('KYC data not found')</h5>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
<style>
    .main-wrapper {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }    
</style>    
@endpush
