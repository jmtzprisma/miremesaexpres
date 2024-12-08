@extends($activeTemplate . 'layouts.master')
@section('content')
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
            @if(auth('admin')->user())
            <h5 class="text--danger">Es necesario cerrar el administrador para realizar esta operación como usuario</h5>
            @endif
            <h4 class="text-center">@lang('Send Money Form')</h4>
            <form action="{{ route('user.send.money.save') }}" class="card-body container-fluid register" id="frmSendMoney" method="post" enctype="multipart/form-data">
                @csrf
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

                                        <div class="mb-3 d-none">
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

                                        <div class="mb-3 d-none">
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
                                <div class="mb-3">
                                    <h5 class="">@lang('Payment Via')</h5>
                                    <div class="d-flex flex-wrap gap-3">
                                        <label class="btn-selected__label flex-grow-1" data-value="2" for="directBtn">
                                            <input class="btn-selected__input directPayment" id="directBtn" name="payment_type" required type="radio" value="2">
                                            <div class="btn-selected btn-selected--secondary">
                                                <span class="btn-selected__icon">
                                                    <img alt="" class="img-fluid" src="{{ getImage($activeTemplateTrue . 'images/credit-card-icon.png') }}">
                                                </span>
                                                <span class="btn-selected__text ">
                                                    @lang('Transferencia bancaria')
                                                </span>
                                            </div>
                                        </label>
                                        @php
                                            $btnBanco = \App\Models\Gateway::where('alias','Cryptopocket')->first();
                                        @endphp
                                        @if($btnBanco->status)
                                        @if($general->pay_card_cripto)
                                        <label class="btn-selected__label flex-grow-1 btnCrypto" data-value="3" for="cryptoBtn">
                                            <input class="btn-selected__input cryptoPayment" id="cryptoBtn" name="payment_type" required type="radio" value="3">
                                            <div class="btn-selected btn-selected--secondary">
                                                <span class="btn-selected__icon">
                                                    <img alt="" class="img-fluid" src="{{ getImage($activeTemplateTrue . 'images/credit-card-icon.png') }}">
                                                </span>
                                                <span class="btn-selected__text ">
                                                    @lang('Tarjeta débito/crédito')
                                                </span>
                                            </div>
                                        </label>
                                        @endif
                                        <label class="btn-selected__label flex-grow-1 btnCrypto" data-value="4" for="cryptoBtnPsd">
                                            <input class="btn-selected__input cryptoPaymentPsd" id="cryptoBtnPsd" name="payment_type" required type="radio" value="4">
                                            <div class="btn-selected btn-selected--secondary">
                                                <span class="btn-selected__icon">
                                                    <img alt="" class="img-fluid" src="{{ getImage($activeTemplateTrue . 'images/credit-card-icon.png') }}">
                                                </span>
                                                <span class="btn-selected__text ">
                                                    @lang('Transferencia PSD2')
                                                </span>
                                            </div>
                                        </label>
                                        @endif
                                    </div>
                                    <small class="text--danger insufficientBalanceError d-none">@lang('You don\'t have sufficient balance. Your current balance is') {{ showAmount(auth()->user()->balance) }} {{ $general->cur_text }}</small>
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
                                        <input class="btn-selected__input" id="newRecipient" name="user_recipient" required type="button">
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
                                <div id="recipients">
                                    <input type="hidden" id="input_key" name="input_key" value="0"/>
                                    <div class="card custom--card">
                                        <div class="card-header"></div>
                                        <div class="card-body">
                                            <div class="mb-3">                           
                                                <label class="text--accent sm-text d-block fw-md mb-2">Cuenta</label>
                                                <div class="form--select-light">
                                                    <select class="form-select form--select selectRecipients" id="recipient_account_0" name="recipient[0][id]" required="" data-indx="0">
                                                        <option value="" selected="" disabled="">Seleccionar Uno</option>
                                                        @foreach(\App\Models\Recipient::where('user_id', auth()->user()->id)->get() as $key => $recipient)
                                                        <option value="{{ $recipient->id }}" data-country_delivery_method_id="{{$recipient->country_delivery_method_id}}" data-indxkey="{{ $key }}">
                                                            @php
                                                                $num_cuenta = '';
                                                                foreach (json_decode($recipient->form_data) as $val)
                                                                    if($val->name == 'Numero de cuenta' || $val->name == 'Codigo del banco')
                                                                        $num_cuenta = $val->value;
                                                            @endphp
                                                            {{ $recipient->name }} - {{ $recipient->email }} ({{ $recipient->mobile }}) |{{$num_cuenta}}|</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">                           
                                                <label class="text--accent sm-text d-block fw-md mb-2">Monto</label>
                                                <div class="input-group">
                                                    <input class="form-control form--control" id="amount_0" name="recipient[0][amount]" required type="number" step="any" value="{{ old('amount') ?? null }}">
                                                    <div class="sending-currency ms-1">VEF</div>
                                                </div>
                                            </div>
                                            <small class="text--danger amountSumIncorrectError d-none">@lang('La suma de los montos no es igual al monto enviado')</small>
                                            <div class="mb-3" id="info_0"></div>
                                        </div>
                                    </div>

                                </div>
                                <div class="mb-3 mt-4">
                                    <ul class="list list--column payment-table">
                                        <li>
                                            <button class="btn btn--base btn--xl w-100" id="buttonAddBenef" type="button">@lang('Añadir beneficiario')</button>
                                        </li>
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
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="d-block sm-text">
                                                    Monto a recibir
                                                </span>
                                                <h5 class="text--base sm-text m-0 d-flex">
                                                    <div class="total_pay ms-1"></div>
                                                    <div class="recipient-currency ms-1"></div>
                                                </h5>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <button class="btn btn--base btn--xl w-100 formSubmitButton" type="submit" id="btnSendMoneySubmit" style="display: none;">@lang('Continue')</button>
                                <button class="btn btn--base btn--xl w-100 formSubmitButton" type="button" id="btnSendMoney">@lang('Continue')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

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
                    <ul class="list list--column payment-table" id="transfer-limit">
                        <li>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="d-block t-heading-font heading-clr sm-text">
                                    @lang('Per Transfer')
                                </span>
                                <h6 class="fw-md heading-clr t-heading-font sm-text m-0">
                                    <span class="send_money_limit">0</span>
                                    <span class="sending-currency"></span>
                                </h6>
                            </div>
                        </li>

                        <li>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="d-block t-heading-font heading-clr sm-text">
                                    @lang('Daily Limit')
                                </span>
                                <h5 class="sm-text m-0">
                                    <span class="daily_send_money_limit">0</span>
                                    <span class="sending-currency"></span>
                                </h5>
                            </div>
                        </li>

                        <li>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="d-block t-heading-font heading-clr sm-text">
                                    @lang('Available for Today')
                                </span>
                                <h5 class="sm-text m-0">
                                    <span class="today_limit">0</span>
                                    <span class="sending-currency"></span>
                                </h5>
                            </div>
                        </li>

                        <li>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="d-block t-heading-font heading-clr sm-text">
                                    @lang('Monthly Limit')
                                </span>
                                <h5 class="sm-text m-0">
                                    <span class="monthly_send_money_limit">0</span>
                                    <span class="sending-currency"></span>
                                </h5>
                            </div>
                        </li>

                        <li>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="d-block t-heading-font heading-clr sm-text">@lang('Available for This Month')</span>
                                <h5 class="sm-text m-0">
                                    <span class="this_month_limit">0</span>
                                    <span class="sending-currency"></span>
                                </h5>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal custom--modal fade" id="newUserModal" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('¿Quién recibe el dinero?')</h5>
                    <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                        <i aria-hidden="true" class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('user.send.money.add_receipt') }}" id="formAddReceipt" class="card-body container-fluid" method="post">
                        @csrf
                        @include($activeTemplate . 'user.send_money._partial_form', ['class' => 'mb-3', 'showLimit' => true])
                        <button class="btn btn--base btn--xl w-100 formSubmitButtonAddReceipt" type="submit">@lang('Continue')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal custom--modal fade" id="nameCard" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Cláusula informativa')</h5>
                    <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                        <i aria-hidden="true" class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="">
                                La función de Fintech Payments, S.L., a través de su plataforma "Cryptopocket", consistirá únicamente en asistir al usuario en la compraventa de los <b>activos digitales (stablecoins)</b>. Tras su adquisición, los activos digitales serán remitidos a través de la plataforma Cryptopocket a la plataforma Andrés Te Lo Cambia, donde esta última entidad procederá según las instrucciones del usuario, y sin vinculación alguna a Cryptopocket o a Fintech Payments, S.L. <br><br>
                                Para proceder a esta operación, <b>usted debe confirmar haber leído estos términos, y mostrar su conformidad y consentimiento</b> para el envío y tratamiento de los <b>activos digitales</b> adquiridos en las condiciones anteriormente descritas.
                                <br>
                                <br>
                                <center><button class="btn btn--base btn--xl w-100" type="button" id="btnContinueNameCard">@lang('Acepto y confirmo')</button></center>
                                <a href="{{ asset('assets/fintech_payments.pdf') }}" download id="download" style="display: none;"></a>
                                <br>
                                Para más información, puede consultar nuestros <b>Términos y Condiciones</b> aquí: <a href="https://cryptopocket.io/terms-conditions.html" target="_blank">https://cryptopocket.io/terms-conditions.html</a> y nuestra <b>Política de Privacidad</b> en lo que respecta al tratamiento de los datos personales de los usuarios aquí: <a href="https://cryptopocket.io/privacy-policy.html" target="_blank">https://cryptopocket.io/privacy-policy.html</a>.<br><br>

                                Para cualquier duda, puede ponerse en contacto con Cryptopocket a través del siguiente correo electrónico: <a href="mailto:support@cryptopocket.io" target="_blank">support@cryptopocket.io</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal custom--modal fade" id="alertCryptoPay" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Pagar con Tarjeta')</h5>
                    <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                        <i aria-hidden="true" class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger">Si desea realizar el pago mediante la pasarela, le será requerida la verificación mediante documento de identidad Europeo (pasaporte, nie, dni o licencia de conducir) y video identificación</div>
                            <br><a href="{{ route('user.kyc.asking') }}">@lang('Click Here to Verify')</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn--base btn--xl w-100" type="button" data-bs-dismiss="modal" >@lang('Aceptar')</button>
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
        let varAdmin = false;

        if (deliveryMethodId) {
            deliveryMethodId *= 1;
        }

        let defaultSelectOption = `@lang('Select One')`;
        let serviceStatus = true;
        let urlConsultaViser = "";
        let serviceURL = "{{ route('services') }}";
        let serviceLabel = `@lang('Service')`;
        let isAgent = false;
        let urlBenef = "{{ route('user.send.money.view_card_benef') }}";
        const recipients = []
        @foreach(\App\Models\Recipient::where('user_id', auth()->user()->id)->get() as $recipient)
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
            @if(!is_null($recipient->form_data))
            @foreach (json_decode($recipient->form_data) as $key => $itm)
            ,'{{ $itm->value }}'
            @endforeach
            @endif
                ]
            )
        @endforeach

        $("#btnBackRecipients").on("click", function(){
            $("[name='sending_country']").trigger("change");
            $("#recipient-country").trigger("change");


            $("#recipient_name").val('');
            $("#recipient_mobile").val('');
            $("#recipient_email").val('');
            $("#recipient_address").val('');

            // $("#selectRecipient").removeClass("d-none");
            // $("#nwRecipient").addClass("d-none");
        })


        function deleteBenef(_key){
            $("#divDeleteBenef_" + _key).remove();
        }

        $("[name='user_recipient']").on("click", function(){
            // var key = $("input[name='user_recipient']:checked").val();
            // //console.log(recipients[key]);
            // if(key != 'new')
            // {
                // $("[name='sending_country']").val(recipients[key][0]);
                // $("[name='sending_country']").trigger("change");
                // $("#recipient-country").val(recipients[key][1]);
                // $("#recipient-country").trigger("change");

                // $("#deliveryMethod").val(recipients[key][2])
                // $("#deliveryMethod").trigger("change");
                
                // setTimeout(() => {
                //     $("[name='service']").val(recipients[key][3]);
                //     $("[name='service']").trigger("change");


                //     setTimeout(() => {
                //         $("[name='numero_de_cuenta']").val(recipients[key][8])
                //         $("[name='cedula']").val(recipients[key][9])
                //         $("[name='tipo_de_cuenta']").val(recipients[key][10])
                //         $("[name='tipo_de_cuenta']").trigger("change");
                //     }, 500);
                // }, 500);

                // $("#recipient_name").val(recipients[key][4])
                // $("#recipient_mobile").val(recipients[key][5])
                // $("#recipient_email").val(recipients[key][6])
                // $("#recipient_address").val(recipients[key][7])
                
                $("#newUserModal").modal("show");

                
                $("#deliveryMethod").val($("#deliveryMethod option:eq(1)").val())
                $("#deliveryMethod").trigger("change");
                setTimeout(() => {
                    $("[name='service']").val($("[name='service'] option:eq(1)").val());
                    $("[name='service']").trigger("change");
                }, 1000);
            //}

            // $("#selectRecipient").addClass("d-none");
            // $("#nwRecipient").removeClass("d-none");
        })
    </script>

    <script src="{{ asset('assets/global/js/sendMoney.js?v19') }}"></script>
    <script>
        (function($) {
            "use strict";

            $("#btnSendMoney").on("click", function(){
                var paymentType = $('[name=payment_type]:checked').val();
                if(paymentType == 3 || paymentType == 4)
                {
                    // $("#info-namecard").addClass("d-none");
                    $("#nameCard").modal("show");
                }else{
                    $("#btnSendMoneySubmit").trigger('click');
                }
                
            });

            $("#btnContinueNameCard").on("click", function(){
                document.getElementById('download').click();
                // if($("#name_card").val() != "")
                // {
                    //$("#name_card_form").val($("#name_card").val());
                    setTimeout(() => {
                        $("#btnSendMoneySubmit").trigger('click');
                    }, 500);
                    
                // }else{
                //     $("#info-namecard").removeClass("d-none");
                // }
            });

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

            @if(auth()->user()->kv == 1 && is_null(auth()->user()->video_id))
            $('.cryptoPayment, .cryptoPaymentPsd').on('click', function() {
                $("#alertCryptoPay").modal("show");
            });
            @endif

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

            $(document).on('input', '.sending-amount, .recipient-amount, [id*=amount_]', function() {
                let sendingAmount = parseFloat($('[name=sending_amount]').val());
                if (sendingAmount > limitPerSendMoney || sendingAmount > availableForToday || sendingAmount > availableForThisMonth) {
                    $('.limitMessage').removeClass('d-none')
                    $('.formSubmitButton').attr('disabled', true);
                } else {
                    $('.limitMessage').addClass('d-none')
                    $('.formSubmitButton').attr('disabled', false);
                }

                var sumAmounts = 0;
                $("[id*=amount_]").each(function(){
                    sumAmounts += parseFloat($(this).val());
                })
                if (sendingAmount.toFixed(2) !=sumAmounts.toFixed(2)) {
                    $('.amountSumIncorrectError').removeClass('d-none').fadeIn()
                    $('.formSubmitButton').attr('disabled', true);
                } else {
                    $('.amountSumIncorrectError').fadeOut().addClass('d-none')
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
                var paymentType = $('[name=payment_type]:checked').val();
                if (!paymentType) {
                    notify('error', 'Please select a payment type')
                }
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
            
            //$(".selectRecipients").select2();

            $('#formAddReceipt').on('submit', function (e) {
                e.preventDefault();
                var fd = new FormData(this);    
                $("#formAddReceipt").find("input[type=file]").each(function(index, field){
                    const file = field.files[0];
                    fd.append(field.name, file);
                });
                var post = $('#formAddReceipt').serialize();
                $.ajax({
                    headers : {
                        'X-CSRF-Token' : '{{csrf_token()}}',
                    },
                    type: "POST",
                    url: "{{ route('user.send.money.add_receipt') }}",
                    data:  fd,
                    contentType: false,
                    cache: false,
                    processData:false,
                    success: function (response) {
                        $("#newUserModal").modal("toggle");

                        // var dt = $("#recipient_name").val() + " - " + $("#recipient_email").val()  + "(" + $("#recipient_mobile").val() + ") |" + $("input[name=numero_de_cuenta]").val() + "|";
                        // var newOption = new Option(dt, response.id, false, false);
                        // $('[id*=recipient_account]').append(newOption).trigger('change');
                        var dt = $("#recipient_name").val();
                        $('[id*=recipient_account]')
                            .append($("<option></option>")
                                        .attr("value", response.id)
                                        .attr("data-country_delivery_method_id", $("#deliveryMethod").val())
                                        .text(dt)); 
                        $('[id*=recipient_account]').trigger('change');

                        $('[id*=recipient_account]').each(function(i,e){
                            if(e.value == ""){
                                $("#" + e.id + ' option[value="'+ response.id +'"]').attr("selected", "selected")
                                return false;
                            }
                        });

                        document.getElementById("formAddReceipt").reset();

                    }
                });
            });

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
        .select2-container {
            z-index: 1 !important;
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
        #recipients .select2-container--default .select2-selection--single .select2-selection__arrow {
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
