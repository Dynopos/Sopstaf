<?php

namespace Tests\Feature;

use App\Enums\AssessmentStatus;
use App\Enums\PeriodStatus;
use App\Enums\Role;
use App\Models\Business;
use App\Models\DailySale;
use App\Models\KpiAssessment;
use App\Models\KpiCriteria;
use App\Models\Reward;
use App\Models\Staff;
use App\Models\User;
use App\Services\AssessmentWorkflow;
use App\Services\PeriodService;
use App\Support\Money;
use Database\Seeders\ConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Walks one month end to end and checks the bonus figures against the summary
 * table in docs/modul-kpi/03-pengiraan.md section 8.
 */
class MonthlyCycleTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $admin;
    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConfigurationSeeder::class);
        $this->business = Business::first();
        $this->admin = User::where('role', Role::Admin)->first();

        $this->supervisor = User::create([
            'business_id' => $this->business->id,
            'name' => 'Penyelia',
            'login_code' => 'sv',
            'password' => Hash::make('123456'),
            'role' => Role::Supervisor,
            'is_active' => true,
        ]);
    }

    public function test_a_full_month_produces_the_documented_reward(): void
    {
        $staff = $this->makeStaff('Ahmad', 'S001');
        $this->recordSales($staff, 36_000);

        $period = app(PeriodService::class)->open($this->business, 2026, 8, $this->admin);

        $assessment = $period->assessments()->where('staff_id', $staff->id)->firstOrFail();

        // Sales component is automatic: RM36,000 against RM40,000 is 36 of 40.
        $this->assertSame('36.00', $assessment->sales_score);
        $this->assertSame(Money::fromRinggit(36_000), $assessment->sales_amount_cents);

        $this->scoreEverything($assessment, 9); // 9 of 10 raw in every category

        $workflow = app(AssessmentWorkflow::class);
        $workflow->submit($assessment->refresh(), $this->supervisor);
        $workflow->approve($assessment->refresh(), $this->admin);

        $assessment->refresh();
        $this->assertSame('54.00', $assessment->performance_score);
        $this->assertSame('90.00', $assessment->total_score);

        // Team assessment has to be approved before team bonus can apply.
        $team = $period->teamAssessment;
        $this->approveTeamAt($period, 90.0);

        app(PeriodService::class)->lock($period->refresh(), $this->admin);

        $reward = Reward::where('period_id', $period->id)->where('staff_id', $staff->id)->firstOrFail();

        $this->assertSame(Money::fromRinggit(300), $reward->individual_bonus_cents, 'Bonus KPI');
        $this->assertSame(Money::fromRinggit(0), $reward->high_sales_incentive_cents, 'Insentif');
        $this->assertSame(Money::fromRinggit(300), $reward->team_bonus_cents, 'Bonus team');
        $this->assertSame(Money::fromRinggit(600), $reward->total_cents, 'Jumlah');

        $this->assertSame(PeriodStatus::Locked, $period->refresh()->status);
        $this->assertSame(AssessmentStatus::Locked, $assessment->refresh()->status);
    }

    public function test_high_sales_earn_the_incentive_on_top(): void
    {
        $staff = $this->makeStaff('Ali', 'S002');
        $this->recordSales($staff, 60_000);

        $period = app(PeriodService::class)->open($this->business, 2026, 8, $this->admin);
        $assessment = $period->assessments()->where('staff_id', $staff->id)->firstOrFail();

        // Sales are capped at 40 even though the target was exceeded by half.
        $this->assertSame('40.00', $assessment->sales_score);

        $this->scoreEverything($assessment, 9);
        $workflow = app(AssessmentWorkflow::class);
        $workflow->submit($assessment->refresh(), $this->supervisor);
        $workflow->approve($assessment->refresh(), $this->admin);
        $this->approveTeamAt($period, 90.0);

        app(PeriodService::class)->lock($period->refresh(), $this->admin);

        $reward = Reward::where('staff_id', $staff->id)->firstOrFail();

        $this->assertSame(Money::fromRinggit(200), $reward->high_sales_incentive_cents);
        $this->assertSame(Money::fromRinggit(800), $reward->total_cents);
    }

    public function test_a_period_cannot_be_locked_while_assessments_are_outstanding(): void
    {
        $staff = $this->makeStaff('Siti', 'S003');
        $this->recordSales($staff, 40_000);

        $period = app(PeriodService::class)->open($this->business, 2026, 8, $this->admin);

        $this->expectException(\App\Exceptions\WorkflowException::class);
        app(PeriodService::class)->lock($period, $this->admin);
    }

    public function test_no_approved_assessment_means_no_bonus(): void
    {
        $approved = $this->makeStaff('Amin', 'S004');
        $this->recordSales($approved, 45_000);

        $period = app(PeriodService::class)->open($this->business, 2026, 8, $this->admin);
        $assessment = $period->assessments()->where('staff_id', $approved->id)->firstOrFail();

        // Left in draft on purpose - the reward run must skip it.
        app(\App\Services\RewardService::class)->computeForPeriod($period);

        $this->assertDatabaseCount('rewards', 0);
    }

    public function test_sales_entered_later_flow_through_to_the_score(): void
    {
        $staff = $this->makeStaff('Mira', 'S005');
        $period = app(PeriodService::class)->open($this->business, 2026, 8, $this->admin);

        $assessment = $period->assessments()->where('staff_id', $staff->id)->firstOrFail();
        $this->assertSame('0.00', $assessment->sales_score);

        $this->recordSales($staff, 20_000);
        app(PeriodService::class)->syncSales($period->refresh());

        $this->assertSame('20.00', $assessment->refresh()->sales_score);
    }

    public function test_a_returned_assessment_goes_back_to_draft_with_the_reason_kept(): void
    {
        $staff = $this->makeStaff('Ahmad', 'S001');
        $this->recordSales($staff, 40_000);
        $period = app(PeriodService::class)->open($this->business, 2026, 8, $this->admin);
        $assessment = $period->assessments()->where('staff_id', $staff->id)->firstOrFail();

        $this->scoreEverything($assessment, 10);
        $workflow = app(AssessmentWorkflow::class);
        $workflow->submit($assessment->refresh(), $this->supervisor);
        $workflow->returnForCorrection($assessment->refresh(), $this->admin, 'Markah disiplin tidak sepadan rekod kehadiran.');

        $assessment->refresh();
        $this->assertSame(AssessmentStatus::Draft, $assessment->status);
        $this->assertStringContainsString('kehadiran', $assessment->returned_reason);
        $this->assertNull($assessment->submitted_at);
    }

    // --- helpers ----------------------------------------------------------

    private function makeStaff(string $name, string $code): Staff
    {
        $user = User::create([
            'business_id' => $this->business->id,
            'name' => $name,
            'login_code' => strtolower($code),
            'password' => Hash::make('123456'),
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        return Staff::create([
            'business_id' => $this->business->id,
            'user_id' => $user->id,
            'employee_code' => $code,
            'name' => $name,
            'joined_on' => '2024-01-01',
            'is_active' => true,
        ]);
    }

    private function recordSales(Staff $staff, int $totalRinggit): void
    {
        DailySale::create([
            'business_id' => $this->business->id,
            'staff_id' => $staff->id,
            'sold_on' => '2026-08-15',
            'amount_cents' => Money::fromRinggit($totalRinggit),
            'focus_qty' => 5,
            'entered_by' => $this->supervisor->id,
        ]);
    }

    /** Give every item the same raw score out of 10 per category. */
    private function scoreEverything(KpiAssessment $assessment, int $rawPerCategory): void
    {
        $criteria = KpiCriteria::where('business_id', $this->business->id)
            ->individual()->orderBy('sort_order')->get();

        $items = [];
        foreach ($criteria->groupBy('category_key') as $group) {
            $remaining = $rawPerCategory;
            foreach ($group as $c) {
                $score = min(2, $remaining);
                $remaining -= $score;
                $items[$c->id] = ['score' => $score, 'note' => $score === 0 ? 'Perlu diperbaiki.' : null];
            }
        }

        app(AssessmentWorkflow::class)->saveScores($assessment, $items, $this->supervisor);
    }

    private function approveTeamAt($period, float $target): void
    {
        $team = $period->teamAssessment;
        $team->performance_score = $target - (float) $team->sales_score;
        $team->total_score = $target;
        $team->status = AssessmentStatus::Approved;
        $team->evaluated_by = $this->supervisor->id;
        $team->approved_by = $this->admin->id;
        $team->save();
    }
}
