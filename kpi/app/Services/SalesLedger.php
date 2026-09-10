<?php

namespace App\Services;

use App\Models\DailySale;
use App\Models\KpiPeriod;
use App\Models\SalesAdjustment;
use App\Models\Staff;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Daily entries are the single source of truth. Weekly and monthly figures are
 * always summed from them, never keyed in separately - that removes the entire
 * class of bug where a summary and its detail disagree.
 */
final class SalesLedger
{
    /** Gross sales plus adjustments falling in the same window. */
    public function netSalesCents(Staff|int $staff, Carbon $from, Carbon $to): int
    {
        $staffId = $staff instanceof Staff ? $staff->id : $staff;

        $gross = (int) DailySale::where('staff_id', $staffId)
            ->whereBetween('sold_on', [$from->toDateString(), $to->toDateString()])
            ->sum('amount_cents');

        $adjustments = (int) SalesAdjustment::where('staff_id', $staffId)
            ->whereBetween('adjusted_on', [$from->toDateString(), $to->toDateString()])
            ->sum('amount_cents');

        return $gross + $adjustments;
    }

    public function netFocusQty(Staff|int $staff, Carbon $from, Carbon $to): int
    {
        $staffId = $staff instanceof Staff ? $staff->id : $staff;

        $gross = (int) DailySale::where('staff_id', $staffId)
            ->whereBetween('sold_on', [$from->toDateString(), $to->toDateString()])
            ->sum('focus_qty');

        $adjustments = (int) SalesAdjustment::where('staff_id', $staffId)
            ->whereBetween('adjusted_on', [$from->toDateString(), $to->toDateString()])
            ->sum('focus_qty');

        return $gross + $adjustments;
    }

    public function netSalesForPeriod(Staff|int $staff, KpiPeriod $period): int
    {
        return $this->netSalesCents($staff, $period->startsOn(), $period->endsOn());
    }

    public function teamNetSalesCents(int $businessId, Carbon $from, Carbon $to): int
    {
        $gross = (int) DailySale::where('business_id', $businessId)
            ->whereBetween('sold_on', [$from->toDateString(), $to->toDateString()])
            ->sum('amount_cents');

        $adjustments = (int) SalesAdjustment::where('business_id', $businessId)
            ->whereBetween('adjusted_on', [$from->toDateString(), $to->toDateString()])
            ->sum('amount_cents');

        return $gross + $adjustments;
    }

    /**
     * Leaderboard rows for a window, ranked by net sales. Ties share a place:
     * two people in second means the next one is fourth.
     *
     * @return Collection<int, array{staff: Staff, sales_cents: int, focus_qty: int, rank: int}>
     */
    public function leaderboard(int $businessId, Carbon $from, Carbon $to): Collection
    {
        // Only people carrying a sales target are ranked - a supervisor with no
        // target of their own would otherwise sit at the bottom on RM0.
        $staff = Staff::where('business_id', $businessId)->active()->assessed()->get();

        $rows = $staff->map(fn (Staff $s) => [
            'staff' => $s,
            'sales_cents' => $this->netSalesCents($s, $from, $to),
            'focus_qty' => $this->netFocusQty($s, $from, $to),
        ])->sortByDesc('sales_cents')->values();

        $rank = 0;
        $seen = 0;
        $previous = null;

        return $rows->map(function (array $row) use (&$rank, &$seen, &$previous) {
            $seen++;
            if ($previous === null || $row['sales_cents'] !== $previous) {
                $rank = $seen;
                $previous = $row['sales_cents'];
            }
            $row['rank'] = $rank;

            return $row;
        });
    }

    /** Monday to Sunday (decision K5). Weeks may cross a month boundary. */
    public function weekBounds(Carbon $anyDayInWeek): array
    {
        return [
            $anyDayInWeek->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
            $anyDayInWeek->copy()->endOfWeek(Carbon::SUNDAY)->startOfDay(),
        ];
    }
}
