@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <form class="form-control" action="{{route('admin.report.detalle_movs_banco')}}">
                <center>
                    <h3>Reporte detalle de bancos</h3>
                </center>
                <div class="row">
                    <div class="col-lg-3">
                        <input type="date" name="start_date" class="form-control" placeholder="Seleccione fecha fin" required>
                    </div>
                    <div class="col-lg-3">
                        <input type="date" name="end_date" class="form-control" placeholder="Seleccione fecha fin" required>
                    </div>
                    <div class="col-lg-3">
                        
                        <select id="bank_id" name="bank_id" required>
                            <option value="">Seleccione un banco</option>
                            @foreach (\App\Models\Bank::where('active', true)->get() as $itm)
                            <option value="{{ $itm->id }}">{{ $itm->name . ' (' . $itm->currency . ')' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-lg-6 pull-right d-flex">
                        <input type="submit" class="btn btn-info" name="btnAction" value="Excel">
                        <input type="submit" class="btn btn-info ms-2" name="btnAction" value="CSV">
                    </div>
                </div>
            </form>

            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table id="tblControl" class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>@lang('Moneda')</th>
                                    <th>@lang('Saldo Inicial')</th>
                                    <th>@lang('Ingresos')</th>
                                    <th>@lang('Egresos')</th>
                                    <th>@lang('Saldo Final')</th>
                                    <th>@lang('Tasa Refencial USDT')</th>
                                    <th>@lang('Saldo Final USDT')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $general = gs();
                                    $obj_currencies_usdt = null;
                                    $obj_currencies_usdt = !is_null($general->additional_info) ? json_decode($general->additional_info) : null;
                                    $_tot = 0;
                                @endphp
                                @forelse($items as $itm)
                                    @php
                                        $value = 0;
                                        if(!is_null($obj_currencies_usdt))
                                            foreach($obj_currencies_usdt as $key => $it)
                                            {
                                                if($key == $itm->currency) $value = $it;
                                            }


                                        $saldo_final = $itm->saldo_inicial + (($itm->ingresos ?? 0) - ($itm->egresos ?? 0));
                                        $tot = ($saldo_final > 0 && $value > 0) ? (($itm->currency == 'VEF') ? ($saldo_final * $value) : ($saldo_final / $value)) : 0;
                                        $_tot += $tot;
                                    @endphp
                                    <tr>
                                        <td>{{ $itm->currency }}</td>
                                        <td>{{ showAmount($itm->saldo_inicial) }}</td>
                                        <td>{{ showAmount($itm->ingresos ?? 0) }}</td>
                                        <td>{{ showAmount($itm->egresos ?? 0) }}</td>
                                        <td>{{ showAmount($saldo_final) }}</td>
                                        <td>{{ $value }}</td>
                                        <td>{{ showAmount($tot) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                                
                                @if($_tot > 0)
                                <tr>
                                    <td class="text-muted text-end" colspan="6">Total</td>
                                    <td class="text-muted text-center">{{ showAmount($_tot) }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($items->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($items) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
@endsection
