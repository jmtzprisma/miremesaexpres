@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card b-radius--5 overflow-hidden">
                <div class="card-body">
                    <form action="{{ route('admin.agents.store') }}" method="POST" class="verify-gcaptcha row">
                        @csrf
                        <div class="form-group col-md-6">
                            <label for="firstname">@lang('First Name')</label>
                            <input id="firstname" type="text" class="form-control" name="firstname" value="{{ old('firstname') }}" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="lastname">@lang('Last Name')</label>
                            <input id="lastname" type="text" class="form-control" name="lastname" value="{{ old('lastname') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="country">@lang('Country')</label>
                            <select name="country" id="country" class="form-control">
                                @foreach ($countries as $country)
                                    <option data-mobile_code="{{ $country->dial_code }}" value="{{ $country->id }}" @if('Spain' == $country->name) selected @endif>{{ __($country->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="mobile">@lang('Mobile')</label>
                            <div class="input-group">
                                <span class="input-group-text mobile-code">
                                </span>
                                <input type="number" name="mobile" id="mobile" value="{{ old('mobile') }}" class="form-control checkUser" placeholder="@lang('Your Phone Number')">
                            </div>
                            <small class="text-danger mobileExist"></small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="username"> @lang('Username') </label>
                            <input id="username" type="text" class="form-control checkUser" name="username" value="{{ old('username') }}" required>
                            <small class="text-danger usernameExist"></small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email">@lang('E-Mail Address')</label>
                            <input id="email" type="email" class="form-control checkUser" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="password">@lang('Password')</label>
                            <div class="input-group">
                                <input id="password" type="password" class="form-control" name="password" required>
                                <button class="input-group-text generatePasswordButton" type="button">@lang('Generate')</button>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="password-confirm">@lang('Confirm Password')</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                        </div>
                        <div class="form-group mb-0">
                            <button type="submit" id="recaptcha" class="btn btn--primary h-45 w-100">
                                @lang('Submit')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- generate password --}}
    <div id="generatePassword" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="">
                    <div class="modal-header">
                        <h5 class="modal-title"> @lang('Generate password')</h5>
                        <button type="button" class="close bg--danger text-white" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="copied-check">@lang('Password')</label>
                            <div class="input-group">
                                <input id="password-generation" type="text" class="form-control" name="generated_password" required>
                                <button class="input-group-text resetPasswordButton" type="button">
                                    <i class="las la-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-0">
                            <input type="checkbox" id="copied-check" name="copied-check" required>
                            <label for="copied-check">@lang('I have copied this password')</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--primary btn-block h-45 w-100 usePasswordButton">@lang('Use this password')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            let mobileElement = $('.mobile-code');
            $('select[name=country]').change(function() {
                mobileElement.text(`+${$('select[name=country] :selected').data('mobile_code')}`);
            });

            mobileElement.text(`+ ${$('select[name=country] :selected').data('mobile_code')}`);

            var generatePasswordModal = $('#generatePassword');

            $('.generatePasswordButton').on('click', function() {
                var form = generatePasswordModal.find('form');
                form[0].reset();
                form.find('[name=generated_password]').val(generatePassword());
                generatePasswordModal.modal('show');
            });
            $('.resetPasswordButton').on('click', function() {
                var form = generatePasswordModal.find('form');
                form.find('[name=generated_password]').val(generatePassword());
            });
            $('.usePasswordButton').on('click', function() {
                var form = generatePasswordModal.find('form');
                var generatedPassword = form.find('[name=generated_password]').val();
                var isCopied = form.find('[name=copied-check]').is(":checked");
                if (!generatedPassword) {
                    showError('@lang('Please re-generate password')');
                    return false;
                }
                if (!isCopied) {
                    showError('@lang('Please copy this password first')');
                    return false;
                }
                $('input[name=password]').val(generatedPassword);
                $('input[name=password_confirmation]').val(generatedPassword);
                generatePasswordModal.modal('hide');
            });

            function showError(text) {
                iziToast.error({
                    message: text,
                    position: "topRight"
                });
            }

            function generatePassword() {
                var length = 8,
                    charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+<>?,./",
                    password = "";
                for (var i = 0, n = charset.length; i < length; ++i) {
                    password += charset.charAt(Math.floor(Math.random() * n));
                }
                return password;
            }

        })(jQuery);
    </script>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.agents.all') }}" />
@endpush

@push('style')
    <style>
        .btn-sm {
            line-height: 5px;
        }
    </style>
@endpush
