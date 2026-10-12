/* Notification center — bell + popover for resident & officer portals.
   Admin uses its own #alertsButton/#alertsPopover, so this no-ops there. */
(function () {
  var btn = document.getElementById('ncButton');
  if (!btn) return;
  var pop = document.getElementById('ncPop');
  var list = document.getElementById('ncList');
  var badge = document.getElementById('ncBadge');
  var alerts = [];

  function seenKey() { return 'subsync.seenAlerts.' + (window.__ACTOR || 'me'); }
  function seen() { try { return JSON.parse(localStorage.getItem(seenKey()) || '[]'); } catch (e) { return []; } }
  function saveSeen(a) { try { localStorage.setItem(seenKey(), JSON.stringify(a.slice(-200))); } catch (e) {} }
  function esc(v) { var d = document.createElement('div'); d.textContent = (v == null ? '' : v); return d.innerHTML; }
  function timeAgo(iso) {
    try {
      var s = (Date.now() - new Date(iso).getTime()) / 1000;
      if (s < 60) return 'just now';
      if (s < 3600) return Math.floor(s / 60) + 'm ago';
      if (s < 86400) return Math.floor(s / 3600) + 'h ago';
      return new Date(iso).toLocaleDateString();
    } catch (e) { return ''; }
  }

  function render() {
    var sn = seen();
    var unread = alerts.filter(function (a) { return sn.indexOf(a.id) < 0; }).length;
    if (badge) { badge.textContent = unread; badge.style.display = unread ? 'inline-flex' : 'none'; }
    list.innerHTML = alerts.length ? alerts.map(function (a) {
      var isUnread = sn.indexOf(a.id) < 0;
      return '<div class="nc-item' + (isUnread ? ' unread' : '') + '" data-tab="' + esc(a.tab) + '" data-id="' + esc(a.id) + '">' +
        '<span class="nc-mark ' + esc(a.severity || 'notice') + '"></span>' +
        '<div><div class="nc-title">' + esc(a.title) + '</div><div class="nc-msg">' + esc(a.message) + '</div><div class="nc-time">' + esc(timeAgo(a.created_at)) + '</div></div>' +
        '</div>';
    }).join('') : '<div class="nc-empty">No notifications.</div>';
  }

  function load() {
    fetch('/api/alerts', { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) { alerts = Array.isArray(d.alerts) ? d.alerts : []; render(); })
      .catch(function () { if (list) list.innerHTML = '<div class="nc-empty">Could not load notifications.</div>'; });
  }

  function toggle(e) {
    if (e) e.stopPropagation();
    var open = pop.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) load();
  }

  btn.addEventListener('click', toggle);
  document.addEventListener('click', function (e) {
    if (!pop.contains(e.target) && !btn.contains(e.target)) {
      pop.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    }
  });
  if (list) list.addEventListener('click', function (e) {
    var item = e.target.closest('.nc-item');
    if (!item) return;
    var sn = seen();
    if (sn.indexOf(item.dataset.id) < 0) { sn.push(item.dataset.id); saveSeen(sn); }
    render();
    pop.classList.remove('open');
    var tab = item.dataset.tab;
    if (window.PortalNav && window.PortalNav.go) window.PortalNav.go(tab);
    else if (typeof nav === 'function') nav(tab);
    else if (typeof switchTab === 'function') switchTab(tab);
  });

  load();
  setInterval(load, 60000);
})();
