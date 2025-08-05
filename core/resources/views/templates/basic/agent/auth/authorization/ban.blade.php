@extends($activeTemplate.'layouts.frontend')

@section('content')
<div class="pt-100 pb-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6">
                <div class="card banned style--two ">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-center">
                        <h5 class="card-title">{{ __($pageTitle) }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row justify-content-center">
                            <div class="col-xl-12">
                                <b class="mb-1">@lang('Reason')</b>
                                <p>{{ $user->ban_reason }}</p>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
