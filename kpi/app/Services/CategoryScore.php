<?php

namespace App\Services;

final class CategoryScore
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly float $weight,
        public readonly int $rawScore,
        public readonly int $maxRaw,
        public readonly float $score,
        public readonly int $itemCount,
    ) {}

    /**
     * Smallest gap between two achievable scores in this category. With five
     * items on a 0/1/2 scale and a 15% weight this is 1.5 - which is why the
     * source document's "13/15" cannot be reproduced item by item.
     */
    public function stepSize(): float
    {
        return $this->maxRaw > 0 ? round($this->weight / $this->maxRaw, 4) : 0.0;
    }
}
