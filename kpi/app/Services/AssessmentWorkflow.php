<?php

namespace App\Services;

use App\Enums\AssessmentStatus;
use App\Exceptions\WorkflowException;
use App\Models\KpiAssessment;
use App\Models\KpiCriteria;
use App\Models\TeamAssessment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Draft -> Submitted -> Approved -> Locked, with Reopened as the only way back.
 *
 * Every guard here is enforced on the server. Hiding a button in a Blade
 * template is not enforcement - a form post goes straight past it.
 */
final class AssessmentWorkflow
{
    /** @var array<string, string[]> */
    private const TRANSITIONS = [
        'draft' => ['submitted'],
        'reopened' => ['submitted'],
        'submitted' => ['draft', 'approved'],
        'approved' => ['locked'],
        'locked' => ['reopened'],
    ];

    public function __construct(
        private readonly AuditLogger $audit,
        private readonly ScoreCalculator $calculator,
    ) {}

    public function canTransition(AssessmentStatus $from, AssessmentStatus $to): bool
    {
        return in_array($to->value, self::TRANSITIONS[$from->value] ?? [], true);
    }

    private function assertTransition(AssessmentStatus $from, AssessmentStatus $to): void
    {
        if (! $this->canTransition($from, $to)) {
            throw new WorkflowException(
                "Peralihan dari {$from->label()} ke {$to->label()} tidak dibenarkan."
            );
        }
    }

    /**
     * Save scores. Only possible while the assessment is still editable - an
     * approved score is final until somebody reopens it on the record.
     *
     * @param  array<int, array{score: int, note: ?string}>  $items  keyed by criteria id
     */
    public function saveScores(KpiAssessment $assessment, array $items, User $actor): KpiAssessment
    {
        if (! $assessment->status->isEditable()) {
            throw new WorkflowException(
                "Penilaian berstatus {$assessment->status->label()} tidak boleh diubah."
            );
        }

        return DB::transaction(function () use ($assessment, $items, $actor) {
            foreach ($items as $criteriaId => $row) {
                $score = (int) ($row['score'] ?? 0);

                if ($score < 0 || $score > 2) {
                    throw new WorkflowException('Skor mesti 0, 1 atau 2.');
                }

                $assessment->items()->updateOrCreate(
                    ['criteria_id' => $criteriaId],
                    ['score' => $score, 'note' => $row['note'] ?? null],
                );
            }

            $this->recalculate($assessment);
            $assessment->evaluated_by = $actor->id;
            $assessment->version++;
            $assessment->save();

            $this->audit->record($assessment, 'scores_saved', $actor,
                businessId: $assessment->staff->business_id);

            return $assessment->fresh(['items']);
        });
    }

    public function submit(KpiAssessment $assessment, User $actor): KpiAssessment
    {
        $this->assertTransition($assessment->status, AssessmentStatus::Submitted);
        $this->assertComplete($assessment);

        $assessment->status = AssessmentStatus::Submitted;
        $assessment->submitted_at = Carbon::now();
        $assessment->evaluated_by ??= $actor->id;
        $assessment->returned_reason = null;
        $assessment->save();

        $this->audit->record($assessment, 'submitted', $actor,
            businessId: $assessment->staff->business_id);

        return $assessment;
    }

    /** Sent back for correction. A reason is required - "fix it" is not feedback. */
    public function returnForCorrection(KpiAssessment $assessment, User $actor, string $reason): KpiAssessment
    {
        $this->assertTransition($assessment->status, AssessmentStatus::Draft);

        if (trim($reason) === '') {
            throw new WorkflowException('Sebab pemulangan diwajibkan.');
        }

        $assessment->status = AssessmentStatus::Draft;
        $assessment->returned_reason = $reason;
        $assessment->submitted_at = null;
        $assessment->save();

        $this->audit->record($assessment, 'returned', $actor, reason: $reason,
            businessId: $assessment->staff->business_id);

        return $assessment;
    }

