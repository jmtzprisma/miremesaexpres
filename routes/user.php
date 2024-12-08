<?php

use Illuminate\Support\Facades\Route;

Route::namespace('User\Auth')->name('user.')->group(function () {
    
    Route::controller('LoginController')->group(function () {
        Route::any('/redirect-response', 'redirectResponse')->name('redirect_response');
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
        Route::get('logout', 'logout')->name('logout');
    });

    Route::controller('RegisterController')->group(function () {
        Route::get('register', 'showRegistrationForm')->name('register');
        Route::post('register', 'register')->middleware('registration.status');
        Route::post('check-mail', 'checkUser')->name('checkUser');
    });

    Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
        Route::get('reset', 'showLinkRequestForm')->name('request');
        Route::post('email', 'sendResetCodeEmail')->name('email');
        Route::get('code-verify', 'codeVerify')->name('code.verify');
        Route::post('verify-code', 'verifyCode')->name('verify.code');
    });

    Route::controller('ResetPasswordController')->group(function () {
        Route::post('password/reset', 'reset')->name('password.update');
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
    });
});

Route::middleware('auth')->name('user.')->group(function () {

    // Authorization
    Route::namespace('User')->controller('AuthorizationController')->group(function () {
        Route::get('authorization', 'authorizeForm')->name('authorization');
        Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
        Route::post('verify-email', 'emailVerification')->name('verify.email');
        Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
        Route::post('verify-g2fa', 'g2faVerification')->name('go2fa.verify');
    });

    Route::middleware(['check.status'])->group(function () {

        Route::get('user-data', 'User\UserController@userData')->name('data');
        Route::post('user-data-submit', 'User\UserController@userDataSubmit')->name('data.submit');

        Route::middleware('registration.complete')->namespace('User')->group(function () {

            Route::controller('UserController')->group(function () {
                Route::get('dashboard', 'home')->name('home');

                //2FA
                Route::get('twofactor', 'show2faForm')->name('twofactor');
                Route::post('twofactor/enable', 'create2fa')->name('twofactor.enable');
                Route::post('twofactor/disable', 'disable2fa')->name('twofactor.disable');

                //KYC
                Route::get('ask-type-kyc', 'kycTypeKyc')->name('kyc.asking');
                Route::get('kyc-form', 'kycForm')->name('kyc.form');
                Route::get('kyc-data', 'kycData')->name('kyc.data');
                Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');

                //Report
                Route::get('transactions', 'transactions')->name('transactions');

                Route::get('attachment-download/{fil_hash}', 'attachmentDownload')->name('attachment.download');
            });

            // send money
            Route::middleware('user.kyc:send_money')->controller('SendMoneyController')->prefix('send-money')->name('send.money.')->group(function () {
                Route::get('send-now', 'sendMoney')->name('now');
                Route::post('save', 'save')->name('save');
                Route::post('add-receipt', 'addReceipt')->name('add_receipt');
                Route::get('view-card-benef', 'viewCardBenef')->name('view_card_benef');
                Route::get('history', 'history')->name('history');
                Route::post('save-video-id', 'saveVideoId')->name('video_id');
                Route::post('procesa-pago/{sendMoneyId}', 'procesaPago')->name('procesa_pago');
                Route::post('save-only-kyc', 'saveOnlyKyc')->name('save_onlykyc');
                Route::post('consulta-pago/{paymentId}', 'consultaPago')->name('consulta_pago');
                Route::get('video-valid/{only_kyc?}', 'videoValid')->name('video_valid');
                Route::get('continue-payment', 'continuePayment')->name('continue_payment');
                Route::get('success-pay/{proccesId}', 'successPay')->name('success_pay');
                Route::get('waiting-response', 'waitingResponse')->name('waiting_response');
                Route::get('destinatarios', 'destinatarios')->name('destinatarios');
                
                Route::middleware('user.kyc:direct_payment')->group(function () {
                    Route::get('pay-now', 'payNow')->name('pay.now');
                    Route::post('pay', 'pay')->name('pay');
                });
            });

            //Profile setting
            Route::controller('ProfileController')->group(function () {
                Route::get('profile-setting', 'profile')->name('profile.setting');
                Route::post('profile-setting', 'submitProfile');
                Route::get('change-password', 'changePassword')->name('change.password');
                Route::post('change-password', 'submitPassword');
            });
        });

        // Payment
        Route::middleware('registration.complete')->middleware('user.kyc:direct_payment')->controller('Gateway\PaymentController')->group(function () {
            Route::post('payment/insert', 'depositInsert')->name('deposit.insert');
            Route::get('payment/confirm', 'depositConfirm')->name('deposit.confirm');
            Route::get('payment/manual', 'manualDepositConfirm')->name('deposit.manual.confirm');
            Route::post('payment/manual', 'manualDepositUpdate')->name('deposit.manual.update');
        });
    });
});
