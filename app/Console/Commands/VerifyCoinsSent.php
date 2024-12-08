<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\SendMoneyController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifyCoinsSent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:verifycoinssent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify if coins is sent';

    protected $sendMoneyController;

    public function __construct(SendMoneyController $sendMoneyController)
    {
        parent::__construct();
        $this->sendMoneyController = $sendMoneyController;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $this->sendMoneyController->verifyCoinsSent();
        } catch (Exception $e) {
            Log::info("Error al generar el reporte: {$e->getMessage()}");
        }

        return Command::SUCCESS;
    }
}
