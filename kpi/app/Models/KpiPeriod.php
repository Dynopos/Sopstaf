<?php

namespace App\Models;

use App\Enums\PeriodStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class KpiPeriod extends Model
{
    protected $fillable = [
        'business_id', 'year', 'month', 'status', 'config_snapshot',
        'opened_at', 'locked_at', 'locked_by',
        'reopened_at', 'reopened_by', 'reopen_reason',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'status' => PeriodStatus::class,
            'config_snapshot' => 'array',
            'opened_at' => 'datetime',
            'locked_at' => 'datetime',
            'reopened_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(KpiAssessment::class, 'period_id');
    }

    public function teamAssessment(): HasOne
    {
        return $this->hasOne(TeamAssessment::class, 'period_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class, 'period_id');
    }

    public function startsOn(): Carbon
    {
        return Carbon::create($this->year, $this->month, 1)->startOfDay();
    }

    public function endsOn(): Carbon
    {
        return $this->startsOn()->endOfMonth()->startOfDay();
    }

    public function label(): string
    {
        return $this->startsOn()->translatedFormat('F Y');
    }

    /** Read config from the frozen snapshot, never from live settings. */
    public function config(string $key, mixed $default = null): mixed
    {
        return data_get($this->config_snapshot, $key, $default);
    }
}
