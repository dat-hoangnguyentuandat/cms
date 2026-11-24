@extends('layouts.admin')

@section('title','Bố cục trang chủ')

@php $isSuperAdmin = ($_adminUser ?? null)?->role === 'admin'; @endphp

@section('content')
<div class="layout-actions">
  <span class="badge badge-info" style="font-size:13px;padding:6px 12px">Theme hiện tại: {{ strtoupper($activeTheme) }}</span>
  <div style="display:flex;gap:8px;align-items:center">
    <button type="button" class="btn btn-outline" onclick="location.reload()">Xóa Cache</button>
    @if($isSuperAdmin)
      <button type="button" class="btn btn-outline" id="addWidgetTypeBtn">Thêm widget</button>
    @endif
    <button type="button" class="btn btn-primary" id="widgetFab">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Thêm khối nội dung
    </button>
  </div>
</div>

<div class="layout-page" id="layoutPage">
  {{-- Float left column --}}
  @if(isset($zones['float_left']))
  <section class="layout-zone layout-zone-float" data-zone="float_left">
    <h3>{{ $zones['float_left'] }}</h3>
    @include('admin.homepage-layout._blocks', ['blocks' => $blocksByZone->get('float_left', collect())])
  </section>
  @endif

  {{-- Layout zones --}}
  <div class="homepage-layout" id="homepageLayout">
    <section class="layout-zone layout-zone-full" data-zone="header">
      <h3>{{ $zones['header'] }}</h3>
      @include('admin.homepage-layout._blocks', ['blocks' => $blocksByZone->get('header', collect())])
    </section>

    <div class="layout-main-grid">
      <section class="layout-zone" data-zone="main">
        <h3>{{ $zones['main'] }}</h3>
        @include('admin.homepage-layout._blocks', ['blocks' => $blocksByZone->get('main', collect())])
      </section>
      <section class="layout-zone" data-zone="sidebar">
        <h3>{{ $zones['sidebar'] }}</h3>
        @include('admin.homepage-layout._blocks', ['blocks' => $blocksByZone->get('sidebar', collect())])
      </section>
    </div>

    <section class="layout-zone layout-zone-full layout-zone-tall" data-zone="footer_top">
      <h3>{{ $zones['footer_top'] }}</h3>
      @include('admin.homepage-layout._blocks', ['blocks' => $blocksByZone->get('footer_top', collect())])
    </section>

    <div class="layout-footer-grid">
      @foreach(['footer_1','footer_2','footer_3'] as $footerZone)
        <section class="layout-zone" data-zone="{{ $footerZone }}">
          <h3>{{ $zones[$footerZone] }}</h3>
          @include('admin.homepage-layout._blocks', ['blocks' => $blocksByZone->get($footerZone, collect())])
        </section>
      @endforeach
    </div>

    <section class="layout-zone layout-zone-full" data-zone="footer_bottom">
      <h3>{{ $zones['footer_bottom'] }}</h3>
      @include('admin.homepage-layout._blocks', ['blocks' => $blocksByZone->get('footer_bottom', collect())])
    </section>
  </div>

  {{-- Float right column --}}
  @if(isset($zones['float_right']))
  <section class="layout-zone layout-zone-float" data-zone="float_right">
    <h3>{{ $zones['float_right'] }}</h3>
    @include('admin.homepage-layout._blocks', ['blocks' => $blocksByZone->get('float_right', collect())])
  </section>
  @endif

  {{-- Widget side panel (inline, not overlay) --}}
  <aside class="widget-panel" id="widgetPanel" aria-hidden="true">
    <div class="widget-panel-header">
      <div>
        <div class="widget-panel-title">Danh sách khối nội dung</div>
        <div class="widget-panel-sub">Kéo khối vào vị trí tương ứng để sử dụng</div>
      </div>
      <button class="widget-panel-close" id="widgetPanelClose" aria-label="Đóng">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <input type="text" class="widget-panel-search" id="widgetSearch" placeholder="Tìm kiếm nhanh..." autocomplete="off">
    <div class="widget-panel-body">
      <div class="widget-panel-cats" id="widgetCats">
        <button class="widget-cat active" data-cat="all">Tất cả</button>
        @foreach($blockTypes as $key => $label)
          <button class="widget-cat" data-cat="{{ $key }}">{{ $label }}</button>
        @endforeach
      </div>
      <div class="widget-panel-list" id="widgetList">
        @foreach($blockTypes as $key => $label)
          @foreach($widgetVariants[$key] ?? [] as $variant)
            <div class="widget-card" data-type="{{ $key }}" data-label="{{ $variant['label'] ?? $label }}" data-variant="{{ $variant['key'] }}" draggable="true">
              <div class="widget-card-preview">
                @if($variant['img'])
                  <img src="{{ $variant['img'] }}" alt="{{ $variant['label'] ?? $label }}" loading="lazy">
                @else
                  <div style="height:100%;display:flex;align-items:center;justify-content:center;text-align:center;padding:14px;color:#64748b;font-weight:600;background:#f8fafc">
                    {{ $variant['label'] ?? $label }}
                  </div>
                @endif
              </div>
              <div class="widget-card-footer">
                <div class="widget-card-label">{{ $variant['label'] ?? $label }}</div>
                @if($isSuperAdmin)
                <button class="widget-card-edit" type="button" data-widget-edit data-type="{{ $key }}" data-variant="{{ $variant['key'] }}" title="Sửa widget">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </button>
                @endif
              </div>
            </div>
          @endforeach
        @endforeach
      </div>
    </div>
  </aside>
</div>

{{-- Widget edit modal (super admin) --}}
@if($isSuperAdmin)
<div class="block-edit-modal" id="widgetEditModal" hidden>
  <div class="block-edit-modal-box" style="max-width:780px">
    <div class="block-edit-modal-header">
      <span id="widgetEditModalTitle">Sửa widget</span>
      <button type="button" class="block-edit-modal-close" id="widgetEditModalClose" aria-label="Đóng">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="block-edit-modal-body" style="padding:20px">
      <form id="widgetEditForm" method="POST" action="{{ route('admin.homepage-layout.widget-update') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" id="weType">
        <input type="hidden" name="variant" id="weVariant">
        <div style="display:grid;grid-template-columns:200px 1fr;gap:20px;margin-bottom:16px">
          <div>
            <label class="form-label" style="font-size:12px;font-weight:600;display:block;margin-bottom:6px">Hình ảnh preview</label>
            <label style="display:block;height:140px;background:#f1f5f9;border:1px dashed var(--border);border-radius:8px;cursor:pointer;overflow:hidden;display:flex;align-items:center;justify-content:center">
              <img id="weImgPreview" src="" alt="" style="max-width:100%;max-height:140px;object-fit:contain;display:none">
              <span id="weImgPlaceholder" style="font-size:12px;color:#94a3b8;text-align:center;padding:8px">Chưa có ảnh<br><small>Click để chọn</small></span>
              <input type="file" name="widget_image" id="weImgInput" accept="image/*" hidden>
            </label>
          </div>
          <div style="display:flex;flex-direction:column;gap:12px">
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;font-weight:600">Tên widget (key)</label>
              <input type="text" id="weKey" class="form-control" disabled style="background:#f8fafc;font-family:monospace">
            </div>
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;font-weight:600">Nhãn hiển thị</label>
              <input type="text" name="widget_label" id="weLabel" class="form-control" placeholder="Tên hiển thị trong danh sách">
            </div>
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;font-weight:600">Vùng cho phép</label>
              <div id="weZones" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px">
                @foreach($zones as $zoneKey => $zoneName)
                  <label style="display:flex;align-items:center;gap:4px;font-size:12px;font-weight:400;cursor:pointer">
                    <input type="checkbox" name="widget_zones[]" value="{{ $zoneKey }}" class="we-zone-check"> {{ $zoneName }}
                  </label>
                @endforeach
              </div>
            </div>
          </div>
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:12px;font-weight:600">Mã nguồn widget</label>
          <textarea name="code" id="weCode" class="form-control" rows="14" style="font-family:monospace;font-size:12px;line-height:1.6;resize:vertical;margin-top:4px"></textarea>
        </div>
        <div style="display:flex;gap:8px;margin-top:16px;justify-content:flex-end">
          <button type="button" class="btn btn-outline btn-sm" id="widgetEditModalCancel">Hủy</button>
          <button type="submit" class="btn btn-primary btn-sm">Lưu</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

{{-- Block edit modal --}}
<div class="block-edit-modal" id="blockEditModal" hidden>
  <div class="block-edit-modal-box">
    <div class="block-edit-modal-header">
      <span id="blockEditModalTitle">Cấu hình khối nội dung</span>
      <button type="button" class="block-edit-modal-close" id="blockEditModalClose" aria-label="Đóng">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="block-edit-modal-body" id="blockEditModalBody"></div>
  </div>
</div>

