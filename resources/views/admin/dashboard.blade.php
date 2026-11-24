@extends('layouts.admin')
@section('title','Dashboard')

@section('content')
<div class="stats-grid">
  <div class="stat-card">
    <div class="icon bg-orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg></div>
    <div><div class="label">Sản phẩm</div><div class="value">{{ $stats['products'] }}</div></div>
  </div>
  <div class="stat-card">
    <div class="icon bg-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg></div>
    <div><div class="label">Danh mục</div><div class="value">{{ $stats['categories'] }}</div></div>
  </div>
  <div class="stat-card">
    <div class="icon bg-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
    <div><div class="label">Bài viết</div><div class="value">{{ $stats['news'] }}</div></div>
  </div>
  <div class="stat-card">
    <div class="icon bg-purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg></div>
    <div><div class="label">Liên hệ</div><div class="value">{{ $stats['contacts'] }} @if($stats['unread'])<small style="font-size:14px;color:#f97316">({{ $stats['unread'] }} mới)</small>@endif</div></div>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3>Liên hệ gần đây</h3><a href="{{ route('admin.contacts.index') }}" class="btn btn-outline btn-sm">Xem tất cả</a></div>
  <table class="table">
    <thead><tr><th>Tên</th><th>Điện thoại</th><th>Nhu cầu</th><th>Thời gian</th><th></th></tr></thead>
    <tbody>
      @foreach($recentContacts as $c)
      <tr>
        <td>{{ $c->name }} @if(!$c->is_read)<span class="badge badge-warning">Mới</span>@endif</td>
        <td>{{ $c->phone }}</td>
        <td>{{ $c->subject }}</td>
        <td>{{ $c->created_at->format('d/m/Y H:i') }}</td>
        <td><a href="{{ route('admin.contacts.show', $c) }}" class="btn btn-outline btn-sm">Xem</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
