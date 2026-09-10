@extends('layouts.app')
@section('title', 'Log Masuk')
@section('content')
<div style="max-width:390px;margin:6vh auto 0">
  <div style="text-align:center;margin-bottom:24px">
    <div style="width:54px;height:54px;border-radius:16px;margin:0 auto 14px;
      background:linear-gradient(135deg,var(--c3),var(--c2));display:grid;place-items:center;font-size:26px">📊</div>
    <h1>{{ $business->name ?? config('kpi.business.name') }}</h1>
    <p class="sub" style="margin:2px 0 0">Sistem KPI, Prestasi &amp; Bonus Staf</p>
  </div>

  <div class="card">
    <form method="post" action="{{ route('login') }}">@csrf
      <div class="field">
        <label for="login_code">Kod log masuk</label>
        <input id="login_code" name="login_code" type="text" autocapitalize="none" autocomplete="username"
               value="{{ old('login_code') }}" autofocus required>
      </div>
      <div class="field">
        <label for="pin">PIN</label>
        <input id="pin" name="pin" type="password" inputmode="numeric" autocomplete="current-password" required>
      </div>
      <button class="btn" style="width:100%">Log Masuk</button>
    </form>
  </div>

  <p class="muted" style="text-align:center">Staf hanya nampak rekod sendiri.</p>
</div>
@endsection
