@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="section section--xl">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card custom--card">
                        <div class="card-header">
                            <h5 class="card-title">
                                {{ __($pageTitle) }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <form class="register" action="" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('First Name')</label>
                                        <input type="text" class="form-control form--control" name="firstname" value="{{ $user->firstname }}" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('Last Name')</label>
                                        <input type="text" class="form-control form--control" name="lastname" value="{{ $user->lastname }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('E-mail Address')</label>
                                        <input class="form-control form--control" value="{{ $user->email }}" readonly>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('Mobile Number')</label>
                                        <input class="form-control form--control" value="{{ $user->mobile }}" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('Número de identificación')</label>
                                        <input class="form-control form--control" value="{{ $user->identification }}" name="identification" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('Address')</label>
                                        <input type="text" class="form-control form--control" name="address" value="{{ @$user->address->address }}">
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('State')</label>
                                        <input type="text" class="form-control form--control" name="state" value="{{ @$user->address->state }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <label class="d-block sm-text mb-2">@lang('Zip Code')</label>
                                        <input type="text" class="form-control form--control" name="zip" value="{{ @$user->address->zip }}">
                                    </div>

                                    <div class="form-group col-sm-4">
                                        <label class="d-block sm-text mb-2">@lang('City')</label>
                                        <input type="text" class="form-control form--control" name="city" value="{{ @$user->address->city }}">
                                    </div>

                                    <div class="form-group col-sm-4">
                                        <label class="d-block sm-text mb-2">@lang('Country')</label>
                                        <input class="form-control form--control" value="{{ @$user->address->country }}" disabled>
                                    </div>

                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn--base w-100  btn--xl">@lang('Submit')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
