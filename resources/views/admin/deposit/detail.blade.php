@extends('admin.layouts.app')

@section('panel')

    @php
    $deposit_amount = 0;
    $deposit_charge = 0;
    $deposit_final_amo = 0;
    $sendMoney_sending_amount = 0;
    $sendMoney_recipient_amount = 0;

    foreach(\App\Models\Deposit::where('combined_id', $deposit->combined_id)->with(['user', 'agent', 'gateway', 'sendMoney'])->get() as $itm)
    {
        $deposit_amount += $itm->amount;
        $deposit_charge += $itm->charge;
        $deposit_final_amo += $itm->final_amo;

        $sendMoney_sending_amount += $itm->sendMoney->sending_amount;
        $sendMoney_recipient_amount += $itm->sendMoney->recipient_amount;
    }
    @endphp
    <div class="row gy-3 justify-content-center">
        @if ($deposit->sendMoney && $deposit->status != Status::PAYMENT_INITIATE)
            <div class="col-12">
                <div class="alert alert-info p-3">
                    <p class="f-size--24">
                        <a href="{{ route('admin.users.detail', $deposit->user_id) }}" class="f-size--24">{{ @$deposit->user->username }}</a> 
                        @lang('have paid')
                        <span class="fw-bold">{{ showAmount($sendMoney_sending_amount) }} {{ $deposit->sendMoney->sending_currency }} </span>
                        @lang('to') @lang(@$deposit->sendMoney->recipientCountry->name) @lang('from') @lang(@$deposit->sendMoney->sendingCountry->name). @lang('The recipient will receive')
                        <span class="fw-bold">{{ showAmount($sendMoney_recipient_amount) }} {{ $deposit->sendMoney->recipient_currency }}</span>
                    </p>
                </div>
            </div>
        @endif
        <div class="col-xl-4 col-md-6">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="mb-20 text-muted">{{ __(ucfirst($type)) }} @lang('Via') {{ __(@$deposit->gateway->name) }}</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Date')
                            <span class="fw-bold">{{ showDateTime($deposit->created_at) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Transaction Number')
                            <span class="fw-bold">{{ $deposit->trx }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Username')
                            @if ($deposit->user_id)
                                <span class="fw-bold">
                                    <a href="{{ route('admin.users.detail', $deposit->user_id) }}">{{ @$deposit->user->username }}</a>
                                </span>
                            @else
                                <span class="fw-bold">
                                    <a href="{{ route('admin.agents.detail', $deposit->agent_id) }}">{{ @$deposit->agent->username }}</a>
                                </span>
                            @endif
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Method')
                            <span class="fw-bold">{{ __(@$deposit->gateway->name) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Amount')
                            <span class="fw-bold">{{ showAmount($deposit_amount) }} {{ __($general->cur_text) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Recibe')
                            <span class="fw-bold">{{ showAmount($sendMoney_recipient_amount) }} {{ $deposit->sendMoney->recipient_currency }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Status')
                            @php echo $deposit->statusBadge @endphp
                        </li>

                        @if ($deposit->admin_feedback)
                            <li class="list-group-item">
                                <strong>@lang('Admin Response')</strong>
                                <br>
                                <p>{{ __($deposit->admin_feedback) }}</p>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        @if ($details || $deposit->status == Status::PAYMENT_PENDING)
            <div class="col-xl-8 col-md-6">
                <div class="card b-radius--10 overflow-hidden box--shadow1">
                    <div class="card-header">
                        <h5 class="card-title">
                            @if ($type == 'payment')
                                @lang('User Payment Information')
                            @else
                                @lang('Agent Deposit Information')
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($details != null)
                            @foreach (json_decode($details) as $val)
                                @if ($deposit->method_code >= 1000)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>{{ __($val->name) }}</h6>
                                            @if ($val->type == 'checkbox')
                                                {{ implode(',', $val->value) }}
                                            @elseif($val->type == 'file')
                                                @if ($val->value)
                                                    <img src="{{ route('admin.download.attachment', encrypt(getFilePath('verify') . '/' . $val->value)) }}" style="max-height: 200px;">
                                                @else
                                                    @lang('No File')
                                                @endif
                                            @else
                                                <p>{{ __($val->value) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            @if ($deposit->method_code < 1000)
                                @include('admin.deposit.gateway_data', ['details' => json_decode($details)])
                            @endif
                        @endif
                        @if ($deposit->status == Status::PAYMENT_PENDING)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <button class="btn btn-outline--success btn-sm ms-1 confirmationBtn" data-action="{{ route('admin.deposit.approve', $deposit->id) }}" data-question="@lang('Are you sure to approve this transaction?')"><i class="las la-check-double"></i>
                                        @lang('Approve')
                                    </button>

                                    <button class="btn btn-outline--danger btn-sm ms-1 rejectBtn" data-id="{{ $deposit->id }}"><i class="las la-ban"></i> @lang('Reject')
                                    </button>
                                </div>
                            </div>
                        @endif
                        <div class="row mt-4">
                            <div class="col-md-12">
                                @lang('Cuenta Receptora')
                                <br>
                                {{ $deposit->sendMoney->recipient->name }}<br>
                                @foreach ($deposit->sendMoney->service_form_data as $val)
                                @if($val->name == 'Número de Cedula' || $val->name == 'Numero de cuenta')
                                    {{ $val->value }}
                                    <br>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
                        
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Seleccione un banco')</label>
                            <select id="banco" name="banco" required>
                                <option value="">Seleccione un banco</option>
                                @foreach (\App\Models\Bank::where('active', true)->where('recibe', true)->where('currency',  $deposit->sendMoney->sending_currency)->get() as $itm)
                                <option value="{{ $itm->name }}" >{{ $itm->name . ' (' . $itm->currency . ')' }}</option>
                                @endforeach
                            </select>
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
                dropdownParent: $("#confirmationModal")
            });

            $('.rejectBtn').on('click', function() {
                var modal = $('#rejectModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.modal('show');
            });
        })(jQuery);
    </script>

    <script>
        (function ($) {
            "use strict";
            $(document).on('click','.confirmationBtn', function () {
                var modal   = $('#confirmationModal');
                let data    = $(this).data();
                modal.find('.question').text(`${data.question}`);
                modal.find('form').attr('action', `${data.action}`);
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush


@push('breadcrumb-plugins')
    <a href="{{ route('admin.deposit.pending') }}" class="btn btn-sm btn-outline--primary">@lang('Volver')</a>
@endpush