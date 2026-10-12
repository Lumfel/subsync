<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SubSync — Admin Dashboard</title>
<script>
window.APP_BASE = '{{ request()->getBaseUrl() }}';
(function () {
    var base = window.APP_BASE || '';
    if (!base) return;
    var prefix = function (url) {
        if (typeof url === 'string' && url.charAt(0) === '/' && url.charAt(1) !== '/') return base + url;
        return url;
    };
    var origFetch = window.fetch;
    window.fetch = function (url, opts) { opts = opts || {}; var h = Object.assign({}, opts.headers || {}, { 'ngrok-skip-browser-warning': '1' }); if (window.__PORTAL) h['X-Portal'] = window.__PORTAL; opts.headers = h; return origFetch.call(this, prefix(url), opts); };
    var OrigES = window.EventSource;
    window.EventSource = function (url, cfg) { var u = prefix(url); if (window.__PORTAL) u += (u.indexOf('?') < 0 ? '?' : '&') + 'portal=' + window.__PORTAL; return new OrigES(u, cfg); };
    window.EventSource.prototype = OrigES.prototype;
    window.EventSource.CONNECTING = 0; window.EventSource.OPEN = 1; window.EventSource.CLOSED = 2;
})();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap"></noscript>
<link rel="preconnect" href="https://unpkg.com" crossorigin>
<link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') . '?v=' . filemtime(public_path('css/admin-dashboard.css')) }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/ui-polish.css') . '?v=' . filemtime(public_path('css/ui-polish.css')) }}">
<meta name="theme-color" content="#86653D">
  <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
  <script src="{{ asset('js/pwa.js') . '?v=' . filemtime(public_path('js/pwa.js')) }}" defer></script>
  <script>window.__PORTAL = @json($role ?? ''); window.__PREFS = @json($prefs ?? []); window.__ACTOR = @json($role ?? '');</script>
  <script src="{{ asset('js/ui.js') . '?v=' . filemtime(public_path('js/ui.js')) }}"></script>
  @vite('resources/js/app.js')
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
 <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo">
      <img src="{{ asset('emblem_1.svg') }}" alt="SubSync">
    </div>
    <div class="brand-sub">Terra Nova Homeowners Association</div>
  </div>
  <div style="padding:10px 14px 2px;">
    <button class="qa-btn" style="width:100%;justify-content:flex-start;background:#FAF6F0;" onclick="SubUI.openSearch()">
      <span class="qa-icon">🔍</span> Search… <kbd style="margin-left:auto;background:#E6DFD5;border-radius:4px;padding:1px 6px;font-size:11px;">Ctrl K</kbd>
    </button>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Overview</div>
    <div class="nav-item active" onclick="nav('dashboard',this)"><span class="nav-icon">📊</span> Dashboard</div>
    <div class="nav-item" onclick="nav('analytics',this)"><span class="nav-icon">📈</span> Analytics</div>

    <div class="nav-section-label">Community</div>
    <div class="nav-item" onclick="nav('residents',this)"><span class="nav-icon">👥</span> Residents <span class="nav-badge" id="pendingBadge" style="display:none;background:#f59e0b;">0</span></div>
    <div class="nav-item" onclick="nav('members',this)"><span class="nav-icon">👤</span> Household Members</div>
    <div class="nav-item" onclick="nav('officers',this)"><span class="nav-icon">🛡️</span> Officers</div>

    <div class="nav-section-label">Operations</div>
    <div class="nav-item" onclick="nav('issues',this)"><span class="nav-icon">🔧</span> Issues <span class="nav-badge" id="issueBadge">0</span></div>
    <div class="nav-item" onclick="nav('mapping',this)"><span class="nav-icon">🗺️</span> Map</div>
    <div class="nav-item" onclick="nav('announcements',this)"><span class="nav-icon">📢</span> Announcements</div>
    <div class="nav-item" onclick="nav('policies',this)"><span class="nav-icon">📜</span> Policies</div>

    <div class="nav-item" onclick="nav('reports',this)"><span class="nav-icon">📁</span> Documents <span class="nav-badge" id="reportsBadge" style="display:none;">0</span></div>
    <div class="nav-item" onclick="nav('archives',this)"><span class="nav-icon">📦</span> Archives</div>
    <div class="nav-item" onclick="nav('gcash',this)"><span class="nav-icon">💳</span> Finance</div>

    <div class="nav-section-label">Communication &amp; System</div>
    <div class="nav-item" onclick="nav('messages',this)"><span class="nav-icon">💬</span> Messages <span class="nav-badge" id="msgBadge">5</span></div>
    <div class="nav-item" onclick="nav('recommendations',this)"><span class="nav-icon">💡</span> Suggestions</div>
    <div class="nav-item" onclick="nav('manageusers',this)"><span class="nav-icon">⚙️</span> Households &amp; Users</div>
    <div class="nav-item" id="navSettings" onclick="window.location='{{ url('settings') }}?portal={{ $role ?? 'admin' }}'"><span class="nav-icon">🎚️</span> Settings</div>
  </nav>
  <div class="sidebar-footer">
    <div class="admin-profile">
      <div class="admin-avatar">{{ strtoupper(substr($adminName, 0, 2)) }}</div>
      <div>
        <div class="admin-name">{{ $adminName }}</div>
        <div class="admin-role">Subdivision Admin</div>
      </div>
    </div>
  </div>
</aside>

