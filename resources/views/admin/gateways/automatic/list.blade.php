@extends('admin.layouts.app')

@section('panel')
    <div class="row">

        <div class="col-lg-12">
            <div class="card b-radius--10 ">

                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>@lang('Gateway')</th>
                                    <th>@lang('Supported Currency')</th>
                                    <th>@lang('Enabled Currency')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gateways->sortBy('alias') as $k=>$gateway)
                                    <tr>
                                        <td>{{ __($gateway->name) }}</td>

                                        <td>
                                            {{ collect($gateway->supported_currencies)->count() }}
                                        </td>
                                        <td>
                                            {{ $gateway->currencies->count() }}
                                        </td>

                                        <td>
                                            @if ($gateway->status == 1)
                                                <span class="badge badge--success">@lang('Enabled')</span>
                                            @else
                                                <span class="badge badge--warning">@lang('Disabled')</span>
                                            @endif

                                        </td>
                                        <td>
                                            <div class="button--group">
                                                @if($gateway->code != 120)
                                                <a href="{{ route('admin.gateway.automatic.edit', $gateway->alias) }}" class="btn btn-sm btn-outline--primary editGatewayBtn">
                                                    <i class="la la-pencil"></i> @lang('Edit')
                                                </a>
                                                @endif

                                                @if ($gateway->status == Status::DISABLE)
                                                    <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn" data-question="@lang('Are you sure to enable this gateway?')" data-action="{{ route('admin.gateway.automatic.status', $gateway->id) }}">
                                                        <i class="la la-eye"></i> @lang('Enable')
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn" data-question="@lang('Are you sure to disable this gateway?')" data-action="{{ route('admin.gateway.automatic.status', $gateway->id) }}">
                                                        <i class="la la-eye-slash"></i> @lang('Disable')
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
            </div><!-- card end -->
        </div>
    </div>

    <x-confirmation-modal />
@endsection
@push('breadcrumb-plugins')
    <div class="d-flex flex-wrap justify-content-end">
        <div class="d-inline">
            <div class="input-group justify-content-end">
                <input type="text" name="search_table" class="form-control bg--white" placeholder="@lang('Search')...">
                <button class="btn btn--primary input-group-text"><i class="fa fa-search"></i></button>
            </div>
        </div>
    </div>
@endpush
