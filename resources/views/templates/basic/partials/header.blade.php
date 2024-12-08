@php
    if (request()->routeIs('home')) {
        $class = 'header--primary';
    } else {
        $class = 'header--secondary';
    }
    $pages = App\Models\Page::where('tempname', $activeTemplate)
        ->where('is_default', Status::NO)
        ->get();
@endphp
<!-- Header -->
<header class="header-fixed {{ $class }}">
<!-- Navbar-->
    
  <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            @if ($class == 'header--primary')
                <a class="logo logo-dark" href="{{ route('home') }}">
                    <img alt="{{ __($general->site_name) }}" class="img-fluid logo__is" src="{{ getImage(getFilepath('logoIcon') . '/logo-dark.png') }}" />
                </a>
            @endif

            <a class="logo logo-light" href="{{ route('home') }}">
                <img alt="{{ __($general->site_name) }}" class="img-fluid logo__is" src="{{ getImage(getFilepath('logoIcon') . '/logo.png') }}" />
            </a>
            <button aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation" class="navbar-toggler" data-bs-target="#navbarSupportedContent" data-bs-toggle="collapse" type="button">
                <span class="menu-toggle"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-lg-0 align-items-lg-center mb-2">
                    <li class="nav-item">
                        <a class="primary-menu__link" href="{{ route('home') }}">@lang('Home')</a>
                    </li>
                    @foreach ($pages as $k => $data)
                        <li class="nav-item"><a class="primary-menu__link" href="{{ route('pages', [$data->slug]) }}">{{ __($data->name) }}</a></li>
                    @endforeach
                    <li class="nav-item">
                        <a class="primary-menu__link" href="{{ route('blog') }}">@lang('Blog')</a>
                    </li>
                    <li class="nav-item">
                        <a class="primary-menu__link" href="{{ route('contact') }}">@lang('Contact')</a>
                    </li>
                    @guest
                        <li class="nav-item pt-lg-0 pb-lg-0 pt-10 pb-10">
                            <a class="btn btn--md btn--base fixed-width" href="{{ route('user.login') }}"> @lang('Login')
                            </a>
                        </li>
                    @endguest
                    @auth
                        <li class="nav-item me-3 me-lg-1">
                            <a class="nav-link" href="{{ route('user.home') }}">
                                <span><i class="fa fa-tachometer-alt fa-lg fa-inverse"></i></span>
                            </a>
                        </li> 
                        <li class="nav-item me-3 me-lg-1">
                            <a class="nav-link" href="{{ route('user.logout') }}">
                                <span><i class="fa fa-sign-out-alt fa-lg fa-inverse"></i></span>
                            </a>
                        </li> 
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</header>
<!-- Header End -->
