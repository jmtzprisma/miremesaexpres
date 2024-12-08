@extends('agent.layouts.app')

@section('panel')
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="border--card">
                <h5 class="title"><i class="la la-wallet"></i> @lang('Payment via Razorpay')</h5>
                <div class="card-body">
                    <ul class="list-group text-center list-group-flush">
                        <li class="list-group-item d-flex justify-content-between style--two">
                            @lang('You have to pay '):
                            <strong>{{ showAmount($deposit->final_amo) }} {{ __($deposit->method_currency) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between style--two">
                            @lang('You will get '):
                            <strong>{{ showAmount($deposit->amount) }} {{ __($general->cur_text) }}</strong>
                        </li>
                    </ul>
                    <form action="{{ $data->url }}" method="{{ $data->method }}">
                        <input type="hidden" custom="{{ $data->custom }}" name="hidden">
                        <script src="{{ $data->checkout_js }}"
                                                @foreach ($data->val as $key => $value) data-{{ $key }}="{{ $value }}" @endforeach></script>
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
            $('input[type="submit"]').addClass("btn btn--base btn-md w-100 mt-3");
        })(jQuery);
    </script>
@endpush
