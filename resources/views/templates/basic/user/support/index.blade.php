@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="section section--xl">
        <div class="container">
            <div class="d-flex flex-wrap flex-row-reverse justify-content-between align-items-center mb-lg-3">
                <div class="text-end">
                    <a href="{{ route('ticket.open') }}" class="btn btn-sm btn--base mb-2"> <i class="la la-plus"></i> @lang('New Ticket')</a>
                </div>
                <div class="custom--table__header">
                    <h5 class="text-lg-start m-0 text-center">@lang('Support Ticket')</h5>
                </div>
            </div>
            <div class="table-responsive--md">
                <table class="table custom--table">
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
                                    [@lang('Ticket')#{{ $support->ticket }}] {{ __($support->subject) }}
                                </td>
                                <td>
                                    @php echo $support->statusBadge; @endphp
                                </td>
                                <td>
                                    @if ($support->priority == Status::PRIORITY_LOW)
                                        <span class="badge badge--dark py-2 px-3">@lang('Low')</span>
                                    @elseif($support->priority == Status::PRIORITY_MEDIUM)
                                        <span class="badge badge--success py-2 px-3">@lang('Medium')</span>
                                    @elseif($support->priority == Status::PRIORITY_HIGH)
                                        <span class="badge badge--primary py-2 px-3">@lang('High')</span>
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($support->last_reply)->diffForHumans() }}
                                </td>

                                <td>
                                    <a href="{{ route('ticket.view', $support->ticket) }}" class="btn btn--base btn--sm">
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
            <div class="mt-3 d-flex justify-content-end">
                @if ($supports->hasPages())
                    {{ paginateLinks($supports) }}
                @endif
            </div>
        </div>
    </div>
@endsection
