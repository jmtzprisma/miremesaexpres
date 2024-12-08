@extends('agent.layouts.app')
@section('panel')
    <form action="" method="POST" id="form">
        @csrf
        <div class="row justify-content-center mt-5">
            <div class="col-lg-8  col-md-10">
                <div class="border--card h-auto">
                    <h4 class="title"><i class="las la-user"></i> {{ __($pageTitle) }}</h4>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="InputFirstname" class="col-form-label">@lang('First Name'):</label>
                            <input type="text" class="form--control" id="InputFirstname" name="firstname" placeholder="@lang('First Name')" value="{{ $agent->firstname }}" minlength="3">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="lastname" class="col-form-label">@lang('Last Name'):</label>
                            <input type="text" class="form--control" id="lastname" name="lastname" placeholder="@lang('Last Name')" value="{{ $agent->lastname }}" required>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="email" class="col-form-label">@lang('E-mail Address'):</label>
                            <input class="form--control" id="email" placeholder="@lang('E-mail Address')" value="{{ $agent->email }}" readonly>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="phone" class="col-form-label">@lang('Mobile Number')</label>
                            <input class="form--control" id="phone" value="{{ $agent->mobile }}" readonly>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-form-label">@lang('Country'):</label>
                            <input class="form--control" value="{{ @$agent->address->country }}" readonly>
                        </div>

                        <div class="form-group col-sm-6">
                            <label for="city" class="col-form-label">@lang('City'):</label>
                            <input type="text" class="form--control" id="city" name="city" placeholder="@lang('City')" value="{{ @$agent->address->city }}" required="">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="state" class="col-form-label">@lang('State'):</label>
                            <input type="text" class="form--control" id="state" name="state" placeholder="@lang('state')" value="{{ @$agent->address->state }}" required="">
                        </div>

                        <div class="form-group col-sm-6">
                            <label for="zip" class="col-form-label">@lang('Zip Code'):</label>
                            <input type="text" class="form--control" id="zip" name="zip" placeholder="@lang('Zip Code')" value="{{ @$agent->address->zip }}" required>
                        </div>

                        <div class="form-group col-sm-12">
                            <label for="address" class="col-form-label">@lang('Address'):</label>
                            <input type="text" class="form--control" id="address" name="address" placeholder="@lang('Address')" value="{{ @$agent->address->address }}" required="">
                        </div>

                        <div class="form-group col-sm-12">
                            <button type="submit" class="btn btn--base w-100">@lang('Update Profile')</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
@endsection
