<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Ad
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $from_currency
 * @property string $to_currency
 * @property string $rate
 * @property string $min_amount
 * @property string $max_amount
 * @property string|null $payout_address
 * @property string|null $address_details
 * @property string|null $payout_banks
 * @property string|null $business_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $orders
 * @property-read int|null $orders_count
 * @method static \Illuminate\Database\Eloquent\Builder|Ad newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ad newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ad query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereAddressDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereFromCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereMaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereMinAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad wherePayoutAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad wherePayoutBanks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereToCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereUuid($value)
 * @mixin \Eloquent
 */
class Ad extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'from_currency',
        'to_currency',
        'rate',
        'min_amount',
        'max_amount',
        'payout_address',
        'address_details',
        'payout_banks',
        'business_id',
        'status',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

     /**
     * Get the user that created the ad.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class, 'user_id');
    }
}