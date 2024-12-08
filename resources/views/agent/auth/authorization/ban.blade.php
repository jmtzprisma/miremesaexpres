@extends('agent.layouts.master')
@section('content')
    <div class="login-main" style="background-image: url('{{ asset('assets/agent/images/login.jpg') }}')">
        <div class="container custom-container d-flex justify-content-center">
            <div class="login-area d-block">
                <div class="text-center mb-3">
                    <h2 class="text-white mb-2">@lang('You are banned')</h2>
                    <p class="fw-bold mb-2">@lang('Reason'):</p>
                    <p>{{ $agent->ban_reason }}</p>
                </div>
                <div class="text-center">
                    <a href="{{ route('agent.logout') }}" class="text-white mt-4"><i class="las la-sign-in-alt" aria-hidden="true"></i>@lang('Back to Login')</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Courier+Prime&display=swap");

        .login-area {
            width: 480px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background-color: #1e157d;
            padding: 40px;
        }

        .login-area::after {
            z-index: -1;
        }

        @media (max-width: 575px) {
            .login-area {
                width: 475px;
                padding: 32px;
            }
        }

        @media (max-width: 500px) {
            .login-area {
                width: 400px;
                padding: 28px;
            }
        }

        @media (max-width: 430px) {
            .login-area {
                width: 380px;
            }
        }

        @media (max-width: 406px) {
            .login-area {
                width: 340px;
            }
        }

        @media (max-width: 366px) {
            .login-area {
                width: 300px;
            }
        }

        @media (max-width: 328px) {
            .login-area {
                width: 290px;
            }
        }

    </style>
@endpush
