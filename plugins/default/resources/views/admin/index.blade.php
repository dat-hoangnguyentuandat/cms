@extends('layouts.admin')

@section('title', 'Plugin Mặc định')

@section('content')
<div class="wc-layout">
  @include('admin.plugins._sidebar')

  <div class="wc-content">
    <div class="card">
      <div class="card-header">
        <h3>Plugin Mặc định</h3>
      </div>
      <div class="card-body">
        <p style="margin:0;color:#475569">Đây là plugin mẫu</p>
      </div>
    </div>
  </div>
</div>
@endsection
