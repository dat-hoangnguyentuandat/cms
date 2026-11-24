@extends('platform.layout')

@section('title', 'Sửa '.$tenant->name)

@section('content')
    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('platform.tenants.show', $tenant) }}">← {{ $tenant->name }}</a>
            <h1>Sửa tenant</h1>
            <p>Thay đổi thông tin kết nối chỉ có hiệu lực từ request tiếp theo.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('platform.tenants.update', $tenant) }}" class="editor">
        @csrf
        @method('PUT')
        @include('platform.tenants._form')
        <div class="form-actions">
            <a class="button ghost" href="{{ route('platform.tenants.show', $tenant) }}">Hủy</a>
            <button class="button primary" type="submit">Lưu thay đổi</button>
        </div>
    </form>
@endsection
