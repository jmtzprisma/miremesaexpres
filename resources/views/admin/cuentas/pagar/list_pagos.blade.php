<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--sm table-responsive">
                    <table class="table table-responsive-md">
                        <thead>
                            <tr>
                                <th>@lang('Fecha')</th>
                                <th>@lang('Abono')</th>
                                <th class="w-85"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pagos as $pago)
                                <tr>
                                    <td><span class="small">{{ showDateTime($pago->created_at) }}</span></td>
                                    <td><span class="small">{{ showAmount($pago->amount) }}</span></td>
                                    <td>
                                        @if(!$pago->cancelado)
                                        <button class="btn btn-sm btn-outline--primary btn-cancelar-cxppago" data-id="{{ $pago->id }}">
                                            <i class="las la-trash"></i>
                                        </button>
                                        @else
                                        Cancelado
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
        </div>
    </div>
</div>

<script>
    
    $(".btn-cancelar-cxppago").on("click", function(){
        cuentaId = $(this).data('id');

        $("#cancelarPagoModal").modal("hide");
        
        $("#cxpPagoIdCancelar").val(cuentaId);
        $("#cancelarcxpPagoModal").modal("show");
    });

</script>