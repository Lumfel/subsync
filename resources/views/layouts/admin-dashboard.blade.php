<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SubSync — Admin Dashboard</title>
<script>
  (function(){
    try {
      var saved = localStorage.getItem('subsync.theme');
      if (saved === 'dark' || saved === 'light') {
        document.documentElement.setAttribute('data-theme', saved);
      }
    } catch(e){}
  })();
</script>
<script>window.APP_BASE = '{{ request()->getBaseUrl() }}';</script>
<script src="{{ asset('js/portal-context.js') . '?v=' . filemtime(public_path('js/portal-context.js')) }}"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') . '?v=' . filemtime(public_path('css/admin-dashboard.css')) }}">
<link rel="preload" as="image" href="{{ asset('images/admin_hero_bg_day.jpg') }}">
<link rel="preload" as="image" href="{{ asset('images/admin_hero_bg_clean.jpg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/ui-polish.css') . '?v=' . filemtime(public_path('css/ui-polish.css')) }}">
<meta name="theme-color" content="#FA7D09">
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
    <button class="qa-btn sidebar-search-btn" onclick="SubUI.openSearch()" title="Search (Ctrl+K)">
      <span class="qa-icon"><x-icon name="search" size="15"/></span> Search… <kbd class="sidebar-search-kbd">Ctrl K</kbd>
    </button>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Overview</div>
    <div class="nav-item active" onclick="nav('dashboard',this)"><span class="nav-icon"><x-icon name="home" size="17"/></span> Dashboard</div>
    <div class="nav-item" onclick="nav('analytics',this)"><span class="nav-icon"><x-icon name="chart-bar" size="17"/></span> Analytics</div>

    <div class="nav-section-label">Community</div>
    <div class="nav-item" onclick="nav('residents',this)"><span class="nav-icon"><x-icon name="users" size="17"/></span> Residents <span class="nav-badge" id="pendingBadge" style="display:none;background:#f59e0b;">0</span></div>
    <div class="nav-item" onclick="nav('members',this)"><span class="nav-icon"><x-icon name="user" size="17"/></span> Household Members</div>
    <div class="nav-item" onclick="nav('officers',this)"><span class="nav-icon"><x-icon name="shield" size="17"/></span> Officers</div>

    <div class="nav-section-label">Communication</div>
    <div class="nav-item" onclick="nav('announcements',this)"><span class="nav-icon"><x-icon name="megaphone" size="17"/></span> Announcements</div>
    <div class="nav-item" onclick="nav('messages',this)"><span class="nav-icon"><x-icon name="message" size="17"/></span> Messages <span class="nav-badge" id="msgBadge">5</span></div>

    <div class="nav-section-label">Operations</div>
    <div class="nav-item" onclick="nav('issues',this)"><span class="nav-icon"><x-icon name="alert" size="17"/></span> Issues <span class="nav-badge" id="issueBadge" style="background:#F58B1F;color:#fff;">3</span></div>
    <div class="nav-item" onclick="nav('recommendations',this)"><span class="nav-icon"><x-icon name="lightbulb" size="17"/></span> Recommendations</div>

    <div class="nav-section-label">Location</div>
    <div class="nav-item" onclick="nav('mapping',this)"><span class="nav-icon"><x-icon name="map" size="17"/></span> Subdivision Map</div>

    <div class="nav-section-label">Administration</div>
    <div class="nav-item" id="navSettings" onclick="window.location='{{ url('settings') }}?portal={{ $role ?? 'admin' }}'"><span class="nav-icon"><x-icon name="gear" size="17"/></span> System Settings</div>
    <div class="nav-item" onclick="nav('archives',this)"><span class="nav-icon"><x-icon name="archive" size="17"/></span> Audit Logs</div>
    <div class="nav-item" onclick="nav('reports',this)"><span class="nav-icon"><x-icon name="folder" size="17"/></span> Documents <span class="nav-badge" id="reportsBadge" style="display:none;">0</span></div>
    <div class="nav-item" onclick="nav('policies',this)"><span class="nav-icon"><x-icon name="scroll" size="17"/></span> Policies</div>
    <div class="nav-item" onclick="nav('gcash',this)"><span class="nav-icon"><x-icon name="credit-card" size="17"/></span> Finance</div>
    <div class="nav-item" onclick="nav('manageusers',this)"><span class="nav-icon"><x-icon name="users" size="17"/></span> Households &amp; Users</div>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-profile admin-profile" onclick="toggleUserMenu()" title="{{ $adminName }} (Click for Account Settings)">
      <div class="admin-avatar" style="width:38px;height:38px;border-radius:50%;background:#c87941;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;">{{ strtoupper(substr($adminName, 0, 2)) }}</div>
      <div style="flex:1;min-width:0;margin-left:10px;text-align:left;">
        <div class="admin-name" style="font-weight:600;font-size:13px;color:var(--text);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $adminName }}</div>
        <div class="admin-role" style="font-size:11.5px;color:var(--text-dim);margin-top:2px;">Subdivision Admin</div>
      </div>
      <span class="admin-chevron" style="font-size:10px;color:var(--text-dim);margin-left:auto;">▼</span>
    </div>
  </div>
</aside>

