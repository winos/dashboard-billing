@extends($activeTemplate . 'layouts.app')

@section('app')
    @yield('content')
    @include('partials.cookie')
@endsection

@push('style-lib')
    <!-- main css -->
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/main.css') }}">
@endpush