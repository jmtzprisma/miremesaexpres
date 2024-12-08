<?php

namespace App\Exports;

use App\Models\BankExtract;
use Maatwebsite\Excel\Concerns\{
    WithEvents,
    WithHeadings,
    WithMapping
};
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportBanks implements FromCollection, WithMapping, WithHeadings, WithEvents
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
        return BankExtract::where('type', 'debito')->where('reason', '<>', 'COMPRA MONEDA')->whereNull('send_money_id')->whereNull('deposit_id')->whereNull('cxc_id')->whereNull('cxp_id')->whereBetween('created_at', [$this->from, $this->to])->get();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Descripcion del Gasto',
            'Tipo de Gasto',
            'Solicitante del Gasto',
            'Beneficiario',
            'Tipo de Operación',
            'Tipo de Moneda',
            'Monto',
            'Banco Emisor',
            'Banco Receptor',
        ];
    }

    
    public function map($itm): array
    {
        $user = \App\Models\Admin::find($itm->user_id);

        return [
            $itm->created_at,
            $itm->description,
            'Variable',
            is_null($user) ? '' : $user->firstname . ' ' . $user->lastname,
            $itm->beneficiario,
            $itm->tipo_operacion,
            is_null($itm->bank_id_input) ? '' : \App\Models\Bank::find($itm->bank_id_input)->currency,
            $itm->amount_currency_local,
            is_null($itm->bank_id_input) ? '' : \App\Models\Bank::find($itm->bank_id_input)->name,
            is_null($itm->bank_id_ouput) ? '' : \App\Models\Bank::find($itm->bank_id_ouput)->name
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
