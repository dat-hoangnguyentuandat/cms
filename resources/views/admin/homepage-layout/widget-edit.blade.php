@extends('layouts.admin')
@section('title', 'Sửa widget: ' . $type . ' / ' . $variant)

@section('content')
<div style="max-width:960px">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
      <h2 style="font-size:16px;font-weight:700;margin:0">Sửa widget: <code>{{ $type }} / {{ $variant }}</code></h2>
      <div style="font-size:12px;color:#64748b;margin-top:4px">{{ $file }}</div>
    </div>
    <a href="{{ route('admin.homepage-layout.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success" style="margin-bottom:16px">{{ session('success') }}</div>
  @endif

  <form method="POST" action="{{ route('admin.homepage-layout.widget-update') }}">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">
    <input type="hidden" name="variant" value="{{ $variant }}">
    <div class="form-group">
      <textarea name="code" id="widgetCode" class="form-control" rows="30" style="font-family:monospace;font-size:13px;line-height:1.6;resize:vertical">{{ $code }}</textarea>
    </div>
    <div style="display:flex;gap:8px;margin-top:12px">
      <button type="submit" class="btn btn-primary">Lưu</button>
      <a href="{{ route('admin.homepage-layout.index') }}" class="btn btn-outline">Hủy</a>
    </div>
  </form>
</div>
@endsection
