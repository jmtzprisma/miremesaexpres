@php
$brands = getContent('brand.element', false, null, true);
@endphp

<!-- Client Slider  -->
<div class="section--sm section--bottom">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="client-slider">
                    @foreach ($brands as $brand)
                        <div class="client-slider__item">
                            <div class="client-card">
                                <img src={{ getImage('assets/images/frontend/brand/' . @$brand->data_values->image, '130x50') }} alt="{{ __($general->site_name) }}" class="client-card__img">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Client Slider End -->
