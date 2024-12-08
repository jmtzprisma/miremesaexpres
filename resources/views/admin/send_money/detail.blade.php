@extends('admin.layouts.app')
@section('panel')
    <div class="row gy-4">
        <div class="col-xl-4 col-md-6">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="mb-3 text-center">@lang('The recipient will receive') <span class="text--danger">{{ showAmount($sendMoney->recipient_amount) }}
                            {{ __($sendMoney->recipient_currency) }}</span></h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Initiated')
                            <span class="fw-bold">{{ diffForHumans($sendMoney->created_at) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('MTCN Number')
                            <span class="fw-bold">{{ @$sendMoney->mtcn_number }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Transaction Number')
                            @if ($sendMoney->deposit)
                                <span class="fw-bold">
                                    <a href="{{ route('admin.payment.list') }}?search={{ @$sendMoney->trx }}">{{ @$sendMoney->trx }}</a>
                                </span>
                            @else
                                <span class="fw-bold text-end">
                                    {{ @$sendMoney->trx }}
                                    <br>
                                    <small class="text--warning">@lang('Payment via Refunded Wallet')</small>
                                </span>
                            @endif
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Created By')
                            <span class="fw-bold">
                                @if ($sendMoney->user_id)
                                    <a href="{{ route('admin.users.detail', $sendMoney->user_id) }}"><span>@</span>{{ @$sendMoney->user->username }}</a>
                                @else
                                    <a href="{{ route('admin.agents.detail', $sendMoney->agent_id) }}"><span>@</span>{{ @$sendMoney->agent->username }}</a>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Sending Amount')
                            <span class="fw-bold">
                                {{ showAmount($sendMoney->sending_amount) }} {{ $sendMoney->sending_currency }} &nbsp;@lang('OR')&nbsp;
                                {{ showAmount($sendMoney->base_currency_amount) }} {{ __($general->cur_text) }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Source of fund')
                            <span class="fw-bold"> {{ __(@$sendMoney->sourceOfFund->name) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Sending Purpose')
                            <span class="fw-bold"> {{ __(@$sendMoney->sendingPurpose->name) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Delivery Method')
                            <span class="fw-bold"> {{ __(@$sendMoney->countryDeliveryMethod->deliveryMethod->name ?? __('Agent')) }}</span>
                        </li>

                        @if ($sendMoney->service_form_data)
                            <li class="list-group-item d-flex justify-content-center align-items-center py-3">
                                <span class="fw-bold">
                                    @lang('Receiver\'s ') {{ __($sendMoney->service->name) }} @lang(' Information')
                                </span>
                            </li>

                            @foreach ($sendMoney->service_form_data as $val)
                                @continue(!$val->value)
                                <li class="list-group-item d-flex justify-content-between align-items-center service-data">
                                    {{ __($val->name) }}
                                    <span>
                                        @if ($val->type == 'checkbox')
                                            {{ implode(',', $val->value) }}
                                        @elseif($val->type == 'file')
                                            @if ($val->value)
                                                <a class="me-3" href="{{ route('admin.download.attachment', encrypt(getFilePath('verify') . '/' . $val->value)) }}"><i class="fa fa-file"></i> @lang('Attachment') </a>
                                            @else
                                                @lang('No File')
                                            @endif
                                        @else
                                            <p>{{ __($val->value) }}</p>
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
                    <div class="buttons d-flex flex-wrap gap-1 mt-2">
                        @if ($sendMoney->status == Status::SEND_MONEY_PENDING)
                            <button class="btn btn--base btn-outline--danger refundButton h-45 @if ($sendMoney->country_delivery_method_id) flex-50 @else  w-100 flex-100 @endif" data-action="{{ route('admin.send.money.refund.now', $sendMoney->id) }}"><i class="fas fa-times"></i>
                                @lang('Reject Send Money')
                            </button>
                        @endif
                        @if ($sendMoney->status == Status::SEND_MONEY_PENDING && $sendMoney->country_delivery_method_id)
                            <button class="btn btn-outline--success confirmationBtn h-45 flex-50" data-action="{{ route('admin.send.money.pay.receiver', $sendMoney->id) }}" data-question="@lang('Have you sent money to this receipient\'s details?')"><i class="las la-shipping-fast"></i> @lang('Complete Send Money')</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="card-title border-bottom pb-2">@lang('Sender Information')</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Sender')
                            <span class="fw-bold">{{ @$sendMoney->senderInfo->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Sender Mobile')
                            <span class="fw-bold">+{{ @$sendMoney->senderInfo->mobile }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Sender Address')
                            <span class="fw-bold">{{ ltrim(@$sendMoney->senderInfo->address, ', ') }}</span>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="card-title border-bottom pb-2">@lang('Recipient Information')</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Recipient')
                            <span class="fw-bold">{{ $sendMoney->recipient->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Recipient Mobile')
                            <span class="fw-bold">+{{ @$sendMoney->recipient->dial_code . $sendMoney->recipient->mobile }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Recipient Address')
                            <span class="fw-bold">{{ $sendMoney->recipient->address }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

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
                <form action="{{ route('admin.send.money.pay.receiver', $sendMoney->id) }}" method="POST"  enctype="multipart/form-data">
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
                            <select id="banco" name="banco" required>
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
                            <div id="imageContainer"></div>
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

            $("#banco").select2({
                dropdownParent: $("#confirmationModalPay")
            });

            
            $('.refundButton').on('click', function() {
                var modal = $('#rejectModal');
                modal.find('form').attr('action', $(this).data('action'));
                modal.modal('show');
            });
        })(jQuery);
    </script>

    <script>
        (function ($) {
            "use strict";
            $(document).on('click','.confirmationBtn', function () {
                var modal   = $('#confirmationModalPay');
                // let data    = $(this).data();
                // modal.find('.question').text(`${data.question}`);
                // modal.find('form').attr('action', `${data.action}`);
                modal.modal('show');
            });


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
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .flex-50 {
            flex-basis: 48%;
        }

        .flex-100 {
            flex-basis: 100%;
        }

        .service-data {
            background-color: #fbfbfb;
        }
    </style>
@endpush
