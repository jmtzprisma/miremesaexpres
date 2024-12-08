@extends('agent.layouts.app')

@section('panel')
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="border--card">
                <h5 class="title"><i class="la la-wallet"></i> @lang('Payment via Voguepay')</h5>
                <div class="card-body p-0">
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
                    <button type="button" class="btn btn--base btn-md w-100 mt-3" id="btn-confirm">@lang('Pay Now')</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="//pay.voguepay.com/js/voguepay.js"></script>
    <script>
        "use strict";
        var closedFunction = function() {}
        var successFunction = function(transaction_id) {
            window.location.href = '{{ route(gatewayRedirectUrl()) }}';
        }
        var failedFunction = function(transaction_id) {
            window.location.href = '{{ route(gatewayRedirectUrl()) }}';
        }

        function pay(item, price) {
            //Initiate voguepay inline payment
            Voguepay.init({
                v_merchant_id: "{{ $data->v_merchant_id }}",
                total: price,
                notify_url: "{{ $data->notify_url }}",
                cur: "{{ $data->cur }}",
                merchant_ref: "{{ $data->merchant_ref }}",
                memo: "{{ $data->memo }}",
                recurrent: true,
                frequency: 10,
                developer_code: '60a4ecd9bbc77',
                custom: "{{ $data->custom }}",
                customer: {
                    name: 'Customer name',
                    country: 'Country',
                    address: 'Customer address',
                    city: 'Customer city',
                    state: 'Customer state',
                    zipcode: 'Customer zip/post code',
                    email: 'example@example.com',
                    phone: 'Customer phone'
                },
                closed: closedFunction,
                success: successFunction,
                failed: failedFunction
            });
        }
        (function($) {
            $('#btn-confirm').on('click', function(e) {
                e.preventDefault();
                pay('Buy', {{ $data->Buy }});
            });
        })(jQuery);
    </script>
@endpush
