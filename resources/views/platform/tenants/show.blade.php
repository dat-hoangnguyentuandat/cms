@extends('platform.layout')

@section('title', $tenant->name)

@section('content')
    @php
        $primary = $tenant->domains->firstWhere('is_primary', true);
        $assignedThemes = $tenant->packages->where('package_type', 'theme');
        $assignedPlugins = $tenant->packages->where('package_type', 'plugin');
        $availableThemes = $themes->reject(fn ($theme) => $assignedThemes->contains('package_name', $theme['name']));
        $availablePlugins = $plugins->reject(fn ($plugin) => $assignedPlugins->contains('package_name', $plugin['name']));
    @endphp

    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('platform.tenants.index') }}">← Tenants</a>
            <div class="title-row">
                <h1>{{ $tenant->name }}</h1>
                <span @class(['badge', 'active' => $tenant->is_active, 'suspended' => ! $tenant->is_active])>
                    {{ $tenant->is_active ? 'Hoạt động' : 'Tạm ngưng' }}
                </span>
            </div>
            <p>{{ $tenant->uuid }}</p>
        </div>
        <a class="button" href="{{ route('platform.tenants.edit', $tenant) }}">Sửa cấu hình</a>
    </div>

    <div class="detail-grid">
        <section class="panel">
            <div class="panel-heading"><h2>Domains</h2></div>
            <dl class="definition-list">
                <div><dt>Chính</dt><dd>{{ $primary?->domain ?: 'Chưa có' }}</dd></div>
                <div>
                    <dt>Phụ</dt>
                    <dd>{{ $tenant->domains->where('is_primary', false)->pluck('domain')->implode(', ') ?: 'Không có' }}</dd>
                </div>
            </dl>
        </section>

        <section class="panel">
            <div class="panel-heading"><h2>Database</h2></div>
            <dl class="definition-list">
                <div><dt>Driver</dt><dd>{{ $tenant->db_driver }}</dd></div>
                <div><dt>Host</dt><dd>{{ $tenant->db_host ?: 'Nội bộ' }}{{ $tenant->db_port ? ':'.$tenant->db_port : '' }}</dd></div>
                <div><dt>Database</dt><dd class="break">{{ $tenant->db_database }}</dd></div>
                <div><dt>Username</dt><dd>{{ $tenant->db_username ?: 'Không dùng' }}</dd></div>
            </dl>
        </section>
    </div>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Kiểm tra và bảo trì</h2>
                <p>Health check chỉ đọc; migrate chạy toàn bộ migration còn thiếu.</p>
            </div>
            <div class="inline-actions">
                <form method="POST" action="{{ route('platform.tenants.health', $tenant) }}">
                    @csrf
                    <button class="button" type="submit">Kiểm tra kết nối</button>
                </form>
                <form method="POST" action="{{ route('platform.tenants.migrate', $tenant) }}"
                      onsubmit="return confirm('Chạy migration cho tenant này?')">
                    @csrf
                    <button class="button" type="submit">Migrate</button>
                </form>
                <form method="POST" action="{{ route('platform.tenants.cache', $tenant) }}"
                      onsubmit="return confirm('Xóa cache riêng của tenant này?')">
                    @csrf
                    <button class="button" type="submit">Xóa cache</button>
                </form>
                <form method="POST" action="{{ route('platform.tenants.status', $tenant) }}"
                      onsubmit="return confirm('{{ $tenant->is_active ? 'Tạm ngưng tenant này?' : 'Bật lại tenant này?' }}')">
                    @csrf
                    <input type="hidden" name="is_active" value="{{ $tenant->is_active ? 0 : 1 }}">
                    <button class="button {{ $tenant->is_active ? 'danger' : 'primary' }}" type="submit">
                        {{ $tenant->is_active ? 'Tạm ngưng' : 'Bật tenant' }}
                    </button>
                </form>
            </div>
        </div>
        @if ($health)
            <div @class(['health', 'ok' => $health['ok'], 'failed' => ! $health['ok']])>
                <strong>{{ $health['ok'] ? 'Kết nối tốt' : 'Kết nối lỗi' }}</strong>
                <span>{{ $health['latency_ms'] }} ms</span>
                @if ($health['active_theme'])<span>Theme: {{ $health['active_theme'] }}</span>@endif
                @if (!empty($health['active_plugins']))<span>Plugin active: {{ implode(', ', $health['active_plugins']) }}</span>@endif
                @if (!empty($health['missing_tables']))<span>Thiếu bảng: {{ implode(', ', $health['missing_tables']) }}</span>@endif
                @if ($health['error'])<code>{{ $health['error'] }}</code>@endif
            </div>
        @endif
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div><h2>Theme được cấp</h2><p>Tenant có thể được cấp nhiều theme nhưng chỉ kích hoạt một theme tại một thời điểm.</p></div>
            @if ($availableThemes->isNotEmpty())
                <form class="inline-form" method="POST" action="{{ route('platform.tenants.package', $tenant) }}">
                    @csrf
                    <input type="hidden" name="package_type" value="theme">
                    <input type="hidden" name="operation" value="assign">
                    <select name="package_name" required>
                        <option value="">Chọn theme</option>
                        @foreach ($availableThemes as $theme)
                            <option value="{{ $theme['name'] }}">{{ $theme['name'] }}</option>
                        @endforeach
                    </select>
                    <button class="button" type="submit">Cấp theme</button>
                </form>
            @endif
        </div>
        <div class="package-list">
            @forelse ($assignedThemes as $assignment)
                <div class="package-row">
                    <div>
                        <strong>{{ $assignment->package_name }}</strong>
                        @if ($activeTheme === $assignment->package_name)
                            <small>Đang kích hoạt</small>
                        @endif
                    </div>
                    <div class="inline-actions">
                        <form method="POST" action="{{ route('platform.tenants.package', $tenant) }}"
                              onsubmit="return confirm('Kích hoạt theme này và seed lại layout?')">
                            @csrf
                            <input type="hidden" name="package_type" value="theme">
                            <input type="hidden" name="package_name" value="{{ $assignment->package_name }}">
                            <input type="hidden" name="operation" value="activate">
                            <button class="button small" type="submit">
                                {{ $activeTheme === $assignment->package_name ? 'Kích hoạt lại' : 'Kích hoạt' }}
                            </button>
                        </form>
                        @if ($assignedThemes->count() > 1 && $activeTheme !== $assignment->package_name)
                            <form method="POST" action="{{ route('platform.tenants.package', $tenant) }}"
                                  onsubmit="return confirm('Thu hồi theme này khỏi tenant?')">
                                @csrf
                                <input type="hidden" name="package_type" value="theme">
                                <input type="hidden" name="package_name" value="{{ $assignment->package_name }}">
                                <input type="hidden" name="operation" value="revoke">
                                <button class="button danger small" type="submit">Thu hồi</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="empty">Tenant chưa được cấp theme.</p>
            @endforelse
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div><h2>Plugins được cấp</h2><p>Plugin mới được cấp sẽ được migrate và kích hoạt ngay.</p></div>
            @if ($availablePlugins->isNotEmpty())
                <form class="inline-form" method="POST" action="{{ route('platform.tenants.package', $tenant) }}">
                    @csrf
                    <input type="hidden" name="package_type" value="plugin">
                    <input type="hidden" name="operation" value="assign">
                    <select name="package_name" required>
                        <option value="">Chọn plugin</option>
                        @foreach ($availablePlugins as $plugin)
                            <option value="{{ $plugin['name'] }}">{{ $plugin['title'] ?? $plugin['name'] }}</option>
                        @endforeach
                    </select>
                    <button class="button" type="submit">Cấp plugin</button>
                </form>
            @endif
        </div>
        <div class="package-list">
            @forelse ($assignedPlugins as $assignment)
                <div class="package-row">
                    <strong>{{ $assignment->package_name }}</strong>
                    <div class="inline-actions">
                        <form method="POST" action="{{ route('platform.tenants.package', $tenant) }}">
                            @csrf
                            <input type="hidden" name="package_type" value="plugin">
                            <input type="hidden" name="package_name" value="{{ $assignment->package_name }}">
                            <input type="hidden" name="operation" value="activate">
                            <button class="button small" type="submit">Kích hoạt</button>
                        </form>
                        <form method="POST" action="{{ route('platform.tenants.package', $tenant) }}"
                              onsubmit="return confirm('Tắt và thu hồi plugin này khỏi tenant?')">
                            @csrf
                            <input type="hidden" name="package_type" value="plugin">
                            <input type="hidden" name="package_name" value="{{ $assignment->package_name }}">
                            <input type="hidden" name="operation" value="revoke">
                            <button class="button danger small" type="submit">Thu hồi</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="empty">Tenant chưa được cấp plugin.</p>
            @endforelse
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div><h2>Seeder tenant</h2><p>Seeder chỉ chạy trên database của {{ $tenant->name }}.</p></div>
        </div>
        <form method="POST" action="{{ route('platform.tenants.seed', $tenant) }}" class="form-grid"
              onsubmit="return confirm('Seeder có thể thay đổi dữ liệu tenant. Tiếp tục?')">
            @csrf
            <label>
                Seeder được phép
                <select name="seeder" required>
                    <option value="">Chọn seeder</option>
                    @foreach ($seeders as $class)
                        <option value="{{ $class }}" title="{{ $class }}">
                            {{ class_basename($class) }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                Nhập đúng tên tenant để xác nhận
                <input name="confirmation" required placeholder="{{ $tenant->name }}" autocomplete="off">
            </label>
            <div class="span-2 form-actions">
                <button class="button danger" type="submit">Chạy seeder</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div><h2>Admin tenant</h2><p>Tạo mới, sửa thông tin, đổi mật khẩu hoặc khóa tài khoản.</p></div>
        </div>
        @if (!$tenantAdmins['ok'])
            <div class="alert error">{{ $tenantAdmins['error'] }}</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tài khoản</th><th>Email</th><th>Lần đăng nhập cuối</th><th>Trạng thái</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($tenantAdmins['items'] as $admin)
                            <tr>
                                <td><strong>{{ $admin['full_name'] }}</strong><small>{{ $admin['username'] }}</small></td>
                                <td>{{ $admin['email'] }}</td>
                                <td>{{ $admin['last_login'] ?: 'Chưa đăng nhập' }}</td>
                                <td><span @class(['badge', 'active' => $admin['is_active'], 'suspended' => !$admin['is_active']])>{{ $admin['is_active'] ? 'Hoạt động' : 'Đã khóa' }}</span></td>
                                <td class="actions">
                                    <div class="inline-actions">
                                        <a class="button small" href="{{ route('platform.tenants.show', [$tenant, 'edit_admin' => $admin['id']]) }}#tenant-admin-form">Sửa</a>
                                        <form method="POST" action="{{ route('platform.tenants.admins.status', [$tenant, $admin['id']]) }}">
                                            @csrf
                                            <input type="hidden" name="is_active" value="{{ $admin['is_active'] ? 0 : 1 }}">
                                            <button class="button small {{ $admin['is_active'] ? 'danger' : '' }}" type="submit">{{ $admin['is_active'] ? 'Khóa' : 'Mở khóa' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($editingTenantAdmin)
            <form id="tenant-admin-form" method="POST"
                  action="{{ route('platform.tenants.admins.update', [$tenant, $editingTenantAdmin['id']]) }}"
                  class="form-grid subform">
                @csrf
                @method('PUT')
                <label>Username<input name="username" required maxlength="50" autocomplete="off"
                    value="{{ old('username', $editingTenantAdmin['username']) }}"></label>
                <label>Họ tên<input name="full_name" required maxlength="100"
                    value="{{ old('full_name', $editingTenantAdmin['full_name']) }}"></label>
                <label>Email<input type="email" name="email" required maxlength="150"
                    value="{{ old('email', $editingTenantAdmin['email']) }}"></label>
                <label>Mật khẩu mới
                    <input type="password" name="password" minlength="12" autocomplete="new-password">
                    <small>Để trống để giữ mật khẩu hiện tại.</small>
                </label>
                <label>Xác nhận mật khẩu
                    <input type="password" name="password_confirmation" minlength="12" autocomplete="new-password">
                </label>
                <div class="form-actions">
                    <a class="button" href="{{ route('platform.tenants.show', $tenant) }}#tenant-admin-form">Hủy</a>
                    <button class="button primary" type="submit">Lưu thay đổi</button>
                </div>
            </form>
        @else
            <form id="tenant-admin-form" method="POST" action="{{ route('platform.tenants.admins.store', $tenant) }}" class="form-grid subform">
                @csrf
                <label>Username<input name="username" required maxlength="50" autocomplete="off"></label>
                <label>Họ tên<input name="full_name" required maxlength="100"></label>
                <label>Email<input type="email" name="email" required maxlength="150"></label>
                <label>Mật khẩu<input type="password" name="password" required minlength="12" autocomplete="new-password"></label>
                <label>Xác nhận mật khẩu<input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></label>
                <div class="form-actions"><button class="button primary" type="submit">Tạo admin</button></div>
            </form>
        @endif
    </section>

    <section class="panel">
        <div class="panel-heading"><h2>Audit log tenant</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Thời gian</th><th>Người thực hiện</th><th>Hành động</th><th>Chi tiết</th></tr></thead>
                <tbody>
                    @forelse ($auditLogs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->admin?->email ?: 'System' }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td class="break">{{ $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_UNICODE) : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Chưa có audit log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
