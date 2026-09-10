@extends('layouts.app')
@section('title', 'Penilaian KPI')
@section('content')

<h1>Penilaian KPI</h1>
<p class="sub">
  @if($period)
    {{ $period->label() }} — <span class="tag t-{{ $period->status->value }}">{{ $period->status->label() }}</span>
  @else
    Belum ada tempoh dibuka. Buka tempoh dari papan pemuka.
  @endif
</p>

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
  <h2>👤 Penilaian individu</h2>
  @if($assessments->isEmpty())
    <div class="empty">Tiada penilaian untuk tempoh ini.</div>
  @else
  <div class="tw"><table>
    <thead><tr>
      <th>Staf</th><th class="num">Jualan /40</th><th class="num">Prestasi /60</th>
      <th class="num">Jumlah</th><th>Status</th><th></th>
    </tr></thead>
    <tbody>
    @foreach($assessments as $a)
      <tr>
        <td><b>{{ $a->staff->name }}</b><div class="muted">{{ $a->staff->employee_code }}</div></td>
        <td class="num mono">{{ number_format((float) $a->sales_score, 2) }}</td>
        <td class="num mono">{{ number_format((float) $a->performance_score, 2) }}</td>
        <td class="num mono"><b>{{ number_format((float) $a->total_score, 2) }}</b></td>
        <td><span class="tag t-{{ $a->status->value }}">{{ $a->status->label() }}</span></td>
        <td style="text-align:right">
          <a class="btn o" style="padding:7px 13px;min-height:0;font-size:13px"
             href="{{ route('assessments.edit', $a) }}">
            {{ $a->status->isEditable() ? 'Nilai' : 'Lihat' }}
          </a>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
  @endif
</div>

@if($team)
<div class="card">
  <h2>👥 KPI Team</h2>
  <div class="grid g3">
    <div class="stat"><div class="k">Jualan team</div><div class="v">@rmshort($team->sales_amount_cents)</div></div>
    <div class="stat a"><div class="k">Markah jualan</div><div class="v">{{ number_format((float) $team->sales_score, 2) }}<span style="font-size:16px;color:var(--ink2)">/40</span></div></div>
    <div class="stat"><div class="k">Status</div><div class="v" style="font-size:17px;padding-top:6px"><span class="tag t-{{ $team->status->value }}">{{ $team->status->label() }}</span></div></div>
  </div>
  <p class="hint">Komponen prestasi team dinilai untuk team secara keseluruhan, bukan purata markah individu.</p>
</div>
@endif
@endsection
