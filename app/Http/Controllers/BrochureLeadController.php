<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BrochureLeadController extends Controller
{
    public function create(string $locale, Product $product): Response
    {
        $this->ensureAvailable($product);
        $product->load('translations');

        return response()->view('brochure-request', ['product' => $product, 'language' => $locale])->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request, string $locale, Product $product): RedirectResponse
    {
        $this->ensureAvailable($product);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+() .-]{6,30}$/'],
            'company' => ['nullable', 'string', 'max:150'],
            'consent' => ['accepted'],
        ]);
        DB::table('brochure_leads')->insert([
            'product_id' => $product->id, 'name' => $data['name'], 'email' => $data['email'],
            'phone' => $data['phone'], 'company' => $data['company'] ?? null,
            'consented_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $request->session()->put('brochure_grants.'.$product->id, true);
        $path = preg_replace('#^(?:public/)?brochures/#', '', ltrim($product->brochure_path, '/'));

        return redirect()->route('stored.brochure', ['path' => $path]);
    }

    public function index(): View
    {
        $leads = DB::table('brochure_leads')->join('products', 'products.id', '=', 'brochure_leads.product_id')
            ->leftJoin('product_translations', function ($join): void {
                $join->on('product_translations.product_id', '=', 'products.id')->where('product_translations.lang', '=', 'id');
            })->select('brochure_leads.*', 'product_translations.name as product_name')
            ->orderByDesc('brochure_leads.id')->paginate(20);

        return view('admin.content.brochure-leads', compact('leads'));
    }

    private function ensureAvailable(Product $product): void
    {
        abort_unless($product->status === 'published' && $product->category?->status === 'published' && filled($product->brochure_path), 404);
    }
}
