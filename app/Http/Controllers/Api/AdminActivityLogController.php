<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'],
            'user_id' => ['nullable', 'integer'], 'action' => ['nullable', 'string', 'max:30'],
            'entity_type' => ['nullable', 'string', 'max:50'],
        ]);
        $logs = ActivityLog::query()->with('user')
            ->when($validated['from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($validated['to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($validated['user_id'] ?? null, fn ($query, int $userId) => $query->where('user_id', $userId))
            ->when($validated['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($validated['entity_type'] ?? null, fn ($query, string $entity) => $query->where('entity_type', 'like', '%'.$entity.'%'))
            ->latest('created_at')->paginate(25)->withQueryString();

        return response()->json(['success' => true, 'data' => $logs, 'message' => 'Log aktivitas berhasil dimuat.']);
    }
}
