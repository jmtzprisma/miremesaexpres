@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="section section--xl">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form action="{{ route('user.deposit.insert') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input name="currency" type="hidden">
                        <input name="amount" type="hidden" value="{{ $base_currency_amount + $base_currency_charge }}">
                        <div class="card custom--card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    {{ __($pageTitle) }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="d-block sm-text mb-2 required ">@lang('Select Gateway')</label>
                                    <div class="form--select-light">
                                        <select class="form-select form--select" name="gateway" required>
                                            <option value="">@lang('Select One')</option>
                                            @foreach ($gatewayCurrency as $data)
                                                <option @selected(old('gateway') == $data->method_code) data-gateway="{{ $data }}" value="{{ $data->method_code }}">{{ $data->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 preview-details d-nonee">
                                    <h6>Datos del depósito</h6>
                                    <ul class="list-group list-group-flush text-center">
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <span>@lang('Payable Amount')</span>
                                            <span>{{ showAmount($sending_amount + $sending_charge) }}
                                                {{ $sendMoney->sending_currency }}</span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <span>@lang('In') {{ __($general->cur_text) }}</span>
                                            <span>{{ showAmount($base_currency_amount + $base_currency_charge) }}
                                                {{ __($general->cur_text) }}</span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex d-none charge-data justify-content-between">
                                            <span>@lang('Gateway Charge')</span>
                                            <span><span class="charge">0</span> {{ __($general->cur_text) }}</span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex d-none payable-data justify-content-between">
                                            <span>@lang('Payable Including Charge')</span> <span><span class="payable ">
                                                    0</span>
                                                {{ __($general->cur_text) }}</span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex d-none limit-data justify-content-between">
                                            <span>@lang('Limit')</span>
                                            <span><span class="min">0</span> {{ __($general->cur_text) }} - <span class="max ">0</span> {{ __($general->cur_text) }}</span>
                                        </li>
                                        <li class="list-group-item px-0 justify-content-between d-none rate-element">

                                        </li>
                                        <li class="list-group-item px-0 justify-content-between d-none in-site-cur">
                                            <span>@lang('In') <span class="base-currency"></span></span>
                                            <span class="final_amo">0</span>
                                        </li>
                                        <li class="list-group-item px-0 justify-content-center crypto_currency d-none">
                                            <span>@lang('Conversion with') <span class="method_currency"></span> @lang('and final value will Show on nextstep')</span>
                                        </li>
                                    </ul>
                                </div>
                                <button class="btn btn--base w-100  btn--xl mt-3" type="submit">@lang('Submit')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('select[name=gateway]').change(function() {
                var gatewayValue = $('select[name=gateway]').val();
                if (!gatewayValue) {
                    $('.accounts_bank').addClass('d-none');
                    $('.limit-data').addClass('d-none');
                    $('.charge-data').addClass('d-none');
                    $('.payable-data').addClass('d-none');
                    return false;
                } else {
                    $('.accounts_bank').removeClass('d-none');
                    $('.limit-data').removeClass('d-none');
                    $('.charge-data').removeClass('d-none');
                    $('.payable-data').removeClass('d-none');

                }

                var resource = $('select[name=gateway] option:selected').data('gateway');
                let minLimit = parseFloat(resource.min_amount).toFixed(2);
                let maxLimit = parseFloat(resource.max_amount).toFixed(2);
                var amount = parseFloat($('input[name=amount]').val());

                if (amount > maxLimit || amount < minLimit) {
                    $('form :submit').attr('disabled', 'true');
                    notify('error', 'This gateway doesn\'t follow payment limit')
                } else {
                    $('form :submit').removeAttr('disabled');
                }

                var fixed_charge = parseFloat(resource.fixed_charge);
                var percent_charge = parseFloat(resource.percent_charge);
                var rate = "{{ $sendMoney->sendingCountry->rate }}" * 1;
                if (resource.method.crypto == 1) {
                    var toFixedDigit = 8;
                    $('.crypto_currency').removeClass('d-none');
                } else {
                    var toFixedDigit = 2;
                    $('.crypto_currency').addClass('d-none');
                }
                $('.min').text(minLimit);
                $('.max').text(maxLimit);



                if (!amount) {
                    amount = 0;
                }
                var charge = parseFloat(fixed_charge + (amount * percent_charge / 100)).toFixed(2);
                $('.charge').text(charge);
                var payable = parseFloat((parseFloat(amount) + parseFloat(charge))).toFixed(2);
                $('.payable').text(payable);
                var final_amo = (parseFloat((parseFloat(amount) + parseFloat(charge))) * rate).toFixed(toFixedDigit);
                $('.final_amo').text(final_amo);

                if (resource.currency != '{{ $general->cur_text }}') {
                    var rateElement =
                        `<span>@lang('Conversion Rate')</span> <span><span >1 {{ __($general->cur_text) }} = <span class="rate">${rate}</span>  <span class="base-currency">${resource.currency}</span></span></span>`;
                    $('.rate-element').html(rateElement)
                    $('.rate-element').removeClass('d-none');
                    $('.in-site-cur').removeClass('d-none');
                    $('.rate-element').addClass('d-flex');
                    $('.in-site-cur').addClass('d-flex');
                } else {
                    $('.rate-element').html('')
                    $('.rate-element').addClass('d-none');
                    $('.in-site-cur').addClass('d-none');
                    $('.rate-element').removeClass('d-flex');
                    $('.in-site-cur').removeClass('d-flex');
                }


                $('.base-currency').text(resource.currency);
                $('.method_currency').text(resource.currency);
                $('input[name=currency]').val(resource.currency);
                $('input[name=amount]').on('input');
            });
            $('input[name=amount]').on('input', function() {
                $('select[name=gateway]').change();
                $('.amount').text(parseFloat($(this).val()).toFixed(2));
            });
        })(jQuery);
    </script>
@endpush
