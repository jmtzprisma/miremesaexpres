@php
$video = getContent('video.content', true);
preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $video->data_values->youtube_link, $url);
$youtubeVideoID = $url[0];
@endphp
<div class="video-section section">
    <div class="section__head">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="text-md-center">
                        <h3 class="text-md-center mt-0 mb-4">
                            {{ __($video->data_values->heading) }}
                        </h3>
                        <p class="text-md-center section__para mx-md-auto mb-0">
                            {{ __($video->data_values->description) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="video-content">
                    <img src="{{ getImage('assets/images/frontend/video/' . @$video->data_values->image, '1150x635') }}" alt="{{ __($general->site_name) }}" class="video-content__img" />
                    <a href="https://www.youtube.com/watch?v={{ $youtubeVideoID }}" class="btn btn--video btn--circle btn--base show-video">
                        <i class="fas fa-play"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
