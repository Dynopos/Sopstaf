@extends('layouts.app')
@section('title', 'Penilaian ' . $assessment->staff->name)
@section('content')

@php
  $salesScore = (float) $assessment->sales_score;
  $target = $config->individualTargetCents;
  $status = $assessment->status;
@endphp

<h1>{{ $assessment->staff->name }}</h1>
<p class="sub">
  {{ $assessment->period->label() }} ·
  <span class="tag t-{{ $status->value }}">{{ $status->label() }}</span>
  @if($assessment->evaluator) · dinilai oleh {{ $assessment->evaluator->name }} @endif
</p>

@if($assessment->returned_reason && $status->isEditable())
  <div class="warnbox"><b>Dipulangkan untuk pembetulan:</b> {{ $assessment->returned_reason }}</div>
@endif

<div class="card">
  <h2>💵 Pencapaian jualan individu — 40%</h2>
  <div class="grid g3">
    <div class="stat"><div class="k">Jualan bersih</div><div class="v">@rmshort($assessment->sales_amount_cents)</div></div>
    <div class="stat"><div class="k">Target</div><div class="v">@rmshort($target)</div>
      <div class="n">{{ $target > 0 ? number_format($assessment->sales_amount_cents / $target * 100, 1) : '—' }}% dicapai</div></div>
    <div class="stat a"><div class="k">Markah jualan</div><div class="v">{{ number_format($salesScore, 2) }}<span style="font-size:16px;color:var(--ink2)">/40</span></div></div>
  </div>
  <p class="hint">Dikira automatik daripada rekod jualan harian. Tidak boleh ditaip — had maksimum 40 walaupun jualan melebihi target.</p>
</div>

<form method="post" action="{{ route('assessments.update', $assessment) }}" id="f">
  @csrf @method('put')

  @foreach($criteria as $key => $items)
    @php $cat = $items->first(); @endphp
    <div class="catbar">
      <h2>{{ $cat->category_label }}</h2>
      <span class="w">{{ rtrim(rtrim(number_format((float) $cat->category_weight, 2), '0'), '.') }}%</span>
      {{-- Rendered server-side so the figure is right even when the live
           recalculation script is not loaded, as on a read-only assessment. --}}
      <span class="sc" data-cat-score="{{ $key }}">{{ number_format($performance->categories[$key]->score ?? 0, 2) }} / {{ number_format((float) $cat->category_weight, 0) }}</span>
    </div>

    @foreach($items as $c)
      @php $score = $scores[$c->id] ?? null; @endphp
      <div class="crit"
           data-cat="{{ $key }}"
           data-weight="{{ (float) $c->category_weight }}"
           data-items="{{ $items->count() }}">
        <h3>{{ $c->label }}</h3>
        <div class="opts">
          @foreach([0 => 'Tidak Memuaskan', 1 => 'Memuaskan', 2 => 'Cemerlang'] as $v => $gradeLabel)
            <label class="opt s{{ $v }}">
              <input type="radio" name="items[{{ $c->id }}][score]" value="{{ $v }}"
                     @checked($score === $v) @disabled(! $canEdit) required>
              <span><b>{{ $v }} · {{ $gradeLabel }}</b>{{ $c->descriptionFor($v) }}</span>
            </label>
          @endforeach
        </div>
        <div class="note {{ $score === 0 ? 'show' : '' }}">
          <label for="n{{ $c->id }}">Catatan <span style="color:var(--c4)">(wajib untuk skor 0)</span></label>
          <textarea id="n{{ $c->id }}" name="items[{{ $c->id }}][note]"
                    placeholder="Nyatakan apa yang berlaku dan apa yang perlu diperbaiki."
                    @disabled(! $canEdit)>{{ $notes[$c->id] ?? '' }}</textarea>
        </div>
      </div>
    @endforeach
  @endforeach

  @if($canEdit)
  <div class="live">
    <div>
      <div class="t">Prestasi</div>
      <div class="n"><span id="perf">{{ number_format($performance->total, 2) }}</span><span style="font-size:14px;color:var(--ink2)">/60</span></div>
    </div>
    <div>
      <div class="t">Jumlah KPI</div>
      <div class="n"><span id="total">{{ number_format($salesScore + $performance->total, 2) }}</span><span style="font-size:14px;color:var(--ink2)">/100</span></div>
    </div>
    <div style="margin-left:auto" class="muted"><span id="done">{{ $performance->itemsScored }}</span> / {{ $criteria->flatten()->count() }} item dinilai</div>
    <div style="display:flex;gap:9px;flex-wrap:wrap">
      <button class="btn o" name="submit" value="0">Simpan Draf</button>
      <button class="btn g" name="submit" value="1">Hantar untuk Kelulusan</button>
    </div>
  </div>
  @endif
