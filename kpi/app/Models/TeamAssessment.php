<?php

namespace App\Models;

use App\Enums\AssessmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamAssessment extends Model
{
    protected $fillable = [
        'period_id', 'status', 'sales_amount_cents',
        'sales_score', 'performance_score', 'total_score',
        'evaluated_by', 'submitted_at', 'approved_by', 'approved_at',
        'returned_reason', 'version',
    ];

    protected function casts(): array
    {
        return [
            'status' => AssessmentStatus::class,
            'sales_amount_cents' => 'integer',
            'sales_score' => 'decimal:2',
            'performance_score' => 'decimal:2',
            'total_score' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TeamAssessmentItem::class, 'team_assessment_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
