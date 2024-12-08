<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Country;
use App\Models\Deposit;
use App\Models\NotificationLog;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ManageAgentController extends Controller {
    public $pageTitle;

    public function add() {
        $pageTitle   = 'Add New Agent';
        $countries   = Country::all();
        return view('admin.agents.add', compact('pageTitle', 'countries'));
    }

    public function store(Request $request) {
        $general = gs();
        $agent   = new Agent();
        $this->validation($request);

        $exist = Agent::where('mobile', $request->mobile_code . $request->mobile)->first();
        if ($exist) {
            $notify[] = ['error', 'The mobile number already exists'];
            return back()->withNotify($notify)->withInput();
        }

        $country = Country::find($request->country);
        if (!$country) {
            $notify[] = ['error', 'Country not exist'];
            return back()->withNotify($notify)->withInput();
        }

        $agent->username = $request->username;
        $agent->password = Hash::make($request->password);

        $agent->status = Status::ENABLE;
        $agent->kv = $general->kv ? Status::KYC_UNVERIFIED : Status::KYC_VERIFIED;
        $agent->ts = Status::DISABLE;
        $agent->tv = Status::ENABLE;

        $agent = $this->saveAgent($request, $country, $agent);

        notify($agent, 'AGENT_ACCOUNT_CREATED', [
            'username' => $request->username,
            'password' => $request->password,
            'login_url' => route('agent.login'),
        ]);


        $notify[] = ['success', 'Agent added successfully'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id) {
        $agent = Agent::findOrFail($id);
        $this->validation($request, $agent);

        if (!$request->kv) {
            $agent->kv       = Status::KYC_UNVERIFIED;
            $agent->kyc_data = null;
        } else {
            $agent->kv = Status::KYC_VERIFIED;
        }

        $country = Country::find($request->country);
        if (!$country) {
            $notify[] = ['error', 'Country not exist'];
            return back()->withNotify($notify)->withInput();
        }

        $this->saveAgent($request, $country, $agent);

        $notify[] = ['success', 'Agent details updated successfully'];
        return back()->withNotify($notify);
    }

    public function validation($request, $agent = null) {
        $passwordValidation = Password::min(6);
        $rules = [
            'firstname' => 'required|string|max:40',
            'lastname'  => 'required|string|max:40',
            'country'   => 'required|integer|gt:0',
            'mobile'    => 'required|regex:/^([0-9]*)$/',
            'email'     => 'required|email|string|max:40|unique:agents,email,' . @$agent->id,
        ];
        if (!$agent) {
            $rules['password'] = ['required', 'confirmed', $passwordValidation];
            $rules['username'] = 'required|alpha_num|unique:agents|min:6';
        }
        $request->validate($rules);
    }

    public function saveAgent($request, $country, $agent) {

        $agent->country_id    = $country->id;
        $agent->mobile        = $country->dial_code . $request->mobile;
        $agent->country_code  = $country->country_code;
        $agent->firstname     = $request->firstname;
        $agent->lastname      = $request->lastname;
        $agent->email         = $request->email;
        $agent->address      = [
            'address' => $request->address,
            'city'    => $request->city,
            'state'   => $request->state,
            'zip'     => $request->zip,
            'country' => $country->name,
        ];

        $agent->save();
        return $agent;
    }


    public function allAgents() {
        $this->pageTitle = 'All Agents';
        return $this->agentData();
    }

    public function activeAgents() {
        $this->pageTitle = 'Active Agents';
        return $this->agentData('active');
    }

    public function bannedAgents() {
        $this->pageTitle = 'Banned Agents';
        return $this->agentData('banned');
    }

    public function kycUnverifiedAgents() {
        $this->pageTitle = 'KYC Unverified Agents';
        return $this->agentData('kycUnverified');
    }

    public function kycPendingAgents() {
        $this->pageTitle = 'KYC Unverified Agents';
        return $this->agentData('kycPending');
    }

    public function agentsWithBalance() {
        $this->pageTitle = 'Agents with Balance';
        return $this->agentData('withBalance');
    }


    protected function agentData($scope = null) {
        if ($scope) {
            $agents = Agent::$scope();
        } else {
            $agents = Agent::query();
        }
        $request = request();

        if ($request->search) {
            $search = $request->search;
            $agents = $agents->where('username', 'like', "%$search%");
        }

        $pageTitle = $this->pageTitle;
        $agents = $agents->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.agents.list', compact('pageTitle', 'agents'));
    }


    public function detail($id) {
        $agent     = Agent::findOrFail($id);
        $pageTitle = 'Agent Detail - ' . $agent->username;

        $totalDeposit     = Deposit::where('agent_id', $agent->id)->where('status', 1)->sum('amount');
        $totalWithdrawals = Withdrawal::where('agent_id', $agent->id)->where('status', 1)->sum('amount');
        $totalTransaction = Transaction::where('agent_id', $agent->id)->count();
        $countries        = Country::all();
        return view('admin.agents.detail', compact('pageTitle', 'agent', 'totalDeposit', 'totalWithdrawals', 'totalTransaction', 'countries'));
    }


    public function kycDetails($id) {
        $pageTitle = 'KYC Details';
        $agent     = Agent::findOrFail($id);
        return view('admin.agents.kyc_detail', compact('pageTitle', 'agent'));
    }

    public function kycApprove($id) {
        $agent     = Agent::findOrFail($id);
        $agent->kv = Status::KYC_VERIFIED;
        $agent->save();

        notify($agent, 'KYC_APPROVE', []);

        $notify[] = ['success', 'KYC approved successfully'];
        return to_route('admin.agents.kyc.pending')->withNotify($notify);
    }

    public function kycReject($id) {
        $agent = Agent::findOrFail($id);
        foreach ($agent->kyc_data as $kycData) {
            if ($kycData->type == 'file') {
                fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
            }
        }
        $agent->kv       = Status::KYC_UNVERIFIED;
        $agent->kyc_data = null;
        $agent->save();

        notify($agent, 'KYC_REJECT', []);

        $notify[] = ['success', 'KYC rejected successfully'];
        return to_route('admin.agents.kyc.pending')->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id) {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'act'    => 'required|in:add,sub',
            'remark' => 'required|string|max:255',
        ]);

        $agent   = Agent::findOrFail($id);
        $amount  = $request->amount;
        $general = gs();
        $trx     = getTrx();

        $transaction = new Transaction();

        if ($request->act == 'add') {
            $agent->balance += $amount;
            $transaction->trx_type = '+';
            $transaction->remark   = 'balance_add';
            $notifyTemplate        = 'BAL_ADD';
            $notification          = $general->cur_sym . $amount . ' added successfully';
        } else {

            if ($amount > $agent->balance) {
                $notify[] = ['error', $agent->username . ' doesn\'t have sufficient balance.'];
                return back()->withNotify($notify);
            }

            $agent->balance        -= $amount;
            $transaction->trx_type  = '-';
            $transaction->remark    = 'balance_subtract';
            $notifyTemplate         = 'BAL_SUB';
            $notification           = $general->cur_sym . $amount . ' subtracted successfully';
        }

        $agent->save();

        $transaction->agent_id     = $agent->id;
        $transaction->amount       = $amount;
        $transaction->post_balance = $agent->balance;
        $transaction->charge       = 0;
        $transaction->trx          = $trx;
        $transaction->details      = $request->remark;
        $transaction->save();

        notify($agent, $notifyTemplate, [
            'trx'          => $trx,
            'amount'       => showAmount($amount),
            'remark'       => $request->remark,
            'post_balance' => showAmount($agent->balance)
        ]);

        $notify[] = ['success', $notification];

        return back()->withNotify($notify);
    }

    public function login($id) {
        auth()->logout();
        auth()->guard('agent')->loginUsingId($id);
        return to_route('agent.dashboard');
    }

    public function status(Request $request, $id) {
        $agent = Agent::findOrFail($id);

        if ($agent->status == Status::ENABLE) {

            $request->validate([
                'reason' => 'required|string|max:255'
            ]);

            $agent->status     = Status::ENABLE;
            $agent->ban_reason = $request->reason;
            $notify[]          = ['success', 'Agent banned successfully'];
        } else {
            $agent->status     = Status::ENABLE;
            $agent->ban_reason = null;
            $notify[]          = ['success', 'Agent unbanned successfully'];
        }

        $agent->save();
        return back()->withNotify($notify);
    }


    public function showNotificationSingleForm($id) {
        $agent   = Agent::findOrFail($id);
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.agents.detail', $agent->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $agent->username;
        return view('admin.agents.notification_single', compact('pageTitle', 'agent'));
    }

    public function sendNotificationSingle(Request $request, $id) {
        $request->validate([
            'message' => 'required|string',
            'subject' => 'required|string',
        ]);

        $agent = Agent::findOrFail($id);

        notify($agent, 'DEFAULT', [
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        $notify[] = ['success', 'Notification sent successfully'];
        return back()->withNotify($notify);
    }

    public function showNotificationAllForm() {
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }
        $agents    = Agent::where('status', Status::ENABLE)->count();
        $pageTitle = 'Notification to Verified Agents';
        return view('admin.agents.notification_all', compact('pageTitle', 'agents'));
    }

    public function sendNotificationAll(Request $request) {

        $validator = Validator::make($request->all(), [
            'message' => 'required',
            'subject' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $agent = Agent::where('status', Status::ENABLE)->skip($request->skip)->first();

        notify($agent, 'DEFAULT', [
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return response()->json([
            'success'    => 'message sent',
            'total_sent' => $request->skip + 1,
        ]);
    }

    public function notificationLog($id) {
        $agent     = Agent::findOrFail($id);
        $pageTitle = trans('Notifications Sent to') . ' ' . $agent->username;
        $logs      = NotificationLog::where('agent_id', $id)->with('user', 'agent')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle', 'logs', 'agent'));
    }
}
