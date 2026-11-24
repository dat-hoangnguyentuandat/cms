@extends('layouts.admin')
@section('title', isset($faq) ? 'Sửa câu hỏi' : 'Thêm câu hỏi')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>{{ isset($faq) ? 'Sửa' : 'Thêm' }} câu hỏi</h3>
    <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <form method="post"
        action="{{ isset($faq) ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">
    @csrf
    @if(isset($faq)) @method('PUT') @endif

    <div class="form-group">
      <label>Câu hỏi <span class="req">*</span></label>
      <input type="text" name="question" class="form-control" value="{{ old('question', $faq->question ?? '') }}" required>
    </div>

    <div class="form-group">
      <label>Câu trả lời <span class="req">*</span></label>
      <textarea name="answer" class="form-control rich-editor" rows="4" required>{{ old('answer', $faq->answer ?? '') }}</textarea>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Thứ tự</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $faq->sort_order ?? 0) }}">
      </div>
      <div class="form-group" style="display:flex;align-items:end">
        <label class="form-check">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active', $faq->is_active ?? 1) ? 'checked' : '' }}>
          Hiển thị
        </label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Lưu</button>
  </form>
</div>
@endsection
