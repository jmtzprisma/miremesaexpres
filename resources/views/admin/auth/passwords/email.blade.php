@extends('admin.layouts.master')
@section('content')
    <div class="login-main" style="background-image: url('{{ asset('assets/admin/images/section1.jpg') }}')">
        <div class="container custom-container">
            <div class="row justify-content-center">
                <div class="d-xs-none col-md-6 col-lg-6 col-md-8 col-sm-11" style="background: white;">
                    <div style="padding: 2rem;">
                        <img src="{{ getImage(getFilePath('logoIcon') . '/logo.png') }}" alt="@lang('image')">
                        <h2 class="title text-center">@lang('Welcome to') <strong>{{ __($general->site_name) }} </strong></h2>
                        <p class="text-center">@lang('Recover Account')</p>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-6">
                    <div class="login-area">
                        <div class="login-wrapper" style="background-color: #00733270;">
                            <div class="login-wrapper__body">
                                <form action="{{ route('admin.password.reset') }}" method="POST" class="login-form">
                                    @csrf
                                    <div class="form-group">
                                        <label>@lang('Email')</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                    </div>
                                    <x-captcha />
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <a href="{{ route('admin.login') }}" class="forget-text">@lang('Login Here')</a>
                                    </div>
                                    <button type="submit" class="btn cmn-btn w-100">@lang('Submit')</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
