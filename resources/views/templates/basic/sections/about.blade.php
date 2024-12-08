@php
$aboutContent = getContent('about.content', true);
$aboutElement = getContent('about.element', false, null, true);
@endphp
<!-- about  -->
<div class="section">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6 col-xl-7">
                <div class="me-xxl-4">
                    <img src="{{ getImage('assets/images/frontend/about/' . @$aboutContent->data_values->image, '720x460') }}" alt="{{ __($general->site_name) }}" class="img-fluid">
                </div>
            </div>

            <div class="col-lg-6 col-xl-5">
                <h3 class="mt-0">{{ __($aboutContent->data_values->heading) }}</h3>
                <p class="section__para">
                    {{ __($aboutContent->data_values->description) }}
                </p>
                <div class="row g-4">

                    <div class="col-md-6">
                        <ul class="list list--column list--base">
                            @foreach ($aboutElement as $item)
                                @if ($loop->even)
                                    <li class="list--column__item">
                                        {{ __($item->data_values->feature_item) }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list list--column list--base">
                            @foreach ($aboutElement as $item)
                                @if ($loop->odd)
                                    <li class="list--column__item">
                                        {{ __($item->data_values->feature_item) }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- about End -->
