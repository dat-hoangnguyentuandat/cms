@extends('layouts.admin')
@section('title','HTML Tags')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Danh sách HTML Tags</h3>
    <a href="{{ route('admin.html-tags.create') }}" class="btn btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Thêm tag
    </a>
  </div>

  @include('admin.partials.list-filters', [
    'clearUrl' => route('admin.html-tags.index'),
    'hasActiveFilters' => request()->filled('q') || request()->filled('position') || request()->filled('status') || request()->filled('home_only') || request()->filled('sort_from') || request()->filled('sort_to') || request()->filled('from') || request()->filled('to'),
    'fields' => [
      ['type' => 'text', 'name' => 'q', 'value' => $q, 'placeholder' => 'Tìm tên, mô tả, nội dung...'],
      ['type' => 'select', 'name' => 'position', 'value' => $position, 'label' => 'Vị trí', 'options' => ['head' => '<head>', 'body_start' => 'Đầu body', 'body_end' => 'Cuối body']],
      ['type' => 'select', 'name' => 'status', 'value' => $status, 'label' => 'Trạng thái', 'options' => ['1' => 'Bật', '0' => 'Tắt']],
      ['type' => 'select', 'name' => 'home_only', 'value' => $homeOnly, 'label' => 'Chỉ trang chủ', 'options' => ['1' => 'Có', '0' => 'Không']],
      ['type' => 'number', 'name' => 'sort_from', 'value' => $sortFrom, 'placeholder' => 'Thứ tự từ'],
      ['type' => 'number', 'name' => 'sort_to', 'value' => $sortTo, 'placeholder' => 'Thứ tự đến'],
      ['type' => 'date', 'name' => 'from', 'value' => $from, 'placeholder' => 'Từ ngày'],
      ['type' => 'date', 'name' => 'to', 'value' => $to, 'placeholder' => 'Đến ngày'],
    ],
  ])

  @if($tags->isEmpty())
    <div class="empty-state">Chưa có HTML tag nào.</div>
  @else
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Tên</th>
          <th>Vị trí</th>
          <th>Mô tả</th>
          <th>Chỉ trang chủ</th>
          <th>Thứ tự</th>
          <th>Trạng thái</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($tags as $tag)
        <tr>
          <td>{{ $tag->id }}</td>
          <td><b>{{ $tag->name }}</b></td>
          <td>
            @if($tag->position === 'head')
              <span class="badge badge-secondary">&lt;head&gt;</span>
            @elseif($tag->position === 'body_start')
              <span class="badge badge-secondary">&lt;body&gt; đầu</span>
            @else
              <span class="badge badge-secondary">&lt;/body&gt; cuối</span>
            @endif
          </td>
          <td style="color:var(--text-2);font-size:13px">{{ $tag->description ?? '—' }}</td>
          <td>
            @if($tag->only_homepage)
              <span class="badge badge-success">Có</span>
            @else
              <span style="color:var(--text-2);font-size:13px">Không</span>
            @endif
          </td>
          <td>{{ $tag->sort_order }}</td>
          <td>
            <form method="post" action="{{ route('admin.html-tags.toggle', $tag) }}" style="display:inline">
              @csrf
              <button type="submit" class="btn btn-sm {{ $tag->is_active ? 'btn-primary' : 'btn-outline' }}" title="{{ $tag->is_active ? 'Đang bật — nhấn để tắt' : 'Đang tắt — nhấn để bật' }}">
                {{ $tag->is_active ? 'Bật' : 'Tắt' }}
              </button>
            </form>
          </td>
          <td>
            <div class="actions">
              <a href="{{ route('admin.html-tags.edit', $tag) }}" class="btn btn-outline btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>
              <form method="post" action="{{ route('admin.html-tags.destroy', $tag) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa tag này?')">
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
  @if($tags->hasPages())
  <div class="pagination">
    {{ $tags->links('vendor.pagination.admin') }}
  </div>
  @endif
  @endif
</div>
@endsection
