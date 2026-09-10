<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KpiCriteria extends Model
{
    protected $table = 'kpi_criteria';

    protected $fillable = [
        'business_id', 'scope', 'category_key', 'category_label', 'category_weight',
        'label', 'desc_0', 'desc_1', 'desc_2', 'sort_order', 'version', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category_weight' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeIndividual(Builder $query): Builder
    {
        return $query->where('scope', 'individual');
    }

    public function scopeTeam(Builder $query): Builder
    {
        return $query->where('scope', 'team');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function descriptionFor(int $score): string
    {
        return match ($score) {
            0 => $this->desc_0,
            1 => $this->desc_1,
            default => $this->desc_2,
        };
    }
}
