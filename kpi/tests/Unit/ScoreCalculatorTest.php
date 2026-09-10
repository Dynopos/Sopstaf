<?php

namespace Tests\Unit;

use App\Models\KpiCriteria;
use App\Services\ScoreCalculator;
use App\Support\Money;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScoreCalculatorTest extends TestCase
{
    private ScoreCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new ScoreCalculator;
    }

    // --- Sales component -------------------------------------------------

    #[DataProvider('salesExamples')]
    public function test_sales_score_matches_the_source_document(int $salesRinggit, float $expected): void
    {
        $this->assertSame($expected, $this->calc->salesScore(
            Money::fromRinggit($salesRinggit),
            Money::fromRinggit(40_000),
            40.0,
        ));
    }

    public static function salesExamples(): array
    {
        return [
            'target met'     => [40_000, 40.00],
            'RM36,000'       => [36_000, 36.00],
            'RM32,000'       => [32_000, 32.00],
            'RM28,000'       => [28_000, 28.00],
            'RM20,000'       => [20_000, 20.00],
            'over target'    => [60_000, 40.00],
            'far over target'=> [90_000, 40.00],
            'no sales'       => [0, 0.00],
        ];
    }

    public function test_sales_score_is_capped_at_the_weight(): void
    {
        // Exceeding target earns nothing further here - that is the incentive's job.
        $score = $this->calc->salesScore(Money::fromRinggit(200_000), Money::fromRinggit(40_000), 40.0);

        $this->assertSame(40.00, $score);
    }

    public function test_sales_score_is_zero_when_no_target_is_configured(): void
    {
        $this->assertSame(0.0, $this->calc->salesScore(Money::fromRinggit(10_000), 0, 40.0));
    }

    public function test_achievement_is_reported_separately_from_the_score(): void
    {
        // RM39,999 scores 40.00 once rounded, but has not actually hit target.
        // The two numbers are shown apart so that difference stays visible.
        $target = Money::fromRinggit(40_000);
        $sales = Money::fromRinggit(39_999);

        $this->assertSame(40.00, $this->calc->salesScore($sales, $target, 40.0));
        $this->assertSame(100.00, round($this->calc->salesAchievement($sales, $target), 2));
        $this->assertLessThan(40.0, $sales / $target * 40);
    }

    // --- Pro-rating ------------------------------------------------------

    public function test_target_is_prorated_for_a_mid_month_joiner(): void
    {
        $full = Money::fromRinggit(40_000);

        $this->assertSame(Money::fromRinggit(20_000), $this->calc->proratedTarget($full, 15, 30));
        $this->assertSame($full, $this->calc->proratedTarget($full, 30, 30));
        $this->assertSame($full, $this->calc->proratedTarget($full, 45, 30));
        $this->assertSame(0, $this->calc->proratedTarget($full, 0, 30));
    }

    // --- Performance component -------------------------------------------

    public function test_performance_scores_each_category_by_weight(): void
    {
        $criteria = $this->criteria();
        $scores = [];
        foreach ($criteria as $c) {
            $scores[$c->id] = 2; // everything excellent
        }

        $result = $this->calc->performance($criteria, $scores);

        $this->assertSame(60.00, $result->total);
        $this->assertTrue($result->isComplete());
    }

    public function test_all_zeroes_score_nothing(): void
    {
        $criteria = $this->criteria();
        $scores = [];
        foreach ($criteria as $c) {
            $scores[$c->id] = 0;
        }

        $this->assertSame(0.00, $this->calc->performance($criteria, $scores)->total);
    }

    public function test_the_three_grade_scale_cannot_produce_thirteen_out_of_fifteen(): void
    {
        // The source document scores customer service as 13/15. On a 0/1/2 scale
        // over five items a 15%-weighted category can only land on multiples of
        // 1.5, so 13 is unreachable and 13.5 is the nearest value. Documented in
        // docs/modul-kpi/03-pengiraan.md section 2.1.
        $criteria = $this->criteria();
        $service = $criteria->where('category_key', 'khidmat')->values();

        $scores = [];
        foreach ($criteria as $c) {
            $scores[$c->id] = 0;
        }
        foreach ($service as $i => $c) {
            $scores[$c->id] = $i < 4 ? 2 : 1; // raw 9 of 10
        }

        $result = $this->calc->performance($criteria, $scores);
        $category = $result->categories['khidmat'];

        $this->assertSame(13.5, $category->score);
        $this->assertSame(1.5, $category->stepSize());
        $this->assertNotEquals(13.0, $category->score);
    }

    public function test_ten_percent_categories_step_in_whole_points(): void
    {
        $criteria = $this->criteria();
        $discipline = $criteria->where('category_key', 'disiplin')->values();

        $scores = [];
        foreach ($criteria as $c) {
            $scores[$c->id] = 0;
        }
        foreach ($discipline as $i => $c) {
            $scores[$c->id] = $i < 4 ? 2 : 1; // raw 9 of 10
        }

        $category = $this->calc->performance($criteria, $scores)->categories['disiplin'];

        $this->assertSame(9.00, $category->score);
        $this->assertSame(1.0, $category->stepSize());
    }

    public function test_the_documented_ninety_percent_example_is_reachable(): void
    {
        // 36.0 sales + 13.5 + 13.5 + 9 + 9 + 9 = 90.00
        $criteria = $this->criteria();
        $scores = [];
        foreach ($criteria as $c) {
            $scores[$c->id] = 0;
        }
        foreach ($criteria->groupBy('category_key') as $group) {
            foreach ($group->values() as $i => $c) {
                $scores[$c->id] = $i < 4 ? 2 : 1; // 9 of 10 in every category
            }
        }

        $performance = $this->calc->performance($criteria, $scores);
        $sales = $this->calc->salesScore(Money::fromRinggit(36_000), Money::fromRinggit(40_000), 40.0);

        $this->assertSame(54.00, $performance->total);
        $this->assertSame(90.00, $this->calc->total($sales, $performance->total));
    }

    public function test_an_incomplete_assessment_is_reported_as_incomplete(): void
    {
        $criteria = $this->criteria();
        $scores = [$criteria->first()->id => 2];

        $result = $this->calc->performance($criteria, $scores);

        $this->assertFalse($result->isComplete());
        $this->assertSame(1, $result->itemsScored);
        $this->assertSame(25, $result->itemsTotal);
    }

    public function test_total_cannot_exceed_one_hundred(): void
    {
        $criteria = $this->criteria();
        $scores = [];
        foreach ($criteria as $c) {
            $scores[$c->id] = 2;
        }

        $sales = $this->calc->salesScore(Money::fromRinggit(999_999), Money::fromRinggit(40_000), 40.0);
        $total = $this->calc->total($sales, $this->calc->performance($criteria, $scores)->total);

        $this->assertSame(100.00, $total);
    }

    /**
     * Five categories of five items, matching the individual KPI structure.
     *
     * @return Collection<int, KpiCriteria>
     */
    private function criteria(): Collection
    {
        $shape = [
            ['khidmat', 'Khidmat Pelanggan', 15.00],
            ['operasi', 'Operasi Kedai & Pengurusan Barang', 15.00],
            ['disiplin', 'Disiplin & Kehadiran', 10.00],
            ['sop', 'Pematuhan SOP', 10.00],
            ['kerjasama', 'Kerjasama Team & Sikap Kerja', 10.00],
        ];

        $items = collect();
        $id = 1;

        foreach ($shape as [$key, $label, $weight]) {
            for ($i = 1; $i <= 5; $i++) {
                $c = new KpiCriteria([
                    'category_key' => $key,
                    'category_label' => $label,
                    'category_weight' => $weight,
                    'label' => "$label item $i",
                    'desc_0' => 'x', 'desc_1' => 'y', 'desc_2' => 'z',
                ]);
                $c->id = $id++;
                $items->push($c);
            }
        }

        return $items;
    }
}
