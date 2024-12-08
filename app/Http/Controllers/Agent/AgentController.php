<?php

namespace App\Http\Controllers\Agent;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\GoogleAuthenticator;
use App\Models\Deposit;
use App\Models\SendMoney;
use App\Models\Transaction;
use App\Models\Form;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class AgentController extends Controller
{

    public function dashboard($duration = 'today')
    {

        $info = getInsightDuration($duration);
        $day = $info['day'];
        $insights['duration'] = $info['duration'];

        $pageTitle = 'Dashboard';
        $agent     = authAgent();

        $widget['balance']           = $agent->balance;
        $widget['pending_deposit']   = Deposit::pending()->filterAgent()->count();
        $widget['pending_withdraw']  = Withdrawal::pending()->filterAgent()->count();
        $widget['total_transaction'] = Transaction::filterAgent()->count();

        //transaction report start
        $trxReport['date']  = collect([]);
        $plusTrx = Transaction::filterAgent()->filterByDay(365)
            ->where('trx_type', '+')
            ->selectRaw("SUM(amount ) as amount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as date")
            ->orderBy('created_at')
            ->groupBy('date')
            ->get();
        $plusTrx->map(function ($trxData) use ($trxReport) {
            $trxReport['date']->push($trxData->date);
        });

        $minusTrx = Transaction::filterAgent()->filterByDay(365)
            ->where('trx_type', '-')
            ->selectRaw("SUM(amount ) as amount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as date")
            ->orderBy('created_at')
            ->groupBy('date')
            ->get();

        $minusTrx->map(function ($trxData) use ($trxReport) {
            $trxReport['date']->push($trxData->date);
        });

        $trxReport['date'] = dateSorting($trxReport['date']->unique()->toArray());
        //transaction report end

        $insights['payouts']      = SendMoney::where('payout_by', authAgent()->id)->filterByDay($day)->sum('base_currency_amount');
        $insights['sent_amounts'] = SendMoney::filterAgent()->whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_PENDING])->filterByDay($day)->sum('base_currency_amount');
        $insights['deposits']     = Deposit::filterAgent()->where('status', Status::PAYMENT_SUCCESS)->filterByDay($day)->sum('amount');
        $insights['withdraws']    = Withdrawal::filterAgent()->approved()->filterByDay($day)->sum('amount');

        $transactions = Transaction::filterAgent()->orderBy('id', 'desc')->take(10)->get();
        return view('agent.dashboard', compact('pageTitle', 'widget', 'insights', 'transactions', 'trxReport', 'plusTrx', 'minusTrx'));
    }

    public function show2faForm()
    {
        $general   = gs();
        $ga        = new GoogleAuthenticator();
        $agent     = authAgent();
        $secret    = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($agent->username . '@' . $general->site_name, $secret);
        $pageTitle = '2FA Setting';
        return view('agent.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request)
    {
        $agent = authAgent();
        $this->validate($request, [
            'key'  => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($agent, $request->code, $request->key);
        if ($response) {
            $agent->tsc = $request->key;
            $agent->ts  = 1;
            $agent->save();
            $notify[] = ['success', 'Google authenticator activated successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Wrong verification code'];
            return back()->withNotify($notify);
        }
    }

    public function disable2fa(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $agent    = authAgent();
        $response = verifyG2fa($agent, $request->code);
        if ($response) {
            $agent->tsc = null;
            $agent->ts  = 0;
            $agent->save();
            $notify[] = ['success', 'Two factor authenticator deactivated successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }
    public function transactions(Request $request)
    {
        $day = transactionDuration();
        $pageTitle    = 'Transactions';
        $remarks      = Transaction::filterAgent()->distinct('remark')->orderBy('remark')->get('remark');
        $transactions = Transaction::filterAgent()->searchable(['trx'])->filter(['trx_type', 'remark'])->dateFilter();
        if ($day) {
            $transactions = $transactions->filterByDay($day);
        }
        $transactions = $transactions->with('agent')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('agent.transactions', compact('pageTitle', 'transactions', 'remarks'));
    }
    public function depositHistory(Request $request)
    {
        $pageTitle = 'Deposit History';
        $deposits  = Deposit::where('status', '!=', 0)->filterAgent()->filter(['trx'])->dateFilter();
        $successful = clone $deposits;
        $pending    = clone $deposits;
        $rejected   = clone $deposits;

        $deposits   = $deposits->orderBy('id', 'desc')->with(['gateway'])->paginate(getPaginate());
        $successful = $successful->where('status', 1)->sum('amount');
        $pending    = $pending->where('status', 2)->sum('amount');
        $rejected   = $rejected->where('status', 3)->sum('amount');
        return view('agent.deposit_history', compact('pageTitle', 'deposits', 'successful', 'pending', 'rejected'));
    }
    public function kycForm()
    {
        if (authAgent()->kv == 2) {
            $notify[] = ['error', 'Your KYC is under review'];
            return to_route('agent.kyc.data')->withNotify($notify);
        }
        if (authAgent()->kv == 1) {
            $notify[] = ['error', 'You are already KYC verified'];
            return to_route('agent.dashboard')->withNotify($notify);
        }
        $pageTitle = 'KYC Form';
        $form      = Form::where('act', 'agent.kyc')->first();
        return view('agent.kyc.form', compact('pageTitle', 'form'));
    }
    public function kycData()
    {
        $user      = authAgent();
        $pageTitle = 'KYC Data';
        return view('agent.kyc.info', compact('pageTitle', 'user'));
    }
    public function kycSubmit(Request $request)
    {
        $form           = Form::where('act', 'agent.kyc')->first();
        $formData       = $form->form_data;
        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData       = $formProcessor->processFormData($request, $formData);
        $user           = authAgent();
        $user->kyc_data = $userData;
        $user->kv       = 2;
        $user->save();

        $notify[] = ['success', 'KYC data submitted successfully'];
        return to_route('agent.dashboard')->withNotify($notify);
    }
    public function attachmentDownload($fileHash)
    {
        $filePath = decrypt($fileHash);
        if (!(file_exists($filePath) && is_file($filePath))) {
            $notify[] = ['error', 'File not found!'];
            return back()->withNotify($notify);
        }
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $general = gs();
        $title = slug($general->site_name) . '- attachments.' . $extension;
        $mimetype = mime_content_type($filePath);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }
}
