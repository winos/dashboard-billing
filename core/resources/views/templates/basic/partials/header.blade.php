@php
    $icons = getContent('social_icon.element', orderById: true);
    $contact = getContent('contact_us.content', true)->data_values;
@endphp
<header class="header">
    <div class="header__top">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <ul class="header-info-list d-flex flex-wrap justify-content-lg-start justify-content-center">
                        <li>
                            <a href="mailto:{{ $contact->email_address }}"><i class="las la-envelope"></i>
                                {{ $contact->email_address }}
                            </a>
                        </li>
                        <li>
                            <a href="tel:{{ $contact->contact_number }}"><i class="las la-phone-volume"></i>
                                {{ $contact->contact_number }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6 d-flex flex-wrap align-items-center justify-content-lg-end justify-content-center mt-lg-0 mt-2 header-top-left">
                    <ul class="social-links style--white d-flex flex-wrap align-items-center justify-content-end">
                        <li class="font-size--14px text-white me-3">@lang('Social Links') :</li>
                        @foreach ($icons as $icon)
                            <li>
                                <a target="_blank" href="{{ $icon->data_values->url }}">
                                    @php echo $icon->data_values->social_icon @endphp
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    @if (gs('multi_language'))
                        @php
                            $language = App\Models\Language::all();
                            $currentLang = $language->where('code', session('lang'))->first();
                        @endphp

                        <div class="ms-2">
                            <div class="language dropdown">

                                <button class="language-wrapper" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="language-content">
                                        <div class="language_flag">
                                            <img src="{{ getImage(getFilePath('language') . '/' . @$currentLang->image, getFileSize('language')) }}" alt="flag">
                                        </div>
                                        <p class="language_text_select">{{ __(@$currentLang->name) }}</p>
                                    </div>
                                    <span class="collapse-icon"><i class="las la-angle-down"></i></span>
                                </button>

                                <div class="dropdown-menu langList_dropdow py-2">
                                    <ul class="langList">
                                        @foreach ($language as $item)
                                            @if (session('lang') != $item->code)
                                                <li class="language-list languageList" data-code="{{ $item->code }}">
                                                    <div class="language_flag">
                                                        <img src="{{ getImage(getFilePath('language') . '/' . $item->image, getFileSize('language')) }}" alt="flag">
                                                    </div>
                                                    <p class="language_text">{{ __($item->name) }}</p>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="header__bottom">
        <div class="container-fluid">
            <nav class="navbar navbar-expand-xl p-0 align-items-center">
                <a class="site-logo site-title" href="{{ route('home') }}">
                    <img src="{{ siteLogo() }}" alt="@lang('logo')">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="menu-toggle"></span>
                </button>
                <div class="collapse navbar-collapse mt-lg-0 mt-3" id="navbarSupportedContent">
                    <ul class="navbar-nav main-menu ms-auto">
                        <li><a href="{{ route('home') }}">@lang('Home')</a></li>
                        @php
                            $pages = App\Models\Page::where('tempname', $activeTemplate)->where('is_default', 0)->get();
                        @endphp
                        @foreach ($pages as $k => $data)
                            <li><a href="{{ route('pages', [$data->slug]) }}">{{ __($data->name) }}</a></li>
                        @endforeach
                        <li><a href="{{ route('blogs') }}">@lang('Announcement')</a></li>
                        <li><a href="{{ route('api.documentation') }}">@lang('Developer')</a></li>
                        <li><a href="{{ route('contact') }}">@lang('Contact')</a></li>
                    </ul>
                    <div class="nav-right">
                        @if (auth()->user())
                            <a href="{{ route('user.logout') }}" class="btn btn-sm btn--danger d-lg-inline-flex align-items-center me-2">
                                <i class="las la-sign-out-alt font-size--18px me-2"></i> @lang('Logout')
                            </a>
                            <a href="{{ route('user.home') }}" class="btn btn-sm btn--base d-lg-inline-flex align-items-center">
                                <i class="las la-home font-size--18px me-2"></i> @lang('Dashboard')
                            </a>
                        @elseif(agent())
                            <a href="{{ route('agent.logout') }}" class="btn btn-sm btn--danger d-lg-inline-flex align-items-center me-2">
                                <i class="las la-sign-out-alt font-size--18px me-2"></i> @lang('Logout')
                            </a>
                            <a href="{{ route('agent.home') }}" class="btn btn-sm btn--base d-lg-inline-flex align-items-center">
                                <i class="las la-home font-size--18px me-2"></i> @lang('Dashboard')
                            </a>
                        @elseif(merchant())
                            <a href="{{ route('merchant.logout') }}" class="btn btn-sm btn--danger d-lg-inline-flex align-items-center me-2">
                                <i class="las la-sign-out-alt font-size--18px me-2"></i> @lang('Logout')
                            </a>
                            <a href="{{ route('merchant.home') }}" class="btn btn-sm btn--base d-lg-inline-flex align-items-center">
                                <i class="las la-home font-size--18px me-2"></i> @lang('Dashboard')
                            </a>
                        @else
                            <a href="{{ route('user.login') }}" class="btn btn-sm btn--base d-lg-inline-flex align-items-center">
                                <i class="las la-user-circle font-size--18px me-2"></i> @lang('Login')
                            </a>
                        @endif
                    </div>
                </div>
            </nav>
        </div>
    </div><!-- header__bottom end -->
</header>

@push('script')
    <script>
        (function($) {
            "use strict";

            const $mainlangList = $(".langList");
            const $langBtn = $(".language-content");
            const $langListItem = $mainlangList.children();

            $langListItem.each(function() {
                const $innerItem = $(this);
                const $languageText = $innerItem.find(".language_text");
                const $languageFlag = $innerItem.find(".language_flag");

                $innerItem.on("click", function(e) {
                    $langBtn.find(".language_text_select").text($languageText.text());
                    $langBtn.find(".language_flag").html($languageFlag.html());
                });
            });

        })(jQuery);
    </script>
@endpush


@push('style')
    <style>
        .language-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 5px 12px;
            border-radius: 4px;
            width: max-content;
            background-color: transparent;
            border: 1px solid hsl(var(--white) / .5) !important;
            height: 38px;
        }

        .sm-screen {
            max-width: 130px;
        }

        .sm-screen .language-wrapper {
            border: 1px solid hsl(var(--dark) / .5) !important;
        }

        .sm-screen .language_text_select {
            color: #ffffff;
        }

        .sm-screen .collapse-icon {
            color: #ffffff;
        }


        .language_flag {
            flex-shrink: 0
        }

        .language_flag img {
            height: 20px;
            width: 20px;
            object-fit: cover;
            border-radius: 50%;
        }

        .language-wrapper.show .collapse-icon {
            transform: rotate(180deg)
        }

        .collapse-icon {
            font-size: 14px;
            display: flex;
            transition: all linear 0.2s;
            color: #fff;
        }

        .language_text_select {
            font-size: 14px;
            font-weight: 400;
            color: #fff;
            margin-bottom: 0;
        }

        .language-content {
            display: flex;
            align-items: center;
            gap: 6px;
        }


        .language_text {
            color: #fff;
            margin-bottom: 0;
        }

        .langList {
            padding: 0;
        }

        .language-list {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            cursor: pointer;
        }

        .language .dropdown-menu {
            position: absolute;
            -webkit-transition: ease-in-out 0.1s;
            transition: ease-in-out 0.1s;
            opacity: 0;
            visibility: hidden;
            top: 100%;
            display: unset;
            background: #1a4164;
            -webkit-transform: scaleY(1);
            transform: scaleY(1);
            min-width: 150px;
            padding: 7px 0 !important;
            border-radius: 8px;
            border: 1px solid rgb(255 255 255 / 10%);
        }

        .language .dropdown-menu.show {
            visibility: visible;
            opacity: 1;
            inset: unset !important;
            margin: 0px !important;
            transform: unset !important;
            top: 100% !important;
        }

        @media(max-width: 425px) {
            .header-top-left {
                flex-direction: column;
                justify-content: center !important;
                align-items: center !important
            }

            .header-top-left .ms-auto {
                margin: 0 auto !important;
            }
        }
    </style>
@endpush
