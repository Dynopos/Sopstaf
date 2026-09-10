<?php

namespace App\Services;

use App\Enums\AssessmentStatus;
use App\Enums\PeriodStatus;
use App\Exceptions\WorkflowException;
use App\Models\Business;
use App\Models\KpiAssessment;
use App\Models\KpiPeriod;
use App\Models\Staff;
use App\Models\TeamAssessment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class PeriodService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly ScoreCalculator $calculator,
        private readonly SalesLedger $ledger,
        private readonly RewardService $rewards,
    ) {}

    /**
     * Open a month for assessment. The configuration in force right now is
     * frozen into the period; nothing here reads live settings again.
     */
    public function open(Business $business, int $year, int $month, ?User $actor = null): KpiPeriod
    {
        return DB::transaction(function () use ($business, $year, $month, $actor) {
            $period = KpiPeriod::firstOrNew([
                'business_id' => $business->id,
                'year' => $year,
                'month' => $month,
            ]);

            if ($period->exists) {
                return $period;
            }

            $period->status = PeriodStatus::Open;
            $period->config_snapshot = KpiConfig::forBusiness($business)->toArray();
            $period->opened_at = Carbon::now();
            $period->save();

            $this->createAssessments($period);
            $this->syncSales($period);

            $this->audit->record($period, 'opened', $actor, businessId: $business->id);

            return $period->fresh();
        });
    }

    private function createAssessments(KpiPeriod $period): void
    {
        $staff = Staff::where('business_id', $period->business_id)
            ->active()
            ->assessed()
            ->get()
            ->filter(fn (Staff $s) => $s->workingDaysWithin($period->startsOn(), $period->endsOn()) > 0);

        foreach ($staff as $member) {
            KpiAssessment::firstOrCreate([
                'period_id' => $period->id,
                'staff_id' => $member->id,
            ], [
                'status' => AssessmentStatus::Draft,
            ]);
        }

        TeamAssessment::firstOrCreate(
            ['period_id' => $period->id],
            ['status' => AssessmentStatus::Draft],
        );
    }

    /**
     * Refresh the automatic 40% from the sales ledger. Supervisors never type
     * this figure - keeping it out of their hands is what stops the sales
     * component drifting toward how they feel about somebody.
     */
    public function syncSales(KpiPeriod $period): void
    {
        $config = KpiConfig::fromArray($period->config_snapshot);
        $start = $period->startsOn();
        $end = $period->endsOn();
        $daysInMonth = $start->daysInMonth;

        foreach ($period->assessments()->with('staff')->get() as $assessment) {
            if ($assessment->status->isFinal()) {
                continue; // approved figures are frozen
            }

            $net = $this->ledger->netSalesCents($assessment->staff, $start, $end);
            $target = $config->individualTargetCents;

            if ($config->prorateTarget) {
                $target = $this->calculator->proratedTarget(
                    $config->individualTargetCents,
                    $assessment->staff->workingDaysWithin($start, $end),
                    $daysInMonth,
                );
            }

            $assessment->sales_amount_cents = $net;
            $assessment->sales_score = $this->calculator->salesScore($net, $target, $config->salesWeight);
            $assessment->total_score = $this->calculator->total(
                (float) $assessment->sales_score,
                (float) $assessment->performance_score,
            );
            $assessment->save();
        }

        if ($team = $period->teamAssessment) {
            if (! $team->status->isFinal()) {
                $teamNet = $this->ledger->teamNetSalesCents($period->business_id, $start, $end);
                $team->sales_amount_cents = $teamNet;
                $team->sales_score = $this->calculator->salesScore(
                    $teamNet, $config->teamTargetCents, $config->salesWeight,
                );
                $team->total_score = $this->calculator->total(
                    (float) $team->sales_score,
                    (float) $team->performance_score,
                );
                $team->save();
            }
        }
    }

    /** Assessments still standing between the month and being closed. */
    public function outstanding(KpiPeriod $period): \Illuminate\Support\Collection
    {
        return $period->assessments()
            ->with('staff')
            ->whereIn('status', [
                AssessmentStatus::Draft->value,
                AssessmentStatus::Submitted->value,
                AssessmentStatus::Reopened->value,
            ])
            ->get();
    }

    public function lock(KpiPeriod $period, User $actor): KpiPeriod
    {
        if ($period->status === PeriodStatus::Locked) {
            throw new WorkflowException('Tempoh ini sudah dikunci.');
        }

        $outstanding = $this->outstanding($period);

        if ($outstanding->isNotEmpty()) {
            $names = $outstanding->pluck('staff.name')->join(', ');
            throw new WorkflowException("Masih ada penilaian belum diluluskan: {$names}.");
        }

        return DB::transaction(function () use ($period, $actor) {
            $this->rewards->computeForPeriod($period);

            foreach ($period->assessments as $assessment) {
                if ($assessment->status === AssessmentStatus::Approved) {
                    $assessment->status = AssessmentStatus::Locked;
                    $assessment->save();
                }
            }

            $period->status = PeriodStatus::Locked;
            $period->locked_at = Carbon::now();
            $period->locked_by = $actor->id;
            $period->save();

            $this->audit->record($period, 'locked', $actor, businessId: $period->business_id);

            return $period;
        });
    }

    public function reopen(KpiPeriod $period, User $actor, string $reason): KpiPeriod
    {
        if ($period->status !== PeriodStatus::Locked) {
            throw new WorkflowException('Hanya tempoh yang dikunci boleh dibuka semula.');
        }

        if (trim($reason) === '') {
            throw new WorkflowException('Sebab buka semula diwajibkan.');
        }

        $period->status = PeriodStatus::Reopened;
        $period->reopened_at = Carbon::now();
        $period->reopened_by = $actor->id;
        $period->reopen_reason = $reason;
        $period->save();

        $this->audit->record($period, 'reopened', $actor, reason: $reason,
            businessId: $period->business_id);

        return $period;
    }
}
