@extends($activeTemplate . 'layouts.app')

@section('app')

    @include($activeTemplate.'partials.auth_header')

    <div class="main-wrapper">
        <div class="pt-50 pb-50">
            <div class="container">
                <div class="row justify-content-center">
                    @if(request()->routeIs('user.home'))
                        @include($activeTemplate.'user.partials.sidenav')
                    @endif
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @include($activeTemplate.'partials.auth_footer')

    @include('partials.sleep_mode', ['userType'=>'USER']) 
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/dashboard.css') }}">
@endpush

@push('script')
<script>
    (function ($) {
        "use strict";
        var currentRoute = '{{ url()->current() }}';
        $('.navbar-nav li a[href="'+ currentRoute +'"]')
            .closest('li').addClass('active')
            .closest('.menu_has_children').first()
        .addClass('active');
    })(jQuery);
</script>
@endpush