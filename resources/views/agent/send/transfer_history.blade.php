@extends('agent.layouts.app')
@section('panel')
    <div class="custom--card mt-5">
        <div class="card-body">
            <div class="row align-items-center mb-3 flex-wrap">
                <div class="col-12 col-md-8">
                    <h6 class="mb-md-0 mb-3">@lang($pageTitle)</h6>
                </div>
                <div class="col-12 col-md-4">
                    <div class="text-end">
                        <form action="" method="GET">
                            <div class="search--box">
                                <input type="text" name="search" class="form--control" value="{{ request()->search }}" placeholder="@lang('MTCN Number')" autocomplete="off">
                                <button type="submit" class="search-box-btn">
                                    <i class="las la-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="table-responsive--md">
                <table class="table table-striped table-hover border-info">
                    <thead>
                        <tr>
                            <th>@lang('MTCN')</th>
                            <th>@lang('Sent Amount') </th>
                            <th>@lang('Recipient')</th>
                            <th>@lang('Recipient Will Get')</th>
                            <th>@lang('Sent Date')</th>
                            <th>@lang('Payout Date')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transfers as $transfer)
                            <tr>
                                <td>
                                    {{ $transfer->mtcn_number }}</td>
                                <td>
                                    <strong>{{ showAmount($transfer->sending_amount) }} {{ __($transfer->sending_currency) }}</strong>
                                </td>
                                <td>
                                    {{ $transfer->recipient->name }}
                                </td>
                                <td>
                                    {{ showAmount($transfer->recipient_amount) }} {{ __($transfer->recipient_currency) }}
                                </td>
                                <td>
                                    <span title="{{ diffForHumans($transfer->created_at) }}"> {{ showDateTime($transfer->created_at) }}</span>
                                </td>
                                <td>
                                    @if ($transfer->received_at)
                                        <span title="{{ diffForHumans($transfer->received_at) }}"> {{ showDateTime($transfer->received_at) }}</span>
                                    @else
                                        @lang('N/A')
                                    @endif
                                </td>
                                <td>
                                    @php
                                        echo $transfer->paymentStatusBadge;
                                    @endphp
                                </td>
                                @php
                                    $details = $transfer->detail != null ? json_encode($transfer->detail) : null;
                                @endphp

                                <td>
                                    <button class="btn btn-outline--primary btn-sm approveBtn" data-info="{{ $details }}" data-id="{{ encrypt($transfer->id) }}" data-name="{{ $transfer->recipient->name }}" data-mobile="{{ @$transfer->recipient->dial_code . $transfer->recipient->mobile }}" data-address="{{ $transfer->recipient->address }}" data-country="{{ @$transfer->recipientCountry->name }}" data-trx="{{ $transfer->trx }}" data-status="{{ $transfer->status }}">
                                        <i class="la la-desktop"></i> @lang('Details')
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center">{{ __($emptyMessage) }}</td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
            @if ($transfers->hasPages())
                <div class="d-flex justify-content-end">
                    {{ paginateLinks($transfers) }}

                </div>
            @endif
        </div>
    </div>

    {{-- Details MODAL --}}
    <div id="detailsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <button type="button" class="btn-sm btn-danger" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">@lang('Recipient Name') : <span class="name fw-bold"></span></li>
                        <li class="list-group-item d-flex justify-content-between">@lang('Recipient Number') : <span class="mobile fw-bold"></span></li>
                        <li class="list-group-item d-flex justify-content-between">@lang('Recipient Address') : <span class="address fw-bold"></span></li>
                        <li class="list-group-item d-flex justify-content-between">@lang('Recipient Country') : <span class="country fw-bold"></span></li>
                        <li class="list-group-item d-flex justify-content-between">@lang('Transaction No.') : <span class="trx fw-bold"></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        (function($) {
            "use strict";
            $('.approveBtn').on('click', function() {
                var modal = $('#detailsModal');
                modal.find('.name').text($(this).data('name'));
                modal.find('.mobile').text('+' + $(this).data('mobile'));
                modal.find('.country').text($(this).data('country'));
                modal.find('.address').text($(this).data('address'));
                modal.find('.trx').text($(this).data('trx'));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
