<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Settings — SubSync</title>
<script>
window.APP_BASE = '{{ request()->getBaseUrl() }}';
window.__PORTAL = @json($actor['type']);
window.__PREFS = @json($prefs);
window.__ACTOR = @json($actor['type']);
(function () {
    var base = window.APP_BASE || '';
    var prefix = function (url) {
        if (base && typeof url === 'string' && url.charAt(0) === '/' && url.charAt(1) !== '/') return base + url;
        return url;
    };
    var origFetch = window.fetch;
    window.fetch = function (url, opts) {
        opts = opts || {};
        var h = Object.assign({}, opts.headers || {}, { 'ngrok-skip-browser-warning': '1' });
        if (window.__PORTAL) h['X-Portal'] = window.__PORTAL;
        opts.headers = h;
        return origFetch.call(this, prefix(url), opts);
    };
})();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/ui-polish.css') . '?v=' . filemtime(public_path('css/ui-polish.css')) }}">
<link rel="stylesheet" href="{{ asset('css/settings.css') . '?v=' . filemtime(public_path('css/settings.css')) }}">
<script src="{{ asset('js/ui.js') . '?v=' . filemtime(public_path('js/ui.js')) }}"></script>
</head>
<body class="settings-body">

<header class="set-header">
  <a class="set-brand" href="{{ $portalUrl }}"><span class="set-brand-mark">🏡</span> SubSync</a>
  <a class="set-back" href="{{ $portalUrl }}">&larr; Back to portal</a>
</header>

<main class="set-wrap">
  <div class="set-titlebar">
    <h1>Settings</h1>
    <p>Manage your profile, security, appearance, and notifications.</p>
  </div>

  <!-- Profile -->
  <section class="card set-card">
    <div class="set-card-head">
      <h2>Profile</h2>
      <span class="set-role-chip">{{ ucfirst($actor['type']) }}</span>
    </div>
    <div class="set-profile">
      <div class="set-avatar">
        <img id="setAvatarImg" src="{{ $actor['user']->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($actor['name']).'&background=86653D&color=fff&size=140' }}" alt="Profile photo">
        <button type="button" class="set-avatar-edit" id="setAvatarBtn" title="Change photo">Change</button>
        <input type="file" id="setAvatarInput" accept="image/*" hidden>
      </div>
      <form id="profileForm" class="set-form" autocomplete="off">
        <div class="set-field">
          <label for="pfName">Full name</label>
          <input id="pfName" name="name" type="text" value="{{ $actor['user']->name }}" required maxlength="150">
        </div>
        <div class="set-field">
          <label for="pfEmail">Email address</label>
          <input id="pfEmail" name="email" type="email" value="{{ $actor['user']->email }}" required maxlength="150">
        </div>
        <div class="set-field">
          <label for="pfContact">Contact number</label>
          <input id="pfContact" name="contact_number" type="text" value="{{ $actor['user']->contact_number }}" maxlength="20" placeholder="e.g. 0917 000 0000">
        </div>
        <div class="set-actions">
          <button class="btn btn-primary" type="submit">Save profile</button>
        </div>
      </form>
    </div>
  </section>

  <!-- Security -->
  <section class="card set-card">
    <div class="set-card-head"><h2>Security</h2></div>
    <form id="passwordForm" class="set-form set-form-narrow" autocomplete="off">
      <div class="set-field">
        <label for="pwCurrent">Current password</label>
        <input id="pwCurrent" name="current_password" type="password" autocomplete="current-password" placeholder="Leave blank if you signed in with Google">
      </div>
      <div class="set-field">
        <label for="pwNew">New password</label>
        <input id="pwNew" name="password" type="password" minlength="8" autocomplete="new-password" required>
      </div>
      <div class="set-field">
        <label for="pwConfirm">Confirm new password</label>
        <input id="pwConfirm" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
      </div>
      <div class="set-actions">
        <button class="btn btn-primary" type="submit">Update password</button>
      </div>
    </form>
  </section>

  <!-- Appearance -->
  <section class="card set-card">
    <div class="set-card-head"><h2>Appearance</h2></div>
    <div class="set-grid">
      <div class="set-field">
        <label>Theme</label>
        <div class="seg" data-pref="theme">
          <button type="button" data-val="light">Light</button>
          <button type="button" data-val="dark">Dark</button>
          <button type="button" data-val="system">System</button>
        </div>
      </div>
      <div class="set-field">
        <label>Density</label>
        <div class="seg" data-pref="density">
          <button type="button" data-val="comfortable">Comfortable</button>
          <button type="button" data-val="compact">Compact</button>
        </div>
      </div>
      <div class="set-field">
        <label>Text size</label>
        <div class="seg" data-pref="font_scale">
          <button type="button" data-val="small">Small</button>
          <button type="button" data-val="default">Default</button>
          <button type="button" data-val="large">Large</button>
        </div>
      </div>
    </div>
    <p class="set-hint">Appearance changes apply instantly and are saved to your account.</p>
  </section>

  <!-- Notifications -->
  <section class="card set-card">
    <div class="set-card-head"><h2>Notifications</h2></div>
    <p class="set-hint" style="margin-top:0;">Choose what appears in your notification center.</p>
    <div class="set-toggles">
      <label class="set-toggle"><span><strong>Announcements</strong><small>New community announcements</small></span><input type="checkbox" data-notify="notify_announcements"></label>
      <label class="set-toggle"><span><strong>Payments</strong><small>Payment validations and updates</small></span><input type="checkbox" data-notify="notify_payments"></label>
      <label class="set-toggle"><span><strong>Issues</strong><small>Concern reports and status changes</small></span><input type="checkbox" data-notify="notify_issues"></label>
      <label class="set-toggle"><span><strong>Messages</strong><small>New direct messages</small></span><input type="checkbox" data-notify="notify_messages"></label>
      <label class="set-toggle"><span><strong>Email copies</strong><small>Also send notifications by email</small></span><input type="checkbox" data-notify="email_notifications"></label>
    </div>
  </section>

  <p class="set-foot">Signed in as <strong>{{ $actor['name'] }}</strong> · {{ $actor['user']->email }}</p>
</main>

<script src="{{ asset('js/settings.js') . '?v=' . filemtime(public_path('js/settings.js')) }}"></script>
</body>
</html>
