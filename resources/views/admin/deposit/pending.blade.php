@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        @if (request()->routeIs("admin.$type.list") || request()->routeIs("admin.$type.method"))
            <div class="col-xxl-3 col-sm-6 mb-30">
                <x-widget bg="success" color="white" link='{{ route("admin.$type.successful") }}' style="4" title="Successful {{ ucfirst($type) }}" value="{{ __($general->cur_sym) }}{{ showAmount($successful) }}" />
            </div>
            <div class="col-xxl-3 col-sm-6 mb-30">
                <x-widget bg="6" color="white" link='{{ route("admin.$type.pending") }}' style="4" title="Pending {{ ucfirst($type) }}" value="{{ __($general->cur_sym) }}{{ showAmount($pending) }}" />
            </div>
            <div class="col-xxl-3 col-sm-6 mb-30">
                <x-widget bg="pink" color="white" link='{{ route("admin.$type.rejected") }}' style="4" title="Rejected {{ ucfirst($type) }}" value="{{ __($general->cur_sym) }}{{ showAmount($rejected) }}" />
            </div>
            <div class="col-xxl-3 col-sm-6 mb-30">
                <x-widget bg="dark" color="white" link='{{ route("admin.$type.initiated") }}' style="4" title="Initiated {{ ucfirst($type) }}" value="{{ __($general->cur_sym) }}{{ showAmount($initiated) }}" />
            </div>
        @endif

        <div class="col-md-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>@lang('Operación')</th>
                                    <th>@lang('Capture')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $receivingCountries         = \App\Models\Country::receivableCountries()->get(); @endphp
                                @php $i = 0; $combinedId = 0; @endphp
                                @forelse($deposits as $deposit)
                                    @php
                                        if($combinedId != $deposit->combined_id)
                                        {
                                            $combinedId = $deposit->combined_id;
                                        }else{
                                            continue;
                                        }

                                        $i++;
                                        $details = $deposit->detail ? json_encode($deposit->detail) : null;
                                    @endphp
                                    <tr style="<?= (($i % 2) == 0) ? '' : 'background-color: #8595df63;'?>">
                                        <td>
                                            <span class="fw-bold">{{ @$deposit->user->fullname }}</span><br>
                                            <b>@lang('Gateway | Transaction')</b>
                                            <span class="fw-bold"> <a href="{{ appendQuery('method', @$deposit->gateway->alias) }}">{{ __(@$deposit->gateway->name) }}</a> </span>
                                            <br>
                                            <small> {{ $deposit->trx }} </small>
                                            <hr>
                                            @php
                                                $deposit_amount = 0;
                                                $deposit_charge = 0;
                                                $convert_final_amo = 0;
                                                $method_currency = '';
                                                foreach(\App\Models\Deposit::where('combined_id', $deposit->combined_id)->with('sendMoney')->get() as $itm)
                                                {
                                                    $deposit_amount += $itm->amount;
                                                    $deposit_charge += $itm->charge;
                                                    $convert_final_amo += $itm->sendMoney->recipient_amount;
                                                    $method_currency = $itm->sendMoney->recipient_currency;
                                                }
                                            @endphp
                                            @lang('Amount')<br>
                                            {{ __($general->cur_sym) }}{{ showAmount($deposit_amount) }}
                                            <br>
                                            <strong>{{ showAmount($convert_final_amo) }} {{ __($method_currency) }}</strong>
                                            <hr>
                                            @lang('Status')
                                            <br>
                                            @php echo $deposit->statusBadge @endphp
                                            <hr>
                                            @lang('Numero de envío de hoy')
                                            <br>
                                            @php echo \App\Models\CombinedDeposit::find($deposit->combined_id)->count_by_day @endphp
                                        </td>

                                        <td>
                                            @php $banco_selected = ''; @endphp
                                            @if ($details != null)
                                                @foreach (json_decode($details) as $val)
                                                    @if ($deposit->method_code >= 1000)
                                                        @if($val->type == 'select')
                                                            @if ($val->value)
                                                                <div class="row">
                                                                    <div class="col-md-12 p-2">
                                                                        <strong title="@lang('Banco de depósito')">
                                                                            Banco de depósito
                                                                        </strong>
                                                                        @php $banco_selected = $val->value; @endphp
                                                                        <span class="badge badge--info">{{$val->value}}</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @endif
                                                @endforeach
                                                @foreach (json_decode($details) as $val)
                                                    @if ($deposit->method_code >= 1000)
                                                        @if($val->type == 'file')
                                                            @if ($val->value)
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <img src="{{ route('admin.download.attachment', encrypt(getFilePath('verify') . '/' . $val->value)) }}" style="max-height: 200px;">
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @endif
                                                @endforeach
                                                @if ($deposit->method_code < 1000)
                                                    @include('admin.deposit.gateway_data', ['details' => json_decode($details)])
                                                @endif
                                            @endif
                                        </td>
                                        <td style="text-align: center">
                                            <button type="button" class="btn btn--info btn-sm ms-1 editAmounts" data-combined_id="{{ $deposit->combined_id }}" data-bank_selected="{{$banco_selected}}" data-action="{{ route('admin.deposit.save_amount', $deposit->combined_id) }}"><i class="la la-usd"></i> @lang('Editar monto y Banco')</button>
                                            <br>
                                            <br>
                                            <button type="button" class="btn btn--warning btn-sm ms-1 editRecipientButton" 
                                                @php
                                                    $sendMoney = \App\Models\SendMoney::find($deposit->send_money_id);
                                                    $receivingCountry           = @$receivingCountries->where('currency', $sendMoney->recipient_currency)->first();
                                                    $_rec = $sendMoney->recipient;
                                                    $recipient_id               = $_rec->id;
                                                    $recipients = \App\Models\Recipient::where('user_id', $sendMoney->user_id)->pluck('name', 'id');
                                                @endphp
                                                data-recipients="{{json_encode($recipients)}}"
                                                data-country_delivery_method_id="{{$receivingCountry->countryDeliveryMethods}}"
                                                data-sendmoneyid="{{$sendMoney->id}}"
                                                data-recipient_country_id="{{$sendMoney->recipient_country_id}}"
                                                data-recipient_id="{{$recipient_id}}"
                                                data-combined_id="{{ $deposit->combined_id }}"><i class="las la-edit"></i> @lang('Editar cuentas')</button>
                                            <br>
                                            <br>
                                            <a class="btn btn-sm btn--primary ms-1" href='{{ route("admin.$type.details", $deposit->id) }}'>
                                                <i class="la la-desktop"></i>@lang('Details')
                                            </a>
                                            <br>
                                            <br>
                                            <form action="{{ route('admin.deposit.approve', $deposit->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn--success btn-sm ms-1 " data-combined_id="{{ $deposit->combined_id }}" data-bank_selected="{{$banco_selected}}" data-question="@lang('Are you sure to approve this transaction?')"><i class="las la-check-double"></i>
                                                    @lang('Approve')
                                                </button>
                                            </form>
                                            <br>
                                            <br>
                                            <button type="button" class="btn btn--danger btn-sm ms-1 rejectBtn" data-id="{{ $deposit->id }}"><i class="las la-ban"></i> @lang('Reject')</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($deposits->hasPages())
                    <div class="card-footer py-4">
                        @php echo paginateLinks($deposits) @endphp
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
    {{-- REJECT MODAL --}}
    <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if ($type == 'payment')
                            @lang('Reject Payment Confirmation')
                        @else
                            @lang('Reject Deposit Confirmation')
                        @endif
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.deposit.reject') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <p>@lang('Are you sure to reject this transaction?')</p>

                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Reason for Rejection')</label>
                            <textarea name="message" maxlength="255" class="form-control" rows="5">{{ old('message') }}</textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="confirmationModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="question"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('No')</button>
                        <button type="submit" class="btn btn--primary">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="editRecipients" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3" id="divUserEdit"></div>
                    <div class="mb-3" id="divUserEditForm">

                        {{-- <div class="form-group">
                            <select class="form-control" name="new_recipient_id" id="new_recipient_id"></select>
                            <center><button class="btn btn--base btn--danger mt-1" id="deleteRecipient"><i class="fas fa-trash"></i></button></center>
                        </div> --}}

                        <form action="{{route('admin.send.money.edit_recipient')}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="recipient_id" id="recipient_id" />
                            <input type="hidden" name="sendmoneyid" id="sendmoneyid" />
                            <select class="form-control" name="new_recipient_id" id="new_recipient_id"></select>
                            @include($activeTemplate . 'user.send_money._partial_form', ['class' => 'mb-3', 'showLimit' => true, 'hide_phone' => true, 'hide_email' => true])
                            <div class="mb-3">
                                <button type="submit" class="btn btn-success">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="cambioMontos" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="question"></p>

                        <p id="info_banco" class="bnk"></p>
                        <div class="form-group bnk">
                            <label class="fw-bold mt-2">@lang('Seleccione un banco')</label>
                            <select id="banco" name="banco" class="select2-basic">
                                <option value="">Seleccione un banco</option>
                                @foreach (\App\Models\Bank::where('active', true)->where('recibe', true)->get() as $itm)
                                <option value="{{ $itm->name }}" >{{ $itm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="sect-amounts">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('No')</button>
                        <button type="submit" class="btn btn--primary">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            let defaultSelectOption = `@lang('Select One')`;
            let serviceURL = "{{ route('services') }}";
            var receivingCountryId = 0;
            let deliveryMethod = $('#deliveryMethod');
            let serviceLabel = `@lang('Service')`;
            var recipient_id = 0;


            function setDeliveryMethods(countryDeliveryMethods) {
                let options = `<option value="" selected>${defaultSelectOption}</option>`;
                let deliveryMethod = $('#deliveryMethod');


                if (countryDeliveryMethods) {

                    countryDeliveryMethods.forEach(countryDeliveryMethod => {
                        if (countryDeliveryMethod.delivery_method) {
                            let fixedCharge = countryDeliveryMethod.charge ? countryDeliveryMethod.charge.fixed_charge : 0;
                            let percentCharge = countryDeliveryMethod.charge ? countryDeliveryMethod.charge.percent_charge : 0;
                            options += `<option value="${countryDeliveryMethod.delivery_method.id}" data-fixed_charge="${fixedCharge}" data-percent_charge="${percentCharge}">${countryDeliveryMethod.delivery_method.name}</option>`;
                        }
                    });
                }

                deliveryMethod.html(options);
            }

            $("#new_recipient_id").on("change", function(){
                changeRecipient($("#new_recipient_id").val());
            });
            
            $('.editRecipientButton').on('click', function() {
                var modal = $('#editRecipients');
                modal.modal('show');

                var recipients = $(this).data('recipients');
                //console.log(recipients);

                $('#new_recipient_id option').remove();
                for(var k in recipients) {
                    $("#new_recipient_id").append(new Option(recipients[k], k));
                    $("#delete_recipient_id").append(new Option(recipients[k], k));
                }

                recipient_id = $(this).data('recipient_id');
                $("#new_recipient_id option[value='" + recipient_id + "']").attr("selected", "selected")

                var countryDeliveryMethods = $(this).data('country_delivery_method_id')
                var sendmoneyid = $(this).data('sendmoneyid')
                receivingCountryId = $(this).data('recipient_country_id')
                
                setDeliveryMethods(countryDeliveryMethods);
                
                $("#sendmoneyid").val(sendmoneyid);
                changeRecipient(recipient_id);
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

            deliveryMethod.on('change', function () {
                let deliveryMethodId = $(this).val();
                setServices();
            }).change();

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

            function setServices() {
                let serviceDiv = $('.services-div');
                let deliveryMethodId = $('#deliveryMethod').val();
                //let receivingCountryId = receiver.val();
                if (deliveryMethodId != 0 && deliveryMethodId) {
                    $.ajax({
                        type: "GET",
                        url: serviceURL,
                        data: {
                            'country_id': receivingCountryId,
                            'delivery_method_id': deliveryMethodId
                        },
                        success: function (response) {
                            if (response.status) {
                                let services = response.data.services;
                                let options = `<option value="" selected disabled>${defaultSelectOption}</option>`;

                                $.each(services, function (index, element) {
                                    options += `<option value="${element.id}">${element.name}</option>`;
                                });

                                if (services.length) {
                                    let html = `
                                    <label class="text--accent sm-text d-block fw-md mb-2">${serviceLabel}</label>
                                    <div class="form--select-light">
                                        <select class="form-select form--select countryServices" name="service" required>
                                            ${options}
                                        </select>
                                    </div>`;
                                    serviceDiv.html(html);

                                    $('.countryServices').trigger('change');
                                }
                            }
                        }
                    });
                } else {
                    serviceDiv.empty();
                    $('.formData').empty();
                }
            }

            $('.rejectBtn').on('click', function() {
                console.log("click2")
                var modal = $('#rejectModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.modal('show');
            });

            $(document).on('click','.editAmounts', function () {
                var modal   = $('#cambioMontos');
                let data    = $(this).data();
                console.log(data)
                $.get("{{route('admin.deposit.edit_amount')}}?combined_id=" + data.combined_id, function(data_rt){
                    console.log(data_rt);
                    
                    console.log(data.bank_selected);
                    $(".bnk").addClass("d-none");
                    if(data.bank_selected != ""){
                        $("#info_banco").html('El banco seleccionado es <span class="badge badge--info">' + data.bank_selected + '</span>, si desea cambiarlo, elija uno de la siguiente lista');
                        $(".bnk").removeClass("d-none");
                    }

                    
                    $("#sect-amounts").html(data_rt);

                    modal.find('.question').text(`${data.question}`);
                    modal.find('form').attr('action', `${data.action}`);
                    modal.modal('show');
                });
            });

            $(document).on('click','.confirmationBtn', function () {

                var modal   = $('#confirmationModal');
                let data    = $(this).data();

                // $.get("{{route('admin.deposit.edit_amount')}}?combined_id=" + data.combined_id, function(data){
                //     console.log(data);
                //     $("#sect-amounts").html(data);
                // });

                modal.find('.question').text(`${data.question}`);
                modal.find('form').attr('action', `${data.action}`);
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush

@push('breadcrumb-plugins')
    <x-search-form dateSearch='yes' />
@endpush


@push('style')
    <style>
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
        label.btn-selected__label.flex-grow-1.w-100 {
            border: 1px solid rgba(0,0,0,.125) !important;
            border-radius: 0.25rem !important;
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
    </style>
@endpush