<?php

namespace App\Services;

final class RewardBreakdown
{
    public function __construct(
        public readonly int $individualBonusCents,
        public readonly int $highSalesIncentiveCents,
        public readonly int $teamBonusCents,
        public readonly array $snapshot,
    ) {}

    public function totalCents(): int
    {
        return $this->individualBonusCents
            + $this->highSalesIncentiveCents
            + $this->teamBonusCents;
    }
}
