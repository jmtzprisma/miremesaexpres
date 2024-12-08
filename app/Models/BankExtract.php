<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BankExtract extends Model
{
    use Searchable;
    protected $table = "bank_extracts";

    public function cuentaPagar() {
        return $this->belongsTo(CuentasPagar::class, 'cxp_id');
    }

    public function cuentaCobrar() {
        return $this->belongsTo(CuentasCobrar::class, 'cxc_id');
    }
    
    public function deposit() {
        return $this->belongsTo(Deposit::class, 'deposit_id', 'id');
    }

    public function sendMoney() {
        return $this->belongsTo(SendMoney::class, 'send_money_id', 'id');
    }
}
