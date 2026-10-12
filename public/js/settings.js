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
})();
