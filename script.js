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

document.getElementById('form').addEventListener('submit', e => {
  e.preventDefault();
  const msg = document.getElementById('msg');
  const ok = ['name', 'tel', 'txt'].every(id => document.getElementById(id).value.trim());
  msg.style.color = ok ? '#2e9e5b' : '#d64545';
  msg.textContent = ok ? 'Paldies! Mēs sazināsimies ar jums tuvākajā laikā.' : 'Aizpildi visus laukus.';
  if (ok) e.target.reset();
});

document.getElementById('year').textContent = new Date().getFullYear();