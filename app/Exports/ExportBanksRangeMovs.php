<?php

namespace App\Exports;

use App\Models\Bank;
use App\Models\BankExtract;
use App\Models\Deposit;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\{
    WithEvents,
    WithHeadings,
    WithMapping,
    WithColumnFormatting
};
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportBanksRangeMovs implements FromCollection, WithMapping, WithColumnFormatting, WithHeadings, WithEvents
{
    
    private $from;
    private $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = BankExtract::with('sendMoney', 'deposit', 'cuentaCobrar', 'cuentaPagar')->whereBetween('created_at', [$this->from, $this->to])->get();
        return $data;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Operación',
            'Id Externo',
            'Persona',
            'Recibo->Entrego',
            'recibo-moneda',
            'recibo-referencia',
            'Estatus',
            'recibo.EUR',
            'recibo.USDT',
            'recibo.VES',
            'recibo.USD',
            'entrego-moneda',
            'entrego-referencia',
            'Nombre del Beneficiario',
            'Nro de Identificion del Beneficiario',
            'entrego.EUR',
            'entrego.USDT',
            'entrego.VES',
            'entrego.USD',
            'tasa',
            'Utilidad'
        ];
    }

    public function map($extract): array
    {
        $obj = [];
        if($extract->deposit){
            $itm = $extract->deposit;
            $user = \App\Models\User::find($itm->user_id);
            if($itm->sendMoney && $itm->sendMoney->status == 1)
                foreach ($itm->sendMoney->service_form_data as $val)
                    if(strtolower($val->name) == 'numero de cuenta')
                    {
                        $numcuenta = $val->value;
                    }

            foreach ($itm->detail as $val)
                if(strtolower($val->name) == strtolower('BANCOS DISPONIBLES') || strtolower($val->name) == 'bancos')
                {
                    $banco_envia = $val->value;
                }

            $bank = Bank::find($extract->bank_id_input);
                
            $bank_out = null;
            $rate = '';
            $revenue = '';
            if($itm->sendMoney)
            {
                $bank_out_id = BankExtract::where('type', 'debito')->where('send_money_id', $itm->sendMoney->id)->first();

                if($bank_out_id)
                {
                    $bank_out = Bank::find($bank_out_id->bank_id_input);
                    $rate = $bank_out_id->rate;
                    $revenue = $bank_out_id->revenue;
                }
            }
                
            $obj = [
                $extract->created_at,
                $extract->id . '|' . $itm->trx . ' ' . ($extract->reason == 'REVERSO OPERACION' ? 'REVERSO OPERACION' : ''),
                is_null($user) ? '' : $user->username,
                is_null($user) ? '' : $user->firstname . ' ' . $user->lastname,
                $itm->method_currency .'-'. (!is_null($bank) ? $bank->name : ''),
                $itm->method_currency,
                '',
                'v',
                ($itm->method_currency == 'EUR') ? showAmount($itm->final_amo, 2, false) : '',
                ($itm->method_currency == 'USDT') ? showAmount($itm->final_amo, 2, false) : '',
                ($itm->method_currency == 'VEF') ? showAmount($itm->final_amo, 2, false) : '',
                ($itm->method_currency == 'USD') ? showAmount($itm->final_amo, 2, false) : '',
                ($itm->sendMoney && $itm->sendMoney->status == 1) ? ($itm->sendMoney->recipient_currency .'-'. (!is_null($bank_out) ? $bank_out->name : '')) : '',
                $numcuenta ?? '',
                $extract->id . '|' . ($itm->sendMoney && $itm->sendMoney->status == 1) ? $itm->sendMoney->recipient->name : '',
                ($itm->sendMoney && $itm->sendMoney->status == 1) ? str_pad($itm->sendMoney->recipient->id, 10, "0", STR_PAD_LEFT) : '',
                ($itm->sendMoney && $itm->sendMoney->status == 1) ? (($itm->sendMoney->recipient_currency == 'EUR') ? showAmount($itm->sendMoney->recipient_amount, 2, false) : '') : '',
                ($itm->sendMoney && $itm->sendMoney->status == 1) ? (($itm->sendMoney->recipient_currency == 'USDT') ? showAmount($itm->sendMoney->recipient_amount, 2, false) : '') : '',
                ($itm->sendMoney && $itm->sendMoney->status == 1) ? (($itm->sendMoney->recipient_currency == 'VEF') ? showAmount($itm->sendMoney->recipient_amount, 2, false) : '') : '',
                ($itm->sendMoney && $itm->sendMoney->status == 1) ? (($itm->sendMoney->recipient_currency == 'USD') ? showAmount($itm->sendMoney->recipient_amount, 2, false) : '') : '',

                ($itm->sendMoney && $itm->sendMoney->status == 1) ? $rate : '',
                ($itm->sendMoney && $itm->sendMoney->status == 1) ? $revenue : '',
                
            ];
        }
        elseif($extract->cuentaCobrar)
        {
            if($extract->type == 'debito')
            {
                if($extract->cuentaCobrar->sendMoney)
                {
                    $sendMoney = $extract->cuentaCobrar->sendMoney;
                    $bank = Bank::find($extract->bank_id_input);
                    
                    $obj = [
                        $extract->created_at,
                        $extract->id,
                        '','','','','','',
                        ($sendMoney->sending_currency == 'EUR') ? showAmount($sendMoney->amount_currency_local, 2, false) : '',
                        ($sendMoney->sending_currency == 'USDT') ? showAmount($sendMoney->amount_currency_local, 2, false) : '',
                        ($sendMoney->sending_currency == 'VEF') ? showAmount($sendMoney->amount_currency_local, 2, false) : '',
                        ($sendMoney->sending_currency == 'USD') ? showAmount($sendMoney->amount_currency_local, 2, false) : '',
                        ($sendMoney && $sendMoney->status == 1) ? ($sendMoney->recipient_currency .'-'. (!is_null($bank) ? $bank->name : '')) : '',
                        $numcuenta ?? '',
                        $extract->id . '|' .($sendMoney && $sendMoney->status == 1) ? $sendMoney->recipient->name : '',
                        ($sendMoney && $sendMoney->status == 1) ? str_pad($sendMoney->recipient->id, 10, "0", STR_PAD_LEFT) : '',
                        ($sendMoney && $sendMoney->status == 1) ? (($sendMoney->recipient_currency == 'EUR') ? showAmount($sendMoney->recipient_amount, 2, false) : '') : '',
                        ($sendMoney && $sendMoney->status == 1) ? (($sendMoney->recipient_currency == 'USDT') ? showAmount($sendMoney->recipient_amount, 2, false) : '') : '',
                        ($sendMoney && $sendMoney->status == 1) ? (($sendMoney->recipient_currency == 'VEF') ? showAmount($sendMoney->recipient_amount, 2, false) : '') : '',
                        ($sendMoney && $sendMoney->status == 1) ? (($sendMoney->recipient_currency == 'USD') ? showAmount($sendMoney->recipient_amount, 2, false) : '') : '',

                        ($sendMoney && $sendMoney->status == 1) ? (!is_null($bank) ? $extract->rate : '') : '',
                        ($sendMoney && $sendMoney->status == 1) ? (!is_null($bank) ? $extract->revenue : '') : '',
                    ];
                }elseif($extract->cuentaCobrar->bank){
                    $bank = $extract->cuentaCobrar->bank;
                    $bankOutput = $extract->cuentaCobrar->bankOutput;
                    
                    $obj = [
                        $extract->created_at,
                        $extract->id,
                        '',
                        $extract->cuentaCobrar->nombre_proveedor,
                        $bankOutput->name . ' ' . $bankOutput->currency,
                        '','',
                        $extract->cuentaCobrar->status == 'pending' ? 'Pendiente' : 'Liquidada',
                        ($bankOutput->currency == 'EUR') ? showAmount($extract->cuentaCobrar->sumPagos(), 2, false) : '',
                        ($bankOutput->currency == 'USDT') ? showAmount($extract->cuentaCobrar->sumPagos(), 2, false) : '',
                        ($bankOutput->currency == 'VEF') ? showAmount($extract->cuentaCobrar->sumPagos(), 2, false) : '',
                        ($bankOutput->currency == 'USD') ? showAmount($extract->cuentaCobrar->sumPagos(), 2, false) : '',
                        $bank->name . ' ' . $bank->currency,
                        '',
                        $extract->concepto,
                        '',
                        ($bank->currency == 'EUR') ? showAmount($extract->amount_currency_local, 2, false) : '',
                        ($bank->currency == 'USDT') ? showAmount($extract->amount_currency_local, 2, false) : '',
                        ($bank->currency == 'VEF') ? showAmount($extract->amount_currency_local, 2, false) : '',
                        ($bank->currency == 'USD') ? showAmount($extract->amount_currency_local, 2, false) : '',
                        $extract->rate,
                        $extract->revenue,
                    ];
                }
            }
        }
        elseif($extract->cuentaPagar)
        {
            if($extract->type == 'credito'){
  
                $bank = $extract->cuentaPagar->bank;
                $user = \App\Models\User::find($extract->cuentaPagar->user_id);                
                $bankOutput = $extract->cuentaPagar->bankOutput;

                $obj = [
                    $extract->created_at,
                    $extract->id,
                    '',
                    $extract->cuentaPagar->proveedor,
                    $bank->name . ' ' . $bank->currency,
                    '','',
                    $extract->cuentaPagar->status == 'pending' ? 'Pendiente' : 'Liquidada',
                    ($bank->currency == 'EUR') ? showAmount($extract->cuentaPagar->amount_currency_local, 2, false) : '',
                    ($bank->currency == 'USDT') ? showAmount($extract->cuentaPagar->amount_currency_local, 2, false) : '',
                    ($bank->currency == 'VEF') ? showAmount($extract->cuentaPagar->amount_currency_local, 2, false) : '',
                    ($bank->currency == 'USD') ? showAmount($extract->cuentaPagar->amount_currency_local, 2, false) : '',
                    $bankOutput->name . ' ' . $bankOutput->currency,
                    '',
                    $extract->concepto,
                    '',
                    ($extract->cuentaPagar->status == 'pending' ? '' : (($bankOutput->currency == 'EUR') ? showAmount($extract->cuentaPagar->amount_currency_convert, 2, false) : '')),
                    ($extract->cuentaPagar->status == 'pending' ? '' : (($bankOutput->currency == 'USDT') ? showAmount($extract->cuentaPagar->amount_currency_convert, 2, false) : '')),
                    ($extract->cuentaPagar->status == 'pending' ? '' : (($bankOutput->currency == 'VEF') ? showAmount($extract->cuentaPagar->amount_currency_convert, 2, false) : '')),
                    ($extract->cuentaPagar->status == 'pending' ? '' : (($bankOutput->currency == 'USD') ? showAmount($extract->cuentaPagar->amount_currency_convert, 2, false) : '')),
                    '','',
                ];
            }
        }else if(!is_null($extract->send_money_id) && is_null($extract->deposit_id) && is_null($extract->cxc_id) && is_null($extract->cxp_id) && $extract->sendMoney->coins_sent == 1){
            
            $bank = \App\Models\Bank::find($extract->bank_id_input);
            $itm = $extract->sendMoney;
            
            $obj = [
                $extract->created_at,
                $extract->id,
                '','','PAGO POR CRIPTOPOCKET','','','','','','','',
                $itm->recipient_currency .'-'. (!is_null($bank) ? $bank->name : ''),
                '',
                $extract->id . '|' .$itm->recipient->name,
                str_pad($itm->recipient->id, 10, "0", STR_PAD_LEFT),
                (($itm->recipient_currency == 'EUR') ? showAmount($itm->recipient_amount, 2, false) : ''),
                (($itm->recipient_currency == 'USDT') ? showAmount($itm->recipient_amount, 2, false) : ''),
                (($itm->recipient_currency == 'VEF') ? showAmount($itm->recipient_amount, 2, false) : ''),
                (($itm->recipient_currency == 'USD') ? showAmount($itm->recipient_amount, 2, false) : ''),

                '',
                '',
            ];
        }else if(is_null($extract->send_money_id) && is_null($extract->deposit_id) && is_null($extract->cxc_id) && is_null($extract->cxp_id)){

            if($extract->type == 'debito')
            {
                $bank = \App\Models\Bank::find($extract->bank_id_input);
                
                $obj = [
                    $extract->created_at,
                    $extract->id,
                    '','','','','','','','','','',
                    $bank->name . ' ' . $bank->currency,
                    '',
                    $extract->id . '|' . $extract->reason . (($extract->reason == 'OTRO') ? $extract->title . ' ' . $extract->description : ''),
                    '',
                    (is_null($bank)) ? '' : (($bank->currency == 'EUR') ? showAmount($extract->amount_currency_local, 2, false) : ''),
                    (is_null($bank)) ? '' : (($bank->currency == 'USDT') ? showAmount($extract->amount_currency_local, 2, false) : ''),
                    (is_null($bank)) ? '' : (($bank->currency == 'VEF') ? showAmount($extract->amount_currency_local, 2, false) : ''),
                    (is_null($bank)) ? '' : (($bank->currency == 'USD') ? showAmount($extract->amount_currency_local, 2, false) : ''),
                    $extract->rate,
                    $extract->revenue,
                ];

            }elseif($extract->type == 'credito'){
  
                $bank = \App\Models\Bank::find($extract->bank_id_input);
                $user = \App\Models\User::find($extract->user_id);

                $obj = [
                    $extract->created_at,
                    $extract->id . '|' . $extract->reason . (($extract->reason == 'OTRO') ? $extract->title . ' ' . $extract->description : ''),
                    is_null($user) ? '' : $user->username,
                    is_null($user) ? '' : $user->firstname . ' ' . $user->lastname,
                    $bank->name . ' ' . $bank->currency,
                    '',
                    '',
                    'v',
                    ($bank->currency == 'EUR') ? showAmount($extract->amount_currency_local, 2, false) : '',
                    ($bank->currency == 'USDT') ? showAmount($extract->amount_currency_local, 2, false) : '',
                    ($bank->currency == 'VEF') ? showAmount($extract->amount_currency_local, 2, false) : '',
                    ($bank->currency == 'USD') ? showAmount($extract->amount_currency_local, 2, false) : '',
                    
                    '','','','','','','','','',''
                ];
            }
        }

        return $obj;
    }
    
    public function registerEvents(): array
    {
        
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getColumnDimension('A')->setAutoSize(true);
                $event->sheet->getColumnDimension('B')->setAutoSize(true);
                $event->sheet->getColumnDimension('C')->setAutoSize(true);
                $event->sheet->getColumnDimension('D')->setAutoSize(true);
                $event->sheet->getColumnDimension('E')->setAutoSize(true);
                $event->sheet->getColumnDimension('F')->setAutoSize(true);
                $event->sheet->getColumnDimension('G')->setAutoSize(true);
                $event->sheet->getColumnDimension('H')->setAutoSize(true);
                $event->sheet->getColumnDimension('I')->setAutoSize(true);
                $event->sheet->getColumnDimension('J')->setAutoSize(true);
                $event->sheet->getColumnDimension('K')->setAutoSize(true);
                $event->sheet->getColumnDimension('L')->setAutoSize(true);
                $event->sheet->getColumnDimension('M')->setAutoSize(true);
                $event->sheet->getColumnDimension('N')->setAutoSize(true);
                $event->sheet->getColumnDimension('O')->setAutoSize(true);
                $event->sheet->getColumnDimension('P')->setAutoSize(true);
                $event->sheet->getColumnDimension('Q')->setAutoSize(true);
                $event->sheet->getColumnDimension('R')->setAutoSize(true);
                $event->sheet->getColumnDimension('S')->setAutoSize(true);
                // format to impar row
                foreach ($event->sheet->getRowIterator() as $fila) {
                    foreach ($fila->getCellIterator() as $celda) {

                        if ($celda->getRow() % 2 != 0) {
                            // if ($celda->getRow() === 1) {
                            //     continue;
                            // }
                            $event->sheet->getStyle("A{$celda->getRow()}:T{$celda->getRow()}")->applyFromArray([
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'color' => ['rgb' => 'e9f4fa'],
                                ],
                            ]);
                        }
                    }
                }
                foreach ($event->sheet->getRowIterator() as $fila) {
                    foreach ($fila->getCellIterator() as $celda) {
                        $event->sheet->getStyle("I{$celda->getRow()}:L{$celda->getRow()}")->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'color' => ['rgb' => '07b1e1'],
                            ],
                        ]);
                    }
                }
                foreach ($event->sheet->getRowIterator() as $fila) {
                    foreach ($fila->getCellIterator() as $celda) {
                        $event->sheet->getStyle("Q{$celda->getRow()}:T{$celda->getRow()}")->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'color' => ['rgb' => '17cd4d'],
                            ],
                        ]);
                    }
                }
            }
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'S' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'T' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        ];
    }
}
