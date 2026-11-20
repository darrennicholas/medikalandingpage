<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PublicStoredAssetController extends Controller
{
    public function image(Request $request, string $path): Response
    {
        $path = $this->normalizePath($path);
        abort_if(str_starts_with($path, 'media/'), 404);

        $media = $path === 'medika-logo.png' ? null : Media::query()->whereIn('path', ['images/'.$path, 'images/site-assets/'.$path])->first();
        if ($media !== null) {
            return app(MediaController::class)->show($request, $media);
        }

        abort_unless($path === 'medika-logo.png', 404);
        $assetPath = 'images/site-assets/'.$path;
        $disk = Storage::disk('public');
        abort_unless($disk->exists($assetPath), 404);

        return response()->file($disk->path($assetPath), [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function brochure(Request $request, string $path): Response
    {
        $path = $this->normalizePath($path);
        $storedPath = 'brochures/'.$path;
        $disk = Storage::disk('public');
        abort_unless($disk->exists($storedPath), 404);

        $product = Product::query()->with(['category', 'translations'])->whereIn('brochure_path', [$path, $storedPath, 'public/'.$storedPath])->first();
        $isPublic = $product === null || ($product->status === 'published' && $product->category?->status === 'published');
        $user = $request->user();
        $isCmsUser = $user?->status === 'active' && ! $user->must_change_password && $user->canAccessCms();
        abort_unless($isPublic || $isCmsUser, 404);
        abort_if(str_starts_with($path, 'cms/') && $product === null, 404);

        $response = response()->file($disk->path($storedPath), [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => $isPublic ? 'public, max-age=86400' : 'private, no-store',
        ]);
        $productName = $product?->translations->firstWhere('lang', 'id')?->name;
        $fileName = $productName ? Str::slug($productName).'.pdf' : basename($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $fileName);

        return $response;
    }

    private function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $segments = explode('/', $path);
        abort_if($path === '' || str_contains($path, "\0") || in_array('..', $segments, true) || in_array('.', $segments, true), 404);

        return trim($path, '/');
    }
}