<!-- ═══ MAIN ═══ -->
<main class="main">
  <!-- Emergency alert banner (shown on critical issues) -->
  <div id="emergencyAlert">
    <x-icon name="alert" size="16"/> <span id="emergencyAlertText">CRITICAL ISSUE REPORTED</span>
    &nbsp;&nbsp;<button onclick="nav('issues');dismissEmergency()" style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.4);color:#fff;padding:3px 12px;border-radius:6px;font-size:12px;cursor:pointer;font-weight:600;">View Issue</button>
    &nbsp;<button aria-label="Dismiss" onclick="dismissEmergency()" style="background:transparent;border:none;color:rgba(255,255,255,0.7);font-size:16px;cursor:pointer;line-height:1;">✕</button>
  </div>
  <!-- Floating announcement notification banner -->
  <div id="annNotif">
    <span style="font-size:18px;"><x-icon name="megaphone" size="18"/></span>
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
      <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()"><x-icon name="menu" size="18"/></button>
      <div>
        <div class="topbar-title" id="topbarTitle">Dashboard Overview</div>
        <div class="topbar-sub" id="topbarSubtitle" style="font-size:11.5px;color:#8C857B;margin-top:2px;">Manage the subdivision's residents, announcements, concerns, and recommendations.</div>
      </div>
    </div>
    <div class="topbar-actions">
      <!-- Search input matching mockup -->
      <div class="topbar-search-box" onclick="SubUI.openSearch()" title="Search residents, issues, or announcements (Ctrl+K)">
        <span style="color:#8C857B;display:flex;align-items:center;"><x-icon name="search" size="14"/></span>
        <span style="color:#7D766D;font-size:12.5px;">Search residents, issues, or announcements…</span>
      </div>

      <!-- Theme Toggle (Light / Dark Mode) -->
      <button class="topbar-btn keep-mobile" id="themeToggleBtn" onclick="toggleTheme()" title="Toggle Light / Dark mode" aria-label="Toggle theme">
        <span id="themeToggleIcon"><x-icon name="moon" size="15"/></span>
      </button>

      <div class="alerts-wrap">
        <button class="topbar-btn keep-mobile" id="alertsButton" aria-expanded="false" aria-controls="alertsPopover" onclick="toggleAlerts()"><x-icon name="bell" size="15"/> <span class="alerts-badge" id="alertsBadge">3</span></button>
        <section class="alerts-popover" id="alertsPopover" aria-label="System alerts">
          <div class="alerts-popover-head"><strong>System alerts</strong><button class="alerts-refresh" onclick="loadAlerts(true)">Refresh</button></div>
          <div id="alertsList"><div class="alerts-empty">Loading alerts…</div></div>
        </section>
      </div>
      <button class="topbar-btn primary" onclick="openModal('ann')">+ New Announcement</button>
      <form id="logoutForm" action="{{ url('logout') }}" method="POST" style="display:none;">@csrf<input type="hidden" name="portal" value="{{ $role ?? 'admin' }}"></form>
      <div class="user-menu-wrap" style="position:relative;">
        <button class="topbar-avatar keep-mobile" id="userMenuButton" aria-expanded="false" aria-controls="userMenuPopover" onclick="toggleUserMenu()" title="{{ $adminName }} (Account menu)">
          {{ strtoupper(substr($adminName, 0, 2)) }}
        </button>
        <div class="user-menu-popover" id="userMenuPopover" role="menu" aria-label="Account menu">
          <!-- Header: User info -->
          <div class="user-menu-header">
            <div class="user-menu-avatar">{{ strtoupper(substr($adminName, 0, 2)) }}</div>
            <div class="user-menu-info">
              <div class="user-menu-name">{{ $adminName }}</div>
              <div class="user-menu-role">Subdivision Admin</div>
              <div class="user-menu-status"><span class="user-status-dot"></span> Online</div>
            </div>
          </div>
          <div class="user-menu-divider"></div>
          <!-- Menu items -->
          <div class="user-menu-items">
            <a href="{{ url('settings') }}?portal={{ $role ?? 'admin' }}" class="user-menu-item active" role="menuitem">
              <span class="user-menu-ic"><x-icon name="gear" size="16"/></span>
              <div class="user-menu-text">
                <div class="user-menu-title">Account Settings</div>
                <div class="user-menu-desc">Profile, account, and preferences</div>
              </div>
            </a>
            <a href="{{ url('settings') }}?portal={{ $role ?? 'admin' }}#notifications" class="user-menu-item" role="menuitem">
              <span class="user-menu-ic"><x-icon name="bell" size="16"/></span>
              <div class="user-menu-text">
                <div class="user-menu-title">Notification Preferences</div>
              </div>
            </a>
          </div>
          <div class="user-menu-divider"></div>
          <div class="user-menu-footer">
            <button class="user-menu-item logout" onclick="confirmLogout()" role="menuitem">
              <span class="user-menu-ic"><x-icon name="logout" size="16"/></span>
              <div class="user-menu-text">
                <div class="user-menu-title">Log Out</div>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="content">

    <!-- ══════════ DASHBOARD ══════════ -->
    <div class="panel active" id="panel-dashboard">
      <!-- Daily Action Center (displays when registrations, payments, or critical issues need triage) -->
      <div id="adminActionCenter" style="display:none;background:linear-gradient(135deg,rgba(250,125,9,0.08),rgba(245,158,11,0.08));border:1px solid rgba(250,125,9,0.3);border-radius:12px;padding:14px 18px;margin-bottom:16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:13px;letter-spacing:.04em;text-transform:uppercase;color:var(--accent);">
            <x-icon name="bell" size="16"/> Action Center · Items Requiring Attention
          </div>
          <span style="font-size:11px;color:var(--text-dim);" id="actionCenterSummary">—</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:10px;" id="actionCenterItems">
          <!-- Pending Registrations Item -->
          <div id="actionItemRegistrations" style="display:none;background:#fff;border:1px solid rgba(0,0,0,0.06);border-radius:8px;padding:10px 14px;align-items:center;justify-content:space-between;gap:8px;">
            <div style="display:flex;align-items:center;gap:10px;">
              <span style="color:#f59e0b;"><x-icon name="users" size="18"/></span>
              <div>
                <div style="font-weight:600;font-size:13px;"><span id="actionPendingResCount">0</span> Registration(s)</div>
                <div style="font-size:11px;color:var(--text-dim);">Awaiting account approval</div>
              </div>
            </div>
            <button class="btn btn-sm" onclick="nav('residents',null)" style="background:#f59e0b;color:#1a100a;border:none;">Review</button>
          </div>
          <!-- Pending GCash Payments Item -->
          <div id="actionItemPayments" style="display:none;background:#fff;border:1px solid rgba(0,0,0,0.06);border-radius:8px;padding:10px 14px;align-items:center;justify-content:space-between;gap:8px;">
            <div style="display:flex;align-items:center;gap:10px;">
              <span style="color:var(--green);"><x-icon name="coins" size="18"/></span>
              <div>
                <div style="font-weight:600;font-size:13px;"><span id="actionPendingPayCount">0</span> Payment(s)</div>
                <div style="font-size:11px;color:var(--text-dim);">In GCash validation queue</div>
              </div>
            </div>
            <button class="btn btn-sm" onclick="nav('gcash',null);setTimeout(()=>{financeTab('settings');},150);" style="background:var(--green);color:#fff;border:none;">Validate</button>
          </div>
          <!-- Critical / Overdue Issues Item -->
          <div id="actionItemIssues" style="display:none;background:#fff;border:1px solid rgba(0,0,0,0.06);border-radius:8px;padding:10px 14px;align-items:center;justify-content:space-between;gap:8px;">
            <div style="display:flex;align-items:center;gap:10px;">
              <span style="color:#ff5555;"><x-icon name="alert" size="18"/></span>
              <div>
                <div style="font-weight:600;font-size:13px;"><span id="actionUrgentIssueCount">0</span> Urgent/Overdue</div>
                <div style="font-size:11px;color:var(--text-dim);">Issues needing assignment</div>
              </div>
            </div>
            <button class="btn btn-sm btn-danger" onclick="nav('issues',null)" style="border:none;">Triage</button>
          </div>
        </div>
      </div>
      <div id="pendingNotice" style="display:none;"><span id="pendingNoticeCount">0</span></div>

      <!-- Hidden hook for JS recent issue list to maintain compatibility -->
      <div id="dashIssueList" style="display:none;"></div>

      <div class="dash-overview">
        <!-- 1. HERO BANNER -->
        <div class="dash-hero-card">
          <div class="dash-hero-content">
            <div class="dash-hero-indicator"></div>
            <div class="dash-hero-body">
              <h1 class="dash-hero-title">
                <span class="dash-hero-greeting-light">Hello, {{ $adminName ?? 'System Admin' }} 👋</span>
                <span class="dash-hero-greeting-dark">Welcome back, {{ $adminName ?? 'System Admin' }} 👋</span>
              </h1>
              <p class="dash-hero-subtitle">Here's what's happening in Terra Nova today.</p>
              <div class="dash-hero-pills">
                <span class="dash-pill"><x-icon name="calendar" size="14"/> {{ date('M d, Y (D)') }}</span>
                <span class="dash-pill" id="heroWeatherPill" title="Live Subdivision Weather"><x-icon name="sun" size="14"/><span id="heroWeatherText">29°C Partly Cloudy</span></span>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. 6 KPI STAT CARDS ROW -->
        <div class="dash-kpi-grid">
          <!-- Total Residents -->
          <div class="dash-kpi-card" onclick="nav('residents',null)" title="View all residents">
            <div class="dash-kpi-icon orange"><x-icon name="users" size="18"/></div>
            <div class="dash-kpi-label">Total Residents</div>
            <div class="dash-kpi-val" id="statResidents">—</div>
            <div class="dash-kpi-sub green">↑ +4 this month</div>
          </div>

          <!-- Verified Residents -->
          <div class="dash-kpi-card" onclick="nav('residents',null)" title="View verified residents">
            <div class="dash-kpi-icon green"><x-icon name="shield" size="18"/></div>
            <div class="dash-kpi-label">Verified Residents</div>
            <div class="dash-kpi-val" id="statActive">—</div>
            <div class="dash-kpi-sub muted">✓ <span id="statVerifiedPct">95.5%</span> verified</div>
          </div>

          <!-- Pending Approvals -->
          <div class="dash-kpi-card" onclick="nav('residents',null)" title="Review pending registrations">
            <div class="dash-kpi-icon amber"><x-icon name="clock" size="18"/></div>
            <div class="dash-kpi-label">Pending Approvals</div>
            <div class="dash-kpi-val" id="statPending">0</div>
            <div class="dash-kpi-sub muted">⏱ Require review</div>
          </div>

          <!-- Open Issues -->
          <div class="dash-kpi-card" onclick="nav('issues',null)" title="View open issues">
            <div class="dash-kpi-icon red"><x-icon name="alert" size="18"/></div>
            <div class="dash-kpi-label">Open Issues</div>
            <div class="dash-kpi-val" id="statIssues">—</div>
            <div class="dash-kpi-sub red">↓ -2 from last week</div>
          </div>

          <!-- Pending Recommendations -->
          <div class="dash-kpi-card" onclick="nav('recommendations',null)" title="Review recommendations">
            <div class="dash-kpi-icon amber"><x-icon name="lightbulb" size="18"/></div>
            <div class="dash-kpi-label">Pending Recommendations</div>
            <div class="dash-kpi-val" id="statRecs">0</div>
            <div class="dash-kpi-sub amber">⏱ For review</div>
          </div>

          <!-- Active Announcements -->
          <div class="dash-kpi-card" onclick="nav('announcements',null)" title="View announcements">
            <div class="dash-kpi-icon orange"><x-icon name="megaphone" size="18"/></div>
            <div class="dash-kpi-label">Active Announcements</div>
            <div class="dash-kpi-val" id="statAnnouncements">0</div>
            <div class="dash-kpi-sub green">↑ Currently published</div>
          </div>
        </div>

        <!-- 3. MIDDLE ROW (3 COLUMNS: Activity Feed, Issue Status, Subdivision Map) -->
        <div class="dash-mid-grid">
          <!-- Recent Activity Feed -->
          <div class="dash-card dash-activity-card">
            <div class="dash-card-header">
              <div style="display:flex;align-items:center;gap:10px;">
                <span class="dash-header-icon amber"><x-icon name="folder" size="17"/></span>
                <div>
                  <div class="dash-card-title">Recent Activity Feed</div>
                  <div class="dash-card-subtitle">The latest actions across the subdivision.</div>
                </div>
              </div>
              <div class="dash-feed-filter">
                <select id="feedFilterSelect" onchange="filterActivityFeed(this.value)">
                  <option value="all">All Activities</option>
                  <option value="announcement">Announcements</option>
                  <option value="issue">Issue Reports</option>
                  <option value="registration">Registrations</option>
                  <option value="recommendation">Recommendations</option>
                </select>
              </div>
            </div>
            <div class="dash-feed-list" id="activityFeed">
              <!-- Rendered dynamically by renderDashboard() in admin-dashboard.js -->
            </div>
          </div>

          <!-- Issue Status Donut Card -->
          <div class="dash-card dash-status-card">
            <div class="dash-card-header">
              <div style="display:flex;align-items:center;gap:10px;">
                <span class="dash-header-icon orange"><x-icon name="sliders" size="17"/></span>
                <div class="dash-card-title">Issue Status</div>
              </div>
            </div>
            <div class="dash-donut-wrap">
              <div class="dash-donut-chart">
                <svg viewBox="0 0 100 100" width="130" height="130" class="dash-donut-svg">
                  <!-- Background ring -->
                  <circle cx="50" cy="50" r="38" fill="none" stroke="#2B2724" stroke-width="12"/>
                  <!-- Resolved segment (Green #22C55E) -->
                  <circle id="donutSegmentResolved" cx="50" cy="50" r="38" fill="none" stroke="#22C55E" stroke-width="12" stroke-dasharray="119.38 238.76" stroke-dashoffset="0" transform="rotate(-90 50 50)"/>
                  <!-- In Progress segment (Blue #3B82F6) -->
                  <circle id="donutSegmentProgress" cx="50" cy="50" r="38" fill="none" stroke="#3B82F6" stroke-width="12" stroke-dasharray="59.69 238.76" stroke-dashoffset="-119.38" transform="rotate(-90 50 50)"/>
                  <!-- Open segment (Orange #F58B1F) -->
                  <circle id="donutSegmentOpen" cx="50" cy="50" r="38" fill="none" stroke="#F58B1F" stroke-width="12" stroke-dasharray="59.69 238.76" stroke-dashoffset="-179.07" transform="rotate(-90 50 50)"/>
                </svg>
                <div class="dash-donut-center">
                  <span class="dash-donut-num" id="donutTotalIssues">4</span>
                  <span class="dash-donut-txt">Total Issues</span>
                </div>
              </div>
              <div class="dash-donut-legend">
                <div class="dash-legend-row">
                  <span class="dash-legend-dot orange"></span>
                  <span class="dash-legend-name">Open</span>
                  <span class="dash-legend-val" id="donutOpenCount">1 (25%)</span>
                </div>
                <div class="dash-legend-row">
                  <span class="dash-legend-dot blue"></span>
                  <span class="dash-legend-name">In Progress</span>
                  <span class="dash-legend-val" id="donutProgCount">1 (25%)</span>
                </div>
                <div class="dash-legend-row">
                  <span class="dash-legend-dot green"></span>
                  <span class="dash-legend-name">Resolved</span>
                  <span class="dash-legend-val" id="donutResCount">2 (50%)</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Subdivision Map Preview Card -->
          <div class="dash-card dash-map-card">
            <div class="dash-card-header">
              <div style="display:flex;align-items:center;gap:10px;">
                <span class="dash-header-icon orange"><x-icon name="map" size="17"/></span>
                <div>
                  <div class="dash-card-title">Subdivision Map</div>
                  <div class="dash-card-subtitle">View colored blocks and live issue heatmap.</div>
                </div>
              </div>
            </div>
            <div class="dash-map-preview-wrap" onclick="nav('mapping',null)" title="Open interactive subdivision map">
              <div id="dash-mini-map" class="dash-mini-map">
                <div class="dash-mini-map-loading">
                  <x-icon name="map" size="18"/>
                  <span>Loading subdivision map...</span>
                </div>
              </div>
              <div class="dash-map-overlay">
                <span class="dash-map-badge"><x-icon name="map" size="12"/> View Only</span>
              </div>
            </div>
            <div style="display:flex;justify-content:flex-end;margin-top:10px;">
              <button class="btn btn-sm btn-map-view" onclick="nav('mapping',null)">View Map &rarr;</button>
            </div>
          </div>
        </div>

        <!-- 4. BOTTOM ROW (2 COLUMNS: Upcoming Events, Quick Actions) -->
        <div class="dash-bottom-grid">
          <!-- Upcoming Community Events -->
          <div class="dash-card dash-events-card">
            <div class="dash-card-header">
              <div style="display:flex;align-items:center;gap:10px;">
                <span class="dash-header-icon orange"><x-icon name="calendar" size="17"/></span>
                <div>
                  <div class="dash-card-title">Upcoming Community Events</div>
                  <div class="dash-card-subtitle">Subdivision activities and important dates.</div>
                </div>
              </div>
              <a href="javascript:void(0)" onclick="nav('announcements',null)" class="dash-card-link">View Calendar &rarr;</a>
            </div>
            <div class="dash-events-list" id="dashEventsList">
              <div class="dash-event-item">
                <div class="dash-event-date">
                  <span class="dash-event-month">NOV</span>
                  <span class="dash-event-day">20</span>
                </div>
                <div class="dash-event-info">
                  <div class="dash-event-title">Homeowners' Association Meeting</div>
                  <div class="dash-event-meta">
                    <span><x-icon name="clock" size="12"/> 6:00 PM</span>
                    <span><x-icon name="map" size="12"/> Clubhouse</span>
                  </div>
                </div>
              </div>
              <div class="dash-event-item">
                <div class="dash-event-date">
                  <span class="dash-event-month">NOV</span>
                  <span class="dash-event-day">27</span>
                </div>
                <div class="dash-event-info">
                  <div class="dash-event-title">Subdivision Clean-Up Drive</div>
                  <div class="dash-event-meta">
                    <span><x-icon name="clock" size="12"/> 7:00 AM</span>
                    <span><x-icon name="map" size="12"/> Main Entrance</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="dash-card dash-qa-card">
            <div class="dash-card-header">
              <div style="display:flex;align-items:center;gap:10px;">
                <span class="dash-header-icon orange"><x-icon name="zap" size="17"/></span>
                <div>
                  <div class="dash-card-title">Quick Actions</div>
                  <div class="dash-card-subtitle">Frequently used functions for community management.</div>
                </div>
              </div>
            </div>
            <div class="dash-qa-grid">
              <button class="dash-qa-btn active" onclick="openModal('ann')" title="Post Announcement">
                <span class="dash-qa-icon orange"><x-icon name="megaphone" size="20"/></span>
                <span class="dash-qa-text">Post Announcement</span>
              </button>
              <button class="dash-qa-btn" onclick="nav('residents',null)" title="Review Resident Applications">
                <span class="dash-qa-icon"><x-icon name="users" size="20"/></span>
                <span class="dash-qa-text">Review Applications</span>
              </button>
              <button class="dash-qa-btn" onclick="nav('issues',null)" title="Manage Issues">
                <span class="dash-qa-icon"><x-icon name="alert" size="20"/></span>
                <span class="dash-qa-text">Manage Issues</span>
              </button>
              <button class="dash-qa-btn" onclick="nav('recommendations',null)" title="Review Recommendations">
                <span class="dash-qa-icon"><x-icon name="lightbulb" size="20"/></span>
                <span class="dash-qa-text">Review Recommendations</span>
              </button>
              <button class="dash-qa-btn" onclick="nav('mapping',null)" title="View Subdivision Map">
                <span class="dash-qa-icon"><x-icon name="map" size="20"/></span>
                <span class="dash-qa-text">View Subdivision Map</span>
              </button>
            </div>
          </div>
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
            <button class="btn btn-sm" style="background:#22c55e;border-color:#22c55e;color:#062b12;" onclick="bulkPending('approve')"><x-icon name="check" size="14"/> Approve selected</button>
            <button class="btn btn-sm btn-danger" onclick="bulkPending('reject')"><x-icon name="x" size="14"/> Reject selected</button>
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
            <button class="send-btn" onclick="adminSend()"><x-icon name="send" size="16"/></button>
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
            <option value="Draft">Drafts</option>
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
          <div class="manage-card-icon"><x-icon name="home" size="22"/></div>
          <h3>Add Household</h3>
          <p>Create new household records</p>
        </div>
        <div class="manage-card" onclick="openAddUserModal()">
          <div class="manage-card-icon"><x-icon name="plus" size="22"/></div>
          <h3>Add User</h3>
          <p>Create new system user account</p>
        </div>
        <div class="manage-card" onclick="openModal('addResident')">
          <div class="manage-card-icon"><x-icon name="user" size="22"/></div>
          <h3>Add Resident</h3>
          <p>Register a new resident</p>
        </div>
        <div class="manage-card" onclick="populateMemberHouseholdSelect();openModal('muAddMember')">
          <div class="manage-card-icon">�</div>
          <h3>Add HH Member</h3>
          <p>Add a household member</p>
        </div>
        <div class="manage-card" onclick="nav('manageusers',null);showToast('ℹ️ Click Status on a household row to change its status.')">
          <div class="manage-card-icon"><x-icon name="alert" size="22"/></div>
          <h3>Change Status</h3>
          <p>Update household condition</p>
        </div>
        <div class="manage-card" onclick="nav('officers',null)">
          <div class="manage-card-icon"><x-icon name="shield" size="22"/></div>
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
            <thead><tr><th>ID</th><th>Location</th><th>Family / Resident</th><th>Occupants</th><th>Status</th><th>Actions</th></tr></thead>
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
        <div class="stat-grid">
          <div class="stat-card">
            <div class="stat-label">Officers</div>
            <div class="stat-value" id="officerStatTotal">0</div>
            <div class="stat-sub">Accounts on record</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Active</div>
            <div class="stat-value stat-green" id="officerStatActive">0</div>
            <div class="stat-sub">Can sign in to the officer portal</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value stat-yellow" id="officerStatPending">0</div>
            <div class="stat-sub">Awaiting your approval</div>
          </div>
        </div>
        <div class="card">
          <div class="card-title" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <span>Officer accounts</span>
            <span style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
              <input id="officerSearch" type="search" placeholder="Search name or email" oninput="renderOfficers()" style="min-width:220px;">
              <button class="btn btn-sm" onclick="openModal('addOfficer')">+ Add Officer</button>
            </span>
          </div>
          <div class="card-subtitle" style="margin-bottom:12px;">Manage subdivision officers and their roles. Pending applications can be approved right here.</div>
          <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Contact</th><th>Joined</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
            <tbody id="officerTable"></tbody>
          </table>
        </div>
      </div>

    <!-- ══════════ RECOMMENDATIONS ══════════ -->
    <div class="panel" id="panel-recommendations">
      <div class="card" style="margin-bottom:16px;">
        <div style="font-size:13px;color:var(--text-mid);">Resident suggestions and feedback submitted for review.</div>
      </div>
      <div id="recsAdminList"><div class="empty-state"><div class="empty-icon"><x-icon name="lightbulb" size="28"/></div>Loading…</div></div>
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
            <div style="font-size:12px;color:var(--text-dim);margin-bottom:8px;">Account and record-handling events from the activity trail.</div>
            <div id="analytics-security-logs" style="min-height:180px;"><div class="empty-state" style="padding:14px;">Loading…</div></div>
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
            <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">Issue pins are numbered by report sequence. Map is limited to the Terra Nova subdivision boundary. The heatmap reveals thermal density and dominant issue categories per area.</div>
          </div>
          <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <div class="map-mode-switch" style="display:inline-flex;border-radius:6px;overflow:hidden;border:1px solid var(--glass-border);background:var(--glass);" title="Toggle between View-Only exploration and Map Editing">
              <button type="button" id="btn-map-mode-view" class="btn btn-sm" onclick="setMapInteractiveMode('view')" style="padding:4px 10px;font-size:12px;font-weight:700;background:var(--accent);color:#fff;border-radius:0;">
                <x-icon name="eye" size="13"/> View Only
              </button>
              <button type="button" id="btn-map-mode-edit" class="btn btn-sm" onclick="setMapInteractiveMode('edit')" style="padding:4px 10px;font-size:12px;font-weight:600;background:transparent;color:var(--text);border-radius:0;">
                <x-icon name="pencil" size="13"/> Edit Mode
              </button>
            </div>
            <label class="map-toggle" title="Toggle block partition boundaries"><input type="checkbox" id="layer-blocks" onchange="toggleMapBlocks(this.checked)"> Block Partitions</label>
            <select id="filter-block-select" onchange="filterMapByBlock(this.value)" class="btn btn-sm" style="padding:4px 8px;font-size:12px;font-weight:600;" title="Filter map to a specific block">
              <option value="">All Blocks (1–14 · 338 Lots)</option>
              <option value="1">Block 1 — Lots 1–10 (Sky Blue)</option>
              <option value="2">Block 2 — Clubhouse &amp; Pool (Purple)</option>
              <option value="3">Block 3 — Perimeter Lots 1–74 (Red)</option>
              <option value="4">Block 4 — Lots 1–40 (Orange)</option>
              <option value="5">Block 5 — Lots 1–34 (Purple)</option>
              <option value="6">Block 6 — Lots 1–28 (Green)</option>
              <option value="7">Block 7 — Lots 1–24 (Amber)</option>
              <option value="8">Block 8 — Lots 1–19 (Lime)</option>
              <option value="9">Block 9 — Lots 1–20 (Rose)</option>
              <option value="10">Block 10 — Lots 1–18 (Magenta)</option>
              <option value="11">Block 11 — Lots 1–18 (Indigo)</option>
              <option value="12">Block 12 — Lots 1–22 (Pink)</option>
              <option value="13">Block 13 — Lots 1–16 (Gold)</option>
              <option value="14">Block 14 — Lots 1–14 (Blue)</option>
            </select>
            <label class="map-toggle" title="Toggle facilities markers"><input type="checkbox" id="layer-facilities" checked onchange="toggleMapFacilities(this.checked)"> Facilities</label>
            <label class="map-toggle" title="Toggle reported issue markers"><input type="checkbox" id="layer-issues" checked onchange="toggleMapIssues(this.checked)"> Issue Pins</label>
            <label class="map-toggle" title="Toggle thermal issue heatmap and frequency categorization"><input type="checkbox" id="layer-heat" onchange="toggleMapHeatmap(this.checked)"> Heatmap</label>
            <select id="heat-category" class="btn btn-sm" style="padding:4px 8px;font-size:12px;" title="Filter the heatmap by issue category">
              <option value="">All categories</option>
            </select>
            <span id="map-edit-actions" style="display:none;align-items:center;gap:6px;">
              <button class="btn btn-sm btn-green" onclick="enterAddFacilityMode()">+ Add Facility</button>
            </span>
            <button class="btn btn-sm" onclick="if(mapInst)mapInst.setView([10.62269,122.96134],17)">⌖ Reset View</button>
          </div>
        </div>
      </div>

      <!-- Heatmap & Hotspot Analysis Bar (Active when Heatmap layer is toggled on) -->
      <div id="heatmap-analysis-card" class="card" style="display:none;margin-bottom:12px;padding:12px 16px;background:var(--glass);border-left:4px solid #ef4444;transition:all 0.25s ease;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
          <div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:rgba(239,68,68,0.15);color:#ef4444;font-size:13px;">🔥</span>
              <span style="font-size:13px;font-weight:700;color:var(--text);">Subdivision Issue Heatmap &amp; Category Frequency</span>
              <span id="heatmap-status-badge" style="font-size:11px;padding:2px 8px;border-radius:12px;background:rgba(239,68,68,0.15);color:#ef4444;font-weight:700;">Live Heat</span>
            </div>
            <div id="heatmap-summary-line" style="font-size:12px;color:var(--text-mid);margin-top:4px;">
              Analyzing issue frequency across subdivision...
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <!-- Thermal Gradient Scale -->
            <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--text-dim);" title="Thermal intensity ramps up as reports cluster in that area">
              <span>Low (1)</span>
              <div style="width:100px;height:9px;border-radius:4px;background:linear-gradient(to right, #38bdf8, #34d399, #facc15, #fb923c, #ef4444);box-shadow:inset 0 1px 2px rgba(0,0,0,0.25);"></div>
              <span style="color:#ef4444;font-weight:700;">Hotspot (4+)</span>
            </div>
          </div>
        </div>
        <!-- Ranked category frequency filter pills -->
        <div id="heatmap-category-pills" style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-top:10px;padding-top:8px;border-top:1px solid var(--glass-border);">
        </div>
      </div>

      <!-- Legend -->
      <div class="card" style="margin-bottom:12px;padding:8px 16px;">
        <div class="map-legend">
          <span><span class="sub-start-dir-marker is-sample" style="display:inline-flex;position:static;vertical-align:middle;margin-right:4px;transform:none;"><span class="start-dir-arrow">↑</span><span class="start-dir-code">N</span></span>Lot 1 Start Point</span>
          <span><span class="map-dot" style="background:#4CAF50;"></span>Facility</span>
          <span><span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#e05555;color:#fff;font-size:9px;font-weight:700;margin-right:4px;">1</span>Issue (Pending)</span>
          <span><span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#f5a623;color:#fff;font-size:9px;font-weight:700;margin-right:4px;">2</span>Issue (In Progress)</span>
          <span><span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#888;color:#fff;font-size:9px;font-weight:700;margin-right:4px;">3</span>Issue (Resolved)</span>
          <span style="margin-left:auto;font-size:11px;color:var(--text-dim);"><x-icon name="map-pin" size="12"/> Numbers = Report sequence</span>
        </div>
      </div>

      <!-- Map -->
      <div id="map-edit-bar-btns" style="display:none;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
        <button class="btn btn-sm" onclick="enterEditBlocksMode()" id="btn-edit-blocks" title="Reshape individual block boundaries directly on the map"><x-icon name="edit" size="14"/> Edit Blocks</button>
        <button class="btn btn-sm" onclick="enterCustomizeArea()" id="btn-customize-area"><x-icon name="pencil" size="14"/> Customize Area</button>
        <span id="map-customize-hint" style="display:none;font-size:12px;color:var(--accent);">Drag handles to freely adjust the border. Drag <strong>Move Area</strong> to reposition, or click <strong>Draw New Border</strong>.</span>
      </div>
      <div>
        
        <!-- Block Editing Toolbar -->
        <span style="margin-left:auto;display:none;gap:8px;align-items:center;flex-wrap:wrap;" id="map-blocks-toolbar">
          <span style="font-size:12px;color:#16a34a;font-weight:600;"><x-icon name="edit" size="13"/> Editing Blocks</span>
          <div style="display:inline-flex;border-radius:6px;overflow:hidden;border:1px solid var(--border);" title="Switch between point editing or 4-corner scale resize">
            <button class="btn btn-sm" id="btn-block-mode-points" onclick="setBlockEditorMode('points')" style="padding:3px 8px;font-size:11px;font-weight:700;background:var(--accent);color:#fff;">∿ Edit Points</button>
            <button class="btn btn-sm" id="btn-block-mode-scale" onclick="setBlockEditorMode('scale')" style="padding:3px 8px;font-size:11px;font-weight:600;background:transparent;color:var(--text);">⊡ 4-Corner Scale</button>
          </div>
          <div style="display:inline-flex;border-radius:6px;overflow:hidden;border:1px solid var(--border);" title="Quick scale block size">
            <button class="btn btn-sm" id="btn-shrink-block" onclick="scaleSelectedBlock(0.85)" style="padding:3px 8px;font-size:11px;" title="Make selected block 15% smaller">⊖ Shrink</button>
            <button class="btn btn-sm" id="btn-enlarge-block" onclick="scaleSelectedBlock(1.15)" style="padding:3px 8px;font-size:11px;" title="Make selected block 15% larger">⊕ Enlarge</button>
          </div>
          <button class="btn btn-sm" id="btn-edit-block-info" onclick="openSelectedBlockInfoModal()" title="Edit Name, Lot Range, START Label, and Color for selected block"><x-icon name="edit" size="13"/> Edit Block Info</button>
          <span style="font-size:11px;color:var(--text-dim);" id="block-edit-hint">Drag any of the 4 corners to scale. Drag the Block Label or START pill to reposition them.</span>
          <button class="btn btn-sm" onclick="subdivideBlockPoints()" id="btn-subdivide-block" title="Subdivide edges to add more points of movement"><x-icon name="plus" size="13"/> + Add More Points</button>
          <button class="btn btn-sm btn-green" onclick="saveBlockPolygons()"><x-icon name="save" size="13"/> Save Blocks</button>
          <button class="btn btn-sm" onclick="cancelEditBlocks()">✕ Cancel</button>
        </span>
        
        <!-- Area Toolbar -->
        <span style="margin-left:auto;display:none;gap:8px;align-items:center;flex-wrap:wrap;" id="map-area-toolbar">
          <button class="btn btn-sm" onclick="toggleDrawAreaMode()" id="btn-draw-area" title="Click and drag on the map to draw a new border box"><x-icon name="plus" size="14"/> <span id="btn-draw-area-text">Draw New Border</span></button>
          <button class="btn btn-sm btn-green" onclick="saveMapArea()" id="btn-save-area"><x-icon name="save" size="14"/> Save Area</button>
          <button class="btn btn-sm" onclick="cancelCustomizeArea()" id="btn-cancel-area">✕ Cancel</button>
          <button class="btn btn-sm" onclick="resetMapArea()" id="btn-reset-area">↺ Reset</button>
        </span>
      </div>

      <!-- Add-facility mode banner (visible directly above map) -->
      <div id="map-add-banner" style="display:none;margin-bottom:10px;padding:10px 14px;background:rgba(16,185,129,0.12);border:1px solid #10b981;border-radius:8px;font-size:12px;color:var(--text);align-items:center;gap:10px;">
        <span><x-icon name="map-pin" size="14"/> <strong>Add Facility Mode:</strong> Click anywhere on the map or drag the green pin to position the facility.</span>
        <button class="btn btn-sm" style="margin-left:auto;" onclick="cancelAddFacilityMode()">✕ Cancel</button>
      </div>

      <div id="map-container"></div>

      <!-- Map Disclaimer -->
      <div style="margin-top:10px;padding:10px 14px;background:var(--accent-dim);border:1px solid var(--accent-border);border-radius:var(--radius-sm);font-size:12px;color:var(--text-dim);">
        <x-icon name="info" size="14"/> <strong>Map Scope:</strong> This map is focused on Terra Nova Subdivision. Reported issues are represented by numbered pins indicating report sequence. The geofence boundary is approximate. For precise location data, please consult official subdivision plans.
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
            <button class="btn btn-green" onclick="reportIssues()"><x-icon name="wrench" size="15"/> Issue Report</button>
            <button class="btn" style="background:var(--purple-dim);border-color:rgba(185,154,245,0.25);color:var(--purple);" onclick="reportResidentDirectory()"><x-icon name="users" size="15"/> Resident Directory</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════ POLICIES ══════════ -->
    <div class="panel" id="panel-policies">
      <div class="card" style="margin-bottom:16px;background:linear-gradient(135deg,#FAF4E7,#FAF6F0);border-left:4px solid var(--accent);">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
          <div style="font-size:32px;"><x-icon name="scroll" size="32"/></div>
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
        <!-- Ledger statement — linked from a resident card's balance -->
        <div class="modal-overlay" id="modal-ledger">
          <div class="modal-box" style="max-width:640px;">
            <div class="modal-title" id="ledgerTitle">Ledger statement</div>
            <div id="ledgerSummary" style="font-size:13px;color:var(--text-mid);margin-bottom:10px;"></div>
            <div style="overflow:auto;max-height:46vh;">
              <table class="data-table">
                <thead><tr><th>Date</th><th>Description</th><th style="text-align:right;">Charge</th><th style="text-align:right;">Payment</th><th style="text-align:right;">Balance</th></tr></thead>
                <tbody id="ledgerBody"></tbody>
              </table>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">
              <button type="button" class="btn btn-sm" onclick="closeModal('ledger')">Close</button>
            </div>
          </div>
        </div>

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
        <button class="fin-tab" onclick="financeTab('requests',this)">Officer Claims <span id="badge-expense-requests" style="display:none;background:#c87941;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;font-weight:700;margin-left:4px;">0</span></button>
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
          <div style="font-size:32px;"><x-icon name="credit-card" size="32"/></div>
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

        <div class="fin-section" data-fin="settings">
        <!-- GCash payment submissions (validation queue) — shown under the
             "GCash & Payments" tab together with the account settings -->
        <div class="card" style="margin-top:16px;">
          <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;flex-wrap:wrap;">
            <div class="card-title" style="margin:0;">Payment Submissions</div>
            <button class="btn btn-sm" onclick="SubUI.exportCsv('/api/export/payments')">&#8595; Export CSV</button>
          </div>
          <div id="paymentsQueue"><div class="empty-state" style="padding:12px;">Loading.</div></div>
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

      <div class="fin-section" data-fin="requests">
      <!-- Officer Expense Claims (1-click approval pipeline) -->
      <div class="card" style="margin-top:16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;flex-wrap:wrap;">
          <div>
            <div class="card-title" style="margin:0;">Officer Expense Claims</div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:2px;">Claims submitted by officers with attached receipts. 1-click approval auto-posts directly to the HOA transparency ledger.</div>
          </div>
          <div style="display:flex;gap:8px;">
            <select id="adminExpenseReqFilter" onchange="renderAdminExpenseRequests()" style="padding:4px 8px;border-radius:6px;font-size:12px;border:1px solid var(--glass-border);background:var(--glass);">
              <option value="">All Statuses</option>
              <option value="Pending" selected>Pending Review</option>
              <option value="Approved">Approved</option>
              <option value="Rejected">Rejected</option>
            </select>
          </div>
        </div>
        <div id="adminExpenseRequestsList"><div class="empty-state" style="padding:12px;">Loading…</div></div>
      </div>
      </div><!-- /requests -->

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

      <!-- Review Officer Expense Request Modal -->
      <div class="modal-overlay" id="modal-reviewExpenseRequest">
        <div class="modal-box" style="max-width:580px;">
          <div class="modal-title">Review Officer Expense Claim</div>
          <input type="hidden" id="reviewExpId">

          <div id="reviewExpHeader" style="background:rgba(43,41,39,0.03);border:1px solid var(--glass-border);border-radius:8px;padding:12px;margin-bottom:14px;font-size:13px;line-height:1.6;"></div>

          <div style="text-align:center;margin-bottom:14px;" id="reviewExpReceiptContainer">
            <img id="reviewExpReceiptImg" alt="Receipt preview" style="max-width:100%;max-height:280px;border-radius:8px;border:1px solid var(--glass-border);display:none;margin:0 auto 8px;">
            <a id="reviewExpReceiptLink" href="#" target="_blank" rel="noopener" class="btn btn-sm" style="display:none;margin:0 auto;">Open Receipt in New Tab</a>
          </div>

          <div class="f-row">
            <div class="f-field">
              <span class="f-label">Confirm Category</span>
              <select id="reviewExpCategory">
                <option>Maintenance</option><option>Utilities</option><option>Security</option>
                <option>Landscaping</option><option>Amenities</option><option>Insurance</option>
                <option>Administrative</option><option>Events</option><option>Other</option>
              </select>
            </div>
            <div class="f-field">
              <span class="f-label">Confirm Fund</span>
              <select id="reviewExpFund">
                <option value="Operating">Operating Fund</option>
                <option value="Reserve">Reserve Fund</option>
              </select>
            </div>
          </div>

          <div class="f-row">
            <div class="f-field">
              <span class="f-label">Admin Notes / Reimbursement Details</span>
              <textarea id="reviewExpNotes" rows="2" placeholder="Optional approval notes, check number, or reason for rejection…"></textarea>
            </div>
          </div>

          <div style="font-size:12px;color:#f08080;margin-bottom:8px;" id="reviewExpMsg"></div>

          <div class="modal-actions" style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;">
            <button class="modal-close-btn" onclick="closeModal('reviewExpenseRequest')">Close</button>
            <div style="display:flex;gap:8px;" id="reviewExpActionBtns">
              <button class="btn btn-sm btn-danger" onclick="rejectExpenseRequest()">Reject Claim</button>
              <button class="btn btn-sm btn-green" onclick="approveExpenseRequest()">✓ Approve &amp; Post to Ledger</button>
            </div>
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
      <button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="applyAnnTemplate('notice')"><x-icon name="clipboard" size="13"/> Notice</button>
      <button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="applyAnnTemplate('announcement')"><x-icon name="megaphone" size="13"/> Announcement</button>
      <button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="applyAnnTemplate('event')"><x-icon name="calendar" size="13"/> Event</button>
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
        <button class="btn btn-sm" style="background:transparent;border:1px solid var(--glass-border);color:var(--text-mid);" onclick="saveAnnDraft()">Save as Draft</button>
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

<!-- Household Detail Modal -->
<div class="modal-overlay" id="modal-householdDetail" onclick="if(event.target===this)closeModal('householdDetail')">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-title" style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:6px;">
      <div>
        <div id="houseD-location" style="font-size:17px;font-weight:700;">—</div>
        <div id="houseD-family" style="font-size:12px;color:var(--text-dim);margin-top:2px;">—</div>
      </div>
      <span id="houseD-status" class="pill">—</span>
    </div>

    <div style="margin:14px 0 10px;">
      <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:8px;">Registered Residents (<span id="houseD-occupants">0</span> Total Occupants)</div>
      <div id="houseD-residents"></div>
    </div>

    <div style="margin:12px 0 16px;">
      <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:8px;">Household Members / Dependents</div>
      <div id="houseD-members"></div>
    </div>

    <div class="modal-actions" style="justify-content:space-between;gap:8px;">
      <button class="modal-close-btn" onclick="closeModal('householdDetail')">Close</button>
      <div style="display:flex;gap:6px;">
        <button class="btn btn-sm" id="houseD-statusBtn"><x-icon name="alert" size="13"/> Status</button>
        <button class="btn btn-sm btn-blue" id="houseD-editBtn"><x-icon name="pencil" size="13"/> Edit</button>
      </div>
    </div>
  </div>
</div>

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
    <div style="font-size:11px;color:var(--text-dim);margin-bottom:10px;"><x-icon name="alert" size="13"/> User will be prompted to change password on first login.</div>
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
<div class="modal-overlay" id="modal-addFacility" onclick="if(event.target===this){closeModal('addFacility');cancelAddFacilityMode();}">
  <div class="modal-box">
    <div class="modal-title">Add Facility</div>
    <div class="f-row"><div class="f-field"><span class="f-label">Facility Name <span style="color:#e05555;">*</span></span><input type="text" id="fac-name" placeholder="e.g. Clubhouse, Basketball Court"></div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Description</span><textarea id="fac-desc" placeholder="Short description (optional)"></textarea></div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Photo (optional)</span>
      <input type="file" id="fac-image" accept="image/*" onchange="previewImage(this,'fac-image-preview')">
      <img id="fac-image-preview" alt="preview" style="display:none;margin-top:8px;max-width:100%;max-height:170px;border-radius:8px;border:1px solid var(--glass-border);">
    </div></div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Latitude</span><input type="text" id="fac-lat" readonly style="background:var(--glass-hover);font-size:12px;" placeholder="Click map or drag pin"></div>
      <div class="f-field"><span class="f-label">Longitude</span><input type="text" id="fac-lng" readonly style="background:var(--glass-hover);font-size:12px;" placeholder="Click map or drag pin"></div>
    </div>
    <div style="font-size:11px;color:var(--text-dim);margin:-2px 0 10px;">💡 Tip: A green pin was placed on the map. You can drag the pin on the map or click anywhere on the map to change its position.</div>
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
    <div class="f-row">
      <div class="f-field"><span class="f-label">Latitude</span><input type="text" id="edit-fac-lat" readonly style="background:var(--glass-hover);font-size:12px;" title="Reposition by dragging the pin on the map"></div>
      <div class="f-field"><span class="f-label">Longitude</span><input type="text" id="edit-fac-lng" readonly style="background:var(--glass-hover);font-size:12px;" title="Reposition by dragging the pin on the map"></div>
    </div>
    <div style="font-size:11px;color:var(--text-dim);margin:-2px 0 10px;">💡 Tip: You can drag the green facility pin directly on the map to reposition it.</div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('editFacility')">Cancel</button>
      <button class="btn btn-sm" onclick="saveEditFacility()">Save Changes</button>
    </div>
  </div>
</div>

<script>
/* ── LAZY LIBRARY LOADERS (RAM/parse only when the feature is opened) ──
     d3 (~280 KB) loads when Analytics opens; Leaflet already has its own
     loader below. */
let _d3Loaded = false;
function loadD3(){
  if(_d3Loaded || typeof d3 !== 'undefined') return Promise.resolve();
  const AB = window.APP_BASE || '';
  return new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = AB + '/js/d3.v7.min.js';
    s.onload = () => { _d3Loaded = true; resolve(); };
    s.onerror = reject;
    document.head.appendChild(s);
  });
}

