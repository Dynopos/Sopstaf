<?php

namespace App\Http\Controllers;

use App\Exceptions\WorkflowException;
use App\Models\KpiPeriod;
use App\Models\Reward;
use App\Services\RewardService;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function __construct(private readonly RewardService $rewards) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $business = $user->business;

        $period = $request->query('period')
            ? KpiPeriod::where('business_id', $business->id)->findOrFail($request->query('period'))
            : KpiPeriod::where('business_id', $business->id)
                ->orderByDesc('year')->orderByDesc('month')->first();

        $query = $period ? $period->rewards()->with('staff') : null;

        // Staff see their own row and nothing else.
        if ($query && ! $user->canEvaluate()) {
            $query->where('staff_id', $user->staff?->id ?? 0);
        }

        return view('rewards.index', [
            'business' => $business,
            'period' => $period,
            'periods' => KpiPeriod::where('business_id', $business->id)
                ->orderByDesc('year')->orderByDesc('month')->get(),
            'rewards' => $query ? $query->get()->sortByDesc('total_cents')->values() : collect(),
        ]);
    }

    public function verify(Reward $reward)
    {
        $this->authorize('verify', $reward);

        try {
            $this->rewards->verify($reward, auth()->user());
        } catch (WorkflowException $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', "Ganjaran {$reward->staff->name} disahkan.");
    }

    public function markPaid(Reward $reward)
    {
        $this->authorize('markPaid', $reward);

        try {
            $this->rewards->markPaid($reward, auth()->user());
        } catch (WorkflowException $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', "Ganjaran {$reward->staff->name} ditanda dibayar.");
    }

    public function withhold(Request $request, Reward $reward)
    {
        $this->authorize('withhold', $reward);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ], ['reason.required' => 'Sebab penahanan diwajibkan.']);

        try {
            $this->rewards->withhold($reward, auth()->user(), $data['reason']);
        } catch (WorkflowException $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', 'Ganjaran ditahan. Sebab direkod.');
    }
}
