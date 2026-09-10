<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\KpiPeriod;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manageSettings', KpiPeriod::class);

        $logs = AuditLog::where('business_id', auth()->user()->business_id)
            ->with('actor')
            ->when($request->query('action'), fn ($q, $a) => $q->where('action', $a))
            ->latest('created_at')
            ->paginate(40)
            ->withQueryString();

        return view('audit.index', [
            'business' => auth()->user()->business,
            'logs' => $logs,
            'actions' => AuditLog::where('business_id', auth()->user()->business_id)
                ->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
