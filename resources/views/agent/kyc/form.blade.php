@extends('agent.layouts.app')

@section('panel')
    <div class="row justify-content-center mt-5">
        <div class="col-lg-8">
            <div class="border--card h-auto">
                <h4 class="title"> <i class="las la-user-check"></i>{{ __($pageTitle) }}</h4>
                <div class="card-body">
                    <form action="{{ route('agent.kyc.submit') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <x-viser-form identifier="act" identifierValue="agent.kyc" />

                        <div class="form-group">
                            <button type="submit" class="btn btn--base btn-md w-100">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
