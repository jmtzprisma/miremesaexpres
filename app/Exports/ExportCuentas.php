<?php

namespace App\Exports;

use App\Models\BankExtract;
use Maatwebsite\Excel\Concerns\{
    WithEvents,
    WithHeadings,
    WithMapping
};
use App\Models\CuentasPagar;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportCuentas implements FromCollection, WithMapping, WithHeadings, WithEvents
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

        return CuentasPagar::$status()->with('bank', 'user')->whereBetween('created_at', [$from, $to])->get();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'N° de Operación',
            'Banco',
            'Concepto',
            'Monto',
            'Abono',
            'Días Crédito',
            'Días Vencido',
            'Saldo',
            'Vencimiento',
        ];
    }

    
    public function map($itm): array
    {
        
        $str_ven = 'Pagado';
        if($itm->daysDiffVencido() == 0){
            $str_ven = 'No vencido';
        }elseif($itm->daysDiffVencido() <= 15){
            $str_ven = 'De 1 a 15 días';
        }elseif($itm->daysDiffVencido() <= 30){
            $str_ven = 'De 16 a 30 días';
        }else{
            $str_ven = 'Más de 30 días';
        }     

        $dias_vencido = 0;
        if($itm->status != 'finished'){
            $dias_vencido = $itm->daysDiffVencido();
        }

        return [
            showDateTime($itm->created_at),
            $itm->id,
            $itm->bank->name,
            $itm->concepto,
            showAmount($itm->amount_currency_local),
            showAmount($itm->sumPagos()),
            $itm->daysDiff(),
            $dias_vencido,
            showAmount($itm->amount_currency_local - $itm->sumPagos()),
            $str_ven
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
                // format to impar row
                foreach ($event->sheet->getRowIterator() as $fila) {
                    foreach ($fila->getCellIterator() as $celda) {
                        if ($celda->getRow() % 2 != 0) {
                            if ($celda->getRow() === 1) {
                                continue;
                            }
                            $event->sheet->getStyle("A{$celda->getRow()}:J{$celda->getRow()}")->applyFromArray([
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
