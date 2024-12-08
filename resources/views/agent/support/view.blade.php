@extends('agent.layouts.app')

@section('panel')
    <div class="row justify-content-center mt-5">
        <div class="col-lg-8">
            <div class="border--card h-auto">
                <div class="card-hseader card-header-bg d-flex flex-wrap justify-content-between align-items-center">
                    <h4 class="title">
                        @php echo $myTicket->statusBadge; @endphp
                        [@lang('Ticket')#{{ $myTicket->ticket }}] {{ $myTicket->subject }}
                    </h4>
                    @if ($myTicket->status != Status::TICKET_CLOSE && $myTicket->user)
                        <button class="btn btn-danger close-button btn-sm closeButton" data-bs-target="#closeModal" data-bs-toggle="modal" type="button"><i class="fa fa-lg fa-times-circle"></i>
                        </button>
                    @endif
                </div>
                <div class="card-body ">
                    <form action="{{ route('ticket.reply', $myTicket->id) }}" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row justify-content-between">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <textarea class="form--control" name="message" rows="4">{{ old('message') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <a class="btn btn--base btn-sm addFile" href="javascript:void(0)"><i class="fa fa-plus"></i> @lang('Add New')</a>
                        </div>
                        <div class="form-group">
                            <label class="form-label">@lang('Attachments')</label> <small class="text-danger">@lang('Max 5 files can be uploaded'). @lang('Maximum upload size is') {{ ini_get('upload_max_filesize') }}</small>
                            <input class="form-control form--control" name="attachments[]" type="file" />
                            <div id="fileUploadsContainer"></div>
                            <p class="my-2 ticket-attachments-message text-muted">
                                @lang('Allowed File Extensions'): .@lang('jpg'), .@lang('jpeg'), .@lang('png'), .@lang('pdf'), .@lang('doc'), .@lang('docx')
                            </p>
                        </div>
                        <button class="btn btn--base btn-md w-100" type="submit"> <i class="fa fa-reply"></i> @lang('Reply')</button>
                    </form>
                </div>
            </div>

            <div class="border--card mt-4 h-auto">
                <div class="card-body">
                    @foreach ($messages as $message)
                        @if ($message->admin_id == 0)
                            <div class="row border border-primary border-radius-3 my-3 py-3 mx-2">
                                <div class="col-md-3 border-end text-end">
                                    <h5 class="my-3">{{ $message->ticket->name }}</h5>
                                </div>
                                <div class="col-md-9">
                                    <p class="text-muted fw-bold my-3">
                                        @lang('Posted on') {{ $message->created_at->format('l, dS F Y @ H:i') }}</p>
                                    <p>{{ $message->message }}</p>
                                    @if ($message->attachments->count() > 0)
                                        <div class="mt-2">
                                            @foreach ($message->attachments as $k => $image)
                                                <a class="mr-3" href="{{ route('ticket.download', encrypt($image->id)) }}"><i class="fa fa-file"></i> @lang('Attachment') {{ ++$k }} </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="row border border-warning border-radius-3 my-3 py-3 mx-2" style="background-color: #ffd96729">
                                <div class="col-md-3 border-end text-end">
                                    <h5 class="my-3">{{ $message->admin->name }}</h5>
                                    <p class="lead text-muted">@lang('Staff')</p>
                                </div>
                                <div class="col-md-9">
                                    <p class="text-muted fw-bold my-3">
                                        @lang('Posted on') {{ $message->created_at->format('l, dS F Y @ H:i') }}</p>
                                    <p>{{ $message->message }}</p>
                                    @if ($message->attachments->count() > 0)
                                        <div class="mt-2">
                                            @foreach ($message->attachments as $k => $image)
                                                <a class="mr-3" href="{{ route('ticket.download', encrypt($image->id)) }}"><i class="fa fa-file"></i> @lang('Attachment') {{ ++$k }} </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <div class="modal custom--modal fade" id="closeModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('ticket.close', $myTicket->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                        <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                            <i aria-hidden="true" class="la la-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        @lang('Are you sure to close this ticket?')
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--danger" data-bs-dismiss="modal" type="button">@lang('No')</button>
                        <button class="btn btn--base" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@push('style')
    <style>
        .input-group-text:focus {
            box-shadow: none !important;
        }
    </style>
@endpush
@push('script')
    <script>
        (function($) {
            "use strict";
            var fileAdded = 0;
            $('.addFile').on('click', function() {
                if (fileAdded >= 4) {
                    notify('error', 'You\'ve added maximum number of file');
                    return false;
                }
                fileAdded++;
                $("#fileUploadsContainer").append(`
                    <div class="input-group my-3">
                        <input type="file" name="attachments[]" class="form--control" required />
                        <button class="input-group-text btn-danger remove-btn"><i class="las la-times"></i></button>
                    </div>
                `)
            });
            $(document).on('click', '.remove-btn', function() {
                fileAdded--;
                $(this).closest('.input-group').remove();
            });
        })(jQuery);
    </script>
@endpush
