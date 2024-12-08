@extends('agent.layouts.app')

@section('panel')
    <div class="row justify-content-center mt-5">
        <div class="col-xxl-5 col-xl-7 col-md-8 col-sm-10">
            <div class="border--card h-auto">
                <h4 class="title"> <i class="las la-money-check-alt"></i> {{ __($pageTitle) }}</h4>
                <div class="card-body p-0">
                    <form action="{{ route('agent.payout.info') }}" class="row" method="get">
                        <div class="form-group">
                            <label for="trx">@lang('MTCN Number')</label>
                            <input class="form--control" id="trx" name="mtcn_number" placeholder="@lang('MTCN Number')" type="text" value="{{ old('mtcn_number') }}">
                        </div>
                        <div class="form-group">
                            <button class="btn btn--base btn-md w-100" type="submit">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
