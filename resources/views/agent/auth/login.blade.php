@extends('agent.layouts.master')
@section('content')
    <div class="login-main" style="background-image: url('{{ asset('assets/agent/images/login.jpg') }}')">
        <div class="custom-container container">
            <div class="row justify-content-center">
                <div class="d-xs-none col-md-6 col-lg-6 col-md-8 col-sm-11" style="background: white;">
                    <div style="padding: 5rem;">
                        <img src="{{ getImage(getFilePath('logoIcon') . '/logo.png') }}" alt="@lang('image')">
                        <h2 class="title text-center">@lang('Welcome to') <strong>{{ __($general->site_name) }} </strong></h2>
                        <p class="text-center">{{ __($pageTitle) }}</p>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-6">
                    <div class="login-area">
                        <div class="login-wrapper" style="background-color: #00733270;">
                            <div class="login-wrapper__body">
                                <form action="{{ route('agent.login') }}" method="POST" class="cmn-form mt-30 verify-gcaptcha login-form">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>@lang('Username')</label>
                                                <input type="text" class="form--control" value="{{ old('username') }}" name="username" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>@lang('Password')</label>
                                                <input type="password" class="form--control" name="password" value="" required>
                                            </div>
                                        </div>
                                        <x-captcha />
                                    </div>
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" name="remember" type="checkbox" id="remember">
                                            <label class="form-check-label" for="remember">@lang('Remember Me')</label>
                                        </div>
                                        <a href="{{ route('agent.password.reset') }}" class="forget-text">@lang('Forgot Password?')</a>
                                    </div>
                                    <button type="submit" class="btn cmn-btn w-100 mt-3">@lang('LOGIN')</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
