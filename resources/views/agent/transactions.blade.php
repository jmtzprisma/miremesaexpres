@extends('agent.layouts.app')

@section('panel')
    <div class="row justify-content-center mt-5">
        <div class="col-12">
            <div class="card custom--card border-0">
                <div class="card-header bg--dark border text-white">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h6 class="text-white">{{ __($pageTitle) }}</h6>
                        </div>
                        <div class="col-4 text-end">
                            <button class="trans-search-open-btn bg-transparent text-white"><i class="las la-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <form class="transaction-top-form d-none my-3 px-3" action="" method="GET">
                        <div class="custom-select-search-box">
                            <input type="text" name="trx" class="form--control" value="{{ request()->trx }}" placeholder="@lang('Transaction ID')" required>
                            <button type="submit" class="search-box-btn">
                                <i class="las la-search"></i>
                            </button>
                        </div>
                    </form><!-- transaction-top-form end -->
                    <div class="row gy-3 gx-3 transaction-top-select p-3">
                        <div class="col-md-4">
                            <div class="custom-select-box-two">
                                <label>@lang('Transaction type')</label>
                                <select onChange="window.location.href=this.value">
                                    <option value="{{ appendQuery('trx_type', '') }}" @selected(request()->type == '')>@lang('All Type')</option>
                                    <option value="{{ appendQuery('trx_type', '+') }}" @selected(request()->type == '+')>@lang('Plus Transactions')</option>
                                    <option value="{{ appendQuery('trx_type', '-') }}" @selected(request()->type == '-')>@lang('Minus Transactions')</option>
                                </select>
                            </div><!-- custom-select-box-two end -->
                        </div>

                        <div class="col-md-4">
                            <div class="custom-select-box-two">
                                <label>@lang('Remarks')</label>
                                <select onChange="window.location.href=this.value">
                                    <option value="{{ appendQuery('remark', '') }}" @selected(request()->remark == '')>@lang('Any')</option>
                                    @foreach ($remarks as $remark)
                                        <option value="{{ appendQuery('remark', $remark->remark) }}" @selected(request()->remark == $remark->remark)>{{ __(keyToTitle($remark->remark)) }}</option>
                                    @endforeach
                                </select>
                            </div><!-- custom-select-box-two end -->
                        </div>

                        <div class="col-md-4">
                            <div class="custom-select-box-two">
                                <label>@lang('History From')</label>
                                <select onChange="window.location.href=this.value">
                                    <option value="{{ appendQuery('duration', '') }}" @selected(request()->duration == '')>@lang('All Time')</option>
                                    <option value="{{ appendQuery('duration', 'last-24-hours') }}" @selected(request()->duration == 'last-24-hours')>@lang('Last 24 hours')</option>
                                    <option value="{{ appendQuery('duration', 'last-week') }}" @selected(request()->duration == 'last-week')>@lang('Last week')</option>
                                    <option value="{{ appendQuery('duration', 'last-15-days') }}" @selected(request()->duration == 'last-15-days')>@lang('Last 15 days')</option>
                                    <option value="{{ appendQuery('duration', 'last-month') }}" @selected(request()->duration == 'last-month')>@lang('Last month')</option>
                                    <option value="{{ appendQuery('duration', 'last-year') }}" @selected(request()->duration == 'last-year')>@lang('Last Year')</option>
                                </select>
                            </div><!-- custom-select-box-two end -->
                        </div>
                    </div>
                    <div class="accordion table--acordion" id="transactionAccordion">
                        @forelse ($transactions as $transaction)
                            <div class="accordion-item transaction-item {{ $transaction->trx_type == '-' ? 'sent-item' : 'rcv-item' }}">
                                <h2 class="accordion-header" id="h-{{ $loop->iteration }}">
                                    <div class="accordion-button collapsed d-flex flex-wrap gap-3" type="button" data-bs-toggle="collapse" data-bs-target="#c-{{ $loop->iteration }}" aria-expanded="false" aria-controls="c-1">

                                        <div class="icon-wrapper order-1">
                                            <div class="left">
                                                <div class="icon">
                                                    <i class="las la-long-arrow-alt-right"></i>
                                                </div>
                                                <div class="content">
                                                    <h6 class="trans-title">{{ keyToTitle($transaction->remark) }}</h6>
                                                    <span class="text-muted font-size--14px mt-2">{{ showDateTime($transaction->created_at, 'M d Y @g:i:a') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="content-wrapper flex-grow-1 order-3">
                                            <p class="text-muted text-start font-size--14px"><b>{{ __($transaction->details) }} {{ $transaction->receiver ? @$transaction->receiver->username : '' }}</b></p>
                                        </div>

                                        <div class="order-sm-3 text-end amount-wrapper order-2 flex-shrink-0">
                                            <p><b> {{ $general->cur_sym . showAmount($transaction->amount) }} </b></p>
                                        </div>
                                    </div>
                                </h2>

                                <div id="c-{{ $loop->iteration }}" class="accordion-collapse collapse" aria-labelledby="h-1" data-bs-parent="#transactionAccordion">
                                    <div class="accordion-body">
                                        <ul class="caption-list">
                                            <li>
                                                <span class="caption">@lang('Transaction ID')</span>
                                                <span class="value">{{ $transaction->trx }}</span>
                                            </li>
                                            <li>
                                                <span class="caption">@lang('Wallet')</span>
                                                <span class="value">{{ $general->cur_text }}</span>
                                            </li>

                                            <li>
                                                <span class="caption">@lang('Transacted Amount')</span>
                                                <span class="value">{{ showAmount($transaction->amount) }} {{ $general->cur_text }}</span>
                                            </li>
                                            <li>
                                                <span class="caption">@lang('Remaining Balance')</span>
                                                <span class="value">{{ showAmount($transaction->post_balance) }} {{ $general->cur_text }}</span>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </div><!-- transaction-item end -->
                        @empty
                            <div class="accordion-body text-center">
                                <h4 class="text--muted">@lang('No transaction found')</h4>
                            </div>
                        @endforelse
                    </div>
                </div>
                @if ($transactions->hasPages())
                    <div class="card-footer d-flex justify-content-end bg-transparent">
                        {{ paginateLinks($transactions) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        (function($) {
            $('.trans-search-open-btn').on('click', function() {
                $('.transaction-top-form,.transaction-top-select').toggleClass('d-none');
            })
        })(jQuery);
    </script>
@endpush
