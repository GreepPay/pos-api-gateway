<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Wallet\PointTransaction;
use App\Models\Wallet\Transaction;
use App\Models\Wallet\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'greep-auth';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'auth_user_id', 'id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id', 'id');
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class, 'user_id', 'id');
    }

    public function getCombinedTransactionsAttribute()
    {
        $normal = $this->transactions;
        $points = $this->pointTransactions;

        return $normal->merge($points)->sortByDesc('created_at')->values();
    }

}
