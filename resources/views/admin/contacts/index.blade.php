@extends('layouts.admin')
@section('title','Liên hệ')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Liên hệ ({{ number_format($total) }})</h3>
  </div>

  @include('admin.partials.list-filters', [
    'clearUrl' => route('admin.contacts.index'),
    'hasActiveFilters' => request()->filled('q') || request()->filled('status') || request()->filled('from') || request()->filled('to'),
    'fields' => [
      ['type' => 'text', 'name' => 'q', 'value' => $q, 'placeholder' => 'Tìm tên, SĐT, email, chủ đề...'],
      ['type' => 'select', 'name' => 'status', 'value' => $status, 'label' => 'Trạng thái', 'options' => ['1' => 'Đã đọc', '0' => 'Mới']],
      ['type' => 'date', 'name' => 'from', 'value' => $from, 'placeholder' => 'Từ ngày'],
      ['type' => 'date', 'name' => 'to', 'value' => $to, 'placeholder' => 'Đến ngày'],
    ],
  ])

  @if($contacts->isEmpty())
    <div class="empty-state">Chưa có liên hệ nào.</div>
  @else
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>#</th><th>Họ tên</th><th>SĐT</th><th>Chủ đề</th><th>Trạng thái</th><th>Ngày</th><th></th></tr></thead>
      <tbody>
      @foreach($contacts as $c)
        <tr style="{{ !$c->is_read ? 'background:#fff7ed' : '' }}">
          <td>{{ $c->id }}</td>
          <td><b>{{ $c->name }}</b></td>
          <td>{{ $c->phone }}</td>
          <td>{{ $c->subject }}</td>
          <td>
            @if($c->is_read)
              <span class="badge badge-secondary">Đã đọc</span>
            @else
              <span class="badge badge-warning">Mới</span>
            @endif
          </td>
          <td>{{ $c->created_at->format('d/m/Y') }}</td>
          <td>
            <div class="actions">
              <a href="{{ route('admin.contacts.show', $c) }}" class="btn btn-outline btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
              <form method="post" action="{{ route('admin.contacts.destroy', $c) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa liên hệ này?')">
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

  @if($contacts->hasPages())
  <div class="pagination">
    {{ $contacts->links('vendor.pagination.admin') }}
  </div>
  @endif
  @endif
</div>
@endsection
