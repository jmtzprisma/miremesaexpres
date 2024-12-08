@extends('agent.layouts.app')

@section('panel')
    <form action="{{ route('agent.deposit.insert') }}" method="POST" id="form">
        @csrf
        <input type="hidden" name="method_code">
        <input type="hidden" name="currency">
        <div class="row justify-content-center mt-5">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="border--card h-auto">
                    <h4 class="title"><i class="las la-plus-circle"></i> @lang('Add Money')</h4>
                    <div class="form-group">
                        <label>@lang('Select Gateway')</label>
                        <select class="select form--control" name="gateway" required>
                            <option value="">@lang('Select One')</option>
                            @foreach ($gatewayCurrency as $data)
                                <option value="{{ $data->method_code }}" @selected(old('gateway') == $data->method_code) data-gateway="{{ $data }}">{{ $data->name }}</option>
                            @endforeach
                        </select>
                        <code class="text--danger gateway-msg"></code>
                    </div>
                    <div class="form-group mb-0">
                        <label>@lang('Amount')</label>
                        <div class="input-group">
                            <input class="form--control amount" type="number" step="any" name="amount" autocomplete="off" placeholder="Enter Amount" value="{{ old('amount') }}" required>
                            <span class="input-group-text">{{ __($general->cur_text) }}</span>
                        </div>
                        <p><code class="text--warning">@lang('limit'): <span class="limit">0.00</span> {{ __($general->cur_text) }}</code></p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="border--card h-auto">
                    <h4 class="title"><i class="lar la-file-alt"></i> @lang('Summery')</h4>
                    <div class="add-money-card-middle">
                        <ul class="add-money-details-list">
                            <li>
                                <span class="caption">@lang('Amount')</span>
                                <div class="value">{{ $general->cur_sym }}<span class="amount">0.00</span></div>
                            </li>
                            <li>
                                <span class="caption">@lang('Charge')</span>
                                <div class="value">{{ $general->cur_sym }}<span class="charge">0.00</span> </div>
                            </li>
                            <li>
                                <span class="caption">@lang('Payable')</span>
                                <div class="value">{{ $general->cur_sym }}<span class="payable">0.00</span> </div>
                            </li>
                            <li class="d-none rate-element">

                            </li>
                            <li class="d-none in-site-cur">
                                <span class="caption">@lang('In') <span class="base-currency"></span></span>
                                <div class="value final_amo fw-bold">0</div>
                            </li>
                        </ul>
                        <div class="add-money-details-bottom text-center d-none crypto_currency">
                            <span>@lang('Conversion with') <span class="method_currency"></span> @lang('and final value will Show on next step')</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-md btn--base w-100 mt-3 req_confirm">@lang('Proceed')</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('select[name=gateway]').change(function() {
                if (!$('select[name=gateway]').val()) {
                    // set all preview 0
                    return false;
                }
                var resource = $('select[name=gateway] option:selected').data('gateway');
                var fixed_charge = parseFloat(resource.fixed_charge);
                var percent_charge = parseFloat(resource.percent_charge);
                var rate = parseFloat(resource.rate)
                if (resource.method.crypto == 1) {
                    var toFixedDigit = 8;
                    $('.crypto_currency').removeClass('d-none');
                } else {
                    var toFixedDigit = 2;
                    $('.crypto_currency').addClass('d-none');
                }
                $('.min').text(parseFloat(resource.min_amount).toFixed(2));
                $('.max').text(parseFloat(resource.max_amount).toFixed(2));

                $('.limit').text(parseFloat(resource.min_amount).toFixed(2) + ' ~ ' + parseFloat(resource.max_amount).toFixed(2));
                var amount = parseFloat($('input[name=amount]').val());
                if (!amount) {
                    amount = 0;
                }
                if (amount <= 0) {
                    //    set all preview zero
                    return false;
                }
                var charge = parseFloat(fixed_charge + (amount * percent_charge / 100)).toFixed(2);
                $('.charge').text(charge);
                var payable = parseFloat((parseFloat(amount) + parseFloat(charge))).toFixed(2);
                $('.payable').text(payable);
                var final_amo = (parseFloat((parseFloat(amount) + parseFloat(charge))) * rate).toFixed(toFixedDigit);
                $('.final_amo').text(final_amo);
                if (resource.currency != '{{ $general->cur_text }}') {
                    var rateElement = `<span class="fw-bold caption">@lang('Conversion Rate')</span> <span class="value"><span  class="fw-bold">1 {{ __($general->cur_text) }} = <span class="rate">${rate}</span>  <span class="base-currency">${resource.currency}</span></span></span>`;
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
                $('input[name=method_code]').val(resource.method_code);
                $('input[name=amount]').on('input');
            });
            $('input[name=amount]').on('input', function() {
                $('select[name=gateway]').change();
                $('.amount').text(parseFloat($(this).val()).toFixed(2));
            });
        })(jQuery);
    </script>
@endpush
