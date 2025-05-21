<?php

namespace App\Models\Commerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MichaelAChrisco\ReadOnlyTrait;

/**
 * @property int $id
 * @property int $product_id
 * @property string|null $variant_id
 * @property int|null $sale_id
 * @property string $user_id
 * @property string $ticket_type
 * @property float $price
 * @property string $qr_code
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Commerce\Product $product
 * @property-read \App\Models\Commerce\Sale|null $sale
 */
class Ticket extends Model
{
    use ReadOnlyTrait;

    protected $connection = "greep-commerce";
    protected $table = "commerce.tickets";

    protected $casts = [
        'price' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}