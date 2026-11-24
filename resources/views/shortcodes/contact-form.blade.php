<div class="cms-shortcode-contact">
  @if($title)
    <h3>{{ $title }}</h3>
  @endif
  <form method="post" action="{{ route('contact.store') }}" data-shortcode-contact-form>
    @csrf
    <input type="hidden" name="subject" value="{{ $source }}">
    <div class="form-row">
      <div class="form-group">
        <label>Họ và tên</label>
        <input type="text" name="name" required>
      </div>
      <div class="form-group">
        <label>Số điện thoại</label>
        <input type="tel" name="phone" required>
      </div>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email">
    </div>
    <div class="form-group">
      <label>Nội dung</label>
      <textarea name="message" rows="4" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">{{ $button }}</button>
    <div class="cms-shortcode-contact-result" role="status" aria-live="polite"></div>
  </form>
</div>
@once
  @push('js')
    <script>
    document.addEventListener('submit', async function (event) {
      const form = event.target.closest('[data-shortcode-contact-form]');
      if (!form) return;
      event.preventDefault();
      const result = form.querySelector('.cms-shortcode-contact-result');
      const button = form.querySelector('[type="submit"]');
      button.disabled = true;
      if (result) result.textContent = '';
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          headers: {'Accept': 'application/json'},
          body: new FormData(form)
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Không gửi được biểu mẫu.');
        form.reset();
        if (result) result.textContent = data.message || 'Gửi thành công.';
      } catch (error) {
        if (result) result.textContent = error.message || 'Không gửi được biểu mẫu.';
      } finally {
        button.disabled = false;
      }
    });
    </script>
  @endpush
@endonce
