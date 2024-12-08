<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Auth')->group(function () {
    Route::controller('LoginController')->group(function () {
        Route::get('/', 'showLoginForm')->name('login');
        Route::post('/', 'login')->name('login');
        Route::get('logout', 'logout')->name('logout');
    });

    // Admin Password Reset
    Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
        Route::get('reset', 'showLinkRequestForm')->name('reset');
        Route::post('reset', 'sendResetCodeEmail');
        Route::get('code-verify', 'codeVerify')->name('code.verify');
        Route::post('verify-code', 'verifyCode')->name('verify.code');
    });

    Route::controller('ResetPasswordController')->name('password.')->prefix('password')->group(function () {
        Route::get('reset/{token}', 'showResetForm')->name('reset.form');
        Route::post('reset/change', 'reset')->name('change');
    });
});

Route::middleware('admin')->group(function () {
    Route::controller('AdminController')->group(function () {
        Route::get('list_users_admin', 'listUsersAdmin')->name('adm');
        Route::get('add', 'add')->name('adm.add');
        Route::get('edit/{id}', 'edit')->name('adm.edit');
        Route::post('add', 'store')->name('adm.store');
        Route::post('update/{id}', 'update')->name('adm.update');
        //Route::delete('delete/{id}', 'store')->name('adm.store');

        
        Route::get('list_roles_admin', 'listRolesAdmin')->name('roles');
        Route::get('add-role', 'addRole')->name('roles.add');
        Route::get('edit-role/{id}', 'editRole')->name('roles.edit');
        Route::post('add-role', 'storeRole')->name('roles.store');
        Route::post('update-role/{id}', 'updateRole')->name('roles.update');


        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('profile', 'profile')->name('profile');
        Route::post('profile', 'profileUpdate')->name('profile.update');
        Route::get('password', 'password')->name('password');
        Route::post('password', 'passwordUpdate')->name('password.update');

        //Notification
        Route::get('notifications', 'notifications')->name('notifications');
        Route::get('notification/read/{id}', 'notificationRead')->name('notification.read');
        Route::get('notifications/read-all', 'readAll')->name('notifications.readAll');

        //Report Bugs
        Route::get('request-report', 'requestReport')->name('request.report');
        Route::post('request-report', 'reportSubmit');

        Route::get('send-money/statistics', 'sendMoneyStatistics')->name('send_money.statistics');

        Route::get('download-attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');
    });

    //country
    Route::controller('CountryController')->name('country.')->prefix('country')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('update-rate', 'updateRate')->name('update.rate');
        Route::post('save', 'store')->name('store');
        Route::post('save/{id}', 'update')->name('update');
        Route::post('status/{id}', 'updateStatus')->name('update.status');

        //currency conversation
        Route::get('currency/conversion-rate/{id}', 'currencyConversionRate')->name('currency.conversion.rate');
        Route::post('currency/conversion-rate/{id}', 'saveCurrencyConversionRates')->name('save.conversion');
        Route::post('currency/conversion-usdt', 'saveCurrencyConversionUsdt')->name('save.rates_conversion_usdt');
        Route::get('set-charges/{id}', 'setCharges')->name('charges.set');
        Route::post('charges/save/{id}', 'saveCharges')->name('charges.save');
    });

    Route::controller('ServiceController')->name('service.')->prefix('country/delivery-method')->group(function () {
        Route::get('services/{id?}', 'index')->name('index');
        Route::get('service/add', 'add')->name('add');
        Route::get('service/edit/{serviceId}', 'edit')->name('edit');
        Route::post('{id}/service/add/{serviceId?}', 'addService')->name('add.update');
        Route::post('service/update/status/{id}', 'updateServiceStatus')->name('status');
    });

    //Delivery Methods
    Route::controller('DeliveryMethodController')->name('delivery.method.')->prefix('delivery')->group(function () {
        Route::get('', 'all')->name('all');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'updateStatus')->name('status');
    });

    //SOF
    Route::controller('SourceOfFundController')->name('sof.')->prefix('source-of-fund')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('save/{id?}', 'saveSourceOfFund')->name('save');
    });

    //Sending Purpose
    Route::controller('SendingPurposeController')->name('sending.purpose.')->prefix('sending-purpose')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('save/{id?}', 'saveSendingPurpose')->name('save');
    });

    // Cuentas por Cobrar y Pagar
    Route::controller('CuentasCobrarPagarController')->name('cuentas.')->prefix('cuentas')->group(function () {
        Route::get('cobrar/{status?}', 'indexCobrar')->name('index.cobrar');
        Route::post('save-manual', 'saveManual')->name('cobrar.store_manual');
        Route::post('save-payment', 'savePayment')->name('save_payment');
        Route::post('cancelar_cuenta_cobrar', 'cancelCuentaCobrar')->name('cancel_cuenta_cobrar');
        Route::post('cancelar_cxc_pago', 'cancelCxcPago')->name('cancel_cxc_pago');
        Route::post('cancelar_cuenta_pagar', 'cancelCuentaPagar')->name('cancel_cuenta_pagar');
        Route::post('cancelar_cxp_pago', 'cancelCxpPago')->name('cancel_cxp_pago');
        Route::post('ccpagos', 'indexCuentasCobrarPagos')->name('cc.pagos');
        
        Route::get('pagar/{status?}', 'indexPagar')->name('index.pagar');
        Route::post('save-cuenta-pagar', 'saveCuentaPagar')->name('pagar.store');
        Route::post('save-cp-payment', 'saveCuentaPagarPayment')->name('save_cp_payment');
        Route::post('cppagos', 'indexCuentasPagarPagos')->name('cp.pagos');
        
    });

    // Send Money
    Route::controller('SendMoneyController')->name('send.money.')->prefix('send-money')->group(function () {
        Route::get('visible/{sendMoneyId}/{ind}', 'setVisibleSendMoney')->name('update_visible');

        Route::get('all', 'index')->name('all');
        Route::get('pending', 'index')->name('pending');
        Route::get('completed', 'index')->name('completed');
        Route::get('refunded', 'index')->name('refunded');
        Route::get('details/{id}', 'details')->name('details');
        Route::post('refund/{id}', 'refundMoney')->name('refund.now');
        Route::post('pay-receiver/{id}', 'payTheReceiver')->name('pay.receiver');

        Route::post('add-receipt', 'addReceipt')->name('add_receipt');
        Route::get('view-card-benef/{user_id}', 'viewCardBenef')->name('view_card_benef');

        Route::get('consulta-viser', 'consultaViser')->name('consulta_viser');

        Route::get('bancos', 'list_bancos')->name('list_bancos');
        Route::get('send', 'send_money_form')->name('send_money_form');
        Route::get('edit-banco/{id}', 'edit_banco')->name('edit_banco');
        Route::get('add-banco', 'create_banco')->name('add_banco');
        Route::post('store-banco', 'store_banco')->name('store_banco');
        Route::post('update-banco/{id}', 'update_banco')->name('update_banco');

        Route::get('consulta-usuarios', 'consultaUsuarios')->name('consulta_usuarios');
        Route::get('consulta-destinatarios', 'consultaDestinatarios')->name('consulta_destinatarios');
        Route::post('consulta-destinatarios-form', 'consultaDestinatariosForm')->name('consulta_destinatarios_form');
        
        Route::post('save', 'save')->name('save');
        
        Route::post('edit-recipient', 'editRecipient')->name('edit_recipient');

        Route::get('create-pdf/{sendMoneyId}', 'createPdf')->name('create_pdf');
        
    });

    // Bank
    Route::controller('BankController')->name('bank.')->prefix('bank')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::get('add', 'create')->name('add');
        Route::post('store', 'store')->name('store');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('convert-currency', 'convertCurrency')->name('convert_currency');
        Route::post('deposit', 'deposit')->name('deposit');
        Route::post('retiro', 'retiro')->name('retiro');

        Route::get('active/{id}', 'active')->name('active');
        Route::get('inactive/{id}', 'inactive')->name('inactive');
        
        Route::get('cierre-diario', 'cierreDiario')->name('cierre_diario');
    });

    // Users Manager
    Route::controller('ManageUsersController')->name('users.')->prefix('users')->group(function () {
        Route::get('/', 'allUsers')->name('all');
        Route::get('active', 'activeUsers')->name('active');
        Route::get('rejected', 'rejectedUsers')->name('rejected');
        Route::get('banned', 'bannedUsers')->name('banned');
        Route::get('email-verified', 'emailVerifiedUsers')->name('email.verified');
        Route::get('email-unverified', 'emailUnverifiedUsers')->name('email.unverified');
        Route::get('mobile-unverified', 'mobileUnverifiedUsers')->name('mobile.unverified');
        Route::get('kyc-unverified', 'kycUnverifiedUsers')->name('kyc.unverified');
        Route::get('kyc-pending', 'kycPendingUsers')->name('kyc.pending');
        Route::get('mobile-verified', 'mobileVerifiedUsers')->name('mobile.verified');
        Route::get('with-balance', 'usersWithBalance')->name('with.balance');

        Route::post('register', 'registerUser')->name('register');

        Route::get('detail/{id}', 'detail')->name('detail');
        Route::get('kyc-data/{id}', 'kycDetails')->name('kyc.details');
        Route::get('new-verify/{id}', 'kycNewVerify')->name('kyc.new_verify');
        Route::post('kyc-approve/{id}', 'kycApprove')->name('kyc.approve');
        Route::post('kyc-reject/{id}', 'kycReject')->name('kyc.reject');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('add-sub-balance/{id}', 'addSubBalance')->name('add.sub.balance');
        Route::get('send-notification/{id}', 'showNotificationSingleForm')->name('notification.single');
        Route::post('send-notification/{id}', 'sendNotificationSingle')->name('notification.single');
        Route::get('login/{id}', 'login')->name('login');
        Route::post('status/{id}', 'status')->name('status');

        Route::get('send-notification', 'showNotificationAllForm')->name('notification.all');
        Route::post('send-notification', 'sendNotificationAll')->name('notification.all.send');
        Route::get('notification-log/{id}', 'notificationLog')->name('notification.log');
    });

    // Agent Manager
    Route::controller('ManageAgentController')->name('agents.')->prefix('agents')->group(function () {
        Route::get('add', 'add')->name('add');
        Route::post('add', 'store')->name('store');

        Route::get('/', 'allAgents')->name('all');
        Route::get('active', 'activeAgents')->name('active');
        Route::get('banned', 'bannedAgents')->name('banned');
        Route::get('kyc-unverified', 'kycUnverifiedAgents')->name('kyc.unverified');
        Route::get('kyc-pending', 'kycPendingAgents')->name('kyc.pending');
        Route::get('with-balance', 'agentsWithBalance')->name('with.balance');

        Route::get('detail/{id}', 'detail')->name('detail');
        Route::get('kyc-data/{id}', 'kycDetails')->name('kyc.details');
        Route::post('kyc-approve/{id}', 'kycApprove')->name('kyc.approve');
        Route::post('kyc-reject/{id}', 'kycReject')->name('kyc.reject');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('add-sub-balance/{id}', 'addSubBalance')->name('add.sub.balance');
        Route::get('send-notification/{id}', 'showNotificationSingleForm')->name('notification.single');
        Route::post('send-notification/{id}', 'sendNotificationSingle')->name('notification.single');
        Route::get('login/{id}', 'login')->name('login');
        Route::post('status/{id}', 'status')->name('status');

        Route::get('send-notification', 'showNotificationAllForm')->name('notification.all');
        Route::post('send-notification', 'sendNotificationAll')->name('notification.all.send');
        Route::get('notification-log/{id}', 'notificationLog')->name('notification.log');
    });


    // Deposit Gateways
    Route::name('gateway.')->prefix('gateway')->group(function () {
        // Automatic Gateway
        Route::controller('AutomaticGatewayController')->prefix('automatic')->name('automatic.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('edit/{alias}', 'edit')->name('edit');
            Route::post('update/{code}', 'update')->name('update');
            Route::post('remove/{id}', 'remove')->name('remove');
            Route::post('status/{id}', 'status')->name('status');
        });

        // Manual Methods
        Route::controller('ManualGatewayController')->prefix('manual')->name('manual.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('new', 'create')->name('create');
            Route::post('new', 'store')->name('store');
            Route::get('edit/{alias}', 'edit')->name('edit');
            Route::post('update/{id}', 'update')->name('update');
            Route::post('status/{id}', 'status')->name('status');
        });
    });


    // DEPOSIT SYSTEM
    Route::controller('DepositController')->prefix('deposit')->name('deposit.')->group(function () {
        Route::get('/', 'deposit')->name('list');
        Route::get('pending', 'pending')->name('pending');
        Route::post('cancel-deposit', 'cancelDeposit')->name('cancel_deposit');
        Route::get('rejected', 'rejected')->name('rejected');
        Route::get('approved', 'approved')->name('approved');
        Route::get('successful', 'successful')->name('successful');
        Route::get('initiated', 'initiated')->name('initiated');
        Route::get('details/{id}', 'details')->name('details');
        Route::get('edit-amount', 'editAmount')->name('edit_amount');
        Route::post('edit-amount/{combined_id}', 'saveAmount')->name('save_amount');

        Route::post('reject', 'reject')->name('reject');
        Route::post('approve/{id}', 'approve')->name('approve');
    });

    // payment SYSTEM
    Route::controller('DepositController')->prefix('payment')->name('payment.')->group(function () {
        Route::get('/', 'deposit')->name('list');
        Route::get('pending', 'pending')->name('pending');
        Route::get('rejected', 'rejected')->name('rejected');
        Route::get('approved', 'approved')->name('approved');
        Route::get('successful', 'successful')->name('successful');
        Route::get('initiated', 'initiated')->name('initiated');
        Route::get('details/{id}', 'details')->name('details');
    });

    // WITHDRAW SYSTEM
    Route::name('withdraw.')->prefix('withdraw')->group(function () {
        Route::controller('WithdrawalController')->group(function () {
            Route::get('pending', 'pending')->name('pending');
            Route::get('approved', 'approved')->name('approved');
            Route::get('rejected', 'rejected')->name('rejected');
            Route::get('log', 'log')->name('log');
            Route::get('details/{id}', 'details')->name('details');
            Route::post('approve', 'approve')->name('approve');
            Route::post('reject', 'reject')->name('reject');
        });

        // Withdraw Method
        Route::controller('WithdrawMethodController')->prefix('method')->name('method.')->group(function () {
            Route::get('/', 'methods')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('create', 'store')->name('store');
            Route::get('edit/{id}', 'edit')->name('edit');
            Route::post('edit/{id}', 'update')->name('update');
            Route::post('status/{id}', 'status')->name('status');
        });
    });

    // Report
    Route::controller('ReportController')->prefix('report')->name('report.')->group(function () {
        Route::get('control-gastos', 'controlGastos')->name('control_gastos');
        Route::get('control-gastos-excel', 'controlGastosExcel')->name('control_gastos_excel');
        Route::get('datos-bancos', 'datosBancos')->name('datos_bancos');
        Route::get('datos-bancos-excel', 'datosBancosExcel')->name('datos_bancos_excel');
        
        Route::get('balance-general-movimientos', 'balanceGeneralMovimientos')->name('balance_gral_movimientos');
        Route::get('balance-general-movimientos-excel', 'balanceGeneralMovimientosExcel')->name('balance_gral_movimientos_excel');

        Route::get('detalle-movimientos-banco-excel', 'detalleMovimientosBancoExcel')->name('detalle_movs_banco');
        
        Route::get('balance-general', 'balanceGeneral')->name('balance_gral');
        
        Route::get('transaction', 'transaction')->name('transaction');
        Route::get('login/history', 'loginHistory')->name('login.history');
        Route::get('login/ipHistory/{ip}', 'loginIpHistory')->name('login.ipHistory');
        Route::get('notification/history', 'notificationHistory')->name('notification.history');
        Route::get('email/detail/{id}', 'emailDetails')->name('email.details');
    });

    // Admin Support need check
    Route::controller('SupportTicketController')->prefix('ticket')->name('ticket.')->group(function () {
        Route::get('/', 'tickets')->name('index');
        Route::get('tickets/agent', 'agentTicket')->name('agent');
        Route::get('tickets/user', 'userTicket')->name('user');
        Route::get('tickets/guest', 'guestTicket')->name('guest');
        Route::get('pending', 'pendingTicket')->name('pending');
        Route::get('closed', 'closedTicket')->name('closed');
        Route::get('answered', 'answeredTicket')->name('answered');
        Route::get('view/{id}', 'ticketReply')->name('view');
        Route::post('reply/{id}', 'replyTicket')->name('reply');
        Route::post('close/{id}', 'closeTicket')->name('close');
        Route::get('download/{ticket}', 'ticketDownload')->name('download');
        Route::post('delete/{id}', 'ticketDelete')->name('delete');
    });


    // Language Manager
    Route::controller('LanguageController')->prefix('language')->name('language.')->group(function () {
        Route::get('/', 'langManage')->name('manage');
        Route::post('/', 'langStore')->name('manage.store');
        Route::post('delete/{id}', 'langDelete')->name('manage.delete');
        Route::post('update/{id}', 'langUpdate')->name('manage.update');
        Route::get('edit/{id}', 'langEdit')->name('key');
        Route::post('import', 'langImport')->name('import.lang');
        Route::post('store/key/{id}', 'storeLanguageJson')->name('store.key');
        Route::post('delete/key/{id}', 'deleteLanguageJson')->name('delete.key');
        Route::post('update/key/{id}', 'updateLanguageJson')->name('update.key');
    });

    Route::controller('GeneralSettingController')->group(function () {
        // General Setting
        Route::get('general-setting', 'index')->name('setting.index');
        Route::post('general-setting', 'update')->name('setting.update');

        //configuration
        Route::get('setting/system-configuration', 'systemConfiguration')->name('setting.system.configuration');
        Route::post('setting/system-configuration', 'systemConfigurationSubmit');

        // Logo-Icon
        Route::get('setting/logo-icon', 'logoIcon')->name('setting.logo.icon');
        Route::post('setting/logo-icon', 'logoIconUpdate')->name('setting.logo.icon');

        //Custom CSS
        Route::get('custom-css', 'customCss')->name('setting.custom.css');
        Route::post('custom-css', 'customCssSubmit');

        //Cookie
        Route::get('cookie', 'cookie')->name('setting.cookie');
        Route::post('cookie', 'cookieSubmit');

        //maintenance_mode
        Route::get('maintenance-mode', 'maintenanceMode')->name('maintenance.mode');
        Route::post('maintenance-mode', 'maintenanceModeSubmit');
    });


    //KYC setting
    Route::controller('KycController')->group(function () {
        Route::get('kyc/setting/user', 'setting')->name('kyc.setting.user');
        Route::post('kyc/setting/user', 'settingUpdate');

        Route::get('kyc/setting/agent', 'setting')->name('kyc.setting.agent');
        Route::post('kyc/setting/agent', 'settingUpdate');

        Route::get('kyc/setting/module', 'module')->name('kyc.setting.module');
        Route::post('kyc/setting/module', 'moduleUpdate');
    });

    //Notification Setting
    Route::controller('NotificationController')->name('setting.notification.')->prefix('notification')->group(function () {
        //Template Setting
        Route::get('global', 'global')->name('global');
        Route::post('global/update', 'globalUpdate')->name('global.update');
        Route::get('templates', 'templates')->name('templates');
        Route::get('template/edit/{id}', 'templateEdit')->name('template.edit');
        Route::post('template/update/{id}', 'templateUpdate')->name('template.update');

        //Email Setting
        Route::get('email/setting', 'emailSetting')->name('email');
        Route::post('email/setting', 'emailSettingUpdate');
        Route::post('email/test', 'emailTest')->name('email.test');

        //SMS Setting
        Route::get('sms/setting', 'smsSetting')->name('sms');
        Route::post('sms/setting', 'smsSettingUpdate');
        Route::post('sms/test', 'smsTest')->name('sms.test');
    });

    // Plugin
    Route::controller('ExtensionController')->prefix('extensions')->name('extensions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('status/{id}', 'status')->name('status');
    });


    //System Information
    Route::controller('SystemController')->name('system.')->prefix('system')->group(function () {
        Route::get('info', 'systemInfo')->name('info');
        Route::get('server-info', 'systemServerInfo')->name('server.info');
        Route::get('optimize', 'optimize')->name('optimize');
        Route::get('optimize-clear', 'optimizeClear')->name('optimize.clear');
    });


    // SEO
    Route::get('seo', 'FrontendController@seoEdit')->name('seo');


    // Frontend
    Route::name('frontend.')->prefix('frontend')->group(function () {

        Route::controller('FrontendController')->group(function () {
            Route::get('templates', 'templates')->name('templates');
            Route::post('templates', 'templatesActive')->name('templates.active');
            Route::get('frontend-sections/{key}', 'frontendSections')->name('sections');
            Route::post('frontend-content/{key}', 'frontendContent')->name('sections.content');
            Route::get('frontend-element/{key}/{id?}', 'frontendElement')->name('sections.element');
            Route::post('remove/{id}', 'remove')->name('remove');
        });

        // Page Builder
        Route::controller('PageBuilderController')->group(function () {
            Route::get('manage-pages', 'managePages')->name('manage.pages');
            Route::post('manage-pages', 'managePagesSave')->name('manage.pages.save');
            Route::post('manage-pages/update', 'managePagesUpdate')->name('manage.pages.update');
            Route::post('manage-pages/delete/{id}', 'managePagesDelete')->name('manage.pages.delete');
            Route::get('manage-section/{id}', 'manageSection')->name('manage.section');
            Route::post('manage-section/{id}', 'manageSectionUpdate')->name('manage.section.update');
        });
    });
});
