@extends('layouts.admin')
@section('title', isset($adminRole) ? 'Sửa nhóm admin' : 'Thêm nhóm admin')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>{{ isset($adminRole) ? 'Sửa nhóm admin' : 'Thêm nhóm admin' }}</h3>
    <a href="{{ route('admin.admin-roles.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <form method="post" action="{{ isset($adminRole) ? route('admin.admin-roles.update', $adminRole) : route('admin.admin-roles.store') }}">
    @csrf
    @if(isset($adminRole)) @method('PUT') @endif

    <div class="form-group">
      <label>Tên nhóm <span class="req">*</span></label>
      <input type="text" name="name" class="form-control" value="{{ old('name', $adminRole->name ?? '') }}" required placeholder="VD: Nhóm đăng sản phẩm">
      @error('name')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
      <label style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
        Các quyền truy cập
        <label style="display:flex;align-items:center;gap:6px;font-weight:400;cursor:pointer;margin-left:12px">
          <input type="checkbox" id="check-all" style="width:16px;height:16px">
          <span style="font-size:13px;color:#6b7280">Tất cả</span>
        </label>
      </label>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px 24px">
        @foreach(\App\Models\AdminRole::permissions() as $key => $label)
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:400">
            <input type="checkbox" name="permissions[]" value="{{ $key }}" class="perm-check" style="width:16px;height:16px"
              {{ in_array($key, old('permissions', $adminRole->permissions ?? [])) ? 'checked' : '' }}>
            <span style="font-size:14px">{{ $label }}</span>
          </label>
        @endforeach
      </div>
    </div>

    <div class="form-group">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:400">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" style="width:16px;height:16px"
          {{ old('is_active', $adminRole->is_active ?? true) ? 'checked' : '' }}>
        <span>Kích hoạt nhóm</span>
      </label>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">{{ isset($adminRole) ? 'Cập nhật' : 'Thêm nhóm' }}</button>
    </div>
  </form>
</div>
@endsection

@push('js')
<script>
  const checkAll = document.getElementById('check-all');
  const permChecks = document.querySelectorAll('.perm-check');

  checkAll.addEventListener('change', () => {
    permChecks.forEach(c => c.checked = checkAll.checked);
  });

  permChecks.forEach(c => c.addEventListener('change', () => {
    checkAll.checked = [...permChecks].every(c => c.checked);
    checkAll.indeterminate = !checkAll.checked && [...permChecks].some(c => c.checked);
  }));

  // Set initial indeterminate state
  const checked = [...permChecks].filter(c => c.checked).length;
  if (checked > 0 && checked < permChecks.length) checkAll.indeterminate = true;
  if (checked === permChecks.length) checkAll.checked = true;
</script>
@endpush
