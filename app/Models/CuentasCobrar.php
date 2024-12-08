<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CuentasCobrar extends Model
{
    use Searchable;
    protected $table = "cuentas_cobrar";

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function bank() {
        return $this->belongsTo(Bank::class);
    }
    
    public function bankOutput() {
        return $this->belongsTo(Bank::class, 'bank_id_output');
    }
    
    public function sendMoney() {
        return $this->belongsTo(SendMoney::class);
    }
    
    public function sumPagos() {
        return CuentasCobrarPagos::where('cuentas_cobrar_id', $this->id)->where('cancelado', 0)->sum('amount');
    }

    public function daysDiff(){
        $date = \Carbon\Carbon::parse($this->created_at);
        $now = \Carbon\Carbon::parse($this->fecha_vencimiento)->format('Y-m-d') . '23:59:59';

        return $date->diffInDays($now);

    }

    public function daysDiffVencido(){
        $date = \Carbon\Carbon::parse($this->fecha_vencimiento);
        $now = \Carbon\Carbon::parse(\Carbon\Carbon::now()->format('Y-m-d') . '23:59:59');
        $diff = $date->diffInDays($now);
        
        return $now > $date ? $diff : 0;

    }

    public function scopePending($query) {
        return $query->where('status', 'pending');
    }

    public function scopeFinished($query) {
        return $query->where('status', 'finished');
    }

}