/* ══ LAZY LEAFLET LOADER — Leaflet loads from local origin only when the map is opened ══ */
let _leafletLoaded = false;
let _leafletPromise = null;
function loadLeaflet(){
  if(window.L && L.map) return Promise.resolve();
  if(_leafletPromise) return _leafletPromise;
  const AB = window.APP_BASE || '';
  _leafletPromise = new Promise((resolve) => {
    if(!document.querySelector('link[href*="leaflet.css"]')){
      const l = document.createElement('link');
      l.rel = 'stylesheet';
      l.href = AB + '/vendor/leaflet/leaflet.css';
      document.head.appendChild(l);
    }
    const srcs = [
      AB + '/vendor/leaflet/leaflet.js',
      AB + '/vendor/leaflet/leaflet-heat.js',
    ];
    let i = 0;
    (function next(){
      if(i >= srcs.length){
        _leafletLoaded = true;
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
  return _leafletPromise;
}

function ensureLeafletHeat(){
  if(window.L && typeof L.heatLayer === 'function') return Promise.resolve();
  const AB = window.APP_BASE || '';
  return new Promise((resolve) => {
    const s = document.createElement('script');
    s.src = AB + '/vendor/leaflet/leaflet-heat.js';
    s.onload = () => resolve();
    s.onerror = () => { console.warn('Could not load leaflet-heat.js'); resolve(); };
    document.body.appendChild(s);
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
  btn.innerHTML = isOpen ? '<x-icon name="menu" size="18"/>' : '<x-icon name="x" size="18"/>';
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('show');
  document.getElementById('menuToggle').innerHTML = '<x-icon name="menu" size="18"/>';
}
/* ══════════════ DASHBOARD MINI-MAP (VIEW-ONLY) ══════════════ */
let dashMiniMap = null;
let dashMiniBlocksLayer = null;
let dashMiniHeatLayer = null;
let dashMiniFacilitiesLayer = null;
let dashMiniIssuesLayer = null;
let dashMiniAreaRect = null;
let _dashMiniMapLoading = false;

async function initDashboardMiniMap() {
  const container = document.getElementById('dash-mini-map');
  if (!container) return;
  const isDashActive = document.getElementById('panel-dashboard')?.classList.contains('active');
  if (dashMiniMap) {
    if (isDashActive) {
      setTimeout(() => {
        dashMiniMap.invalidateSize();
        if (mapSettings) {
          const b = mapSettings;
          dashMiniMap.fitBounds([[b.min_lat, b.min_lng], [b.max_lat, b.max_lng]], { padding: [6, 6], animate: false });
        }
      }, 60);
    }
    return;
  }
  if (_dashMiniMapLoading) return;
  _dashMiniMapLoading = true;

  try {
    await loadLeaflet();
    if (!mapSettings) {
      await fetchMapSettings();
    }
    const b = mapSettings || { min_lat: 10.6195, max_lat: 10.6260, min_lng: 122.9580, max_lng: 122.9650, center_lat: 10.62269, center_lng: 122.96134, zoom: 17 };
    const areaBounds = L.latLngBounds([[b.min_lat, b.min_lng], [b.max_lat, b.max_lng]]);

    container.innerHTML = '';

    dashMiniMap = L.map(container, {
      zoomControl: false,
      scrollWheelZoom: false,
      doubleClickZoom: false,
      touchZoom: false,
      dragging: false,
      boxZoom: false,
      keyboard: false,
      attributionControl: false
    }).fitBounds(areaBounds, { padding: [6, 6], animate: false });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 20
    }).addTo(dashMiniMap);

    dashMiniAreaRect = L.rectangle(areaBounds, {
      color: '#FA7D09',
      weight: 1.5,
      dashArray: '5 3',
      fillColor: '#FA7D09',
      fillOpacity: 0.04,
      interactive: false
    }).addTo(dashMiniMap);

    dashMiniBlocksLayer = L.layerGroup().addTo(dashMiniMap);
    dashMiniHeatLayer = L.layerGroup().addTo(dashMiniMap);
    dashMiniFacilitiesLayer = L.layerGroup();
    dashMiniIssuesLayer = L.layerGroup();

    await refreshDashboardMiniMapData();

    setTimeout(() => {
      if (dashMiniMap) {
        dashMiniMap.invalidateSize();
        dashMiniMap.fitBounds(areaBounds, { padding: [6, 6], animate: false });
      }
    }, 150);
  } catch (err) {
    console.warn('Could not initialize dashboard mini-map:', err);
  } finally {
    _dashMiniMapLoading = false;
  }
}

async function refreshDashboardMiniMapData() {
  if (!dashMiniMap) return;
  try {
    const res = await fetch('/api/map/all', { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return;
    const data = await res.json();

    // 1. Render colored block boundaries only (no letter labels, no text pills)
    if (dashMiniBlocksLayer) {
      dashMiniBlocksLayer.clearLayers();
      (data.blocks || []).forEach(b => {
        if (!b.coordinates || !b.coordinates.length) return;
        L.polygon(b.coordinates, {
          color: b.color || '#2563eb',
          weight: 1.5,
          opacity: 0.9,
          fillColor: b.color || '#2563eb',
          fillOpacity: 0.32,
          interactive: false
        }).addTo(dashMiniBlocksLayer);
      });
    }

    // 2. Render thermal issue heatmap (pins and labels omitted per user request)
    await ensureLeafletHeat();
    if (dashMiniHeatLayer) {
      dashMiniHeatLayer.clearLayers();
      const issueList = (data.issues && data.issues.length) ? data.issues : (window.issues || []);
      const heatPoints = [];
      issueList.forEach(i => {
        const lat = parseFloat(i.latitude), lng = parseFloat(i.longitude);
        if (isNaN(lat) || isNaN(lng)) return;
        let intensity = 0.9;
        const st = (i.status || '').toLowerCase();
        if (st === 'in progress') intensity = 0.65;
        else if (st === 'resolved') intensity = 0.28;
        const pr = (i.priority || '').toLowerCase();
        if (pr === 'critical') intensity *= 1.4;
        else if (pr === 'high') intensity *= 1.2;
        heatPoints.push([lat, lng, intensity]);
      });

      if (typeof L.heatLayer === 'function' && heatPoints.length > 0) {
        const miniHeat = L.heatLayer(heatPoints, {
          radius: 28,
          blur: 18,
          maxZoom: 18,
          max: 1.4,
          minOpacity: 0.38,
          gradient: {
            0.2: '#38bdf8',
            0.45: '#34d399',
            0.65: '#facc15',
            0.82: '#fb923c',
            1.0: '#ef4444'
          }
        });
        dashMiniHeatLayer.addLayer(miniHeat);
      }
    }

    if (dashMiniFacilitiesLayer) dashMiniFacilitiesLayer.clearLayers();
    if (dashMiniIssuesLayer) dashMiniIssuesLayer.clearLayers();
  } catch (e) {
    console.warn('Could not refresh dashboard mini map data:', e);
  }
}

async function refreshDashboardMiniMap() {
  if (!dashMiniMap) return;
  await fetchMapSettings();
  const b = mapSettings || { min_lat: 10.6195, max_lat: 10.6260, min_lng: 122.9580, max_lng: 122.9650 };
  const areaBounds = L.latLngBounds([[b.min_lat, b.min_lng], [b.max_lat, b.max_lng]]);
  if (dashMiniAreaRect) {
    dashMiniAreaRect.setBounds(areaBounds);
  }
  dashMiniMap.fitBounds(areaBounds, { padding: [6, 6], animate: false });
  await refreshDashboardMiniMapData();
}

window.initDashboardMiniMap = initDashboardMiniMap;
window.refreshDashboardMiniMap = refreshDashboardMiniMap;
window.refreshDashboardMiniMapData = refreshDashboardMiniMapData;

/* Auto-initialize dashboard mini map when DOM is ready */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('panel-dashboard')?.classList.contains('active')) {
      initDashboardMiniMap();
    }
  });
} else {
  if (document.getElementById('panel-dashboard')?.classList.contains('active')) {
    initDashboardMiniMap();
  }
}

/* ══════════════ LEAFLET MAP ══════════════ */
let mapInst = null, layerHouseholds, layerFacilities, layerIssues, layerHeat;
let mapIssuesData = [];
let analyticsMapInst = null;
let addFacilityMode = false, pendingPin = null;
let editFacilityId = null;
let issueMarkerCount = 0;
let mapInteractiveMode = 'view';
let adminFacilityMarkers = [];

function makeCircleIcon(color, isDraggable = false) {
  const pinClass = isDraggable ? 'facility-marker-pin' : '';
  const pinTitle = isDraggable ? ' title="Drag to move facility"' : ' title="Facility"';
  return L.divIcon({
    className: '',
    html: `<div class="sub-map-dot ${pinClass}" style="width:14px;height:14px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.95);box-shadow:0 1px 6px rgba(0,0,0,0.5);"${pinTitle}></div>`,
    iconSize: [14, 14], iconAnchor: [7, 7], popupAnchor: [0, -10]
  });
}

function setMapInteractiveMode(mode) {
  mapInteractiveMode = mode === 'edit' ? 'edit' : 'view';
  const isView = mapInteractiveMode === 'view';

  const btnView = document.getElementById('btn-map-mode-view');
  const btnEdit = document.getElementById('btn-map-mode-edit');
  if (btnView && btnEdit) {
    if (isView) {
      btnView.style.background = 'var(--accent)';
      btnView.style.color = '#fff';
      btnView.style.fontWeight = '700';
      btnEdit.style.background = 'transparent';
      btnEdit.style.color = 'var(--text)';
      btnEdit.style.fontWeight = '600';
    } else {
      btnEdit.style.background = 'var(--accent)';
      btnEdit.style.color = '#fff';
      btnEdit.style.fontWeight = '700';
      btnView.style.background = 'transparent';
      btnView.style.color = 'var(--text)';
      btnView.style.fontWeight = '600';
    }
  }

  const editActions = document.getElementById('map-edit-actions');
  if (editActions) editActions.style.display = isView ? 'none' : 'inline-flex';

  const editBarBtns = document.getElementById('map-edit-bar-btns');
  if (editBarBtns) editBarBtns.style.display = isView ? 'none' : 'inline-flex';

  const mapContainer = document.getElementById('map-container');
  if (mapContainer) {
    if (isView) mapContainer.classList.add('map-view-only');
    else mapContainer.classList.remove('map-view-only');
  }

  (adminFacilityMarkers || []).forEach(item => {
    if (item && item.marker) {
      if (isView) {
        if (item.marker.dragging) item.marker.dragging.disable();
        const el = item.marker.getElement();
        if (el) {
          const dot = el.querySelector('.sub-map-dot');
          if (dot) dot.classList.remove('facility-marker-pin');
        }
      } else {
        if (item.marker.dragging) item.marker.dragging.enable();
        const el = item.marker.getElement();
        if (el) {
          const dot = el.querySelector('.sub-map-dot');
          if (dot) dot.classList.add('facility-marker-pin');
        }
      }
    }
  });
}
window.setMapInteractiveMode = setMapInteractiveMode;

function makeNumberedIcon(color, num) {
  return L.divIcon({
    className: '',
    html: `<div class="sub-map-pin" style="width:24px;height:24px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.9);box-shadow:0 2px 6px rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;font-family:sans-serif;">${num}</div>`,
    iconSize: [24, 24], iconAnchor: [12, 12], popupAnchor: [0, -14]
  });
}

let mapSettings = null;
let areaRect = null;
let customizingArea = false;
let editBounds = null;
let areaHandles = {};
let drawAreaMode = false;
let drawTempRect = null;
let drawStartLatLng = null;
const MIN_AREA_DELTA = 0.0003; // Minimum boundary span (~33m) to prevent inverted rectangles

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
  if (!b.min_lat || !b.max_lat || !b.min_lng || !b.max_lng) return;
  const bounds = L.latLngBounds([[b.min_lat,b.min_lng],[b.max_lat,b.max_lng]]);
  if (mapInst) {
    mapInst.setMinZoom(14);
    mapInst.setMaxZoom(20);
    if (typeof bounds.pad === 'function') {
      mapInst.setMaxBounds(bounds.pad(0.08));
    } else {
      mapInst.setMaxBounds(bounds);
    }
    mapInst.options.maxBoundsViscosity = 0.8;
  }
  if(areaRect) areaRect.remove();
  if(mapInst) {
    areaRect = L.rectangle(bounds,{color:'#FA7D09',weight:2,dashArray:'6 4',fillColor:'#FA7D09',fillOpacity:0.06,interactive:false}).addTo(mapInst);
  }
}

function makeAreaHandleIcon(type, cursor) {
  if (type === 'corner') {
    return L.divIcon({
      className: 'area-handle area-handle-corner',
      html: `<div style="cursor:${cursor};" title="Drag with mouse to resize border"></div>`,
      iconSize: [14, 14],
      iconAnchor: [7, 7]
    });
  } else if (type === 'edge-ns') {
    return L.divIcon({
      className: 'area-handle area-handle-edge-ns',
      html: `<div style="cursor:${cursor};" title="Drag North/South border"></div>`,
      iconSize: [20, 10],
      iconAnchor: [10, 5]
    });
  } else if (type === 'edge-ew') {
    return L.divIcon({
      className: 'area-handle area-handle-edge-ew',
      html: `<div style="cursor:${cursor};" title="Drag East/West border"></div>`,
      iconSize: [10, 20],
      iconAnchor: [5, 10]
    });
  } else if (type === 'center') {
    return L.divIcon({
      className: 'area-handle area-handle-center',
      html: `<div title="Drag with mouse to move the entire border area"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="15 19 12 22 9 19"></polyline><polyline points="19 9 22 12 19 15"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line></svg> Move Area</div>`,
      iconSize: [95, 24],
      iconAnchor: [47, 12]
    });
  }
}

function destroyAreaHandles(){
  Object.values(areaHandles).forEach(h => {
    try { h.remove(); } catch(e){}
  });
  areaHandles = {};
}

function createAreaHandles(){
  if(!mapInst || !editBounds) return;
  destroyAreaHandles();

  const midLat = (editBounds.minLat + editBounds.maxLat) / 2;
  const midLng = (editBounds.minLng + editBounds.maxLng) / 2;

  const handleDefs = [
    { key: 'nw', pos: [editBounds.maxLat, editBounds.minLng], icon: makeAreaHandleIcon('corner', 'nwse-resize') },
    { key: 'ne', pos: [editBounds.maxLat, editBounds.maxLng], icon: makeAreaHandleIcon('corner', 'nesw-resize') },
    { key: 'se', pos: [editBounds.minLat, editBounds.maxLng], icon: makeAreaHandleIcon('corner', 'nwse-resize') },
    { key: 'sw', pos: [editBounds.minLat, editBounds.minLng], icon: makeAreaHandleIcon('corner', 'nesw-resize') },
    { key: 'n',  pos: [editBounds.maxLat, midLng],             icon: makeAreaHandleIcon('edge-ns', 'ns-resize') },
    { key: 's',  pos: [editBounds.minLat, midLng],             icon: makeAreaHandleIcon('edge-ns', 'ns-resize') },
    { key: 'e',  pos: [midLat, editBounds.maxLng],             icon: makeAreaHandleIcon('edge-ew', 'ew-resize') },
    { key: 'w',  pos: [midLat, editBounds.minLng],             icon: makeAreaHandleIcon('edge-ew', 'ew-resize') },
    { key: 'center', pos: [midLat, midLng],                    icon: makeAreaHandleIcon('center', 'move') }
  ];

  handleDefs.forEach(def => {
    const marker = L.marker(def.pos, {
      icon: def.icon,
      draggable: true,
      zIndexOffset: def.key === 'center' ? 1000 : 1100
    }).addTo(mapInst);
    areaHandles[def.key] = marker;
  });

  areaHandles.nw.on('drag', function(e){
    const p = e.target.getLatLng();
    editBounds.maxLat = Math.max(p.lat, editBounds.minLat + MIN_AREA_DELTA);
    editBounds.minLng = Math.min(p.lng, editBounds.maxLng - MIN_AREA_DELTA);
    updateAreaRectAndHandles('nw');
  });

  areaHandles.ne.on('drag', function(e){
    const p = e.target.getLatLng();
    editBounds.maxLat = Math.max(p.lat, editBounds.minLat + MIN_AREA_DELTA);
    editBounds.maxLng = Math.max(p.lng, editBounds.minLng + MIN_AREA_DELTA);
    updateAreaRectAndHandles('ne');
  });

  areaHandles.se.on('drag', function(e){
    const p = e.target.getLatLng();
    editBounds.minLat = Math.min(p.lat, editBounds.maxLat - MIN_AREA_DELTA);
    editBounds.maxLng = Math.max(p.lng, editBounds.minLng + MIN_AREA_DELTA);
    updateAreaRectAndHandles('se');
  });

  areaHandles.sw.on('drag', function(e){
    const p = e.target.getLatLng();
    editBounds.minLat = Math.min(p.lat, editBounds.maxLat - MIN_AREA_DELTA);
    editBounds.minLng = Math.min(p.lng, editBounds.maxLng - MIN_AREA_DELTA);
    updateAreaRectAndHandles('sw');
  });

  areaHandles.n.on('drag', function(e){
    const p = e.target.getLatLng();
    editBounds.maxLat = Math.max(p.lat, editBounds.minLat + MIN_AREA_DELTA);
    updateAreaRectAndHandles('n');
  });

  areaHandles.s.on('drag', function(e){
    const p = e.target.getLatLng();
    editBounds.minLat = Math.min(p.lat, editBounds.maxLat - MIN_AREA_DELTA);
    updateAreaRectAndHandles('s');
  });

  areaHandles.e.on('drag', function(e){
    const p = e.target.getLatLng();
    editBounds.maxLng = Math.max(p.lng, editBounds.minLng + MIN_AREA_DELTA);
    updateAreaRectAndHandles('e');
  });

  areaHandles.w.on('drag', function(e){
    const p = e.target.getLatLng();
    editBounds.minLng = Math.min(p.lng, editBounds.maxLng - MIN_AREA_DELTA);
    updateAreaRectAndHandles('w');
  });

  let centerDragStartPos = null;
  let centerDragStartBounds = null;
  areaHandles.center.on('dragstart', function(e){
    centerDragStartPos = e.target.getLatLng();
    centerDragStartBounds = { ...editBounds };
  });
  areaHandles.center.on('drag', function(e){
    if(!centerDragStartPos || !centerDragStartBounds) return;
    const cur = e.target.getLatLng();
    const dLat = cur.lat - centerDragStartPos.lat;
    const dLng = cur.lng - centerDragStartPos.lng;
    editBounds.minLat = centerDragStartBounds.minLat + dLat;
    editBounds.maxLat = centerDragStartBounds.maxLat + dLat;
    editBounds.minLng = centerDragStartBounds.minLng + dLng;
    editBounds.maxLng = centerDragStartBounds.maxLng + dLng;
    updateAreaRectAndHandles('center');
  });
  areaHandles.center.on('dragend', function(){
    centerDragStartPos = null;
    centerDragStartBounds = null;
    updateAreaRectAndHandles(null);
  });

  ['nw','ne','se','sw','n','s','e','w'].forEach(k => {
    if(areaHandles[k]){
      areaHandles[k].on('dragend', function(){
        updateAreaRectAndHandles(null);
      });
    }
  });
}

function updateAreaRectAndHandles(skipHandle = null){
  const bounds = L.latLngBounds(
    [editBounds.minLat, editBounds.minLng],
    [editBounds.maxLat, editBounds.maxLng]
  );
  if (areaRect) {
    areaRect.setBounds(bounds);
  }

  const midLat = (editBounds.minLat + editBounds.maxLat) / 2;
  const midLng = (editBounds.minLng + editBounds.maxLng) / 2;

  const positions = {
    nw: [editBounds.maxLat, editBounds.minLng],
    ne: [editBounds.maxLat, editBounds.maxLng],
    se: [editBounds.minLat, editBounds.maxLng],
    sw: [editBounds.minLat, editBounds.minLng],
    n:  [editBounds.maxLat, midLng],
    s:  [editBounds.minLat, midLng],
    e:  [midLat, editBounds.maxLng],
    w:  [midLat, editBounds.minLng],
    center: [midLat, midLng]
  };

  for (const [key, latlng] of Object.entries(positions)) {
    if (areaHandles[key] && key !== skipHandle) {
      areaHandles[key].setLatLng(latlng);
    }
  }
}

function toggleDrawAreaMode(){
  if(!customizingArea || !mapInst) return;
  if(drawAreaMode){
    stopDrawAreaMode();
  } else {
    startDrawAreaMode();
  }
}

function startDrawAreaMode(){
  drawAreaMode = true;
  const btnText = document.getElementById('btn-draw-area-text');
  if(btnText) btnText.textContent = '✕ Stop Drawing';
  mapInst.getContainer().classList.add('area-drawing-active');
  mapInst.dragging.disable();
  destroyAreaHandles();

  mapInst.on('mousedown', handleDrawMouseDown);
  mapInst.on('mousemove', handleDrawMouseMove);
  window.addEventListener('mouseup', handleDrawMouseUp);
}

function stopDrawAreaMode(){
  drawAreaMode = false;
  const btnText = document.getElementById('btn-draw-area-text');
  if(btnText) btnText.textContent = 'Draw New Border';
  if(mapInst){
    mapInst.getContainer().classList.remove('area-drawing-active');
    mapInst.dragging.enable();
    mapInst.off('mousedown', handleDrawMouseDown);
    mapInst.off('mousemove', handleDrawMouseMove);
  }
  window.removeEventListener('mouseup', handleDrawMouseUp);
  if(drawTempRect){
    drawTempRect.remove();
    drawTempRect = null;
  }
  drawStartLatLng = null;
  createAreaHandles();
}

function handleDrawMouseDown(e){
  if(!drawAreaMode) return;
  drawStartLatLng = e.latlng;
  if(drawTempRect) drawTempRect.remove();
  drawTempRect = L.rectangle([e.latlng, e.latlng], {
    color: '#2563EB',
    weight: 2,
    dashArray: '5 5',
    fillColor: '#3B82F6',
    fillOpacity: 0.15
  }).addTo(mapInst);
}

function handleDrawMouseMove(e){
  if(!drawAreaMode || !drawStartLatLng || !drawTempRect) return;
  drawTempRect.setBounds(L.latLngBounds(drawStartLatLng, e.latlng));
}

function handleDrawMouseUp(e){
  if(!drawAreaMode || !drawStartLatLng) return;
  const endPoint = (e.latlng && e.latlng.lat) ? e.latlng : (drawTempRect ? drawTempRect.getBounds().getNorthEast() : drawStartLatLng);
  const b = L.latLngBounds(drawStartLatLng, endPoint);
  const dLat = Math.abs(b.getNorthEast().lat - b.getSouthWest().lat);
  const dLng = Math.abs(b.getNorthEast().lng - b.getSouthWest().lng);

  if (dLat >= MIN_AREA_DELTA && dLng >= MIN_AREA_DELTA) {
    editBounds = {
      minLat: b.getSouthWest().lat,
      maxLat: b.getNorthEast().lat,
      minLng: b.getSouthWest().lng,
      maxLng: b.getNorthEast().lng
    };
    if (areaRect) areaRect.setBounds(b);
    showToast('Border updated. Adjust handles to refine.');
  }

  stopDrawAreaMode();
}

function enterCustomizeArea(){
  if(typeof cancelAddFacilityMode === 'function' && addFacilityMode) cancelAddFacilityMode();
  customizingArea = true;
  document.getElementById('btn-customize-area').style.display='none';
  const bBlocks = document.getElementById('btn-edit-blocks'); if (bBlocks) bBlocks.style.display = 'none';
  document.getElementById('map-area-toolbar').style.display='flex';
  document.getElementById('map-customize-hint').style.display='inline-block';
  document.getElementById('btn-save-area').disabled = false;
  document.getElementById('btn-reset-area').disabled = false;

  if(mapInst){
    mapInst.setMaxBounds(null);
  }

  const b = (areaRect && areaRect.getBounds()) ||
            (mapSettings && L.latLngBounds([[mapSettings.min_lat, mapSettings.min_lng],[mapSettings.max_lat, mapSettings.max_lng]])) ||
            (mapInst ? mapInst.getBounds() : L.latLngBounds([[10.6195,122.9580],[10.6260,122.9650]]));

  editBounds = {
    minLat: +b.getSouthWest().lat,
    maxLat: +b.getNorthEast().lat,
    minLng: +b.getSouthWest().lng,
    maxLng: +b.getNorthEast().lng
  };

  if (areaRect) {
    areaRect.setBounds(L.latLngBounds([
      [editBounds.minLat, editBounds.minLng],
      [editBounds.maxLat, editBounds.maxLng]
    ]));
    areaRect.setStyle({
      color: '#2563EB',
      weight: 3,
      dashArray: '6 4',
      fillColor: '#3B82F6',
      fillOpacity: 0.12
    });
  }

  createAreaHandles();
}

function exitCustomizeArea(saved = false){
  if(drawAreaMode) stopDrawAreaMode();
  destroyAreaHandles();
  customizingArea = false;
  document.getElementById('btn-customize-area').style.display='inline-flex';
  const bBlocks = document.getElementById('btn-edit-blocks'); if (bBlocks) bBlocks.style.display = 'inline-flex';
  document.getElementById('map-area-toolbar').style.display='none';
  document.getElementById('map-customize-hint').style.display='none';

  if (areaRect) {
    areaRect.setStyle({
      color: '#FA7D09',
      weight: 2,
      dashArray: '6 4',
      fillColor: '#FA7D09',
      fillOpacity: 0.06
    });
  }
}

function cancelCustomizeArea(){
  if(!customizingArea) return;
  exitCustomizeArea(false);
  applyMapArea();
  showToast('Boundary editing cancelled.');
}

function saveMapArea(){
  if(!mapInst || !customizingArea) return;
  const b = areaRect ? areaRect.getBounds() : L.latLngBounds([
    [editBounds.minLat, editBounds.minLng],
    [editBounds.maxLat, editBounds.maxLng]
  ]);
  const c = b.getCenter();
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
  }).then(r=>r.json()).then(res=>{
    if(res && res.errors){
      showToast('⚠ Failed: ' + Object.values(res.errors).flat().join(', '));
      return;
    }
    mapSettings = payload;
    exitCustomizeArea(true);
    applyMapArea();
    showToast('✅ Map border updated.');
    if(typeof loadMapData === 'function') loadMapData();
    if(typeof refreshDashboardMiniMap === 'function') refreshDashboardMiniMap();
  }).catch(()=>showToast('⚠ Could not save map border.'));
}

