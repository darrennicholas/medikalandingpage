<?php

namespace App\Models;

use Database\Factories\ProductCategoryTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCategoryTranslation extends Model
{
    /** @use HasFactory<ProductCategoryTranslationFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['lang', 'name', 'slug'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }
}
