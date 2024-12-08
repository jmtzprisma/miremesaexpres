@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 bg--transparent shadow-none">
                <div class="card-body bg-white p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>@lang('S.N.')</th>
                                    <th class="text-start">@lang('Name')</th>
                                    <th>@lang('Currency')</th>
                                    <th>@lang('Rate')</th>
                                    <th>@lang('Has Agent')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($countries as $country)
                                    <tr>
                                        <td>
                                            {{ $countries->firstItem() + $loop->index }}</td>
                                        <td class="text-start">
                                            <span class="user">
                                                <span class="thumb me-2">
                                                    <img alt="image" src="{{ getImage(getFilePath('country') . '/' . $country->image, getFileSize('country')) }}">
                                                </span>
                                                {{ $country->name }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $country->currency }}</td>
                                        <td>
                                            1 {{ $general->cur_text }} = {{ currencyFormatter($country->rate) }} {{ $country->currency }}
                                        </td>

                                        <td>@php echo $country->agentStatus;@endphp</td>

                                        <td>@php echo $country->statusBadge;@endphp</td>

                                        @php
                                            $country->image_with_path = getImage(getFilePath('country') . '/' . $country->image, getFileSize('country'));
                                        @endphp

                                        <td>
                                            <button aria-expanded="false" class="btn text-muted btn-sm border-0" data-bs-toggle="dropdown" type="button">
                                                <i class="fa fa-ellipsis-v"></i></button>

                                            <div class="dropdown-menu">
                                                <a class="dropdown-item cuModalBtn editBtn" data-has_status="true" data-modal_title="@lang('Update Country')" data-resource="{{ $country }}" href="javascript:void(0)"><i class="la la-pencil-alt"></i> @lang('Edit')</a>

                                                @if ($country->status)
                                                    <a class="dropdown-item confirmationBtn" data-action="{{ route('admin.country.update.status', $country->id) }}" data-question="@lang('Are you sure that you want to disable this country?')" href="javascript:void(0)"><i class="la la-eye-slash"></i> @lang('Disable')</a>
                                                @else
                                                    <a class="dropdown-item confirmationBtn" data-action="{{ route('admin.country.update.status', $country->id) }}" data-question="@lang('Are you sure that you want to enable this country?')" href="javascript:void(0)"><i class="la la-eye"></i> @lang('Enable')</a>
                                                @endif

                                                <a class="dropdown-item" href="{{ route('admin.country.currency.conversion.rate', $country->id) }}"><i class="la la-coins"></i> @lang('Conversion Rates')</a>
                                                <a class="dropdown-item" href="{{ route('admin.service.index', $country->id) }}"><i class="las la-list"></i> @lang('Services')</a>
                                                <a class="dropdown-item" href="{{ route('admin.country.charges.set', $country->id) }}"><i class="las la-comment-dollar"></i> @lang('Set Charges')</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="100%">
                                            {{ __($emptyMessage) }}
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($countries->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($countries) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ADD METHOD MODAL --}}
    <div class="modal fade" id="cuModal">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.country.store') }}" enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="modal-body row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>@lang('Image')</label>
                                <div class="image-upload">
                                    <div class="thumb">
                                        <div class="avatar-preview">
                                            <div class="profilePicPreview">
                                                <button type="button" class="remove-image"><i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                        <div class="avatar-edit">
                                            <input type="file" class="profilePicUpload" name="image" id="profilePicUpload" accept=".png, .jpg, .jpeg" required>
                                            <label for="profilePicUpload" class="bg--success">@lang('Upload Image')</label>

                                            <small class="mt-2 ">@lang('Supported files'): <b>@lang('jpeg'), @lang('jpg').</b> @lang('Image will be resized into') {{ getFileSize('country') }} @lang('px')</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>@lang('Country') </label>
                                <select class="form-control select2-basic" name="country_code" required>
                                    <option disabled selected value="">@lang('Select One')</option>
                                    @foreach ($countryList as $shortCode => $countryData)
                                        <option data-currency="{{ $countryData->currency->code }}" value="{{ $shortCode }}">{{ $countryData->country }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>@lang('Currency') </label>
                                <input class="form-control bg--white" name="currency" readonly type="text" value="{{ old('currency') }}" />
                            </div>

                            <div class="form-group">
                                <label>@lang('Rate')</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        1 {{ $general->cur_text }} =
                                    </span>
                                    <input class="form-control" name="rate" required step="any" type="number" value="{{ old('rate') }}" />
                                    <span class="input-group-text currency">{{ old('currency') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>@lang('Has Agent')</label>
                                <input data-height="45" data-off="@lang('No')" data-offstyle="-danger" data-on="@lang('Yes')" data-onstyle="-success" data-size="large" data-toggle="toggle" data-width="100%" name="has_agent" type="checkbox">
                            </div>

                            <div class="form-group">
                                <label>@lang('Sending Country')</label>
                                <input data-height="45" data-off="@lang('No')" data-offstyle="-danger" data-on="@lang('Yes')" data-onstyle="-success" data-size="large" data-toggle="toggle" data-width="100%" name="is_sending" type="checkbox">
                            </div>

                            <div class="form-group">
                                <label>@lang('Receiving Country')</label>
                                <input data-height="45" data-off="@lang('No')" data-offstyle="-danger" data-on="@lang('Yes')" data-onstyle="-success" data-size="large" data-toggle="toggle" data-width="100%" name="is_receiving" type="checkbox">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div aria-hidden="true" class="modal fade" id="helpModal" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">@lang('Information')</h5>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush list-group-numbered">
                        <li class="list-group-item">@lang("A country won't be displayed in the frontend if it is disabled.")</li>
                        <li class="list-group-item">@lang("A country won't be displayed as receiving country in the frontend if it has no active service.")</li>
                        <li class="list-group-item">@lang("If you don't set the conversion rate for a combination of sending and receiving countries, then the rate will be calculated by the rate in base currency.")</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <button class="btn btn-outline--info" data-bs-target="#helpModal" data-bs-toggle="modal" type="button">
        <i class="la la-info-circle"></i> @lang('Info')
    </button>

    <x-search-form placeholder="Name / Currency" />
    <button class="btn btn-outline--primary cuModalBtn" data-modal_title="@lang('Add New Country')" type="button"><i class="las la-plus"></i>@lang('Add New')</button>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            let cuModal = $('#cuModal');
            $('input[name=currency]').on('input', function() {
                $('.currency').text($(this).val());
            });

            $('.cuModalBtn').on('click', function() {
                let countryCode = `{{ old('country_code') }}`;
                cuModal.find('[type=checkbox]').bootstrapToggle("off");
                $(".select2-basic").val('');
                let resource = $(this).data('resource');

                if (resource) {
                    toggleSwitch(resource.has_agent, 'has_agent')
                    toggleSwitch(resource.is_sending, 'is_sending')
                    toggleSwitch(resource.is_receiving, 'is_receiving')
                    $('.currency').text(resource.currency);

                    $("[name=country_code]").val(resource.country_code);
                } else {
                    cuModal.find(".profilePicPreview").css("background-image", `url({{ getImage(null, getFileSize('country')) }})`);

                }

                $(".select2-basic").select2({
                    dropdownParent: cuModal
                });
            });

            function toggleSwitch(data, fieldName) {
                let element = cuModal.find(`[name=${fieldName}]`);
                if (data) {
                    $(element).bootstrapToggle("on");
                } else {
                    $(element).bootstrapToggle("off");
                }
            }

            $('select[name=country_code]').on('change', function() {
                $('[name=currency]').val($(this).find(':selected').data('currency'));
                $('.currency').text($(this).find(':selected').data('currency'));
            });

            $('.editBtn').on('click', function() {
                $('[type=file]').removeAttr('required');
            });

            cuModal.on('hidden.bs.modal', function() {
                $('[type=file]').attr('required', true);
            });

        })(jQuery);
    </script>
@endpush
@push('style')
    <style>
        .table-responsive {
            background: transparent;
            min-height: 350px;
        }

        .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.255em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }
    </style>
@endpush
