@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card b-radius--5 overflow-hidden">
                <div class="card-body">
                    <form action="{{ route('admin.bank.update', $bank->id) }}" method="POST" class="verify-gcaptcha row">
                        @csrf
                        <div class="form-group col-md-6">
                            <label for="name">@lang('Name')</label>
                            <input id="name" type="text" class="form-control" name="name" value="{{ $bank->name }}" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="name_account">@lang('A nombre de')</label>
                            <input id="name_account" type="text" class="form-control" name="name_account" value="{{ $bank->name_account }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="account">@lang('Account')</label>
                            <input id="account" type="text" class="form-control" name="account" value="{{ $bank->account }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="codigo_banco">@lang('Codigo del banco')</label>
                            <input id="codigo_banco" type="text" class="form-control" name="codigo_banco" value="{{ $bank->codigo_banco }}" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="recibe">@lang('Recibe')</label>
                            <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Si')" data-off="@lang('No')" name="recibe" id="recibe" {{ $bank->recibe ? 'checked' : '' }}>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="envia">@lang('Envia')</label>
                            <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Si')" data-off="@lang('No')" name="envia" id="envia"{{ $bank->envia ? 'checked' : '' }}>
                        </div>
                        <div class="form-group col-md-3" id="divRecibe">
                            <label for="currency">@lang('Currency')</label>
                            <select autocomplete="off" class="form-control form--control country-picker" name="currency">
                                @php $currencies = array(); @endphp 
                                @foreach ($sendingCountries as $sendingCountry)
                                    @php $currencies[] = $sendingCountry->currency; @endphp 
                                    <option value="{{ $sendingCountry->currency }}" @if($bank->currency == $sendingCountry->currency) selected @endif>
                                        {{ $sendingCountry->currency }}
                                    </option>
                                @endforeach
                                @foreach ($receivingCountries as $receivingCountry)
                                    @if(!in_array($receivingCountry->currency, $currencies))
                                    <option value="{{ $receivingCountry->currency }}" @if($bank->currency == $receivingCountry->currency) selected @endif>
                                        {{ $receivingCountry->currency }}
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="balance">@lang('Balance')</label>
                            <input id="balance" type="text" class="form-control" name="balance" value="{{ $bank->balance }}" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="average_rate">@lang('Tasa promedio')</label>
                            <input id="average_rate" type="text" class="form-control" name="average_rate" value="{{ $bank->average_rate }}" required>
                        </div>
                        
                        <div class="form-group col-md-3">
                            <label> @lang('Cuenta para cuentas de criptopocket')</label>
                            <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="@lang('Enable')" data-off="@lang('Disable')" name="only_criptopocket" @if ($bank->only_criptopocket) checked @endif>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" id="recaptcha" class="btn btn--primary h-45 w-100">
                                @lang('Submit')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.bank.list') }}" />
@endpush

@push('style')
    <style>
        .btn-sm {
            line-height: 5px;
        }
    </style>
@endpush
@push('script')
    <script>
        $(document).ready(function() {

            // $('#bank_type').change(function() {
            //     if(this.checked) {
            //         $("#divEnvia").addClass('d-none');
            //         $("#divRecibe").removeClass('d-none');
            //     }else{
            //         $("#divRecibe").addClass('d-none');
            //         $("#divEnvia").removeClass('d-none');
            //     }     
            // });
        });

    </script>
@endpush