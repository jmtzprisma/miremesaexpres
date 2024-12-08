@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card b-radius--5 overflow-hidden">
                <div class="card-body">
                    <form action="{{ route('admin.roles.update', $rol->id) }}" method="POST" class="verify-gcaptcha row">
                        @csrf
                        <div class="form-group col-md-7">
                            <label for="name">@lang('Name')</label>
                            <input id="name" type="text" class="form-control" name="name" value="{{ $rol->name }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            @php if(!is_null($rol->permissions)) $_permisos = explode(",", $rol->permissions) @endphp
                            <label for="permisos">@lang('Permisos')</label>
                            <select name="permisos[]" class="form-control" multiple style="height: auto;" required>
                                <option value="1" @if(in_array(1, $_permisos)) selected @endif>SuperAdmin</option>
                                <option value="2" @if(in_array(2, $_permisos)) selected @endif>Bancos Envía</option>
                                <option value="3" @if(in_array(3, $_permisos)) selected @endif>Bancos Recibe</option>
                                <option value="4" @if(in_array(4, $_permisos)) selected @endif>@lang('Send Money')</option>
                                <option value="5" @if(in_array(5, $_permisos)) selected @endif>@lang('Manage Users')</option>
                                <option value="6" @if(in_array(6, $_permisos)) selected @endif>@lang('Manage Agents')</option>
                                <option value="7" @if(in_array(7, $_permisos)) selected @endif>@lang('Report')</option>
                                <option value="8" @if(in_array(8, $_permisos)) selected @endif>@lang('Payments')</option>
                                <option value="9" @if(in_array(9, $_permisos)) selected @endif>Envío manual</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            @php $_bancos = explode(",", $rol->bancos) @endphp
                            <label for="bancos">@lang('Bancos')</label>
                            <select name="bancos[]" class="form-control" multiple style="height: auto;" required>
                                @foreach (\App\Models\Bank::get() as $itm)
                                    <option value="{{ $itm->id }}" @if(in_array($itm->id, $_bancos)) selected @endif>{{ __($itm->name) }} ({{ __($itm->account) }})</option>
                                @endforeach
                            </select>
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

@push('script')
    <script>
        (function($) {
            "use strict";
            

        })(jQuery);
    </script>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.roles') }}" />
@endpush

@push('style')
    <style>
        .btn-sm {
            line-height: 5px;
        }
    </style>
@endpush
