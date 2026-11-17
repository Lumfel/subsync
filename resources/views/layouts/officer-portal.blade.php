<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SubSync — Officer Portal</title>
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
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap"></noscript>
<link rel="preload" as="image" href="{{ asset('images/admin_hero_bg_day.jpg') }}">
<link rel="preload" as="image" href="{{ asset('images/admin_hero_bg_clean.jpg') }}">
<link rel="stylesheet" href="{{ asset('css/officer-portal.css') . '?v=' . filemtime(public_path('css/officer-portal.css')) }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/ui-polish.css') . '?v=' . filemtime(public_path('css/ui-polish.css')) }}">
<meta name="theme-color" content="#FA7D09">
  <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
  <script src="{{ asset('js/pwa.js') . '?v=' . filemtime(public_path('js/pwa.js')) }}" defer></script>
  <script>window.__PORTAL = @json($role ?? ''); window.__PREFS = @json($prefs ?? []); window.__ACTOR = @json($role ?? ''); window.__OFFICER_ID = {{ (int) ($officerId ?? 0) }}; window.__OFFICER_NAME = @json($officerName ?? 'Officer');</script>
  <script src="{{ asset('js/ui.js') . '?v=' . filemtime(public_path('js/ui.js')) }}"></script>
  @vite('resources/js/app.js')
</head>
<body class="officer-body">

<!-- Mobile Responsive Header -->
<div class="mobile-header" style="display:none;">
  <div style="font-family:'DM Serif Display',serif; font-size:20px; color:#1C1A17; display:flex; align-items:center; gap:8px;">
    <span style="font-size:24px;"><x-icon name="home" size="24"/></span> SubSync
  </div>
  <button class="btn" style="padding: 6px 12px; font-size: 13px;" onclick="toggleMobileSidebar()"><x-icon name="menu" size="16"/> Menu</button>
</div>

<div class="sidebar-overlay" onclick="toggleMobileSidebar()"></div>

<!-- Left Sidebar Navigation -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo">
      <img src="{{ asset('emblem_1.svg') }}" alt="SubSync">
    </div>
    <div class="brand-sub">Officer Portal · Terra Nova HOA</div>
  </div>
  <ul>
    <li class="tab-btn active" onclick="switchTab('overview')" id="tab-overview"><x-icon name="home" size="15"/> Overview</li>
    <li class="tab-btn" onclick="switchTab('reports')" id="tab-reports"><x-icon name="clipboard" size="15"/> Issues</li>
    <li class="tab-btn" onclick="switchTab('announcements')" id="tab-announcements"><x-icon name="megaphone" size="15"/> Announcements</li>
    <li class="tab-btn" onclick="switchTab('calendar')" id="tab-calendar"><x-icon name="calendar" size="15"/> Calendar</li>
    <li class="tab-btn" onclick="switchTab('messages')" id="tab-messages"><x-icon name="message" size="15"/> Messages <span class="tab-badge" id="badge-messages" style="display:none;">0</span></li>
    <li class="tab-btn" onclick="switchTab('files')" id="tab-files"><x-icon name="folder" size="15"/> Documents</li>
    <li class="tab-btn" onclick="switchTab('expenses')" id="tab-expenses"><x-icon name="coins" size="15"/> Expenses</li>
    <li class="tab-btn" onclick="switchTab('map')" id="tab-map"><x-icon name="map" size="15"/> Map</li>
  </ul>

  <!-- Sidebar tools: search + notifications -->
  <div class="sidebar-tools" style="display:none;">
    <button type="button" class="nc-btn" onclick="SubUI.openSearch()" title="Search (Ctrl+K)" aria-label="Search"><x-icon name="search" size="14"/></button>
  </div>

  <!-- Sidebar Profile Card -->
  <div class="sidebar-footer">
    <div class="sidebar-profile" onclick="toggleUserMenu()" title="{{ $officerName }} (Click for Account Settings)">
      <div class="avatar-wrap" onclick="event.stopPropagation(); document.getElementById('avatarInput').click()" style="width:38px;height:38px;margin:0;flex-shrink:0;">
        <img id="avatarPreview" src="{{ $avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($officerName).'&background=2a6acc&color=fff&size=80' }}" alt="Officer" style="width:38px; height:38px; border-radius:50%; object-fit:cover;">
        <input type="file" id="avatarInput" accept="image/*" onchange="changeImg('avatarPreview',this)" style="display:none;">
      </div>
      <div style="flex:1;min-width:0;margin-left:10px;text-align:left;">
        <div style="font-weight:600; font-size:13px; color:var(--text); max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" id="officerName" contenteditable="true" spellcheck="false" title="Click to edit name">{{ $officerName }}</div>
        <div style="font-size:11.5px; color:var(--text-dim); margin-top:2px;">Subdivision Officer</div>
      </div>
      <span class="admin-chevron" style="font-size:10px;color:var(--text-dim);">▼</span>
    </div>
  </div>
