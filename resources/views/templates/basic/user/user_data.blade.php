@extends($activeTemplate . 'layouts.frontend')

@section('content')
    <div class="section section--xl">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-6">
                    <div class="card custom--card">

                        <div class="card-body">
                            <div class="alert alert-warning" role="alert">
                                @lang('Para obtener acceso a su panel, debe completar su perfil enviando el siguiente formulario con la información adecuada.')
                            </div>

                            <form method="POST" action="{{ route('user.data.submit') }}">
                                @csrf
                                <div class="row mb-0">
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('First Name')</label>
                                        <input type="text" class="form-control form--control" name="firstname" value="{{ old('firstname') }}" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('Last Name')</label>
                                        <input type="text" class="form-control form--control" name="lastname" value="{{ old('lastname') }}" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('Número de identificación')</label>
                                        <input type="text" class="form-control form--control" name="identification" value="{{ old('identification') }}" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('Address')</label>
                                        <input type="text" class="form-control form--control" name="address" value="{{ old('address') }}">
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('State')</label>
                                        <input type="text" class="form-control form--control" name="state" value="{{ old('state') }}">
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('Zip Code')</label>
                                        <input type="text" class="form-control form--control" name="zip" value="{{ old('zip') }}">
                                    </div>

                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('City')</label>
                                        <input type="text" class="form-control form--control" name="city" value="{{ old('city') }}">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn--base w-100 btn--xl">
                                            @lang('Submit')
                                        </button>
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
