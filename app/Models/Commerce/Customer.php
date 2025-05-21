<?php

namespace App\Models\Commerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MichaelAChrisco\ReadOnlyTrait;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $phone_number
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property string|null $country
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Commerce\Order[] $orders
 */
class Customer extends Model
{
    use ReadOnlyTrait;

    protected $connection = "greep-commerce";
    protected $table = "commerce.customers";

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}