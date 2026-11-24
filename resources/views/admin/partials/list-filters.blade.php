@php
  $fields = $fields ?? [];
  $clearUrl = $clearUrl ?? url()->current();
  $hasActiveFilters = $hasActiveFilters ?? false;
@endphp

<form method="get" class="filters admin-list-filters">
  @foreach($fields as $field)
    @php
      $type = $field['type'] ?? 'text';
      $name = $field['name'] ?? '';
      $value = $field['value'] ?? request()->query($name, '');
      $label = $field['label'] ?? '';
      $placeholder = $field['placeholder'] ?? $label;
    @endphp

    @if($type === 'select')
      <select name="{{ $name }}" class="form-control">
        <option value="">{{ $label ?: 'Tất cả' }}</option>
        @foreach(($field['options'] ?? []) as $optionValue => $optionLabel)
          <option value="{{ $optionValue }}" {{ (string) $value === (string) $optionValue ? 'selected' : '' }}>
            {{ $optionLabel }}
          </option>
        @endforeach
      </select>
    @elseif($type === 'date')
      <input type="date" name="{{ $name }}" value="{{ $value }}" class="form-control" placeholder="{{ $placeholder }}">
    @elseif($type === 'number')
      <input type="number" name="{{ $name }}" value="{{ $value }}" class="form-control" placeholder="{{ $placeholder }}">
    @else
      <input type="text" name="{{ $name }}" value="{{ $value }}" class="form-control" placeholder="{{ $placeholder }}">
    @endif
  @endforeach

  <button type="submit" class="btn btn-primary">Lọc</button>
  @if($hasActiveFilters)
    <a href="{{ $clearUrl }}" class="btn btn-outline">Xóa lọc</a>
  @endif
</form>
