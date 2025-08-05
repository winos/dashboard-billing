<div class="d-sidebar h-100 rounded">
    <button class="sidebar-close-btn bg--base text-white"><i class="las la-times"></i></button>
    <a href="#0" class="header-username">{{ucwords(agent()->fullname)}}</a>
    <div class="sidebar-menu-wrapper" id="sidebar-menu-wrapper">
        <ul class="sidebar-menu">
            <li class="sidebar-menu__header">@lang('Main')</li>
            <li class="sidebar-menu__item {{menuActive('agent.home')}}">
                <a href="{{route('agent.home')}}" class="sidebar-menu__link">
                    <i class="lab la-buffer"></i>
                   @lang(' Dashboard')
                </a>
            </li>
            
            <li class="sidebar-menu__item {{menuActive('agent.money.in')}}">
                <a href="{{route('agent.money.in')}}" class="sidebar-menu__link">
                    <i class="las la-copy"></i>
                    @lang('Money In')
                </a>
            </li>

            
            <li class="sidebar-menu__item {{menuActive('agent.transactions')}}">
                <a href="{{route('agent.transactions')}}" class="sidebar-menu__link">
                    <i class="las la-file-invoice-dollar"></i>
                   @lang('Transactions')
                </a>
            </li>

            <li class="sidebar-menu__item {{menuActive('agent.commission.log')}}">
                <a href="{{route('agent.commission.log')}}" class="sidebar-menu__link">
                    <i class="las la-history"></i>
                   @lang('Commission Log')
                </a>
            </li>

            @if(module('add_money',$module)->status)
            <li class="sidebar-menu__header">@lang('Deposit')</li>
            <li class="sidebar-menu__item {{menuActive('agent.deposit')}}">
                <a href="{{route('agent.deposit')}}" class="sidebar-menu__link">
                    <i class="las la-wallet"></i>
                   @lang('Add Money')
                </a>
            </li>
            <li class="sidebar-menu__item {{menuActive('agent.deposit.history')}}">
                <a href="{{route('agent.deposit.history')}}" class="sidebar-menu__link">
                    <i class="las la-history"></i>
                   @lang('Add Money History')
                </a>
            </li>
            @endif

            @if(module('withdraw_money',$module)->status)
            <li class="sidebar-menu__header">@lang('Withdraw')</li>
            <li class="sidebar-menu__item {{menuActive('agent.withdraw.money')}}">
                <a href="{{route('agent.withdraw')}}" class="sidebar-menu__link">
                    <i class="las la-university"></i>
                    @lang('Withdraw Money')
                </a>
            </li>
            <li class="sidebar-menu__item {{menuActive('agent.withdraw.history')}}">
                <a href="{{route('agent.withdraw.history')}}" class="sidebar-menu__link">
                    <i class="las la-history"></i>
                   @lang('Withdraw History')
                </a>
            </li>
            @endif

            <li class="sidebar-menu__header">@lang('Settings')</li>
            <li class="sidebar-menu__item {{menuActive('agent.profile.setting')}}">
                <a href="{{route('agent.profile.setting')}}" class="sidebar-menu__link">
                    <i class="las la-user"></i>
                    @lang('Profile Setting')
                </a>
            </li>
            <li class="sidebar-menu__item {{menuActive('agent.change.password')}}">
                <a href="{{route('agent.change.password')}}" class="sidebar-menu__link">
                    <i class="las la-cogs"></i>
                   @lang('Password Setting')
                </a>
            </li>
            <li class="sidebar-menu__item {{menuActive('agent.twofactor')}}">
                <a href="{{route('agent.twofactor')}}" class="sidebar-menu__link">
                    <i class="las la-key"></i>
                   @lang('2FA Security')
                </a>
            </li>

            <li class="sidebar-menu__item {{menuActive('agent.qr')}}">
                <a href="{{route('agent.qr')}}" class="sidebar-menu__link">
                    <i class="las la-qrcode"></i>
                   @lang('My QRcode')
                </a>
            </li>

            <li class="sidebar-menu__item">
                <a href="{{route('agent.logout')}}" class="sidebar-menu__link">
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