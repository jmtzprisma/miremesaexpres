<div class="card custom--card" id="divDeleteBenef_{{$ind_key}}">
    <div class="card-header text-end">
        <button aria-label="Close" class="close" type="button" onClick="deleteBenef({{$ind_key}})">
            <i class="las la-times"></i>
        </button>
    </div>
    <div class="card-body">
        <div class="mb-3">                           
            <label class="text--accent sm-text d-block fw-md mb-2">Cuenta</label>
            <div class="form--select-light">
                <select class="form-select form--select selectRecipients" id="recipient_account_{{$ind_key}}" name="recipient[{{$ind_key}}][id]" required="" data-indx="{{$ind_key}}">
                    <option value="" selected="" disabled="">Seleccionar Uno</option>
                    @foreach(\App\Models\Recipient::where('user_id', $user_id)->get() as $key => $recipient)
                    <option value="{{ $recipient->id }}" data-country_delivery_method_id="{{$recipient->country_delivery_method_id}}" data-indxkey="{{ $key }}">
                        @php
                            $num_cuenta = '';
                            $email = $recipient->email;
                            foreach (json_decode($recipient->form_data) as $val)
                            {
                                if($val->name == 'Numero de cuenta' || $val->name == 'Codigo del banco') $num_cuenta = $val->value;
                                if($val->name == 'CORREO ELECTRONICO') $email = $val->value;
                            }
                        @endphp
                        {{ $recipient->name }} - {{ $email }} @if(!empty($recipient->mobile)) ({{ $recipient->mobile }}) @endif @if(!empty($num_cuenta)) |{{$num_cuenta}}| @endif </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mb-3">                           
            <label class="text--accent sm-text d-block fw-md mb-2">Monto</label>
            <div class="input-group">
                <input class="form-control form--control" id="amount_{{$ind_key}}" name="recipient[{{$ind_key}}][amount]" required type="number" value="{{ old('amount') ?? null }}">
                <div class="sending-currency ms-1">VEF</div>
            </div>
        </div>
        <small class="text--danger amountSumIncorrectError d-none">@lang('La suma de los montos no es igual al monto enviado')</small>
        <div class="mb-3" id="info_{{$ind_key}}"></div>
    </div>
</div>