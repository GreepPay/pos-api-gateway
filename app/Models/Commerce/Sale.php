<?php

namespace App\Models\Commerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MichaelAChrisco\ReadOnlyTrait;

/**
 * @property string $id
 * @property string $transaction_id
 * @property string $customer_id
 * @property float $subtotal_amount
 * @property float $tax_amount
 * @property float $discount_amount
 * @property float $total_amount
 * @property string $currency
 * @property string $status
 * @property array $items
 * @property array|null $applied_discounts
 * @property array|null $tax_details
 * @property array $payment_details
 * @property array|null $refund_details
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Commerce\Customer $customer
 */
class Sale extends Model
{
    use ReadOnlyTrait;

    protected $connection = "greep-commerce";
    protected $table = "commerce.sales";

    protected $casts = [
        'items' => 'array',
        'applied_discounts' => 'array',
        'tax_details' => 'array',
        'payment_details' => 'array',
        'refund_details' => 'array',
        'metadata' => 'array',
        'subtotal_amount' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}