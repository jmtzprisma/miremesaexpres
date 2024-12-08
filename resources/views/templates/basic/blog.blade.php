@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="section">
        <div class="container">
            <div class="row g-4 g-lg-3 g-xxl-4 justify-content-center">
                @foreach ($blogs as $blog)
                    <div class="col-md-6 col-lg-4">
                        <div class="blog-post">
                            <div class="blog-post__img">
                                <img src="{{ getImage('assets/images/frontend/blog/thumb_' . @$blog->data_values->image, '415x280') }}" alt="{{ __($general->site_name) }}" class="blog-post__img-is">
                                <a href="{{ route('blog.details', [slug($blog->data_values->title), $blog->id]) }}" class="t-link blog-post__img-link">
                                    <span class="d-inline-block">
                                        <i class="las la-plus"></i>
                                    </span>
                                </a>
                            </div>

                            <div class="blog-post__body">
                                <ul class="list list--row">
                                    <li class="list--row__item">
                                        <div class="blog-post__meta">
                                            <span class="t-link t-link--base blog-post__meta-text">
                                                {{ $blog->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </li>
                                </ul>

                                <h5 class="mt-3">
                                    <a href="{{ route('blog.details', [slug($blog->data_values->title), $blog->id]) }}" class="t-link blog-post__link">
                                        {{ __($blog->data_values->title) }}
                                    </a>
                                </h5>
                                <p>
                                    @php
                                        echo strLimit(strip_tags($blog->data_values->description), 120);
                                    @endphp
                                </p>
                                <a href="{{ route('blog.details', [slug($blog->data_values->title), $blog->id]) }}" class="t-link blog-post__btn">
                                    @lang('Read More')
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($blogs->hasPages())
                <div class="d-flex justify-content-center mt-5">
                    {{ paginateLinks($blogs) }}
                </div>
            @endif
        </div>
    </div>
    @if ($sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif
@endsection
