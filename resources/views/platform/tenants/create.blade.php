@extends('platform.layout')

@section('title', 'Tạo tenant')

@section('content')
    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('platform.tenants.index') }}">← Tenants</a>
            <h1>Tạo tenant</h1>
            <p>Provision domain, database riêng, package và tài khoản admin ban đầu.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('platform.tenants.store') }}" class="editor">
        @csrf
        @include('platform.tenants._form')
        <div class="form-actions">
            <a class="button ghost" href="{{ route('platform.tenants.index') }}">Hủy</a>
            <button class="button primary" type="submit">Tạo và provision</button>
        </div>
    </form>
@endsection
