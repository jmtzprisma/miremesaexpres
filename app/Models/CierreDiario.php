<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreDiario extends Model
{
    use HasFactory;
    protected $table = 'cierre_diario';
    protected $fillable = [
        'bank_id',
        'date',
        'saldo_inicial',
        'ingresos',
        'egresos',
        'saldo_final',
        'revenue',
    ];
}
