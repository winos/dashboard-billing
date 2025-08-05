@extends($activeTemplate . 'layouts.agent_master')
@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card custom--card style--two border-1">
                <div class="card-header justify-content-center d-flex bg--primary">
                    <h5 class="card-title text-white">@lang('KYC Data')</h5>
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
                                            <a href="{{ route('agent.attachment.download', encrypt(getFilePath('verify') . '/' . $val->value)) }}"><i class="fa fa-file"></i> @lang('Attachment') </a>
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
.card {
     border-radius: 5px; 
}
.card-header:first-child {
    border-top-right-radius: 5px;
    border-top-left-radius: 5px;
}
.list-group-item:last-child {
    border-bottom-right-radius: 5px;
    border-bottom-left-radius: 5px;
}
</style>    
@endpush