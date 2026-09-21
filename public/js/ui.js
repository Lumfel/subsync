/* ─────────────────────────────────────────────
   SubSync UI helpers — toasts, global search,
   skeleton placeholders, quick actions
   Loaded on admin/officer/resident portals.
   ───────────────────────────────────────────── */
(function () {
  const AB = window.APP_BASE || '';

  /* ── Toasts ── */
  function toast(msg, type = 'info', ms = 3200) {
    let wrap = document.querySelector('.ui-toast-wrap');
    if (!wrap) { wrap = document.createElement('div'); wrap.className = 'ui-toast-wrap'; document.body.appendChild(wrap); }
    const el = document.createElement('div');
    el.className = 'ui-toast ' + type;
    const icon = type === 'success' ? '✅' : type === 'error' ? '⚠️' : 'ℹ️';
    el.innerHTML = '<span>' + icon + '</span><span>' + msg + '</span>';
    wrap.appendChild(el);
    requestAnimationFrame(() => el.classList.add('show'));
    setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 300); }, ms);
  }

  /* ── Skeletons ── */
  function skeleton(rows = 3, card = false) {
    const cls = card ? 'sk-card' : 'sk-row';
    let html = '';
    for (let i = 0; i < rows; i++) html += '<div class="skeleton ' + cls + '"></div>';
    return html;
  }
  function showSkeleton(el, rows, card) { if (el) el.innerHTML = skeleton(rows, card); }

  /* ── Global search ── */
  let searchModal = null;
  function openSearch() {
    if (searchModal) { searchModal.remove(); searchModal = null; }
    searchModal = document.createElement('div');
    searchModal.className = 'ui-modal';
    searchModal.innerHTML =
      '<div class="ui-modal-box">' +
        '<input class="ui-search-input" type="text" placeholder="Search residents, households, issues, announcements…" autofocus>' +
        '<div class="ui-search-results"></div>' +
        '<div class="ui-search-footer"><span><kbd>Esc</kbd> close</span><span><kbd>↑</kbd><kbd>↓</kbd> navigate · <kbd>Enter</kbd> open</span></div>' +
      '</div>';
    document.body.appendChild(searchModal);
    const input = searchModal.querySelector('.ui-search-input');
    const results = searchModal.querySelector('.ui-search-results');
    let items = [];

    function render(list) {
      items = list;
      const wrap = results;
      if (!list.length) { wrap.innerHTML = '<div class="ui-search-empty">No matches found.</div>'; return; }
      wrap.innerHTML = list.map((it, i) =>
        '<button class="ui-search-item" data-i="' + i + '">' +
          '<span class="s-tag">' + it.tag + '</span>' +
          '<span class="s-label">' + it.label + '</span>' +
          (it.sub ? '<div class="s-sub">' + it.sub + '</div>' : '') +
        '</button>').join('');
    }
    function build(query) {
      const q = query.toLowerCase().trim();
      const out = [];
      (window.__subData || {}).residents.forEach(r => {
        if (!q || (r.name||'').toLowerCase().includes(q) || (r.block_lot||'').toLowerCase().includes(q) || (r.email||'').toLowerCase().includes(q)) {
          out.push({ tag: 'Resident', label: r.name, sub: (r.block_lot || '—') + (r.email ? ' · ' + r.email : '') + ' · ' + r.status, go: 'residents' });
        }
      });
      (window.__subData || {}).households.forEach(h => {
        if (!q || (h.block_lot_number||'').toLowerCase().includes(q) || (h.status||'').toLowerCase().includes(q)) {
          out.push({ tag: 'Household', label: h.block_lot_number, sub: h.status, go: 'residents' });
        }
      });
      (window.__subData || {}).issues.forEach(i => {
        if (!q || (i.title||'').toLowerCase().includes(q) || (i.category||'').toLowerCase().includes(q) || (i.status||'').toLowerCase().includes(q)) {
          out.push({ tag: 'Issue', label: i.title, sub: i.category + ' · ' + i.status, go: 'issues' });
        }
      });
      (window.__subData || {}).announcements.forEach(a => {
        if (!q || (a.title||'').toLowerCase().includes(q)) {
          out.push({ tag: 'Announcement', label: a.title, sub: (a.tag || 'notice') + ' · ' + a.created_at, go: 'announcements' });
        }
      });
      return out.slice(0, 30);
    }
    input.addEventListener('input', () => render(build(input.value)));
    results.addEventListener('click', (e) => {
      const btn = e.target.closest('.ui-search-item');
      if (!btn) return;
      const it = items[+btn.dataset.i];
      close(); if (it && it.go && typeof nav === 'function') nav(it.go);
    });
    results.addEventListener('mouseover', (e) => {
      const btn = e.target.closest('.ui-search-item');
      results.querySelectorAll('.ui-search-item').forEach(x => x.classList.remove('active'));
      if (btn) btn.classList.add('active');
    });
    searchModal.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') { close(); return; }
      const cur = results.querySelector('.ui-search-item.active');
      const all = [...results.querySelectorAll('.ui-search-item')];
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        const idx = cur ? all.indexOf(cur) : -1;
        const next = e.key === 'ArrowDown' ? (idx + 1) % all.length : (idx - 1 + all.length) % all.length;
        all.forEach(x => x.classList.remove('active'));
        if (all[next]) { all[next].classList.add('active'); all[next].scrollIntoView({ block: 'nearest' }); }
      }
      if (e.key === 'Enter' && cur) { const it = items[+cur.dataset.i]; close(); if (it && it.go && typeof nav === 'function') nav(it.go); }
    });
    searchModal.addEventListener('click', (e) => { if (e.target === searchModal) close(); });
    render(build(''));
    setTimeout(() => input.focus(), 30);
  }
  function close() { if (searchModal) { searchModal.remove(); searchModal = null; } }

  /* Expose */
  window.SubUI = { toast, skeleton, showSkeleton, openSearch, closeSearch: close };

  /* Keyboard shortcut: Ctrl/Cmd+K or "/" */
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openSearch(); }
    else if (e.key === 'Escape') { close(); }
  });

  /* Lock body scroll while any modal is open (so the page behind doesn't scroll) */
  (function(){
    var lockModals = new Set();
    function syncLock(){
      var anyOpen = false;
      document.querySelectorAll('.modal-overlay.open').forEach(function(m){ anyOpen = true; lockModals.add(m); });
      document.body.style.overflow = anyOpen ? 'hidden' : '';
    }
    // Watch for .open class toggles on modals
    var mo = new MutationObserver(function(muts){
      muts.forEach(function(mut){
        if (mut.type !== 'attributes' || mut.attributeName !== 'class') return;
        if (mut.target.classList && mut.target.classList.contains('modal-overlay')) syncLock();
      });
    });
    mo.observe(document.documentElement, { subtree: true, attributes: true, attributeFilter: ['class'] });
    // Also restore on modal removal
    setInterval(function(){
      if(!document.querySelector('.modal-overlay.open') && document.body.style.overflow === 'hidden'){
        document.body.style.overflow = '';
      }
    }, 800);
  })();
})();