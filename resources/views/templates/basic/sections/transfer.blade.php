@php
$transferContent = getContent('transfer.content', true);
$transferElement = getContent('transfer.element', false, null, true);
@endphp
<!-- Transfer  -->
<div class="section">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6 col-xl-5">
                <h3 class="mt-0">{{ __($transferContent->data_values->heading) }}</h3>
                <p class="section__para">
                    {{ __($transferContent->data_values->description) }}
                </p>
                <div class="row g-4">

                    <div class="col-md-6">
                        <ul class="list list--column list--base">
                            @foreach ($transferElement as $item)
                                @if ($loop->even)
                                    <li class="list--column__item">
                                        {{ __($item->data_values->key_features) }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list list--column list--base">
                            @foreach ($transferElement as $item)
                                @if ($loop->odd)
                                    <li class="list--column__item">
                                        {{ __($item->data_values->key_features) }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
                <a href="{{ $transferContent->data_values->button_link }}" class="btn btn--xl btn--base mt-4"> {{ __($transferContent->data_values->button_text) }} </a>
            </div>
            <div class="col-lg-6 col-xl-7">
                <div class="ms-xxl-4">
                    <div class="section__img section__img--right">
                        <img src="{{ getImage('assets/images/frontend/transfer/' . @$transferContent->data_values->image, '720x460') }}" alt="{{ __($general->site_name) }}" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Transfer End -->
