@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="section section--xl">
        <div class="container">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="card-title">
                        {{ __($pageTitle) }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('ticket.store') }}" method="post" enctype="multipart/form-data">
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
                                    <select name="priority" class="form-select form--select ">
                                        <option value="3">@lang('High')</option>
                                        <option value="2">@lang('Medium')</option>
                                        <option value="1">@lang('Low')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="text--accent sm-text d-block mb-2 fw-md" for="inputMessage">@lang('Message')</label>
                                <textarea name="message" id="inputMessage" rows="6" class="form-control form--control-textarea " required>{{ old('message') }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="support-upload-field mb-3 gy-3 row">
                                    <label class="text--accent sm-text d-block  fw-md">@lang('Attachments:') <small class="text-danger">@lang('Max 5 files can be uploaded'). @lang('Maximum upload size is') {{ ini_get('upload_max_filesize') }}</small></label>
                                    <div class="col-md-11">
                                        <input type="file" name="attachments[]" class="form-control form--control" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx" />
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn--base btn--xl w-100 addBtn"><i class="las la-plus"></i></button>
                                    </div>
                                </div>
                                <div id="file-upload-list"></div>
                                <div class="text--accent sm-text d-block mb-2 fw-md form-text text-muted">@lang('Allowed File Extensions'): .@lang('jpg'), .@lang('jpeg'), .@lang('png'), .@lang('pdf'), .@lang('doc'), .@lang('docx')</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <button class="btn btn--base btn--xl w-100" type="submit"><i class="fa fa-paper-plane"></i>&nbsp;@lang('Submit')</button>
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
            $('#file-upload-list').on('click', '.removeBtn', function() {
                $(this).parent('.input-group').remove();
            });

            $('.addBtn').on('click', function() {
                $('#file-upload-list').append(`
                    <div class="input-group mb-3">
                        <input type="file" name="attachments[]" class="form-control form--control" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx"  />
                        <button type="button" class="input-group-text btn btn--danger removeBtn"><i class="las la-times"></i></button>
                    </div>
                `);
            });
        })(jQuery);
    </script>
@endpush
