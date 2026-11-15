<?php

namespace App\Models;

use Database\Factories\ContentBlockTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentBlockTranslation extends Model
{
    /** @use HasFactory<ContentBlockTranslationFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['lang', 'title', 'subtitle', 'body', 'cta_label', 'copyright_text', 'metrics', 'buttons'];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'buttons' => 'array',
        ];
    }

    public function contentBlock(): BelongsTo
    {
        return $this->belongsTo(ContentBlock::class);
    }
}
