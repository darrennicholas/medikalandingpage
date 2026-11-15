<?php

namespace App\Models;

use Database\Factories\MissionItemTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionItemTranslation extends Model
{
    /** @use HasFactory<MissionItemTranslationFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['lang', 'text'];

    public function missionItem(): BelongsTo
    {
        return $this->belongsTo(MissionItem::class);
    }
}
