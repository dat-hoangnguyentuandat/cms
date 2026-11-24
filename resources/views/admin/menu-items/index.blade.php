@extends('layouts.admin')
@section('title','Quản lý menu')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Menu Chính</h3>
    <div class="btn-dropdown-wrap" id="add-menu-wrap">
      <button class="btn btn-primary btn-dropdown-toggle" onclick="toggleDropdown('add-menu-menu', event)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Thêm menu
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-left:2px"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="btn-dropdown-menu" id="add-menu-menu">
        <a href="{{ route('admin.menu-items.create', ['type' => 'custom']) }}" class="btn-dropdown-item">Trang nội dung</a>
        <a href="{{ route('admin.menu-items.create', ['type' => 'service_list']) }}" class="btn-dropdown-item">Trang dịch vụ</a>
        <a href="{{ route('admin.menu-items.create', ['type' => 'product_list']) }}" class="btn-dropdown-item">Trang sản phẩm</a>
        <a href="{{ route('admin.menu-items.create', ['type' => 'news_list']) }}" class="btn-dropdown-item">Trang tin tức</a>
        <a href="{{ route('admin.menu-items.create', ['type' => 'contact']) }}" class="btn-dropdown-item">Trang liên hệ</a>
      </div>
    </div>
  </div>

  @include('admin.partials.list-filters', [
    'clearUrl' => route('admin.menu-items.index'),
    'hasActiveFilters' => request()->filled('q') || request()->filled('type') || request()->filled('status') || request()->filled('sort_from') || request()->filled('sort_to') || request()->filled('from') || request()->filled('to'),
    'fields' => [
      ['type' => 'text', 'name' => 'q', 'value' => request('q'), 'placeholder' => 'Tìm label, slug, url...'],
      ['type' => 'select', 'name' => 'type', 'value' => request('type'), 'label' => 'Tất cả loại', 'options' => [
        'custom' => 'Trang nội dung',
        'page' => 'Trang giới thiệu',
        'news_list' => 'Trang tin tức',
        'product_list' => 'Trang sản phẩm',
        'service_list' => 'Trang dịch vụ',
        'news_cat' => 'Danh mục tin tức',
        'product_cat' => 'Danh mục sản phẩm',
        'service_cat' => 'Danh mục dịch vụ',
        'contact' => 'Trang liên hệ',
      ]],
      ['type' => 'select', 'name' => 'status', 'value' => request('status'), 'label' => 'Trạng thái', 'options' => ['1' => 'Hiện', '0' => 'Ẩn']],
      ['type' => 'number', 'name' => 'sort_from', 'value' => request('sort_from'), 'placeholder' => 'Thứ tự từ'],
      ['type' => 'number', 'name' => 'sort_to', 'value' => request('sort_to'), 'placeholder' => 'Thứ tự đến'],
      ['type' => 'date', 'name' => 'from', 'value' => request('from'), 'placeholder' => 'Từ ngày'],
      ['type' => 'date', 'name' => 'to', 'value' => request('to'), 'placeholder' => 'Đến ngày'],
    ],
  ])

  @if($menuItems->isEmpty())
    <div class="empty-state">Chưa có menu item nào.</div>
  @else
  <div style="padding:16px">
    <ul id="menu-sortable" class="sortable-list">
      @foreach($menuItems as $item)
        @if(!$item->parent_id)
        <li class="sortable-item" data-id="{{ $item->id }}">
          <div class="menu-row menu-row-parent">
            <span class="drag-handle" title="Kéo để sắp xếp">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="9" cy="5" r="1" fill="currentColor"/><circle cx="15" cy="5" r="1" fill="currentColor"/><circle cx="9" cy="12" r="1" fill="currentColor"/><circle cx="15" cy="12" r="1" fill="currentColor"/><circle cx="9" cy="19" r="1" fill="currentColor"/><circle cx="15" cy="19" r="1" fill="currentColor"/></svg>
            </span>
            <span class="menu-label">{{ $item->label }}</span>
            <span class="menu-type">{{ \App\Models\MenuItem::typeLabel($item->type) }}</span>
            <div class="menu-actions">
              <a href="{{ route('admin.menu-items.edit', $item) }}" class="btn btn-outline btn-sm" title="Sửa">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>
              <button onclick="toggleActive({{ $item->id }}, this)"
                      class="badge {{ $item->is_active ? 'badge-success' : 'badge-secondary' }}"
                      style="font-size:11px;border:none;cursor:pointer"
                      title="Click để đổi trạng thái">
                {{ $item->is_active ? 'Hiện' : 'Ẩn' }}
              </button>
              <div class="btn-dropdown-wrap">
                <button class="btn btn-sm btn-dropdown-toggle"
                        onclick="toggleDropdown('child-menu-{{ $item->id }}', event)"
                        style="background:#26a69a;color:#fff;border:none;white-space:nowrap;padding:5px 10px;border-radius:4px;font-size:12px;display:flex;align-items:center;gap:4px">
                  Thêm menu con
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="btn-dropdown-menu" id="child-menu-{{ $item->id }}">
                  <a href="{{ route('admin.menu-items.create', ['type' => 'custom',      'parent_id' => $item->id]) }}" class="btn-dropdown-item">Trang nội dung</a>
                  <a href="{{ route('admin.menu-items.create', ['type' => 'service_cat', 'parent_id' => $item->id]) }}" class="btn-dropdown-item">Danh mục dịch vụ</a>
                  <a href="{{ route('admin.menu-items.create', ['type' => 'product_cat', 'parent_id' => $item->id]) }}" class="btn-dropdown-item">Danh mục sản phẩm</a>
                  <a href="{{ route('admin.menu-items.create', ['type' => 'news_cat',    'parent_id' => $item->id]) }}" class="btn-dropdown-item">Danh mục tin tức</a>
                </div>
              </div>
              <form method="post" action="{{ route('admin.menu-items.destroy', $item) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa menu item này?')" title="Xóa">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
              </form>
            </div>
          </div>

          {{-- Children --}}
          <ul class="sortable-list children-list" data-parent="{{ $item->id }}">
            @foreach($item->children as $child)
            <li class="sortable-item" data-id="{{ $child->id }}">
              <div class="menu-row menu-row-child">
                <span class="drag-handle" title="Kéo để sắp xếp">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="9" cy="5" r="1" fill="currentColor"/><circle cx="15" cy="5" r="1" fill="currentColor"/><circle cx="9" cy="12" r="1" fill="currentColor"/><circle cx="15" cy="12" r="1" fill="currentColor"/><circle cx="9" cy="19" r="1" fill="currentColor"/><circle cx="15" cy="19" r="1" fill="currentColor"/></svg>
                </span>
                <span class="menu-label">{{ $child->label }}</span>
                <span class="menu-type">{{ \App\Models\MenuItem::typeLabel($child->type) }}</span>
                <div class="menu-actions">
                  <a href="{{ route('admin.menu-items.edit', $child) }}" class="btn btn-outline btn-sm" title="Sửa">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </a>
                  <button onclick="toggleActive({{ $child->id }}, this)"
                          class="badge {{ $child->is_active ? 'badge-success' : 'badge-secondary' }}"
                          style="font-size:11px;border:none;cursor:pointer"
                          title="Click để đổi trạng thái">
                    {{ $child->is_active ? 'Hiện' : 'Ẩn' }}
                  </button>
                  <div class="btn-dropdown-wrap">
                    <button class="btn btn-sm btn-dropdown-toggle"
                            onclick="toggleDropdown('child-menu-{{ $child->id }}', event)"
                            style="background:#26a69a;color:#fff;border:none;white-space:nowrap;padding:5px 10px;border-radius:4px;font-size:12px;display:flex;align-items:center;gap:4px">
                      Thêm menu con
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="btn-dropdown-menu" id="child-menu-{{ $child->id }}">
                      <a href="{{ route('admin.menu-items.create', ['type' => 'custom',      'parent_id' => $child->id]) }}" class="btn-dropdown-item">Trang nội dung</a>
                      <a href="{{ route('admin.menu-items.create', ['type' => 'service_cat', 'parent_id' => $child->id]) }}" class="btn-dropdown-item">Danh mục dịch vụ</a>
                      <a href="{{ route('admin.menu-items.create', ['type' => 'product_cat', 'parent_id' => $child->id]) }}" class="btn-dropdown-item">Danh mục sản phẩm</a>
                      <a href="{{ route('admin.menu-items.create', ['type' => 'news_cat',    'parent_id' => $child->id]) }}" class="btn-dropdown-item">Danh mục tin tức</a>
                    </div>
                  </div>
                  <form method="post" action="{{ route('admin.menu-items.destroy', $child) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa menu item này?')" title="Xóa">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                  </form>
                </div>
              </div>
            </li>
            @endforeach
          </ul>
        </li>
        @endif
      @endforeach
    </ul>
  </div>
  @endif
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
var reorderUrl = '{{ route('admin.menu-items.reorder') }}';
var csrfToken = '{{ csrf_token() }}';

