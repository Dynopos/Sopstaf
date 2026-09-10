<?php

namespace Database\Seeders;

use App\Enums\AssessmentStatus;
use App\Enums\Role;
use App\Models\Business;
use App\Models\KpiAssessment;
use App\Models\KpiCriteria;
use App\Models\User;
use App\Services\AssessmentWorkflow;
use App\Services\PeriodService;
use App\Services\RewardService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Puts the demo data through a realistic cycle so every screen has something on
 * it: one month closed with rewards paid out, one month still being worked on.
 */
class DemoCycleSeeder extends Seeder
{
    /** Raw score out of 10 per category, per staff member. */
    private const SCORES = [
        'Ahmad' => [10, 9, 9, 10, 9],
        'Ali' => [9, 9, 8, 9, 9],
        'Siti' => [10, 10, 9, 9, 10],
        'Amin' => [7, 8, 6, 7, 8],
        'Mira' => [8, 9, 9, 8, 8],
    ];

    public function run(): void
    {
        $business = Business::where('slug', config('kpi.business.slug'))->firstOrFail();
        $admin = User::where('business_id', $business->id)->where('role', Role::Admin)->firstOrFail();
        $supervisor = User::where('business_id', $business->id)->where('role', Role::Supervisor)->firstOrFail();

        $workflow = app(AssessmentWorkflow::class);
        $criteria = KpiCriteria::where('business_id', $business->id)
            ->individual()->orderBy('sort_order')->get()->groupBy('category_key');

        // The earlier month runs the whole way through and gets locked, so the
        // rewards screen shows real figures.
        $closed = Carbon::now()->subMonthsNoOverflow(2);
        $closedPeriod = app(PeriodService::class)->open($business, $closed->year, $closed->month, $admin);
        $this->score($closedPeriod, $criteria, $workflow, $supervisor, $admin, approveAll: true);
        $this->approveTeam($closedPeriod, $supervisor, $admin);
        app(PeriodService::class)->lock($closedPeriod->refresh(), $admin);

        // The current month is mid-flight, with one assessment still waiting.
        $open = Carbon::now()->subMonthNoOverflow();
        $openPeriod = app(PeriodService::class)->open($business, $open->year, $open->month, $admin);
        $this->score($openPeriod, $criteria, $workflow, $supervisor, $admin, approveAll: false);
        $this->approveTeam($openPeriod, $supervisor, $admin);

        $this->command?->info("Kitaran demo: {$closedPeriod->label()} dikunci, {$openPeriod->label()} sedang berjalan.");
    }

    private function score($period, $criteria, AssessmentWorkflow $workflow, User $supervisor, User $admin, bool $approveAll): void
    {
        foreach ($period->assessments()->with('staff')->get() as $assessment) {
            $raws = self::SCORES[$assessment->staff->name] ?? [8, 8, 8, 8, 8];

            $workflow->saveScores($assessment, $this->items($criteria, $raws), $supervisor);
            $workflow->submit($assessment->refresh(), $supervisor);

            // Amin waits on approval in the open month, so the outstanding list
            // on the dashboard is not empty.
            if ($approveAll || $assessment->staff->name !== 'Amin') {
                $workflow->approve($assessment->refresh(), $admin);
            }
        }
    }

    /** Spread a raw category total of 0-10 across five items. */
    private function items($criteria, array $raws): array
    {
        $items = [];
        $i = 0;

        foreach ($criteria as $group) {
            $remaining = $raws[$i++] ?? 8;

            foreach ($group as $c) {
                $score = max(0, min(2, $remaining));
                $remaining -= $score;
                $items[$c->id] = [
                    'score' => $score,
                    'note' => $score === 0 ? 'Perlu penambahbaikan — dibincang dengan staf.' : null,
                ];
            }
        }

        return $items;
    }

    private function approveTeam($period, User $supervisor, User $admin): void
    {
        $team = $period->teamAssessment;
        $criteria = KpiCriteria::where('business_id', $period->business_id)
            ->team()->orderBy('sort_order')->get();

        foreach ($criteria as $c) {
            $team->items()->updateOrCreate(['criteria_id' => $c->id], ['score' => 2]);
        }

        $performance = app(\App\Services\ScoreCalculator::class)->performance(
            $criteria, $team->items()->pluck('score', 'criteria_id')->all(),
        );

        $team->performance_score = $performance->total;
        $team->total_score = (float) $team->sales_score + $performance->total;
        $team->status = AssessmentStatus::Approved;
        $team->evaluated_by = $supervisor->id;
        $team->approved_by = $admin->id;
        $team->approved_at = now();
        $team->save();
    }
}
