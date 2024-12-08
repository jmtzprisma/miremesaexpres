@php
    $contact = getContent('contact.content', true);
    $app = getContent('app.content', true);
    $pages = getContent('policy_pages.element', false, null, true);
@endphp

<div id="modalPrivacy" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span class="type"></span> <span>{{ __('Políticas de Privacidad') }}</span></h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    La función de Fintech Payments, S.L., a través de su plataforma "Cryptopocket", consistirá únicamente en asistir al usuario en la compraventa de los activos digitales (stablecoins). Tras su adquisición, los activos digitales serán remitidos a través de la plataforma Cryptopocket a la plataforma Andrés Te Lo Cambia, donde esta última entidad procederá según las instrucciones del usuario, y sin vinculación alguna a Cryptopocket o a Fintech Payments, S.L. <br><br>
                    Para proceder a esta operación, usted debe confirmar haber leído estos términos, y mostrar su conformidad y consentimiento para el envío y tratamiento de los activos digitales adquiridos en las condiciones anteriormente descritas.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" data-bs-dismiss="modal" class="btn btn--primary h-45 w-100">@lang('Aceptar')</button>
            </div>
        </div>
    </div>
</div>

<!-- Footer  -->
<footer class="footer bg--accent">
    <div class="section">
        <div class="container">
            <div class="row g-4 gy-sm-5 justify-content-xl-between">
                <div class="col-sm-6 col-lg-4 col-xxl-3">
                    <h5 class="text--white widget__title mt-0">@lang('About Company')</h5>
                    <p class="text--white t-short-para mb-0">
                        {{ __($app->data_values->short_description) }}
                    </p>

                </div>
                <div class="col-sm-6 col-lg-2 col-xl-2">
                    <h5 class="text--white widget__title mt-0">@lang('Accounts')</h5>
                    <ul class="list list--column list--base">
                        <li class="list--column__item">
                            <a href="{{ route('user.login') }}" class="t-link t-link--base text--white d-inline-block">
                                @lang('Login')
                            </a>
                        </li>
                        <li class="list--column__item">
                            <a href="{{ route('user.register') }}" class="t-link t-link--base text--white d-inline-block">
                                @lang('Register')
                            </a>
                        </li>
                        <li class="list--column__item">
                            <a href="{{ route('agent.login') }}" class="t-link t-link--base text--white d-inline-block">
                                @lang('Agent Login')
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-sm-6 col-lg-3 col-xl-2">
                    <h5 class="text--white widget__title mt-0">@lang('Policy Pages')</h5>
                    <ul class="list list--column list--base">
                        
                        
                        <li class="list--column__item">
                            <a data-bs-toggle="modal" data-bs-target="#modalPrivacy" href="#" class="t-link t-link--base text--white d-inline-block">
                                {{ __('Políticas de Privacidad') }}
                            </a>
                        </li>
                        @foreach ($pages as $page)
                            <li class="list--column__item">
                                <a href="{{ route('policy.pages', [slug($page->data_values->title), $page->id]) }}" class="t-link t-link--base text--white d-inline-block">
                                    {{ __($page->data_values->title) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-sm-6 col-lg-3 col-xl-4 col-xxl-3">
                    <h5 class="text--white widget__title mt-0">
                        @lang('Contact Us')
                    </h5>
                    <ul class="list list--column">
                        <li class="list--column__item">
                            <div class="contact-card">
                                <div class="contact-card__icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-card__content">
                                    <p class="text--white mb-0">
                                        {{ __($contact->data_values->address) }}
                                    </p>
                                </div>
                            </div>
                        </li>
                        <li class="list--column__item">
                            <div class="contact-card">
                                <div class="contact-card__icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-card__content">
                                    <p class="text--white mb-0">
                                        {{ __($contact->data_values->email) }}
                                    </p>
                                </div>
                            </div>
                        </li>
                        <li class="list--column__item">
                            <div class="contact-card">
                                <div class="contact-card__icon">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div class="contact-card__content">
                                    <p class="text--white mb-0">
                                        {{ __($contact->data_values->mobile) }}
                                    </p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-copyright bg--accent-dark py-3">
        <p class="sm-text text--white mb-0 text-center">@lang('Copyright') &copy; {{ __(date('Y')) }}. @lang('All Rights Reserved')</p>
    </div>
</footer>
<!-- Footer End -->
