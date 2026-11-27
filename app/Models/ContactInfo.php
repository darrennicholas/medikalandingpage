<?php

namespace App\Models;

use Database\Factories\ContactInfoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactInfo extends Model
{
    /** @use HasFactory<ContactInfoFactory> */
    use HasFactory;

    protected $table = 'contact_info';

    protected $fillable = ['label', 'phone', 'email', 'maps_url', 'latitude', 'longitude', 'updated_by'];

    public function translations(): HasMany
    {
        return $this->hasMany(ContactInfoTranslation::class);
    }
}