async function resetMapArea(){
  if(typeof SubUI !== 'undefined' && SubUI.confirm){
    const ok = await SubUI.confirm({
      title: 'Reset Map Boundary?',
      message: 'This will reset the subdivision border back to original default coordinates.',
      confirmText: 'Reset to Default',
      danger: true,
      icon: 'alert'
    });
    if(!ok) return;
  }
  const payload = {min_lat:10.6195,max_lat:10.6260,min_lng:122.9580,max_lng:122.9650,center_lat:10.62269,center_lng:122.96134,zoom:17};
  fetch('/api/map/settings',{
    method:'PUT',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
    body: JSON.stringify(payload),
  }).then(r=>r.json()).then(()=>{
    mapSettings = payload;
    exitCustomizeArea(true);
    if(mapInst){
      mapInst.setMaxBounds(null);
      mapInst.fitBounds([[payload.min_lat,payload.min_lng],[payload.max_lat,payload.max_lng]],{padding:[20,20]});
      applyMapArea();
    }
    showToast('✅ Map border reset to default.');
    if(typeof loadMapData === 'function') loadMapData();
    if(typeof refreshDashboardMiniMap === 'function') refreshDashboardMiniMap();
  }).catch(()=>showToast('⚠ Could not reset map area.'));
}

/* ══════════════ SUBDIVISION BLOCK PARTITIONS & EDITING ══════════════ */
let layerBlocks = null;
let blocksData = [];
let blockPolygons = {}; // key: block_number => L.polygon
let blockLabelMarkers = {}; // key: block_number => L.marker
let blockStartMarkers = {}; // key: block_number => L.marker
let editingBlocks = false;
let blockEditorMode = 'scale'; // 'scale' (Word document 4-corner resizing) or 'points' (vertex movement)
let selectedBlockNumber = null;
let blockVertexMarkers = [];
let blockScaleHandles = {}; // nw, ne, se, sw, n, s, e, w, center
let blockScaleRect = null;
let scaleDragSnapshot = null;
let activeFilterBlock = '';

async function loadBlocksData() {
  try {
    const data = await apiGet('/api/map/blocks');
    blocksData = Array.isArray(data) ? data : [];
    if (mapInst && !layerBlocks) {
      layerBlocks = L.layerGroup();
      if (document.getElementById('layer-blocks')?.checked) {
        layerBlocks.addTo(mapInst);
      }
    }
    renderBlockPolygons();
  } catch(e) {
    console.error('Failed to load blocks:', e);
  }
}

function getBlockStartInfo(b) {
  const lbl = String(b.start_label || '').trim();
  let code = 'N';
  let label = 'North';
  let arrow = '↑';

  if (/east/i.test(lbl)) {
    code = 'E';
    label = 'East';
    arrow = '→';
  } else if (/west/i.test(lbl)) {
    code = 'W';
    label = 'West';
    arrow = '←';
  } else if (/north/i.test(lbl)) {
    code = 'N';
    label = 'North';
    arrow = '↑';
  } else if (/south/i.test(lbl)) {
    code = 'S';
    label = 'South';
    arrow = '↓';
  } else if (String(b.block_number) === '1') {
    code = 'E';
    label = 'East';
    arrow = '→';
  } else if (String(b.block_number) === '2') {
    code = 'E';
    label = 'East';
    arrow = '→';
  }

  const matchLot = lbl.match(/Lot\s*(\d+)/i);
  const startLot = matchLot ? `Lot ${matchLot[1]}` : 'Lot 1';

  return { code, label, arrow, startLot };
}

/* ── MAP LAYER TOGGLE HANDLERS (ADMIN) ── */
function toggleMapBlocks(show) {
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('layer-blocks')?.checked);
  const toggle = document.getElementById('layer-blocks');
  if (toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  if (!mapInst) return;
  if (!layerBlocks) {
    layerBlocks = L.layerGroup();
  }
  if (isChecked) {
    if (!mapInst.hasLayer(layerBlocks)) mapInst.addLayer(layerBlocks);
    if (!blocksData || !blocksData.length) loadBlocksData();
  } else {
    if (mapInst.hasLayer(layerBlocks)) mapInst.removeLayer(layerBlocks);
  }
}

function toggleMapFacilities(show) {
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('layer-facilities')?.checked);
  const toggle = document.getElementById('layer-facilities');
  if (toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  if (!mapInst) return;
  if (!layerFacilities) {
    layerFacilities = L.layerGroup();
  }
  if (isChecked) {
    if (!mapInst.hasLayer(layerFacilities)) mapInst.addLayer(layerFacilities);
  } else {
    if (mapInst.hasLayer(layerFacilities)) mapInst.removeLayer(layerFacilities);
  }
}

function toggleMapIssues(show) {
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('layer-issues')?.checked);
  const toggle = document.getElementById('layer-issues');
  if (toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  if (!mapInst) return;
  if (!layerIssues) {
    layerIssues = L.layerGroup();
  }
  if (isChecked) {
    if (!mapInst.hasLayer(layerIssues)) mapInst.addLayer(layerIssues);
  } else {
    if (mapInst.hasLayer(layerIssues)) mapInst.removeLayer(layerIssues);
  }
}

