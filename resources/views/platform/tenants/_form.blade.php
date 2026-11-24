@php
    $editing = isset($tenant);
    $primary = $editing ? $tenant->domains->firstWhere('is_primary', true)?->domain : null;
    $aliases = $editing
        ? $tenant->domains->where('is_primary', false)->pluck('domain')->implode("\n")
        : '';
@endphp

<div class="form-section">
    <h2>Thông tin khách hàng</h2>
    <div class="form-grid">
        <label class="span-2">
            Tên khách hàng / website
            <input name="name" value="{{ old('name', $tenant->name ?? '') }}" required maxlength="255">
        </label>
        <label>
            Domain chính
            <input name="primary_domain" value="{{ old('primary_domain', $primary) }}" placeholder="customer.example.com" required>
        </label>
        <label>
            Domain phụ
            <textarea name="aliases" rows="3" placeholder="www.customer.example.com&#10;alias.example.com">{{ old('aliases', $aliases) }}</textarea>
            <small>Mỗi dòng một domain, không nhập giao thức hoặc đường dẫn.</small>
        </label>
    </div>
</div>

<div class="form-section">
    <h2>Database riêng</h2>
    <div class="form-grid">
        <label>
            Driver
            <select name="db_driver" required>
                @foreach (['mysql', 'mariadb', 'pgsql', 'sqlsrv', 'sqlite'] as $driver)
                    <option value="{{ $driver }}" @selected(old('db_driver', $tenant->db_driver ?? 'mysql') === $driver)>{{ $driver }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Host
            <input name="db_host" value="{{ old('db_host', $tenant->db_host ?? '127.0.0.1') }}">
        </label>
        <label>
            Port
            <input type="number" name="db_port" min="1" max="65535" value="{{ old('db_port', $tenant->db_port ?? '') }}">
        </label>
        <label>
            Database / đường dẫn SQLite
            <input name="db_database" value="{{ old('db_database', $tenant->db_database ?? '') }}" required>
        </label>
        <label>
            Username
            <input name="db_username" value="{{ old('db_username', $tenant->db_username ?? '') }}" autocomplete="off">
        </label>
        <label>
            {{ $editing ? 'Mật khẩu mới' : 'Mật khẩu' }}
            <input type="password" name="db_password" autocomplete="new-password">
            @if ($editing)<small>Để trống để giữ mật khẩu hiện tại.</small>@endif
        </label>
    </div>
    @unless ($editing)
        <label class="check">
            <input type="checkbox" name="create_database" value="1" @checked(old('create_database'))>
            Tự tạo database MySQL/MariaDB bằng kết nối central
        </label>
    @endunless
</div>

@unless ($editing)
    <div class="form-section">
        <h2>Package ban đầu</h2>
        <div class="package-setup-grid">
            <label class="package-active">
                Theme kích hoạt ban đầu
                <select name="theme" required data-active-theme>
                    @foreach ($themes as $theme)
                        <option value="{{ $theme['name'] }}" @selected(old('theme', 'default') === $theme['name'])>
                            {{ $theme['name'] }}
                        </option>
                    @endforeach
                </select>
                <small>Theme này luôn được cấp cho tenant.</small>
            </label>
            <fieldset class="package-picker" data-package-picker data-theme-picker>
                <legend>Theme cấp thêm <span data-selection-count></span></legend>
                <input type="search" placeholder="Tìm theme" aria-label="Tìm theme cấp thêm" data-package-search>
                <div class="package-options">
                    @forelse ($themes as $theme)
                        <label class="check" data-package-option data-package-name="{{ $theme['name'] }}">
                            <input type="checkbox" name="themes[]" value="{{ $theme['name'] }}"
                                @checked(in_array($theme['name'], old('themes', []), true))>
                            {{ $theme['name'] }}
                        </label>
                    @empty
                        <small>Catalog chưa có theme.</small>
                    @endforelse
                    <small class="package-filter-empty" hidden data-filter-empty>Không tìm thấy theme.</small>
                </div>
            </fieldset>
            <fieldset class="package-picker" data-package-picker>
                <legend>Plugins <span data-selection-count></span></legend>
                <input type="search" placeholder="Tìm plugin" aria-label="Tìm plugin" data-package-search>
                <div class="package-options">
                    @forelse ($plugins as $plugin)
                        <label class="check" data-package-option data-package-name="{{ $plugin['name'] }}">
                            <input type="checkbox" name="plugins[]" value="{{ $plugin['name'] }}"
                                @checked(in_array($plugin['name'], old('plugins', []), true))>
                            {{ $plugin['title'] ?? $plugin['name'] }}
                        </label>
                    @empty
                        <small>Catalog chưa có plugin.</small>
                    @endforelse
                    <small class="package-filter-empty" hidden data-filter-empty>Không tìm thấy plugin.</small>
                </div>
            </fieldset>
        </div>
    </div>

    <div class="form-section">
        <h2>Admin đầu tiên của tenant</h2>
        <div class="form-grid">
            <label>
                Username
                <input name="admin_user" value="{{ old('admin_user', $initialAdmin['username']) }}" maxlength="50" autocomplete="off">
            </label>
            <label>
                Họ tên
                <input name="admin_name" value="{{ old('admin_name', $initialAdmin['name']) }}" maxlength="100">
            </label>
            <label>
                Email
                <input type="email" name="admin_email" value="{{ old('admin_email', $initialAdmin['email']) }}" maxlength="150">
            </label>
            <div class="field">
                <label for="initial-admin-password">Mật khẩu</label>
                <input id="initial-admin-password" type="password" name="admin_password"
                       value="{{ $initialAdmin['password'] }}" minlength="12"
                       autocomplete="new-password" data-initial-admin-password>
                <label class="check compact-check">
                    <input type="checkbox" data-password-visibility>
                    Hiện mật khẩu
                </label>
            </div>
        </div>
        <label class="check">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
            Bật tenant sau khi provision thành công
        </label>
    </div>
@endunless

@unless ($editing)
    <script>
        document.querySelectorAll('[data-package-picker]').forEach((picker) => {
            const search = picker.querySelector('[data-package-search]');
            const options = Array.from(picker.querySelectorAll('[data-package-option]'));
            const count = picker.querySelector('[data-selection-count]');
            const empty = picker.querySelector('[data-filter-empty]');
            const activeTheme = picker.hasAttribute('data-theme-picker')
                ? document.querySelector('[data-active-theme]')
                : null;

            const refresh = () => {
                const query = search.value.trim().toLocaleLowerCase('vi');
                const activeName = activeTheme?.value;
                let visible = 0;

                options.forEach((option) => {
                    const name = option.dataset.packageName;
                    const isActiveTheme = activeName && name === activeName;
                    const matches = name.toLocaleLowerCase('vi').includes(query);
                    option.hidden = isActiveTheme || !matches;

                    if (isActiveTheme) {
                        option.querySelector('input').checked = false;
                    }
                    if (!option.hidden) {
                        visible++;
                    }
                });

                const selected = options.filter((option) => option.querySelector('input').checked).length;
                count.textContent = selected ? `(${selected} đã chọn)` : '';
                empty.hidden = visible > 0;
            };

            search.addEventListener('input', refresh);
            picker.addEventListener('change', refresh);
            activeTheme?.addEventListener('change', refresh);
            refresh();
        });

        document.querySelector('[data-password-visibility]')?.addEventListener('change', (event) => {
            const password = document.querySelector('[data-initial-admin-password]');
            password.type = event.currentTarget.checked ? 'text' : 'password';
        });
    </script>
@endunless
