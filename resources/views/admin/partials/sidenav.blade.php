<?php

    $roles = explode(',', \App\Models\Roles::find(auth('admin')->user()->role_id)->permissions);
?>
<div class="sidebar bg--dark">
    <button class="res-sidebar-close-btn"><i class="las la-times"></i></button>
    <div class="sidebar__inner">
        <div class="sidebar__logo">
            <a href="{{ route('admin.dashboard') }}" class="sidebar__main-logo"><img src="{{ getImage(getFilePath('logoIcon') . '/logo-dark.png') }}" alt="@lang('image')"></a>
        </div>

        <div class="sidebar__menu-wrapper" id="sidebar__menuWrapper">
            <ul class="sidebar__menu">
                {{-- SUPERADMIN --}}
                @if(in_array('1', $roles))
                <li class="sidebar-menu-item {{ menuActive('admin.dashboard') }}">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <i class="menu-icon las la-home"></i>
                        <span class="menu-title">@lang('Dashboard')</span>
                    </a>
                </li>
                <li class="sidebar-menu-item {{ menuActive('admin.adm') }}">
                    <a href="{{ route('admin.adm') }}" class="nav-link">
                        <i class="menu-icon las la-home"></i>
                        <span class="menu-title">@lang('Usuarios ADMIN')</span>
                    </a>
                </li>
                <li class="sidebar-menu-item {{ menuActive('admin.roles') }}">
                    <a href="{{ route('admin.roles') }}" class="nav-link">
                        <i class="menu-icon las la-home"></i>
                        <span class="menu-title">@lang('Roles ADMIN')</span>
                    </a>
                </li>
                @endif

                {{-- ENVIO MANUAL DE DINERO --}}
                @if(in_array('1', $roles) || in_array('9', $roles))
                <li class="sidebar-menu-item {{ menuActive('admin.send.money.send_money_form') }}">
                    <a href="{{ route('admin.send.money.send_money_form') }}" class="nav-link">
                        <i class="menu-icon las la-dot-circle"></i>
                        <span class="menu-title">Envío manual</span>
                    </a>
                </li>
                @endif
                
                {{-- BANCOS --}}
                @if(in_array('1', $roles) || in_array('3', $roles) || in_array('2', $roles))
                <li class="sidebar-menu-item {{ menuActive('admin.bank.list') }}">
                    <a href="{{ route('admin.bank.list') }}" class="nav-link">
                        <i class="menu-icon las la-euro-sign"></i>
                        <span class="menu-title">@lang('Bancos')</span>
                    </a>
                </li>
                @endif

                {{-- BANCOS --}}
                @if(in_array('1', $roles) || in_array('3', $roles) || in_array('2', $roles))
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.cuentas.*', 3) }}">
                        <i class="menu-icon las la-euro-sign"></i>
                        <span class="menu-title">@lang('Cuentas')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.cuentas.*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.cuentas.index.cobrar') }}">
                                <a href="{{ route('admin.cuentas.index.cobrar') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Cuentas por Cobrar')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.cuentas.index.pagar') }}">
                                <a href="{{ route('admin.cuentas.index.pagar') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Cuentas por Pagar')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                {{-- USERS --}}
                @if(in_array('1', $roles) || in_array('5', $roles))
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.users*', 3) }}">
                        <i class="menu-icon las la-users"></i>
                        <span class="menu-title">@lang('Manage Users')</span>
                        @if ($bannedUsersCount > 0 || $emailUnverifiedUsersCount > 0 || $mobileUnverifiedUsersCount > 0 || $kycUnverifiedUsersCount > 0 || $kycPendingUsersCount > 0)
                            <span class="menu-badge pill bg--danger ms-auto">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.users*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.users.active') }}">
                                <a href="{{ route('admin.users.active') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Active Users')</span>
                                </a>
                            </li>
                            
                            <li class="sidebar-menu-item {{ menuActive('admin.users.rejected') }}">
                                <a href="{{ route('admin.users.rejected') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Usuarios rechazados')</span>
                                    @if ($rejectedUsersCount)
                                        <span class="menu-badge pill bg--danger ms-auto">{{ $rejectedUsersCount }}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.users.banned') }}">
                                <a href="{{ route('admin.users.banned') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Banned Users')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.users.email.unverified') }}">
                                <a href="{{ route('admin.users.email.unverified') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Email Unverified')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.users.mobile.unverified') }}">
                                <a href="{{ route('admin.users.mobile.unverified') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Mobile Unverified')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.users.kyc.unverified') }}">
                                <a href="{{ route('admin.users.kyc.unverified') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('KYC Unverified')</span>
                                    @if ($kycUnverifiedUsersCount)
                                        <span class="menu-badge pill bg--danger ms-auto">{{ $kycUnverifiedUsersCount }}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.users.kyc.pending') }}">
                                <a href="{{ route('admin.users.kyc.pending') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('KYC Pending')</span>
                                    @if ($kycPendingUsersCount)
                                        <span class="menu-badge pill bg--danger ms-auto">{{ $kycPendingUsersCount }}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.users.with.balance') }}">
                                <a href="{{ route('admin.users.with.balance') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('With Balance')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.users.all') }}">
                                <a href="{{ route('admin.users.all') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('All Users')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.users.notification.all') }}">
                                <a href="{{ route('admin.users.notification.all') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Notification to All')</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
                @endif

                {{-- AGENTES --}}
                @if(in_array('1', $roles) || in_array('6', $roles))
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.agents*', 3) }}">
                        <i class="menu-icon las la-users"></i>
                        <span class="menu-title">@lang('Manage Agents')</span>

                        @if ($bannedAgentsCount > 0 || $kycUnverifiedAgentsCount > 0 || $kycPendingAgentsCount > 0)
                            <span class="menu-badge pill bg--danger ms-auto">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.agents*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.agents.active') }}">
                                <a href="{{ route('admin.agents.active') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Active Agents')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.agents.banned') }}">
                                <a href="{{ route('admin.agents.banned') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Banned Agents')</span>
                                    @if ($bannedAgentsCount)
                                        <span class="menu-badge pill bg--danger ms-auto">{{ $bannedAgentsCount }}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.agents.kyc.unverified') }}">
                                <a href="{{ route('admin.agents.kyc.unverified') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('KYC Unverified')</span>
                                    @if ($kycUnverifiedAgentsCount)
                                        <span class="menu-badge pill bg--danger ms-auto">{{ $kycUnverifiedAgentsCount }}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.agents.kyc.pending') }}">
                                <a href="{{ route('admin.agents.kyc.pending') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('KYC Pending')</span>
                                    @if ($kycPendingAgentsCount)
                                        <span class="menu-badge pill bg--danger ms-auto">{{ $kycPendingAgentsCount }}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.agents.with.balance') }}">
                                <a href="{{ route('admin.agents.with.balance') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('With Balance')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.agents.all') }}">
                                <a href="{{ route('admin.agents.all') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('All Agents')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.agents.notification.all') }}">
                                <a href="{{ route('admin.agents.notification.all') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Notification to All')</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
                @endif

                {{-- SUPERADMIN --}}
                @if(in_array('1', $roles))
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascrip:void(0)" class="{{ menuActive(['admin.delivery*', 'admin.sof.*', 'admin.sending.purpose.*'], 3) }}">
                        <i class="menu-icon las la-people-carry"></i>
                        <span class="menu-title">@lang('Remittance Options')</span>
                    </a>

                    <div class="sidebar-submenu {{ menuActive(['admin.delivery*', 'admin.sof.*', 'admin.sending.purpose.*'], 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.delivery.*') }}">
                                <a href="{{ route('admin.delivery.method.all') }}" class="nav-link" data-default-url="{{ route('admin.delivery.method.all') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Delivery Methods') </span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.sof.*') }}">
                                <a href="{{ route('admin.sof.index') }}" class="nav-link" data-default-url="{{ route('admin.sof.index') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Source of Fund') </span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.sending.purpose.*') }}">
                                <a href="{{ route('admin.sending.purpose.index') }}" class="nav-link" data-default-url="{{ route('admin.sending.purpose.index') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Sending Purpose') </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive(['admin.country.*', 'admin.service.*'], 3) }}">
                        <i class="menu-icon las la-globe"></i>
                        <span class="menu-title">@lang('Manage Countries')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive(['admin.country.*', 'admin.service.*'], 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.country.*') }}">
                                <a href="{{ route('admin.country.index') }}" class="nav-link" data-default-url="{{ route('admin.country.index') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Countries') </span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.service.*') }}">
                                <a href="{{ route('admin.service.index') }}" class="nav-link" data-default-url="{{ route('admin.service.index') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Services') </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                @if(in_array('1', $roles) || in_array('8', $roles))
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.payment*', 3) }}">
                        <i class="menu-icon las la-hand-holding-usd"></i>
                        <span class="menu-title">@lang('Payments')</span>
                        @if (0 < $pendingPaymentsCount)
                            <span class="menu-badge pill bg--danger ms-auto">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.payment*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.payment.pending') }}">
                                <a href="{{ route('admin.payment.pending') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Pending Payments')</span>
                                    @if ($pendingPaymentsCount)
                                        <span class="menu-badge pill bg--danger ms-auto">{{ $pendingPaymentsCount }}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.payment.approved') }}">
                                <a href="{{ route('admin.payment.approved') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Approved Payments')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.payment.successful') }}">
                                <a href="{{ route('admin.payment.successful') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Successful Payments')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.payment.rejected') }}">
                                <a href="{{ route('admin.payment.rejected') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Rejected Payments')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.payment.initiated') }}">

                                <a href="{{ route('admin.payment.initiated') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Initiated Payments')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.payment.list') }}">
                                <a href="{{ route('admin.payment.list') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('All Payments')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                @if(in_array('1', $roles) || in_array('4', $roles))
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.send.money*', 3) }}">
                        <i class="menu-icon la la-comment-dollar"></i>
                        <span class="menu-title">@lang('Send Money')</span>
                        @if ($shouldPayoutCount)
                            <span class="menu-badge pill bg--danger ms-auto">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.send.money*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.send.money.pending') }}">
                                <a href="{{ route('admin.send.money.pending') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Pending')</span>
                                    @if ($shouldPayoutCount)
                                        <span class="menu-badge pill bg--danger ms-auto">{{ $shouldPayoutCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.send.money.completed') }}">
                                <a href="{{ route('admin.send.money.completed') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Completed')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.send.money.refunded') }}">
                                <a href="{{ route('admin.send.money.refunded') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Refunded')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.send.money.all') }}">
                                <a href="{{ route('admin.send.money.all') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('All')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                {{-- SUPERADMIN --}}
                @if(in_array('1', $roles))
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.gateway*', 3) }}">
                        <i class="menu-icon las la-credit-card"></i>
                        <span class="menu-title">@lang('Payment Gateways')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.gateway*', 2) }}">
                        <ul>

                            <li class="sidebar-menu-item {{ menuActive('admin.gateway.automatic.*') }}">
                                <a href="{{ route('admin.gateway.automatic.index') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Automatic Gateways')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.gateway.manual.*') }}">
                                <a href="{{ route('admin.gateway.manual.index') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Manual Gateways')</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.deposit*', 3) }}">
                        <i class="menu-icon las la-file-invoice-dollar"></i>
                        <span class="menu-title">@lang('Agent\'s Deposits')</span>
                        @if (0 < $pendingDepositsCount)
                            <span class="menu-badge pill bg--danger ms-auto">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.deposit*', 2) }}">
                        <ul>

                            <li class="sidebar-menu-item {{ menuActive('admin.deposit.pending') }}">
                                <a href="{{ route('admin.deposit.pending') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Pending Deposits')</span>
                                    @if ($pendingDepositsCount)
                                        <span class="menu-badge pill bg--danger ms-auto">{{ $pendingDepositsCount }}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.deposit.approved') }}">
                                <a href="{{ route('admin.deposit.approved') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Approved Deposits')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.deposit.successful') }}">
                                <a href="{{ route('admin.deposit.successful') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Successful Deposits')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.deposit.rejected') }}">
                                <a href="{{ route('admin.deposit.rejected') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Rejected Deposits')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.deposit.initiated') }}">

                                <a href="{{ route('admin.deposit.initiated') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Initiated Deposits')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.deposit.list') }}">
                                <a href="{{ route('admin.deposit.list') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('All Deposits')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.withdraw*', 3) }}">
                        <i class="menu-icon la la-bank"></i>
                        <span class="menu-title">@lang('Withdrawals') </span>
                        @if (0 < $pendingWithdrawCount)
                            <span class="menu-badge pill bg--danger ms-auto">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.withdraw*', 2) }}">
                        <ul>

                            <li class="sidebar-menu-item {{ menuActive('admin.withdraw.method.*') }}">
                                <a href="{{ route('admin.withdraw.method.index') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Withdrawal Methods')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.withdraw.pending') }}">
                                <a href="{{ route('admin.withdraw.pending') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Pending Withdrawals')</span>

                                    @if ($pendingWithdrawCount)
                                        <span class="menu-badge pill bg--danger ms-auto">{{ $pendingWithdrawCount }}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.withdraw.approved') }}">
                                <a href="{{ route('admin.withdraw.approved') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Approved Withdrawals')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.withdraw.rejected') }}">
                                <a href="{{ route('admin.withdraw.rejected') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Rejected Withdrawals')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.withdraw.log') }}">
                                <a href="{{ route('admin.withdraw.log') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('All Withdrawals')</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.ticket*', 3) }}">
                        <i class="menu-icon la la-ticket"></i>
                        <span class="menu-title">@lang('Support Ticket') </span>
                        @if (0 < $pendingTicketCount)
                            <span class="menu-badge pill bg--danger ms-auto">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.ticket*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.ticket.pending') }}">
                                <a href="{{ route('admin.ticket.pending') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Pending Ticket')</span>
                                    @if ($pendingTicketCount)
                                        <span class="menu-badge pill bg--danger ms-auto">{{ $pendingTicketCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.ticket.closed') }}">
                                <a href="{{ route('admin.ticket.closed') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Closed Ticket')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.ticket.answered') }}">
                                <a href="{{ route('admin.ticket.answered') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Answered Ticket')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.ticket.guest') }}">
                                <a href="{{ route('admin.ticket.guest') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Guest Ticket')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.ticket.user') }}">
                                <a href="{{ route('admin.ticket.user') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('User Ticket')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.ticket.agent') }}">
                                <a href="{{ route('admin.ticket.agent') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Agent Ticket')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.ticket.index') }}">
                                <a href="{{ route('admin.ticket.index') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('All Ticket')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                @if(in_array('1', $roles) || in_array('7', $roles)){{-- REPORTES --}}
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.report*', 3) }}">
                        <i class="menu-icon la la-list"></i>
                        <span class="menu-title">@lang('Report') </span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.report*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive(['admin.report.transaction', 'admin.report.transaction.search']) }}">
                                <a href="{{ route('admin.report.transaction') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Transaction Log')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive(['admin.report.login.history', 'admin.report.login.ipHistory']) }}">
                                <a href="{{ route('admin.report.login.history') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Login History')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.report.notification.history') }}">
                                <a href="{{ route('admin.report.notification.history') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Notification History')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.report.balance_gral') }}">
                                <a href="{{ route('admin.report.balance_gral') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Balance General')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.report.balance_gral_movimientos') }}">
                                <a href="{{ route('admin.report.balance_gral_movimientos') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Balance General de Movimientos')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.report.control_gastos') }}">
                                <a href="{{ route('admin.report.control_gastos') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Control de gastos')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.report.datos_bancos') }}">
                                <a href="{{ route('admin.report.datos_bancos') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Reporte diario')</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
                @endif

                {{-- SUPERADMIN --}}
                @if(in_array('1', $roles))
                <li class="sidebar__menu-header">@lang('Settings')</li>

                <li class="sidebar-menu-item {{ menuActive('admin.setting.index') }}">
                    <a href="{{ route('admin.setting.index') }}" class="nav-link">
                        <i class="menu-icon las la-life-ring"></i>
                        <span class="menu-title">@lang('General Setting')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item {{ menuActive('admin.setting.system.configuration') }}">
                    <a href="{{ route('admin.setting.system.configuration') }}" class="nav-link">
                        <i class="menu-icon las la-cog"></i>
                        <span class="menu-title">@lang('System Configuration')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item {{ menuActive('admin.setting.logo.icon') }}">
                    <a href="{{ route('admin.setting.logo.icon') }}" class="nav-link">
                        <i class="menu-icon las la-images"></i>
                        <span class="menu-title">@lang('Logo & Favicon')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item {{ menuActive('admin.extensions.index') }}">
                    <a href="{{ route('admin.extensions.index') }}" class="nav-link">
                        <i class="menu-icon las la-cogs"></i>
                        <span class="menu-title">@lang('Extensions')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item {{ menuActive('admin.seo') }}">
                    <a href="{{ route('admin.seo') }}" class="nav-link">
                        <i class="menu-icon las la-globe"></i>
                        <span class="menu-title">@lang('SEO Manager')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.kyc.*', 3) }}">
                        <i class="menu-icon las la-user-check"></i>
                        <span class="menu-title">@lang('KYC Setting')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.kyc*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.kyc.setting.agent') }}">
                                <a href="{{ route('admin.kyc.setting.agent') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Agent KYC Form')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.kyc.setting.user') }}">
                                <a href="{{ route('admin.kyc.setting.user') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('User KYC Form')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.kyc.setting.module') }}">
                                <a href="{{ route('admin.kyc.setting.module') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('KYC Modules')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.setting.notification*', 3) }}">
                        <i class="menu-icon las la-bell"></i>
                        <span class="menu-title">@lang('Notification Setting')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.setting.notification*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.setting.notification.global') }}">
                                <a href="{{ route('admin.setting.notification.global') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Global Template')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.setting.notification.email') }}">
                                <a href="{{ route('admin.setting.notification.email') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Email Setting')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.setting.notification.sms') }}">
                                <a href="{{ route('admin.setting.notification.sms') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('SMS Setting')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.setting.notification.templates') }}">
                                <a href="{{ route('admin.setting.notification.templates') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Notification Templates')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar__menu-header">@lang('Frontend Manager')</li>

                <li class="sidebar-menu-item {{ menuActive('admin.frontend.manage.*') }}">
                    <a href="{{ route('admin.frontend.manage.pages') }}" class="nav-link">
                        <i class="menu-icon la la-list"></i>
                        <span class="menu-title">@lang('Manage Pages')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive('admin.frontend.sections*', 3) }}">
                        <i class="menu-icon la la-puzzle-piece"></i>
                        <span class="menu-title">@lang('Manage Section')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive('admin.frontend.sections*', 2) }}">
                        <ul>
                            @php
                                $lastSegment = collect(request()->segments())->last();
                            @endphp
                            @foreach (getPageSections(true) as $k => $secs)
                                @if ($secs['builder'])
                                    <li class="sidebar-menu-item @if ($lastSegment == $k) active @endif">
                                        <a href="{{ route('admin.frontend.sections', $k) }}" class="nav-link">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">{{ __($secs['name']) }}</span>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </li>

                <li class="sidebar__menu-header">@lang('Extra')</li>

                <li class="sidebar-menu-item {{ menuActive('admin.maintenance.mode') }}">
                    <a href="{{ route('admin.maintenance.mode') }}" class="nav-link">
                        <i class="menu-icon las la-robot"></i>
                        <span class="menu-title">@lang('Maintenance Mode')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item {{ menuActive('admin.setting.cookie') }}">
                    <a href="{{ route('admin.setting.cookie') }}" class="nav-link">
                        <i class="menu-icon las la-cookie-bite"></i>
                        <span class="menu-title">@lang('GDPR Cookie')</span>
                    </a>
                </li>

                @endif
            </ul>
            <div class="text-uppercase mb-3 text-center">
                <span class="text--primary">{{ __(systemDetails()['name']) }}</span>
                <span class="text--success">@lang('V'){{ systemDetails()['version'] }} </span>
            </div>
        </div>
    </div>
</div>
<!-- sidebar end -->

@push('script')
    <script>
        if ($('li').hasClass('active')) {
            $('#sidebar__menuWrapper').animate({
                scrollTop: eval($(".active").offset().top - 320)
            }, 500);
        }
    </script>
@endpush
