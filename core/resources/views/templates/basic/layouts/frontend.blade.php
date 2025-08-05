@extends($activeTemplate . 'layouts.app')

@section('app')

    @stack('fbComment')
    
    <div class="preloader">
        <div class="preloader-container">
            <span class="animated-preloader"></span>
        </div>
    </div>

    @include($activeTemplate .'partials.header')

    <div class="main-wrapper">
        @if(!request()->routeIs('home')) 
            @include($activeTemplate . 'partials.breadcrumb')
        @endif

        @yield('content')

        @include($activeTemplate . 'partials.footer')
    </div>

    @include('partials.cookie')
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/main.css') }}">
@endpush