@php
    $banner = getContent('banner.content', true);
    $sendingAmount = old('sending_amount');
    $recipientAmount = old('recipient_amount');
    $deliveryMethodId = old('delivery_method');
    //$recipientCountryId = old('recipient_country');
    $sendingCountryId = old('sending_country');
@endphp

<!-- Hero  -->
<section class="hero" style="background-image: url('{{ getImage('assets/images/frontend/banner/' . @$banner->data_values->image, '1800x840') }}')">
    <div class="hero__content">
        <div class="container">
            <div class="row gy-5 g-lg-4 align-items-center justify-content-center justify-content-lg-between">
                <div class="col-lg-7 col-xxl-6 text-lg-start text-center">
                    <h2 class="hero__content-title text-capitalize text--white pt-lg-0 pt-4">
                        {{ __($banner->data_values->heading) }}
                    </h2>

                    <p class="hero__content-para text--white ms-lg-0 mx-auto">
                        {{ __($banner->data_values->description) }}
                    </p>

                    <div class="hero__btn-group justify-content-center justify-content-lg-start mt-4">
                        <a class="btn btn--xl btn--base" href="{{ $banner->data_values->button_link }}">
                            {{ __($banner->data_values->button_text) }}
                        </a>
                        <a class="btn btn--video btn--circle btn--base show-video" href="{{ $banner->data_values->youtube_link }}">
                            <i class="fas fa-play"></i>
                        </a>
                    </div>
                </div>
                <div class="col-sm-10 col-lg-5 col-xxl-4">

                    <div class="exchange-form__header">
                        <span class="exchange-form__sub-title text--white"> @lang('Conversion Rate') </span>
                        <h5 class="exchange-form__title m-0 text-white">1 <span class="sending-currency"></span> = <span class="exchange-rate">1</span> <span class="recipient-currency"></span></h5>
                    </div>

                    <div class="exchange-form">
                        <form action="{{ route('currency.calculator') }}" id="calculatorForm" class="row g-0" method="POST">
                            @csrf
                            <div class="col-12">
                                <div class="exchange-form__body">
                                    <div class="row g-3">

                                        @include($activeTemplate . 'partials.country_fields', ['class' => 'col-12', 'showLimit' => false])

                                        <div class="col-12">
                                            <label class="text--accent sm-text d-block fw-md mb-2" for="deliveryMethod">@lang('Delivery Methods')</label>
                                            <div class="form--select-light">
                                                <select class="form-select form--select" id="deliveryMethod" name="delivery_method" required>
                                                    <option value="">@lang('Select One')</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 charge-section">
                                            <ul class="list list--column timeline-list">
                                                <li class="list__item timeline-list__item">
                                                    <span class="timeline-list__dot"></span>
                                                    <div class="timeline-list__content justify-content-between">
                                                        <div class="timeline-list__left">
                                                            <span class="d-block sm-text">@lang('CHARGE')
                                                                <span class="chargeInfo" data-bs-toggle="tooltip" title="@lang('Select delivery method and input amount to view charge rate')">
                                                                    <i class="fa fa-info-circle"></i>
                                                                </span>
                                                            </span>
                                                        </div>
                                                        <div class="timeline-list__right">
                                                            +
                                                            <span class="charge-amount-text">0</span>
                                                            <span class="sending-currency"></span>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list__item timeline-list__item">
                                                    <span class="timeline-list__dot"></span>
                                                    <div class="timeline-list__content justify-content-between">
                                                        <div class="timeline-list__left">
                                                            <span class="d-block heading-clr">
                                                                @lang('Payable Amount')
                                                            </span>
                                                        </div>
                                                        <div class="timeline-list__right">
                                                            <span class="d-block heading-clr">
                                                                <span class="final-amount-text">0</span>
                                                                <span class="sending-currency"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="col-12">
                                            <button class="btn btn--xl btn--base w-100 btn--xl sendBtn" type="{{ auth()->user() ? 'submit' : 'button' }}">
                                                @lang('Send Now')
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal custom--modal fade" id="loginModal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('You Must Login First')</h5>
                <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                    <i aria-hidden="true" class="la la-times"></i>
                </button>
            </div>

            <div class="login__right">
                @include($activeTemplate . 'user.auth.login_form')
            </div>
        </div>
    </div>
</div>

<!-- Hero End -->
@push('script')
    <script>
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
        
        let agentStatus = `{{ $general->agent_module }}`;
        let agent = `@lang('Agent')`;
        let agentFixedCharge = `{{ $general->agent_charges->fixed_charge ?? 0 }}`;
        let agentPercentCharge = `{{ $general->agent_charges->percent_charge ?? 0 }}`;

        let sendLimit = `{{ $general->send_money_limit }}`;
        let sendingAmount = `{{ $sendingAmount }}`;
        let recipientAmount = `{{ $recipientAmount }}`;
        let deliveryMethodId = `{{ $deliveryMethodId }}`;
        let recipientCountryId = `{{ $recipientCountryId }}`;
        let sendingCountryId = `{{ $sendingCountryId }}`;

        if (deliveryMethodId) {
            deliveryMethodId *= 1;
        }

        let defaultSelectOption = `@lang('Select One')`;
        let serviceStatus = false;

        @guest
        $('.sendBtn').on('click', function(e) {
            let calculatorForm = $('#calculatorForm');

            let fields = `
                <input type="hidden" name="sending_amount" value="${calculatorForm.find('[name=sending_amount]').val()??''}">
                <input type="hidden" name="sending_country" value="${calculatorForm.find('[name=sending_country]').val()??''}">
                <input type="hidden" name="recipient_amount" value="${calculatorForm.find('[name=recipient_amount]').val()??''}">
                <input type="hidden" name="recipient_country" value="${calculatorForm.find('[name=recipient_country]').val()??''}">
                <input type="hidden" name="delivery_method" value="${calculatorForm.find('[name=delivery_method]').val()??''}">
            `;

            $('#loginModal').find('form').prepend(fields)
            $('#loginModal').modal('show');
        });
        @endguest
        var varAdmin = false;
    </script>

    <script src="{{ asset('assets/global/js/sendMoney.js') }}"></script>
@endpush

@push('style')
    <style>
        .select2-container--default .select2-results__option[aria-disabled=true] {
            display: none;
        }

        input::placeholder {
            font-size: 0.875rem;
        }

        .reverseCountryBtn i {
            transform: rotate(90deg);
            font-size: 20px;
        }

        .modal-backdrop.show {
            opacity: 0.7;
        }
    </style>
@endpush
