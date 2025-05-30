<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property string $uuid
 * @property int $ad_id
 * @property int $user_id
 * @property float $amount
 * @property \Illuminate\Support\Carbon $expired_at
 * @property float $expected_amount
 * @property string $status
 * @property string $payment_amount
 * @property string $payment_type
 * @property string $payout_option
 * @property string $pickup_location_address_line
 * @property string $pickup_location_city
 * @property string $pickup_location_country
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ad $ad
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereExpectedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePayoutOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePickupLocationAddressLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePickupLocationCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePickupLocationCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUuid($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    protected $fillable = [
        'uuid',
        'ad_id',
        'user_id',
        'amount',
        'expired_at',
        'expected_amount',
        'status',
        'payment_amount',
        'payment_type',
        'payout_option',
        'pickup_location_address_line',
        'pickup_location_city',
        'pickup_location_country',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'expired_at' => 'datetime',
    ];

    /**
     * Get the ad associated with this order.
     */
    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    /**
     * Get the user who placed this order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}