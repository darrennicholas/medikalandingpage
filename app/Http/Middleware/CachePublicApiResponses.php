<?php

namespace App\Http\Middleware;

use App\Services\PublicApiCache;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PDOException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CachePublicApiResponses
{
    public function __construct(private PublicApiCache $publicApiCache) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = (string) $request->route()?->getName();
        $isPublicApiGet = $request->isMethod('GET') && str_starts_with($routeName, 'api.public.');
        $isLocalizedPageGet = $request->isMethod('GET') && str_starts_with($routeName, 'localized.');
        $isPublicCacheableGet = $isPublicApiGet || $isLocalizedPageGet;
        $cacheKey = null;
        $staleKey = $isPublicCacheableGet ? $this->publicApiCache->staleResponseKey($request) : null;

        if ($isPublicCacheableGet) {
            try {
                $cache = Cache::store('public');
                $cacheKey = $this->publicApiCache->responseKey($request);
                $cached = $cache->get($cacheKey);
                if (is_array($cached) && isset($cached['content'], $cached['status'])) {
                    return response($cached['content'], $cached['status'])
                        ->header('Content-Type', $cached['content_type'] ?? 'application/json; charset=UTF-8');
                }
            } catch (Throwable $exception) {
                $cacheKey = null;
                Log::warning('Public response cache is unavailable; continuing without cache.', [
                    'route' => $routeName,
                    'exception' => $exception,
                ]);
            }
        }

        try {
            $response = $next($request);
        } catch (PDOException $exception) {
            try {
                $stale = $staleKey === null ? null : Cache::store('public')->get($staleKey);
            } catch (Throwable $cacheException) {
                Log::warning('Could not read stale public response after a database failure.', [
                    'route' => $routeName,
                    'exception' => $cacheException,
                ]);
                $stale = null;
            }
            if (is_array($stale) && isset($stale['content'], $stale['status'])) {
                Log::warning('Serving stale public API response after a database failure.', [
                    'route' => $routeName,
                    'exception' => $exception,
                ]);

                return response($stale['content'], $stale['status'])
                    ->header('Content-Type', $stale['content_type'] ?? 'application/json; charset=UTF-8')
                    ->header('Cache-Control', 'no-cache');
            }

            throw $exception;
        }

        $contentType = $response->headers->get('Content-Type', '');
        $isCacheableResponse = $response instanceof JsonResponse
            || ($isLocalizedPageGet && str_starts_with($contentType, 'text/html'));

        if ($cacheKey !== null && $isCacheableResponse && $response->isSuccessful()) {
            $cachedResponse = [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'content_type' => $contentType ?: 'application/json; charset=UTF-8',
            ];
            try {
                $cache = Cache::store('public');
                $cache->put($cacheKey, $cachedResponse, now()->addSeconds(PublicApiCache::TTL_SECONDS));
                $cache->put($staleKey, $cachedResponse, now()->addSeconds(PublicApiCache::STALE_TTL_SECONDS));
            } catch (Throwable $exception) {
                Log::warning('Could not save public response to cache.', [
                    'route' => $routeName,
                    'exception' => $exception,
                ]);
            }
        }

        if (
            str_starts_with($routeName, 'api.admin.') || str_starts_with($routeName, 'admin.')
        ) {
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $response->getStatusCode() < 400) {
                $this->publicApiCache->invalidate();
            }
        }

        return $response;
    }
}
