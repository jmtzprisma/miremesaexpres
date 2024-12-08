@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $contact = getContent('contact.content', true);
        $socials = getContent('social_icon.element', false, null, true);
    @endphp

    <!-- Map Section  -->
    <div class="section">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-6 col-xl-5">

                    <div class="bg--light">
                        <form action="" method="POST" class="verify-gcaptcha row g-3 g-sm-4 login__form">
                            @csrf
                            <div class="col-12">
                                <h4 class="mt-0">{{ __($contact->data_values->title) }}</h4>
                                <p class="mb-0">
                                    {{ __($contact->data_values->description) }}
                                </p>
                            </div>

                            @guest
                                <div class="col-12">
                                    <label class="d-block sm-text mb-2">@lang('Name')</label>
                                    <input name="name" type="text" class="form-control form--control" value="{{ old('name') }}" required>
                                </div>

                                <div class="col-12">
                                    <label class="d-block sm-text mb-2">@lang('Email')</label>
                                    <input name="email" type="email" class="form-control form--control" value="{{ old('email') }}" required>
                                </div>
                            @else
                                <div class="col-12">
                                    <label class="d-block sm-text mb-2">@lang('Name')</label>
                                    <input class="form-control form--control" value="{{ auth()->user()->fullname }}" disabled>
                                </div>

                                <div class="col-12">
                                    <label class="d-block sm-text mb-2">@lang('Email')</label>
                                    <input class="form-control form--control" value="{{ auth()->user()->email }}" disabled>
                                </div>
                            @endguest
                            <div class="col-12">
                                <label class="d-block sm-text mb-2">@lang('Subject')</label>
                                <input name="subject" type="text" class="form-control form--control" value="{{ old('subject') }}" required>
                            </div>

                            <div class="col-12">
                                <label class="d-block sm-text mb-2">@lang('Message')</label>
                                <textarea name="message" wrap="off" class="form-control form--control-textarea" required>{{ old('message') }}</textarea>
                            </div>

                            <x-captcha class="d-block sm-text" />
                            <div class="col-12">
                                <button class="btn btn--xl btn--base"> @lang('Send Message') </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6 col-xl-5">
                    <div class="d-flex flex-column gap-5">
                        <img src="{{ getImage('assets/images/frontend/contact/' . @$contact->data_values->image, '525x395') }}" alt="" class="img-fluid d-none d-lg-block">
                        <ul class="list list--column">
                            <li class="list--column__item">
                                <div class="header-top__info">
                                    <span class="header-top__icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </span>
                                    <span class="header-top__text t-short-para"> {{ __($contact->data_values->address) }}</span>
                                </div>
                            </li>
                            <li class="list--column__item">
                                <div class="header-top__info">
                                    <span class="header-top__icon">
                                        <i class="fas fa-phone-alt"></i>
                                    </span>
                                    <span class="header-top__text t-short-para"> {{ __($contact->data_values->mobile) }}</span>
                                </div>
                            </li>
                            <li class="list--column__item">
                                <div class="header-top__info">
                                    <span class="header-top__icon">
                                        <i class="far fa-envelope"></i>
                                    </span>
                                    <span class="header-top__text t-short-para"> {{ __($contact->data_values->email) }}</span>
                                </div>
                            </li>
                            <li class="list--column__item">
                                <ul class="list list--row-sm align-items-center">
                                    @foreach ($socials as $social)
                                        <li>
                                            <a href="{{ $social->data_values->url }}" target="_blank" class="social-icon">
                                                @php
                                                    echo $social->data_values->icon;
                                                @endphp
                                            </a>
                                        </li>
                                    @endforeach

                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif

    <!-- Map Section End -->
    <div class="map-section">
        <div class="container-fluid p-0">
            <div class="row g-0">
                <div class="col-12">
                    <iframe class="map" src="https://maps.google.com/maps?q={{ $contact->data_values->latitude }},{{ $contact->data_values->longitude }}&hl=es&z=14&amp;output=embed"></iframe>
                </div>
            </div>
        </div>
    </div>



@endsection
