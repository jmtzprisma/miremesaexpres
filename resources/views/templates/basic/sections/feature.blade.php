@php
$content = getContent('feature.content', true);
$features = getContent('feature.element', false, null, true);
@endphp
<!-- Feature Section  -->
<div class="section features-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h3 class="mt-0 mb-4 text-center">
                    {{ @$content->data_values->heading }}
                </h3>
            </div>
        </div>
        <div class="row g-4 g-md-3 justify-content-center">




            @foreach ($features as $feature)
                <div class="col-md-4">
                    <div class="features-card flex-column flex-xl-row justify-content-center text-xl-start align-items-xl-center text-center">
                        <div class="icon icon--circle icon--xl bg--base text--white mx-auto flex-shrink-0">
                            @php
                                echo $feature->data_values->icon;
                            @endphp
                        </div>
                        <div class="features-card__content">
                            <h5 class="mt-0 mb-2">
                                {{ __($feature->data_values->title) }}
                            </h5>
                            <p class="mb-0">
                                {{ __($feature->data_values->description) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
