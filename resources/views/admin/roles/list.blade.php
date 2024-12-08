@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>@lang('Nombre')</th>
                                    <th>@lang('Permisos')</th>
                                    <th>@lang('Bancos')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $rol)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $rol->name }}</span>
                                        </td>
                                        <td>
                                            @foreach(explode(',', $rol->permissions) as $itm)
                                                @if($itm == '1')
                                                    <span class="badge badge--success">SuperAdmin</span>
                                                @elseif($itm == '2')
                                                    <span class="badge badge--success">Bancos Envía</span>
                                                @elseif($itm == '3')
                                                    <span class="badge badge--success">Bancos Recibe</span>
                                                @elseif($itm == '4')
                                                    <span class="badge badge--success">@lang('Send Money')</span>
                                                @elseif($itm == '5')
                                                    <span class="badge badge--success">@lang('Manage Users')</span>
                                                @elseif($itm == '6')
                                                    <span class="badge badge--success">@lang('Manage Agents')</span>
                                                @elseif($itm == '7')
                                                    <span class="badge badge--success">@lang('Report')</span>
                                                @elseif($itm == '8')
                                                    <span class="badge badge--success">@lang('Payments')</span>
                                                @elseif($itm == '9')
                                                    <span class="badge badge--success">Envío manual</span>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            @if(!is_null($rol->bancos))
                                            @foreach(\App\Models\Bank::whereIn('id', explode(",", $rol->bancos))->get() as $itm)
                                                <span class="badge badge--info">{{ $itm->name }}</span>
                                            @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.roles.edit', $rol->id) }}" class="btn btn-sm btn-outline--info">
                                                    <i class="las la-pencil"></i>&nbsp;&nbsp; @lang('Edit')
                                                </a>
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
                @if ($roles->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($roles) }}
                    </div>
                @endif
            </div>
        </div>


    </div>
@endsection

@push('breadcrumb-plugins')
    <a class="btn btn-outline--primary h-45" href="{{ route('admin.roles.add') }}"><i class="las la-plus"></i>@lang('Add New')</a>
    <x-search-form placeholder="Search Role" />
@endpush
