@extends($activeTemplate .'layouts.frontend')
@section('content')
    <!-- documentation section start -->
    <div class="pt-50 pb-50 documentation-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-2">
                    <button class="sidebar-menu-open-btn mb-5"><i class="las la-bars"></i> @lang('Menu')</button>
                    <div class="documentation-menu-wrapper">
                        <button class="sidebar-close-btn"><i class="las la-times"></i></button>
                        <nav class="sidebar-menu">
                            <ul class="menu">
                                <li class="has_child"><a href="#introduction-section">@lang('Get started')</a>
                                    <ul class="drp-menu">
                                        <li class="active"><a href="#introduction">@lang('Introduction')</a></li>
                                        <li><a href="#currency">@lang('Supported Currencies')</a></li>
                                        <li><a href="#api-key">@lang('Get Api Key')</a></li>
                                        <li><a href="#initiate">@lang('Initiate Payment')</a></li>
                                        <li><a href="#ipn">@lang('IPN and Get Payment')</a></li>
                                        @if(!blank($plugins))
                                        <li><a href="#plugins">@lang('API Plugins')</a></li>
                                        @endif
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="col-lg-10">
                    <div class="doc-body">
                        <div class="doc-section" id="introduction-section">
                            <div class="doc-content">
                                <section id="introduction">
                                    <h3>@lang('Introduction')</h3>
                                    <p class="mt-2">@lang('This section describes the') <strong>{{ __(gs('site_name')) }}</strong>
                                        @lang('payment gateway API.')
                                    </p>
                                    <hr>
                                    <p class="text-justify">
                                        <strong>{{ __(gs('site_name')) }}</strong> @lang('API is easy to implement in your business software. Our API is well formatted URLs, accepts cURL requests, returns JSON responses.')
                                    </p>
                                    <p class="text-justify">
                                        @lang('You can use the API in test mode, which does not affect your live data. The API key is use to authenticate the request and determines the request is valid payment or not. For test mode just use the sandbox URL and In case of live mode use the live URL from  section') <a href="#initiate">@lang('Initiate Payment')</a> .
                                    </p>
                                </section>
                            </div><!-- doc-content end -->
                        </div><!-- doc-section end -->
                        <div class="doc-section" id="currency">
                            <div class="doc-content">
                                <section id="">
                                    <h2>@lang('Supported Currencies')</h2>
                                    <p class="mt-2">@lang('This section describes the currencies supported by') <strong>{{ __(gs('site_name')) }}</strong></p>
                                    <hr>
                                    <p>
                                        <strong>{{ __(gs('site_name')) }}</strong>
                                        @lang(' allows to make transaction with below currencies. Any new currency may update in future.')
                                    </p>
                                </section>
                                <section id="setting-two">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('Currency Name')</th>
                                                    <th>@lang('Currency Symbol')</th>
                                                    <th>@lang('Currency Code')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($allCurrency as $currency)
                                                    <tr>
                                                        <td>{{ $currency->currency_fullname }}</td>
                                                        <td>{{ $currency->currency_symbol }}</td>
                                                        <td>{{ $currency->currency_code }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div><!-- table-responsive end -->
                                </section>
                            </div><!-- doc-content end -->
                        </div><!-- doc-section end -->
                        <div class="doc-section" id="api-key">
                            <div class="doc-content">
                                <section id="">
                                    <h2>@lang('Get The Api Key')</h2>
                                    <p class="mt-2">@lang('This section describes how you can get your api key.')</p>
                                    <hr>
                                    <p class="text-justify">@lang('Login to your') <strong>{{ __(gs('site_name')) }}</strong>
                                        @lang('merchant account.') @lang('If you don\'t have any ? ')
                                        <a target="_blank" href=" {{ route('merchant.login') }} ">@lang('Click Here')</a>
                                    </p>
                                    <p>@lang('Next step is to find the') <span class="text--base">@lang('Api Key')</span>
                                        @lang('menu in your dashboard sidebar. Click the menu.')
                                    </p>
                                    <p class="text-justify">@lang('The api keys can be found there which is') <strong>@lang('Public key and Secret key.')</strong>
                                        @lang('Use these keys to initiate the API request. Every time you can generate new API key by clicking')
                                        <span class="text--base">@lang('Generate Api Key')</span>
                                        @lang('button. Remember do not share these keys with anyone.')
                                    </p>
                                </section>
                            </div><!-- doc-content end -->
                        </div><!-- doc-section end -->
                        <div class="doc-section" id="initiate">
                            <div class="doc-content">
                                <section id="">
                                    <h2>@lang('Initiate Payment')</h2>
                                    <p class="mt-2">@lang('This section describes the process of initaiing the payment.')</p>
                                    <hr>
                                    <p>
                                        @lang('To initiate the payment follow the example code and be careful with the perameters. You will need to make request with these following API end points.')
                                    </p>
                                    <p>
                                        <strong>@lang('Live End Point:')</strong>
                                        <span class="text--base"> {{ route('initiate.payment') }} </span>
                                    </p>
                                    <p class="d-flex align-items-center flex-wrap gap-2">
                                        <strong>@lang('Test End Point:')</strong>
                                        <span class="text--base responsive-text"> {{ route('test.initiate.payment') }} </span>
                                    </p>
                                    <p>
                                        <strong>@lang('Test Mode Mail:')</strong>
                                        <span class="text--base">test_mode@mail.com</span>
                                    </p>
                                    <p>
                                        <strong>@lang('Test Mode Verification Code:')</strong>
                                        <span class="text--base">222666</span>
                                    </p>
                                    <p>
                                        <strong>@lang('Request Method:')</strong>
                                        <span class="text--base">POST</span>
                                    </p>
                                </section>
                                <section id="setting-two">
                                    <p>@lang('Request to the end point with the following parameters below.')</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('Param Name')</th>
                                                    <th>@lang('Param Type')</th>
                                                    <th>@lang('Description')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>public_key</td>
                                                    <td>string (50)</td>
                                                    <td>
                                                        <span class="badge badge--danger font-size--12px">@lang('Required')</span>
                                                        @lang('Your Public API key')
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>identifier</td>
                                                    <td>string (20)</td>
                                                    <td>
                                                        <span class="badge badge--danger font-size--12px">@lang('Required')</span>
                                                        @lang('Identifier is basically for identify payment at your end')
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>currency</td>
                                                    <td>string (4)</td>
                                                    <td>
                                                        <span class="badge badge--danger font-size--12px">@lang('Required')</span>
                                                        @lang('Currency Code, Must be in Upper Case. e.g. USD,EUR')
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>amount</td>
                                                    <td>decimal</td>
                                                    <td>
                                                        <span class="badge badge--danger font-size--12px">@lang('Required')</span>
                                                        @lang('Payment amount.')
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>details</td>
                                                    <td>string (100)</td>
                                                    <td>
                                                        <span class="badge badge--danger font-size--12px">@lang('Required')</span>
                                                        @lang('Details of your payment or transaction.')
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>ipn_url</td>
                                                    <td>string</td>
                                                    <td>
                                                        <span class="badge badge--danger font-size--12px">@lang('Required')</span>
                                                        @lang('The url of instant payment notification.')
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>success_url</td>
                                                    <td>string</td>
                                                    <td>
                                                        <span class="badge badge--danger font-size--12px">@lang('Required')</span>
                                                        @lang('Payment success redirect url.')
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>cancel_url</td>
                                                    <td>string</td>
                                                    <td>
                                                        <span class="badge badge--danger font-size--12px">@lang('Required')</span>
                                                        @lang('Payment cancel redirect url.')
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>site_logo</td>
                                                    <td>string/url</td>
                                                    <td>
                                                        <span class="badge badge--danger font-size--12px">@lang('Required')</span>
                                                        @lang('Your business site logo.')
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>checkout_theme</td>
                                                    <td>string</td>
                                                    <td>
                                                        <span class="badge badge--info font-size--12px">@lang('Optional')</span>
                                                        @lang('Checkout form theme dark/light. Default theme is light')
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>customer_name</td>
                                                    <td>string (30)</td>
                                                    <td>
                                                        <span class="badge badge--danger font-size--12px">@lang('Required')</span>
                                                        @lang('Customer name.')
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>customer_email</td>
                                                    <td>string (30)</td>
                                                    <td>
                                                        <span class="badge badge--danger font-size--12px">@lang('Required')</span>
                                                        @lang('Customer valid email.')
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div><!-- table-responsive end -->

                                </section>
                            </div><!-- doc-content end -->
                            <div class="doc-code">
                                <div class="doc-code-inner">
                                    <div class="code-block">
                                        <button class="clipboard-btn" data-clipboard-target="#php">@lang('copy')</button>
                                        <div class="code-block-header">@lang('Example PHP code')</div>

<pre><code class="language-php" id="php">&lt;?php
    $parameters = [
        'identifier' =&gt; 'DFU80XZIKS',
        'currency' =&gt; 'USD',
        'amount' =&gt; 100.00,
        'details' =&gt; 'Purchase T-shirt',
        'ipn_url' =&gt; 'http://example.com/ipn_url.php',
        'cancel_url' =&gt; 'http://example.com/cancel_url.php',
        'success_url' =&gt; 'http://example.com/success_url.php',
        'public_key' =&gt; 'your_public_key',
        'site_logo' =&gt; '{{ asset('assets/images/logoIcon/logo.png') }}',
        'checkout_theme' =&gt; 'dark',
        'customer_name' =&gt; 'John Doe',
        'customer_email' =&gt; 'john@mail.com',

    ];

    //live end point
    $url = "{{ route('initiate.payment') }}";

    //test end point
    $url = "{{ route('test.initiate.payment') }}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS,  $parameters);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    //$result contains the response back.
?&gt;</code></pre>

                                    </div><!-- code-block end -->
                                    <div class="code-block">
                                        <button class="clipboard-btn" data-clipboard-target="#response">@lang('copy')</button>
                                        <div class="code-block-header">@lang('Example Responses')</div>

<pre><code class="language-php" id="response">//Error Response.
{
    "error": "true",
    "message": "Invalid api key"
}

//Success Response.
{
    "success": "ok",
    "message": "Payment Initiated. Redirect to url.",
    "url":"http://example.com/initiate/payment/checkout?payment_id=eJSAASDxdrt4DASDASVNASJA7893232432cvmdsamnvASF"
}
</code></pre>

                                    </div><!-- code-block end -->
                                </div>
                            </div>
                        </div><!-- doc-section end -->

                        <div class="doc-section" id="ipn">
                            <div class="doc-content">
                                <section id="">
                                    <h2>@lang('Validate The Payment and IPN')</h2>
                                    <p class="mt-2">@lang('This section describes the process to get your instant payment notification.')</p>
                                    <hr>
                                    <p>
                                        @lang('To initiate the payment follow the example code and be careful with the perameters. You will need to make request with these following API end points.')
                                    </p>
                                    <p>
                                        <strong>@lang('End Point:')</strong> <span class="text--base">@lang('Your business application ipn url.')</span>
                                    </p>
                                    <p><strong>@lang('Request Method:')</strong> <span class="text--base">POST</span></p>
                                </section>
                                <section id="setting-two">
                                    <p>@lang('You will get following parameters below.')</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('Param Name')</th>
                                                    <th>@lang('Description')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>status</td>
                                                    <td>@lang('Payment success status.')</td>
                                                </tr>
                                                <tr>
                                                    <td>identifier</td>
                                                    <td>@lang('Identifier is basically for identify payment at your end.')</td>
                                                </tr>
                                                <tr>
                                                    <td>signature</td>
                                                    <td>@lang('A hash signature to verify your payment at your end.')</td>
                                                </tr>
                                                <tr>
                                                    <td>data</td>
                                                    <td> @lang('Data contains some basic information with charges, amount, currency, payment transaction id etc.')</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div><!-- table-responsive end -->
                                </section>
                            </div><!-- doc-content end -->
                            <div class="doc-code">
                                <div class="doc-code-inner">
                                    <div class="code-block">
                                        <button class="clipboard-btn" data-clipboard-target="#ipn-s">@lang('copy')</button>
                                        <div class="code-block-header">@lang('Example PHP code')</div>

<pre><code class="language-php" id="ipn-s">&lt;?php
    //Receive the response parameter
    $status = $_POST['status'];
    $signature = $_POST['signature'];
    $identifier = $_POST['identifier'];
    $data = $_POST['data'];

    // Generate your signature
    $customKey = $data['amount'].$identifier;
    $secret = 'YOUR_SECRET_KEY';
    $mySignature = strtoupper(hash_hmac('sha256', $customKey , $secret));

    $myIdentifier = 'YOUR_GIVEN_IDENTIFIER';

    if($status == &quot;success&quot; &amp;&amp; $signature == $mySignature &amp;&amp;  $identifier ==  $myIdentifier){
        //your operation logic
    }
?&gt;</code></pre>

                                    </div><!-- code-block end -->
                                </div>
                            </div>
                        </div><!-- doc-section end -->

                        @if(!blank($plugins))
                        <div class="doc-section" id="plugins">
                            <div class="doc-content">
                                <section>
                                    <h2>@lang('API Plugin')</h2>
                                    <hr>
                                    <p>
                                        @lang('You can use our ready made API Plugin for your desire CMS to collect payment using '.gs()->site_name.'.')
                                    </p>

                                    <div class="plugins">
                                        @foreach($plugins as $plugin)
                                        <div class="plugin-item">
                                            <div class="plugin-item__thumb">
                                                <img src="{{ getImage('assets/plugins/'.$plugin->plugin_for.'.png') }}" alt="">
                                            </div>
                                            <div class="plugin-item__content">
                                                <h3 class="plugin-item__title">{{ __($plugin->plugin_for.' Plugin') }}</h3>
                                                <a href="{{ asset('assets/plugins/'.$plugin->file_name) }}" download=""><img src="{{ getImage('assets/images/download.png') }}" alt=""></a>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </section>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- documentation section end -->
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/dashboard.css') }}">
@endpush

@push('style')
    <style>
        .header.style--two .main-menu li a {
            padding: 0.5rem 0;
        }
        .header.style--two .header__bottom {
            padding: 15px 0;
        }
    </style>
@endpush

@push('script-lib')
    <script src="{{ asset($activeTemplateTrue . 'js/lib/clipboard.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/lib/menu-spy.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/lib/jquery.easing.min.js') }}"></script>
    @endpush
    
@push('header-scrip-lib')
    <script src="{{ asset($activeTemplateTrue . 'js/lib/highlight.min.js') }}"></script>
@endpush
    

@push('script')
    <script>
        'use strict';

        hljs.highlightAll();

        //jQuery for page scrolling feature - requires jQuery Easing plugin
        $('.sidebar-menu ul.menu').each(function() {
            $('.sidebar-menu ul.menu li a').on('click', function(event) {
                var $anchor = $(this);
                $('html, body').stop().animate({
                    scrollTop: $($anchor.attr('href')).offset().top - 100
                }, 300, 'easeInOutExpo');
                event.preventDefault();
            });
        });

        // spy scroll menu activation
        const elm = document.querySelector('.sidebar-menu');
        const ms = new MenuSpy(elm, {
            // menu selector
            menuItemSelector: 'a[href^="#"]',
            // CSS class for active item
            activeClass: 'active',
            // amount of space between your menu and the next section to be activated.
            threshold: 0,
            // timeout to apply browser's hash location.
            hashTimeout: 500,
            // called every time a new menu item activates.
            callback: null
        });

        new ClipboardJS('.clipboard-btn');

        const sidebarWrapper = document.querySelector('.documentation-menu-wrapper');
        const sidebarOpenBtn = document.querySelector('.sidebar-menu-open-btn');
        const sidebarCloseBtn = document.querySelector('.sidebar-close-btn');

        sidebarOpenBtn.addEventListener('click', function() {
            sidebarWrapper.classList.add('open');
        });

        sidebarCloseBtn.addEventListener('click', function() {
            sidebarWrapper.classList.remove('open');
        });
    </script>
@endpush
