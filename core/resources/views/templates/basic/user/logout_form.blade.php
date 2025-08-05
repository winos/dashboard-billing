@extends($activeTemplate.'layouts.user_master')

@section('content')
    <div class="col-md-12">
        <div class='card'>
            <div class="d-user-notification d-flex flex-wrap align-items-center">
                <div class="icon text--warning">
                    <i class="las la-info-circle"></i>
                </div>
                <div class="content">
                    <p class="text-white fw-bold">{{ __($pageTitle) }}</p>
                </div>
            </div>
            <div class='card-body text-center'>
                @lang("If you're signing out of browser or app remotely, signing out will remove your")  
                <span class="text--base fw-bold">{{ __(gs('site_name')) }}</span>
                @lang("account from that device").
            </div>
        </div>

        <div class="card style--two mt-4">
            <div class="card-body p-4">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <form action="{{ route('user.logout.other.devices') }}" method="post">
                            @csrf
                            <div>
                                <label for="password">@lang('Enter Your Password')</label>
                                <input id="password" type="password" class="form--control" name="password" required
                                    autocomplete="current-password" placeholder="@lang('Password')"
                                >
                            </div>
                            <div class="form-group">
                                <input type="submit" class="mt-4 btn btn--base w-100" value="@lang('Submit')">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

