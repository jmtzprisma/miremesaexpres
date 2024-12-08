@extends('admin.layouts.app')
@section('panel')
    @php
        if (old()) {
            $sendingAmount = old('sending_amount');
            $recipientAmount = old('recipient_amount');
            $deliveryMethodId = old('delivery_method');
            $sendingCountryId = old('sending_country');
            $recipientCountryId = old('recipient_country');
        }
    @endphp
    <section class="section section--sm">
        <div class="container">
            <h4 class="text-center">@lang('Send Money Form')</h4>
            <form action="{{ route('admin.send.money.send_money_form') }}" class="card-body container-fluid" method="get" id="frmSend">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="form-group col-md-12">
                            <select id="user" name="user" class="select2-basic" onchange="$('#frmSend').submit()">
                                <option value="">Seleccione un usuario</option>
                                @foreach (\App\Models\User::where('status', true)->get() as $itm)
                                <option value="{{ $itm->id }}" @if(!is_null($user) && $user->id == $itm->id) selected @endif >{{ $itm->firstname . ' ' . $itm->lastname . ' (' . $itm->email . ' - ' . $itm->mobile . ')' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group col-md-12">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#addNewUser" class="btn btn--success btn--shadow w-100 btn-lg bal-btn" data-act="add">
                                <i class="las la-plus-circle"> </i>&nbsp;
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            @if(!is_null($user))
            <form action="{{ route('admin.send.money.save') }}" class="card-body container-fluid register" method="post" id="frmSendMoney">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}" >
                <div class="row g-4">
                    <div class="col-lg-6 ">
                        <div class="card custom--card h-100">
                            <div class="card-header">
                                <h5 class="card-title text-center">@lang('Sender Information')</h5>
                            </div>
                            <div class="card-body">

                                <div class="exchange-form">
                                    <div class="exchange-form__body p-0">
                                        @include($activeTemplate . 'partials.country_fields', ['class' => 'mb-3', 'showLimit' => true])

                                        <div class="conversion__rate mb-5">
                                            <div>1 <span class="sending-currency"></span> = </div>
                                            <div class="exchange-rate ms-1"></div>
                                            <div class="recipient-currency ms-1"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="text--accent sm-text d-block fw-md mb-2" for="source_of_funds">@lang('Source of Funds')</label>
                                            <div class="form--select-light">
                                                <select class="form-select form--select" id="source_of_funds" name="source_of_funds" required>
                                                    <option value="">@lang('Select One')</option>
                                                    @foreach ($sources as $key => $source)
                                                        <option @selected($key == 0 || old('source_of_funds') == $source->id) value="{{ $source->id }}">{{ __($source->name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="text--accent sm-text d-block fw-md mb-2" for="source_purpose">@lang('Sending Purpose')</label>
                                            <div class="form--select-light">
                                                <select class="form-select form--select" id="source_purpose" name="sending_purpose">
                                                    <option value="">@lang('Select One')</option>
                                                    @foreach ($purposes as $key => $purpose)
                                                        <option @selected($key == 0 || old('sending_purpose') == $purpose->id) value="{{ $purpose->id }}">{{ __($purpose->name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 banco">
                                    <input name="payment_type" type="hidden" value="2">
                                    <label class="text--accent sm-text d-block fw-md mb-2" for="banco">@lang('Banco')</label>
                                    <div class="row g-4">
                                        <div class="col-lg-12">
                                            <select id="banco" name="banco" class="select2-basic" required>
                                                <option value="">Seleccione un banco</option>
                                                @foreach (\App\Models\Banco::get() as $itm)
                                                <option value="{{ $itm->id }}" >{{ $itm->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <small class="text--danger insufficientBalanceError d-none">@lang('You don\'t have sufficient balance. Your current balance is') {{ $general->cur_text }}</small>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card custom--card" id="selectRecipient">
                            <div class="card-header">
                                <h5 class="card-title text-center">@lang('¿A quien envias?')</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="btn-selected__label flex-grow-1 w-100" data-value="1" for="newRecipient">
                                        <input class="btn-selected__input" id="newRecipient" name="user_recipient" required type="radio" value="new">
                                        <span class="btn-selected btn-selected--primary">
                                            <div class="icon icon--lg icon--circle">
                                                <i class="fas fa-user-plus"></i>
                                            </div>
                                            <span class="btn-selected__text ">
                                                @lang('Agregar nuevo destinatario')
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                @if(!is_null($user))
                                @foreach(\App\Models\Recipient::where('user_id', $user->id)->get() as $key => $recipient)
                                <div class="mb-3">
                                    <label class="btn-selected__label flex-grow-1 w-100" data-value="1" for="recipient_{{ $key }}">
                                        <input class="btn-selected__input" id="recipient_{{ $key }}" name="user_recipient" required type="radio" value="{{ $key }}">
                                        <span class="btn-selected btn-selected--primary" style="justify-content: left; text-align: left;">
                                            <div class="icon icon--lg icon--circle">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span class="btn-selected__text ">
                                                {{ $recipient->name }}
                                                <br>
                                                <span style="font-size: 10px;">{{ $recipient->email }} ({{ $recipient->mobile }})</span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="card custom--card d-none" id="nwRecipient">
                            <div class="card-header d-flex">
                                <h5 class="card-title text-center">@lang('Recipient Information')</h5>
                                <button type="button" id="btnBackRecipients" class="ml-5 btn btn-info t-ml-10">
                                    <i class="la la-undo"></i>
                                </button>
                            </div>
                            <div class="card-body">

                                <div class="mb-3">
                                    <label class="text--accent sm-text d-block fw-md mb-2" for="recipient_name">@lang('Recipient Name')</label>
                                    <input class="form-control form--control" id="recipient_name" name="recipient[name]" required type="text" value="{{ old('recipient')['name'] ?? null }}">
                                </div>
                                <div class="mb-3">
                                    <label class="text--accent sm-text d-block fw-md mb-2" for="recipient_mobile">@lang('Recipient Mobile No.')</label>
                                    <div class="input-group">
                                        <span class="input-group-text recipient-dial-code"></span>
                                        <input class="form-control form--control" id="recipient_mobile" name="recipient[mobile]" required type="number" value="{{ old('recipient')['mobile'] ?? null }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="text--accent sm-text d-block fw-md mb-2" for="recipient_email">@lang('Recipient Email')</label>
                                    <input class="form-control form--control" id="recipient_email" name="recipient[email]" required type="email" value="{{ old('recipient')['email'] ?? null }}">
                                </div>
                                <div class="mb-3">
                                    <label class="text--accent sm-text d-block fw-md mb-2" for="recipient_address">@lang('Recipient Address')</label>
                                    <input class="form-control form--control-textarea" id="recipient_address" name="recipient[address]" value="{{ old('recipient')['address'] ?? null }}">
                                </div>

                                <div class="mb-3">
                                    <label class="text--accent sm-text d-block fw-md mb-2" for="deliveryMethod">@lang('Delivery Methods')</label>
                                    <div class="form--select-light">
                                        <select class="form-select form--select" id="deliveryMethod" name="delivery_method" required>
                                            <option value="">@lang('Select One')</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3 services-div"></div>
                                <div class="mb-3 mt-4 d-none formData"></div>

                                <div class="mb-3 mt-4">
                                    <ul class="list list--column payment-table">
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="d-block t-heading-font heading-clr sm-text">
                                                    @lang('Sending Amount')
                                                </span>
                                                <h5 class="fw-md heading-clr t-heading-font sm-text m-0">
                                                    <span class="sending-amount-total"></span>
                                                    <span class="sending-currency"></span>
                                                </h5>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="d-block sm-text">
                                                    @lang('Total Charge')
                                                </span>
                                                <h5 class="fw-md heading-clr t-heading-font sm-text m-0">
                                                    <span class="charge-amount-text"></span>
                                                    <span class="sending-currency"></span>
                                                </h5>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="d-block t-heading-font heading-clr sm-text">
                                                    @lang('Final Amount')
                                                </span>
                                                <h5 class="sm-text m-0">
                                                    <span class="final-amount-text"></span>
                                                    <span class="sending-currency"></span>
                                                </h5>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="d-block sm-text">
                                                    @lang('Payable In '){{ __($general->cur_text) }}
                                                </span>
                                                <h5 class="text--base sm-text m-0">
                                                    <span class="base-amount-text"></span>
                                                    <span>{{ __($general->cur_text) }}</span>
                                                </h5>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <button class="btn btn--base btn--xl w-100 formSubmitButton" type="submit">@lang('Continue')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            @else
            <h5 class="text-center">@lang('Seleccione un usuario')</h5>
            @endif
        </div>
    </section>

    <div class="modal custom--modal fade" id="addNewUser" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Add New User')</h5>
                    <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                        <i aria-hidden="true" class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row g-4 g-xl-0 justify-content-between align-items-center">
                            <div class="col-lg-12">
                                <form action="{{ route('admin.users.register') }}" autocomplete="off" class="login__form row g-3 g-sm-4" method="POST" onsubmit="return submitUserForm();">
                                    @csrf
                                    <div class="col-sm-6 col-xl-6 ">
                                        <label class="form-label sm-text t-heading-font heading-clr fw-md" for="user-name">@lang('Username')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="la la-user"></i>
                                            </span>
                                            <input class="form-control form--control checkUser" id="username" id="user-name" name="username" required type="text" value="{{ old('username') }}">
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
                                                        <option data-code="{{ $key }}" data-mobile_code="{{ $country->dial_code }}" value="{{ $country->country }}" @if('Spain' == $country->country) selected @endif>{{ __($country->country) }}</option>
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
                                    <div class="row">
                                        <div class="form-group col-sm-6">
                                            <label class="d-block sm-text mb-2">@lang('First Name')</label>
                                            <input type="text" class="form-control form--control" name="firstname" required>
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label class="d-block sm-text mb-2">@lang('Last Name')</label>
                                            <input type="text" class="form-control form--control" name="lastname" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-sm-6">
                                            <label class="d-block sm-text mb-2">@lang('Address')</label>
                                            <input type="text" class="form-control form--control" name="address">
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label class="d-block sm-text mb-2">@lang('State')</label>
                                            <input type="text" class="form-control form--control" name="state">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-sm-4">
                                            <label class="d-block sm-text mb-2">@lang('Zip Code')</label>
                                            <input type="text" class="form-control form--control" name="zip">
                                        </div>

                                        <div class="form-group col-sm-4">
                                            <label class="d-block sm-text mb-2">@lang('City')</label>
                                            <input type="text" class="form-control form--control" name="city">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn--xl btn--base w-100 btn--xl"> @lang('Submit') </button>
                                    </div>
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
        "use strict";

        let mobileElement = $('.mobile-code');
        $('select[name=country]').change(function() {
            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            //$('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));
            mobileElement.text(`+${$('select[name=country] :selected').data('mobile_code')}`);
        });

        mobileElement.text(`+ ${$('select[name=country] :selected').data('mobile_code')}`);
        
        let agentStatus = `{{ $general->agent_module }}`;
        let agent = `@lang('Agent')`;
        let agentFixedCharge = `{{ $general->agent_charges->fixed_charge ?? 0 }}`;
        let agentPercentCharge = `{{ $general->agent_charges->percent_charge ?? 0 }}`;

        let sendLimit = `{{ $general->user_send_money_limit }}`;
        let sendingAmount = `{{ $sendingAmount }}`;
        let recipientAmount = `{{ $recipientAmount }}`;
        let deliveryMethodId = `{{ $deliveryMethodId }}`;
        let recipientCountryId = `{{ $recipientCountryId }}`;
        let sendingCountryId = `{{ $sendingCountryId }}`;

        if (deliveryMethodId) {
            deliveryMethodId *= 1;
        }

        let defaultSelectOption = `@lang('Select One')`;
        let serviceStatus = true;
        let serviceURL = "{{ route('services') }}";
        let serviceLabel = `@lang('Service')`;
        let isAgent = false;

        const recipients = []
        @if(!is_null($user))
        @foreach(\App\Models\Recipient::where('user_id', $user->id)->get() as $recipient)
            recipients.push(
                [
                    '{{ $recipient->sending_currency }}',
                    '{{ $recipient->recipient_currency }}',
                    '{{ $recipient->country_delivery_method_id }}',
                    '{{ $recipient->service_id }}',
                    '{{ $recipient->name }}',
                    '{{ $recipient->mobile }}',
                    '{{ $recipient->email }}',
                    '{{ $recipient->address }}'
            @foreach (json_decode($recipient->form_data) as $key => $itm)
            ,'{{ $itm->value }}'
            @endforeach
                ]
            )
        @endforeach
        @endif

        $("#btnBackRecipients").on("click", function(){
            $("[name='sending_country']").trigger("change");
            $("#recipient-country").trigger("change");

            
            $("#recipient_name").val('');
            $("#recipient_mobile").val('');
            $("#recipient_email").val('');
            $("#recipient_address").val('');
            
            $("#selectRecipient").removeClass("d-none");
            $("#nwRecipient").addClass("d-none");
        })

        $("[name='user_recipient']").on("change", function(){
            var key = $("input[name='user_recipient']:checked").val();
            //console.log(recipients[key]);
            if(key != 'new')
            {
                $("[name='sending_country']").val(recipients[key][0]);
                $("[name='sending_country']").trigger("change");
                $("#recipient-country").val(recipients[key][1]);
                $("#recipient-country").trigger("change");

                $("#deliveryMethod").val(recipients[key][2])
                $("#deliveryMethod").trigger("change");
                
                setTimeout(() => {
                    $("[name='service']").val(recipients[key][3]);
                    $("[name='service']").trigger("change");

                
                    setTimeout(() => {
                        $("[name='numero_de_cuenta']").val(recipients[key][8])
                        $("[name='cedula']").val(recipients[key][9])
                        $("[name='tipo_de_cuenta']").val(recipients[key][10])
                        $("[name='tipo_de_cuenta']").trigger("change");
                    }, 500);
                }, 500);

                $("#recipient_name").val(recipients[key][4])
                $("#recipient_mobile").val(recipients[key][5])
                $("#recipient_email").val(recipients[key][6])
                $("#recipient_address").val(recipients[key][7])
            }
            
            $("#selectRecipient").addClass("d-none");
            $("#nwRecipient").removeClass("d-none");
        })
    </script>

    <script src="{{ asset('assets/global/js/sendMoney.js') }}"></script>
    <script>
        (function($) {
            "use strict";

            $(document).on('change', '.countryServices', function() {
                let serviceId = $(this).val();
                if (serviceId) {
                    let data = {
                        service_id: serviceId
                    }

                    $.get("{{ route('service.form') }}", data,
                        function(data, textStatus, jqXHR) {
                            if (data.success && data.html.length) {
                                $('.formData').html(data.html);
                                $('.formData').find('label').addClass('text--accent sm-text d-block fw-md mb-2');
                                $('.formData').removeClass('d-none');
                            } else {
                                $('.formData').html('');
                                $('.formData').addClass('d-none');
                            }
                        }
                    );
                } else {
                    $('.formData').empty();
                }
            });

            $('.walletPayment').on('click', function() {
                checkBalance();
            });

            let availableForToday = 0;
            let availableForThisMonth = 0;
            let limitPerSendMoney = 0;

            $('.country-picker').on('change', function() {
                let general = @json($general);
                let todaySendMoneyInBaseCur = @json($todaySendMoney);
                let thisMonthSendMoneyInBase = @json($thisMonthSendMoney);
                let sender = $('[name=sending_country]');
                let baseToSenderCurrency = parseFloat(sender.find(':selected').data('rate'));
                let dailyLimit = parseFloat(general.user_daily_send_money_limit * baseToSenderCurrency).toFixed(2);
                let monthlyLimit = parseFloat(general.user_monthly_send_money_limit * baseToSenderCurrency).toFixed(2);

                limitPerSendMoney = parseFloat(general.user_send_money_limit * baseToSenderCurrency).toFixed(2);
                availableForToday = parseFloat(dailyLimit - todaySendMoneyInBaseCur * baseToSenderCurrency).toFixed(2);
                availableForToday = parseFloat(availableForToday > 0 ? availableForToday : 0).toFixed(2);
                availableForThisMonth = parseFloat(monthlyLimit - thisMonthSendMoneyInBase * baseToSenderCurrency).toFixed(2);
                availableForThisMonth = parseFloat(availableForThisMonth > 0 ? availableForThisMonth : 0).toFixed(2);

                if (availableForToday > availableForThisMonth) {
                    availableForToday = availableForThisMonth;
                }

                $('.send_money_limit').text(limitPerSendMoney);
                $('.daily_send_money_limit').text(dailyLimit);
                $('.monthly_send_money_limit').text(monthlyLimit);
                $('.today_limit').text(availableForToday);
                $('.this_month_limit').text(availableForThisMonth);
            }).change();

            $(document).on('input', '.sending-amount, .recipient-amount', function() {
                let sendingAmount = parseFloat($('[name=sending_amount]').val());
                if (sendingAmount > limitPerSendMoney || sendingAmount > availableForToday || sendingAmount > availableForThisMonth) {
                    $('.limitMessage').removeClass('d-none')
                    $('.formSubmitButton').attr('disabled', true);
                } else {
                    $('.limitMessage').addClass('d-none')
                    $('.formSubmitButton').attr('disabled', false);
                }

                if ($('[name=payment_type]:checked').val() == 1) {
                    checkBalance();
                }
            });

            function checkBalance() {
                var balance = $('.walletPayment').data('balance');
                var finalAmount = parseInt($('.base-amount-text').text());
                if (finalAmount > balance) {
                    $('.insufficientBalanceError').removeClass('d-none').fadeIn();
                    $('.formSubmitButton').attr('disabled', true);
                } else {
                    $('.insufficientBalanceError').fadeOut().addClass('d-none');
                    if ($('.limitMessage').hasClass('d-none')) {
                        $('.formSubmitButton').attr('disabled', false);
                    }
                }
            }

            $('.directPayment').on('click', function() {
                $('.insufficientBalanceError').fadeOut().addClass('d-none');
                if ($('.limitMessage').hasClass('d-none')) {
                    $('.formSubmitButton').attr('disabled', false);
                }
            });

            $('.formSubmitButton').on('click', function() {
                // var paymentType = $('[name=payment_type]:checked').val();
                // if (!paymentType) {
                //     notify('error', 'Please select a payment type')
                // }
            });

            $('.showLimit').on('click', function() {
                $('#limitModal').modal('show');
            });

            @if (old('payment_type') == 1)
                $('.walletPayment').click();
            @endif

            @if (old('payment_type') == 2)
                $('.directPayment').click();
            @endif
            
            $('.select2-basic').select2();
        })(jQuery);
    </script>
@endpush
@push('style')
    <style>
        .exchange-form {
            box-shadow: none;
        }

        .select2-container--default .select2-results__option[aria-disabled=true] {
            display: none;
        }

        .select2-container {
            z-index: 1 !important;
        }

        .reverseCountryBtn i {
            transform: rotate(90deg);
            font-size: 20px;
        }

        .conversion__rate {
            display: flex;
            justify-content: center;
            font-size: 27px;
            font-weight: 500;
        }

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid rgba(0,0,0,.125) !important;
            border-radius: 0.25rem !important;
        }
        .btn-selected {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 12px 30px;
            cursor: pointer;
            text-align: center;
            flex-shrink: 0;
            letter-spacing: 0.03em;
            transition: all 0.3s ease;
            border-radius: 3px;
        }

        .icon--lg {
            width: 60px;
            height: 60px;
            line-height: 60px;
            font-size: 28px;
        }
        .icon--circle {
            border-radius: 50%;
            text-align: center;
        }
        .icon {
            position: relative;
            display: grid;
            place-items: center;
            isolation: isolate;
        }
        .btn-selected__text {
            display: inline-block;
            font-family: "Poppins", sans-serif;
            font-weight: 500;
            line-height: 1;
        }
        .btn-selected__input {
            display: none;
        }
        .exchange-form .select2-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            width: 125px !important;
        }
        .select2-container {
            box-sizing: border-box;
            display: inline-block;
            margin: 0;
            position: relative;
            vertical-align: middle;
        }
        .exchange-form .select2-container--default .select2-selection--single {
            background-color: rgba(var(--r), var(--g), var(--b), 1);
            border: none;
            border-radius: 0 3px 3px 0;
            width: 125px !important;
            height: 100%;
            display: flex;
        }

        .exchange-form .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 45px;
        }
        .exchange-form .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: rgba(var(--light-r), var(--light-g), var(--light-b), 1);
            line-height: 50px;
            padding-left: 0;
            padding-right: 0;
            text-align: center;
        }
        .exchange-form .select2-container--default .select2-selection--single .select2-selection__rendered {
            flex-grow: 1;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: rgba(var(--light-r), var(--light-g), var(--light-b), 1);
            line-height: 28px;
            padding-left: 8px;
            padding-right: 8px;
            font-size: 14px;
            letter-spacing: 0.05em;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            flex-grow: 1;
        }
        #frmSendMoney .exchange-form .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 50px;
            position: relative;
            top: auto;
            right: auto;
            width: 30px;
        }
        #frmSendMoney .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: auto;
            position: relative;
            top: auto;
            right: auto;
            width: 26px;
        }
        .banco .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: inherit !important;
            position: absolute !important;
            top: 0 !important;
            right: 0 !important;
            width: 26px !important;
        }
        .exchange-form__flags {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }
        img, svg {
            vertical-align: text-bottom;
        }
        label.btn-selected__label.flex-grow-1.w-100 {
            border: 1px solid rgba(0,0,0,.125) !important;
            border-radius: 0.25rem !important;
        }
    </style>
@endpush

