<?php

namespace App\Http\Controllers;

use App\Enums\AssessmentStatus;
use App\Models\KpiPeriod;
use App\Models\Reward;
use App\Models\Staff;
use App\Services\KpiConfig;
use App\Services\PeriodService;
use App\Services\SalesLedger;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SalesLedger $ledger,
        private readonly PeriodService $periods,
    ) {}

    public function index()
    {
        $user = auth()->user();
        $business = $user->business;
        $period = $this->currentPeriod($business->id);

        return $user->canEvaluate()
            ? $this->management($business, $period)
            : $this->staff($business, $period);
    }

    private function management($business, ?KpiPeriod $period)
    {
        $today = Carbon::today();
        [$weekFrom, $weekTo] = $this->ledger->weekBounds($today);

        $config = $period
            ? KpiConfig::fromArray($period->config_snapshot)
            : KpiConfig::forBusiness($business);

        $monthFrom = $period ? $period->startsOn() : $today->copy()->startOfMonth();
        $monthTo = $period ? $period->endsOn() : $today->copy()->endOfMonth();

        $monthSales = $this->ledger->teamNetSalesCents($business->id, $monthFrom, $monthTo);

        return view('dashboard.management', [
            'business' => $business,
            'period' => $period,
            'config' => $config,
            'todaySales' => $this->ledger->teamNetSalesCents($business->id, $today, $today),
            'weekSales' => $this->ledger->teamNetSalesCents($business->id, $weekFrom, $weekTo),
            'monthSales' => $monthSales,
            'teamTarget' => $config->teamTargetCents,
            'leaderboard' => $this->ledger->leaderboard($business->id, $monthFrom, $monthTo),
            'outstanding' => $period ? $this->periods->outstanding($period) : collect(),
            'staffCount' => Staff::where('business_id', $business->id)->active()->assessed()->count(),
        ]);
    }

    private function staff($business, ?KpiPeriod $period)
    {
        $user = auth()->user();
        $staff = $user->staff;

        if (! $staff) {
            return view('dashboard.staff', [
                'business' => $business, 'staff' => null, 'period' => $period,
            ]);
        }

        $from = $period ? $period->startsOn() : Carbon::today()->startOfMonth();
        $to = $period ? $period->endsOn() : Carbon::today()->endOfMonth();

        $config = $period
            ? KpiConfig::fromArray($period->config_snapshot)
            : KpiConfig::forBusiness($business);

        $assessment = $period?->assessments()->where('staff_id', $staff->id)->first();

        return view('dashboard.staff', [
            'business' => $business,
            'staff' => $staff,
            'period' => $period,
            'config' => $config,
            'sales' => $this->ledger->netSalesCents($staff, $from, $to),
            'focusQty' => $this->ledger->netFocusQty($staff, $from, $to),
            // Draft scores are never shown to staff - a number that is still
            // being argued over should not reach the person it is about.
            'assessment' => $assessment && $assessment->status->isFinal() ? $assessment : null,
            'pending' => $assessment && ! $assessment->status->isFinal(),
            'reward' => $period ? Reward::where('period_id', $period->id)
                ->where('staff_id', $staff->id)->first() : null,
            'rank' => $this->ledger->leaderboard($business->id, $from, $to)
                ->firstWhere('staff.id', $staff->id)['rank'] ?? null,
        ]);
    }

    private function currentPeriod(int $businessId): ?KpiPeriod
    {
        return KpiPeriod::where('business_id', $businessId)
            ->orderByDesc('year')->orderByDesc('month')
            ->first();
    }
}
