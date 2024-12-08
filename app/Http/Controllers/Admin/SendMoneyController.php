<?php

namespace App\Http\Controllers\Admin;

use PDF;
use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\Banco;
use App\Models\CuentasCobrar;
use App\Models\Deposit;
use App\Models\LogBanco;
use App\Models\SendMoney;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SourceOfFund;
use App\Models\SendingPurpose;
use App\Models\Country;
use App\Models\Service;
use App\Models\Form;

use App\Lib\ProcessSendMoney;
use App\Models\Bank;
use App\Models\CierreDiario;
use App\Models\CombinedDeposit;
use App\Models\CountryDeliveryMethod;
use App\Models\Recipient;
use App\Models\GatewayCurrency;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

use App\Models\LogNotify;
use App\Notify\Notify;

use Twilio\Rest\Client;


class SendMoneyController extends Controller
{

    public function index()
    {
        $segments       = request()->segments();
        $scope          = end($segments);
        $pageTitle      = ucfirst($scope) . " Send Money";

        if (request()->search) {
            $pageTitle .= ' - ' . request()->search;
        }

        $sendMoneys     = SendMoney::latest()->with('deposit')->where('status', '!=', Status::SEND_MONEY_INITIATED);
        if ($scope != 'all') {
            $sendMoneys = $sendMoneys->$scope();
        }

        if (request()->payment_method) {
            //$sendMoneys = $sendMoneys->where('payment_type', 3)->orWhere('payment_type', 4);
            $sendMoneys = $sendMoneys->where(function ($query) {
                $query->where('payment_type', 3);
                $query->orWhere('payment_type', 4);
            });
        }

        $_codigo_banco = [];
        $idSendMoney = [];
        if(auth('admin')->user()->role_id != 1)
        {
            $bancos = explode(',', \App\Models\Roles::find(auth('admin')->user()->role_id)->bancos);
            foreach(Bank::where('active', true)->whereIn('id', $bancos)->get() as $itm)
            {
                if(!is_null($itm->codigo_banco))
                    array_push($_codigo_banco, $itm->codigo_banco);
            }
            $_sendMoneys = clone $sendMoneys;
            foreach ($_sendMoneys->get() as $sendMoney)
            {

                $recipient = $sendMoney->recipient;
                $obj_recipient = Recipient::find($recipient->id);

                if($obj_recipient->service_id == 10){
                    if(in_array(52, $bancos)) array_push($idSendMoney, $sendMoney->id);
                }else{

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
            }
            $sendMoneys->where('visible', 1)->whereIn('id', $idSendMoney);
        }
        
        $sendMoneys     = $sendMoneys->orderBy('updated_at', 'desc')->searchable(['mtcn_number', 'sender', 'recipient', 'user:username', 'agent:username'])->with('user', 'agent', 'payoutBy', 'service', 'deposit', 'sourceOfFund', 'sendingPurpose', 'countryDeliveryMethod.deliveryMethod', 'sendingCountry', 'recipientCountry')->paginate(getPaginate());

        return view('admin.send_money.list', compact('pageTitle', 'sendMoneys'));
    }

    public function details($id = null)
    {
        $sendMoney = SendMoney::with(['user', 'agent', 'payoutBy', 'service', 'countryDeliveryMethod.deliveryMethod', 'sendingCountry', 'recipientCountry'])->findOrFail($id);
        $pageTitle = 'Send money to ' . @$sendMoney->recipientCountry->name . ' from ' . @$sendMoney->sendingCountry->name;
        return view('admin.send_money.detail', compact('pageTitle', 'sendMoney'));
    }

    public function refundMoney(Request $request, $id = null)
    {
        $request->validate([
            'message' => 'required'
        ], [
            'message.required' => 'Please write a feedback'
        ]);

        $sendMoney                 = SendMoney::with('sendingCountry', 'recipientCountry')->where('status', Status::SEND_MONEY_PENDING)->findOrFail($id);
        $sendMoney->status         = Status::SEND_MONEY_REFUNDED;
        $sendMoney->admin_feedback = $request->message;
        $sendMoney->save();

        $transaction               = new Transaction();
        if ($sendMoney->user_id) {
            $user                  = $sendMoney->user;
            $transaction->user_id  = $sendMoney->user_id;
        } else {
            $user                  = $sendMoney->agent;
            $transaction->agent_id = $sendMoney->agent_id;
        }
        $user->balance += $sendMoney->base_currency_amount;
        $user->save();
        $transaction->amount       = $sendMoney->base_currency_amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '+';
        $transaction->details      = 'Refunded sent money. Message: ' . $request->message;
        $transaction->trx          = $sendMoney->trx;
        $transaction->save();

        notify($user, 'SEND_MONEY_REFUND', [
            'trx'                => $sendMoney->trx,
            'sending_country'    => @$sendMoney->sendingCountry->name,
            'sending_amount'     => showAmount($sendMoney->sending_amount),
            'sending_currency'   => $sendMoney->sending_currency,
            'recipient_country' => @$sendMoney->recipientCountry->name,
            'recipient_amount'   => showAmount($sendMoney->recipient_amount),
            'recipient_currency' => $sendMoney->recipient_currency,
            'message'            => $request->message,
        ]);
        $notify[] = ['success', 'This send money is refunded successfully'];
        return back()->withNotify($notify);
    }

    public function payTheReceiver(Request $request, $id)
    {
        if ($request->hasFile('image')) {
            try {
                $comprobante = fileUploader($request->image, getFilePath('sendMoney'));
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Image could not be uploaded.'];
                return back()->withNotify($notify);
            }
        }else{
            $notify[] = ['error', 'Es necesario adjuntar una imagen de la operación realizada.'];
            return back()->withNotify($notify);
        }
        
        $sendMoney = SendMoney::with('user', 'recipientCountry')->findOrFail($id);    
        
        if($sendMoney->status == Status::SEND_MONEY_PENDING && $sendMoney->payment_status == Status::PAYMENT_SUCCESS){
            $sendMoney->status = Status::SEND_MONEY_COMPLETED;
            $sendMoney->save();

            if (@$sendMoney->user) {
                $user = $sendMoney->user;
            } else {
                $sender = $sendMoney->sender;
                $user = new User();
                $user->username = @$sender->firstname . @$sender->lastname;
                $user->email = $sender->email;
                $user->mobile = @$sender->mobile;
            }

            $deposit = Deposit::where('send_money_id', $sendMoney->id)->first();
            
            $bank_process = new \App\Http\Controllers\Admin\BankController;
            $req = new Request;
            $req->merge(
                [
                    'comprobante' => $comprobante,
                    'bankId' => $request->banco,
                    'send_money_id' => $sendMoney->id,
                    'amount_currency_local' => $sendMoney->recipient_amount,
                    'amount_currency_convert' => $sendMoney->sending_amount
                ]
            );
            $bank_process->retiroBanco($req);

            // $tranx = LogBanco::where('deposit_id', $deposit->id)->where('operation_type', 1)->first();
            
            // $banco = Banco::find($tranx->banco_id);
            // $banco->balance -= $sendMoney->sending_amount;
            // $banco->save();

            // $log_banco = new LogBanco;
            // $log_banco->banco_id = $tranx->banco_id;
            // $log_banco->deposit_id = 0;
            // $log_banco->operation_type = 0;
            // $log_banco->save();

            $sid = getenv("TWILIO_ACCOUNT_SID");
            $token = getenv("TWILIO_AUTH_TOKEN");
            $twilio = new Client($sid, $token);

            $mobile_number = $this->cleanMobile($user->mobile);
            //$mobile_number = "+5219516152009";

            if(!is_null($mobile_number) && $request->hasFile('image')) $message = $twilio->messages->create("whatsapp:" . $mobile_number, // to
                [
                    "contentSid" => "HXf06328ff04585c1f9d0d82a60c456fce",
                    "from" => "MGdccc9ede0b660303c61f34826bfeceb6",
                    "contentVariables" => json_encode([
                        "1" => '#' . $sendMoney->mtcn_number,
                        "2" => $comprobante
                    ])
                ]);

            notify($user, 'SEND_MONEY_RECEIVED', [
                'recipient_country'  => @$sendMoney->recipientCountry->name,
                'recipient_amount'   => showAmount($sendMoney->recipient_amount, 3),
                'recipient_currency' => $sendMoney->recipient_currency,
                'image_url'          => $request->hasFile('image') ? getImage(getFilePath('sendMoney') . '/' . $comprobante) : '',
                'url_pdf'            => route('create_pdf', $sendMoney->id),
                'trx'                => $sendMoney->trx
            ]);

            $notify[] = ['success', 'Send money completed successfully'];
            return to_route('admin.send.money.pending')->withNotify($notify);
        }
        return to_route('admin.send.money.pending');
    }

    private function cleanMobile($strMobile){
        $mobile = null;

        $strMobile = str_replace(' ','',$strMobile);
        $strMobile = str_replace('-','',$strMobile);

        if(substr($strMobile,0,3) == '+34' || substr($strMobile,0,3) == '+58'){
            $mobile = $strMobile;
        }elseif(substr($strMobile,0,2) == '34' || substr($strMobile,0,2) == '58'){
            $mobile = '+' . $strMobile;
        }elseif(strlen($strMobile) == 9){
            $mobile = '+34' . $strMobile;
        }elseif(strlen($strMobile) == 10){
            $mobile = '+58' . $strMobile;
        }

        return $mobile;
    }

    /**
     * CRUD BANCOS
     */
    public function list_bancos()
    {
        $segments       = request()->segments();
        $scope          = end($segments);
        $pageTitle      = ucfirst($scope);

        $bancos = Banco::latest();
        $bancos = $bancos->searchable(['name', 'cuenta', 'instrucciones'])->paginate(getPaginate());
        return view('admin.send_money.list_bancos', compact('pageTitle', 'bancos'));
    }

    public function create_banco()
    {
        $segments       = request()->segments();
        $scope          = end($segments);
        $pageTitle      = ucfirst($scope);

        return view('admin.send_money.add_banco', compact('pageTitle'));
    }

    public function store_banco(Request $request)
    {
        $banco = new Banco;
        $banco->name = $request->name;
        $banco->cuenta = $request->cuenta;
        $banco->instrucciones = $request->instrucciones;

        $banco->save();

        return redirect(route('admin.send.money.list_bancos'));
    }

    public function edit_banco($id)
    {
        $segments       = request()->segments();
        $scope          = end($segments);
        $pageTitle      = "Agregar Banco";

        $banco = Banco::find($id);
        return view('admin.send_money.edit_banco', compact('banco', 'pageTitle'));
    }

    public function update_banco($id, Request $request)
    {
        
        $banco = Banco::find($id);
        $banco->name = $request->name;
        $banco->cuenta = $request->cuenta;
        $banco->instrucciones = $request->instrucciones;

        $banco->save();
        return redirect(route('admin.send.money.list_bancos'));
    }

    public function send_money_form(Request $request)
    {
        $user = null;
        if($request->has('user'))
            $user = User::find($request->user);
        
        $bancos = Banco::get()->pluck('name', 'id');

        $pageTitle                 = 'Send Money';

        $sources                   = SourceOfFund::active()->get();
        $purposes                  = SendingPurpose::active()->get();
        $sessionData               = session()->get('send_money') ?? [];
        $recipientCountryId        = null;
        $deliveryMethodId          = null;
        $sendingCountries          = Country::active()->sending()->with('conversionRates')->get();
        $receivingCountries        = Country::receivableCountries()->get();

        if ($sessionData) {
            $sendingCountryId   = $sendingCountries->where('id', 'EUR')->first()->id;
            $recipientCountryId = $receivingCountries->where('id', $sessionData['recipient_country'])->first()->id;
            $deliveryMethodId   = $sessionData['delivery_method'];
        } else {
            $ipInfo             = json_decode(json_encode(getIpInfo()), true);
            $countryName        = @implode(',', $ipInfo['country']);
            $sendingCountryId   = /*@$sendingCountries->where('name', $countryName)->first()->id ??*/ @$sendingCountries->where('currency', 'EUR')->first()->id;
            $recipientCountryId = @$receivingCountries->where('currency', 'VEF')->first()->id;
        }

        $todaySendMoney     = SendMoney::whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_COMPLETED])->where('user_id', auth()->id())->whereDate('created_at', now())->sum('base_currency_amount');
        $thisMonthSendMoney = SendMoney::whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_COMPLETED])->where('user_id', auth()->id())->whereMonth('created_at', now()->month)->sum('base_currency_amount');
        $sendingAmount      = @$sessionData['sending_amount'];
        $recipientAmount    = @$sessionData['recipient_amount'];

        
        $info = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $hideBreadcrumb = true;

        
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('method_code')->get();

        session()->forget('send_money');
        return view('admin.send_money.form', compact('gatewayCurrency', 'bancos', 'user', 'pageTitle', 'sources', 'purposes', 'sendingAmount', 'recipientAmount', 'sendingCountryId', 'recipientCountryId', 'sendingCountries', 'receivingCountries', 'deliveryMethodId', 'todaySendMoney', 'thisMonthSendMoney', 'mobileCode', 'countries', 'hideBreadcrumb'));
    }
    
    /*
     * Initially save the send-money data
     */
    public function save(Request $request) {
        $rules =  [
            'sending_amount'    => 'required|numeric|gt:0',
            'sending_country'   => 'required|gt:0',
            'recipient_country' => 'required|gt:0',
            'payment_type'      => 'required|in:1,2',
            'source_of_funds'   => 'required|gt:0',
            'sending_purpose'   => 'required|gt:0',
            'recipient'         => 'required|array',
            // 'recipient.*'       => 'required|string',
            // 'delivery_method'   => 'required|numeric',
            // 'service'           => 'nullable|required_unless:delivery_method,0|integer'
        ];

        $messages = [
            'recipient.amount.required'    => 'Recipient amount field is required',
            //'recipient.mobile.required'  => 'Recipient mobile number field is required',
            //'recipient.address.required' => 'Recipient address field is required',
            //'service.required_unless'    => 'Service field is required if delivery method is not an agent'
        ];

        $serviceFormData = null;

        
        if(!$request->has('por_cobrar'))
        {
            // $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            //     $gate->where('status', Status::ENABLE);
            // })->where('currency', $request->currency)->first();
            $gatewayCurrency = GatewayCurrency::find($request->gateway);
            if($gatewayCurrency)
            {
                $formProcessorGate    = new FormProcessor();
                $gateway         = $gatewayCurrency->method;
                $formDataGate        = $gateway->form->form_data;
                $validationRuleGate   = $formProcessorGate->valueValidation($formDataGate);
                $rules            = array_merge($rules, $validationRuleGate);
            }
        }

        $request->validate($rules, $messages);
        // if ($request->service) {
        //     $service          = Service::findOrFail($request->service);
        //     $form             = Form::where('act', 'service_form')->findOrFail($service->form_id);
        //     $formData         = $form->form_data;
        //     $formProcessor    = new FormProcessor();
        //     $validationRule   = $formProcessor->valueValidation($formData);
        //     $rules            = array_merge($rules, $validationRule);
        //     $request->validate($rules, $messages);
        //     $serviceFormData  = $formProcessor->processFormData($request, $formData);
        // } else {
        //     $request->validate($rules, $messages);
        // }

        // $bank = Bank::findOrFail($request->banco);
        // $currency_selected = Country::findOrFail($request->sending_country);
        // if($bank->currency != $currency_selected->currency)
        // {
        //     throw ValidationException::withMessages(['error' => 'Bank selected is not match with sending currency']);
        // }

        
        $count_day = CombinedDeposit::where('user_id', $request->user_id)->whereDate('dt_by_day', now())->count();
        $combined_order = new CombinedDeposit;
        $combined_order->user_id = $request->user_id;
        $combined_order->amount = $request->sending_amount;
        $combined_order->dt_by_day = now();
        $combined_order->count_by_day = $count_day + 1;
        $combined_order->save();

        $user = User::find($request->user_id);
        $sending_currency ='';
        $payment = new ProcessSendMoney($request);
        foreach($request->recipient as $_recipient)
        {
            $or_amount = $_recipient['amount'];//$payment->revertAmountVariables($_recipient['amount']);
            $recipient = Recipient::find($_recipient['id']);
            $request->merge(
                [
                    'sending_amount' => $or_amount,
                    'combined_id' => $combined_order->id,
                    'delivery_method' => $recipient->country_delivery_method_id,
                    'service' => $recipient->service_id
                ]);
            $payment->setAmountVariables($request, $or_amount);

            $payment->user       = $user;
            $payment->columnName = 'user_id';
            $sendMoney           = $payment->createSendMoney($request, $recipient->form_data, $recipient);
            
            session()->put('combined_id', $combined_order->id);
            // session()->put('Track', $sendMoney->trx);
            $sendMoney->created_by_admin    = 1;
            $sendMoney->save();

            if(!$request->has('por_cobrar'))
            {
                //if(is_null(\App\Models\GatewayCurrency::where('currency', $sendMoney->sending_currency)->first()))
                if(is_null(\App\Models\GatewayCurrency::find($request->gateway)))
                {
                    $notify[] = ['error', 'Pasarela manual no configurada para la moneda seleccionada'];
                    return back()->withNotify($notify);
                }
            }else{
                $cuentas_cobrar = new CuentasCobrar;
                $cuentas_cobrar->user_id = auth('admin')->user()->id;
                $cuentas_cobrar->send_money_id = $sendMoney->id;
                $cuentas_cobrar->status = 'pending';
                $cuentas_cobrar->fecha_vencimiento = $request->fecha_vencimiento;
                $cuentas_cobrar->concepto = $request->description_cc;

                $cuentas_cobrar->save();

                $sendMoney->payment_status = Status::PAYMENT_SUCCESS;
                $sendMoney->status = Status::SEND_MONEY_PENDING;
                $sendMoney->save();
            }

            $sending_currency = $sendMoney->sending_currency;
        }
    
        if(!$request->has('por_cobrar'))
        {
            $req = new Request;
            $req->merge(
                [
                    'user_id' => $request->user_id,
                    'amount' => $request->sending_amount,
                    'gateway' => \App\Models\GatewayCurrency::find($request->gateway)->method_code,
                    'currency' => $sending_currency,
                    'send_adm' => true
                ]
                );
            $request->merge(['send_adm' => true]);

            $deposit_id = (new \App\Http\Controllers\Gateway\PaymentController)->depositInsert($req);
            (new \App\Http\Controllers\Gateway\PaymentController)->manualDepositUpdate($request);
        }
        // (new DepositController)->approve($request, $deposit_id);

        // $bank_process = new \App\Http\Controllers\Admin\BankController;
        // $req = new Request;
        // $req->merge(
        //     [
        //         'bankId' => $request->banco,
        //         'send_money_id' => $sendMoney->id,
        //         'amount_currency_local' => $sendMoney->sending_amount,
        //         'amount_currency_convert' => $sendMoney->recipient_amount
        //     ]
        // );
        // $bank_process->ingresoBanco($req);

        
        // $banco = Banco::find($request->banco);
        // $banco->balance += $request->sending_amount;
        // $banco->save();

        // $log_banco = new LogBanco;
        // $log_banco->banco_id = $request->banco;
        // $log_banco->deposit_id = $deposit_id;
        // $log_banco->operation_type = 1;
        // $log_banco->save();
        $roles = explode(',', \App\Models\Roles::find(auth('admin')->user()->role_id)->permissions);

        if(!$request->has('por_cobrar'))
        {
            if(in_array('1', $roles) || in_array('8', $roles))
                return to_route('admin.payment.pending');
            elseif(in_array('1', $roles) || in_array('4', $roles))
                return to_route('admin.send.money.pending');
            else 
                return to_route('admin.send.money.send_money_form');
        }else{
            return to_route('admin.cuentas.index.cobrar');
        }
    }

    public function verifyCoinsSent()
    {
        //->where('coins_sent <> 1')
        $sends = SendMoney::whereNotNull('proccesId')
            ->where('status', Status::SEND_MONEY_INITIATED)
            ->where('payment_status', Status::PAYMENT_INITIATE)
            ->with('deposit')->get();

        foreach($sends as $itm)
        {
            $response = $this->consultaPago($itm->proccesId);
            if($response == 'coins_sent')
            {
                $itm->coins_sent = 1;
                
                // $bank = Bank::where('account', '0x041cd6b859dae2c5fc93bde47a89d2529042013c')->first();
                // if(is_null($bank))
                // {
                //     $bank = new Bank;
                    
                //     $bank->name = 'USDT';
                //     $bank->bank_type = 'RECIBE';
                //     $bank->account = '0x041cd6b859dae2c5fc93bde47a89d2529042013c';
                //     $bank->currency = 'USDT';
                //     $bank->balance = 0;
                //     $bank->average_rate = 0;
                //     $bank->save();

                //     $obj = array(
                //         'bank_id' => $bank->id,
                //         'date' => Carbon::now(),
                //         'saldo_inicial' => $bank->balance,
                //     );
                //     CierreDiario::create($obj);
                // }

                // $bank_process = new \App\Http\Controllers\Admin\BankController;
                // $req = new Request;
                // $req->merge(
                //     [
                //         'bankId' => $bank->id,
                //         'send_money_id' => $itm->id,
                //         'amount_currency_local' => $itm->sending_amount,
                //         'amount_currency_convert' => $itm->recipient_amount
                //     ]
                // );
                // $bank_process->ingresoBanco($req);

                $itm->status         = Status::SEND_MONEY_PENDING;
                $itm->payment_status = Status::PAYMENT_SUCCESS;
                $itm->save();
            }else if($response == "payment_rejected"){
                
                if ($itm->status == Status::SEND_MONEY_INITIATED) {
                    $itm->status         = Status::SEND_MONEY_INITIATED;
                    $itm->payment_status = Status::PAYMENT_REJECT;
                    $itm->admin_feedback = "Pago rechazado por Cryptopocket";
                    $itm->save();
                }
                notify($itm->user, 'PAYMENT_REJECT', [
                    'trx'                => $itm->trx,
                    'sending_country'    => @$itm->sendingCountry->name,
                    'sending_amount'     => showAmount($itm->sending_amount),
                    'sending_currency'   => $itm->sending_currency,
                    'recipient_country'  => $itm->recipientCountry->name,
                    'recipient_amount'   => showAmount($itm->recipient_amount),
                    'recipient_currency' => $itm->recipient_currency,
                    'message'            => "Pago rechazado por Cryptopocket",
                ]);
            }
            else
                $itm->coins_sent = 0;

            $itm->save();
        }

    }

    private function consultaPago($paymentId){
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pasarela.cryptopocket.io/get-payment/' . $paymentId,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,  
        CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        
        $log = new \App\Models\LogKyc;
        $log->params = 'https://pasarela.cryptopocket.io/get-payment/' . $paymentId;
        $log->response = $response;
        $log->save();

        $response = json_decode($response);
        //dd($response);
        curl_close($curl);
        if(isset($response->status) && $response->status == 'success')
        {
            if($response->process->status == 'coins_sent')
            {
                return "coins_sent";
            }else if($response->process->status == 'payment_rejected')
            {
                return "payment_rejected";
            }
        }
        return "no_response";
    }

    public function consultaUsuarios(Request $request)
    {
        $search = $request->q;
        //$search = str_replace(' ', '', $search);
        $search = str_replace('+', '', $search);
        // $searchs = explode(' ', $search);

        // $qry = array();
        // foreach($searchs as $itm)
        // {
        //     $qry[] = ['username', 'like', "%$itm%"];
        //     $qry[] = ['email', 'like', "%$itm%"];
        //     $qry[] = ['mobile', 'like', "%$itm%"];
        // }

        $query = \App\Models\User::where('status', true)
                    ->where(function ($query) use ($search) {
                        $item = explode(' ', $search);

                        //foreach (explode(' ', $search) as $item) {
                            if(count($item) > 1){
                                $query->where('firstname', 'LIKE', "%{$item[0]}%")
                                ->where('username', 'LIKE', "%{$item[1]}%");
                            }else{
                                $query->orWhere('firstname', 'LIKE', "%{$item[0]}%")
                                    ->orWhere('username', 'LIKE', "%{$item[0]}%")
                                    ->orWhere('email', 'LIKE', "%{$item[0]}%")
                                    ->orWhere('mobile', 'LIKE', "%{$item[0]}%");
                            }
                        //}
                    })
                    //->where('firstname', 'like', "%$search%")
                    //->orWhere('username', 'like', "%$search%")
                    //->orWhere('email', 'like', "%$search%")
                    //->orWhere('mobile', 'like', "%$search%")
                    ->select('firstname', 'email', 'mobile', 'lastname', 'id')->orderBy('firstname')->take(10)->get();//->pluck('firstname', 'id');

        $users = array();
        foreach($query as $itm)
        {
            $users[] = array(
                'id' => $itm->id,
                'text' => $itm->firstname . ' ' . $itm->lastname . ' (' . $itm->email . ' - ' . $itm->mobile . ')',
            );
        }

        return response()->json($users);
    }

    public function verifyPendingKyc()
    {
        $users = User::where('kv', 2)->whereNull('kyc_data')->get();

        foreach($users as $user)
        {
            if(!is_null($user->video_id))
            {
                $response = $this->consultaUser($user->email, $user->video_id);
                        
                if(isset($response->status) && $response->status == 'success' && $response->kyc_status == 'valid'){
                    
                    $user->kv = 1;
                    $user->save();
                }else if(isset($response->status) && $response->status == 'success' && $response->kyc_status == 'fail'){
                    
                    $user->kv = 0;
                    $user->past_video_id = $user->video_id;
                    $user->video_id = null;
                    $user->save();
                }else if(isset($response->status) && $response->status == 'error'){ 
                    $user->kv = 0;
                    $user->past_video_id = $user->video_id;
                    $user->video_id = null;
                    $user->save();
                    
                }
            }else{
                $user->kv = 0;
                $user->past_video_id = $user->video_id;
                $user->video_id = null;
                $user->save();
            }
        }
        echo "ok";
    }

    private function consultaUser($email, $videoId){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pasarela.cryptopocket.io/check-kyc',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "email": "' . $email . '",
            "videoId":"' . $videoId . '" 
        } 
        ',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);
        $log = new \App\Models\LogKyc;
        $log->params = 'https://pasarela.cryptopocket.io/check-kyc|{
            "email": "' . $email . '",
            "videoId":"' . $videoId . '" 
        } 
        ';
        $log->response = $response;
        $log->save();

        $response = json_decode($response);

        curl_close($curl);
        return $response;
    }

    public function consultaViser(Request $request){
        
        // $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
        //     $gate->where('status', Status::ENABLE);
        // })->where('currency', $request->currency)->first();
        $gatewayCurrency = GatewayCurrency::find($request->gateway_id);

        if(!$gatewayCurrency)
        {
            return '<span class="badge badge--danger">No existe flujo para aceptar pago con esta moneda</span>';
        }

        $gateway         = $gatewayCurrency->method;
        $formData        = $gateway->form->form_data;
        $currency        = $request->currency;

        return view('components.viser-form', compact('formData', 'currency'))->render();
    }
    
    public function addReceipt(Request $request)
    {
        if($request->has('recipient_id') && !empty($request->recipient_id) && !is_null($request->recipient_id))
        {
            $request->merge(['new_recipient_id' => $request->recipient_id]);
            return $this->editRecipient($request, false);
        }

        $rules =  [
            'recipient'         => 'required|array|min:3',
            'recipient.*'       => 'required|string',
            'delivery_method'   => 'required|numeric',
            'service'           => 'nullable|required_unless:delivery_method,0|integer'
        ];

        $messages = [
            'recipient.name.required'    => 'Recipient name field is required',
            'recipient.mobile.required'  => 'Recipient mobile number field is required',
            //'recipient.address.required' => 'Recipient address field is required',
            'service.required_unless'    => 'Service field is required if delivery method is not an agent'
        ];

        $email_str = (empty($request->recipient['email']) ? (strtolower(str_replace(' ', '_', $request->recipient['name'])) . '@djandresromay.es') : $request->recipient['email']);
        $mobile = (empty($request->recipient['mobile']) ? '9999999999' : $request->recipient['mobile']);
        $recipient = $request->recipient;
        $recipient['email'] = $email_str;
        $recipient['mobile'] = $mobile;
        $request->merge(['recipient' => $recipient]);

        if ($request->service) {
            $service          = Service::findOrFail($request->service);
            $form             = Form::where('act', 'service_form')->findOrFail($service->form_id);
            $formData         = $form->form_data;
            $formProcessor    = new FormProcessor();
            $validationRule   = $formProcessor->valueValidation($formData);
            $rules            = array_merge($rules, $validationRule);
            $request->validate($rules, $messages);
            $serviceFormData  = $formProcessor->processFormData($request, $formData);
        } 
        $user_recipient = Recipient::where('user_id', $request->user_id)->where('name', $request->recipient['name'])
        // $user_recipient = Recipient::where('user_id', $request->user_id)
        //         ->where(function ($query) use($request) {
        //             $query->where('mobile', $request->recipient['mobile'])
        //                 ->orWhere('email', $request->recipient['email']);
        //         })
                ->first();

        if(is_null($user_recipient))
        {
            $user_recipient = new Recipient;
            $user_recipient->user_id = $request->user_id;
        }
        $user_recipient->country_delivery_method_id = $request->delivery_method;
        $user_recipient->service_id = $request->service;
        //$user_recipient->sending_currency = $request->sending_country;
        //$user_recipient->recipient_currency = $request->recipient_country;

        $user_recipient->name = $request->recipient['name'];
        $user_recipient->mobile = $request->recipient['mobile'];
        $user_recipient->email = $request->recipient['email'];
        $user_recipient->address = $request->recipient['address'];

        $user_recipient->form_data = json_encode($serviceFormData);
        $user_recipient->save();

        return response()->json(['message' => 'Nuevo beneficiario agregado.', 'id' => $user_recipient->id]);
    }

    public function viewCardBenef($user_id, Request $request){
        $ind_key = $request->ind_key;
        return view($this->activeTemplate . 'user.send_money.view_card_benef', compact('ind_key', 'user_id'))->render();
    }

    public function consultaDestinatarios(Request $request)
    {
        $id = $request->combined_id;
        $send_moneys = SendMoney::where('combined_id', $id)->get();
        $send_money_ = SendMoney::where('combined_id', $id)->first();
        $html = '';
        
        foreach($send_moneys as $key => $send_money)
        {
            $_rec = $send_money->recipient;
            $recipient = \App\Models\Recipient::
                                        /*where('user_id', $send_money->user_id)
                                        ->where('name', $_rec->name)
                                        ->*/where('email', $_rec->email)->first();
            if(!is_null($recipient))
            {
            $html .=  '' .
                '<div class="mb-3">' .
                    '<label class="btn-selected__label flex-grow-1 w-100" data-value="1" for="recipient_' . $recipient->id . '">' .
                        '<input class="btn-selected__input" id="recipient_' . $recipient->id . '" name="user_recipient" required type="radio" data-sendmoneyid="'.$send_money->id.'" value="' . $recipient->id . '">' .
                        '<span class="btn-selected btn-selected--primary" style="justify-content: left; text-align: left;">' .
                            '<div class="icon icon--lg icon--circle">' .
                                '<i class="fas fa-user"></i>' .
                            '</div>' .
                            '<span class="btn-selected__text ">' .
                                $recipient->name .
                                /*'<br>' .
                                '<span style="font-size: 10px;">' . $recipient->email . ' (' . $recipient->mobile . ')</span>' .*/
                            '</span>' .
                        '</span>' .
                    '</label>' .
                '</div>';
            }
        }
        
        $receivingCountries         = Country::receivableCountries()->get();
        $receivingCountry         = @$receivingCountries->where('currency', $send_money_->recipient_currency)->first();
          
        return response()->json([
            'html' => $html,
            'countryDeliveryMethods' => $receivingCountry->countryDeliveryMethods,
            'receivingCountryId' => $send_money_->recipient_country_id
        ]);
    }


    public function consultaDestinatariosForm(Request $request)
    {
        $recipient = \App\Models\Recipient::where('id', $request->recipient_id)->first();

        $obj_form = array();
        foreach (json_decode($recipient->form_data) as $key => $itm)
        {
            $obj_form[titleToKey($itm->name)] = $itm->type .'|'. $itm->value;
        }
        $obj = array(
            'sending_currency'              => $recipient->sending_currency,
            'recipient_currency'            => $recipient->recipient_currency,
            'deliveryMethod'                => $recipient->country_delivery_method_id,
            'service_id'                    => $recipient->service_id,
            'name'                          => $recipient->name,
            'mobile'                        => $recipient->mobile,
            'email'                         => $recipient->email,
            'address'                       => $recipient->address
        );
        $resultado = array_merge($obj, $obj_form);

        return response()->json($resultado);
    }

    public function editRecipient(Request $request, $updt_sndM = true)
    {
        //dd($request);
        $rules =  [
            'recipient_id'      => 'required|integer',
            //'recipient'         => 'required|array|min:3',
            //'recipient.*'       => 'required|string',
            'delivery_method'   => 'required|numeric',
            'service'           => 'nullable|required_unless:delivery_method,0|integer'
        ];

        $messages = [
            //'recipient.name.required'    => 'Recipient name field is required',
            //'recipient.mobile.required'  => 'Recipient mobile number field is required',
            //'recipient.address.required' => 'Recipient address field is required',
            'service.required_unless'    => 'Service field is required if delivery method is not an agent'
        ];
        
        if ($request->service) {
            $service          = Service::findOrFail($request->service);
            $form             = Form::where('act', 'service_form')->findOrFail($service->form_id);
            $formData         = $form->form_data;
            $formProcessor    = new FormProcessor();
            $validationRule   = $formProcessor->valueValidation($formData);
            $rules            = array_merge($rules, $validationRule);
            //dd($rules);
            $request->validate($rules, $messages);
            $serviceFormData  = $formProcessor->processFormData($request, $formData);
        } 
        if($updt_sndM) $sendMoney = SendMoney::findOrFail($request->sendmoneyid);
        // $user_recipient = Recipient::where('user_id', $request->user_id)
        //         ->where(function ($query) use($request) {
        //             $query->where('mobile', $request->recipient['mobile'])
        //                 ->orWhere('email', $request->recipient['email']);
        //         })->first();
        $user_recipient = Recipient::find($request->new_recipient_id);
        // if(is_null($user_recipient))
        // {
        //     $user_recipient = new Recipient;
        //     $user_recipient->user_id = $request->user_id;
        // }
        $user_recipient->country_delivery_method_id = $request->delivery_method;
        $user_recipient->service_id = $request->service;
        //$user_recipient->sending_currency = $request->sending_country;
        //$user_recipient->recipient_currency = $request->recipient_country;

        $user_recipient->name = $request->recipient['name'];
        $user_recipient->mobile = $request->recipient['mobile'];
        //$user_recipient->email = $request->recipient['email'];
        $user_recipient->address = '';

        $user_recipient->form_data = json_encode($serviceFormData);
        $user_recipient->save();


        if($updt_sndM){
            $recipient['id']         = $user_recipient->id;
            $recipient['name']       = $user_recipient->name;
            $recipient['mobile']     = @$user_recipient->mobile;
            $recipient['email']      = @$user_recipient->email;
            $recipient['address']    = @$user_recipient->address;

            $sendMoney->service_form_data          = json_decode($user_recipient->form_data);
            $sendMoney->recipient                  = $recipient;
            $sendMoney->save();
        }
        //return response()->json(['message' => 'Nuevo beneficiario agregado.', 'id' => $user_recipient->id]);
        
        $notify[] = ['success', '¡El destinatario se editó correctamente!'];
        return back()->withNotify($notify);
    }

    public function createPdf($sendMoneyId){
        
        $sendMoney = SendMoney::findOrFail($sendMoneyId);
        
        $pdf = PDF::loadView('admin.send_money.invoice', compact('sendMoney'));
        return $pdf->download(str_pad($sendMoney->id, 5, "0", STR_PAD_LEFT) . '.pdf');
    }

    public function logNotify(){
        
        $logs = LogNotify::where('status', 0)->get();

        foreach($logs as $itm)
        {
            $user = User::find($itm->user_id);
            
            $notify = new Notify(is_null($itm->send_via) ? null : json_decode($itm->send_via));
            $notify->templateName = $itm->template_name;
            $notify->shortCodes = json_decode($itm->short_codes);
            $notify->user = $user;
            $notify->createLog = $itm->create_log;
            $notify->userColumn = isset($user->id) ? $user->getForeignKey() : 'user_id';
            $response = $notify->send();

            $itm->status = $response;
            $itm->save();
        }
    }

    public function setVisibleSendMoney($sendMoneyId, $ind)
    {
        $sendMoney = SendMoney::find($sendMoneyId);

        $sendMoney->visible = $ind;
        $sendMoney->save();

        $notify[] = ['success', 'Visibilidad del envío actualizado correctamente'];
        return back()->withNotify($notify);
    }
}

