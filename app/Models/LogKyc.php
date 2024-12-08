<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class LogKyc extends Model
{
    use Searchable;
    protected $table = "log_kyc";
}
