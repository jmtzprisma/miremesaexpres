<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use GlobalStatus, Searchable;

    // public function deliveryMethod(){
    //     return $this->hasOneThrough(DeliveryMethod::class, CountryDeliveryMethod::class, 'delivery_method_id', 'id', 'country_delivery_method_id');
    // }

    // public function country(){
    //     return $this->hasOneThrough(Country::class, CountryDeliveryMethod::class, 'country_id', 'id', 'country_delivery_method_id');
    // }

    public function form(){
        return $this->belongsTo(Form::class);
    }

    public function countryDeliveryMethod(){
        return $this->belongsTo(CountryDeliveryMethod::class);
    }

    
}