function toggleMapHeatmap(show) {
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('layer-heat')?.checked);
  const toggle = document.getElementById('layer-heat');
  if (toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  const analysisCard = document.getElementById('heatmap-analysis-card');
  if (!mapInst) return;
  if (!layerHeat) {
    layerHeat = L.layerGroup();
  }
  if (isChecked) {
    if (!mapInst.hasLayer(layerHeat)) mapInst.addLayer(layerHeat);
    if (analysisCard) analysisCard.style.display = 'block';
    renderIssueHeatmap();
  } else {
    if (mapInst.hasLayer(layerHeat)) mapInst.removeLayer(layerHeat);
    if (analysisCard) analysisCard.style.display = 'none';
  }
}

window.toggleMapBlocks = toggleMapBlocks;
window.toggleMapFacilities = toggleMapFacilities;
window.toggleMapIssues = toggleMapIssues;
window.toggleMapHeatmap = toggleMapHeatmap;

function renderBlockPolygons() {
  if (!mapInst) return;
  if (window.SubUI && SubUI.enableDynamicPinScaling) SubUI.enableDynamicPinScaling(mapInst);
  if (!layerBlocks) {
    layerBlocks = L.layerGroup();
    if (document.getElementById('layer-blocks')?.checked && mapInst) {
      layerBlocks.addTo(mapInst);
    }
  }
  layerBlocks.clearLayers();
  blockPolygons = {};
  blockLabelMarkers = {};
  blockStartMarkers = {};

  blocksData.forEach(b => {
    if (!b.coordinates || !b.coordinates.length) return;
    const isFiltered = activeFilterBlock && String(activeFilterBlock) !== String(b.block_number);
    const isBlock3 = String(b.block_number) === '3';
    const isBlock2 = String(b.block_number) === '2';

    const poly = L.polygon(b.coordinates, {
      color: b.color || '#2563eb',
      weight: isFiltered ? 1 : (isBlock3 ? 2.5 : 2.5),
      dashArray: isBlock3 ? '6 4' : null,
      opacity: isFiltered ? 0.2 : 0.9,
      fillColor: b.color || '#2563eb',
      fillOpacity: isFiltered ? 0.03 : (isBlock3 ? 0.08 : 0.18),
      className: 'subdivision-block-poly'
    });

    if (isBlock3 && poly.bringToBack) {
      poly.bringToBack();
    }

    const center = poly.getBounds().getCenter();
    const labelPos = (b.label_lat && b.label_lng) ? [b.label_lat, b.label_lng] : center;
    b.label_lat = labelPos.lat !== undefined ? +labelPos.lat.toFixed(7) : +labelPos[0].toFixed(7);
    b.label_lng = labelPos.lng !== undefined ? +labelPos.lng.toFixed(7) : +labelPos[1].toFixed(7);

    // Centered block label badge
    const blockDisplayName = isBlock2 ? '🏛 Clubhouse & Pool' : b.name;
    const lotCountText = isBlock2 ? 'Lot 15' : (b.total_lots ? `${b.total_lots} Lots` : '');
    const labelTitle = editingBlocks
      ? `${blockDisplayName}&#10;• Drag to move label&#10;• Click to edit block details`
      : `${blockDisplayName}${lotCountText ? ' • ' + lotCountText : ''}&#10;• Click to view block details`;

    const labelIcon = L.divIcon({
      className: 'sub-block-div-icon',
      html: `<div class="sub-block-pill${isBlock2 ? ' sub-block-pill-clubhouse' : ''}" style="background:${b.color || '#2563eb'};" title="${labelTitle}"><span class="sub-block-name">${blockDisplayName}</span>${lotCountText ? `<span class="sub-block-count">• ${lotCountText}</span>` : ''}</div>`,
      iconSize: [0, 0],
      iconAnchor: [0, 0]
    });
    const labelMarker = L.marker([b.label_lat, b.label_lng], {
      icon: labelIcon,
      draggable: editingBlocks,
      zIndexOffset: 1200
    });

    labelMarker.on('click', function(e) {
      L.DomEvent.stopPropagation(e);
      if (editingBlocks) {
        if (blockEditorMode === 'scale') {
          selectBlockForScale(b.block_number);
        }
        openEditBlockModal(b.block_number);
      } else {
        poly.openPopup(labelMarker.getLatLng());
      }
    });

    if (editingBlocks) {
      labelMarker.on('drag', function(ev) {
        const pos = ev.target.getLatLng();
        b.label_lat = +pos.lat.toFixed(7);
        b.label_lng = +pos.lng.toFixed(7);
      });
      labelMarker.on('dragend', function() {
        showToast(`Moved ${b.name} label.`);
      });
    }

    // Starting point directional marker (N, E, or W arrow)
    let startMarker = null;
    if (b.start_lat && b.start_lng) {
      const info = getBlockStartInfo(b);
      const startTitle = editingBlocks
        ? `${b.name} Starting Point: ${info.startLot} (${info.label})&#10;• Drag to move START point&#10;• Click to edit details`
        : `${b.name} • Starting ${info.startLot} (${info.label})`;

      const startIcon = L.divIcon({
        className: 'sub-block-div-icon',
        html: `<div class="sub-start-dir-marker" style="border-color:${b.color || '#ffffff'};" title="${startTitle}"><span class="start-dir-arrow">${info.arrow}</span><span class="start-dir-code">${info.code}</span></div>`,
        iconSize: [0, 0],
        iconAnchor: [0, 0]
      });

      startMarker = L.marker([b.start_lat, b.start_lng], {
        icon: startIcon,
        draggable: editingBlocks,
        zIndexOffset: 1350
      });

      const startPopupHtml = `
        <div class="sub-start-popup" style="min-width:145px;padding:3px 2px;">
          <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;padding-bottom:4px;border-bottom:1px solid rgba(148,163,184,0.25);">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:${b.color || '#0f172a'};color:#fff;font-size:10px;font-weight:800;">${info.code}</span>
            <div>
              <strong style="font-size:12px;color:var(--text, #0f172a);display:block;line-height:1.2;">${b.name}</strong>
              <span style="font-size:10px;color:var(--text-dim, #64748b);">Starting Point</span>
            </div>
          </div>
          <div style="font-size:11px;line-height:1.5;color:var(--text, #334155);">
            <div style="display:flex;justify-content:space-between;gap:8px;">
              <span style="color:var(--text-dim, #64748b);">Starting Lot:</span>
              <strong style="color:var(--text, #0f172a);">${info.startLot}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;gap:8px;margin-top:2px;">
              <span style="color:var(--text-dim, #64748b);">Starting Direction:</span>
              <strong style="color:var(--text, #0f172a);">${info.label} (${info.code})</strong>
            </div>
            ${b.total_lots ? `
            <div style="display:flex;justify-content:space-between;gap:8px;margin-top:2px;padding-top:2px;border-top:1px dashed rgba(148,163,184,0.2);">
              <span style="color:var(--text-dim, #64748b);">Total Lots:</span>
              <span style="font-weight:600;">${b.total_lots} lots</span>
            </div>` : ''}
          </div>
        </div>
      `;
      startMarker.bindPopup(startPopupHtml, { maxWidth: 220, offset: [0, -4] });

      startMarker.on('click', function(e) {
        if (editingBlocks) {
          L.DomEvent.stopPropagation(e);
          if (blockEditorMode === 'scale') {
            selectBlockForScale(b.block_number);
          }
          openEditBlockModal(b.block_number);
        }
      });

      if (editingBlocks) {
        startMarker.on('drag', function(ev) {
          const pos = ev.target.getLatLng();
          b.start_lat = +pos.lat.toFixed(7);
          b.start_lng = +pos.lng.toFixed(7);
        });
        startMarker.on('dragend', function() {
          showToast(`Moved START point for ${b.name}.`);
        });
      }
    }

    // Popup content
    const lotRangeHtml = b.lot_range 
      ? `<div style="font-size:12px;font-weight:700;color:#1e293b;margin:4px 0 2px;">Lot Range: <strong>${b.lot_range}</strong> (${b.total_lots || b.households_count} total lots)</div>` 
      : '';
    const popupContent = `
      <div style="min-width:190px;">
        <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
          <span style="width:12px;height:12px;border-radius:3px;background:${b.color};display:inline-block;"></span>
          <strong style="font-size:13px;">${b.name}</strong>
        </div>
        ${lotRangeHtml}
        <div style="font-size:11px;color:#64748b;margin-bottom:6px;">${b.description || ''}</div>
        <div style="font-size:11px;margin-top:6px;padding:4px 0;border-top:1px solid #e2e8f0;">
          <strong>SubSync Households:</strong> ${b.households_count} (${b.occupied_count} Occupied, ${b.vacant_count} Vacant)
        </div>
        <div style="font-size:11px;color:#475569;margin-top:2px;">
          <strong>Numbering:</strong> Starts at ${b.start_label || 'Lot 1'}
        </div>
        <button onclick="openEditBlockModal('${b.block_number}')" style="margin-top:6px;width:100%;padding:4px 8px;font-size:11px;border-radius:4px;border:1px solid #0284c7;background:#0284c7;color:#fff;cursor:pointer;font-weight:700;">✎ Edit Block & START Details</button>
        <button onclick="filterMapByBlock('${b.block_number}')" style="margin-top:6px;width:100%;padding:4px 8px;font-size:11px;border-radius:4px;border:1px solid ${b.color};background:${b.color};color:#fff;cursor:pointer;font-weight:700;">Filter to Lots in ${b.name}</button>
      </div>
    `;
    poly.bindPopup(popupContent);

    poly.on('click', function(e) {
      if (addFacilityMode || document.getElementById('modal-addFacility')?.classList.contains('open')) {
        if (poly.closePopup) poly.closePopup();
        handleMapClickForFacility(e.latlng);
        return;
      }
      if (!editingBlocks) return;
      L.DomEvent.stopPropagation(e);
      if (poly.closePopup) poly.closePopup();

      if (blockEditorMode === 'scale') {
        selectBlockForScale(b.block_number);
        return;
      }

      const clicked = e.latlng;
      const bestIdx = findClosestSegment(clicked, b.coordinates);
      const newPt = [+clicked.lat.toFixed(7), +clicked.lng.toFixed(7)];
      b.coordinates.splice(bestIdx + 1, 0, newPt);
      poly.setLatLngs(b.coordinates);
      createBlockVertexHandles();
      showToast(`+ Added movement point to ${b.name} (${b.coordinates.length} total points)`);
    });

    poly.addTo(layerBlocks);
    if (!isFiltered) {
      labelMarker.addTo(layerBlocks);
      if (startMarker) startMarker.addTo(layerBlocks);
    }

    blockPolygons[b.block_number] = poly;
    blockLabelMarkers[b.block_number] = labelMarker;
    if (startMarker) blockStartMarkers[b.block_number] = startMarker;
  });

  const blockToggle = document.getElementById('layer-blocks');
  if (blockToggle && mapInst && layerBlocks) {
    if (blockToggle.checked) {
      if (!mapInst.hasLayer(layerBlocks)) mapInst.addLayer(layerBlocks);
    } else {
      if (mapInst.hasLayer(layerBlocks)) mapInst.removeLayer(layerBlocks);
    }
  }
}

function findClosestSegment(latlng, coords) {
  let minDist = Infinity;
  let bestIdx = 0;
  const n = coords.length;
  for (let i = 0; i < n; i++) {
    const p1 = coords[i];
    const p2 = coords[(i + 1) % n];
    const dy = p2[0] - p1[0];
    const dx = p2[1] - p1[1];
    const lenSq = dy * dy + dx * dx;
    let t = lenSq === 0 ? 0 : ((latlng.lat - p1[0]) * dy + (latlng.lng - p1[1]) * dx) / lenSq;
    t = Math.max(0, Math.min(1, t));
    const projLat = p1[0] + t * dy;
    const projLng = p1[1] + t * dx;
    const dist = Math.hypot(latlng.lat - projLat, latlng.lng - projLng);
    if (dist < minDist) {
      minDist = dist;
      bestIdx = i;
    }
  }
  return bestIdx;
}

function filterMapByBlock(blockNum) {
  activeFilterBlock = blockNum || '';
  const sel = document.getElementById('filter-block-select');
  if (sel) sel.value = activeFilterBlock;

  // Auto-check and display block partitions when filtering to a specific block
  if (activeFilterBlock) {
    const blockToggle = document.getElementById('layer-blocks');
    if (blockToggle && !blockToggle.checked) {
      blockToggle.checked = true;
      if (layerBlocks && mapInst && !mapInst.hasLayer(layerBlocks)) {
        mapInst.addLayer(layerBlocks);
      }
    }
  }

  renderBlockPolygons();
  if (typeof renderFilteredHouseholds === 'function') {
    renderFilteredHouseholds();
  }

  if (editingBlocks) {
    if (activeFilterBlock) {
      selectedBlockNumber = activeFilterBlock;
    }
    if (blockEditorMode === 'scale') {
      createBlockTransformHandles();
    } else {
      createBlockVertexHandles();
    }
  }

  if (activeFilterBlock && blockPolygons[activeFilterBlock]) {
    const b = blockPolygons[activeFilterBlock].getBounds();
    mapInst.fitBounds(b, { padding: [40, 40], maxZoom: 19 });
  } else if (areaRect) {
    mapInst.fitBounds(areaRect.getBounds(), { padding: [20, 20] });
  }
}

function enterEditBlocksMode() {
  if (typeof cancelCustomizeArea === 'function' && customizingArea) cancelCustomizeArea();
  if (typeof cancelAddFacilityMode === 'function' && addFacilityMode) cancelAddFacilityMode();

  editingBlocks = true;
  document.getElementById('btn-edit-blocks').style.display = 'none';
  document.getElementById('btn-customize-area').style.display = 'none';
  document.getElementById('map-blocks-toolbar').style.display = 'flex';

  if (mapInst) {
    mapInst.setMaxBounds(null);
    mapInst.getContainer().classList.add('editing-blocks-active');
  }

  const blockToggle = document.getElementById('layer-blocks');
  if (blockToggle && !blockToggle.checked) {
    blockToggle.checked = true;
    if (layerBlocks && mapInst && !mapInst.hasLayer(layerBlocks)) mapInst.addLayer(layerBlocks);
  }

  renderBlockPolygons();
  setBlockEditorMode('points');
  showToast('Edit Blocks: Drag points to reshape, click (+) to add points. Drag labels or START pins to move them.');
}

function createBlockVertexHandles() {
  destroyBlockVertexHandles();

  const targets = activeFilterBlock
    ? blocksData.filter(b => String(b.block_number) === String(activeFilterBlock))
    : blocksData;

  targets.forEach(b => {
    if (!b.coordinates || b.coordinates.length < 3) return;
    const n = b.coordinates.length;

    // 1. Draggable Vertex Movement Handles
    b.coordinates.forEach((pt, vertexIdx) => {
      const handleIcon = L.divIcon({
        className: 'sub-block-div-icon',
        html: `<div class="block-vertex-handle" title="${b.name} (Point #${vertexIdx + 1} of ${n})&#10;• Drag to move point&#10;• Right-click or double-click to delete point" style="background:${b.color};"></div>`,
        iconSize: [14, 14],
        iconAnchor: [7, 7]
      });

      const m = L.marker([pt[0], pt[1]], {
        icon: handleIcon,
        draggable: true,
        zIndexOffset: 1600
      }).addTo(mapInst);

      m.on('drag', function(ev) {
        const newPos = ev.target.getLatLng();
        pt[0] = +newPos.lat.toFixed(7);
        pt[1] = +newPos.lng.toFixed(7);
        if (blockPolygons[b.block_number]) {
          blockPolygons[b.block_number].setLatLngs(b.coordinates);
        }
      });

      m.on('dragend', function() {
        createBlockVertexHandles();
      });

      const deletePoint = function(ev) {
        if (ev) {
          L.DomEvent.stopPropagation(ev);
          if (ev.originalEvent) ev.originalEvent.preventDefault();
        }
        if (b.coordinates.length <= 3) {
          showToast('⚠ A block polygon must have at least 3 points.');
          return;
        }
        b.coordinates.splice(vertexIdx, 1);
        if (blockPolygons[b.block_number]) {
          blockPolygons[b.block_number].setLatLngs(b.coordinates);
        }
        createBlockVertexHandles();
        showToast(`Point removed from ${b.name} (${b.coordinates.length} points remaining).`);
      };

      m.on('contextmenu', deletePoint);
      m.on('dblclick', deletePoint);

      blockVertexMarkers.push(m);
    });

    // 2. Midpoint "+" Handles Between Consecutive Vertices
    for (let i = 0; i < n; i++) {
      const p1 = b.coordinates[i];
      const p2 = b.coordinates[(i + 1) % n];
      const midLat = (p1[0] + p2[0]) / 2;
      const midLng = (p1[1] + p2[1]) / 2;
      const nextIdx = (i + 1) % n;

      const midIcon = L.divIcon({
        className: 'sub-block-div-icon',
        html: `<div class="block-midpoint-handle" title="Click to add a point between #${i + 1} and #${nextIdx + 1}" style="border-color:${b.color}; color:${b.color};"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></div>`,
        iconSize: [14, 14],
        iconAnchor: [7, 7]
      });

      const midMarker = L.marker([midLat, midLng], {
        icon: midIcon,
        zIndexOffset: 1400
      }).addTo(mapInst);

      midMarker.on('click', function(ev) {
        L.DomEvent.stopPropagation(ev);
        const insertIdx = i + 1;
        b.coordinates.splice(insertIdx, 0, [+midLat.toFixed(7), +midLng.toFixed(7)]);
        if (blockPolygons[b.block_number]) {
          blockPolygons[b.block_number].setLatLngs(b.coordinates);
        }
        createBlockVertexHandles();
        showToast(`+ Added movement point to ${b.name} (now ${b.coordinates.length} points)`);
      });

      blockVertexMarkers.push(midMarker);
    }
  });
}

function destroyBlockVertexHandles() {
  blockVertexMarkers.forEach(m => {
    try { m.remove(); } catch(e){}
  });
  blockVertexMarkers = [];
}

function setBlockEditorMode(mode) {
  blockEditorMode = mode || 'points';
  const btnScale = document.getElementById('btn-block-mode-scale');
  const btnPoints = document.getElementById('btn-block-mode-points');
  const hint = document.getElementById('block-edit-hint');
  const btnSubdivide = document.getElementById('btn-subdivide-block');

  if (blockEditorMode === 'scale') {
    if (btnScale) {
      btnScale.style.background = 'var(--accent)';
      btnScale.style.color = '#fff';
      btnScale.style.fontWeight = '700';
    }
    if (btnPoints) {
      btnPoints.style.background = 'transparent';
      btnPoints.style.color = 'var(--text)';
      btnPoints.style.fontWeight = '600';
    }
    if (hint) {
      hint.textContent = 'Drag any of the 4 corners to scale smaller/larger. Click any block to select it.';
    }
    if (btnSubdivide) btnSubdivide.style.display = 'none';
    destroyBlockVertexHandles();
    createBlockTransformHandles();
  } else {
    if (btnPoints) {
      btnPoints.style.background = 'var(--accent)';
      btnPoints.style.color = '#fff';
      btnPoints.style.fontWeight = '700';
    }
    if (btnScale) {
      btnScale.style.background = 'transparent';
      btnScale.style.color = 'var(--text)';
      btnScale.style.fontWeight = '600';
    }
    if (hint) {
      hint.textContent = 'Drag movement points, click midpoint (+) handles to add points, right-click to delete.';
    }
    if (btnSubdivide) btnSubdivide.style.display = 'inline-flex';
    destroyBlockTransformHandles();
    createBlockVertexHandles();
  }
}

function selectBlockForScale(blockNum) {
  selectedBlockNumber = String(blockNum);
  const target = blocksData.find(b => String(b.block_number) === String(blockNum));
  createBlockTransformHandles();
  if (target) {
    showToast(`Selected ${target.name} (drag 4 corners to scale)`);
  }
}

