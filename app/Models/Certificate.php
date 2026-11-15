<?php

namespace App\Models;

use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['media_id', 'issued_year', 'display_order', 'status', 'created_by', 'updated_by'];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CertificateTranslation::class);
    }
}
