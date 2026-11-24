@extends('layouts.admin')
@section('title','Sản phẩm')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Danh sách sản phẩm ({{ number_format($total) }})</h3>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Thêm sản phẩm
    </a>
  </div>

  @include('admin.partials.list-filters', [
    'clearUrl' => route('admin.products.index'),
    'hasActiveFilters' => request()->filled('q') || request()->filled('cat') || request()->filled('brand') || request()->filled('status') || request()->filled('featured') || request()->filled('price_from') || request()->filled('price_to') || request()->filled('stock_from') || request()->filled('stock_to') || request()->filled('from') || request()->filled('to'),
    'fields' => [
      ['type' => 'text', 'name' => 'q', 'value' => $q, 'placeholder' => 'Tìm tên, SKU, thương hiệu...'],
      ['type' => 'select', 'name' => 'cat', 'value' => $catFilter, 'label' => 'Tất cả danh mục', 'options' => $categories->flatMap(function ($parent) {
        $options = [$parent->id => $parent->name];
        foreach ($parent->children as $child) {
          $options[$child->id] = '└ ' . $child->name;
        }
        return $options;
      })->toArray()],
      ['type' => 'text', 'name' => 'brand', 'value' => $brand, 'placeholder' => 'Thương hiệu'],
      ['type' => 'select', 'name' => 'status', 'value' => $status, 'label' => 'Trạng thái', 'options' => ['1' => 'Hiện', '0' => 'Ẩn']],
      ['type' => 'select', 'name' => 'featured', 'value' => $featured, 'label' => 'Nổi bật', 'options' => ['1' => 'Có', '0' => 'Không']],
      ['type' => 'number', 'name' => 'price_from', 'value' => $priceFrom, 'placeholder' => 'Giá từ'],
      ['type' => 'number', 'name' => 'price_to', 'value' => $priceTo, 'placeholder' => 'Giá đến'],
      ['type' => 'number', 'name' => 'stock_from', 'value' => $stockFrom, 'placeholder' => 'Tồn kho từ'],
      ['type' => 'number', 'name' => 'stock_to', 'value' => $stockTo, 'placeholder' => 'Tồn kho đến'],
      ['type' => 'date', 'name' => 'from', 'value' => $from, 'placeholder' => 'Từ ngày'],
      ['type' => 'date', 'name' => 'to', 'value' => $to, 'placeholder' => 'Đến ngày'],
    ],
  ])

  @if($products->isEmpty())
    <div class="empty-state">Không có sản phẩm.</div>
  @else
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>#</th><th>Ảnh</th><th>Tên</th><th>Danh mục</th><th>Giá</th><th>KM</th><th>Kho</th><th>Trạng thái</th><th></th></tr></thead>
      <tbody>
      @foreach($products as $p)
        <tr>
          <td>{{ $p->id }}</td>
          <td>@if($p->image)<img src="{{ $p->image_url }}" class="img-thumb" alt="">@endif</td>
          <td>
            <b>{{ $p->name }}</b>
            @if($p->is_featured)<span class="badge badge-warning" style="margin-left:6px">Nổi bật</span>@endif
            <div style="font-size:11px;color:var(--text-2)">{{ $p->sku }}</div>
          </td>
          <td>{{ $p->category->name ?? '—' }}</td>
          <td>{{ number_format($p->price) }}đ</td>
          <td>{{ $p->sale_price ? number_format($p->sale_price).'đ' : '—' }}</td>
          <td>
            @if($p->stock <= 10)
              <span class="badge badge-danger">{{ $p->stock }}</span>
            @else
              {{ $p->stock }}
            @endif
          </td>
          <td>
            @if($p->is_active)
              <span class="badge badge-success">Hiện</span>
            @else
              <span class="badge badge-secondary">Ẩn</span>
            @endif
          </td>
          <td>
            <div class="actions">
              <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-outline btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>
              <form method="post" action="{{ route('admin.products.destroy', $p) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa sản phẩm này?')">
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

  @if($products->hasPages())
  <div class="pagination">
    {{ $products->links('vendor.pagination.admin') }}
  </div>
  @endif
  @endif
</div>
@endsection
