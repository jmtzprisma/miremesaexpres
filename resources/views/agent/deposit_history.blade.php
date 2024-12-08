@extends('agent.layouts.app')

@section('panel')
    <div class="row justify-content-center mt-5">
        <div class="col-12 col-md-4">
            <div class="d-widget curve--shape">
                <div class="d-widget__content">
                    <i class="las la-check-circle"></i> @lang('Successful Deposit')
                    <h2 class="d-widget__amount fw-normal">{{ __($general->cur_sym) }}{{ showAmount($successful) }}</h2>
                </div>

            </div><!-- d-widget end -->
        </div>
        <div class="col-12 col-md-4">
            <div class="d-widget curve--shape">
                <div class="d-widget__content">
                    <i class="las la-pause-circle"></i> @lang('Pending Deposit')
                    <h2 class="d-widget__amount fw-normal">{{ __($general->cur_sym) }}{{ showAmount($pending) }}</h2>
                </div>

            </div><!-- d-widget end -->
        </div>
        <div class="col-12 col-md-4">
            <div class="d-widget curve--shape">
                <div class="d-widget__content ">
                    <i class="las la-times-circle"></i> @lang('Rejected Deposit')
                    <h2 class="d-widget__amount fw-normal">{{ __($general->cur_sym) }}{{ showAmount($rejected) }}</h2>
                </div>

            </div><!-- d-widget end -->
        </div>
    </div>
    <div class="custom--card mt-3">

        <div class="card-body">
            <div class="row align-items-center flex-wrap mb-3">
                <div class="col-12 col-md-8">
                    <h6 class="mb-3 mb-md-0">@lang($pageTitle)</h6>
                </div>
                <div class="col-12 col-md-4">
                    <div class="text-end">
                        <form action="" method="GET">
                            <div class="search--box">
                                <input type="text" name="trx" class="form--control" value="{{ request()->trx }}" placeholder="@lang('Transaction ID')" autocomplete="off">
                                <button type="submit" class="search-box-btn">
                                    <i class="las la-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="table-responsive--sm">
                <table class="table table-responsive-md">
                    <thead>
                        <tr>
                            <th>@lang('Gateway | Transaction')</th>
                            <th>@lang('Initiated')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Conversion')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Details')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits as $deposit)
                            @php
                                $details = $deposit->detail ? json_encode($deposit->detail) : null;
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-bold"> <a href="{{ appendQuery('method', @$deposit->gateway->alias) }}">{{ __(@$deposit->gateway->name) }}</a> </span>
                                    <br>
                                    <small> {{ $deposit->trx }} </small>
                                </td>

                                <td>
                                    {{ showDateTime($deposit->created_at) }}<br>{{ diffForHumans($deposit->created_at) }}
                                </td>
                                <td>
                                    {{ __($general->cur_sym) }}{{ showAmount($deposit->amount) }} + <span class="text-danger" title="@lang('charge')">{{ showAmount($deposit->charge) }} </span>
                                    <br>
                                    <strong title="@lang('Amount with charge')">
                                        {{ showAmount($deposit->amount + $deposit->charge) }} {{ __($general->cur_text) }}
                                    </strong>
                                </td>
                                <td>
                                    1 {{ __($general->cur_text) }} = {{ showAmount($deposit->rate) }} {{ __($deposit->method_currency) }}
                                    <br>
                                    <strong>{{ showAmount($deposit->final_amo) }} {{ __($deposit->method_currency) }}</strong>
                                </td>
                                <td>
                                    @php echo $deposit->statusBadge @endphp
                                </td>
                                @php
                                    $details = $deposit->detail != null ? json_encode($deposit->detail) : null;
                                @endphp
                                <td>
                                    <a href="javascript:void(0)" class="btn btn-sm btn-outline--primary ms-1 @if ($deposit->method_code >= 1000) detailBtn @else disabled @endif"
                                        @if ($deposit->method_code >= 1000) data-info="{{ $details }}" @endif
                                        @if ($deposit->status == Status::PAYMENT_REJECT) data-admin_feedback="{{ $deposit->admin_feedback }}" @endif>
                                        <i class="la la-desktop"></i>@lang('Details')
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($deposits->hasPages())
                <div class="d-flex justify-content-end">
                    {{ paginateLinks($deposits) }}

                </div>
            @endif
        </div>
    </div>


    {{-- APPROVE MODAL --}}
    <div id="detailModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <button type="button" class="btn-sm btn-danger" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush userData mb-2">
                    </ul>
                    <div class="feedback"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            $('.detailBtn').on('click', function() {
                var modal = $('#detailModal');

                var userData = $(this).data('info');
                var html = '';
                if (userData) {
                    userData.forEach(element => {
                        if (element.type != 'file') {
                            html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${element.name}</span>
                                <span">${element.value}</span>
                            </li>`;
                        }
                    });
                }

                modal.find('.userData').html(html);

                if ($(this).data('admin_feedback') != undefined) {
                    var adminFeedback = `
                        <div class="my-3">
                            <strong>@lang('Admin Feedback')</strong>
                            <p>${$(this).data('admin_feedback')}</p>
                        </div>
                    `;
                } else {
                    var adminFeedback = '';
                }

                modal.find('.feedback').html(adminFeedback);


                modal.modal('show');
            });
        })(jQuery)
    </script>
@endpush
