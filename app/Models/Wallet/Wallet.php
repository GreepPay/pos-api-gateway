<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $connection = 'greep-wallet';
    protected $guarded = [];
}
