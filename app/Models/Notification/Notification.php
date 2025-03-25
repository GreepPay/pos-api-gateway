<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class Notification extends Model
{
    use HasFactory;
    use ReadOnlyTrait;

    protected $connection = 'greep-notification';

    protected $guarded = [];

}
