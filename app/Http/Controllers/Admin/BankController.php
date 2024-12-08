<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankExtract;
use App\Models\CierreDiario;
use App\Models\Country;
use App\Models\SendMoney;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BankController extends Controller
{

    public function __construct()
    {
        parent::__construct();

    }
/**
 * CRUD
 */
    public function list()
    {
        $segments       = request()->segments();
        $scope          = end($segments);
        $pageTitle      = 'Bancos';

        $banks = Bank::query();
        if(auth('admin')->user()->role_id != 1)
        {
            $bancos = explode(',', \App\Models\Roles::find(auth('admin')->user()->role_id)->bancos);
            $banks->whereIn('id', $bancos);
        }
        $banks = $banks->searchable(['name', 'account', 'currency'])->paginate(getPaginate());
        return view('admin.banks.list', compact('pageTitle', 'banks'));
    }

    public function create()
    {
        $sendingCountries          = Country::active()->sending()->with('conversionRates')->get();
        $receivingCountries        = Country::receivableCountries()->get();

        $segments       = request()->segments();
        $scope          = end($segments);
        $pageTitle      = ucfirst($scope);

        return view('admin.banks.add', compact('pageTitle', 'sendingCountries', 'receivingCountries'));
    }

    public function store(Request $request)
    {
        $bank = new Bank;
        $bank->name = $request->name;
        $bank->recibe = $request->has('recibe') ? true : false;
        $bank->envia = $request->has('envia') ? true : false;
        $bank->name_account = $request->name_account ?? '';
        $bank->account = $request->account;
        $bank->codigo_banco = $request->codigo_banco;
        $bank->currency = $request->currency;
        $bank->balance = $request->balance;
        $bank->average_rate = $request->average_rate;
        $bank->only_criptopocket = $request->has('only_criptopocket') ? true : false;
        
        $bank->save();

        
        $obj = array(
            'bank_id' => $bank->id,
            'date' => Carbon::now(),
            'saldo_inicial' => $bank->balance,
        );
        CierreDiario::create($obj);

        return redirect(route('admin.bank.list'));
    }

    public function edit($id)
    {
        $sendingCountries          = Country::active()->sending()->with('conversionRates')->get();
        $receivingCountries        = Country::receivableCountries()->get();

        $segments       = request()->segments();
        $scope          = end($segments);
        $pageTitle      = "Editar Banco";

        $bank = Bank::find($id);
        return view('admin.banks.edit', compact('bank', 'pageTitle', 'sendingCountries', 'receivingCountries'));
    }

    public function update($id, Request $request)
    {
        
        $bank = Bank::find($id);
        $bank->name_account = $request->name_account ?? '';
        $bank->account = $request->account;
        $bank->codigo_banco = $request->codigo_banco;

        $bank->recibe = $request->has('recibe') ? true : false;
        $bank->envia = $request->has('envia') ? true : false;
        $bank->currency = $request->currency;
        $bank->balance = $request->balance;
        $bank->average_rate = $request->average_rate;
        $bank->only_criptopocket = $request->has('only_criptopocket') ? true : false;

        $bank->save();
        
        return redirect(route('admin.bank.list'));
    }
/**
 * END CRUD
 */

    public function convertCurrency(Request $request)
    {
        if(empty($request->amount_currency_local) || $request->amount_currency_local == 0 || empty($request->amount_currency_convert) || $request->amount_currency_convert == 0)
        {
            $notify[] = ['warning', 'Los montos deben ser mayor a 0'];
            return back()->withNotify($notify );
        }

        $message = $this->retiroBanco($request, false, false, true);
        //$bank = Bank::findOrFail($request->bankId);

        $notify[] = ['success', 'Conversion correctamente realizada'];
        return back()->withNotify($notify);
    }

    public function deposit(Request $request)
    {
        $this->ingresoBanco($request);
        $bank = Bank::findOrFail($request->bankId);

        return redirect(route('admin.bank.list'));
    }

    public function retiro(Request $request)
    {
        $this->retiroBanco($request, true);
        $bank = Bank::findOrFail($request->bankId);
        
        return redirect(route('admin.bank.list'));
    }

    public function retiroBanco(Request $request, $retiro = false, $cxc = false, $ind_convert_currency = false)
    {
        $bank = Bank::findOrFail($request->bankId);

        $amount_currency_convert = $request->amount_currency_convert;
        $amount_currency_local = $request->amount_currency_local;
        $revenue = 0;
        $tasa_cambio = 0;

        if($request->has('send_money_id'))
        {
            $tasa_cambio = number_format($amount_currency_local / $amount_currency_convert, 3);

            $send_money = SendMoney::find($request->send_money_id);
            $revenue = (($bank->average_rate - $send_money->conversion_rate) * $amount_currency_convert) / $send_money->conversion_rate;
        }

        if($request->has('deposit_id'))
        {
            $tasa_cambio = number_format($amount_currency_convert / $amount_currency_local, 3);
        }

        // if($bank->bank_type == 'ENVIA' && $ind_convert_currency)
        //     $tasa_cambio = number_format($amount_currency_local / $amount_currency_convert, 3);
        // else
        //     $tasa_cambio = number_format($amount_currency_convert / $amount_currency_local, 3);

        // if($bank->bank_type == 'ENVIA' && !$retiro && !$cxc && !$ind_convert_currency)
        // {
        //     $send_money = SendMoney::find($request->send_money_id);
        //     if($bank->average_rate > 0)
        //     {
        //         $revenue = (($bank->average_rate - $send_money->conversion_rate) * $amount_currency_convert) / $bank->average_rate;
        //     }else{
        //         $revenue = (($send_money->conversion_rate - $send_money->conversion_rate) * $amount_currency_convert) / $send_money->conversion_rate;
        //     }
        // }

        $bank_extract = new BankExtract;
        $bank_extract->type = 'debito';
        $bank_extract->title = $request->has('title') ? $request->title : '';
        $bank_extract->description = $request->has('description') ? $request->description : '';
        $bank_extract->beneficiario = $request->has('beneficiario') ? $request->beneficiario : '';
        $bank_extract->tipo_operacion = $request->has('tipo_operacion') ? $request->tipo_operacion : '';
        $bank_extract->banco_receptor = $request->has('banco_receptor') ? $request->banco_receptor : '';
        $bank_extract->reason = $request->reason ?? 'OTRO';
        $bank_extract->deposit_id = $request->has('deposit_id') ? $request->deposit_id : null;
        $bank_extract->bank_id_input = $request->bankId;
        $bank_extract->amount_currency_local = $amount_currency_local;
        $bank_extract->bank_id_ouput = $request->has('bankIdOutput') ? $request->bankIdOutput : null;
        $bank_extract->saldo_banco_antes = $bank->balance;
        $bank_extract->saldo_banco_despues = $bank->balance - $request->amount_currency_local;
        $bank_extract->amount_currency_convert = $amount_currency_convert ?? 0;
        $bank_extract->rate = $tasa_cambio;
        $bank_extract->revenue = $revenue;
        $bank_extract->send_money_id = $request->has('send_money_id') ? $request->send_money_id : null;
        $bank_extract->cxc_id = $request->has('cxc_id') ? $request->cxc_id : null;
        $bank_extract->cxp_id = $request->has('cxp_id') ? $request->cxp_id : null;
        $bank_extract->comprobante = $request->comprobante;
        $bank_extract->user_id = auth('admin')->user()->id;

        $bank_extract->save();
        
        $bank->balance -= $request->amount_currency_local;
        $bank->save();
        
        if($ind_convert_currency && !$retiro && !$cxc)
        {
            /**
             * GENERA UN INGRESO AL BANCO QUE PAGA
             */
            $bankId = $request->bankId;
            $bankIdOutput = $request->bankIdOutput;
            
            $amount_currency_local = $request->amount_currency_local;
            $amount_currency_convert = $request->amount_currency_convert;


            $req = new Request;
            $req->merge(
                [
                    'bank_extract_id' => $bank_extract->id,
                    'bankId' => $bankIdOutput,
                    'bankIdOutput' => $bankId,
                    'amount_currency_local' => $amount_currency_convert,
                    'amount_currency_convert' => $amount_currency_local,
                    'reason' => $request->reason ?? 'OTRO'
                ]
                );
                
            $this->ingresoBanco($req, $ind_convert_currency);
        }
    }
    
    public function ingresoBanco(Request $request, $ind_convert_currency = false)
    {
        $bank = Bank::findOrFail($request->bankId);
        //dd($request);

        $last_record = null;
        if($request->has('bank_extract_id'))
            $last_record = BankExtract::find($request->bank_extract_id);
        elseif($request->has('bankId'))
            $last_record = BankExtract::where('bank_id_input', $request->bankId)->where('type', 'debito')->orderBy('id', 'desc')->first();

        $amount_currency_convert = $request->amount_currency_convert;
        $amount_currency_local = $request->amount_currency_local;

        $promedio = 1;
        if($request->has('send_money_id'))
        {
            $tasa_cambio = number_format($amount_currency_local / $amount_currency_convert, 3);
            $promedio = $tasa_cambio;
        }

        if($request->has('deposit_id'))
        {
            $tasa_cambio = number_format($amount_currency_convert / $amount_currency_local, 3);
            $promedio = $tasa_cambio;
        }

        //$new_balance = $amount_currency_local;
        $revenue = 0;

        //RETIRO PARA CAMBIO
        // if(!is_null($last_record))
        // {
        //     $promedio = $bank->average_rate;
        //     $new_balance = number_format($last_record->amount_currency_local + $amount_currency_local, 3);
        // }

        if($ind_convert_currency)
        {
            if($bank->balance > 0)
            {
                $amount_rate = $bank->average_rate > 0 ? $bank->average_rate : $promedio;
                $currency_original = ($bank->balance / $amount_rate)+ $amount_currency_convert;
                $currency_convert = $bank->balance + $amount_currency_local;
                $promedio_banco = number_format($currency_convert / $currency_original, 3);
            }else{
                $promedio_banco = number_format($amount_currency_local / $amount_currency_convert, 3);
            }
            $bank->average_rate = $promedio_banco;
            $bank->save();
        }else{
            //$revenue = (($bank->average_rate - $tasa_cambio) * $amount_currency_local) /$bank->average_rate;
        }

        
        $bank_extract = new BankExtract;
        $bank_extract->type = 'credito';
        $bank_extract->title = $request->has('title') ? $request->title : '';
        $bank_extract->description = $request->has('description') ? $request->description : '';
        $bank_extract->reason = ($request->has('reason') && $request->reason == 'REVERSO OPERACION' ? $request->reason : ($request->has('cxc_id') ? 'CXC' : ($request->has('cxp_id') ? 'CXP' : (($request->has('reason')) ? $request->reason : 'OTRO'))));
        $bank_extract->send_money_id = $request->send_money_id;
        $bank_extract->cxp_id = $request->has('cxp_id') ? $request->cxp_id : null;
        $bank_extract->deposit_id = $request->deposit_id;
        $bank_extract->bank_id_input = $request->bankId;
        $bank_extract->saldo_banco_antes = $bank->balance;
        $bank_extract->saldo_banco_despues = $bank->balance + $request->amount_currency_local;
        $bank_extract->amount_currency_local = $amount_currency_local;
        $bank_extract->bank_id_ouput = null;
        $bank_extract->amount_currency_convert = $amount_currency_convert ?? 0;
        $bank_extract->cxc_id = $request->has('cxc_id') ? $request->cxc_id : null;
        $bank_extract->cxp_id = $request->has('cxp_id') ? $request->cxp_id : null;
        $bank_extract->rate = $tasa_cambio ?? 0;
        $bank_extract->revenue = 0;//$revenue;
        $bank_extract->user_id = auth('admin')->user()->id;

        $bank_extract->save();
        
        $bank->balance += $request->amount_currency_local;
        $bank->save();
    }
    

    // Función para realizar una compra de bolívares fuertes (bs) mediante pago con euros
    public function buyBs(Request $request, $bankId)
    {
        $bank = Bank::findOrFail($bankId);
        $amountInEuros = $request->input('amount');
        $rate = $request->input('rate');

        // Calcular el monto en bolívares fuertes (bs) recibido
        $amountInBs = $amountInEuros * $rate;

        // Actualizar el saldo y la tasa promedio
        $bank->balance += $amountInBs;
        $bank->average_rate = $this->calculateAverageRate($bank, $amountInBs, $rate);
        $bank->save();

        return response()->json(['message' => 'Compra de bolívares fuertes (bs) realizada con éxito']);
    }

    // Función para calcular la tasa promedio
    private function calculateAverageRate($bank, $amount, $rate)
    {
        $totalBalance = $bank->balance * $bank->average_rate;
        $totalBalance += $amount * $rate;$totalAmount = $bank->balance + $amount;
        return $totalBalance / $totalAmount;
    }

    public function cierreDiario()
    {
        $bancos = Bank::all();
        $date_now = Carbon::now();
        $past_day =  Carbon::now()->add('-1 days');
        //echo $past_day;

        foreach($bancos as $banco)
        {
            //debe crearse un primer registro al crear el banco
            $cierre = CierreDiario::where('bank_id', $banco->id)->where('date', $past_day->format('Y-m-d'))->first();

            if(!$cierre)
            {
                $obj = array(
                    'bank_id' => $banco->id,
                    'date' => $past_day->format('Y-m-d'),
                    'saldo_inicial' => $banco->balance,
                );
                CierreDiario::create($obj);
                $cierre = CierreDiario::where('bank_id', $banco->id)->where('date', $past_day->format('Y-m-d'))->first();
            }
        
            $ingresos = BankExtract::where('type', 'credito')->where('bank_id_input', $banco->id)->whereDate('created_at', $past_day->format('Y-m-d'))->sum('amount_currency_local');
            $egresos = BankExtract::where('type', 'debito')->where('bank_id_input', $banco->id)->whereDate('created_at', $past_day->format('Y-m-d'))->sum('amount_currency_local');
            $revenue = BankExtract::where('bank_id_input', $banco->id)->whereDate('created_at', $past_day->format('Y-m-d'))->sum('revenue');
            $saldo_final = ($cierre->saldo_inicial + $ingresos) - $egresos;

            $cierre->ingresos = $ingresos;
            $cierre->egresos = $egresos;
            $cierre->saldo_final = $saldo_final;
            $cierre->revenue = $revenue;
            $cierre->save();

            $obj = array(
                'bank_id' => $banco->id,
                'date' => $date_now->format('Y-m-d'),
                'saldo_inicial' => $saldo_final,
            );
            CierreDiario::create($obj);
            
        }
    }

    public function active($id)
    {
        $bank = Bank::find($id);
        $bank->active = true;
        $bank->save();

        $notify[] = ['success', 'Banco activado correctamente'];
        return back()->withNotify($notify);
    }
    
    public function inactive($id)
    {
        $bank = Bank::find($id);
        $bank->active = false;
        $bank->save();

        $notify[] = ['success', 'Banco desactivado correctamente'];
        return back()->withNotify($notify);
    }
}
