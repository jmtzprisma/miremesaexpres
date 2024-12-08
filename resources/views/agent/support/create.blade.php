@extends('agent.layouts.app')

@section('panel')
    <div class="row justify-content-center mt-5">
        <div class="col-lg-8">
            <div class="border--card">
                <h4 class="title"><i class="las la-ticket-alt"></i> {{ __($pageTitle) }}</h4>
                <div class="card-body ">
                    <form action="{{ route('agent.ticket.store') }}" method="post" enctype="multipart/form-data" onsubmit="return submitUserForm();">
                        @csrf
                        <input type="hidden" name="name" value="{{ @$user->firstname . ' ' . @$user->lastname }}">
                        <input type="hidden" name="email" value="{{ @$user->email }}">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="text--accent sm-text d-block mb-2 fw-md" for="website">@lang('Subject')</label>
                                <input type="text" name="subject" value="{{ old('subject') }}" class="form-control form--control ">
                            </div>
                            <div class="col-md-4">
                                <label class="text--accent sm-text d-block mb-2 fw-md" for="priority">@lang('Priority')</label>
                                <div class="form--select-light">
                                    <select name="priority" class="select form--select ">
                                        <option value="3">@lang('High')</option>
                                        <option value="2">@lang('Medium')</option>
                                        <option value="1">@lang('Low')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="text--accent sm-text d-block mb-2 fw-md" for="inputMessage">@lang('Message')</label>
                                <textarea name="message" id="inputMessage" rows="6" class="form--control " required>{{ old('message') }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="text-end">
                                    <a href="javascript:void(0)" class="btn btn--base btn-sm addFile"><i class="fa fa-plus"></i> @lang('Add New')</a>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">@lang('Attachments')</label> <small class="text-danger">@lang('Max 5 files can be uploaded'). @lang('Maximum upload size is') {{ ini_get('upload_max_filesize') }}</small>
                                <input type="file" name="attachments[]" class="form-control form--control" />
                                <div id="fileUploadsContainer"></div>
                                <p class="my-2 ticket-attachments-message text-muted">
                                    @lang('Allowed File Extensions'): .@lang('jpg'), .@lang('jpeg'), .@lang('png'), .@lang('pdf'), .@lang('doc'), .@lang('docx')
                                </p>
                            </div>
                        </div>

                        <div class="col-12">
                            <button class="btn btn--base btn-md w-100" type="submit" id="recaptcha"><i class="fa fa-paper-plane"></i>&nbsp;@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


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
