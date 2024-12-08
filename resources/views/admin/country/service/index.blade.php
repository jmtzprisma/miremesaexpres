@extends('admin.layouts.app')
@section('panel')
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive--sm table-responsive">
                <table class="table table-responsive-md">
                    <thead>
                        <tr>
                            <th>@lang('SL')</th>
                            <th>@lang('Name')</th>
                            <th>@lang('Country')</th>
                            <th>@lang('Delivery Method')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $service)
                            <tr>
                                <td>{{ $services->firstItem() + $loop->index }}</td>
                                <td>{{ __($service->name) }}</td>
                                <td>{{ __($service->countryDeliveryMethod->country->name) }}</td>
                                <td>{{ __($service->countryDeliveryMethod->deliveryMethod->name) }}</td>
                                <td>@php echo $service->statusBadge @endphp</td>
                                <td>
                                    <div class="d-flex flex-wrap justify-content-end gap-1">
                                        <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.service.edit', $service->id) }}"><i class="la la-pencil"></i>@lang('Edit')</a>
                                        @if ($service->status)
                                            <button class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.service.status', $service->id) }}" data-question="@lang('Are you sure that, you want to disable this service?')" type="button"><i class="las la-eye-slash"></i>@lang('Disable')</button>
                                        @else
                                            <button class="btn btn-sm btn-outline--success confirmationBtn" data-action="{{ route('admin.service.status', $service->id) }}" data-question="@lang('Are you sure that, you want to enable this service?')" type="button"><i class="las la-eye-slash"></i>@lang('Enable')</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="100"> {{ __($emptyMessage) }} </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($services->hasPages())
            <div class="card-footer py-4">
                @php echo paginateLinks($services) @endphp
            </div>
        @endif
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <div class="position-relative min-w-200px">
        <select class="form-control select2-basic" name="country_id" required>
            <option value="">@lang('All')</option>
            @foreach ($countries as $country)
                <option @selected(request()->country_id == $country->id) value="{{ $country->id }}">{{ __($country->name) }}</option>
            @endforeach
        </select>
    </div>
    <x-search-form placeholder="Search..." />
    <a class="btn btn-outline--primary h-45" data-modal_title="@lang('Add Service')" href="{{ route('admin.service.add') }}"><i class="las la-plus"></i>@lang('Add Service')</a>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.select2-basic').select2({
                dropdownParent: $('.select2-basic').parent()
            });

            $('.select2-basic').on("change", function() {
                window.location.href = `${window.location.pathname}?country_id=${$(this).val()}`;
            });
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .min-w-200px {
            min-width: 200px
        }
    </style>
@endpush
