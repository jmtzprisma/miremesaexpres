@php
$breadcrumb = getContent('breadcrumb.content', true);
@endphp
<!-- Banner  -->

<div class="banner" style="background-image: url('{{ getImage('assets/images/frontend/breadcrumb/' . @$breadcrumb->data_values->image, '1920x300') }}')">
    <div class="banner__content">
        <div class="container">
            <div class="row g-3 justify-content-center">
                <div class="col-lg-10 text-center">
                    <h3 class="text--white m-0">{{ __($pageTitle) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Banner End -->
