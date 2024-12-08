<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model {
    use HasFactory, Searchable, GlobalStatus;

    public function countryServices() {
        return $this->hasMany(Service::class);
    }

    public function conversionRates() {
        return $this->hasMany(CurrencyConversionRate::class, 'from_country', 'id');
    }

    public function services() {
        return $this->hasManyThrough(Service::class, CountryDeliveryMethod::class);
    }

    public function sendingTransfers() {
        return $this->hasMany(SendMoney::class, 'sending_country_id');
    }

    public function receivingTransfers() {
        return $this->hasMany(SendMoney::class, 'recipient_country_id');
    }

    public function deliveryCharges() {
        return $this->hasMany(DeliveryCharge::class);
    }

    public function deliveryMethods() {
        return $this->belongsToMany(DeliveryMethod::class, 'country_delivery_method', 'country_id', 'delivery_method_id');
    }

    public function countryDeliveryMethods() {
        return $this->hasMany(CountryDeliveryMethod::class);
    }

    public function agentStatus(): Attribute {
        return new Attribute(
            function () {
                if ($this->has_agent) {
                    $class = 'success';
                    $text = 'Yes';
                } else {
                    $class = 'danger';
                    $text = 'No';
                }

                $html = '<span class="badge badge--' . $class . '">' . trans($text) . '</span>';

                return $html;
            }
        );
    }

    public function scopeSending($query) {
        $query->where('is_sending', Status::YES);
    }

    public function scopeReceiving($query) {
        $query->where('is_receiving', Status::YES);
    }

    public function scopeHasAgent($query) {
        $query->where('has_agent', Status::YES);
    }

    public function scopeReceivableCountries($query) {
        $query->active()->receiving()
            ->with([
                'countryDeliveryMethods.deliveryMethod' => function ($query) {
                    $query->select('id', 'name')->active();
                },
                'countryDeliveryMethods.charge:country_delivery_method_id,fixed_charge,percent_charge'
            ])
            ->where(function ($query) {
                $query->whereHas('countryDeliveryMethods.deliveryMethod', function ($deliveryMethod) {
                    $deliveryMethod->active();
                })
                    ->orWhere(function ($query) {
                        if (gs()->agent_module) {
                            $query->hasAgent();
                        }
                    });
            });
    }
}
