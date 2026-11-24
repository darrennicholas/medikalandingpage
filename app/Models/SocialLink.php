<?php

namespace App\Models;

use Database\Factories\SocialLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialLink extends Model
{
    /** @use HasFactory<SocialLinkFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['platform', 'url', 'preview_media_id', 'display_order', 'status', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['display_order' => 'integer'];
    }

    public function previewMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'preview_media_id');
    }
}