var toggleUrl = '{{ url('admin/menu-items') }}';

function toggleActive(id, btn) {
  fetch(toggleUrl + '/' + id + '/toggle', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrfToken }
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    btn.textContent = data.is_active ? 'Hiện' : 'Ẩn';
    btn.className = 'badge ' + (data.is_active ? 'badge-success' : 'badge-secondary');
    btn.style.cssText = 'font-size:11px;border:none;cursor:pointer';
  });
}

function toggleDropdown(id, e) {
  e.stopPropagation();
  document.querySelectorAll('.btn-dropdown-menu.open').forEach(function(m) {
    if (m.id !== id) m.classList.remove('open');
  });
  document.getElementById(id).classList.toggle('open');
}
document.addEventListener('click', function() {
  document.querySelectorAll('.btn-dropdown-menu.open').forEach(function(m) { m.classList.remove('open'); });
});

function serializeOrder() {
  var items = [];
  document.querySelectorAll('#menu-sortable > .sortable-item').forEach(function(parentEl) {
    var children = [];
    parentEl.querySelectorAll(':scope > .children-list > .sortable-item').forEach(function(childEl) {
      children.push({ id: parseInt(childEl.dataset.id) });
    });
    items.push({ id: parseInt(parentEl.dataset.id), children: children });
  });
  return items;
}

