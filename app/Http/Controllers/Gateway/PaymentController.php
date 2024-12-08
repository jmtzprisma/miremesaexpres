<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\ProcessSendMoney;
use App\Models\AdminNotification;
use App\Models\Agent;
use App\Models\Deposit;
use App\Models\GatewayCurrency;
use App\Models\SendMoney;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller {
    public function deposit() {
        $pageTitle       = 'Deposit Methods';
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('method_code')->get();
        return view('agent.payment.deposit', compact('gatewayCurrency', 'pageTitle'));
    }

    public function depositInsert(Request $request) {
        $request->validate([
            'amount'      => 'required|numeric|gt:0',
            'gateway' => 'required',
            'currency'    => 'required',
        ]);

        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();

        if (!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }

        $combined       = session()->get('combined_id');
        foreach(SendMoney::where('combined_id', $combined)->with('deposit', 'sendingCountry:id,rate')->get() as $sendMoney)
        {
            $data = new Deposit();
            // $trx  = session()->get('payment_trx');
            //$trx  = $sndMny->trx;
            if (auth()->user() || auth('admin')->user()) {
                //$sendMoney  = $sndMny->with('deposit', 'sendingCountry:id,rate')->first();
                if (!$sendMoney) {
                    $notify[] = ['error', 'Session invalid'];
                    return to_route('user.send.money.history')->withNotify($notify);
                }
                $rate = $sendMoney->sendingCountry->rate;
                if ($sendMoney->deposit) {
                    $data = $sendMoney->deposit;
                }
                $userType     = 'user';

                if ($sendMoney->payment_status != Status::PAYMENT_INITIATE && $sendMoney->payment_status != Status::PAYMENT_REJECT) {
                    $notify[] = ['error', 'Payment for this send-money is already completed'];
                    return to_route('user.send.money.history')->withNotify($notify);
                }

                $data->user_id       = $request->has('send_adm') ? $request->user_id : auth()->user()->id;
                $amount              = $sendMoney->base_currency_amount + $sendMoney->base_currency_charge;
                $data->trx           = $sendMoney->trx;
                $data->send_money_id = $sendMoney->id;
            } else {
                $data->agent_id = authAgent()->id;
                $amount         = $request->amount;
                $data->trx      = getTrx();
                $rate           = $gate->rate;
                $userType       = 'agent';
            }

            if ($gate->min_amount > $amount || $gate->max_amount < $amount) {
                $notify[] = ['error', 'Please follow the limit'];
                return back()->withNotify($notify);
            }

            $charge                = $gate->fixed_charge + ($amount * $gate->percent_charge / 100);
            $payable               = $amount + $charge;
            $final_amo             = $payable * $rate;

            $data->method_code     = $gate->method_code;
            $data->method_currency = strtoupper($gate->currency);
            $data->amount          = $amount;
            $data->charge          = $charge;
            $data->rate            = $rate;
            $data->final_amo       = $final_amo;
            $data->btc_amo         = 0;
            $data->btc_wallet      = "";
            $data->payment_try     = 0;
            $data->status          = 0;
            $data->combined_id     = $combined;
            $data->save();
            // session()->forget('payment_trx');
            // session()->put('Track', $data->trx);
        }
        if($request->has('send_adm'))
            return $data->id;
        else
            return to_route($userType . '.deposit.confirm');

    }

    public function appDepositConfirm($hash) {

        try {
            $id = decrypt($hash);
        } catch (\Exception $ex) {
            return "Sorry, invalid URL.";
        }

        $data = Deposit::where('id', $id)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->firstOrFail();
        $user = User::findOrFail($data->user_id);

        auth()->login($user);
        session()->put('Track', $data->trx);

        if ($data->user_id) {
            return to_route('user.deposit.confirm');
        } else {
            return to_route('agent.deposit.confirm');
        }
    }

    public function depositConfirm() {
        
        $combined       = session()->get('combined_id');
        $troute         = null;
        foreach(SendMoney::where('combined_id', $combined)->get() as $sndMny)
        {
            //$track = session()->get('Track');
            $track = $sndMny->trx;
            $deposit = Deposit::where('trx', $track)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->with('gateway', 'user', 'agent')->firstOrFail();
            if ($deposit->method_code >= 1000) {
                if ($deposit->user_id) {
                    $troute = to_route('user.deposit.manual.confirm');
                    break;
                }
                $troute =  to_route('agent.deposit.manual.confirm');
                break;
            }

            $dirName = $deposit->gateway->alias;
            $new     = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

            $data    = $new::process($deposit);
            $data    = json_decode($data);


            if (isset($data->error)) {
                $notify[] = ['error', $data->message];
                $troute = to_route(gatewayRedirectUrl())->withNotify($notify);
                break;
            }
            if (isset($data->redirect)) {
                $troute = redirect($data->redirect_url);
                break;
            }

            // for Stripe V3
            if (@$data->session) {
                $deposit->btc_wallet = $data->session->id;
                $deposit->save();
            }
        }

        if(!is_null($troute))
        {
            return $troute;
        }
        $pageTitle = 'Confirmación de depósito';
        if ($deposit->user_id) {
            $pageTitle = 'Confirmación de pago';
            return view($this->activeTemplate . $data->view, compact('data', 'pageTitle', 'deposit'));
        }

        return view($data->view, compact('data', 'pageTitle', 'deposit'));
    }


    public static function userDataUpdate($deposit, $isManual = null) {
        if ($deposit->status == Status::PAYMENT_INITIATE ||  $deposit->status == Status::PAYMENT_PENDING) {
            $deposit->status = Status::PAYMENT_SUCCESS;
            $deposit->save();
            if ($deposit->user_id) {
                $sendMoney = $deposit->sendMoney;

                if (@$sendMoney->payment_status != Status::PAYMENT_SUCCESS) {
                    ProcessSendMoney::updateSendMoney($sendMoney, $sendMoney->user);
                }

                $transaction               = new Transaction();
                $transaction->user_id     = $deposit->user_id;
                $transaction->amount       = $deposit->amount;
                $transaction->post_balance = $deposit->user->balance + $deposit->amount;
                $transaction->charge       = $deposit->charge;
                $transaction->trx_type     = '+';
                $transaction->remark       = 'send_money_in';
                $transaction->details      = 'Money added for payment via' . $deposit->gatewayCurrency()->name;
                $transaction->trx          = $deposit->trx;
                $transaction->save();

                $transaction               = new Transaction();
                $transaction->user_id     = $deposit->user_id;
                $transaction->amount       = $deposit->amount;
                $transaction->post_balance = $deposit->user->balance;
                $transaction->charge       = 0;
                $transaction->trx_type     = '-';
                $transaction->remark       = 'send_money_out';
                $transaction->details      = 'Amount subtracted to pay';
                $transaction->trx          = $deposit->trx;
                $transaction->save();
            } else if ($deposit->agent_id) {
                $agent = Agent::find($deposit->agent_id);
                $agent->balance += $deposit->amount;
                $agent->save();

                $transaction               = new Transaction();
                $transaction->agent_id     = $deposit->agent_id;
                $transaction->amount       = $deposit->amount;
                $transaction->post_balance = $agent->balance;
                $transaction->charge       = $deposit->charge;
                $transaction->trx_type     = '+';
                $transaction->remark       = 'deposits';
                $transaction->details      = 'Deposited via ' . $deposit->gatewayCurrency()->name;
                $transaction->trx          = $deposit->trx;
                $transaction->save();

                if (!$isManual) {
                    $adminNotification            = new AdminNotification();
                    $adminNotification->agent_id  = $agent->id;
                    $adminNotification->title     = trans('Deposit succeeded via') .' ' . $deposit->gatewayCurrency()->name;
                    $adminNotification->click_url = urlPath('admin.deposit.successful');
                    $adminNotification->save();
                }

                if ($deposit->agent_id) {
                    notify($agent, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                        'method_name'     => $deposit->gatewayCurrency()->name,
                        'method_currency' => $deposit->method_currency,
                        'method_amount'   => showAmount($deposit->final_amo),
                        'amount'          => showAmount($deposit->amount),
                        'charge'          => showAmount($deposit->charge),
                        'rate'            => showAmount($deposit->rate),
                        'trx'             => $deposit->trx,
                        'post_balance'    => showAmount($agent->balance)
                    ]);
                }
            }
        }
    }

    public function manualDepositConfirm() {
        
        $combined       = session()->get('combined_id');
        $troute         = null;
        $sndMny = SendMoney::where('combined_id', $combined)->first();
        
        // $track = session()->get('Track');
        $track = $sndMny->trx;
        $data  = Deposit::initiated()->with('gateway')->where('trx', $track)->orderBy('id', 'desc')->first();

        if (!$data) {
            return to_route(gatewayRedirectUrl());
        }

        $amount = 0;
        $final_amo = 0;
        $method_currency = 0;
        foreach(Deposit::where('combined_id', $combined)->get() as $itm)
        {
            $amount += $itm['amount'];
            $final_amo += $itm['final_amo'];
            $method_currency = $itm['method_currency'];
        }

        if ($data->method_code > 999) {
            $method    = $data->gatewayCurrency();
            $gateway   = $method->method;
            $pageTitle = 'Confirmación de pago vía ' . $gateway->name;

            if ($data->user_id) {
                $data['amount'] = $amount;
                $data['final_amo'] = $final_amo;
                $data['method_currency'] = $method_currency;

                return view($this->activeTemplate . 'user.payment.manual', compact('data', 'pageTitle', 'method', 'gateway'));
            }

            $pageTitle = 'Deposit via ' . $gateway->name;
            return view('agent.payment.manual', compact('data', 'pageTitle', 'method', 'gateway'));
        }

        abort(404);
    }

    public function manualDepositUpdate(Request $request) {
        $combined       = session()->get('combined_id');
        $sndMny         = SendMoney::where('combined_id', $combined)->first();
        
        // $track = session()->get('Track');
        $track = $sndMny->trx;

        $data = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->orderBy('id', 'desc')->first();
        if (!$data) {
            return to_route(gatewayRedirectUrl());
        }
        $gatewayCurrency = $data->gatewayCurrency();
        $gateway         = $gatewayCurrency->method;
        $formData        = $gateway->form->form_data;
        
        $formProcessor   = new FormProcessor();
        $validationRule  = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData        = $formProcessor->processFormData($request, $formData);

        foreach(Deposit::where('combined_id', $combined)->get() as $itm)
        {
            $itm->detail    = $userData;
            $itm->status    = Status::PAYMENT_PENDING; // pending
            $itm->save();

            if ($data->user_id) {
                $sendMoney                  = @$itm->sendMoney;
                $sendMoney->payment_status  = Status::PAYMENT_PENDING;
                $sendMoney->save();
            }
        }

        $adminNotification = new AdminNotification();

        if ($data->user_id) {
            $user                       = $data->user;
            $adminNotification->user_id = $user->id;
            // $sendMoney                  = @$data->sendMoney;
            // $sendMoney->payment_status  = Status::PAYMENT_PENDING;
            // $sendMoney->save();
            $notify[] = ['success', 'Your payment request has been taken'];
        // } else {
        //     $user                        = $data->agent;
        //     $adminNotification->agent_id = $user->id;
        //     $notify[] = ['success', 'Your deposit request has been taken'];

        //     notify($user, 'DEPOSIT_REQUEST', [
        //         'method_name'     => $data->gatewayCurrency()->name,
        //         'method_currency' => $data->method_currency,
        //         'method_amount'   => showAmount($data->final_amo),
        //         'amount'          => showAmount($data->amount),
        //         'charge'          => showAmount($data->charge),
        //         'rate'            => showAmount($data->rate),
        //         'trx'             => $data->trx
        //     ]);
        }

        $adminNotification->title     = trans('Deposit request from') . ' ' . $user->username;
        $adminNotification->click_url = urlPath('admin.deposit.details', $combined);
        $adminNotification->save();
        return to_route(gatewayRedirectUrl())->withNotify($notify);
    }
}
