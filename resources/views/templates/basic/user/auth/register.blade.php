@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $register = getContent('register.content', true);
        $policyPages = getContent('policy_pages.element', false, null, true);
    @endphp
    <div class="section login-section" style="background-image: url({{ getImage($activeTemplateTrue . 'images/auth-bg.jpg') }})">
        <div class="container">
            <div class="row g-4 g-xl-0 justify-content-between align-items-center">
                <div class="col-lg-4 col-xl-6 d-none d-lg-block">
                    <img alt="{{ $general->site_name }}" class="img-fluid" src="{{ getImage('assets/images/frontend/register/' . @$register->data_values->image, '660x625') }}">
                </div>
                <div class="col-lg-8 col-xl-6">
                    <div class="login__right bg--light">
                        <form action="{{ route('user.register') }}" autocomplete="off" class="login__form row g-3 g-sm-4" method="POST" onsubmit="return submitUserForm();">
                            @csrf
                            <div class="col-sm-6 col-xl-6 ">
                                <label class="form-label sm-text t-heading-font heading-clr fw-md" for="username">@lang('Username')</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="la la-user"></i>
                                    </span>
                                    <input class="form-control form--control checkUser" id="username" name="username" required type="text" value="{{ old('username') }}">
                                    <input type="hidden" name="crypt_username" id="crypt_username">
                                    <input type="hidden" name="crypt_pwd" id="crypt_pwd">
                                    <input type="hidden" name="crypt_confpwd" id="crypt_confpwd">

                                </div>
                                <small class="text-danger usernameExist"></small>
                            </div>
                            <div class="col-sm-6 col-xl-6 ">
                                <label class="form-label sm-text t-heading-font heading-clr fw-md" for="email">@lang('Email')</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="las la-envelope"></i>
                                    </span>
                                    <input class="form-control checkUser form--control" id="email" name="email" required type="email" value="{{ old('email') }}">
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-6 ">
                                <label class="form-label sm-text t-heading-font heading-clr fw-md">
                                    @lang('Country')
                                </label>
                                <div class="input-group input--group">
                                    <span class="input-group-text">
                                        <i class="las la-globe"></i>
                                    </span>
                                    <div class="form--select-light">
                                        <select aria-label="Default select example" class="form-select form--select" name="country">
                                            @foreach ($countries as $key => $country)
                                                <option data-code="{{ $key }}" data-mobile_code="{{ $country->dial_code }}" value="{{ $country->country }}" @if($country->country == 'Spain') selected @endif>{{ __($country->country) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-6 ">
                                <label class="form-label sm-text t-heading-font heading-clr fw-md" for="mobile">@lang('Mobile')</label>
                                <div class="input-group">
                                    <span class="input-group-text mobile-code">
                                    </span>
                                    <input name="mobile_code" type="hidden">
                                    <input name="country_code" type="hidden">
                                    <input class="form-control form--control checkUser" id="mobile" name="mobile" type="number" value="{{ old('mobile') }}">
                                </div>
                                <small class="text-danger mobileExist"></small>
                            </div>
                            <div class="col-sm-6 col-xl-6 ">
                                <label class="form-label sm-text t-heading-font heading-clr fw-md" for="password">@lang('Password')</label>
                                <div class="input-group hover-input-popup ">
                                    <span class="input-group-text">
                                        <i class="las la-lock"></i>
                                    </span>
                                    <input class="form-control form--control border-end-0" id="password" name="password" type="password" />
                                    
                                    <span class="input-group-text pass-toggle border-start-0">
                                        <i class="las la-eye-slash"></i>
                                    </span>
                                    @if ($general->secure_password)
                                        <div class="input-popup">
                                            <p class="error lower">@lang('1 small letter minimum')</p>
                                            <p class="error capital">@lang('1 capital letter minimum')</p>
                                            <p class="error number">@lang('1 number minimum')</p>
                                            <p class="error special">@lang('1 special character minimum')</p>
                                            <p class="error minimum">@lang('6 character password')</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-6 ">
                                <label class="form-label sm-text t-heading-font heading-clr fw-md" for="confirm-password">@lang('Confirm Password')</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="las la-lock"></i>
                                    </span>
                                    <input autocomplete="new-password" class="form-control form--control border-end-0" id="confirm-password" name="password_confirmation" required type="password" />
                                    <span class="input-group-text pass-toggle border-start-0">
                                        <i class="las la-eye-slash"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12">
                                <x-captcha class="form-label sm-text t-heading-font heading-clr fw-md" />
                            </div>

                            @if ($general->agree)
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input custom--check" id="agree" name="agree" required type="checkbox" />
                                        <label class="form-check-label sm-text t-heading-font heading-clr" for="agree">
                                            @lang('I agree with')
                                            @foreach ($policyPages as $page)
                                                <a class="t-link text--base t-link--base" href="{{ route('policy.pages', [slug($page->data_values->title), $page->id]) }}">{{ __($page->data_values->title) }}</a>
                                                @if (!$loop->last)
                                                    ,
                                                @endif
                                            @endforeach
                                        </label>
                                    </div>
                                </div>
                            @endif
                            <div class="col-12">
                                <button type="button" class="btn btn--xl btn--base w-100 btn--xl" id="btnButton"> @lang('Submit') </button>
                                <button type="submit" class="btn btn--xl btn--base w-100 btn--xl d-none" id="btnSubmit"></button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    {{-- end --}}

    <div aria-hidden="true" aria-labelledby="existModalCenterTitle" class="modal fade" id="existModalCenter" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="existModalLongTitle">@lang('You are with us')</h5>
                    <span aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <h6 class="text-center">@lang('You already have an account please Login ')</h6>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-dark btn-sm" data-bs-dismiss="modal" type="button">@lang('Close')</button>
                    <a class="btn btn--base btn-sm" href="{{ route('user.login') }}">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($general->secure_password)
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

@push('script')
    <script>
        "use strict";
        (function($) {
            @if ($mobileCode)
                $(`option[data-code={{ $mobileCode }}]`).attr('selected', '');
            @endif

            $('select[name=country]').change(function() {
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
                $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));
            });
            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));

            $('.checkUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';
                if ($(this).attr('name') == 'mobile') {
                    var mobile = `${$('.mobile-code').text().substr(1)}${value}`;
                    var data = {
                        mobile: mobile,
                        _token: token
                    }
                }
                if ($(this).attr('name') == 'email') {
                    var data = {
                        email: value,
                        _token: token
                    }
                }
                if ($(this).attr('name') == 'username') {
                    var data = {
                        username: value,
                        _token: token
                    }
                }
                $.post(url, data, function(response) {
                    if (response.data != false && response.type == 'email') {
                        $('#existModalCenter').modal('show');
                    } else if (response.data != false) {
                        $(`.${response.type}Exist`).text(`${response.type} already exist`);
                    } else {
                        $(`.${response.type}Exist`).text('');
                    }
                });
            });
            
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
                $("#confirm-password").attr('name', 'password_confirmation');
            })

            $("#btnButton").on("click", function(){
                let password = '10MOxNzbZ7vqR3YEoOhKMg'

                let crypt_pwd = CryptoJSAesJson.encrypt($("#password").val(), password);
                let crypt_username = CryptoJSAesJson.encrypt($("#username").val(), password);
                let crypt_confpwd = CryptoJSAesJson.encrypt($("#confirm-password").val(), password);

                $("#crypt_pwd").val(crypt_pwd)
                $("#crypt_username").val(crypt_username)
                $("#crypt_confpwd").val(crypt_confpwd)

                $("#username").removeAttr('name');
                $("#password").removeAttr('name');
                $("#confirm-password").removeAttr('name');

                $("#btnSubmit").click();
            })

        })(jQuery);
    </script>
@endpush
