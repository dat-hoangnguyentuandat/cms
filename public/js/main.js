// ===== HEADER SCROLL =====
const header = document.getElementById('header');
const scrollTopBtn = document.getElementById('scrollTop');

window.addEventListener('scroll', () => {
  const scrolled = window.scrollY > 60;
  header.classList.toggle('scrolled', scrolled);
  scrollTopBtn.classList.toggle('show', window.scrollY > 400);

  // Active nav highlight
  document.querySelectorAll('section[id]').forEach(section => {
    const top = section.offsetTop - 100;
    const link = document.querySelector(`.nav-links a[href="#${section.id}"]`);
    if (link) {
      link.classList.toggle('active', window.scrollY >= top && window.scrollY < top + section.offsetHeight);
    }
  });
}, { passive: true });

// ===== HAMBURGER =====
const hamburger = document.querySelector('.hamburger');
const navLinks  = document.querySelector('.nav-links');

const overlay = document.createElement('div');
overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:29;display:none;backdrop-filter:blur(2px);';
document.body.appendChild(overlay);

function openMenu() {
  hamburger.classList.add('active');
  navLinks.classList.add('open');
  overlay.style.display = 'block';
  document.body.style.overflow = 'hidden';
  hamburger.setAttribute('aria-expanded', 'true');
}
function closeMenu() {
  hamburger.classList.remove('active');
  navLinks.classList.remove('open');
  overlay.style.display = 'none';
  document.body.style.overflow = '';
  hamburger.setAttribute('aria-expanded', 'false');
}

hamburger.addEventListener('click', () => navLinks.classList.contains('open') ? closeMenu() : openMenu());
overlay.addEventListener('click', closeMenu);
navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));

// ===== SCROLL TOP =====
scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// ===== COUNTER ANIMATION =====
function animateCount(el, target, suffix) {
  const duration = 1800;
  const start = performance.now();
  const update = (now) => {
    const p = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - p, 3);
    el.textContent = Math.floor(target * eased).toLocaleString('vi-VN') + suffix;
    if (p < 1) requestAnimationFrame(update);
  };
  requestAnimationFrame(update);
}

// ===== INTERSECTION OBSERVER =====
const io = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.fade-up').forEach(el => io.observe(el));

// Stats counter
const statsIO = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.querySelectorAll('[data-count]').forEach(el => {
        animateCount(el, +el.dataset.count, el.dataset.suffix || '');
      });
      statsIO.unobserve(e.target);
    }
  });
}, { threshold: 0.5 });
const statsEl = document.getElementById('stats');
if (statsEl) statsIO.observe(statsEl);

// ===== PRODUCT FILTER =====
const filterBtns = document.querySelectorAll('.filter-btn');
const productCards = document.querySelectorAll('.product-card[data-category]');

filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    filterBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const cat = btn.dataset.filter;
    productCards.forEach(card => {
      const show = cat === 'all' || card.dataset.category === cat;
      card.style.display = show ? '' : 'none';
      if (show) { card.classList.remove('visible'); setTimeout(() => card.classList.add('visible'), 50); }
    });
  });
});

// ===== WISHLIST =====
document.querySelectorAll('.product-wishlist').forEach(btn => {
  btn.addEventListener('click', e => {
    e.stopPropagation();
    const active = btn.classList.toggle('active');
    btn.innerHTML = active ? heartFilledSVG() : heartSVG();
    showToast(active ? 'Đã thêm vào yêu thích' : 'Đã xóa khỏi yêu thích', active ? 'success' : 'error');
  });
});

function heartSVG() {
  return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>`;
}
function heartFilledSVG() {
  return `<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>`;
}

// ===== FORM VALIDATION =====
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  const rules = [
    { id: 'name',    test: v => v.length >= 2,                          msg: 'Vui lòng nhập họ tên (ít nhất 2 ký tự)' },
    { id: 'email',   test: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),  msg: 'Địa chỉ email không hợp lệ' },
    { id: 'phone',   test: v => /^(0|\+84)[0-9]{9}$/.test(v),          msg: 'Số điện thoại không hợp lệ (VD: 0901234567)' },
    { id: 'message', test: v => v.length >= 10,                         msg: 'Nội dung quá ngắn (ít nhất 10 ký tự)' },
  ];

  contactForm.addEventListener('submit', async e => {
    e.preventDefault();
    let valid = true;

    rules.forEach(({ id, test, msg }) => {
      const input = document.getElementById(id);
      const errEl = input.parentElement.querySelector('.error-msg');
      const ok = test(input.value.trim());
      input.classList.toggle('error', !ok);
      if (errEl) { errEl.textContent = msg; errEl.classList.toggle('show', !ok); }
      if (!ok) valid = false;
    });

    if (!valid) return;

    const btn = contactForm.querySelector('.btn-submit');
    btn.disabled = true;
    btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Đang gửi...`;

    try {
      const res  = await fetch('contact.php', { method: 'POST', body: new FormData(contactForm) });
      const data = await res.json();
      if (data.success) {
        contactForm.style.display = 'none';
        document.getElementById('formSuccess').classList.add('show');
        showToast('Gửi thành công! Chúng tôi sẽ liên hệ sớm.', 'success');
      } else {
        showToast(data.message || 'Có lỗi xảy ra, vui lòng thử lại.', 'error');
        btn.disabled = false;
        btn.innerHTML = sendBtnHTML();
      }
    } catch {
      showToast('Không thể kết nối. Vui lòng thử lại sau.', 'error');
      btn.disabled = false;
      btn.innerHTML = sendBtnHTML();
    }
  });

  contactForm.querySelectorAll('input, textarea').forEach(el => {
    el.addEventListener('input', () => {
      el.classList.remove('error');
      const err = el.parentElement.querySelector('.error-msg');
      if (err) err.classList.remove('show');
    });
  });
}

function sendBtnHTML() {
  return `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Gửi Tin Nhắn`;
}

// ===== TOAST =====
function showToast(msg, type = 'success') {
  let toast = document.querySelector('.toast');
  if (!toast) { toast = document.createElement('div'); toast.className = 'toast'; document.body.appendChild(toast); }
  toast.className = `toast ${type}`;
  toast.textContent = msg;
  toast.classList.add('show');
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => toast.classList.remove('show'), 4000);
}

// ===== SMOOTH SCROLL =====
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) {
      e.preventDefault();
      window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
    }
  });
});

// ===== SPIN KEYFRAME (for loading) =====
const style = document.createElement('style');
style.textContent = '@keyframes spin { to { transform: rotate(360deg); } } .spin { animation: spin 0.8s linear infinite; }';
document.head.appendChild(style);
