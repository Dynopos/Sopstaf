<?php

namespace App\Models;

use App\Enums\RewardStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends Model
{
    protected $fillable = [
        'period_id', 'staff_id', 'kpi_total',
        'individual_bonus_cents', 'high_sales_incentive_cents', 'team_bonus_cents',
        'total_cents', 'calc_snapshot', 'status',
        'verified_by', 'verified_at', 'paid_at', 'withheld_reason',
    ];

    protected function casts(): array
    {
        return [
            'kpi_total' => 'decimal:2',
            'individual_bonus_cents' => 'integer',
            'high_sales_incentive_cents' => 'integer',
            'team_bonus_cents' => 'integer',
            'total_cents' => 'integer',
            'calc_snapshot' => 'array',
            'status' => RewardStatus::class,
            'verified_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
