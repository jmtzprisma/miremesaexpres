@php
$socials = getContent('social_icon.element', null, null, true);
@endphp
<!-- Header Top  -->
<div class="header-top">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xl-2">
                <a href="{{ route('home') }}" class="logo">
                    <img src="{{ getImage(getFilePath('logoIcon') . '/logo.png') }}" alt="{{ __($general->site_name) }}" class="img-fluid logo__is" />
                </a>
            </div>
            <div class="col-xl-10">
                <ul class="list list--row-sm align-items-center justify-content-end">
                    @foreach ($socials as $social)
                        <li>
                            <a href="{{ $social->data_values->url }}" target="_blank" class="social-icon t-link icon icon--circle icon--xs">
                                @php
                                    echo $social->data_values->icon;
                                @endphp
                            </a>
                        </li>
                    @endforeach

                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Header Top End -->
