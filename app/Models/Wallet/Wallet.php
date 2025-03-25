<?php

namespace App\Models\Wallet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class Wallet extends Model
{
    use HasFactory;
    use ReadOnlyTrait;
    protected $connection = 'greep-wallet';
    protected $guarded = [];
}
