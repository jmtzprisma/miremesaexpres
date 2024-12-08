<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\BankController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CierreDiario extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:cierrediario';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cierre diario de bancos';

    protected $bankController;

    public function __construct(BankController $bankController)
    {
        parent::__construct();
        $this->bankController = $bankController;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        $date = date('Y-m-d H:i:s');
        Log::info("Generate report {$date}");

        try{
            $this->bankController->cierreDiario();
        } catch (Exception $e) {
            Log::info("Error al generar el reporte: {$e->getMessage()}");
        }
        return 0;

        return Command::SUCCESS;
    }
}
