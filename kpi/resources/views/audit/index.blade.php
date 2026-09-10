@extends('layouts.app')
@section('title', 'Audit Trail')
@section('content')

<h1>Audit Trail</h1>
<p class="sub">Rekod sisip sahaja — tiada laluan untuk mengubah atau memadam, termasuk untuk Admin.</p>

<form method="get" class="card" style="padding:14px">
  <div class="row" style="align-items:end">
    <div class="field" style="margin:0">
      <label for="a">Jenis tindakan</label>
      <select id="a" name="action" onchange="this.form.submit()">
        <option value="">Semua</option>
        @foreach($actions as $action)
          <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
        @endforeach
      </select>
    </div>
  </div>
</form>

<div class="card">
  @if($logs->isEmpty())
    <div class="empty">Tiada rekod audit.</div>
  @else
  <div class="tw"><table>
    <thead><tr><th>Masa</th><th>Pengguna</th><th>Tindakan</th><th>Rekod</th><th>Sebab / perubahan</th></tr></thead>
    <tbody>
    @foreach($logs as $log)
      <tr>
        <td class="mono muted" style="white-space:nowrap">{{ $log->created_at?->format('d/m/y H:i') }}</td>
        <td>{{ $log->actor->name ?? 'Sistem' }}</td>
        <td><span class="tag t-draft">{{ $log->action }}</span></td>
        <td class="muted">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
        <td class="muted" style="max-width:280px">
          {{ $log->reason ?? '' }}
          @if($log->changes)
            <div style="font-size:12px">{{ collect($log->changes)->keys()->join(', ') }}</div>
          @endif
        </td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
  <div style="margin-top:16px">{{ $logs->links() }}</div>
  @endif
</div>
@endsection
