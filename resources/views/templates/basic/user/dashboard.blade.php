@extends($activeTemplate . 'layouts.master')
@section('content')
    @php
        $kycInstruction = getContent('kyc_instruction_user.content', true);
    @endphp
    <div class="section section--xl">
        @if (auth()->user()->kv != 1)
            <div class="section__head">
                <div class="container">
                    @if (auth()->user()->kv == 0)
                        @if(auth()->user()->kyc)
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-warning mb-0" role="alert">
                                        <h5 class="alert-heading m-0"><a href="{{ route('user.kyc.asking') }}">@lang('Su verificacion fue rechazada')</a></h5>
                                        <hr>
                                        <p class="mb-0">
                                            Si usted desea, puede contactarnos vía whatsapp si tiene dudas respecto a su proceso de verificación.<br>
                                            <a href="https://wa.me/34642533357?text=Necesito ayuda con mi verificación de la cuenta" target="_blank">Enviar mensaje</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info mb-0" role="alert">
                                    <h5 class="alert-heading m-0"><a href="{{ route('user.kyc.asking') }}">@lang('KYC Verification Required')</a></h5>
                                    <hr>
                                    <p class="mb-0"> {{ __($kycInstruction->data_values->verification_instruction) }} <a href="{{ route('user.send.money.video_valid', ['only_kyc' => 'true']) }}">@lang('Click Here to Verify')</a></p> --}}
                                    <p class="mb-0"> {{ __($kycInstruction->data_values->verification_instruction) }} <a href="{{ route('user.kyc.asking') }}">@lang('Click Here to Verify')</a></p>
                                </div>
                            </div>
                        </div>
                        @if(!empty(auth()->user()->reason) && !is_null(auth()->user()->reason))
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning mb-0" role="alert">
                                    <p class="mb-0">
                                        {{ auth()->user()->reason }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                    @elseif(auth()->user()->kv == 2)
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning mb-0" role="alert">
                                    <h5 class="alert-heading m-0">@lang('KYC Verification pending')</h5>
                                    <hr>
                                    @if(is_null(auth()->user()->video_id))
                                    <p class="mb-0"> {{ __($kycInstruction->data_values->pending_instruction) }} <a href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a></p>
                                    @else
                                    <p class="mb-0"> {{ __($kycInstruction->data_values->pending_instruction) }} <a href="{{ route('user.send.money.video_valid', ['only_kyc' => 'true']) }}">@lang('See KYC Data')</a></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="section__head">
            <div class="container">
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-4 p-2">
                        <div class="dashboard-card flex-grow-1" style="height: 100%;">
                            <div class="user align-items-center justify-content-center">
                                <div class="icon icon--lg icon--circle">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="user__content">
                                    <p class="xl-text mb-0">@lang('Send Money Completed')</p>
                                    <div class="text  mt-2 mb-0">
                                        {{ $general->cur_sym }}{{ showAmount($widget['send_money_amount']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-4 p-2">
                        <div class="dashboard-card flex-grow-1" style="height: 100%;">
                            <div class="user align-items-center justify-content-center">
                                <div class="icon icon--lg icon--circle">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <div class="user__content">
                                    <p class="xl-text mb-0">@lang('Send Money Pending')</p>
                                    <div class="text  mt-2 mb-0">
                                        {{ $general->cur_sym }}{{ showAmount($widget['send_money_pending']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-4 p-2">
                        <div class="dashboard-card flex-grow-1" style="height: 100%;">
                            <div class="user align-items-center justify-content-center">
                                <div class="icon icon--lg icon--circle">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div class="user__content">
                                    <p class="xl-text mb-0">@lang('Send Money Initiated')</p>
                                    <div class="text">
                                        {{ $general->cur_sym }}{{ showAmount($widget['send_money_initiated']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-4 p-2">
                        <div class="dashboard-card flex-grow-1" style="height: 100%;">
                            <div class="user align-items-center justify-content-center">
                                <div class="icon icon--lg icon--circle">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <div class="user__content">
                                    <p class="xl-text mb-0">@lang('Pending Payment')</p>
                                    <div class="text">
                                        {{ $general->cur_sym }}{{ showAmount($widget['payment_pending']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-4 p-2">
                        <div class="dashboard-card flex-grow-1" style="height: 100%;">
                            <div class="user align-items-center justify-content-center">
                                <div class="icon icon--lg icon--circle">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="user__content">
                                    <p class="xl-text mb-0">@lang('Rejected Payment')</p>
                                    <div class="text">
                                        {{ $general->cur_sym }}{{ showAmount($widget['payment_rejected']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row g-lg-3">
                <div class="col-12">
                    <div class="custom--table__header">
                        <h5 class="text-lg-start m-0 text-center">@lang('Recent Send Money Log')</h5>
                    </div>
                </div>
                <div class="col-12">
                    <div class="table-responsive--md">

                        @include($activeTemplate . 'partials.send_money_table', ['transfers' => $transfers, 'hasBtn' => true])
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Details MODAL --}}
    <div class="modal custom--modal fade" id="detailsModal" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                        <i aria-hidden="true" class="la la-times"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6">

                            <div class="p-1 d-flex flex-column">
                                <small class="text-muted"> <i class="la la-user"></i> @lang('Recipient\'s Name')</small>
                                <h6 class="name"></h6>
                            </div>
                            <div class="p-1 d-flex flex-column">
                                <small class="text-muted"> <i class="la la-mobile"></i> @lang('Recipient\'s Mobile No.')</small>
                                <h6 class="mobile"></h6>
                            </div>

                            <div class="p-1 d-flex flex-column">
                                <small class="text-muted"><i class="la la-map-marker"></i> @lang('Recipient\'s Address')</small>
                                <h6 class="address"></h6>
                            </div>
                            <div class="p-1 d-flex flex-column">
                                <small class="text-muted"> <i class="la la-globe"></i> @lang('Recipient\'s Country')</small>
                                <h6 class="country"></h6>
                            </div>
                            <div class="p-1 d-flex flex-column">
                                <small class="text-muted"><i class="las la-braille"></i> @lang('MTCN')</small>
                                <h6 class="mtcn_number"></h6>
                            </div>
                            <div class="p-1 d-flex flex-column">
                                <small class="text-muted"><i class="las la-random"></i> @lang('Transaction Number')</small>
                                <h6 class="trx"></h6>
                            </div>
                            <div class="p-1 d-flex flex-column">
                                <small class="text-muted"><i class="las la-money-check-alt"></i> @lang('Delivery Method')</small>
                                <h6 class="delivery_method"></h6>
                            </div>
                            <div class="p-1 d-flex flex-column">
                                <small class="text-muted"><i class="las la-clock"></i> @lang('Sent At')</small>
                                <h6 class="sent_at"></h6>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-1 d-flex flex-column">
                                <small class="text-muted"><i class="las la-file-invoice-dollar"></i> @lang('Recipient will Get')</small>
                                <h6 class="receivable_amount"></h6>
                            </div>
                            <div class="p-1 d-flex flex-column">
                                <small class="text-muted"><i class="las la-exchange-alt"></i> @lang('Conversion Rate')</small>
                                <h6>1 <span class="sending_currency"></span> = <span class="conversion_rate"></span></h6>
                            </div>
                            <div class="p-1 d-flex flex-column">
                                <small class="text-muted"><i class="las la-hand-holding-usd"></i> @lang('Sent Amount')</small>
                                <h6 class="send_money_amount"></h6>
                            </div>
                            <div class="p-1 d-flex flex-column ">
                                <small class="text-muted view_imagen"><i class="las la-image"></i> @lang('View Image')</small>
                                <a id="view_image" target="_blank" class="view_imagen" href="">@lang('View')</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <form action="{{ route('user.send.money.pay') }}" class="w-100" method="POST">
                        @csrf
                        <input name="id" type="hidden">
                        <button class="btn btn--base w-100 btn--xl">
                            @lang('Pay Now')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    {{-- feedback MODAL --}}
    <div class="modal custom--modal fade" id="feedbackModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Feedback')</h5>
                    <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                        <i aria-hidden="true" class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <span class="admin_feedback"></span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.detailBtn').on('click', function() {
                var modal = $('#detailsModal');
                var data = $(this).data();

                modal.find('.name').text(data.name);
                modal.find('.mobile').text('+' + data.mobile);
                modal.find('.country').text(data.country);
                modal.find('.address').text(data.address);
                modal.find('.payment_via').text(data.payment_via);
                modal.find('.send_money_amount').text(parseFloat(data.send_money_amount).toFixed(2) + ' ' + data.sending_currency);
                modal.find('.including_charge').text(data.including_charge + ' ' + data.sending_currency);
                modal.find('.conversion_rate').text(data.conversion_rate + ' ' + data.recipient_currency);
                modal.find('.base_currency_rate').text(data.base_currency_rate + ' ' + data.sending_currency);
                modal.find('.sending_currency').text(data.sending_currency);
                modal.find('.send_amount_in_base_currency').text(data.send_amount_in_base_currency + " {{ __($general->cur_text) }}");
                modal.find('.receivable_amount').text(data.recipient_amount);
                modal.find('.mtcn_number').text('#' + data.mtcn_number);
                modal.find('.trx').text('#' + data.trx);
                modal.find('.delivery_charge').text(data.delivery_charge + ' ' + data.sending_currency);
                modal.find('.total_payable_amount').text(data.total_payable_amount);
                modal.find('.delivery_method').text(data.delivery_method);
                modal.find('.sent_at').text(data.sent_at);
                modal.find('#view_image').attr("href", data.image_pay);

                modal.find('.gateway_charge').text(parseFloat(data.deposit.charge ?? 0).toFixed(2) + " {{ __($general->cur_text) }}");

                if (data.status == 0 && (data.payment_status == 0 || data.payment_status == 3)) {
                    modal.find('.modal-footer form [name=id]').val(data.id);
                    modal.find('.modal-footer form :submit').removeAttr('disabled');
                    modal.find('.modal-footer').show();
                } else {
                    modal.find('.modal-footer form [name=id]').val('');
                    modal.find('.modal-footer').hide();
                }

                if (data.image_pay == '')
                    modal.find('.view_imagen').hide();
                else
                    modal.find('.view_imagen').show();
                

                modal.modal('show');
            });

            $('.feedbackBtn').on('click', function() {
                var modal = $('#feedbackModal');
                modal.find('.admin_feedback').text($(this).data('admin_feedback'));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush

@push('style-lib')
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600&display=swap" rel="stylesheet">
@endpush
@push('style')
    <style>
        .dashboard-card .user__content h4 {
            font-family: "rajdhani", sans-serif;
            font-weight: 500;
        }
    </style>
@endpush
