@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Instrucciones')</th>
                                    <th>@lang('Options')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bancos as $banco)
                                    <tr>
                                        <td>
                                            <span class="text--muted fw-bold">{{ $banco->name }}</span>
                                        </td>
                                        <td>
                                            <span class="small">{{ $banco->instrucciones }}</span>
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.send.money.edit_banco', $banco->id) }}">
                                                <i class="las la-edit"></i>@lang('Editar')
                                            </a>
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
                @if ($bancos->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($bancos) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a class="btn btn-outline--primary h-45" href="{{ route('admin.send.money.add_banco') }}"><i class="las la-plus"></i>@lang('Add New')</a>
    <x-search-form placeholder="Nombre" />
@endpush
