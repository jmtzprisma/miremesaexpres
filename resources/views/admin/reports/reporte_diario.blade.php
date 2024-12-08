@extends('admin.layouts.app')

@section('panel')
<form class="form-control" action="{{route('admin.report.datos_bancos_excel')}}">
    <div class="row">
        <div class="col-lg-3">
            <input type="date" name="start_date" class="form-control" placeholder="Seleccione fecha fin" required>
        </div>
        <div class="col-lg-3">
            <input type="date" name="end_date" class="form-control" placeholder="Seleccione fecha fin" required>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-lg-6 pull-right">
            <button class="btn btn-info">Descargar Excel</button>
        </div>
    </div>
</form>
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