<!-- ═══ MAIN ═══ -->
<main class="main">
  <!-- Emergency alert banner (shown on critical issues) -->
  <div id="emergencyAlert">
    🚨 <span id="emergencyAlertText">CRITICAL ISSUE REPORTED</span>
    &nbsp;&nbsp;<button onclick="nav('issues');dismissEmergency()" style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.4);color:#fff;padding:3px 12px;border-radius:6px;font-size:12px;cursor:pointer;font-weight:600;">View Issue</button>
    &nbsp;<button aria-label="Dismiss" onclick="dismissEmergency()" style="background:transparent;border:none;color:rgba(255,255,255,0.7);font-size:16px;cursor:pointer;line-height:1;">✕</button>
  </div>
  <!-- Floating announcement notification banner -->
  <div id="annNotif">
    <span style="font-size:18px;">📢</span>
    <div style="flex:1;min-width:0;">
      <div style="font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--accent);margin-bottom:2px;">New Announcement</div>
      <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" id="annNotifTitle">—</div>
    </div>
    <button id="annNotifViewBtn" style="background:var(--accent);color:#fff;border:none;padding:5px 14px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;">View</button>
    <button aria-label="Dismiss notification" onclick="document.getElementById('annNotif').classList.remove('show')" style="background:transparent;border:none;color:var(--text-dim);font-size:16px;cursor:pointer;line-height:1;padding:0 4px;">✕</button>
  </div>
  <!-- Floating new issue notification banner -->
  <div id="issueNotif">
    <span style="font-size:18px;">🆕</span>
    <div style="flex:1;min-width:0;">
      <div style="font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--blue);margin-bottom:2px;">New Issue Report</div>
      <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" id="issueNotifTitle">—</div>
    </div>
    <button id="issueNotifViewBtn" style="background:var(--blue);color:#fff;border:none;padding:5px 14px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;">View</button>
    <button aria-label="Dismiss notification" onclick="document.getElementById('issueNotif').classList.remove('show')" style="background:transparent;border:none;color:var(--text-dim);font-size:16px;cursor:pointer;line-height:1;padding:0 4px;">✕</button>
  </div>

  <div class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()">☰</button>
      <div class="topbar-title" id="topbarTitle">Dashboard Overview</div>
    </div>
    <div class="topbar-actions">
      <div class="alerts-wrap">
        <button class="topbar-btn keep-mobile" id="alertsButton" aria-expanded="false" aria-controls="alertsPopover" onclick="toggleAlerts()">🔔 Alerts <span class="alerts-badge" id="alertsBadge">0</span></button>
        <section class="alerts-popover" id="alertsPopover" aria-label="System alerts">
          <div class="alerts-popover-head"><strong>System alerts</strong><button class="alerts-refresh" onclick="loadAlerts(true)">Refresh</button></div>
          <div id="alertsList"><div class="alerts-empty">Loading alerts…</div></div>
        </section>
      </div>
      <button class="topbar-btn primary" onclick="openModal('ann')">+ New Announcement</button>
      <form id="logoutForm" action="{{ url('logout') }}" method="POST" style="display:none;">@csrf<input type="hidden" name="portal" value="{{ $role ?? 'admin' }}"></form>
      <button class="topbar-btn keep-mobile" onclick="confirmLogout()" style="color:#f08080;border-color:rgba(240,128,128,0.3);">⏻ Log Out</button>
    </div>
  </div>

  <div class="content">

    <!-- ══════════ DASHBOARD ══════════ -->
    <div class="panel active" id="panel-dashboard">
      <!-- Pending registration notice (shown only when count > 0) -->
      <div id="pendingNotice" style="display:none;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.35);border-radius:10px;padding:14px 18px;margin-bottom:16px;display:none;align-items:center;gap:14px;flex-wrap:wrap;">
        <span style="font-size:20px;">⏳</span>
        <div style="flex:1;min-width:160px;">
          <div style="font-weight:600;font-size:14px;color:#f59e0b;">Pending Account Registrations</div>
          <div style="font-size:12px;color:var(--text-dim);margin-top:2px;"><span id="pendingNoticeCount">0</span> resident(s) have submitted registration requests and are waiting for your approval.</div>
        </div>
        <button class="btn btn-sm" style="background:#f59e0b;border-color:#f59e0b;color:#1a100a;" onclick="nav('residents',null)">Review Now →</button>
      </div>

      <!-- Quick actions -->
      <div class="card" style="margin-bottom:16px;padding:18px 20px;">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-dim);margin-bottom:12px;">Quick Actions</div>
        <div class="quick-actions">
          <button class="qa-btn" onclick="nav('announcements',null)"><span class="qa-icon">📢</span> Post Announcement</button>
          <button class="qa-btn" onclick="nav('residents',null)"><span class="qa-icon">👥</span> Approve Residents</button>
          <button class="qa-btn" onclick="nav('issues',null)"><span class="qa-icon">🔧</span> Respond to Issues</button>
          <button class="qa-btn" onclick="SubUI.openSearch()"><span class="qa-icon">🔍</span> Quick Search</button>
        </div>
      </div>

      <!-- BENTO GRID -->
      <div class="bento-grid">
        <!-- Bento A: Greeting Banner (Span 12) -->
        <div class="card bento-col-12" style="background: linear-gradient(135deg, #FAF4E7, #FAF6F0); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
          <div>
            <h1 style="font-family:'DM Serif Display',serif; font-size:32px; font-weight:400; color:#2B2927; margin-bottom:6px;">SubSync Administration Portal</h1>
            <p style="font-size:13px; color:#7D7975;">Manage residents, resolve issues, monitor finances, and communicate with your community — all in one place.</p>
          </div>
          <div style="display: flex; gap: 10px;">
            <button class="btn" onclick="openModal('ann')">📣 Post Notice</button>
            <button class="btn" style="background:#2B2927; color:#fff; border-color:#2B2927;" onclick="nav('residents',null)">👥 Verify Residents</button>
          </div>
        </div>

        <!-- Bento B: Metrics Sub-Grid (Span 8) -->
        <div class="bento-col-8 bento-metrics-grid">
          <div class="card" onclick="nav('residents',null)" style="cursor:pointer; text-align: center;">
            <div class="card-title" style="justify-content:center;">Total Residents</div>
            <div style="font-size:36px; font-weight:700; color:#2B2927; margin: 10px 0 6px;" id="statResidents">—</div>
            <div style="font-size:11px; color:#7D7975;"><span id="statActive">0</span> verified accounts</div>
          </div>

          <div class="card" onclick="nav('issues',null)" style="cursor:pointer; text-align: center;">
            <div class="card-title" style="justify-content:center;">Open Issues</div>
            <div style="font-size:36px; font-weight:700; color:#f08080; margin: 10px 0 6px;" id="statIssues">—</div>
            <div style="font-size:11px; color:#7D7975;">requires resolution</div>
          </div>
        </div>

        <!-- Bento C: Approvals (Span 4) -->
        <div class="bento-col-4" style="display: flex; flex-direction: column; gap: 24px;">
          <div class="card" onclick="nav('residents',null)" id="pendingStatCard" style="cursor:pointer; display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-left: 3px solid #f59e0b;">
            <div>
              <div class="card-title" style="margin-bottom:4px; color:#f59e0b;">Pending Accounts</div>
              <div style="font-size:22px; font-weight:700; color:#f59e0b;" id="statPending">0</div>
            </div>
            <span style="font-size:24px;">⏳</span>
          </div>
        </div>

        <!-- Bento D: Live Feed (Span 8) -->
        <div class="card bento-col-8">
          <div class="card-title">Recent Activity Feed</div>
          <div id="activityFeed"></div>
        </div>

        <!-- Bento F: Issue Reports (Span 12) -->
        <div class="card bento-col-12">
          <div class="card-title">Recent Issues</div>
          <div id="dashIssueList"><div class="skeleton sk-card" style="margin-bottom:10px;"></div><div class="skeleton sk-card" style="margin-bottom:10px;"></div><div class="skeleton sk-card"></div></div>
          <button class="btn btn-sm" style="margin-top:16px;" onclick="nav('issues',null)">View All Issues</button>
        </div>
      </div>
    </div>

    <!-- ══════════ RESIDENTS ══════════ -->
    <div class="panel" id="panel-residents">
      <!-- Pending Registrations -->
      <div id="pendingSection" style="display:none;margin-bottom:18px;">
        <div class="card" style="border-left:3px solid #f59e0b;">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <span style="font-size:16px;">⏳</span>
            <div class="card-title" style="margin:0;">Pending Registrations</div>
            <span class="nav-badge" id="pendingCount" style="background:#f59e0b;">0</span>
          </div>
          <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:12px;">
            <label style="font-size:12px;color:var(--text-dim);display:flex;align-items:center;gap:6px;cursor:pointer;">
              <input type="checkbox" id="pendingSelectAll" onclick="toggleAllPending(this)"> Select all
            </label>
            <button class="btn btn-sm" style="background:#22c55e;border-color:#22c55e;color:#062b12;" onclick="bulkPending('approve')">✓ Approve selected</button>
            <button class="btn btn-sm btn-danger" onclick="bulkPending('reject')">✕ Reject selected</button>
          </div>
          <div id="pendingGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;"></div>
        </div>
      </div>
      <div class="card" style="margin-bottom:16px;">
        <div class="card-title">Import Residents (CSV)</div>
        <div class="card-subtitle">Columns: name, email, password (optional), contact_number, block_lot, status. Existing emails are skipped.</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:10px;">
          <input type="file" id="residentImportFile" accept=".csv,text/csv" style="flex:1;min-width:200px;">
          <button class="btn btn-sm" onclick="importResidents()">Import CSV</button>
        </div>
      </div>
      <div class="card" style="margin-bottom:16px;">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
          <input type="text" id="residentSearch" placeholder="Search household or block…" style="flex:1;min-width:180px;" oninput="filterResidents()">
          <select id="residentBlock" onchange="filterResidents()" style="width:auto;">
            <option value="">All Blocks</option>
            <option>Block 1</option><option>Block 2</option>
            <option>Block 3</option><option>Block 4</option><option>Block 5</option>
          </select>
          <button class="btn btn-sm" onclick="openModal('addResident')">+ Add Resident</button>
          <button class="btn btn-sm btn-green" onclick="SubUI.exportCsv('/api/export/residents')">&#8595; Export CSV</button>
        </div>
      </div>
      <div class="resident-grid" id="residentGrid"></div>
    </div>

    <!-- ══════════ MEMBERS ══════════ -->
    <!-- ══════════ MEMBERS ══════════ -->
    <div class="panel" id="panel-members">
      <div class="card" style="margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div>
          <div class="card-title">Household Members</div>
          <div style="font-size:13px;color:var(--text-dim);">Non-resident household members. Click a card to view or edit.</div>
        </div>
        <button class="btn btn-sm" onclick="openModal('addMember')">+ Add Member</button>
      </div>
      <div style="margin-bottom:14px;display:flex;gap:10px;flex-wrap:wrap;">
        <input id="memberSearch" type="text" placeholder="Search by name or block/lot…" oninput="renderMembers()" style="flex:1;min-width:200px;padding:8px 12px;border-radius:8px;border:1px solid var(--glass-border);background:var(--glass);color:var(--text);font-size:13px;">
        <select id="memberRelFilter" onchange="renderMembers()" style="padding:8px 12px;border-radius:8px;border:1px solid var(--glass-border);background:var(--glass);color:var(--text);font-size:13px;">
          <option value="">All Relationships</option>
          <option>Owner</option><option>Spouse</option><option>Child</option><option>Parent</option><option>Tenant</option><option>Other</option>
        </select>
      </div>
      <div style="font-size:12px;color:var(--text-dim);margin-bottom:10px;">Total: <span id="statTotalMembers">—</span> &nbsp;|&nbsp; Houses with members: <span id="statTotalHouses">—</span></div>
      <div class="members-grid" id="membersGrid"></div>
    </div>

    <!-- ══════════ MESSAGES ══════════ -->
    <div class="panel" id="panel-messages">
      <div class="msg-panel-heading">
        <div>
          <div class="card-title">Community Correspondence</div>
          <div class="card-subtitle">Review and respond to documented resident and officer communications.</div>
        </div>
        <div class="message-status" id="messageStatus" role="status">Select a correspondence record</div>
      </div>
      <div class="msg-layout">
        <div class="msg-sidebar">
          <div class="msg-sidebar-head" style="display:flex;align-items:center;justify-content:space-between;">
            <div id="msgHeaderTitle">Community Chat</div>
            <button class="btn btn-sm" style="padding:3px 8px;font-size:11px;" onclick="openNewThreadModal()">+ New</button>
          </div>
          <div class="msg-search-wrap">
            <input type="text" class="msg-search" placeholder="Search…" oninput="filterThreads(this.value)">
          </div>
          <div class="msg-threads" id="threadList"></div>
        </div>
        <div class="msg-main">
          <div class="msg-header">
            <div class="msg-header-avatar ta-r1" id="chatAvatar">DC</div>
            <div>
              <div class="msg-header-name" id="chatName">Dela Cruz Family</div>
              <div class="msg-header-sub" id="chatSub">Official communication record</div>
            </div>
          </div>
          <div class="msg-body" id="msgBody"></div>
          <div class="msg-compose">
            <textarea id="msgInput" placeholder="Type a reply…" rows="1"
              onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();adminSend();}"></textarea>
            <button class="send-btn" onclick="adminSend()">➤</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════ ANNOUNCEMENTS ══════════ -->
    <div class="panel" id="panel-announcements">
      <div class="card" style="margin-bottom:16px;">
        <div class="card-title">Event Calendar</div>
        <div class="card-subtitle">Events residents can RSVP to appear in their Calendar tab.</div>
        <div id="adminEventList" style="margin-top:10px;"><div class="empty-state" style="padding:12px;">Loading…</div></div>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:8px;margin-top:12px;">
          <input type="text" id="evTitle" placeholder="Event title">
          <input type="text" id="evLocation" placeholder="Location (optional)">
          <input type="datetime-local" id="evStarts" title="Starts at">
          <input type="datetime-local" id="evEnds" title="Ends at (optional)">
        </div>
        <textarea id="evDesc" placeholder="Description (optional)" rows="2" style="margin-top:8px;width:100%;"></textarea>
        <div style="display:flex;justify-content:flex-end;margin-top:8px;">
          <button class="btn btn-sm" onclick="saveEvent()">+ Add Event</button>
        </div>
      </div>
      <div class="card" style="margin-bottom:16px;padding:16px 22px;">
        <div style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-dim);margin-bottom:10px;">Announcements Board</div>
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <input type="text" id="annSearch" placeholder="Search announcements…" style="width:200px;" oninput="renderAnnouncements()">
            <select id="annStatusFilter" onchange="renderAnnouncements()" style="width:auto;">
              <option value="all">All</option>
              <option value="Active" selected>Active</option>
              <option value="Archived">Archived</option>
            </select>
          </div>
          <button class="btn" onclick="openModal('ann')">+ New Announcement</button>
        </div>
      </div>
      <div id="annList"></div>
    </div>

    <!-- ══════════ ISSUES ══════════ -->
    <div class="panel" id="panel-issues">
      <div class="card" style="margin-bottom:16px;padding:16px 22px;">
        <div style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-dim);margin-bottom:10px;">Issue Management</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
          <input type="text" id="issueSearch" placeholder="Search by title or resident…" style="flex:1;min-width:160px;" oninput="renderIssues()">
          <select id="issueFilter" onchange="renderIssues()" style="width:auto;">
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Resolved">Resolved</option>
          </select>
          <select id="issuePriFilter" onchange="renderIssues()" style="width:auto;">
            <option value="">All Priority</option>
            <option value="Critical">Critical</option>
            <option value="High">High</option>
            <option value="Medium">Medium</option>
            <option value="Low">Low</option>
          </select>
          <label style="font-size:12px;color:var(--text-dim);margin-left:4px;">From</label>
          <input type="date" id="issueDateFrom" onchange="renderIssues()" style="width:140px;">
          <label style="font-size:12px;color:var(--text-dim);">To</label>
          <input type="date" id="issueDateTo" onchange="renderIssues()" style="width:140px;">
          <button class="btn btn-sm" id="issueArchiveToggle" onclick="toggleArchivedIssues()">Show Archived</button>
          <button class="btn btn-sm btn-green" style="margin-left:auto;" onclick="exportIssuesCSV()">&#8595; Export</button>
          <button class="btn btn-sm" onclick="bulkArchiveSelected()">Archive selected</button>
        </div>
      </div>
      <div id="issueList"></div>
    </div>

    <!-- ══════════ MANAGE USERS ══════════ -->
    <div class="panel" id="panel-manageusers">
      <div class="manage-grid">
        <div class="manage-card" onclick="openModal('muAddHousehold')">
          <div class="manage-card-icon">🏠</div>
          <h3>Add Household</h3>
          <p>Create new household records</p>
        </div>
        <div class="manage-card" onclick="openAddUserModal()">
          <div class="manage-card-icon">➕</div>
          <h3>Add User</h3>
          <p>Create new system user account</p>
        </div>
        <div class="manage-card" onclick="openModal('addResident')">
          <div class="manage-card-icon">👤</div>
          <h3>Add Resident</h3>
          <p>Register a new resident</p>
        </div>
        <div class="manage-card" onclick="populateMemberHouseholdSelect();openModal('muAddMember')">
          <div class="manage-card-icon">�</div>
          <h3>Add HH Member</h3>
          <p>Add a household member</p>
        </div>
        <div class="manage-card" onclick="nav('manageusers',null);showToast('ℹ️ Click Status on a household row to change its status.')">
          <div class="manage-card-icon">⚠️</div>
          <h3>Change Status</h3>
          <p>Update household condition</p>
        </div>
        <div class="manage-card" onclick="nav('officers',null)">
          <div class="manage-card-icon">🛡️</div>
          <h3>Manage Officers</h3>
          <p>Assign admin roles</p>
        </div>
      </div>

      <!-- Filter + table switch -->
      <div class="card" style="margin-bottom:10px;">
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
          <input type="text" id="manageSearch" placeholder="Search records…" style="flex:1;" onkeyup="filterManageTable()">
          <select id="tableSelector" onchange="switchManagePanel()" style="width:auto;">
            <option value="householdsPanel">Households</option>
            <option value="usersPanel">Users</option>
            <option value="familiesPanel">Families</option>
            <option value="membersPanel">Household Members</option>
          </select>
        </div>
      </div>

      <div class="card">
        <div id="householdsPanel" class="table-panel visible">
          <div class="card-title">Households</div>
          <table class="panel-table" id="householdTable">
            <thead><tr><th>ID</th><th>Location</th><th>Family</th><th>Members</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="householdTbody"></tbody>
          </table>
        </div>
        <div id="usersPanel" class="table-panel">
          <div class="card-title">Users</div>
          <table class="panel-table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="userTbody"></tbody></table>
        </div>
        <div id="familiesPanel" class="table-panel">
          <div class="card-title">Families</div>
          <table class="panel-table"><thead><tr><th>ID</th><th>Family Name</th><th>Family Head</th><th>Members</th><th>Actions</th></tr></thead>
          <tbody id="familyTbody"></tbody></table>
        </div>
        <div id="membersPanel" class="table-panel">
          <div class="card-title">Members</div>
          <table class="panel-table"><thead><tr><th>ID</th><th>Name</th><th>Household</th><th>Type</th><th>Actions</th></tr></thead>
          <tbody id="memberTbody"></tbody></table>
        </div>
      </div>
    </div>

    <!-- ══════════ OFFICERS ══════════ -->
    <div class="panel" id="panel-officers">
      <div class="card" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <div style="font-size:13px;color:var(--text-mid);">Manage subdivision officers and their roles.</div>
          <button class="btn btn-sm" onclick="openModal('addOfficer')">+ Add Officer</button>
        </div>
      </div>
      <table class="data-table">
        <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="officerTable"></tbody>
      </table>
    </div>

    <!-- ══════════ RECOMMENDATIONS ══════════ -->
    <div class="panel" id="panel-recommendations">
      <div class="card" style="margin-bottom:16px;">
        <div style="font-size:13px;color:var(--text-mid);">Resident suggestions and feedback submitted for review.</div>
      </div>
      <div id="recsAdminList"><div class="empty-state"><div class="empty-icon">💡</div>Loading…</div></div>
    </div>

    <!-- ══════════ ANALYTICS ══════════ -->
    <div class="panel" id="panel-analytics">
      <div class="stat-grid">
        <div class="stat-card"><div class="stat-label">Members</div><div class="stat-value stat-blue" id="analyticMembers">—</div></div>
        <div class="stat-card"><div class="stat-label">Houses</div><div class="stat-value stat-accent" id="analyticHouses">—</div></div>
      </div>
      <div class="graph-grid">
        <div class="card">
          <div class="card-title">Resident Status</div>
          <div id="chart-payment-statuses" style="width:100%;min-height:180px;display:flex;align-items:center;justify-content:center;"></div>
        </div>
        <div class="card">
          <div class="card-title">Issues by Status</div>
          <div id="chart-issue-status" style="width:100%;min-height:180px;display:flex;align-items:center;justify-content:center;"></div>
        </div>
        <div class="card">
          <div class="card-title">Issues by Category</div>
          <div id="chart-complaints" style="width:100%;min-height:180px;"></div>
        </div>
      </div>
      <div class="graph-grid">
        <div class="card">
          <div class="card-title">Suggestions by Status</div>
          <div id="chart-recs-status" style="width:100%;min-height:180px;display:flex;align-items:center;justify-content:center;"></div>
        </div>
        <div class="card">
          <div class="card-title">HOA Expenses by Category</div>
          <div id="chart-expenses-cat" style="width:100%;min-height:180px;"></div>
        </div>
        <div class="card">
          <div class="card-title">Issues Trend (6 Months)</div>
          <div id="chart-issue-trend" style="width:100%;min-height:180px;"></div>
        </div>
      </div>
      <div class="graph-grid-wide">
        <div class="card">
          <div class="card-title">Most Reported Issues</div>
          <div id="analytics-common-issues" style="min-height:180px;"></div>
        </div>
        <div class="card">
          <div class="card-title">Security Logs</div>
          <div class="graph-placeholder"><div class="g-icon">🔒</div><div>Security Events</div></div>
        </div>
      </div>
    </div>

    <!-- ══════════ MAPPING ══════════ -->
    <div class="panel" id="panel-mapping">

      <!-- Toolbar -->
      <div class="card" style="margin-bottom:12px;padding:10px 16px;">
        <div class="map-toolbar">
          <div>
            <span style="font-size:14px;font-weight:600;color:var(--text);">Terra Nova Subdivision</span>
            <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">Issue pins are numbered by report sequence. Map is limited to the Terra Nova subdivision boundary.</div>
          </div>
          <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <label class="map-toggle"><input type="checkbox" id="layer-households" checked> Households</label>
            <label class="map-toggle"><input type="checkbox" id="layer-facilities" checked> Facilities</label>
            <label class="map-toggle"><input type="checkbox" id="layer-issues" checked> Issue Pins</label>
            <button class="btn btn-sm btn-green" onclick="enterAddFacilityMode()">+ Add Facility</button>
            <button class="btn btn-sm" onclick="if(mapInst)mapInst.setView([10.62269,122.96134],17)">⌖ Reset View</button>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="card" style="margin-bottom:12px;padding:8px 16px;">
        <div class="map-legend">
          <span><span class="map-dot" style="background:#4CAF50;"></span>Facility</span>
          <span><span class="map-dot" style="background:#4287f5;"></span>Household (Active)</span>
          <span><span class="map-dot" style="background:#f08080;"></span>Household (Vacant)</span>
          <span><span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#e05555;color:#fff;font-size:9px;font-weight:700;margin-right:4px;">1</span>Issue (Pending)</span>
          <span><span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#f5a623;color:#fff;font-size:9px;font-weight:700;margin-right:4px;">2</span>Issue (In Progress)</span>
          <span><span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#888;color:#fff;font-size:9px;font-weight:700;margin-right:4px;">3</span>Issue (Resolved)</span>
          <span style="margin-left:auto;font-size:11px;color:var(--text-dim);">📍 Numbers = Report sequence</span>
        </div>
      </div>

      <!-- Map -->
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
        <button class="btn btn-sm" onclick="enterCustomizeArea()" id="btn-customize-area">✏️ Customize Area</button>
        <span id="map-customize-hint" style="display:none;font-size:12px;color:var(--accent);">Pan &amp; zoom to frame the subdivision area, then click <strong>Save Area</strong>.</span>
        <span style="margin-left:auto;display:none;gap:8px;align-items:center;" id="map-area-toolbar">
          <button class="btn btn-sm btn-green" onclick="saveMapArea()" id="btn-save-area">💾 Save Area</button>
          <button class="btn btn-sm" onclick="resetMapArea()" id="btn-reset-area">↺ Reset</button>
        </span>
      </div>
      <div id="map-container"></div>

      <!-- Add-facility mode banner -->
      <div id="map-add-banner">
        <span>📍 Click anywhere within the Terra Nova boundary to place the facility pin</span>
        <button class="btn btn-sm" style="margin-left:auto;" onclick="cancelAddFacilityMode()">Cancel</button>
      </div>

      <!-- Map Disclaimer -->
      <div style="margin-top:10px;padding:10px 14px;background:var(--accent-dim);border:1px solid var(--accent-border);border-radius:var(--radius-sm);font-size:12px;color:var(--text-dim);">
        ℹ️ <strong>Map Scope:</strong> This map is focused on Terra Nova Subdivision. Reported issues are represented by numbered pins indicating report sequence. The geofence boundary is approximate. For precise location data, please consult official subdivision plans.
      </div>

    </div>

    <!-- ══════════ REPORTS ══════════ -->
    <div class="panel" id="panel-reports">
      <!-- Date Range Filter -->
      <div class="card" style="margin-bottom:16px;padding:16px 22px;">
        <div style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-dim);margin-bottom:10px;">Report Date Range</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
          <label style="font-size:13px;color:var(--text-dim);">From</label>
          <input type="date" id="reportDateFrom" style="width:160px;">
          <label style="font-size:13px;color:var(--text-dim);">To</label>
          <input type="date" id="reportDateTo" style="width:160px;">
          <span style="font-size:12px;color:var(--text-dim);margin-left:4px;">Leave blank to include all records.</span>
        </div>
      </div>
      <div class="grid-2">
        <div class="card">
          <div class="card-title">Officer-Submitted Files</div>
          <div class="card-subtitle">Documents uploaded by officers through their portal.</div>
          <div id="officerFiles"></div>
          <!-- Admin file upload -->
          <div style="margin-top:16px;border-top:1px solid var(--glass-border);padding-top:14px;">
            <div style="font-size:12px;font-weight:600;color:var(--text-mid);margin-bottom:8px;">Upload Supporting Document</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
              <input type="file" id="adminReportFile" accept=".pdf,.doc,.docx,.xlsx,.xls,.png,.jpg,.jpeg" style="flex:1;font-size:13px;padding:6px 0;">
              <input type="text" id="adminReportNote" placeholder="Description / notes…" style="flex:1;min-width:160px;">
              <button class="btn btn-sm" onclick="uploadAdminReport()">Upload</button>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-title">Report Templates</div>
          <div class="card-subtitle">Templates residents use to generate their own reports. Use <code>&#123;&#123;&#123;variable_name&#125;&#125;&#125;</code> placeholders.</div>
          <div id="reportTemplateAdminList" style="margin-top:10px;"><div class="empty-state" style="padding:12px;">Loading templates…</div></div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;">
            <input type="text" id="rtName" placeholder="Template name">
            <input type="text" id="rtType" placeholder="Type (e.g. Clearance)">
          </div>
          <input type="text" id="rtSubject" placeholder="Subject line (e.g. Report for &#123;&#123;&#123;resident_name&#125;&#125;&#125;)" style="margin-top:8px;width:100%;">
          <textarea id="rtBody" placeholder="Body text (e.g. Dear &#123;&#123;&#123;resident_name&#125;&#125;&#125;, ...)" rows="3" style="margin-top:8px;width:100%;"></textarea>
          <input type="text" id="rtVars" placeholder="Variables, comma-separated (e.g. resident_name, block_lot)" style="margin-top:8px;width:100%;">
          <div style="display:flex;justify-content:flex-end;margin-top:8px;">
            <button class="btn btn-sm" onclick="saveReportTemplate()">+ Add Template</button>
          </div>
        </div>
        <div class="card">
          <div class="card-title">Issue a Certificate / Report</div>
          <div class="card-subtitle">Generate a document for a resident. It gets a reference number and can be printed.</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;">
            <select id="issueResidentSelect"><option value="">Select resident…</option></select>
            <select id="issueTemplateSelect" onchange="renderIssueVars()"><option value="">Select template…</option></select>
          </div>
          <div id="issueVarsContainer"></div>
          <div style="display:flex;justify-content:flex-end;margin-top:8px;">
            <button class="btn btn-sm" onclick="issueReport()">Issue Document</button>
          </div>
          <div class="card-title" style="margin-top:16px;">Issued Documents</div>
          <div id="issuedReportsList"><div class="empty-state" style="padding:12px;">Loading…</div></div>
        </div>
        <div class="card">
          <div class="card-title">Generate Reports</div>
          <div class="card-subtitle">Download filtered records as CSV. Use the date range above to narrow results.</div>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <button class="btn btn-green" onclick="reportIssues()">🔧 Issue Report</button>
            <button class="btn" style="background:var(--purple-dim);border-color:rgba(185,154,245,0.25);color:var(--purple);" onclick="reportResidentDirectory()">👥 Resident Directory</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════ POLICIES ══════════ -->
    <div class="panel" id="panel-policies">
      <div class="card" style="margin-bottom:16px;background:linear-gradient(135deg,#FAF4E7,#FAF6F0);border-left:4px solid var(--accent);">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
          <div style="font-size:32px;">📜</div>
          <div>
            <div style="font-family:'DM Serif Display',serif;font-size:22px;color:#2B2927;margin-bottom:4px;">Terra Nova Homeowners Association</div>
            <div style="font-size:13px;color:var(--text-dim);">Community Rules, Regulations &amp; Policies — Effective Current Term</div>
          </div>
        </div>
      </div>

      <!-- Policy Management (DB-backed) -->
      <div class="card" style="margin-bottom:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;">
          <div class="card-title" style="margin:0;">Policy Management</div>
          <button class="btn btn-sm" onclick="openPolicyModal()">+ Add Policy</button>
        </div>
        <div id="policyAdminList"><div class="empty-state" style="padding:12px;">Loading policies…</div></div>
      </div>

      <!-- Policy add/edit modal -->
      <div class="modal-overlay" id="modal-policy">
        <div class="modal-box" style="max-width:560px;">
          <div class="modal-title" id="policyModalTitle">Add Policy</div>
          <input type="hidden" id="policyId">
          <div class="f-row"><div class="f-field"><span class="f-label">Title</span><input type="text" id="policyTitle" maxlength="200"></div></div>
          <div class="f-row">
            <div class="f-field"><span class="f-label">Category</span><input type="text" id="policyCategory" maxlength="100" placeholder="e.g. Community Rules"></div>
            <div class="f-field"><span class="f-label">Effective Date</span><input type="date" id="policyEffective"></div>
          </div>
          <div class="f-row">
            <div class="f-field"><span class="f-label">Status</span>
              <select id="policyStatus"><option>Active</option><option>Inactive</option><option>Archived</option></select>
            </div>
          </div>
          <div class="f-row"><div class="f-field"><span class="f-label">Description</span><textarea id="policyDescription" rows="4"></textarea></div></div>
          <div style="font-size:12px;color:#f08080;margin-bottom:8px;" id="policyMsg"></div>
          <div class="modal-actions">
            <button class="modal-close-btn" onclick="closeModal('policy')">Cancel</button>
            <button class="btn btn-sm" onclick="savePolicy()">Save Policy</button>
          </div>
        </div>
      </div>

      <!-- Policy Section 1 -->
      <div class="card" style="margin-bottom:12px;">
        <div style="font-family:'DM Serif Display',serif;font-size:17px;color:var(--accent);margin-bottom:8px;border-bottom:1px solid var(--glass-border);padding-bottom:6px;">1. Membership &amp; Community Conduct</div>
        <ul style="padding-left:20px;font-size:14px;color:var(--text-mid);line-height:1.85;">
          <li>All registered homeowners and residents are members of the Terra Nova Homeowners Association (TNHOA) and are expected to uphold the community's rules and values.</li>
          <li>Members are expected to maintain courteous, respectful conduct toward neighbors, guests, and HOA staff at all times.</li>
          <li>Households must keep their registered contact details and list of occupants up to date with the HOA office.</li>
          <li>Participation in community meetings, clean-up drives, and general assemblies is encouraged to foster a cooperative neighborhood.</li>
        </ul>
      </div>

      <!-- Policy Section 2 -->
      <div class="card" style="margin-bottom:12px;">
        <div style="font-family:'DM Serif Display',serif;font-size:17px;color:var(--accent);margin-bottom:8px;border-bottom:1px solid var(--glass-border);padding-bottom:6px;">2. Property Use &amp; Structural Guidelines</div>
        <ul style="padding-left:20px;font-size:14px;color:var(--text-mid);line-height:1.85;">
          <li>All renovations, extensions, or structural modifications to residential units must be approved in writing by the TNHOA Board prior to commencement.</li>
          <li>Exterior aesthetics must conform to the subdivision's approved design standards. Unapproved alterations may be subject to fines and mandated restoration.</li>
          <li>Properties must be maintained in a clean, orderly condition. Overgrown vegetation, visible debris, or dilapidated structures are subject to violation notices.</li>
          <li>Commercial activities within residential lots are prohibited unless expressly approved by the Board.</li>
        </ul>
      </div>

      <!-- Policy Section 3 -->
      <div class="card" style="margin-bottom:12px;">
        <div style="font-family:'DM Serif Display',serif;font-size:17px;color:var(--accent);margin-bottom:8px;border-bottom:1px solid var(--glass-border);padding-bottom:6px;">3. Noise, Conduct &amp; Community Standards</div>
        <ul style="padding-left:20px;font-size:14px;color:var(--text-mid);line-height:1.85;">
          <li>Residents and their guests are expected to observe courteous conduct toward neighbors and subdivision staff at all times.</li>
          <li>Noise levels must not disturb neighboring households, particularly during designated quiet hours (10:00 PM – 6:00 AM).</li>
          <li>Parties, gatherings, or events that may cause significant noise or traffic must be reported to the HOA office at least 48 hours in advance.</li>
          <li>Acts of harassment, intimidation, or threatening behavior within the subdivision premises are strictly prohibited and may be reported to the appropriate authorities.</li>
        </ul>
      </div>

      <!-- Policy Section 4 -->
      <div class="card" style="margin-bottom:12px;">
        <div style="font-family:'DM Serif Display',serif;font-size:17px;color:var(--accent);margin-bottom:8px;border-bottom:1px solid var(--glass-border);padding-bottom:6px;">4. Waste Management &amp; Sanitation</div>
        <ul style="padding-left:20px;font-size:14px;color:var(--text-mid);line-height:1.85;">
          <li>Garbage must be properly bagged, segregated by type (biodegradable, non-biodegradable, recyclable), and placed at the designated collection point on scheduled collection days.</li>
          <li>Littering within the subdivision — including common areas, walkways, and drainage systems — is prohibited and subject to fines.</li>
          <li>Residents are responsible for maintaining drainage systems adjacent to their properties free of blockages.</li>
          <li>Burning of waste materials within the subdivision is strictly prohibited in compliance with environmental regulations.</li>
        </ul>
      </div>

      <!-- Policy Section 5 -->
      <div class="card" style="margin-bottom:12px;">
        <div style="font-family:'DM Serif Display',serif;font-size:17px;color:var(--accent);margin-bottom:8px;border-bottom:1px solid var(--glass-border);padding-bottom:6px;">5. Security, Gate Access &amp; Visitor Policy</div>
        <ul style="padding-left:20px;font-size:14px;color:var(--text-mid);line-height:1.85;">
          <li>All visitors and delivery personnel must register at the main gate before entering the subdivision. Residents must authorize their guests prior to entry.</li>
          <li>Resident identification stickers or RFID tags must be affixed to registered vehicles as required. Untagged vehicles may be denied entry at the guard's discretion.</li>
          <li>Residents are responsible for the conduct of their authorized guests while within the subdivision premises.</li>
          <li>Security personnel are authorized to refuse entry to individuals who cannot be verified or who pose a safety concern.</li>
        </ul>
      </div>

      <!-- Policy Section 6 -->
      <div class="card" style="margin-bottom:12px;">
        <div style="font-family:'DM Serif Display',serif;font-size:17px;color:var(--accent);margin-bottom:8px;border-bottom:1px solid var(--glass-border);padding-bottom:6px;">6. Violations, Notices &amp; Penalties</div>
        <ul style="padding-left:20px;font-size:14px;color:var(--text-mid);line-height:1.85;">
          <li>A first-offense Violation Notice will be issued in writing for any breach of HOA rules. The resident will be given a specified period to rectify the violation.</li>
          <li>Repeated or unresolved violations may result in escalating fines, suspension of amenity access, or referral to the appropriate legal body.</li>
          <li>All fines and penalties are documented in the resident's account and must be settled within 30 days of issuance.</li>
          <li>Residents have the right to contest a violation notice by submitting a written appeal to the Board within 10 business days of receipt.</li>
        </ul>
      </div>

      <!-- Policy Section 7 -->
      <div class="card" style="margin-bottom:12px;">
        <div style="font-family:'DM Serif Display',serif;font-size:17px;color:var(--accent);margin-bottom:8px;border-bottom:1px solid var(--glass-border);padding-bottom:6px;">7. Dispute Resolution &amp; Conflict Management</div>
        <ul style="padding-left:20px;font-size:14px;color:var(--text-mid);line-height:1.85;">
          <li>Residents are encouraged to resolve neighborly disputes amicably and in good faith before escalating to the HOA or authorities.</li>
          <li>Formal complaints may be submitted in writing to the HOA office. All complaints will be acknowledged within 5 business days.</li>
          <li>The Board may convene a mediation session between parties. Both parties are expected to cooperate and attend in good faith.</li>
          <li>Decisions rendered by the Board following mediation are binding. Appeals may be escalated to the appropriate barangay or legal channels if unresolved.</li>
          <li>Residents must not take matters into their own hands. Vigilante actions or retaliation against complainants are treated as separate violations.</li>
        </ul>
      </div>

      <!-- Policy Section 8 -->
      <div class="card" style="margin-bottom:12px;">
        <div style="font-family:'DM Serif Display',serif;font-size:17px;color:var(--accent);margin-bottom:8px;border-bottom:1px solid var(--glass-border);padding-bottom:6px;">8. Issue Reporting &amp; Community Communication</div>
        <ul style="padding-left:20px;font-size:14px;color:var(--text-mid);line-height:1.85;">
          <li>Residents are encouraged to report subdivision concerns through SubSync's Issues feature. All submissions are documented and reviewed by the Administration.</li>
          <li>Reports must be factual, specific, and submitted in good faith. False, malicious, or duplicate reports are subject to appropriate action.</li>
          <li>Residents must not modify or tamper with another resident's submitted report. Each resident is responsible solely for their own submissions.</li>
          <li>The HOA Administration will review and assign priority to submitted issues. Residents will be notified of updates via the SubSync portal.</li>
          <li>For urgent or emergency concerns, residents must contact the HOA office or security personnel directly and not rely solely on the online portal.</li>
        </ul>
      </div>

      <div style="font-size:12px;color:var(--text-dim);text-align:center;margin-top:8px;padding:12px;border:1px solid var(--glass-border);border-radius:var(--radius-sm);">
        These policies are subject to amendment by the TNHOA Board. Residents will be notified of any changes through official announcements.
        For inquiries, please contact the HOA Administration Office.
      </div>
    </div>

    <!-- ═══ GCASH PAYMENT SETTINGS ═══ -->
    <div class="panel" id="panel-gcash">
      <div class="fin-tabs" id="finTabs">
        <button class="fin-tab active" onclick="financeTab('overview',this)">Overview</button>
        <button class="fin-tab" onclick="financeTab('settings',this)">GCash &amp; Payments</button>
        <button class="fin-tab" onclick="financeTab('dues',this)">Dues &amp; Assessments</button>
        <button class="fin-tab" onclick="financeTab('collections',this)">Collections &amp; Aging</button>
        <button class="fin-tab" onclick="financeTab('expenses',this)">Expenses</button>
        <button class="fin-tab" onclick="financeTab('budgets',this)">Budgets</button>
      </div>

      <div class="fin-section active" data-fin="overview">
        <div class="card">
          <div class="card-title" style="margin:0 0 4px;">Financial Overview</div>
          <div style="font-size:12px;color:var(--text-dim);margin-bottom:14px;">Year-to-date collections, spending, and outstanding dues.</div>
          <div class="fin-summary" id="financeOverview"><div class="fs-cell">Loading…</div></div>
        </div>
      </div>

      <div class="fin-section" data-fin="settings">
      <div class="card" style="margin-bottom:16px;background:linear-gradient(135deg,#FAF4E7,#FAF6F0);border-left:4px solid var(--accent);">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
          <div style="font-size:32px;">💳</div>
          <div>
            <div style="font-family:'DM Serif Display',serif;font-size:22px;color:#2B2927;margin-bottom:4px;">GCash Receiving Account</div>
            <div style="font-size:13px;color:var(--text-dim);">Manage the QR code and account residents use to send payments.</div>
          </div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:16px;align-items:start;" id="gcashGrid">
        <div class="card" style="margin-bottom:12px;">
          <div class="card-title" style="margin:0 0 14px;">Account Details</div>
          <div class="f-row"><div class="f-field"><span class="f-label">Account Name</span>
            <input type="text" id="gcashAccountName" placeholder="e.g. Terra Nova Homeowners Association" maxlength="120">
          </div></div>
          <div class="f-row"><div class="f-field"><span class="f-label">GCash Number</span>
            <input type="text" id="gcashAccountNumber" placeholder="e.g. 09XX XXX XXXX" maxlength="40">
          </div></div>
          <div class="f-row"><div class="f-field"><span class="f-label">Payment Instructions</span>
            <textarea id="gcashInstructions" rows="3" maxlength="1000" placeholder="Instructions shown to residents when paying."></textarea>
          </div></div>
          <div class="f-row"><div class="f-field"><span class="f-label">GCash QR Code Image</span>
            <input type="file" id="gcashQrInput" accept="image/*" onchange="previewGcashQr(this)">
            <div style="font-size:12px;color:var(--text-dim);margin-top:6px;">PNG or JPG, up to 4 MB. Replaces the current QR code.</div>
          </div></div>
          <div style="font-size:12px;color:#f08080;margin-top:-4px;margin-bottom:8px;" id="gcashMsg"></div>
          <div class="modal-actions" style="display:flex;gap:8px;justify-content:flex-end;">
            <button class="btn btn-sm" style="background:transparent;border:1px solid var(--glass-border);color:var(--text-mid);" onclick="removeGcashQr()">Remove QR</button>
            <button class="btn btn-sm" id="gcashSaveBtn" onclick="saveGcashSettings()">Save Changes</button>
          </div>
        </div>

        <div class="card">
          <div class="card-title" style="margin:0 0 14px;">Resident Preview</div>
          <div id="gcashPreview" style="text-align:center;border:1px dashed var(--glass-border);border-radius:var(--radius-sm);padding:16px;min-height:200px;display:flex;align-items:center;justify-content:center;background:#fff;">
            <div style="font-size:12px;color:var(--text-dim);">No QR code set yet.</div>
          </div>
          <div style="margin-top:12px;font-size:13px;color:var(--text-mid);line-height:1.6;" id="gcashPreviewDetails"></div>
        </div>
      </div>

      </div><!-- /settings -->

      <div class="fin-section" data-fin="payments">
      <!-- GCash payment submissions (validation queue) -->
      <div class="card" style="margin-top:16px;">
        <div class="card-title" style="margin:0 0 12px;">Payment Submissions</div>
        <div id="paymentsQueue"><div class="empty-state" style="padding:12px;">Loading…</div></div>
      </div>

      </div><!-- /payments -->

      <div class="fin-section" data-fin="expenses">
      <!-- HOA expense recording (where the residents' money goes) -->
      <div class="card" style="margin-top:16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;">
          <div class="card-title" style="margin:0;">HOA Expenses</div>
          <button class="btn btn-sm" onclick="openExpenseModal()">+ Record Expense</button>
        </div>
        <div id="adminExpenseList"><div class="empty-state" style="padding:12px;">Loading…</div></div>
      </div>

      </div><!-- /expenses -->

      <div class="fin-section" data-fin="dues">
      <!-- Dues & Assessments -->
      <div class="card" style="margin-top:16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;flex-wrap:wrap;">
          <div>
            <div class="card-title" style="margin:0;">Dues &amp; Assessments</div>
            <div style="font-size:12px;color:var(--text-dim);">Recurring charges posted to every active resident's statement.</div>
          </div>
          <button class="btn btn-sm" onclick="generateDuesNow()">Generate This Month</button>
        </div>
        <div id="assessmentList"><div class="empty-state" style="padding:12px;">Loading…</div></div>
        <div class="add-row" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;align-items:flex-end;">
          <div class="f-field" style="flex:2;min-width:150px;"><span class="f-label">Name</span><input type="text" id="assessName" placeholder="e.g. Monthly Dues"></div>
          <div class="f-field"><span class="f-label">Amount (₱)</span><input type="number" id="assessAmount" min="0.01" step="0.01" style="width:120px;"></div>
          <div class="f-field"><span class="f-label">Frequency</span>
            <select id="assessFrequency"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="annual">Annual</option><option value="one_time">One-time</option></select>
          </div>
          <div class="f-field"><span class="f-label">Due day</span><input type="number" id="assessDueDay" min="1" max="28" value="15" style="width:90px;"></div>
          <div class="f-field"><span class="f-label">Start</span><input type="date" id="assessStart"></div>
          <button class="btn btn-sm btn-green" onclick="saveAssessment()">+ Add</button>
        </div>
      </div>

      </div><!-- /dues -->

      <div class="fin-section" data-fin="collections">
      <!-- Collections & Aging -->
      <div class="card" style="margin-top:16px;">
        <div class="card-title" style="margin:0 0 12px;">Collections &amp; Aging</div>
        <div id="agingTable"><div class="empty-state" style="padding:12px;">Loading…</div></div>
      </div>

      </div><!-- /collections -->

      <div class="fin-section" data-fin="budgets">
      <!-- Category budgets -->
      <div class="card" style="margin-top:16px;">
        <div class="card-title" style="margin:0 0 4px;">Category Budgets</div>
        <div style="font-size:12px;color:var(--text-dim);margin-bottom:12px;">Planned spend per category — shown to residents as budget vs actual.</div>
        <div id="budgetList"><div class="empty-state" style="padding:12px;">Loading…</div></div>
        <div class="add-row" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;align-items:flex-end;">
          <div class="f-field" style="flex:2;min-width:140px;"><span class="f-label">Category</span><input type="text" id="budgetCategory" placeholder="e.g. Security"></div>
          <div class="f-field"><span class="f-label">Fund</span><select id="budgetFund"><option>Operating</option><option>Reserve</option></select></div>
          <div class="f-field"><span class="f-label">Amount (₱)</span><input type="number" id="budgetAmount" min="0" step="0.01" style="width:120px;"></div>
          <button class="btn btn-sm btn-green" onclick="saveBudget()">+ Add</button>
        </div>
      </div>

      </div><!-- /budgets -->

      <!-- HOA expense add/edit modal -->
      <div class="modal-overlay" id="modal-expense">
        <div class="modal-box" style="max-width:520px;">
          <div class="modal-title" id="expenseModalTitle">Record HOA Expense</div>
          <input type="hidden" id="expenseId">
          <div class="f-row"><div class="f-field"><span class="f-label">Title</span><input type="text" id="expenseTitle" maxlength="200"></div></div>
          <div class="f-row">
            <div class="f-field"><span class="f-label">Category</span>
              <select id="expenseCategory">
                <option>Maintenance</option><option>Utilities</option><option>Security</option>
                <option>Landscaping</option><option>Amenities</option><option>Insurance</option>
                <option>Administrative</option><option>Events</option><option>Other</option>
              </select>
            </div>
            <div class="f-field"><span class="f-label">Amount (₱)</span><input type="number" id="expenseAmount" min="0.01" step="0.01"></div>
          </div>
          <div class="f-row">
            <div class="f-field"><span class="f-label">Date</span><input type="date" id="expenseDate"></div>
            <div class="f-field"><span class="f-label">Fund</span>
              <select id="expenseFund"><option>Operating</option><option>Reserve</option></select>
            </div>
            <div class="f-field"><span class="f-label">Status</span>
              <select id="expenseStatus"><option>Approved</option><option>Pending</option><option>Paid</option></select>
            </div>
          </div>
          <div class="f-row"><div class="f-field"><span class="f-label">Description</span><textarea id="expenseDescription" rows="3"></textarea></div></div>
          <div class="f-row"><div class="f-field"><span class="f-label">Receipt (optional)</span><input type="file" id="expenseReceipt" accept="image/*"></div></div>
          <div style="font-size:12px;color:#f08080;margin-bottom:8px;" id="expenseMsg"></div>
          <div class="modal-actions">
            <button class="modal-close-btn" onclick="closeModal('expense')">Cancel</button>
            <button class="btn btn-sm" onclick="saveExpense()">Save Expense</button>
          </div>
        </div>
      </div>

      <!-- Payment validation modal -->
      <div class="modal-overlay" id="modal-payment">
        <div class="modal-box" style="max-width:560px;">
          <div class="modal-title">Validate Payment</div>
          <div id="paymentModalInfo" style="font-size:13px;color:var(--text-mid);margin-bottom:10px;"></div>
          <img id="paymentModalReceipt" alt="Resident receipt" style="max-width:100%;max-height:260px;border:1px solid var(--glass-border);border-radius:8px;margin-bottom:12px;display:none;">
          <div class="f-row"><div class="f-field"><span class="f-label">Admin Response</span><textarea id="paymentResponse" rows="2" placeholder="Optional note to the resident."></textarea></div></div>
          <div class="f-row"><div class="f-field"><span class="f-label">Official Receipt (optional)</span><input type="file" id="paymentOfficialReceipt" accept="image/*"></div></div>
          <div class="f-row"><div class="f-field"><span class="f-label">Rejection Reason (if rejecting)</span><input type="text" id="paymentRejectReason" maxlength="500"></div></div>
          <div style="font-size:12px;color:#f08080;margin-bottom:8px;" id="paymentMsg"></div>
          <div class="modal-actions" style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;">
            <button class="modal-close-btn" onclick="closeModal('payment')">Cancel</button>
            <button class="btn btn-sm" onclick="validatePayment('needs_info')">Needs Info</button>
            <button class="btn btn-sm" style="background:#f08080;color:#3a0d0d;" onclick="validatePayment('reject')">Reject</button>
            <button class="btn btn-sm btn-green" onclick="validatePayment('approve')">Approve</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ ARCHIVES ═══ -->
    <div class="panel" id="panel-archives">
      <div class="card" style="margin-top:0;">
        <div class="card-title" style="margin:0 0 12px;">Archives</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
          <button class="btn btn-sm btn-primary" id="arch-tab-issues" onclick="loadArchives('issues')">Issues</button>
          <button class="btn btn-sm" id="arch-tab-files" onclick="loadArchives('files')">Files</button>
          <button class="btn btn-sm" id="arch-tab-announcements" onclick="loadArchives('announcements')">Announcements</button>
          <button class="btn btn-sm" id="arch-tab-policies" onclick="loadArchives('policies')">Policies</button>
        </div>
        <div id="archiveList"><div class="empty-state" style="padding:12px;">Loading…</div></div>
      </div>
    </div>

  </div><!-- end .content -->
</main>

<!-- ═══ MODALS ═══ -->

<!-- Announcement -->
<div class="modal-overlay" id="modal-ann">
  <div class="modal-box">
    <div class="modal-title">Post Announcement</div>
    <!-- Template quick-fill -->
    <div style="display:flex;gap:6px;margin-bottom:14px;flex-wrap:wrap;" id="annTemplateRow">
      <span style="font-size:11px;color:var(--text-dim);align-self:center;white-space:nowrap;">Template:</span>
      <button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="applyAnnTemplate('notice')">📋 Notice</button>
      <button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="applyAnnTemplate('announcement')">📢 Announcement</button>
      <button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="applyAnnTemplate('event')">📅 Event</button>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Type</span>
        <select id="annType"><option value="notice">Notice</option><option value="urgent">Urgent</option><option value="event">Event</option></select>
      </div>
      <div class="f-field"><span class="f-label">Target Audience</span>
        <select id="annTarget"><option>All Residents</option><option>Block 1 Only</option><option>Block 2 Only</option><option>Block 3 Only</option><option>Block 4 Only</option><option>Block 5 Only</option></select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Priority</span>
        <select id="annPriority"><option>Normal</option><option>High</option></select>
      </div>
      <div class="f-field"><span class="f-label">Event Date (optional)</span>
        <input type="date" id="annDate">
      </div>
    </div>
    <div class="f-row"><div class="f-field"><span class="f-label">Title</span><input type="text" id="annTitle" placeholder="Announcement title"></div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Message</span><textarea id="annBody" placeholder="Write your announcement…" style="min-height:100px;resize:vertical;"></textarea></div></div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('ann')">Cancel</button>
      <button class="btn btn-sm" onclick="SubUI&&SubUI.busyClick?SubUI.busyClick(this,postAnnouncement):postAnnouncement()">Post Announcement</button>
    </div>
  </div>
</div>

<!-- Add Resident -->
<div class="modal-overlay" id="modal-addResident">
  <div class="modal-box">
    <div class="modal-title">Add Resident</div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Full Name</span><input type="text" id="newResName" placeholder="e.g. Pedro Santos"></div>
      <div class="f-field"><span class="f-label">Block & Lot</span><input type="text" id="newResBlock" placeholder="e.g. Blk 2 Lot 5"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Email</span><input type="email" id="newResEmail" placeholder="resident@email.com"></div>
      <div class="f-field"><span class="f-label">Contact Number</span><input type="text" id="newResContact" placeholder="09xx-xxx-xxxx"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Password</span><input type="password" id="newResPass" placeholder="Required – min 8 characters"></div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('addResident')">Cancel</button>
      <button class="btn btn-sm" onclick="SubUI&&SubUI.busyClick?SubUI.busyClick(this,addResident):addResident()">Add Resident</button>
    </div>
  </div>
</div>

<!-- Add Officer -->
<div class="modal-overlay" id="modal-addOfficer">
  <div class="modal-box">
    <div class="modal-title">Add Officer</div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Full Name</span><input type="text" id="newOfficerName" placeholder="Full name"></div>
      </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Email</span><input type="email" id="newOfficerEmail" placeholder="officer@email.com"></div>
      <div class="f-field"><span class="f-label">Password</span><input type="password" id="newOfficerPass" placeholder="Required – min 8 characters"></div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('addOfficer')">Cancel</button>
      <button class="btn btn-sm" onclick="addOfficer()">Add Officer</button>
    </div>
  </div>
</div>

<!-- Respond to Issue -->
<div class="modal-overlay" id="modal-respond">
  <div class="modal-box">
    <div class="modal-title">Respond to Issue</div>
    <div style="font-size:13px;color:var(--text-mid);margin-bottom:16px;" id="respondIssueTitle"></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Update Status</span>
      <select id="respondStatus"><option value="Pending">Pending</option><option value="In Progress">In Progress</option><option value="Resolved">Resolved</option></select>
    </div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Admin Response</span><textarea id="respondText" placeholder="Type your response to the resident…"></textarea></div></div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('respond')">Cancel</button>
      <button class="btn btn-sm" onclick="submitResponse()">Send Response</button>
    </div>
  </div>
</div>

<!-- Assign Issue (officer + SLA due date) -->
<div class="modal-overlay" id="modal-assign">
  <div class="modal-box">
    <div class="modal-title">Assign Issue</div>
    <div style="font-size:13px;color:var(--text-mid);margin-bottom:16px;" id="assignIssueTitle"></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Assigned Officer</span>
      <select id="assignOfficer"><option value="">— Unassigned —</option></select>
    </div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">SLA Due Date</span>
      <input type="date" id="assignDueDate">
    </div></div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('assign')">Cancel</button>
      <button class="btn btn-sm" onclick="submitAssign()">Save Assignment</button>
    </div>
  </div>
</div>

<!-- Add Receipt -->
<!-- New Conversation -->
<div class="modal-overlay" id="modal-newThread">
  <div class="modal-box">
    <div class="modal-title">Start New Conversation</div>
    <div class="f-row">
      <div class="f-field" style="flex:1"><span class="f-label">Subject / Title</span>
        <input type="text" id="newThreadTitle" placeholder="e.g. Billing Inquiry — Block 3 Lot 5">
      </div>
    </div>
    <div class="f-row">
      <div class="f-field" style="flex:1"><span class="f-label">Resident (optional)</span>
        <select id="newThreadResident"><option value="">— General / No specific resident —</option></select>
      </div>
      <div class="f-field" style="flex:1"><span class="f-label">Officer (optional)</span>
        <select id="newThreadOfficer"><option value="">— No specific officer —</option></select>
      </div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('newThread')">Cancel</button>
      <button class="btn btn-sm" onclick="adminStartThread()">Start Conversation</button>
    </div>
  </div>
</div>



<div id="toast"></div>

<!-- ═══ MANAGE USERS MODALS ═══ -->

<!-- Add Household -->
<div class="modal-overlay" id="modal-muAddHousehold">
  <div class="modal-box">
    <div class="modal-title">Add Household</div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Location (Block & Lot)</span><input type="text" id="muHLoc" placeholder="e.g. Blk 3 Lot 12"></div>
      <div class="f-field"><span class="f-label">Family Name</span><input type="text" id="muHFamily" placeholder="e.g. Santos Family"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">No. of Members</span><input type="text" id="muHMembers" placeholder="e.g. 4"></div>
      <div class="f-field"><span class="f-label">Status</span>
        <select id="muHStatus"><option>Occupied</option><option>Vacant</option></select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Head of Household</span><input type="text" id="muHHead" placeholder="Full name"></div>
      <div class="f-field"><span class="f-label">Contact Number</span><input type="text" id="muHContact" placeholder="09xx-xxx-xxxx"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Notes (optional)</span><textarea id="muHNotes" style="min-height:60px;" placeholder="Any special notes about this household…"></textarea></div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('muAddHousehold')">Cancel</button>
      <button class="btn btn-sm" onclick="saveAddHousehold()">Add Household</button>
    </div>
  </div>
</div>

<!-- Edit Household -->
<div class="modal-overlay" id="modal-muEditHousehold">
  <div class="modal-box">
    <div class="modal-title">Edit Household</div>
    <input type="hidden" id="editHIdx">
    <div class="f-row">
      <div class="f-field"><span class="f-label">Location (Block & Lot)</span><input type="text" id="editHLoc" placeholder="e.g. Blk 3 Lot 12"></div>
      <div class="f-field"><span class="f-label">Family Name</span><input type="text" id="editHFamily" placeholder="e.g. Santos Family"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">No. of Members</span><input type="text" id="editHMembers"></div>
      <div class="f-field"><span class="f-label">Status</span>
        <select id="editHStatus"><option>Occupied</option><option>Vacant</option></select>
      </div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('muEditHousehold')">Cancel</button>
      <button class="btn btn-sm" onclick="saveEditHousehold()">Save Changes</button>
    </div>
  </div>
</div>

<!-- Change Status -->
<div class="modal-overlay" id="modal-muChangeStatus">
  <div class="modal-box">
    <div class="modal-title">Change Household Status</div>
    <input type="hidden" id="statusHIdx">
    <div style="margin-bottom:16px;padding:14px;border-radius:var(--radius-sm);background:rgba(255,255,255,0.05);border:1px solid var(--glass-border);">
      <div style="font-size:12px;color:var(--text-dim);margin-bottom:4px;">Selected Household</div>
      <div id="statusHName" style="font-size:15px;font-weight:500;"></div>
      <div id="statusHLoc" style="font-size:12px;color:var(--text-dim);margin-top:2px;"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">New Status</span>
        <select id="statusHNew"><option>Occupied</option><option>Vacant</option></select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Reason for Status Change</span>
        <select id="statusHReason">
          <option>Violation of subdivision rules</option>
          <option>Extended leave / vacation</option>
          <option>Property sold / transferred</option>
          <option>Resolved — reinstating</option>
          <option>Administrative update</option>
          <option>Other</option>
        </select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Additional Remarks (optional)</span>
        <textarea id="statusHRemarks" style="min-height:70px;" placeholder="Add any notes about this status change…"></textarea>
      </div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('muChangeStatus')">Cancel</button>
      <button class="btn btn-sm" onclick="saveChangeStatus()">Apply Status</button>
    </div>
  </div>
</div>

<!-- Add User -->
<div class="modal-overlay" id="modal-muAddUser">
  <div class="modal-box">
    <div class="modal-title">Add System User</div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Full Name</span><input type="text" id="muUName" placeholder="Full name"></div>
      <div class="f-field"><span class="f-label">Email Address</span><input type="email" id="muUEmail" placeholder="email@example.com"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Role</span>
        <select id="muURole">
          <option>Resident</option>
          <option>Officer</option>
        </select>
      </div>
      <div class="f-field"><span class="f-label">Linked Household</span>
        <select id="muUHousehold">
          <option value="">— None —</option>
        </select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Temporary Password</span><input type="password" id="muUPass" placeholder="Set a temporary password"></div>
      <div class="f-field"><span class="f-label">Confirm Password</span><input type="password" id="muUPass2" placeholder="Repeat password"></div>
    </div>
    <div style="font-size:11px;color:var(--text-dim);margin-bottom:10px;">⚠️ User will be prompted to change password on first login.</div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('muAddUser')">Cancel</button>
      <button class="btn btn-sm" onclick="saveAddUser()">Create User</button>
    </div>
  </div>
</div>

<!-- Edit User -->
<div class="modal-overlay" id="modal-muEditUser">
  <div class="modal-box">
    <div class="modal-title">Edit User</div>
    <input type="hidden" id="editUIdx">
    <div class="f-row">
      <div class="f-field"><span class="f-label">Full Name</span><input type="text" id="editUName"></div>
      <div class="f-field"><span class="f-label">Email Address</span><input type="email" id="editUEmail"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Account Status</span>
        <select id="editUStatus"><option>Active</option><option>Inactive</option></select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Reset Password (leave blank to keep current)</span><input type="password" id="editUPass" placeholder="New password"></div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('muEditUser')">Cancel</button>
      <button class="btn btn-sm" onclick="saveEditUser()">Save Changes</button>
    </div>
  </div>
</div>

<!-- (Add Resident handled by modal-addResident) -->

<!-- Edit Officer -->
<div class="modal-overlay" id="modal-editOfficer">
  <div class="modal-box">
    <div class="modal-title">Edit Officer</div>
    <input type="hidden" id="editOfficerIdx">
    <div class="f-row">
      <div class="f-field"><span class="f-label">Full Name</span><input type="text" id="editOfficerName" placeholder="Full name"></div>
      <div class="f-field"><span class="f-label">Email</span><input type="email" id="editOfficerEmail" placeholder="email@example.com"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Contact Number</span><input type="text" id="editOfficerContact" placeholder="09xxxxxxxxx"></div>
      </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Account Status</span>
        <select id="editOfficerStatus"><option>Active</option><option>Inactive</option><option>Pending</option></select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Reset Password (leave blank to keep current)</span><input type="password" id="editOfficerPass" placeholder="New password"></div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('editOfficer')">Cancel</button>
      <button class="btn btn-sm" onclick="saveEditOfficer()">Save Changes</button>
    </div>
  </div>
</div>

<!-- (Edit Resident handled inline) -->

<!-- Add Member -->
<div class="modal-overlay" id="modal-muAddMember">
  <div class="modal-box">
    <div class="modal-title">Add Household Member</div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Full Name</span><input type="text" id="muMName" placeholder="Full name"></div>
      <div class="f-field"><span class="f-label">Relationship to Head</span>
        <select id="muMRelation">
          <option>Head of Household</option>
          <option>Spouse</option>
          <option>Child</option>
          <option>Parent</option>
          <option>Sibling</option>
          <option>Relative</option>
          <option>Tenant</option>
        </select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Household (Block & Lot)</span>
        <select id="muMHousehold"><option value="">— Select Household —</option></select>
      </div>
      <div class="f-field"><span class="f-label">Member Type</span>
        <select id="muMType"><option>HOA Member</option><option>Officer</option><option>Tenant</option></select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Date of Birth</span><input type="date" id="muMDob"></div>
      <div class="f-field"><span class="f-label">Contact Number</span><input type="text" id="muMContact" placeholder="09xx-xxx-xxxx"></div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('muAddMember')">Cancel</button>
      <button class="btn btn-sm" onclick="saveAddMember()">Add Member</button>
    </div>
  </div>
</div>

<!-- Edit Member -->
<div class="modal-overlay" id="modal-muEditMember">
  <div class="modal-box">
    <div class="modal-title">Edit Member</div>
    <input type="hidden" id="editMIdx">
    <div class="f-row">
      <div class="f-field"><span class="f-label">Full Name</span><input type="text" id="editMName"></div>
      <div class="f-field"><span class="f-label">Household</span><input type="text" id="editMHousehold"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Relationship to Head</span>
        <select id="editMType"><option>Head of Household</option><option>Spouse</option><option>Child</option><option>Parent</option><option>Sibling</option><option>Relative</option><option>Tenant</option></select>
      </div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('muEditMember')">Cancel</button>
      <button class="btn btn-sm" onclick="saveEditMember()">Save Changes</button>
    </div>
  </div>
</div>

<script>window.__ADMIN = { id: {{ $adminId ?? 'null' }}, name: @json($adminName) };</script>
<script src="{{ asset('js/admin-dashboard.js') . '?v=' . filemtime(public_path('js/admin-dashboard.js')) }}"></script>

<!-- Add Facility Modal -->
<div class="modal-overlay" id="modal-payDetail">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-title">Payment Record</div>
    <div id="payDetailBody"></div>
    <div class="modal-actions" style="margin-top:16px;">
      <button class="modal-close-btn" onclick="closeModal('payDetail')">Close</button>
    </div>
  </div>
</div>

<!-- Add Facility Modal -->
<div class="modal-overlay" id="modal-addFacility">
  <div class="modal-box">
    <div class="modal-title">Add Facility</div>
    <div class="f-row"><div class="f-field"><span class="f-label">Facility Name</span><input type="text" id="fac-name" placeholder="e.g. Clubhouse, Basketball Court"></div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Description</span><textarea id="fac-desc" placeholder="Short description (optional)"></textarea></div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Photo (optional)</span>
      <input type="file" id="fac-image" accept="image/*" onchange="previewImage(this,'fac-image-preview')">
      <img id="fac-image-preview" alt="preview" style="display:none;margin-top:8px;max-width:100%;max-height:170px;border-radius:8px;border:1px solid var(--glass-border);">
    </div></div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Latitude</span><input type="text" id="fac-lat" readonly placeholder="Set by clicking map"></div>
      <div class="f-field"><span class="f-label">Longitude</span><input type="text" id="fac-lng" readonly placeholder="Set by clicking map"></div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('addFacility');cancelAddFacilityMode()">Cancel</button>
      <button class="btn btn-sm btn-green" onclick="saveNewFacility()">Save Facility</button>
    </div>
  </div>
</div>

<!-- Edit Facility Modal -->
<div class="modal-overlay" id="modal-editFacility">
  <div class="modal-box">
    <div class="modal-title">Edit Facility</div>
    <div class="f-row"><div class="f-field"><span class="f-label">Name</span><input type="text" id="edit-fac-name"></div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Description</span><textarea id="edit-fac-desc"></textarea></div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Photo</span>
      <img id="edit-fac-preview" alt="facility" style="display:none;margin-bottom:8px;max-width:100%;max-height:170px;border-radius:8px;border:1px solid var(--glass-border);">
      <input type="file" id="edit-fac-image" accept="image/*" onchange="previewImage(this,'edit-fac-preview')">
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-dim);margin-top:8px;white-space:normal;"><input type="checkbox" id="edit-fac-remove-image"> Remove current photo</label>
    </div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Status</span>
      <select id="edit-fac-status"><option value="Active">Active</option><option value="Inactive">Inactive</option></select>
    </div></div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('editFacility')">Cancel</button>
      <button class="btn btn-sm" onclick="saveEditFacility()">Save Changes</button>
    </div>
  </div>
