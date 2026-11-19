<?php

namespace App\Http\Middleware;

use App\Models\ContentBlock;
use App\Models\SocialLink;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCmsPermission
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->hasRole('superadmin')) {
            return $next($request);
        }

        [$module, $action] = $this->resolvePermission($request);

        if ($module === null && ! $request->routeIs('admin.password.*', 'admin.logout', 'api.admin.password.update', 'api.admin.logout')) {
            abort(403, 'Modul tidak memiliki pemetaan hak akses.');
        }

        if ($module !== null && ! $user->role?->hasPermission($module, $action)) {
            abort(403, 'Role Anda tidak memiliki izin untuk tindakan ini.');
        }

        $status = $request->input('status', $request->input('attributes.status'));
        $unchangedSocialStatus = $request->routeIs('api.admin.resources.update')
            && $request->route('resource') === 'social-links'
            && $status === 'published'
            && SocialLink::query()->whereKey($request->route('id'))->where('status', 'published')->exists();
        $unchangedFooterStatus = $request->routeIs('api.admin.content-blocks.update')
            && $request->route('id') === 'footer_about'
            && ContentBlock::query()->where('block_key', 'footer_about')->where('status', $status)->exists();
        if ($module !== null && in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)
            && in_array($status, ['published', 'open', 'closed'], true)
            && ! $unchangedSocialStatus
            && ! $unchangedFooterStatus
            && ! $user->role?->hasPermission($module, 'publish')) {
            abort(403, 'Role Anda tidak memiliki izin publikasi.');
        }

        return $next($request);
    }

    /** @return array{0: ?string, 1: string} */
    private function resolvePermission(Request $request): array
    {
        $routeName = (string) $request->route()?->getName();
        $resource = (string) $request->route('resource');
        $section = (string) ($request->route('id') ?? $request->query('section') ?? $request->input('block_key'));
        $method = $request->method();

        if (str_starts_with($routeName, 'api.admin.content-blocks.')) {
            $resource = 'content-blocks';
        }

        if ($resource === 'content-blocks' && ctype_digit($section)) {
            $section = (string) ContentBlock::query()->whereKey((int) $section)->value('block_key');
        }

        if (str_starts_with($routeName, 'admin.dashboard') || str_starts_with($routeName, 'api.admin.dashboard')) {
            return ['dashboard', 'view'];
        }

        if (str_starts_with($routeName, 'admin.users.') || str_starts_with($routeName, 'api.admin.users.')) {
            $action = match (true) {
                str_contains($routeName, '.create'), str_contains($routeName, '.store') => 'create',
                str_contains($routeName, '.edit'), str_contains($routeName, '.update'), str_contains($routeName, '.reset-password'), str_contains($routeName, '.unlock') => 'update',
                str_contains($routeName, '.destroy') => 'delete',
                default => 'view',
            };

            return ['users', $action];
        }

        if (str_starts_with($routeName, 'admin.activity-logs') || str_starts_with($routeName, 'api.admin.activity-logs')) {
            return ['activity_logs', 'view'];
        }

        $module = match ($resource) {
            'hero-slides' => 'home_hero_banner',
            'brands' => 'home_brands',
            'company-summary' => 'about_summary',
            'mission-items' => 'about_mission',
            'certificates' => 'about_certificates',
            'product-categories' => 'products_categories',
            'products' => 'products_catalog',
            'documentations' => 'documentation',
            'job-openings' => 'careers',
            'contact-info' => 'contacts',
            'social-links' => 'footer',
            'seo-metas' => 'seo',
            'content-blocks' => match ($section) {
                'home_coverage' => 'home_coverage',
                'home_cta' => 'home_cta',
                'about_company' => 'about_company',
                'about_vision' => 'about_mission',
                'career_intro' => 'careers',
                'footer_about' => 'footer',
                default => null,
            },
            default => null,
        };

        if (str_starts_with($routeName, 'admin.media.') || str_starts_with($routeName, 'api.admin.media.')) {
            $module = 'media';
        }

        if (str_contains($routeName, 'featured.')) {
            $module = 'products_catalog';
        }

        if ($module === null) {
            return [null, 'view'];
        }

        $action = match (true) {
            str_contains($routeName, '.create') => 'create',
            str_contains($routeName, '.edit') => 'update',
            str_contains($routeName, '.status'), str_contains($routeName, '.publish') => 'publish',
            in_array($method, ['POST'], true) => 'create',
            in_array($method, ['PUT', 'PATCH'], true) => 'update',
            in_array($method, ['DELETE'], true) => 'delete',
            default => 'view',
        };

        return [$module, $action];
    }
}
