<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Gateway\PaymentController;
use App\Lib\ProcessSendMoney;
use App\Models\Bank;
use Illuminate\Http\Request;

class DepositController extends Controller {

    protected $type;
    protected $title;
    public function __construct() {
        $segments = request()->segments();
        if (!empty($segments)) {
            $this->type     = request()->segments()[1];
        }
        $this->title    = ucfirst($this->type);
    }

    public function pending() {
        $pageTitle = "Pending " . plural($this->title);
        $deposits  = $this->depositData('pending');
        $type      = $this->type;
        return view('admin.deposit.pending', compact('pageTitle', 'deposits', 'type'));
    }

    public function approved(Request $request) {
        $pageTitle = "Approved " . plural($this->title);
        $banco = $request->has('banco') ? $request->banco : null;
        $deposits  = $this->depositData('approved', false, $banco);
        $type      = $this->type;
        
        return view('admin.deposit.log', compact('pageTitle', 'deposits', 'type', 'banco'));
    }

    public function successful() {
        $pageTitle = "Successful " . plural($this->title);
        $deposits  = $this->depositData('successful');
        $type      = $this->type;
        return view('admin.deposit.log', compact('pageTitle', 'deposits', 'type'));
    }

    public function rejected() {
        $pageTitle = "Rejected " . plural($this->title);
        $deposits  = $this->depositData('rejected');
        $type      = $this->type;
        return view('admin.deposit.log', compact('pageTitle', 'deposits', 'type'));
    }

    public function initiated() {
        $pageTitle = "Initiated " . plural($this->title);
        $deposits  = $this->depositData('initiated');
        $type      = $this->type;
        return view('admin.deposit.log', compact('pageTitle', 'deposits', 'type'));
    }

    public function deposit() {
        $pageTitle   = "All " . plural($this->title);
        $depositData = $this->depositData($scope = null, $summery = true);
        $deposits    = $depositData['data'];
        $summery     = $depositData['summery'];
        $successful  = $summery['successful'];
        $pending     = $summery['pending'];
        $rejected    = $summery['rejected'];
        $initiated   = $summery['initiated'];
        $type        = $this->type;
        return view('admin.deposit.log', compact('pageTitle', 'deposits', 'successful', 'pending', 'rejected', 'initiated', 'type'));
    }

    protected function depositData($scope = null, $summery = false, $banco = null) {


        if ($scope) {
            $deposits = Deposit::$scope()->with(['gateway', 'agent', 'user', 'sendMoney:id,mtcn_number,status']);
        } else {
            $deposits = Deposit::with(['user', 'gateway', 'agent', 'user', 'sendMoney:id,mtcn_number,status']);
        }

        if ($this->type == 'payment') {
            $deposits->payment();
            $deposits = $deposits->searchable(['trx', 'user:username'])->dateFilter();
        } else {
            $deposits->agentDeposit();
            $deposits = $deposits->searchable(['trx', 'agent:username'])->dateFilter();
        }

        if(!is_null($banco))
        {
            $bnk = \App\Models\Bank::find($banco);
            $deposits->where('detail', 'LIKE', '%'. $bnk->name .'%');
        }

        $request = request();

        if ($request->method) {
            $method   = Gateway::where('alias', $request->method)->firstOrFail();
            $deposits = $deposits->where('method_code', $method->code);
        }

        if (!$summery) {
            return $deposits->orderBy('id', 'desc')->paginate(getPaginate());
        } else {
            $successful = clone $deposits;
            $pending    = clone $deposits;
            $rejected   = clone $deposits;
            $initiated  = clone $deposits;

            $successfulSummery = $successful->where('status', Status::PAYMENT_SUCCESS)->sum('amount');
            $pendingSummery = $pending->where('status', Status::PAYMENT_PENDING)->sum('amount');
            $rejectedSummery = $rejected->where('status', Status::PAYMENT_REJECT)->sum('amount');
            $initiatedSummery = $initiated->where('status', Status::PAYMENT_INITIATE)->sum('amount');

            return [
                'data'    => $deposits->orderBy('id', 'desc')->paginate(getPaginate()),
                'summery' => [
                    'successful' => $successfulSummery,
                    'pending'    => $pendingSummery,
                    'rejected'   => $rejectedSummery,
                    'initiated'  => $initiatedSummery,
                ]
            ];
        }
    }

