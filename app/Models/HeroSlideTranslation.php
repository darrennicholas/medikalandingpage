<?php

namespace App\Models;

use Database\Factories\HeroSlideTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroSlideTranslation extends Model
{
    /** @use HasFactory<HeroSlideTranslationFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['lang', 'title', 'subtitle', 'cta_label'];

    public function heroSlide(): BelongsTo
    {
        return $this->belongsTo(HeroSlide::class);
    }
}