</div>

<script src="{{ asset('js/d3.v7.min.js') }}"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
<script>
/* ══ LAZY LEAFLET LOADER — Leaflet loads from local origin only when the map is opened ══ */
let _leafletLoaded = false;
function loadLeaflet(){
  if(_leafletLoaded) return Promise.resolve();
  _leafletLoaded = true;
  const AB = window.APP_BASE || '';
  return new Promise((resolve) => {
    if(!document.querySelector('link[href*="leaflet.css"]')){
      const l = document.createElement('link');
      l.rel = 'stylesheet';
      l.href = AB + '/vendor/leaflet/leaflet.css';
      document.head.appendChild(l);
    }
    const srcs = [
      AB + '/vendor/leaflet/leaflet.js',
    ];
    let i = 0;
    (function next(){
      if(i >= srcs.length){
        if(window.L && L.Icon && L.Icon.Default){
          L.Icon.Default.mergeOptions({
            iconUrl: AB + '/vendor/leaflet/images/marker-icon.png',
            iconRetinaUrl: AB + '/vendor/leaflet/images/marker-icon-2x.png',
            shadowUrl: AB + '/vendor/leaflet/images/marker-shadow.png',
          });
        }
        resolve(); return;
      }
      const s = document.createElement('script');
      s.src = srcs[i++];
      s.onload = next;
      s.onerror = () => { s.onerror = null; next(); };
      document.body.appendChild(s);
    })();
  });
}
</script>
<script>
  function toggleSidebar(){
  const sb  = document.getElementById('sidebar');
  const ov  = document.getElementById('sidebarOverlay');
  const btn = document.getElementById('menuToggle');
  const isOpen = sb.classList.contains('open');
  sb.classList.toggle('open');
  ov.classList.toggle('show');
  btn.textContent = isOpen ? '☰' : '✕';
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('show');
  document.getElementById('menuToggle').textContent = '☰';
}
/* ══════════════ LEAFLET MAP ══════════════ */
let mapInst = null, layerHouseholds, layerFacilities, layerIssues;
let analyticsMapInst = null;
let addFacilityMode = false, pendingPin = null;
let editFacilityId = null;
let issueMarkerCount = 0;

