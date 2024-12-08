@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>@lang('Fecha')</th>
                                    <th>@lang('ID Cliente')</th>
                                    <th>@lang('Nombre')</th>
                                    <th>@lang('Banco')</th>
                                    <th>@lang('Concepto')</th>
                                    <th>@lang('N° de Crédito')</th>
                                    <th>@lang('Cargo')</th>
                                    <th>@lang('Abono')</th>
                                    <th>@lang('Días Crédito')</th>
                                    <th>@lang('Días Vencido')</th>
                                    <th>@lang('Saldo')</th>
                                    <th>@lang('Vencimiento')</th>
                                    <th class="w-85"></th>
                                    <th class="w-85"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cuentas as $cuenta)
                                    @php
                                        $sendMoney = $cuenta->sendMoney;
                                    @endphp
                                    <tr>
                                        <td><span class="small">{{ ($sendMoney) ?  showDateTime($sendMoney->created_at) : $cuenta->created_at }}</span></td>
                                        <td>
                                            @if($sendMoney)
                                            <span class="text--muted fw-bold">{{ $sendMoney->user_id }}</span>
                                            @else
                                            <span class="text--muted fw-bold">CXC</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sendMoney)
                                            <span class="small">{{ $cuenta->user->fullname }}</span>
                                            @else
                                            <span class="small">{{ $cuenta->nombre_proveedor }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$sendMoney)
                                            <span class="small">{{ $cuenta->bank->name }}</span>
                                            @endif
                                        </td>
                                        <td><span class="small">{{ $cuenta->concepto }}</span></td>
                                        <td>
                                            @if($sendMoney)
                                                <span class="small">{{ $sendMoney->mtcn_number }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sendMoney)
                                            <span class="small">{{ showAmount($sendMoney->sending_amount) }} {{ $sendMoney->sending_currency }}</span>
                                            @else
                                            <span class="small">{{ showAmount($cuenta->amount_currency_local) }} {{$cuenta->bank->currency}}</span>
                                            @endif
                                        </td>
                                        <td><span class="small">{{ showAmount($cuenta->sumPagos()) }}</span></td>
                                        <td><span class="small">{{ $cuenta->daysDiff() }}</span></td>
                                        <td><span class="small">{{ $cuenta->daysDiffVencido() }}</span></td>
                                        <td>
                                            @if($sendMoney)
                                            <span class="small">{{ showAmount($sendMoney->sending_amount - $cuenta->sumPagos()) }} {{ $sendMoney->sending_currency }}</span>
                                            @else
                                            <span class="small">{{ showAmount($cuenta->amount_currency_local - $cuenta->sumPagos()) }} {{$cuenta->bank->currency}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small">
                                                @if($cuenta->status != 'finished')
                                                    @if($cuenta->daysDiffVencido() == 0)
                                                        No vencido
                                                    @elseif($cuenta->daysDiffVencido() <= 15)
                                                        De 1 a 15 días
                                                    @elseif($cuenta->daysDiffVencido() <= 30)
                                                        De 16 a 30 días
                                                    @else
                                                        Más de 30 días                                                        
                                                    @endif
                                                @else
                                                    Pagado
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline--primary btn-lista-pagos" data-id="{{ $cuenta->id }}">
                                                <i class="las la-file-invoice-dollar"></i>
                                            </button>
                                        </td>
                                        <td>
                                            @if($cuenta->status != 'finished' && !$cuenta->cancelado)
                                            <button class="btn btn-sm btn-outline--primary btn-agregar-pago" data-id="{{ $cuenta->id }}">
                                                <i class="las la-euro-sign"></i>
                                            </button>
                                            @endif
                                        </td>
                                        <td>
                                            @if($cuenta->status != 'finished' && !$cuenta->cancelado && is_null($cuenta->send_money_id))
                                            <button class="btn btn-sm btn-outline--primary btn-cancelar-pago" data-id="{{ $cuenta->id }}">
                                                <i class="las la-trash"></i>
                                            </button>
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
                @if ($cuentas->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($cuentas) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="nuevaCuentaPagar" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Convert Currency')</h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.cuentas.cobrar.store_manual') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Fecha de vencimiento')</label>
                            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control" value="<?= \Carbon\Carbon::now()->format('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Proveedor')</label>
                            <input type="text" name="nombre_proveedor" id="nombre_proveedor" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Concepto')</label>
                            <textarea name="description_cp" id="description_cp" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Banco')</label>
                            <select name="bankId" class="select2-basic" required>
                                <option value="">Seleccione un banco</option>
                                @foreach (\App\Models\Bank::where('recibe', true)->where('active', 1)->get() as $itm)
                                <option value="{{ $itm->id }}">{{ $itm->name . ' (' . $itm->currency . ')' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Monto a convertir')</label>
                            <input class="form-control form--control text--right convert-amount" min="0" name="amount_currency_local" id="amount" placeholder="0.00" required step="any" type="number">
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Banco')</label>
                            <select name="bancoIdOutput" class="select2-basic" required>
                                <option value="">Seleccione un banco</option>
                                @foreach (\App\Models\Bank::where('envia', true)->where('active', 1)->get() as $itm)
                                <option value="{{ $itm->id }}">{{ $itm->name . ' (' . $itm->currency . ')' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Monto convertido')</label>
                            <input class="form-control form--control text--right convert-amount" min="0" name="amount_currency_convert" id="amount_convert" placeholder="0.00" required step="any" type="number">
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2" id="rate">@lang('Tasa')</label>
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
    <div class="modal fade" id="listaPagosModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Deposit')</h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body" id="dvPagos"></div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="agregarPagoModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Agregar pago')</h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.cuentas.save_payment') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cuentaId" id="cuentaIdPago">
                    
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Amount')</label>
                            <input class="form-control form--control text--right" min="0" name="amount" placeholder="0.00" required step="any" type="number">
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

    <div class="modal fade" id="cancelarPagoModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Cancelar cuenta por cobrar')</h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.cuentas.cancel_cuenta_cobrar') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cuentaId" id="cuentaIdPagoCancelar">
                    
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('¿Desea cancelar esta operación? El proceso revertira la operación realizada, y revertirá los pagos agregados si es que hubiera.')</label>
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

    <div class="modal fade" id="cancelarcxcPagoModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Cancelar pago de cuenta por cobrar')</h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.cuentas.cancel_cxc_pago') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cxcPagoId" id="cxcPagoIdCancelar">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('¿Desea cancelar el pago?')</label>
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
@endsection

@push('breadcrumb-plugins')
    <a class="btn btn-outline--primary h-45" href="#" id="btnNew"><i class="las la-plus"></i>@lang('Add New')</a>
    <form>
        <div class="row">
            <div class="col">
                <select id="status" name="status" class="form-control" style="width: 150px !important;" required>
                    <option value="pending" @if($status == 'pending') selected @endif>Pendiente</option>
                    <option value="finished" @if($status == 'finished') selected @endif>Finalizada</option>
                </select>
            </div>
            <div class="col">
                <input type="date" name="start_date" class="form-control" value="{{$from->format('Y-m-d')}}" placeholder="Seleccione fecha fin" required>
            </div>
            <div class="col">
                <input type="date" name="end_date" class="form-control" value="{{$to->format('Y-m-d')}}" placeholder="Seleccione fecha fin" required>
            </div>
            <div class="col">
                <button type="subtmit" name="button" value="search" class="btn btn-sm btn-info">
                    <i class="las la-search"></i>
                </button>
                <button type="subtmit" name="button" value="excel" class="btn btn-sm btn-success">
                    <i class="las la-file"></i>
                </button>
            </div>
        </div>
    </form>
@endpush

@push('script')
<script>
    $(document).ready(function() {
        $(".btn-lista-pagos").on("click", function(){
            cuentaId = $(this).data('id');

            $.ajax({
                headers : {
                    'X-CSRF-Token' : '{{csrf_token()}}',
                },
                type: "POST",
                url: "{{ route('admin.cuentas.cc.pagos') }}",
                data: {
                    'cuenta_id': cuentaId
                },
                success: function (response) {
                    $("#dvPagos").html(response)
                    $("#listaPagosModal").modal("show");
                }
            });
        });

        $(".btn-agregar-pago").on("click", function(){
            cuentaId = $(this).data('id');
            $("#cuentaIdPago").val(cuentaId);
            $("#agregarPagoModal").modal("show");
        });

        $(".btn-cancelar-pago").on("click", function(){
            cuentaId = $(this).data('id');
            $("#cuentaIdPagoCancelar").val(cuentaId);
            $("#cancelarPagoModal").modal("show");
        });

        $("#status").on("change", function(){
            window.location.href = "{{route('admin.cuentas.index.cobrar')}}/"+$("#status").val()
        });

        $("#btnNew").on("click", function(){
            $("#nuevaCuentaPagar").modal("show");
        });

        $(".convert-amount").on("blur", function(){
            $("#rate").html('@lang('Tasa')');
            
            if($("#amount").val() > 0 && $("#amount_convert").val() > 0 )
            {
                $("#rate").html('@lang('Tasa'): ' + parseFloat($("#amount_convert").val()) / parseFloat($("#amount").val()));
            }
        })
    });
</script>
@endpush
