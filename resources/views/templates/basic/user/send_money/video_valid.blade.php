@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="section section--xl">
        <div class="container" style="min-height: 500px;">
            <div class="row">
                <div class="col-md-12">
                    <div id="video" style="top: 160px;"></div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('script')
    <script>
        (function($) {
            "use strict";

            
            if(!{{ is_null(auth()->user()->video_id) ? 'false' : 'true'}})
            {
            var time = null;
            var estatusPago = null;
            
            var videoId = EID.videoId('#video', {
                lang: "es"
            });
            videoId.start({
                authorization: "{{session()->get('token_cryptopocket')}}"
            });
           
            videoId.on("completed",
                function(video) {
                    console.log("Este es tu videoID:" ,{
                        video
                    })
                    @if(session()->has('only_kyc'))
                    var url = "{{route('user.send.money.save_onlykyc')}}";
                    var form = $('<form action="' + url + '" method="post">' +
                        '{{csrf_field()}}' +
                    '<input type="text" name="video_id" value="' + video.id + '" />' +
                    '<input type="text" name="email" value="{{session()->get('email_cryptopocket')}}" />' +
                    '</form>');
                    @else
                    var url = "{{route('user.send.money.procesa_pago', session()->get('combined_id'))}}";
                    var form = $('<form action="' + url + '" method="post">' +
                        '{{csrf_field()}}' +
                    '<input type="text" name="video_id" value="' + video.id + '" />' +
                    '<input type="text" name="payment_type" value="{{session()->get('payment_type_cryptopocket')}}" />' +
                    '<input type="text" name="email" value="{{session()->get('email_cryptopocket')}}" />' +
                    '<input type="text" name="amount" value="{{session()->get('amount')}}" />' +
                    '</form>');
                    @endif
                    $('body').append(form);
                    form.submit();

                });


            videoId.on("failed",
                function(error) {
                    console.log("VideoId Failed")
                });

           
            }

        })(jQuery);
    </script>
@endpush