{{-- Quick-add modal --}}
<div class="widget-quickadd" id="widgetQuickAdd" hidden>
  <div class="widget-quickadd-box">
    <div class="widget-quickadd-title" id="widgetQuickAddTitle">Thêm khối nội dung</div>
    <form id="widgetQuickAddForm" action="{{ route('admin.homepage-layout.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="block_type" id="widgetQuickAddType">
      <input type="hidden" name="sort_order" id="widgetQuickAddSortOrder">
      <input type="hidden" name="insert_after_id" id="widgetQuickAddInsertAfter">
      <input type="hidden" name="insert_before_id" id="widgetQuickAddInsertBefore">
      <div class="form-group">
        <label>Vùng hiển thị</label>
        <select name="zone" class="form-control" required>
          @foreach($zones as $key => $name)
            <option value="{{ $key }}">{{ $name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Kiểu</label>
        <input type="text" name="style_key" class="form-control" placeholder="style-1">
      </div>
      <div class="form-group">
        <label>Tên hiển thị</label>
        <input type="text" name="title" class="form-control" placeholder="Tên hiển thị">
      </div>
      <div class="banner-settings" id="quickAddProductListSettings" data-quick-settings hidden>
        <div class="form-group"><label>Đường dẫn cho tiêu đề</label><input type="text" name="settings[title_url]" class="form-control"></div>
        <div class="form-group"><label>Tiêu đề phụ</label><textarea name="settings[subtitle]" class="form-control" rows="3"></textarea></div>
        <div class="form-group">
          <label>Thể loại</label>
          <div class="widget-category-checklist">
            @foreach($categories->whereNull('parent_id') as $parent)
              <div class="widget-category-group">
                <label class="widget-category-option widget-category-parent-option">
                  <input type="checkbox" name="settings[category_ids][]" value="{{ $parent->id }}">
                  <span>{{ $parent->name }}</span>
                </label>
                @foreach($categories->where('parent_id', $parent->id) as $child)
                  <label class="widget-category-option widget-category-child-option">
                    <input type="checkbox" name="settings[category_ids][]" value="{{ $child->id }}">
                    <span>{{ $child->name }}</span>
                  </label>
                @endforeach
              </div>
            @endforeach
          </div>
        </div>
        <label class="form-check"><input type="hidden" name="settings[show_children]" value="0"><input type="checkbox" name="settings[show_children]" value="1" checked> Hiển thị tất cả thể loại con</label>
        <label class="form-check"><input type="hidden" name="settings[group_by_category]" value="0"><input type="checkbox" name="settings[group_by_category]" value="1" checked> Phân loại sản phẩm theo từng danh mục</label>
        <label class="form-check"><input type="hidden" name="settings[show_all_tab]" value="0"><input type="checkbox" name="settings[show_all_tab]" value="1"> Hiện tab Tất cả</label>
        <div class="form-group"><label>Giới hạn số lượng từng thể loại (Mặc định là 6)</label><input type="number" min="1" max="30" name="settings[limit_per_category]" value="6" class="form-control"></div>
        <label class="form-check"><input type="hidden" name="settings[autoplay]" value="0"><input type="checkbox" name="settings[autoplay]" value="1"> Tự động chạy slider</label>
        <div class="form-group"><label>Số lượng trên 1 dòng (Desktop)</label><select name="settings[desktop_items]" class="form-control">@foreach([1,2,3,4,5,6] as $num)<option value="{{ $num }}" @selected($num === 4)>{{ $num }}</option>@endforeach</select></div>
        <div class="form-group"><label>Số lượng trên 1 dòng (Mobile)</label><select name="settings[mobile_items]" class="form-control">@foreach([1,2,3] as $num)<option value="{{ $num }}" @selected($num === 2)>{{ $num }}</option>@endforeach</select></div>
        <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show">Hiển thị</option><option value="hide">Ẩn</option></select></div></div>
      </div>
      <div class="banner-settings" id="quickAddArticleCategorySettings" data-quick-settings hidden>
        <div class="form-group"><label>Đường dẫn cho tiêu đề</label><input type="text" name="settings[title_url]" class="form-control"></div>
        <div class="form-group"><label>Tiêu đề phụ</label><textarea name="settings[subtitle]" class="form-control" rows="3"></textarea></div>
        <div class="form-group">
          <label>Thể loại</label>
          <div class="widget-category-checklist">
            @foreach($categories->whereNull('parent_id') as $parent)
              <div class="widget-category-group">
                <label class="widget-category-option widget-category-parent-option">
                  <input type="checkbox" name="settings[category_ids][]" value="{{ $parent->id }}">
                  <span>{{ $parent->name }}</span>
                </label>
                @foreach($categories->where('parent_id', $parent->id) as $child)
                  <label class="widget-category-option widget-category-child-option">
                    <input type="checkbox" name="settings[category_ids][]" value="{{ $child->id }}">
                    <span>{{ $child->name }}</span>
                  </label>
                @endforeach
              </div>
            @endforeach
          </div>
        </div>
        <label class="form-check"><input type="hidden" name="settings[show_children]" value="0"><input type="checkbox" name="settings[show_children]" value="1" checked> Hiển thị tất cả thể loại con</label>
        <label class="form-check"><input type="hidden" name="settings[group_by_category]" value="0"><input type="checkbox" name="settings[group_by_category]" value="1" checked> Phân loại sản phẩm theo từng danh mục</label>
        <label class="form-check"><input type="hidden" name="settings[show_all_tab]" value="0"><input type="checkbox" name="settings[show_all_tab]" value="1"> Hiện tab Tất cả</label>
        <div class="form-group"><label>Giới hạn số lượng từng thể loại (Mặc định là 6)</label><input type="number" min="1" max="30" name="settings[limit_per_category]" value="6" class="form-control"></div>
        <label class="form-check"><input type="hidden" name="settings[autoplay]" value="0"><input type="checkbox" name="settings[autoplay]" value="1"> Tự động chạy slider</label>
        <div class="form-group"><label>Số lượng trên 1 dòng (Desktop)</label><select name="settings[desktop_items]" class="form-control">@foreach([1,2,3,4,5,6] as $num)<option value="{{ $num }}" @selected($num === 4)>{{ $num }}</option>@endforeach</select></div>
        <div class="form-group"><label>Số lượng trên 1 dòng (Mobile)</label><select name="settings[mobile_items]" class="form-control">@foreach([1,2,3] as $num)<option value="{{ $num }}" @selected($num === 2)>{{ $num }}</option>@endforeach</select></div>
        <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show">Hiển thị</option><option value="hide">Ẩn</option></select></div></div>
      </div>
      <div class="banner-settings" id="quickAddServiceBlockSettings" data-quick-settings hidden>
        <div class="form-group"><label>Tiêu đề</label><input type="text" name="settings[title]" value="Khối dịch vụ" class="form-control"></div>
        <div class="form-group"><label>Đường dẫn cho tiêu đề</label><input type="text" name="settings[title_url]" class="form-control"></div>
        <div class="form-group"><label>Tiêu đề phụ</label><textarea name="settings[subtitle]" class="form-control" rows="3"></textarea></div>
        <div class="banner-content-config">
          <div class="banner-content-title"><span>▤</span><b>NGUỒN NỘI DUNG</b></div>
          <div class="form-row-2">
            <div class="form-group">
              <label>Chế độ dữ liệu</label>
              <select name="settings[content_source]" class="form-control" data-source-mode>
                <option value="manual" selected>Cấu hình tay</option>
                <option value="import">Import từ dữ liệu có sẵn</option>
              </select>
            </div>
            <div class="form-group">
              <label>Nguồn import</label>
              <select name="settings[source_type]" class="form-control" data-source-import-field>
                <option value="product">Sản phẩm</option>
                <option value="service" selected>Dịch vụ</option>
                <option value="news">Tin tức</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Số lượng lấy về</label>
            <input type="number" min="1" max="12" name="settings[source_limit]" value="6" class="form-control" data-source-import-field>
          </div>
        </div>
        <div class="form-row-2">
          <div class="form-group"><label>Kiểu bố cục</label><select name="settings[layout]" class="form-control"><option value="image_top">Ảnh trên - icon dưới</option><option value="image_card">Card ảnh</option><option value="product_cards">Card ảnh trên - chữ dưới</option><option value="icons_only">Chỉ icon + nội dung</option><option value="icon_left">Icon trái - nội dung phải</option><option value="icon_card">Card icon nổi</option><option value="icon_line">Icon nằm trên đường kẻ</option><option value="number_icon">Số thứ tự + icon</option><option value="image_side">Danh sách + ảnh bên cạnh</option><option value="image_diamond">Danh sách + ảnh hình thoi</option><option value="overlay">Ảnh nền phủ màu</option><option value="color_blocks">Các ô màu / ảnh nền</option><option value="center_image_features">Ảnh giữa - nội dung 2 bên</option><option value="simple_horizontal">Hàng ngang đơn giản</option></select></div>
          <div class="form-group"><label>Icon nằm ở vị trí</label><select name="settings[icon_position]" class="form-control"><option value="top">Trên nội dung</option><option value="line">Nằm trên đường kẻ</option></select></div>
        </div>
        <div class="form-row-2">
          <div class="form-group"><label>Số item / dòng Desktop</label><select name="settings[desktop_items]" class="form-control">@foreach([1,2,3,4,5,6] as $num)<option value="{{ $num }}" @selected($num === 3)>{{ $num }}</option>@endforeach</select></div>
          <div class="form-group"><label>Số item / dòng Mobile</label><select name="settings[mobile_items]" class="form-control">@foreach([1,2,3] as $num)<option value="{{ $num }}" @selected($num === 1)>{{ $num }}</option>@endforeach</select></div>
        </div>
        <div class="form-row-2">
          <div class="form-group"><label>Dạng icon</label><select name="settings[icon_shape]" class="form-control"><option value="circle">Tròn</option><option value="square">Vuông bo góc</option><option value="none">Không nền</option></select></div>
          <div class="form-group"><label>Vị trí ảnh lớn (bố cục danh sách)</label><select name="settings[image_position]" class="form-control"><option value="right">Bên phải</option><option value="left">Bên trái</option></select></div>
        </div>
        <div class="form-row-2">
          <div class="form-group"><label>Màu icon / nhấn</label><input type="color" name="settings[icon_color]" value="#1683f7" class="form-control"></div>
          <div class="form-group"><label>Màu nền khối</label><input type="color" name="settings[background_color]" value="#ffffff" class="form-control"></div>
        </div>
        <div class="form-row-3">
          <div class="form-group"><label>Màu tiêu đề item</label><input type="color" name="settings[title_color]" value="#1f2937" class="form-control"></div>
          <div class="form-group"><label>Màu mô tả</label><input type="color" name="settings[text_color]" value="#6b7280" class="form-control"></div>
          <div class="form-group"><label>Màu đường kẻ</label><input type="color" name="settings[line_color]" value="#cfe3ff" class="form-control"></div>
        </div>
        <div class="form-group"><label>Ảnh lớn bên cạnh (dùng cho bố cục danh sách)</label><input type="file" name="settings[side_image]" accept="image/*" class="form-control"></div>
        <div data-source-manual>
        <div class="service-block-items" data-service-block-items></div>
        <button type="button" class="btn btn-primary btn-sm" data-add-service-item>+ Thêm item</button>
        <template data-service-item-template>
          <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
            <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> ITEM __NUMBER__</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
            <div class="form-row-2"><div class="form-group"><label>Tiêu đề</label><input type="text" name="settings[items][__INDEX__][title]" class="form-control"></div><div class="form-group"><label>Đường dẫn</label><input type="text" name="settings[items][__INDEX__][url]" class="form-control"></div></div>
            <div class="form-group"><label>Mô tả phụ</label><input type="text" name="settings[items][__INDEX__][description]" class="form-control"></div>
            <input type="hidden" name="settings[items][__INDEX__][existing_image]" value="">
            <input type="hidden" name="settings[items][__INDEX__][existing_icon_image]" value="">
            <div class="form-row-2"><div class="form-group"><label>Hình ảnh item</label><input type="file" name="settings[items][__INDEX__][image]" accept="image/*" class="form-control"></div><div class="form-group"><label>Hình icon riêng</label><input type="file" name="settings[items][__INDEX__][icon_image]" accept="image/*" class="form-control"></div></div>
          </div>
        </template>
        </div>
        <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show">Hiển thị</option><option value="hide">Ẩn</option></select></div></div>
      </div>
      <div class="banner-settings" id="quickAddPartnersSettings" data-quick-settings hidden>
        <div class="form-group">
          <label>Tiêu đề phụ</label>
          <input type="text" name="settings[subtitle]" class="form-control">
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label>Kiểu hiển thị</label>
            <select name="settings[layout]" class="form-control">
              <option value="marquee" selected>Marquee (cuộn ngang)</option>
              <option value="grid">Lưới</option>
            </select>
          </div>
          <div class="form-group">
            <label>Số cột (lưới)</label>
            <select name="settings[desktop_cols]" class="form-control">
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5" selected>5</option>
              <option value="6">6</option>
            </select>
          </div>
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label>Tốc độ cuộn (10–100)</label>
            <input type="number" min="10" max="100" name="settings[speed]" value="40" class="form-control">
          </div>
          <div class="form-group">
            <label>Màu nền khối</label>
            <input type="color" name="settings[bg_color]" value="#ffffff" class="form-control">
          </div>
        </div>
        <label class="form-check"><input type="hidden" name="settings[autoplay]" value="0"><input type="checkbox" name="settings[autoplay]" value="1" checked> Tự động cuộn</label>
        <label class="form-check"><input type="hidden" name="settings[show_name]" value="0"><input type="checkbox" name="settings[show_name]" value="1"> Hiển thị tên đối tác</label>
        <div class="service-block-items" data-service-block-items></div>
        <button type="button" class="btn btn-primary btn-sm" data-add-service-item>+ Thêm đối tác</button>
        <template data-service-item-template>
          <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
            <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center">
              <span><span>▤</span><b data-service-item-title> ĐỐI TÁC __NUMBER__</b></span>
              <button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button>
            </div>
            <div class="form-row-2">
              <div class="form-group"><label>Tên đối tác</label><input type="text" name="settings[items][__INDEX__][name]" class="form-control"></div>
              <div class="form-group"><label>Đường dẫn (URL)</label><input type="text" name="settings[items][__INDEX__][url]" class="form-control" placeholder="https://..."></div>
            </div>
            <div class="form-group">
              <label>Logo</label>
              <input type="hidden" name="settings[items][__INDEX__][existing_logo]" value="">
              <input type="file" name="settings[items][__INDEX__][logo]" accept="image/*" class="form-control">
            </div>
          </div>
        </template>
        <div class="banner-content-config">
          <div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div>
          <div class="form-group">
            <label>Chọn kiểu hiển thị widget</label>
            <select name="settings[display_mode]" class="form-control">
              <option value="show">Hiển thị</option>
              <option value="hide">Ẩn</option>
            </select>
          </div>
        </div>
      </div>
      <div class="banner-settings" id="quickAddCustomerTestimonialsSettings" data-quick-settings hidden>
        <div class="banner-content-config" data-source-import>
          <div class="banner-content-title"><span>▤</span><b>NGUỒN NỘI DUNG</b></div>
          <div class="form-row-2">
            <div class="form-group">
              <label>Chế độ dữ liệu</label>
              <select name="settings[content_source]" class="form-control" data-source-mode>
                <option value="manual" selected>Nhập tay</option>
                <option value="import">Lấy từ danh sách đánh giá</option>
              </select>
            </div>
            <div class="form-group">
              <label>Số lượng lấy về</label>
              <input type="number" min="1" max="50" name="settings[source_limit]" value="6" class="form-control" data-source-import-field>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Tiêu đề phụ</label>
          <textarea name="settings[subtitle]" class="form-control" rows="2"></textarea>
        </div>
        <div class="banner-content-config">
          <div class="banner-content-title"><span>▤</span><b>BỐ CỤC HIỂN THỊ</b></div>
          <div class="form-row-2">
            <div class="form-group">
              <label>Kiểu bố cục</label>
              <select name="settings[layout]" class="form-control">
                <option value="slider" selected>Slider (cuộn ngang)</option>
                <option value="grid">Lưới (grid)</option>
                <option value="card_minimal">Card tối giản</option>
                <option value="bubble">Khung trích dẫn (bubble)</option>
                <option value="centered">Một item nổi bật giữa</option>
                <option value="compact">Hàng ngang gọn</option>
                <option value="featured">Featured + danh sách phụ</option>
              </select>
            </div>
            <div class="form-group">
              <label>Vị trí sao đánh giá</label>
              <select name="settings[star_position]" class="form-control">
                <option value="top">Trên cùng</option>
                <option value="after_name">Sau tên</option>
                <option value="before_content" selected>Trước nội dung</option>
                <option value="bottom">Cuối card</option>
                <option value="hide">Ẩn</option>
              </select>
            </div>
          </div>
          <div class="form-row-3">
            <div class="form-group">
              <label>Số cột Desktop</label>
              <select name="settings[desktop_cols]" class="form-control">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3" selected>3</option>
                <option value="4">4</option>
              </select>
            </div>
            <div class="form-group">
              <label>Số cột Mobile</label>
              <select name="settings[mobile_cols]" class="form-control">
                <option value="1" selected>1</option>
                <option value="2">2</option>
              </select>
            </div>
            <div class="form-group">
              <label>Khoảng cách (px)</label>
              <input type="number" min="0" max="80" name="settings[gap]" value="20" class="form-control">
            </div>
          </div>
          <div class="form-row-2">
            <div class="form-group">
              <label>Chiều rộng tối đa (px)</label>
              <input type="number" min="400" max="1600" name="settings[max_width]" value="960" class="form-control">
            </div>
            <div class="form-group">
              <label>Padding section (px)</label>
              <input type="number" min="0" max="200" name="settings[section_padding]" value="64" class="form-control">
            </div>
          </div>
          <div class="form-row-2">
            <label class="form-check"><input type="hidden" name="settings[autoplay]" value="0"><input type="checkbox" name="settings[autoplay]" value="1"> Tự động chuyển slide</label>
            <div class="form-group">
              <label>Tốc độ tự chuyển (giây)</label>
              <input type="number" min="1" max="20" name="settings[autoplay_speed]" value="5" class="form-control">
            </div>
          </div>
        </div>
        <div class="banner-content-config">
          <div class="banner-content-title"><span>▤</span><b>KÍCH THƯỚC & MÀU SẮC</b></div>
          <div class="form-row-3">
            <div class="form-group">
              <label>Cỡ tiêu đề (px)</label>
              <input type="number" min="14" max="60" name="settings[heading_size]" value="30" class="form-control">
            </div>
            <div class="form-group">
              <label>Cỡ tên (px)</label>
              <input type="number" min="10" max="32" name="settings[title_size]" value="15" class="form-control">
            </div>
            <div class="form-group">
              <label>Cỡ nội dung (px)</label>
              <input type="number" min="10" max="24" name="settings[content_size]" value="14" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label>Giới hạn dòng nội dung (0 = không giới hạn)</label>
            <input type="number" min="0" max="30" name="settings[content_lines]" value="6" class="form-control">
          </div>
          <div class="form-row-3">
            <div class="form-group">
              <label>Avatar (px)</label>
              <input type="number" min="32" max="160" name="settings[avatar_size]" value="80" class="form-control">
            </div>
            <div class="form-group">
              <label>Bo góc card (px)</label>
              <input type="number" min="0" max="48" name="settings[card_radius]" value="16" class="form-control">
            </div>
            <div class="form-group">
              <label>Padding card (px)</label>
              <input type="number" min="8" max="60" name="settings[card_padding]" value="28" class="form-control">
            </div>
          </div>
          <div class="form-row-3">
            <div class="form-group">
              <label>Màu nền section</label>
              <input type="color" name="settings[bg_color]" value="#f8fafc" class="form-control">
            </div>
            <div class="form-group">
              <label>Màu nền card</label>
              <input type="color" name="settings[card_bg]" value="#ffffff" class="form-control">
            </div>
            <div class="form-group">
              <label>Màu nhấn</label>
              <input type="color" name="settings[accent_color]" value="#1683f7" class="form-control">
            </div>
          </div>
          <div class="form-row-2">
            <div class="form-group">
              <label>Màu tên</label>
              <input type="color" name="settings[title_color]" value="#1a2340" class="form-control">
            </div>
            <div class="form-group">
              <label>Màu nội dung</label>
              <input type="color" name="settings[text_color]" value="#4b5563" class="form-control">
            </div>
          </div>
        </div>
        <div class="banner-content-config">
          <div class="banner-content-title"><span>▤</span><b>THÔNG TIN HIỂN THỊ</b></div>
          <label class="form-check"><input type="hidden" name="settings[show_role]" value="0"><input type="checkbox" name="settings[show_role]" value="1" checked> Hiển thị chức vụ</label>
          <label class="form-check"><input type="hidden" name="settings[show_company]" value="0"><input type="checkbox" name="settings[show_company]" value="1" checked> Hiển thị công ty</label>
          <label class="form-check"><input type="hidden" name="settings[show_quote]" value="0"><input type="checkbox" name="settings[show_quote]" value="1"> Hiển thị icon dấu nháy</label>
        </div>
        <div class="service-block-items" data-service-block-items data-source-manual></div>
        <button type="button" class="btn btn-primary btn-sm" data-add-service-item data-source-manual>+ Thêm khách hàng</button>
        <template data-service-item-template>
          <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
            <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center">
              <span><span>▤</span><b data-service-item-title> KHÁCH HÀNG __NUMBER__</b></span>
              <button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button>
            </div>
            <div class="form-row-2">
              <div class="form-group"><label>Họ tên</label><input type="text" name="settings[items][__INDEX__][name]" class="form-control"></div>
              <div class="form-group"><label>Chức vụ</label><input type="text" name="settings[items][__INDEX__][role]" class="form-control"></div>
            </div>
            <div class="form-group"><label>Công ty</label><input type="text" name="settings[items][__INDEX__][company]" class="form-control"></div>
            <div class="form-group"><label>Nội dung cảm nhận</label><textarea name="settings[items][__INDEX__][content]" class="form-control" rows="3"></textarea></div>
            <div class="form-row-2">
              <div class="form-group">
                <label>Số sao</label>
                <select name="settings[items][__INDEX__][rating]" class="form-control">
                  <option value="5" selected>5 sao</option>
                  <option value="4">4 sao</option>
                  <option value="3">3 sao</option>
                  <option value="2">2 sao</option>
                  <option value="1">1 sao</option>
                </select>
              </div>
              <div class="form-group">
                <label>Ảnh đại diện</label>
                <input type="hidden" name="settings[items][__INDEX__][existing_avatar]" value="">
                <input type="file" name="settings[items][__INDEX__][avatar]" accept="image/*" class="form-control">
              </div>
            </div>
          </div>
        </template>
        <div class="banner-content-config">
          <div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div>
          <div class="form-group">
            <label>Chọn kiểu hiển thị widget</label>
            <select name="settings[display_mode]" class="form-control">
              <option value="show">Hiển thị</option>
              <option value="hide">Ẩn</option>
            </select>
          </div>
        </div>
      </div>
      <div class="banner-settings" id="quickAddContactFormSettings" data-quick-settings hidden>
        <div class="form-group">
          <label>Đường dẫn cho tiêu đề</label>
          <input type="text" name="settings[title_url]" class="form-control">
        </div>
        <div class="form-group">
          <label>Tiêu đề phụ</label>
          <textarea name="settings[subtitle]" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label>Tiêu đề email nhận được (subject)</label>
          <input type="text" name="settings[subject]" value="Liên hệ từ trang chủ" class="form-control">
        </div>
        <div class="form-group">
          <label>Văn bản nút gửi</label>
          <input type="text" name="settings[btn_text]" value="Gửi liên hệ" class="form-control">
        </div>
        <div class="form-group">
          <label>Kiểu bố cục</label>
          <select name="settings[layout]" class="form-control">
            <option value="two_col">2 cột — thông tin + form</option>
            <option value="form_only">Chỉ form</option>
            <option value="form_center">Form giữa trang</option>
          </select>
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label>Màu nền khối</label>
            <input type="color" name="settings[bg_color]" value="#f8fafc" class="form-control">
          </div>
          <div class="form-group">
            <label>Màu nhấn (nút, icon)</label>
            <input type="color" name="settings[accent_color]" value="#1683f7" class="form-control">
          </div>
        </div>
        <div class="banner-content-config">
          <div class="banner-content-title"><span>▤</span><b>THÔNG TIN LIÊN HỆ HIỂN THỊ</b></div>
          <label class="form-check"><input type="hidden" name="settings[show_phone]" value="0"><input type="checkbox" name="settings[show_phone]" value="1" checked> Hiển thị số điện thoại</label>
          <label class="form-check"><input type="hidden" name="settings[show_email]" value="0"><input type="checkbox" name="settings[show_email]" value="1" checked> Hiển thị email</label>
          <label class="form-check"><input type="hidden" name="settings[show_address]" value="0"><input type="checkbox" name="settings[show_address]" value="1" checked> Hiển thị địa chỉ</label>
        </div>
        <div class="banner-content-config">
          <div class="banner-content-title"><span>▤</span><b>CẤU HÌNH TRƯỜNG FORM</b></div>
          <label class="form-check"><input type="hidden" name="settings[req_phone]" value="0"><input type="checkbox" name="settings[req_phone]" value="1" checked> Số điện thoại bắt buộc</label>
          <label class="form-check"><input type="hidden" name="settings[req_email]" value="0"><input type="checkbox" name="settings[req_email]" value="1"> Email bắt buộc</label>
          <label class="form-check"><input type="hidden" name="settings[show_company]" value="0"><input type="checkbox" name="settings[show_company]" value="1"> Hiển thị trường Công ty</label>
        </div>
        <div class="banner-content-config">
          <div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div>
          <div class="form-group">
            <label>Chọn kiểu hiển thị widget</label>
            <select name="settings[display_mode]" class="form-control">
              <option value="show">Hiển thị</option>
              <option value="hide">Ẩn</option>
            </select>
          </div>
        </div>
      </div>
      <div class="banner-settings" id="quickAddBannerSettings" data-quick-settings hidden>
        <div class="banner-content-config">
          <div class="banner-content-title"><span>▤</span><b>NGUỒN NỘI DUNG</b></div>
          <div class="form-row-2">
            <div class="form-group">
              <label>Chế độ dữ liệu</label>
              <select name="settings[content_source]" class="form-control" data-source-mode>
                <option value="manual" selected>Cấu hình tay</option>
                <option value="import">Import từ dữ liệu có sẵn</option>
              </select>
            </div>
            <div class="form-group">
              <label>Nguồn import</label>
              <select name="settings[source_type]" class="form-control" data-source-import-field>
                <option value="product" selected>Sản phẩm</option>
                <option value="service">Dịch vụ</option>
                <option value="news">Tin tức</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Số lượng lấy về</label>
            <input type="number" min="1" max="1" name="settings[source_limit]" value="1" class="form-control" data-source-import-field>
          </div>
        </div>
        <div data-source-manual>
        <label class="banner-image-preview">
          <span>Chưa có ảnh</span>
          <input type="file" name="settings[image]" accept="image/*" hidden data-banner-image-input>
        </label>
        <div class="form-row-2">
          <div class="form-group">
            <label>Chiều rộng hình ảnh</label>
            <div class="input-unit">
              <input type="number" min="0" name="settings[width]" value="0" class="form-control">
              <span>px</span>
            </div>
          </div>
          <div class="form-group">
            <label>Chiều cao hình ảnh</label>
            <div class="input-unit">
              <input type="number" min="0" name="settings[height]" value="0" class="form-control">
              <span>px</span>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Bo góc hình ảnh</label>
          <div class="input-unit">
            <input type="number" min="0" name="settings[radius]" value="0" class="form-control">
            <span>px</span>
          </div>
        </div>
        <label class="form-check">
          <input type="hidden" name="settings[fullscreen]" value="0">
          <input type="checkbox" name="settings[fullscreen]" value="1"> Tràn màn hình
        </label>
        </div>
        <div class="banner-content-config">
          <div class="banner-content-title">
            <span>▤</span>
            <b>CẤU HÌNH KHỐI NỘI DUNG</b>
          </div>
          <div class="form-group">
            <label>Chọn kiểu hiển thị widget</label>
            <select name="settings[display_mode]" class="form-control">
              <option value="show">Hiển thị</option>
              <option value="hide">Ẩn</option>
            </select>
          </div>
        </div>
      </div>
      <div class="banner-settings" id="quickAddBanner8Settings" data-quick-settings hidden>
        <div class="form-group"><label>Đường dẫn cho tiêu đề</label><input type="text" name="settings[title_url]" class="form-control"></div>
        <div class="form-row-2">
          <div class="form-group"><label>Kích thước tên hiển thị (px)</label><input type="number" min="10" max="72" name="settings[title_size]" value="16" class="form-control"></div>
          <div class="form-group"><label>Vị trí tên hiển thị</label><select name="settings[title_align]" class="form-control"><option value="left">Trái</option><option value="center" selected>Giữa</option><option value="right">Phải</option></select></div>
        </div>
        <div class="form-group"><label>Tiêu đề phụ</label><textarea name="settings[subtitle]" class="form-control" rows="3"></textarea></div>
        <div class="banner-content-config">
          <div class="banner-content-title"><span>▤</span><b>NGUỒN NỘI DUNG</b></div>
          <div class="form-row-2">
            <div class="form-group">
              <label>Chế độ dữ liệu</label>
              <select name="settings[content_source]" class="form-control" data-source-mode>
                <option value="manual" selected>Cấu hình tay</option>
                <option value="import">Import từ dữ liệu có sẵn</option>
              </select>
            </div>
            <div class="form-group">
              <label>Nguồn import</label>
              <select name="settings[source_type]" class="form-control" data-source-import-field>
                <option value="product" selected>Sản phẩm</option>
                <option value="service">Dịch vụ</option>
                <option value="news">Tin tức</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Số lượng lấy về</label>
            <input type="number" min="1" max="6" name="settings[source_limit]" value="6" class="form-control" data-source-import-field>
          </div>
        </div>
        <div data-source-manual>
        <div class="banner8-images">
          @for($i = 0; $i < 6; $i++)
            <label class="banner8-image-slot">
              <span>Chưa có ảnh</span>
              <input type="file" name="settings[images][{{ $i }}]" accept="image/*" hidden data-banner-image-input>
            </label>
          @endfor
        </div>
        <div class="form-row-2">
          <div class="form-group"><label>Chiều rộng hình ảnh</label><div class="input-unit"><input type="number" min="0" name="settings[width]" value="0" class="form-control"><span>px</span></div></div>
          <div class="form-group"><label>Chiều cao hình ảnh</label><div class="input-unit"><input type="number" min="0" name="settings[height]" value="0" class="form-control"><span>px</span></div></div>
        </div>
        </div>
        <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show">Hiển thị</option><option value="hide">Ẩn</option></select></div></div>
      </div>
      <div class="form-actions">
        <button type="button" class="btn btn-outline" id="widgetQuickAddCancel">Hủy</button>
        <button type="submit" class="btn btn-primary">Thêm</button>
      </div>
    </form>
  </div>
</div>

@if($isSuperAdmin)
{{-- Add widget type modal (super admin only) --}}
<div class="widget-quickadd" id="addWidgetModal" hidden>
  <div class="widget-quickadd-box" style="max-width:560px;max-height:90vh;overflow-y:auto">
    <div class="widget-quickadd-title">Thêm loại widget mới</div>
    <form id="addWidgetForm" action="{{ route('admin.homepage-layout.widget-store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="form-row">
        <div class="form-group">
          <label>Tên widget (key) <span class="req">*</span></label>
          <input type="text" name="widget_key" class="form-control" placeholder="vd: countdown-timer" required pattern="[a-z0-9\-]+" title="Chỉ dùng chữ thường, số và dấu -">
          <div class="form-help">Chỉ dùng chữ thường, số và dấu -</div>
        </div>
        <div class="form-group">
          <label>Nhãn hiển thị <span class="req">*</span></label>
          <input type="text" name="widget_label" class="form-control" placeholder="vd: Đếm ngược thời gian" required>
        </div>
      </div>
      <div class="form-group">
        <label>Vùng cho phép <span class="req">*</span></label>
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px">
          @foreach($zones as $zKey => $zName)
            <label class="form-check" style="font-weight:400;cursor:pointer">
              <input type="checkbox" name="widget_zones[]" value="{{ $zKey }}"> {{ $zName }}
            </label>
          @endforeach
        </div>
      </div>
      <div class="form-group">
        <label>Hình ảnh widget (có thể chọn nhiều)</label>
        <input type="file" name="widget_images[]" class="form-control" accept="image/*" multiple>
      </div>
      <div class="form-group">
        <label>Mã nguồn widget (Blade)</label>
        <textarea name="widget_code" class="form-control" rows="6" placeholder="@php&#10;  // widget code here&#10;@endphp&#10;<section>...</section>"></textarea>
      </div>
      <div class="form-actions" style="margin-top:16px">
        <button type="button" class="btn btn-outline" id="addWidgetModalCancel">Hủy</button>
        <button type="submit" class="btn btn-primary">Lưu widget</button>
      </div>
    </form>
  </div>
</div>
@endif
@endsection

@push('css')
<style>
.layout-actions{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.layout-toast{position:fixed;right:24px;bottom:24px;z-index:9999;display:flex;align-items:center;gap:10px;max-width:320px;padding:12px 16px;background:#cf5c58;color:#fff;border-radius:8px;font-size:13px;box-shadow:0 10px 24px rgba(15,23,42,.2);opacity:0;transform:translateY(12px);pointer-events:none;transition:.2s}
.layout-toast.show{opacity:1;transform:translateY(0)}
.layout-toast span{display:flex}

/* Page layout — flex row so panel sits beside zones */
.layout-page{display:flex;gap:20px;align-items:flex-start}
.homepage-layout{flex:1;min-width:0;display:flex;flex-direction:column;gap:20px}
.layout-main-grid{display:grid;grid-template-columns:1.35fr 1fr;gap:20px}
.layout-footer-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.layout-zone{background:#fff;border:1px solid var(--border);border-radius:8px;min-height:260px;box-shadow:var(--shadow)}
.layout-zone-full{min-height:auto}
.layout-zone-tall{min-height:200px}
.layout-zone h3{font-size:18px;font-weight:500;padding:16px 20px;border-bottom:1px solid var(--border)}
.layout-block-list{padding:20px;display:flex;flex-direction:column;gap:14px;min-height:90px}
.layout-block{border:1px solid var(--border);border-top:2px solid #22c55e;background:#fff;border-radius:4px;box-shadow:0 1px 2px rgba(0,0,0,.08);padding:14px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.layout-block.inactive{opacity:.55;border-top-color:var(--border)}
.layout-block-title{font-size:13px;line-height:1.5;flex:1}
.layout-block-title b{font-weight:600}
.drag-handle{cursor:grab;color:#94a3b8;flex-shrink:0;display:flex;align-items:center}
.drag-handle:active{cursor:grabbing}
.layout-block-ghost{opacity:.45;border:2px dashed #22c55e}
.layout-block-actions{display:flex;align-items:center;gap:8px;flex-shrink:0}
.layout-block-actions button{border:0;background:transparent;cursor:pointer;color:var(--text);padding:3px}
.layout-block-actions svg{width:18px;height:18px;display:block}
.layout-empty{color:var(--text-2);font-size:13px;padding:14px;border:1px dashed var(--border);border-radius:6px;text-align:center}
.layout-edit{display:none;padding:14px 20px 20px;border-top:1px solid var(--border);background:var(--bg-light)}
.layout-block.open + .layout-edit{display:block}
.layout-edit .form-actions{display:flex;gap:8px;align-items:center;justify-content:space-between}
.layout-delete{display:inline}
.form-row-2{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}
.banner-settings{margin:14px 0;display:flex;flex-direction:column;gap:14px}
.banner-settings[hidden]{display:none}
.banner-image-preview{height:260px;background:#ddd;color:#999;font-size:48px;font-weight:600;display:flex;align-items:center;justify-content:center;cursor:pointer;overflow:hidden}
.banner-image-preview img{width:100%;height:100%;object-fit:cover;display:block}
.input-unit{display:flex}
.input-unit .form-control{border-radius:4px 0 0 4px}
.input-unit span{display:flex;align-items:center;padding:0 12px;border:1px solid var(--border);border-left:0;border-radius:0 4px 4px 0;background:#fff;font-size:13px}
.banner-content-config{border-top:1px solid var(--border);padding-top:12px}
.banner-content-title{display:flex;align-items:center;gap:8px;font-size:12px;margin-bottom:16px;color:var(--text)}

/* Float left/right columns */
.layout-zone-float{flex:0 0 80px;min-width:80px;min-height:100%;background:#fff;border:1px solid var(--border);border-radius:8px;box-shadow:var(--shadow);align-self:stretch;overflow-y:auto;display:flex;flex-direction:column}
.layout-zone-float h3{font-size:11px;font-weight:600;padding:10px 6px;border-bottom:1px solid var(--border);text-align:center;letter-spacing:.5px;color:var(--text-muted,#6b7280);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.layout-zone-float .layout-block-list{padding:8px;gap:8px;min-height:60px}
.layout-zone-float .layout-block{flex-direction:column;align-items:center;padding:8px 4px;gap:4px}
.layout-zone-float .layout-block-title{font-size:10px;text-align:center;max-height:80px;overflow:hidden;text-overflow:ellipsis;writing-mode:horizontal-tb;transform:none}
.layout-zone-float .layout-block-actions{flex-direction:column}

/* Block edit modal */
.block-edit-modal{position:fixed;inset:0;z-index:1050;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.45);padding:24px}
.block-edit-modal[hidden]{display:none}
.block-edit-modal-box{background:#fff;border-radius:12px;width:100%;max-width:680px;max-height:calc(100vh - 48px);overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);display:flex;flex-direction:column}
.block-edit-modal-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);font-size:15px;font-weight:600;flex-shrink:0}
.block-edit-modal-close{background:transparent;border:none;cursor:pointer;color:#64748b;display:flex;padding:4px;border-radius:6px}
.block-edit-modal-close:hover{background:#f1f5f9;color:#0f172a}
.block-edit-modal-body{padding:0}
.block-edit-modal-body .layout-edit{display:block;border-top:none}

/* Widget side panel — 2/5 of page */
.widget-panel{flex:0 0 0;min-width:0;overflow:hidden;background:#1e293b;border-radius:10px;color:#fff;display:flex;flex-direction:column;transition:flex-basis .25s cubic-bezier(.4,0,.2,1),opacity .2s;opacity:0;position:sticky;top:20px;max-height:calc(100vh - 80px)}
.widget-panel.open{flex:0 0 40%;min-width:0;overflow:visible;opacity:1}
.widget-panel-header{display:flex;align-items:flex-start;justify-content:space-between;padding:16px 16px 10px;border-bottom:1px solid rgba(255,255,255,.08);white-space:nowrap}
.widget-panel-title{font-size:14px;font-weight:700}
.widget-panel-sub{font-size:11px;color:rgba(255,255,255,.5);margin-top:3px}
.widget-panel-close{background:transparent;border:none;color:rgba(255,255,255,.6);cursor:pointer;padding:2px;display:flex;border-radius:6px;flex-shrink:0}
.widget-panel-close:hover{color:#fff;background:rgba(255,255,255,.08)}
.widget-panel-search{margin:10px 12px;padding:8px 12px;border-radius:7px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06);color:#fff;font-size:12px;font-family:inherit;outline:none;width:calc(100% - 24px);white-space:nowrap}
.widget-panel-search::placeholder{color:rgba(255,255,255,.35)}
.widget-panel-search:focus{border-color:var(--primary)}
.widget-panel-body{display:flex;flex:1;overflow:hidden;min-height:0}
.widget-panel-cats{width:130px;flex-shrink:0;overflow-y:auto;border-right:1px solid rgba(255,255,255,.08);padding:6px 0}
.widget-cat{display:block;width:100%;text-align:left;padding:9px 14px;background:transparent;border:none;color:rgba(255,255,255,.65);font-size:13px;font-family:inherit;cursor:pointer;transition:.15s;line-height:1.35;word-break:break-word}
.widget-cat:hover{background:rgba(255,255,255,.06);color:#fff}
.widget-cat.active{background:rgba(249,115,22,.15);color:var(--primary);font-weight:600}
.widget-panel-list{flex:1;overflow-y:auto;padding:10px;display:flex;flex-direction:column;gap:8px}
.widget-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:8px;cursor:grab;position:relative;transition:.15s;overflow:hidden;display:flex;flex-direction:column;flex:0 0 auto}
.widget-card:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2)}
.widget-card:active{cursor:grabbing}
.widget-card-preview{width:100%;overflow:hidden;border-radius:6px 6px 0 0;background:#fff;border-bottom:1px solid rgba(255,255,255,.06)}
.widget-card-preview img{width:100%;height:auto;display:block}
.widget-card-preview.portrait{display:flex;align-items:center;justify-content:center;padding:8px}
.widget-card-preview.portrait img{width:auto;max-width:100%;max-height:120px;height:auto}
.widget-card-footer{display:flex;align-items:center;gap:8px;padding:9px 12px}
.widget-card-icon{color:rgba(255,255,255,.45);flex-shrink:0}
.widget-card-label{font-size:13px;font-weight:500;color:rgba(255,255,255,.9);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.widget-card-add{background:var(--primary);border:none;color:#fff;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;opacity:0;transition:.15s}
.widget-card-edit{background:rgba(255,255,255,.15);border:none;color:rgba(255,255,255,.8);border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;opacity:0;transition:.15s;text-decoration:none}
.widget-card:hover .widget-card-add,.widget-card:hover .widget-card-edit{opacity:1}
.widget-card-edit:hover{background:rgba(255,255,255,.3);color:#fff}
.widget-card[hidden]{display:none}
.widget-card-ghost{opacity:.35;background:rgba(255,255,255,.08) !important}
.layout-block-ghost{opacity:.45;border:2px dashed #22c55e;background:#f0fdf4 !important}
/* Drop highlight */
.layout-block-list.drag-over{background:rgba(34,197,94,.06);border-radius:6px;outline:2px dashed #22c55e}

/* Quick-add modal */
.widget-quickadd{position:fixed;inset:0;z-index:1100;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.45);padding:24px}
.widget-quickadd[hidden]{display:none}
.widget-quickadd-box{background:#fff;border-radius:12px;padding:24px;width:100%;max-width:720px;max-height:calc(100vh - 48px);overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.widget-quickadd-title{font-size:16px;font-weight:700;margin-bottom:16px}
.widget-quickadd-box .banner-settings{max-height:none;overflow:visible}
.widget-category-checklist{max-height:220px;overflow-y:auto;border:1px solid var(--border);border-radius:6px;background:#fff;padding:8px;display:flex;flex-direction:column;gap:8px}
.widget-category-group{border-bottom:1px dashed var(--border);padding-bottom:8px}.widget-category-group:last-child{border-bottom:0;padding-bottom:0}
.widget-category-option{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:400;cursor:pointer;line-height:1.35}
.widget-category-parent-option{font-weight:600}.widget-category-child-option{margin-left:24px;display:inline-flex;width:calc(50% - 28px);margin-top:6px}
.widget-category-option input{flex-shrink:0}
.banner8-images{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;margin:12px 0}
.banner8-image-slot{display:flex;align-items:center;justify-content:center;height:80px;background:#f1f5f9;border:1px dashed var(--border);border-radius:6px;cursor:pointer;overflow:hidden;font-size:11px;color:#94a3b8;text-align:center}
.banner8-image-slot img{width:100%;height:100%;object-fit:contain;display:block}

@media(max-width:1100px){.layout-main-grid,.layout-footer-grid{grid-template-columns:1fr}.widget-panel.open{width:280px}.layout-zone-float{display:none}}
@media(max-width:800px){.layout-page{flex-direction:column}.widget-panel.open{width:100%;position:static;max-height:400px}}
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
var reorderUrl = '{{ route('admin.homepage-layout.reorder') }}';
var storeUrl   = '{{ route('admin.homepage-layout.store') }}';
var csrfToken  = '{{ csrf_token() }}';
var allowedZones = @json($allowedZonesMap);
var layoutErrorTimer;
var dragSnapshot = null;

// ── Edit block modal ──────────────────────────────────
var blockEditModal     = document.getElementById('blockEditModal');
var blockEditModalBody = document.getElementById('blockEditModalBody');
var blockEditModalTitle = document.getElementById('blockEditModalTitle');

document.getElementById('layoutPage').addEventListener('click', function(e) {
  var btn = e.target.closest('[data-edit-block]');
  if (!btn) return;
  var block = btn.closest('.layout-block');
  var editPanel = block.nextElementSibling;
  if (!editPanel || !editPanel.classList.contains('layout-edit')) return;
  var titleEl = block.querySelector('.layout-block-title b');
  blockEditModalTitle.textContent = titleEl ? titleEl.textContent : 'Cấu hình khối nội dung';
  blockEditModalBody.innerHTML = '';
  blockEditModalBody.appendChild(editPanel.cloneNode(true));
  // Re-wire banner image previews in cloned content
  blockEditModalBody.querySelectorAll('[data-banner-image-input]').forEach(function(input) {
    input.addEventListener('change', function() {
      var file = this.files && this.files[0];
      if (!file) return;
      var preview = this.closest('.banner-image-preview') || this.closest('.banner8-image-slot');
      if (!preview) return;
      var url = URL.createObjectURL(file);
      preview.innerHTML = '<img src="' + url + '" alt="Banner preview">';
      preview.appendChild(this);
    });
  });
  initServiceBlockItems(blockEditModalBody);
  initSourceModeControls(blockEditModalBody);
  // Wire up the cloned form's submit to also close the modal
  blockEditModalBody.querySelector('form')?.addEventListener('submit', function() {
    blockEditModal.hidden = true;
  });
  blockEditModal.hidden = false;
});

document.getElementById('blockEditModalClose').addEventListener('click', function() {
  blockEditModal.hidden = true;
});
blockEditModal.addEventListener('click', function(e) {
  if (e.target === this) this.hidden = true;
});

// ── Serialize & save ───────────────────────────────────
function serializeLayout() {
  var zones = {};
  document.querySelectorAll('.layout-zone').forEach(zone => {
    zones[zone.dataset.zone] = Array.from(zone.querySelectorAll(':scope > .layout-block-list > .layout-block'))
      .map(block => parseInt(block.dataset.id));
  });
  return zones;
}

function saveLayoutOrder() {
  fetch(reorderUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    body: JSON.stringify({ zones: serializeLayout() })
  });
}

// ── Toast error ────────────────────────────────────────
function showLayoutError() {
  var box = document.getElementById('layout-toast');
  if (!box) {
    box = document.createElement('div');
    box.id = 'layout-toast';
    box.className = 'layout-toast';
    box.innerHTML = '<span><svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M12 2 3 5.6v6.8c0 4.8 3.1 9.1 9 9.6 5.9-.5 9-4.8 9-9.6V5.6L12 2Zm1 14h-2v-2h2v2Zm0-4h-2V7h2v5Z"/></svg></span><strong>Không tồn tại widget ở vị trí này</strong>';
    document.body.appendChild(box);
  }
  box.classList.add('show');
  clearTimeout(layoutErrorTimer);
  layoutErrorTimer = setTimeout(() => box.classList.remove('show'), 3000);
}

function isAllowedDrop(zone, type) {
  return (allowedZones[zone] || []).includes(type);
}

// ── Empty state ────────────────────────────────────────
function refreshEmptyStates() {
  document.querySelectorAll('.layout-block-list').forEach(list => {
    var hasBlock = list.querySelector(':scope > .layout-block') !== null;
    var empty = list.querySelector(':scope > .layout-empty');
    if (!empty) {
      empty = document.createElement('div');
      empty.className = 'layout-empty';
      empty.textContent = 'Chưa có khối nội dung';
      list.appendChild(empty);
    }
    empty.hidden = hasBlock;
  });
}

// ── Drag snapshot ──────────────────────────────────────
function captureDragSnapshot(item) {
  dragSnapshot = {
    item: item,
    parent: item.parentElement,
    nextSibling: item.nextElementSibling,
    editPanel: item.nextElementSibling?.classList.contains('layout-edit') ? item.nextElementSibling : null,
    editNextSibling: item.nextElementSibling?.classList.contains('layout-edit') ? item.nextElementSibling.nextElementSibling : null,
  };
}

function restoreDragSnapshot() {
  if (!dragSnapshot) return;
  if (dragSnapshot.nextSibling && dragSnapshot.nextSibling.parentElement === dragSnapshot.parent) {
    dragSnapshot.parent.insertBefore(dragSnapshot.item, dragSnapshot.nextSibling);
  } else {
    dragSnapshot.parent.appendChild(dragSnapshot.item);
  }
  if (dragSnapshot.editPanel) {
    if (dragSnapshot.editNextSibling && dragSnapshot.editNextSibling.parentElement === dragSnapshot.parent) {
      dragSnapshot.parent.insertBefore(dragSnapshot.editPanel, dragSnapshot.editNextSibling);
    } else {
      dragSnapshot.parent.insertBefore(dragSnapshot.editPanel, dragSnapshot.item.nextSibling);
    }
  }
  dragSnapshot = null;
  refreshEmptyStates();
}

refreshEmptyStates();

// ── SortableJS for existing blocks ────────────────────
var zoneSortables = [];
document.querySelectorAll('.layout-block-list').forEach(list => {
  var s = Sortable.create(list, {
    handle: '.drag-handle',
    animation: 150,
    group: { name: 'homepage-layout', pull: true, put: true },
    draggable: '.layout-block',
    ghostClass: 'layout-block-ghost',
    onStart: function(evt) {
      // Capture the edit panel before SortableJS moves the item
      captureDragSnapshot(evt.item);
      // Temporarily detach edit panel so SortableJS doesn't lose it
      if (dragSnapshot.editPanel) {
        dragSnapshot.editPanel.remove();
      }
    },
    onEnd: function(evt) {
      var zone = evt.to.closest('.layout-zone')?.dataset.zone;
      if (!isAllowedDrop(zone, evt.item.dataset.type)) {
        restoreDragSnapshot();
        showLayoutError();
        return;
      }
      // Re-attach edit panel right after the block in its new position
      if (dragSnapshot && dragSnapshot.editPanel) {
        evt.item.after(dragSnapshot.editPanel);
      }
      dragSnapshot = null;
      refreshEmptyStates();
      saveLayoutOrder();
    },
  });
  zoneSortables.push(s);
});

// ── Widget panel toggle ────────────────────────────────
var fab   = document.getElementById('widgetFab');
var panel = document.getElementById('widgetPanel');
var closeBtn = document.getElementById('widgetPanelClose');

function openPanel() {
  panel.classList.add('open');
  panel.setAttribute('aria-hidden', 'false');
  document.getElementById('widgetSearch').focus();
}
function closePanel() {
  panel.classList.remove('open');
  panel.setAttribute('aria-hidden', 'true');
}

fab.addEventListener('click', () => panel.classList.contains('open') ? closePanel() : openPanel());
closeBtn.addEventListener('click', closePanel);

// ── Search filter ──────────────────────────────────────
document.getElementById('widgetSearch').addEventListener('input', function() {
  var q = this.value.toLowerCase();
  document.querySelectorAll('.widget-card').forEach(card => {
    card.hidden = q && !card.dataset.label.toLowerCase().includes(q);
  });
});

// ── Category filter ────────────────────────────────────
document.querySelectorAll('.widget-cat').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.widget-cat').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    var cat = this.dataset.cat;
    document.querySelectorAll('.widget-card').forEach(card => {
      card.hidden = cat !== 'all' && card.dataset.type !== cat;
    });
    document.getElementById('widgetSearch').value = '';
  });
});

// ── SortableJS from widget panel → zones (clone) ──────
// Re-init zone sortables to accept widget-source group
zoneSortables.forEach((s, idx) => {
  var list = document.querySelectorAll('.layout-block-list')[idx];
  s.option('group', {
    name: 'homepage-layout',
    pull: true,
    put: ['homepage-layout', 'widget-source'],
  });
  s.option('onAdd', function(evt) {
    var item = evt.item;
    if (!item.dataset.id) {
      var type    = item.dataset.type;
      var label   = item.querySelector('.layout-block-title b')?.textContent || item.dataset.label || type;
      var variant = item.dataset.variant || '';
      var targetList = evt.to;
      var zone    = targetList.closest('.layout-zone')?.dataset.zone;
      var prevBlock = item.previousElementSibling?.classList.contains('layout-edit') ? item.previousElementSibling.previousElementSibling : item.previousElementSibling;
      while (prevBlock && !prevBlock.classList.contains('layout-block')) prevBlock = prevBlock.previousElementSibling;
      var nextBlock = item.nextElementSibling;
      while (nextBlock && !nextBlock.classList.contains('layout-block')) nextBlock = nextBlock.nextElementSibling;
      var sortOrder = Array.from(targetList.querySelectorAll(':scope > .layout-block')).indexOf(item) + 1;
      var insertAfterId = prevBlock?.dataset.id || '';
      var insertBeforeId = nextBlock?.dataset.id || '';
      item.remove();
      refreshEmptyStates();
      if (!isAllowedDrop(zone, type)) { showLayoutError(); return; }
      openConfigModal(type, label, zone, variant, sortOrder, insertAfterId, insertBeforeId);
    }
  });
});

Sortable.create(document.getElementById('widgetList'), {
  animation: 150,
  group: { name: 'widget-source', pull: 'clone', put: false },
  sort: false,
  draggable: '.widget-card',
  ghostClass: 'widget-card-ghost',
  onClone: function(evt) {
    var type    = evt.item.dataset.type;
    var label   = evt.item.dataset.label;
    var variant = evt.item.dataset.variant || '';
    var ph = document.createElement('div');
    ph.className = 'layout-block';
    ph.dataset.type = type;
    ph.dataset.label = label;
    ph.dataset.variant = variant;
    ph.innerHTML = '<span class="drag-handle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="9" cy="5" r="1" fill="currentColor"/><circle cx="15" cy="5" r="1" fill="currentColor"/><circle cx="9" cy="12" r="1" fill="currentColor"/><circle cx="15" cy="12" r="1" fill="currentColor"/><circle cx="9" cy="19" r="1" fill="currentColor"/><circle cx="15" cy="19" r="1" fill="currentColor"/></svg></span>'
      + '<div class="layout-block-title"><b>' + label + '</b></div>';
    evt.clone.replaceWith(ph);
  },
});

// ── Config modal after drop ────────────────────────────
var configModal      = document.getElementById('widgetQuickAdd');
var configType       = document.getElementById('widgetQuickAddType');
var configTitle      = document.getElementById('widgetQuickAddTitle');
var configZoneSelect = configModal.querySelector('select[name="zone"]');

function openConfigModal(type, label, zone, variant, sortOrder, insertAfterId, insertBeforeId) {
  configType.value = type;
  configTitle.textContent = 'Cấu hình widget: ' + label;
  if (zone && configZoneSelect) configZoneSelect.value = zone;
  var sortInput = document.getElementById('widgetQuickAddSortOrder');
  if (sortInput) sortInput.value = sortOrder || '';
  var insertAfterInput = document.getElementById('widgetQuickAddInsertAfter');
  if (insertAfterInput) insertAfterInput.value = insertAfterId || '';
  var insertBeforeInput = document.getElementById('widgetQuickAddInsertBefore');
  if (insertBeforeInput) insertBeforeInput.value = insertBeforeId || '';
  var styleInput = configModal.querySelector('input[name="style_key"]');
  if (styleInput) styleInput.value = variant || '';
  var titleInput = configModal.querySelector('input[name="title"]');
  if (titleInput) titleInput.value = label || '';
  configModal.querySelectorAll('[data-quick-settings]').forEach(function(section) {
    section.hidden = true;
    section.querySelectorAll('input, select, textarea').forEach(function(input) { input.disabled = true; });
  });
  var activeSettings = null;
  if (type === 'banner') activeSettings = (variant === 'banner_8')
    ? document.getElementById('quickAddBanner8Settings')
    : document.getElementById('quickAddBannerSettings');
  if (type === 'product-list') activeSettings = document.getElementById('quickAddProductListSettings');
  if (type === 'list-of-articles-by-category') activeSettings = document.getElementById('quickAddArticleCategorySettings');
  if (type === 'service-block') activeSettings = document.getElementById('quickAddServiceBlockSettings');
  if (type === 'partners') activeSettings = document.getElementById('quickAddPartnersSettings');
  if (type === 'customer-testimonials') activeSettings = document.getElementById('quickAddCustomerTestimonialsSettings');
  if (type === 'contact-form') {
    activeSettings = document.getElementById('quickAddContactFormSettings');
    // Set variant-specific defaults before showing
    if (activeSettings) {
      var bgInput = activeSettings.querySelector('input[name="settings[bg_color]"]');
      if (bgInput) bgInput.value = (variant === 'contact-form_2') ? '#0f1c3f' : '#f8fafc';
    }
  }
  if (activeSettings) {
    activeSettings.hidden = false;
    activeSettings.querySelectorAll('input, select, textarea').forEach(function(input) { input.disabled = false; });
    initServiceBlockItems(activeSettings);
    initSourceModeControls(activeSettings);
  }
  configModal.hidden = false;
}

document.getElementById('widgetQuickAddCancel').addEventListener('click', () => configModal.hidden = true);
configModal.addEventListener('click', function(e) { if (e.target === this) this.hidden = true; });

document.querySelectorAll('[data-banner-image-input]').forEach(input => {
  input.addEventListener('change', function() {
    var file = this.files && this.files[0];
    if (!file) return;
    var preview = this.closest('.banner-image-preview') || this.closest('.banner8-image-slot');
    if (!preview) return;
    var url = URL.createObjectURL(file);
    preview.innerHTML = '<img src="' + url + '" alt="Banner preview">';
    preview.appendChild(this);
  });
});

// ── Service block dynamic items ────────────────────────
function initServiceBlockItems(scope) {
  scope.querySelectorAll('[data-service-block-items]').forEach(function(list) {
    if (list._serviceItemsReady) return;
    list._serviceItemsReady = true;
    var wrapper = list.closest('.banner-settings');
    var addBtn = wrapper?.querySelector('[data-add-service-item]');
    var tpl = wrapper?.querySelector('[data-service-item-template]');

    function reindex() {
      list.querySelectorAll('[data-service-block-item]').forEach(function(item, index) {
        item.querySelector('[data-service-item-title]')?.replaceChildren(document.createTextNode(' ITEM ' + (index + 1)));
        item.querySelectorAll('[name]').forEach(function(input) {
          input.name = input.name.replace(/settings\[items\]\[\d+\]/, 'settings[items][' + index + ']');
        });
      });
    }

    addBtn?.addEventListener('click', function() {
      if (!tpl || list.querySelectorAll('[data-service-block-item]').length >= 12) return;
      var index = list.querySelectorAll('[data-service-block-item]').length;
      var html = tpl.innerHTML.replaceAll('__INDEX__', index).replaceAll('__NUMBER__', index + 1);
      list.insertAdjacentHTML('beforeend', html);
      reindex();
    });

    list.addEventListener('click', function(e) {
      var removeBtn = e.target.closest('[data-remove-service-item]');
      if (!removeBtn) return;
      removeBtn.closest('[data-service-block-item]')?.remove();
      reindex();
    });
  });
}
initServiceBlockItems(document);

// ── Manual/import source toggle ───────────────────────
function initSourceModeControls(scope) {
  scope.querySelectorAll('[data-source-mode]').forEach(function(select) {
    var wrapper = select.closest('.banner-settings') || select.closest('form');
    if (!wrapper) return;

    function syncSourceMode() {
      var isImport = select.value === 'import';
      wrapper.querySelectorAll('[data-source-manual]').forEach(function(section) {
        section.hidden = isImport;
        section.querySelectorAll('input, select, textarea, button').forEach(function(input) {
          input.disabled = isImport;
        });
      });
      wrapper.querySelectorAll('[data-source-import-field]').forEach(function(input) {
        input.disabled = !isImport;
        var group = input.closest('.form-group');
        if (group) group.hidden = !isImport;
      });
    }

    if (!select._sourceModeReady) {
      select._sourceModeReady = true;
      select.addEventListener('change', syncSourceMode);
    }
    syncSourceMode();
  });
}
initSourceModeControls(document);

// ── Category parent/child picker ───────────────────────
document.addEventListener('change', function(e) {
  var parentInput = e.target.closest('[data-parent-category]');
  if (!parentInput) return;
  var group = parentInput.closest('[data-category-parent]');
  var children = group?.querySelector('[data-category-children]');
  if (!children) return;
  children.hidden = !parentInput.checked;
  if (!parentInput.checked) {
    children.querySelectorAll('input[type="checkbox"]').forEach(function(input) { input.checked = false; });
  }
});

// ── Quick-add + button on card ─────────────────────────
document.querySelectorAll('.widget-card-add').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.stopPropagation();
    openConfigModal(this.dataset.type, this.dataset.label, null, this.dataset.variant || '');
  });
});

// ── Portrait image detection ───────────────────────────
document.querySelectorAll('.widget-card-preview img').forEach(function(img) {
  function check() {
    if (img.naturalHeight > img.naturalWidth) {
      img.closest('.widget-card-preview').classList.add('portrait');
    }
  }
  if (img.complete) { check(); } else { img.addEventListener('load', check); }
});

// ── Widget edit modal (super admin) ───────────────────
var widgetEditModal = document.getElementById('widgetEditModal');
if (widgetEditModal) {
  var weEditUrl = '{{ route('admin.homepage-layout.widget-edit') }}';

  document.getElementById('widgetList').addEventListener('click', function(e) {
    var btn = e.target.closest('[data-widget-edit]');
    if (!btn) return;
    e.preventDefault();
    var type = btn.dataset.type, variant = btn.dataset.variant;
    fetch(weEditUrl + '?type=' + encodeURIComponent(type) + '&variant=' + encodeURIComponent(variant), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(function(d) {
      document.getElementById('widgetEditModalTitle').textContent = 'Sửa widget: ' + d.variant;
      document.getElementById('weType').value    = d.type;
      document.getElementById('weVariant').value = d.variant;
      document.getElementById('weKey').value     = d.type + ' / ' + d.variant;
      document.getElementById('weLabel').value   = d.label;
      document.getElementById('weCode').value    = d.code;
      // Image preview
      var img = document.getElementById('weImgPreview');
      var ph  = document.getElementById('weImgPlaceholder');
      if (d.img) { img.src = d.img; img.style.display = 'block'; ph.style.display = 'none'; }
      else       { img.style.display = 'none'; ph.style.display = 'block'; }
      // Zone checkboxes
      document.querySelectorAll('.we-zone-check').forEach(function(cb) {
        cb.checked = d.allowed_zones.includes(cb.value);
      });
      widgetEditModal.hidden = false;
    });
  });

  document.getElementById('widgetEditModalClose').addEventListener('click', function() { widgetEditModal.hidden = true; });
  document.getElementById('widgetEditModalCancel').addEventListener('click', function() { widgetEditModal.hidden = true; });
  widgetEditModal.addEventListener('click', function(e) { if (e.target === this) this.hidden = true; });

  // Submit via fetch
  document.getElementById('widgetEditForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    fetch('{{ route('admin.homepage-layout.widget-update') }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
      body: fd,
    })
    .then(r => r.json())
    .then(function(d) {
      if (d.ok) {
        widgetEditModal.hidden = true;
        var t = document.createElement('div');
        t.className = 'layout-toast show';
        t.style.background = '#16a34a';
        t.innerHTML = '<span>✓</span><strong>' + d.message + '</strong>';
        document.body.appendChild(t);
        setTimeout(function(){ t.remove(); }, 2500);
      }
    });
  });

  // Image preview on file select
  document.getElementById('weImgInput').addEventListener('change', function() {
    var file = this.files && this.files[0];
    if (!file) return;
    var img = document.getElementById('weImgPreview');
    var ph  = document.getElementById('weImgPlaceholder');
    img.src = URL.createObjectURL(file);
    img.style.display = 'block';
    ph.style.display = 'none';
  });
}

// ── Add widget type (super admin only) ─────────────────
var addWidgetBtn = document.getElementById('addWidgetTypeBtn');
if (addWidgetBtn) {
  addWidgetBtn.addEventListener('click', function() {
    document.getElementById('addWidgetModal').hidden = false;
  });
}
document.getElementById('addWidgetModalCancel')?.addEventListener('click', () => {
  document.getElementById('addWidgetModal').hidden = true;
});
document.getElementById('addWidgetModal')?.addEventListener('click', function(e) {
  if (e.target === this) this.hidden = true;
});
</script>
@endpush
