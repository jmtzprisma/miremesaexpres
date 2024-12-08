@php
$transferWay = getContent('how_to_receive.content', true);
$transferElement = getContent('how_to_receive.element', false, null, true);
@endphp
<!-- Transfer Section  -->
<div class="section">
    <div class="section__head">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-xl-6">
                    <div class="text-md-center">
                        <h3 class="text-md-center mt-0 mb-4">
                            {{ __($transferWay->data_values->heading) }}
                        </h3>
                        <p class="text-md-center section__para mx-md-auto mb-0">
                            {{ __($transferWay->data_values->description) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row g-4 justify-content-center">
            @foreach ($transferElement as $element)
                <div class="col-md-4">
                    <div class="features-card flex-column align-items-center bg--light-2">
                        <div class="icon icon--circle icon--xl bg--base text--white flex-shrink-0">
                            @php
                                echo $element->data_values->icon;
                            @endphp
                        </div>
                        <div class="features-card__content">
                            <h5 class="mt-0 mb-2 text-center">
                                {{ __($element->data_values->title) }}
                            </h5>
                            <p class="m-0 text-center">
                                {{ __($element->data_values->description) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<!-- Transfer Section End -->
