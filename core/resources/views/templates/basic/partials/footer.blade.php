@php
    $footer = @getContent('footer.content', true)->data_values;
    $contact = @getContent('contact_us.content', true)->data_values;
    $policies = @getContent('policy_pages.element', orderById:true);
@endphp

<footer class="footer bg_img" style="background-image: url('{{ frontendImage('footer',$footer->background_image, '1920x768') }}');">
    <div class="footer__cta">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">
                    <div class="cta-wrapper rounded-3 wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.3s">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-xxl-7 col-lg-8 text-lg-start text-center">
                                <h2 class="title text-white">{{ __(@$footer->box_heading) }}</h2>
                            </div>
                            <div class="col-lg-4 text-lg-end text-center mt-lg-0 mt-4">
                                <a href="{{ $footer->box_button_link }}" class="btn btn--dark">{{ __(@$footer->box_button_name) }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer__top">
        <div class="widget-area">
            <div class="container">
                <div class="row gy-5">
                    <div class="col-lg-3">
                        <div class="footer-widget">
                            <a href="{{ route('home') }}" class="footer-logo">
                                <img src="{{siteLogo()}}" alt="image">
                            </a>
                            <p class="text-white mt-4">{{ __(@$footer->short_details) }}</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-4 ps-lg-4">
                        <div class="footer-widget">
                            <h6 class="footer-widget__title text-white">@lang('Quick Menu')</h6>
                            <ul class="footer-link-list">
                                @php
                                    $pages = App\Models\Page::where('tempname', $activeTemplate)->where('is_default', 0)->get();
                                @endphp
                                @foreach ($pages as $k => $data)
                                    <li><a href="{{ route('pages', [$data->slug]) }}">{{ __($data->name) }}</a></li>
                                @endforeach
                                <li><a href="{{ route('blogs') }}">@lang('Announcement')</a></li>
                                <li><a href="{{ route('api.documentation') }}">@lang('Developer')</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-4 ps-lg-4">
                        <div class="footer-widget">
                            <h6 class="footer-widget__title text-white">@lang('Get Started')</h6>
                            <ul class="footer-link-list">
                                <li><a href="{{ route('user.login') }}">@lang('Login as User')</a></li>
                                <li><a href="{{ route('agent.login') }}">@lang('Login as Agent')</a></li>
                                <li><a href="{{ route('merchant.login') }}">@lang('Login as Merchant')</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-4 ps-lg-4">
                        <div class="footer-widget">
                            <h6 class="footer-widget__title text-white">@lang('Useful links')</h6>
                            <ul class="footer-link-list">
                                @foreach ($policies as $policy)
                                    <li>
                                        <a href="{{ route('policy.pages', $policy->slug) }}">
                                            {{ $policy->data_values->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-contact-area">
            <div class="container">
                <div class="row justify-content-center align-items-center mb-5 footer-contact-wrapper">
                    <div class="col-md-4 col-sm-6 footer-contact-item">
                        <div class="footer-contact-card">
                            <div class="icon">
                                <i class="las la-envelope"></i>
                            </div>
                            <div class="content">
                                <h5><a href="mailto:{{ @$contact->email_address }}">{{ @$contact->email_address }}</a>
                                </h5>
                                <span class="caption font-size--14px">@lang('Mail Address')</span>
                            </div>
                        </div><!-- footer-contact-card end -->
                    </div>
                    <div class="col-md-4 col-sm-6 footer-contact-item">
                        <div class="footer-contact-card">
                            <div class="icon">
                                <i class="las la-phone-volume"></i>
                            </div>
                            <div class="content">
                                <h5><a href="tel:{{ @$contact->contact_number }}">{{ @$contact->contact_number }}</a>
                                </h5>
                                <span class="caption font-size--14px">@lang('Call Us')</span>
                            </div>
                        </div><!-- footer-contact-card end -->
                    </div>
                    <div class="col-md-4 col-sm-6 footer-contact-item">
                        <div class="footer-contact-card">
                            <div class="icon">
                                <i class="las la-map-marked-alt"></i>
                            </div>
                            <div class="content">
                                <h5 class="text-white">{{ @$contact->address }}</h5>
                                <span class="caption font-size--14px">@lang('Address')</span>
                            </div>
                        </div><!-- footer-contact-card end -->
                    </div>
                </div><!-- row end -->
            </div>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <p class="text-white">&copy; {{ date('Y') }} <a href="{{ route('home') }}">{{ __(gs('site_name')) }}</a> . @lang('All rights reserved')</p>
                </div>
            </div>
        </div>
    </div>
</footer>
