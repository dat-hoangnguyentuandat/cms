@extends('layouts.admin')
@section('title','Tin tức')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Bài viết ({{ number_format($total) }})</h3>
    <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Thêm bài viết
    </a>
  </div>

  @include('admin.partials.list-filters', [
    'clearUrl' => route('admin.news.index'),
    'hasActiveFilters' => request()->filled('q') || request()->filled('cat') || request()->filled('status') || request()->filled('author') || request()->filled('views_min') || request()->filled('views_max') || request()->filled('from') || request()->filled('to'),
    'fields' => [
      ['type' => 'text', 'name' => 'q', 'value' => $q, 'placeholder' => 'Tìm tiêu đề, slug, nội dung...'],
      ['type' => 'select', 'name' => 'cat', 'value' => $cat, 'label' => 'Tất cả danh mục', 'options' => \App\Models\Category::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->get()->mapWithKeys(fn($c) => [$c->id => $c->name])->toArray()],
      ['type' => 'text', 'name' => 'author', 'value' => $author, 'placeholder' => 'Tác giả'],
      ['type' => 'select', 'name' => 'status', 'value' => $status, 'label' => 'Trạng thái', 'options' => ['1' => 'Công khai', '0' => 'Bản nháp']],
      ['type' => 'number', 'name' => 'views_min', 'value' => $viewsMin, 'placeholder' => 'Lượt xem từ'],
      ['type' => 'number', 'name' => 'views_max', 'value' => $viewsMax, 'placeholder' => 'Lượt xem đến'],
      ['type' => 'date', 'name' => 'from', 'value' => $from, 'placeholder' => 'Từ ngày'],
      ['type' => 'date', 'name' => 'to', 'value' => $to, 'placeholder' => 'Đến ngày'],
    ],
  ])

  @if($posts->isEmpty())
    <div class="empty-state">Chưa có bài viết nào.</div>
  @else
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>#</th><th>Ảnh</th><th>Tiêu đề</th><th>Danh mục</th><th>Tác giả</th><th>Lượt xem</th><th>Trạng thái</th><th>Ngày</th><th></th></tr></thead>
      <tbody>
      @foreach($posts as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td>@if($r->image)<img src="{{ $r->image_url }}" class="img-thumb" alt="">@endif</td>
          <td>
            <b>{{ $r->title }}</b>
            <div style="font-size:11px;color:var(--text-2)">{{ $r->slug }}</div>
          </td>
          <td>{{ $r->category->name ?? '—' }}</td>
          <td>{{ $r->author }}</td>
          <td>{{ number_format($r->views) }}</td>
          <td>
            @if($r->is_published)
              <span class="badge badge-success">Công khai</span>
            @else
              <span class="badge badge-secondary">Bản nháp</span>
            @endif
          </td>
          <td>{{ $r->created_at->format('d/m/Y') }}</td>
          <td>
            <div class="actions">
              <a href="{{ route('admin.news.edit', $r) }}" class="btn btn-outline btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>
              <a href="{{ route('news.show', $r->slug) }}" target="_blank" class="btn btn-outline btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
              <form method="post" action="{{ route('admin.news.destroy', $r) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa bài viết này?')">
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

  @if($posts->hasPages())
  <div class="pagination">
    {{ $posts->links('vendor.pagination.admin') }}
  </div>
  @endif
  @endif
</div>
@endsection
