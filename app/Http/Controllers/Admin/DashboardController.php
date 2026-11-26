<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const RECENT_ACTIVITY_LIMIT = 10;

    public function api(): JsonResponse
    {
        $contentTables = [
            'products' => ['table' => 'products'],
            'product_categories' => ['table' => 'product_categories'],
            'articles' => ['table' => 'documentations', 'type' => 'article'],
            'photo_activities' => ['table' => 'documentations', 'type' => 'activity'],
            'brands' => ['table' => 'brands'],
            'certificates' => ['table' => 'certificates'],
        ];
        $counts = [];

        foreach ($contentTables as $key => $definition) {
            $query = DB::table($definition['table'])->whereNull('deleted_at');
            if (isset($definition['type'])) {
                $query->where('type', $definition['type']);
            }
            $counts[$key] = $query
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');
        }

        $user = request()->user();

        return response()->json([
            'success' => true,
            'data' => [
                'content_counts' => $counts,
                'open_jobs' => DB::table('job_openings')->where('status', 'open')->whereNull('deleted_at')->count(),
                'closed_jobs' => DB::table('job_openings')->where('status', 'closed')->whereNull('deleted_at')->count(),
                'jobs_closing_soon' => DB::table('job_openings')->where('status', 'open')
                    ->whereBetween('closing_date', [today(), today()->addDays(7)])->count(),
                'recent_activities' => ($user?->hasRole('superadmin') || $user?->role?->hasPermission('activity_logs', 'view'))
                    ? ActivityLog::query()->with('user')->latest('created_at')->limit(self::RECENT_ACTIVITY_LIMIT)->get()
                    : [],
            ],
            'message' => 'Dashboard berhasil dimuat.',
        ]);
    }

    public function index(Request $request): View
    {
        $contentTables = [
            ['label' => 'Total Produk', 'table' => 'products', 'resource' => 'products', 'icon' => 'inventory_2'],
            ['label' => 'Total Kategori', 'table' => 'product_categories', 'resource' => 'product-categories', 'icon' => 'category'],
            ['label' => 'Artikel', 'table' => 'documentations', 'type' => 'article', 'resource' => 'documentations', 'icon' => 'article'],
            ['label' => 'Foto', 'table' => 'documentations', 'type' => 'activity', 'resource' => 'documentations', 'icon' => 'photo_library'],
            ['label' => 'Brand', 'table' => 'brands', 'resource' => 'brands', 'icon' => 'verified'],
            ['label' => 'Sertifikat', 'table' => 'certificates', 'resource' => 'certificates', 'icon' => 'workspace_premium'],
        ];
        $dashboardStats = [];

        foreach ($contentTables as $definition) {
            $query = DB::table($definition['table'])->whereNull('deleted_at');
            if (isset($definition['type'])) {
                $query->where('type', $definition['type']);
            }
            $statuses = $query
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();

            $dashboardStats[] = [
                'label' => $definition['label'],
                'total' => array_sum($statuses),
                'statuses' => $statuses,
                'icon' => $definition['icon'],
                'url' => route('admin.content.index', $definition['resource']),
            ];
        }

        $translationTables = [
            ['table' => 'hero_slides', 'translation' => 'hero_slide_translations', 'foreign' => 'hero_slide_id', 'label' => 'Hero Banner', 'resource' => 'hero-slides', 'title' => 'title'],
            ['table' => 'content_blocks', 'translation' => 'content_block_translations', 'foreign' => 'content_block_id', 'label' => 'Blok Konten', 'resource' => 'content-blocks', 'title' => 'title'],
            ['table' => 'mission_items', 'translation' => 'mission_item_translations', 'foreign' => 'mission_item_id', 'label' => 'Visi & Misi', 'resource' => 'mission-items', 'title' => 'text'],
            ['table' => 'certificates', 'translation' => 'certificate_translations', 'foreign' => 'certificate_id', 'label' => 'Sertifikat', 'resource' => 'certificates', 'title' => 'title'],
            ['table' => 'products', 'translation' => 'product_translations', 'foreign' => 'product_id', 'label' => 'Produk', 'resource' => 'products', 'title' => 'name'],
            ['table' => 'product_categories', 'translation' => 'product_category_translations', 'foreign' => 'product_category_id', 'label' => 'Kategori', 'resource' => 'product-categories', 'title' => 'name'],
            ['table' => 'documentations', 'translation' => 'documentation_translations', 'foreign' => 'documentation_id', 'label' => 'Dokumentasi', 'resource' => 'documentations', 'title' => 'title'],
            ['table' => 'job_openings', 'translation' => 'job_opening_translations', 'foreign' => 'job_opening_id', 'label' => 'Karir', 'resource' => 'job-openings', 'title' => 'title'],
            ['table' => 'contact_info', 'translation' => 'contact_info_translations', 'foreign' => 'contact_info_id', 'label' => 'Informasi Kontak', 'resource' => 'contact-info', 'title' => 'address', 'hasStatus' => false, 'hasSoftDeletes' => false],
        ];
        $totalIndonesianContent = 0;
        $missingEnglishTranslations = 0;
        $missingEnglishItems = collect();

        foreach ($translationTables as $translationTable) {
            $contentQuery = DB::table($translationTable['table'].' as content')
                ->join($translationTable['translation'].' as translation_id', function ($join) use ($translationTable): void {
                    $join->on('translation_id.'.$translationTable['foreign'], '=', 'content.id')
                        ->where('translation_id.lang', '=', 'id');
                });
            if ($translationTable['hasStatus'] ?? true) {
                $contentQuery->where('content.status', $translationTable['table'] === 'job_openings' ? 'open' : 'published');
            }
            if ($translationTable['hasSoftDeletes'] ?? true) {
                $contentQuery->whereNull('content.deleted_at');
            }
            $missingQuery = (clone $contentQuery)->whereNotExists(function (Builder $query) use ($translationTable): void {
                $query->selectRaw('1')
                    ->from($translationTable['translation'].' as translation_en')
                    ->whereColumn('translation_en.'.$translationTable['foreign'], 'content.id')
                    ->where('translation_en.lang', 'en')
                    ->whereNotNull('translation_en.'.$translationTable['title'])
                    ->where('translation_en.'.$translationTable['title'], '<>', '');
            });
            $contentCount = $contentQuery->count('content.id');
            $missingCount = $missingQuery->count('content.id');

            $totalIndonesianContent += $contentCount;
            $missingEnglishTranslations += $missingCount;

            if ($missingCount > 0) {
                $missingEnglishItems = $missingEnglishItems->merge(
                    (clone $missingQuery)
                        ->select('translation_id.'.$translationTable['title'].' as title')
                        ->orderByDesc('content.id')
                        ->limit(3)
                        ->get()
                        ->map(fn (object $item): array => [
                            'category' => $translationTable['label'],
                            'title' => $item->title,
                            'url' => route('admin.content.index', $translationTable['resource']),
                        ]),
                );
            }
        }

        $translationCompleted = max(0, $totalIndonesianContent - $missingEnglishTranslations);
        $translationPercent = $totalIndonesianContent > 0
            ? (int) round(($translationCompleted / $totalIndonesianContent) * 100)
            : 0;
        $dates = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $fromDate = $dates['from'] ?? $dates['to'] ?? now()->startOfMonth()->toDateString();
        $toDate = $dates['to'] ?? $dates['from'] ?? now()->endOfMonth()->toDateString();
        $isSuperadmin = $request->user()?->hasRole('superadmin') || $request->user()?->role?->hasPermission('activity_logs', 'view');
        $activityQuery = ActivityLog::query()->with('user')->latest('created_at');
        $activityQuery->whereBetween('created_at', [
            Carbon::parse($fromDate)->startOfDay(),
            Carbon::parse($toDate)->endOfDay(),
        ]);

        return view('admin.dashboard', [
            'dashboardStats' => $dashboardStats,
            'totalIndonesianContent' => $totalIndonesianContent,
            'translationCompleted' => $translationCompleted,
            'translationPercent' => $translationPercent,
            'missingEnglishTranslations' => $missingEnglishTranslations,
            'missingEnglishItems' => $missingEnglishItems->take(6),
            'recentActivities' => $isSuperadmin ? $activityQuery->limit(self::RECENT_ACTIVITY_LIMIT)->get() : collect(),
            'jobStats' => [
                'open' => DB::table('job_openings')->whereNull('deleted_at')->where('status', 'open')->count(),
                'closed' => DB::table('job_openings')->whereNull('deleted_at')->where('status', 'closed')->count(),
                'closingSoon' => DB::table('job_openings')->whereNull('deleted_at')->where('status', 'open')->whereBetween('closing_date', [today(), today()->addDays(7)])->count(),
            ],
            'isSuperadmin' => $isSuperadmin,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }
}
