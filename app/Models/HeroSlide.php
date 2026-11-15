<?php

namespace App\Models;

use Database\Factories\HeroSlideFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeroSlide extends Model
{
    /** @use HasFactory<HeroSlideFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['media_id', 'cta_url', 'display_order', 'status', 'created_by', 'updated_by', 'draft_payload'];

    protected function casts(): array
    {
        return ['draft_payload' => 'array'];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(HeroSlideTranslation::class);
    }
}
