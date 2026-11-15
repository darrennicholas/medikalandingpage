<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LegacyProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryNames = [
            'Molecular',
            'Rapid Test (Master)',
            'Point of Care Testing (POCT)',
            'Medical Instruments',
            'Medical Consumables',
        ];

        foreach ($categoryNames as $index => $name) {
            $categoryExists = ProductCategory::query()
                ->whereHas('translations', fn ($query) => $query->where('lang', 'id')->where('name', $name))
                ->exists();

            if ($categoryExists) {
                continue;
            }

            $category = ProductCategory::query()->create([
                'display_order' => $index + 3,
                'status' => 'published',
            ]);

            $category->translations()->create([
                'lang' => 'id',
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }
}
