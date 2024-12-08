@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="section section--xl">
        <div class="container" style="min-height: 500px;">
            <div class="row">
                <div class="col-md-12">
                    <h3>La validación aun no se completa, por favor espere unos minutos. (la validación puede demorar aproximadamente 10 minutos)</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <form action="{{ route('user.send.money.now') }}" class="w-100" method="GET">
                        @csrf                     
                        <button class="btn btn--base w-100 btn--xl">
                            @lang('Volver a consultar')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('script')
    <script>
        (function($) {
            "use strict";

        })(jQuery);
    </script>
@endpush
