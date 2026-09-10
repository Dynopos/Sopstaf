@extends('layouts.app')
@section('title', 'Leaderboard')
@section('content')

<h1>Leaderboard</h1>
<p class="sub">{{ $label }}</p>

<div class="btns" style="margin:0 0 16px">
  @foreach(['day' => 'Hari', 'week' => 'Minggu', 'month' => 'Bulan', 'year' => 'Tahun'] as $key => $text)
    <a class="btn {{ $range === $key ? '' : 'o' }}" href="{{ route('leaderboard', ['range' => $key]) }}">{{ $text }}</a>
  @endforeach
</div>

<div class="card">
  <h2>💵 Ranking jualan</h2>
  @if($rows->isEmpty())
    <div class="empty">Belum ada rekod jualan untuk tempoh ini.</div>
  @else
  <div class="tw"><table>
    <thead><tr><th style="width:44px">#</th><th>Staf</th><th class="num">Jualan</th><th class="num">{{ config('kpi.focus_product_label') }}</th></tr></thead>
    <tbody>
    @foreach($rows as $row)
      <tr>
        <td><span class="rank r{{ $row['rank'] }}">{{ $row['rank'] }}</span></td>
        <td><b>{{ $row['staff']->name }}</b></td>
        <td class="num mono">@rm($row['sales_cents'])</td>
        <td class="num mono">{{ $row['focus_qty'] }}</td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
  @endif
</div>

<div class="card">
  <h2>📦 Ranking {{ config('kpi.focus_product_label') }}</h2>
  @php $byQty = $rows->sortByDesc('focus_qty')->values(); @endphp
  @if($byQty->isEmpty())
    <div class="empty">Belum ada rekod.</div>
  @else
  <div class="tw"><table>
    <thead><tr><th style="width:44px">#</th><th>Staf</th><th class="num">Unit</th></tr></thead>
    <tbody>
    @foreach($byQty as $i => $row)
      <tr>
        <td><span class="rank r{{ $i + 1 }}">{{ $i + 1 }}</span></td>
        <td><b>{{ $row['staff']->name }}</b></td>
        <td class="num mono">{{ $row['focus_qty'] }}</td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
  @endif
</div>

@if(auth()->user()->canEvaluate())
<div class="card">
  <h2>📊 Ranking KPI</h2>
  @if($kpiRows->isEmpty())
    <div class="empty">Ranking KPI hanya memaparkan penilaian yang sudah diluluskan.</div>
  @else
  <div class="tw"><table>
    <thead><tr><th style="width:44px">#</th><th>Staf</th><th class="num">Jualan /40</th><th class="num">Prestasi /60</th><th class="num">KPI</th></tr></thead>
    <tbody>
    @foreach($kpiRows as $i => $a)
      <tr>
        <td><span class="rank r{{ $i + 1 }}">{{ $i + 1 }}</span></td>
        <td><b>{{ $a->staff->name }}</b></td>
        <td class="num mono">{{ number_format((float) $a->sales_score, 2) }}</td>
        <td class="num mono">{{ number_format((float) $a->performance_score, 2) }}</td>
        <td class="num mono"><b>{{ number_format((float) $a->total_score, 2) }}</b></td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
  @endif
</div>
@endif
@endsection
