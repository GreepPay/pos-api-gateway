<?php

namespace App\Models\Wallet;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class UserBank extends Model
{
    use HasFactory;
    use ReadOnlyTrait;
    protected $connection = 'greep-wallet';
    protected $guarded = [];

    public function user()
{
    return $this->belongsTo(User::class, 'user_id', 'id');
}
}