function makeCircleIcon(color) {
  return L.divIcon({
    className: '',
    html: `<div style="width:13px;height:13px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.85);box-shadow:0 1px 5px rgba(0,0,0,0.5);"></div>`,
    iconSize: [13, 13], iconAnchor: [6, 6], popupAnchor: [0, -9]
  });
}

function makeNumberedIcon(color, num) {
  return L.divIcon({
    className: '',
    html: `<div style="width:26px;height:26px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.9);box-shadow:0 2px 6px rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;font-family:sans-serif;">${num}</div>`,
    iconSize: [26, 26], iconAnchor: [13, 13], popupAnchor: [0, -14]
  });
}

let mapSettings = null;
let areaRect = null;
let customizingArea = false;

async function fetchMapSettings(){
  try{
    const r = await fetch('/api/map/settings',{headers:{'Accept':'application/json'}});
    mapSettings = await r.json();
  }catch(e){
    mapSettings = {min_lat:10.6195,max_lat:10.6260,min_lng:122.9580,max_lng:122.9650,center_lat:10.62269,center_lng:122.96134,zoom:17};
  }
}

function applyMapArea(){
  const b = mapSettings || {};
  const bounds = L.latLngBounds([[b.min_lat,b.min_lng],[b.max_lat,b.max_lng]]);
  mapInst.setMinZoom(14);
  mapInst.setMaxZoom(20);
  mapInst.setMaxBounds(bounds);
  mapInst.options.maxBoundsViscosity = 1.0;
  if(areaRect) areaRect.remove();
  areaRect = L.rectangle(bounds,{color:'#A07D53',weight:2,dashArray:'6 4',fillColor:'#A07D53',fillOpacity:0.06}).addTo(mapInst);
}

