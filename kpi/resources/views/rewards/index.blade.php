@extends('layouts.app')
@section('title', 'Ganjaran')
@section('content')

<h1>Ganjaran</h1>
<p class="sub">{{ $period?->label() ?? 'Belum ada tempoh' }}</p>

@if($periods->count() > 1)
  <form method="get" class="card" style="padding:14px">
    <div class="row" style="align-items:end">
      <div class="field" style="margin:0">
        <label for="p">Tempoh</label>
        <select id="p" name="period" onchange="this.form.submit()">
          @foreach($periods as $p)
            <option value="{{ $p->id }}" @selected($period && $p->id === $period->id)>{{ $p->label() }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </form>
@endif

<div class="card">
  <h2>💰 Pecahan ganjaran</h2>
  @if($rewards->isEmpty())
    <div class="empty">Ganjaran dikira apabila tempoh dikunci.</div>
  @else
  <div class="tw"><table>
    <thead><tr>
      <th>Staf</th><th class="num">KPI</th><th class="num">Bonus KPI</th>
      <th class="num">Insentif</th><th class="num">Bonus Team</th><th class="num">Jumlah</th>
      <th>Status</th>@if(auth()->user()->isAdmin())<th></th>@endif
    </tr></thead>
    <tbody>
    @foreach($rewards as $r)
      <tr>
        <td><b>{{ $r->staff->name }}</b></td>
        <td class="num mono">{{ number_format((float) $r->kpi_total, 2) }}</td>
        <td class="num mono">@rm($r->individual_bonus_cents)</td>
        <td class="num mono">@rm($r->high_sales_incentive_cents)</td>
        <td class="num mono">@rm($r->team_bonus_cents)</td>
        <td class="num mono"><b>@rm($r->total_cents)</b></td>
        <td><span class="tag t-{{ $r->status->value }}">{{ $r->status->label() }}</span></td>
        @if(auth()->user()->isAdmin())
        <td style="text-align:right;white-space:nowrap">
          @if($r->status->value === 'calculated')
            <form method="post" action="{{ route('rewards.verify', $r) }}" style="display:inline">@csrf
              <button class="btn g" style="padding:6px 11px;min-height:0;font-size:12.5px">Sahkan</button>
            </form>
          @elseif($r->status->value === 'verified')
            <form method="post" action="{{ route('rewards.pay', $r) }}" style="display:inline">@csrf
              <button class="btn" style="padding:6px 11px;min-height:0;font-size:12.5px">Tanda Dibayar</button>
            </form>
          @endif
        </td>
        @endif
      </tr>
    @endforeach
    </tbody>
    <tfoot><tr>
      <td colspan="5" style="text-align:right"><b>Jumlah keseluruhan</b></td>
      <td class="num mono"><b>@rm($rewards->sum('total_cents'))</b></td>
      <td colspan="2"></td>
    </tr></tfoot>
  </table></div>

  <p class="hint" style="margin-top:14px">
    Pengiraan bukan kebenaran untuk membayar. Bonus dibayar selepas pengurusan menyemak rekod
    jualan, kehadiran, disiplin dan pematuhan SOP.
  </p>
  @endif
</div>

@if($rewards->isNotEmpty() && auth()->user()->canEvaluate())
<div class="card">
  <h2>🧾 Bagaimana angka ini dikira</h2>
  @foreach($rewards as $r)
    @php $m = $r->calc_snapshot['matched'] ?? []; @endphp
    <div style="padding:11px 0;border-bottom:1px solid var(--line)">
      <b>{{ $r->staff->name }}</b>
      <div class="muted" style="margin-top:3px">
        KPI {{ number_format((float) $r->kpi_total, 2) }} → gred {{ $m['individual_band'] ?? '—' }} ·
        jualan @rmshort($r->calc_snapshot['net_sales_cents'] ?? 0) → insentif {{ $m['incentive_band'] ?? '—' }} ·
        bonus team {{ $m['team_band'] ?? '—' }}
        ({{ $r->calc_snapshot['working_days'] ?? 0 }} hari bekerja)
      </div>
    </div>
  @endforeach
</div>
@endif
@endsection
