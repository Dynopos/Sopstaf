<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Setting;

/**
 * The configuration a period is calculated against.
 *
 * A period freezes one of these into kpi_periods.config_snapshot when it opens,
 * and every figure for that month is read back from the frozen copy. Raising the
 * target in March must not silently rewrite January's KPI, because January's
 * bonus has already been paid against the old number.
 */
final class KpiConfig
{
    public const KEYS = [
        'individual_target_cents',
        'team_target_cents',
        'sales_weight',
        'prorate_target',
        'bonus_bands',
        'incentive_bands',
        'team_bonus_bands',
        'team_bonus_min_working_days',
        'require_note_on_zero',
        'criteria_version',
    ];

    public function __construct(
        public readonly int $individualTargetCents,
        public readonly int $teamTargetCents,
        public readonly float $salesWeight,
        public readonly bool $prorateTarget,
        /** @var array<int, array{min_score: float, amount_cents: int}> */
        public readonly array $bonusBands,
        /** @var array<int, array{min_sales_cents: int, amount_cents: int}> */
        public readonly array $incentiveBands,
        /** @var array<int, array{min_score: float, amount_cents: int}> */
        public readonly array $teamBonusBands,
        public readonly int $teamBonusMinWorkingDays,
        public readonly bool $requireNoteOnZero,
        public readonly int $criteriaVersion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            individualTargetCents: (int) ($data['individual_target_cents'] ?? 0),
            teamTargetCents: (int) ($data['team_target_cents'] ?? 0),
            salesWeight: (float) ($data['sales_weight'] ?? 40),
            prorateTarget: (bool) ($data['prorate_target'] ?? true),
            bonusBands: self::sortBands($data['bonus_bands'] ?? [], 'min_score'),
            incentiveBands: self::sortBands($data['incentive_bands'] ?? [], 'min_sales_cents'),
            teamBonusBands: self::sortBands($data['team_bonus_bands'] ?? [], 'min_score'),
            teamBonusMinWorkingDays: (int) ($data['team_bonus_min_working_days'] ?? 0),
            requireNoteOnZero: (bool) ($data['require_note_on_zero'] ?? true),
            criteriaVersion: (int) ($data['criteria_version'] ?? 1),
        );
    }

    public static function forBusiness(Business|int $business): self
    {
        $id = $business instanceof Business ? $business->id : $business;

        $data = [];
        foreach (self::KEYS as $key) {
            $data[$key] = Setting::get($id, $key);
        }

        return self::fromArray($data);
    }

    public function toArray(): array
    {
        return [
            'individual_target_cents' => $this->individualTargetCents,
            'team_target_cents' => $this->teamTargetCents,
            'sales_weight' => $this->salesWeight,
            'prorate_target' => $this->prorateTarget,
            'bonus_bands' => $this->bonusBands,
            'incentive_bands' => $this->incentiveBands,
            'team_bonus_bands' => $this->teamBonusBands,
            'team_bonus_min_working_days' => $this->teamBonusMinWorkingDays,
            'require_note_on_zero' => $this->requireNoteOnZero,
            'criteria_version' => $this->criteriaVersion,
        ];
    }

    /** Highest threshold first, so the first match wins. */
    private static function sortBands(array $bands, string $field): array
    {
        $bands = array_values($bands);
        usort($bands, fn ($a, $b) => ($b[$field] ?? 0) <=> ($a[$field] ?? 0));

        return $bands;
    }
}
