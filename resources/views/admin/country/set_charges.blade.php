@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 bg--transparent shadow-none">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>@lang('S.N.')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Fixed Charge')</th>
                                    <th>@lang('Percent Charge')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($country->countryDeliveryMethods as $countryDeliveryMethod)
                                    @php
                                        $deliveryMethod = $countryDeliveryMethod->deliveryMethod;
                                        $deliveryCharge = $countryDeliveryMethod->charge;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ __($deliveryMethod->name) }}</td>
                                        <td>{{ showAmount(@$deliveryCharge->fixed_charge) }} {{ __($country->currency) }}</td>
                                        <td>{{ showAmount(@$deliveryCharge->percent_charge) }}%</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline--primary btnSetCharge" data-method_name="{{ __(@$deliveryMethod->name) }}" data-id="{{ @$deliveryMethod->id }}" data-fixed_charge="{{ @$deliveryCharge->fixed_charge }}" data-percent_charge="{{ @$deliveryCharge->percent_charge }}"><i class="las la-coins"></i>@lang('Set Charge')</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="100%">
                                            @lang('No service added for this country yet. You need to add services') <a href="{{ route('admin.service.index', $country->id) }}">@lang('From Here')</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="chargeModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Set Charge for') <span class="methodName fw-bold"></span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.country.charges.save', $country->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="delivery_method_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Fixed Charge')</label>
                            <div class="input-group">
                                <input type="number" step="any" min="0" class="form-control" name="fixed_charge" required>
                                <span class="input-group-text">{{ __($country->currency) }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Percent Charge')</label>
                            <div class="input-group">
                                <input type="number" step="any" min="0" class="form-control" name="percent_charge" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.btnSetCharge').on('click', function() {
                let modal = $('#chargeModal');
                let methodName = $(this).data('method_name');
                let fixedCharge = $(this).data('fixed_charge');
                let percentCharge = $(this).data('percent_charge');
                let deliveryMethodId = $(this).data('id');

                modal.find('.methodName').text(methodName);
                modal.find('[name=delivery_method_id]').val(deliveryMethodId);
                modal.find('[name=fixed_charge]').val(fixedCharge);
                modal.find('[name=percent_charge]').val(percentCharge);
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
