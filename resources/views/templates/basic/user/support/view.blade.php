@extends($activeTemplate . 'layouts.' . $layout)

@section('content')
    <div class="section section--xl" style="overflow: auto">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card custom--card">
                        <div class="card-header card-header-bg d-flex flex-wrap justify-content-between align-items-center">
                            <h5 class="m-0">
                                @php echo $myTicket->statusBadge; @endphp
                                [@lang('Ticket')#{{ $myTicket->ticket }}] {{ $myTicket->subject }}
                            </h5>
                            @if ($myTicket->status != Status::TICKET_CLOSE && $myTicket->user)
                                <button class="btn btn-danger close-button btn-sm closeButton" data-bs-toggle="modal" data-bs-target="#closeModal" type="button"><i class="fa fa-lg fa-times-circle"></i>
                                </button>
                            @endif
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('ticket.reply', $myTicket->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row justify-content-between">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <textarea name="message" class="form-control form--control-textarea" rows="4">{{ old('message') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <a href="javascript:void(0)" class="btn btn--base btn-sm addFile"><i class="fa fa-plus"></i> @lang('Add New')</a>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">@lang('Attachments')</label> <small class="text-danger">@lang('Max 5 files can be uploaded'). @lang('Maximum upload size is') {{ ini_get('upload_max_filesize') }}</small>
                                    <input type="file" name="attachments[]" class="form-control form--control" />
                                    <div id="fileUploadsContainer"></div>
                                    <p class="my-2 ticket-attachments-message text-muted">
                                        @lang('Allowed File Extensions'): .@lang('jpg'), .@lang('jpeg'), .@lang('png'), .@lang('pdf'), .@lang('doc'), .@lang('docx')
                                    </p>
                                </div>
                                <button type="submit" class="btn btn--base btn--xl w-100"> <i class="fa fa-reply"></i> @lang('Reply')</button>
                            </form>
                        </div>
                    </div>

                    <div class="card custom--card mt-4">
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
                                                <ul class="list list--row">
                                                    @foreach ($message->attachments as $k => $image)
                                                        <li>
                                                            <a href="{{ route('ticket.download', encrypt($image->id)) }}" class="t-link file-button">
                                                                <span class="d-inline-block me-1">
                                                                    <i class="fa fa-file"></i>
                                                                </span> @lang('Attachment') {{ ++$k }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
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
                                                        <a href="{{ route('ticket.download', encrypt($image->id)) }}" class="t-link file-button">
                                                            <span class="d-inline-block me-1">
                                                                <i class="fa fa-file"></i>
                                                            </span> @lang('Attachment') {{ ++$k }}
                                                        </a>
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
        </div>
    </div>

    <div id="closeModal" class="modal custom--modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('ticket.close', $myTicket->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                        <button type="button" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" aria-label="Close">
                            <i class="la la-times" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        @lang('Are you sure to close this ticket?')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--danger" data-bs-dismiss="modal">@lang('No')</button>
                        <button type="submit" class="btn btn--primary">@lang('Yes')</button>
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
                        <input type="file" name="attachments[]" class="form-control form--control" required />
                        <button type="button" class="input-group-text btn-danger remove-btn"><i class="las la-times"></i></button>
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
