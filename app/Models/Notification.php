<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Traits\ReadOnlyTrait;

class Notification extends Model
{
    use HasFactory;
    // use ReadOnlyTrait;

    protected $connection = 'greep-notification';

    protected $guarded = [];

}
