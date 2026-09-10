<?php

namespace Tests\Unit;

use App\Services\KpiConfig;
use App\Services\RewardCalculator;
use App\Support\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RewardCalculatorTest extends TestCase
{
    private RewardCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new RewardCalculator;
    }

    // --- Individual bonus bands ------------------------------------------

    #[DataProvider('bonusBands')]
    public function test_individual_bonus_band(float $kpi, int $expectedRinggit): void
    {
        $this->assertSame(
            Money::fromRinggit($expectedRinggit),
            $this->calc->individualBonusCents($kpi, $this->config()),
        );
    }

    public static function bonusBands(): array
    {
        return [
            'perfect'          => [100.00, 300],
            'top of band'      => [95.00, 300],
            'exactly 90'       => [90.00, 300],
            'just under 90'    => [89.99, 200],
            'the documented gap' => [89.50, 200],
            'exactly 80'       => [80.00, 200],
            'just under 80'    => [79.99, 100],
            'exactly 70'       => [70.00, 100],
            'just under 70'    => [69.99, 0],
            'nothing'          => [0.00, 0],
        ];
    }

    public function test_the_gap_between_eighty_nine_and_ninety_is_closed(): void
    {
        // Written as "80% - 89%" the source table has no home for 89.5, and a
        // score of 89.5 is reachable. Greater-than-or-equal boundaries put it in
        // the 80 band rather than leaving two people to reach two answers.
        $config = $this->config();

        foreach ([89.01, 89.25, 89.50, 89.75, 89.99] as $score) {
            $this->assertSame(
                Money::fromRinggit(200),
                $this->calc->individualBonusCents($score, $config),
                "Skor {$score} patut jatuh dalam gred 80.",
            );
        }
    }

    // --- High sales incentive --------------------------------------------

    #[DataProvider('incentiveBands')]
    public function test_high_sales_incentive_band(int $salesRinggit, int $expectedRinggit): void
    {
        $this->assertSame(
            Money::fromRinggit($expectedRinggit),
            $this->calc->highSalesIncentiveCents(Money::fromRinggit($salesRinggit), $this->config()),
        );
    }

    public static function incentiveBands(): array
    {
        return [
            'below target'    => [30_000, 0],
            'target met'      => [40_000, 0],
            'just under 50k'  => [49_999, 0],
            'exactly 50k'     => [50_000, 100],
            'just under 60k'  => [59_999, 100],
            'exactly 60k'     => [60_000, 200],
            'just under 70k'  => [69_999, 200],
            'exactly 70k'     => [70_000, 300],
            'well over'       => [120_000, 300],
        ];
    }

    public function test_cents_between_bands_do_not_fall_through(): void
    {
        // RM49,999.50 sits between the source table's "RM40,000 - RM49,999" and
        // "RM50,000 - RM59,999". Cents exist in real data.
        $this->assertSame(
            0,
            $this->calc->highSalesIncentiveCents(Money::fromRinggit(49_999.50), $this->config()),
        );
        $this->assertSame(
            Money::fromRinggit(100),
            $this->calc->highSalesIncentiveCents(Money::fromRinggit(50_000.01), $this->config()),
        );
    }

    // --- Team bonus -------------------------------------------------------

    public function test_team_bonus_requires_eligibility(): void
    {
        $config = $this->config();

        $this->assertSame(Money::fromRinggit(300), $this->calc->teamBonusCents(90.0, $config, true));
        $this->assertSame(0, $this->calc->teamBonusCents(90.0, $config, false));
    }

    public function test_eligibility_needs_both_service_days_and_an_approved_kpi(): void
    {
        $config = $this->config(); // threshold is 15 days

        $this->assertTrue($this->calc->isTeamBonusEligible(20, true, $config));
        $this->assertTrue($this->calc->isTeamBonusEligible(15, true, $config));
        $this->assertFalse($this->calc->isTeamBonusEligible(14, true, $config), 'Bawah ambang hari.');
        $this->assertFalse($this->calc->isTeamBonusEligible(30, false, $config), 'KPI belum diluluskan.');
    }

    // --- Combined totals, against the source document ---------------------

    #[DataProvider('documentedTotals')]
    public function test_total_reward_matches_the_source_document(
        int $salesRinggit,
        int $bonus,
        int $incentive,
        int $teamBonus,
        int $total,
    ): void {
        $breakdown = $this->calc->breakdown(
            kpiTotal: 90.0,
            netSalesCents: Money::fromRinggit($salesRinggit),
            teamKpiTotal: 90.0,
            teamEligible: true,
            config: $this->config(),
        );

        $this->assertSame(Money::fromRinggit($bonus), $breakdown->individualBonusCents);
        $this->assertSame(Money::fromRinggit($incentive), $breakdown->highSalesIncentiveCents);
        $this->assertSame(Money::fromRinggit($teamBonus), $breakdown->teamBonusCents);
        $this->assertSame(Money::fromRinggit($total), $breakdown->totalCents());
    }

    /** The summary table in docs/modul-kpi/03-pengiraan.md section 8. */
    public static function documentedTotals(): array
    {
        return [
            'RM36,000' => [36_000, 300, 0, 300, 600],
            'RM50,000' => [50_000, 300, 100, 300, 700],
            'RM60,000' => [60_000, 300, 200, 300, 800],
            'RM70,000' => [70_000, 300, 300, 300, 900],
        ];
    }

    public function test_the_three_rewards_are_independent(): void
    {
        // A weak KPI still earns the sales incentive; strong sales do not lift a
        // weak KPI into a higher bonus band.
        $breakdown = $this->calc->breakdown(
            kpiTotal: 65.0,
            netSalesCents: Money::fromRinggit(70_000),
            teamKpiTotal: 95.0,
            teamEligible: true,
            config: $this->config(),
        );

        $this->assertSame(0, $breakdown->individualBonusCents);
        $this->assertSame(Money::fromRinggit(300), $breakdown->highSalesIncentiveCents);
        $this->assertSame(Money::fromRinggit(300), $breakdown->teamBonusCents);
        $this->assertSame(Money::fromRinggit(600), $breakdown->totalCents());
    }

    public function test_breakdown_records_why_each_figure_is_what_it_is(): void
    {
        $breakdown = $this->calc->breakdown(
            kpiTotal: 90.0,
            netSalesCents: Money::fromRinggit(60_000),
            teamKpiTotal: 90.0,
            teamEligible: true,
            config: $this->config(),
        );

        $matched = $breakdown->snapshot['matched'];

        $this->assertSame('90% ke atas', $matched['individual_band']);
        $this->assertSame('RM60,000 ke atas', $matched['incentive_band']);
        $this->assertSame('90% ke atas', $matched['team_band']);
        $this->assertSame(Money::fromRinggit(800), $breakdown->snapshot['amounts_cents']['total']);
    }

    public function test_bands_are_matched_highest_first_even_if_configured_out_of_order(): void
    {
        $config = KpiConfig::fromArray([
            'bonus_bands' => [
                ['min_score' => 70, 'amount_cents' => Money::fromRinggit(100)],
                ['min_score' => 90, 'amount_cents' => Money::fromRinggit(300)],
                ['min_score' => 80, 'amount_cents' => Money::fromRinggit(200)],
            ],
        ]);

        $this->assertSame(Money::fromRinggit(300), $this->calc->individualBonusCents(95.0, $config));
    }

    private function config(): KpiConfig
    {
        return KpiConfig::fromArray([
            'individual_target_cents' => Money::fromRinggit(40_000),
            'team_target_cents' => Money::fromRinggit(200_000),
            'sales_weight' => 40,
            'prorate_target' => true,
            'bonus_bands' => [
                ['min_score' => 90, 'amount_cents' => Money::fromRinggit(300)],
                ['min_score' => 80, 'amount_cents' => Money::fromRinggit(200)],
                ['min_score' => 70, 'amount_cents' => Money::fromRinggit(100)],
            ],
            'incentive_bands' => [
                ['min_sales_cents' => Money::fromRinggit(70_000), 'amount_cents' => Money::fromRinggit(300)],
                ['min_sales_cents' => Money::fromRinggit(60_000), 'amount_cents' => Money::fromRinggit(200)],
                ['min_sales_cents' => Money::fromRinggit(50_000), 'amount_cents' => Money::fromRinggit(100)],
            ],
            'team_bonus_bands' => [
                ['min_score' => 90, 'amount_cents' => Money::fromRinggit(300)],
                ['min_score' => 80, 'amount_cents' => Money::fromRinggit(200)],
                ['min_score' => 70, 'amount_cents' => Money::fromRinggit(100)],
            ],
            'team_bonus_min_working_days' => 15,
            'require_note_on_zero' => true,
            'criteria_version' => 1,
        ]);
    }
}
