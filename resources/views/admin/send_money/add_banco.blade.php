@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card b-radius--5 overflow-hidden">
                <div class="card-body">
                    <form action="{{ route('admin.send.money.store_banco') }}" method="POST" class="verify-gcaptcha row">
                        @csrf
                        <div class="form-group col-md-6">
                            <label for="name">@lang('Name')</label>
                            <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="cuenta">@lang('Cuenta')</label>
                            <input id="cuenta" type="text" class="form-control" name="cuenta" value="{{ old('cuenta') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="instrucciones">@lang('Instrucciones')</label>
                            <input id="instrucciones" type="text" class="form-control" name="instrucciones" value="{{ old('instrucciones') }}" required>
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
    <x-back route="{{ route('admin.send.money.list_bancos') }}" />
@endpush

@push('style')
    <style>
        .btn-sm {
            line-height: 5px;
        }
    </style>
@endpush