function createBlockTransformHandles() {
  destroyBlockTransformHandles();
  if (!mapInst || !editingBlocks || blockEditorMode !== 'scale') return;

  if (!selectedBlockNumber) {
    selectedBlockNumber = activeFilterBlock || (blocksData[0] ? String(blocksData[0].block_number) : null);
  }

  const b = blocksData.find(item => String(item.block_number) === String(selectedBlockNumber));
  if (!b || !b.coordinates || b.coordinates.length < 3) return;

  const color = b.color || '#2563eb';
  const coords = b.coordinates;
  let minLat = Infinity, maxLat = -Infinity;
  let minLng = Infinity, maxLng = -Infinity;
  for (let i = 0; i < coords.length; i++) {
    if (coords[i][0] < minLat) minLat = coords[i][0];
    if (coords[i][0] > maxLat) maxLat = coords[i][0];
    if (coords[i][1] < minLng) minLng = coords[i][1];
    if (coords[i][1] > maxLng) maxLng = coords[i][1];
  }
  const midLat = (minLat + maxLat) / 2;
  const midLng = (minLng + maxLng) / 2;

  // Bounding box outline rectangle
  blockScaleRect = L.rectangle([[minLat, minLng], [maxLat, maxLng]], {
    color: color,
    weight: 1.5,
    dashArray: '5 4',
    fillColor: color,
    fillOpacity: 0.04,
    interactive: false
  }).addTo(mapInst);

  function makeHandle(lat, lng, html, iconSize, iconAnchor, handleType) {
    const icon = L.divIcon({
      className: 'sub-block-div-icon',
      html: html,
      iconSize: iconSize,
      iconAnchor: iconAnchor
    });
    const marker = L.marker([lat, lng], {
      icon: icon,
      draggable: true,
      zIndexOffset: handleType === 'center' ? 1800 : 2000
    }).addTo(mapInst);

    marker.on('dragstart', function() {
      startScaleDrag(handleType, b);
    });

    marker.on('drag', function(ev) {
      if (!scaleDragSnapshot) return;
      handleScaleDrag(ev, handleType);
    });

    marker.on('dragend', function() {
      if (scaleDragSnapshot) {
        updateScaleHandlePositions(b);
        scaleDragSnapshot = null;
        showToast(`Resized ${b.name}`);
      }
    });

    return marker;
  }

  // 4 Corner scaling handles (Word document style)
  blockScaleHandles.nw = makeHandle(
    maxLat, minLng,
    `<div class="block-scale-handle block-scale-handle-corner" style="cursor:nwse-resize;border-color:${color};" title="Drag to scale ${b.name} (NW corner)"></div>`,
    [14, 14], [7, 7], 'nw'
  );

  blockScaleHandles.ne = makeHandle(
    maxLat, maxLng,
    `<div class="block-scale-handle block-scale-handle-corner" style="cursor:nesw-resize;border-color:${color};" title="Drag to scale ${b.name} (NE corner)"></div>`,
    [14, 14], [7, 7], 'ne'
  );

  blockScaleHandles.se = makeHandle(
    minLat, maxLng,
    `<div class="block-scale-handle block-scale-handle-corner" style="cursor:nwse-resize;border-color:${color};" title="Drag to scale ${b.name} (SE corner)"></div>`,
    [14, 14], [7, 7], 'se'
  );

  blockScaleHandles.sw = makeHandle(
    minLat, minLng,
    `<div class="block-scale-handle block-scale-handle-corner" style="cursor:nesw-resize;border-color:${color};" title="Drag to scale ${b.name} (SW corner)"></div>`,
    [14, 14], [7, 7], 'sw'
  );

  // 4 Edge stretch handles (N, S, E, W)
  blockScaleHandles.n = makeHandle(
    maxLat, midLng,
    `<div class="block-scale-handle block-scale-handle-edge-ns" style="cursor:ns-resize;border-color:${color};" title="Drag to scale height (North edge)"></div>`,
    [20, 9], [10, 4.5], 'n'
  );

  blockScaleHandles.s = makeHandle(
    minLat, midLng,
    `<div class="block-scale-handle block-scale-handle-edge-ns" style="cursor:ns-resize;border-color:${color};" title="Drag to scale height (South edge)"></div>`,
    [20, 9], [10, 4.5], 's'
  );

  blockScaleHandles.e = makeHandle(
    midLat, maxLng,
    `<div class="block-scale-handle block-scale-handle-edge-ew" style="cursor:ew-resize;border-color:${color};" title="Drag to scale width (East edge)"></div>`,
    [9, 20], [4.5, 10], 'e'
  );

  blockScaleHandles.w = makeHandle(
    midLat, minLng,
    `<div class="block-scale-handle block-scale-handle-edge-ew" style="cursor:ew-resize;border-color:${color};" title="Drag to scale width (West edge)"></div>`,
    [9, 20], [4.5, 10], 'w'
  );

  // Center move/translation handle
  blockScaleHandles.center = makeHandle(
    midLat, midLng,
    `<div class="block-scale-center" style="background:${color};" title="Drag to move entire ${b.name}"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:4px;"><polyline points="5 9 2 12 5 15"></polyline><polyline points="9 5 12 2 15 5"></polyline><polyline points="15 19 12 22 9 19"></polyline><polyline points="19 9 22 12 19 15"></polyline><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line></svg>${b.name} (Move)</div>`,
    [120, 22], [60, 11], 'center'
  );
}

function startScaleDrag(handleType, b) {
  const coords = b.coordinates;
  let minLat = Infinity, maxLat = -Infinity;
  let minLng = Infinity, maxLng = -Infinity;
  for (let i = 0; i < coords.length; i++) {
    if (coords[i][0] < minLat) minLat = coords[i][0];
    if (coords[i][0] > maxLat) maxLat = coords[i][0];
    if (coords[i][1] < minLng) minLng = coords[i][1];
    if (coords[i][1] > maxLng) maxLng = coords[i][1];
  }
  const width = Math.max(maxLng - minLng, 0.00001);
  const height = Math.max(maxLat - minLat, 0.00001);

  const normalized = coords.map(pt => [
    (pt[1] - minLng) / width,
    (pt[0] - minLat) / height
  ]);

  let startNormalized = null;
  if (b.start_lat && b.start_lng) {
    startNormalized = [
      (b.start_lng - minLng) / width,
      (b.start_lat - minLat) / height
    ];
  }

  const curLabelLat = (b.label_lat !== undefined && b.label_lat !== null) ? b.label_lat : ((minLat + maxLat) / 2);
  const curLabelLng = (b.label_lng !== undefined && b.label_lng !== null) ? b.label_lng : ((minLng + maxLng) / 2);
  const labelNormalized = [
    (curLabelLng - minLng) / width,
    (curLabelLat - minLat) / height
  ];

  scaleDragSnapshot = {
    handleType,
    block: b,
    origMinLat: minLat,
    origMaxLat: maxLat,
    origMinLng: minLng,
    origMaxLng: maxLng,
    origWidth: width,
    origHeight: height,
    normalized,
    startNormalized,
    labelNormalized,
    origCenter: [(minLat + maxLat) / 2, (minLng + maxLng) / 2]
  };
}

function handleScaleDrag(ev, handleType) {
  const snap = scaleDragSnapshot;
  if (!snap) return;

  const curPos = ev.target.getLatLng();
  const curLat = curPos.lat;
  const curLng = curPos.lng;

  let newMinLat = snap.origMinLat;
  let newMaxLat = snap.origMaxLat;
  let newMinLng = snap.origMinLng;
  let newMaxLng = snap.origMaxLng;

  const minSpan = 0.00003; // ~3m minimum dimension

  switch (handleType) {
    case 'se':
      newMinLat = Math.min(curLat, snap.origMaxLat - minSpan);
      newMaxLng = Math.max(curLng, snap.origMinLng + minSpan);
      break;
    case 'nw':
      newMaxLat = Math.max(curLat, snap.origMinLat + minSpan);
      newMinLng = Math.min(curLng, snap.origMaxLng - minSpan);
      break;
    case 'ne':
      newMaxLat = Math.max(curLat, snap.origMinLat + minSpan);
      newMaxLng = Math.max(curLng, snap.origMinLng + minSpan);
      break;
    case 'sw':
      newMinLat = Math.min(curLat, snap.origMaxLat - minSpan);
      newMinLng = Math.min(curLng, snap.origMaxLng - minSpan);
      break;
    case 'n':
      newMaxLat = Math.max(curLat, snap.origMinLat + minSpan);
      break;
    case 's':
      newMinLat = Math.min(curLat, snap.origMaxLat - minSpan);
      break;
    case 'e':
      newMaxLng = Math.max(curLng, snap.origMinLng + minSpan);
      break;
    case 'w':
      newMinLng = Math.min(curLng, snap.origMaxLng - minSpan);
      break;
    case 'center':
      const dLat = curLat - snap.origCenter[0];
      const dLng = curLng - snap.origCenter[1];
      newMinLat = snap.origMinLat + dLat;
      newMaxLat = snap.origMaxLat + dLat;
      newMinLng = snap.origMinLng + dLng;
      newMaxLng = snap.origMaxLng + dLng;
      break;
  }

  const newWidth = Math.max(newMaxLng - newMinLng, minSpan);
  const newHeight = Math.max(newMaxLat - newMinLat, minSpan);

  for (let i = 0; i < snap.normalized.length; i++) {
    const u = snap.normalized[i][0];
    const v = snap.normalized[i][1];
    snap.block.coordinates[i] = [
      +(newMinLat + v * newHeight).toFixed(7),
      +(newMinLng + u * newWidth).toFixed(7)
    ];
  }

  if (snap.startNormalized) {
    snap.block.start_lat = +(newMinLat + snap.startNormalized[1] * newHeight).toFixed(7);
    snap.block.start_lng = +(newMinLng + snap.startNormalized[0] * newWidth).toFixed(7);
    if (blockStartMarkers[snap.block.block_number]) {
      blockStartMarkers[snap.block.block_number].setLatLng([snap.block.start_lat, snap.block.start_lng]);
    }
  }

  if (snap.labelNormalized) {
    snap.block.label_lat = +(newMinLat + snap.labelNormalized[1] * newHeight).toFixed(7);
    snap.block.label_lng = +(newMinLng + snap.labelNormalized[0] * newWidth).toFixed(7);
    if (blockLabelMarkers[snap.block.block_number]) {
      blockLabelMarkers[snap.block.block_number].setLatLng([snap.block.label_lat, snap.block.label_lng]);
    }
  }

  if (blockPolygons[snap.block.block_number]) {
    blockPolygons[snap.block.block_number].setLatLngs(snap.block.coordinates);
  }

  if (blockScaleRect) {
    blockScaleRect.setBounds([[newMinLat, newMinLng], [newMaxLat, newMaxLng]]);
  }

  const midLat = (newMinLat + newMaxLat) / 2;
  const midLng = (newMinLng + newMaxLng) / 2;
  if (handleType !== 'nw' && blockScaleHandles.nw) blockScaleHandles.nw.setLatLng([newMaxLat, newMinLng]);
  if (handleType !== 'ne' && blockScaleHandles.ne) blockScaleHandles.ne.setLatLng([newMaxLat, newMaxLng]);
  if (handleType !== 'se' && blockScaleHandles.se) blockScaleHandles.se.setLatLng([newMinLat, newMaxLng]);
  if (handleType !== 'sw' && blockScaleHandles.sw) blockScaleHandles.sw.setLatLng([newMinLat, newMinLng]);
  if (handleType !== 'n' && blockScaleHandles.n) blockScaleHandles.n.setLatLng([newMaxLat, midLng]);
  if (handleType !== 's' && blockScaleHandles.s) blockScaleHandles.s.setLatLng([newMinLat, midLng]);
  if (handleType !== 'e' && blockScaleHandles.e) blockScaleHandles.e.setLatLng([midLat, newMaxLng]);
  if (handleType !== 'w' && blockScaleHandles.w) blockScaleHandles.w.setLatLng([midLat, newMinLng]);
  if (handleType !== 'center' && blockScaleHandles.center) blockScaleHandles.center.setLatLng([midLat, midLng]);
}

function updateScaleHandlePositions(b) {
  if (!b || !b.coordinates || !b.coordinates.length) return;
  const coords = b.coordinates;
  let minLat = Infinity, maxLat = -Infinity;
  let minLng = Infinity, maxLng = -Infinity;
  for (let i = 0; i < coords.length; i++) {
    if (coords[i][0] < minLat) minLat = coords[i][0];
    if (coords[i][0] > maxLat) maxLat = coords[i][0];
    if (coords[i][1] < minLng) minLng = coords[i][1];
    if (coords[i][1] > maxLng) maxLng = coords[i][1];
  }
  const midLat = (minLat + maxLat) / 2;
  const midLng = (minLng + maxLng) / 2;

  if (blockScaleHandles.nw) blockScaleHandles.nw.setLatLng([maxLat, minLng]);
  if (blockScaleHandles.ne) blockScaleHandles.ne.setLatLng([maxLat, maxLng]);
  if (blockScaleHandles.se) blockScaleHandles.se.setLatLng([minLat, maxLng]);
  if (blockScaleHandles.sw) blockScaleHandles.sw.setLatLng([minLat, minLng]);
  if (blockScaleHandles.n) blockScaleHandles.n.setLatLng([maxLat, midLng]);
  if (blockScaleHandles.s) blockScaleHandles.s.setLatLng([minLat, midLng]);
  if (blockScaleHandles.e) blockScaleHandles.e.setLatLng([midLat, maxLng]);
  if (blockScaleHandles.w) blockScaleHandles.w.setLatLng([midLat, minLng]);
  if (blockScaleHandles.center) blockScaleHandles.center.setLatLng([midLat, midLng]);

  if (blockScaleRect) {
    blockScaleRect.setBounds([[minLat, minLng], [maxLat, maxLng]]);
  }
}

function destroyBlockTransformHandles() {
  Object.values(blockScaleHandles).forEach(m => {
    try { if (m) m.remove(); } catch(e){}
  });
  blockScaleHandles = {};
  if (blockScaleRect) {
    try { blockScaleRect.remove(); } catch(e){}
    blockScaleRect = null;
  }
}

function subdivideBlockPoints() {
  if (!editingBlocks) return;
  const targets = activeFilterBlock
    ? blocksData.filter(b => String(b.block_number) === String(activeFilterBlock))
    : blocksData;

  if (!targets.length) return;

  targets.forEach(b => {
    if (!b.coordinates || b.coordinates.length < 3) return;
    const n = b.coordinates.length;
    const newCoords = [];
    for (let i = 0; i < n; i++) {
      const p1 = b.coordinates[i];
      const p2 = b.coordinates[(i + 1) % n];
      newCoords.push(p1);
      newCoords.push([
        +((p1[0] + p2[0]) / 2).toFixed(7),
        +((p1[1] + p2[1]) / 2).toFixed(7)
      ]);
    }
    b.coordinates = newCoords;
    if (blockPolygons[b.block_number]) {
      blockPolygons[b.block_number].setLatLngs(b.coordinates);
    }
  });

  createBlockVertexHandles();
  const label = activeFilterBlock ? `Block ${activeFilterBlock}` : 'All blocks';
  showToast(`✅ Subdivided ${label}: added more points of movement!`);
}

function scaleSelectedBlock(factor) {
  if (!editingBlocks) return;
  const targetNum = selectedBlockNumber || activeFilterBlock || (blocksData[0] ? blocksData[0].block_number : null);
  if (!targetNum) {
    showToast('Select a block first to scale it.');
    return;
  }
  const b = blocksData.find(item => String(item.block_number) === String(targetNum));
  if (!b || !b.coordinates || !b.coordinates.length) return;

  applyScaleToBlock(b, factor);
  if (blockEditorMode === 'scale') {
    createBlockTransformHandles();
  } else {
    createBlockVertexHandles();
  }
  showToast(`${factor < 1 ? 'Shrank' : 'Enlarged'} ${b.name} (${Math.round(factor * 100)}%)`);
}

function shrinkAllBlocks() {
  if (!editingBlocks) return;
  blocksData.forEach(b => {
    applyScaleToBlock(b, 0.80);
  });
  if (blockEditorMode === 'scale') {
    createBlockTransformHandles();
  } else {
    createBlockVertexHandles();
  }
  showToast('Shrank all blocks by 20% to leave room between streets.');
}

function applyScaleToBlock(b, factor) {
  const coords = b.coordinates;
  const n = coords.length;
  let sumLat = 0, sumLng = 0;
  for (let i = 0; i < n; i++) {
    sumLat += coords[i][0];
    sumLng += coords[i][1];
  }
  const cLat = sumLat / n;
  const cLng = sumLng / n;

  b.coordinates = coords.map(pt => [
    +(cLat + factor * (pt[0] - cLat)).toFixed(7),
    +(cLng + factor * (pt[1] - cLng)).toFixed(7)
  ]);

  if (b.start_lat && b.start_lng) {
    b.start_lat = +(cLat + factor * (b.start_lat - cLat)).toFixed(7);
    b.start_lng = +(cLng + factor * (b.start_lng - cLng)).toFixed(7);
  }

  if (b.label_lat && b.label_lng) {
    b.label_lat = +(cLat + factor * (b.label_lat - cLat)).toFixed(7);
    b.label_lng = +(cLng + factor * (b.label_lng - cLng)).toFixed(7);
  }

  if (blockPolygons[b.block_number]) {
    blockPolygons[b.block_number].setLatLngs(b.coordinates);
  }
  if (blockStartMarkers[b.block_number] && b.start_lat && b.start_lng) {
    blockStartMarkers[b.block_number].setLatLng([b.start_lat, b.start_lng]);
  }
  if (blockLabelMarkers[b.block_number] && b.label_lat && b.label_lng) {
    blockLabelMarkers[b.block_number].setLatLng([b.label_lat, b.label_lng]);
  }
}

function exitEditBlocksMode() {
  destroyBlockVertexHandles();
  destroyBlockTransformHandles();
  editingBlocks = false;
  selectedBlockNumber = null;
  if (mapInst) {
    mapInst.getContainer().classList.remove('editing-blocks-active');
  }
  document.getElementById('btn-edit-blocks').style.display = 'inline-flex';
  document.getElementById('btn-customize-area').style.display = 'inline-flex';
  document.getElementById('map-blocks-toolbar').style.display = 'none';
  renderBlockPolygons();
  applyMapArea();
}

function cancelEditBlocks() {
  exitEditBlocksMode();
  loadBlocksData();
  showToast('Block editing cancelled.');
}

async function saveBlockPolygons() {
  const payload = {
    blocks: blocksData.map(b => ({
      id: b.id,
      coordinates: b.coordinates,
      start_lat: b.start_lat,
      start_lng: b.start_lng,
      label_lat: b.label_lat,
      label_lng: b.label_lng,
      name: b.name,
      lot_range: b.lot_range,
      total_lots: b.total_lots,
      start_label: b.start_label,
      color: b.color,
      description: b.description
    }))
  };

  try {
    const res = await apiPut('/api/map/blocks', payload);
    if (res && res.success) {
      exitEditBlocksMode();
      renderBlockPolygons();
      showToast('✅ All block partitions saved successfully!');
    } else {
      showToast('⚠ Failed to save blocks.');
    }
  } catch(e) {
    showToast('⚠ Error saving blocks.');
  }
}

function openSelectedBlockInfoModal() {
  const targetNum = selectedBlockNumber || activeFilterBlock || (blocksData[0] ? blocksData[0].block_number : null);
  if (!targetNum) {
    showToast('Select a block first to edit its info.');
    return;
  }
  openEditBlockModal(targetNum);
}

function openEditBlockModal(blockNum) {
  const b = blocksData.find(item => String(item.block_number) === String(blockNum));
  if (!b) return;

  selectedBlockNumber = String(blockNum);
  if (editingBlocks && blockEditorMode === 'scale') {
    createBlockTransformHandles();
  }

  document.getElementById('editBlockId').value = b.id;
  document.getElementById('editBlockNum').value = b.block_number;
  document.getElementById('editBlockModalTitle').textContent = `Edit ${b.name} Details`;
  document.getElementById('editBlockName').value = b.name || '';
  document.getElementById('editBlockColor').value = b.color || '#2563eb';
  document.getElementById('editBlockLotRange').value = b.lot_range || '';
  document.getElementById('editBlockTotalLots').value = b.total_lots || 0;
  document.getElementById('editBlockStartLabel').value = b.start_label || 'Lot 1';
  document.getElementById('editBlockDescription').value = b.description || '';
  document.getElementById('editBlockMsg').textContent = '';

  const modal = document.getElementById('modal-editBlock');
  if (modal) modal.classList.add('open');
}

