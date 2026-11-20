<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'media_id', 'brochure_path', 'buy_url', 'parameters', 'subcategory', 'new_until',
        'logo_top_media_id', 'logo_bottom_media_id', 'display_order', 'status', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['display_order' => 'integer', 'parameters' => 'array', 'new_until' => 'date'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function topLogo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_top_media_id');
    }

    public function bottomLogo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_bottom_media_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function featuredCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'category_featured_products', 'product_id', 'category_id')
            ->withPivot('display_order');
    }
}
