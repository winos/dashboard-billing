@extends($activeTemplate.'layouts.agent_master')
@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-12">
           <div class="card style--two">
            <div class="card-header justify-content-center d-flex">
                <h5 class="card-title">@lang('KYC Form')</h5>
            </div>
            <div class="card-body p-4">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <form action="{{route('agent.kyc.submit')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <x-viser-form identifier="act" identifierValue="agent_kyc"></x-viser-form>
                            <div class="form-group">
                                <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
