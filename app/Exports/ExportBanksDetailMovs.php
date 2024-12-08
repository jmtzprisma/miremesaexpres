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

class ExportBanksDetailMovs implements FromCollection, WithMapping, WithColumnFormatting, WithHeadings, WithEvents
{
    
    private $from;
    private $to;
    private $bank_id;

    public function __construct($from, $to, $bank_id)
    {
        $this->from = $from;
        $this->to = $to;
        $this->bank_id = $bank_id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = BankExtract::with('sendMoney', 'deposit', 'cuentaCobrar', 'cuentaPagar')->where('bank_id_input', $this->bank_id)->whereBetween('created_at', [$this->from, $this->to])->get();
        return $data;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Tipo',
            'Nombre',
            'Operación',
            'ID',
            'Monto',
            'Saldo Antes',
            'Saldo Despues'
        ];
    }

    
    public function map($extract): array
    {
        $tipo_operacion = '';
        $nombre = '';
        $admin = \App\Models\Admin::find($extract->user_id);


        if($extract->deposit){
            $itm = $extract->deposit;
            $user = \App\Models\User::find($itm->user_id);
            $nombre = is_null($user) ? '' : $user->firstname . ' ' . $user->lastname;
            //$nombre = $admin->name . ' (' . $admin->username .')';
            $tipo_operacion = 'Envio de dinero';
        }elseif($extract->sendMoney){
            $itm = $extract->sendMoney;
            $user = \App\Models\User::find($itm->user_id);
            $nombre = is_null($user) ? '' : $user->firstname . ' ' . $user->lastname;
            //$nombre = $admin->name . ' (' . $admin->username .')';
            $tipo_operacion = 'Envio de dinero';
        }elseif($extract->cuentaCobrar){
            $itm = $extract->cuentaCobrar;
            $user = \App\Models\User::find($itm->user_id);
            $nombre = is_null($user) ? '' : $user->firstname . ' ' . $user->lastname;
            //$nombre = $admin->name . ' (' . $admin->username .')';
            $tipo_operacion = 'Cuenta por cobrar';
        }elseif($extract->cuentaPagar){
            $itm = $extract->cuentaPagar;
            $user = \App\Models\User::find($itm->user_id);
            $nombre = is_null($user) ? '' : $user->firstname . ' ' . $user->lastname;
            $nombre = $admin->name . ' (' . $admin->username .')';
            $tipo_operacion = 'Cuenta por pagar';
        }else if(!is_null($extract->send_money_id) && is_null($extract->deposit_id) && is_null($extract->cxc_id) && is_null($extract->cxp_id) && $extract->sendMoney->coins_sent == 1){
            $itm = $extract->sendMoney;
            $user = \App\Models\User::find($itm->user_id);
            $nombre = is_null($user) ? '' : $user->firstname . ' ' . $user->lastname;
            //$nombre = $admin->name . ' (' . $admin->username .')';
            $tipo_operacion = 'PAGO POR CRIPTOPOCKET';
        }else if(is_null($extract->send_money_id) && is_null($extract->deposit_id) && is_null($extract->cxc_id) && is_null($extract->cxp_id)){
            $nombre = $admin->name . ' (' . $admin->username .')';
            $tipo_operacion = $extract->reason . (($extract->reason == 'OTRO') ? $extract->title . ' ' . $extract->description : '');
        }

        $obj = [
            $extract->created_at,
            $extract->type,
            $nombre,
            $tipo_operacion,
            $extract->id,
            $extract->amount_currency_local,
            $extract->saldo_banco_antes,
            $extract->saldo_banco_despues,
        ];

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
                // format to impar row
                foreach ($event->sheet->getRowIterator() as $fila) {
                    foreach ($fila->getCellIterator() as $celda) {

                        if ($celda->getRow() % 2 != 0) {
                            // if ($celda->getRow() === 1) {
                            //     continue;
                            // }
                            $event->sheet->getStyle("A{$celda->getRow()}:H{$celda->getRow()}")->applyFromArray([
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'color' => ['rgb' => 'e9f4fa'],
                                ],
                            ]);
                        }
                    }
                }
            }
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
