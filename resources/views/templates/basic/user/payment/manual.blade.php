@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="section section--xl">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card custom--card">
                        <div class="card-header">
                            <h5 class="card-title text-center">{{ __($pageTitle) }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('user.deposit.manual.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <h4 class="text-center mt-2">@lang('You have requested to pay') <b class="text--success">{{ showAmount($data['amount']) }} {{ __($general->cur_text) }}</b></h4>
                                        <h5>
                                            @lang('Please pay') <b class="text--success">{{ showAmount($data['final_amo']) . ' ' . $data['method_currency'] }} </b> @lang('siguiendo la información a continuación')
                                        </h5>
                                        <p class="my-4 text-center">@php echo  $data->gateway->description @endphp</p>
                                        <p class="my-4 text-center" id="info_section_bank"></p>
                                    </div>

                                    <x-viser-form identifier="id" identifierValue="{{ $gateway->form_id }}" currency="{{$data['method_currency']}}"/>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn--base w-100  btn--xl">@lang('Pay Now')</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    // $(function(){
    //     $(".select_accounts").on("change", function(){
    //         console.log("entra");
    //         account = $(this).find(':selected').data('account');
    //         name_account = $(this).find(':selected').data('name_account');

    //         console.log("account");
    //         console.log(account);
    //         console.log("name_account");
    //         console.log(name_account);
    //         $("#info_section_bank").html()
    //     });
    // });

    function selectAccounts()
    {
        console.log("entra");
        account = $(".select_accounts").find(':selected').data('account');
        name_account = $(".select_accounts").find(':selected').data('name_account');

        console.log("account");
        console.log(account);
        console.log("name_account");
        console.log(name_account);
        html = "<p><strong>Titular de la cuenta</strong> <br>" + name_account + 
                "<button type='button' class=\"btn btn-info btn-sm copy\" onclick=\"copyInfo('" + name_account + "')\"><span class=\"fa fa-copy\"></span></button>" +
                "<br><br><strong>Número de la cuenta</strong> <br>" + account +
                "<button type='button' class=\"btn btn-info btn-sm copy\" onclick=\"copyInfo('" + account + "')\"><span class=\"fa fa-copy\"></span></button>" +
                "</p>";
        $("#info_section_bank").html(html)
    }

    
    function copyInfo(info){
        navigator.clipboard.writeText(info)
        .then(() => {
            console.log('Texto copiado al portapapeles')
            notify('success', 'Información copiada al portapapeles')
        })
    }
</script>
@endpush
