<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CuentasPagarPagos extends Model
{
    use Searchable;
    protected $table = "cuentas_pagar_pagos";
    
    public function cuentaPagar() {
        return $this->belongsTo(CuentasPagar::class, 'cuentas_pagar_id');
    }
}
