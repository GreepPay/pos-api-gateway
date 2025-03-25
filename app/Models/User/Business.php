<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class Business extends Model
{
    use HasFactory;
    use ReadOnlyTrait;

    protected $connection = 'greep-user';

    protected $guarded = [];
}
