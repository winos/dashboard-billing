@extends($activeTemplate.'layouts.user_master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-deposit text-center box-shadow">
                <div class="card-header card-header-bg">
                    <h5 class="card-title">@lang('Your Unique QR Code')</h5>
                </div>
                <div class="card-body card-body-deposit text-center">
                    <img src="{{ $qrCode }}" alt="@lang('QR')" class="w-50">
                    <div class="d-flex flex-wrap justify-content-center">
                        <a class="btn btn--base m-1 mt-4 w-100" href="{{ route('user.qr.code.jpg') }}">
                            @lang('Downlaod as Image')
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
