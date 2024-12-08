@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card b-radius--10">
                <div class="card-body">
                    @if ($user->kyc_data)
                        <ul class="list-group ">
                            @foreach ($user->kyc_data as $val)
                                @continue(!$val->value)
                                <li class="list-group-item list-group-flush d-flex justify-content-between align-items-center">
                                    {{ __($val->name) }}
                                    <span>
                                        @if ($val->type == 'checkbox')
                                            {{ implode(',', $val->value) }}
                                        @elseif($val->type == 'file')
                                            @if ($val->value)
                                                <a href="{{ route('admin.download.attachment', encrypt(getFilePath('verify') . '/' . $val->value)) }}" class="me-3"><i class="fa fa-file"></i> @lang('Attachment') </a>
                                            @else
                                                @lang('No File')
                                            @endif
                                        @else
                                            <p>{{ __($val->value) }}</p>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <h5 class="text-center">@lang('KYC data not found')</h5>
                    @endif

                    @if ($user->kv == 2)
                        <div class="d-flex flex-wrap justify-content-end mt-3">
                            <button class="btn btn-outline--danger me-3 confirmationBtn" data-process="reject" data-question="@lang('Are you sure to reject this documents?')" data-action="{{ route('admin.users.kyc.reject', $user->id) }}"><i class="las la-ban"></i>@lang('Reject')</button>
                            <button class="btn btn-outline--success confirmationBtn" data-process="approve" data-question="@lang('Are you sure to approve this documents?')" data-action="{{ route('admin.users.kyc.approve', $user->id) }}"><i class="las la-check"></i>@lang('Approve')</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div id="confirmationModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="question"></p>
                        <div class="row" id="dvReason">
                            <div class="col-12">
                                <textarea class="form-control" name="reason" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('No')</button>
                        <button type="submit" class="btn btn--primary">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    @push('script')
    
    <script>
        (function ($) {
            "use strict";
            $(document).on('click','.confirmationBtn', function () {
                var modal   = $('#confirmationModal');
                let data    = $(this).data();

                if(data.process == 'reject'){
                    $("#dvReason").removeClass("d-none");
                }else{
                    $("#dvReason").addClass("d-none");
                }
                modal.find('.question').text(`${data.question}`);
                modal.find('form').attr('action', `${data.action}`);
                modal.modal('show');
            });
        })(jQuery);
    </script>
    @endpush
    
@endsection
