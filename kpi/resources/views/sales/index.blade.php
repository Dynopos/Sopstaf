@extends('layouts.app')
@section('title', 'Jualan Harian')
@section('content')

<h1>Jualan Harian</h1>
<p class="sub">Rekod harian ialah sumber tunggal — angka mingguan dan bulanan dijumlahkan daripadanya.</p>

@if($locked)
  <div class="warnbox">
    Tempoh untuk {{ $date->translatedFormat('F Y') }} sudah <b>dikunci</b>. Guna rekod pelarasan di bawah
    untuk pemulangan atau pembetulan — bulan yang dikunci kekal seperti diluluskan.
  </div>
@endif

<form method="get" class="card" style="padding:14px">
  <div class="row" style="align-items:end">
    <div class="field" style="margin:0">
      <label for="date">Tarikh</label>
      <input id="date" name="date" type="date" value="{{ $date->toDateString() }}"
             max="{{ now()->toDateString() }}" onchange="this.form.submit()">
    </div>
  </div>
</form>

<form method="post" action="{{ route('sales.store') }}">@csrf
  <input type="hidden" name="date" value="{{ $date->toDateString() }}">
  <div class="card">
    <h2>📝 Kemasukan {{ $date->translatedFormat('l, j F Y') }}</h2>
    @if($staff->isEmpty())
      <div class="empty">Belum ada staf aktif.</div>
    @else
    <div class="tw"><table>
      <thead><tr>
        <th>Staf</th><th style="width:150px">Jualan (RM)</th>
        <th style="width:110px">{{ config('kpi.focus_product_label') }}</th>
        <th class="num">Bulan ini</th>
      </tr></thead>
      <tbody>
      @foreach($staff as $s)
        @php $row = $existing->get($s->id); @endphp
        <tr>
          <td><b>{{ $s->name }}</b><div class="muted">{{ $s->employee_code }}</div></td>
          <td><input type="number" step="0.01" min="0" name="rows[{{ $s->id }}][amount]"
                     value="{{ $row ? number_format($row->amount_cents / 100, 2, '.', '') : '' }}"
                     placeholder="0.00" @disabled($locked)></td>
          <td><input type="number" min="0" name="rows[{{ $s->id }}][focus_qty]"
                     value="{{ $row->focus_qty ?? '' }}" placeholder="0" @disabled($locked)></td>
          <td class="num mono">@rm($monthTotals[$s->id] ?? 0)</td>
        </tr>
      @endforeach
      </tbody>
    </table></div>
    @unless($locked)
      <div class="btns"><button class="btn">Simpan Jualan</button></div>
      <p class="hint">Satu rekod per staf per hari. Menyimpan semula akan mengemas kini rekod sedia ada, dan setiap perubahan direkod.</p>
    @endunless
    @endif
  </div>
</form>

<div class="card">
  <h2>↩️ Rekod pelarasan</h2>
  <p class="hint" style="margin:0 0 14px">
    Untuk pemulangan atau pembatalan. Rekod asal tidak diubah — kalau tidak, hakikat bahawa
    pemulangan pernah berlaku akan hilang.
  </p>
  <form method="post" action="{{ route('sales.adjust') }}">@csrf
    <div class="row">
      <div class="field">
        <label for="as">Staf</label>
        <select id="as" name="staff_id" required>
          @foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
      </div>
      <div class="field">
        <label for="ad">Tarikh pelarasan</label>
        <input id="ad" name="adjusted_on" type="date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
      </div>
      <div class="field">
        <label for="aa">Jumlah (RM, boleh negatif)</label>
        <input id="aa" name="amount" type="number" step="0.01" placeholder="-1200.00" required>
      </div>
      <div class="field">
        <label for="aq">{{ config('kpi.focus_product_label') }}</label>
        <input id="aq" name="focus_qty" type="number" placeholder="-1">
      </div>
    </div>
    <div class="field">
      <label for="ar">Sebab (wajib)</label>
      <input id="ar" name="reason" type="text" placeholder="Contoh: pemulangan barang, resit dibatalkan" required>
    </div>
    <button class="btn o">Simpan Pelarasan</button>
  </form>

  @if($recentAdjustments->isNotEmpty())
    <div class="tw" style="margin-top:18px"><table>
      <thead><tr><th>Tarikh</th><th>Staf</th><th class="num">Jumlah</th><th>Sebab</th></tr></thead>
      <tbody>
      @foreach($recentAdjustments as $adj)
        <tr>
          <td class="mono">{{ $adj->adjusted_on->format('d/m/Y') }}</td>
          <td>{{ $adj->staff->name }}</td>
          <td class="num mono">@rm($adj->amount_cents)</td>
          <td class="muted">{{ $adj->reason }}</td>
        </tr>
      @endforeach
      </tbody>
    </table></div>
  @endif
</div>
@endsection
