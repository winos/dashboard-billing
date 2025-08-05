<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" itemscope itemtype="http://schema.org/WebPage">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title> {{ gs()->siteName(__($pageTitle)) }}</title>
    @include('partials.seo')

    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <!-- fontawesome 5  -->
    <link rel="stylesheet" href="{{ asset('assets/global/css/all.min.css') }}">
    <!-- lineawesome font -->
    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/global/css/lightcase.css') }}">
    <!-- slick slider css -->
    <link rel="stylesheet" href="{{ asset('assets/global/css/slick.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">

    <!-- main css -->

    @stack('style-lib')

    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/custom.css') }}">

    @stack('style')

    <link href="{{ asset($activeTemplateTrue . 'css/color.php') }}?color={{ gs('base_color') }}" rel="stylesheet" />
    @stack('header-scrip-lib')
</head>

@php echo loadExtension('google-analytics') @endphp

<body>

    <div class="preloader">
        <div class="preloader-container">
            <span class="animated-preloader"></span>
        </div>
    </div>


    @yield('app')

    <!-- jQuery library -->
    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    <!-- bootstrap js -->
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <!-- slick slider js -->
    <script src="{{ asset('assets/global/js/slick.min.js') }}"></script>
    <!-- scroll animation -->
    <script src="{{ asset('assets/global/js/wow.min.js') }}"></script>
    <!-- lightcase js -->
    <script src="{{ asset('assets/global/js/lightcase.min.js') }}"></script>

    <script src="{{ asset('assets/global/js/jquery.paroller.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
    <!-- main js -->
    <script src="{{ asset($activeTemplateTrue . 'js/app.js') }}"></script>

    @stack('script-lib')

    @php echo loadExtension('tawk-chat') @endphp

    @include('partials.notify')

    @if (gs('pn'))
        @include('partials.push_script')
    @endif

    @stack('script')

    <script>
        (function($) {
            "use strict";

            $('.select2').select2();

            $('.main-menu li a[href="{{ url()->current() }}"]').closest('li').addClass('active');

            $(".languageList").on("click", function() {
                window.location.href = "{{ url('/') }}/change/" + $(this).data('code');
            });

            $('.policy').on('click', function() {
                $.get('{{ route('cookie.accept') }}', function(response) {
                    $('.cookies-card').addClass('d-none');
                });
            });

            setTimeout(function() {
                $('.cookies-card').removeClass('hide')
            }, 2000);

            $.each($('input:not([type=checkbox]):not([type=hidden]), select, textarea'), function(i, element) {
                var elementType = $(element);
                if (elementType.attr('type') != 'checkbox') {
                    if (element.hasAttribute('required')) {
                        $(element).closest('.form-group').find('label').addClass('required');
                    }
                }
            });

            var inputElements = $('[type=text],select,textarea,input');
            $.each(inputElements, function(index, element) {
                element = $(element);

                if (!element.hasClass('exclude')) {
                    element.closest('.form-group').find('label').attr('for', element.attr('name'));
                    element.attr('id', element.attr('name'))
                }

            });

            let disableSubmission = false;
            $('.disableSubmission').on('submit', function(e) {
                if (disableSubmission) {
                    e.preventDefault()
                } else {
                    disableSubmission = true;
                }
            });

        })(jQuery)
    </script>

</body>

</html>
