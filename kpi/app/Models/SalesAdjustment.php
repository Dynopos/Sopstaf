<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Returns and cancellations. Kept separate from daily_sales so gross and net
 * both stay visible - editing the original row would hide that a return ever
 * happened.
 */
class SalesAdjustment extends Model
{
    protected $fillable = [
        'business_id', 'staff_id', 'adjusted_on', 'original_sale_date',
        'amount_cents', 'focus_qty', 'reason', 'entered_by',
    ];

    protected function casts(): array
    {
        return [
            'adjusted_on' => 'date',
            'original_sale_date' => 'date',
            'amount_cents' => 'integer',
            'focus_qty' => 'integer',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
