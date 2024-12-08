<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CuentasCobrar;
use App\Models\CuentasCobrarPagos;
use App\Models\CuentasPagar;
use App\Models\CuentasPagarPagos;

use Maatwebsite\Excel\Facades\Excel as Excel;
use Illuminate\Http\Request;
use Carbon\{Carbon, CarbonPeriod};
use App\Exports\ExportCuentas;
use App\Exports\ExportCuentasCobrar;

class CuentasCobrarPagarController extends Controller
{
    
    public function __construct()
    {
        parent::__construct();

    }

    public function indexCobrar(Request $request, $status = 'pending')
    {
        $pageTitle = 'Cuentas por Cobrar';
        $cuentas = CuentasCobrar::$status()->with('sendMoney', 'user', 'bank');

        if($request->input('start_date') !== null && $request->input('end_date') !== null)
        {
            $from = Carbon::create($request->input('start_date') . '00:00:00');
            $to = Carbon::create($request->input('end_date') . '23:59:59');
        }else{
            $from =  Carbon::now()->add('-30 days');
            $to =  Carbon::now();
        }

        $cuentas = $cuentas->whereBetween('created_at', [$from, $to]);
        if($request->button == 'excel')
        {
            //$cuentas = $cuentas;
            return Excel::download(new ExportCuentasCobrar($status, $from, $to), 'cxc.xlsx');
        }else{
            $cuentas = $cuentas->paginate(10);
            return view('admin.cuentas.cobrar.index',compact('cuentas', 'pageTitle', 'status', 'from', 'to'));
        }
    }
    
    public function saveManual(Request $request)
    {
        $bankId = $request->bankId;
        $bankIdOutput = $request->bancoIdOutput;

        $cuentas_cobrar = new CuentasCobrar;
        
        $cuentas_cobrar->user_id = auth('admin')->user()->id;
        $cuentas_cobrar->bank_id = $bankId;
        $cuentas_cobrar->bank_id_output = $bankIdOutput;
        $cuentas_cobrar->amount_currency_convert = $request->amount_currency_convert;
        $cuentas_cobrar->amount_currency_local = $request->amount_currency_local;
        $cuentas_cobrar->user_id = auth('admin')->user()->id;
        $cuentas_cobrar->fecha_vencimiento = $request->fecha_vencimiento;
        $cuentas_cobrar->nombre_proveedor = $request->nombre_proveedor;
        $cuentas_cobrar->concepto = $request->description_cp;

        $cuentas_cobrar->status = 'pending';
        $cuentas_cobrar->save();
        
        $amount_currency_local = $request->amount_currency_local;
        $amount_currency_convert = $request->amount_currency_convert;

        $bank_process = new \App\Http\Controllers\Admin\BankController;
        $req = new Request;
        $req->merge(
            [
                'bankId' => $bankId,
                'amount_currency_local' => $amount_currency_local,
                'amount_currency_convert' => $amount_currency_convert,
                'description' => $request->description_cp,
                'cxc_id' => $cuentas_cobrar->id,
                'reason' => 'CXC'
            ]
        );
            
        $bank_process->retiroBanco($req, true, true);

        $notify[] = ['success', 'La cuenta por pagar fue creada correctamente'];
        return back()->withNotify($notify);
    }

    public function indexCuentasCobrarPagos(Request $request)
    {
        $pagos = CuentasCobrarPagos::where('cuentas_cobrar_id', $request->cuenta_id)->paginate(20);

        return view('admin.cuentas.cobrar.list_pagos',compact('pagos'))->render();
    }
 
    public function savePayment(Request $request)
    {
        $itm = CuentasCobrar::findOrFail($request->cuentaId);
        $sendMoney = $itm->sendMoney;
        if($sendMoney){
            $saldo = ($itm->sendMoney->sending_amount - $itm->sumPagos());
        }else{
            $saldo = $itm->amount_currency_convert;
        }

        if($saldo >= $request->amount)
        {
            $pago = new CuentasCobrarPagos;
            $pago->user_id = auth('admin')->user()->id;
            $pago->cuentas_cobrar_id = $request->cuentaId;
            $pago->amount = $request->amount;
            $pago->save();
            $notify[] = ['success', 'Pago agregado correctamente'];
            $saldo = ($sendMoney) ? ($itm->sendMoney->sending_amount - $itm->sumPagos()) : ($itm->amount_currency_local - $itm->sumPagos());
                
            $bank_process = new \App\Http\Controllers\Admin\BankController;
            $req = new Request;
            $req->merge(
                [
                    'bankId' => $itm->bank_id_output,
                    'amount_currency_local' => $request->amount,
                    'cxc_id' => $itm->id,
                    'reason' => 'CXC'
                ]
            );
                
            $bank_process->ingresoBanco($req, true, true);
            
            if($saldo == 0)
            {
                $itm->status = 'finished';
                $itm->save();
                $notify[] = ['success', 'La cuenta por cobrar a sido finalizada'];
            }
        }else{
            $notify[] = ['error', 'El monto ingresado es superior al saldo de la cuenta'];
        }
        return back()->withNotify($notify);
        
    }
    
