<?php

namespace App\Models\Commerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MichaelAChrisco\ReadOnlyTrait;

/**
 * @property string $id
 * @property string $order_id
 * @property string $tracking_number
 * @property string $status
 * @property \Illuminate\Support\Carbon $estimated_delivery_date
 * @property \Illuminate\Support\Carbon|null $actual_delivery_date
 * @property string $delivery_address
 * @property array|null $metadata
 * @property array|null $tracking_updates
 * @property array|null $delivery_attempts
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Commerce\Order $order
 */
class Delivery extends Model
{
    use ReadOnlyTrait;

    protected $connection = "greep-commerce";
    protected $table = "commerce.deliveries";

    protected $casts = [
        'estimated_delivery_date' => 'datetime',
        'actual_delivery_date' => 'datetime',
        'metadata' => 'array',
        'tracking_updates' => 'array',
        'delivery_attempts' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}