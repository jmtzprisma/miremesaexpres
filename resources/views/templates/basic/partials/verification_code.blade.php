<div class="mb-3">
    <label>@lang('Verification Code')</label>
    <div class="verification-code">
        <input type="text" name="code" id="verification-code" class="form-control overflow-hidden" required autocomplete="off">
        <div class="boxes">
            <span>-</span>
            <span>-</span>
            <span>-</span>
            <span>-</span>
            <span>-</span>
            <span>-</span>
        </div>
    </div>
</div>

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/global/css/verification-code.css') }}">
    <style>
        .verification-code-wrapper {
            border: 1px solid rgba(var(--r), var(--g), var(--b), 0.1);
            box-shadow: 0 5px 10px 0 rgb(var(--dark) / 0.02);
        }
    </style>
@endpush

@push('script')
    <script>
        $('#verification-code').on('input', function() {
            $(this).val(function(i, val) {
                if (val.length >= 6) {
                    $('.submit-form').find('button[type=submit]').html('<i class="las la-spinner fa-spin"></i>');
                    $('.submit-form').submit()
                }
                if (val.length > 6) {
                    return val.substring(0, val.length - 1);
                }
                return val;
            });
            for (let index = $(this).val().length; index >= 0; index--) {
                $($('.boxes span')[index]).html('');
            }
            console.log($(this).val());
        });
    </script>
@endpush
