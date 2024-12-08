@extends('agent.layouts.app')

@section('panel')
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="border--card">
                <h5 class="title"><i class="la la-wallet"></i> @lang('Payment via Stripe Storefront')</h5>
                <div class="card-body p-0">
                    <form action="{{ $data->url }}" method="{{ $data->method }}">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between bg-transparent">
                                @lang('You have to pay '):
                                <strong>{{ showAmount($deposit->final_amo) }} {{ __($deposit->method_currency) }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between bg-transparent">
                                @lang('You will get '):
                                <strong>{{ showAmount($deposit->amount) }} {{ __($general->cur_text) }}</strong>
                            </li>
                        </ul>
                        <script src="{{ $data->src }}" class="stripe-button" @foreach ($data->val as $key => $value) data-{{ $key }}="{{ $value }}" @endforeach></script>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        (function($) {
            "use strict";
            $('button[type="submit"]').addClass("btn btn-primary w-100 btn-md mt-3");
            $('button[type="submit"]').text("Pay with Card");
        })(jQuery);
    </script>
@endpush
