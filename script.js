/* ---------- Dati (šeit var aizvietot ar fetch('/api/tarifi')) ---------- */
const TARIFFS = [
  { id: 'eko',   name: 'Ekonomiskā', car: 'Škoda Fabia / VW Polo', seats: 4, board: 1.50, km: 0.75, wait: 0.10, tag: '' },
  { id: 'kom',   name: 'Komforta',   car: 'Škoda Octavia / VW Passat', seats: 4, board: 2.00, km: 0.95, wait: 0.12, tag: 'Populārākā' },
  { id: 'uni',   name: 'Universālis', car: 'Škoda Superb Combi', seats: 4, board: 2.50, km: 1.05, wait: 0.15, tag: '' },
  { id: 'bus',   name: 'Mikroautobuss', car: 'VW Caravelle (līdz 8 vietām)', seats: 8, board: 4.00, km: 1.35, wait: 0.20, tag: 'Grupām' },
];
const REVIEWS = [
  { name: 'Anna K.', stars: 5, text: 'Auto atbrauca piecās minūtēs, vadītājs laipns, cena sakrita ar aprēķinu.' },
  { name: 'Dmitrijs S.', stars: 5, text: 'Vienmēr izmantoju lidostas transfēru — laikā un bez stresa.' },
  { name: 'Ilze B.', stars: 4, text: 'Tīrs salons un ērta pasūtīšana iepriekš. Ieteiktu draugiem.' },
];
const fmt = n => n.toFixed(2).replace('.', ',') + ' €';

/* ---------- Tarifu sadaļa ---------- */
let filter = 'all';
function renderFilter() {
  const opts = [['all', 'Visi'], ['4', 'Līdz 4 vietām'], ['8', 'Grupām (8 vietas)']];
  document.getElementById('filter').innerHTML = opts.map(([v, l]) =>
    `<button type="button" data-v="${v}" class="${v === filter ? 'on' : ''}">${l}</button>`).join('');
}
function renderTariffs() {
  const list = TARIFFS.filter(t => filter === 'all' || String(t.seats) === filter);
  document.getElementById('tariffs').innerHTML = list.map(t => `
    <article class="card">
      ${t.tag ? `<span class="tag">${t.tag}</span>` : ''}
      <h3>${t.name}</h3>
      <p>${t.car}</p>
      <div class="price">${fmt(t.km)} <span>/ km</span></div>
      <ul>
        <li>Iekāpšana: ${fmt(t.board)}</li>
        <li>Gaidīšana: ${fmt(t.wait)} / min</li>
        <li>Vietu skaits: ${t.seats}</li>
      </ul>
    </article>`).join('');
}
document.getElementById('filter').addEventListener('click', e => {
  const b = e.target.closest('button'); if (!b) return;
  filter = b.dataset.v; renderFilter(); renderTariffs();
});

/* ---------- Kalkulators ---------- */
const cls = document.getElementById('cls'), km = document.getElementById('km'), wait = document.getElementById('wait');
cls.innerHTML = TARIFFS.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
function calc() {
  const t = TARIFFS.find(x => x.id === cls.value);
  const d = +km.value, w = Math.max(0, +wait.value || 0);
  document.getElementById('kmv').textContent = d;
  document.getElementById('sum').textContent = fmt(t.board + d * t.km + w * t.wait);
  document.getElementById('calcInfo').textContent = `${t.name}: ${fmt(t.board)} + ${d} km × ${fmt(t.km)}` + (w ? ` + ${w} min gaidīšana` : '');
}
[cls, km, wait].forEach(el => el.addEventListener('input', calc));

/* ---------- Atsauksmes ---------- */
document.getElementById('reviews').innerHTML = REVIEWS.map(r => `
  <blockquote class="card">
    <div class="stars" aria-label="${r.stars} no 5">${'<i class="fa-solid fa-star"></i>'.repeat(r.stars)}${'<i class="fa-regular fa-star"></i>'.repeat(5 - r.stars)}</div>
    <p style="margin-top:8px">${r.text}</p>
    <footer>— ${r.name}</footer>
  </blockquote>`).join('');

/* ---------- Krāsu režīms (saglabājas localStorage) ---------- */
const root = document.documentElement, themeBtn = document.getElementById('theme');
function applyTheme(t) {
  root.setAttribute('data-theme', t);
  themeBtn.innerHTML = t === 'dark' ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
  try { localStorage.setItem('taxi-theme', t); } catch (e) {}
}
themeBtn.addEventListener('click', () => applyTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'));
applyTheme(root.getAttribute('data-theme'));

/* ---------- Izvēlne, aktīvā sadaļa, forma ---------- */
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
renderFilter(); renderTariffs(); calc();