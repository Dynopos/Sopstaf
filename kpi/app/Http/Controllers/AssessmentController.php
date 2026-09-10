<?php

namespace App\Http\Controllers;

use App\Exceptions\WorkflowException;
use App\Models\KpiAssessment;
use App\Models\KpiCriteria;
use App\Models\KpiPeriod;
use App\Services\AssessmentWorkflow;
use App\Services\KpiConfig;
use App\Services\ScoreCalculator;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function __construct(
        private readonly AssessmentWorkflow $workflow,
        private readonly ScoreCalculator $calculator,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', KpiAssessment::class);

        $business = auth()->user()->business;

        $period = $request->query('period')
            ? KpiPeriod::where('business_id', $business->id)->findOrFail($request->query('period'))
            : KpiPeriod::where('business_id', $business->id)
                ->orderByDesc('year')->orderByDesc('month')->first();

        return view('assessments.index', [
            'business' => $business,
            'period' => $period,
            'periods' => KpiPeriod::where('business_id', $business->id)
                ->orderByDesc('year')->orderByDesc('month')->get(),
            'assessments' => $period
                ? $period->assessments()->with('staff', 'evaluator')->get()
                    ->sortBy('staff.name')->values()
                : collect(),
            'team' => $period?->teamAssessment,
        ]);
    }

    public function edit(KpiAssessment $assessment)
    {
        $this->authorize('view', $assessment);

        $assessment->load('staff', 'period', 'items');

        $criteria = KpiCriteria::where('business_id', $assessment->staff->business_id)
            ->individual()->active()->orderBy('sort_order')->get();

        $scores = $assessment->items->pluck('score', 'criteria_id')->all();
        $notes = $assessment->items->pluck('note', 'criteria_id')->all();

        return view('assessments.edit', [
            'business' => auth()->user()->business,
            'assessment' => $assessment,
            'criteria' => $criteria->groupBy('category_key'),
            'scores' => $scores,
            'notes' => $notes,
            'performance' => $this->calculator->performance($criteria, $scores),
            'config' => KpiConfig::fromArray($assessment->period->config_snapshot),
            'canEdit' => auth()->user()->can('update', $assessment),
        ]);
    }

    public function update(Request $request, KpiAssessment $assessment)
    {
        $this->authorize('update', $assessment);

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.score' => ['required', 'integer', 'min:0', 'max:2'],
            'items.*.note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->workflow->saveScores($assessment, $data['items'], auth()->user());
        } catch (WorkflowException $e) {
            return back()->with('err', $e->getMessage());
        }

        if ($request->boolean('submit')) {
            try {
                $this->workflow->submit($assessment->refresh(), auth()->user());
            } catch (WorkflowException $e) {
                return back()->with('err', $e->getMessage());
            }

            return redirect()->route('assessments.index')
                ->with('ok', "Penilaian {$assessment->staff->name} dihantar untuk kelulusan.");
        }

        return back()->with('ok', 'Markah disimpan sebagai draf.');
    }

    public function approve(KpiAssessment $assessment)
    {
        $this->authorize('approve', $assessment);

        try {
            $this->workflow->approve($assessment, auth()->user());
        } catch (WorkflowException $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', "Penilaian {$assessment->staff->name} diluluskan.");
    }

    public function returnForCorrection(Request $request, KpiAssessment $assessment)
    {
        $this->authorize('returnForCorrection', $assessment);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ], ['reason.required' => 'Sebab pemulangan diwajibkan.']);

        try {
            $this->workflow->returnForCorrection($assessment, auth()->user(), $data['reason']);
        } catch (WorkflowException $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', 'Penilaian dipulangkan untuk pembetulan.');
    }

    public function reopen(Request $request, KpiAssessment $assessment)
    {
        $this->authorize('reopen', $assessment);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ], ['reason.required' => 'Sebab buka semula diwajibkan.']);

        try {
            $this->workflow->reopen($assessment, auth()->user(), $data['reason']);
        } catch (WorkflowException $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', 'Penilaian dibuka semula. Perubahan direkod dalam audit trail.');
    }
}