function enterCustomizeArea(){
  customizingArea = true;
  document.getElementById('map-area-toolbar').style.display='flex';
  document.getElementById('map-customize-hint').style.display='block';
  // release the lock so the admin can pan/zoom to frame the new area
  if(mapInst){
    mapInst.setMaxBounds(null);
    document.getElementById('btn-save-area').disabled = false;
    document.getElementById('btn-reset-area').disabled = false;
  }
}

function saveMapArea(){
  if(!mapInst || !customizingArea) return;
  const b = mapInst.getBounds();
  const c = mapInst.getCenter();
  const payload = {
    min_lat:    +b.getSouthWest().lat.toFixed(7),
    max_lat:    +b.getNorthEast().lat.toFixed(7),
    min_lng:    +b.getSouthWest().lng.toFixed(7),
    max_lng:    +b.getNorthEast().lng.toFixed(7),
    center_lat: +c.lat.toFixed(7),
    center_lng: +c.lng.toFixed(7),
    zoom:       mapInst.getZoom(),
  };
  fetch('/api/map/settings',{
    method:'PUT',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
    body: JSON.stringify(payload),
  }).then(r=>r.json()).then(()=>{
    mapSettings = payload;
    applyMapArea();
    customizingArea = false;
    document.getElementById('map-customize-hint').style.display='none';
    showToast('✅ Map area updated.');
    if(typeof loadMapData === 'function') loadMapData();
  }).catch(()=>showToast('⚠ Could not save map area.'));
}

