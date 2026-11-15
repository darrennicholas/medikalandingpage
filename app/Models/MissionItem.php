<?php

namespace App\Models;

use Database\Factories\MissionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MissionItem extends Model
{
    /** @use HasFactory<MissionItemFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['display_order', 'status', 'created_by', 'updated_by'];

    public function translations(): HasMany
    {
        return $this->hasMany(MissionItemTranslation::class);
    }
}