</aside>

<!-- Right Main Workspace -->
<main class="main">
  <!-- Topbar Header -->
  <header class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <button class="menu-toggle" id="menuToggle" onclick="toggleMobileSidebar()"><x-icon name="menu" size="18"/></button>
      <div>
        <div class="topbar-title" id="topbarTitle">Officer Portal</div>
        <div class="topbar-sub" id="topbarSubtitle">Subdivision Operations &amp; Oversight</div>
      </div>
    </div>
    <div class="topbar-actions">
      <!-- Search shortcut -->
      <div class="topbar-search-box" onclick="SubUI.openSearch()" title="Search (Ctrl+K)">
        <span style="color:#8C857B;display:flex;align-items:center;"><x-icon name="search" size="14"/></span>
        <span style="color:#7D766D;font-size:12.5px;">Search issues, notices, residents…</span>
      </div>

      <!-- Theme Toggle (Light / Dark Mode) -->
      <button class="topbar-btn keep-mobile" id="themeToggleBtn" onclick="toggleTheme()" title="Toggle Light / Dark mode" aria-label="Toggle theme">
        <span id="themeToggleIcon"><x-icon name="moon" size="15"/></span>
      </button>

      <!-- Notification Center / Alerts -->
      <div class="topbar-notif-wrap">
        @include('partials.notification-center')
      </div>

      <form id="logoutForm" action="{{ url('logout') }}" method="POST" style="display:none;">@csrf<input type="hidden" name="portal" value="{{ $role ?? 'officer' }}"></form>

      <!-- User Profile Dropdown Menu -->
      <div class="user-menu-wrap" style="position:relative;">
        <button class="topbar-avatar keep-mobile" id="userMenuButton" aria-expanded="false" aria-controls="userMenuPopover" onclick="toggleUserMenu()" title="{{ $officerName }} (Account menu)">
          {{ strtoupper(substr($officerName, 0, 2)) }}
        </button>
        <div class="user-menu-popover" id="userMenuPopover" role="menu" aria-label="Account menu">
          <!-- Header: User info -->
          <div class="user-menu-header">
            <div class="user-menu-avatar">{{ strtoupper(substr($officerName, 0, 2)) }}</div>
            <div class="user-menu-info">
              <div class="user-menu-name">{{ $officerName }}</div>
              <div class="user-menu-role">Subdivision Officer</div>
              <div class="user-menu-status"><span class="user-status-dot"></span> Online</div>
            </div>
          </div>
          <div class="user-menu-divider"></div>
          <!-- Menu items -->
          <div class="user-menu-items">
            <a href="{{ url('settings') }}?portal={{ $role ?? 'officer' }}" class="user-menu-item active" role="menuitem">
              <span class="user-menu-ic"><x-icon name="gear" size="16"/></span>
              <div class="user-menu-text">
                <div class="user-menu-title">Account Settings</div>
                <div class="user-menu-desc">Profile, account, and preferences</div>
              </div>
            </a>
            <a href="{{ url('settings') }}?portal={{ $role ?? 'officer' }}#notifications" class="user-menu-item" role="menuitem">
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
  </header>

  <!-- Emergency alert banner (shown on critical issues) -->
  <div id="emergencyAlert" style="display:none;">
    <x-icon name="alert" size="16"/> <span id="emergencyAlertText">CRITICAL ISSUE REPORTED</span>
    &nbsp;&nbsp;<button onclick="switchTab('reports');dismissEmergency()" style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.4);color:#fff;padding:3px 12px;border-radius:6px;font-size:12px;cursor:pointer;font-weight:600;">View Issue</button>
    &nbsp;<button aria-label="Dismiss" onclick="dismissEmergency()" style="background:transparent;border:none;color:rgba(255,255,255,0.7);font-size:16px;cursor:pointer;line-height:1;">✕</button>
  </div>
  <!-- ═══════════ OVERVIEW ═══════════ -->
  <div class="tab-panel active" id="panel-overview">
    <!-- 1. HERO BANNER -->
    <div class="dash-hero-card">
      <div class="dash-hero-content">
        <div class="dash-hero-indicator"></div>
        <div class="dash-hero-body">
          <h1 class="dash-hero-title">Welcome back, Officer {{ explode(' ', $officerName)[0] }} 👋</h1>
          <p class="dash-hero-subtitle">Subdivision operations, resident concerns, and notices.</p>
          <div class="dash-hero-pills">
            <span class="dash-pill"><x-icon name="calendar" size="14"/> {{ date('M d, Y (D)') }}</span>
            <span class="dash-pill" id="heroWeatherPill" title="Live Subdivision Weather"><x-icon name="sun" size="14"/><span id="heroWeatherText">29°C Partly Cloudy</span></span>
            <span class="dash-pill"><x-icon name="shield" size="14"/> Officer on Duty</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. 4 OFFICER KPI STAT CARDS ROW -->
    <div class="dash-kpi-grid">
      <!-- Open Issues -->
      <div class="dash-kpi-card" onclick="switchTab('reports')" title="Review open issues">
        <div class="dash-kpi-icon amber"><x-icon name="clipboard" size="18"/></div>
        <div class="dash-kpi-label">Open Issues</div>
        <div class="dash-kpi-val" id="openReportsCount">—</div>
        <div class="dash-kpi-sub muted">Require attention</div>
      </div>

      <!-- Published Notices -->
      <div class="dash-kpi-card" onclick="switchTab('announcements')" title="Manage announcements">
        <div class="dash-kpi-icon blue"><x-icon name="megaphone" size="18"/></div>
        <div class="dash-kpi-label">Published Notices</div>
        <div class="dash-kpi-val" id="annCount">—</div>
        <div class="dash-kpi-sub muted">Active bulletins</div>
      </div>

      <!-- Excel / Records Sent -->
      <div class="dash-kpi-card" onclick="switchTab('files')" title="Inspect dispatched files">
        <div class="dash-kpi-icon green"><x-icon name="folder" size="18"/></div>
        <div class="dash-kpi-label">Documents Sent</div>
        <div class="dash-kpi-val" id="filesSentCount">—</div>
        <div class="dash-kpi-sub muted">HOA files dispatched</div>
      </div>

      <!-- Verified Residents -->
      <div class="dash-kpi-card" onclick="switchTab('map')" title="View subdivision map">
        <div class="dash-kpi-icon orange"><x-icon name="users" size="18"/></div>
        <div class="dash-kpi-label">Verified Residents</div>
        <div class="dash-kpi-val" id="blockResidentsCount">—</div>
        <div class="dash-kpi-sub green">Subdivision members</div>
      </div>
    </div>

    <!-- 3. QUICK ACTIONS ROW -->
    <div class="card" style="margin-top:0;padding:20px 24px;">
      <div class="card-title" style="font-size:15px;margin-bottom:2px;">Quick Actions</div>
      <div class="card-subtitle" style="margin-bottom:14px;">Jump directly to tasks and operations.</div>
      <div class="officer-quick-actions-row">
        <button class="btn btn-full officer-qa-btn orange-accent" onclick="switchTab('announcements')">
          <x-icon name="megaphone" size="18"/>
          <span>Post Announcement</span>
        </button>
        <button class="btn btn-full officer-qa-btn" onclick="switchTab('reports')">
          <x-icon name="clipboard" size="18"/>
          <span>Resolve Issues</span>
        </button>
        <button class="btn btn-full officer-qa-btn" onclick="switchTab('files')">
          <x-icon name="upload" size="18"/>
          <span>Send Report File</span>
        </button>
        <button class="btn btn-full officer-qa-btn" onclick="switchTab('map')">
          <x-icon name="map" size="18"/>
          <span>Subdivision Map</span>
        </button>
      </div>
    </div>

    <!-- 4. RECENT ANNOUNCEMENTS FROM ADMIN -->
    <div class="card" style="margin-top:20px;">
      <div class="card-title">Recent Announcements from Admin</div>
      <div class="card-subtitle">The latest notices posted by the subdivision administration.</div>
      <div id="overviewAnnList"></div>
    </div>
  </div>

  <!-- ═══════════ ANNOUNCEMENTS ═══════════ -->
  <div class="tab-panel" id="panel-announcements">
    <div class="card" style="margin-top:0;">
      <div class="card-title">Create Announcement</div>
      <div class="card-subtitle">Write a notice, then choose who should see it and when.</div>
      <!-- Template quick-fill (built-in presets, replaced by DB templates when seeded) -->
      <div style="display:flex;gap:6px;margin-bottom:14px;flex-wrap:wrap;" id="officerAnnTemplateRow">
        <span style="font-size:11px;color:var(--text-dim);align-self:center;white-space:nowrap;">Template:</span>
        <button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="applyAnnTemplate('notice')"><x-icon name="clipboard" size="13"/> Notice</button>
        <button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="applyAnnTemplate('urgent')"><x-icon name="alert" size="13"/> Urgent</button>
        <button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="applyAnnTemplate('event')"><x-icon name="calendar" size="13"/> Event</button>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Type</span>
          <select id="annType">
            <option value="notice">Notice</option>
            <option value="urgent">Urgent</option>
            <option value="event">Event</option>
          </select>
        </div>
        <div class="f-field">
          <span class="f-label">Target Audience</span>
          <select id="annTarget">
            <option>All Residents</option>
            <option>Block 1 Only</option>
            <option>Block 2 Only</option>
            <option>Block 3 Only</option>
            <option>Block 4 Only</option>
            <option>Block 5 Only</option>
          </select>
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Date / Time</span>
          <input type="date" id="annDate">
        </div>
        <div class="f-field">
          <span class="f-label">Priority</span>
          <select id="annPriority">
            <option>Normal</option>
            <option>High</option>
          </select>
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Announcement Title</span>
          <input type="text" id="annTitle" placeholder="One-line summary of the announcement">
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Message</span>
          <textarea id="annBody" placeholder="Write the full announcement here…"></textarea>
        </div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button class="btn btn-full" style="flex:1;" onclick="SubUI&&SubUI.busyClick?SubUI.busyClick(this,postAnnouncement):postAnnouncement()"><x-icon name="megaphone" size="15"/> Post Announcement</button>
        <button class="btn btn-full" style="flex:1;background:transparent;border:1px solid var(--glass-border);color:var(--text-mid);" onclick="saveAnnDraft()"><x-icon name="file" size="15"/> Save as Draft</button>
      </div>
    </div>

    <div class="card">
      <div class="card-title">My Posted Announcements</div>
      <div id="myAnnList"></div>
    </div>
  </div>

  <!-- ═══════════ CALENDAR ═══════════ -->
  <div class="tab-panel" id="panel-calendar">
    <div class="card" style="margin-top:0;">
      <div class="card-title">Community Calendar</div>
      <div class="card-subtitle">Upcoming HOA events and meetings. Residents RSVP from their portal.</div>
      <div id="officerEventList" style="margin-top:12px;"><div class="empty-state">Loading…</div></div>
    </div>
  </div>

  <!-- ═══════════ REPORTS ═══════════ -->
  <div class="tab-panel" id="panel-reports">
    <div class="card" style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;flex-wrap:wrap;gap:10px; margin-top:0;">
      <div class="card-title" style="margin:0;">Issues</div>
      <div class="card-subtitle" style="margin-top:4px;">Reports submitted by residents. Tap one to respond and update its status.</div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <select id="officerIssueAssigneeFilter" onchange="renderOfficerIssues()" style="background:var(--glass);border:1px solid var(--glass-border);color:var(--text);padding:6px 10px;border-radius:6px;font-size:12px;">
          <option value="">All Assignments</option>
          <option value="mine">Assigned to Me</option>
          <option value="unassigned">Unassigned</option>
        </select>
        <select id="officerIssueFilter" onchange="renderOfficerIssues()" style="background:var(--glass);border:1px solid var(--glass-border);color:var(--text);padding:6px 10px;border-radius:6px;font-size:12px;">
          <option value="">All Statuses</option>
          <option value="Pending">Pending</option>
          <option value="In Progress">In Progress</option>
          <option value="Resolved">Resolved</option>
        </select>
        <select id="officerIssueSort" onchange="renderOfficerIssues()" style="background:var(--glass);border:1px solid var(--glass-border);color:var(--text);padding:6px 10px;border-radius:6px;font-size:12px;">
          <option value="">Newest first</option>
          <option value="urgent">Urgent first</option>
        </select>
        <button class="btn btn-sm" style="background:var(--green);border-color:var(--green);color:#fff;" onclick="SubUI.exportCsv('/api/export/issues')">&#8595; Export</button>
      </div>
    </div>
    <div id="officerIssueList"></div>
    <div class="card" style="margin-top:4px;">
      <div class="card-title" style="margin:0 0 12px;">Most Reported Issues</div>
      <div id="officerCommonIssues" style="min-height:40px;"></div>
    </div>

    <!-- Respond Modal -->
    <div class="modal-overlay" id="modal-officerRespond">
      <div class="modal-box">
        <div class="modal-title">Respond to Issue</div>
        <div id="officerRespondTitle" style="font-size:14px;color:var(--text-mid);margin-bottom:12px;"></div>
        <div id="officerRespondMedia" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:12px;"></div>
        <div class="f-field" style="margin-bottom:12px;">
          <span class="f-label">Update Status</span>
          <select id="officerRespondStatus" style="background:rgba(255,255,255,0.07);border:1px solid var(--glass-border);color:var(--text);padding:8px;border-radius:6px;width:100%;">
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Resolved">Resolved</option>
          </select>
        </div>
        <div class="f-field" style="margin-bottom:12px;">
          <span class="f-label">Urgency / Priority</span>
          <select id="officerRespondPriority" style="background:rgba(255,255,255,0.07);border:1px solid var(--glass-border);color:var(--text);padding:8px;border-radius:6px;width:100%;">
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
            <option value="Critical">Critical</option>
          </select>
        </div>
        <div class="f-field" style="margin-bottom:12px;">
          <span class="f-label">Assign To</span>
          <select id="officerRespondAssignee" style="background:rgba(255,255,255,0.07);border:1px solid var(--glass-border);color:var(--text);padding:8px;border-radius:6px;width:100%;">
            <option value="">Unassigned</option>
          </select>
        </div>
        <div class="f-field" style="margin-bottom:12px;">
          <span class="f-label">Due Date (SLA)</span>
          <input type="date" id="officerRespondDue">
        </div>
        <div id="officerRespondHistory" style="display:none;margin-bottom:12px;max-height:150px;overflow-y:auto;background:rgba(43,41,39,0.03);border:1px solid var(--glass-border);border-radius:8px;padding:10px 12px;font-size:12px;"></div>
        <div class="f-field">
          <span class="f-label">Response / Notes</span>
          <textarea id="officerRespondText" placeholder="Enter your response…" style="min-height:100px;"></textarea>
        </div>
        <div class="modal-actions" style="justify-content:space-between;flex-wrap:wrap;gap:8px;">
          <button type="button" class="btn btn-sm" style="background:var(--glass);border:1px solid var(--glass-border);color:var(--text);" onclick="openClaimForCurrentIssue()"><x-icon name="coins" size="13"/> Claim Expense for this Issue</button>
          <div style="display:flex;gap:8px;">
            <button class="modal-close-btn" onclick="closeModal('officerRespond')">Cancel</button>
            <button class="btn btn-sm" onclick="SubUI&&SubUI.busyClick?SubUI.busyClick(this,submitOfficerResponse):submitOfficerResponse()">Submit</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Issue location map modal -->
    <div class="modal-overlay" id="modal-officerMap">
      <div class="modal-box" style="max-width:640px;">
        <div class="modal-title">Issue Location</div>
        <div id="officerMapTitle" style="font-size:14px;color:var(--text-mid);margin-bottom:10px;"></div>
        <div id="officerMapCanvas" style="width:100%;height:360px;border-radius:8px;border:1px solid var(--glass-border);"></div>
        <div class="modal-actions">
          <button class="modal-close-btn" onclick="closeModal('officerMap')">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════ MAP (issue monitoring) ═══════════ -->
  <div class="tab-panel" id="panel-map">
    <div class="card" style="margin-top:0;">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
        <div class="card-title" style="margin:0;">Subdivision Issue Map</div>
      <div class="card-subtitle" style="margin-top:4px;">Numbered pins show reported issues. Tap a pin to view and respond.</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
          <label class="map-toggle" title="Toggle block partition boundaries">
            <input type="checkbox" id="officerLayerBlocks" onchange="toggleOfficerBlocks(this.checked)"> Block Partitions
          </label>
          <label class="map-toggle" title="Toggle facilities markers">
            <input type="checkbox" id="officerLayerFacilities" checked onchange="toggleOfficerFacilities(this.checked)"> Facilities
          </label>
          <label class="map-toggle" title="Toggle reported issue markers">
            <input type="checkbox" id="officerLayerIssues" checked onchange="toggleOfficerIssues(this.checked)"> Issue Pins
          </label>
          <label class="map-toggle" title="Toggle thermal issue heatmap and frequency categorization">
            <input type="checkbox" id="officerLayerHeat" onchange="toggleOfficerHeatmap(this.checked)"> Heatmap
          </label>
          <select id="officerHeatCategory" onchange="renderOfficerHeatmap()" style="display:none;background:var(--glass);border:1px solid var(--glass-border);color:var(--text);padding:6px 10px;border-radius:6px;font-size:12px;" title="Filter heatmap by issue category">
            <option value="">All categories</option>
          </select>
          <select id="officerFilterBlock" onchange="filterOfficerBlocks(this.value)" style="background:var(--glass);border:1px solid var(--glass-border);color:var(--text);padding:6px 10px;border-radius:6px;font-size:12px;" title="Filter map to a specific block">
            <option value="">All Blocks (1–14)</option>
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
          <select id="officerMapStatus" onchange="renderOfficerMapPins()" style="background:var(--glass);border:1px solid var(--glass-border);color:var(--text);padding:6px 10px;border-radius:6px;font-size:12px;">
            <option value="">All Statuses</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Resolved">Resolved</option>
          </select>
          <select id="officerMapPriority" onchange="renderOfficerMapPins()" style="background:var(--glass);border:1px solid var(--glass-border);color:var(--text);padding:6px 10px;border-radius:6px;font-size:12px;">
            <option value="">All Priorities</option>
            <option value="Critical">Critical</option>
            <option value="High">High</option>
            <option value="Medium">Medium</option>
            <option value="Low">Low</option>
          </select>
        </div>
      </div>
      <!-- Officer Heatmap & Hotspot Analysis Bar -->
      <div id="officerHeatAnalysisCard" class="card" style="display:none;margin-bottom:12px;padding:12px 16px;background:var(--glass);border-left:4px solid #ef4444;transition:all 0.25s ease;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
          <div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:rgba(239,68,68,0.15);color:#ef4444;font-size:13px;">🔥</span>
              <span style="font-size:13px;font-weight:700;color:var(--text);">Subdivision Issue Heatmap &amp; Category Frequency</span>
              <span style="font-size:11px;padding:2px 8px;border-radius:12px;background:rgba(239,68,68,0.15);color:#ef4444;font-weight:700;">Live Heat</span>
            </div>
            <div id="officerHeatSummaryLine" style="font-size:12px;color:var(--text-mid);margin-top:4px;">
              Analyzing issue frequency across subdivision...
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--text-dim);" title="Thermal intensity ramps up as reports cluster">
              <span>Low (1)</span>
              <div style="width:100px;height:9px;border-radius:4px;background:linear-gradient(to right, #38bdf8, #34d399, #facc15, #fb923c, #ef4444);box-shadow:inset 0 1px 2px rgba(0,0,0,0.25);"></div>
              <span style="color:#ef4444;font-weight:700;">Hotspot (4+)</span>
            </div>
          </div>
        </div>
        <div id="officerHeatCategoryPills" style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-top:10px;padding-top:8px;border-top:1px solid var(--glass-border);">
        </div>
      </div>
      <div id="officerMonitorMap" style="width:100%;height:520px;border-radius:10px;border:1px solid var(--glass-border);background:#EDE7DC;"></div>
      <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:13px;color:var(--text-mid);margin-top:10px;align-items:center;">
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:3px;background:#2563eb;display:inline-block;"></span>Block Partitions</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#e05555;display:inline-block;"></span>Pending</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#f5a623;display:inline-block;"></span>In Progress</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#888;display:inline-block;"></span>Resolved</span>
        <span style="margin-left:auto;font-size:12px;color:var(--text-dim);">Pins are numbered by report; click a pin for details and to respond.</span>
      </div>
      <div style="margin-top:12px;padding:12px 14px;border-left:3px solid var(--accent);background:rgba(250,125,9,0.06);border-radius:0 8px 8px 0;">
        <div style="font-size:12px;color:var(--text-dim);line-height:1.6;"><strong>Map scope &amp; limitations.</strong> Limited to the Terra Nova subdivision boundary, which is approximate. For precise locations, consult official subdivision plans. Only reported issues inside the boundary are shown.</div>
      </div>
    </div>
  </div>

  <!-- ═══════════ FILES ═══════════ -->
  <div class="tab-panel" id="panel-files">
    <div class="card" style="margin-top:0;">
      <div class="card-title">Send Report File to Admin</div>
      <div class="card-subtitle">Upload a file for the admin. The files you already sent are listed below.</div>
      <div class="upload-zone" id="uploadZone"
        onclick="document.getElementById('fileUploadInput').click()"
        ondragover="handleDragOver(event)"
        ondragleave="handleDragLeave(event)"
        ondrop="handleDrop(event)">
        <div class="upload-icon"><x-icon name="upload" size="26"/></div>
        <div class="upload-label">Click to upload or drag &amp; drop</div>
        <div class="upload-sub">Supports Excel, CSV, PDF, Word, or images · Max 10 MB</div>
        <input type="file" id="fileUploadInput" accept=".xlsx,.xls,.csv,.pdf,.doc,.docx,.png,.jpg,.jpeg,.gif,.webp" onchange="handleFileSelect(this)">
      </div>

      <div id="selectedFilePreview" style="display:none;" class="file-item">
        <span class="file-icon"><x-icon name="folder" size="16"/></span>
        <div class="file-info">
          <div class="file-name" id="selectedFileName">—</div>
          <div class="file-meta" id="selectedFileMeta">—</div>
        </div>
        <button class="btn btn-sm" style="background:var(--glass);border-color:var(--glass-border);color:var(--text-dim);" onclick="clearFileSelection()"><x-icon name="x" size="13"/> Remove</button>
      </div>

      <div class="f-row" style="margin-top:10px;">
        <div class="f-field">
          <span class="f-label">File Category</span>
          <select id="fileCategory">
            <option>Monthly Collection Report</option>
            <option>Incident Summary</option>
            <option>Maintenance Log</option>
            <option>Community Survey Results</option>
            <option>Budget Report</option>
            <option>Subdivision Project Documents</option>
            <option>Other</option>
          </select>
        </div>
        <div class="f-field">
          <span class="f-label">Reporting Period</span>
          <input type="text" id="filePeriod" placeholder="e.g. May 2026">
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Notes to Admin</span>
          <textarea id="fileNotes" placeholder="Any notes or context about this file…" style="min-height:64px;"></textarea>
        </div>
      </div>
      <button class="btn btn-full btn-green" onclick="sendFile()"><x-icon name="send" size="15"/> Send to Admin</button>
    </div>

    <div class="card">
      <div class="card-title">Sent Files History</div>
      <div id="sentFilesList"></div>
    </div>
  </div>

  <!-- ═══════════ EXPENSES (Officer Claims) ═══════════ -->
  <div class="tab-panel" id="panel-expenses">
    <div class="card" style="margin-top:0;">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
        <div>
          <div class="card-title" style="margin:0;">Expense Claims &amp; Reimbursements</div>
          <div class="card-subtitle" style="margin-top:4px;">Submit receipts for repair materials, contractors, or subdivision improvements. Approved claims auto-post to the HOA transparency ledger.</div>
        </div>
        <button class="btn btn-sm btn-green" onclick="openExpenseClaimModal()"><x-icon name="coins" size="14"/> + Submit Expense Claim</button>
      </div>

      <div id="officerExpenseList">
        <div class="empty-state">Loading your expense claims…</div>
      </div>
    </div>

    <!-- Submit Expense Claim Modal -->
    <div class="modal-overlay" id="modal-officerExpenseClaim">
      <div class="modal-box" style="max-width:540px;">
        <div class="modal-title">Submit Expense Claim</div>
        <div style="font-size:12px;color:var(--text-dim);margin-bottom:14px;">Upload your receipt and enter the details. Once submitted, the Admin can review and approve it into the HOA ledger with 1 click.</div>

        <div class="f-row">
          <div class="f-field">
            <span class="f-label">Title / Item Description</span>
            <input type="text" id="officerExpTitle" placeholder="e.g. Streetlight Bulb Replacements (Phase 2)" maxlength="200">
          </div>
        </div>

        <div class="f-row">
          <div class="f-field">
            <span class="f-label">Category</span>
            <select id="officerExpCategory">
              <option value="Maintenance">Maintenance</option>
              <option value="Utilities">Utilities</option>
              <option value="Security">Security</option>
              <option value="Landscaping">Landscaping</option>
              <option value="Amenities">Amenities</option>
              <option value="Insurance">Insurance</option>
              <option value="Administrative">Administrative</option>
              <option value="Events">Events</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="f-field">
            <span class="f-label">Amount (₱)</span>
            <input type="number" id="officerExpAmount" min="0.01" step="0.01" placeholder="0.00">
          </div>
        </div>

        <div class="f-row">
          <div class="f-field">
            <span class="f-label">Expense Date</span>
            <input type="date" id="officerExpDate">
          </div>
          <div class="f-field">
            <span class="f-label">Fund Source</span>
            <select id="officerExpFund">
              <option value="Operating">Operating Fund (Routine repairs/supplies)</option>
              <option value="Reserve">Reserve Fund (Major improvements)</option>
            </select>
          </div>
        </div>

        <div class="f-row">
          <div class="f-field">
            <span class="f-label">Link to Issue (Optional)</span>
            <select id="officerExpIssue">
              <option value="">— Not linked to an issue —</option>
            </select>
          </div>
        </div>

        <div class="f-row">
          <div class="f-field">
            <span class="f-label">Details / Justification</span>
            <textarea id="officerExpDescription" placeholder="Store name, contractor details, or problem resolved…" rows="3"></textarea>
          </div>
        </div>

        <div class="f-row">
          <div class="f-field">
            <span class="f-label">Official Receipt / Invoice Photo</span>
            <input type="file" id="officerExpReceipt" accept="image/*">
            <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">Upload receipt/invoice photo (PNG, JPG, max 8 MB).</div>
          </div>
        </div>

        <div id="officerExpMsg" style="font-size:12px;color:#c87941;margin-bottom:10px;"></div>

        <div class="modal-actions">
          <button type="button" class="modal-close-btn" onclick="closeModal('officerExpenseClaim')">Cancel</button>
          <button type="button" class="btn btn-sm btn-green" onclick="submitOfficerExpenseClaim()">Submit for Admin Approval</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════ MESSAGES ═══════════ -->
  <div class="tab-panel" id="panel-messages">
    <div class="card" style="padding:24px; margin-top:0;">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px;">
        <div>
          <div class="card-title" style="font-size:18px;">Community Correspondence</div>
          <div class="card-subtitle" style="margin-bottom:0;">Review documented communications with residents and the HOA Administration.</div>
        </div>
        <div style="font-size:12px;color:var(--text-dim);padding-top:4px;">Official record</div>
      </div>
      <div class="msg-layout">
        <div class="msg-sidebar">
          <div class="msg-sidebar-title" style="display:flex;justify-content:space-between;align-items:center;">Conversations
            <button class="btn btn-sm" style="padding:2px 8px;font-size:11px;" onclick="openOfficerNewConv()">+ New</button>
          </div>
          <div class="msg-sidebar-threads"><div style="color:var(--text-dim);font-size:12px;padding:12px;">Loading…</div></div>
        </div>
        <div class="msg-main">
          <div class="msg-header">
            <div class="msg-header-avatar ta-admin" id="chat-avatar">SA</div>
            <div>
              <div class="msg-header-name" id="chat-name">Subdivision Admin</div>
              <div class="msg-header-sub" id="chat-sub">Official communication record</div>
            </div>
          </div>
          <div class="msg-body" id="msgBody"></div>
          <div class="msg-compose">
            <textarea id="msgInput" placeholder="Type a message…" rows="1"
              onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}"></textarea>
            <button class="send-btn" onclick="sendMessage()" title="Send"><x-icon name="send" size="16"/></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- New Conversation Modal (root-level so position:fixed works from any tab) -->
