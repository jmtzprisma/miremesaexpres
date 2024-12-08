<?php

namespace App\Exports\Sheets;

use App\Console\Commands\CierreDiario;
use App\Models\BankExtract;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    WithEvents,
    WithHeadings,
    WithMapping,
    WithTitle
};
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;

class SheetDay implements FromCollection, WithMapping, WithHeadings, WithEvents, WithTitle
{
    private $date;
    private $title;
    public function __construct($date)
    {
        $this->date = $date;
        $this->title = Carbon::create($date)->format('d-m');
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $database = DB::table('cierre_diario')
                    ->join('banks', 'cierre_diario.bank_id', '=', 'banks.id')
                    ->whereDate('date', DB::raw('DATE("' . $this->date .'")'))->orderBy('banks.currency')
                    ->select(
                        'cierre_diario.revenue', 
                        'banks.name', 
                        'banks.currency',
                        DB::raw('SUM(cierre_diario.saldo_inicial) as saldo_inicial'),
                        DB::raw('(SUM(cierre_diario.saldo_inicial) + IFNULL((SELECT SUM(amount_currency_local) FROM bank_extracts WHERE type = "credito" and bank_id_input = banks.id AND DATE(created_at) = DATE("' . $this->date .'")),0) - IFNULL((SELECT SUM(amount_currency_local) FROM bank_extracts WHERE type = "debito" and bank_id_input = banks.id AND DATE(created_at) = DATE("' . $this->date .'")), 0)) as saldo_final'),
                        DB::raw('IFNULL((SELECT SUM(amount_currency_local) FROM bank_extracts WHERE type = "credito" and bank_id_input = banks.id AND DATE(created_at) = DATE("' . $this->date .'")), 0) as ingresos'),
                        DB::raw('IFNULL((SELECT SUM(amount_currency_local) FROM bank_extracts WHERE type = "debito" and bank_id_input = banks.id AND DATE(created_at) = DATE("' . $this->date .'")), 0) as egresos')
                    )
                    ->groupBy('banks.id')
                    ->get();
        return $database;
    }

    public function headings(): array
    {
        return [
            'Nombre Banco',
            'Moneda',
            'Saldo Inicial',
            'Ingresos',
            'Egresos',
            'Saldo Final',
            'Ganancias',
        ];
    }

    
    public function map($itm): array
    {
        return [
            $itm->name,
            $itm->currency,
            $itm->saldo_inicial,
            $itm->ingresos,
            $itm->egresos,
            $itm->saldo_final,
            $itm->revenue,
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
                
                // format to impar row
                foreach ($event->sheet->getRowIterator() as $fila) {
                    foreach ($fila->getCellIterator() as $celda) {
                        if ($celda->getRow() % 2 != 0) {
                            if ($celda->getRow() === 1) {
                                continue;
                            }
                            $event->sheet->getStyle("A{$celda->getRow()}:G{$celda->getRow()}")->applyFromArray([
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

    public function title(): string
    {
        return $this->title;
    }
}
