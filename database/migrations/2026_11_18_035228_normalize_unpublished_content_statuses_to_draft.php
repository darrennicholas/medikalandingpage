<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['brands', 'certificates', 'hero_slides', 'content_blocks', 'products', 'product_categories', 'documentations', 'mission_items', 'social_links'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'status')) {
                DB::table($table)->where('status', 'unpublished')->update(['status' => 'draft']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Legacy unpublished states are intentionally not restored.
    }
};
