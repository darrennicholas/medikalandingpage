<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'], 'action' => ['nullable', 'string', 'max:30'],
            'entity_type' => ['nullable', 'string', 'max:50'],
        ]);
        $logs = ActivityLog::query()->with('user')
            ->when($validated['from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($validated['to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($validated['user_id'] ?? null, fn ($query, int $userId) => $query->where('user_id', $userId))
            ->when($validated['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($validated['entity_type'] ?? null, fn ($query, string $entity) => $query->where('entity_type', 'like', '%'.$entity.'%'))
            ->latest('created_at')->paginate(25)->withQueryString();

        return view('admin.logs.index', [
            'logs' => $logs,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'logStats' => [
                'today' => ActivityLog::query()->whereDate('created_at', today())->count(),
                'failed' => ActivityLog::query()->whereIn('action', ['login_failed', 'auth.locked'])->count(),
                'changes' => ActivityLog::query()->whereIn('action', ['create', 'update', 'delete'])->count(),
                'published' => ActivityLog::query()->whereIn('action', ['publish', 'unpublish'])->count(),
            ],
        ]);
    }
}
