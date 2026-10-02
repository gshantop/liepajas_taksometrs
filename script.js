document.getElementById('filter').addEventListener('click', e => {
  const b = e.target.closest('button'); if (!b) return;
  const v = b.dataset.v;
  document.querySelectorAll('#filter button').forEach(x => x.classList.toggle('on', x === b));
  document.querySelectorAll('#tariffs [data-seats]').forEach(c => {
    const seats = +c.dataset.seats;
    c.style.display = (v === 'all' || (v === '4' && seats <= 4) || (v === '8' && seats > 4)) ? '' : 'none';
  });
});

const root = document.documentElement, themeBtn = document.getElementById('theme');
function applyTheme(t) {
  root.setAttribute('data-theme', t);
  themeBtn.innerHTML = t === 'dark' ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
  try { localStorage.setItem('taxi-theme', t); } catch (e) {}
}
themeBtn.addEventListener('click', () => applyTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'));
applyTheme(root.getAttribute('data-theme'));

const menu = document.getElementById('menu'), burger = document.getElementById('burger');
burger.addEventListener('click', () => {
  const open = menu.classList.toggle('open'); burger.setAttribute('aria-expanded', open);
});
menu.addEventListener('click', e => { if (e.target.matches('a')) { menu.classList.remove('open'); burger.setAttribute('aria-expanded', false); } });

const links = [...document.querySelectorAll('a.link')];
const io = new IntersectionObserver(es => es.forEach(en => {
  if (en.isIntersecting) links.forEach(l => l.classList.toggle('active', l.getAttribute('href') === '#' + en.target.id));
}), { rootMargin: '-45% 0px -50% 0px' });
document.querySelectorAll('section[id]').forEach(s => io.observe(s));

/* ---------- Taksometra pasūtījums (modālais logs + maksājums) ---------- */
const orderForm = document.getElementById('form'),
      orderModal = document.getElementById('order-modal'),
      orderClose = document.getElementById('order-close'),
      orderMsg = document.getElementById('msg'),
      orderBtn = document.getElementById('o-btn'),
      orderSum = document.getElementById('o-sum'),
      tarifSel = orderForm.elements['tarifs'],
      kmInp = orderForm.elements['km'];
let lastFocus = null;

function calcSum() {
  const o = tarifSel.options[tarifSel.selectedIndex]; if (!o) return;
  const v = parseFloat(o.dataset.start) + parseFloat(o.dataset.km) * (parseFloat(kmInp.value) || 0);
  orderSum.textContent = v.toFixed(2).replace('.', ',') + ' €';
}
tarifSel.addEventListener('change', calcSum);
kmInp.addEventListener('input', calcSum);
orderForm.addEventListener('change', () => {
  orderBtn.textContent = orderForm.elements['maksa'].value === 'karte' ? 'Pasūtīt un apmaksāt kartē' : 'Pasūtīt taksometru';
});
calcSum();

function openOrder(e) {
  e.preventDefault();
  const t = e.currentTarget.dataset.tarifs;
  if (t) { tarifSel.value = t; calcSum(); }
  lastFocus = document.activeElement;
  orderModal.classList.add('active');
  orderModal.setAttribute('aria-hidden', 'false');
  document.body.classList.add('modal-open');
  menu.classList.remove('open'); burger.setAttribute('aria-expanded', false);
  setTimeout(() => orderForm.elements['vards'].focus(), 50);
}
function closeOrder() {
  orderModal.classList.remove('active');
  orderModal.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('modal-open');
  if (lastFocus && lastFocus.focus) lastFocus.focus();
}
document.querySelectorAll('[data-order]').forEach(el => el.addEventListener('click', openOrder));
orderClose.addEventListener('click', closeOrder);
orderModal.addEventListener('mousedown', e => { if (e.target === orderModal) closeOrder(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape' && orderModal.classList.contains('active')) closeOrder(); });

orderForm.addEventListener('submit', e => {
  e.preventDefault();
  orderMsg.textContent = ''; orderMsg.style.color = '';
  orderBtn.disabled = true;
  fetch('payment/checkout.php', { method: 'POST', body: new FormData(orderForm) })
    .then(r => r.json())
    .then(d => {
      if (d.ok && d.url) { window.location.href = d.url; return; }
      throw new Error(d.kluda || 'Kļūda');
    })
    .catch(err => {
      orderMsg.textContent = err.message;
      orderMsg.style.color = 'var(--danger)';
      orderBtn.disabled = false;
    });
});

document.getElementById('year').textContent = new Date().getFullYear();