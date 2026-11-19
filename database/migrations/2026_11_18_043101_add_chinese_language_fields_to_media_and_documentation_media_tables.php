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
        Schema::table('media', function (Blueprint $table): void {
            $table->string('alt_text_zh', 200)->nullable()->after('alt_text_en');
        });

        Schema::table('documentation_media', function (Blueprint $table): void {
            $table->string('caption_zh', 200)->nullable()->after('caption_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentation_media', function (Blueprint $table): void {
            $table->dropColumn('caption_zh');
        });

        Schema::table('media', function (Blueprint $table): void {
            $table->dropColumn('alt_text_zh');
        });
    }
};
