<?php

namespace App\Models;

use Database\Factories\CertificateTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTranslation extends Model
{
    /** @use HasFactory<CertificateTranslationFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['lang', 'title', 'description'];

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }
}
