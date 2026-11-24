@extends('layouts.admin')
@section('title','Cấu hình plugin')

@section('content')
@php $fields = $schema['fields'] ?? $schema; @endphp
<div class="wc-layout">
  @include('admin.plugins._sidebar')

  <div class="wc-content">
<div class="card">
  <div class="card-header">
    <h3>Cấu hình: {{ $plugin['title'] }}</h3>
    <a href="{{ route('admin.plugins.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  @if(empty($fields))
    <div class="empty-state">Plugin này chưa khai báo cấu hình.</div>
  @else
    <form method="post" action="{{ route('admin.plugins.settings.update', $plugin['name']) }}">
      @csrf
      @foreach($fields as $key => $field)
        @php
          $type = $field['type'] ?? 'text';
          $label = $field['label'] ?? \Illuminate\Support\Str::headline($key);
          $value = old('settings.'.$key, $values[$key] ?? ($field['default'] ?? ''));
        @endphp

        @if($type === 'textarea')
          <div class="form-group">
            <label>{{ $label }}</label>
            <textarea name="settings[{{ $key }}]" class="form-control" rows="{{ $field['rows'] ?? 3 }}">{{ $value }}</textarea>
          </div>
        @elseif($type === 'select')
          <div class="form-group">
            <label>{{ $label }}</label>
            <select name="settings[{{ $key }}]" class="form-control">
              @foreach(($field['options'] ?? []) as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
              @endforeach
            </select>
          </div>
        @elseif($type === 'checkbox')
          <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:400">
              <input type="hidden" name="settings[{{ $key }}]" value="0">
              <input type="checkbox" name="settings[{{ $key }}]" value="1" @checked((bool) $value)>
              <span>{{ $label }}</span>
            </label>
          </div>
        @else
          <div class="form-group">
            <label>{{ $label }}</label>
            <input type="{{ $type === 'number' ? 'number' : 'text' }}" name="settings[{{ $key }}]" class="form-control" value="{{ $value }}" placeholder="{{ $field['placeholder'] ?? '' }}">
          </div>
        @endif
      @endforeach

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Lưu cấu hình</button>
      </div>
    </form>
  @endif
</div>
  </div>
</div>
@endsection
