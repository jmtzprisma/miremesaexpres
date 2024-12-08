<div class="input-group w-auto flex-fill">
    <input autocomplete="off" class="datepicker-here form-control bg--white pe-2" data-format="Y-m-d" data-language="en" data-multiple-dates-separator=" - " data-position='bottom right' data-range="true" name="date" placeholder="@lang('Start Date - End Date')" type="search" value="{{ request()->date }}">
    <button class="btn btn--primary input-group-text"><i class="la la-search"></i></button>
</div>

@push('script-lib')
    <script src="{{ asset('assets/global/js/datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/datepicker.en.js') }}"></script>
@endpush

@push('style-lib')
    <link href="{{ asset('assets/global/css/datepicker.min.css') }}" rel="stylesheet">
@endpush
