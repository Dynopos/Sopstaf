<?php

namespace App\Http\Controllers;

use App\Enums\PeriodStatus;
use App\Models\DailySale;
use App\Models\KpiPeriod;
use App\Models\SalesAdjustment;
use App\Models\Staff;
use App\Services\AuditLogger;
use App\Services\PeriodService;
use App\Services\SalesLedger;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SalesController extends Controller
{
    public function __construct(
        private readonly SalesLedger $ledger,
        private readonly AuditLogger $audit,
        private readonly PeriodService $periods,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('create', DailySale::class);

        $business = auth()->user()->business;
        $date = Carbon::parse($request->query('date', Carbon::today()->toDateString()));

        $staff = Staff::where('business_id', $business->id)->active()
            ->orderBy('name')->get();

        $existing = DailySale::where('business_id', $business->id)
            ->whereDate('sold_on', $date)->get()->keyBy('staff_id');

        return view('sales.index', [
            'business' => $business,
            'date' => $date,
            'staff' => $staff,
            'existing' => $existing,
            'monthTotals' => $staff->mapWithKeys(fn ($s) => [
                $s->id => $this->ledger->netSalesCents(
                    $s, $date->copy()->startOfMonth(), $date->copy()->endOfMonth()
                ),
            ]),
            'locked' => $this->isLocked($business->id, $date),
            'recentAdjustments' => SalesAdjustment::where('business_id', $business->id)
                ->with('staff')->latest()->limit(5)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', DailySale::class);

        $business = auth()->user()->business;

        $data = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'rows' => ['required', 'array'],
            'rows.*.amount' => ['nullable', 'numeric', 'min:0'],
            'rows.*.focus_qty' => ['nullable', 'integer', 'min:0'],
            'rows.*.note' => ['nullable', 'string', 'max:500'],
        ], [
            'date.before_or_equal' => 'Tarikh jualan tidak boleh tarikh hadapan.',
        ]);

        $date = Carbon::parse($data['date']);

        if ($this->isLocked($business->id, $date)) {
            return back()->with('err', 'Tempoh untuk tarikh ini sudah dikunci. Guna rekod pelarasan.');
        }

        $saved = 0;

        foreach ($data['rows'] as $staffId => $row) {
            $staff = Staff::where('business_id', $business->id)->find($staffId);

            if (! $staff || ($row['amount'] ?? null) === null) {
                continue;
            }

            $sale = DailySale::updateOrCreate(
                ['staff_id' => $staff->id, 'sold_on' => $date->toDateString()],
                [
                    'business_id' => $business->id,
                    'amount_cents' => Money::fromRinggit($row['amount']),
                    'focus_qty' => (int) ($row['focus_qty'] ?? 0),
                    'note' => $row['note'] ?? null,
                    'source' => 'manual',
                    'entered_by' => auth()->id(),
                ],
            );

            $this->audit->record($sale, $sale->wasRecentlyCreated ? 'created' : 'updated',
                changes: $sale->wasRecentlyCreated ? null : $this->audit->diff($sale),
                businessId: $business->id);

            $saved++;
        }

        $this->refreshPeriod($business->id, $date);

        return back()->with('ok', "Jualan {$date->format('d/m/Y')} disimpan untuk {$saved} staf.");
    }

    public function storeAdjustment(Request $request)
    {
        $this->authorize('create', DailySale::class);

        $business = auth()->user()->business;

        $data = $request->validate([
            'staff_id' => ['required', 'integer'],
            'adjusted_on' => ['required', 'date', 'before_or_equal:today'],
            'original_sale_date' => ['nullable', 'date'],
            'amount' => ['required', 'numeric'],
            'focus_qty' => ['nullable', 'integer'],
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'reason.required' => 'Sebab pelarasan diwajibkan.',
            'reason.min' => 'Sebab pelarasan terlalu pendek.',
        ]);

        $staff = Staff::where('business_id', $business->id)->findOrFail($data['staff_id']);

        $adjustment = SalesAdjustment::create([
            'business_id' => $business->id,
            'staff_id' => $staff->id,
            'adjusted_on' => $data['adjusted_on'],
            'original_sale_date' => $data['original_sale_date'] ?? null,
            'amount_cents' => Money::fromRinggit($data['amount']),
            'focus_qty' => (int) ($data['focus_qty'] ?? 0),
            'reason' => $data['reason'],
            'entered_by' => auth()->id(),
        ]);

        $this->audit->record($adjustment, 'created', reason: $data['reason'], businessId: $business->id);
        $this->refreshPeriod($business->id, Carbon::parse($data['adjusted_on']));

        return back()->with('ok', 'Rekod pelarasan disimpan.');
    }

    private function isLocked(int $businessId, Carbon $date): bool
    {
        $period = KpiPeriod::where('business_id', $businessId)
            ->where('year', $date->year)->where('month', $date->month)->first();

        return $period && ! $period->status->acceptsSales();
    }

    /** Keep the automatic 40% in step with the ledger. */
    private function refreshPeriod(int $businessId, Carbon $date): void
    {
        $period = KpiPeriod::where('business_id', $businessId)
            ->where('year', $date->year)->where('month', $date->month)->first();

        if ($period && $period->status->acceptsSales()) {
            $this->periods->syncSales($period);
        }
    }
}
