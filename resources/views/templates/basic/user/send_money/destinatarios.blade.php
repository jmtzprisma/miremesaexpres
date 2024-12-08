@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="section section--xl">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-lg-3 flex-row-reverse flex-wrap">
                <div class="text-end">
                    <a class="btn btn-sm btn--base mb-2" href="{{ route('user.send.money.now') }}"> <i class="la la-plus"></i> @lang('Send New')</a>
                </div>
                <div class="custom--table__header">
                    <h5 class="text-lg-start m-0 text-center">{{ __($pageTitle) }}</h5>
                </div>
            </div>
            <div class="table-responsive--md">
                
                <table class="table table-striped table-hover border-info">
                    <thead>
                        <tr>
                            <th>@lang('Name')</th>
                            <th>@lang('Mobile')</th>
                            <th>@lang('Email')</th>
                            <th>@lang('Address')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recipients as $transfer)
                            <tr>
                                <td><span class="fw-bold">{{ $transfer->name }}</span></td>
                                <td>{{ $transfer->mobile }}</td>
                                <td>{{ $transfer->email }}</td>
                                <td>{{ $transfer->address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                
            </div>
            @if ($recipients->hasPages())
                <div class="d-flex justify-content-end mt-3">
                    {{ paginateLinks($recipients) }}
                </div>
            @endif
        </div>
    </div>
@endsection
@push('script')
    <script>
        (function($) {
            "use strict";
            
        })(jQuery);
    </script>
@endpush
