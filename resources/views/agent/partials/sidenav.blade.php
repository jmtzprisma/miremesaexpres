<div class="d-sidebar h-100 rounded">
    <button class="sidebar-close-btn bg--base text-white"><i class="las la-times"></i></button>

    <div class="sidebar-menu-wrapper" id="sidebar-menu-wrapper">
        <ul class="sidebar-menu">
            <li class="sidebar-menu__header">@lang('Main')</li>
            <li class="sidebar-menu__item {{ menuActive('agent.dashboard') }}">
                <a href="{{ route('agent.dashboard') }}" class="sidebar-menu__link">
                    <i class="lab la-buffer"></i>
                    @lang(' Dashboard')
                </a>
            </li>
            <li class="sidebar-menu__item {{ menuActive('agent.transaction.history') }}">
                <a href="{{ route('agent.transaction.history') }}" class="sidebar-menu__link">
                    <i class="la la-exchange"></i>
                    @lang('Transaction History')
                </a>
            </li>

            <li class="sidebar-menu__header">@lang('Send Money')</li>
            <li class="sidebar-menu__item {{ menuActive('agent.send.money') }}">
                <a href="{{ route('agent.send.money') }}" class="sidebar-menu__link">
                    <i class="la la-comment-dollar"></i>
                    @lang('Send Now')
                </a>
            </li>
            <li class="sidebar-menu__item {{ menuActive('agent.transfer.history') }}">
                <a href="{{ route('agent.transfer.history') }}" class="sidebar-menu__link">
                    <i class="lab la-buffer"></i>
                    @lang('History')
                </a>
            </li>

            <li class="sidebar-menu__header">@lang('Withdraw')</li>
            <li class="sidebar-menu__item {{ menuActive('agent.withdraw') }}">
                <a href="{{ route('agent.withdraw') }}" class="sidebar-menu__link">
                    <i class="las la-university"></i>
                    @lang('Withdraw Money')
                </a>
            </li>
            <li class="sidebar-menu__item {{ menuActive('agent.withdraw.history') }}">
                <a href="{{ route('agent.withdraw.history') }}" class="sidebar-menu__link">
                    <i class="las la-clipboard"></i>
                    @lang('History')
                </a>
            </li>

            <li class="sidebar-menu__header">@lang('Settings')</li>
            <li class="sidebar-menu__item {{ menuActive('agent.profile.setting') }}">
                <a href="{{ route('agent.profile.setting') }}" class="sidebar-menu__link">
                    <i class="las la-user"></i>
                    @lang('Profile Setting')
                </a>
            </li>
            <li class="sidebar-menu__item {{ menuActive('agent.change.password') }}">
                <a href="{{ route('agent.change.password') }}" class="sidebar-menu__link">
                    <i class="las la-cogs"></i>
                    @lang('Password Setting')
                </a>
            </li>
            <li class="sidebar-menu__item {{ menuActive('agent.twofactor') }}">
                <a href="{{ route('agent.twofactor') }}" class="sidebar-menu__link">
                    <i class="las la-key"></i>
                    @lang('2FA Security')
                </a>
            </li>

            <li class="sidebar-menu__item">
                <a href="{{ route('agent.logout') }}" class="sidebar-menu__link">
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
        (function($) {
            const sidebar = document.querySelector('.d-sidebar');
            const sidebarOpenBtn = document.querySelector('.sidebar-open-btn');
            const sidebarCloseBtn = document.querySelector('.sidebar-close-btn');

            sidebarOpenBtn.addEventListener('click', function() {
                sidebar.classList.add('active');
            });
            sidebarCloseBtn.addEventListener('click', function() {
                sidebar.classList.remove('active');
            });


            $(function() {
                $('#sidebar-menu-wrapper').slimScroll({
                    // height: 'calc(100vh - 52px)'
                    height: '100vh'
                });
            });

            $('.sidebar-dropdown > a').on('click', function() {
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
