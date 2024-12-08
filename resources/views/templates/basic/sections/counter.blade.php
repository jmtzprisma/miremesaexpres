@php
$counters = getContent('counter.element', false, null, true);
@endphp
<!-- Counter Section  -->
<div class="section--sm counter-section bg--light-2">
    <div class="container">
        <div class="row gy-4 gy-sm-5 g-xl-4 align-items-center justify-content-center">
            @foreach ($counters as $counter)
                <div class="col-6 col-xl-3">
                    <div class="counter-up">
                        <span class="counter-up__icon">
                            @php
                                echo $counter->data_values->icon;
                            @endphp
                        </span>
                        <div class="counter-up__content">
                            <div class="d-flex align-items-center justify-content-center">
                                <span class="counter-up__counter">
                                    <span class="odometer" data-odometer-final="{{ $counter->data_values->digit }}">0</span>
                                </span>
                                <h5 class="text--base my-0">+</h5>
                            </div>
                            <h6 class="counter-up__title mb-0 text-white">{{ __($counter->data_values->title) }}</h6>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<!-- Counter Section End -->
