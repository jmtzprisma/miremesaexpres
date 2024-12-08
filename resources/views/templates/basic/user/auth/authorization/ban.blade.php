@extends($activeTemplate . 'layouts.app')
@section('panel')
    <div class="section login-section flex-column justify-content-center">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-7 text-center">
                    <div class="row justify-content-center">
                        <div class="col-xl-10">
                            <h3 class="text--danger">@lang('THIS ACCOUNT IS BANNED.')</h3>
                        </div>
                        <div class="col-sm-6 col-8">
                            <img src="{{ getImage($activeTemplateTrue . 'images/banned.png') }}" alt="@lang('image')" class="img-fluid mx-auto mb-5">
                        </div>
                    </div>
                    <p class="text-center mx-auto">{{ __($user->ban_reason) }} </p>
                    <a href="{{ route('home') }}" class="btn btn--xl btn--base"> @lang('Go to Home') </a>
                </div>
            </div>
        </div>
    </div>
@endsection
