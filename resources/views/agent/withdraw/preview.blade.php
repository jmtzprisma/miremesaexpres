@extends('agent.layouts.app')
@section('panel')
    <div class="row justify-content-center mt-5">
        <div class="col-xxl-5 col-xl-7 col-md-8 col-sm-10">
            <div class="border--card">
                <h4 class="title"><i class="las la-hand-holding-usd"></i> {{ __($pageTitle) }}</h4>
                <div class="card-body p-0">
                    <form action="{{ route('agent.withdraw.submit') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            @php
                                echo $withdraw->method->description;
                            @endphp
                        </div>
                        <x-viser-form identifier="id" identifierValue="{{ $withdraw->method->form_id }}" />
                        @if (authAgent()->ts)
                            <div class="form-group">
                                <label>@lang('Google Authenticator Code')</label>
                                <input type="text" name="authenticator_code" class="form--control" required>
                            </div>
                        @endif
                        <div class="form-group">
                            <button type="submit" class="btn btn--base btn-md w-100">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
