<?php

namespace App\Models;

use Database\Factories\ContentBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentBlock extends Model
{
    /** @use HasFactory<ContentBlockFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['block_key', 'media_id', 'cta_url', 'status', 'created_by', 'updated_by', 'draft_payload', 'footer_show_phone', 'footer_show_email'];

    protected function casts(): array
    {
        return ['draft_payload' => 'array', 'footer_show_phone' => 'boolean', 'footer_show_email' => 'boolean'];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ContentBlockTranslation::class);
    }
}
