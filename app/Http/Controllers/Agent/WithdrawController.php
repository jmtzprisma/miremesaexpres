<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\AdminNotification;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use Illuminate\Http\Request;

class WithdrawController extends Controller {

    public function withdrawMoney() {
        $withdrawMethod = WithdrawMethod::where('status', 1)->get();
        $pageTitle      = 'Withdraw Money';
        return view('agent.withdraw.methods', compact('pageTitle', 'withdrawMethod'));
    }

    public function withdrawStore(Request $request) {
        $this->validate($request, [
            'method_code' => 'required',
            'amount'      => 'required|numeric'
        ]);
        $method = WithdrawMethod::where('id', $request->method_code)->where('status', 1)->firstOrFail();
        $agent  = authAgent();

        if ($request->amount < $method->min_limit) {
            $notify[] = ['error', 'Your requested amount is smaller than minimum amount.'];
            return back()->withNotify($notify);
        }
        if ($request->amount > $method->max_limit) {
            $notify[] = ['error', 'Your requested amount is larger than maximum amount.'];
            return back()->withNotify($notify);
        }

        if ($request->amount > $agent->balance) {
            $notify[] = ['error', 'You do not have sufficient balance for withdraw.'];
            return back()->withNotify($notify);
        }


        $charge      = $method->fixed_charge + ($request->amount * $method->percent_charge / 100);
        $afterCharge = $request->amount - $charge;
        $finalAmount = $afterCharge * $method->rate;

        $withdraw               = new Withdrawal();
        $withdraw->method_id    = $method->id;        // wallet method ID
        $withdraw->agent_id     = $agent->id;
        $withdraw->amount       = $request->amount;
        $withdraw->currency     = $method->currency;
        $withdraw->rate         = $method->rate;
        $withdraw->charge       = $charge;
        $withdraw->final_amount = $finalAmount;
        $withdraw->after_charge = $afterCharge;
        $withdraw->trx          = getTrx();
        $withdraw->save();
        session()->put('wtrx', $withdraw->trx);
        return to_route('agent.withdraw.preview');
    }

    public function withdrawPreview() {
        $withdraw  = Withdrawal::with('method', 'agent')->where('trx', session()->get('wtrx'))->where('status', 0)->orderBy('id', 'desc')->firstOrFail();
        $pageTitle = 'Withdraw Via ' . $withdraw->method->name;
        return view('agent.withdraw.preview', compact('pageTitle', 'withdraw'));
    }

    public function withdrawSubmit(Request $request) {
        $withdraw = Withdrawal::with('method', 'agent')->where('trx', session()->get('wtrx'))->where('status', 0)->orderBy('id', 'desc')->firstOrFail();

        $method = $withdraw->method;
        if ($method->status == 0) {
            abort(404);
        }

        $formData = $method->form->form_data;

        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $agentData = $formProcessor->processFormData($request, $formData);

        $agent = authAgent();
        if ($agent->ts) {
            $response = verifyG2fa($agent, $request->authenticator_code);
            if (!$response) {
                $notify[] = ['error', 'Wrong verification code'];
                return back()->withNotify($notify);
            }
        }

        if ($withdraw->amount > $agent->balance) {
            $notify[] = ['error', 'Your request amount is larger then your current balance.'];
            return back()->withNotify($notify);
        }

        $withdraw->status               = 2;
        $withdraw->withdraw_information = $agentData;
        $withdraw->save();
        $agent->balance -= $withdraw->amount;
        $agent->save();

        $transaction               = new Transaction();
        $transaction->agent_id     = $withdraw->agent_id;
        $transaction->amount       = $withdraw->amount;
        $transaction->post_balance = $agent->balance;
        $transaction->charge       = $withdraw->charge;
        $transaction->trx_type     = '-';
        $transaction->details      = showAmount($withdraw->final_amount) . ' ' . $withdraw->currency . ' Withdraw Via ' . $withdraw->method->name;
        $transaction->trx          = $withdraw->trx;
        $transaction->remark       = 'withdraw';
        $transaction->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->agent_id  = $agent->id;
        $adminNotification->title     = trans('New withdraw request from') . ' ' . $agent->username;
        $adminNotification->click_url = urlPath('admin.withdraw.details', $withdraw->id);
        $adminNotification->save();

        notify($agent, 'WITHDRAW_REQUEST', [
            'method_name'     => $withdraw->method->name,
            'method_currency' => $withdraw->currency,
            'method_amount'   => showAmount($withdraw->final_amount),
            'amount'          => showAmount($withdraw->amount),
            'charge'          => showAmount($withdraw->charge),
            'rate'            => showAmount($withdraw->rate),
            'trx'             => $withdraw->trx,
            'post_balance'    => showAmount($agent->balance),
        ]);

        $notify[] = ['success', 'Withdraw request sent successfully'];
        return to_route('agent.withdraw.history')->withNotify($notify);
    }

    public function withdrawLog(Request $request) {
        $pageTitle      = 'Withdrawal History';
        $withdrawals = Withdrawal::where('status', '!=', 0)->filterAgent()->filter(['trx']);

        $successful = clone $withdrawals;
        $pending    = clone $withdrawals;
        $rejected   = clone $withdrawals;

        $withdrawals    = $withdrawals->with(['agent', 'method'])->orderBy('id', 'desc')->paginate(getPaginate());
        $successful     = $successful->where('status', 1)->sum('amount');
        $pending        = $pending->where('status', 2)->sum('amount');
        $rejected       = $rejected->where('status', 3)->sum('amount');
        return view('agent.withdraw.log', compact('pageTitle', 'withdrawals', 'successful', 'pending', 'rejected'));
    }
}
