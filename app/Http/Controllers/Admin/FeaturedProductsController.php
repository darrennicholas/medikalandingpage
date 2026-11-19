<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FeaturedProductsController extends Controller
{
    public function index(Request $request): View
    {
        $categories = ProductCategory::query()->where('status', 'published')->with('translations')->orderBy('display_order')->get();
        $selectedCategory = $categories->firstWhere('id', $request->integer('category')) ?? $categories->first();
        $products = $selectedCategory
            ? $selectedCategory->products()->where('status', 'published')->with('translations', 'media')->orderBy('display_order')->get()
            : collect();
        $selectedProducts = $selectedCategory
            ? $selectedCategory->featuredProducts()->where('products.status', 'published')->whereColumn('products.category_id', 'category_featured_products.category_id')->with('translations', 'media')->get()
            : collect();
        $selectedIds = $selectedProducts->pluck('id')->all();

        $products->each(function ($product): void {
            if ($product->media) {
                $product->media->setAttribute('admin_preview_url', route('media.stream', ['media' => $product->media->id]));
            }
        });

        $selectedProducts->each(function ($product): void {
            if ($product->media) {
                $product->media->setAttribute('admin_preview_url', route('media.stream', ['media' => $product->media->id]));
            }
        });

        return view('admin.content.featured', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'products' => $products,
            'selectedProducts' => $selectedProducts,
            'selectedIds' => $selectedIds,
        ]);
    }
}
