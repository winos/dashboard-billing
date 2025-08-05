<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> {{ gs()->sitename(__($pageTitle)) }}</title>
  @include('partials.seo')
  
<!-- bootstrap 4  -->
<link rel="stylesheet" href="{{asset('assets/global/css/bootstrap.min.css')}}">
<!-- fontawesome 5  -->
<link rel="stylesheet" href="{{asset('assets/global/css/all.min.css')}}"> 
<!-- lineawesome font -->
<link rel="stylesheet" href="{{asset('assets/global/css/line-awesome.min.css')}}"> 

<link rel="stylesheet" href="{{asset($activeTemplateTrue.'merchant/css/main.css')}}">
</head>
  <body>
 
    <!-- checkout section start -->
    @yield('content')
    <!-- checkout section end -->

    <!-- jQuery library -->
    <script src="{{asset('assets/global/js/jquery-3.7.1.min.js')}}"></script>
    <!-- bootstrap js -->
    <script src="{{asset('assets/global/js/bootstrap.bundle.min.js')}}"></script>
    @stack('script')
    @include('partials.notify')
  </body>
</html>