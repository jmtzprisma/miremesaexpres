@php
$faqContent = getContent('faq.content', true);
$faqs = getContent('faq.element', false, null, true);
@endphp
<!-- FAQ Section  -->
<div class="section faq-section">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6 order-lg-2">
                <div class="ms-xl-5 text-center">
                    <img src="{{ getImage('assets/images/frontend/faq/' . @$faqContent->data_values->image, '630x460') }}" alt="{{ __($general->site_name) }}" class="img-fluid" />
                </div>
            </div>
            <div class="col-lg-6 order-lg-1">
                <h3 class="mt-0 mb-4"> {{ __($faqContent->data_values->heading) }} </h3>
                <div class="accordion custom--accordion" id="accordionExample">
                    @foreach ($faqs as $faq)
                        <div class="accordion-item">
                            <span class="accordion-header" id="faq{{ $faq->id }}">
                                <button
                                    class="accordion-button"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse{{ $faq->id }}"
                                    aria-expanded="false"
                                    aria-controls="collapse{{ $faq->id }}">
                                    {{ __($faq->data_values->question) }}
                                </button>
                            </span>
                            <div
                                id="collapse{{ $faq->id }}"
                                class="accordion-collapse collapse  "
                                aria-labelledby="faq{{ $faq->id }}"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    @php
                                        echo $faq->data_values->answer;
                                    @endphp
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FAQ Section End -->
