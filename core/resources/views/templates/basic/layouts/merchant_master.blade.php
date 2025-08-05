@extends($activeTemplate . 'layouts.app')

@section('app')
    <div class="agent-dashboard">
        @include($activeTemplate.'partials.merchant_sidenav')
        @include($activeTemplate.'partials.merchant_topbar')

        <div class="agent-dashboard__body">
            @yield('content')
        </div>
        
    </div>
    
    @include('partials.sleep_mode', ['userType'=>'MERCHANT']) 
@endsection

@push('style-lib')
    <!-- main css -->
    <link rel="stylesheet" href="{{asset($activeTemplateTrue.'merchant/css/main.css')}}"> 
@endpush
 
@push('script-lib')
    <!-- main css -->
    <script src="{{asset($activeTemplateTrue.'merchant/js/app.js')}}"></script>
    <script src="{{asset('assets/global/js/jquery.slimscroll.min.js')}}"></script>
@endpush

