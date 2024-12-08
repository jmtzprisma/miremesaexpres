@extends('admin.layouts.app')
@section('panel')


    <!-- Client Slider  -->
    <div class="section--sm section--bottom">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="client-slider">
                        @php $receivingCountries         = \App\Models\Country::receivableCountries()->get(); @endphp
                        @foreach($sendMoneys as $sendMoney)
                        <div class="client-slider__item">
                            <div class="client-card">
                                <div class="card b-radius--10 overflow-hidden box--shadow1">
                                    <div class="card-header">
                                        <div class="buttons d-flex flex-wrap gap-1 mt-2">
                                            @if ($sendMoney->status == Status::SEND_MONEY_PENDING)
                                                <button class="btn btn--base btn--danger refundButton h-45 flex-50" data-action="{{ route('admin.send.money.refund.now', $sendMoney->id) }}"><i class="fas fa-times"></i>
                                                    @lang('Reject Send Money')
                                                </button>
                                                <button class="btn btn--base btn--warning editRecipientButton h-45 flex-50"
                                                @php
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
                                                ><i class="fas fa-edit"></i>
                                                    @lang('Editar destinatario')
                                                </button>
                                            @endif
                                            @if ($sendMoney->status == Status::SEND_MONEY_PENDING && $sendMoney->country_delivery_method_id)
                                                <button class="btn btn--success confirmationBtn h-45 flex-50" data-action="{{ route('admin.send.money.pay.receiver', $sendMoney->id) }}" data-question="@lang('Have you sent money to this receipient\'s details?')"><i class="las la-shipping-fast"></i> @lang('Complete Send Money')</button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="mb-3 text-center">@lang('The recipient will receive') <span class="text--danger">{{ showAmount($sendMoney->recipient_amount) }}
                                                {{ __($sendMoney->recipient_currency) }}</span></h5>
                                        <h4 class="mb-3 text-center"> {{ @$sendMoney->user->firstname }} {{ @$sendMoney->user->lastname }}</h4>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                @lang('MTCN Number')
                                                <span class="fw-bold">{{ @$sendMoney->mtcn_number }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                @lang('Envía')
                                                <span style="float: left;">
                                                    <span class="fw-bold" style="float: left;">
                                                        {{ @$sendMoney->user->firstname }} {{ @$sendMoney->user->lastname }}<br>
                                                        @if ($sendMoney->user_id)
                                                            <a href="{{ route('admin.users.detail', $sendMoney->user_id) }}"><span>@</span>{{ @$sendMoney->user->username }}</a>
                                                        @else
                                                            <a href="{{ route('admin.agents.detail', $sendMoney->agent_id) }}"><span>@</span>{{ @$sendMoney->agent->username }}</a>
                                                        @endif
                                                    </span>
                                                    @if ($sendMoney->status == Status::SEND_MONEY_PENDING)
                                                    <button type="button" class="btn btn--success btn-sm copy" style="float: left; margin-left: 10px;" data-info="{{ @$sendMoney->user->firstname }} {{ @$sendMoney->user->lastname }}"><i class="las la-copy"></i></button>
                                                    @endif
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                @lang('Recibe')
                                                <span style="float: left;">
                                                    <span class="fw-bold" style="float: left;">
                                                        {{ $sendMoney->recipient->name }}<br>
                                                    </span>
                                                    @if ($sendMoney->status == Status::SEND_MONEY_PENDING)
                                                    <button type="button" class="btn btn--success btn-sm copy" style="float: left; margin-left: 10px;" data-info="{{ @$sendMoney->recipient->name }}"><i class="las la-copy"></i></button>
                                                    @endif
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                @lang('Sending Amount')
                                                <span style="float: left;">
                                                    <span class="fw-bold" style="float: left;">
                                                        {{ showAmount($sendMoney->recipient_amount) }} {{ $sendMoney->recipient_currency }} 
                                                    </span>
                                                    @if ($sendMoney->status == Status::SEND_MONEY_PENDING)
                                                    <button type="button" class="btn btn--success btn-sm copy" style="float: left; margin-left: 10px;" data-info="{{ $sendMoney->recipient_amount }}"><i class="las la-copy"></i></button>
                                                    @endif
                                                </span>
                                            </li>
                                           
                                            @if ($sendMoney->service_form_data)
                                                @foreach ($sendMoney->service_form_data as $val)
                                                    @continue(!$val->value)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center service-data">
                                                        {{ __($val->name) }}
                                                        <span style="float: left;">
                                                            @if ($val->type == 'checkbox')
                                                                {{ implode(',', $val->value) }}
                                                            @elseif($val->type == 'file')
                                                                @if ($val->value)
                                                                    <a class="me-3" href="{{ route('admin.download.attachment', encrypt(getFilePath('verify') . '/' . $val->value)) }}"><i class="fa fa-file"></i> @lang('Attachment') </a>
                                                                @else
                                                                    @lang('No File')
                                                                @endif
                                                            @else
                                                                <p style="float: left;">{{ __($val->value) }}</p>
                                                                @if ($sendMoney->status == Status::SEND_MONEY_PENDING)
                                                                <button type="button" class="btn btn--success btn-sm copy" style="float: left; margin-left: 10px;" data-info="{{ __($val->value) }}"><i class="las la-copy"></i></button>
                                                                @endif
                                                            @endif
                                                        </span>
                                                    </li>
                                                @endforeach
                                            @endif

                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                @lang('Status')
                                                <span class="fw-bold" title="Updated at {{ diffForHumans($sendMoney->updated_at) }}">
                                                    @php
                                                        echo $sendMoney->statusBadge;
                                                    @endphp
                                                </span>
                                            </li>

                                                @php
                                                $bank_extract = \App\Models\BankExtract::where('type', 'debito')->where('send_money_id', $sendMoney->id)->first();
                                            @endphp
                                            @if($bank_extract && $bank_extract->comprobante)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                @lang('Comprobante de pago')
                                                <img src="{{ getImage(getFilePath('sendMoney') . '/' . $bank_extract->comprobante) }}" style="max-height: 250px;">
                                            </li>
                                            @endif

                                            @if ($sendMoney->payout_by)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    @lang('Payout By')
                                                    <span class="fw-bold">
                                                        <a href="{{ route('admin.agents.detail', $sendMoney->payout_by) }}"><span>@</span>{{ @$sendMoney->payoutBy->username }}</a>
                                                    </span>
                                                </li>
                                            @endif

                                            @if (@$sendMoney->admin_feedback)
                                                <li class="list-group-item">
                                                    <strong>@lang('Admin Response')</strong>
                                                    <br>
                                                    <p>{{ __(@$sendMoney->admin_feedback) }}</p>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Client Slider End -->

    {{-- Refund MODAL --}}
    <div class="modal fade" id="rejectModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation Alert')</h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <h6>@lang('Are you sure to reject this send money?')</h6>
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Reason of Rejection')</label>
                            <textarea class="form-control" id="message" name="message" rows="5"></textarea>
                        </div>

                        <div class="alert alert-warning p-2" role="alert">
                            <p>@lang('If you reject this send money the amount will be refunded to user\'s wallet.')</p>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--dark" data-bs-dismiss="modal" type="button">@lang('No')</button>
                        <button class="btn btn--primary" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="confirmationModalPay" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST"  enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="question">@lang('Have you sent money to this receipient\'s details?')</p>
                        @php
                        $obj_bancos = \App\Models\Bank::where('active', true)->where('envia', true);
                        if(auth('admin')->user()->role_id != 1)
                        {
                            $bancos = explode(',', \App\Models\Roles::find(auth('admin')->user()->role_id)->bancos);
                            $obj_bancos = $obj_bancos->whereIn('id', $bancos);
                        }
                        @endphp
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Select Bank')</label>
                            <select id="banco" name="banco" class="select2-basic" required>
                                <option value="">Seleccione un banco</option>
                                @foreach ($obj_bancos->get() as $itm)
                                <option value="{{ $itm->id }}" >{{ $itm->name . ' (' . $itm->currency . ')' }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <input type="file" class="form-control comprobante" name="image" id="comprobante" accept=".png, .jpg, .jpeg" required>
                        </div>
                        <div class="mb-3 banco">
                            <button type="button" id="btnPaste" class="btn btn--primary">Desde portapapeles</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('No')</button>
                        <button type="submit" class="btn btn--primary">@lang('Yes')</button>
                        <div id="imageContainer"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>@lang('MTCN')</th>
                                    <th>@lang('Visible')</th>
                                    <th>@lang('Created By')</th>
                                    <th>@lang('Sender')</th>
                                    <th>@lang('Recipient')</th>
                                    <th>@lang('Delivery Method')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Estatus crypto')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sendMoneys as $sendMoney)
                                    <tr>
                                        <td>
                                            <span class="text--muted fw-bold">#{{ $sendMoney->mtcn_number }}</span>
                                            <br>
                                            <em class="text--muted text--small">{{ showDateTime($sendMoney->created_at) }}</em>
                                        </td>
                                        <td>
                                            @if ($sendMoney->visible)
                                            <a class="btn btn-sm btn--danger" href="{{ route('admin.send.money.update_visible', [$sendMoney->id, 0]) }}">
                                                <i class="las la-eye-slash"></i>
                                            </a>
                                            @else
                                            <a class="btn btn-sm btn--success" href="{{ route('admin.send.money.update_visible', [$sendMoney->id, 1]) }}">
                                                <i class="las la-eye"></i>
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($sendMoney->user_id)
                                                <span class="fw-bold">{{ @$sendMoney->user->fullname }}</span>
                                                <br>
                                                <span class="small">
                                                    <a href="{{ route('admin.users.detail', $sendMoney->user_id) }}"><span>@</span>{{ $sendMoney->user->username }}</a>
                                                </span>
                                            @else
                                                <span class="fw-bold">{{ @$sendMoney->agent->fullname }}</span>
                                                <br>
                                                <span class="small">
                                                    <a href="{{ route('admin.agents.detail', $sendMoney->agent_id) }}"><span>@</span>{{ $sendMoney->agent->username }}</a>
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ @$sendMoney->senderInfo->name }}
                                            <br>
                                            <a href="{{ route('admin.country.index') }}?search={{ @$sendMoney->sendingCountry->name }}" class="fw-bold">{{ __(@$sendMoney->sendingCountry->name) }}</a>
                                        </td>

                                        <td>
                                            {{ $sendMoney->recipient->name }}<br>
                                            <a href="{{ route('admin.country.index') }}?search={{ @$sendMoney->recipientCountry->name }}" class="fw-bold">{{ __($sendMoney->recipientCountry->name) }}</a>
                                        </td>

                                        <td>
                                            @if ($sendMoney->country_delivery_method_id)
                                                <span class="fw-bold text--danger">{{ __(@$sendMoney->countryDeliveryMethod->deliveryMethod->name) }}</span>
                                            @else
                                                <span class="text--info fw-bold">@lang('Agent')</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span>{{ showAmount($sendMoney->sending_amount) }} {{ $sendMoney->sending_currency }}</span>
                                            <i class="la la-arrow-right"></i>
                                            <span>{{ showAmount($sendMoney->recipient_amount) }} {{ __($sendMoney->recipient_currency) }}</span>
                                        </td>
                                        <td>
                                            @if($sendMoney->payment_type == 3 || $sendMoney->payment_type == 4)
                                            @if($sendMoney->coins_sent == 0)
                                            <span class="badge badge--warning">Coins en transito</span>
                                            @elseif($sendMoney->coins_sent == 1)
                                            <span class="badge badge--success">Coins enviados</span>
                                            @endif
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                echo $sendMoney->statusBadge;
                                            @endphp
                                            <br>
                                            {{ diffForHumans($sendMoney->updated_at) }}
                                        </td>

                                        <td>
                                            <a class="btn btn-sm btn--primary" href="{{ route('admin.send.money.details', $sendMoney->id) }}">
                                                <i class="las la-desktop"></i>&nbsp;&nbsp;@lang('Details')
                                            </a><br><br>
                                            <a class="btn btn-sm btn--primary" href="{{ route('admin.send.money.create_pdf', $sendMoney->id) }}">
                                                <i class="las la-file-pdf"></i>&nbsp;&nbsp;@lang('PDF')
                                            </a>
                                            @php
                                                $extract = \App\Models\BankExtract::where('type', 'debito')->where('send_money_id', $sendMoney->id)->whereNotNull('comprobante')->first();
                                            @endphp
                                            @if(!is_null($extract))<br><br>
                                                <a class="btn btn-sm btn--primary" href="{{ route('admin.download.attachment', encrypt(getFilePath('sendMoney') . '/' . $extract->comprobante)) }}"><i class="fa fa-file"></i> @lang('Comprobante de pago') </a>
                                            @endif
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
                @if ($sendMoneys->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($sendMoneys) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    
    <div id="editRecipients" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Edición de destinatarios')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3" id="divUserEdit"></div>
                    <div class="mb-3" id="divUserEditForm">

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


            $("input[type='file']").on("change", function(e){
                const imageCont = document.getElementById("imageContainer"); 
                const file = e.target.files[0];
                const blob = new Blob([file], {type: file.type});
                var img = new Image();
                img.src = URL.createObjectURL(blob);
                imageCont.innerHTML = ''; // Clear any previous image
                imageCont.appendChild(img);
            });

            const btnPaste = document.getElementById("btnPaste"); 
            const imageContainer = document.getElementById("imageContainer"); 
            
            btnPaste.addEventListener('click', function() {
                // Request clipboard access
                navigator.clipboard.read().then(function(clipboardItems) {
                    clipboardItems.forEach(function(clipboardItem) {
                        //if (clipboardItem.types.indexOf('image') !== -1) {
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
                        //}
                    });
                }).catch(function(error) {
                    console.error('Error accessing clipboard: ', error);
                });
            });

            $('.refundButton').on('click', function() {
                var modal = $('#rejectModal');
                modal.find('form').attr('action', $(this).data('action'));
                modal.modal('show');
            });

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
                // if (serviceStatus) {
                //     setServices();
                // }
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
            

        })(jQuery);


    </script>

    <script>
        (function ($) {
            "use strict";
            $(document).on('click','.confirmationBtn', function () {
                var modal   = $('#confirmationModalPay');
                let data    = $(this).data();
                modal.find('.question').text(`${data.question}`);
                modal.find('form').attr('action', `${data.action}`);
                modal.modal('show');
            });
        })(jQuery);
    </script>
