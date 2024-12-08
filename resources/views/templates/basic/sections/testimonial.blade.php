@php
$testimonialContent = getContent('testimonial.content', true);
$testimonials = getContent('testimonial.element', false, null, true);
@endphp
<!-- Feedback Section  -->
<div class="section bg--light-2">
    <div class="section__head">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-xl-6">
                    <div class="text-md-center">
                        <h3 class="mt-0 mb-4 text-md-center">
                            {{ __($testimonialContent->data_values->heading) }}
                        </h3>
                        <p class="mb-0 text-md-center section__para mx-md-auto">
                            {{ __($testimonialContent->data_values->description) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid p-xl-0">
        <div class="row g-xl-0">
            <div class="col-12">
                <div class="feedback-slider">
                    @foreach ($testimonials as $testimonial)
                        <div class="feedback-slider__item">
                            <div class="feedback-card">
                                <div class="user">
                                    <div class="user__img user__img--xl">
                                        <img src="{{ getImage('assets/images/frontend/testimonial/' . @$testimonial->data_values->image, '128x128') }}" alt="{{ __($general->site_name) }}" class="user__img-is">
                                    </div>
                                    <div class="user__content">
                                        <h5 class="mt-0 mb-1">
                                            {{ __($testimonial->data_values->name) }}
                                        </h5>
                                        <p class="mb-2">
                                            {{ __($testimonial->data_values->designation) }}
                                        </p>
                                        <ul class="list list--row list--row-sm">
                                            @if (is_numeric($testimonial->data_values->star_count))
                                                @for ($i = 0; $i < $testimonial->data_values->star_count; $i++)
                                                    <li class="list__item">
                                                        <span class="rating rating--box">
                                                            <i class="fas fa-star"></i>
                                                        </span>
                                                    </li>
                                                @endfor
                                            @else
                                                <li class="list__item">
                                                    <span class="rating rating--box">
                                                        <i class="fas fa-star"></i>
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                <div class="feedback-card__body">
                                    <p class="mb-0 feedback-card__para">
                                        {{ __($testimonial->data_values->description) }}
                                    </p>
                                </div>
                                <div class="feedback-card__footer">
                                    <span class="t-link t-link--base text--base lg-text">
                                        {{ __($testimonial->data_values->date) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Feedback Section End -->
