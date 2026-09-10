<?php

namespace App\Services;

/**
 * Bonus and incentive banding.
 *
 * Bands use "greater than or equal to" boundaries (decision K1). The source
 * document wrote them as 80-89 and 90-100, which leaves 89.5 in no band at all -
 * and scores do land there, because the sales component is continuous. The gap
 * was worth RM100 to whoever fell into it.
 */
final class RewardCalculator
{
    public function individualBonusCents(float $kpiTotal, KpiConfig $config): int
    {
        return $this->matchScoreBand($kpiTotal, $config->bonusBands);
    }

    public function highSalesIncentiveCents(int $netSalesCents, KpiConfig $config): int
    {
        foreach ($config->incentiveBands as $band) {
            if ($netSalesCents >= (int) $band['min_sales_cents']) {
                return (int) $band['amount_cents'];
            }
        }

        return 0;
    }

    public function teamBonusCents(float $teamKpiTotal, KpiConfig $config, bool $eligible): int
    {
        if (! $eligible) {
            return 0;
        }

        return $this->matchScoreBand($teamKpiTotal, $config->teamBonusBands);
    }

    /**
     * The three rewards are independent - none reduces another.
     */
    public function breakdown(
        float $kpiTotal,
        int $netSalesCents,
        float $teamKpiTotal,
        bool $teamEligible,
        KpiConfig $config,
    ): RewardBreakdown {
        $individual = $this->individualBonusCents($kpiTotal, $config);
        $incentive = $this->highSalesIncentiveCents($netSalesCents, $config);
        $team = $this->teamBonusCents($teamKpiTotal, $config, $teamEligible);

        return new RewardBreakdown(
            individualBonusCents: $individual,
            highSalesIncentiveCents: $incentive,
            teamBonusCents: $team,
            snapshot: [
                'kpi_total' => $kpiTotal,
                'net_sales_cents' => $netSalesCents,
                'team_kpi_total' => $teamKpiTotal,
                'team_eligible' => $teamEligible,
                'matched' => [
                    'individual_band' => $this->describeScoreBand($kpiTotal, $config->bonusBands),
                    'incentive_band' => $this->describeSalesBand($netSalesCents, $config->incentiveBands),
                    'team_band' => $teamEligible
                        ? $this->describeScoreBand($teamKpiTotal, $config->teamBonusBands)
                        : 'tidak layak',
                ],
                'amounts_cents' => [
                    'individual_bonus' => $individual,
                    'high_sales_incentive' => $incentive,
                    'team_bonus' => $team,
                    'total' => $individual + $incentive + $team,
                ],
            ],
        );
    }

    /**
     * Team bonus eligibility (decision K8): worked at least the configured
     * number of days in the month, and has an approved individual KPI. Without
     * a rule, somebody who joined in the last week takes the same share as
     * somebody who worked the whole month.
     */
    public function isTeamBonusEligible(int $workingDays, bool $hasApprovedKpi, KpiConfig $config): bool
    {
        return $hasApprovedKpi && $workingDays >= $config->teamBonusMinWorkingDays;
    }

    private function matchScoreBand(float $score, array $bands): int
    {
        foreach ($bands as $band) {
            if ($score >= (float) $band['min_score']) {
                return (int) $band['amount_cents'];
            }
        }

        return 0;
    }

    private function describeScoreBand(float $score, array $bands): string
    {
        foreach ($bands as $band) {
            if ($score >= (float) $band['min_score']) {
                return $band['min_score'].'% ke atas';
            }
        }

        return 'tiada bonus — bawah gred terendah';
    }

    private function describeSalesBand(int $cents, array $bands): string
    {
        foreach ($bands as $band) {
            if ($cents >= (int) $band['min_sales_cents']) {
                return \App\Support\Money::formatShort((int) $band['min_sales_cents']).' ke atas';
            }
        }

        return 'tiada insentif — bawah gred terendah';
    }
}