    public function indexPagar(Request $request, $status = 'pending')
    {
        $pageTitle = 'Cuentas por Pagar';
        $cuentas = CuentasPagar::$status()->with('bank', 'user');

        if($request->input('start_date') !== null && $request->input('end_date') !== null)
        {
            $from = Carbon::create($request->input('start_date') . '00:00:00');
            $to = Carbon::create($request->input('end_date') . '23:59:59');
        }else{
            $from =  Carbon::now()->add('-30 days');
            $to =  Carbon::now();
        }

        $cuentas = $cuentas->whereBetween('created_at', [$from, $to]);
        if($request->button == 'excel')
        {
            //$cuentas = $cuentas;
            return Excel::download(new ExportCuentas($status, $from, $to), 'cxp.xlsx');
        }else{
            $cuentas = $cuentas->paginate(10);
            return view('admin.cuentas.pagar.index',compact('cuentas', 'pageTitle', 'status', 'from', 'to'));
        }
    }

    public function saveCuentaPagar(Request $request)
    {
        $bankId = $request->bankId;
        $bankIdOutput = $request->bancoIdOutput;

        $cuentas_pagar = new CuentasPagar;
        
        $cuentas_pagar->bank_id = $bankId;
        $cuentas_pagar->bank_id_output = $bankIdOutput;
        $cuentas_pagar->amount_currency_convert = $request->amount_currency_convert;
        $cuentas_pagar->amount_currency_local = $request->amount_currency_local;
        $cuentas_pagar->user_id = auth('admin')->user()->id;
        $cuentas_pagar->fecha_vencimiento = $request->fecha_vencimiento;
        $cuentas_pagar->concepto = $request->description_cp;
        $cuentas_pagar->proveedor = $request->proveedor;

        $cuentas_pagar->status = 'pending';
        $cuentas_pagar->save();

        
        $amount_currency_local = $request->amount_currency_local;
        $amount_currency_convert = $request->amount_currency_convert;

        $bank_process = new \App\Http\Controllers\Admin\BankController;
        $req = new Request;
        $req->merge(
            [
                'bankId' => $bankId,
                'amount_currency_local' => $amount_currency_convert,
                'cxp_id' => $cuentas_pagar->id,
                'amount_currency_convert' => $amount_currency_local
            ]
        );
            
        $bank_process->ingresoBanco($req);

        

        $notify[] = ['success', 'La cuenta por pagar fue creada correctamente'];
        return back()->withNotify($notify);
    }

    public function indexCuentasPagarPagos(Request $request)
    {
        $pagos = CuentasPagarPagos::where('cuentas_pagar_id', $request->cuenta_id)->paginate(20);

        return view('admin.cuentas.pagar.list_pagos',compact('pagos'))->render();
    }
 
    public function saveCuentaPagarPayment(Request $request)
    {
        $itm = CuentasPagar::findOrFail($request->cuentaId);
        $saldo = ($itm->amount_currency_local - $itm->sumPagos());
        if($saldo >= $request->amount)
        {
            $pago = new CuentasPagarPagos;
            $pago->user_id = auth('admin')->user()->id;
            $pago->cuentas_pagar_id = $request->cuentaId;
            $pago->amount = $request->amount;
            $pago->save();
            $notify[] = ['success', 'Pago agregado correctamente'];
            $saldo = ($itm->amount_currency_local - $itm->sumPagos());

            $bank_process = new \App\Http\Controllers\Admin\BankController;
            $req = new Request;
            $req->merge(
                [
                    'bankId' => $itm->bank_id_output,
                    'amount_currency_local' => $request->amount,
                    'cxp_id' => $itm->id,
                    'reason' => 'CXP'
                ]
            );
                
            $bank_process->ingresoBanco($req, true, true);
            
            if($saldo == 0)
            {
                $itm->status = 'finished';
                $itm->save();
                $notify[] = ['success', 'La cuenta por pagar a sido finalizada'];
            }
        }else{
            $notify[] = ['error', 'El monto ingresado es superior al saldo de la cuenta'];
        }
        return back()->withNotify($notify);
        
    }
    
