<?php

namespace App\Exports;

use App\Exports\Sheets\SheetDay;
use App\Models\CierreDiario;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\{Exportable, WithMultipleSheets};
use Carbon\{Carbon, CarbonPeriod};

class ExportBanksRange implements WithMultipleSheets, FromCollection
{

    private $period;

    public function __construct($period)
    {
        $this->period = $period;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return CierreDiario::all();
    }
    
    /**
     * @return array
     */
    public function sheets(): array
    {
        $obj = [];

        foreach($this->period as $itm)
        {
            $obj[] = new SheetDay($itm);
        }

        return $obj;
    }

}
