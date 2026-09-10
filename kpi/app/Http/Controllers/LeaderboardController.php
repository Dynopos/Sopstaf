<?php

namespace App\Http\Controllers;

use App\Enums\AssessmentStatus;
use App\Models\KpiPeriod;
use App\Services\SalesLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeaderboardController extends Controller
{
    public function __construct(private readonly SalesLedger $ledger) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $business = $user->business;
        $range = $request->query('range', 'month');
        $today = Carbon::today();

        [$from, $to, $label] = match ($range) {
            'day' => [$today, $today, 'Hari ini'],
            'week' => [...$this->ledger->weekBounds($today), 'Minggu ini'],
            'year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear(), 'Tahun '.$today->year],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth(), $today->translatedFormat('F Y')],
        };

        // KPI ranking uses approved assessments only - draft scores must never
        // surface on a public board.
        $kpiRows = collect();
        if ($user->canEvaluate()) {
            $period = KpiPeriod::where('business_id', $business->id)
                ->where('year', $to->year)->where('month', $to->month)->first();

            if ($period) {
                $kpiRows = $period->assessments()
                    ->with('staff')
                    ->whereIn('status', [AssessmentStatus::Approved->value, AssessmentStatus::Locked->value])
                    ->orderByDesc('total_score')
                    ->get();
            }
        }

        return view('leaderboard.index', [
            'business' => $business,
            'rows' => $this->ledger->leaderboard($business->id, $from, $to),
            'kpiRows' => $kpiRows,
            'range' => $range,
            'label' => $label,
        ]);
    }
}
