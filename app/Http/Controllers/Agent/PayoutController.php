<?php

namespace App\Http\Controllers\Agent;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\SendMoney;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use stdClass;

class PayoutController extends Controller {

    public function payoutHistory() {
        $pageTitle    = 'Payout History';
        $emptyMessage = trans('No payout found');
        $agent        = authAgent();
        $transfers    = SendMoney::completed()->where('payout_by', $agent->id)->searchable(['mtcn_number'])->latest()->paginate(getPaginate());
        return view('agent.payout.history', compact('pageTitle', 'emptyMessage', 'transfers'));
    }

    public function payout() {
        $pageTitle = 'Payout Money';
        return view('agent.payout.payout_money', compact('pageTitle'));
    }


    public function payoutInfo(Request $request) {
        $request->validate([
            'mtcn_number' => 'required|string'
        ]);

        $sendMoney = SendMoney::where('mtcn_number', $request->mtcn_number)->where('service_id', 0)->where('status', Status::SEND_MONEY_PENDING)->orderBy('id', 'DESC')->with('recipientCountry')->first();

        if (!$sendMoney) {
            $notify[] = ['error', 'Invalid MTCN provided'];
            return back()->withNotify($notify);
        }

        $this->checkSendAbility($sendMoney);

        $pageTitle  = 'Confirmación de pago';
        $general    = gs();
        $commission = $general->agent_fixed_commission + $general->agent_percent_commission * $sendMoney->base_currency_amount / 100;
        return view('agent.payout.payout_info', compact('pageTitle', 'sendMoney', 'commission'));
    }

    public function payoutConfirm(Request $request, $id) {
        $request->validate([
            'code' => 'required',
        ]);

        $sendMoney    = SendMoney::with('recipientCountry')->where('id', $id)->where('status', Status::SEND_MONEY_PENDING)->orderBy('id', 'DESC')->first();
        $this->checkSendAbility($sendMoney);

        if ($sendMoney->verification_code != $request->code) {
            $notify[] = ['error', 'Verification code doesn\'t matched'];
            return back()->withNotify($notify);
        }

        $general                = gs();
        $agent                  = authAgent();
        $trx                    = $sendMoney->trx;
        $amount                 = $sendMoney->base_currency_amount;

        $sendMoney->received_at = Carbon::now();
        $sendMoney->status      = Status::SEND_MONEY_COMPLETED;
        $sendMoney->payout_by   = $agent->id;
        $sendMoney->save();

        $agent->balance = $agent->balance + $amount;
        $agent->save();


        $transaction               = new Transaction();
        $transaction->agent_id     = $agent->id;
        $transaction->amount       = $sendMoney->base_currency_amount;
        $transaction->post_balance = $agent->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '+';
        $transaction->details      = 'Payout completed';
        $transaction->trx          = $trx;
        $transaction->remark       = 'payout_completed';
        $transaction->remark       = 'Payout';
        $transaction->save();

        $commission = $general->agent_fixed_commission + $general->agent_percent_commission * $amount / 100;

        if ($commission) {
            $agent->balance = $agent->balance + $commission;
            $agent->save();

            $transaction               = new Transaction();
            $transaction->agent_id     = $agent->id;
            $transaction->amount       = $commission;
            $transaction->post_balance = $agent->balance;
            $transaction->charge       = 0;
            $transaction->trx_type     = '+';
            $transaction->details      = 'Payout commission received';
            $transaction->trx          = $trx;
            $transaction->remark       = 'payout_commission';
            $transaction->save();
        }

        if ($sendMoney->user_id) {
            $user = $sendMoney->user;
        } else {
            $user = $sendMoney->agent;
        }

        notify($user, 'SEND_MONEY_RECEIVED', [
            'recipient_country'  => @$sendMoney->recipientCountry->name,
            'recipient_amount'   => showAmount($sendMoney->recipient_amount),
            'recipient_currency' => $sendMoney->recipient_currency,
            'trx'                => $trx,
        ]);

        $notify[] = ['success', 'Payout completed successfully'];
        return to_route('agent.payout.history')->withNotify($notify);
    }

    public function payoutVerificationCode() {
        $sendMoney    = SendMoney::where('id', request()->id)->first();
        $general      = gs();
        $duration     = $general->resent_code_duration;
        $codeSentTime = Carbon::now()->diffInSeconds($sendMoney->verification_time);

        if (!$sendMoney) {
            $response['status']  = 'error';
            $response['message'] = 'Transaction not found';
        } elseif ($sendMoney->status != Status::SEND_MONEY_PENDING) {
            $response['status']  = 'error';
            $response['message'] = 'Unauthorized access.';
        } elseif ($codeSentTime < $duration) {
            $response['status']  = 'error';
            $response['message'] = 'Please try after ' . ($duration - $codeSentTime) . ' seconds';
        } else {
            $code                         = verificationCode(6);
            $sendMoney->verification_code = $code;
            $sendMoney->verification_time = Carbon::now();
            $sendMoney->save();

            $mobile          = @$sendMoney->recipient->mobile;
            $guest           = new stdClass();
            $guest->username = 'Recipient';
            $guest->fullname = @$sendMoney->recipient->name;
            $guest->mobile   = $mobile;

            notify($guest, 'DEFAULT', [
                'message' => 'Your ' . $general->sitename . ' payout verification is ' . $code,
            ], ['sms']);

            $response['status']  = 'success';
            $response['message'] = 'A 6 digit verification code sent to +' . showMobileNumber($mobile);
        }
        return $response;
    }


    private function checkSendAbility($sendMoney) {
        $agent = authAgent();

        if ($sendMoney->recipientCountry->country_code != $agent->country_code) {
            throw ValidationException::withMessages(['error' => 'You can only payout where the recipient country is ' . $agent->country->name]);
        }

        if (!$sendMoney) {
            throw ValidationException::withMessages(['error' => 'Invalid MTCN provided']);
        }

        if ($sendMoney->agent_id == $agent->id) {
            throw ValidationException::withMessages(['error' => 'You can\'t payout your own send money']);
        }

        if ($sendMoney->status == Status::SEND_MONEY_COMPLETED) {
            throw ValidationException::withMessages(['error' => 'Payout already completed']);
        }
    }
}
