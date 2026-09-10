<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'business_id', 'user_id', 'employee_code', 'name',
        'supervisor_id', 'joined_on', 'left_on', 'is_active', 'is_assessed',
    ];

    protected function casts(): array
    {
        return [
            'joined_on' => 'date',
            'left_on' => 'date',
            'is_active' => 'boolean',
            'is_assessed' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Staff::class, 'supervisor_id');
    }

    public function dailySales(): HasMany
    {
        return $this->hasMany(DailySale::class);
    }

    public function salesAdjustments(): HasMany
    {
        return $this->hasMany(SalesAdjustment::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(KpiAssessment::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAssessed(Builder $query): Builder
    {
        return $query->where('is_assessed', true);
    }

    /**
     * Days this person was actually employed inside the window. Used to
     * pro-rate the target so a mid-month joiner is not measured against a
     * full month they were never there for.
     */
    public function workingDaysWithin(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        $from = $this->joined_on->greaterThan($start) ? $this->joined_on : \Illuminate\Support\Carbon::instance($start);
        $to = $this->left_on && $this->left_on->lessThan($end)
            ? $this->left_on
            : \Illuminate\Support\Carbon::instance($end);

        if ($from->greaterThan($to)) {
            return 0;
        }

        return $from->startOfDay()->diffInDays($to->startOfDay()) + 1;
    }
}