    public function approve(KpiAssessment $assessment, User $actor): KpiAssessment
    {
        $this->assertTransition($assessment->status, AssessmentStatus::Approved);

        // The person who scored it cannot be the person who signs it off.
        if ($assessment->evaluated_by === $actor->id) {
            throw new WorkflowException(
                'Penilai tidak boleh meluluskan penilaian sendiri. Kelulusan mesti daripada orang lain.'
            );
        }

        $assessment->status = AssessmentStatus::Approved;
        $assessment->approved_by = $actor->id;
        $assessment->approved_at = Carbon::now();
        $assessment->save();

        $this->audit->record($assessment, 'approved', $actor,
            businessId: $assessment->staff->business_id);

        return $assessment;
    }

    public function lock(KpiAssessment $assessment, User $actor): KpiAssessment
    {
        $this->assertTransition($assessment->status, AssessmentStatus::Locked);

        $assessment->status = AssessmentStatus::Locked;
        $assessment->save();

        $this->audit->record($assessment, 'locked', $actor,
            businessId: $assessment->staff->business_id);

        return $assessment;
    }

    public function reopen(KpiAssessment $assessment, User $actor, string $reason): KpiAssessment
    {
        $this->assertTransition($assessment->status, AssessmentStatus::Reopened);

        if (trim($reason) === '') {
            throw new WorkflowException('Sebab buka semula diwajibkan.');
        }

        $assessment->status = AssessmentStatus::Reopened;
        $assessment->save();

        $this->audit->record($assessment, 'reopened', $actor, reason: $reason,
            businessId: $assessment->staff->business_id);

        return $assessment;
    }

    /**
     * Nothing may be submitted with a gap in it, and every zero needs a note.
     * That protects both sides: the staff member knows why, and the company has
     * it in writing if the bonus is ever disputed.
     */
    public function assertComplete(KpiAssessment $assessment): void
    {
        $criteria = $this->criteriaFor($assessment);
        $items = $assessment->items()->get()->keyBy('criteria_id');

        $missing = $criteria->reject(fn ($c) => $items->has($c->id));

        if ($missing->isNotEmpty()) {
            throw new WorkflowException(
                "Masih ada {$missing->count()} item belum dinilai."
            );
        }

        if ($assessment->period->config('require_note_on_zero', true)) {
            $zerosWithoutNote = $items->filter(
                fn ($item) => $item->score === 0 && trim((string) $item->note) === ''
            );

            if ($zerosWithoutNote->isNotEmpty()) {
                throw new WorkflowException(
                    "Skor 0 mesti disertakan catatan. {$zerosWithoutNote->count()} item belum ada catatan."
                );
            }
        }
    }

    public function recalculate(KpiAssessment $assessment): KpiAssessment
    {
        $criteria = $this->criteriaFor($assessment);
        $scores = $assessment->items()->pluck('score', 'criteria_id')->all();

        $performance = $this->calculator->performance($criteria, $scores);

        $assessment->performance_score = $performance->total;
        $assessment->total_score = $this->calculator->total(
            (float) $assessment->sales_score,
            $performance->total,
        );

        return $assessment;
    }

    private function criteriaFor(KpiAssessment $assessment): \Illuminate\Support\Collection
    {
        return KpiCriteria::where('business_id', $assessment->staff->business_id)
            ->individual()
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    // --- Team assessments follow the same rules ---------------------------

    public function submitTeam(TeamAssessment $assessment, User $actor): TeamAssessment
    {
        $this->assertTransition($assessment->status, AssessmentStatus::Submitted);

        $assessment->status = AssessmentStatus::Submitted;
        $assessment->submitted_at = Carbon::now();
        $assessment->evaluated_by ??= $actor->id;
        $assessment->save();

        $this->audit->record($assessment, 'submitted', $actor,
            businessId: $assessment->period->business_id);

        return $assessment;
    }

    public function approveTeam(TeamAssessment $assessment, User $actor): TeamAssessment
    {
        $this->assertTransition($assessment->status, AssessmentStatus::Approved);

        if ($assessment->evaluated_by === $actor->id) {
            throw new WorkflowException(
                'Penilai tidak boleh meluluskan penilaian team yang dinilainya sendiri.'
            );
        }

        $assessment->status = AssessmentStatus::Approved;
        $assessment->approved_by = $actor->id;
        $assessment->approved_at = Carbon::now();
        $assessment->save();

        $this->audit->record($assessment, 'approved', $actor,
            businessId: $assessment->period->business_id);

        return $assessment;
    }
}
