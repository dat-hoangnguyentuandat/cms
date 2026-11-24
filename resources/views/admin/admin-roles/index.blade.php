@extends('layouts.admin')
@section('title','Nhóm admin')

@section('content')
@php $permissionLabels = \App\Models\AdminRole::permissions(); @endphp
<div class="card">
  <div class="card-header">
    <h3>Quản lý Admin</h3>
    <a href="{{ route('admin.admin-roles.create') }}" class="btn btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Thêm nhóm
    </a>
  </div>

  <div class="tab-nav" style="padding:0 24px;border-bottom:1px solid #e5e7eb;display:flex;gap:0">
    <a href="{{ route('admin.admin-users.index') }}" class="tab-link" style="padding:12px 20px;text-decoration:none;color:#6b7280;font-size:14px;border-bottom:2px solid transparent;display:flex;align-items:center;gap:6px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Danh sách admin
    </a>
    <a href="{{ route('admin.admin-roles.index') }}" class="tab-link active" style="padding:12px 20px;text-decoration:none;color:#2563eb;font-size:14px;border-bottom:2px solid #2563eb;display:flex;align-items:center;gap:6px;font-weight:500">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      Nhóm admin
    </a>
  </div>

  @include('admin.partials.list-filters', [
    'clearUrl' => route('admin.admin-roles.index'),
    'hasActiveFilters' => request()->filled('q') || request()->filled('status') || request()->filled('permission') || request()->filled('from') || request()->filled('to'),
    'fields' => [
      ['type' => 'text', 'name' => 'q', 'value' => $q, 'placeholder' => 'Tìm tên nhóm...'],
      ['type' => 'select', 'name' => 'permission', 'value' => $perm, 'label' => 'Tất cả quyền', 'options' => $permissionLabels],
      ['type' => 'select', 'name' => 'status', 'value' => $status, 'label' => 'Trạng thái', 'options' => ['1' => 'Hoạt động', '0' => 'Tắt']],
      ['type' => 'date', 'name' => 'from', 'value' => $from, 'placeholder' => 'Tạo từ'],
      ['type' => 'date', 'name' => 'to', 'value' => $to, 'placeholder' => 'Tạo đến'],
    ],
  ])

  @if($roles->isEmpty())
    <div class="empty-state">Chưa có nhóm admin nào.</div>
  @else
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Tên nhóm</th>
          <th>Quyền quản lý</th>
          <th>Số admin</th>
          <th>Trạng thái</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($roles as $role)
        <tr>
          <td>{{ $role->id }}</td>
          <td><b>{{ $role->name }}</b></td>
          <td>
            @forelse($role->permissions ?? [] as $perm)
              <span class="badge badge-info" style="margin:2px 2px 2px 0">{{ $permissionLabels[$perm] ?? $perm }}</span>
            @empty
              <span style="color:#9ca3af;font-size:13px">Chưa có quyền</span>
            @endforelse
          </td>
          <td>{{ $role->admin_users_count }}</td>
          <td>
            @if($role->is_active)
              <span class="badge badge-success">Hoạt động</span>
            @else
              <span class="badge badge-secondary">Tắt</span>
            @endif
          </td>
          <td>
            <div class="actions">
              <a href="{{ route('admin.admin-roles.edit', $role) }}" class="btn btn-outline btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>
              <form method="post" action="{{ route('admin.admin-roles.destroy', $role) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa nhóm {{ $role->name }}?')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
              </form>
            </div>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @if($roles->hasPages())
  <div class="pagination">
    {{ $roles->links('vendor.pagination.admin') }}
  </div>
  @endif
  @endif
</div>
@endsection
