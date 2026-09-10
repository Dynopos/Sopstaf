@extends('layouts.app')
@section('title', 'Papan Pemuka')
@section('content')

<h1>Papan Pemuka Management</h1>
<p class="sub">
  @if($period)
    Tempoh semasa: <b>{{ $period->label() }}</b> — <span class="tag t-{{ $period->status->value }}">{{ $period->status->label() }}</span>
  @else
    Belum ada tempoh dibuka.
  @endif
</p>

<div class="grid g3" style="margin-bottom:16px">
  <div class="stat a"><div class="k">Jualan hari ini</div><div class="v">@rmshort($todaySales)</div></div>
  <div class="stat b"><div class="k">Jualan minggu ini</div><div class="v">@rmshort($weekSales)</div></div>
  <div class="stat c">
    <div class="k">Jualan bulan ini</div>
    <div class="v">@rmshort($monthSales)</div>
    @php
      $pct = $teamTarget > 0 ? round($monthSales / $teamTarget * 100) : 0;
      $barPct = min(100, $pct);
    @endphp
    <div class="n">{{ $pct }}% daripada target @rmshort($teamTarget)</div>
    <div class="bar {{ $pct < 70 ? 'warn' : '' }}"><i style="width:{{ $barPct }}%"></i></div>
  </div>
  <div class="stat d"><div class="k">Staf aktif</div><div class="v">{{ $staffCount }}</div></div>
</div>

@if($period && $outstanding->isNotEmpty())
  <div class="warnbox">
    <b>{{ $outstanding->count() }} penilaian belum diluluskan</b> — tempoh {{ $period->label() }} tidak boleh dikunci sehingga selesai:
    {{ $outstanding->pluck('staff.name')->join(', ') }}.
  </div>
@endif

<div class="card">
  <h2>🏆 Leaderboard jualan — {{ $period?->label() ?? 'bulan ini' }}</h2>
  @if($leaderboard->isEmpty())
    <div class="empty">Belum ada rekod jualan untuk tempoh ini.</div>
  @else
  <div class="tw"><table>
    <thead><tr>
      <th style="width:44px">#</th><th>Staf</th>
      <th class="num">Jualan</th><th class="num">% Target</th><th class="num">{{ config('kpi.focus_product_label') }}</th>
    </tr></thead>
    <tbody>
    @foreach($leaderboard as $row)
      @php $target = $config->individualTargetCents; @endphp
      <tr>
        <td><span class="rank r{{ $row['rank'] }}">{{ $row['rank'] }}</span></td>
        <td><b>{{ $row['staff']->name }}</b><div class="muted">{{ $row['staff']->employee_code }}</div></td>
        <td class="num mono">@rm($row['sales_cents'])</td>
        <td class="num mono">{{ $target > 0 ? number_format($row['sales_cents'] / $target * 100, 1) : '—' }}%</td>
        <td class="num mono">{{ $row['focus_qty'] }}</td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
  @endif
</div>

<div class="card">
  <h2>📅 Tempoh bulanan</h2>
  @if(auth()->user()->isAdmin())
    <form method="post" action="{{ route('periods.open') }}" style="margin-bottom:14px">@csrf
      <div class="row" style="align-items:end">
        <div class="field" style="margin:0">
          <label for="month">Bulan</label>
          <select id="month" name="month">
            @foreach(range(1,12) as $m)
              <option value="{{ $m }}" @selected($m == now()->subMonthNoOverflow()->month)>
                {{ \Illuminate\Support\Carbon::create(null, $m, 1)->translatedFormat('F') }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="field" style="margin:0">
          <label for="year">Tahun</label>
          <input id="year" name="year" type="number" value="{{ now()->subMonthNoOverflow()->year }}">
        </div>
        <div><button class="btn">Buka Tempoh</button></div>
      </div>
      <p class="hint">Membuka tempoh membekukan target dan kadar bonus semasa untuk bulan itu.</p>
    </form>

    @if($period && $period->status->value !== 'locked')
      <form method="post" action="{{ route('periods.lock', $period) }}"
            onsubmit="return confirm('Kunci {{ $period->label() }}? Rekod tidak boleh diubah selepas ini tanpa buka semula.')">@csrf
        <button class="btn g" @disabled($outstanding->isNotEmpty())>Kunci {{ $period->label() }} &amp; Kira Ganjaran</button>
      </form>
      @if($outstanding->isNotEmpty())
        <p class="hint">Selesaikan semua penilaian dahulu sebelum tempoh boleh dikunci.</p>
      @endif
    @elseif($period)
      <p class="muted">{{ $period->label() }} dikunci pada {{ $period->locked_at?->format('d/m/Y H:i') }}.</p>
    @endif
  @endif
</div>
@endsection