function resetMapArea(){
  const payload = {min_lat:10.6195,max_lat:10.6260,min_lng:122.9580,max_lng:122.9650,center_lat:10.62269,center_lng:122.96134,zoom:17};
  fetch('/api/map/settings',{
    method:'PUT',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
    body: JSON.stringify(payload),
  }).then(r=>r.json()).then(()=>{
    mapSettings = payload;
    if(mapInst){ mapInst.setMaxBounds(null); mapInst.fitBounds([[payload.min_lat,payload.min_lng],[payload.max_lat,payload.max_lng]],{padding:[20,20]}); applyMapArea(); }
    customizingArea = false;
    document.getElementById('map-customize-hint').style.display='none';
    showToast('✅ Map area reset to default.');
    if(typeof loadMapData === 'function') loadMapData();
  }).catch(()=>showToast('⚠ Could not reset map area.'));
}

/* ══════════════ GCASH SETTINGS ══════════════ */
let gcashSettings = null;
let gcashNewQrFile = null;

async function loadGcashSettings(){
  try{
    const r = await fetch('/api/gcash-settings',{headers:{'Accept':'application/json'}});
    gcashSettings = await r.json();
  }catch(e){ gcashSettings = null; }
  gcashNewQrFile = null;
  const nm = document.getElementById('gcashAccountName');
  if(!nm) return;
  nm.value = gcashSettings?.account_name || '';
  document.getElementById('gcashAccountNumber').value = gcashSettings?.account_number || '';
  document.getElementById('gcashInstructions').value = gcashSettings?.instructions || '';
  document.getElementById('gcashQrInput').value = '';
  document.getElementById('gcashMsg').textContent = '';
  renderGcashPreview();
}

function previewGcashQr(inp){
  const f = inp.files && inp.files[0];
  if(!f) return;
  gcashNewQrFile = f;
  const reader = new FileReader();
  reader.onload = e => renderGcashPreview(e.target.result);
  reader.readAsDataURL(f);
}

function renderGcashPreview(overrideUrl){
  const box = document.getElementById('gcashPreview');
  if(!box) return;
  const url = overrideUrl || gcashSettings?.qr_image_url || null;
  box.innerHTML = url
    ? `<img src="${url}" alt="GCash QR" style="max-width:200px;max-height:260px;border-radius:8px;">`
    : '<div style="font-size:12px;color:var(--text-dim);">No QR code set yet.</div>';
  const name = document.getElementById('gcashAccountName').value || '';
  const num  = document.getElementById('gcashAccountNumber').value || '';
  const ins  = document.getElementById('gcashInstructions').value || '';
  document.getElementById('gcashPreviewDetails').innerHTML =
    `${name?`<strong>${escHtml(name)}</strong><br>`:''}${num?`GCash: ${escHtml(num)}<br>`:''}${ins?escHtml(ins):''}`;
}

function gcashFormData(){
  const fd = new FormData();
  fd.append('account_name',   document.getElementById('gcashAccountName').value.trim());
  fd.append('account_number', document.getElementById('gcashAccountNumber').value.trim());
  fd.append('instructions',   document.getElementById('gcashInstructions').value.trim());
  return fd;
}

async function saveGcashSettings(){
  const nm = document.getElementById('gcashAccountName');
  if(!nm) return;
  const btn = document.getElementById('gcashSaveBtn');
  const msg = document.getElementById('gcashMsg');
  msg.textContent = '';
  const fd = gcashFormData();
  if(gcashNewQrFile) fd.append('qr_image', gcashNewQrFile);
  btn.disabled = true; btn.textContent = 'Saving…';
  try{
    const r = await fetch('/api/gcash-settings',{
      method:'POST',
      headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
      body: fd,
    });
    const data = await r.json();
    if(data.success){
      gcashSettings = data.settings;
      gcashNewQrFile = null;
      document.getElementById('gcashQrInput').value = '';
      renderGcashPreview();
      showToast('✅ GCash details updated.');
    } else {
      msg.textContent = data.message || 'Could not save changes.';
    }
  }catch(e){
    msg.textContent = '⚠ Could not save GCash settings.';
  }
  btn.disabled = false; btn.textContent = 'Save Changes';
}

async function removeGcashQr(){
  if(!gcashSettings?.qr_image_path){ showToast('No QR code to remove.'); return; }
  if(!(await SubUI.confirm({ title:'Remove QR code?', message:'Remove the current GCash QR code?', confirmText:'Remove', danger:true, icon:'!' }))) return;
  const fd = gcashFormData();
  fd.append('remove_qr', '1');
  try{
    const r = await fetch('/api/gcash-settings',{
      method:'POST',
      headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
      body: fd,
    });
    const data = await r.json();
    if(data.success){
      gcashSettings = data.settings;
      gcashNewQrFile = null;
      document.getElementById('gcashQrInput').value = '';
      renderGcashPreview();
      showToast('✅ QR code removed.');
    }
  }catch(e){ showToast('⚠ Could not remove QR code.'); }
}

/* ══════════════ GCASH PAYMENT VALIDATION QUEUE ══════════════ */
let paymentsQueue = [];
let currentPaymentId = null;
async function loadPaymentsQueue(){
  const el = document.getElementById('paymentsQueue'); if(!el) return;
  try{ paymentsQueue = await apiGet('/api/payments'); }catch(e){ paymentsQueue = []; }
  renderPaymentsQueue();
}
function renderPaymentsQueue(){
  const el = document.getElementById('paymentsQueue'); if(!el) return;
  if(!Array.isArray(paymentsQueue) || !paymentsQueue.length){ el.innerHTML = '<div class="empty-state" style="padding:12px;">No payment submissions yet.</div>'; return; }
  const pill = {Pending:'pill-pending',Validated:'pill-resolved',Rejected:'pill-urgent','Needs Info':'pill-progress'};
  el.innerHTML = '<table class="panel-table"><thead><tr><th>Resident</th><th>Amount</th><th>Reference</th><th>Submitted</th><th>Status</th><th></th></tr></thead><tbody>' +
    paymentsQueue.map(p=>`<tr>
      <td>${escHtml(p.resident||'—')}</td>
      <td>₱${Number(p.amount).toLocaleString(undefined,{minimumFractionDigits:2})}</td>
      <td>${escHtml(p.reference_no||'—')}</td>
      <td>${escHtml(p.created_at||'—')}</td>
      <td><span class="pill ${pill[p.status]||'pill-notice'}">${escHtml(p.status)}</span></td>
      <td style="text-align:right;white-space:nowrap;"><button class="btn btn-sm" onclick="openPaymentModal(${p.id})">Review</button></td>
    </tr>`).join('') + '</tbody></table>';
}
function openPaymentModal(id){
  const p = paymentsQueue.find(x=>x.id===id); if(!p) return;
  currentPaymentId = id;
  document.getElementById('paymentModalInfo').innerHTML = `${escHtml(p.resident||'')} · ₱${Number(p.amount).toLocaleString(undefined,{minimumFractionDigits:2})} · Ref: ${escHtml(p.reference_no||'—')} · Paid: ${escHtml(p.paid_at||'—')}`;
  const img = document.getElementById('paymentModalReceipt');
  if(p.has_receipt){ img.style.display = ''; img.src = '/api/payments/'+id+'/receipt'; } else { img.style.display = 'none'; img.removeAttribute('src'); }
  document.getElementById('paymentResponse').value = p.admin_response || '';
  document.getElementById('paymentRejectReason').value = p.rejection_reason || '';
  document.getElementById('paymentOfficialReceipt').value = '';
  document.getElementById('paymentMsg').textContent = '';
  openModal('payment');
}
async function validatePayment(action){
  if(!currentPaymentId) return;
  const fd = new FormData();
  fd.append('action', action);
  fd.append('admin_response', document.getElementById('paymentResponse').value.trim());
  fd.append('rejection_reason', document.getElementById('paymentRejectReason').value.trim());
  const off = document.getElementById('paymentOfficialReceipt').files[0];
  if(off) fd.append('official_receipt', off);
  const msg = document.getElementById('paymentMsg');
  try{
    const res = await fetch('/api/payments/'+currentPaymentId+'/validate',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:fd});
    const data = await res.json();
    if(data.success){ closeModal('payment'); await loadPaymentsQueue(); showToast('✅ Payment ' + action + 'd.'); }
    else msg.textContent = data.message || 'Could not validate payment.';
  }catch(e){ msg.textContent = 'Could not validate payment.'; }
}

/* ══════════════ HOA EXPENSES (admin) ══════════════ */
let adminExpenses = [];
async function loadAdminExpenses(){
  const el = document.getElementById('adminExpenseList'); if(!el) return;
  try{ adminExpenses = await apiGet('/api/hoa-expenses'); }catch(e){ adminExpenses = []; }
  renderAdminExpenses();
}
function renderAdminExpenses(){
  const el = document.getElementById('adminExpenseList'); if(!el) return;
  if(!Array.isArray(adminExpenses) || !adminExpenses.length){ el.innerHTML = '<div class="empty-state" style="padding:12px;">No expenses recorded yet. Click “Record Expense”.</div>'; return; }
  el.innerHTML = '<table class="panel-table"><thead><tr><th>Date</th><th>Category</th><th>Fund</th><th>Title</th><th>Amount</th><th>Receipt</th><th></th></tr></thead><tbody>' +
    adminExpenses.map(e=>`<tr>
      <td>${escHtml(e.expense_date||'—')}</td>
      <td>${escHtml(e.category||'—')}</td>
      <td>${escHtml(e.fund||'Operating')}</td>
      <td>${escHtml(e.title)}</td>
      <td>₱${Number(e.amount).toLocaleString(undefined,{minimumFractionDigits:2})}</td>
      <td>${e.receipt_url?`<a href="${e.receipt_url}" target="_blank" rel="noopener">View</a>`:'—'}</td>
      <td style="text-align:right;white-space:nowrap;">
        <button class="btn btn-sm" onclick="openExpenseModal(${e.id})">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteExpense(${e.id})">Delete</button>
      </td>
    </tr>`).join('') + '</tbody></table>';
}
function openExpenseModal(id){
  const e = id ? adminExpenses.find(x=>x.id===id) : null;
  document.getElementById('expenseModalTitle').textContent = e ? 'Edit HOA Expense' : 'Record HOA Expense';
  document.getElementById('expenseId').value = e ? e.id : '';
  document.getElementById('expenseTitle').value = e ? e.title : '';
  document.getElementById('expenseCategory').value = e ? (e.category||'Maintenance') : 'Maintenance';
  document.getElementById('expenseAmount').value = e ? e.amount : '';
  document.getElementById('expenseDate').value = e ? (e.expense_date||'') : new Date().toISOString().slice(0,10);
  document.getElementById('expenseStatus').value = e ? (e.status||'Approved') : 'Approved';
  document.getElementById('expenseFund').value = e ? (e.fund||'Operating') : 'Operating';
  document.getElementById('expenseDescription').value = e ? e.description : '';
  document.getElementById('expenseReceipt').value = '';
  document.getElementById('expenseMsg').textContent = '';
  openModal('expense');
}
async function saveExpense(){
  const id = document.getElementById('expenseId').value;
  const fd = new FormData();
  fd.append('title', document.getElementById('expenseTitle').value.trim());
  fd.append('category', document.getElementById('expenseCategory').value);
  fd.append('amount', document.getElementById('expenseAmount').value);
  fd.append('expense_date', document.getElementById('expenseDate').value);
  fd.append('status', document.getElementById('expenseStatus').value);
  fd.append('fund', document.getElementById('expenseFund').value);
  fd.append('description', document.getElementById('expenseDescription').value.trim());
  const rec = document.getElementById('expenseReceipt').files[0];
  if(rec) fd.append('receipt', rec);
  const msg = document.getElementById('expenseMsg');
  if(!fd.get('title') || !fd.get('amount') || !fd.get('expense_date') || !fd.get('description')){ msg.textContent = 'Please fill in all fields.'; return; }
  if(id) fd.append('_method','PUT');
  try{
    const res = await fetch('/api/hoa-expenses' + (id ? '/'+id : ''), {
      method:'POST',
      headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
      body: fd,
    });
    const data = await res.json();
    if(data.success){ closeModal('expense'); await loadAdminExpenses(); showToast('✅ Expense saved.'); }
    else msg.textContent = data.message || 'Could not save expense.';
  }catch(e){ msg.textContent = 'Could not save expense.'; }
}
async function deleteExpense(id){
  const ok = await SubUI.confirm({ title:'Delete this expense?', message:'This removes it from the HOA transparency record. This cannot be undone.', confirmText:'Delete', danger:true, icon:'!' });
  if(!ok) return;
  const res = await apiDelete('/api/hoa-expenses/'+id);
  if(res && res.success){ await loadAdminExpenses(); showToast('✅ Expense deleted.'); }
  else showToast('⚠ Could not delete expense.');
}

