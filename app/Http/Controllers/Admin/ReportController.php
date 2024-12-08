<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankExtract;
use App\Models\NotificationLog;
use App\Models\Transaction;
use App\Models\UserLogin;
use App\Models\Bank;
use Illuminate\Http\Request;
use App\Exports\ExportBanks;
use App\Exports\ExportBanksRange;
use App\Exports\ExportBanksRangeMovs;
use App\Exports\ExportBanksDetailMovs;
use App\Models\CierreDiario;
use Maatwebsite\Excel\Facades\Excel as Excel;
use Carbon\{Carbon, CarbonPeriod};
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function transaction(Request $request)
    {
        $pageTitle = trans('Transaction Logs');

        $remarks = Transaction::distinct('remark')->orderBy('remark')->get('remark');
        $transactions = Transaction::searchable(['trx', 'user:username'])->filter(['trx_type', 'remark'])->dateFilter()->orderBy('id', 'desc')->with(['user','agent'])->paginate(getPaginate());
        return view('admin.reports.transactions', compact('pageTitle', 'transactions', 'remarks'));
    }

    public function loginHistory(Request $request)
    {
        $pageTitle = 'User Login History';
        $loginLogs = UserLogin::orderBy('id', 'desc')->searchable(['user:username'])->with('user', 'agent')->paginate(getPaginate());
        return view('admin.reports.logins', compact('pageTitle', 'loginLogs'));
    }

    public function loginIpHistory($ip)
    {
        $pageTitle = 'Login by - ' . $ip;
        $loginLogs = UserLogin::where('user_ip', $ip)->orderBy('id', 'desc')->with('user', 'agent')->paginate(getPaginate());
        return view('admin.reports.logins', compact('pageTitle', 'loginLogs', 'ip'));
    }

    public function notificationHistory(Request $request)
    {
        $pageTitle = 'Notification History';
        $logs = NotificationLog::orderBy('id', 'desc')->searchable(['user:username'])->with('user')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle', 'logs'));
    }

    public function emailDetails($id)
    {
        $pageTitle = 'Email Details';
        $email = NotificationLog::findOrFail($id);
        return view('admin.reports.email_details', compact('pageTitle', 'email'));
    }
    
    public function controlGastos(Request $request)
    {
        if($request->input('start_date') !== null && $request->input('end_date') !== null)
        {
            $from = Carbon::create($request->input('start_date') . '00:00:00');
            $to = Carbon::create($request->input('end_date') . '23:59:59');
        }else{
            $from =  Carbon::now()->add('-30 days');
            $to =  Carbon::now();
        }

        if($request->button == 'excel')
        {
            return $this->controlGastosExcel($from, $to);
        }else{
            $pageTitle = 'Control de gastos';
            $items = BankExtract::where('type', 'debito')->where('reason', '<>', 'COMPRA MONEDA')->whereNull('send_money_id')->whereNull('deposit_id')->whereNull('cxc_id')->whereNull('cxp_id')->whereBetween('created_at', [$from, $to])->paginate();
            return view('admin.reports.control_gastos', compact('pageTitle', 'items', 'from', 'to'));
        }

    }

    public function controlGastosExcel($from, $to)
    {
        //$items = BankExtract::where('type', 'credito')->where('beneficiario', '<>', 'NULL')->get();
        return Excel::download(new ExportBanks($from, $to), 'control_de_gastos.xlsx');
    }

    public function datosBancos()
    {
        $pageTitle = 'Reporte Diario';
        return view('admin.reports.reporte_diario', compact('pageTitle'));
    }

    public function datosBancosExcel(Request $request)
    {
        $from = Carbon::create($request->input('start_date'));
        $to = Carbon::create($request->input('end_date'));
        
        $period = CarbonPeriod::create($from, $to);

        return Excel::download(new ExportBanksRange($period), 'reporte_diario.xlsx');
    }

    public function balanceGeneral(Request $request)
    {
        // if($request->input('start_date') !== null && $request->input('end_date') !== null)
        // {
        //     $from = Carbon::create($request->input('start_date'));
        //     $to = Carbon::create($request->input('end_date'));
        // }else{
        //     $from =  Carbon::now()->add('-30 days');
        //     $to =  Carbon::now();
        // }

        $pageTitle = 'Balance General';
        //$items = CierreDiario::whereDate('date', Carbon::now()->format('Y-m-d'))->paginate();
        
        $date_now = Carbon::now();

        $items = DB::table('cierre_diario')
                    ->join('banks', 'cierre_diario.bank_id', '=', 'banks.id')
                    ->groupBy('banks.currency')
                    ->whereDate('date', $date_now->format('Y-m-d'))
                    ->orderBy('banks.currency')
                    ->select(
                        DB::raw('SUM(cierre_diario.saldo_inicial) as saldo_inicial'),
                        DB::raw('(SELECT SUM(amount_currency_local) FROM bank_extracts WHERE type = "credito" and bank_id_input IN (SELECT id FROM banks as bkns WHERE bkns.currency = banks.currency) AND DATE(created_at) = DATE("' . $date_now->format('Y-m-d') .'")) as ingresos'),
                        DB::raw('(SELECT SUM(amount_currency_local) FROM bank_extracts WHERE type = "debito" and bank_id_input IN (SELECT id FROM banks as bkns WHERE bkns.currency = banks.currency) AND DATE(created_at) = DATE("' . $date_now->format('Y-m-d') .'")) as egresos'),
                        //DB::raw('SUM(cierre_diario.saldo_inicial) + ((SELECT SUM(amount_currency_local) FROM bank_extracts WHERE type = "credito" and bank_id_input IN (SELECT id FROM banks as bkns WHERE bkns.currency = banks.currency) AND DATE(created_at) = DATE("' . $date_now->format('Y-m-d') .'")) - (SELECT SUM(amount_currency_local) FROM bank_extracts WHERE type = "debito" and bank_id_input IN (SELECT id FROM banks as bkns WHERE bkns.currency = banks.currency) AND DATE(created_at) = DATE("' . $date_now->format('Y-m-d') .'"))) as saldo_final'),
                        'banks.currency'
                    )
                    ->paginate();

        return view('admin.reports.balance_gral', compact('pageTitle', 'items'));
    }

    public function balanceGeneralMovimientos(Request $request)
    {
        $pageTitle = 'Reporte general de movimientos';
        return view('admin.reports.reporte_general_movimientos', compact('pageTitle'));
    }

    public function balanceGeneralMovimientosExcel(Request $request)
    {
        $to = Carbon::create($request->input('start_date') . ' 22:00:00');
        $from = Carbon::create($request->input('start_date') . ' 22:00:00')->add('-1 day');
        // $period = CarbonPeriod::create($from, $to);
        
        if($request->btnAction == 'CSV')
        {
            return Excel::download(new ExportBanksRangeMovs($from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')), 'reporte_general_movimientos.csv', \Maatwebsite\Excel\Excel::CSV);
        }else{
            return Excel::download(new ExportBanksRangeMovs($from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')), 'reporte_general_movimientos.xlsx');
        }
    }

    public function detalleMovimientosBancoExcel(Request $request)
    {
        $to = Carbon::create($request->input('end_date') . ' 22:00:00');
        $from = Carbon::create($request->input('start_date') . ' 22:00:00')->add('-1 day');
        // $period = CarbonPeriod::create($from, $to);
        $bank = Bank::find($request->bank_id);
        if($request->btnAction == 'CSV')
        {
            return Excel::download(new ExportBanksDetailMovs($from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'), $request->bank_id), $bank->name . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }else{
            return Excel::download(new ExportBanksDetailMovs($from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'), $request->bank_id), $bank->name . '.xlsx');
        }
    }
}
