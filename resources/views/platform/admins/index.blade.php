@extends('platform.layout')

@section('title', 'Platform Admins')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Platform Admins</h1>
            <p>Tài khoản quản trị central và lịch sử thao tác hệ thống.</p>
        </div>
    </div>

    <section class="panel">
        <div class="panel-heading"><h2>Tạo hoặc reset tài khoản</h2></div>
        <form method="POST" action="{{ route('platform.admins.store') }}" class="form-grid">
            @csrf
            <label>Họ tên<input name="name" required maxlength="255"></label>
            <label>Tài khoản<input name="username" required maxlength="50" autocomplete="off"></label>
            <label>Email<input type="email" name="email" required maxlength="255"></label>
            <label>Mật khẩu<input type="password" name="password" required minlength="12" autocomplete="new-password"></label>
            <label>Xác nhận mật khẩu<input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></label>
            <div class="span-2 form-actions"><button class="button primary" type="submit">Lưu Platform Admin</button></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-heading"><h2>Tài khoản hiện có</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tài khoản</th><th>Lần đăng nhập cuối</th><th>Trạng thái</th><th></th></tr></thead>
                <tbody>
                    @foreach ($admins as $admin)
                        <tr>
                            <td><strong>{{ $admin->name }}</strong><small>{{ $admin->username }} · {{ $admin->email }}</small></td>
                            <td>{{ $admin->last_login_at?->format('d/m/Y H:i') ?: 'Chưa đăng nhập' }}</td>
                            <td><span @class(['badge', 'active' => $admin->is_active, 'suspended' => !$admin->is_active])>{{ $admin->is_active ? 'Hoạt động' : 'Đã khóa' }}</span></td>
                            <td class="actions">
                                <form method="POST" action="{{ route('platform.admins.status', $admin) }}">
                                    @csrf
                                    <input type="hidden" name="is_active" value="{{ $admin->is_active ? 0 : 1 }}">
                                    <button class="button small {{ $admin->is_active ? 'danger' : '' }}" type="submit">{{ $admin->is_active ? 'Khóa' : 'Mở khóa' }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading"><h2>Audit log gần nhất</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Thời gian</th><th>Người thực hiện</th><th>Tenant</th><th>Hành động</th><th>Chi tiết</th></tr></thead>
                <tbody>
                    @forelse ($auditLogs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->admin?->email ?: 'System' }}</td>
                            <td>{{ $log->tenant?->name ?: 'Toàn hệ thống' }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td class="break">{{ $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_UNICODE) : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">Chưa có audit log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
