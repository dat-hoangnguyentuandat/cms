@extends('platform.layout')

@section('title', 'Catalog')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Catalog dùng chung</h1>
            <p>Source package được quản lý tại đây; tenant chỉ thấy package đã được cấp.</p>
        </div>
    </div>

    <div class="detail-grid">
        <section class="panel">
            <div class="panel-heading"><h2>Cài hoặc cập nhật theme</h2></div>
            <form method="POST" action="{{ route('platform.catalog.themes.upload') }}" enctype="multipart/form-data" class="stack">
                @csrf
                <input type="file" name="theme" accept=".zip,application/zip" required>
                <button class="button primary" type="submit">Tải theme ZIP</button>
            </form>
        </section>
        <section class="panel">
            <div class="panel-heading"><h2>Cài hoặc cập nhật plugin</h2></div>
            <form method="POST" action="{{ route('platform.catalog.plugins.upload') }}" enctype="multipart/form-data" class="stack">
                @csrf
                <input type="file" name="plugin" accept=".zip,application/zip" required>
                <button class="button primary" type="submit">Tải plugin ZIP</button>
            </form>
        </section>
    </div>

    <section class="panel">
        <div class="panel-heading"><h2>Themes</h2><span>{{ $themes->count() }} package</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tên</th><th>Phiên bản</th><th>Tác giả</th><th>Đang cấp cho</th><th></th></tr></thead>
                <tbody>
                    @foreach ($themes as $theme)
                        @php($count = $usage->get('theme:'.$theme['name'])?->tenant_count ?? 0)
                        <tr>
                            <td><strong>{{ $theme['title'] ?? $theme['name'] }}</strong><small>{{ $theme['name'] }}</small></td>
                            <td>{{ $theme['version'] ?? '1.0.0' }}</td>
                            <td>{{ $theme['author'] ?? 'Unknown' }}</td>
                            <td>{{ $count }} tenant</td>
                            <td class="actions">
                                @if ((int) $count === 0 && $theme['name'] !== 'default')
                                    <form method="POST" action="{{ route('platform.catalog.themes.delete', $theme['name']) }}"
                                          onsubmit="return confirm('Xóa theme {{ $theme['name'] }} khỏi catalog dùng chung?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger small" type="submit">Xóa</button>
                                    </form>
                                @else
                                    <button class="button small" type="button" disabled
                                            title="{{ $theme['name'] === 'default' ? 'Theme mặc định được bảo vệ' : 'Hãy thu hồi khỏi tất cả tenant trước' }}">
                                        Xóa
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading"><h2>Plugins</h2><span>{{ $plugins->count() }} package</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tên</th><th>Phiên bản</th><th>Tác giả</th><th>Đang cấp cho</th><th></th></tr></thead>
                <tbody>
                    @foreach ($plugins as $plugin)
                        @php($count = $usage->get('plugin:'.$plugin['name'])?->tenant_count ?? 0)
                        <tr>
                            <td><strong>{{ $plugin['title'] ?? $plugin['name'] }}</strong><small>{{ $plugin['name'] }}</small></td>
                            <td>{{ $plugin['version'] ?? '1.0.0' }}</td>
                            <td>{{ $plugin['author'] ?? 'Unknown' }}</td>
                            <td>{{ $count }} tenant</td>
                            <td class="actions">
                                @if ((int) $count === 0)
                                    <form method="POST" action="{{ route('platform.catalog.plugins.delete', $plugin['name']) }}"
                                          onsubmit="return confirm('Xóa plugin {{ $plugin['name'] }} khỏi catalog dùng chung?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger small" type="submit">Xóa</button>
                                    </form>
                                @else
                                    <button class="button small" type="button" disabled
                                            title="Hãy thu hồi khỏi tất cả tenant trước">Xóa</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
