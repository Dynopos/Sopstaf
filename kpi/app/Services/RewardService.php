<?php

namespace App\Services;

use App\Enums\AssessmentStatus;
use App\Enums\RewardStatus;
use App\Exceptions\WorkflowException;
use App\Models\KpiPeriod;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Turns approved assessments into reward rows.
 *
 * Calculating is not authority to pay. Rewards carry their own status, because
 * the source document requires management to verify sales, attendance,
 * discipline and SOP records before any bonus is paid out.
 */
final class RewardService
{
    public function __construct(
        private readonly RewardCalculator $calculator,
        private readonly AuditLogger $audit,
    ) {}

    public function computeForPeriod(KpiPeriod $period): int
    {
        $config = KpiConfig::fromArray($period->config_snapshot);
        $team = $period->teamAssessment;

        $teamScore = $team && $team->status->isFinal() ? (float) $team->total_score : 0.0;
        $teamApproved = $team && $team->status->isFinal();

        $count = 0;

        DB::transaction(function () use ($period, $config, $teamScore, $teamApproved, &$count) {
            foreach ($period->assessments()->with('staff')->get() as $assessment) {
                if (! in_array($assessment->status, [AssessmentStatus::Approved, AssessmentStatus::Locked], true)) {
                    continue; // no approved KPI, no bonus
                }

                $workingDays = $assessment->staff->workingDaysWithin(
                    $period->startsOn(), $period->endsOn(),
                );

                $eligible = $teamApproved && $this->calculator->isTeamBonusEligible(
                    $workingDays, true, $config,
                );

                $breakdown = $this->calculator->breakdown(
                    kpiTotal: (float) $assessment->total_score,
                    netSalesCents: (int) $assessment->sales_amount_cents,
                    teamKpiTotal: $teamScore,
                    teamEligible: $eligible,
                    config: $config,
                );

                $snapshot = $breakdown->snapshot;
                $snapshot['working_days'] = $workingDays;
                $snapshot['computed_at'] = Carbon::now()->toIso8601String();

                Reward::updateOrCreate(
                    ['period_id' => $period->id, 'staff_id' => $assessment->staff_id],
                    [
                        'kpi_total' => $assessment->total_score,
                        'individual_bonus_cents' => $breakdown->individualBonusCents,
                        'high_sales_incentive_cents' => $breakdown->highSalesIncentiveCents,
                        'team_bonus_cents' => $breakdown->teamBonusCents,
                        'total_cents' => $breakdown->totalCents(),
                        'calc_snapshot' => $snapshot,
                        'status' => RewardStatus::Calculated,
                    ],
                );

                $count++;
            }
        });

        return $count;
    }

    public function verify(Reward $reward, User $actor): Reward
    {
        if ($reward->status !== RewardStatus::Calculated) {
            throw new WorkflowException('Hanya ganjaran berstatus Dikira boleh disahkan.');
        }

        $reward->status = RewardStatus::Verified;
        $reward->verified_by = $actor->id;
        $reward->verified_at = Carbon::now();
        $reward->save();

        $this->audit->record($reward, 'verified', $actor,
            businessId: $reward->staff->business_id);

        return $reward;
    }

    public function markPaid(Reward $reward, User $actor): Reward
    {
        if ($reward->status !== RewardStatus::Verified) {
            throw new WorkflowException('Ganjaran mesti disahkan sebelum ditanda dibayar.');
        }

        $reward->status = RewardStatus::Paid;
        $reward->paid_at = Carbon::now();
        $reward->save();

        $this->audit->record($reward, 'paid', $actor,
            businessId: $reward->staff->business_id);

        return $reward;
    }

    public function withhold(Reward $reward, User $actor, string $reason): Reward
    {
        if (trim($reason) === '') {
            throw new WorkflowException('Sebab penahanan diwajibkan.');
        }

        $reward->status = RewardStatus::Withheld;
        $reward->withheld_reason = $reason;
        $reward->save();

        $this->audit->record($reward, 'withheld', $actor, reason: $reason,
            businessId: $reward->staff->business_id);

        return $reward;
    }
}
