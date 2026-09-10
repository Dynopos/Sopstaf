<?php

namespace App\Http\Controllers;

use App\Exceptions\WorkflowException;
use App\Models\KpiPeriod;
use App\Services\PeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PeriodController extends Controller
{
    public function __construct(private readonly PeriodService $periods) {}

    public function open(Request $request)
    {
        $this->authorize('open', KpiPeriod::class);

        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $period = $this->periods->open(
            auth()->user()->business, $data['year'], $data['month'], auth()->user(),
        );

        return redirect()->route('assessments.index', ['period' => $period->id])
            ->with('ok', "Tempoh {$period->label()} dibuka. Konfigurasi semasa telah dibekukan untuk bulan ini.");
    }

    public function lock(KpiPeriod $period)
    {
        $this->authorize('lock', $period);

        try {
            $this->periods->lock($period, auth()->user());
        } catch (WorkflowException $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', "Tempoh {$period->label()} dikunci dan ganjaran dikira.");
    }

    public function reopen(Request $request, KpiPeriod $period)
    {
        $this->authorize('reopen', $period);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ], ['reason.required' => 'Sebab buka semula diwajibkan.']);

        try {
            $this->periods->reopen($period, auth()->user(), $data['reason']);
        } catch (WorkflowException $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', 'Tempoh dibuka semula. Tindakan ini direkod dalam audit trail.');
    }
}
