<?php

namespace App\Services;

use App\Models\KpiCriteria;
use Illuminate\Support\Collection;

/**
 * Every formula in docs/modul-kpi/03-pengiraan.md lives here and nowhere else.
 * This is the part of the system that decides what people get paid, so it is
 * kept free of database access and covered directly by unit tests.
 */
final class ScoreCalculator
{
    /**
     * Sales component. Capped at the weight: exceeding the target earns nothing
     * further here, it is rewarded through the high-sales incentive instead.
     */
    public function salesScore(int $netCents, int $targetCents, float $weight): float
    {
        if ($targetCents <= 0 || $netCents <= 0) {
            return 0.0;
        }

        return round(min($netCents / $targetCents * $weight, $weight), 2);
    }

    /** Percentage of target achieved, shown separately so 39,999 does not read as 100%. */
    public function salesAchievement(int $netCents, int $targetCents): float
    {
        if ($targetCents <= 0) {
            return 0.0;
        }

        return round($netCents / $targetCents * 100, 2);
    }

    /**
     * Pro-rate the target for someone who was not employed for the whole month.
     * A full target across half a month makes the sales component unreachable,
     * which makes the whole KPI unfair in that person's first month.
     */
    public function proratedTarget(int $targetCents, int $workingDays, int $daysInPeriod): int
    {
        if ($daysInPeriod <= 0 || $workingDays >= $daysInPeriod) {
            return $targetCents;
        }

        if ($workingDays <= 0) {
            return 0;
        }

        return (int) round($targetCents * $workingDays / $daysInPeriod);
    }

    /**
     * Performance component: each category scores sum-of-items over the maximum
     * possible, scaled by the category weight.
     *
     * @param  Collection<int, KpiCriteria>  $criteria
     * @param  array<int, int>  $scoresByCriteriaId
     */
    public function performance(Collection $criteria, array $scoresByCriteriaId): PerformanceResult
    {
        $categories = [];
        $total = 0.0;
        $scored = 0;

        foreach ($criteria->groupBy('category_key') as $key => $group) {
            $weight = (float) $group->first()->category_weight;
            $itemCount = $group->count();
            $maxRaw = $itemCount * 2;

            $raw = 0;
            foreach ($group as $item) {
                if (array_key_exists($item->id, $scoresByCriteriaId)) {
                    $raw += (int) $scoresByCriteriaId[$item->id];
                    $scored++;
                }
            }

            // Category scores land on exact multiples of weight/maxRaw, so this
            // rounding is lossless and the breakdown always adds up to the total.
            $score = $maxRaw > 0 ? round($raw / $maxRaw * $weight, 2) : 0.0;
            $total += $score;

            $categories[$key] = new CategoryScore(
                key: (string) $key,
                label: $group->first()->category_label,
                weight: $weight,
                rawScore: $raw,
                maxRaw: $maxRaw,
                score: $score,
                itemCount: $itemCount,
            );
        }

        return new PerformanceResult(
            total: round($total, 2),
            categories: $categories,
            itemsScored: $scored,
            itemsTotal: $criteria->count(),
        );
    }

    /** Sales plus performance. Both components are bounded, so this cannot exceed 100. */
    public function total(float $salesScore, float $performanceScore): float
    {
        return round($salesScore + $performanceScore, 2);
    }
}
