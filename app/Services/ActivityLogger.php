<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function log(
        ?User $user,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): void {
        $request = request();

        ActivityLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $this->removeSensitiveValues($oldValues),
            'new_values' => $this->removeSensitiveValues($newValues),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 300),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $values
     * @return array<string, mixed>|null
     */
    private function removeSensitiveValues(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        foreach ($values as $key => $value) {
            if (preg_match('/password|token|secret/i', (string) $key) === 1) {
                unset($values[$key]);
            } elseif (is_array($value)) {
                $values[$key] = $this->removeSensitiveValues($value);
            }
        }

        return $values;
    }
}
