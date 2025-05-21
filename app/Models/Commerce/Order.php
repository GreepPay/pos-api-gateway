<?php

namespace App\Models\Commerce;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

/**
 * @property string $id
 * @property string $order_number
 * @property string $customer_id
 * @property string $sale_id
 * @property array $items
 * @property float $subtotal_amount
 * @property float $tax_amount
 * @property float $discount_amount
 * @property float $total_amount
 * @property string $currency
 * @property string $status
 * @property string|null $shipping_address
 * @property string|null $billing_address
 * @property string|null $payment_method
 * @property string $payment_status
 * @property array|null $payment_details
 * @property array|null $applied_discounts
 * @property array|null $tax_details
 * @property array|null $refund_details
 * @property array $status_history
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Commerce\Delivery[] $deliveries
 * @property-read \App\Models\Commerce\Customer $customer
 * @property-read \App\Models\Commerce\Sale $sale
 */
class Order extends Model
{
    use ReadOnlyTrait;

    protected $connection = "greep-commerce";
    protected $table = "commerce.orders";

    protected $casts = [
        'items' => 'array',
        'payment_details' => 'array',
        'applied_discounts' => 'array',
        'tax_details' => 'array',
        'refund_details' => 'array',
        'status_history' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}