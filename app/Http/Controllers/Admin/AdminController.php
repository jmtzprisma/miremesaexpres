<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\Admin;
use App\Models\Roles;
use App\Models\AdminNotification;
use App\Models\Agent;
use App\Models\Country;
use App\Models\Deposit;
use App\Models\SendMoney;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Withdrawal;
use App\Rules\FileTypeValidate;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller {

    public function dashboard() {

        $roles = explode(',', \App\Models\Roles::find(auth('admin')->user()->role_id)->permissions);

        if(in_array('2', $roles) || in_array('3', $roles))
            return redirect(route('admin.bank.list'));
        if(in_array('4', $roles))
            return redirect(route('admin.send.money.send_money_form'));
        if(in_array('7', $roles))
            return redirect(route('admin.report.transaction'));


        $pageTitle = 'Dashboard';
        // User Info
        $widget['total_users']             = User::count();
        $widget['verified_users']          = User::active()->count();
        $widget['email_unverified_users']  = User::emailUnverified()->count();
        $widget['mobile_unverified_users'] = User::mobileUnverified()->count();

        $widget['total_agent']             = Agent::count();
        $widget['active_agent']            = Agent::active()->count();
        $widget['kycUnverified']           = Agent::kycUnverified()->count();
        $widget['kycPending']              = Agent::kycPending()->count();


        // user Browsing, Country, Operating Log
        $userLoginData                 = UserLogin::where('created_at', '>=', now()->subDay(30))->get(['browser', 'os', 'country']);

        $chart['user_browser_counter'] = $userLoginData->groupBy('browser')->map(function ($item, $key) {
            return collect($item)->count();
        });

        $chart['user_os_counter']      = $userLoginData->groupBy('os')->map(function ($item, $key) {
            return collect($item)->count();
        });

        $chart['user_country_counter'] = $userLoginData->groupBy('country')->map(function ($item, $key) {
            return collect($item)->count();
        })->sort()->reverse()->take(5);

        // SendMoney Info
        $sendMoney['total']                     = SendMoney::where('payment_status', Status::PAYMENT_SUCCESS)->count();
        $sendMoney['pending']                   = SendMoney::pending()->count();
        $sendMoney['completed']                 = SendMoney::completed()->count();
        $sendMoney['refunded']                  = SendMoney::refunded()->count();

        $deposit['total_deposit_amount']        = Deposit::successful()->where('agent_id', '!=', 0)->sum('amount');
        $deposit['total_deposit_pending']       = Deposit::pending()->where('agent_id', '!=', 0)->count();
        $deposit['total_deposit_rejected']      = Deposit::rejected()->where('agent_id', '!=', 0)->count();
        $deposit['total_deposit_charge']        = Deposit::successful()->where('agent_id', '!=', 0)->sum('charge');

        $payment['total_payment_amount']        = Deposit::successful()->where('user_id', '!=', 0)->sum('amount');
        $payment['total_payment_pending']       = Deposit::pending()->where('user_id', '!=', 0)->count();
        $payment['total_payment_rejected']      = Deposit::rejected()->where('user_id', '!=', 0)->count();
        $payment['total_payment_charge']        = Deposit::successful()->where('user_id', '!=', 0)->sum('charge');

        $withdrawals['total_withdraw_amount']   = Withdrawal::approved()->sum('amount');
        $withdrawals['total_withdraw_pending']  = Withdrawal::pending()->count();
        $withdrawals['total_withdraw_rejected'] = Withdrawal::rejected()->count();
        $withdrawals['total_withdraw_charge']   = Withdrawal::approved()->sum('charge');

        // Transaction Graph
        $trxReport['date']  = collect([]);
        $plusTrx            = Transaction::where('trx_type', '+')->where('created_at', '>=', now()->subDays(30))
            ->selectRaw("SUM(amount) as amount, DATE_FORMAT(created_at,'%Y-%m-%d') as date")
            ->orderBy('created_at')
            ->groupBy('date')
            ->get();

        $plusTrx->map(function ($trxData) use ($trxReport) {
            $trxReport['date']->push($trxData->date);
        });

        $minusTrx = Transaction::where('trx_type', '-')->where('created_at', '>=', now()->subDays(30))
            ->selectRaw("SUM(amount) as amount, DATE_FORMAT(created_at,'%Y-%m-%d') as date")
            ->orderBy('created_at')
            ->groupBy('date')
            ->get();

        $minusTrx->map(function ($trxData) use ($trxReport) {
            $trxReport['date']->push($trxData->date);
        });

        $trxReport['date'] = dateSorting($trxReport['date']->unique()->toArray());


        // Monthly Deposit & Withdraw Report Graph
        $report['months']                = collect([]);
        $report['deposit_month_amount']  = collect([]);
        $report['withdraw_month_amount'] = collect([]);

        $depositsMonth = Deposit::where('created_at', '>=', now()->subYear())
            ->where('status', Status::PAYMENT_SUCCESS)
            ->where('agent_id', '!=', 0)
            ->selectRaw("SUM( CASE WHEN (status = " . Status::PAYMENT_SUCCESS . " AND agent_id != 0) THEN amount END) as depositAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();

        $depositsMonth->map(function ($depositData) use ($report) {
            $report['months']->push($depositData->months);
            $report['deposit_month_amount']->push(getAmount($depositData->depositAmount));
        });

        $withdrawalMonth = Withdrawal::where('created_at', '>=', now()->subYear())
            ->where('agent_id', '!=', 0)
            ->where('status', Status::PAYMENT_SUCCESS)
            ->selectRaw("SUM( CASE WHEN (status = " . Status::PAYMENT_SUCCESS . " AND agent_id != 0) THEN amount END) as withdrawAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();

        $withdrawalMonth->map(function ($withdrawData) use ($report) {
            if (!in_array($withdrawData->months, $report['months']->toArray())) {
                $report['months']->push($withdrawData->months);
            }
            $report['withdraw_month_amount']->push(getAmount($withdrawData->withdrawAmount));
        });

        //send Money statistics
        $sendingCountries = Country::whereHas('sendingTransfers', function ($query) {
            $query->completed();
        })->get(['id', 'currency']);

        $receivingCountries = Country::whereHas('receivingTransfers', function ($query) {
            $query->completed();
        })->get(['id', 'currency']);

        if ($sendingCountries->first() && $sendingCountries->first() == $receivingCountries->first()) {
            $firstReceivingCountry = $receivingCountries->shift();
            $secondReceivingCountry = $receivingCountries->shift();

            $receivingCountries->prepend($firstReceivingCountry);
            $receivingCountries->prepend($secondReceivingCountry);
        }

        //, SUM(send_money.base_currency_amount) as total_base_amount
        $sendMoneyData = SendMoney::selectRaw('sending_country.id as sending_country_id, recipient_country.currency as recipient_currency, sending_country.currency as sending_currency, sending_country.name as sending_country, sending_country.image as sending_country_image, recipient_country.id as recipient_country_id, recipient_country.name as recipient_country, recipient_country.image as recipient_country_image, SUM(send_money.sending_amount) as total_amount, SUM(send_money.recipient_amount) as total_base_amount')
            ->join('countries as sending_country', 'send_money.sending_country_id', '=', 'sending_country.id')
            ->join('countries as recipient_country', 'send_money.recipient_country_id', '=', 'recipient_country.id')
            ->groupBy('sending_country_id', 'recipient_country_id')
            ->where('send_money.created_at', '>=', now()->subYear())
            ->get();


        $sendMoneyAll = SendMoney::where('payment_status', Status::PAYMENT_SUCCESS)->SelectRaw('count(id) as total, status')->groupBy('status')->get();

        $sendMoneyLabels = [
            ['title' => 'Initiated', 'status' => 0],
            ['title' => 'Pending', 'status' => 2],
            ['title' => 'Completed', 'status' => 1],
            ['title' => 'Refunded', 'status' => 3]
        ];

        $sendMoneyStatistics = [];
        foreach ($sendMoneyLabels as $item) {
            $sendMoneyStatusWise = $sendMoneyAll->where('status', $item['status'])->first();
            if ($sendMoneyStatusWise) {
                $sendMoneyStatistics[$item['title']] = getAmount($sendMoneyStatusWise->total);
            } else {
                $sendMoneyStatistics[$item['title']] = 0;
            }
        }

        //send money statistics end
        $months = $report['months'];
        for ($i = 0; $i < $months->count(); ++$i) {
            $monthVal      = Carbon::parse($months[$i]);
            for ($j = $i + 1; $j < $months->count(); $j++) {
                if (isset($months[$j])) {
                    $dateValNext = Carbon::parse($months[$j]);
                    if ($dateValNext < $monthVal) {
                        $temp = $months[$i];
                        $months[$i]   = Carbon::parse($months[$j])->format('F-Y');
                        $months[$j] = Carbon::parse($temp)->format('F-Y');
                    } else {
                        $months[$i]   = Carbon::parse($months[$i])->format('F-Y');
                    }
                }
            }
        }

        return view('admin.dashboard', compact('pageTitle', 'widget', 'chart', 'deposit', 'payment', 'withdrawals', 'sendMoney', 'report', 'depositsMonth', 'withdrawalMonth', 'months', 'trxReport', 'plusTrx', 'minusTrx', 'sendingCountries', 'receivingCountries', 'sendMoneyStatistics', 'sendMoneyData'));
    }

    public function sendMoneyStatistics(Request $request) {

        $sendMoney = SendMoney::where('created_at', '>=', now()->subYear())->completed()->where('sending_currency', $request->sending_currency)->where('recipient_currency', $request->recipient_currency)->get();
        $allSendMoney = $sendMoney->mapWithKeys(function ($money) {
            $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
            $meses_EN = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
            //$date = date_format($money->created_at, 'M-Y');
            $date_m = date_format($money->created_at, 'M');

            $date_y = date_format($money->created_at, 'Y');
            
            $nombreMes = str_replace($meses_EN, $meses_ES, $date_m);

            $date = $nombreMes . '-' . $date_y;
            return [
                $date => [
                    'sending_amount' => (float)$money->sending_amount,
                    'base_currency_amount' => (float) $money->base_currency_amount
                ]
            ];
        });

        return [
            'allSendMoney' => $allSendMoney
        ];
    }

    public function profile() {
        $pageTitle = 'Profile';
        $admin     = auth('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request) {
        $this->validate($request, [
            'name'  => 'required',
            'email' => 'required|email',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])]
        ]);
        $admin = auth('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old          = $admin->image;
                $admin->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $admin->name  = $request->name;
        $admin->email = $request->email;
        $admin->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return to_route('admin.profile')->withNotify($notify);
    }


    public function password() {
        $pageTitle = 'Password Setting';
        $admin     = auth('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request) {
        $this->validate($request, [
            'old_password' => 'required',
            'password'     => 'required|min:5|confirmed',
        ]);

        $user = auth('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password doesn\'t match!!'];
            return back()->withNotify($notify);
        }
        $user->password = bcrypt($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return to_route('admin.password')->withNotify($notify);
    }

    public function notifications() {
        $notifications = AdminNotification::orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        $pageTitle     = 'Notifications';
        return view('admin.notifications', compact('pageTitle', 'notifications'));
    }


    public function notificationRead($id) {
        $notification = AdminNotification::findOrFail($id);
        $notification->is_read = Status::YES;

        $notification->save();
        $url = $notification->click_url;
        if ($url == '#') {
            $url = url()->previous();
        }
        return redirect($url);
    }

    public function requestReport() {
      
    }

    public function reportSubmit(Request $request) {
      
    }

    public function readAll() {
        AdminNotification::where('read_status', Status::NO)->update([
            'read_status' => Status::YES
        ]);
        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function downloadAttachment($fileHash) {
        $filePath  = decrypt($fileHash);
        if (!(file_exists($filePath) && is_file($filePath))) {
            $notify[] = ['error', 'File not found!'];
            return back()->withNotify($notify);
        }
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $general   = gs();
        $title     = slug($general->site_name) . '- attachments.' . $extension;
        $mimetype  = mime_content_type($filePath);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }
    
    public function listRolesAdmin()
    {
        $pageTitle = 'Roles Admin';
        $roles = Roles::orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.roles.list', compact('pageTitle', 'roles'));
    }

    public function addRole() {
        $pageTitle   = 'Add New Role';
        return view('admin.roles.add', compact('pageTitle'));
    }

    public function editRole($id) {
        $pageTitle   = 'Edit Role';
        $rol = Roles::find($id);
        return view('admin.roles.edit', compact('pageTitle', 'rol'));
    }

    public function updateRole($id, Request $request) {
        
        $role   = Roles::find($id);
        $rules = [
            'name' => 'required|string|max:40',
        ];
        $request->validate($rules);

        $role->name = $request->name;
        $role->permissions = implode(",", $request->permisos);
        $role->bancos = implode(",", $request->bancos);
        
        $role->save();

        $notify[] = ['success', 'Role updated successfully'];
        return back()->withNotify($notify);
    }

    public function storeRole(Request $request) {
        
        $role   = new Roles();
        $rules = [
            'name' => 'required|string|max:40',
        ];
        $request->validate($rules);

        $role->name = $request->name;
        $role->permissions = implode(",", $request->permisos);
        $role->bancos = implode(",", $request->bancos);
        $role->save();

        $notify[] = ['success', 'Role added successfully'];
        return back()->withNotify($notify);
    }


    public function listUsersAdmin()
    {
        $pageTitle = 'Usuarios Admin';
        $admins = Admin::orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.admins.list', compact('pageTitle', 'admins'));
    }

    
    public function add() {
        $pageTitle   = 'Add New Admin';
        return view('admin.admins.add', compact('pageTitle'));
    }

    public function edit($id) {
        $pageTitle   = 'Edit Admin';
        $user = Admin::find($id);
        return view('admin.admins.edit', compact('pageTitle', 'user'));
    }
    
    public function update($id, Request $request) {
        
        $admin   = Admin::find($id);
        $rules = [
            'name' => 'required|string|max:40',
            'email'     => 'required|email|string|max:40|unique:admins,email,' . $id,
        ];
        $request->validate($rules);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->username = $request->username;
        $admin->role_id = $request->role_id;
        
        if(!empty($request->password))
            $admin->password = Hash::make($request->password);
        
        $admin->save();

        $notify[] = ['success', 'Admin updated successfully'];
        return back()->withNotify($notify);
    }

    public function store(Request $request) {
        
        $admin   = new Admin();
        $this->validation($request);

        $exist = Admin::where('email', $request->email)->first();
        if ($exist) {
            $notify[] = ['error', 'The mobile number already exists'];
            return back()->withNotify($notify)->withInput();
        }

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->username = $request->username;
        $admin->role_id = $request->role_id;
        $admin->password = Hash::make($request->password);
        $admin->save();

        $notify[] = ['success', 'Admin added successfully'];
        return back()->withNotify($notify);
    }

    public function validation($request, $agent = null) {
        $passwordValidation = Password::min(6);
        $rules = [
            'name' => 'required|string|max:40',
            'email'     => 'required|email|string|max:40|unique:admins,email,' . @$agent->id,
        ];
        if (!$agent) {
            $rules['password'] = ['required', 'confirmed', $passwordValidation];
            $rules['username'] = 'required|alpha_num|unique:agents|min:6';
        }
        $request->validate($rules);
    }
}
