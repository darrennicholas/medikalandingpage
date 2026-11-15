<?php

namespace App\Models;

use Database\Factories\SocialLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialLink extends Model
{
    /** @use HasFactory<SocialLinkFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['platform', 'url', 'display_order', 'status', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['display_order' => 'integer'];
    }
}
