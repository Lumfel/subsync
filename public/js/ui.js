/* ─────────────────────────────────────────────
   SubSync UI helpers — toasts, global search,
   skeleton placeholders, quick actions
   Loaded on admin/officer/resident portals.
   ───────────────────────────────────────────── */
(function () {
  const AB = window.APP_BASE || '';

  /* ── Icons ────────────────────────────────────────────────
     JS mirror of resources/views/components/icon.blade.php so script-rendered
     markup (empty states, lists) uses the same monochrome line icons instead
     of platform emoji. Usage: SubUI.icon('message', 16) returns SVG markup. */
  const ICON_PATHS = {
    home: '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/>',
    megaphone: '<path d="m3 11 18-6v14L3 13v-2Z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
    message: '<path d="M21 15a2 2 0 0 1-2 2H8l-5 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10Z"/>',
    clipboard: '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>',
    lightbulb: '<path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.4 1 2.3h6c0-.9.4-1.8 1-2.3A7 7 0 0 0 12 2Z"/>',
    folder: '<path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7l-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/>',
    users: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    user: '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    check: '<path d="m4 12.5 5 5L20 6.5"/>',
    x: '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
    calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4"/><path d="M16 3v4"/><path d="M3 11h18"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    phone: '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
    bell: '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
    download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
    refresh: '<path d="M21 12a9 9 0 1 1-2.6-6.4"/><path d="M21 3v6h-6"/>',
    trash: '<path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/>',
    archive: '<rect x="3" y="4" width="18" height="5" rx="1"/><path d="M5 9v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9"/><path d="M10 13h4"/>',
    scroll: '<path d="M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 0 2 2H8a2 2 0 0 1-2-2V3Z"/><path d="M10 7h6"/><path d="M10 11h6"/>',
    search: '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
    alert: '<path d="m10.3 3.9-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
    file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>',
    coins: '<circle cx="8" cy="8" r="6"/><path d="M18.1 10.9a6 6 0 1 1-8.2 8.2"/>',
      send: '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
      'map-pin': '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
      paperclip: '<path d="m21.4 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/>',
    info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
  };
  function icon(name, size = 16) {
    const p = ICON_PATHS[name];
    if (!p) return '';
    return '<svg class="ui-icon" width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' + p + '</svg>';
  }

  /* ── Toasts ── */
  function toast(msg, type = 'info', ms = 3200) {
    let wrap = document.querySelector('.ui-toast-wrap');
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.className = 'ui-toast-wrap';
      wrap.setAttribute('role', type === 'error' ? 'alert' : 'status');
      wrap.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
      document.body.appendChild(wrap);
    }
    const el = document.createElement('div');
    el.className = 'ui-toast ' + type;
    const toastIcon = type === 'success' ? 'check' : type === 'error' ? 'alert' : 'info';
    el.innerHTML = '<span class="ui-toast-ic">' + icon(toastIcon, 16) + '</span><span>' + msg + '</span>';
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
    let reqId = 0;
    let debounce = null;

    function esc(v) { const d = document.createElement('div'); d.textContent = (v == null ? '' : v); return d.innerHTML; }

    function render(list) {
      items = list;
      if (!list.length) {
        results.innerHTML = '<div class="ui-search-empty">' + (input.value.trim() ? 'No matches found.' : 'Type to search across the system…') + '</div>';
        return;
      }
      results.innerHTML = list.map((it, i) =>
        '<button class="ui-search-item" data-i="' + i + '">' +
          '<span class="s-tag">' + esc(it.tag) + '</span>' +
          '<span class="s-label">' + esc(it.label) + '</span>' +
          (it.sub ? '<div class="s-sub">' + esc(it.sub) + '</div>' : '') +
        '</button>').join('');
    }
    function go(it) {
      close();
      if (!it || !it.go) return;
      if (window.PortalNav && typeof window.PortalNav.go === 'function') window.PortalNav.go(it.go);
      else if (typeof nav === 'function') nav(it.go);
      else if (typeof switchTab === 'function') switchTab(it.go);
    }
    function fetchResults(q) {
      const id = ++reqId;
      if (!q.trim()) { render([]); return; }
      fetch('/api/search?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(d => { if (id === reqId) render(d.results || []); })
        .catch(() => { if (id === reqId) render([]); });
    }
    input.addEventListener('input', () => { clearTimeout(debounce); debounce = setTimeout(() => fetchResults(input.value), 180); });
    results.addEventListener('click', (e) => {
      const btn = e.target.closest('.ui-search-item');
      if (!btn) return;
      go(items[+btn.dataset.i]);
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
      if (e.key === 'Enter' && cur) { go(items[+cur.dataset.i]); }
    });
    searchModal.addEventListener('click', (e) => { if (e.target === searchModal) close(); });
    render([]);

    setTimeout(() => input.focus(), 30);
  }
  function close() { if (searchModal) { searchModal.remove(); searchModal = null; } }

  /* ── Confirmation dialog (replaces the default window.confirm) ── */
  function confirmDialog(opts = {}) {
    const o = (typeof opts === 'string') ? { message: opts } : opts;
    return new Promise((resolve) => {
      const overlay = document.createElement('div');
      overlay.className = 'ui-confirm-overlay';
      overlay.innerHTML =
        '<div class="ui-confirm-box' + (o.danger ? ' danger' : '') + '" role="dialog" aria-modal="true">' +
          '<div class="ui-confirm-icon">' + (o.icon || (o.danger ? '!' : '?')) + '</div>' +
          '<div class="ui-confirm-title"></div>' +
          '<div class="ui-confirm-msg"></div>' +
          '<div class="ui-confirm-actions">' +
            '<button type="button" class="btn btn-ghost ui-confirm-cancel"></button>' +
            '<button type="button" class="btn ' + (o.danger ? 'btn-danger' : 'btn-primary') + ' ui-confirm-ok"></button>' +
          '</div>' +
        '</div>';
      overlay.querySelector('.ui-confirm-title').textContent = o.title || 'Please confirm';
      overlay.querySelector('.ui-confirm-msg').textContent = o.message || '';
      overlay.querySelector('.ui-confirm-cancel').textContent = o.cancelText || 'Cancel';
      const ok = overlay.querySelector('.ui-confirm-ok');
      ok.textContent = o.confirmText || 'Confirm';
      document.body.appendChild(overlay);
      requestAnimationFrame(() => overlay.classList.add('show'));

      const finish = (val) => {
        overlay.classList.remove('show');
        document.removeEventListener('keydown', onKey);
        setTimeout(() => overlay.remove(), 220);
        resolve(val);
      };
      const onKey = (e) => { if (e.key === 'Escape') finish(false); else if (e.key === 'Enter') finish(true); };
      overlay.querySelector('.ui-confirm-cancel').addEventListener('click', () => finish(false));
      ok.addEventListener('click', () => finish(true));
      overlay.addEventListener('click', (e) => { if (e.target === overlay) finish(false); });
      document.addEventListener('keydown', onKey);
      ok.focus();
    });
  }

  /* Disable a button while an async action is in flight (prevents double-submit). */
  function busy(btn, isBusy) {
    if (!btn) return;
    if (isBusy) { btn.dataset._busy = '1'; btn.disabled = true; btn.setAttribute('aria-busy', 'true'); }
    else { delete btn.dataset._busy; btn.disabled = false; btn.removeAttribute('aria-busy'); }
  }

  /* Wrap a click handler so its button is disabled for the whole async call. */
  function busyClick(btn, fn) {
    busy(btn, true);
    Promise.resolve()
      .then(() => fn())
      .catch((e) => { try { console.error(e); } catch (x) {} })
      .finally(() => busy(btn, false));
  }

  /* Inline field validation: show a message under the field and focus the first invalid one. */
  function ensureFieldErrorStyle() {
    if (document.getElementById('subui-field-error-style')) return;
    const s = document.createElement('style');
    s.id = 'subui-field-error-style';
    s.textContent = '.field-error{color:#BD5B5B;font-size:11px;margin:2px 0 8px;display:block;}' +
      '[aria-invalid="true"]{border-color:#BD5B5B !important;box-shadow:none !important;}';
    document.head.appendChild(s);
  }
  function clearFieldError(el) {
    if (!el) return;
    el.removeAttribute('aria-invalid');
    const err = el.parentElement && el.parentElement.querySelector('.field-error');
    if (err) err.remove();
  }
  function showFieldError(el, msg) {
    if (!el) return;
    ensureFieldErrorStyle();
    el.setAttribute('aria-invalid', 'true');
    let err = el.parentElement.querySelector('.field-error');
    if (!err) { err = document.createElement('div'); err.className = 'field-error'; err.setAttribute('role', 'alert'); el.parentElement.appendChild(err); }
    err.textContent = msg;
  }
  /* fields: [[value, inputId, message], ...] -> true when all present. */
  function required(fields) {
    let first = null;
    fields.forEach(([val, id, msg]) => {
      const el = document.getElementById(id);
      if (!el) return;
      clearFieldError(el);
      if (!val) { showFieldError(el, msg); if (!first) first = el; }
    });
    if (first) { try { first.focus(); } catch (e) {} return false; }
    return true;
  }

  /* One consistent money/date format everywhere. */
  function money(value, opts) {
    const n = Number(value || 0);
    return '₱' + n.toLocaleString('en-PH', Object.assign({ minimumFractionDigits: 2, maximumFractionDigits: 2 }, opts || {}));
  }
  function dateFmt(value, opts) {
    if (!value) return '';
    const d = (value instanceof Date) ? value : new Date(value);
    if (isNaN(d.getTime())) return String(value);
    return d.toLocaleDateString('en-PH', Object.assign({ year: 'numeric', month: 'short', day: 'numeric' }, opts || {}));
  }

  /* ── Deterministic per-person avatar colour ── */
  const AVATAR_PALETTE = [
    { bg: 'rgba(122,180,240,0.22)', fg: '#4f7ba3' },
    { bg: 'rgba(185,154,245,0.22)', fg: '#7d6ba8' },
    { bg: 'rgba(130,201,138,0.22)', fg: '#5c7d61' },
    { bg: 'rgba(240,192,96,0.28)',  fg: '#a9791f' },
    { bg: 'rgba(232,168,124,0.28)', fg: '#a0653a' },
    { bg: 'rgba(199,80,80,0.18)',   fg: '#a8443e' },
    { bg: 'rgba(122,200,200,0.22)', fg: '#3f7e7e' },
    { bg: 'rgba(200,140,200,0.20)', fg: '#8a4f8a' },
  ];
  function avatarColor(name) {
    const s = String(name || '?');
    let h = 0;
    for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0;
    return AVATAR_PALETTE[h % AVATAR_PALETTE.length];
  }

  /* ── Appearance preferences (theme / density / text size) ── */
  function resolvedTheme(theme) {
    if (theme === 'system') {
      return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
    }
    return theme === 'dark' ? 'dark' : 'light';
  }
  function applyPrefs(prefs) {
    if (!prefs) return;
    const root = document.documentElement;
    const theme = prefs.theme || 'light';
    root.setAttribute('data-theme-pref', theme);
    root.setAttribute('data-theme', resolvedTheme(theme));
    root.setAttribute('data-density', prefs.density || 'comfortable');
    root.setAttribute('data-font', prefs.font_scale || 'default');
    try { window.dispatchEvent(new CustomEvent('subsync:theme', { detail: { theme: root.getAttribute('data-theme') } })); } catch (e) {}
  }
  function loadPrefs() {
    let prefs = null;
    try { prefs = window.__PREFS || JSON.parse(localStorage.getItem('subsync.prefs') || 'null'); } catch (e) {}
    if (prefs) {
      applyPrefs(prefs);
      try { localStorage.setItem('subsync.prefs', JSON.stringify(prefs)); } catch (e) {}
    }
    return prefs || {};
  }
  loadPrefs();
  if (window.matchMedia) {
    try {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
        if (document.documentElement.getAttribute('data-theme-pref') === 'system') {
          document.documentElement.setAttribute('data-theme', resolvedTheme('system'));
        }
      });
    } catch (e) {}
  }

  /* ── CSV download (fetch+blob so ngrok's interstitial is bypassed and headers are honoured) ── */
  async function exportCsv(url) {
    try {
      const res = await fetch(url, { headers: { 'Accept': 'text/csv,application/octet-stream' } });
      if (!res.ok) throw new Error('export failed');
      const blob = await res.blob();
      const cd = res.headers.get('Content-Disposition') || '';
      const m = /filename="?([^"]+)"?/.exec(cd);
      const name = m ? m[1] : 'export.csv';
      const objUrl = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = objUrl;
      a.download = name;
      document.body.appendChild(a);
      a.click();
      setTimeout(() => { URL.revokeObjectURL(objUrl); a.remove(); }, 0);
    } catch (e) {
      toast('Export failed. Please try again.', 'error');
    }
  }

  /* ── Fetch-based EventSource ──────────────────────────────
     Native EventSource cannot send request headers, so behind ngrok (which
     requires the "ngrok-skip-browser-warning" header) it received ngrok's
     warning page instead of the event stream and never delivered updates.
     This polyfill keeps the exact EventSource API the portals use, but reads
     the stream with the already-patched fetch (which adds that header, the
     X-Portal header and the base-path prefix). */
  if (typeof ReadableStream !== 'undefined' && typeof TextDecoder !== 'undefined') {
    function FetchEventSource(url) {
      this.url = url;
      this.readyState = 0; // CONNECTING
      this._listeners = {};
      this._closed = false;
      this._abort = null;
      this.onerror = null;
      this.onmessage = null;
      this._connect();
    }
    FetchEventSource.CONNECTING = 0;
    FetchEventSource.OPEN = 1;
    FetchEventSource.CLOSED = 2;
    FetchEventSource.prototype.addEventListener = function (name, fn) {
      (this._listeners[name] = this._listeners[name] || []).push(fn);
    };
    FetchEventSource.prototype.removeEventListener = function (name, fn) {
      const arr = this._listeners[name] || [];
      const i = arr.indexOf(fn);
      if (i >= 0) arr.splice(i, 1);
    };
    FetchEventSource.prototype._emit = function (name, data) {
      const ev = { type: name, data: data };
      (this._listeners[name] || []).slice().forEach((fn) => { try { fn(ev); } catch (e) { try { console.error(e); } catch (_) {} } });
      if (name === 'message' && typeof this.onmessage === 'function') { try { this.onmessage(ev); } catch (e) {} }
    };
    FetchEventSource.prototype.close = function () {
      this._closed = true;
      this.readyState = 2;
      try { if (this._abort) this._abort.abort(); } catch (e) {}
    };
    FetchEventSource.prototype._fail = function () {
      if (this._closed) return;
      this.readyState = 2;
      if (typeof this.onerror === 'function') { try { this.onerror({ type: 'error' }); } catch (e) {} }
    };
    FetchEventSource.prototype._connect = function () {
      const self = this;
      const ac = (typeof AbortController !== 'undefined') ? new AbortController() : null;
      this._abort = ac;
      fetch(this.url, { headers: { 'Accept': 'text/event-stream' }, cache: 'no-store', signal: ac ? ac.signal : undefined })
        .then((res) => {
          if (!res.ok || !res.body || !res.body.getReader) throw new Error('stream ' + res.status);
          self.readyState = 1; // OPEN
          const reader = res.body.getReader();
          const dec = new TextDecoder();
          let buf = '';
          const pump = () => reader.read().then((r) => {
            if (r.done) { self.readyState = 2; self._fail(); return; }
            buf += dec.decode(r.value, { stream: true });
            let i;
            while ((i = buf.indexOf('\n\n')) !== -1) {
              const raw = buf.slice(0, i); buf = buf.slice(i + 2);
              let name = 'message', data = '';
              raw.split('\n').forEach((line) => {
                if (line.indexOf('event:') === 0) name = line.slice(6).trim();
                else if (line.indexOf('data:') === 0) data += line.slice(5).trim();
              });
              if (data !== '') self._emit(name, data);
            }
            pump();
          }).catch(() => self._fail());
          pump();
        })
        .catch(() => self._fail());
    };
    try { window.EventSource = FetchEventSource; } catch (e) {}
  }

  /* Expose */
  window.SubUI = { icon, toast, skeleton, showSkeleton, openSearch, closeSearch: close, confirm: confirmDialog, busy, busyClick, required, showFieldError, clearFieldError, money, dateFmt, avatarColor, applyPrefs, loadPrefs, exportCsv };

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

  /* Accessibility: give every portal modal dialog semantics, move focus in,
     restore it on close, and close on Escape. Pure enhancement — it never
     changes how a modal is opened. */
  (function () {
    let lastFocus = null;
    const isOpen = (el) => el.classList.contains('open') || el.classList.contains('active');

    function enhance(el) {
      if (!el.hasAttribute('role')) el.setAttribute('role', 'dialog');
      el.setAttribute('aria-modal', 'true');
    }
    function focusFirst(el) {
      const f = el.querySelector('input:not([type=hidden]), select, textarea, button, a[href], [tabindex]:not([tabindex="-1"])');
      if (f) setTimeout(() => { try { f.focus(); } catch (e) {} }, 30);
    }
    function close(el) {
      const id = (el.id || '').replace(/^modal-/, '');
      if (typeof window.closeModal === 'function' && id) { try { window.closeModal(id); } catch (e) {} }
      el.classList.remove('open', 'active');
    }

    document.querySelectorAll('.modal-overlay').forEach(enhance);

    new MutationObserver((muts) => {
      muts.forEach((mut) => {
        if (mut.type !== 'attributes' || mut.attributeName !== 'class') return;
        const el = mut.target;
        if (!el.classList || !el.classList.contains('modal-overlay')) return;
        enhance(el);
        if (isOpen(el)) { lastFocus = document.activeElement; focusFirst(el); }
        else if (lastFocus && typeof lastFocus.focus === 'function') { try { lastFocus.focus(); } catch (e) {} lastFocus = null; }
      });
    }).observe(document.documentElement, { subtree: true, attributes: true, attributeFilter: ['class'] });

    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape') return;
      const open = [...document.querySelectorAll('.modal-overlay')].filter(isOpen);
      if (open.length) close(open[open.length - 1]);
    });
  })();

  /* Accessibility: upgrade the portal navigation (sidebar tabs / tab bars) so it
     is keyboard operable and exposes proper tab semantics. Pure enhancement. */
  (function enhanceNavA11y() {
    function markActive(el) {
      const group = el.parentElement;
      if (!group) return;
      group.querySelectorAll('[role="tab"]').forEach((t) => t.setAttribute('aria-selected', t === el ? 'true' : 'false'));
    }
    function upgrade(el) {
      if (el.dataset._a11y) return;
      el.dataset._a11y = '1';
      el.setAttribute('role', 'tab');
      if (!el.hasAttribute('tabindex')) el.setAttribute('tabindex', '0');
      el.setAttribute('aria-selected', el.classList.contains('active') ? 'true' : 'false');
      el.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); el.click(); }
      });
      el.addEventListener('click', () => setTimeout(() => markActive(el), 0));
    }
    function scan() {
      document.querySelectorAll('li.tab-btn[onclick]').forEach((li) => {
        if (li.parentElement && !li.parentElement.hasAttribute('role')) li.parentElement.setAttribute('role', 'tablist');
        upgrade(li);
      });
      document.querySelectorAll('nav.sidebar-nav .nav-item[onclick]').forEach((item) => {
        if (item.parentElement && !item.parentElement.hasAttribute('role')) item.parentElement.setAttribute('role', 'tablist');
        upgrade(item);
      });
      document.querySelectorAll('.menu-toggle, [onclick="toggleMobileSidebar()"], [onclick="toggleSidebar()"]').forEach((btn) => {
        if (!btn.hasAttribute('aria-label')) btn.setAttribute('aria-label', 'Toggle navigation menu');
        if (!btn.hasAttribute('aria-expanded')) btn.setAttribute('aria-expanded', 'false');
      });
    }
    scan();

    new MutationObserver((muts) => {
      let addRemove = false;
      muts.forEach((m) => {
        if (m.type === 'childList') { addRemove = true; return; }
        if (m.attributeName === 'class') {
          if (m.target.classList && (m.target.classList.contains('tab-btn') || m.target.classList.contains('nav-item'))) markActive(m.target);
          if (m.target.classList && m.target.classList.contains('sidebar')) {
            const open = m.target.classList.contains('show') || m.target.classList.contains('active') || m.target.classList.contains('open');
            document.querySelectorAll('[aria-label="Toggle navigation menu"]').forEach((b) => b.setAttribute('aria-expanded', open ? 'true' : 'false'));
          }
        }
      });
      if (addRemove) scan();
    }).observe(document.documentElement, { subtree: true, childList: true, attributes: true, attributeFilter: ['class'] });
  })();
})();