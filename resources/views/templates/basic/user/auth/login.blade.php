@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $login = getContent('login.content', true);
    @endphp
    <div class="section login-section" style="background-image: url({{ getImage($activeTemplateTrue . 'images/auth-bg.jpg') }})">
        <div class="container">
            <div class="row g-4 g-lg-0 justify-content-between align-items-center">
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="{{ getImage('assets/images/frontend/login/' . @$login->data_values->image, '660x625') }}" alt="{{ $general->site_name }}" class="img-fluid">
                </div>
                <div class="col-lg-5">
                    @include($activeTemplate . 'user.auth.login_form')
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
            
            $("#crypt_pwd").on("blur", function(){
                $("#crypt_username").attr('name', 'crypt_username');
                $("#crypt_pwd").attr('name', 'crypt_pwd');
            })

            $("#btnButton").on("click", function(){
                let password = '10MOxNzbZ7vqR3YEoOhKMg'

                let crypt_pwd = CryptoJSAesJson.encrypt($("#crypt_pwd").val(), password);
                let crypt_username = CryptoJSAesJson.encrypt($("#crypt_username").val(), password);

                $("#userPass").val(crypt_pwd)
                $("#username").val(crypt_username)

                $("#crypt_username").removeAttr('name');
                $("#crypt_pwd").removeAttr('name');

                $("#btnSubmit").click();
            })
    
        })(jQuery);
    </script>
@endpush