    public function cancelCuentaCobrar(Request $request)
    {
        $itm = CuentasCobrar::findOrFail($request->cuentaId);
        $bank_process = new \App\Http\Controllers\Admin\BankController;

        foreach(CuentasCobrarPagos::where('cuentas_cobrar_id', $request->cuentaId)->where('cancelado', false)->get() as $itm_pago)
        {

            $req = new Request;
            $req->merge(
                [
                    'bankId' => $itm_pago->cuentaCobrar->bank_id_output,
                    'amount_currency_local' => $itm_pago->amount,
                    'cxc_id' => $itm_pago->cuentas_cobrar_id,
                    'reason' => 'REVERSO OPERACION'
                ]
            );
            $bank_process->retiroBanco($req, true, true);

            $itm_pago->cancelado = 1;
            $itm_pago->save();
        }


        $req = new Request;
        $req->merge(
            [
                'bankId' => $itm->bank_id,
                'amount_currency_local' => $itm->amount_currency_local,
                'cxc_id' => $itm->id,
                'reason' => 'REVERSO OPERACION'
            ]
        );
        $bank_process->ingresoBanco($req);

        $itm->status = 'pending';
        $itm->cancelado = 1;
        $itm->save();

        $notify[] = ['success', 'La operación fue cancelada correctamente'];
        return back()->withNotify($notify);
    }

    public function cancelCxcPago(Request $request)
    {
        $itm = CuentasCobrarPagos::findOrFail($request->cxcPagoId);
        $req = new Request;
        $req->merge(
            [
                'bankId' => $itm->cuentaCobrar->bank_id_output,
                'amount_currency_local' => $itm->amount,
                'cxc_id' => $itm->cuentas_cobrar_id,
                'reason' => 'REVERSO OPERACION'
            ]
        );
        $bank_process = new \App\Http\Controllers\Admin\BankController;
        $bank_process->retiroBanco($req, true, true);
        
        $itm->status = 'pending';
        $itm->cancelado = 1;
        $itm->save();
        
        $notify[] = ['success', 'El pago fue cancelado correctamente'];
        return back()->withNotify($notify);
    }
    
    public function cancelCuentaPagar(Request $request)
    {
        $itm = CuentasPagar::findOrFail($request->cuentaId);
        $bank_process = new \App\Http\Controllers\Admin\BankController;

        foreach(CuentasPagarPagos::where('cuentas_pagar_id', $request->cuentaId)->where('cancelado', false)->get() as $itm_pago)
        {

            $req = new Request;
            $req->merge(
                [
                    'bankId' => $itm_pago->cuentaPagar->bank_id_output,
                    'amount_currency_local' => $itm_pago->amount,
                    'cxp_id' => $itm_pago->cuentas_pagar_id,
                    'reason' => 'REVERSO OPERACION'
                ]
            );
            $bank_process->retiroBanco($req, true, true);

            $itm_pago->cancelado = 1;
            $itm_pago->save();
        }


        $req = new Request;
        $req->merge(
            [
                'bankId' => $itm->bank_id,
                'amount_currency_local' => $itm->amount_currency_local,
                'cxp_id' => $itm->id,
                'reason' => 'REVERSO OPERACION'
            ]
        );
        $bank_process->ingresoBanco($req);

        $itm->status = 'pending';
        $itm->cancelado = 1;
        $itm->save();

        $notify[] = ['success', 'La operación fue cancelada correctamente'];
        return back()->withNotify($notify);
    }

    public function cancelCxpPago(Request $request)
    {
        $itm = CuentasPagarPagos::findOrFail($request->cxpPagoId);
        $req = new Request;
        $req->merge(
            [
                'bankId' => $itm->cuentaPagar->bank_id_output,
                'amount_currency_local' => $itm->amount,
                'cxc_id' => $itm->cuentas_pagar_id,
                'reason' => 'REVERSO OPERACION'
            ]
        );
        $bank_process = new \App\Http\Controllers\Admin\BankController;
        $bank_process->retiroBanco($req, true, true);
        
        $itm->status = 'pending';
        $itm->cancelado = 1;
        $itm->save();
        
        $notify[] = ['success', 'El pago fue cancelado correctamente'];
        return back()->withNotify($notify);
    }
}