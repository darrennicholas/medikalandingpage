<?php

namespace App\Models;

use Database\Factories\ProductCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    /** @use HasFactory<ProductCategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['display_order', 'status'];

    protected function casts(): array
    {
        return ['display_order' => 'integer'];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductCategoryTranslation::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function featuredProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_featured_products', 'category_id', 'product_id')
            ->withPivot('display_order')
            ->orderByPivot('display_order');
    }
}
