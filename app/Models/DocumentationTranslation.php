<?php

namespace App\Models;

use Database\Factories\DocumentationTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentationTranslation extends Model
{
    /** @use HasFactory<DocumentationTranslationFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['lang', 'title', 'slug', 'excerpt', 'body'];

    public function documentation(): BelongsTo
    {
        return $this->belongsTo(Documentation::class);
    }
}
