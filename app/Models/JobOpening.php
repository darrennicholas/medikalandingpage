<?php

namespace App\Models;

use Database\Factories\JobOpeningFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOpening extends Model
{
    /** @use HasFactory<JobOpeningFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['employment_type', 'location', 'posted_at', 'closing_date', 'apply_url', 'status', 'source', 'external_ref', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['posted_at' => 'date', 'closing_date' => 'date'];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(JobOpeningTranslation::class);
    }
}
