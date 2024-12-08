<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CuentasCobrarPagos extends Model
{
    use Searchable;
    protected $table = "cuentas_cobrar_pagos";
    
    public function cuentaCobrar() {
        return $this->belongsTo(CuentasCobrar::class, 'cuentas_cobrar_id');
    }
}
