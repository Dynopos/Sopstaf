<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Insert only. There is deliberately no update or delete path, for any role -
 * an audit trail the audited party can edit proves nothing.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'business_id', 'actor_id', 'action', 'auditable_type', 'auditable_id',
        'changes', 'reason', 'ip_address', 'user_agent', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new \RuntimeException('Rekod audit tidak boleh diubah.');
        }

        return parent::save($options);
    }

    public function delete(): bool
    {
        throw new \RuntimeException('Rekod audit tidak boleh dipadam.');
    }
}
