@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card b-radius--5 overflow-hidden">
                <div class="card-body">
                    <form action="{{ route('admin.roles.store') }}" method="POST" class="verify-gcaptcha row">
                        @csrf
                        <div class="form-group col-md-6">
                            <label for="name">@lang('Name')</label>
                            <input id="name" type="text" class="form-control" name="name" value="{{ old('firstname') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="permisos">@lang('Permisos')</label>
                            <select name="permisos[]" class="form-control" required>
                                <option value="1">SuperAdmin</option>
                                <option value="2">Bancos Envía</option>
                                <option value="3">Bancos Recibe</option>
                                <option value="4">@lang('Send Money')</option>
                                <option value="5">@lang('Manage Users')</option>
                                <option value="6">@lang('Manage Agents')</option>
                                <option value="7">@lang('Report')</option>
                                <option value="8">@lang('Payments')</option>
                                <option value="9">Envío manual</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="bancos">@lang('Bancos')</label>
                            <select name="bancos[]" class="form-control" multiple style="height: auto;" required>
                                @foreach (\App\Models\Bank::get() as $itm)
                                    <option value="{{ $itm->id }}" >{{ __($itm->name) }} ({{ __($itm->account) }})</option>
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
