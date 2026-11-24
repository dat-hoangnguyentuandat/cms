@extends('layouts.admin')
@section('title','FAQ')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Câu hỏi thường gặp ({{ number_format($faqs->total()) }})</h3>
    <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Thêm câu hỏi
    </a>
  </div>

  @include('admin.partials.list-filters', [
    'clearUrl' => route('admin.faqs.index'),
    'hasActiveFilters' => request()->filled('q') || request()->filled('status') || request()->filled('sort_from') || request()->filled('sort_to') || request()->filled('from') || request()->filled('to'),
    'fields' => [
      ['type' => 'text', 'name' => 'q', 'value' => $q, 'placeholder' => 'Tìm câu hỏi, câu trả lời...'],
      ['type' => 'select', 'name' => 'status', 'value' => $status, 'label' => 'Hiển thị', 'options' => ['1' => 'Hiện', '0' => 'Ẩn']],
      ['type' => 'number', 'name' => 'sort_from', 'value' => $sortFrom, 'placeholder' => 'Thứ tự từ'],
      ['type' => 'number', 'name' => 'sort_to', 'value' => $sortTo, 'placeholder' => 'Thứ tự đến'],
      ['type' => 'date', 'name' => 'from', 'value' => $from, 'placeholder' => 'Từ ngày'],
      ['type' => 'date', 'name' => 'to', 'value' => $to, 'placeholder' => 'Đến ngày'],
    ],
  ])

  @if($faqs->isEmpty())
    <div class="empty-state">Chưa có FAQ nào.</div>
  @else
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>#</th><th>Câu hỏi</th><th>Câu trả lời</th><th>Thứ tự</th><th>Hiển thị</th><th></th></tr></thead>
      <tbody>
      @foreach($faqs as $f)
        <tr>
          <td>{{ $f->id }}</td>
          <td><b>{{ $f->question }}</b></td>
          <td style="max-width:400px">{{ mb_substr($f->answer, 0, 120) }}{{ mb_strlen($f->answer) > 120 ? '...' : '' }}</td>
          <td>{{ $f->sort_order }}</td>
          <td>
            @if($f->is_active)
              <span class="badge badge-success">Hiện</span>
            @else
              <span class="badge badge-secondary">Ẩn</span>
            @endif
          </td>
          <td>
            <div class="actions">
              <a href="{{ route('admin.faqs.edit', $f) }}" class="btn btn-outline btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>
              <form method="post" action="{{ route('admin.faqs.destroy', $f) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa FAQ này?')">
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
  @if($faqs->hasPages())
  <div class="pagination">
    {{ $faqs->links('vendor.pagination.admin') }}
  </div>
  @endif
  @endif
</div>
@endsection
