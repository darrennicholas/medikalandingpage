<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use App\Models\JobOpening;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $staticPages = [];
        foreach (['', '/tentang', '/produk', '/produk/semua', '/dokumentasi', '/karir', '/kontak'] as $path) {
            foreach (['id', 'en'] as $language) {
                $staticPages[] = url('/'.$language.$path);
            }
        }

        $products = Product::query()->where('status', 'published')->whereHas('category', fn ($query) => $query->where('status', 'published'))
            ->with('translations')->get()->flatMap(fn (Product $product): array => $this->localizedDetailUrls($product->translations, 'produk'));
        $articles = Documentation::query()->where('status', 'published')->where('type', 'article')->with('translations')->get()
            ->flatMap(fn (Documentation $item): array => $this->localizedDetailUrls($item->translations, 'dokumentasi'));
        $activities = Documentation::query()->where('status', 'published')->where('type', 'activity')->get()
            ->flatMap(fn (Documentation $item): array => ['id' => url('/id/dokumentasi/kegiatan/'.$item->id), 'en' => url('/en/dokumentasi/kegiatan/'.$item->id)]);
        $careers = JobOpening::query()->where('status', 'open')->where(fn ($query) => $query->whereNull('closing_date')->orWhereDate('closing_date', '>=', today()))->get()
            ->flatMap(fn (JobOpening $job): array => ['id' => url('/id/karir/'.$job->id), 'en' => url('/en/karir/'.$job->id)]);

        return response()->view('sitemap', [
            'urls' => collect($staticPages)->merge($products)->merge($articles)->merge($activities)->merge($careers)->unique()->values(),
        ])->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /** @return list<string> */
    private function localizedDetailUrls(Collection $translations, string $path): array
    {
        $fallback = $translations->firstWhere('lang', 'id')?->slug;
        $urls = [];
        foreach (['id', 'en'] as $language) {
            $slug = $translations->firstWhere('lang', $language)?->slug ?: $fallback;
            if (filled($slug)) {
                $urls[] = url('/'.$language.'/'.$path.'/'.$slug);
            }
        }

        return $urls;
    }

    public function robots(): Response
    {
        return response("User-agent: *\nAllow: /\nDisallow: /admin\nSitemap: ".url('/sitemap.xml')."\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
