<?php

use Illuminate\Support\Facades\Route;


Route::namespace('Agent\Auth')->controller('LoginController')->group(function () {
    Route::get('/', 'showLoginForm')->name('login');
    Route::post('/', 'login')->name('login');
    Route::get('logout', 'logout')->name('logout');

    // Agent Password Reset
    Route::controller('ForgotPasswordController')->group(function () {
        Route::get('password/reset', 'showLinkRequestForm')->name('password.reset');
        Route::post('password/reset', 'sendResetCodeEmail');
        Route::get('password/code-verify', 'codeVerify')->name('password.code.verify');
        Route::post('password/verify-code', 'verifyCode')->name('password.verify.code');
    });

    Route::controller('ResetPasswordController')->group(function () {
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset.form');
        Route::post('password/reset/change', 'reset')->name('password.change');
    });
});
Route::middleware('agent')->group(function () {
    Route::namespace('Agent')->group(function () {
        //authorization
        Route::controller('AuthorizationController')->group(function () {
            Route::get('authorization', 'authorizeForm')->name('authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
            Route::post('verify-email', 'emailVerification')->name('verify.email');
            Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
            Route::post('verify-g2fa', 'g2faVerification')->name('go2fa.verify');
        });
        // User Support Ticket
        Route::controller('AgentTicketController')->prefix('ticket')->group(function () {
            Route::get('/', 'supportTicket')->name('ticket');
            Route::get('/new', 'openSupportTicket')->name('ticket.open');
            Route::post('/create', 'storeSupportTicket')->name('ticket.store');
            Route::get('/view/{ticket}', 'viewTicket')->name('ticket.view');
            Route::post('/reply/{ticket}', 'replyTicket')->name('ticket.reply');
            Route::post('/close/{ticket}', 'closeTicket')->name('ticket.close');
            Route::get('/download/{ticket}', 'ticketDownload')->name('ticket.download');
        });
    });

    Route::middleware(['check.agent.status'])->namespace('Agent')->group(function () {

        Route::controller('AgentController')->group(function () {
            Route::get('dashboard/{day?}', 'dashboard')->name('dashboard');
            //2FA
            Route::get('twofactor', 'show2faForm')->name('twofactor');
            Route::post('twofactor/enable', 'create2fa')->name('twofactor.enable');
            Route::post('twofactor/disable', 'disable2fa')->name('twofactor.disable');

            //transactions
            Route::get('/transactions', 'transactions')->name('transaction.history');

            //KYC
            Route::get('kyc-form', 'kycForm')->name('kyc.form');
            Route::get('kyc-data', 'kycData')->name('kyc.data');
            Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');
            Route::get('attachment-download/{fil_hash}', 'attachmentDownload')->name('attachment.download');

            //Report
            Route::any('deposit/history', 'depositHistory')->middleware('agent.kyc:deposit')->name('deposit.history');

            Route::get('attachment-download/{fil_hash}', 'attachmentDownload')->name('attachment.download');
        });

        Route::middleware('agent.kyc:payout')->controller('PayoutController')->group(function () {
            // payout
            Route::get('/payout-money', 'payout')->name('payout');
            Route::get('/payout-info', 'payoutInfo')->name('payout.info');
            Route::post('/payout-confirm/{id}', 'payoutConfirm')->name('payout.confirm');
            Route::get('payout/send-verification-code', 'payoutVerificationCode')->name('payout.verification.code');
            Route::get('/payout-history', 'payoutHistory')->name('payout.history');
        });

        Route::middleware('agent.kyc:send_money')->controller('AgentSendMoneyController')->group(function () {
            // send money
            Route::get('send-money', 'sendMoney')->name('send.money');
            Route::post('send-money', 'sendMoneyInsert')->name('send.money.insert');
            Route::get('transfer/history', 'transferHistory')->name('transfer.history');
        });

        //Profile setting
        Route::controller('ProfileController')->group(function () {
            Route::get('profile-setting', 'profile')->name('profile.setting');
            Route::post('profile-setting', 'submitProfile');
            Route::get('change-password', 'changePassword')->name('change.password');
            Route::post('change-password', 'submitPassword');
        });

        // Withdraw
        Route::middleware('agent.kyc:withdrawals')->controller('WithdrawController')->prefix('withdraw')->name('withdraw')->group(function () {
            Route::get('/', 'withdrawMoney');
            Route::post('/', 'withdrawStore')->name('.money');
            Route::get('preview', 'withdrawPreview')->name('.preview');
            Route::post('preview', 'withdrawSubmit')->name('.submit');
            Route::get('history', 'withdrawLog')->name('.history');
        });
    });

    // Payment
    Route::middleware('agent.kyc:deposit')->controller('Gateway\PaymentController')->group(function () {
        Route::any('/deposit', 'deposit')->name('deposit');
        Route::post('payment/insert', 'depositInsert')->name('deposit.insert');
        Route::get('payment/confirm', 'depositConfirm')->name('deposit.confirm');
        Route::get('payment/manual', 'manualDepositConfirm')->name('deposit.manual.confirm');
        Route::post('payment/manual', 'manualDepositUpdate')->name('deposit.manual.update');
    });
});
