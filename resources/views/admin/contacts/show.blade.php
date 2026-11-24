@extends('layouts.admin')
@section('title','Chi tiết liên hệ')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Chi tiết liên hệ #{{ $contact->id }}</h3>
    <a href="{{ route('admin.contacts.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <table class="data-table">
    <tr><th width="150">Họ tên</th><td>{{ $contact->name }}</td></tr>
    <tr><th>Điện thoại</th><td><a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a></td></tr>
    <tr><th>Email</th><td>@if($contact->email)<a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>@else —@endif</td></tr>
    <tr><th>Chủ đề</th><td>{{ $contact->subject }}</td></tr>
    <tr><th>Nội dung</th><td>{!! nl2br(e($contact->message)) !!}</td></tr>
    <tr><th>Ngày gửi</th><td>{{ $contact->created_at->format('d/m/Y H:i') }}</td></tr>
  </table>

  <div style="margin-top:16px">
    <form method="post" action="{{ route('admin.contacts.destroy', $contact) }}" style="display:inline">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-danger" onclick="return confirm('Xóa liên hệ này?')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="margin-right:4px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        Xóa
      </button>
    </form>
  </div>
</div>
@endsection