async function saveBlockDetails() {
  const blockId = document.getElementById('editBlockId').value;
  const blockNum = document.getElementById('editBlockNum').value;
  const b = blocksData.find(item => String(item.id) === String(blockId) || String(item.block_number) === String(blockNum));
  if (!b) return;

  const newName = document.getElementById('editBlockName').value.trim() || b.name;
  const newColor = document.getElementById('editBlockColor').value || b.color;
  const newLotRange = document.getElementById('editBlockLotRange').value.trim();
  const newTotalLots = parseInt(document.getElementById('editBlockTotalLots').value, 10) || 0;
  const newStartLabel = document.getElementById('editBlockStartLabel').value.trim() || 'Lot 1';
  const newDesc = document.getElementById('editBlockDescription').value.trim();

  b.name = newName;
  b.color = newColor;
  b.lot_range = newLotRange;
  b.total_lots = newTotalLots;
  b.start_label = newStartLabel;
  b.description = newDesc;

  renderBlockPolygons();
  if (editingBlocks) {
    if (blockEditorMode === 'scale') {
      createBlockTransformHandles();
    } else {
      createBlockVertexHandles();
    }
  }

  try {
    const payload = {
      name: b.name,
      color: b.color,
      lot_range: b.lot_range,
      total_lots: b.total_lots,
      start_label: b.start_label,
      description: b.description,
      start_lat: b.start_lat,
      start_lng: b.start_lng,
      label_lat: b.label_lat,
      label_lng: b.label_lng,
      coordinates: b.coordinates
    };
    const res = await apiPut(`/api/map/block/${b.id}`, payload);
    if (res && res.success) {
      closeModal('editBlock');
      showToast(`✅ Saved details for ${b.name}`);
      if (typeof refreshDashboardMiniMapData === 'function') refreshDashboardMiniMapData();
    } else {
      document.getElementById('editBlockMsg').textContent = 'Failed to save block details.';
    }
  } catch(e) {
    document.getElementById('editBlockMsg').textContent = 'Error saving block details.';
  }
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
      <td>${e.receipt_url?`<a href="${e.receipt_url + (e.receipt_url.indexOf('?') < 0 ? '?' : '&') + 'portal=admin'}" target="_blank" rel="noopener">View</a>`:'—'}</td>
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

/* ══════════════ OFFICER EXPENSE CLAIMS & APPROVALS (admin) ══════════════ */
let adminExpenseRequests = [];

async function loadAdminExpenseRequests(){
  const el = document.getElementById('adminExpenseRequestsList');
  try{ adminExpenseRequests = await apiGet('/api/expense-requests'); }catch(e){ adminExpenseRequests = []; }
  
  // Update badge on finTab
  const pendingCount = (adminExpenseRequests||[]).filter(r=>r.status==='Pending').length;
  const badge = document.getElementById('badge-expense-requests');
  if(badge){
    badge.textContent = pendingCount;
    badge.style.display = pendingCount > 0 ? 'inline-block' : 'none';
  }

  renderAdminExpenseRequests();
}

function renderAdminExpenseRequests(){
  const el = document.getElementById('adminExpenseRequestsList');
  if(!el) return;
  const filter = (document.getElementById('adminExpenseReqFilter')?.value || '');
  let list = adminExpenseRequests || [];
  if(filter) list = list.filter(r => r.status === filter);

  if(!Array.isArray(list) || !list.length){
    el.innerHTML = '<div class="empty-state" style="padding:16px;">No ' + (filter ? filter.toLowerCase() : '') + ' expense claims found.</div>';
    return;
  }

  const peso = v => '₱'+Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2});
  const statusBadge = s => {
    if(s === 'Approved') return '<span class="pill pill-resolved">Approved</span>';
    if(s === 'Rejected') return '<span class="pill pill-urgent">Rejected</span>';
    return '<span class="pill pill-pending">Pending Review</span>';
  };

  el.innerHTML = '<table class="panel-table"><thead><tr>' +
    '<th>Date</th><th>Officer</th><th>Category</th><th>Fund</th><th>Title / Purpose</th><th>Amount</th><th>Linked Issue</th><th>Status</th><th>Receipt</th><th></th>' +
    '</tr></thead><tbody>' +
    list.map(r => `<tr>
      <td>${escHtml(r.expense_date||'—')}</td>
      <td><strong>${escHtml(r.officer||'—')}</strong></td>
      <td>${escHtml(r.category||'Maintenance')}</td>
      <td>${escHtml(r.fund||'Operating')}</td>
      <td>
        <div style="font-weight:600;color:var(--text);">${escHtml(r.title)}</div>
        <div style="font-size:12px;color:var(--text-dim);">${escHtml(r.description||'')}</div>
      </td>
      <td style="font-weight:600;color:#c87941;">${peso(r.amount)}</td>
      <td>${r.issue_id ? `<span class="pill pill-progress" title="${escHtml(r.issue_title||'')}">#${r.issue_id}</span>` : '—'}</td>
      <td>${statusBadge(r.status)}</td>
      <td>${r.receipt_url ? `<a href="${r.receipt_url + (r.receipt_url.indexOf('?') < 0 ? '?' : '&') + 'portal=admin'}" target="_blank" rel="noopener">View</a>` : '—'}</td>
      <td style="text-align:right;white-space:nowrap;">
        <button class="btn btn-sm" onclick="openReviewExpenseRequest(${r.id})">${r.status==='Pending' ? 'Review & Approve' : 'Details'}</button>
      </td>
    </tr>`).join('') +
    '</tbody></table>';
}

function openReviewExpenseRequest(id){
  const r = (adminExpenseRequests||[]).find(x => x.id === id);
  if(!r) return;

  document.getElementById('reviewExpId').value = r.id;
  document.getElementById('reviewExpCategory').value = r.category || 'Maintenance';
  document.getElementById('reviewExpFund').value = r.fund || 'Operating';
  document.getElementById('reviewExpNotes').value = r.admin_notes || '';
  document.getElementById('reviewExpMsg').textContent = '';

  const peso = '₱' + Number(r.amount||0).toLocaleString(undefined, {minimumFractionDigits:2});
  const header = document.getElementById('reviewExpHeader');
  header.innerHTML = `
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
      <div>
        <div style="font-size:16px;font-weight:700;color:var(--text);">${escHtml(r.title)}</div>
        <div style="color:var(--text-dim);font-size:12px;margin-top:2px;">Submitted by <strong>${escHtml(r.officer)}</strong> on ${escHtml(r.created_at||r.expense_date)}</div>
      </div>
      <div style="font-size:18px;font-weight:700;color:#c87941;">${peso}</div>
    </div>
    <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--glass-border);color:var(--text);">${escHtml(r.description||'No details provided.')}</div>
    ${r.issue_id ? `<div style="margin-top:6px;font-size:12px;color:var(--text-dim);">Resolves: <strong>Issue #${r.issue_id} — ${escHtml(r.issue_title||'')}</strong></div>` : ''}
    <div style="margin-top:6px;font-size:12px;">Status: <strong>${escHtml(r.status)}</strong> ${r.reviewed_at ? `(Reviewed ${escHtml(r.reviewed_at)} by ${escHtml(r.reviewed_by||'Admin')})` : ''}</div>
  `;

  // Receipt preview
  const img = document.getElementById('reviewExpReceiptImg');
  const link = document.getElementById('reviewExpReceiptLink');
  if(r.receipt_url){
    img.src = r.receipt_url;
    img.style.display = 'block';
    link.href = r.receipt_url;
    link.style.display = 'inline-block';
  } else {
    img.style.display = 'none';
    link.style.display = 'none';
  }

  // Show/hide action buttons if already reviewed
  const actionBtns = document.getElementById('reviewExpActionBtns');
  if(actionBtns){
    actionBtns.style.display = r.status === 'Pending' ? 'flex' : 'none';
  }

  openModal('reviewExpenseRequest');
}

async function approveExpenseRequest(){
  const id = document.getElementById('reviewExpId').value;
  const category = document.getElementById('reviewExpCategory').value;
  const fund = document.getElementById('reviewExpFund').value;
  const admin_notes = document.getElementById('reviewExpNotes').value.trim();
  const msg = document.getElementById('reviewExpMsg');

  msg.textContent = 'Approving and posting to ledger…';
  try {
    const res = await apiPost('/api/expense-requests/' + id + '/approve', {
      category, fund, admin_notes
    });

    if(res.success){
      closeModal('reviewExpenseRequest');
      await loadAdminExpenseRequests();
      await loadAdminExpenses();
      if(typeof loadFinanceOverview === 'function') loadFinanceOverview();
      showToast('✅ Claim approved & auto-posted to HOA ledger.');
    } else {
      msg.textContent = res.message || 'Could not approve request.';
    }
  } catch(e) {
    msg.textContent = 'Error approving request.';
  }
}

async function rejectExpenseRequest(){
  const id = document.getElementById('reviewExpId').value;
  const admin_notes = document.getElementById('reviewExpNotes').value.trim();
  const msg = document.getElementById('reviewExpMsg');

  if(!admin_notes){
    msg.textContent = 'Please enter a note/reason for rejecting this claim.';
    return;
  }

  msg.textContent = 'Rejecting claim…';
  try {
    const res = await apiPost('/api/expense-requests/' + id + '/reject', { admin_notes });
    if(res.success){
      closeModal('reviewExpenseRequest');
      await loadAdminExpenseRequests();
      showToast('Expense claim rejected.');
    } else {
      msg.textContent = res.message || 'Could not reject request.';
    }
  } catch(e) {
    msg.textContent = 'Error rejecting request.';
  }
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
  try {
    sessionStorage.setItem('subsync_fin_tab_admin', name);
  } catch(e) {}
  if(name==='overview') loadFinanceOverview();
  if(name==='requests') loadAdminExpenseRequests();
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
  if (mapInst) {
    setTimeout(() => mapInst.invalidateSize(), 100);
    if (!layerBlocks) {
      layerBlocks = L.layerGroup();
      if (document.getElementById('layer-blocks')?.checked) {
        layerBlocks.addTo(mapInst);
      }
    }
    loadBlocksData();
    loadMapData();
    return;
  }
  await fetchMapSettings();
  const b = mapSettings;
  const areaBounds = L.latLngBounds([[b.min_lat,b.min_lng],[b.max_lat,b.max_lng]]);
  mapInst = L.map('map-container', {
    minZoom: 14,
    maxZoom: 20,
    maxBounds: typeof areaBounds.pad === 'function' ? areaBounds.pad(0.08) : areaBounds,
    maxBoundsViscosity: 0.8,
  }).fitBounds(areaBounds, {padding:[20,20]});
  if (window.SubUI && SubUI.enableDynamicPinScaling) SubUI.enableDynamicPinScaling(mapInst);
  areaRect = L.rectangle(areaBounds,{color:'#FA7D09',weight:2,dashArray:'6 4',fillColor:'#FA7D09',fillOpacity:0.06,interactive:false}).addTo(mapInst);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 20,
  }).addTo(mapInst);

  const blockInitChecked = Boolean(document.getElementById('layer-blocks')?.checked);
  const facInitChecked = document.getElementById('layer-facilities') ? document.getElementById('layer-facilities').checked : true;
  const issueInitChecked = document.getElementById('layer-issues') ? document.getElementById('layer-issues').checked : true;
  const heatInitChecked = Boolean(document.getElementById('layer-heat')?.checked);

  layerBlocks     = L.layerGroup();
  if (blockInitChecked) layerBlocks.addTo(mapInst);

  layerHouseholds = L.layerGroup(); // individual lot dots removed per simplified subdivision map

  layerFacilities = L.layerGroup();
  if (facInitChecked) layerFacilities.addTo(mapInst);

  layerIssues     = L.layerGroup();
  if (issueInitChecked) layerIssues.addTo(mapInst);

  layerHeat       = L.layerGroup(); // off by default — toggled by the Heatmap checkbox
  if (heatInitChecked) layerHeat.addTo(mapInst);

  loadBlocksData();
  loadMapData();
  setTimeout(() => { if (mapInst) mapInst.invalidateSize(); }, 200);

  mapInst.on('click', function(e) {
    if (customizingArea || editingBlocks) return;
    if (addFacilityMode || document.getElementById('modal-addFacility')?.classList.contains('open')) {
      handleMapClickForFacility(e.latlng);
    }
  });

  const blocksToggle = document.getElementById('layer-blocks');
  if (blocksToggle) {
    blocksToggle.onchange = function() { toggleMapBlocks(this.checked); };
  }
  const facToggle = document.getElementById('layer-facilities');
  if (facToggle) {
    facToggle.onchange = function() { toggleMapFacilities(this.checked); };
  }
  const issuesToggle = document.getElementById('layer-issues');
  if (issuesToggle) {
    issuesToggle.onchange = function() { toggleMapIssues(this.checked); };
  }
  const heatToggle = document.getElementById('layer-heat');
  if (heatToggle) {
    heatToggle.onchange = function() { toggleMapHeatmap(this.checked); };
  }
  const heatCat = document.getElementById('heat-category');
  if (heatCat) heatCat.onchange = renderIssueHeatmap;
  setMapInteractiveMode(mapInteractiveMode);
  });
}

let mapHouseholdsData = [];

function loadMapData() {
  fetch('/api/map/households').then(r => r.json()).then(data => {
    mapHouseholdsData = Array.isArray(data) ? data : [];
    renderFilteredHouseholds();
  });

  fetch('/api/map/facilities').then(r => r.json()).then(data => {
    layerFacilities.clearLayers();
    adminFacilityMarkers = [];
    (data || []).forEach(f => {
      const lat = parseFloat(f.latitude);
      const lng = parseFloat(f.longitude);
      if (isNaN(lat) || isNaN(lng)) return;

      const isEdit = mapInteractiveMode === 'edit';
      const marker = L.marker([lat, lng], {
        icon: makeCircleIcon('#4CAF50', isEdit),
        draggable: isEdit,
        title: isEdit ? `${f.name} (Drag to reposition)` : f.name
      });
      adminFacilityMarkers.push({ marker, f });

      marker.bindPopup(
        (f.image_url ? `<img src="${f.image_url}" alt="${f.name}" style="max-width:210px;width:100%;border-radius:6px;margin-bottom:6px;display:block;">` : '') +
        `<strong>${f.name}</strong>${f.description ? '<br><small>' + f.description + '</small>' : ''}<br>` +
        `<small style="color:#888;">${f.status} • Drag pin to reposition</small><br>` +
        `<div style="margin-top:5px;display:flex;gap:4px;">` +
        `<button onclick="editFacility(${f.id})" style="padding:2px 7px;font-size:11px;border:1px solid #ccc;border-radius:3px;cursor:pointer;">Edit</button>` +
        `<button onclick="deleteFacilityById(${f.id})" style="padding:2px 7px;font-size:11px;border:1px solid #f88;color:#c00;border-radius:3px;cursor:pointer;">Delete</button>` +
        `</div>`
      );

      marker.on('dragstart', function() {
        marker._origLatLng = marker.getLatLng();
        if (marker.closePopup) marker.closePopup();
      });

      marker.on('dragend', async function(e) {
        const p = e.target.getLatLng();
        const newLat = +p.lat.toFixed(7);
        const newLng = +p.lng.toFixed(7);

        try {
          const res = await updateFacilityLocation(f.id, newLat, newLng);
          if (res && res.success) {
            f.latitude = newLat;
            f.longitude = newLng;
            marker._origLatLng = p;
            showToast(`📍 Moved ${f.name} to new position.`);
            if (typeof refreshDashboardMiniMapData === 'function') {
              refreshDashboardMiniMapData();
            }
          } else {
            showToast('⚠ ' + (res && res.message ? res.message : 'Could not reposition facility.'));
            if (marker._origLatLng) {
              marker.setLatLng(marker._origLatLng);
            }
          }
        } catch (err) {
          showToast('⚠ Error repositioning facility.');
          if (marker._origLatLng) {
            marker.setLatLng(marker._origLatLng);
          }
        }
      });

      marker.addTo(layerFacilities);
    });
  });

  fetch('/api/map/issues').then(r => r.json()).then(data => {
    layerIssues.clearLayers();
    issueMarkerCount = 0;
    data.forEach((i, idx) => {
      const color = i.status === 'Resolved' ? '#888' : (i.status === 'In Progress' ? '#f5a623' : '#e05555');
      issueMarkerCount++;
      const num = i.id != null ? i.id : (idx + 1);
      L.marker([parseFloat(i.latitude), parseFloat(i.longitude)], {icon: makeNumberedIcon(color, num)})
        .bindPopup(`<strong>Report #${num} - ${i.title}</strong><br>${i.category}<br><small>Status: ${i.status}</small>`)
        .addTo(layerIssues);
    });

    // Heatmap source data + category options (kept in sync with what the map shows).
    mapIssuesData = Array.isArray(data) ? data : [];
    const catSel = document.getElementById('heat-category');
    if (catSel && catSel.options.length <= 1) {
      [...new Set(mapIssuesData.map(i => i.category).filter(Boolean))].sort().forEach(c => {
        const o = document.createElement('option');
        o.value = c; o.textContent = c;
        catSel.appendChild(o);
      });
    }
    renderIssueHeatmap();
  });
}

function renderFilteredHouseholds() {
  if (!mapInst || !layerHouseholds) return;
  layerHouseholds.clearLayers();
  // Individual household lot pins removed to keep the subdivision map clean and focused
  // on block boundaries, lot counts, directional Lot 1 starting points, and landmarks.
}

let heatCanvasLayer = null;

function filterHeatmapToCategory(category) {
  const catSel = document.getElementById('heat-category');
  if (catSel) {
    catSel.value = category;
    renderIssueHeatmap();
  }
}

/* Subdivision Issue Heatmap:
   1. Renders an authentic continuous thermal canvas heatmap (via Leaflet.heat) that
      ramps up in heat (cool sky blue/green → amber → orange → scorching red)
      when issues occur or cluster in that part of the subdivision.
   2. Geographically clusters nearby issues (~80m / 0.00072 deg) into distinct subdivision
      hotspots and categorizes which sort of issue is most frequent in that area.
   3. Badges each hotspot with interactive pills and rich breakdown popups.
   4. Synchronizes the top analysis card and category filters with ranked frequency metrics. */
