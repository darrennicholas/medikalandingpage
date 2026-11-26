<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_subcategories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->restrictOnDelete();
            $table->string('name', 100);
            $table->timestamps();
            $table->unique(['category_id', 'name']);
        });

        DB::table('products')->whereNotNull('category_id')->whereNotNull('subcategory')->where('subcategory', '<>', '')
            ->select('category_id', 'subcategory')->distinct()->orderBy('category_id')->get()
            ->each(fn ($product) => DB::table('product_subcategories')->insertOrIgnore([
                'category_id' => $product->category_id, 'name' => trim($product->subcategory),
                'created_at' => now(), 'updated_at' => now(),
            ]));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_subcategories');
    }
};
