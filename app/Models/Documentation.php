<?php

namespace App\Models;

use Database\Factories\DocumentationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documentation extends Model
{
    /** @use HasFactory<DocumentationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['type', 'cover_media_id', 'event_date', 'status', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'display_order' => 'integer'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentationTranslation::class);
    }

    public function gallery(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'documentation_media')
            ->withPivot(['caption_id', 'caption_en', 'caption_zh', 'display_order'])
            ->orderByPivot('display_order');
    }
}
