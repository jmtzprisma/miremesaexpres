@extends('admin.layouts.app')

@section('panel')
    @if (@json_decode($general->system_info)->version > systemDetails()['version'])
        <div class="row">
            <div class="col-md-12">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">
                        <h3 class="card-title"> @lang('New Version Available') <button class="btn btn--dark float-end">@lang('Version') {{ json_decode($general->system_info)->version }}</button></h3>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-dark">@lang('What is the Update ?')</h5>
                        <p>
                            <pre class="f-size--24">{{ json_decode($general->system_info)->details }}</pre>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (@json_decode($general->system_info)->message)
        <div class="row">
            @foreach (json_decode($general->system_info)->message as $msg)
                <div class="col-md-12">
                    <div class="alert border border--primary" role="alert">
                        <div class="alert__icon bg--primary"><i class="far fa-bell"></i></div>
                        <p class="alert__message">@php echo $msg; @endphp</p>
                        <button aria-label="Close" class="close" data-bs-dismiss="alert" type="button">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="card bg-primary-light analytics-card">
                    <div class="card-body mt-4 pb-1">
                        <div class="row align-items-center"> 
                            <div class="col-xl-6">
                                <div class="row">
                                    <div class="col-xl-3 col-6">
                                        <div class="card ov-card">
                                            <a href="{{ route('admin.users.all') }}">
                                            <div class="card-body">
                                                <div class="ana-box">	
                                                    <div class="ic-n-bx">
                                                        <div class="icon-box bg-primary rounded-circle">
                                                            <i class="las la-users f-size--24"></i>
                                                        </div>
                                                    </div>
                                                    <div class="anta-data">
                                                        <h5>@lang('Total Users')</h5>
                                                        {{-- <span>@statistics</span> --}}
                                                        <h3>{{ $widget['total_users'] }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-6">
                                        <div class="card ov-card">
                                            <a href="{{ route('admin.users.active') }}">
                                            <div class="card-body">
                                                <div class="ana-box">	
                                                    <div class="ic-n-bx">
                                                        <div class="icon-box bg-primary rounded-circle">
                                                            <i class="las la-user-check f-size--24"></i>
                                                        </div>
                                                    </div>
                                                    <div class="anta-data">
                                                        <h5>@lang('Active Users')</h5>
                                                        {{-- <span>@fb</span> --}}
                                                        <h3>{{ $widget['verified_users'] }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-6">
                                        <div class="card ov-card">
                                            <a href="{{ route('admin.users.email.unverified') }}">
                                            <div class="card-body">
                                                <div class="ana-box">	
                                                    <div class="ic-n-bx">
                                                        <div class="icon-box bg-primary rounded-circle">
                                                            <i class="lar la-envelope f-size--24"></i>
                                                        </div>
                                                    </div>
                                                    <div class="anta-data">
                                                        <h5>@lang('Email Unverified Users')</h5>
                                                        {{-- <span>@fb</span> --}}
                                                        <h3>{{ $widget['email_unverified_users'] }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-6">
                                        <div class="card ov-card">
                                            <a href="{{ route('admin.users.mobile.unverified') }}">
                                            <div class="card-body">
                                                <div class="ana-box">	
                                                    <div class="ic-n-bx">
                                                        <div class="icon-box bg-primary rounded-circle">
                                                            <i class="las la-comment-slash f-size--24"></i>
                                                        </div>
                                                    </div>
                                                    <div class="anta-data">
                                                        <h5>@lang('Mobile Unverified Users')</h5>
                                                        {{-- <span>@fb</span> --}}
                                                        <h3>{{ $widget['mobile_unverified_users'] }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="row">
                                    <div class="col-xl-3 col-6">
                                        <div class="card ov-card">
                                            <a href="{{ route('admin.agents.all') }}">
                                            <div class="card-body">
                                                <div class="ana-box">	
                                                    <div class="ic-n-bx">
                                                        <div class="icon-box bg-primary rounded-circle">
                                                            <i class="las la-users f-size--24"></i>
                                                        </div>
                                                    </div>
                                                    <div class="anta-data">
                                                        <h5>@lang('Total Agent')</h5>
                                                        {{-- <span>@fb</span> --}}
                                                        <h3>{{ $widget['total_agent'] }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-6">
                                        <div class="card ov-card">
                                            <a href="{{ route('admin.agents.active') }}">
                                            <div class="card-body">
                                                <div class="ana-box">	
                                                    <div class="ic-n-bx">
                                                        <div class="icon-box bg-primary rounded-circle">
                                                            <i class="las la-user-check f-size--24"></i>
                                                        </div>
                                                    </div>
                                                    <div class="anta-data">
                                                        <h5>@lang('Active Agent')</h5>
                                                        {{-- <span>@fb</span> --}}
                                                        <h3>{{ $widget['active_agent'] }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-6">
                                        <div class="card ov-card">
                                            <a href="{{ route('admin.agents.kyc.unverified') }}">
                                            <div class="card-body">
                                                <div class="ana-box">	
                                                    <div class="ic-n-bx">
                                                        <div class="icon-box bg-primary rounded-circle">
                                                            <i class="las la-envelope f-size--24"></i>
                                                        </div>
                                                    </div>
                                                    <div class="anta-data">
                                                        <h5>@lang('KYC Unverified Agent')</h5>
                                                        {{-- <span>@fb</span> --}}
                                                        <h3>{{ $widget['kycUnverified'] }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-6">
                                        <div class="card ov-card">
                                            <a href="{{ route('admin.agents.kyc.pending') }}">
                                            <div class="card-body">
                                                <div class="ana-box">	
                                                    <div class="ic-n-bx">
                                                        <div class="icon-box bg-primary rounded-circle">
                                                            <i class="las la-comment-slash f-size--24"></i>
                                                        </div>
                                                    </div>
                                                    <div class="anta-data">
                                                        <h5>@lang('KYC Pending Agent')</h5>
                                                        {{-- <span>@fb</span> --}}
                                                        <h3>{{ $widget['kycPending'] }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>	
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-xl-3 col-sm-6">
                        <div class="card">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex">
                                    <div class="icon-box icon-box-md bg-danger-light me-1">
                                        <i class="las la-exchange-alt"></i>
                                    </div>
                                    <div class="ms-2">
                                        <h4>{{ $sendMoney['total'] }}</h4>
                                        <p class="mb-0">@lang('Total Send Money')</p>
                                    </div>
                                </div>
                                <a href="{{ route('admin.send.money.all') }}"><i class="las la-chevron-right f-size--24 text-danger"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="card">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex">
                                    <div class="icon-box icon-box-md bg-primary-light me-1">
                                        <i class="las la-spinner"></i>
                                    </div>
                                    <div class="ms-2">
                                        <h4>{{ $sendMoney['pending'] }}</h4>
                                        <p class="mb-0">@lang('Send Money Pending')</p>
                                    </div>
                                </div>
                                <a href="{{ route('admin.send.money.pending') }}"><i class="las la-chevron-right f-size--24 text-primary"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="card">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex">
                                    <div class="icon-box icon-box-md bg-info-light me-1">
                                        <i class="las la-check-circle"></i>
                                    </div>
                                    <div class="ms-2">
                                        <h4>{{ $sendMoney['completed'] }}</h4>
                                        <p class="mb-0">@lang('Send Money Completed')</p>
                                    </div>
                                </div>
                                <a href="{{ route('admin.send.money.completed') }}"><i class="las la-chevron-right f-size--24 text-info"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="card">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex">
                                    <div class="icon-box icon-box-md bg-secondary-light me-1">
                                        <i class="las la-times"></i>
                                    </div>
                                    <div class="ms-2">
                                        <h4>{{ $sendMoney['refunded'] }}</h4>
                                        <p class="mb-0">@lang('Send Money Refunded')</p>
                                    </div>
                                </div>
                                <a href="{{ route('admin.send.money.refunded') }}"><i class="las la-chevron-right f-size--24 text-secondary"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-none-30 mt-30">
        <div class="col-xxl-12 mb-30">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"> @lang('Tasa de conversión USDT')</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.country.save.rates_conversion_usdt') }}" method="POST">
                        @csrf
                        <div class="row">
                            @php 
                                $general = gs();
                                $obj_currencies_usdt = null;
                                $obj_currencies_usdt = !is_null($general->additional_info) ? json_decode($general->additional_info) : null;
                            @endphp
                            @foreach (\App\Models\Country::where('status', true)->groupBy('currency')->get() as $country)
                                @php
                                    $value = null;
                                    if(!is_null($obj_currencies_usdt))
                                        foreach($obj_currencies_usdt as $key => $it)
                                        {
                                            if($key == $country->currency) $value = $it;
                                        }
                                @endphp
                                <div class="col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label>{{ $country->currency }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text">1 {{ $country->currency }} = </span>
                                            <input class="form-control" name="conversion[{{ $country->currency }}]" step="any" type="number" value="{{ $value }}">
                                            <span class="input-group-text">USDT</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="col-lg-6 col-xl-3">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-success">@lang('Guardar')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-none-30 mt-30">
        <div class="col-xxl-12 mb-30">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"> @lang('Tasas de cambio')</h3>
                </div>
                <div class="card-body">
                    @foreach (\App\Models\Country::with('conversionRates')/*->where('is_sending', true)*/->where('status', true)->get() as $country)
                        @php
                                $rates      = $country->conversionRates;
                        @endphp
                        <form action="{{ route('admin.country.save.conversion', $country->id) }}" method="POST">
                        @csrf
                        <h4>{{ $country->name }}</h4>
                        <div class="row">
                        @foreach (\App\Models\Country::where('id', '!=', $country->id)->where('status', true)->orderBy('name')->get() as $item)
                            @php
                                $rate = $rates->where('to_country', $item->id)->first();
                                $value = $rate->rate ?? null;
                            @endphp
                            <div class="col-lg-6 col-xl-6">
                                <div class="form-group">
                                    <label>{{ $country->currency }} @lang('to') {{ $item->currency }}</label>
                                    <input name="data[{{ $loop->index }}][from_country]" type="hidden" value="{{ $country->id }}">
                                    <input name="data[{{ $loop->index }}][to_country]" type="hidden" value="{{ $item->id }}">
                                    <div class="input-group">
                                        <span class="input-group-text">1 {{ $country->currency }} = </span>
                                        <input class="form-control" name="data[{{ $loop->index }}][rate]" step="any" type="number" value="{{ $value }}">
                                        <span class="input-group-text">{{ $item->currency }} </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-lg-6 col-xl-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-success">@lang('Guardar')</button>
                        </div>
                        </div>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    <div class="row mb-none-30 mt-30">
        <div class="col-xxl-6 mb-30">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="card-title">@lang('Monthly Send Money Report') (@lang('Last 12 Month'))</h5>
                        <div class="d-flex gap-2 align-items-center">
                            <select name="sending_currency">
                                @foreach ($sendingCountries as $sendingCountry)
                                    <option value="{{ $sendingCountry->currency }}">{{ $sendingCountry->currency }}</option>
                                @endforeach
                            </select>
                            <div>@lang('to')</div>
                            <select name="recipient_currency">
                                @foreach ($receivingCountries as $receivingCountry)
                                    <option value="{{ $receivingCountry->currency }}">{{ $receivingCountry->currency }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="send_money_chart"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-6 mb-30">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title m-0 py-2">@lang('Country to Country Send Money') (@lang('Last 12 Month'))</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive--sm table-responsive country-to-country-table">
                        <table class="table--light style--two table bg-white">
                            <thead>
                                <th>@lang('Countries')</th>
                                <th>@lang('Sent Amount')</th>
                            </thead>
                            <tbody>
                                @foreach ($sendMoneyData as $transfers)
                                    <tr>
                                        <td>
                                            <div class="d-flex gap-2 align-items-center">
                                                <span class="user">
                                                    {{ $transfers->sending_country }}
                                                    <span class="thumb ms-2">
                                                        <img alt="image" src="{{ getImage(getFilePath('country') . '/' . $transfers->sending_country_image, getFileSize('country')) }}">
                                                    </span>
                                                </span>
                                                <i class="la la-arrow-right"></i>
                                                <span class="user">
                                                    <span class="thumb me-2">
                                                        <img alt="image" src="{{ getImage(getFilePath('country') . '/' . $transfers->recipient_country_image, getFileSize('country')) }}">
                                                    </span>
                                                    {{ $transfers->recipient_country }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <small>
                                                <span class="fw-bold">{{ showAmount($transfers->total_amount) }} {{ $transfers->sending_currency }}</span>
                                                <br>@lang('OR')
                                                <span class="fw-bold">{{ showAmount($transfers->total_base_amount) }} {{ $transfers->recipient_currency }}</span>
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget color="success" icon="las la-hand-holding-usd" link="{{ route('admin.payment.list') }}" style="2" title="Total Payment for Send Money" value="{{ $general->cur_sym }}{{ showAmount($payment['total_payment_amount']) }}" />
        </div>

        <div class="col-xxl-3 col-sm-6">
            <x-widget color="warning" icon="las la-spinner" link="{{ route('admin.payment.pending') }}" style="2" title="Pending Payments for Send Money" value="{{ $payment['total_payment_pending'] }}" />
        </div>

        <div class="col-xxl-3 col-sm-6">
            <x-widget color="danger" icon="las la-spinner" link="{{ route('admin.payment.rejected') }}" style="2" title="Rejected Payments for Send Money" value="{{ $payment['total_payment_rejected'] }}" />
        </div>

        <div class="col-xxl-3 col-sm-6">
            <x-widget color="primary" icon="las la-percentage" link="{{ route('admin.payment.list') }}" style="2" title="Payment Charges for Send Money" value="{{ $general->cur_sym }}{{ showAmount($payment['total_payment_charge']) }}" />
        </div>
    </div>

    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget color="success" icon="las la-hand-holding-usd" icon_style="" link="{{ route('admin.deposit.list') }}" style="2" title="Total Deposited" value="{{ $general->cur_sym }}{{ showAmount($deposit['total_deposit_amount']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget color="warning" icon="las la-spinner" icon_style="" link="{{ route('admin.deposit.pending') }}" style="2" title="Pending Deposits" value="{{ $deposit['total_deposit_pending'] }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget color="danger" icon="las la-spinner" icon_style="" link="{{ route('admin.deposit.rejected') }}" style="2" title="Rejected Deposits" value="{{ $deposit['total_deposit_rejected'] }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget color="primary" icon="las la-percentage" icon_style="" link="{{ route('admin.deposit.list') }}" style="2" title="Deposit Charges" value="{{ $general->cur_sym }}{{ showAmount($deposit['total_deposit_charge']) }}" />
        </div>
    </div>

    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget color="success" icon="lar la-credit-card" link="{{ route('admin.withdraw.log') }}" style="2" title="Total Withdrawn" value="{{ $general->cur_sym }}{{ showAmount($withdrawals['total_withdraw_amount']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget color="warning" icon="las la-sync" link="{{ route('admin.withdraw.pending') }}" style="2" title="Pending Withdrawals" value="{{ $withdrawals['total_withdraw_pending'] }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget color="danger" icon="las la-times-circle" link="{{ route('admin.withdraw.rejected') }}" style="2" title="Rejected Withdrawals" value="{{ $withdrawals['total_withdraw_rejected'] }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget color="primary" icon="las la-percent" link="{{ route('admin.withdraw.log') }}" style="2" title="Withdrawal Charge" value="{{ $general->cur_sym }}{{ showAmount($withdrawals['total_withdraw_charge']) }}" />
        </div>
    </div>

    <div class="row mb-none-30 mt-30">
        <div class="col-xl-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Monthly Agent\'s Deposit & Withdraw Report') (@lang('Last 12 Month'))</h5>
                    <div id="apex-bar-chart"> </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Transactions Report') (@lang('Last 30 Days'))</h5>
                    <div id="apex-line"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-none-30 mt-5">
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By Browser') (@lang('Last 30 days'))</h5>
                    <canvas id="userBrowserChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By OS') (@lang('Last 30 days'))</h5>
                    <canvas id="userOsChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By Country') (@lang('Last 30 days'))</h5>
                    <canvas id="userCountryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .btn-outline--red {
            color: #F44336;
            border-color: #F44336;
        }

        .btn-outline--red:hover {
            background-color: #F44336;
            color: #fff;
        }

        .widget-three__icon {
            width: 80px;
            height: 80px;
        }

        .widget-three__icon i {
            font-size: 32px;
        }

        .widget-three__content {
            margin-top: 15px;
        }

        .country-to-country-table {
            max-height: 400px;
            overflow-x: auto;
        }

        /* width */
        .country-to-country-table::-webkit-scrollbar {
            width: 5px;
        }

        /* Track */
        .country-to-country-table::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* Handle */
        .country-to-country-table::-webkit-scrollbar-thumb {
            border-radius: 8px;
            background: #4634ff;
        }

        /* Handle on hover */
        .country-to-country-table::-webkit-scrollbar-thumb:hover {
            background: #4634ff;
        }
    </style>
@endpush

@push('script')
    <script src="{{ asset('assets/global/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/chart.js.2.8.0.js') }}"></script>

    <script>
        "use strict";

        var options = {
            series: [{
                name: '<?php echo trans('Total Deposit'); ?>',//'Total Deposit',
                data: [
                    @foreach ($months as $month)
                        {{ getAmount(@$depositsMonth->where('months', $month)->first()->depositAmount) }},
                    @endforeach
                ]
            }, {
                name: '<?php echo trans('Total Withdraw'); ?>',//'Total Withdraw',
                data: [
                    @foreach ($months as $month)
                        {{ getAmount(@$withdrawalMonth->where('months', $month)->first()->withdrawAmount) }},
                    @endforeach
                ]
            }],
            chart: {
                type: 'bar',
                height: 450,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: @json($months),
            },
            yaxis: {
                title: {
                    text: "{{ __($general->cur_sym) }}",
                    style: {
                        color: '#7c97bb'
                    }
                }
            },
            grid: {
                xaxis: {
                    lines: {
                        show: false
                    }
                },
                yaxis: {
                    lines: {
                        show: false
                    }
                },
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "{{ __($general->cur_sym) }}" + val + " "
                    }
                }
            }
        };
        var chart = new ApexCharts(document.querySelector("#apex-bar-chart"), options);
        chart.render();



        var ctx = document.getElementById('userBrowserChart');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chart['user_browser_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_browser_counter']->flatten() }},
                    backgroundColor: [
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(231, 80, 90, 0.75)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                maintainAspectRatio: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: false,
                }
            }
        });



        var ctx = document.getElementById('userOsChart');
        var myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: @json($chart['user_os_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_os_counter']->flatten() }},
                    backgroundColor: [
                        '#1e90ff',
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(0, 0, 0, 0.05)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: false,
                }
            },
        });


        // Donut chart
        var ctx = document.getElementById('userCountryChart');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chart['user_country_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_country_counter']->flatten() }},
                    backgroundColor: [
                        '#bf9fee',
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(231, 80, 90, 0.75)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: true
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: true,
                }
            }
        });

        // apex-line chart
        var options = {
            chart: {
                height: 450,
                type: "area",
                toolbar: {
                    show: false
                },
                dropShadow: {
                    enabled: true,
                    enabledSeries: [0],
                    top: -2,
                    left: 0,
                    blur: 10,
                    opacity: 0.08
                },
                animations: {
                    enabled: true,
                    easing: 'linear',
                    dynamicAnimation: {
                        speed: 1000
                    }
                },
            },
            dataLabels: {
                enabled: false
            },
            series: [{
                    name: '<?php echo trans('Plus Transactions'); ?>',//"Plus Transactions",
                    data: [
                        @foreach ($trxReport['date'] as $trxDate)
                            {{ getAmount(@$plusTrx->where('date', $trxDate)->first()->amount ?? 0) }},
                        @endforeach
                    ]
                },
                {
                    name: '<?php echo trans('Minus Transactions'); ?>',//"Minus Transactions",
                    data: [
                        @foreach ($trxReport['date'] as $trxDate)
                            {{ getAmount(@$minusTrx->where('date', $trxDate)->first()->amount ?? 0) }},
                        @endforeach
                    ]
                }
            ],
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.9,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: [
                    @foreach ($trxReport['date'] as $trxDate)
                        "{{ $trxDate }}",
                    @endforeach
                ]
            },
            grid: {
                padding: {
                    left: 5,
                    right: 5
                },
                xaxis: {
                    lines: {
                        show: false
                    }
                },
                yaxis: {
                    lines: {
                        show: false
                    }
                },
            },
        };

        var chart = new ApexCharts(document.querySelector("#apex-line"), options);

        chart.render();

        sendMoneyGraph();
        $('select[name=sending_currency], select[name=recipient_currency]').on('change', function() {
            sendMoneyGraph();
        });

        function sendMoneyGraph() {
            var url = "{{ route('admin.send_money.statistics') }}";
            var sendingCurrency = $('select[name=sending_currency]').val();
            var recipientCurrency = $('select[name=recipient_currency]').val();

            $.ajax({
                type: "get",
                url: url,
                data: {
                    sending_currency: sendingCurrency,
                    recipient_currency: recipientCurrency
                },
                success: function(response) {
                    const sendingAmount = Object.values(response.allSendMoney).map(item => item.sending_amount);
                    const baseCurrencyAmount = Object.values(response.allSendMoney).map(item => item.base_currency_amount);

                    var options = {
                        series: [{
                            name: '<?php echo trans('Sending Amount'); ?>',
                            data: sendingAmount,
                        }],
                        chart: {
                            type: 'bar',
                            height: 400,
                            toolbar: {
                                show: false
                            },
                            locales: [{
                                name: 'en',
                                options: {
                                    months: ['January', 'February', 'March', 'Aspril', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                                    shortMonths: ['Jan', 'Feb', 'Mar', 'Aspr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                                    days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                                    shortDays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                                    toolbar: {
                                        download: 'Download SVG',
                                        selection: 'Selection',
                                        selectionZoom: 'Selection Zoom',
                                        zoomIn: 'Zoom In',
                                        zoomOut: 'Zoom Out',
                                        pan: 'Panning',
                                        reset: 'Reset Zoom',
                                    }
                                }
                            }],
                            defaultLocale: 'en'
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '50%',
                                endingShape: 'rounded'
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            show: true,
                            width: 2,
                            colors: ['transparent']
                        },
                        xaxis: {
                            categories: Object.keys(response.allSendMoney),
                        },
                        yaxis: {
                            title: {
                                text: sendingCurrency,
                                style: {
                                    color: '#7c97bb'
                                }
                            }
                        },
                        grid: {
                            xaxis: {
                                lines: {
                                    show: false
                                }
                            },
                            yaxis: {
                                lines: {
                                    show: false
                                }
                            },
                        },
                        fill: {
                            opacity: 1
                        },
                        tooltip: {
                            y: {
                                formatter: function(val, data) {
                                    if (typeof baseCurrencyAmount[data.seriesIndex] === 'undefined') {
                                        return val + ` ${sendingCurrency}`;
                                    } else {
                                        return val + ` ${sendingCurrency} ({{ $general->cur_sym }}${parseFloat(baseCurrencyAmount[data.seriesIndex]).toFixed(2) })`
                                    }
                                }
                            }
                        }
                    };
                    $('.send_money_chart').html(`<div id="sendMoneyChart"></div>`);
                    var chart = new ApexCharts(document.querySelector("#sendMoneyChart"), options);
                    chart.render();

                }
            });
        }
    </script>
@endpush
