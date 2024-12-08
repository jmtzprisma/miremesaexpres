@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="section section--xl">
        <div class="container" style="min-height: 800px;">
            <div class="row">
                <div class="col-md-12">
                    <iframe src="{{ $urlform }}" style="height: 800px; width: 100%;" id="ifr_payment" sandbox="allow-same-origin allow-forms allow-top-navigation allow-scripts allow-popups"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        (function($) {
            "use strict";

            // setInterval(() => {
                
            //     $("#ifr_payment").contents().find("button").css("background-color", "#BADA55" );
            //     var obj_contents = $("#ifr_payment").contents();
            //     var obj_find = $("#ifr_payment").contents().find("#dvRejectedPayRem");

            //     if(obj_find){
                    
            //         $.ajax({
            //             type: "POST",
            //             url: "{{route('user.send.money.consulta_pago', $proccesId)}}",
            //             data: {_token: "{{ csrf_token() }}"},
            //             success: function (response) {
            //                 if(response)
            //                 {
            //                     if(response == 'reload')
            //                     {
            //                         notify('error', 'El pago fue rechazado')
            //                         notify('info', 'Por favor intente nuevamente.')

            //                         window.location.reload();
            //                     }else{
            //                         window.location.href = response;
            //                     }
            //                 }
            //             }
            //         });
            //     }
            // }, 2000);

            setInterval(() => {
            
                $.ajax({
                    type: "POST",
                    url: "{{route('user.send.money.consulta_pago', $proccesId)}}",
                    data: {_token: "{{ csrf_token() }}"},
                    success: function (response) {
                        if(response)
                        {
                            // if(response == 'reload')
                            // {
                            //     notify('error', 'El pago fue rechazado')
                            //     notify('info', 'Por favor intente nuevamente.')

                            //     window.location.reload();
                            // }else{
                                window.location.href = response;
                            // }
                        }
                    }
                });

            }, 2000);
            
        })(jQuery);
    </script>
@endpush
