<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

class PublicApiCache
{
    public const TTL_SECONDS = 60;

    public const STALE_TTL_SECONDS = 86400;

    private const VERSION_KEY = 'cms.public.api.version';

    public function responseKey(Request $request): string
    {
        $version = Cache::store('public')->rememberForever(self::VERSION_KEY, fn (): string => (string) Str::uuid());

        return 'cms.public.api.responses.'.$version.'.'.hash('sha256', $request->fullUrl());
    }

    public function staleResponseKey(Request $request): string
    {
        return 'cms.public.api.stale.'.hash('sha256', $request->fullUrl());
    }

    public function invalidate(): void
    {
        $version = (string) Str::uuid();

        try {
            $cache = Cache::store('public');
            retry(3, function () use ($cache, $version): void {
                if (! $cache->forever(self::VERSION_KEY, $version)) {
                    throw new RuntimeException('Public API cache version could not be updated.');
                }
            }, 100);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
