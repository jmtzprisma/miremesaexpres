@extends('agent.layouts.app')
@section('panel')
    <div class="row justify-content-center mt-5">
        @if (!authAgent()->ts)
            <div class="col-md-6">
                <div class="border--card h-auto">
                    <h4 class="title"><i class="las la-qrcode"></i> @lang('Add Your Account')</h4>
                    <div class="card-body p-4">
                        <h6 class="mb-3">
                            @lang('Use the QR code or setup key on your Google Authenticator app to add your account. ')
                        </h6>

                        <div class="form-group mx-auto text-center">
                            <img class="mx-auto" src="{{ $qrCodeUrl }}">
                        </div>

                        <div class="form-group">
                            <label class="d-block sm-text mb-2">@lang('Setup Key')</label>
                            <div class="input-group input--group">
                                <input type="text" name="key" value="{{ $secret }}" class="form--control referralURL" readonly>
                                <button type="button" class="input-group-text copytext" id="copyBoard"> <i class="fa fa-copy"></i> </button>
                            </div>
                        </div>

                        <label><i class="fa fa-info-circle"></i> @lang('Help')</label>
                        <p>@lang('Google Authenticator is a multi-factor app for mobile devices. It generates timed codes used during the 2-step verification process. To use Google Authenticator, install the Google Authenticator application on your mobile device.') <a class="text--base" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en" target="_blank">@lang('Download')</a></p>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-md-6">

            @if (authAgent()->ts)
                <div class="border--card h-auto ">
                    <h4 class="title"><i class="las la-times-circle"></i> @lang('Disable 2FA Security')</h4>
                    <form action="{{ route('agent.twofactor.disable') }}" method="POST">
                        <div class="card-body p-4">
                            @csrf
                            <input type="hidden" name="key" value="{{ $secret }}">
                            <div class="form-group">
                                <label class="d-block sm-text mb-2">@lang('Google Authenticatior OTP')</label>
                                <input type="text" class="form--control" name="code" required>
                            </div>
                            <button type="submit" class="btn btn--base btn-md w-100">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="border--card">
                    <h4 class="title"><i class="las la-check-circle"></i> @lang('Enable 2FA Security')</h4>
                    <form action="{{ route('agent.twofactor.enable') }}" method="POST">
                        <div class="card-body p-4">
                            @csrf
                            <input type="hidden" name="key" value="{{ $secret }}">
                            <div class="form-group">
                                <label class="d-block sm-text mb-2">@lang('Google Authenticatior OTP')</label>
                                <input type="text" class="form--control" name="code" required>
                            </div>
                            <button type="submit" class="btn btn--base btn-md w-100">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('style')
    <style>
        .copyInput {
            display: inline-block;
            line-height: 50px;
            position: absolute;
            top: 0;
            right: 0;
            width: 40px;
            text-align: center;
            font-size: 14px;
            cursor: pointer;
            -webkit-transition: all 0.3s;
            -o-transition: all 0.3s;
            transition: all 0.3s;
        }

        .copied::after {
            position: absolute;
            top: 8px;
            right: 12%;
            width: 100px;
            display: block;
            content: "COPIED";
            font-size: 1em;
            padding: 5px 5px;
            color: #fff;
            background-color: #071251;
            border-radius: 3px;
            opacity: 0;
            will-change: opacity, transform;
            animation: showcopied 1.5s ease;
        }

        @keyframes showcopied {
            0% {
                opacity: 0;
                transform: translateX(100%);
            }

            50% {
                opacity: 0.7;
                transform: translateX(40%);
            }

            70% {
                opacity: 1;
                transform: translateX(0);
            }

            100% {
                opacity: 0;
            }
        }

        /* copy end */

    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('#copyBoard').click(function() {
                var copyText = document.getElementsByClassName("referralURL");
                copyText = copyText[0];
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                /*For mobile devices*/
                document.execCommand("copy");
                copyText.blur();
                this.classList.add('copied');
                setTimeout(() => this.classList.remove('copied'), 1500);
            });
        })(jQuery);
    </script>
@endpush