function saveOrder() {
  fetch(reorderUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    body: JSON.stringify({ items: serializeOrder() })
  });
}

function ensureParentItem(item) {
  item.querySelector('.menu-row')?.classList.remove('menu-row-child');
  item.querySelector('.menu-row')?.classList.add('menu-row-parent');

  if (!item.querySelector(':scope > .children-list')) {
    var list = document.createElement('ul');
    list.className = 'sortable-list children-list';
    item.appendChild(list);
    initChildrenSortable(list);
  }
}

function snapshotChildLists() {
  return Array.from(document.querySelectorAll('#menu-sortable > .sortable-item')).map(function(item) {
    var list = item.querySelector(':scope > .children-list');
    return Array.from(list ? list.children : []);
  });
}

function restoreChildListsByPosition(snapshot) {
  document.querySelectorAll('#menu-sortable > .sortable-item').forEach(function(item, index) {
    ensureParentItem(item);
    var list = item.querySelector(':scope > .children-list');
    list.innerHTML = '';
    (snapshot[index] || []).forEach(function(child) {
      child.querySelector('.menu-row')?.classList.remove('menu-row-parent');
      child.querySelector('.menu-row')?.classList.add('menu-row-child');
      child.querySelector(':scope > .children-list')?.remove();
      list.appendChild(child);
    });
  });
}

function initChildrenSortable(el) {
  Sortable.create(el, {
    handle: '.drag-handle',
    animation: 150,
    group: { name: 'menu', pull: true, put: true },
    onEnd: function(evt) {
      if (evt.to.id === 'menu-sortable') ensureParentItem(evt.item);
      saveOrder();
    },
  });
}

var childListSnapshot = [];
Sortable.create(document.getElementById('menu-sortable'), {
  handle: '.drag-handle',
  animation: 150,
  group: { name: 'menu', pull: true, put: true },
  onStart: function() {
    childListSnapshot = snapshotChildLists();
  },
  onEnd: function(evt) {
    if (evt.from.id === 'menu-sortable' && evt.to.id === 'menu-sortable') {
      restoreChildListsByPosition(childListSnapshot);
    } else {
      ensureParentItem(evt.item);
    }
    saveOrder();
  },
});

document.querySelectorAll('.children-list').forEach(initChildrenSortable);
</script>
<style>
.sortable-list { list-style: none; margin: 0; padding: 0; }
.sortable-item { margin-bottom: 8px; }

.menu-row {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 14px; background: #fff;
  border: 1px solid var(--border); border-radius: 6px;
}
.menu-row-parent { font-weight: 600; }
.menu-row-child  { background: #f9fafb; }

.drag-handle {
  cursor: grab; color: #bbb; flex-shrink: 0;
  display: flex; align-items: center;
}
.drag-handle:active { cursor: grabbing; }

.menu-label { flex: 1; font-size: 13px; text-transform: uppercase; }
.menu-type  { font-size: 12px; color: var(--text-2); white-space: nowrap; margin-right: 8px; }

.menu-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

.children-list { margin-top: 4px; margin-left: 40px; min-height: 4px; }

/* ghost while dragging */
.sortable-ghost .menu-row { opacity: .4; border: 2px dashed #26a69a; }

.btn-dropdown-wrap { position: relative; display: inline-block; }
.btn-dropdown-toggle { display: flex; align-items: center; gap: 5px; }
.btn-dropdown-menu {
  display: none; position: absolute; right: 0; top: calc(100% + 4px);
  background: #fff; border: 1px solid var(--border); border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0,0,0,.1); min-width: 200px; z-index: 200; overflow: hidden;
}
.btn-dropdown-menu.open { display: block; }
.btn-dropdown-item {
  display: block; padding: 10px 16px; font-size: 14px; color: var(--text-1) !important;
  text-decoration: none !important; background: #fff !important; transition: background .15s;
}
.btn-dropdown-item:hover { background: #f3f4f6 !important; }
</style>
@endpush
