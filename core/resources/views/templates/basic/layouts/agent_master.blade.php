@extends($activeTemplate . 'layouts.app')

@section('app')
    <div class="agent-dashboard">
        @include($activeTemplate.'partials.agent_sidenav')
        @include($activeTemplate.'partials.agent_topbar')

        <div class="agent-dashboard__body">
            @yield('content')
        </div>
        
    </div>

    @include('partials.sleep_mode', ['userType'=>'AGENT']) 
@endsection

@push('style-lib')
    <!-- main css -->
    <link rel="stylesheet" href="{{asset($activeTemplateTrue.'agent/css/main.css')}}"> 
@endpush

@push('script-lib')
    <!-- main css -->
    <script src="{{asset($activeTemplateTrue.'agent/js/app.js')}}"></script>
    <script src="{{asset('assets/global/js/jquery.slimscroll.min.js')}}"></script>
@endpush

