@php
  $settings = $block->settings ?? [];
  $fields = $schema['fields'] ?? $schema;

  $schemaValue = function (string $key, $default = '') use ($settings) {
      return data_get(old('settings', []), $key, data_get($settings, $key, $default));
  };

  $schemaName = function (string $key): string {
      return 'settings'.collect(explode('.', $key))->map(fn ($part) => '['.$part.']')->implode('');
  };

  $assetValue = function (?string $path) {
      if (!$path) {
          return null;
      }

      return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', 'theme-assets/'])
          ? asset($path)
          : asset('storage/'.$path);
  };
@endphp

<div class="banner-settings" data-widget-schema-settings>
  @foreach($fields as $key => $field)
    @php
      $type = $field['type'] ?? 'text';
      $label = $field['label'] ?? \Illuminate\Support\Str::headline($key);
      $value = $schemaValue($key, $field['default'] ?? '');
    @endphp

    @if($type === 'textarea' || $type === 'richtext')
      <div class="form-group">
        <label>{{ $label }}</label>
        <textarea name="{{ $schemaName($key) }}" class="form-control {{ $type === 'richtext' ? 'rich-editor' : '' }}" rows="{{ $field['rows'] ?? 3 }}" placeholder="{{ $field['placeholder'] ?? '' }}">{{ $value }}</textarea>
      </div>
    @elseif($type === 'select')
      <div class="form-group">
        <label>{{ $label }}</label>
        <select name="{{ $schemaName($key) }}" class="form-control">
          @foreach(($field['options'] ?? []) as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
          @endforeach
        </select>
      </div>
    @elseif($type === 'checkbox')
      <label class="form-check">
        <input type="hidden" name="{{ $schemaName($key) }}" value="0">
        <input type="checkbox" name="{{ $schemaName($key) }}" value="1" @checked((bool) $value)> {{ $label }}
      </label>
    @elseif($type === 'image')
      @php $imageUrl = $assetValue($value); @endphp
      <div class="form-group">
        <label>{{ $label }}</label>
        @if($imageUrl)
          <div style="margin:6px 0">
            <img src="{{ $imageUrl }}" alt="" style="max-width:220px;max-height:130px;object-fit:cover;border-radius:6px">
          </div>
        @endif
        <input type="file" name="{{ $schemaName($key) }}" accept="image/*" class="form-control">
      </div>
    @elseif($type === 'repeater')
      @php
        $rows = array_values((array) $value);
        $childFields = $field['fields'] ?? [];
        $itemLabel = $field['item_label'] ?? $label;
      @endphp
      <div class="service-block-items" data-service-block-items>
        @foreach($rows as $i => $row)
          <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
            <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center">
              <span><span>▤</span><b data-service-item-title> {{ $itemLabel }} {{ $i + 1 }}</b></span>
              <button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button>
            </div>
            @foreach($childFields as $childKey => $child)
              @php
                $childType = $child['type'] ?? 'text';
                $childLabel = $child['label'] ?? \Illuminate\Support\Str::headline($childKey);
                $childValue = data_get(old('settings.'.$key.'.'.$i, []), $childKey, data_get($row, $childKey, $child['default'] ?? ''));
              @endphp
              @if($childType === 'textarea')
                <div class="form-group"><label>{{ $childLabel }}</label><textarea name="settings[{{ $key }}][{{ $i }}][{{ $childKey }}]" class="form-control" rows="{{ $child['rows'] ?? 2 }}">{{ $childValue }}</textarea></div>
              @elseif($childType === 'select')
                <div class="form-group"><label>{{ $childLabel }}</label><select name="settings[{{ $key }}][{{ $i }}][{{ $childKey }}]" class="form-control">
                  @foreach(($child['options'] ?? []) as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $childValue === (string) $optionValue)>{{ $optionLabel }}</option>
                  @endforeach
                </select></div>
              @elseif($childType === 'image')
                @php $childImageUrl = $assetValue($childValue); @endphp
                <div class="form-group">
                  <label>{{ $childLabel }}</label>
                  @if($childImageUrl)
                    <div style="margin:6px 0"><img src="{{ $childImageUrl }}" alt="" style="max-width:160px;max-height:90px;object-fit:cover;border-radius:6px"></div>
                  @endif
                  <input type="hidden" name="settings[{{ $key }}][{{ $i }}][existing_{{ $childKey }}]" value="{{ $childValue }}">
                  <input type="file" name="settings[{{ $key }}][{{ $i }}][{{ $childKey }}]" accept="image/*" class="form-control">
                </div>
              @else
                <div class="form-group"><label>{{ $childLabel }}</label><input type="{{ $childType === 'number' ? 'number' : 'text' }}" name="settings[{{ $key }}][{{ $i }}][{{ $childKey }}]" value="{{ $childValue }}" class="form-control" placeholder="{{ $child['placeholder'] ?? '' }}"></div>
              @endif
            @endforeach
          </div>
        @endforeach
      </div>
      <button type="button" class="btn btn-primary btn-sm" data-add-service-item>+ Thêm {{ mb_strtolower($itemLabel) }}</button>
      <template data-service-item-template>
        <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
          <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center">
            <span><span>▤</span><b data-service-item-title> {{ $itemLabel }} __NUMBER__</b></span>
            <button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button>
          </div>
          @foreach($childFields as $childKey => $child)
            @php $childType = $child['type'] ?? 'text'; $childLabel = $child['label'] ?? \Illuminate\Support\Str::headline($childKey); @endphp
            @if($childType === 'textarea')
              <div class="form-group"><label>{{ $childLabel }}</label><textarea name="settings[{{ $key }}][__INDEX__][{{ $childKey }}]" class="form-control" rows="{{ $child['rows'] ?? 2 }}"></textarea></div>
            @elseif($childType === 'select')
              <div class="form-group"><label>{{ $childLabel }}</label><select name="settings[{{ $key }}][__INDEX__][{{ $childKey }}]" class="form-control">
                @foreach(($child['options'] ?? []) as $optionValue => $optionLabel)
                  <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                @endforeach
              </select></div>
            @elseif($childType === 'image')
              <div class="form-group"><label>{{ $childLabel }}</label><input type="hidden" name="settings[{{ $key }}][__INDEX__][existing_{{ $childKey }}]" value=""><input type="file" name="settings[{{ $key }}][__INDEX__][{{ $childKey }}]" accept="image/*" class="form-control"></div>
            @else
              <div class="form-group"><label>{{ $childLabel }}</label><input type="{{ $childType === 'number' ? 'number' : 'text' }}" name="settings[{{ $key }}][__INDEX__][{{ $childKey }}]" class="form-control" placeholder="{{ $child['placeholder'] ?? '' }}"></div>
            @endif
          @endforeach
        </div>
      </template>
    @else
      <div class="form-group">
        <label>{{ $label }}</label>
        <input type="{{ $type === 'number' ? 'number' : ($type === 'color' ? 'color' : 'text') }}" name="{{ $schemaName($key) }}" value="{{ $value }}" class="form-control" placeholder="{{ $field['placeholder'] ?? '' }}" @isset($field['min']) min="{{ $field['min'] }}" @endisset @isset($field['max']) max="{{ $field['max'] }}" @endisset>
      </div>
    @endif
  @endforeach
</div>
