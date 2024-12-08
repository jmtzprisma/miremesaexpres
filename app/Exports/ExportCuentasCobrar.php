<?php

namespace App\Exports;

use App\Models\BankExtract;
use Maatwebsite\Excel\Concerns\{
    WithEvents,
    WithHeadings,
    WithMapping
};
use App\Models\CuentasCobrar;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportCuentasCobrar implements FromCollection, WithMapping, WithHeadings, WithEvents
{
    
    private $status;
    private $from;
    private $to;

    public function __construct($status, $from, $to)
    {
        $this->status = $status;
        $this->from = $from;
        $this->to = $to;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $status = $this->status;
        $from = $this->from;
        $to = $this->to;

        return CuentasCobrar::$status()->with('sendMoney', 'user')->whereBetween('created_at', [$from, $to])->get();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'ID Cliente',
            'Nombre',
            'Concepto',
            'N° de Crédito',
            'Cargo',
            'Abono',
            'Días Crédito',
            'Días Vencido',
            'Saldo',
            'Vencimiento',
        ];
    }

    
    public function map($cuenta): array
    {
        
        $sendMoney = $cuenta->sendMoney;
        
        $str_cliente = '';
        $user_fullname = '';
        $mtcn_number = '';
        $str_amount = '';
        $saldo = '';
        $str_ven = 'Pagado';
        $dias_vencido = 0;
        if($sendMoney){
            $str_cliente = $sendMoney->user_id;
        }else{
            $str_cliente = 'CXC';
        }
        if($sendMoney){
            $user_fullname = $cuenta->user->fullname;
        }else{
            $user_fullname = $cuenta->nombre_proveedor;
        }
        if($sendMoney) $mtcn_number = $sendMoney->mtcn_number;
        if($sendMoney){
            $str_amount = showAmount($sendMoney->sending_amount) . ' ' . $sendMoney->sending_currency;
        }else{
            $str_amount = showAmount($cuenta->amount_currency_local);
        }
        if($sendMoney){
            $saldo = showAmount($sendMoney->sending_amount - $cuenta->sumPagos());
        }else{
            $saldo = showAmount($cuenta->amount_currency_local - $cuenta->sumPagos());
        }
        if($cuenta->daysDiffVencido() == 0){
            $str_ven = 'No vencido';
        }elseif($cuenta->daysDiffVencido() <= 15){
            $str_ven = 'De 1 a 15 días';
        }elseif($cuenta->daysDiffVencido() <= 30){
            $str_ven = 'De 16 a 30 días';
        }else{
            $str_ven = 'Más de 30 días';
        }
        if($cuenta->status != 'finished'){
            $dias_vencido = $cuenta->daysDiffVencido();
        }



        return [
            $cuenta->created_at,
            $str_cliente,
            $user_fullname,
            $cuenta->concepto,
            $mtcn_number,
            $str_amount,
            showAmount($cuenta->sumPagos()),
            $cuenta->daysDiff(),
            $dias_vencido,
            $saldo,
            $str_ven,
        ];


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
                // format to impar row
                foreach ($event->sheet->getRowIterator() as $fila) {
                    foreach ($fila->getCellIterator() as $celda) {
                        if ($celda->getRow() % 2 != 0) {
                            if ($celda->getRow() === 1) {
                                continue;
                            }
                            $event->sheet->getStyle("A{$celda->getRow()}:K{$celda->getRow()}")->applyFromArray([
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
}
