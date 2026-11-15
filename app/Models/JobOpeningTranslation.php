<?php

namespace App\Models;

use Database\Factories\JobOpeningTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOpeningTranslation extends Model
{
    /** @use HasFactory<JobOpeningTranslationFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['lang', 'title', 'description', 'requirements'];

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }
}
