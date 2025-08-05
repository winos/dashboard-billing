@php $user = auth()->user(); @endphp
<header class="header">
    <div class="header__top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-3 col-sm-4 text-sm-start text-center d-sm-block d-none">
                    <a href="javascript:void(0)" class="header-username">{{ $user->fullname }}</a>
                </div>
                <div class="col-lg-9 col-sm-8">
                    <div class="d-flex flex-wrap justify-content-sm-end justify-content-center align-items-center">
                        <ul class="header-top-menu">
                            <li><a href="{{ route('ticket.index') }}">@lang('Support Ticket')</a></li>
                        </ul>
                        <div class="header-user">
                            <span class="thumb">
                                <img src="{{ getImage(getFilePath('userProfile') . '/' . @$user->image, getFileSize('userProfile')) }}"
                                    alt="@lang('Profile')">
                            </span>
                            <span class="name">{{ $user->username }}</span>
                            <ul class="header-user-menu">
                                <li>
                                    <a href="{{ route('user.profile.setting') }}">
                                        <i class="las la-user-circle"></i>@lang('Profile')
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user.change.password') }}">
                                        <i class="las la-cogs"></i>@lang('Change Password')
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user.twofactor') }}">
                                        <i class="las la-bell"></i>@lang('2FA Security')
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user.qr.code') }}">
                                        <i class="las la-qrcode"></i>@lang('My QRcode')
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user.logout.other.devices.form') }}">
                                        <i class="las la-laptop"></i>@lang('Logout From Others')
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user.logout') }}">
                                        <i class="las la-sign-out-alt"></i>@lang('Logout')
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="header__bottom">
        <div class="container">
            <nav class="navbar navbar-expand-xl p-0 align-items-center">
                <a class="site-logo site-title" href="{{ route('home') }}">
                    <img src="{{ siteLogo() }}" alt="logo">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="menu-toggle"></span>
                </button>
                <div class="collapse navbar-collapse mt-lg-0 mt-3" id="navbarSupportedContent">
                    <ul class="navbar-nav main-menu ms-auto">
                        <li><a href="{{ route('user.home') }}">@lang('Dashboard')</a></li>

                        @if (module('add_money', $module)->status)
                            <li class="menu_has_children"><a href="#0">@lang('Add Money')</a>
                                <ul class="sub-menu">
                                    <li><a href="{{ route('user.deposit.index') }}">@lang('Add Money')</a></li>
                                    <li><a href="{{ route('user.deposit.history') }}">@lang('Add Money History')</a></li>
                                </ul>
                            </li>
                        @endif

                        @if (module('money_out', $module)->status || module('make_payment', $module)->status)
                            <li class="menu_has_children"><a href="#0">@lang('Money Discharge')</a>
                                <ul class="sub-menu">
                                    @if (module('money_out', $module)->status)
                                        <li><a href="{{ route('user.money.out') }}">@lang('Money Out')</a></li>
                                    @endif
                                    @if (module('make_payment', $module)->status)
                                        <li><a href="{{ route('user.payment') }}">@lang('Make Payment')</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if (module('transfer_money', $module)->status)
                            <li><a href="{{ route('user.transfer') }}">@lang('Transfer')</a></li>
                        @endif

                        @if (module('request_money', $module)->status)
                            <li class="menu_has_children"><a href="#0">@lang('Request Money')</a>
                                <ul class="sub-menu">
                                    <li><a href="{{ route('user.request.money') }}">@lang('Request Money')</a></li>  
                                    <li><a href="{{ route('user.requests') }}">@lang('Requests to me')</a></li> 
                                    <li><a href="{{ route('user.request.money.history') }}">@lang('My Requested History')</a></li> 
                                </ul> 
                            </li>
                        @endif

                        @if (module('create_voucher', $module)->status)
                            <li class="menu_has_children"><a href="#0">@lang('Voucher')</a>
                                <ul class="sub-menu">
                                    <li><a href="{{ route('user.voucher.list') }}">@lang('My Vouchers')</a></li>
                                    <li><a href="{{ route('user.voucher.redeem') }}">@lang('Voucher Redeem')</a></li>
                                </ul>
                            </li>
                        @endif

                        @if (module('withdraw_money', $module)->status)
                            <li class="menu_has_children"><a href="#0">@lang('Withdrawals')</a>
                                <ul class="sub-menu">
                                    <li><a href="{{ route('user.withdraw') }}">@lang('Withdraw Money')</a></li>
                                    <li><a href="{{ route('user.withdraw.methods') }}">@lang('Withdraw methods')</a></li>
                                    <li><a href="{{ route('user.withdraw.history') }}">@lang('Withdraw History')</a></li>
                                </ul>
                            </li>
                        @endif 

                        <li><a href="{{ route('user.transactions') }}">@lang('Transactions')</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </div><!-- header__bottom end -->
</header>
