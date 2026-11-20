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
        Schema::table('documentations', function (Blueprint $table): void {
            $table->string('cta_url', 500)->nullable();
        });
        Schema::table('documentation_translations', function (Blueprint $table): void {
            $table->string('cta_label', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentation_translations', function (Blueprint $table): void {
            $table->dropColumn('cta_label');
        });
        Schema::table('documentations', function (Blueprint $table): void {
            $table->dropColumn('cta_url');
        });
    }
};
