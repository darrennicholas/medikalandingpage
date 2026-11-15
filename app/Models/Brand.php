<?php

namespace App\Models;

use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'logo_media_id', 'website_url', 'display_order', 'status', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['display_order' => 'integer'];
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }
}
