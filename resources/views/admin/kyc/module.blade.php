@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <form action="" method="POST">
                        @csrf
                        <div class="card mt-3">
                            <h5 class="card-header bg--primary">@lang('Agent Panel') <span class="text--primary" title="@lang('If you enable this, agents need KYC verification to access modules.')"><i class="fas text-white fa-info-circle"></i></span></h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group border--primary col-md-3 col-sm-6">
                                        <label>@lang('Payout') </label>
                                        <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="50" data-on="@lang('Enable')" data-off="@lang('Disabled')" name="modules[agent][payout]" @if (@$modules->agent->payout) checked @endif>
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label>@lang('Send Money') </label>
                                        <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="50" data-on="@lang('Enable')" data-off="@lang('Disabled')" name="modules[agent][send_money]" @if (@$modules->agent->send_money) checked @endif>
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label>@lang('Deposits') </label>
                                        <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="50" data-on="@lang('Enable')" data-off="@lang('Disabled')" name="modules[agent][deposit]" @if (@$modules->agent->deposit) checked @endif>
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label>@lang('Withdrawals') </label>
                                        <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="50" data-on="@lang('Enable')" data-off="@lang('Disabled')" name="modules[agent][withdrawals]" @if (@$modules->agent->withdrawals) checked @endif>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card  mt-3">
                            <h5 class="card-header border--primary bg--primary">@lang('User Panel') <span class="text--primary" title="@lang('If you enable this, users need KYC verification to access modules.')"><i class="fas text-white fa-info-circle"></i></span></h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label>@lang('Send Money') </label>
                                        <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="50" data-on="@lang('Enable')" data-off="@lang('Disabled')" name="modules[user][send_money]" @if (@$modules->user->send_money) checked @endif>
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label>@lang('Direct Payment') </label>
                                        <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="50" data-on="@lang('Enable')" data-off="@lang('Disabled')" name="modules[user][direct_payment]" @if (@$modules->user->direct_payment) checked @endif>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
