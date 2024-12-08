@foreach ($formData as $data)
    <div class="form-group">
        <label class="d-block sm-text mb-2 @if($data->label == 'numero_de_cuenta' || $data->label == 'iban') justify-content-between @endif">
            {{ __($data->name) }}
            @if($data->label == 'numero_de_cuenta' || $data->label == 'iban')
            <span class="rule_iban_length"></span>
            @endif
        </label>
        @if ($data->type == 'text')
            <input type="text" class="form-control form--control @if($data->label == 'numero_de_cuenta' || $data->label == 'iban') rule_iban @endif" name="{{ $data->label }}" value="{{ old($data->label) }}" @if ($data->is_required == 'required') required @endif
            @if($data->label == 'numero_de_cuenta' || $data->label == 'iban') maxlength="20" @endif>
        @elseif($data->type == 'textarea')
            <textarea class="form-control form--control" name="{{ $data->label }}" @if ($data->is_required == 'required') required @endif>{{ old($data->label) }}</textarea>
        @elseif($data->type == 'select')
            <select class="form-control form--control select_accounts" name="{{ $data->label }}" @if ($data->is_required == 'required') required @endif @if($data->options[0] == 'model_database') onchange="selectAccounts()" @endif>
                <option value="">@lang('Select One')</option>

                @php
                $banks = \App\Models\Bank::where('active', true)->where('recibe', true)->where('only_criptopocket', false);
                if(auth()->check() && auth()->user()->kv == 1 && !is_null(auth()->user()->video_id))
                    $banks = \App\Models\Bank::where('active', true)->where('recibe', true);//->where('only_criptopocket', true);
                
                if(isset($currency) && !empty($currency))
                    $banks = $banks->where('currency', $currency)
                @endphp

                @if($data->options[0] == 'model_database')
                @foreach ($banks->get() as $item)
                    <option value="{{ $item->name }}" @selected($item->name == old($data->label)) data-account="{{ $item->account }}" data-name_account="{{ $item->name_account }}">{{ __($item->name) }}</option>
                @endforeach
                @else
                @foreach ($data->options as $item)
                    <option value="{{ $item }}" @selected($item == old($data->label))>{{ __($item) }}</option>
                @endforeach
                @endif
            </select>
        @elseif($data->type == 'checkbox')
            @foreach ($data->options as $option)
                <div class="form-check">
                    <input class="form-check-input" name="{{ $data->label }}[]" type="checkbox" value="{{ $option }}" id="{{ $data->label }}_{{ titleToKey($option) }}">
                    <label class="form-check-label" for="{{ $data->label }}_{{ titleToKey($option) }}">{{ $option }}</label>
                </div>
            @endforeach
        @elseif($data->type == 'radio')
            @foreach ($data->options as $option)
                <div class="form-check">
                    <input class="form-check-input" name="{{ $data->label }}" type="radio" value="{{ $option }}" id="{{ $data->label }}_{{ titleToKey($option) }}" @checked($option == old($data->label))>
                    <label class="form-check-label" for="{{ $data->label }}_{{ titleToKey($option) }}">{{ $option }}</label>
                </div>
            @endforeach
        @elseif($data->type == 'file')
            <input type="file" class="form-control form--control" name="{{ $data->label }}" @if ($data->is_required == 'required') required @endif accept="@foreach (explode(',', $data->extensions) as $ext) .{{ $ext }}, @endforeach">
            <pre class="text--base mt-1">@lang('Supported mimes'): {{ $data->extensions }}</pre>
        @endif
    </div>
@endforeach
