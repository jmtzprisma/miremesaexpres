@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="section">
        <div class="container-fluid container-restricted">
            <div class="row gy-5 g-lg-4">
                <div class="col-lg-8 col-xxl-9">
                    <div class="blog-post px-md-3">
                        <div class="blog-post__img blog-post__img-xl">
                            <img src="{{ getImage('assets/images/frontend/blog/' . @$blog->data_values->image, '1245x840') }}" alt="{{ __($general->site_name) }}" class="blog-post__img-is" />
                        </div>

                        <div class="blog-post__body">
                            <h4 class="mt-0">
                                {{ __($blog->data_values->title) }}
                            </h4>

                            <p class="contact-section__content-text mt-4">
                                @php echo $blog->data_values->description; @endphp
                            </p>

                            <div class="row g-4">
                                <div class="col-12">
                                    <ul class="list list--row-sm align-items-center">
                                        <li class="list--row__item">
                                            <span class="d-block xl-text fw-md heading-clr">
                                                @lang('Share')
                                            </span>
                                        </li>
                                        <li class="list--row__item">
                                            <a href="http://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                               class="t-link t-link--accent icon icon--xs icon--circle bg--base text--white">
                                                <i class="lab la-facebook-f"></i>
                                            </a>
                                        </li>
                                        <li class="list--row__item">
                                            <a href="https://twitter.com/intent/tweet?text=my share text&amp;url={{ urlencode(url()->current()) }}"
                                               class="t-link t-link--accent icon icon--xs icon--circle bg--base text--white">
                                                <i class="lab la-twitter"></i>
                                            </a>
                                        </li>
                                        <li class="list--row__item">
                                            <a href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{ urlencode(url()->current()) }}"
                                               class="t-link t-link--accent icon icon--xs icon--circle bg--base text--white">
                                                <i class="lab la-linkedin-in"></i>
                                            </a>
                                        </li>
                                        <li class="list--row__item">
                                            <a href="https://www.instagram.com/?url={{ urlencode(url()->current()) }}"
                                               class="t-link t-link--accent icon icon--xs icon--circle bg--base text--white">
                                                <i class="lab la-instagram"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                            <div class="fb-comments" data-href="{{ url()->current() }}" data-width="" data-numposts="5"></div>

                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-xxl-3">
                    <aside class="sidebar">
                        <ul class="list list--column">
                            <li class="list--column__item">
                                <div class="widget">
                                    <h5 class="widget__title widget-title mb-4">@lang('Recent Posts')</h5>
                                    <ul class="list list--column widget-category">
                                        @foreach ($recents as $blog)
                                            <li class="list--column__item widget-category__item">
                                                <div class="d-flex pb-3">
                                                    <div class="me-3 flex-shrink-0">
                                                        <div class="recent-img user__img user__img--md">
                                                            <img src="{{ getImage('assets/images/frontend/blog/thumb_' . @$blog->data_values->image, '415x280') }}" alt="{{ __($general->site_name) }}" class="user__img-is" />
                                                        </div>
                                                    </div>
                                                    <div class="article">
                                                        <h6 class="blog-post__title fw-md mt-0 mb-2">
                                                            <a href="{{ route('blog.details', [slug($blog->data_values->title), $blog->id]) }}"
                                                               class="t-link d-block text--accent t-link--base"> {{ __($blog->data_values->title) }}
                                                            </a>
                                                        </h6>
                                                        <ul class="list list--row align-items-center">
                                                            <li class="list--row__item">
                                                                <p class="sm-text t-heading-font text--accent mb-0">
                                                                    {{ $blog->created_at->diffForHumans() }}
                                                                </p>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </aside>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('fbComment')
    @php echo loadExtension('fb-comment') @endphp
@endpush
