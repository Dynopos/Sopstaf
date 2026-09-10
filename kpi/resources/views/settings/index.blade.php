@extends('layouts.app')
@section('title', 'Tetapan')
@section('content')

<h1>Tetapan KPI</h1>
<p class="sub">Perubahan di sini hanya memberi kesan kepada tempoh yang <b>belum dibuka</b>.</p>

<div class="warnbox">
  Tempoh yang sudah dibuka membawa salinan konfigurasinya sendiri. Menaikkan target hari ini
  tidak akan menulis semula KPI bulan lepas — bonus bulan itu sudah dikira atas angka lama.
</div>

<form method="post" action="{{ route('settings.update') }}">@csrf @method('put')

  <div class="card">
    <h2>🎯 Target</h2>
    <div class="row">
      <div class="field">
        <label for="it">Target individu (RM / bulan)</label>
        <input id="it" name="individual_target" type="number" step="0.01" required
               value="{{ number_format($config->individualTargetCents / 100, 2, '.', '') }}">
      </div>
      <div class="field">
        <label for="tt">Target team (RM / bulan)</label>
        <input id="tt" name="team_target" type="number" step="0.01" required
               value="{{ number_format($config->teamTargetCents / 100, 2, '.', '') }}">
      </div>
    </div>
    <p class="hint">
      Target team ialah nilai tetap, bukan target individu didarab bilangan staf — kalau tidak,
      seorang staf berhenti akan menurunkan sasaran team di tengah bulan.
    </p>
  </div>

  <div class="card">
    <h2>🏅 Gred bonus KPI</h2>
    <p class="hint" style="margin:0 0 14px">
      Sempadan dipadankan sebagai "lebih besar atau sama dengan". Jadual asal ditulis 80–89 dan
      90–100, yang meninggalkan 89.5 tanpa gred — dan markah memang boleh mendarat di situ.
    </p>
    @foreach($config->bonusBands as $i => $band)
      <div class="row" style="margin-bottom:10px">
        <div class="field" style="margin:0">
          <label>KPI minimum (%)</label>
          <input name="bonus[{{ $i }}][min_score]" type="number" step="0.01" required value="{{ $band['min_score'] }}">
        </div>
        <div class="field" style="margin:0">
          <label>Bonus (RM)</label>
          <input name="bonus[{{ $i }}][amount]" type="number" step="0.01" required
                 value="{{ number_format($band['amount_cents'] / 100, 2, '.', '') }}">
        </div>
      </div>
    @endforeach
    <p class="hint">Gred yang sama digunakan untuk bonus team.</p>
  </div>

  <div class="card">
    <h2>🚀 Gred insentif jualan tinggi</h2>
    <p class="hint" style="margin:0 0 14px">
      Berasingan sepenuhnya daripada markah KPI. Inilah yang memberi ganjaran kepada jualan
      melebihi target, memandangkan komponen KPI jualan berhenti pada 40.
    </p>
    @foreach($config->incentiveBands as $i => $band)
      <div class="row" style="margin-bottom:10px">
        <div class="field" style="margin:0">
          <label>Jualan minimum (RM)</label>
          <input name="incentive[{{ $i }}][min_sales]" type="number" step="0.01" required
                 value="{{ number_format($band['min_sales_cents'] / 100, 2, '.', '') }}">
        </div>
        <div class="field" style="margin:0">
          <label>Insentif (RM)</label>
          <input name="incentive[{{ $i }}][amount]" type="number" step="0.01" required
                 value="{{ number_format($band['amount_cents'] / 100, 2, '.', '') }}">
        </div>
      </div>
    @endforeach
  </div>

  <div class="card">
    <h2>⚙️ Peraturan</h2>
    <div class="field">
      <label style="display:flex;gap:9px;align-items:flex-start;font-weight:600;text-transform:none;font-size:14px">
        <input type="checkbox" name="prorate_target" value="1" style="width:auto;margin-top:3px" @checked($config->prorateTarget)>
        <span>Pro-rata target untuk staf yang tidak bekerja sebulan penuh
          <span class="hint" style="display:block">Target penuh untuk separuh bulan menjadikan markah jualan mustahil dicapai.</span></span>
      </label>
    </div>
    <div class="field">
      <label style="display:flex;gap:9px;align-items:flex-start;font-weight:600;text-transform:none;font-size:14px">
        <input type="checkbox" name="require_note_on_zero" value="1" style="width:auto;margin-top:3px" @checked($config->requireNoteOnZero)>
        <span>Wajibkan catatan untuk setiap skor 0
          <span class="hint" style="display:block">Staf tahu sebabnya, dan syarikat ada rekod bertulis jika bonus dipertikaikan.</span></span>
      </label>
    </div>
    <div class="field">
      <label for="md">Hari bekerja minimum untuk layak bonus team</label>
      <input id="md" name="team_bonus_min_working_days" type="number" min="0" max="31" required
             value="{{ $config->teamBonusMinWorkingDays }}">
      <p class="hint">Tanpa ambang, staf yang menyertai pada minggu terakhir menerima jumlah yang sama.</p>
    </div>
  </div>

  <button class="btn">Simpan Tetapan</button>
</form>
@endsection
