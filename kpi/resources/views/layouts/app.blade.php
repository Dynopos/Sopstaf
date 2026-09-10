<!doctype html>
<html lang="ms">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>@yield('title', 'KPI Staf') — {{ $business->name ?? config('kpi.business.name') }}</title>
<style>
  :root{
    --ink:#16233c; --ink2:#5c6b85; --paper:#ffffff; --line:#e7edf6; --bg:#f7f9fc;
    --c1:#f59e0b; --c1b:#fef3c7;
    --c2:#0ea5e9; --c2b:#e0f2fe;
    --c3:#8b5cf6; --c3b:#ede9fe;
    --c4:#f43f5e; --c4b:#ffe4e6;
    --c5:#10b981; --c5b:#d1fae5;
    --c6:#f97316; --c6b:#ffedd5;
  }
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
    background:var(--bg);color:var(--ink);line-height:1.55;-webkit-font-smoothing:antialiased}
  a{color:inherit;text-decoration:none}
  :focus-visible{outline:3px solid var(--c3);outline-offset:2px;border-radius:6px}

  /* ---- chrome ---- */
  .top{background:#fff;border-bottom:1px solid var(--line);position:sticky;top:0;z-index:50}
  .top .in{max-width:1080px;margin:0 auto;padding:12px 18px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}
  .brand{font-weight:900;letter-spacing:-.02em;font-size:17px;display:flex;align-items:center;gap:9px}
  .brand .dot{width:26px;height:26px;border-radius:8px;background:linear-gradient(135deg,var(--c3),var(--c2));
    display:grid;place-items:center;color:#fff;font-size:13px}
  .who{margin-left:auto;display:flex;align-items:center;gap:10px;font-size:13px;color:var(--ink2)}
  .pill{background:var(--c3b);color:#5b21b6;padding:3px 10px;border-radius:99px;font-weight:800;font-size:11.5px;
    text-transform:uppercase;letter-spacing:.04em}
  .nav{background:#fff;border-bottom:1px solid var(--line);overflow-x:auto;-webkit-overflow-scrolling:touch}
  .nav .in{max-width:1080px;margin:0 auto;padding:0 18px;display:flex;gap:4px;white-space:nowrap}
  .nav a{padding:12px 13px;font-size:14px;font-weight:700;color:var(--ink2);border-bottom:3px solid transparent}
  .nav a:hover{color:var(--ink)}
  .nav a.on{color:var(--c3);border-bottom-color:var(--c3)}

  .wrap{max-width:1080px;margin:0 auto;padding:22px 18px 60px}
  h1{font-size:23px;letter-spacing:-.02em;margin-bottom:3px}
  h2{font-size:16px;margin-bottom:12px}
  .sub{color:var(--ink2);font-size:14px;margin-bottom:20px}

  /* ---- cards ---- */
  .card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:18px;margin-bottom:16px}
  .card > h2{margin-bottom:14px;display:flex;align-items:center;gap:8px}
  .grid{display:grid;gap:14px}
  .g2{grid-template-columns:repeat(auto-fit,minmax(230px,1fr))}
  .g3{grid-template-columns:repeat(auto-fit,minmax(165px,1fr))}

  .stat{background:#fff;border:1px solid var(--line);border-radius:14px;padding:15px 16px}
  .stat .k{font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink2);font-weight:800}
  .stat .v{font-size:25px;font-weight:900;letter-spacing:-.03em;margin-top:5px}
  .stat .n{font-size:12.5px;color:var(--ink2);margin-top:3px}
  .stat.a{background:var(--c1b);border-color:#fde68a}
  .stat.b{background:var(--c2b);border-color:#bae6fd}
  .stat.c{background:var(--c5b);border-color:#a7f3d0}
  .stat.d{background:var(--c3b);border-color:#ddd6fe}

  /* ---- progress ---- */
  .bar{height:9px;background:var(--line);border-radius:99px;overflow:hidden;margin-top:9px}
  .bar i{display:block;height:100%;background:linear-gradient(90deg,var(--c2),var(--c5));border-radius:99px}
  .bar.warn i{background:linear-gradient(90deg,var(--c1),var(--c6))}

  /* ---- tables ---- */
  .tw{overflow-x:auto;margin:0 -18px;padding:0 18px}
  table{width:100%;border-collapse:collapse;font-size:14px;min-width:520px}
  /* Two-column breakdowns fit a phone as they are - forcing 520px would push
     the figures off-screen, which is exactly the number the staff came to see. */
  .tw.fit table{min-width:0}
  th{text-align:left;font-size:11.5px;text-transform:uppercase;letter-spacing:.04em;color:var(--ink2);
    padding:9px 10px;border-bottom:2px solid var(--line);white-space:nowrap}
  td{padding:11px 10px;border-bottom:1px solid var(--line);vertical-align:middle}
  tr:last-child td{border-bottom:0}
  td.num,th.num{text-align:right;font-variant-numeric:tabular-nums}
  .rank{width:26px;height:26px;border-radius:8px;background:var(--line);display:grid;place-items:center;
    font-weight:900;font-size:12.5px}
  .rank.r1{background:var(--c1);color:#fff}
  .rank.r2{background:#cbd5e1;color:#fff}
  .rank.r3{background:var(--c6);color:#fff}

  /* ---- badges ---- */
  .tag{display:inline-block;padding:3px 9px;border-radius:99px;font-size:11.5px;font-weight:800;white-space:nowrap}
  .t-draft{background:var(--line);color:var(--ink2)}
  .t-submitted{background:var(--c1b);color:#92400e}
  .t-approved{background:var(--c5b);color:#065f46}
  .t-locked{background:var(--c2b);color:#075985}
  .t-reopened{background:var(--c4b);color:#9f1239}
  .t-calculated{background:var(--line);color:var(--ink2)}
  .t-verified{background:var(--c2b);color:#075985}
  .t-paid{background:var(--c5b);color:#065f46}
  .t-withheld{background:var(--c4b);color:#9f1239}

  /* ---- forms ---- */
  label{display:block;font-size:12.5px;font-weight:800;margin-bottom:5px;color:var(--ink2)}
  input[type=text],input[type=number],input[type=date],input[type=password],select,textarea{
    width:100%;padding:11px 12px;border:1.5px solid var(--line);border-radius:10px;font:inherit;font-size:15px;
    background:#fff;color:var(--ink)}
  input:focus,select:focus,textarea:focus{border-color:var(--c3);outline:none}
  textarea{min-height:64px;resize:vertical}
  .field{margin-bottom:14px}
  .row{display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(150px,1fr))}

  .btn{display:inline-block;padding:11px 18px;border-radius:10px;border:0;font:inherit;font-weight:800;font-size:14.5px;
    cursor:pointer;background:var(--c3);color:#fff;min-height:44px}
  .btn:hover{filter:brightness(1.07)}
  .btn.g{background:var(--c5)}
  .btn.r{background:var(--c4)}
  .btn.o{background:#fff;color:var(--ink);border:1.5px solid var(--line)}
  .btn:disabled{opacity:.45;cursor:not-allowed}
  .btns{display:flex;gap:9px;flex-wrap:wrap;margin-top:16px}

  /* ---- scoring ---- */
  .crit{border:1px solid var(--line);border-radius:13px;padding:14px;margin-bottom:11px;background:#fff}
  .crit h3{font-size:14.5px;margin-bottom:10px}
  .opts{display:grid;gap:8px;grid-template-columns:repeat(3,1fr)}
  .opt{position:relative}
  .opt input{position:absolute;opacity:0;width:100%;height:100%;cursor:pointer;margin:0}
  .opt span{display:block;padding:9px 8px;border:1.5px solid var(--line);border-radius:10px;text-align:left;
    font-size:11.5px;line-height:1.35;cursor:pointer;min-height:74px;color:var(--ink2)}
  .opt b{display:block;font-size:12.5px;margin-bottom:3px;color:var(--ink)}
  .opt input:checked + span{border-color:var(--c3);background:var(--c3b);color:var(--ink)}
  .opt input:focus-visible + span{outline:3px solid var(--c3);outline-offset:2px}
  .opt.s0 input:checked + span{border-color:var(--c4);background:var(--c4b)}
  .opt.s2 input:checked + span{border-color:var(--c5);background:var(--c5b)}
  .note{margin-top:10px;display:none}
  .note.show{display:block}
  .catbar{display:flex;align-items:baseline;gap:9px;margin:22px 0 11px;padding-bottom:7px;border-bottom:2px solid var(--line)}
  .catbar h2{margin:0;font-size:15px}
  .catbar .w{font-size:12px;color:var(--ink2);font-weight:800}
  .catbar .sc{margin-left:auto;font-weight:900;font-variant-numeric:tabular-nums}

  /* ---- sticky score summary ---- */
  .live{position:sticky;bottom:0;background:#fff;border-top:1.5px solid var(--line);padding:12px 18px;
    margin:22px -18px -60px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;z-index:40}
  .live .t{font-size:12px;color:var(--ink2);font-weight:800;text-transform:uppercase;letter-spacing:.04em}
  .live .n{font-size:24px;font-weight:900;letter-spacing:-.03em}

  /* ---- misc ---- */
  .flash{padding:12px 15px;border-radius:11px;margin-bottom:16px;font-size:14px;font-weight:600}
  .flash.ok{background:var(--c5b);color:#065f46}
  .flash.err{background:var(--c4b);color:#9f1239}
  .empty{text-align:center;padding:34px 18px;color:var(--ink2);font-size:14px}
  .muted{color:var(--ink2);font-size:13px}
  .mono{font-variant-numeric:tabular-nums}
  .hint{font-size:12.5px;color:var(--ink2);margin-top:5px}
  .warnbox{background:var(--c1b);border:1px solid #fde68a;border-radius:11px;padding:12px 14px;font-size:13.5px;
    margin-bottom:16px;color:#92400e}
  @media (max-width:560px){
    .opts{grid-template-columns:1fr}
    .opt span{min-height:0}
    h1{font-size:20px}
  }
</style>
</head>
<body>
@auth
<header class="top">
  <div class="in">
    <div class="brand"><span class="dot">📊</span> {{ $business->name ?? config('kpi.business.name') }}</div>
    <div class="who">
      <span>{{ auth()->user()->name }}</span>
      <span class="pill">{{ auth()->user()->role->label() }}</span>
      <form method="post" action="{{ route('logout') }}">@csrf
        <button class="btn o" style="padding:7px 12px;min-height:0;font-size:13px">Keluar</button>
      </form>
    </div>
  </div>
</header>
<nav class="nav"><div class="in">
  <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'on' : '' }}">Papan Pemuka</a>
  <a href="{{ route('leaderboard') }}" class="{{ request()->routeIs('leaderboard') ? 'on' : '' }}">Leaderboard</a>
  @can('create', App\Models\DailySale::class)
    <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'on' : '' }}">Jualan</a>
  @endcan
  @can('viewAny', App\Models\KpiAssessment::class)
    <a href="{{ route('assessments.index') }}" class="{{ request()->routeIs('assessments.*') ? 'on' : '' }}">Penilaian KPI</a>
  @endcan
  <a href="{{ route('rewards.index') }}" class="{{ request()->routeIs('rewards.*') ? 'on' : '' }}">Ganjaran</a>
  @if(auth()->user()->isAdmin())
    <a href="{{ route('audit.index') }}" class="{{ request()->routeIs('audit.*') ? 'on' : '' }}">Audit</a>
    <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'on' : '' }}">Tetapan</a>
  @endif
</div></nav>
@endauth

<main class="wrap">
  @if(session('ok'))<div class="flash ok">{{ session('ok') }}</div>@endif
  @if(session('err'))<div class="flash err">{{ session('err') }}</div>@endif
  @if($errors->any())<div class="flash err">{{ $errors->first() }}</div>@endif
  @yield('content')
</main>
</body>
</html>