<div class="modal-overlay" id="modal-officerNewConv">
  <div class="modal-box">
    <div class="modal-title">New Message</div>
    <div class="f-field" style="margin-bottom:12px;">
      <span class="f-label">To</span>
      <select id="officerConvRecipient" style="width:100%;padding:8px 10px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:6px;color:inherit;font-size:13px;box-sizing:border-box;">
        <option value="admin|0">HOA Admin</option>
      </select>
    </div>
    <div class="f-field" style="margin-bottom:16px;">
      <span class="f-label">Subject</span>
      <input type="text" id="officerConvTitle" placeholder="e.g. Block 2 maintenance request…">
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('officerNewConv')">Cancel</button>
      <button class="btn btn-sm" onclick="startOfficerConversation()">Send</button>
    </div>
  </div>
</div>

<div id="toast"></div>
<div id="annNotif">
  <span style="font-size:18px;"><x-icon name="megaphone" size="18"/></span>
  <div style="flex:1;min-width:0;">
    <div class="ann-meta">New Announcement</div>
    <div class="ann-notif-title" id="annNotifTitle">—</div>
  </div>
  <button class="btn-view" id="annNotifViewBtn">View</button>
  <button class="btn-close" onclick="document.getElementById('annNotif').classList.remove('show')">✕</button>
</div>

<script>window.__OFFICER = { id: {{ $officerId ?? 'null' }}, name: @json($officerName), role: 'Officer' };</script>
<script src="{{ asset('js/officer-portal.js') . '?v=' . filemtime(public_path('js/officer-portal.js')) }}"></script>
<script src="{{ asset('js/notifications.js') . '?v=' . filemtime(public_path('js/notifications.js')) }}"></script>

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
      <button class="btn btn-sm" onclick="document.getElementById('modal-annView').classList.remove('open')">Close</button>
    </div>
  </div>
</div>
</body>
</html>