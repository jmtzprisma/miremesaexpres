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
                                    <th>@lang('User')</th>
                                    <th>@lang('Email-Phone')</th>
                                    <th>@lang('Country')</th>
                                    <th>@lang('Joined At')</th>
                                    <th>@lang('Balance')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $user->fullname }}</span>
                                            <br>
                                            <span class="small">
                                                <a href="{{ route('admin.users.detail', $user->id) }}"><span>@</span>{{ $user->username }}</a>
                                            </span>
                                        </td>

                                        <td>
                                            {{ $user->email }}<br>{{ $user->mobile }}
                                        </td>
                                        <td>
                                            <span class="fw-bold" title="{{ @$user->address->country }}">{{ $user->country_code }}</span>
                                        </td>

                                        <td>
                                            {{ showDateTime($user->created_at) }} <br> {{ diffForHumans($user->created_at) }}
                                        </td>

                                        <td>
                                            <span class="fw-bold">

                                                {{ $general->cur_sym }}{{ showAmount($user->balance) }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="button--group">
                                                <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.users.detail', $user->id) }}">
                                                    <i class="las la-desktop"></i>&nbsp;&nbsp;@lang('Details')
                                                </a>
                                                @if (request()->routeIs('admin.users.kyc.pending'))
                                                    <a class="btn btn-sm btn-outline--dark" href="{{ route('admin.users.kyc.details', $user->id) }}" target="_blank">
                                                        <i class="las la-user-check"></i>@lang('KYC Data')
                                                    </a>
                                                @endif
                                                @if ($user->kv == 0 && $user->kyc)
                                                <br>
                                                <button class="btn btn-outline--success confirmationBtn" data-process="approve" data-question="@lang('Are you sure to approve this documents?')" data-action="{{ route('admin.users.kyc.approve', $user->id) }}"><i class="las la-check"></i>@lang('Approve')</button>
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
                @if ($users->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($users) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Username / Email" />
@endpush
