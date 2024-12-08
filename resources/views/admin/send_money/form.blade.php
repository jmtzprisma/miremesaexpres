@extends('admin.layouts.app')

@push('style')
<style>
    .form--control[type="file"] {
        line-height: 37px;
        padding-left: 10px;
    }
</style>
@endpush

@section('panel')
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
            <h4 class="text-center">@lang('Send Money Form')</h4>
            <form action="{{ route('admin.send.money.send_money_form') }}" class="card-body container-fluid" method="get" id="frmSend">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="form-group col-md-12">
                            <select id="user" name="user" class="js-example-data-ajax" onchange="$('#frmSend').submit()">
                                <option value="">Seleccione un usuario</option>
                                @if(!is_null($user))
                                @php $usr_ = \App\Models\User::find($user->id); @endphp
                                <option value="$user->id" selected="selected">{{ $usr_->firstname . ' ' . $usr_->lastname . ' (' . $usr_->email . ' - ' . $usr_->mobile . ')' }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group col-md-12">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#addNewUser" class="btn btn--success btn--shadow w-100 btn-lg bal-btn" data-act="add">
                                <i class="las la-plus-circle"> </i>&nbsp;
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            @if(!is_null($user))
            <form action="{{ route('admin.send.money.save') }}" class="card-body container-fluid register" method="post" id="frmSendMoney" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}" >
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
                                    <div class="row g-4">
                                        <div class="col-lg-6">
                                            <label class="text--accent sm-text d-block fw-md mb-2" for="por_cobrar"><input type="checkbox" name="por_cobrar" id="por_cobrar">&nbsp;&nbsp;@lang('Por cobrar')</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control d-none">
                                        </div>
                                        <div class="col-lg-12">
                                            <textarea name="description_cc" id="description_cc" class="form-control d-none"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3 gateway">
                                    <label class="text--accent sm-text d-block fw-md mb-2" for="gateway">@lang('Pasarela manual')</label>
                                    <div class="row g-4">
                                        <div class="col-lg-12">
                                            <select class="select2-basic form-select form--select" name="gateway" id="slct_gateway" onchange="consultaViser()" required>
                                                <option value="">@lang('Select One')</option>
                                                @foreach ($gatewayCurrency as $data)
                                                    <option @selected(old('gateway') == $data->id) data-currency="{{$data->currency}}" value="{{ $data->id }}">{{ $data->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3" id="viser"></div>
                                <div class="mb-3 banco">
                                    <div id="imageContainer"></div>
                                    <button type="button" id="btnPaste" class="btn btn--primary">Desde portapapeles</button>
                                    <input name="payment_type" type="hidden" value="2">
                                    <small class="text--danger insufficientBalanceError d-none">@lang('You don\'t have sufficient balance. Your current balance is') {{ $general->cur_text }}</small>
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
                                                @lang('Agregar nuevo o editar destinatario')
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
                                                        @foreach(\App\Models\Recipient::where('user_id', $user->id)->get() as $key => $recipient)
                                                        <option value="{{ $recipient->id }}" data-country_delivery_method_id="{{$recipient->country_delivery_method_id}}" data-indxkey="{{ $key }}">
                                                            @php
                                                                $num_cuenta = '';
                                                                $email = $recipient->email;
                                                                foreach (json_decode($recipient->form_data) as $val)
                                                                {
                                                                    if($val->name == 'Numero de cuenta' || $val->name == 'Codigo del banco') $num_cuenta = $val->value;
                                                                    if($val->name == 'CORREO ELECTRONICO') $email = $val->value;
                                                                }
                                                            @endphp
                                                            {{ $recipient->name }} - {{ $email }} ({{ $recipient->mobile }}) |{{$num_cuenta}}| </option>
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
                                        <li class="text-center">
                                            <button class="btn btn--info" id="buttonAddBenef" type="button">@lang('Añadir beneficiario')</button>
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
                                                <span class="d-block t-heading-font heading-clr sm-text">
                                                    @lang('Monto final a recibir')
                                                </span>
                                                <h5 class="sm-text m-0">
                                                    <span class="recipient-amount-text"></span>
                                                    <span class="recipient-currency"></span>
                                                </h5>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between d-none">
                                                <span class="d-block sm-text">
                                                    @lang('Payable In '){{ __($general->cur_text) }}
                                                </span>
                                                <h5 class="text--base sm-text m-0">
                                                    <span class="base-amount-text"></span>
                                                    <span>{{ __($general->cur_text) }}</span>
                                                </h5>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <button class="btn btn--success btn--shadow btn--lg w-100 formSubmitButton" type="submit">@lang('Continue')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            @else
            <h5 class="text-center">@lang('Seleccione un usuario')</h5>
            @endif
        </div>
    </section>

    <div class="modal custom--modal fade" id="addNewUser" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Add New User')</h5>
                    <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                        <i aria-hidden="true" class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row g-4 g-xl-0 justify-content-between align-items-center">
                            <div class="col-lg-12">
                                <form action="{{ route('admin.users.register') }}" autocomplete="off" class="login__form row g-3 g-sm-4" method="POST" onsubmit="return submitUserForm();">
                                    @csrf
                                    <div class="col-sm-6 col-xl-6 ">
                                        <label class="form-label sm-text t-heading-font heading-clr fw-md" for="user-name">@lang('Username')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="la la-user"></i>
                                            </span>
                                            <input class="form-control form--control checkUser" id="username" id="user-name" name="username" required type="text" value="{{ old('username') }}">
                                        </div>
                                        <small class="text-danger usernameExist"></small>
                                    </div>
                                    <div class="col-sm-6 col-xl-6 ">
                                        <label class="form-label sm-text t-heading-font heading-clr fw-md" for="email">@lang('Email')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="las la-envelope"></i>
                                            </span>
                                            <input class="form-control checkUser form--control" id="email" name="email" type="email" value="{{ old('email') }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-6 ">
                                        <label class="form-label sm-text t-heading-font heading-clr fw-md">
                                            @lang('Country')
                                        </label>
                                        <div class="input-group input--group">
                                            <span class="input-group-text">
                                                <i class="las la-globe"></i>
                                            </span>
                                            <div class="form--select-light">
                                                <select aria-label="Default select example" class="form-select form--select" name="country">
                                                    @foreach ($countries as $key => $country)
                                                        <option data-code="{{ $key }}" data-mobile_code="{{ $country->dial_code }}" value="{{ $country->country }}" @if('Spain' == $country->country) selected @endif>{{ __($country->country) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-6 ">
                                        <label class="form-label sm-text t-heading-font heading-clr fw-md" for="mobile">@lang('Mobile')</label>
                                        <div class="input-group">
                                            <span class="input-group-text mobile-code d-none">
                                            </span>
                                            <input name="mobile_code" type="hidden"  value="34">
                                            <input name="country_code" type="hidden" value="ES">
                                            <input class="form-control form--control checkUser" id="mobile" name="mobile" type="number" value="{{ old('mobile') }}" required>
                                        </div>
                                        <small class="text-danger mobileExist"></small>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-sm-6">
                                            <label class="d-block sm-text mb-2">@lang('First Name')</label>
                                            <input type="text" class="form-control form--control" name="firstname" required>
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label class="d-block sm-text mb-2">@lang('Last Name')</label>
                                            <input type="text" class="form-control form--control" name="lastname" required>
                                        </div>
                                    </div>
                                    <div class="row d-none">
                                        <div class="form-group col-sm-6">
                                            <label class="d-block sm-text mb-2">@lang('Address')</label>
                                            <input type="text" class="form-control form--control" name="address">
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label class="d-block sm-text mb-2">@lang('State')</label>
                                            <input type="text" class="form-control form--control" name="state">
                                        </div>
                                    </div>

                                    <div class="row d-none">
                                        <div class="form-group col-sm-4">
                                            <label class="d-block sm-text mb-2">@lang('Zip Code')</label>
                                            <input type="text" class="form-control form--control" name="zip">
                                        </div>

                                        <div class="form-group col-sm-4">
                                            <label class="d-block sm-text mb-2">@lang('City')</label>
                                            <input type="text" class="form-control form--control" name="city">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn--xl btn--success w-100 btn--xl"> @lang('Submit') </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(!is_null($user))
    <div class="modal custom--modal fade" id="newUserModal" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Agregar nuevo destinatario')</h5>
                    <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                        <i aria-hidden="true" class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.send.money.add_receipt') }}" id="formAddReceipt" class="card-body container-fluid" method="post">
                        @csrf
                        <input type="hidden" name="user_id" value="{{$user->id}}">
                        <select class="form-control" name="recipient_id" id="recipient_id">
                            <option value="">Seleccion un destinatario para editar</option>
                            @foreach(\App\Models\Recipient::where('user_id', $user->id)->pluck('name', 'id') as $key => $itm)
                            <option value="{{$key}}">{{$itm}}</option>
                            @endforeach
                        </select>
                        @include($activeTemplate . 'user.send_money._partial_form', ['class' => 'mb-3', 'showLimit' => true, 'hide_phone' => true, 'hide_email' => true])
                        <button class="btn btn--success btn--xl w-100 formSubmitButtonAddReceipt" type="submit">@lang('Continue')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection
@push('script')
    <script>
        "use strict";

        let mobileElement = $('.mobile-code');
        $('select[name=country]').change(function() {
            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            //$('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));
            mobileElement.text(`+${$('select[name=country] :selected').data('mobile_code')}`);
        });

        mobileElement.text(`+ ${$('select[name=country] :selected').data('mobile_code')}`);
        
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
        let varAdmin = true;

        if (deliveryMethodId) {
            deliveryMethodId *= 1;
        }

        let defaultSelectOption = `@lang('Select One')`;
        let serviceStatus = true;
        let urlConsultaViser = "{{ route('admin.send.money.consulta_viser') }}";
        let serviceURL = "{{ route('services') }}";
        let serviceLabel = `@lang('Service')`;
        let isAgent = false;

        @if(!is_null($user))
        let urlBenef = "{{ route('admin.send.money.view_card_benef', $user->id) }}";
        @endif
        const recipients = []
        @if(!is_null($user))
        @foreach(\App\Models\Recipient::where('user_id', $user->id)->get() as $recipient)
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
            @foreach (json_decode($recipient->form_data) as $key => $itm)
            ,'{{ $itm->value }}'
            @endforeach
                ]
            )
        @endforeach
        @endif

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
            $("#newUserModal").modal("show");
            
            $("#deliveryMethod").val($("#deliveryMethod option:eq(1)").val())
            $("#deliveryMethod").trigger("change");
            setTimeout(() => {
                $("[name='service']").val($("[name='service'] option:eq(1)").val());
                $("[name='service']").trigger("change");
            }, 1000);
        })

        $("#recipient_id").on("change", function(){
            changeRecipient($("#recipient_id").val());
        });

        function changeRecipient(recipient_id){

            $.ajax({
                headers : {
                    'X-CSRF-Token' : '{{csrf_token()}}',
                },
                type: "POST",
                url: '{{ route('admin.send.money.consulta_destinatarios_form') }}',
                data: {
                    'recipient_id': recipient_id
                },
                success: function (response) {
                    console.log(response);
                    
                    $("#recipient_id").val(recipient_id);
                    $("#deliveryMethod").val(response.deliveryMethod)
                    $("#deliveryMethod").trigger("change");
                    setTimeout(() => {
                        $("[name='service']").val(response.service_id);
                        $("[name='service']").trigger("change");
                    }, 1500);
                    setTimeout(() => {
                        jQuery.each(response, function(i, val) {
                            var inp_obj = String(val).split('|');
                            if(i == 'name' || i == 'mobile' || i == 'email'){
                                $("[name='recipient["+i+"]']").val(val);
                            }else if(inp_obj.length > 1){
                                if(inp_obj[0] == 'text'){
                                    $("[name='"+i+"']").val(inp_obj[1]);
                                }else if(inp_obj[0] == 'select'){
                                    $("[name='"+i+"']").val(inp_obj[1])
                                    $("[name='"+i+"']").trigger("change");
                                }
                            }else{
                                if(i != 'service_id' && i != 'deliveryMethod') $("[name='"+i+"']").val(val)
                            }
                        });
                    }, 2500);
                }
            });

            }

        function consultaViser()
        {
            $("#viser").html('');
            
            let sender = $('[name=sending_country]');
            let currency = sender.find(':selected').data('currency');

            if(($("#slct_gateway").val() != "" && $("#slct_gateway").val() != null) && urlConsultaViser != "")
                $.ajax({
                    type: "GET",
                    url: urlConsultaViser,
                    data: {
                        gateway_id: $("#slct_gateway").val(),
                        currency: currency
                    },
                    success: function (response) {
                        $("#viser").html(response);

                        
                        $("input[type='file']").on("change", function(e){
                            const imageCont = document.getElementById("imageContainer"); 
                            const file = e.target.files[0];
                            const blob = new Blob([file], {type: file.type});
                            var img = new Image();
                            img.src = URL.createObjectURL(blob);
                            imageCont.innerHTML = ''; // Clear any previous image
                            imageCont.appendChild(img);
                        });

                    }
                });
        }

    </script>

    <script src="{{ asset('assets/global/js/sendMoney.js?v24') }}"></script>
    <script>
        (function($) {
            "use strict";

            $("#por_cobrar").on("change", function(){
                if($("#por_cobrar").is(":checked"))
                {
                    $(".gateway").addClass('d-none');
                    $("#fecha_vencimiento").removeClass('d-none');
                    $("#fecha_vencimiento").attr('required', true);
                    $("#description_cc").removeClass('d-none');
                    $("#description_cc").attr('required', true);
                    $("#slct_gateway").attr('required', false);
                }else{
                    $("#fecha_vencimiento").addClass('d-none');
                    $(".gateway").removeClass('d-none');
                    $("#fecha_vencimiento").attr('required', false);
                    $("#slct_gateway").attr('required', true);
                    $("#description_cc").addClass('d-none');
                    $("#description_cc").attr('required', false);
                }
            })

            @if(!is_null($user))
            const btnPaste = document.getElementById("btnPaste"); 
            const imageContainer = document.getElementById("imageContainer"); 
            
            btnPaste.addEventListener('click', function() {
                // Request clipboard access
                navigator.clipboard.read().then(function(clipboardItems) {
                    clipboardItems.forEach(function(clipboardItem) {
                        clipboardItem.getType('image/png').then(function(blob) {
                            var img = new Image();
                            img.src = URL.createObjectURL(blob);
                            imageContainer.innerHTML = ''; // Clear any previous image
                            imageContainer.appendChild(img);



                            const file = new File([blob], 'clipboard_image.png', {
                                            type: 'image/png',
                                        });
                                        
                            // Create a custom FileList object and dispatch a change event
                            const fileList = new DataTransfer();
                            fileList.items.add(file);

                            var objFiles = document.getElementsByTagName("input");
                                                            
                            for (i = 0; i < objFiles.length; ++i) {
                                if(objFiles[i].type == 'file'){
                                objFiles[i].files = fileList.files;
                            
                                // Trigger a change event to make it work across browsers
                                const event = new Event('change', { bubbles: true });
                                objFiles[i].dispatchEvent(event);
                                }
                            
                            }

                        }).catch(function(error) {
                            console.error('Error reading clipboard image data: ', error);
                        });
                    });
                }).catch(function(error) {
                    console.error('Error accessing clipboard: ', error);
                });
            });
            
            @endif
            
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

                //$("#amount_0").val(recipientAmount);

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

            $('.showLimit').on('click', function() {
                $('#limitModal').modal('show');
            });

            @if (old('payment_type') == 1)
                $('.walletPayment').click();
            @endif

            @if (old('payment_type') == 2)
                $('.directPayment').click();
            @endif
            
            $('.select2-basic').select2();

            $(".js-example-data-ajax").select2({
                ajax: {
                    url: "{{ route('admin.send.money.consulta_usuarios') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                    return {
                        'token': '{{ csrf_token() }}',
                        q: params.term, // search term
                        page: params.page
                    };
                    },
                    processResults: function (data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        params.page = params.page || 1;

                        return {
                            results: data,
                            pagination: {
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                placeholder: 'Buscar usuario',
                minimumInputLength: 3,
            });

            @if(is_null($user))
            $(".js-example-data-ajax").on("select2:open", function (e) { 
                setTimeout(() => {
                    document.querySelector('.select2-search__field').focus();
                }, 500);
            });
            $(".js-example-data-ajax").select2('open');

            $(".select2-search__field").on("input", function(){
                var txt = $(this).val()

                //txt = txt.replace(" ", "");
                txt = txt.replace("+", "");

                $(this).val(txt);
            })
            @endif

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
                    url: "{{ route('admin.send.money.add_receipt') }}",
                    data:  fd,
                    contentType: false,
                    cache: false,
                    processData:false,
                    success: function (response) {
                        if($("#recipient_id").val() != "")
                        {
                            window.location.reload();
                        }
                        $("#newUserModal").modal("toggle");

                        var dt = $("#recipient_name").val() + ($("input[name=correo_electronico]").length ? (" (" + $("input[name=correo_electronico]").val() + ")") : '');
                        $('[id*=recipient_account]')
                            .append($("<option></option>")
                                        .attr("value", response.id)
                                        .attr("data-country_delivery_method_id", $("#deliveryMethod").val())
                                        .text(dt)); 
                        $('[id*=recipient_account]').trigger('change');

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

        .select2-container {
            z-index: 1 !important;
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

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid rgba(0,0,0,.125) !important;
            border-radius: 0.25rem !important;
        }
        .btn-selected {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 12px 30px;
            cursor: pointer;
            text-align: center;
            flex-shrink: 0;
            letter-spacing: 0.03em;
            transition: all 0.3s ease;
            border-radius: 3px;
        }

        .icon--lg {
            width: 60px;
            height: 60px;
            line-height: 60px;
            font-size: 28px;
        }
        .icon--circle {
            border-radius: 50%;
            text-align: center;
        }
        .icon {
            position: relative;
            display: grid;
            place-items: center;
            isolation: isolate;
        }
        .btn-selected__text {
            display: inline-block;
            font-family: "Poppins", sans-serif;
            font-weight: 500;
            line-height: 1;
        }
        .btn-selected__input {
            display: none;
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
        .gateway .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: inherit !important;
            position: absolute !important;
            top: 0 !important;
            right: 0 !important;
            width: 26px !important;
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

