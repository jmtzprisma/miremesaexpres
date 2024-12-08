<!-- Header -->
<header class="header-fixed header--secondary">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="logo logo-light" href="{{ route('home') }}">
                <img alt="{{ __($general->site_name) }}" class="img-fluid logo__is" src="{{ getImage(getFilepath('logoIcon') . '/logo.png') }}" />
            </a>
            <button aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation" class="navbar-toggler" data-bs-target="#navbarSupportedContent" data-bs-toggle="collapse" type="button">
                <span class="menu-toggle"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-lg-0 align-items-lg-center mb-2">
                    <li class="nav-item">
                        @if (auth()->user()->kv != 1)
                            @if(is_null(auth()->user()->video_id))
                            @if (auth()->user()->kv == 0)
                            <a class="btn btn--md btn--base" href="{{ route('user.kyc.asking') }}">@lang('Send Now')</a>
                            @elseif(auth()->user()->kv == 2)
                            <a class="btn btn--md btn--base" href="{{ route('user.kyc.data') }}">@lang('Send Now')</a>
                            @endif
                            @else
                            <a class="btn btn--md btn--base" href="{{ route('user.send.money.now') }}">@lang('Send Now')</a>
                            @endif
                        @else
                            <a class="btn btn--md btn--base" href="{{ route('user.send.money.now') }}">@lang('Send Now')</a>
                        @endif
                    </li>
                    <li class="nav-item">
                        <a class="primary-menu__link" href="{{ route('user.home') }}">@lang('Dashboard')</a>
                    </li>
                    <li class="nav-item primary-menu__list has-sub">
                        <a class="primary-menu__link" href="javascript:void(0)">@lang('Send Money')</a>
                        <ul class="primary-menu__sub">
                            <li class="primary-menu__sub-list">
                                @if (auth()->user()->kv != 1)
                                    @if(is_null(auth()->user()->video_id))
                                    @if (auth()->user()->kv == 0)
                                    <a class="t-link primary-menu__sub-link" href="{{ route('user.kyc.asking') }}">@lang('Send Now')</a>
                                    @elseif(auth()->user()->kv == 2)
                                    <a class="t-link primary-menu__sub-link" href="{{ route('user.kyc.data') }}">@lang('Send Now')</a>
                                    @endif
                                    @else
                                    <a class="t-link primary-menu__sub-link" href="{{ route('user.send.money.now') }}">@lang('Send Now')</a>
                                    @endif
                                @else
                                    <a class="t-link primary-menu__sub-link" href="{{ route('user.send.money.now') }}">@lang('Send Now')</a>
                                @endif
                            </li>
                            <li class="primary-menu__sub-list">
                                <a class="t-link primary-menu__sub-link" href="{{ route('user.send.money.history') }}">
                                    @lang('View History')
                                </a>
                            </li>
                            <li class="primary-menu__sub-list">
                                <a class="t-link primary-menu__sub-link" href="{{ route('user.send.money.destinatarios') }}">
                                    @lang('Destinatarios')
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item primary-menu__list has-sub">
                        <a class="primary-menu__link" href="javascript:void(0)">@lang('Support Ticket')</a>
                        <ul class="primary-menu__sub">
                            <li class="primary-menu__sub-list">
                                <a class="t-link primary-menu__sub-link" href="{{ route('ticket.open') }}">
                                    @lang('Open Now')
                                </a>
                            </li>
                            <li class="primary-menu__sub-list">
                                <a class="t-link primary-menu__sub-link" href="{{ route('ticket.index') }}">
                                    @lang('All Tickets')
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item primary-menu__list has-sub">
                        <a class="primary-menu__link" href="javascript:void(0)">{{ auth()->user()->username }}</a>
                        <ul class="primary-menu__sub">
                            <li class="primary-menu__sub-list">
                                <a class="t-link primary-menu__sub-link" href="{{ route('user.transactions') }}">
                                    @lang('Transactions')
                                </a>
                            </li>
                            <li class="primary-menu__sub-list">
                                <a class="t-link primary-menu__sub-link" href="{{ route('user.change.password') }}">
                                    @lang('Change Password')
                                </a>
                            </li>
                            <li class="primary-menu__sub-list">
                                <a class="t-link primary-menu__sub-link" href="{{ route('user.profile.setting') }}">
                                    @lang('Profile Setting')
                                </a>
                            </li>
                            <li class="primary-menu__sub-list">
                                <a class="t-link primary-menu__sub-link" href="{{ route('user.twofactor') }}">
                                    @lang('2FA Security')
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item pt-lg-0 pb-lg-0 pt-10 pb-10">
                        <a class="btn btn--md btn--base fixed-width" href="{{ route('user.logout') }}" style="width: 150px;"> @lang('Logout')
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
</header>
<!-- Header End -->
