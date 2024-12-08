@extends('admin.layouts.app')

@section('panel')
    <form>
        <div class="row mb-3">
            <div class="offset-lg-2 col-lg-3">
                <input type="date" name="start_date" class="form-control" value="{{$from->format('Y-m-d')}}" placeholder="Seleccione fecha fin" required>
            </div>
            <div class="col-lg-3">
                <input type="date" name="end_date" class="form-control" value="{{$to->format('Y-m-d')}}" placeholder="Seleccione fecha fin" required>
            </div>
            <div class="col-lg-4" style="text-align: right;">
                <button type="subtmit" name="button" value="buscar" class="btn btn-info">Buscar</button>
                <button type="subtmit" name="button" value="excel" class="btn btn-info">Descargar Excel</button>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-lg-12">
            <div class="show-filter mb-3 text-end">
                <button type="button" class="btn btn-outline--primary showFilterBtn btn-sm"><i class="las la-filter"></i> @lang('Filter')</button>
            </div>
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table id="tblControl" class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>@lang('Fecha')</th>
                                    <th>@lang('Descripcion del Gasto')</th>
                                    <th>@lang('Tipo de Gasto')</th>
                                    <th>@lang('Solicitante del Gasto')</th>
                                    <th>@lang('Beneficiario')</th>
                                    <th>@lang('Tipo de Operación')</th>
                                    <th>@lang('Tipo de Moneda')</th>
                                    <th>@lang('Monto')</th>
                                    <th>@lang('Banco Emisor')</th>
                                    <th>@lang('Banco Receptor')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $itm)
                                @php
                                    $user = \App\Models\Admin::find($itm->user_id);
                                @endphp
                                    <tr>
                                        <td>{{ $itm->created_at }}</td>
                                        <td><strong>{{ $itm->description }}</strong></td>
                                        <td>Variable</td>
                                        <td>{{ $user->firstname . ' ' . $user->lastname }}</td>
                                        <td>{{ $itm->beneficiario }}</td>
                                        <td>{{ $itm->tipo_operacion }}</td>
                                        <td>{{ \App\Models\Bank::find($itm->bank_id_input)->currency }}</td>
                                        <td>{{ $itm->amount_currency_local }}</td>
                                        <td>{{ \App\Models\Bank::find($itm->bank_id_input)->name }}</td>
                                        <td>{{ is_null($itm->bank_id_ouput) ? '' : \App\Models\Bank::find($itm->bank_id_ouput)->name }}</td>
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
                @if ($items->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($items) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
@endsection

@push('script-lib')
    <script src="{{ asset('assets/global/js/datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/datepicker.en.js') }}"></script>
@endpush
@push('script')
    <script>
        (function($) {
            "use strict";
            if (!$('.datepicker-here').val()) {
                $('.datepicker-here').datepicker();
            }

        })(jQuery)
            
    </script>
@endpush
