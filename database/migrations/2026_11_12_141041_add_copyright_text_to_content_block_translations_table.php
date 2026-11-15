<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('content_block_translations', function (Blueprint $table): void {
            $table->string('copyright_text', 150)->nullable()->after('cta_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_block_translations', function (Blueprint $table): void {
            $table->dropColumn('copyright_text');
        });
    }
};
