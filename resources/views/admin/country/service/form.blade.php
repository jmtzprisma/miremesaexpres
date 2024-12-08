@extends('admin.layouts.app')
@section('panel')
    <form action="{{ route('admin.service.add.update', $service->id ?? 0) }}" enctype="multipart/form-data" method="post">
        <div class="row gy-3">
            <div class="col-12">
                <div class="card">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Country')</label>
                                    <select class="form-control select2-basic" name="country_id" required>
                                        <option disabled selected value="">@lang('Select One')</option>
                                        @foreach ($countries as $country)
                                            <option @selected(@$service->countryDeliveryMethod->country_id == $country->id) value="{{ $country->id }}">{{ __($country->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Delivery Method')</label>
                                    <select class="form-control" name="delivery_method_id" required>
                                        <option disabled selected value="">@lang('Select One')</option>
                                        @foreach ($deliveryMethods as $deliveryMethod)
                                            <option @selected(@$service->countryDeliveryMethod->delivery_method_id == $deliveryMethod->id) value="{{ $deliveryMethod->id }}">{{ __($deliveryMethod->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Service Name')</label>
                                    <input class="form-control" name="name" required type="text" value="{{ old('name', @$service->name) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border--primary">
                    <div class="card-header bg--primary d-flex justify-content-between">
                        <h5 class="text-white">@lang('Form for User')</h5>
                        <button class="btn btn-sm btn-outline-light float-end form-generate-btn" type="button"> <i class="la la-fw la-plus"></i>@lang('Add New')</button>
                    </div>
                    <div class="card-body">
                        <div class="row addedField">
                            @if (@$service->form)
                                @foreach ($service->form->form_data as $formData)
                                    <div class="col-md-4">
                                        <div class="card border mb-3" id="{{ $loop->index }}">
                                            <input name="form_generator[is_required][]" type="hidden" value="{{ $formData->is_required }}">
                                            <input name="form_generator[extensions][]" type="hidden" value="{{ $formData->extensions }}">
                                            <input name="form_generator[options][]" type="hidden" value="{{ implode(',', $formData->options) }}">

                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>@lang('Label')</label>
                                                    <input class="form-control" name="form_generator[form_label][]" readonly type="text" value="{{ $formData->name }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>@lang('Type')</label>
                                                    <input class="form-control" name="form_generator[form_type][]" readonly type="text" value="{{ $formData->type }}">
                                                </div>
                                                @php
                                                    $jsonData = json_encode([
                                                        'type' => $formData->type,
                                                        'is_required' => $formData->is_required,
                                                        'label' => $formData->name,
                                                        'extensions' => explode(',', $formData->extensions) ?? 'null',
                                                        'options' => $formData->options,
                                                        'old_id' => '',
                                                    ]);
                                                @endphp
                                                <div class="btn-group w-100">
                                                    <button class="btn btn--primary editFormData" data-form_item="{{ $jsonData }}" data-update_id="{{ $loop->index }}" type="button"><i class="las la-pen"></i></button>
                                                    <button class="btn btn--danger removeFormData" type="button"><i class="las la-times"></i></button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
            </div>
        </div>
    </form>
    <x-form-generator />
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.service.index') }}" />
@endpush

@push('script')
    <script>
        "use strict"
        var formGenerator = new FormGenerator();
        $(".select2-basic").select2({
            dropdownParent: $(".select2-basic").parent()
        });
    </script>

    <script src="{{ asset('assets/global/js/form_actions.js') }}"></script>
@endpush

@push('style')
    <style>
        .select2-container {
            z-index: 9 !important;
        }
    </style>
@endpush
