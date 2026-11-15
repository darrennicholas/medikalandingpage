<?php

namespace App\Models;

use Database\Factories\SeoMetaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoMeta extends Model
{
    /** @use HasFactory<SeoMetaFactory> */
    use HasFactory;

    protected $fillable = ['page_key', 'lang', 'meta_title', 'meta_description', 'canonical_url', 'og_media_id', 'robots_index'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['robots_index' => 'boolean'];
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }
}