    public function details($id) {
        $general   = gs();
        $deposit   = Deposit::where('id', $id)->with(['user', 'agent', 'gateway'])->firstOrFail();
        $pageTitle = $deposit->user->username ?? $deposit->agent->username . ' requested ' . showAmount($deposit->amount) . ' ' . $general->cur_text;
        $details   = ($deposit->detail != null) ? json_encode($deposit->detail) : null;
        $type        = $this->type;
        return view('admin.deposit.detail', compact('pageTitle', 'deposit', 'details', 'type'));
    }

    public function approve(Request $request, $id) {
        $_deposit         = Deposit::where('id', $id)->where('status', Status::PAYMENT_PENDING)->firstOrFail();

        foreach(Deposit::where('combined_id', $_deposit->combined_id)->get() as $deposit)
        {
            PaymentController::userDataUpdate($deposit, true);
            if ($deposit->user_id) {
                $notify[] = ['success', 'Payment request for send money approved successfully'];
                $type = 'payment';
            } elseif ($deposit->agent_id) {
                $type = 'deposit';
                $notify[] = ['success', 'Deposit request approved successfully'];
            }

            $name_bank = '';
            $details = $deposit->detail ? json_encode($deposit->detail) : null;
            if(!is_null($deposit->detail)){
                foreach ($deposit->detail as $key => $val)
                    if ($deposit->method_code >= 1000)
                        if($val->type == 'select')
                            if ($val->value)
                            {
                                if($request->has('banco') && !empty($request->banco))
                                {
                                    $name_bank = $request->banco;
                                    $deposit->detail = json_decode(str_replace($val->value, $name_bank, json_encode($deposit->detail)));
                                    $deposit->save();
                                }
                                else
                                    $name_bank = $val->value;
                            }
            }

            $bank = Bank::where('name', $name_bank)->first();

            if($bank){
                $bank_process = new \App\Http\Controllers\Admin\BankController;
                $req = new Request;
                $req->merge(
                    [
                        'bankId' => $bank->id,
                        'deposit_id' => $deposit->id,
                        'amount_currency_local' => $deposit->amount,
                        //'amount_currency_convert' => $deposit->recipient_amount
                    ]
                );
                $bank_process->ingresoBanco($req);
            }
        }

        return to_route("admin.$type.pending")->withNotify($notify);
    }

    public function reject(Request $request) {
        $request->validate([
            'id'      => 'required|integer',
            //'message' => 'required|string|max:255'
        ]);

        $_deposit         = Deposit::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->firstOrFail();
        
        foreach(Deposit::where('combined_id', $_deposit->combined_id)->get() as $deposit)
        {
            //$deposit                 = Deposit::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->firstOrFail();
            $deposit->admin_feedback = $request->message ?? '';
            $deposit->status         = Status::PAYMENT_REJECT;
            $deposit->save();

            if ($deposit->user_id) {
                $sendMoney = $deposit->sendMoney;
                if ($sendMoney->status == Status::SEND_MONEY_INITIATED) {
                    $sendMoney->status         = Status::SEND_MONEY_INITIATED;
                    $sendMoney->payment_status = Status::PAYMENT_REJECT;
                    $sendMoney->admin_feedback = $request->message ?? '';
                    $sendMoney->save();
                }
                notify($deposit->user, 'PAYMENT_REJECT', [
                    'trx'                => $deposit->trx,
                    'sending_country'    => @$sendMoney->sendingCountry->name,
                    'sending_amount'     => showAmount($sendMoney->sending_amount),
                    'sending_currency'   => $sendMoney->sending_currency,
                    'recipient_country'  => $sendMoney->recipientCountry->name,
                    'recipient_amount'   => showAmount($sendMoney->recipient_amount),
                    'recipient_currency' => $sendMoney->recipient_currency,
                    'message'            => $request->message ?? '',
                ]);
                $type = 'payment';
                $notification = 'Payment request rejected successfully';
            } else {
                notify($deposit->agent, 'DEPOSIT_REJECT', [
                    'method_name'       => $deposit->gatewayCurrency()->name,
                    'method_currency'   => $deposit->method_currency,
                    'method_amount'     => showAmount($deposit->final_amo),
                    'amount'            => showAmount($deposit->amount),
                    'charge'            => showAmount($deposit->charge),
                    'rate'              => showAmount($deposit->rate),
                    'trx'               => $deposit->trx,
                    'rejection_message' => $request->message ?? ''
                ]);
                $type = 'deposit';
                $notification = 'Deposit request rejected successfully';
            }
        }

        $notify[] = ['success', $notification];
        return  to_route("admin.$type.pending")->withNotify($notify);
    }

