<?php

namespace App\Models\Commerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Commerce\Product[] $products
 */
class Category extends Model
{
    use ReadOnlyTrait;

    protected $connection = "greep-commerce";
    protected $table = "commerce.categories";

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'commerce.product_category');
    }
}