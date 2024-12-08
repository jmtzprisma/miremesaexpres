<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryDeliveryMethod extends Model
{
    protected $table = 'country_delivery_method';
    public $timestamps = false;

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function deliveryMethod()
    {
        return $this->belongsTo(DeliveryMethod::class);
    }

    public function charge()
    {
        return $this->hasOne(DeliveryCharge::class);
    }

}