/* ══════════════ DUES, COLLECTIONS & BUDGETS ══════════════ */
let assessments = [];
let agingRows = [];
let budgetRows = [];
async function loadAssessments(){ try{ assessments = await apiGet('/api/assessments'); }catch(e){ assessments = []; } renderAssessments(); }
function renderAssessments(){
  const el = document.getElementById('assessmentList'); if(!el) return;
  if(!Array.isArray(assessments) || !assessments.length){ el.innerHTML = '<div class="empty-state" style="padding:12px;">No assessments yet. Add one above.</div>'; return; }
  const peso = v => '₱'+Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2});
  el.innerHTML = '<table class="panel-table"><thead><tr><th>Name</th><th>Amount</th><th>Frequency</th><th>Due day</th><th>Status</th><th></th></tr></thead><tbody>' +
    assessments.map(a=>`<tr>
      <td>${escHtml(a.name)}</td>
      <td>${peso(a.amount)}</td>
      <td>${escHtml(a.frequency)}</td>
      <td>${a.due_day||'—'}</td>
      <td>${a.is_active?'Active':'Inactive'}</td>
      <td style="text-align:right;white-space:nowrap;">
        <button class="btn btn-sm" onclick="toggleAssessment(${a.id},${a.is_active?0:1})">${a.is_active?'Disable':'Enable'}</button>
        <button class="btn btn-sm btn-danger" onclick="deleteAssessment(${a.id})">Delete</button>
      </td></tr>`).join('') + '</tbody></table>';
}
async function saveAssessment(){
  const name = document.getElementById('assessName').value.trim();
  const amount = document.getElementById('assessAmount').value;
  const frequency = document.getElementById('assessFrequency').value;
  const due_day = document.getElementById('assessDueDay').value || null;
  const start_date = document.getElementById('assessStart').value || new Date().toISOString().slice(0,10);
  if(!name || !amount){ showToast('⚠ Enter a name and amount.'); return; }
  const res = await apiPost('/api/assessments',{name,amount,frequency,due_day,start_date,is_active:1});
  if(res.success){ ['assessName','assessAmount'].forEach(id=>document.getElementById(id).value=''); await loadAssessments(); showToast('✅ Assessment added.'); }
  else showToast('⚠ '+(res.message||'Could not add assessment.'));
}
async function toggleAssessment(id,active){
  const a = (assessments||[]).find(x=>x.id===id); if(!a) return;
  const res = await apiPut('/api/assessments/'+id,{name:a.name,amount:a.amount,frequency:a.frequency,due_day:a.due_day,start_date:a.start_date,is_active:!!active});
  if(res.success){ await loadAssessments(); showToast('✅ Updated.'); } else showToast('⚠ Failed.');
}
async function deleteAssessment(id){
  if(!(await SubUI.confirm({title:'Delete assessment?',message:'Already-posted charges are kept; only future generation stops.',confirmText:'Delete',danger:true,icon:'!'}))) return;
  const res = await apiDelete('/api/assessments/'+id);
  if(res.success){ await loadAssessments(); showToast('✅ Deleted.'); } else showToast('⚠ Failed.');
}
async function generateDuesNow(){
  const res = await apiPost('/api/assessments/generate',{});
  if(res.success){ await loadAssessments(); await loadAging(); if(typeof loadAdminExpenses==='function') await loadAdminExpenses(); showToast('✅ '+(res.message||'Dues generated.')); }
  else showToast('⚠ Could not generate dues.');
}
async function loadAging(){ try{ agingRows = await apiGet('/api/ledger/aging'); }catch(e){ agingRows = []; } renderAging(); }
function renderAging(){
  const el = document.getElementById('agingTable'); if(!el) return;
  const rows = (agingRows||[]).filter(r=>Number(r.balance)!==0 || (r.credit||0)>0);
  if(!rows.length){ el.innerHTML = '<div class="empty-state" style="padding:12px;">All residents are settled.</div>'; return; }
  const peso = v => '₱'+Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2});
  el.innerHTML = '<div style="overflow-x:auto;"><table class="panel-table"><thead><tr><th>Resident</th><th>Block/Lot</th><th>Balance</th><th>Current</th><th>1–30d</th><th>31–60d</th><th>61–90d</th><th>90d+</th></tr></thead><tbody>' +
    rows.map(r=>{ const b=r.buckets||{}; return `<tr>
      <td>${escHtml(r.name)}</td><td>${escHtml(r.block_lot||'—')}</td>
      <td style="font-weight:600;">${peso(r.balance)}</td>
      <td>${peso(b.current)}</td><td>${peso(b.d1_30)}</td><td>${peso(b.d31_60)}</td><td>${peso(b.d61_90)}</td><td>${peso(b.d90_plus)}</td>
    </tr>`; }).join('') + '</tbody></table></div>';
}
async function loadBudgets(){ try{ budgetRows = await apiGet('/api/budgets'); }catch(e){ budgetRows = []; } renderBudgets(); }
function renderBudgets(){
  const el = document.getElementById('budgetList'); if(!el) return;
  if(!Array.isArray(budgetRows) || !budgetRows.length){ el.innerHTML = '<div class="empty-state" style="padding:12px;">No budgets set.</div>'; return; }
  const peso = v => '₱'+Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2});
  el.innerHTML = '<table class="panel-table"><thead><tr><th>Category</th><th>Fund</th><th>Amount</th><th></th></tr></thead><tbody>' +
    budgetRows.map(b=>`<tr>
      <td>${escHtml(b.category)}</td><td>${escHtml(b.fund)}</td><td>${peso(b.amount)}</td>
      <td style="text-align:right;"><button class="btn btn-sm btn-danger" onclick="deleteBudget(${b.id})">Delete</button></td>
    </tr>`).join('') + '</tbody></table>';
}
async function saveBudget(){
  const category = document.getElementById('budgetCategory').value.trim();
  const fund = document.getElementById('budgetFund').value;
  const amount = document.getElementById('budgetAmount').value;
  if(!category || amount===''){ showToast('⚠ Enter a category and amount.'); return; }
  const res = await apiPost('/api/budgets',{category,fund,amount});
  if(res.success){ ['budgetCategory','budgetAmount'].forEach(id=>document.getElementById(id).value=''); await loadBudgets(); showToast('✅ Budget saved.'); }
  else showToast('⚠ '+(res.message||'Could not save budget.'));
}
async function deleteBudget(id){
  const res = await apiDelete('/api/budgets/'+id);
  if(res.success){ await loadBudgets(); showToast('✅ Deleted.'); } else showToast('⚠ Failed.');
}
function financeTab(name, el){
  document.querySelectorAll('#finTabs .fin-tab').forEach(t=>t.classList.remove('active'));
  if(el) el.classList.add('active');
  else document.querySelectorAll('#finTabs .fin-tab').forEach(t=>{ if(t.textContent.trim().toLowerCase().startsWith(name)) t.classList.add('active'); });
  document.querySelectorAll('#panel-gcash .fin-section').forEach(s=>s.classList.toggle('active', s.dataset.fin===name));
  if(name==='overview') loadFinanceOverview();
}
async function loadFinanceOverview(){
  const el = document.getElementById('financeOverview'); if(!el) return;
  let s = {collected:0,spent:0,net:0}, aging = [];
  try{ s = await apiGet('/api/transparency/summary'); }catch(e){}
  try{ aging = await apiGet('/api/ledger/aging'); }catch(e){}
  const peso = v => '₱'+Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2});
  const outstanding = (aging||[]).reduce((a,r)=>a+Math.max(0,Number(r.balance)||0),0);
  const withBalance = (aging||[]).filter(r=>Number(r.balance)>0.005).length;
  const cell = (label,val,color)=>`<div class="fs-cell"><div class="fs-label">${label}</div><div class="fs-val" style="color:${color||'var(--text)'};">${peso(val)}</div></div>`;
  el.innerHTML =
    cell('Collected (YTD)', s.collected, 'var(--green,#6B8E70)') +
    cell('Expenses (YTD)', s.spent, '#c87941') +
    cell('Net', s.net, (Number(s.net) < 0 ? '#c87941' : 'var(--text)')) +
    cell('Outstanding dues', outstanding) +
    `<div class="fs-cell"><div class="fs-label">Accounts with a balance</div><div class="fs-val">${withBalance}</div></div>`;
}

/* ══════════════ HOA POLICIES (admin CRUD) ══════════════ */
let hoaPolicyList = [];
async function loadAdminPolicies(){
  try{ hoaPolicyList = await apiGet('/api/hoa-policies'); }catch(e){ hoaPolicyList = []; }
  renderAdminPolicies();
}
function renderAdminPolicies(){
  const el = document.getElementById('policyAdminList');
  if(!el) return;
  if(!hoaPolicyList.length){ el.innerHTML = '<div class="empty-state" style="padding:12px;">No policies yet. Click “Add Policy”.</div>'; return; }
  el.innerHTML = '<table class="panel-table"><thead><tr><th>Title</th><th>Category</th><th>Effective</th><th>Status</th><th></th></tr></thead><tbody>' +
    hoaPolicyList.map(p=>`<tr>
      <td>${escHtml(p.title)}</td>
      <td>${escHtml(p.category)}</td>
      <td>${escHtml(p.effective_date||'—')}</td>
      <td>${escHtml(p.status||'Active')}</td>
      <td style="text-align:right;white-space:nowrap;">
        <button class="btn btn-sm" onclick="openPolicyModal(${p.id})">Edit</button>
        <button class="btn btn-sm" onclick="deletePolicy(${p.id})">Archive</button>
      </td>
    </tr>`).join('') + '</tbody></table>';
}
function openPolicyModal(id){
  const p = id ? hoaPolicyList.find(x=>x.id===id) : null;
  document.getElementById('policyModalTitle').textContent = p ? 'Edit Policy' : 'Add Policy';
  document.getElementById('policyId').value = p ? p.id : '';
  document.getElementById('policyTitle').value = p ? p.title : '';
  document.getElementById('policyCategory').value = p ? p.category : '';
  document.getElementById('policyEffective').value = p ? (p.effective_date||'') : '';
  document.getElementById('policyStatus').value = p ? (p.status||'Active') : 'Active';
  document.getElementById('policyDescription').value = p ? p.description : '';
  document.getElementById('policyMsg').textContent = '';
  openModal('policy');
}
async function savePolicy(){
  const id = document.getElementById('policyId').value;
  const payload = {
    title: document.getElementById('policyTitle').value.trim(),
    category: document.getElementById('policyCategory').value.trim(),
    effective_date: document.getElementById('policyEffective').value,
    status: document.getElementById('policyStatus').value,
    description: document.getElementById('policyDescription').value.trim(),
  };
  const msg = document.getElementById('policyMsg');
  if(!payload.title || !payload.category || !payload.effective_date || !payload.description){ msg.textContent = 'Please fill in all fields.'; return; }
  if(id){
    const ok = await SubUI.confirm({ title:'Save changes to this policy?', message:'This updates the policy residents see. Review the details before saving.', confirmText:'Save Changes', icon:'?' });
    if(!ok) return;
  }
  const res = id ? await apiPut('/api/hoa-policies/'+id, payload) : await apiPost('/api/hoa-policies', payload);
  if(res && res.success){ closeModal('policy'); await loadAdminPolicies(); showToast('✅ Policy saved.'); }
  else msg.textContent = 'Could not save policy.';
}
async function deletePolicy(id){
  if(!(await SubUI.confirm({ title:'Archive policy?', message:'This policy will be archived (kept for reference) and hidden from residents.', confirmText:'Archive', danger:true, icon:'!' }))) return;
  const res = await apiDelete('/api/hoa-policies/'+id);
  if(res && res.success){ await loadAdminPolicies(); showToast('✅ Policy archived.'); }
  else showToast('⚠ Could not archive policy.');
}

function initMap() {
  loadLeaflet().then(async ()=>{
  if (mapInst) { setTimeout(() => mapInst.invalidateSize(), 100); return; }
  await fetchMapSettings();
  const b = mapSettings;
  const areaBounds = L.latLngBounds([[b.min_lat,b.min_lng],[b.max_lat,b.max_lng]]);
  mapInst = L.map('map-container', {
    minZoom: 14,
    maxZoom: 20,
    maxBounds: areaBounds,
    maxBoundsViscosity: 1.0,
  }).fitBounds(areaBounds, {padding:[20,20]});
  areaRect = L.rectangle(areaBounds,{color:'#A07D53',weight:2,dashArray:'6 4',fillColor:'#A07D53',fillOpacity:0.06}).addTo(mapInst);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 20,
  }).addTo(mapInst);

  layerHouseholds = L.layerGroup().addTo(mapInst);
  layerFacilities = L.layerGroup().addTo(mapInst);
  layerIssues     = L.layerGroup().addTo(mapInst);

  loadMapData();

  mapInst.on('click', function(e) {
    if (!addFacilityMode) return;
    const { lat, lng } = e.latlng;
    if (pendingPin) pendingPin.remove();
    pendingPin = L.marker([lat, lng]).addTo(mapInst);
    document.getElementById('fac-lat').value = lat.toFixed(7);
    document.getElementById('fac-lng').value = lng.toFixed(7);
    openModal('addFacility');
  });

  document.getElementById('layer-households').addEventListener('change', function() {
    this.checked ? mapInst.addLayer(layerHouseholds) : mapInst.removeLayer(layerHouseholds);
  });
  document.getElementById('layer-facilities').addEventListener('change', function() {
    this.checked ? mapInst.addLayer(layerFacilities) : mapInst.removeLayer(layerFacilities);
  });
  document.getElementById('layer-issues').addEventListener('change', function() {
    this.checked ? mapInst.addLayer(layerIssues) : mapInst.removeLayer(layerIssues);
  });
  });
}