</form>

@if(! $canEdit && $status->isFinal())
  <div class="card">
    <h2>Keputusan</h2>
    <div class="tw fit"><table><tbody>
      <tr><td>Markah jualan</td><td class="num mono">{{ number_format($salesScore, 2) }} / 40</td></tr>
      <tr><td>Markah prestasi</td><td class="num mono">{{ number_format((float) $assessment->performance_score, 2) }} / 60</td></tr>
      <tr><td><b>Jumlah KPI</b></td><td class="num mono"><b>{{ number_format((float) $assessment->total_score, 2) }} / 100</b></td></tr>
    </tbody></table></div>
  </div>
@endif

@can('approve', $assessment)
  <div class="card">
    <h2>Kelulusan</h2>
    <form method="post" action="{{ route('assessments.approve', $assessment) }}" style="display:inline">@csrf
      <button class="btn g">Luluskan Penilaian</button>
    </form>
    <form method="post" action="{{ route('assessments.return', $assessment) }}" style="margin-top:14px">@csrf
      <div class="field">
        <label for="reason">Atau pulangkan untuk pembetulan — nyatakan sebab</label>
        <textarea id="reason" name="reason" required placeholder="Apa yang perlu dibetulkan?"></textarea>
      </div>
      <button class="btn r">Pulangkan</button>
    </form>
    <p class="hint">Penilai tidak boleh meluluskan penilaian yang dinilainya sendiri.</p>
  </div>
@elsecan('reopen', $assessment)
  @if($status->isFinal())
  <div class="card">
    <h2>Buka semula</h2>
    <form method="post" action="{{ route('assessments.reopen', $assessment) }}">@csrf
      <div class="field">
        <label for="rr">Sebab buka semula (direkod dalam audit trail)</label>
        <textarea id="rr" name="reason" required></textarea>
      </div>
      <button class="btn r">Buka Semula</button>
    </form>
  </div>
  @endif
@endcan

@if($canEdit)
<script>
(function () {
  var salesScore = {{ $salesScore }};
  var form = document.getElementById('f');

  function recalc() {
    var cats = {}, done = 0;

    form.querySelectorAll('.crit').forEach(function (crit) {
      var key = crit.dataset.cat;
      cats[key] = cats[key] || { raw: 0, max: crit.dataset.items * 2, weight: +crit.dataset.weight };
      var picked = crit.querySelector('input:checked');
      if (picked) {
        cats[key].raw += +picked.value;
        done++;
      }
      // A zero needs a note - show the box the moment it is chosen.
      var note = crit.querySelector('.note');
      if (note) note.classList.toggle('show', picked && picked.value === '0');
    });

    var perf = 0;
    Object.keys(cats).forEach(function (key) {
      var c = cats[key];
      var score = c.max ? (c.raw / c.max) * c.weight : 0;
      perf += score;
      var el = form.querySelector('[data-cat-score="' + key + '"]');
      if (el) el.textContent = score.toFixed(2) + ' / ' + c.weight.toFixed(0);
    });

    document.getElementById('perf').textContent = perf.toFixed(2);
    document.getElementById('total').textContent = (salesScore + perf).toFixed(2);
    document.getElementById('done').textContent = done;
  }

  form.addEventListener('change', recalc);
  recalc();
})();
</script>
@endif
@endsection
