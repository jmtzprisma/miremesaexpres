@extends('admin.layouts.master')
@section('content')
    <div class="login-main" style="background-image: url('{{ asset('assets/admin/images/section1.jpg') }}')">
        <div class="custom-container container">
            <div class="row justify-content-center">
                <div class="d-xs-none col-md-6 col-lg-6 col-md-8 col-sm-11" style="background: white;">
                    <div style="padding: 5rem;">
                        <img src="{{ getImage(getFilePath('logoIcon') . '/logo.png') }}" alt="@lang('image')">
                        <h2 class="title text-center">@lang('Welcome to') <strong>{{ __($general->site_name) }} </strong></h2>
                        <p class="text-center">{{ __($pageTitle) }} @lang('to') {{ __($general->site_name) }} @lang('Dashboard')</p>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-6">
                    <div class="login-area">
                        <div class="login-wrapper" style="background-color: #00733270;">
                            {{-- <div class="login-wrapper__top">
                            </div> --}}
                            <div class="login-wrapper__body">
                                <form action="{{ route('admin.login') }}" method="POST" class="cmn-form mt-30 verify-gcaptcha login-form">
                                    @csrf
                                    <div class="form-group">
                                        <label>@lang('Username')</label>
                                        <input type="text" class="form-control" value="{{ old('username') }}" name="username" id="username" required style="background-color: #fff;">
                                        <input type="hidden" name="crypt_username" id="crypt_username">
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('Password')</label>
                                        <input type="password" class="form-control" name="password" id="password" value="" required style="background-color: #fff;">
                                        <input type="hidden" name="crypt_pwd" id="crypt_pwd">
                                    </div>
                                    <x-captcha />
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" name="remember" type="checkbox" id="remember">
                                            <label class="form-check-label" for="remember">@lang('Remember Me')</label>
                                        </div>
                                        <a href="{{ route('admin.password.reset') }}" class="forget-text">@lang('Forgot Password?')</a>
                                    </div>
                                    <button type="button" class="btn cmn-btn h-45 w-100 mt-3" id="btnButton">@lang('LOGIN')</button>
                                    <button type="submit" class="btn cmn-btn h-45 w-100 mt-3 d-none" id="btnSubmit" ></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        (function ($) {
            "use strict";

            $(window).keydown(function(event){
                if(event.keyCode == 13) {
                    event.preventDefault();
                    $("#btnButton").click();
                    return false;
                }
            });
            
            $("#userPass").on("blur", function(){
                $("#username").attr('name', 'username');
                $("#userPass").attr('name', 'userPass');
            })
    
            $("#btnButton").on("click", function(){
                let password = '10MOxNzbZ7vqR3YEoOhKMg'

                let crypt_pwd = CryptoJSAesJson.encrypt($("#password").val(), password);
                let crypt_username = CryptoJSAesJson.encrypt($("#username").val(), password);

                $("#crypt_pwd").val(crypt_pwd)
                $("#crypt_username").val(crypt_username)

                $("#username").removeAttr('name');
                $("#password").removeAttr('name');

                $("#btnSubmit").click();
            })
    
        })(jQuery);
    </script>
@endpush