function loadMapData() {
  fetch('/api/map/households').then(r => r.json()).then(data => {
    layerHouseholds.clearLayers();
    data.forEach(h => {
      const color = h.status === 'Vacant' ? '#f08080' : '#4287f5';
      const marker = L.marker([parseFloat(h.latitude), parseFloat(h.longitude)], {
        icon: makeCircleIcon(color),
        draggable: true,
        title: 'Drag to move ' + h.block_lot_number
      })
        .bindPopup(`<strong>${h.block_lot_number}</strong><br>Status: ${h.status}<br><small style="color:#888;">Drag to reposition</small>`)
        .addTo(layerHouseholds);
      marker.on('dragend', async function(ev) {
        const { lat, lng } = ev.target.getLatLng();
        const res = await apiPut('/api/map/household/' + h.id + '/location', { latitude: lat, longitude: lng });
        if (res && res.success) {
          showToast('📍 ' + h.block_lot_number + ' location updated.');
        } else {
          showToast('⚠ Failed to save location.');
          ev.target.setLatLng([parseFloat(h.latitude), parseFloat(h.longitude)]); // revert
        }
      });
    });
  });

  fetch('/api/map/facilities').then(r => r.json()).then(data => {
    layerFacilities.clearLayers();
    data.forEach(f => {
      L.marker([parseFloat(f.latitude), parseFloat(f.longitude)], {icon: makeCircleIcon('#4CAF50')})
        .bindPopup(
          (f.image_url ? `<img src="${f.image_url}" alt="${f.name}" style="max-width:210px;width:100%;border-radius:6px;margin-bottom:6px;display:block;">` : '') +
          `<strong>${f.name}</strong>${f.description ? '<br><small>' + f.description + '</small>' : ''}<br>` +
          `<small style="color:#888;">${f.status}</small><br>` +
          `<div style="margin-top:5px;display:flex;gap:4px;">` +
          `<button onclick="editFacility(${f.id})" style="padding:2px 7px;font-size:11px;border:1px solid #ccc;border-radius:3px;cursor:pointer;">Edit</button>` +
          `<button onclick="deleteFacilityById(${f.id})" style="padding:2px 7px;font-size:11px;border:1px solid #f88;color:#c00;border-radius:3px;cursor:pointer;">Delete</button>` +
          `</div>`
        ).addTo(layerFacilities);
    });
  });

  fetch('/api/map/issues').then(r => r.json()).then(data => {
    layerIssues.clearLayers();
    issueMarkerCount = 0;
    data.forEach((i, idx) => {
      const color = i.status === 'Resolved' ? '#888' : (i.status === 'In Progress' ? '#f5a623' : '#e05555');
      issueMarkerCount++;
      L.marker([parseFloat(i.latitude), parseFloat(i.longitude)], {icon: makeNumberedIcon(color, idx + 1)})
        .bindPopup(`<strong>Report #${idx + 1} — ${i.title}</strong><br>${i.category}<br><small>Status: ${i.status}</small>`)
        .addTo(layerIssues);
    });
  });
}

function enterAddFacilityMode() {
  if (!mapInst) { showToast('Map not loaded yet.'); return; }
  addFacilityMode = true;
  document.getElementById('map-add-banner').style.display = 'flex';
  mapInst.getContainer().style.cursor = 'crosshair';
}

function cancelAddFacilityMode() {
  addFacilityMode = false;
  const banner = document.getElementById('map-add-banner');
  if (banner) banner.style.display = 'none';
  if (mapInst) mapInst.getContainer().style.cursor = '';
  if (pendingPin) { pendingPin.remove(); pendingPin = null; }
}

function previewImage(input, previewId){
  const img = document.getElementById(previewId);
  if(!img) return;
  if(input.files && input.files[0]){ img.src = URL.createObjectURL(input.files[0]); img.style.display = 'block'; }
  else { img.style.display = 'none'; img.removeAttribute('src'); }
}

function saveNewFacility() {
  const name = document.getElementById('fac-name').value.trim();
  const lat  = document.getElementById('fac-lat').value;
  const lng  = document.getElementById('fac-lng').value;
  if (!name) { showToast('Please enter a facility name.'); return; }
  if (!lat || !lng) { showToast('Click the map to pin a location first.'); return; }

  const fd = new FormData();
  fd.append('name', name);
  fd.append('description', document.getElementById('fac-desc').value.trim());
  fd.append('latitude', lat);
  fd.append('longitude', lng);
  const file = document.getElementById('fac-image').files[0];
  if (file) fd.append('image', file);

  fetch('/api/map/facility', {
    method: 'POST',
    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json'},
    body: fd,
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) {
      cancelAddFacilityMode();
      closeModal('addFacility');
      document.getElementById('fac-name').value = '';
      document.getElementById('fac-desc').value = '';
      document.getElementById('fac-image').value = '';
      previewImage(document.getElementById('fac-image'), 'fac-image-preview');
      loadMapData();
      showToast('Facility pinned!');
    } else {
      showToast('⚠ ' + (d.message || 'Could not save facility.'));
    }
  });
}

function editFacility(id) {
  editFacilityId = id;
  fetch('/api/map/facilities').then(r => r.json()).then(data => {
    const f = data.find(x => x.id === id);
    if (!f) return;
    document.getElementById('edit-fac-name').value   = f.name;
    document.getElementById('edit-fac-desc').value   = f.description || '';
    document.getElementById('edit-fac-status').value = f.status;
    const prev = document.getElementById('edit-fac-preview');
    if (f.image_url) { prev.src = f.image_url; prev.style.display = 'block'; }
    else { prev.style.display = 'none'; prev.removeAttribute('src'); }
    document.getElementById('edit-fac-image').value = '';
    document.getElementById('edit-fac-remove-image').checked = false;
    openModal('editFacility');
  });
}

function saveEditFacility() {
  const fd = new FormData();
  fd.append('_method', 'PUT');
  fd.append('name', document.getElementById('edit-fac-name').value.trim());
  fd.append('description', document.getElementById('edit-fac-desc').value.trim());
  fd.append('status', document.getElementById('edit-fac-status').value);
  const file = document.getElementById('edit-fac-image').files[0];
  if (file) fd.append('image', file);
  if (document.getElementById('edit-fac-remove-image').checked) fd.append('remove_image', '1');

  fetch(`/api/map/facility/${editFacilityId}`, {
    method: 'POST',
    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json'},
    body: fd,
  })
  .then(r => r.json())
  .then(d => { if (d.success) { closeModal('editFacility'); loadMapData(); showToast('Facility updated.'); } else showToast('⚠ ' + (d.message || 'Could not update facility.')); });
}

async function deleteFacilityById(id) {
  if (!(await SubUI.confirm({ title:'Delete facility?', message:'Delete this map facility? This cannot be undone.', confirmText:'Delete', danger:true, icon:'!' }))) return;
  fetch(`/api/map/facility/${id}`, {
    method: 'DELETE',
    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
  })
  .then(r => r.json())
  .then(d => { if (d.success) { loadMapData(); showToast('Facility removed.'); } });
}
</script>

<!-- Resident Detail Modal -->
<div class="modal-overlay" id="modal-residentDetail" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:440px;">
    <div class="modal-title" style="display:flex;align-items:center;gap:14px;">
      <div id="resD-avatar" class="r-avatar" style="width:50px;height:50px;font-size:18px;flex-shrink:0;"></div>
      <div>
        <div id="resD-name" style="font-size:16px;font-weight:600;">—</div>
        <div id="resD-block" style="font-size:12px;color:var(--text-dim);margin-top:2px;">—</div>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
      <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:12px;">
        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:4px;">Status</div>
        <span id="resD-status" class="pill">—</span>
      </div>
      <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:12px;">
        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:4px;">Household</div>
        <div id="resD-household" style="font-size:13px;font-weight:600;">—</div>
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
      <div style="display:flex;gap:10px;align-items:center;font-size:13px;">
        <span style="color:var(--text-dim);min-width:60px;">📧 Email</span>
        <span id="resD-email">—</span>
      </div>
      <div style="display:flex;gap:10px;align-items:center;font-size:13px;">
        <span style="color:var(--text-dim);min-width:60px;">📞 Phone</span>
        <span id="resD-phone">—</span>
      </div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="document.getElementById('modal-residentDetail').classList.remove('open')">Close</button>
      <button class="btn btn-sm" id="resD-msgBtn">✉️ Message Resident</button>
    </div>
  </div>
</div>

<!-- Issue Detail Modal -->
<div class="modal-overlay" id="modal-issueDetail" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box wide" style="max-width:560px;">
    <div class="modal-title">
      <div id="issD-title" style="font-size:15px;font-weight:600;margin-bottom:6px;">—</div>
      <div id="issD-status" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;"></div>
    </div>
    <div style="font-size:12px;color:var(--text-dim);margin-bottom:2px;" id="issD-who">—</div>
    <div style="font-size:11px;color:var(--text-dim);margin-bottom:14px;" id="issD-cat">—</div>
    <!-- Priority setter (admin/officer only) -->
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;padding:10px 12px;background:rgba(255,255,255,0.04);border-radius:10px;">
      <span style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);flex-shrink:0;">Set Priority</span>
      <select id="issD-prioritySel" style="flex:1;font-size:12px;">
        <option value="">— Not set —</option>
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
        <option value="Critical">Critical</option>
      </select>
      <button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="saveIssuePriority()">Save</button>
    </div>
    <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:14px;margin-bottom:14px;">
      <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:6px;">Description</div>
      <div id="issD-desc" style="font-size:13px;line-height:1.6;white-space:pre-wrap;"></div>
    </div>
    <!-- Photo gallery -->
    <div id="issD-media" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;"></div>
    <div id="issD-location" style="margin-bottom:12px;"></div>
    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:8px;">Responses (<span id="issD-responseCount"></span>)</div>
    <div id="issD-responses" style="max-height:220px;overflow-y:auto;"></div>
    <div class="modal-actions" style="margin-top:16px;">
      <button class="modal-close-btn" onclick="document.getElementById('modal-issueDetail').classList.remove('open')">Close</button>
      <button class="btn btn-sm" id="issD-respondBtn">Respond</button>
    </div>
  </div>
</div>

<!-- Recommendation Detail Modal -->
<div class="modal-overlay" id="modal-recDetail" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-title">Recommendation Details</div>
    <div style="font-size:12px;color:var(--text-dim);margin-bottom:2px;" id="recD-who">—</div>
    <div style="font-size:11px;color:var(--text-dim);margin-bottom:14px;" id="recD-cat">—</div>
    <div style="font-size:15px;font-weight:600;margin-bottom:10px;" id="recD-title">—</div>
    <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:14px;margin-bottom:16px;">
      <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:6px;">Description</div>
      <div id="recD-desc" style="font-size:13px;line-height:1.6;white-space:pre-wrap;"></div>
    </div>
    <div class="f-row"><div class="f-field"><span class="f-label">Update Status</span>
      <select id="recD-statusSel">
        <option value="Pending">Pending</option>
        <option value="Reviewed">Reviewed</option>
        <option value="Approved">Approved</option>
        <option value="Rejected">Rejected</option>
      </select>
    </div></div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="document.getElementById('modal-recDetail').classList.remove('open')">Close</button>
      <button class="btn btn-sm" id="recD-updateBtn">Update Status</button>
    </div>
  </div>
</div>

<!-- Announcement View Modal -->
<div class="modal-overlay" id="modal-annView" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:500px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
      <span id="annV-tag" class="pill">Notice</span>
      <span id="annV-priority" style="display:none;font-size:11px;font-weight:700;color:#f08080;">⚡ High Priority</span>
    </div>
    <div id="annV-title" style="font-size:17px;font-weight:700;margin-bottom:10px;line-height:1.4;">—</div>
    <div style="font-size:11px;color:var(--text-dim);margin-bottom:4px;" id="annV-by">—</div>
    <div style="font-size:11px;color:var(--text-dim);margin-bottom:16px;" id="annV-meta">—</div>
    <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:16px;margin-bottom:16px;">
      <div id="annV-content" style="font-size:14px;line-height:1.7;white-space:pre-wrap;color:var(--text-mid);"></div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" id="annV-delBtn" style="color:#f08080;border-color:rgba(240,128,128,0.3);">🗑 Delete</button>
      <button class="btn btn-sm" id="annV-archBtn" style="background:var(--yellow-dim);border-color:rgba(196,150,70,0.3);color:var(--yellow);" onclick="toggleAnnArchive()">📦 Archive</button>
      <button class="btn btn-sm" onclick="document.getElementById('modal-annView').classList.remove('open')">Close</button>
    </div>
  </div>
</div>

<!-- Member Detail / Edit Modal -->
<div class="modal-overlay" id="modal-memberDetail" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:420px;">
    <div class="modal-title" style="display:flex;align-items:center;gap:12px;">
      <div id="memDetailInitials" style="width:42px;height:42px;border-radius:50%;background:var(--glass-hover);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;flex-shrink:0;">?</div>
      <div>
        Member Details
        <div id="memDetailBlock" style="font-size:12px;color:var(--text-dim);font-weight:400;margin-top:2px;">—</div>
      </div>
    </div>
    <div class="f-row"><div class="f-field"><span class="f-label">Full Name</span>
      <input type="text" id="memDetailName" placeholder="Full name">
    </div></div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Relationship</span>
        <select id="memDetailRel">
          <option value="">— Select —</option>
          <option>Owner</option><option>Spouse</option><option>Child</option><option>Parent</option><option>Tenant</option><option>Other</option>
        </select>
      </div>
      <div class="f-field"><span class="f-label">Contact Number</span>
        <input type="text" id="memDetailPhone" placeholder="e.g. 09xx-xxx-xxxx">
      </div>
    </div>
    <div style="font-size:12px;color:#f08080;margin-top:-4px;margin-bottom:8px;" id="memDetailMsg"></div>
    <div class="modal-actions" style="justify-content:space-between;">
      <button class="modal-close-btn" onclick="deleteMember()" style="color:#f08080;border-color:rgba(240,128,128,0.3);">🗑 Delete</button>
      <div style="display:flex;gap:8px;">
        <button class="modal-close-btn" onclick="document.getElementById('modal-memberDetail').classList.remove('open')">Cancel</button>
        <button class="btn btn-sm" onclick="saveMemberDetail()">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Member Modal -->
<div class="modal-overlay" id="modal-addMember" onclick="if(event.target===this)closeModal('addMember')">
  <div class="modal-box" style="max-width:440px;">
    <div class="modal-title">Add Household Member</div>
    <div class="f-row"><div class="f-field"><span class="f-label">Household (Block/Lot)</span>
      <select id="addMemHouse">
        <option value="">— Select household —</option>
      </select>
    </div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Full Name</span>
      <input type="text" id="addMemName" placeholder="Member's full name">
    </div></div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Relationship to Owner</span>
        <select id="addMemRel">
          <option value="">— Select —</option>
          <option>Owner</option><option>Spouse</option><option>Child</option><option>Parent</option><option>Tenant</option><option>Other</option>
        </select>
      </div>
      <div class="f-field"><span class="f-label">Contact Number</span>
        <input type="text" id="addMemPhone" placeholder="Optional">
      </div>
    </div>
    <div style="font-size:12px;color:#f08080;margin-top:-4px;margin-bottom:8px;" id="addMemMsg"></div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('addMember')">Cancel</button>
      <button class="btn btn-sm" onclick="addMemberSubmit()">Add Member</button>
    </div>
  </div>
</div>

</body>
</html>