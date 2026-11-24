(function(){
  'use strict';

  // Mobile menu toggle
  const burger = document.querySelector('.burger');
  const nav = document.querySelector('.nav');
  if (burger && nav) {
    burger.addEventListener('click', () => {
      nav.classList.toggle('open');
      burger.classList.toggle('open');
    });
    document.querySelectorAll('.nav a').forEach(a => a.addEventListener('click', () => {
      nav.classList.remove('open');
      burger.classList.remove('open');
    }));
  }

  // Fade-in on scroll
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('show');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('.fade-in').forEach(el => io.observe(el));
  } else {
    document.querySelectorAll('.fade-in').forEach(el => el.classList.add('show'));
  }

  // Contact form AJAX
  const form = document.querySelector('form[data-contact]');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const status = form.querySelector('[data-status]');
      const btn = form.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Đang gửi...';
      try {
        const res = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json().catch(() => ({}));
        if (res.ok && data.success) {
          status.className = 'alert alert-success';
          status.textContent = data.message || 'Gửi thành công!';
          form.reset();
        } else {
          status.className = 'alert alert-error';
          status.textContent = (data.errors ? Object.values(data.errors).flat()[0] : null) || data.message || 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
      } catch (err) {
        status.className = 'alert alert-error';
        status.textContent = 'Không thể kết nối tới máy chủ.';
      } finally {
        btn.disabled = false;
        btn.textContent = 'Gửi liên hệ';
      }
    });
  }
})();
