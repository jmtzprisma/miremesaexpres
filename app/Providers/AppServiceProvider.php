<?php

namespace App\Providers;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Agent;
use App\Models\Bank;
use App\Models\Deposit;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\SendMoney;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        $general = gs();
        $activeTemplate = activeTemplate();
        $viewShare['general'] = $general;
        $viewShare['activeTemplate'] = $activeTemplate;
        $viewShare['activeTemplateTrue'] = activeTemplate(true);
        $viewShare['language'] = Language::all();
        $viewShare['emptyMessage'] = trans('Data not found');
        view()->share($viewShare);


        view()->composer('admin.partials.sidenav', function ($view) {
            
            $sendMoneys = SendMoney::with('deposit')->where('status', Status::SEND_MONEY_PENDING);
            $_codigo_banco = [];
            $idSendMoney = [];
            if(auth('admin')->user()->role_id != 1)
            {
                $bancos = explode(',', \App\Models\Roles::find(auth('admin')->user()->role_id)->bancos);
                foreach(\App\Models\Bank::where('active', true)->whereIn('id', $bancos)->get() as $itm)
                {
                    if(!is_null($itm->codigo_banco))
                        array_push($_codigo_banco, $itm->codigo_banco);
                }
                $_sendMoneys = clone $sendMoneys;
                foreach ($_sendMoneys->get() as $sendMoney)
                {
                    foreach ($sendMoney->service_form_data as $val)
                        if($val->name == 'Numero de cuenta' || $val->name == 'Codigo del banco')
                        {
                            $numcuenta = substr($val->value,0,4);
                            if(in_array($numcuenta, $_codigo_banco))
                                array_push($idSendMoney, $sendMoney->id);
                        }

                    $details = $sendMoney->deposit && $sendMoney->deposit->detail ? json_encode($sendMoney->deposit->detail) : null;
                    if ($details != null){
                        foreach (json_decode($details) as $val){
                            if ($sendMoney->deposit->method_code >= 1000){
                                if($val->type == 'select'){
                                    if ($val->value){
                                        $banco_selected = Bank::where('name', $val->value)->first();
                                        if(!is_null($banco_selected) && in_array($banco_selected->id, $bancos))
                                            array_push($idSendMoney, $sendMoney->id);
                                    }
                                }
                            }
                        }
                    }
                }
                $sendMoneys->whereIn('id', $idSendMoney);
            }
            
            $view->with([
                'bannedUsersCount'            => User::banned()->count(),
                'rejectedUsersCount'          => User::rejected()->count(),
                'bannedAgentsCount'           => Agent::banned()->count(),
                'emailUnverifiedUsersCount'   => User::emailUnverified()->count(),
                'mobileUnverifiedUsersCount'  => User::mobileUnverified()->count(),
                'kycUnverifiedUsersCount'     => User::kycUnverified()->count(),
                'kycPendingUsersCount'        => User::kycPending()->count(),
                'kycUnverifiedAgentsCount'    => Agent::kycUnverified()->count(),
                'kycPendingAgentsCount'       => Agent::kycPending()->count(),
                'pendingTicketCount'          => SupportTicket::whereIN('status', [Status::TICKET_OPEN, Status::TICKET_REPLY])->count(),
                'pendingDepositsCount'        => Deposit::pending()->where('user_id', 0)->count(),
                'pendingPaymentsCount'        => Deposit::pending()->where('user_id', '!=', 0)->count(),
                'pendingWithdrawCount'        => Withdrawal::pending()->count(),
                'shouldPayoutCount'           => $sendMoneys->count()
            ]);
        });

        view()->composer('admin.partials.topnav', function ($view) {
            $view->with([
                'adminNotifications' => AdminNotification::where('is_read', Status::NO)->with(['user', 'agent'])->orderBy('id', 'desc')->take(10)->get(),
                'adminNotificationCount' => AdminNotification::where('is_read', Status::NO)->count(),
            ]);
        });

        view()->composer('partials.seo', function ($view) {
            $seo = Frontend::where('data_keys', 'seo.data')->first();
            $view->with([
                'seo' => $seo ? $seo->data_values : $seo,
            ]);
        });

        if ($general->force_ssl) {
            \URL::forceScheme('https');
        }


        Paginator::useBootstrapFour();
    }
}
