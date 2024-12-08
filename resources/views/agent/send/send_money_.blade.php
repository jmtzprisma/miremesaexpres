@extends('agent.layouts.app')

@section('panel')
    @php
        $info = json_decode(json_encode(getIpInfo()), true);
        $sendingAmount = old('sending_amount') ?? '';
        $recipientAmount = old('recipient_amount') ?? '';
        $deliveryMethodId = old('delivery_method') ?? '';
        $recipientCountryId = old('recipient_country') ?? '';
    @endphp
    <form action="{{ route('agent.send.money.insert') }}" method="post">
        <div class="row justify-content-center mt-5">
            @csrf
            <div class="col-xl-8 mb-xl-0 mb-3">
                <div class="border--card">
                    <h4 class="title"><i class="lab la-telegram-plane"></i> {{ __($pageTitle) }}</h4>
                    <div class="card-body p-0">
                        <div class="row">
                            @if ($sendingCountry->status == Status::DISABLE || $sendingCountry->is_sending == Status::NO)
                                <div class="col-12">
                                    <div class="alert alert-danger">
                                        @lang('Sending money from') {{ __($sendingCountry->name) }} @lang('is not available now.')
                                    </div>
                                </div>
                            @endif
                            <div class="form-group col-md-12">
                                <label for="sending-country">@lang('Sending From')</label>
                                <div class="input-group input--group agent-selector sending-parent">
                                    <select class="country-picker form--control sending-countries" id="sending-country" name="sending_country">
                                        <option data-conversion_rates="{{ $sendingCountry->conversionRates }}" data-currency="{{ $sendingCountry->currency }}" data-dial_code="{{ $sendingCountry->dial_code }}" data-id="{{ $sendingCountry->id }}" data-image="{{ getImage(getFilePath('country') . '/' . $sendingCountry->image, getFileSize('country')) }}" data-name="{{ $sendingCountry->name }}" data-rate="{{ $sendingCountry->rate }}" selected value="{{ $sendingCountry->id }}">
                                            {{ $sendingCountry->currency }}
                                        </option>
                                    </select>
                                    <input class="form--control sending-amount text-right" id="from" name="sending_amount" placeholder="0.00" required step="any" type="number" value="{{ $sendingAmount }}">
                                </div>
                                <a class="showLimit xsm-text text-muted  text-decoration-underline" href="javascript:void(0)"> <i class="la la-info-circle"></i> @lang('Limit')</a>
                                <small class="mb-3 limitMessage d-none text--danger">
                                    @lang('The amount exceeds the limit')
                                </small>
                            </div>

                            <div class="form-group col-md-12">
                                <label for="recipient-country">@lang('Send To')</label>
                                <div class="input-group input--group agent-selector recipient-parent">
                                    <select class="country-picker input-group-text form--control recipient-countries" id="recipient-country" name="recipient_country">
                                        @foreach ($receivingCountries as $receivingCountry)
                                            <option data-currency="{{ $receivingCountry->currency }}" data-delivery_methods="{{ $receivingCountry->countryDeliveryMethods }}" data-dial_code="{{ $receivingCountry->dial_code }}" data-fixed_charge="{{ $receivingCountry->fixed_charge }}" data-has_agent="{{ $receivingCountry->has_agent }}" data-id="{{ $receivingCountry->id }}" data-image="{{ getImage(getFilePath('country') . '/' . $receivingCountry->image, getFileSize('country')) }}" data-name="{{ $receivingCountry->name }}" data-percent_charge="{{ $receivingCountry->percent_charge }}" data-rate="{{ $receivingCountry->rate }}" value="{{ $receivingCountry->id }}">
                                                {{ $receivingCountry->currency }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input class="form--control recipient-amount text-right" id="to" name="recipient_amount" placeholder="0.00" required step="any" type="number" value="{{ $recipientAmount }}">
                                </div>
                            </div>

                            <h5 class="text--info text-center">
                                1 <span class="sending-currency"></span> = <span class="exchange-rate"></span> <span class="recipient-currency"></span>
                            </h5>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="deliveryMethod">@lang('Delivery Methods')</label>
                                    <select class="form--control" id="deliveryMethod" name="delivery_method" required>
                                        <option value="">@lang('Select One')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 service-div d-none">
                                <div class="form-group">
                                    <label for="service">@lang('Service')</label>
                                    <select class="form--control countryServices" disabled id="service" name="service">
                                        <option value="">@lang('Select One')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-none formData"></div>

                            <div class="form-group error-section text-center">
                                <small class="text-danger fw-bold error-text"></small>
                            </div>
                            <div class="col-12">
                                <div class="recipient-info row">
                                    <div class="border-line-area mt-2">
                                        <h6 class="border-line-title fw-bold">@lang('Sender and Recipient\'s  Information')</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sender_name">@lang('Sender Name')</label>
                                            <input class="form--control" id="sender_name" name="sender[name]" required type="text" value="{{ old('sender')['name'] ?? null }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="sender_mobile">@lang('Sender Mobile No.')</label>
                                            <div class="input-group input--group">
                                                <span class="input-group-text sender-dial-code">

                                                </span>
                                                <input class="form--control" id="sender_mobile" name="sender[mobile]" required type="number" value="{{ old('sender')['mobile'] ?? null }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="sender_email">@lang('Sender Email')</label>
                                            <input class="form--control" id="sender_email" name="sender[email]" required type="email" value="{{ old('sender')['email'] ?? null }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="sender_address">@lang('Sender Address')</label>
                                            <textarea class="form--control" id="sender_address" name="sender[address]">{{ old('sender')['address'] ?? null }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="recipient_name">@lang('Recipient Name')</label>
                                            <input class="form--control" id="recipient_name" name="recipient[name]" required type="text" value="{{ old('recipient')['name'] ?? null }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="recipient_mobile">@lang('Recipient Mobile No.')</label>
                                            <div class="input-group input--group">
                                                <span class="input-group-text recipient-dial-code">

                                                </span>
                                                <input class="form--control" id="recipient_mobile" name="recipient[mobile]" required type="number" value="{{ old('recipient')['mobile'] ?? null }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="recipeint_email">@lang('Recipient Email')</label>
                                            <input class="form--control" id="recipeint_email" name="recipient[email]" required type="email" value="{{ old('recipient')['email'] ?? null }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="recipient_address">@lang('Recipient Address')</label>
                                            <textarea class="form--control" id="recipient_address" name="recipient[address]">{{ old('recipient')['address'] ?? null }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="border--card h-auto">
                    <h4 class="title"><i class="lar la-file-alt"></i> @lang('Summery')</h4>

                    <div class="add-money-details-bottom d-none crypto_currency text-center">
                        <span>@lang('Conversion with') <span class="method_currency"></span> @lang('and final value will Show on next step')</span>
                    </div>
                    <div class="add-money-card-middle">
                        <ul class="add-money-details-list">
                            <li>
                                <div class="caption">
                                    @lang('Sending Amount')
                                </div>
                                <div class="value">
                                    <span class="sending-amount-total"></span>
                                    <span class="sending-currency"></span>
                                </div>
                            </li>
                            <li>
                                <div class="caption">
                                    <span>@lang('Total Charge')</span>
                                </div>
                                <div class="value">
                                    <span class="charge-amount-text">0</span>
                                    <span class="sending-currency"></span>
                                </div>
                            </li>
                            <li>
                                <div class="caption">
                                    <span>@lang('Final Amount')</span>
                                </div>
                                <div class="value">
                                    <span class="final-amount-text"></span>
                                    <span class="sending-currency"></span>
                                </div>
                            </li>
                            <li>
                                <div class="caption">
                                    <span>@lang('Payable in') {{ __($general->cur_text) }}</span>
                                </div>
                                <div class="value">
                                    <span class="base-amount-text"></span>
                                    <span>{{ __($general->cur_text) }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="border-line-area mt-4">
                        <h6 class="border-line-title fw-bold">@lang('Acknowledgement')</h6>
                    </div>
                    <div class="form-group">
                        <label for="source_of_funds">@lang('Source of Funds')</label>
                        <select class="form--control" id="source_of_funds" name="source_of_funds" required>
                            <option value="">@lang('Select one')</option>
                            @foreach ($sources as $source)
                                <option @selected(old('source_of_funds') == $source->id) value="{{ $source->id }}">{{ __($source->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sending_purpose">@lang('Sending Purpose')</label>
                        <select class="form--control" id="sending_purpose" name="sending_purpose" required>
                            <option value="">@lang('Select one')</option>
                            @foreach ($purposes as $purpose)
                                <option @selected(old('sending_purpose') == $purpose->id) value="{{ $purpose->id }}">{{ __($purpose->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-md btn--base w-100 trx-submit-button formSubmitButton mt-3" type="submit">@lang('Proceed')</button>
                </div>
            </div>
        </div>
    </form>

    <div class="modal custom--modal fade" id="limitModal" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Send Money Limit')</h5>
                    <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                        <i aria-hidden="true" class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush" id="transfer-limit">
                        <li class="list-group-item d-flex justify-content-between">
                            <span> @lang('Per Transfer')</span>
                            <span>
                                <span class="send_money_limit">0</span>
                                <span class="sending-currency"></span>
                            </span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between">
                            <span>@lang('Daily Limit')</span>
                            <span>
                                <span class="daily_send_money_limit">0</span>
                                <span class="sending-currency"></span>
                            </span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between">
                            <span>
                                @lang('Available for Today')
                            </span>
                            <span>
                                <span class="today_limit">0</span>
                                <span class="sending-currency"></span>
                            </span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between">
                            <span>
                                @lang('Monthly Limit')
                            </span>
                            <span>
                                <span class="monthly_send_money_limit">0</span>
                                <span class="sending-currency"></span>
                            </span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between">
                            <span>@lang('Available for This Month')</span>
                            <span>
                                <span class="this_month_limit">0</span>
                                <span class="sending-currency"></span>
                            </span>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        let agentStatus = `{{ $general->agent_module }}`;
        let agent = `@lang('Agent')`;
        let agentFixedCharge = `{{ $general->agent_charges->fixed_charge ?? 0 }}`;
        let agentPercentCharge = `{{ $general->agent_charges->percent_charge ?? 0 }}`;
        let sendLimit = `{{ $general->agent_send_money_limit }}`;

        let sendingAmount = `{{ $sendingAmount }}`;
        let recipientAmount = `{{ $recipientAmount }}`;
        let deliveryMethodId = `{{ $deliveryMethodId }}`;
        let recipientCountryId = `{{ $recipientCountryId }}`;
        let sendingCountryId = `{{ $sendingCountry->id }}`;

        if (deliveryMethodId) {
            deliveryMethodId *= 1;
        }

        let serviceStatus = true;
        let defaultSelectOption = `@lang('Select One')`;
        let serviceURL = "{{ route('services') }}";
        let serviceLabel = `@lang('Service')`;
        let isAgent = true;
    </script>

    <script src="{{ asset('assets/global/js/sendMoney.js') }}"></script>

    <script>
        (function($) {
            "use strict";

            $('.countryServices').on('change', function() {
                let serviceId = $(this).val();
                let countryId = $('.recipient-countries').val();
                let deliveryMethodId = $('#deliveryMethod').val();

                let data = {
                    service_id: serviceId,
                    country_id: countryId,
                    country_delivery_method_id: deliveryMethodId
                }

                $.get("{{ route('service.form') }}", data, function(data, textStatus, jqXHR) {
                    if (data.success && data.html.length) {
                        $('.formData').html(data.html);
                        $('.formData').removeClass('d-none');
                    } else {
                        $('.formData').html('');
                        $('.formData').addClass('d-none');
                    }
                });
            });
            let avaiableForToday = 0;
            let avaialbeForThisMonth = 0;
            let limitPerSendMoney = 0;
            $('.country-picker').on('change', function() {
                let general = @json($general);
                let todaySendMoneyInBaseCur = @json($todaySendMoney);
                let thisMonthSendMoneyInBase = @json($thisMonthSendMoney);
                let sender = $('[name=sending_country]');
                let baseToSenderCurrency = parseFloat(sender.find(':selected').data('rate'));
                let dailyLimit = parseFloat(general.agent_daily_send_money_limit * baseToSenderCurrency).toFixed(2);
                let monthlyLimit = parseFloat(general.agent_monthly_send_money_limit * baseToSenderCurrency).toFixed(2);

                limitPerSendMoney = parseFloat(general.agent_send_money_limit * baseToSenderCurrency).toFixed(2);
                avaiableForToday = dailyLimit - parseFloat(todaySendMoneyInBaseCur * baseToSenderCurrency).toFixed(2);
                avaiableForToday = parseFloat(avaiableForToday > 0 ? avaiableForToday : 0).toFixed(2);
                avaialbeForThisMonth = monthlyLimit - parseFloat(thisMonthSendMoneyInBase * baseToSenderCurrency).toFixed(2);
                avaialbeForThisMonth = parseFloat(avaialbeForThisMonth > 0 ? avaialbeForThisMonth : 0).toFixed(2);


                if (avaiableForToday > avaialbeForThisMonth) {
                    avaiableForToday = avaialbeForThisMonth;
                }

                $('.send_money_limit').text(limitPerSendMoney);
                $('.daily_send_money_limit').text(dailyLimit);
                $('.monthly_send_money_limit').text(monthlyLimit);
                $('.today_limit').text(avaiableForToday);
                $('.this_month_limit').text(avaialbeForThisMonth);
            }).change();


            $('.showLimit').on('click', function() {
                $('#limitModal').modal('show');
            });
        })(jQuery);
    </script>
@endpush
@push('style')
    <style>
        .select2-container--default .select2-results__option[aria-disabled=true] {
            display: none;
        }

        .reverseCountryBtn:focus {
            box-shadow: none;
        }

        .reverseCountryBtn i {
            transform: rotate(90deg);
            font-size: 20px;
        }
    </style>
@endpush
