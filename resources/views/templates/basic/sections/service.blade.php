@php
$service = getContent('service.content', true);
@endphp
<!-- Service Area Section  -->
<div class="section">
    <div class="container">
        <div class="row gy-5 g-lg-4">
            <div class="col-lg-6">
                <div class="me-xxl-5">
                    <h3 class="mt-0">{{ __($service->data_values->heading) }}</h3>
                    <p class="mb-0">
                        @php
                            echo $service->data_values->description_one;
                        @endphp
                    </p>
                    <br>
                    <p class="mb-0">
                        @php
                            echo $service->data_values->description_two;
                        @endphp
                    </p>
                    <a href="{{ $service->data_values->button_link }}" class="btn btn--xl btn--base mt-3"> {{ __($service->data_values->button_text) }} </a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="{{ getImage('assets/images/frontend/service/' . @$service->data_values->image, '630x630') }}" alt="{{ __($general->site_name) }}" class="img-fluid" />
            </div>
        </div>
    </div>
</div>
<!-- Service Area Section End -->
