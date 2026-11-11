/* Settings page — profile, security, appearance, notifications */
(function () {
  var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var prefs = window.__PREFS || {};

  function toast(msg, type) { if (window.SubUI) SubUI.toast(msg, type); }

  function api(method, url, body) {
    var opts = { method: method, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } };
    if (body instanceof FormData) {
      opts.body = body;
    } else if (body) {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(body);
    }
    return fetch(url, opts).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (d) {
        return { ok: r.ok, status: r.status, data: d };
      });
    });
  }
  function errText(res) {
    var e = res.data && res.data.errors;
    if (e) { for (var k in e) { if (e[k] && e[k][0]) return e[k][0]; } }
    return (res.data && res.data.message) || 'Something went wrong.';
  }

  /* ── Appearance: segmented controls ── */
  function syncSeg() {
    document.querySelectorAll('.seg').forEach(function (seg) {
      var key = seg.dataset.pref;
      seg.querySelectorAll('button').forEach(function (b) {
        b.classList.toggle('active', b.dataset.val === prefs[key]);
      });
    });
  }
  document.querySelectorAll('.seg').forEach(function (seg) {
    seg.addEventListener('click', function (e) {
      var b = e.target.closest('button');
      if (!b) return;
      prefs[seg.dataset.pref] = b.dataset.val;
      syncSeg();
      if (window.SubUI) SubUI.applyPrefs(prefs);
      savePrefs();
    });
  });

  /* ── Notification toggles ── */
  document.querySelectorAll('[data-notify]').forEach(function (chk) {
    chk.checked = !!prefs[chk.dataset.notify];
    chk.addEventListener('change', function () {
      prefs[chk.dataset.notify] = chk.checked;
      savePrefs();
    });
  });

  var saveTimer = null;
  function savePrefs() {
    if (saveTimer) clearTimeout(saveTimer);
    saveTimer = setTimeout(function () {
      api('PUT', '/api/settings/preferences', prefs).then(function (res) {
        if (res.ok) { if (window.SubUI) SubUI.applyPrefs(res.data.preferences); toast('Preferences saved', 'success'); }
        else toast(errText(res), 'error');
      });
    }, 250);
  }

  /* ── Profile ── */
  var pf = document.getElementById('profileForm');
  if (pf) pf.addEventListener('submit', function (e) {
    e.preventDefault();
    var fd = new FormData(pf);
    api('PUT', '/api/settings/profile', {
      name: fd.get('name'), email: fd.get('email'), contact_number: fd.get('contact_number'),
    }).then(function (res) {
      toast(res.ok ? (res.data.message || 'Profile updated.') : errText(res), res.ok ? 'success' : 'error');
    });
  });

  /* ── Password ── */
  var pw = document.getElementById('passwordForm');
  if (pw) pw.addEventListener('submit', function (e) {
    e.preventDefault();
    var fd = new FormData(pw);
    api('POST', '/api/settings/password', {
      current_password: fd.get('current_password'),
      password: fd.get('password'),
      password_confirmation: fd.get('password_confirmation'),
    }).then(function (res) {
      if (res.ok) { pw.reset(); toast(res.data.message || 'Password updated.', 'success'); }
      else toast(errText(res), 'error');
    });
  });

  /* ── Photo ── */
  var img = document.getElementById('setAvatarImg');
  var input = document.getElementById('setAvatarInput');
  function pick() { if (input) input.click(); }
  var editBtn = document.getElementById('setAvatarBtn');
  if (editBtn) editBtn.addEventListener('click', pick);
  if (img) img.addEventListener('click', pick);
  if (input) input.addEventListener('change', function () {
    if (!input.files[0]) return;
    var fd = new FormData();
    fd.append('photo', input.files[0]);
    api('POST', '/api/settings/photo', fd).then(function (res) {
      if (res.ok) { img.src = res.data.avatar; toast('Photo updated', 'success'); }
      else toast(errText(res), 'error');
    });
  });

  syncSeg();

  /* ── System tools (admin only) — backups + activity log ── */
  var backupList = document.getElementById('backupList');
  var activityList = document.getElementById('activityList');
  if (backupList || activityList) {
    var fmtSize = function (b) {
      return b > 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB';
    };
    var renderRows = function (el, rowsHtml, emptyText) {
      if (!rowsHtml) { el.innerHTML = '<div class="set-sysrow set-sysrow-empty">' + emptyText + '</div>'; return; }
      el.innerHTML = rowsHtml;
    };
    var loadBackups = function () {
      if (!backupList) return;
      api('GET', '/api/backups').then(function (res) {
        if (!res.ok) { renderRows(backupList, '', 'Could not load backups.'); return; }
        var list = (res.data && res.data.backups) || [];
        var latest = document.getElementById('backupLatest');
        if (latest) latest.textContent = list.length ? ('Latest: ' + list[0].name + ' · ' + list[0].date) : 'No backups yet.';
        renderRows(backupList, list.map(function (b) {
          return '<div class="set-sysrow"><span>' + b.name + '</span><small>' + fmtSize(b.size) + ' · ' + b.date + '</small></div>';
        }).join(''), 'No backups yet.');
      });
    };
    var loadActivity = function () {
      if (!activityList) return;
      api('GET', '/api/activity-logs').then(function (res) {
        if (!res.ok) { renderRows(activityList, '', 'Could not load activity.'); return; }
        var logs = (res.data && res.data.logs) || [];
        renderRows(activityList, logs.map(function (l) {
          return '<div class="set-sysrow"><span>' + l.description + '</span><small>' + l.actor + ' · ' + l.time + '</small></div>';
        }).join(''), 'No activity yet.');
      });
    };
    var runBtn = document.getElementById('backupRunBtn');
    if (runBtn) runBtn.addEventListener('click', function () {
      runBtn.disabled = true;
      var old = runBtn.textContent;
      runBtn.textContent = 'Backing up…';
      api('POST', '/api/backups/run').then(function (res) {
        toast((res.data && res.data.message) || 'Backup finished.', res.data && res.data.success ? 'success' : 'error');
      }).catch(function () {
        toast('Backup failed.', 'error');
      }).then(function () {
        runBtn.disabled = false;
        runBtn.textContent = old;
        loadBackups();
      });
    });
    loadBackups();
    loadActivity();
  }
})();

/* ── Privacy: RA 10173 erasure request ── */
(function () {
  var btn = document.getElementById('erasureBtn');
  if (!btn) return;
  btn.addEventListener('click', function () {
    SubUI.confirm({
      title: 'Request account erasure?',
      message: 'This files a data-erasure request with the HOA office under RA 10173. Your account stays active until the office processes it (up to 15 working days).',
      confirmText: 'Submit request',
      danger: true,
      icon: '!'
    }).then(function (ok) {
      if (!ok) return;
      api('POST', '/api/settings/erasure-request', { reason: '' }).then(function (res) {
        toast((res.data && res.data.message) || 'Request submitted.', 'success');
      }).catch(function () {
        toast('Could not submit the request.', 'error');
      });
    });
  });
})();
