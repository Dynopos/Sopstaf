<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySale extends Model
{
    protected $fillable = [
        'business_id', 'staff_id', 'sold_on', 'amount_cents',
        'focus_qty', 'note', 'source', 'entered_by',
    ];

    protected function casts(): array
    {
        return [
            'sold_on' => 'date',
            'amount_cents' => 'integer',
            'focus_qty' => 'integer',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
