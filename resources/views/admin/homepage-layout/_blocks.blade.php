<div class="layout-block-list">
  @forelse($blocks as $block)
    <div class="layout-block {{ $block->is_active ? '' : 'inactive' }}" data-id="{{ $block->id }}" data-type="{{ $block->block_type }}">
      <span class="drag-handle" title="Kéo để sắp xếp">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="9" cy="5" r="1" fill="currentColor"/><circle cx="15" cy="5" r="1" fill="currentColor"/><circle cx="9" cy="12" r="1" fill="currentColor"/><circle cx="15" cy="12" r="1" fill="currentColor"/><circle cx="9" cy="19" r="1" fill="currentColor"/><circle cx="15" cy="19" r="1" fill="currentColor"/></svg>
      </span>
      <div class="layout-block-title">
        <b>{{ $blockTypes[$block->block_type] ?? $block->block_type }}</b>{{ $block->style_key ? ' (' . $block->style_key . ')' : '' }}{{ $block->title ? ' : ' . $block->title : '' }}
      </div>
      <div class="layout-block-actions">
        <button type="button" title="Cấu hình" data-edit-block>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
        </button>
      </div>
    </div>
    <div class="layout-edit">
      <form action="{{ route('admin.homepage-layout.update', $block) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-row-3">
          <div class="form-group">
            <label>Vùng hiển thị</label>
            <select name="zone" class="form-control" required>
              @foreach($zones as $key => $name)
                <option value="{{ $key }}" @selected($block->zone === $key)>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label>Loại khối</label>
            <select name="block_type" class="form-control" required>
              @foreach($blockTypes as $key => $name)
                <option value="{{ $key }}" @selected($block->block_type === $key)>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label>Kiểu</label>
            <input type="text" name="style_key" value="{{ old('style_key', $block->style_key) }}" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label>Tên hiển thị</label>
          <input type="text" name="title" value="{{ old('title', $block->title) }}" class="form-control">
        </div>
        @php $widgetSettingsSchema = app(\App\Services\Widget\WidgetRegistry::class)->settingsSchema($block->block_type); @endphp
        @if($widgetSettingsSchema)
          @include('admin.homepage-layout._schema-settings', ['block' => $block, 'schema' => $widgetSettingsSchema])
        @else
        @if($block->block_type === 'banner')
          @php
            $settings = $block->settings ?? [];
            $isBanner8 = ($block->style_key === 'banner_8');
            $image = $settings['image'] ?? null;
            $imageUrl = $image ? asset('storage/' . $image) : null;
            $contentSource = $settings['content_source'] ?? 'manual';
            $sourceType = $settings['source_type'] ?? 'product';
            $sourceLimit = (int) ($settings['source_limit'] ?? ($isBanner8 ? 6 : 1));
          @endphp
          @if($isBanner8)
            <div class="banner-settings">
              <div class="form-group">
                <label>Đường dẫn cho tiêu đề</label>
                <input type="text" name="settings[title_url]" value="{{ old('settings.title_url', $settings['title_url'] ?? '') }}" class="form-control">
              </div>
              <div class="form-row-2">
                <div class="form-group">
                  <label>Kích thước tên hiển thị (px)</label>
                  <input type="number" min="10" max="72" name="settings[title_size]" value="{{ old('settings.title_size', $settings['title_size'] ?? 16) }}" class="form-control">
                </div>
                <div class="form-group">
                  <label>Vị trí tên hiển thị</label>
                  <select name="settings[title_align]" class="form-control">
                    <option value="left" @selected(old('settings.title_align', $settings['title_align'] ?? 'center') === 'left')>Trái</option>
                    <option value="center" @selected(old('settings.title_align', $settings['title_align'] ?? 'center') === 'center')>Giữa</option>
                    <option value="right" @selected(old('settings.title_align', $settings['title_align'] ?? 'center') === 'right')>Phải</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label>Tiêu đề phụ</label>
                <textarea name="settings[subtitle]" class="form-control" rows="3">{{ old('settings.subtitle', $settings['subtitle'] ?? '') }}</textarea>
              </div>
              <div class="banner-content-config" data-source-import>
                <div class="banner-content-title"><span>▤</span><b>NGUỒN NỘI DUNG</b></div>
                <div class="form-row-2">
                  <div class="form-group">
                    <label>Chế độ dữ liệu</label>
                    <select name="settings[content_source]" class="form-control" data-source-mode>
                      <option value="manual" @selected(($contentSource ?? 'manual') === 'manual')>Cấu hình tay</option>
                      <option value="import" @selected(($contentSource ?? 'manual') === 'import')>Import từ dữ liệu có sẵn</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Nguồn import</label>
                    <select name="settings[source_type]" class="form-control" data-source-import-field>
                      <option value="product" @selected($sourceType === 'product')>Sản phẩm</option>
                      <option value="service" @selected($sourceType === 'service')>Dịch vụ</option>
                      <option value="news" @selected($sourceType === 'news')>Tin tức</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label>Số lượng lấy về</label>
                  <input type="number" min="1" max="6" name="settings[source_limit]" value="{{ old('settings.source_limit', $sourceLimit) }}" class="form-control" data-source-import-field>
                </div>
              </div>
              <div class="banner8-images" data-source-manual>
                @for($i = 0; $i < 6; $i++)
                  @php $imgPath = $settings['images'][$i] ?? null; $imgUrl = $imgPath ? asset('storage/'.$imgPath) : null; @endphp
                  <label class="banner8-image-slot">
                    @if($imgUrl)
                      <img src="{{ $imgUrl }}" alt="Ảnh {{ $i+1 }}">
                    @else
                      <span>Chưa có ảnh</span>
                    @endif
                    <input type="file" name="settings[images][{{ $i }}]" accept="image/*" hidden data-banner-image-input>
                  </label>
                @endfor
              </div>
              <div class="form-row-2" data-source-manual>
                <div class="form-group">
                  <label>Chiều rộng hình ảnh</label>
                  <div class="input-unit">
                    <input type="number" min="0" name="settings[width]" value="{{ old('settings.width', $settings['width'] ?? 0) }}" class="form-control">
                    <span>px</span>
                  </div>
                </div>
                <div class="form-group">
                  <label>Chiều cao hình ảnh</label>
                  <div class="input-unit">
                    <input type="number" min="0" name="settings[height]" value="{{ old('settings.height', $settings['height'] ?? 0) }}" class="form-control">
                    <span>px</span>
                  </div>
                </div>
              </div>
              <div class="banner-content-config">
                <div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div>
                <div class="form-group">
                  <label>Chọn kiểu hiển thị widget</label>
                  <select name="settings[display_mode]" class="form-control">
                    <option value="show" @selected(old('settings.display_mode', $settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option>
                    <option value="hide" @selected(old('settings.display_mode', $settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option>
                  </select>
                </div>
              </div>
            </div>
          @else
            <div class="banner-settings">
              <div class="banner-content-config" data-source-import>
                <div class="banner-content-title"><span>▤</span><b>NGUỒN NỘI DUNG</b></div>
                <div class="form-row-2">
                  <div class="form-group">
                    <label>Chế độ dữ liệu</label>
                    <select name="settings[content_source]" class="form-control" data-source-mode>
                      <option value="manual" @selected(($contentSource ?? 'manual') === 'manual')>Cấu hình tay</option>
                      <option value="import" @selected(($contentSource ?? 'manual') === 'import')>Import từ dữ liệu có sẵn</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Nguồn import</label>
                    <select name="settings[source_type]" class="form-control" data-source-import-field>
                      <option value="product" @selected($sourceType === 'product')>Sản phẩm</option>
                      <option value="service" @selected($sourceType === 'service')>Dịch vụ</option>
                      <option value="news" @selected($sourceType === 'news')>Tin tức</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label>Số lượng lấy về</label>
                  <input type="number" min="1" max="1" name="settings[source_limit]" value="{{ old('settings.source_limit', $sourceLimit) }}" class="form-control" data-source-import-field>
                </div>
              </div>
            <div data-source-manual>
            <label class="banner-image-preview">
              @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $block->title ?: 'Banner' }}">
              @else
                <span>Chưa có ảnh</span>
              @endif
              <input type="file" name="settings[image]" accept="image/*" hidden data-banner-image-input>
            </label>
            <div class="form-row-2">
              <div class="form-group">
                <label>Chiều rộng hình ảnh</label>
                <div class="input-unit">
                  <input type="number" min="0" name="settings[width]" value="{{ old('settings.width', $settings['width'] ?? 0) }}" class="form-control">
                  <span>px</span>
                </div>
              </div>
              <div class="form-group">
                <label>Chiều cao hình ảnh</label>
                <div class="input-unit">
                  <input type="number" min="0" name="settings[height]" value="{{ old('settings.height', $settings['height'] ?? 0) }}" class="form-control">
                  <span>px</span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Bo góc hình ảnh</label>
              <div class="input-unit">
                <input type="number" min="0" name="settings[radius]" value="{{ old('settings.radius', $settings['radius'] ?? 0) }}" class="form-control">
                <span>px</span>
              </div>
            </div>
            <label class="form-check">
              <input type="hidden" name="settings[fullscreen]" value="0">
              <input type="checkbox" name="settings[fullscreen]" value="1" @checked(old('settings.fullscreen', $settings['fullscreen'] ?? false))> Tràn màn hình
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
                  <option value="show" @selected(old('settings.display_mode', $settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option>
                  <option value="hide" @selected(old('settings.display_mode', $settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option>
                </select>
              </div>
            </div>
          </div>
          @endif
        @endif
        @if($block->block_type === 'product-list')
          @php $settings = $block->settings ?? []; $selectedCategoryIds = collect($settings['category_ids'] ?? [])->map(fn($id) => (int) $id)->all(); @endphp
          <div class="banner-settings">
            <div class="form-group"><label>Đường dẫn cho tiêu đề</label><input type="text" name="settings[title_url]" value="{{ old('settings.title_url', $settings['title_url'] ?? '') }}" class="form-control"></div>
            <div class="form-group"><label>Tiêu đề phụ</label><textarea name="settings[subtitle]" class="form-control" rows="3">{{ old('settings.subtitle', $settings['subtitle'] ?? '') }}</textarea></div>
            <div class="form-group">
              <label>Thể loại</label>
              <select name="settings[category_ids][]" class="form-control" multiple size="5">
                @foreach($categories as $category)
                  <option value="{{ $category->id }}" @selected(in_array($category->id, $selectedCategoryIds))>{{ $category->parent_id ? '— ' : '' }}{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
            <label class="form-check"><input type="hidden" name="settings[show_children]" value="0"><input type="checkbox" name="settings[show_children]" value="1" @checked(old('settings.show_children', $settings['show_children'] ?? true))> Hiển thị tất cả thể loại con</label>
            <label class="form-check"><input type="hidden" name="settings[group_by_category]" value="0"><input type="checkbox" name="settings[group_by_category]" value="1" @checked(old('settings.group_by_category', $settings['group_by_category'] ?? true))> Phân loại sản phẩm theo từng danh mục</label>
            <label class="form-check"><input type="hidden" name="settings[show_all_tab]" value="0"><input type="checkbox" name="settings[show_all_tab]" value="1" @checked(old('settings.show_all_tab', $settings['show_all_tab'] ?? false))> Hiện tab Tất cả</label>
            <div class="form-group"><label>Giới hạn số lượng từng thể loại (Mặc định là 6)</label><input type="number" min="1" max="30" name="settings[limit_per_category]" value="{{ old('settings.limit_per_category', $settings['limit_per_category'] ?? 6) }}" class="form-control"></div>
            <label class="form-check"><input type="hidden" name="settings[autoplay]" value="0"><input type="checkbox" name="settings[autoplay]" value="1" @checked(old('settings.autoplay', $settings['autoplay'] ?? false))> Tự động chạy slider</label>
            <div class="form-group"><label>Số lượng trên 1 dòng (Desktop)</label><select name="settings[desktop_items]" class="form-control">@foreach([1,2,3,4,5,6] as $num)<option value="{{ $num }}" @selected((int) old('settings.desktop_items', $settings['desktop_items'] ?? 4) === $num)>{{ $num }}</option>@endforeach</select></div>
            <div class="form-group"><label>Số lượng trên 1 dòng (Mobile)</label><select name="settings[mobile_items]" class="form-control">@foreach([1,2,3] as $num)<option value="{{ $num }}" @selected((int) old('settings.mobile_items', $settings['mobile_items'] ?? 2) === $num)>{{ $num }}</option>@endforeach</select></div>
            <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show" @selected(old('settings.display_mode', $settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option><option value="hide" @selected(old('settings.display_mode', $settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option></select></div></div>
          </div>
        @endif
        @if($block->block_type === 'list-of-articles-by-category')
          @php $settings = $block->settings ?? []; $selectedCategoryIds = collect($settings['category_ids'] ?? [])->map(fn($id) => (int) $id)->all(); @endphp
          <div class="banner-settings">
            <div class="form-group"><label>Đường dẫn cho tiêu đề</label><input type="text" name="settings[title_url]" value="{{ old('settings.title_url', $settings['title_url'] ?? '') }}" class="form-control"></div>
            <div class="form-group"><label>Tiêu đề phụ</label><textarea name="settings[subtitle]" class="form-control" rows="3">{{ old('settings.subtitle', $settings['subtitle'] ?? '') }}</textarea></div>
            <div class="form-group">
              <label>Thể loại</label>
              <div class="widget-category-checklist">
                @foreach($categories->whereNull('parent_id') as $parent)
                  <div class="widget-category-group">
                    <label class="widget-category-option widget-category-parent-option">
                      <input type="checkbox" name="settings[category_ids][]" value="{{ $parent->id }}" @checked(in_array($parent->id, $selectedCategoryIds))>
                      <span>{{ $parent->name }}</span>
                    </label>
                    @foreach($categories->where('parent_id', $parent->id) as $child)
                      <label class="widget-category-option widget-category-child-option">
                        <input type="checkbox" name="settings[category_ids][]" value="{{ $child->id }}" @checked(in_array($child->id, $selectedCategoryIds))>
                        <span>{{ $child->name }}</span>
                      </label>
                    @endforeach
                  </div>
                @endforeach
              </div>
            </div>
            <label class="form-check"><input type="hidden" name="settings[show_children]" value="0"><input type="checkbox" name="settings[show_children]" value="1" @checked(old('settings.show_children', $settings['show_children'] ?? true))> Hiển thị tất cả thể loại con</label>
            <label class="form-check"><input type="hidden" name="settings[group_by_category]" value="0"><input type="checkbox" name="settings[group_by_category]" value="1" @checked(old('settings.group_by_category', $settings['group_by_category'] ?? true))> Phân loại sản phẩm theo từng danh mục</label>
            <label class="form-check"><input type="hidden" name="settings[show_all_tab]" value="0"><input type="checkbox" name="settings[show_all_tab]" value="1" @checked(old('settings.show_all_tab', $settings['show_all_tab'] ?? false))> Hiện tab Tất cả</label>
            <div class="form-group"><label>Giới hạn số lượng từng thể loại (Mặc định là 6)</label><input type="number" min="1" max="30" name="settings[limit_per_category]" value="{{ old('settings.limit_per_category', $settings['limit_per_category'] ?? 6) }}" class="form-control"></div>
            <label class="form-check"><input type="hidden" name="settings[autoplay]" value="0"><input type="checkbox" name="settings[autoplay]" value="1" @checked(old('settings.autoplay', $settings['autoplay'] ?? false))> Tự động chạy slider</label>
            <div class="form-group"><label>Số lượng trên 1 dòng (Desktop)</label><select name="settings[desktop_items]" class="form-control">@foreach([1,2,3,4,5,6] as $num)<option value="{{ $num }}" @selected((int) old('settings.desktop_items', $settings['desktop_items'] ?? 4) === $num)>{{ $num }}</option>@endforeach</select></div>
            <div class="form-group"><label>Số lượng trên 1 dòng (Mobile)</label><select name="settings[mobile_items]" class="form-control">@foreach([1,2,3] as $num)<option value="{{ $num }}" @selected((int) old('settings.mobile_items', $settings['mobile_items'] ?? 2) === $num)>{{ $num }}</option>@endforeach</select></div>
            <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show" @selected(old('settings.display_mode', $settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option><option value="hide" @selected(old('settings.display_mode', $settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option></select></div></div>
          </div>
        @endif
        @if($block->block_type === 'service-block')
          @php
            $settings = $block->settings ?? [];
            $items = array_values($settings['items'] ?? []);
            $sideImageUrl = !empty($settings['side_image']) ? asset('storage/' . $settings['side_image']) : null;
            $contentSource = $settings['content_source'] ?? 'manual';
            $sourceType = $settings['source_type'] ?? 'service';
            $sourceLimit = (int) ($settings['source_limit'] ?? 6);
          @endphp
          <div class="banner-settings">
            <div class="form-group"><label>Tiêu đề</label><input type="text" name="settings[title]" value="{{ old('settings.title', $settings['title'] ?? ($block->title ?: 'Khối dịch vụ')) }}" class="form-control"></div>
            <div class="form-group"><label>Đường dẫn cho tiêu đề</label><input type="text" name="settings[title_url]" value="{{ old('settings.title_url', $settings['title_url'] ?? '') }}" class="form-control"></div>
            <div class="form-row-2">
              <div class="form-group"><label>Kích thước tiêu đề (px)</label><input type="number" min="10" max="72" name="settings[heading_size]" value="{{ old('settings.heading_size', $settings['heading_size'] ?? 18) }}" class="form-control"></div>
              <div class="form-group"><label>Màu tiêu đề</label><input type="color" name="settings[heading_color]" value="{{ old('settings.heading_color', $settings['heading_color'] ?? ($settings['icon_color'] ?? '#1683f7')) }}" class="form-control"></div>
            </div>
            <div class="form-group"><label>Tiêu đề phụ</label><textarea name="settings[subtitle]" class="form-control" rows="3">{{ old('settings.subtitle', $settings['subtitle'] ?? '') }}</textarea></div>
            <div class="banner-content-config">
              <div class="banner-content-title"><span>▤</span><b>NGUỒN NỘI DUNG</b></div>
              <div class="form-row-2">
                <div class="form-group">
                  <label>Chế độ dữ liệu</label>
                  <select name="settings[content_source]" class="form-control" data-source-mode>
                    <option value="manual" @selected(($contentSource ?? 'manual') === 'manual')>Cấu hình tay</option>
                    <option value="import" @selected(($contentSource ?? 'manual') === 'import')>Import từ dữ liệu có sẵn</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Nguồn import</label>
                  <select name="settings[source_type]" class="form-control" data-source-import-field>
                    <option value="product" @selected($sourceType === 'product')>Sản phẩm</option>
                    <option value="service" @selected($sourceType === 'service')>Dịch vụ</option>
                    <option value="news" @selected($sourceType === 'news')>Tin tức</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label>Số lượng lấy về</label>
                <input type="number" min="1" max="12" name="settings[source_limit]" value="{{ old('settings.source_limit', $sourceLimit) }}" class="form-control" data-source-import-field>
              </div>
            </div>
            <div class="form-row-2">
              <div class="form-group"><label>Kiểu bố cục</label><select name="settings[layout]" class="form-control"><option value="image_top" @selected(($settings['layout'] ?? 'image_top') === 'image_top')>Ảnh trên - icon dưới</option><option value="image_card" @selected(($settings['layout'] ?? 'image_top') === 'image_card')>Card ảnh</option><option value="product_cards" @selected(($settings['layout'] ?? 'image_top') === 'product_cards')>Card ảnh trên - chữ dưới</option><option value="icons_only" @selected(($settings['layout'] ?? 'image_top') === 'icons_only')>Chỉ icon + nội dung</option><option value="icon_left" @selected(($settings['layout'] ?? 'image_top') === 'icon_left')>Icon trái - nội dung phải</option><option value="icon_card" @selected(($settings['layout'] ?? 'image_top') === 'icon_card')>Card icon nổi</option><option value="icon_line" @selected(($settings['layout'] ?? 'image_top') === 'icon_line')>Icon nằm trên đường kẻ</option><option value="number_icon" @selected(($settings['layout'] ?? 'image_top') === 'number_icon')>Số thứ tự + icon</option><option value="image_side" @selected(($settings['layout'] ?? 'image_top') === 'image_side')>Danh sách + ảnh bên cạnh</option><option value="image_diamond" @selected(($settings['layout'] ?? 'image_top') === 'image_diamond')>Danh sách + ảnh hình thoi</option><option value="overlay" @selected(($settings['layout'] ?? 'image_top') === 'overlay')>Ảnh nền phủ màu</option><option value="color_blocks" @selected(($settings['layout'] ?? 'image_top') === 'color_blocks')>Các ô màu / ảnh nền</option><option value="center_image_features" @selected(($settings['layout'] ?? 'image_top') === 'center_image_features')>Ảnh giữa - nội dung 2 bên</option><option value="simple_horizontal" @selected(($settings['layout'] ?? 'image_top') === 'simple_horizontal')>Hàng ngang đơn giản</option></select></div>
              <div class="form-group"><label>Icon nằm ở vị trí</label><select name="settings[icon_position]" class="form-control"><option value="top" @selected(($settings['icon_position'] ?? 'top') === 'top')>Trên nội dung</option><option value="line" @selected(($settings['icon_position'] ?? 'top') === 'line')>Nằm trên đường kẻ</option></select></div>
            </div>
            <div class="form-row-2">
              <div class="form-group"><label>Số item / dòng Desktop</label><select name="settings[desktop_items]" class="form-control">@foreach([1,2,3,4,5,6] as $num)<option value="{{ $num }}" @selected((int)($settings['desktop_items'] ?? 3) === $num)>{{ $num }}</option>@endforeach</select></div>
              <div class="form-group"><label>Số item / dòng Mobile</label><select name="settings[mobile_items]" class="form-control">@foreach([1,2,3] as $num)<option value="{{ $num }}" @selected((int)($settings['mobile_items'] ?? 1) === $num)>{{ $num }}</option>@endforeach</select></div>
            </div>
            <div class="form-row-2">
              <div class="form-group"><label>Dạng icon</label><select name="settings[icon_shape]" class="form-control"><option value="circle" @selected(($settings['icon_shape'] ?? 'circle') === 'circle')>Tròn</option><option value="square" @selected(($settings['icon_shape'] ?? 'circle') === 'square')>Vuông bo góc</option><option value="none" @selected(($settings['icon_shape'] ?? 'circle') === 'none')>Không nền</option></select></div>
              <div class="form-group"><label>Vị trí ảnh lớn (bố cục danh sách)</label><select name="settings[image_position]" class="form-control"><option value="right" @selected(($settings['image_position'] ?? 'right') === 'right')>Bên phải</option><option value="left" @selected(($settings['image_position'] ?? 'right') === 'left')>Bên trái</option></select></div>
            </div>
            <div class="form-row-2">
              <div class="form-group"><label>Màu icon / nhấn</label><input type="color" name="settings[icon_color]" value="{{ old('settings.icon_color', $settings['icon_color'] ?? '#1683f7') }}" class="form-control"></div>
              <div class="form-group"><label>Màu nền khối</label><input type="color" name="settings[background_color]" value="{{ old('settings.background_color', $settings['background_color'] ?? '#ffffff') }}" class="form-control"></div>
            </div>
            <div class="form-row-3">
              <div class="form-group"><label>Màu tiêu đề item</label><input type="color" name="settings[title_color]" value="{{ old('settings.title_color', $settings['title_color'] ?? '#1f2937') }}" class="form-control"></div>
              <div class="form-group"><label>Màu mô tả</label><input type="color" name="settings[text_color]" value="{{ old('settings.text_color', $settings['text_color'] ?? '#6b7280') }}" class="form-control"></div>
              <div class="form-group"><label>Màu đường kẻ</label><input type="color" name="settings[line_color]" value="{{ old('settings.line_color', $settings['line_color'] ?? '#cfe3ff') }}" class="form-control"></div>
            </div>
            <div class="form-group">
              <label>Ảnh lớn bên cạnh (dùng cho bố cục danh sách)</label>
              @if($sideImageUrl)<div style="margin:6px 0"><img src="{{ $sideImageUrl }}" alt="" style="max-width:160px;max-height:100px;object-fit:cover"></div>@endif
              <input type="file" name="settings[side_image]" accept="image/*" class="form-control">
            </div>
            <div data-source-manual>
            <div class="service-block-items" data-service-block-items>
              @foreach($items as $i => $item)
                @php
                  $itemImageUrl = !empty($item['image']) ? asset('storage/' . $item['image']) : null;
                  $iconImageUrl = !empty($item['icon_image']) ? asset('storage/' . $item['icon_image']) : null;
                @endphp
                <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                  <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> ITEM {{ $i + 1 }}</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                  <div class="form-row-2"><div class="form-group"><label>Tiêu đề</label><input type="text" name="settings[items][{{ $i }}][title]" value="{{ old('settings.items.'.$i.'.title', $item['title'] ?? '') }}" class="form-control"></div><div class="form-group"><label>Đường dẫn</label><input type="text" name="settings[items][{{ $i }}][url]" value="{{ old('settings.items.'.$i.'.url', $item['url'] ?? '') }}" class="form-control"></div></div>
                  <div class="form-group"><label>Mô tả phụ</label><input type="text" name="settings[items][{{ $i }}][description]" value="{{ old('settings.items.'.$i.'.description', $item['description'] ?? '') }}" class="form-control"></div>
                  <input type="hidden" name="settings[items][{{ $i }}][existing_image]" value="{{ $item['image'] ?? '' }}">
                  <input type="hidden" name="settings[items][{{ $i }}][existing_icon_image]" value="{{ $item['icon_image'] ?? '' }}">
                  <div class="form-row-2"><div class="form-group"><label>Hình ảnh item</label>@if($itemImageUrl)<div style="margin:6px 0"><img src="{{ $itemImageUrl }}" alt="" style="max-width:120px;max-height:80px;object-fit:cover"></div>@endif<input type="file" name="settings[items][{{ $i }}][image]" accept="image/*" class="form-control"></div><div class="form-group"><label>Hình icon riêng</label>@if($iconImageUrl)<div style="margin:6px 0"><img src="{{ $iconImageUrl }}" alt="" style="max-width:60px;max-height:60px;object-fit:cover"></div>@endif<input type="file" name="settings[items][{{ $i }}][icon_image]" accept="image/*" class="form-control"></div></div>
                </div>
              @endforeach
            </div>
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
            <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show" @selected(($settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option><option value="hide" @selected(($settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option></select></div></div>
          </div>
        @endif
        @if($block->block_type === 'partners')
          @php
            $settings = $block->settings ?? [];
            $items = array_values($settings['items'] ?? []);
          @endphp
          <div class="banner-settings">
            <div class="form-group">
              <label>Đường dẫn cho tiêu đề</label>
              <input type="text" name="settings[title_url]" value="{{ old('settings.title_url', $settings['title_url'] ?? '') }}" class="form-control">
            </div>
            <div class="form-group">
              <label>Tiêu đề phụ</label>
              <input type="text" name="settings[subtitle]" value="{{ old('settings.subtitle', $settings['subtitle'] ?? '') }}" class="form-control">
            </div>
            <div class="form-row-2">
              <div class="form-group">
                <label>Kiểu hiển thị</label>
                <select name="settings[layout]" class="form-control">
                  <option value="marquee" @selected(($settings['layout'] ?? 'marquee') === 'marquee')>Marquee (cuộn ngang)</option>
                  <option value="grid"    @selected(($settings['layout'] ?? 'marquee') === 'grid')>Lưới</option>
                </select>
              </div>
              <div class="form-group">
                <label>Số cột (chỉ dùng cho lưới)</label>
                <select name="settings[desktop_cols]" class="form-control">
                  @foreach([2,3,4,5,6] as $num)
                    <option value="{{ $num }}" @selected((int)($settings['desktop_cols'] ?? 5) === $num)>{{ $num }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-row-2">
              <div class="form-group">
                <label>Tốc độ cuộn (10=chậm, 100=nhanh)</label>
                <input type="number" min="10" max="100" name="settings[speed]" value="{{ old('settings.speed', $settings['speed'] ?? 40) }}" class="form-control">
              </div>
              <div class="form-group">
                <label>Màu nền khối</label>
                <input type="color" name="settings[bg_color]" value="{{ old('settings.bg_color', $settings['bg_color'] ?? '#ffffff') }}" class="form-control">
              </div>
            </div>
            <label class="form-check"><input type="hidden" name="settings[autoplay]" value="0"><input type="checkbox" name="settings[autoplay]" value="1" @checked(old('settings.autoplay', $settings['autoplay'] ?? true))> Tự động cuộn</label>
            <label class="form-check"><input type="hidden" name="settings[show_name]" value="0"><input type="checkbox" name="settings[show_name]" value="1" @checked(old('settings.show_name', $settings['show_name'] ?? false))> Hiển thị tên đối tác</label>
            <div class="service-block-items" data-service-block-items>
              @foreach($items as $i => $item)
                @php $logoUrl = !empty($item['logo']) ? asset('storage/' . $item['logo']) : null; @endphp
                <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                  <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center">
                    <span><span>▤</span><b data-service-item-title> ĐỐI TÁC {{ $i + 1 }}</b></span>
                    <button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button>
                  </div>
                  <div class="form-row-2">
                    <div class="form-group">
                      <label>Tên đối tác</label>
                      <input type="text" name="settings[items][{{ $i }}][name]" value="{{ old('settings.items.'.$i.'.name', $item['name'] ?? '') }}" class="form-control">
                    </div>
                    <div class="form-group">
                      <label>Đường dẫn (URL)</label>
                      <input type="text" name="settings[items][{{ $i }}][url]" value="{{ old('settings.items.'.$i.'.url', $item['url'] ?? '') }}" class="form-control" placeholder="https://...">
                    </div>
                  </div>
                  <div class="form-group">
                    <label>Logo</label>
                    @if($logoUrl)<div style="margin:4px 0"><img src="{{ $logoUrl }}" alt="" style="max-height:48px;max-width:160px;object-fit:contain"></div>@endif
                    <input type="hidden" name="settings[items][{{ $i }}][existing_logo]" value="{{ $item['logo'] ?? '' }}">
                    <input type="file" name="settings[items][{{ $i }}][logo]" accept="image/*" class="form-control">
                  </div>
                </div>
              @endforeach
            </div>
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
                  <option value="show" @selected(($settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option>
                  <option value="hide" @selected(($settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option>
                </select>
              </div>
            </div>
          </div>
        @endif
        @if($block->block_type === 'customer-testimonials')
          @php
            $settings = $block->settings ?? [];
            $items = array_values($settings['items'] ?? []);
            $contentSource = $settings['content_source'] ?? 'manual';
            $sourceLimit = (int) ($settings['source_limit'] ?? 6);
          @endphp
          <div class="banner-settings">
            <div class="banner-content-config" data-source-import>
              <div class="banner-content-title"><span>▤</span><b>NGUỒN NỘI DUNG</b></div>
              <div class="form-row-2">
                <div class="form-group">
                  <label>Chế độ dữ liệu</label>
                  <select name="settings[content_source]" class="form-control" data-source-mode>
                    <option value="manual" @selected($contentSource === 'manual')>Nhập tay</option>
                    <option value="import" @selected($contentSource === 'import')>Lấy từ danh sách đánh giá</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Số lượng lấy về</label>
                  <input type="number" min="1" max="50" name="settings[source_limit]" value="{{ old('settings.source_limit', $sourceLimit) }}" class="form-control" data-source-import-field>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Đường dẫn cho tiêu đề</label>
              <input type="text" name="settings[title_url]" value="{{ old('settings.title_url', $settings['title_url'] ?? '') }}" class="form-control">
            </div>
            <div class="form-group">
              <label>Tiêu đề phụ</label>
              <textarea name="settings[subtitle]" class="form-control" rows="3">{{ old('settings.subtitle', $settings['subtitle'] ?? '') }}</textarea>
            </div>
            <div class="banner-content-config">
              <div class="banner-content-title"><span>▤</span><b>BỐ CỤC HIỂN THỊ</b></div>
              <div class="form-row-2">
                <div class="form-group">
                  <label>Kiểu bố cục</label>
                  <select name="settings[layout]" class="form-control">
                    @php $_layout = $settings['layout'] ?? 'slider'; @endphp
                    <option value="slider" @selected($_layout === 'slider')>Slider (cuộn ngang)</option>
                    <option value="grid" @selected($_layout === 'grid')>Lưới (grid)</option>
                    <option value="card_minimal" @selected($_layout === 'card_minimal')>Card tối giản</option>
                    <option value="bubble" @selected($_layout === 'bubble')>Khung trích dẫn (bubble)</option>
                    <option value="centered" @selected($_layout === 'centered')>Một item nổi bật giữa</option>
                    <option value="compact" @selected($_layout === 'compact')>Hàng ngang gọn</option>
                    <option value="featured" @selected($_layout === 'featured')>Featured + danh sách phụ</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Vị trí sao đánh giá</label>
                  <select name="settings[star_position]" class="form-control">
                    @php $_starPos = $settings['star_position'] ?? 'before_content'; @endphp
                    <option value="top" @selected($_starPos === 'top')>Trên cùng</option>
                    <option value="after_name" @selected($_starPos === 'after_name')>Sau tên</option>
                    <option value="before_content" @selected($_starPos === 'before_content')>Trước nội dung</option>
                    <option value="bottom" @selected($_starPos === 'bottom')>Cuối card</option>
                    <option value="hide" @selected($_starPos === 'hide')>Ẩn</option>
                  </select>
                </div>
              </div>
              <div class="form-row-3">
                <div class="form-group">
                  <label>Số cột Desktop</label>
                  <select name="settings[desktop_cols]" class="form-control">
                    @foreach([1,2,3,4] as $num)
                      <option value="{{ $num }}" @selected((int)($settings['desktop_cols'] ?? 3) === $num)>{{ $num }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group">
                  <label>Số cột Mobile</label>
                  <select name="settings[mobile_cols]" class="form-control">
                    @foreach([1,2] as $num)
                      <option value="{{ $num }}" @selected((int)($settings['mobile_cols'] ?? 1) === $num)>{{ $num }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group">
                  <label>Khoảng cách card (px)</label>
                  <input type="number" min="0" max="80" name="settings[gap]" value="{{ old('settings.gap', $settings['gap'] ?? 20) }}" class="form-control">
                </div>
              </div>
              <div class="form-row-2">
                <div class="form-group">
                  <label>Chiều rộng tối đa (px)</label>
                  <input type="number" min="400" max="1600" name="settings[max_width]" value="{{ old('settings.max_width', $settings['max_width'] ?? 960) }}" class="form-control">
                </div>
                <div class="form-group">
                  <label>Padding section (px)</label>
                  <input type="number" min="0" max="200" name="settings[section_padding]" value="{{ old('settings.section_padding', $settings['section_padding'] ?? 64) }}" class="form-control">
                </div>
              </div>
              <div class="form-row-2">
                <label class="form-check"><input type="hidden" name="settings[autoplay]" value="0"><input type="checkbox" name="settings[autoplay]" value="1" @checked(old('settings.autoplay', $settings['autoplay'] ?? false))> Tự động chuyển slide</label>
                <div class="form-group">
                  <label>Tốc độ tự chuyển (giây)</label>
                  <input type="number" min="1" max="20" name="settings[autoplay_speed]" value="{{ old('settings.autoplay_speed', $settings['autoplay_speed'] ?? 5) }}" class="form-control">
                </div>
              </div>
            </div>
            <div class="banner-content-config">
              <div class="banner-content-title"><span>▤</span><b>KÍCH THƯỚC & MÀU SẮC</b></div>
              <div class="form-row-3">
                <div class="form-group">
                  <label>Cỡ tiêu đề (px)</label>
                  <input type="number" min="14" max="60" name="settings[heading_size]" value="{{ old('settings.heading_size', $settings['heading_size'] ?? 30) }}" class="form-control">
                </div>
                <div class="form-group">
                  <label>Cỡ tên (px)</label>
                  <input type="number" min="10" max="32" name="settings[title_size]" value="{{ old('settings.title_size', $settings['title_size'] ?? 15) }}" class="form-control">
                </div>
                <div class="form-group">
                  <label>Cỡ nội dung (px)</label>
                  <input type="number" min="10" max="24" name="settings[content_size]" value="{{ old('settings.content_size', $settings['content_size'] ?? 14) }}" class="form-control">
                </div>
              </div>
              <div class="form-group">
                <label>Giới hạn dòng nội dung (0 = không giới hạn)</label>
                <input type="number" min="0" max="30" name="settings[content_lines]" value="{{ old('settings.content_lines', $settings['content_lines'] ?? 6) }}" class="form-control">
              </div>
              <div class="form-row-3">
                <div class="form-group">
                  <label>Kích thước avatar (px)</label>
                  <input type="number" min="32" max="160" name="settings[avatar_size]" value="{{ old('settings.avatar_size', $settings['avatar_size'] ?? 80) }}" class="form-control">
                </div>
                <div class="form-group">
                  <label>Bo góc card (px)</label>
                  <input type="number" min="0" max="48" name="settings[card_radius]" value="{{ old('settings.card_radius', $settings['card_radius'] ?? 16) }}" class="form-control">
                </div>
                <div class="form-group">
                  <label>Padding card (px)</label>
                  <input type="number" min="8" max="60" name="settings[card_padding]" value="{{ old('settings.card_padding', $settings['card_padding'] ?? 28) }}" class="form-control">
                </div>
              </div>
              <div class="form-row-3">
                <div class="form-group">
                  <label>Màu nền section</label>
                  <input type="color" name="settings[bg_color]" value="{{ old('settings.bg_color', $settings['bg_color'] ?? '#f8fafc') }}" class="form-control">
                </div>
                <div class="form-group">
                  <label>Màu nền card</label>
                  <input type="color" name="settings[card_bg]" value="{{ old('settings.card_bg', $settings['card_bg'] ?? '#ffffff') }}" class="form-control">
                </div>
                <div class="form-group">
                  <label>Màu nhấn</label>
                  <input type="color" name="settings[accent_color]" value="{{ old('settings.accent_color', $settings['accent_color'] ?? '#1683f7') }}" class="form-control">
                </div>
              </div>
              <div class="form-row-2">
                <div class="form-group">
                  <label>Màu tên</label>
                  <input type="color" name="settings[title_color]" value="{{ old('settings.title_color', $settings['title_color'] ?? '#1a2340') }}" class="form-control">
                </div>
                <div class="form-group">
                  <label>Màu nội dung</label>
                  <input type="color" name="settings[text_color]" value="{{ old('settings.text_color', $settings['text_color'] ?? '#4b5563') }}" class="form-control">
                </div>
              </div>
            </div>
            <div class="banner-content-config">
              <div class="banner-content-title"><span>▤</span><b>THÔNG TIN HIỂN THỊ</b></div>
              <label class="form-check"><input type="hidden" name="settings[show_role]" value="0"><input type="checkbox" name="settings[show_role]" value="1" @checked(old('settings.show_role', $settings['show_role'] ?? true))> Hiển thị chức vụ</label>
              <label class="form-check"><input type="hidden" name="settings[show_company]" value="0"><input type="checkbox" name="settings[show_company]" value="1" @checked(old('settings.show_company', $settings['show_company'] ?? true))> Hiển thị công ty</label>
              <label class="form-check"><input type="hidden" name="settings[show_quote]" value="0"><input type="checkbox" name="settings[show_quote]" value="1" @checked(old('settings.show_quote', $settings['show_quote'] ?? false))> Hiển thị icon dấu nháy</label>
            </div>
            <div class="service-block-items" data-service-block-items data-source-manual>
              @foreach($items as $i => $item)
                @php $avatarUrl = !empty($item['avatar']) ? asset('storage/' . $item['avatar']) : null; @endphp
                <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                  <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center">
                    <span><span>▤</span><b data-service-item-title> KHÁCH HÀNG {{ $i + 1 }}</b></span>
                    <button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button>
                  </div>
                  <div class="form-row-2">
                    <div class="form-group">
                      <label>Họ tên</label>
                      <input type="text" name="settings[items][{{ $i }}][name]" value="{{ old('settings.items.'.$i.'.name', $item['name'] ?? '') }}" class="form-control">
                    </div>
                    <div class="form-group">
                      <label>Chức vụ</label>
                      <input type="text" name="settings[items][{{ $i }}][role]" value="{{ old('settings.items.'.$i.'.role', $item['role'] ?? '') }}" class="form-control">
                    </div>
                  </div>
                  <div class="form-group">
                    <label>Công ty / Tổ chức</label>
                    <input type="text" name="settings[items][{{ $i }}][company]" value="{{ old('settings.items.'.$i.'.company', $item['company'] ?? '') }}" class="form-control">
                  </div>
                  <div class="form-group">
                    <label>Nội dung cảm nhận</label>
                    <textarea name="settings[items][{{ $i }}][content]" class="form-control" rows="3">{{ old('settings.items.'.$i.'.content', $item['content'] ?? '') }}</textarea>
                  </div>
                  <div class="form-row-2">
                    <div class="form-group">
                      <label>Số sao (1–5)</label>
                      <select name="settings[items][{{ $i }}][rating]" class="form-control">
                        @foreach([5,4,3,2,1] as $star)
                          <option value="{{ $star }}" @selected((int)($item['rating'] ?? 5) === $star)>{{ $star }} sao</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Ảnh đại diện</label>
                      @if($avatarUrl)<div style="margin:4px 0"><img src="{{ $avatarUrl }}" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover"></div>@endif
                      <input type="hidden" name="settings[items][{{ $i }}][existing_avatar]" value="{{ $item['avatar'] ?? '' }}">
                      <input type="file" name="settings[items][{{ $i }}][avatar]" accept="image/*" class="form-control">
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
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
                <div class="form-group"><label>Công ty / Tổ chức</label><input type="text" name="settings[items][__INDEX__][company]" class="form-control"></div>
                <div class="form-group"><label>Nội dung cảm nhận</label><textarea name="settings[items][__INDEX__][content]" class="form-control" rows="3"></textarea></div>
                <div class="form-row-2">
                  <div class="form-group">
                    <label>Số sao (1–5)</label>
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
                  <option value="show" @selected(($settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option>
                  <option value="hide" @selected(($settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option>
                </select>
              </div>
            </div>
          </div>
        @endif
        @if($block->block_type === 'contact-form')
          @php $settings = $block->settings ?? []; @endphp
          <div class="banner-settings">
            <div class="form-group">
              <label>Đường dẫn cho tiêu đề</label>
              <input type="text" name="settings[title_url]" value="{{ old('settings.title_url', $settings['title_url'] ?? '') }}" class="form-control">
            </div>
            <div class="form-group">
              <label>Tiêu đề phụ</label>
              <textarea name="settings[subtitle]" class="form-control" rows="3">{{ old('settings.subtitle', $settings['subtitle'] ?? '') }}</textarea>
            </div>
            <div class="form-group">
              <label>Tiêu đề email nhận được (subject)</label>
              <input type="text" name="settings[subject]" value="{{ old('settings.subject', $settings['subject'] ?? 'Liên hệ từ trang chủ') }}" class="form-control">
            </div>
            <div class="form-group">
              <label>Văn bản nút gửi</label>
              <input type="text" name="settings[btn_text]" value="{{ old('settings.btn_text', $settings['btn_text'] ?? 'Gửi liên hệ') }}" class="form-control">
            </div>
            <div class="form-group">
              <label>Kiểu bố cục</label>
              <select name="settings[layout]" class="form-control">
                <option value="two_col"    @selected(($settings['layout'] ?? 'two_col') === 'two_col')>2 cột — thông tin + form</option>
                <option value="form_only"  @selected(($settings['layout'] ?? 'two_col') === 'form_only')>Chỉ form</option>
                <option value="form_center" @selected(($settings['layout'] ?? 'two_col') === 'form_center')>Form giữa trang</option>
              </select>
            </div>
            <div class="form-row-2">
              <div class="form-group">
                <label>Màu nền khối</label>
                <input type="color" name="settings[bg_color]" value="{{ old('settings.bg_color', $settings['bg_color'] ?? '#f8fafc') }}" class="form-control">
              </div>
              <div class="form-group">
                <label>Màu nhấn (nút, icon)</label>
                <input type="color" name="settings[accent_color]" value="{{ old('settings.accent_color', $settings['accent_color'] ?? '#1683f7') }}" class="form-control">
              </div>
            </div>
            <div class="banner-content-config">
              <div class="banner-content-title"><span>▤</span><b>THÔNG TIN LIÊN HỆ HIỂN THỊ</b></div>
              <label class="form-check"><input type="hidden" name="settings[show_phone]" value="0"><input type="checkbox" name="settings[show_phone]" value="1" @checked(old('settings.show_phone', $settings['show_phone'] ?? true))> Hiển thị số điện thoại</label>
              <label class="form-check"><input type="hidden" name="settings[show_email]" value="0"><input type="checkbox" name="settings[show_email]" value="1" @checked(old('settings.show_email', $settings['show_email'] ?? true))> Hiển thị email</label>
              <label class="form-check"><input type="hidden" name="settings[show_address]" value="0"><input type="checkbox" name="settings[show_address]" value="1" @checked(old('settings.show_address', $settings['show_address'] ?? true))> Hiển thị địa chỉ</label>
            </div>
            <div class="banner-content-config">
              <div class="banner-content-title"><span>▤</span><b>CẤU HÌNH TRƯỜNG FORM</b></div>
              <label class="form-check"><input type="hidden" name="settings[req_phone]" value="0"><input type="checkbox" name="settings[req_phone]" value="1" @checked(old('settings.req_phone', $settings['req_phone'] ?? true))> Số điện thoại bắt buộc</label>
              <label class="form-check"><input type="hidden" name="settings[req_email]" value="0"><input type="checkbox" name="settings[req_email]" value="1" @checked(old('settings.req_email', $settings['req_email'] ?? false))> Email bắt buộc</label>
              <label class="form-check"><input type="hidden" name="settings[show_company]" value="0"><input type="checkbox" name="settings[show_company]" value="1" @checked(old('settings.show_company', $settings['show_company'] ?? false))> Hiển thị trường Công ty</label>
            </div>
            <div class="banner-content-config">
              <div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div>
              <div class="form-group">
                <label>Chọn kiểu hiển thị widget</label>
                <select name="settings[display_mode]" class="form-control">
                  <option value="show" @selected(($settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option>
                  <option value="hide" @selected(($settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option>
                </select>
              </div>
            </div>
          </div>
        @endif
        @if($block->block_type === 'floating-action-buttons')
          @php $settings = $block->settings ?? []; @endphp
          <div class="banner-settings">
            <div class="form-group">
              <label>Vị trí dọc</label>
              <select name="settings[position]" class="form-control">
                <option value="top"    @selected(($settings['position'] ?? 'middle') === 'top')>Trên</option>
                <option value="middle" @selected(($settings['position'] ?? 'middle') === 'middle')>Giữa</option>
                <option value="bottom" @selected(($settings['position'] ?? 'middle') === 'bottom')>Dưới</option>
              </select>
            </div>
          </div>
        @endif
        @if($block->block_type === 'stats')
          @php $settings = $block->settings ?? []; $items = array_values($settings['items'] ?? []); $bgUrl = !empty($settings['background_image']) ? asset('storage/'.$settings['background_image']) : null; @endphp
          <div class="banner-settings">
            <div class="form-group"><label>Tiêu đề phụ</label><textarea name="settings[subtitle]" class="form-control" rows="2">{{ old('settings.subtitle', $settings['subtitle'] ?? '') }}</textarea></div>
            <div class="form-group">
              <label>Ảnh nền (tùy chọn)</label>
              @if($bgUrl)<div style="margin:6px 0"><img src="{{ $bgUrl }}" alt="" style="max-width:200px;max-height:90px;object-fit:cover"></div>@endif
              <input type="file" name="settings[background_image]" accept="image/*" class="form-control">
            </div>
            <div class="service-block-items" data-service-block-items>
              @foreach($items as $i => $item)
                <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                  <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> SỐ LIỆU {{ $i + 1 }}</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                  <div class="form-row-2"><div class="form-group"><label>Con số</label><input type="text" name="settings[items][{{ $i }}][num]" value="{{ old('settings.items.'.$i.'.num', $item['num'] ?? '') }}" class="form-control" placeholder="10+"></div><div class="form-group"><label>Nhãn</label><input type="text" name="settings[items][{{ $i }}][label]" value="{{ old('settings.items.'.$i.'.label', $item['label'] ?? '') }}" class="form-control" placeholder="Năm kinh nghiệm"></div></div>
                </div>
              @endforeach
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-add-service-item>+ Thêm số liệu</button>
            <template data-service-item-template>
              <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> SỐ LIỆU __NUMBER__</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                <div class="form-row-2"><div class="form-group"><label>Con số</label><input type="text" name="settings[items][__INDEX__][num]" class="form-control" placeholder="10+"></div><div class="form-group"><label>Nhãn</label><input type="text" name="settings[items][__INDEX__][label]" class="form-control" placeholder="Năm kinh nghiệm"></div></div>
              </div>
            </template>
            <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show" @selected(($settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option><option value="hide" @selected(($settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option></select></div></div>
          </div>
        @endif
        @if($block->block_type === 'process')
          @php $settings = $block->settings ?? []; $items = array_values($settings['items'] ?? []); @endphp
          <div class="banner-settings">
            <div class="form-group"><label>Tiêu đề phụ</label><textarea name="settings[subtitle]" class="form-control" rows="2">{{ old('settings.subtitle', $settings['subtitle'] ?? '') }}</textarea></div>
            <div class="service-block-items" data-service-block-items>
              @foreach($items as $i => $item)
                <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                  <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> BƯỚC {{ $i + 1 }}</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                  <div class="form-row-2"><div class="form-group"><label>Số thứ tự</label><input type="text" name="settings[items][{{ $i }}][num]" value="{{ old('settings.items.'.$i.'.num', $item['num'] ?? '') }}" class="form-control" placeholder="01"></div><div class="form-group"><label>Tiêu đề bước</label><input type="text" name="settings[items][{{ $i }}][title]" value="{{ old('settings.items.'.$i.'.title', $item['title'] ?? '') }}" class="form-control"></div></div>
                  <div class="form-group"><label>Mô tả</label><input type="text" name="settings[items][{{ $i }}][desc]" value="{{ old('settings.items.'.$i.'.desc', $item['desc'] ?? '') }}" class="form-control"></div>
                </div>
              @endforeach
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-add-service-item>+ Thêm bước</button>
            <template data-service-item-template>
              <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> BƯỚC __NUMBER__</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                <div class="form-row-2"><div class="form-group"><label>Số thứ tự</label><input type="text" name="settings[items][__INDEX__][num]" class="form-control" placeholder="01"></div><div class="form-group"><label>Tiêu đề bước</label><input type="text" name="settings[items][__INDEX__][title]" class="form-control"></div></div>
                <div class="form-group"><label>Mô tả</label><input type="text" name="settings[items][__INDEX__][desc]" class="form-control"></div>
              </div>
            </template>
            <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show" @selected(($settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option><option value="hide" @selected(($settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option></select></div></div>
          </div>
        @endif
        @if($block->block_type === 'why-choose')
          @php $settings = $block->settings ?? []; $items = array_values($settings['items'] ?? []); @endphp
          <div class="banner-settings">
            <div class="form-group"><label>Tiêu đề khối (để trống dùng mặc định)</label><input type="text" name="settings[intro_title]" value="{{ old('settings.intro_title', $settings['intro_title'] ?? '') }}" class="form-control"></div>
            <div class="form-group"><label>Mô tả</label><textarea name="settings[intro_desc]" class="form-control" rows="3">{{ old('settings.intro_desc', $settings['intro_desc'] ?? '') }}</textarea></div>
            <div class="form-row-2"><div class="form-group"><label>Chữ trên nút</label><input type="text" name="settings[btn_text]" value="{{ old('settings.btn_text', $settings['btn_text'] ?? '') }}" class="form-control" placeholder="Tìm hiểu thêm →"></div><div class="form-group"><label>Đường dẫn nút</label><input type="text" name="settings[btn_url]" value="{{ old('settings.btn_url', $settings['btn_url'] ?? '') }}" class="form-control"></div></div>
            <div class="service-block-items" data-service-block-items>
              @foreach($items as $i => $item)
                <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                  <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> LÝ DO {{ $i + 1 }}</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                  <div class="form-group"><label>Tiêu đề</label><input type="text" name="settings[items][{{ $i }}][title]" value="{{ old('settings.items.'.$i.'.title', $item['title'] ?? '') }}" class="form-control"></div>
                  <div class="form-group"><label>Mô tả</label><input type="text" name="settings[items][{{ $i }}][desc]" value="{{ old('settings.items.'.$i.'.desc', $item['desc'] ?? '') }}" class="form-control"></div>
                </div>
              @endforeach
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-add-service-item>+ Thêm lý do</button>
            <template data-service-item-template>
              <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> LÝ DO __NUMBER__</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                <div class="form-group"><label>Tiêu đề</label><input type="text" name="settings[items][__INDEX__][title]" class="form-control"></div>
                <div class="form-group"><label>Mô tả</label><input type="text" name="settings[items][__INDEX__][desc]" class="form-control"></div>
              </div>
            </template>
            <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show" @selected(($settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option><option value="hide" @selected(($settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option></select></div></div>
          </div>
        @endif
        @if($block->block_type === 'newsletter')
          @php $settings = $block->settings ?? []; $items = array_values($settings['items'] ?? []); @endphp
          <div class="banner-settings">
            <div class="form-group"><label>Mô tả phụ</label><textarea name="settings[subtitle]" class="form-control" rows="2">{{ old('settings.subtitle', $settings['subtitle'] ?? '') }}</textarea></div>
            <div class="form-row-2"><div class="form-group"><label>Chữ trên nút gửi</label><input type="text" name="settings[btn_text]" value="{{ old('settings.btn_text', $settings['btn_text'] ?? '') }}" class="form-control" placeholder="Gửi yêu cầu tư vấn"></div><div class="form-group"><label>Tiêu đề email nhận được</label><input type="text" name="settings[subject]" value="{{ old('settings.subject', $settings['subject'] ?? '') }}" class="form-control" placeholder="Yêu cầu tư vấn từ trang chủ"></div></div>
            <div class="service-block-items" data-service-block-items>
              @foreach($items as $i => $item)
                <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                  <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> BADGE {{ $i + 1 }}</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                  <div class="form-group"><label>Nội dung badge</label><input type="text" name="settings[items][{{ $i }}][text]" value="{{ old('settings.items.'.$i.'.text', $item['text'] ?? '') }}" class="form-control" placeholder="Tư vấn miễn phí"></div>
                </div>
              @endforeach
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-add-service-item>+ Thêm badge</button>
            <template data-service-item-template>
              <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> BADGE __NUMBER__</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                <div class="form-group"><label>Nội dung badge</label><input type="text" name="settings[items][__INDEX__][text]" class="form-control" placeholder="Tư vấn miễn phí"></div>
              </div>
            </template>
            <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show" @selected(($settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option><option value="hide" @selected(($settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option></select></div></div>
          </div>
        @endif
        @if($block->block_type === 'slider')
          @php $settings = $block->settings ?? []; $items = array_values($settings['items'] ?? ($settings['slides'] ?? [])); @endphp
          <div class="banner-settings">
            <label class="form-check"><input type="hidden" name="settings[autoplay]" value="0"><input type="checkbox" name="settings[autoplay]" value="1" @checked(old('settings.autoplay', $settings['autoplay'] ?? true))> Tự động chuyển slide</label>
            <div class="service-block-items" data-service-block-items>
              @foreach($items as $i => $item)
                @php $slideImg = !empty($item['image']) ? (\Illuminate\Support\Str::startsWith($item['image'], ['http://','https://','theme-assets/']) ? asset($item['image']) : asset('storage/'.$item['image'])) : null; @endphp
                <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                  <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> SLIDE {{ $i + 1 }}</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                  <div class="form-group"><label>Tiêu đề</label><input type="text" name="settings[items][{{ $i }}][heading]" value="{{ old('settings.items.'.$i.'.heading', $item['heading'] ?? '') }}" class="form-control"></div>
                  <div class="form-group"><label>Mô tả</label><input type="text" name="settings[items][{{ $i }}][sub]" value="{{ old('settings.items.'.$i.'.sub', $item['sub'] ?? '') }}" class="form-control"></div>
                  <div class="form-group"><label>Đường dẫn (tùy chọn)</label><input type="text" name="settings[items][{{ $i }}][url]" value="{{ old('settings.items.'.$i.'.url', $item['url'] ?? '') }}" class="form-control" placeholder="https://..."></div>
                  <div class="form-group">
                    <label>Ảnh nền slide</label>
                    @if($slideImg)<div style="margin:4px 0"><img src="{{ $slideImg }}" alt="" style="max-width:200px;max-height:90px;object-fit:cover"></div>@endif
                    <input type="hidden" name="settings[items][{{ $i }}][existing_image]" value="{{ $item['image'] ?? '' }}">
                    <input type="file" name="settings[items][{{ $i }}][image]" accept="image/*" class="form-control">
                  </div>
                </div>
              @endforeach
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-add-service-item>+ Thêm slide</button>
            <template data-service-item-template>
              <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> SLIDE __NUMBER__</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                <div class="form-group"><label>Tiêu đề</label><input type="text" name="settings[items][__INDEX__][heading]" class="form-control"></div>
                <div class="form-group"><label>Mô tả</label><input type="text" name="settings[items][__INDEX__][sub]" class="form-control"></div>
                <div class="form-group"><label>Đường dẫn (tùy chọn)</label><input type="text" name="settings[items][__INDEX__][url]" class="form-control" placeholder="https://..."></div>
                <div class="form-group"><label>Ảnh nền slide</label><input type="hidden" name="settings[items][__INDEX__][existing_image]" value=""><input type="file" name="settings[items][__INDEX__][image]" accept="image/*" class="form-control"></div>
              </div>
            </template>
            <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show" @selected(($settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option><option value="hide" @selected(($settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option></select></div></div>
          </div>
        @endif
        @if(in_array($block->block_type, ['service-section', 'product-group', 'post-group']))
          @php $settings = $block->settings ?? []; @endphp
          <div class="banner-settings">
            <div class="form-group"><label>Đường dẫn cho tiêu đề</label><input type="text" name="settings[title_url]" value="{{ old('settings.title_url', $settings['title_url'] ?? '') }}" class="form-control"></div>
            <div class="form-group"><label>Tiêu đề phụ</label><textarea name="settings[subtitle]" class="form-control" rows="2">{{ old('settings.subtitle', $settings['subtitle'] ?? '') }}</textarea></div>
            <div class="form-group"><label>Số lượng hiển thị</label><input type="number" min="1" max="24" name="settings[limit]" value="{{ old('settings.limit', $settings['limit'] ?? ($block->block_type === 'post-group' ? 4 : 6)) }}" class="form-control"></div>
            <p style="color:#64748b;font-size:13px;margin:4px 0">Nội dung lấy tự động từ {{ $block->block_type === 'post-group' ? 'Tin tức' : ($block->block_type === 'product-group' ? 'Sản phẩm' : 'Dịch vụ') }}. Sửa nội dung tại trang quản lý tương ứng.</p>
            <div class="banner-content-config"><div class="banner-content-title"><span>▤</span><b>CẤU HÌNH KHỐI NỘI DUNG</b></div><div class="form-group"><label>Chọn kiểu hiển thị widget</label><select name="settings[display_mode]" class="form-control"><option value="show" @selected(($settings['display_mode'] ?? 'show') === 'show')>Hiển thị</option><option value="hide" @selected(($settings['display_mode'] ?? 'show') === 'hide')>Ẩn</option></select></div></div>
          </div>
        @endif
        @if($block->block_type === 'header')
          @php $settings = $block->settings ?? []; $logoUrl = !empty($settings['logo']) ? (\Illuminate\Support\Str::startsWith($settings['logo'], ['http://','https://','theme-assets/']) ? asset($settings['logo']) : asset('storage/'.$settings['logo'])) : null; @endphp
          <div class="banner-settings">
            <div class="form-group">
              <label>Logo (để trống dùng tên thương hiệu)</label>
              @if($logoUrl)<div style="margin:6px 0"><img src="{{ $logoUrl }}" alt="" style="max-height:48px;max-width:180px;object-fit:contain;background:#f1f5f9;padding:4px;border-radius:4px"></div>@endif
              <input type="file" name="settings[logo]" accept="image/*" class="form-control">
            </div>
            <div class="form-row-2">
              <div class="form-group"><label>Tên thương hiệu (override)</label><input type="text" name="settings[brand_name]" value="{{ old('settings.brand_name', $settings['brand_name'] ?? '') }}" class="form-control" placeholder="Để trống = tên site"></div>
              <div class="form-group"><label>Slogan dưới tên</label><input type="text" name="settings[brand_tag]" value="{{ old('settings.brand_tag', $settings['brand_tag'] ?? '') }}" class="form-control"></div>
            </div>
            <div class="form-row-2">
              <div class="form-group"><label>Chữ nút CTA</label><input type="text" name="settings[cta_text]" value="{{ old('settings.cta_text', $settings['cta_text'] ?? '') }}" class="form-control" placeholder="Khảo sát miễn phí"></div>
              <div class="form-group"><label>Đường dẫn nút CTA</label><input type="text" name="settings[cta_url]" value="{{ old('settings.cta_url', $settings['cta_url'] ?? '') }}" class="form-control"></div>
            </div>
            <label class="form-check"><input type="hidden" name="settings[show_hotline]" value="0"><input type="checkbox" name="settings[show_hotline]" value="1" @checked(old('settings.show_hotline', $settings['show_hotline'] ?? true))> Hiển thị hotline</label>
            <label class="form-check"><input type="hidden" name="settings[show_cta]" value="0"><input type="checkbox" name="settings[show_cta]" value="1" @checked(old('settings.show_cta', $settings['show_cta'] ?? true))> Hiển thị nút CTA</label>
          </div>
        @endif
        @if($block->block_type === 'topbar')
          @php $settings = $block->settings ?? []; $items = array_values($settings['items'] ?? []); @endphp
          <div class="banner-settings">
            <div class="form-row-2">
              <div class="form-group"><label>Nhãn bên trái</label><input type="text" name="settings[left_label]" value="{{ old('settings.left_label', $settings['left_label'] ?? '') }}" class="form-control" placeholder="Gọi ngay"></div>
              <div class="form-group"><label>Màu nền</label><input type="color" name="settings[bg_color]" value="{{ old('settings.bg_color', $settings['bg_color'] ?: '#0f172a') }}" class="form-control"></div>
            </div>
            <label class="form-check"><input type="hidden" name="settings[show_auth]" value="0"><input type="checkbox" name="settings[show_auth]" value="1" @checked(old('settings.show_auth', $settings['show_auth'] ?? true))> Hiển thị Đăng nhập / Đăng ký</label>
            <div class="service-block-items" data-service-block-items>
              @foreach($items as $i => $item)
                <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                  <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> LIÊN KẾT {{ $i + 1 }}</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                  <div class="form-row-2"><div class="form-group"><label>Nhãn</label><input type="text" name="settings[items][{{ $i }}][label]" value="{{ old('settings.items.'.$i.'.label', $item['label'] ?? '') }}" class="form-control"></div><div class="form-group"><label>URL</label><input type="text" name="settings[items][{{ $i }}][url]" value="{{ old('settings.items.'.$i.'.url', $item['url'] ?? '') }}" class="form-control"></div></div>
                </div>
              @endforeach
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-add-service-item>+ Thêm liên kết</button>
            <template data-service-item-template>
              <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> LIÊN KẾT __NUMBER__</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                <div class="form-row-2"><div class="form-group"><label>Nhãn</label><input type="text" name="settings[items][__INDEX__][label]" class="form-control"></div><div class="form-group"><label>URL</label><input type="text" name="settings[items][__INDEX__][url]" class="form-control"></div></div>
              </div>
            </template>
          </div>
        @endif
        @if(in_array($block->block_type, ['menu', 'social-links', 'footer']))
          @php $settings = $block->settings ?? []; $items = array_values($settings['items'] ?? []); $itemLabel = $block->block_type === 'social-links' ? 'MẠNG XÃ HỘI' : 'LIÊN KẾT'; @endphp
          <div class="banner-settings">
            @if($block->block_type === 'footer')
              <div class="form-group"><label>Dòng bản quyền (để trống dùng mặc định)</label><input type="text" name="settings[copyright]" value="{{ old('settings.copyright', $settings['copyright'] ?? '') }}" class="form-control"></div>
            @endif
            <div class="service-block-items" data-service-block-items>
              @foreach($items as $i => $item)
                <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                  <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> {{ $itemLabel }} {{ $i + 1 }}</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                  <div class="form-row-2"><div class="form-group"><label>{{ $block->block_type === 'social-links' ? 'Tên nền tảng' : 'Nhãn' }}</label><input type="text" name="settings[items][{{ $i }}][label]" value="{{ old('settings.items.'.$i.'.label', $item['label'] ?? '') }}" class="form-control"></div><div class="form-group"><label>URL</label><input type="text" name="settings[items][{{ $i }}][url]" value="{{ old('settings.items.'.$i.'.url', $item['url'] ?? '') }}" class="form-control" placeholder="https://..."></div></div>
                </div>
              @endforeach
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-add-service-item>+ Thêm liên kết</button>
            <template data-service-item-template>
              <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> {{ $itemLabel }} __NUMBER__</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                <div class="form-row-2"><div class="form-group"><label>Nhãn</label><input type="text" name="settings[items][__INDEX__][label]" class="form-control"></div><div class="form-group"><label>URL</label><input type="text" name="settings[items][__INDEX__][url]" class="form-control" placeholder="https://..."></div></div>
              </div>
            </template>
            <p style="color:#64748b;font-size:13px;margin:4px 0">Để trống danh sách = dùng nội dung mặc định (lấy từ Cài đặt / danh mục).</p>
          </div>
        @endif
        @if($block->block_type === 'editor')
          @php $settings = $block->settings ?? []; @endphp
          <div class="banner-settings">
            <div class="form-group"><label>Nội dung HTML (để trống dùng block liên hệ mặc định)</label><textarea name="settings[content]" class="form-control" rows="6" placeholder="<p>Nội dung tùy ý, hỗ trợ HTML</p>">{{ old('settings.content', $settings['content'] ?? '') }}</textarea></div>
          </div>
        @endif
        @if($block->block_type === 'fanpage')
          @php $settings = $block->settings ?? []; @endphp
          <div class="banner-settings">
            <div class="form-group"><label>Mô tả</label><textarea name="settings[desc]" class="form-control" rows="2">{{ old('settings.desc', $settings['desc'] ?? '') }}</textarea></div>
            <div class="form-row-2">
              <div class="form-group"><label>URL Facebook (override)</label><input type="text" name="settings[fb_url]" value="{{ old('settings.fb_url', $settings['fb_url'] ?? '') }}" class="form-control"></div>
              <div class="form-group"><label>Chữ nút</label><input type="text" name="settings[btn_text]" value="{{ old('settings.btn_text', $settings['btn_text'] ?? '') }}" class="form-control" placeholder="Theo dõi Fanpage"></div>
            </div>
            <div class="form-group"><label>Mã nhúng iframe (tùy chọn, có thì thay nút)</label><textarea name="settings[embed]" class="form-control" rows="3" placeholder="<iframe ...></iframe>">{{ old('settings.embed', $settings['embed'] ?? '') }}</textarea></div>
          </div>
        @endif
        @if($block->block_type === 'product-filter')
          @php $settings = $block->settings ?? []; $selectedCategoryIds = collect($settings['category_ids'] ?? [])->map(fn($id) => (int) $id)->all(); @endphp
          <div class="banner-settings">
            <div class="form-group">
              <label>Thể loại (để trống = tất cả)</label>
              <select name="settings[category_ids][]" class="form-control" multiple size="5">
                @foreach($categories as $category)
                  <option value="{{ $category->id }}" @selected(in_array($category->id, $selectedCategoryIds))>{{ $category->parent_id ? '— ' : '' }}{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group"><label>Số lượng hiển thị</label><input type="number" min="1" max="30" name="settings[limit]" value="{{ old('settings.limit', $settings['limit'] ?? 8) }}" class="form-control"></div>
          </div>
        @endif
        @if($block->block_type === 'about')
          @php $settings = $block->settings ?? []; $stats = array_values($settings['stats'] ?? []); $aboutImg = !empty($settings['image']) ? (\Illuminate\Support\Str::startsWith($settings['image'], ['http://','https://','theme-assets/']) ? asset($settings['image']) : asset('storage/'.$settings['image'])) : null; @endphp
          <div class="banner-settings">
            <div class="form-row-2">
              <div class="form-group"><label>Nhãn nhỏ (label)</label><input type="text" name="settings[heading]" value="{{ old('settings.heading', $settings['heading'] ?? '') }}" class="form-control" placeholder="Về Chúng Tôi"></div>
              <div class="form-group"><label>Tiêu đề lớn</label><input type="text" name="settings[subheading]" value="{{ old('settings.subheading', $settings['subheading'] ?? '') }}" class="form-control"></div>
            </div>
            <div class="form-group"><label>Mô tả</label><textarea name="settings[description]" class="form-control" rows="3">{{ old('settings.description', $settings['description'] ?? '') }}</textarea></div>
            <div class="form-group">
              <label>Ảnh</label>
              @if($aboutImg)<div style="margin:6px 0"><img src="{{ $aboutImg }}" alt="" style="max-width:200px;max-height:120px;object-fit:cover;border-radius:6px"></div>@endif
              <input type="file" name="settings[image]" accept="image/*" class="form-control">
            </div>
            <div class="form-row-2">
              <div class="form-group"><label>Chữ nút CTA</label><input type="text" name="settings[cta_text]" value="{{ old('settings.cta_text', $settings['cta_text'] ?? '') }}" class="form-control" placeholder="Đặt Lịch Ngay"></div>
              <div class="form-group"><label>Đường dẫn CTA</label><input type="text" name="settings[cta_link]" value="{{ old('settings.cta_link', $settings['cta_link'] ?? '') }}" class="form-control"></div>
            </div>
            <div class="service-block-items" data-service-block-items>
              @foreach($stats as $i => $item)
                <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                  <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> SỐ LIỆU {{ $i + 1 }}</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                  <div class="form-row-2"><div class="form-group"><label>Giá trị</label><input type="text" name="settings[stats][{{ $i }}][value]" value="{{ old('settings.stats.'.$i.'.value', $item['value'] ?? '') }}" class="form-control" placeholder="10+"></div><div class="form-group"><label>Nhãn</label><input type="text" name="settings[stats][{{ $i }}][label]" value="{{ old('settings.stats.'.$i.'.label', $item['label'] ?? '') }}" class="form-control"></div></div>
                </div>
              @endforeach
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-add-service-item>+ Thêm số liệu</button>
            <template data-service-item-template>
              <div class="banner-content-config service-block-item-config" style="margin-bottom:12px" data-service-block-item>
                <div class="banner-content-title" style="display:flex;justify-content:space-between;align-items:center"><span><span>▤</span><b data-service-item-title> SỐ LIỆU __NUMBER__</b></span><button type="button" class="btn btn-danger btn-sm" data-remove-service-item>Xóa</button></div>
                <div class="form-row-2"><div class="form-group"><label>Giá trị</label><input type="text" name="settings[stats][__INDEX__][value]" class="form-control" placeholder="10+"></div><div class="form-group"><label>Nhãn</label><input type="text" name="settings[stats][__INDEX__][label]" class="form-control"></div></div>
              </div>
            </template>
          </div>
        @endif
        @endif
        <div class="form-actions">
          <label class="form-check">
            <input type="checkbox" name="is_active" value="1" @checked($block->is_active)> Hiển thị
          </label>
          <div>
            <button type="submit" class="btn btn-primary btn-sm">Lưu</button>
          </div>
        </div>
      </form>
      <form class="layout-delete" action="{{ route('admin.homepage-layout.destroy', $block) }}" method="POST" onsubmit="return confirm('Xóa khối nội dung này?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
      </form>
    </div>
  @empty
    <div class="layout-empty">Chưa có khối nội dung</div>
  @endforelse
</div>