async function renderIssueHeatmap() {
  if (!mapInst || !layerHeat) return;
  layerHeat.clearLayers();
  heatCanvasLayer = null;

  const analysisCard = document.getElementById('heatmap-analysis-card');
  if (!mapInst.hasLayer(layerHeat)) {
    if (analysisCard) analysisCard.style.display = 'none';
    return;
  }
  if (analysisCard) analysisCard.style.display = 'block';

  // Ensure Leaflet.heat is loaded
  await ensureLeafletHeat();

  const catEl = document.getElementById('heat-category');
  const selectedCat = catEl ? catEl.value : '';
  const allIssues = (mapIssuesData || []).filter(i => {
    const lat = parseFloat(i.latitude), lng = parseFloat(i.longitude);
    return !isNaN(lat) && !isNaN(lng);
  });

  // Calculate overall category frequency across all mapped subdivision issues
  const overallCategoryCounts = {};
  allIssues.forEach(i => {
    const c = i.category || 'Other';
    overallCategoryCounts[c] = (overallCategoryCounts[c] || 0) + 1;
  });

  const sortedOverallCats = Object.entries(overallCategoryCounts).sort((a, b) => b[1] - a[1]);
  const totalMappedIssues = allIssues.length;
  const topOverallCategory = sortedOverallCats.length ? sortedOverallCats[0][0] : 'None';
  const topOverallCount = sortedOverallCats.length ? sortedOverallCats[0][1] : 0;
  const topOverallPct = totalMappedIssues ? Math.round((topOverallCount / totalMappedIssues) * 100) : 0;

  // Sync category dropdown with ranked categories
  if (catEl) {
    const prevVal = catEl.value;
    catEl.innerHTML = `<option value="">All categories (${totalMappedIssues} total)</option>`;
    sortedOverallCats.forEach(([catName, count], idx) => {
      const opt = document.createElement('option');
      opt.value = catName;
      opt.textContent = `${idx === 0 ? '★ ' : ''}#${idx + 1} ${catName} (${count}${idx === 0 ? ' — Most Frequent' : ''})`;
      catEl.appendChild(opt);
    });
    catEl.value = prevVal;
  }

  // Update category filter pills in the analysis card
  const pillsContainer = document.getElementById('heatmap-category-pills');
  if (pillsContainer) {
    let pillsHtml = `
      <span style="font-size:11px;font-weight:700;color:var(--text-dim);margin-right:4px;">Filter Heatmap:</span>
      <button class="heatmap-pill-btn ${!selectedCat ? 'active' : ''}" onclick="filterHeatmapToCategory('')" title="Show all issue categories">
        All Issues <span class="pill-count">${totalMappedIssues}</span>
      </button>
    `;
    sortedOverallCats.forEach(([catName, count], idx) => {
      const isSel = selectedCat === catName;
      const rankBadge = idx === 0 ? '★ ' : `#${idx + 1} `;
      pillsHtml += `
        <button class="heatmap-pill-btn ${isSel ? 'active' : ''}" onclick="filterHeatmapToCategory('${catName.replace(/'/g, "\\'")}')" title="Filter to ${catName}">
          ${rankBadge}${escHtml(catName)} <span class="pill-count">${count}</span>
        </button>
      `;
    });
    pillsContainer.innerHTML = pillsHtml;
  }

  // Filter issues if a specific category is chosen
  const activeIssues = allIssues.filter(i => !selectedCat || i.category === selectedCat);

  // 1. Build thermal heat points with intensity ramp-up
  const heatPoints = [];
  activeIssues.forEach(i => {
    const lat = parseFloat(i.latitude), lng = parseFloat(i.longitude);

    // Status thermal weighting: Unresolved/Pending burns hot, In Progress is warm, Resolved cool
    let intensity = 0.85;
    const st = (i.status || '').toLowerCase();
    if (st === 'in progress') intensity = 0.65;
    else if (st === 'resolved') intensity = 0.28;

    // Priority multiplier
    const pr = (i.priority || '').toLowerCase();
    if (pr === 'critical') intensity *= 1.4;
    else if (pr === 'high') intensity *= 1.2;
    else if (pr === 'low') intensity *= 0.8;

    // Freshness boost: fresh reports (< 7 days) burn hotter
    if (i.created_at) {
      const ageDays = (Date.now() - new Date(i.created_at).getTime()) / (1000 * 60 * 60 * 24);
      if (ageDays <= 3) intensity += 0.20;
      else if (ageDays <= 7) intensity += 0.10;
    }

    heatPoints.push([lat, lng, Math.min(intensity, 1.8)]);
  });

  // Render authentic thermal canvas heatmap via Leaflet.heat
  if (typeof L.heatLayer === 'function' && heatPoints.length > 0) {
    heatCanvasLayer = L.heatLayer(heatPoints, {
      radius: 34,
      blur: 20,
      maxZoom: 19,
      max: 2.2,
      minOpacity: 0.28,
      gradient: {
        0.15: '#38bdf8', // Cool Sky Blue (isolated / minor report)
        0.35: '#34d399', // Emerald Green (mild warmth)
        0.55: '#facc15', // Amber Yellow (warming cluster / 2 issues)
        0.75: '#fb923c', // Vibrant Orange (escalating hotspot / 3 issues)
        0.90: '#f87171', // Hot Coral (critical density / 4+ issues)
        1.00: '#dc2626'  // Deep Crimson (intense focal heat)
      }
    });
    layerHeat.addLayer(heatCanvasLayer);
  }

  // 2. Spatial Clustering & Hotspot Categorization
  // Cluster nearby issues (~80m / 0.00072 deg) to discover subdivision problem zones
  const CLUSTER_THRESH_DEG = 0.00072;
  const clusters = [];

  activeIssues.forEach(issue => {
    const lat = parseFloat(issue.latitude), lng = parseFloat(issue.longitude);
    let matchedCluster = null;

    for (const cl of clusters) {
      const dist = Math.hypot(lat - cl.centerLat, lng - cl.centerLng);
      if (dist <= CLUSTER_THRESH_DEG) {
        matchedCluster = cl;
        break;
      }
    }

    if (matchedCluster) {
      matchedCluster.issues.push(issue);
      matchedCluster.centerLat = matchedCluster.issues.reduce((s, it) => s + parseFloat(it.latitude), 0) / matchedCluster.issues.length;
      matchedCluster.centerLng = matchedCluster.issues.reduce((s, it) => s + parseFloat(it.longitude), 0) / matchedCluster.issues.length;
    } else {
      clusters.push({
        centerLat: lat,
        centerLng: lng,
        issues: [issue]
      });
    }
  });

  // Update summary line in analysis card
  const summaryLine = document.getElementById('heatmap-summary-line');
  if (summaryLine) {
    if (activeIssues.length === 0) {
      summaryLine.innerHTML = selectedCat 
        ? `No active issues reported for category <strong>${escHtml(selectedCat)}</strong>.`
        : 'No issues with coordinates recorded in the subdivision.';
    } else {
      const filterNotice = selectedCat ? `Filtered to <strong>${escHtml(selectedCat)}</strong> (${activeIssues.length} issues) · ` : '';
      summaryLine.innerHTML = `
        ${filterNotice}
        🏆 <strong>Subdivision Most Frequent Issue:</strong> 
        <span style="color:#ef4444;font-weight:800;">${escHtml(topOverallCategory)}</span> 
        (${topOverallCount} of ${totalMappedIssues} reports · ${topOverallPct}%) across <strong>${clusters.length}</strong> active hotspot area${clusters.length > 1 ? 's' : ''}.
      `;
    }
  }

  // Render Hotspot Badges & Categorization popups
  clusters.forEach(cl => {
    const total = cl.issues.length;
    const catCounts = {};
    cl.issues.forEach(it => {
      const c = it.category || 'Other';
      catCounts[c] = (catCounts[c] || 0) + 1;
    });

    const sortedCats = Object.entries(catCounts).sort((a, b) => b[1] - a[1]);
    const topCat = sortedCats[0][0];
    const topCount = sortedCats[0][1];
    const topPct = Math.round((topCount / total) * 100);

    const severity = total >= 4 ? 'critical' : (total === 3 ? 'high' : (total === 2 ? 'moderate' : 'low'));
    const severityLabel = total >= 4 ? 'Critical Hotspot' : (total === 3 ? 'High Heat Area' : (total === 2 ? 'Moderate Activity' : 'Single Issue Area'));
    const severityColor = total >= 4 ? '#dc2626' : (total === 3 ? '#ea580c' : (total === 2 ? '#d97706' : '#0284c7'));
    const flameIcon = total >= 4 ? '🔥🔥' : (total >= 2 ? '🔥' : '📍');

    const badgeHtml = `
      <div class="sub-hotspot-pill heat-${severity}" title="${severityLabel}: ${escHtml(topCat)} is most frequent (${topCount}/${total})">
        <span>${flameIcon}</span>
        <span class="hotspot-count">${total}</span>
        <span class="hotspot-cat">${escHtml(topCat)}</span>
        <span class="hotspot-pct">${topPct}%</span>
      </div>
    `;

    const icon = L.divIcon({
      className: 'sub-hotspot-marker',
      html: badgeHtml,
      iconSize: [0, 0],
      iconAnchor: [0, 0]
    });

    const breakdownRows = sortedCats.map(([cat, cnt]) => {
      const pct = Math.round((cnt / total) * 100);
      const isTop = cat === topCat;
      return `
        <div class="sub-hotspot-breakdown-row">
          <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:2px;">
            <span style="font-weight:${isTop ? '700' : '500'};color:var(--text);">${isTop ? '★ ' : ''}${escHtml(cat)}</span>
            <span style="font-weight:700;color:var(--text-dim);">${cnt} (${pct}%)</span>
          </div>
          <div class="sub-hotspot-bar-track">
            <div class="sub-hotspot-bar-fill" style="width:${pct}%;background:${isTop ? severityColor : '#94a3b8'};"></div>
          </div>
        </div>
      `;
    }).join('');

    const recentIssuesHtml = cl.issues.slice(0, 3).map(it => {
      const stColor = it.status === 'Resolved' ? '#64748b' : (it.status === 'In Progress' ? '#d97706' : '#dc2626');
      return `
        <div style="display:flex;align-items:center;justify-content:space-between;font-size:10.5px;padding:3px 0;border-bottom:1px solid rgba(148,163,184,0.15);">
          <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px;" title="${escHtml(it.title)}">#${it.id || ''} ${escHtml(it.title || 'Issue')}</span>
          <span style="color:${stColor};font-weight:700;font-size:10px;">${escHtml(it.status || '')}</span>
        </div>
      `;
    }).join('');

    const popupHtml = `
      <div class="sub-hotspot-popup">
        <div class="sub-hotspot-popup-header">
          <span style="color:${severityColor};font-weight:800;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">${flameIcon} ${severityLabel}</span>
          <span style="font-size:11px;font-weight:700;background:var(--glass, rgba(0,0,0,0.06));padding:2px 7px;border-radius:10px;">${total} issue${total > 1 ? 's' : ''} nearby</span>
        </div>
        
        <div class="sub-hotspot-dominant-box">
          <div class="sub-hotspot-dominant-tag">★ Most Frequent Issue Category</div>
          <div class="sub-hotspot-dominant-title">${escHtml(topCat)}</div>
          <div class="sub-hotspot-dominant-stat">
            <strong>${topCount} of ${total} reports</strong> in this area (${topPct}%)
          </div>
        </div>
        
        <div style="font-size:11px;font-weight:700;color:var(--text-mid);margin-bottom:6px;">Category Breakdown</div>
        <div class="sub-hotspot-breakdown">
          ${breakdownRows}
        </div>

        ${recentIssuesHtml ? `
          <div style="font-size:10px;font-weight:700;color:var(--text-dim);margin:8px 0 4px;text-transform:uppercase;">Recent Reports in this Area</div>
          <div style="margin-bottom:10px;">${recentIssuesHtml}</div>
        ` : ''}

        <button class="btn btn-sm btn-green" onclick="filterHeatmapToCategory('${topCat.replace(/'/g, "\\'")}')" style="width:100%;font-size:11px;padding:4px 8px;justify-content:center;font-weight:600;">
          Focus Heatmap on "${escHtml(topCat)}"
        </button>
      </div>
    `;

    const marker = L.marker([cl.centerLat, cl.centerLng], { icon })
      .bindPopup(popupHtml, { maxWidth: 320 });

    layerHeat.addLayer(marker);
  });
}

function handleMapClickForFacility(latlng) {
  if (!latlng) return;
  const rawLat = latlng.lat !== undefined ? latlng.lat : latlng[0];
  const rawLng = latlng.lng !== undefined ? latlng.lng : latlng[1];
  if (rawLat === undefined || rawLng === undefined) return;

  const lat = +parseFloat(rawLat).toFixed(7);
  const lng = +parseFloat(rawLng).toFixed(7);

  const latInput = document.getElementById('fac-lat');
  const lngInput = document.getElementById('fac-lng');
  if (latInput) latInput.value = lat;
  if (lngInput) lngInput.value = lng;

  if (pendingPin) {
    pendingPin.setLatLng([lat, lng]);
  } else if (mapInst) {
    pendingPin = L.marker([lat, lng], {
      draggable: true,
      zIndexOffset: 1000,
      icon: L.divIcon({
        className: '',
        html: '<div class="facility-marker-pin" style="width:20px;height:20px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:#4CAF50;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4);" title="Drag to adjust position"></div>',
        iconSize: [20, 20],
        iconAnchor: [10, 20]
      })
    }).addTo(mapInst);

    pendingPin.on('dragend', function(ev) {
      const p = ev.target.getLatLng();
      if (latInput) latInput.value = p.lat.toFixed(7);
      if (lngInput) lngInput.value = p.lng.toFixed(7);
    });
  }

  const modal = document.getElementById('modal-addFacility');
  if (modal && !modal.classList.contains('open')) {
    openModal('addFacility');
  }
}

function enterAddFacilityMode() {
  if (!mapInst) { showToast('Map not loaded yet.'); return; }
  addFacilityMode = true;

  // Make sure facilities layer is visible
  const facToggle = document.getElementById('layer-facilities');
  if (facToggle && !facToggle.checked) {
    facToggle.checked = true;
    if (layerFacilities && mapInst && !mapInst.hasLayer(layerFacilities)) {
      mapInst.addLayer(layerFacilities);
    }
  }

  // Pre-fill location to center of subdivision map if not already set
  let lat = document.getElementById('fac-lat')?.value;
  let lng = document.getElementById('fac-lng')?.value;
  if (!lat || !lng) {
    const center = (mapSettings && mapSettings.center_lat && mapSettings.center_lng)
      ? [parseFloat(mapSettings.center_lat), parseFloat(mapSettings.center_lng)]
      : mapInst.getCenter();
    lat = +(center.lat !== undefined ? center.lat : center[0]).toFixed(7);
    lng = +(center.lng !== undefined ? center.lng : center[1]).toFixed(7);
  }

  handleMapClickForFacility({ lat, lng });

  const banner = document.getElementById('map-add-banner');
  if (banner) banner.style.display = 'flex';
  mapInst.getContainer().style.cursor = 'crosshair';

  openModal('addFacility');
}

window.enterAddFacilityMode = enterAddFacilityMode;
window.handleMapClickForFacility = handleMapClickForFacility;

function cancelAddFacilityMode() {
  addFacilityMode = false;
  const banner = document.getElementById('map-add-banner');
  if (banner) banner.style.display = 'none';
  if (mapInst) mapInst.getContainer().style.cursor = '';
  if (pendingPin) { pendingPin.remove(); pendingPin = null; }
  const nameEl = document.getElementById('fac-name');
  if (nameEl) nameEl.value = '';
  const descEl = document.getElementById('fac-desc');
  if (descEl) descEl.value = '';
  const latEl = document.getElementById('fac-lat');
  if (latEl) latEl.value = '';
  const lngEl = document.getElementById('fac-lng');
  if (lngEl) lngEl.value = '';
  const imgEl = document.getElementById('fac-image');
  if (imgEl) imgEl.value = '';
  const prev = document.getElementById('fac-image-preview');
  if (prev) { prev.style.display = 'none'; prev.removeAttribute('src'); }
}

function previewImage(input, previewId){
  const img = document.getElementById(previewId);
  if(!img) return;
  if(input.files && input.files[0]){ img.src = URL.createObjectURL(input.files[0]); img.style.display = 'block'; }
  else { img.style.display = 'none'; img.removeAttribute('src'); }
}

function saveNewFacility() {
  const name = document.getElementById('fac-name').value.trim();
  let lat  = document.getElementById('fac-lat').value;
  let lng  = document.getElementById('fac-lng').value;
  if (!name) { showToast('Please enter a facility name.'); return; }
  if (!lat || !lng) {
    if (mapInst) {
      const center = mapInst.getCenter();
      lat = center.lat.toFixed(7);
      lng = center.lng.toFixed(7);
      document.getElementById('fac-lat').value = lat;
      document.getElementById('fac-lng').value = lng;
    } else {
      showToast('Click the map to pin a location first.');
      return;
    }
  }

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
      loadMapData();
      if(typeof refreshDashboardMiniMapData === 'function') refreshDashboardMiniMapData();
      showToast('✅ Facility added successfully!');
    } else {
      showToast('⚠ ' + (d.message || 'Could not save facility.'));
    }
  })
  .catch(err => {
    showToast('⚠ Network error saving facility.');
  });
}

async function updateFacilityLocation(id, lat, lng) {
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const r = await fetch(`/api/map/facility/${id}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': token
    },
    body: JSON.stringify({ latitude: lat, longitude: lng })
  });
  return r.json();
}

function editFacility(id) {
  editFacilityId = id;
  fetch('/api/map/facilities').then(r => r.json()).then(data => {
    const f = data.find(x => x.id === id);
    if (!f) return;
    document.getElementById('edit-fac-name').value   = f.name;
    document.getElementById('edit-fac-desc').value   = f.description || '';
    document.getElementById('edit-fac-status').value = f.status;
    const latEl = document.getElementById('edit-fac-lat');
    const lngEl = document.getElementById('edit-fac-lng');
    if (latEl) latEl.value = f.latitude != null ? parseFloat(f.latitude).toFixed(7) : '';
    if (lngEl) lngEl.value = f.longitude != null ? parseFloat(f.longitude).toFixed(7) : '';
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
  const latVal = document.getElementById('edit-fac-lat')?.value;
  const lngVal = document.getElementById('edit-fac-lng')?.value;
  if (latVal && lngVal) {
    fd.append('latitude', latVal);
    fd.append('longitude', lngVal);
  }
  const file = document.getElementById('edit-fac-image').files[0];
  if (file) fd.append('image', file);
  if (document.getElementById('edit-fac-remove-image').checked) fd.append('remove_image', '1');

  fetch(`/api/map/facility/${editFacilityId}`, {
    method: 'POST',
    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json'},
    body: fd,
  })
  .then(r => r.json())
  .then(d => { if (d.success) { closeModal('editFacility'); loadMapData(); if(typeof refreshDashboardMiniMapData === 'function') refreshDashboardMiniMapData(); showToast('Facility updated.'); } else showToast('⚠ ' + (d.message || 'Could not update facility.')); });
}

async function deleteFacilityById(id) {
  if (!(await SubUI.confirm({ title:'Delete facility?', message:'Delete this map facility? This cannot be undone.', confirmText:'Delete', danger:true, icon:'!' }))) return;
  fetch(`/api/map/facility/${id}`, {
    method: 'DELETE',
    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
  })
  .then(r => r.json())
  .then(d => { if (d.success) { loadMapData(); if(typeof refreshDashboardMiniMapData === 'function') refreshDashboardMiniMapData(); showToast('Facility removed.'); } });
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
        <span style="color:var(--text-dim);min-width:60px;"><x-icon name="mail" size="13"/> Email</span>
        <span id="resD-email">—</span>
      </div>
      <div style="display:flex;gap:10px;align-items:center;font-size:13px;">
        <span style="color:var(--text-dim);min-width:60px;"><x-icon name="phone" size="13"/> Phone</span>
        <span id="resD-phone">—</span>
      </div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="document.getElementById('modal-residentDetail').classList.remove('open')">Close</button>
      <button class="btn btn-sm" id="resD-msgBtn"><x-icon name="message" size="14"/> Message Resident</button>
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
      <button class="modal-close-btn" id="annV-delBtn" style="color:#f08080;border-color:rgba(240,128,128,0.3);"><x-icon name="trash" size="14"/> Delete</button>
      <button class="btn btn-sm" id="annV-archBtn" style="background:var(--yellow-dim);border-color:rgba(196,150,70,0.3);color:var(--yellow);" onclick="toggleAnnArchive()"><x-icon name="archive" size="14"/> Archive</button>
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
      <button class="modal-close-btn" onclick="deleteMember()" style="color:#f08080;border-color:rgba(240,128,128,0.3);"><x-icon name="trash" size="14"/> Delete</button>
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

<!-- Edit Block Details Modal -->
<div class="modal-overlay" id="modal-editBlock" onclick="if(event.target===this)closeModal('editBlock')">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-title" id="editBlockModalTitle">Edit Block Details</div>
    <input type="hidden" id="editBlockId">
    <input type="hidden" id="editBlockNum">
    <div class="f-row">
      <div class="f-field">
        <span class="f-label">Block Name</span>
        <input type="text" id="editBlockName" placeholder="e.g. Block 4">
      </div>
      <div class="f-field">
        <span class="f-label">Color</span>
        <input type="color" id="editBlockColor" style="height:38px;padding:2px 4px;cursor:pointer;">
      </div>
    </div>
    <div class="f-row">
      <div class="f-field">
        <span class="f-label">Lot Range</span>
        <input type="text" id="editBlockLotRange" placeholder="e.g. Lots 1–40">
      </div>
      <div class="f-field">
        <span class="f-label">Total Lots</span>
        <input type="number" id="editBlockTotalLots" placeholder="40" min="0">
      </div>
    </div>
    <div class="f-row">
      <div class="f-field">
        <span class="f-label">START Label (Where Numbering Starts)</span>
        <input type="text" id="editBlockStartLabel" placeholder="e.g. Lot 1 (East)">
      </div>
    </div>
    <div class="f-row">
      <div class="f-field">
        <span class="f-label">Description / Notes</span>
        <textarea id="editBlockDescription" rows="2" placeholder="Optional notes for this block"></textarea>
      </div>
    </div>
    <div style="font-size:12px;color:var(--text-dim);margin-bottom:8px;line-height:1.4;">
      💡 Tip: In <strong>Edit Blocks</strong> mode, you can also drag the <strong>Block Label</strong> and <strong>START Pin</strong> directly on the map to position them anywhere.
    </div>
    <div style="font-size:12px;color:#f08080;margin-bottom:8px;" id="editBlockMsg"></div>
    <div class="modal-actions" style="display:flex;gap:8px;justify-content:flex-end;">
      <button class="modal-close-btn" onclick="closeModal('editBlock')">Cancel</button>
      <button class="btn btn-sm btn-green" onclick="saveBlockDetails()"><x-icon name="save" size="13"/> Save Details</button>
    </div>
  </div>
</div>

</body>
</html>