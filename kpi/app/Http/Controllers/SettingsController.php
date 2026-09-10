<?php

namespace App\Http\Controllers;

use App\Models\KpiPeriod;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Services\KpiConfig;
use App\Support\Money;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index()
    {
        $this->authorize('manageSettings', KpiPeriod::class);

        $business = auth()->user()->business;

        return view('settings.index', [
            'business' => $business,
            'config' => KpiConfig::forBusiness($business),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('manageSettings', KpiPeriod::class);

        $business = auth()->user()->business;

        $data = $request->validate([
            'individual_target' => ['required', 'numeric', 'min:0'],
            'team_target' => ['required', 'numeric', 'min:0'],
            'prorate_target' => ['nullable', 'boolean'],
            'require_note_on_zero' => ['nullable', 'boolean'],
            'team_bonus_min_working_days' => ['required', 'integer', 'min:0', 'max:31'],
            'bonus' => ['required', 'array', 'size:3'],
            'bonus.*.min_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'bonus.*.amount' => ['required', 'numeric', 'min:0'],
            'incentive' => ['required', 'array', 'size:3'],
            'incentive.*.min_sales' => ['required', 'numeric', 'min:0'],
            'incentive.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        $bonusBands = collect($data['bonus'])->map(fn ($b) => [
            'min_score' => (float) $b['min_score'],
            'amount_cents' => Money::fromRinggit($b['amount']),
        ])->values()->all();

        $values = [
            'individual_target_cents' => Money::fromRinggit($data['individual_target']),
            'team_target_cents' => Money::fromRinggit($data['team_target']),
            'prorate_target' => $request->boolean('prorate_target'),
            'require_note_on_zero' => $request->boolean('require_note_on_zero'),
            'team_bonus_min_working_days' => (int) $data['team_bonus_min_working_days'],
            'bonus_bands' => $bonusBands,
            'team_bonus_bands' => $bonusBands,
            'incentive_bands' => collect($data['incentive'])->map(fn ($b) => [
                'min_sales_cents' => Money::fromRinggit($b['min_sales']),
                'amount_cents' => Money::fromRinggit($b['amount']),
            ])->values()->all(),
        ];

        foreach ($values as $key => $value) {
            Setting::put($business->id, $key, $value);
        }

        $this->audit->record($business, 'settings_updated', businessId: $business->id,
            changes: ['tetapan' => array_keys($values)]);

        return back()->with('ok',
            'Tetapan disimpan. Tempoh yang sudah dibuka kekal menggunakan konfigurasi lamanya.');
    }
}
