<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\NotificationLog;
use App\Models\SendMoney;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminNotification;

class ManageUsersController extends Controller {

    public function allUsers() {
        $pageTitle = 'All Users';
        $users     = $this->userData();
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function rejectedUsers() {
        $pageTitle = 'Rejected Users';
        $users     = $this->userData('rejected');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function activeUsers() {
        $pageTitle = 'Active Users';
        $users     = $this->userData('active');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function bannedUsers() {
        $pageTitle = 'Banned Users';
        $users     = $this->userData('banned');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailUnverifiedUsers() {
        $pageTitle = 'Email Unverified Users';
        $users     = $this->userData('emailUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }
    public function kycUnverifiedUsers() {
        $pageTitle = 'KYC Unverified Users';
        $users =  $this->userData('kycUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function kycPendingUsers() {
        $pageTitle = 'KYC Unverified Users';
        $users =  $this->userData('kycPending');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailVerifiedUsers() {
        $pageTitle = 'Email Verified Users';
        $users     = $this->userData('emailVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function mobileUnverifiedUsers() {
        $pageTitle = 'Mobile Unverified Users';
        $users     = $this->userData('mobileUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function mobileVerifiedUsers() {
        $pageTitle = 'Mobile Verified Users';
        $users     = $this->userData('mobileVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function usersWithBalance() {
        $pageTitle = 'Users with Balance';
        $users     = $this->userData('withBalance');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    protected function userData($scope = null) {
        if ($scope) {
            $users = User::$scope();
        } else {
            $users = User::query();
        }
        return $users->searchable(['username', 'email'])->orderBy('id', 'desc')->paginate(getPaginate());
    }


    public function detail($id) {
        $user             = User::findOrFail($id);
        $pageTitle        = 'User Detail - ' . $user->username;
        $totalSendMoney   = SendMoney::where('user_id', $user->id)->whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_COMPLETED])->sum('base_currency_amount');
        $totalDeposit     = Deposit::where('user_id', $user->id)->where('status', Status::PAYMENT_SUCCESS)->sum('amount');
        $totalTransaction = Transaction::where('user_id', $user->id)->count();
        $countries        = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return view('admin.users.detail', compact('pageTitle', 'user', 'totalDeposit', 'totalSendMoney', 'totalTransaction', 'countries'));
    }

    public function update(Request $request, $id) {
        $user         = User::findOrFail($id);
        $countryData  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryArray = (array)$countryData;
        $countries    = implode(',', array_keys($countryArray));

        $countryCode = 'ES';
        $country     = $countryData->$countryCode->country;
        $dialCode    = $countryData->$countryCode->dial_code;

        $request->validate([
            'firstname' => 'required|string|max:40',
            'lastname'  => 'required|string|max:40',
            'email'     => 'required|email|string|max:40|unique:users,email,' . $user->id,
            'mobile'    => 'required|string|max:40|unique:users,mobile,' . $user->id,
            //'country'   => 'required|in:' . $countries,
        ]);
        //$user->mobile       = $dialCode . $request->mobile;
        $user->mobile       = $request->mobile;
        $user->country_code = $countryCode;
        $user->firstname    = $request->firstname;
        $user->lastname     = $request->lastname;
        $user->email        = $request->email;
        $user->address      = [
            'address' => $request->address,
            'city'    => $request->city,
            'state'   => $request->state,
            'zip'     => $request->zip,
            'country' => @$country,
        ];
        if (!$request->kv) {
            $user->kv       = Status::KYC_UNVERIFIED;
            if ($user->kyc_data) {
                foreach ($user->kyc_data as $kycData) {
                    if ($kycData->type == 'file') {
                        fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
                    }
                }
            }
            $user->kyc_data = null;
        } else {
            $user->kv = Status::KYC_VERIFIED;
        }
        $user->ev = $request->ev ? Status::VERIFIED : Status::UNVERIFIED;
        $user->sv = $request->sv ? Status::VERIFIED : Status::UNVERIFIED;
        $user->ts = $request->ts ? Status::ENABLE : Status::DISABLE;
        $user->save();

        $notify[] = ['success', 'User details updated successfully'];
        return back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id) {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'act'    => 'required|in:add,sub',
            'remark' => 'required|string|max:255',
        ]);

        $user    = User::findOrFail($id);
        $amount  = $request->amount;
        $general = gs();
        $trx     = getTrx();

        $transaction = new Transaction();

        if ($request->act == 'add') {
            $user->balance += $amount;

            $transaction->trx_type = '+';
            $transaction->remark   = 'balance_add';

            $notifyTemplate = 'BAL_ADD';

            $notify[] = ['success', $general->cur_sym . $amount . ' added successfully'];
        } else {
            if ($amount > $user->balance) {
                $notify[] = ['error', $user->username . ' doesn\'t have sufficient balance.'];
                return back()->withNotify($notify);
            }

            $user->balance -= $amount;

            $transaction->trx_type = '-';
            $transaction->remark   = 'balance_subtract';

            $notifyTemplate = 'BAL_SUB';
            $notify[]               = ['success', $general->cur_sym . $amount . ' subtracted successfully'];
        }

        $user->save();

        $transaction->user_id      = $user->id;
        $transaction->amount       = $amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx          = $trx;
        $transaction->details      = $request->remark;
        $transaction->save();

        notify($user, $notifyTemplate, [
            'trx'          => $trx,
            'amount'       => showAmount($amount),
            'remark'       => $request->remark,
            'post_balance' => showAmount($user->balance)
        ]);

        return back()->withNotify($notify);
    }

    public function login($id) {
        auth()->guard('agent')->logout();
        Auth::loginUsingId($id);
        return to_route('user.home');
    }

    public function status(Request $request, $id) {
        $user = User::findOrFail($id);
        if ($user->status == Status::USER_ACTIVE) {
            $request->validate([
                'reason' => 'required|string|max:255'
            ]);
            $user->status     = Status::USER_BAN;
            $user->ban_reason = $request->reason;
            $notify[]         = ['success', 'User banned successfully'];
        } else {
            $user->status     = Status::USER_ACTIVE;
            $user->ban_reason = null;
            $notify[]         = ['success', 'User unbanned successfully'];
        }

        $user->save();
        return back()->withNotify($notify);
    }


    public function showNotificationSingleForm($id) {
        $user    = User::findOrFail($id);
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.users.detail', $user->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $user->username;
        return view('admin.users.notification_single', compact('pageTitle', 'user'));
    }

    public function sendNotificationSingle(Request $request, $id) {
        $request->validate([
            'message' => 'required|string',
            'subject' => 'required|string',
        ]);

        $user = User::findOrFail($id);
        notify($user, 'DEFAULT', [
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

        $users     = User::active()->count();
        $pageTitle = 'Notification to Verified Users';
        return view('admin.users.notification_all', compact('pageTitle', 'users'));
    }

    public function sendNotificationAll(Request $request) {
        $validator = Validator::make($request->all(), [
            'message' => 'required',
            'subject' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $user = User::active()->skip($request->skip)->first();

        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'total_sent' => 0,
            ]);
        }
        notify($user, 'DEFAULT', [
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return response()->json([
            'success'    => 'message sent',
            'total_sent' => $request->skip + 1,
        ]);
    }

    public function notificationLog($id) {
        $user      = User::findOrFail($id);
        $pageTitle = trans('Notifications Sent to') . ' ' . $user->username;
        $logs      = NotificationLog::where('user_id', $id)->with('user')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle', 'logs', 'user'));
    }
    public function kycDetails($id) {
        $pageTitle = 'KYC Details';
        $user     = User::findOrFail($id);
        return view('admin.users.kyc_detail', compact('pageTitle', 'user'));
    }

    public function kycApprove($id) {
        $user     = User::findOrFail($id);
        $user->kv = Status::KYC_VERIFIED;
        $user->save();

        notify($user, 'KYC_APPROVE', []);

        $notify[] = ['success', 'KYC approved successfully'];
        return to_route('admin.users.kyc.pending')->withNotify($notify);
    }

    public function kycReject(Request $request, $id) {
        $user = User::findOrFail($id);
        if(!is_null($user->kyc_data))
        {
            foreach ($user->kyc_data as $kycData) {
                if ($kycData->type == 'file') {
                    fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
                }
            }
        }
        
        $user->kv       = Status::KYC_UNVERIFIED;
        
        $user->reason = $request->reason;
        $user->kyc_data = null;
        $user->save();

        notify($user, 'KYC_REJECT', []);

        $notify[] = ['success', 'KYC rejected successfully'];
        return to_route('admin.users.kyc.pending')->withNotify($notify);
    }

    
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $agree = 'nullable';
        $countryData = (array)json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes = implode(',', array_column($countryData, 'dial_code'));
        $countries = implode(',', array_column($countryData, 'country'));
        $validate = Validator::make($data, [
            'email' => 'required|string|email|unique:users',
            'mobile' => 'required|regex:/^([0-9]*)$/',
            'username' => 'required|unique:users|min:6',
            'mobile_code' => 'required|in:' . $mobileCodes,
            'country_code' => 'required|in:' . $countryCodes,
            'country' => 'required|in:' . $countries,
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'agree' => $agree
        ]);

        return $validate;
    }


    public function registerUser(Request $request)
    {
        
        $email_str = (empty($request->email) ? (strtolower(str_replace(' ', '_', $request->firstname . '_' . $request->lastname)) . '@djandresromay.es') : $request->email);
        $request->merge(['email' => $email_str]);

        $this->validator($request->all())->validate();

        $request->session()->regenerateToken();
        $request->username = strtolower($request->username);
        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $notify[] = ['info', 'El nombre de usuario puede contener solo letras minúsculas, números y guión bajo.'];
            $notify[] = ['error', 'El nombre de usuario no debe contener carácteres especiales, especios o letras mayúsculas.'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        $exist = User::where('mobile', $request->mobile_code . $request->mobile)->first();
        if ($exist) {
            $notify[] = ['error', 'The mobile number already exists'];
            return back()->withNotify($notify)->withInput();
        }

        event(new Registered($user = $this->create($request->all())));

        //$this->guard()->login($user);

        // return $this->registered($request, $user)
        //     ?: redirect($this->redirectPath());


        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;

        $user->address = [
            'address' => $request->address,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => @$user->address->country,
            'city' => $request->city,
        ];

        $user->save();

        $notify[] = ['success', 'User added successfully'];
        //return back()->withNotify($notify);
        return to_route('admin.send.money.send_money_form', ['user' => $user->id])->withNotify($notify);



    }



    
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $general = gs();

        $referBy = session()->get('reference');
        if ($referBy) {
            $referUser = User::where('username', $referBy)->first();
        } else {
            $referUser = null;
        }
        //User Create
        $user               = new User();
        $user->email        = strtolower($data['email']);
        $user->password     = Hash::make('12345678');
        $user->username     = $data['username'];
        $user->ref_by       = $referUser ? $referUser->id : 0;
        $user->country_code = $data['country_code'];
        //$user->mobile       = $data['mobile_code'] . $data['mobile'];
        $user->mobile       = $data['mobile'];


        // $user->address = [
        //     'address' => '',
        //     'state'   => '',
        //     'zip'     => '',
        //     'country' => isset($data['country']) ? $data['country'] : null,
        //     'city'    => ''
        // ];
        $user->kv = $general->kv ? Status::NO : Status::YES;
        $user->ev = $general->ev ? Status::NO : Status::YES;
        $user->sv = $general->sv ? Status::NO : Status::YES;
        $user->ts = 0;
        $user->tv = 1;
        $user->save();


        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $user->id;
        $adminNotification->title     = trans('New member registered');
        $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
        $adminNotification->save();


        //Login Log Create
        // $ip = getRealIP();
        // // $exist = UserLogin::where('user_ip', $ip)->first();
        // $userLogin = new UserLogin();

        // //Check exist or not
        // // if ($exist) {
        // //     $userLogin->longitude    =  $exist->longitude;
        // //     $userLogin->latitude     =  $exist->latitude;
        // //     $userLogin->city         =  $exist->city;
        // //     $userLogin->country_code = $exist->country_code;
        // //     $userLogin->country      =  $exist->country;
        // // } else {
        //     $info                    = json_decode(json_encode(getIpInfo()), true);
        //     $userLogin->longitude    =  @implode(',', $info['long']);
        //     $userLogin->latitude     =  @implode(',', $info['lat']);
        //     $userLogin->city         =  @implode(',', $info['city']);
        //     $userLogin->country_code = @implode(',', $info['code']);
        //     $userLogin->country      =  @implode(',', $info['country']);
        // // }

        // $userAgent          = osBrowser();
        // $userLogin->user_id = $user->id;
        // $userLogin->user_ip =  $ip;

        // $userLogin->browser = @$userAgent['browser'];
        // $userLogin->os      = @$userAgent['os_platform'];
        // $userLogin->save();


        return $user;
    }

    public function kycNewVerify($id)
    {
        $user = User::find($id);
        
        $user->kv = 2;
        $user->video_id = $user->past_video_id;
        $user->save();

        $notify[] = ['success', 'Se ha realizado la solicitud correctamente.'];
        return back()->withNotify($notify);
    }

}