<script>

$(function(e) {
    $(".copy").on("click", function(){
        console.log('entras')
        navigator.clipboard.writeText($(this).data('info'))
        .then(() => {
            console.log('Texto copiado al portapapeles')
            notify('success', 'Información copiada al portapapeles')
        })
    })
})
    // Client Slider
    let clientSlider = $(".client-slider");
    if (clientSlider) {
        clientSlider.slick({
            mobileFirst: true,
            arrows: true,
            autoplay: false,
            slidesToShow: 1,
            //autoplaySpeed: 1000,
            speed: 2000,
            nextArrow: '<button class="slick-next"><i class="las la-chevron-right f-size--24 text-primary"></i></button>',
            prevArrow: '<button class="slick-prev"><i class="las la-chevron-left f-size--24 text-primary"></i></button>',
            // responsive: [
            //     {
            //         breakpoint: 539,
            //         settings: {
            //             slidesToShow: 2,
            //         },
            //     },
            //     {
            //         breakpoint: 767,
            //         settings: {
            //             slidesToShow: 3,
            //         },
            //     },
            //     {
            //         breakpoint: 991,
            //         settings: {
            //             slidesToShow: 4,
            //         },
            //     },
            //     {
            //         breakpoint: 1199,
            //         settings: {
            //             slidesToShow: 5,
            //         },
            //     },
            //     {
            //         breakpoint: 1399,
            //         settings: {
            //             slidesToShow: 6,
            //         },
            //     },
            // ],
        });
    }
    // Client Slider End

</script>
@endpush
@push('breadcrumb-plugins')
    <form action="" method="GET" class="d-flex flex-wrap gap-2" id="frmSearch">
        <select class="form-control" name="payment_method" onchange="javascript:$('#frmSearch').submit();">
            <option value="">Todos</option>
            <option value="con_tarjeta" {{ request()->payment_method ? (request()->payment_method == 'con_tarjeta' ? 'selected' : ''): '' }}>Con tarjeta</option>
        </select>
    </form>
    <x-search-form placeholder="MTCN/Sender/Recipient" />
@endpush
