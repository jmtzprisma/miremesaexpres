<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\ProcessSendMoney;
use App\Models\CombinedDeposit;
use App\Models\Country;
use App\Models\CountryDeliveryMethod;
use App\Models\Service;
use App\Models\Recipient;
use App\Models\GatewayCurrency;
use App\Models\SendingPurpose;
use App\Models\SendMoney;
use App\Models\Form;
use App\Models\SourceOfFund;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SendMoneyController extends Controller {

    /*
     * Return Send Money View Page
     */
    public function sendMoney() {
        $pageTitle                 = 'Send Money';

        $sources                   = SourceOfFund::active()->get();
        $purposes                  = SendingPurpose::active()->get();
        $sessionData               = session()->get('send_money') ?? [];
        $recipientCountryId        = null;
        $deliveryMethodId          = null;
        $sendingCountries          = Country::active()->sending()->with('conversionRates')->get();
        $receivingCountries        = Country::receivableCountries()->get();

        if ($sessionData) {
            $sendingCountryId   = $sendingCountries->where('id', $sessionData['sending_country'])->first()->id;
            $recipientCountryId = $receivingCountries->where('id', $sessionData['recipient_country'])->first()->id;
            $deliveryMethodId   = $sessionData['delivery_method'];
        } else {
            $ipInfo             = json_decode(json_encode(getIpInfo()), true);
            $countryName        = @implode(',', $ipInfo['country']);
            $sendingCountryId   = @$sendingCountries->where('name', $countryName)->first()->id ?? @$sendingCountries->where('currency', 'EUR')->first()->id;
            $recipientCountryId = @$receivingCountries->where('currency', 'VEF')->first()->id;
        }

        $todaySendMoney     = SendMoney::whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_COMPLETED])->where('user_id', auth()->id())->whereDate('created_at', now())->sum('base_currency_amount');
        $thisMonthSendMoney = SendMoney::whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_COMPLETED])->where('user_id', auth()->id())->whereMonth('created_at', now()->month)->sum('base_currency_amount');
        $sendingAmount      = @$sessionData['sending_amount'];
        $recipientAmount    = @$sessionData['recipient_amount'];

        session()->forget('send_money');
        return view($this->activeTemplate . 'user.send_money.form', compact('pageTitle', 'sources', 'purposes', 'sendingAmount', 'recipientAmount', 'sendingCountryId', 'recipientCountryId', 'sendingCountries', 'receivingCountries', 'deliveryMethodId', 'todaySendMoney', 'thisMonthSendMoney'));
    }

    /*
     * Initially save the send-money data
     */
    public function save(Request $request) {
        $rules =  [
            'sending_amount'    => 'required|numeric|gt:0',
            'sending_country'   => 'required|gt:0',
            'recipient_country' => 'required|gt:0',
            'payment_type'      => 'required|in:1,2,3,4',
            'source_of_funds'   => 'required|gt:0',
            'sending_purpose'   => 'required|gt:0',
            'recipient'         => 'required|array',
        ];

        $messages = [
            'recipient.amount.required'    => 'Recipient amount field is required',
        ];
        $request->validate($rules, $messages);

        $count_day = CombinedDeposit::where('user_id', auth()->user()->id)->whereDate('dt_by_day', now())->count();
        $combined_order = new CombinedDeposit;
        $combined_order->user_id = auth()->user()->id;
        $combined_order->amount = $request->sending_amount;
        $combined_order->dt_by_day = now();
        $combined_order->count_by_day = $count_day + 1;
        $combined_order->save();

        $user    = auth()->user();
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
            if ($request->payment_type == 1 && $payment->amountWithCharge > $user->balance) {
                $notify[] = ['error', 'Saldo insuficiente'];
                return back()->withNotify($notify);
            }
            
            $payment->user       = $user;
            $payment->columnName = 'user_id';
            $sendMoney           = $payment->createSendMoney($request, $recipient->form_data, $recipient);
            
            if ($request->payment_type == 1) {
                $payment->createTransaction();
                ProcessSendMoney::updateSendMoney($sendMoney, $user);
            } else if ($request->payment_type == 3 || $request->payment_type == 4) {
                $payment->createTransaction();
            }
        }

        if ($request->payment_type == 1) {
            $notify[] = ['success', 'Enviar solicitud de dinero enviada exitosamente'];
            return to_route('user.send.money.history')->withNotify($notify);
        } else if ($request->payment_type == 3 || $request->payment_type == 4) {
            session()->put('combined_id', $combined_order->id);
            session()->put('amount', $request->sending_amount);
            session()->put('payment_type_cryptopocket', $request->payment_type);
            session()->put('email_cryptopocket', $user->email);
            if($user->kv == 0){
                $new_pwd = $this->generate_string(20);
                if($this->creaUsuario($user, $new_pwd))
                {
                    $user->kyc = $new_pwd;
                    $user->save();

                    $token = $this->nuevoToken($user->email, $user->kyc);
                    if($token == 'login incorrecto')
                    {
                        $new_pwd = $this->generate_string(20);
                        $user->kyc = $new_pwd;
                        $user->save();
        
                        
                        $identity = $user->identification;
                        if(empty($identity) || is_null($identity))
                        {
                            $notify[] = ['error', 'Complete sus datos y vuelva a intentarlo por favor.'];
                            return to_route('user.profile.setting')->withNotify($notify);
                        }else{
                            $this->cambiaPwd($user->email, $user->identification, $user->kyc);
                        }
                        
                        $token = $this->nuevoToken($user->email, $user->kyc);
                    }
                    session()->put('token_cryptopocket', $token);
                    $notify[] = ['info', 'Por favor valide su cuenta por video'];
                    return to_route('user.send.money.video_valid')->withNotify($notify);
                }
            }else{

                if(!$user->video_id){
                    $token = $this->nuevoToken($user->email, $user->kyc);
                    if($token == 'login incorrecto')
                    {
                        $new_pwd = $this->generate_string(20);
                        $user->kyc = $new_pwd;
                        $user->save();

                        
                        $identity = $user->identification;
                        if(empty($identity) || is_null($identity))
                        {
                            $notify[] = ['error', 'Complete sus datos y vuelva a intentarlo por favor.'];
                            return to_route('user.profile.setting')->withNotify($notify);
                        }else{
                            $response_cpwd = $this->cambiaPwd($user->email, $user->identification, $user->kyc);
                            if($response_cpwd == 'User not found') $this->creaUsuario($user, $new_pwd);
                        }
                        
                        $token = $this->nuevoToken($user->email, $user->kyc);
                    }
                    session()->put('token_cryptopocket', $token);
                    $notify[] = ['info', 'Por favor valide su cuenta por video'];
                    return to_route('user.send.money.video_valid')->withNotify($notify);
                }else{
                    $req = new Request;
                    $req->merge(
                            [
                                'name_card_form'=> '',//$request->name_card_form,
                                'payment_type'  => $request->payment_type,
                                'email'         => $user->email,
                                'video_id'      => $user->video_id,
                                'amount'        => $combined_order->amount + $combined_order->charge,
                            ]
                        );

                    return $this->procesaPago($req, $combined_order->id);
                }
            }
        }

        session()->put('combined_id', $combined_order->id);
        

        return to_route('user.send.money.pay.now');
    }

    public function esperaConsulta(){

    }
    
    public function saveOnlyKyc(Request $request) {
        $user    = auth()->user();
        $user->kv = 2;
        $user->video_id = $request->video_id;
        $user->save();

        return redirect(route('user.send.money.now'));
    }

    public function getAmount($amount) 
    { 
        if (empty($amount)) { 
            return '000'; 
        } 
    
        $amount = preg_replace('/[^0-9,\.]/', '', $amount); 
    
        // Remove pretty number format: 1.234,56 > 1234,56 
        if (preg_match('/[\d]+\.[\d]+,[\d]+/', $amount)) { 
            $amount = str_replace('.', '', $amount); 
        } 
    
        // Remove pretty number format: 1,234.56 > 1234.56 
        if (preg_match('/[\d]+,[\d]+\.[\d]+/', $amount)) { 
            $amount = str_replace(',', '', $amount); 
        } 
    
        // Remove comma as decimal separator: 1234,56 > 1234.56 
        if (strpos($amount, ',') !== false) { 
            $amount = str_replace(',', '.', $amount); 
        } 
    
        $amount = floatval($amount); 
    
        // Truncate float from second decimal (not rounded): 1.119 > 1.11 
        if (($point = strpos($amount, '.')) !== false) { 
            $amount = substr($amount, 0, $point + 1 + 2); 
        } 
    
        // Set as Redsys valid amount value: 12,34 = 1234 
        return ($amount * 100); 
    } 

    public function procesaPago(Request $request, $combinedId) {
        $user    = auth()->user();
        $user->video_id = $request->video_id;
        $user->save();

        $response = $this->consultaUser($request->email, $request->video_id);

        if(isset($response->status) && $response->status == 'success' && $response->kyc_status == 'valid'){
            $pageTitle = 'Pagar';
        $responseTramitaPago = $this->tramitaPago($request->email, $request->amount, $request->payment_type/*, $request->name_card_form*/);
            $urlform = $responseTramitaPago->url_payment;
            $proccesId = $responseTramitaPago->proccesId;
            
            foreach(SendMoney::where('combined_id', $combinedId)->get() as $sendMoney){
                $sendMoney->proccesId = $proccesId;
                $sendMoney->save();
            }
            
            if($request->payment_type == 3)
                return view($this->activeTemplate . 'user.send_money.continue_payment', compact('urlform', 'pageTitle', 'proccesId'));
            else return redirect($urlform);
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
        }else{
            $email = $request->email;
            $amount = $request->amount;
            $pageTitle = 'Pagar';

            return view($this->activeTemplate . 'user.send_money.waiting_response', compact('email', 'amount', 'pageTitle'));
        }
    }
    
    public function consultaPago($paymentId, $ind = false){
        
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
        // CURLOPT_POSTFIELDS =>'{
        //     "email": "' . $email . '",
        //     "amount":"' . $amount . '", 
        //     "wallet":"TNrKVLuhEUHG53L7jc7D3QDAJcYam84ETM" 
        // } 
        // ',
        // CURLOPT_HTTPHEADER => array(
        //     'Content-Type: application/json'
        // ),
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
            if($response->process->status == 'payment_received')
            {
                if($ind)
                {
                    return true;
                }else{
                    echo route('user.send.money.history');
                    //echo route('user.send.money.success_pay', $paymentId);
                    return;
                }
            }else if($response->process->status != 'started')
            {
                if($ind)
                {
                    return false;
                }else{
                    echo route('user.send.money.history');
                    //echo 'reload';
                    return;
                }
            }

        }

        if($ind)
        {
            return false;
        }else{
            echo false;
            return;
        }
    }

    private function tramitaPago($email, $amount, $payment_type /*, $card_holder*/){
        $general   = gs();

        $paymenttype = $payment_type == '3' ? 'card' : 'psd2';
        $url_return = $payment_type == '3' ? route('user.redirect_response') : route('user.send.money.history');
        /*print_r('{
            "payment_type": "' . $paymenttype . '",
            "email": "' . $email . '",
            "amount":"' . $amount . '", 
            "wallet":"0x041cd6b859dae2c5fc93bde47a89d2529042013c",
            "url": "'. route('user.redirect_response') .'"
        }');*/
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pasarela.cryptopocket.io/create-payment-tlc',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',

        //"card_holder": "' . $card_holder . '",
        CURLOPT_POSTFIELDS =>'{
            "payment_type": "' . $paymenttype . '",
            "email": "' . $email . '",
            "amount":"' . $amount . '", 
            "wallet":"'. $general->wallet .'",
            "url": "'. $url_return .'"
        } 
        ',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);
        
        $log = new \App\Models\LogKyc;
        $log->params = 'https://pasarela.cryptopocket.io/create-payment-tlc|{
            "payment_type": "' . $paymenttype . '",
            "email": "' . $email . '",
            "amount":"' . $amount . '", 
            "wallet":"'. $general->wallet .'",
            "url": "'. $url_return .'"
        } 
        ';
        $log->response = $response;
        $log->save();

        $response = json_decode($response);
        //dd($response);
        curl_close($curl);
        return isset($response->status) ? $response : null;
    }

    private function generate_string($strength = 16) {
        $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
        return $random_string;
    }

    public function saveVideoId(Request $request){
        $user    = auth()->user();
        //dd($user);
        $user->video_id = $request->videoId;
        $user->save();
    }
    public function successPay($proccesId){
        
        $sendMoney = SendMoney::where('proccesId', $proccesId)->first();
        
        $pago = $this->consultaPago($sendMoney->proccesId, true);

        if($pago){
            $combined       = session()->get('combined_id');
            foreach(SendMoney::where('combined_id', $combined)->get() as $sndMny)
            {
                $trx       = $sndMny->trx;
                $sendMoney = SendMoney::filterUser()->with('sendingCountry:id,rate')->where('trx', $trx)->first();
                if (!$sendMoney) {
                    $notify[] = ['error', 'La sesión no es válida.'];
                    return to_route('user.home')->withNotify($notify);
                }

                $user    = auth()->user();
                
                ProcessSendMoney::updateSendMoney($sendMoney, $user);
            }

            $notify[] = ['success', 'Pago efectuado correctamente'];
            return to_route('user.send.money.history')->withNotify($notify);
        }
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

    private function nuevoToken($email, $password){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pasarela.cryptopocket.io/kyc-token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "email": "' . $email . '",
            "password":"' . $password . '" 
        } 
        ',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        $log = new \App\Models\LogKyc;
        $log->params = 'https://pasarela.cryptopocket.io/kyc-token|{
            "email": "' . $email . '",
            "password":"' . $password . '" 
        } 
        ';
        $log->response = $response;
        $log->save();

        $response = json_decode($response);
        //dd($response);
        
        curl_close($curl);
        return isset($response->status) ? $response->message : $response->kycToken->authorization;
    }

    private function creaUsuario($user, $new_pwd){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pasarela.cryptopocket.io/kyc-tlc',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "email": "'. $user->email .'",
            "name": "'. $user->firstname .'",
            "last_name": "'. $user->lastname .'",
            "last_name2": "",
            "phone": "'. $user->mobile .'",
            "country": "' . @$user->address->country .  '",
            "address": "' . @$user->address->address .  '",
            "identification_number": "' . $user->identification . '",
            "password":"'. $new_pwd .'" 
        } 
        ',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);
        $log = new \App\Models\LogKyc;
        $log->params = 'https://pasarela.cryptopocket.io/kyc-tlc|{
            "email": "'. $user->email .'",
            "name": "'. $user->firstname .'",
            "last_name": "'. $user->lastname .'",
            "last_name2": "",
            "phone": "'. $user->mobile .'",
            "country": "' . @$user->address->country .  '",
            "address": "' . @$user->address->address .  '",
            "identification_number": "' . $user->identification . '",
            "password":"'. $new_pwd .'" 
        } 
        ';
        $log->response = $response;
        $log->save();
        $response = json_decode($response);
        //dd($response);

        curl_close($curl);

        return $response->status == 'success' ? true : false;
    }

    /*
     * Redirect to payment page
     */
    public function payNow() {
        $pageTitle = 'Pay Money';
        
        $combined       = session()->get('combined_id');
        $base_currency_amount = 0;
        $base_currency_charge = 0;
        $sending_amount = 0;
        $sending_charge = 0;
        foreach(SendMoney::where('combined_id', $combined)->get() as $sndMny)
        {
            $base_currency_amount += $sndMny->base_currency_amount;
            $base_currency_charge += $sndMny->base_currency_charge;
            $sending_amount += $sndMny->sending_amount;
            $sending_charge += $sndMny->sending_charge;
        }

        $sendMoney = SendMoney::where('combined_id', $combined)->filterUser()->with('sendingCountry:id,rate')->first();
        if (!$sendMoney) {
            $notify[] = ['error', 'La sesión no es válida.'];
            return to_route('user.home')->withNotify($notify);
        }
        
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('currency', $sendMoney->sending_currency)->with('method')->orderby('method_code')->get();


        return view($this->activeTemplate . 'user.payment.payment', compact('gatewayCurrency', 'pageTitle', 'sendMoney', 'base_currency_amount', 'base_currency_charge', 'sending_amount', 'sending_charge'));
    }

    /*
     * Redirect to payment page
     */
    public function videoValid($only_kyc = false) {
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('method_code')->get();

        $pageTitle = 'Pay Money';
        if(!session()->has('only_kyc') && !$only_kyc)
        {
            $combined       = session()->get('combined_id');
            $sendMoney = SendMoney::filterUser()->with('sendingCountry:id,rate')->where('combined_id', $combined)->first();
            if (!$sendMoney) {
                $notify[] = ['error', 'La sesión no es válida.'];
                return to_route('user.home')->withNotify($notify);
            }
        }else{session()->put('only_kyc', true);}

        // if(!session()->has('token_cryptopocket') || empty(session()->get('token_cryptopocket')))
        // {
            $user    = auth()->user();
            $new_pwd = $this->generate_string(20);

            if(!$user->kyc){
                $this->creaUsuario($user, $new_pwd);
            }else{
                $response_cpwd = $this->cambiaPwd($user->email, $user->identification, $new_pwd);
                if($response_cpwd == 'User not found') $this->creaUsuario($user, $new_pwd);
            }

            $user->kyc = $new_pwd;
            $user->save();

            $token = $this->nuevoToken($user->email, $user->kyc);
            if($token == 'login incorrecto')
            {
                $new_pwd = $this->generate_string(20);
                $user->kyc = $new_pwd;
                $user->save();

                
                $identity = $user->identification;
                if(empty($identity) || is_null($identity))
                {
                    $notify[] = ['error', 'Complete sus datos y vuelva a intentarlo por favor.'];
                    return to_route('user.profile.setting')->withNotify($notify);
                }else{
                    $this->cambiaPwd($user->email, $user->identification, $user->kyc);
                }
                
                $token = $this->nuevoToken($user->email, $user->kyc);
            }
            session()->put('token_cryptopocket', $token);
        // }else
        // if(session()->get('token_cryptopocket') == 'login incorrecto'){
        //     $user    = auth()->user();
        //     $new_pwd = $this->generate_string(20);
        //     $user->kyc = $new_pwd;
        //     $user->save();
            
        //     $identity = $user->identification;
        //     if(empty($identity) || is_null($identity))
        //     {
        //         $notify[] = ['error', 'Complete sus datos y vuelva a intentarlo por favor.'];
        //         return to_route('user.profile.setting')->withNotify($notify);
        //     }else{
        //         $this->cambiaPwd($user->email, $user->identification, $user->kyc);
        //     }

        //     $token = $this->nuevoToken($user->email, $user->kyc);
        //     session()->put('token_cryptopocket', $token);
        // }

        return view($this->activeTemplate . 'user.send_money.video_valid', compact('gatewayCurrency', 'pageTitle'));
    }

    
    private function cambiaPwd($email, $identity, $password){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pasarela.cryptopocket.io/tlc/change-pwd',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "email": "' . $email . '",
            "identification_number":"' . $identity . '",
            "pwd":"' . $password . '" 
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);
        $log = new \App\Models\LogKyc;
        $log->params = 'https://pasarela.cryptopocket.io/tlc/change-pwd|{
            "email": "' . $email . '",
            "identification_number":"' . $identity . '",
            "pwd":"' . $password . '" 
        } 
        ';
        $log->response = $response;
        $log->save();

        $response = json_decode($response);
        //dd($response);
        
        curl_close($curl);
        return isset($response->status) ? $response->message : $response->kycToken->authorization;
    }

    /*
     * Redirect to payment page to pay for previously initialized send-money
     */
    public function pay(Request $request) {
        $sendMoney = SendMoney::filterUser()->initiated()->findOrFail(decrypt($request->id));
        $user    = auth()->user();

        session()->put('combined_id', $sendMoney->combined_id);
        if ($sendMoney->payment_type == 3 || $sendMoney->payment_type == 4) {
            session()->put('amount', $sendMoney->sending_amount);
            session()->put('payment_type_cryptopocket', $sendMoney->payment_type);
            session()->put('email_cryptopocket', $user->email);
            if(!$user->kyc){
                $new_pwd = $this->generate_string(20);
                if($this->creaUsuario($user, $new_pwd))
                {
                    $user->kyc = $new_pwd;
                    $user->save();

                    $token = $this->nuevoToken($user->email, $user->kyc);
                    if($token == 'login incorrecto')
                    {
                        $new_pwd = $this->generate_string(20);
                        $user->kyc = $new_pwd;
                        $user->save();

                        $identity = $user->identification;
                        if(empty($identity) || is_null($identity))
                        {
                            $notify[] = ['error', 'Complete sus datos y vuelva a intentarlo por favor.'];
                            return to_route('user.profile.setting')->withNotify($notify);
                        }else{
                            $response_cpwd = $this->cambiaPwd($user->email, $user->identification, $user->kyc);
                            if($response_cpwd == 'User not found') $this->creaUsuario($user, $new_pwd);
                        }
                        
                        $token = $this->nuevoToken($user->email, $user->kyc);
                    }
                    session()->put('token_cryptopocket', $token);
                    $notify[] = ['info', 'Por favor valide su cuenta por video'];
                    return to_route('user.send.money.video_valid')->withNotify($notify);
                }
            }else{

                if(!$user->video_id){
                    $token = $this->nuevoToken($user->email, $user->kyc);
                    if($token == 'login incorrecto')
                    {
                        $new_pwd = $this->generate_string(20);
                        $user->kyc = $new_pwd;
                        $user->save();

                        $identity = $user->identification;
                        if(empty($identity) || is_null($identity))
                        {
                            $notify[] = ['error', 'Complete sus datos y vuelva a intentarlo por favor.'];
                            return to_route('user.profile.setting')->withNotify($notify);
                        }else{
                            $response_cpwd = $this->cambiaPwd($user->email, $user->identification, $user->kyc);
                            if($response_cpwd == 'User not found') $this->creaUsuario($user, $new_pwd);
                        }
                                    
                        $token = $this->nuevoToken($user->email, $user->kyc);
                    }
                    session()->put('token_cryptopocket', $token);
                    $notify[] = ['info', 'Por favor valide su cuenta por video'];
                    return to_route('user.send.money.video_valid')->withNotify($notify);
                }else{
                    //$this->consultaUser($user->email, $user->video_id);
                    //$this->tramitaPago($user->email, $sendMoney->sending_amount);//UirxKx8Kk018TrnaBtmcnLKRV//20ljaDW1YU1yqEtDl2V38kaoq
                    //$this->consultaPago('UirxKx8Kk018TrnaBtmcnLKRV');//UirxKx8Kk018TrnaBtmcnLKRV

                    $combined_order = CombinedDeposit::find($sendMoney->combined_id);
                    
                    $req = new Request;
                    $req->merge(
                        [
                            'name_card_form'=> '',//$request->name_card_form,
                            'payment_type'  => $sendMoney->payment_type,
                            'email'         => $user->email,
                            'video_id'      => $user->video_id,
                            'amount'        => $combined_order->amount + $combined_order->charge,
                        ]
                    );
                    return $this->procesaPago($req, $sendMoney->combined_id);
                }
            }
        }else{
            return to_route('user.send.money.pay.now');
        }
    }

    /*
     * Transfer History
     */
    public function history() {
        $pageTitle    = 'Send Money History';
        $emptyMessage = trans('No send money found');
        $transfers    = SendMoney::with('deposit.gateway', 'recipientCountry', 'countryDeliveryMethod.deliveryMethod')->filterUser()->latest()->paginate(getPaginate());

        return view($this->activeTemplate . 'user.send_money.history', compact('pageTitle', 'emptyMessage', 'transfers'));
    }

    public function waitingResponse(){
        $pageTitle = 'En espera de validación';

        return view($this->activeTemplate . 'user.send_money.waiting_response', compact('pageTitle'));
    }

    public function addReceipt(Request $request)
    {
        $rules =  [
            'recipient'         => 'required|array|min:3',
            //'recipient.*'       => 'required|string',
            'delivery_method'   => 'required|numeric',
            'service'           => 'nullable|required_unless:delivery_method,0|integer'
        ];

        $messages = [
            'recipient.name.required'    => 'Recipient name field is required',
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
            $request->validate($rules, $messages);
            $serviceFormData  = $formProcessor->processFormData($request, $formData);
        } 
        $user_recipient = Recipient::where('user_id', auth()->user()->id)->where('name', $request->recipient['name'])
                // ->where(function ($query) use($request) {
                //     $query->where('mobile', $request->recipient['mobile'])
                //         ->orWhere('email', $request->recipient['email']);
                // })
                ->first();

        if(is_null($user_recipient))
        {
            $user_recipient = new Recipient;
            $user_recipient->user_id = auth()->user()->id;
        }
        $user_recipient->country_delivery_method_id = $request->delivery_method;
        $user_recipient->service_id = $request->service;
        //$user_recipient->sending_currency = $request->sending_country;
        //$user_recipient->recipient_currency = $request->recipient_country;

        $user_recipient->name = $request->recipient['name'];
        $user_recipient->mobile = isset($request->recipient['mobile']) ? $request->recipient['mobile'] : '';
        $user_recipient->email = isset($request->recipient['email']) ? $request->recipient['email'] : '';
        $user_recipient->address = $request->recipient['address'];

        $user_recipient->form_data = json_encode($serviceFormData);
        $user_recipient->save();

        return response()->json(['message' => 'Nuevo beneficiario agregado.', 'id' => $user_recipient->id]);
    }

    public function viewCardBenef(Request $request){
        $ind_key = $request->ind_key;
        $user_id = auth()->user()->id;
        return view($this->activeTemplate . 'user.send_money.view_card_benef', compact('ind_key', 'user_id'))->render();
    }

    public function destinatarios(Request $request)
    {
        $pageTitle    = 'Destinatarios';
        $emptyMessage = trans('No hay destinatarios');
        $recipients    = Recipient::where('user_id', auth()->user()->id)->latest()->paginate(getPaginate());

        return view($this->activeTemplate . 'user.send_money.destinatarios', compact('pageTitle', 'emptyMessage', 'recipients'));
    }

}
