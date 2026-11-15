<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CmsProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = json_decode(File::get(resource_path('products.json')), true, 512, JSON_THROW_ON_ERROR);
        $mediaByPath = [];

        foreach ($catalog as $category) {
            foreach ($category['products'] as $product) {
                foreach (['image', 'logo_top', 'logo_bottom'] as $assetKey) {
                    $assetPath = $product[$assetKey] ?? null;
                    if ($assetPath) {
                        $mediaByPath[$assetPath] = $this->storeMediaRecord($assetPath);
                    }
                }
            }
        }

        foreach ($catalog as $categoryOrder => $categoryData) {
            $category = ProductCategory::query()->whereHas('translations', fn ($query) => $query->where('lang', 'id')->where('name', $categoryData['name']))->first();
            if ($category === null) {
                $category = ProductCategory::query()->create(['display_order' => $categoryOrder + 1, 'status' => 'published']);
            }
            $category->forceFill(['display_order' => $categoryOrder + 1, 'status' => 'published'])->save();
            $category->translations()->updateOrCreate(
                ['lang' => 'id'],
                ['name' => $categoryData['name'], 'slug' => Str::slug($categoryData['name'])],
            );
            $englishCategoryName = match ($categoryData['name']) {
                'Drugs Abuse' => 'Drugs of Abuse',
                'Infection Disease' => 'Infectious Diseases',
                default => $categoryData['name'],
            };
            $category->translations()->updateOrCreate(
                ['lang' => 'en'],
                ['name' => $englishCategoryName, 'slug' => Str::slug($englishCategoryName)],
            );

            foreach ($categoryData['products'] as $productOrder => $productData) {
                $parameters = $productData['parameters'] ?? [];
                $slug = Str::slug($productData['name'].'-'.implode('-', $parameters));
                $product = Product::query()->whereHas('translations', fn ($query) => $query->where('lang', 'id')->where('slug', $slug))->first();
                if ($product === null) {
                    $product = Product::query()->create([
                        'category_id' => $category->id,
                        'media_id' => $mediaByPath[$productData['image']]->id,
                        'brochure_path' => $productData['brochure_url'] ?: null,
                        'buy_url' => $productData['buy_url'] ?? null,
                        'parameters' => $parameters,
                        'subcategory' => $productData['subcategory'] ?? null,
                        'logo_top_media_id' => $mediaByPath[$productData['logo_top']]->id,
                        'logo_bottom_media_id' => $mediaByPath[$productData['logo_bottom']]->id,
                        'display_order' => $productOrder + 1,
                        'status' => 'published',
                    ]);
                } else {
                    $product->forceFill([
                        'category_id' => $category->id,
                        'media_id' => $mediaByPath[$productData['image']]->id,
                        'brochure_path' => $productData['brochure_url'] ?: null,
                        'buy_url' => $productData['buy_url'] ?? null,
                        'parameters' => $parameters,
                        'subcategory' => $productData['subcategory'] ?? null,
                        'logo_top_media_id' => $mediaByPath[$productData['logo_top']]->id,
                        'logo_bottom_media_id' => $mediaByPath[$productData['logo_bottom']]->id,
                        'display_order' => $productOrder + 1,
                        'status' => 'published',
                    ])->save();
                }

                $description = $productData['subtitle'] ?? '';
                $productDescription = trim($description.' '.($productData['parameter'] ?? ''));
                $product->translations()->updateOrCreate(['lang' => 'id'], [
                    'name' => $productData['name'], 'slug' => $slug,
                    'description' => $productDescription,
                ]);
                $englishSubtitle = match ($productData['subtitle'] ?? '') {
                    'Napza Test | Drug Abuse' => 'Drug abuse test',
                    'Rapid Test | Infection Disease' => 'Rapid test | Infectious disease',
                    default => $productData['subtitle'] ?? '',
                };
                $product->translations()->updateOrCreate(['lang' => 'en'], [
                    'name' => $productData['name'],
                    'slug' => $slug,
                    'description' => trim($englishSubtitle.' '.($productData['parameter'] ?? '')),
                ]);
            }

            $category->featuredProducts()->sync(
                $category->products()->where('status', 'published')->orderBy('display_order')->limit((int) config('cms.products.featured_per_category', 6))
                    ->pluck('products.id')->values()->mapWithKeys(fn (int $productId, int $index): array => [$productId => ['display_order' => $index + 1]])->all(),
            );
        }
    }

    private function storeMediaRecord(string $assetPath): Media
    {
        $fullPath = public_path($assetPath);
        $imageInfo = @getimagesize($fullPath);
        $mimeType = $imageInfo['mime'] ?? 'application/octet-stream';
        $fileName = basename($assetPath);

        return Media::query()->updateOrCreate(
            ['path' => $assetPath],
            [
                'file_name' => $fileName,
                'original_name' => $fileName,
                'mime_type' => $mimeType,
                'size_bytes' => File::exists($fullPath) ? File::size($fullPath) : 0,
                'width' => $imageInfo[0] ?? null,
                'height' => $imageInfo[1] ?? null,
                'alt_text_id' => pathinfo($fileName, PATHINFO_FILENAME),
            ],
        );
    }
}
