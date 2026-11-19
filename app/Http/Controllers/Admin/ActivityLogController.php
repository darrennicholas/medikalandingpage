<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $logs = $this->query($filters)->with('user')
            ->latest('created_at')->paginate(in_array(request()->integer('per_page'), [10, 25, 50], true) ? request()->integer('per_page') : 10)->withQueryString();

        return view('admin.logs.index', [
            'logs' => $logs,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'logStats' => [
                'today' => ActivityLog::query()->whereDate('created_at', today())->count(),
                'failed' => ActivityLog::query()->whereIn('action', ['login_failed', 'auth.locked'])->count(),
                'changes' => ActivityLog::query()->whereIn('action', ['create', 'update', 'delete'])->count(),
                'published' => ActivityLog::query()->where('action', 'publish')->count(),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $query = $this->query($filters)->with('user')->latest('created_at');

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Waktu', 'User', 'Email', 'Aksi', 'Entitas', 'Entity ID', 'Nilai Sebelum', 'Nilai Sesudah', 'IP Address', 'User Agent']);

            $query->chunk(200, function ($logs) use ($handle): void {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->created_at?->format('Y-m-d H:i:s'),
                        $log->user?->name ?? 'Sistem',
                        $log->user?->email ?? '',
                        $log->action,
                        $log->entity_type ? class_basename($log->entity_type) : '',
                        $log->entity_id,
                        $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '',
                        $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '',
                        $log->ip_address,
                        $log->user_agent,
                    ]);
                }
            });

            fclose($handle);
        }, 'activity-logs-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        return $request->validate([
            'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'], 'action' => ['nullable', 'string', 'max:30'],
            'entity_type' => ['nullable', 'string', 'max:50'],
        ]);
    }

    /** @param array<string, mixed> $filters */
    private function query(array $filters): Builder
    {
        return ActivityLog::query()
            ->when($filters['from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['user_id'] ?? null, fn ($query, int $userId) => $query->where('user_id', $userId))
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['entity_type'] ?? null, fn ($query, string $entity) => $query->where('entity_type', 'like', '%'.$entity.'%'));
    }
}