    public function editAmount(Request $request)
    {
        $combined_id = $request->combined_id;

        $partial_html = null;
        foreach(Deposit::where('combined_id', $combined_id)->get() as $deposit)
        {
            $partial_html .= view('admin.deposit._amount', compact('deposit'));
        }

        return response()->json($partial_html);
    }

    public function saveAmount(Request $request)
    {
        foreach(Deposit::where('combined_id', $request->combined_id)->get() as $deposit)
        {
            
            if(!is_null($deposit->detail)){
                foreach ($deposit->detail as $key => $val)
                    if ($deposit->method_code >= 1000)
                        if($val->type == 'select')
                            if ($val->value)
                            {
                                if($request->has('banco') && !empty($request->banco))
                                {
                                    $name_bank = $request->banco;
                                    $deposit->detail = json_decode(str_replace($val->value, $name_bank, json_encode($deposit->detail)));
                                    $deposit->save();
                                }
                            }
            }

            if($deposit->final_amo != $request['amount_' . $deposit->id])
            {
                $deposit->final_amo = $request['amount_' . $deposit->id];
                $deposit->amount = $request['amount_' . $deposit->id];
                $deposit->save();

                $sendMoney = $deposit->sendMoney;

                $req = new Request;
                $req->merge(
                    [
                        'sending_country'       => $sendMoney->sending_country_id,
                        'recipient_country'     => $sendMoney->recipient_country_id,
                        'source_of_funds'       => $sendMoney->source_of_fund_id,
                        'sending_purpose'       => $sendMoney->sending_purpose_id,
                    ]
                );
                
                $payment = new ProcessSendMoney($req);
                $payment->setAmountVariables($req, $request['amount_' . $deposit->id]);

                //$sendMoney

                $sendMoney->base_currency_amount        = $payment->sendingAmountInBaseCurrency;
                $sendMoney->base_currency_charge        = $payment->chargeInBaseCurrency;
                $sendMoney->sending_amount              = $payment->sendingAmount;
                $sendMoney->sending_charge              = $payment->chargeInSendingCurrency;
                $sendMoney->recipient_amount            = $payment->receivableAmount;
                $sendMoney->conversion_rate             = $payment->conversionRate;
                $sendMoney->save();
            }
        }
        
        $notify[] = ['success', '¡Monto actualizado correctamente!'];
        return  to_route("admin.payment.pending")->withNotify($notify);
    }

    
    public function cancelDeposit(Request $request)
    {
        $_deposit         = Deposit::where('id', $request->id)->where('status', Status::PAYMENT_SUCCESS)->firstOrFail();

        $bank_process = new \App\Http\Controllers\Admin\BankController;
        foreach(Deposit::where('combined_id', $_deposit->combined_id)->get() as $deposit)
        {
            if($deposit->sendMoney->status == Status::SEND_MONEY_PENDING)
            {



                $name_bank = '';
                $details = $deposit->detail ? json_encode($deposit->detail) : null;
                if(!is_null($deposit->detail)){
                    foreach ($deposit->detail as $key => $val)
                        if ($deposit->method_code >= 1000)
                            if($val->type == 'select')
                                if ($val->value)
                                {
                                    if($request->has('banco') && !empty($request->banco))
                                    {
                                        $name_bank = $request->banco;
                                        $deposit->detail = json_decode(str_replace($val->value, $name_bank, json_encode($deposit->detail)));
                                        $deposit->save();
                                    }
                                    else
                                        $name_bank = $val->value;
                                }
                }
        
                $bank = Bank::where('name', $name_bank)->first();
        
                $req = new Request;
                $req->merge(
                    [
                        'bankId' => $bank->id,
                        'amount_currency_local' => $deposit->amount,
                        'deposit_id' => $deposit->id,
                        'reason' => 'REVERSO OPERACION'
                    ]
                );

                $bank_process->retiroBanco($req, true);

                $sendMoney = $deposit->sendMoney;
                $sendMoney->payment_status = Status::PAYMENT_PENDING;
                $sendMoney->status = Status::SEND_MONEY_INITIATED;
                $sendMoney->save();


                $deposit->status = Status::PAYMENT_PENDING;
                $deposit->save();
                $notify[] = ['success', 'El deposito fue revertido correctamente'];
            }else{
                $notify[] = ['warning', 'El envío ' . $deposit->sendMoney->trx . ' ya fue pagado'];
            }
        }

        return back()->withNotify($notify);
    }
} 
