<?php

namespace App\Models\Commerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

/**
 * @property string $id
 * @property int $business_id
 * @property string $sku
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property float $price
 * @property string $currency
 * @property string|null $tax_code
 * @property string $type
 * @property string $status
 * @property array $variants
 * @property int|null $inventory_count
 * @property int|null $stock_threshold
 * @property bool $is_backorder_allowed
 * @property string|null $download_url
 * @property int|null $download_limit
 * @property array|null $dimensions
 * @property float|null $weight
 * @property string|null $billing_interval
 * @property int|null $trial_period_days
 * @property string|null $event_type
 * @property \Illuminate\Support\Carbon|null $event_start_date
 * @property \Illuminate\Support\Carbon|null $event_end_date
 * @property string|null $venue_name
 * @property string|null $event_online_url
 * @property array|null $event_location
 * @property int|null $event_capacity
 * @property int $event_registered_count
 * @property bool $event_waitlist_enabled
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property bool $is_visible
 * @property array $images
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Commerce\Category[] $categories
 * @property-read \App\Models\Commerce\Product[] $relatedProducts
 * @property-read \App\Models\User\Business $business
 */
class Product extends Model
{
    use ReadOnlyTrait;

    protected $connection = "greep-commerce";
    protected $table = "commerce.products";

    protected $casts = [
        'variants' => 'array',
        'dimensions' => 'array',
        'event_location' => 'array',
        'images' => 'array',
        'is_backorder_allowed' => 'boolean',
        'event_waitlist_enabled' => 'boolean',
        'is_visible' => 'boolean',
        'inventory_count' => 'integer',
        'stock_threshold' => 'integer',
        'download_limit' => 'integer',
        'event_capacity' => 'integer',
        'event_registered_count' => 'integer',
        'trial_period_days' => 'integer',
        'weight' => 'float',
        'price' => 'float',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User\Business::class, 'business_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'commerce.product_category');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'commerce.related_products', 'product_id', 'related_product_id');
    }
}