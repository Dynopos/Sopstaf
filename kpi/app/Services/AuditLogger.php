<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;

final class AuditLogger
{
    public function record(
        Model $subject,
        string $action,
        ?User $actor = null,
        ?array $changes = null,
        ?string $reason = null,
        ?int $businessId = null,
    ): AuditLog {
        $actor ??= auth()->user();

        return AuditLog::create([
            'business_id' => $businessId
                ?? $subject->business_id
                ?? $actor?->business_id,
            'actor_id' => $actor?->id,
            'action' => $action,
            'auditable_type' => $subject::class,
            'auditable_id' => $subject->getKey(),
            'changes' => $changes,
            'reason' => $reason,
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 500) ?: null,
            'created_at' => Carbon::now(),
        ]);
    }

    /** Before and after for the fields that actually moved. */
    public function diff(Model $model): array
    {
        $changes = [];

        foreach ($model->getDirty() as $field => $new) {
            $changes[$field] = [
                'dari' => $model->getOriginal($field),
                'ke' => $new,
            ];
        }

        return $changes;
    }
}
