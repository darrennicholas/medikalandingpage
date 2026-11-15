<?php

namespace App\Models;

use Database\Factories\CompanySummaryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySummary extends Model
{
    /** @use HasFactory<CompanySummaryFactory> */
    use HasFactory;

    protected $table = 'company_summary';

    protected $fillable = ['established_year', 'partner_count', 'province_count', 'updated_by'];

    protected function casts(): array
    {
        return ['established_year' => 'integer', 'partner_count' => 'integer', 'province_count' => 'integer'];
    }
}
