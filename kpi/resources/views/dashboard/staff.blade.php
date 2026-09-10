@extends('layouts.app')
@section('title', 'KPI Saya')
@section('content')

@if(! $staff)
  <div class="card"><div class="empty">Akaun anda belum dikaitkan dengan rekod staf. Sila hubungi Admin.</div></div>
@else
<h1>Hai, {{ $staff->name }}</h1>
<p class="sub">{{ $period?->label() ?? now()->translatedFormat('F Y') }}</p>

@php
  $target = $config->individualTargetCents;
  // Show what was actually achieved; only the bar is clamped. Reporting 113%
  // as "100%" would quietly hide the part that earns the incentive.
  $pct = $target > 0 ? round($sales / $target * 100) : 0;
  $barPct = min(100, $pct);
@endphp

<div class="grid g3" style="margin-bottom:16px">
  <div class="stat a">
    <div class="k">Jualan saya</div>
    <div class="v">@rmshort($sales)</div>
    <div class="n">{{ $pct }}% daripada @rmshort($target)</div>
    <div class="bar {{ $pct < 70 ? 'warn' : '' }}"><i style="width:{{ $barPct }}%"></i></div>
  </div>
  <div class="stat b"><div class="k">{{ config('kpi.focus_product_label') }}</div><div class="v">{{ $focusQty }}</div><div class="n">unit terjual</div></div>
  <div class="stat c"><div class="k">Kedudukan</div><div class="v">{{ $rank ? '#'.$rank : '—' }}</div><div class="n">berdasarkan jualan</div></div>
</div>

<div class="card">
  <h2>📋 KPI saya</h2>
  @if($assessment)
    @php $t = (float) $assessment->total_score; @endphp
    <div style="display:flex;align-items:baseline;gap:12px;flex-wrap:wrap;margin-bottom:12px">
      <div style="font-size:40px;font-weight:900;letter-spacing:-.04em">{{ number_format($t, 2) }}<span style="font-size:20px;color:var(--ink2)">/100</span></div>
      <span class="tag t-{{ $assessment->status->value }}">{{ $assessment->status->label() }}</span>
    </div>
    <div class="bar"><i style="width:{{ min(100, $t) }}%"></i></div>
    <div class="tw fit" style="margin-top:16px"><table>
      <tbody>
        <tr><td>Pencapaian jualan individu</td><td class="num mono"><b>{{ number_format((float) $assessment->sales_score, 2) }}</b> / 40</td></tr>
        <tr><td>Penilaian prestasi</td><td class="num mono"><b>{{ number_format((float) $assessment->performance_score, 2) }}</b> / 60</td></tr>
        <tr><td><b>Jumlah KPI</b></td><td class="num mono"><b>{{ number_format($t, 2) }}</b> / 100</td></tr>
      </tbody>
    </table></div>
  @elseif($pending)
    <div class="empty">Penilaian bulan ini masih dalam semakan. Markah dipaparkan selepas diluluskan.</div>
  @else
    <div class="empty">Belum ada penilaian untuk tempoh ini.</div>
  @endif
</div>

<div class="card">
  <h2>💰 Ganjaran</h2>
  @if($reward)
    <div class="tw fit"><table>
      <tbody>
        <tr><td>Bonus KPI individu</td><td class="num mono">@rm($reward->individual_bonus_cents)</td></tr>
        <tr><td>Insentif jualan tinggi</td><td class="num mono">@rm($reward->high_sales_incentive_cents)</td></tr>
        <tr><td>Bonus KPI team</td><td class="num mono">@rm($reward->team_bonus_cents)</td></tr>
        <tr><td><b>Jumlah</b></td><td class="num mono"><b style="font-size:17px">@rm($reward->total_cents)</b></td></tr>
      </tbody>
    </table></div>
    <p class="hint" style="margin-top:12px">
      Status: <span class="tag t-{{ $reward->status->value }}">{{ $reward->status->label() }}</span>
      — bonus dibayar selepas pengesahan pengurusan.
    </p>
  @else
    <div class="empty">Ganjaran dikira selepas tempoh dikunci.</div>
  @endif
</div>
@endif
@endsection
