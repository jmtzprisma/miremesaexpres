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
                                    <th>@lang('Email')</th>
                                    <th>@lang('Rol')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($admins as $adm)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $adm->name }}</span> <br>
                                            <span class="small">
                                                <span>@</span>{{ $adm->username }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $adm->email }}
                                        </td>
                                        <td>
                                            {{ \App\Models\Roles::find($adm->role_id)->name }}
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.adm.edit', $adm->id) }}" class="btn btn-sm btn-outline--info">
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
                @if ($admins->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($admins) }}
                    </div>
                @endif
            </div>
        </div>


    </div>
@endsection

@push('breadcrumb-plugins')
    <a class="btn btn-outline--primary h-45" href="{{ route('admin.adm.add') }}"><i class="las la-plus"></i>@lang('Add New')</a>
    <x-search-form placeholder="Search Username" />
@endpush
