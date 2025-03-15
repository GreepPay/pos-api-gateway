<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Traits\ReadOnlyTrait;

class UserProfile extends Model
{
    use HasFactory;
    // use ReadOnlyTrait;

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
}
