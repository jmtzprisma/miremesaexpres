@extends('agent.layouts.app')
@section('panel')
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="border--card">
                <h5 class="title"><i class="la la-wallet"></i> @lang('Payment via NMI')</h5>
                <div class="card-body p-0">
                    <form action="{{ $data->url }}" id="payment-form" method="{{ $data->method }}" role="form">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">@lang('Card Number')</label>
                                <div class="input-group">
                                    <input autocomplete="off" autofocus class="form-control form--control" name="billing-cc-number" required type="tel" value="{{ old('billing-cc-number') }}" />
                                    <span class="input-group-text"><i class="fa fa-credit-card"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label">@lang('Expiration Date')</label>
                                <input autocomplete="off" class="form-control form--control" name="billing-cc-exp" placeholder="e.g. MM/YY" required type="tel" value="{{ old('billing-cc-exp') }}" />
                            </div>
                            <div class="col-md-6 ">
                                <label class="form-label">@lang('CVC Code')</label>
                                <input autocomplete="off" class="form-control form--control" name="billing-cc-cvv" required type="tel" value="{{ old('billing-cc-cvv') }}" />
                            </div>
                        </div>
                        <br>
                        <button class="btn btn--base btn-md w-100" type="submit"> @lang('Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
