<?php

namespace App\Services;

final class PerformanceResult
{
    /** @param array<string, CategoryScore> $categories */
    public function __construct(
        public readonly float $total,
        public readonly array $categories,
        public readonly int $itemsScored,
        public readonly int $itemsTotal,
    ) {}

    public function isComplete(): bool
    {
        return $this->itemsTotal > 0 && $this->itemsScored === $this->itemsTotal;
    }
}
