@php
$appContent = getContent('app.content', true);
$appElement = getContent('app.element', false, null, true);
@endphp
<!-- App Section  -->
<div class="section--top">
    <div class="container">
        <div class="row gy-5 g-lg-4 align-items-center justify-content-center">
            <div class="col-lg-6 col-md-5 col-sm-10">
                <img src="{{ getImage('assets/images/frontend/app/' . @$appContent->data_values->image, '630x635') }}" alt="{{ __($general->site_name) }}" class="img-fluid">
            </div>
            <div class="col-lg-6 col-md-7">
                <div class="ms-xxl-5">
                    <h3 class="mt-0">
                        {{ __($appContent->data_values->heading) }}
                    </h3>
                    <p class="section__para">
                        {{ __($appContent->data_values->short_description) }}
                    </p>
                    <ul class="list list--column list--base">
                        @foreach ($appElement as $app)
                            <li class="list--column__item">
                                {{ __($app->data_values->key_feature_item) }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="hero__btn-group flex-lg-wrap gap-sm-4 mt-4 flex-nowrap gap-3">
                        <a target="_blank" href="{{ $appContent->data_values->play_store_url }}" class="t-link d-inline-block">
                            <img src="{{ getImage('assets/images/frontend/app/' . @$appContent->data_values->play_store_icon, '200x60') }}" alt="remitance" class="img-fluid">
                        </a>

                        <a target="_blank" href="{{ $appContent->data_values->app_store_url }}" class="t-link d-inline-block">
                            <img src="{{ getImage('assets/images/frontend/app/' . @$appContent->data_values->app_store_icon, '200x60') }}" alt="remitance" class="img-fluid">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- App Section End -->
