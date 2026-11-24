document.addEventListener('DOMContentLoaded', () => {
  // Sidebar toggle with overlay
  const toggle = document.querySelector('.sidebar-toggle');
  const sidebar = document.querySelector('.sidebar');
  let overlay = document.querySelector('.sidebar-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
  }
  function openSidebar() { sidebar?.classList.add('open'); overlay.classList.add('active'); }
  function closeSidebar() { sidebar?.classList.remove('open'); overlay.classList.remove('active'); }
  toggle?.addEventListener('click', () => sidebar?.classList.contains('open') ? closeSidebar() : openSidebar());
  overlay.addEventListener('click', closeSidebar);

  // User dropdown
  const userMenu = document.querySelector('.topbar-user');
  userMenu?.addEventListener('click', e => {
    e.stopPropagation();
    userMenu.classList.toggle('open');
  });
  document.addEventListener('click', () => userMenu?.classList.remove('open'));

  // Image preview on upload
  document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
    input.addEventListener('change', e => {
      const file = e.target.files?.[0];
      if (!file) return;
      const target = document.querySelector(input.dataset.preview);
      if (!target) return;
      const reader = new FileReader();
      reader.onload = ev => target.src = ev.target.result;
      reader.readAsDataURL(file);
    });
  });

  // Auto slug
  document.querySelectorAll('[data-auto-slug]').forEach(el => {
    const target = document.querySelector(el.dataset.autoSlug);
    if (!target) return;
    el.addEventListener('input', () => {
      if (target.dataset.touched === '1') return;
      target.value = el.value
        .toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/đ/g,'d')
        .replace(/[^a-z0-9]+/g,'-')
        .replace(/^-+|-+$/g,'');
    });
    target.addEventListener('input', () => target.dataset.touched = '1');
  });

  // Confirm delete
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  // Auto-hide alerts
  setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
      a.style.transition = 'opacity .4s';
      a.style.opacity = '0';
      setTimeout(() => a.remove(), 400);
    });
  }, 4000);
});
