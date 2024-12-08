@extends($activeTemplate . 'layouts.app')
@section('panel')
    <div class="section login-section flex-column justify-content-center">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-7 text-center">
                    <div class="row justify-content-center">
                        <div class="col-xl-10">
                            <h4 class="text--danger">{{ $title }}</h4>
                        </div>
                        <div class="col-sm-6 col-8">
                            <img src="{{ getImage($activeTemplateTrue . 'images/maintenance.png') }}" alt="@lang('image')" class="img-fluid mx-auto mb-5">
                        </div>
                    </div>
                    <p class="mx-auto text-center">@php echo $maintenance->data_values->description @endphp</p>
                </div>
            </div>
        </div>
    </div>
@endsection
