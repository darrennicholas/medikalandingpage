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
        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->string('text_alignment', 16)->default('center')->after('cta_url');
        });

        Schema::table('hero_slide_translations', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('subtitle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_slide_translations', function (Blueprint $table): void {
            $table->dropColumn('description');
        });

        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->dropColumn('text_alignment');
        });
    }
};
