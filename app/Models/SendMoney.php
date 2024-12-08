<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SendMoney extends Model {
    use Searchable;

    protected $casts = [
        'sender'            => 'object',
        'recipient'         => 'object',
        'service_form_data' => 'object',
        'received_at'       => 'datetime'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function agent() {
        return $this->belongsTo(Agent::class);
    }
    public function sendingCountry() {
        return $this->belongsTo(Country::class, 'sending_country_id');
    }
    public function recipientCountry() {
        return $this->belongsTo(Country::class, 'recipient_country_id');
    }

    public function service() {
        return $this->belongsTo(Service::class);
    }
    public function countryDeliveryMethod() {
        return $this->belongsTo(CountryDeliveryMethod::class);
    }

    public function deposit() {
        return $this->hasOne(Deposit::class);
    }

    public function payoutBy() {
        return $this->hasOne(Agent::class, 'id', 'payout_by');
    }

    public function sourceOfFund() {
        return $this->belongsTo(SourceOfFund::class);
    }

    public function sendingPurpose() {
        return $this->belongsTo(SendingPurpose::class);
    }

    public function scopeInitiated($query) {
        return $query->where('status', Status::SEND_MONEY_INITIATED);
    }

    public function scopePending($query) {
        return $query->where('status', Status::SEND_MONEY_PENDING);
    }

    public function scopeCompleted($query) {
        return $query->where('status', Status::SEND_MONEY_COMPLETED);
    }

    public function scopeRefunded($query) {
        return $query->where('status', Status::SEND_MONEY_REFUNDED);
    }

    public function scopePaymentPending($query) {
        return $query->where('payment_status', Status::PAYMENT_PENDING);
    }

    public function scopePaymentRejected($query) {
        return $query->where('payment_status', Status::PAYMENT_REJECT);
    }

    public function scopeFilterAgent($query) {
        return $query->where('agent_id', authAgent()->id);
    }

    public function scopeFilterUser($query) {
        return $query->where('user_id', auth()->id());
    }

    public function scopeFilterByDay($query, $day = 7) {
        return $query->whereDate('created_at', '>=', now()->subDays($day));
    }

    public function statusBadge(): Attribute {
        return new Attribute(
            function () {
                $className = '';

                if ($this->status == Status::SEND_MONEY_PENDING || $this->status == Status::SEND_MONEY_INITIATED) {
                    $className .= 'warning';
                    $text = 'Pending';
                } elseif ($this->status == Status::SEND_MONEY_REFUNDED) {
                    $className .= 'danger';
                    $text = 'Refunded';
                } else {
                    $className .= 'success';
                    $text = 'Completed';
                }
                return "<span class='badge badge--$className'>" . trans($text) . "</span>";
            }
        );
    }

    public function paymentStatusBadge(): Attribute {
        return new Attribute(
            function () {
                $className = '';
                if ($this->payment_status == Status::PAYMENT_INITIATE && $this->deposit) {
                    $className .= 'dark';
                    $text = 'Initiated';
                } elseif ($this->payment_status == Status::PAYMENT_PENDING) {
                    $className .= 'warning';
                    $text = 'Pending';
                } elseif ($this->payment_status == Status::PAYMENT_SUCCESS) {
                    $className .= 'success';
                    $text = 'Received';
                } elseif ($this->payment_status == Status::PAYMENT_REJECT) {
                    $className .= 'danger';
                    $text = 'Reject';
                } else {
                    $className .= 'danger';
                    $text = 'Yet to Pay';
                }
                return "<span class='badge badge--$className'>" . trans($text) . "</span>";
            }
        );
    }

    public function getSenderInfoAttribute() {
        if ($this->user_id) {
            $user = $this->user;
            $address = $user->address;
            $sender['name']    = $user->fullname;
            $sender['mobile']  = $user->mobile;
            $sender['address'] = $address->zip . ', ' . $address->state  . ', ' .  $address->city  . ', ' .  $address->country;
            return (object) $sender;
        } else {
            return $this->sender;
        }
    }

    public function paidAmount() {
        $totalAmount = $this->base_currency_amount + $this->base_currency_charge;

        if ($this->deposit) {
            $totalAmount += $this->deposit->charge;
        }

        return $totalAmount;
    }
}
