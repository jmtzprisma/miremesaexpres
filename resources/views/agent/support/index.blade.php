@extends('agent.layouts.app')

@section('panel')
    <div class="custom--card mt-5">
        <div class="card-body">
            <div class="d-flex flex-wrap flex-row-reverse justify-content-between align-items-center mb-lg-3">
                <div class="text-end">
                    <a href="{{ route('agent.ticket.open') }}" class="btn btn-sm btn--base mb-2"> <i class="fa fa-plus"></i> @lang('New Ticket')</a>
                </div>
                <div class="">
                    <h6 class="text-lg-start m-0 text-center">@lang('Support Ticket')</h6>
                </div>
            </div>

            <div class="table-responsive--md">
                <table class="table table-responsive-md">
                    <thead>
                        <tr>
                            <th>@lang('Subject')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Priority')</th>
                            <th>@lang('Last Reply')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($supports as $support)
                            <tr>
                                <td>
                                    [@lang('Ticket')#{{ $support->ticket }}] {{ __($support->subject) }} </td>
                                <td>
                                    @if ($support->status == 0)
                                        <span class="badge badge--success py-2 px-3">@lang('Open')</span>
                                    @elseif($support->status == 1)
                                        <span class="badge badge--primary py-2 px-3">@lang('Answered')</span>
                                    @elseif($support->status == 2)
                                        <span class="badge badge--warning py-2 px-3">@lang('Customer Reply')</span>
                                    @elseif($support->status == 3)
                                        <span class="badge badge--dark py-2 px-3">@lang('Closed')</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($support->priority == 1)
                                        <span class="badge badge--dark py-2 px-3">@lang('Low')</span>
                                    @elseif($support->priority == 2)
                                        <span class="badge badge--success py-2 px-3">@lang('Medium')</span>
                                    @elseif($support->priority == 3)
                                        <span class="badge badge--primary py-2 px-3">@lang('High')</span>
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($support->last_reply)->diffForHumans() }} </td>

                                <td>
                                    <a href="{{ route('agent.ticket.view', $support->ticket) }}" class="btn btn--base btn-sm">
                                        <i class="fa fa-desktop"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center text-muted">{{ __($emptyMessage) }} </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
            @if ($supports->hasPages())
                <div class="d-flex justify-content-end">
                    {{ paginateLinks($supports) }}

                </div>
            @endif
        </div>
    </div>
@endsection
