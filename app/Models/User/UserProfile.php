<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class UserProfile extends Model
{
    use HasFactory;
    use ReadOnlyTrait;

    protected $connection = 'greep-user';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'auth_user_id', 'id');
    }

    public function getUserTypeAttribute()
    {
        return $this->attributes['user_type'] ?? null;
    }

    public function business()
    {
        return $this->hasOne(Business::class, 'auth_user_id', 'auth_user_id');
    }

    // Define the relationship to Rider details
    public function rider()
    {
        return $this->hasOne(Rider::class, 'auth_user_id', 'auth_user_id');
    }

    // Define the relationship to Customer details
    public function customer()
    {
        return $this->hasOne(Customer::class, 'auth_user_id', 'auth_user_id');
    }
}
