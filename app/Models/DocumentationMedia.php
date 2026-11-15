<?php

namespace App\Models;

use Database\Factories\DocumentationMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentationMedia extends Model
{
    /** @use HasFactory<DocumentationMediaFactory> */
    use HasFactory;

    public $timestamps = false;
}
