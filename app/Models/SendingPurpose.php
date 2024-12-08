<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendingPurpose extends Model
{
    use HasFactory, Searchable;
    public function scopeActive()
    {
        return $this->where('status', Status::ENABLE);
    }
}
