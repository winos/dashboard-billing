<div class="d-sidebar h-100 rounded">
    <button class="sidebar-close-btn bg--base text-white"><i class="las la-times"></i></button>
    <a href="javascript:void(0)" class="header-username">{{ucwords(merchant()->fullname)}}</a>
    <div class="sidebar-menu-wrapper" id="sidebar-menu-wrapper">
        <ul class="sidebar-menu">
            <li class="sidebar-menu__header">@lang('Main')</li>
            <li class="sidebar-menu__item {{menuActive('merchant.home')}}">
                <a href="{{route('merchant.home')}}" class="sidebar-menu__link">
                    <i class="lab la-buffer"></i>
                   @lang(' Dashboard')
                </a>
            </li>
            
            <li class="sidebar-menu__item {{menuActive('merchant.transactions')}}">
                <a href="{{route('merchant.transactions')}}" class="sidebar-menu__link">
                    <i class="las la-file-invoice-dollar"></i>
                   @lang('Transactions')
                </a>
            </li>
                       
            @if(module('withdraw_money',$module)->status)
            <li class="sidebar-menu__header">@lang('Withdraw')</li>
            <li class="sidebar-menu__item {{menuActive('merchant.withdraw')}}">
                <a href="{{route('merchant.withdraw')}}" class="sidebar-menu__link">
                    <i class="las la-university"></i>
                    @lang('Withdraw Money')
                </a>
            </li>
            <li class="sidebar-menu__item {{menuActive('merchant.withdraw.history')}}">
                <a href="{{route('merchant.withdraw.history')}}" class="sidebar-menu__link">
                    <i class="las la-history"></i>
                   @lang('Withdraw History')
                </a>
            </li>
            @endif


            <li class="sidebar-menu__header">@lang('Settings')</li>
            <li class="sidebar-menu__item {{menuActive('merchant.profile.setting')}}">
                <a href="{{route('merchant.profile.setting')}}" class="sidebar-menu__link">
                    <i class="las la-user"></i>
                    @lang('Profile Setting')
                </a>
            </li>
            <li class="sidebar-menu__item {{menuActive('merchant.change.password')}}">
                <a href="{{route('merchant.change.password')}}" class="sidebar-menu__link">
                    <i class="las la-cogs"></i>
                   @lang('Password Setting')
                </a>
            </li>
            <li class="sidebar-menu__item {{menuActive('merchant.twofactor')}}">
                <a href="{{route('merchant.twofactor')}}" class="sidebar-menu__link">
                    <i class="las la-key"></i>
                   @lang('2FA Security')
                </a>
            </li>

            <li class="sidebar-menu__item {{menuActive('merchant.qr')}}">
                <a href="{{route('merchant.qr')}}" class="sidebar-menu__link">
                    <i class="las la-qrcode"></i>
                   @lang('My QRcode')
                </a>
            </li>

            <li class="sidebar-menu__item {{menuActive('merchant.api.key')}}">
                <a href="{{route('merchant.api.key')}}" class="sidebar-menu__link">
                    <i class="las la-key"></i>
                    @lang('Api Key')
                </a>
            </li>

            <li class="sidebar-menu__item">
                <a href="{{route('merchant.logout')}}" class="sidebar-menu__link">
                    <i class="las la-sign-out-alt"></i>
                    @lang('Logout')
                </a>
            </li>


        </ul><!-- sidebar-menu end -->
    </div>
</div>

@push('script')
     <script>
            'use strict';
            (function ($) {
                const sidebar = document.querySelector('.d-sidebar');
                const sidebarOpenBtn = document.querySelector('.sidebar-open-btn');
                const sidebarCloseBtn = document.querySelector('.sidebar-close-btn');

                sidebarOpenBtn.addEventListener('click', function(){
                    sidebar.classList.add('active');
                });
                sidebarCloseBtn.addEventListener('click', function(){
                    sidebar.classList.remove('active');
                });


                $(function(){
                    $('#sidebar-menu-wrapper').slimScroll({
                        // height: 'calc(100vh - 52px)'
                        height: '100vh'
                    });
                });

                $('.sidebar-dropdown > a').on('click', function () {
                    if ($(this).parent().find('.sidebar-submenu').length) {
                        if ($(this).parent().find('.sidebar-submenu').first().is(':visible')) {
                        $(this).find('.side-menu__sub-icon').removeClass('transform rotate-180');
                        $(this).removeClass('side-menu--open');
                        $(this).parent().find('.sidebar-submenu').first().slideUp({
                            done: function done() {
                            $(this).removeClass('sidebar-submenu__open');
                            }
                        });
                        } else {
                        $(this).find('.side-menu__sub-icon').addClass('transform rotate-180');
                        $(this).addClass('side-menu--open');
                        $(this).parent().find('.sidebar-submenu').first().slideDown({
                            done: function done() {
                            $(this).addClass('sidebar-submenu__open');
                            }
                        });
                        }
                    }
                });
            })(jQuery);
     </script>
@endpush