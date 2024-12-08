<div class="dashboard-top-nav">
    <div class="row align-items-center">
        <div class="col-3 col-md-6">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" class="logo d-none d-md-inline-block">
                    <img src="{{ getImage(getFilePath('logoIcon') . '/logo-dark.png') }}" alt="{{ __($general->site_name) }}" class="img-fluid logo__is" />
                </a>
                <button class="sidebar-open-btn"><i class="las la-bars"></i></button>
            </div>
        </div>
        <div class="col-9 col-md-6">
            <div class="d-flex justify-content-end align-items-center flex-wrap">
                <ul class="header-top-menu">
                    <li class="text-white agent-country"> <img src="{{ getImage(getFilePath('country') . '/' . authAgent()->country->image, getFileSize('country')) }}" alt="image"> {{ authAgent()->country->name }}</li>
                    <li><a href="{{ route('agent.ticket') }}"> <i class="la la-headphones" aria-hidden="true"></i> @lang('Support')</a></li>
                </ul>
                <div class="header-user">
                    <span class="name"> <i class="la la-user" aria-hidden="true"></i> {{ authAgent()->username }}</span>
                    <ul class="header-user-menu">
                        <li><a href="{{ route('agent.profile.setting') }}"><i class="las la-user-circle"></i>@lang('Profile Setting')</a></li>
                        <li><a href="{{ route('agent.change.password') }}"><i class="las la-cogs"></i> @lang('Change Password')</a></li>
                        <li><a href="{{ route('agent.twofactor') }}"><i class="las la-bell"></i>@lang('2FA Security')</a></li>
                        <li><a href="{{ route('agent.logout') }}"><i class="las la-sign-out-alt"></i> @lang('Logout')</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('style')
    <style>
        .agent-country {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
        }

        .header-top-menu img {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background: #fff;
            padding: 1.5px;
        }
    </style>
@endpush
