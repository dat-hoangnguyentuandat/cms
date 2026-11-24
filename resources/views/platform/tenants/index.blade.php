@extends('platform.layout')

@section('title', 'Tenants')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tenants</h1>
            <p>Quản lý website, domain, database và package được cấp.</p>
        </div>
        <a class="button primary" href="{{ route('platform.tenants.create') }}">+ Tạo tenant</a>
    </div>

    <div class="metrics">
        <div><span>Tổng</span><strong>{{ $counts['all'] }}</strong></div>
        <div><span>Đang hoạt động</span><strong>{{ $counts['active'] }}</strong></div>
        <div><span>Tạm ngưng</span><strong>{{ $counts['suspended'] }}</strong></div>
    </div>

    <form class="filters" method="GET">
        <input type="search" name="search" value="{{ $search }}" placeholder="Tên, UUID hoặc domain">
        <select name="status">
            <option value="">Tất cả trạng thái</option>
            <option value="active" @selected($status === 'active')>Đang hoạt động</option>
            <option value="suspended" @selected($status === 'suspended')>Tạm ngưng</option>
        </select>
        <button class="button" type="submit">Lọc</button>
        @if ($search !== '' || $status !== '')
            <a class="button ghost" href="{{ route('platform.tenants.index') }}">Xóa lọc</a>
        @endif
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Khách hàng</th>
                    <th>Domain chính</th>
                    <th>Database</th>
                    <th>Package</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tenants as $tenant)
                    @php
                        $primary = $tenant->domains->firstWhere('is_primary', true)?->domain;
                        $themeCount = $tenant->packages->where('package_type', 'theme')->count();
                        $pluginCount = $tenant->packages->where('package_type', 'plugin')->count();
                    @endphp
                    <tr>
                        <td>
                            <a class="tenant-name" href="{{ route('platform.tenants.show', $tenant) }}">{{ $tenant->name }}</a>
                            <small>{{ $tenant->uuid }}</small>
                        </td>
                        <td>{{ $primary ?: 'Chưa có' }}</td>
                        <td>
                            <span>{{ $tenant->db_driver }}</span>
                            <small>{{ $tenant->db_database }}</small>
                        </td>
                        <td>
                            <span>{{ $themeCount }} theme</span>
                            <small>{{ $pluginCount }} plugin</small>
                        </td>
                        <td>
                            <span @class(['badge', 'active' => $tenant->is_active, 'suspended' => ! $tenant->is_active])>
                                {{ $tenant->is_active ? 'Hoạt động' : 'Tạm ngưng' }}
                            </span>
                        </td>
                        <td class="actions"><a class="button small" href="{{ route('platform.tenants.show', $tenant) }}">Chi tiết</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Không tìm thấy tenant.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $tenants->links() }}</div>
@endsection
