<?php

namespace App\Models;

use Database\Factories\ContactInfoTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInfoTranslation extends Model
{
    /** @use HasFactory<ContactInfoTranslationFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['lang', 'address', 'office_hours'];

    public function contactInfo(): BelongsTo
    {
        return $this->belongsTo(ContactInfo::class);
    }
}
