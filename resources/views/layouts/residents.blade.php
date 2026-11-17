<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Resident Portal</title>
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
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap"></noscript>
<link rel="preload" as="image" href="{{ asset('images/admin_hero_bg_day.jpg') }}">
<link rel="preload" as="image" href="{{ asset('images/admin_hero_bg_clean.jpg') }}">
<link rel="stylesheet" href="{{ asset('css/residents.css') . '?v=' . filemtime(public_path('css/residents.css')) }}">
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
<body class="residents-body">

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
    <div class="brand-sub">Terra Nova Homeowners Association</div>
  </div>
  <ul>
    <li class="tab-btn active" onclick="switchTab('overview')" id="tab-overview"><x-icon name="home" size="15"/> Overview</li>
    <li class="tab-btn" onclick="switchTab('payments')" id="tab-payments"><x-icon name="credit-card" size="15"/> Payments</li>
    <li class="tab-btn" onclick="switchTab('issues')" id="tab-issues"><x-icon name="wrench" size="15"/> Issues</li>
    <li class="tab-btn" onclick="switchTab('messages')" id="tab-messages"><x-icon name="message" size="15"/> Messages <span class="tab-badge" id="badge-messages" style="display:none;">0</span></li>
    <li class="tab-btn" onclick="switchTab('calendar')" id="tab-calendar"><x-icon name="calendar" size="15"/> Calendar</li>
    <li class="tab-btn" onclick="switchTab('expenses')" id="tab-expenses"><x-icon name="coins" size="15"/> Expenses</li>
    <li class="tab-btn" onclick="switchTab('recs')" id="tab-recs"><x-icon name="lightbulb" size="15"/> Suggestions</li>
    <li class="tab-btn" onclick="switchTab('policies')" id="tab-policies"><x-icon name="scroll" size="15"/> Policies</li>
    <li class="tab-btn" onclick="switchTab('map')" id="tab-map"><x-icon name="map" size="15"/> Map</li>
  </ul>

  <!-- Sidebar tools: search + notifications -->
  <div class="sidebar-tools" style="display:none;">
    <button type="button" class="nc-btn" onclick="SubUI.openSearch()" title="Search (Ctrl+K)" aria-label="Search"><x-icon name="search" size="14"/></button>
  </div>

  <!-- Sidebar Profile Card -->
  <div class="sidebar-footer">
    <div class="sidebar-profile" onclick="toggleUserMenu()" title="{{ $residentName }} (Click for Account Settings)">
      <div class="avatar-wrap" onclick="event.stopPropagation(); document.getElementById('profileInput').click()" style="width:38px;height:38px;margin:0;flex-shrink:0;">
        <img id="profilePreview" src="{{ $avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($residentName).'&background=c87941&color=fff&size=80&font-size=0.4' }}" alt="Profile" style="width:38px; height:38px; border-radius:50%; object-fit:cover;">
        <input type="file" id="profileInput" accept="image/*" onchange="changeImage('profilePreview',this)" style="display:none;">
      </div>
      <div style="flex:1;min-width:0;margin-left:10px;text-align:left;">
        <div style="font-weight:600; font-size:13px; color:var(--text); max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" id="householdName" contenteditable="true" spellcheck="false" title="Click to edit name">{{ $residentName }}</div>
        <div style="font-size:11.5px; color:var(--text-dim); margin-top:2px;" id="locationText">Block/Lot: {{ $blockLot }}</div>
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
        <div class="topbar-title" id="topbarTitle">Resident Portal</div>
        <div class="topbar-sub" id="topbarSubtitle">Terra Nova Homeowners Association</div>
      </div>
    </div>
    <div class="topbar-actions">
      <!-- Search shortcut -->
      <div class="topbar-search-box" onclick="SubUI.openSearch()" title="Search (Ctrl+K)">
        <span style="color:#8C857B;display:flex;align-items:center;"><x-icon name="search" size="14"/></span>
        <span style="color:#7D766D;font-size:12.5px;">Search announcements, policies, dues…</span>
      </div>

      <!-- Theme Toggle (Light / Dark Mode) -->
      <button class="topbar-btn keep-mobile" id="themeToggleBtn" onclick="toggleTheme()" title="Toggle Light / Dark mode" aria-label="Toggle theme">
        <span id="themeToggleIcon"><x-icon name="moon" size="15"/></span>
      </button>

      <!-- Notification Center / Alerts -->
      <div class="topbar-notif-wrap">
        @include('partials.notification-center')
      </div>

      <form id="logoutForm" action="{{ url('logout') }}" method="POST" style="display:none;">@csrf<input type="hidden" name="portal" value="{{ $role ?? 'resident' }}"></form>

      <!-- User Profile Dropdown Menu -->
      <div class="user-menu-wrap" style="position:relative;">
        <button class="topbar-avatar keep-mobile" id="userMenuButton" aria-expanded="false" aria-controls="userMenuPopover" onclick="toggleUserMenu()" title="{{ $residentName }} (Account menu)">
          {{ strtoupper(substr($residentName, 0, 2)) }}
        </button>
        <div class="user-menu-popover" id="userMenuPopover" role="menu" aria-label="Account menu">
          <!-- Header: User info -->
          <div class="user-menu-header">
            <div class="user-menu-avatar">{{ strtoupper(substr($residentName, 0, 2)) }}</div>
            <div class="user-menu-info">
              <div class="user-menu-name">{{ $residentName }}</div>
              <div class="user-menu-role">Resident · {{ $blockLot }}</div>
              <div class="user-menu-status"><span class="user-status-dot"></span> Online</div>
            </div>
          </div>
          <div class="user-menu-divider"></div>
          <!-- Menu items -->
          <div class="user-menu-items">
            <a href="{{ url('settings') }}?portal={{ $role ?? 'resident' }}" class="user-menu-item active" role="menuitem">
              <span class="user-menu-ic"><x-icon name="gear" size="16"/></span>
              <div class="user-menu-text">
                <div class="user-menu-title">Account Settings</div>
                <div class="user-menu-desc">Profile, account, and preferences</div>
              </div>
            </a>
            <a href="{{ url('settings') }}?portal={{ $role ?? 'resident' }}#notifications" class="user-menu-item" role="menuitem">
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

  <!-- ═══════════ OVERVIEW ═══════════ -->
  <div class="tab-panel active" id="panel-overview">
    <!-- 1. HERO BANNER -->
    <div class="dash-hero-card">
      <div class="dash-hero-content">
        <div class="dash-hero-indicator"></div>
        <div class="dash-hero-body">
          <h1 class="dash-hero-title">
            <span id="greetingText">Welcome back, {{ explode(' ', $residentName)[0] }} 👋</span>
          </h1>
          <p class="dash-hero-subtitle">Here's what's happening in Terra Nova today.</p>
          <div class="dash-hero-pills">
            <span class="dash-pill"><x-icon name="calendar" size="14"/> {{ date('M d, Y (D)') }}</span>
            <span class="dash-pill" id="heroWeatherPill" title="Live Subdivision Weather"><x-icon name="sun" size="14"/><span id="heroWeatherText">29°C Partly Cloudy</span></span>
            <span class="dash-pill"><x-icon name="home" size="14"/> Block/Lot: {{ $blockLot }}</span>
          </div>
        </div>
      </div>
    </div>
    <script>
      (function(){
        const hrs = new Date().getHours();
        let greet = "Welcome back";
        if (hrs < 12) greet = "Good morning";
        else if (hrs < 18) greet = "Good afternoon";
        else greet = "Good evening";
        document.addEventListener("DOMContentLoaded", () => {
          const gEl = document.getElementById("greetingText");
          if(gEl) gEl.innerHTML = `${greet}, {{ explode(' ', $residentName)[0] }} 👋`;
        });
      })();
    </script>

    <!-- 2. 4 CONCISE HIGHLIGHT CARDS ("Except a bit less") -->
    <div class="dash-kpi-grid">
      <!-- Dues / Balance -->
      <div class="dash-kpi-card" onclick="switchTab('payments')" title="View dues & payments">
        <div class="dash-kpi-icon orange"><x-icon name="credit-card" size="18"/></div>
        <div class="dash-kpi-label">Outstanding Balance</div>
        <div class="dash-kpi-val" id="residentBalance">₱{{ number_format((float)($balance ?? 0), 2) }}</div>
        <div class="dash-kpi-sub green" style="display:flex;align-items:center;justify-content:space-between;width:100%;margin-top:6px;">
          <span>Settle via GCash</span>
          <button class="btn btn-sm btn-orange" style="padding:2px 8px;font-size:11px;" onclick="event.stopPropagation();quickPay();">Pay</button>
        </div>
      </div>

      <!-- Maintenance / Issues -->
      <div class="dash-kpi-card" onclick="switchTab('issues')" title="Report or track maintenance concerns">
        <div class="dash-kpi-icon amber"><x-icon name="wrench" size="18"/></div>
        <div class="dash-kpi-label">Maintenance &amp; Issues</div>
        <div class="dash-kpi-val" style="font-size:20px;font-weight:700;">Report Issue</div>
        <div class="dash-kpi-sub muted">Fixes, lighting, &amp; drainage</div>
      </div>

      <!-- Bulletins & Notices -->
      <div class="dash-kpi-card" onclick="document.getElementById('announcementList').closest('.card').scrollIntoView({behavior:'smooth'})" title="Read neighborhood announcements">
        <div class="dash-kpi-icon blue"><x-icon name="megaphone" size="18"/></div>
        <div class="dash-kpi-label">Community Notices</div>
        <div class="dash-kpi-val" style="font-size:20px;font-weight:700;">Bulletins</div>
        <div class="dash-kpi-sub muted">Official HOA announcements</div>
      </div>

      <!-- Calendar & Events -->
      <div class="dash-kpi-card" onclick="switchTab('calendar')" title="View community calendar">
        <div class="dash-kpi-icon green"><x-icon name="calendar" size="18"/></div>
        <div class="dash-kpi-label">Community Events</div>
        <div class="dash-kpi-val" style="font-size:20px;font-weight:700;">Calendar</div>
        <div class="dash-kpi-sub muted">HOA meetings &amp; drives</div>
      </div>
    </div>

    <!-- 3. ELDER-FRIENDLY QUICK ACTIONS -->
    <div class="card" id="residentQuickActions" style="margin-top:0;padding:20px 24px;">
      <div class="card-title" style="font-size:15px;margin-bottom:2px;">Quick Actions</div>
      <div class="card-subtitle" style="margin-bottom:14px;">Common community tasks, one tap away.</div>
      <div class="res-quick-actions-row">
        <button class="btn btn-full res-qa-btn orange-accent" onclick="switchTab('payments')">
          <x-icon name="credit-card" size="18"/>
          <span>Pay My Dues</span>
        </button>
        <button class="btn btn-full res-qa-btn" onclick="switchTab('issues')">
          <x-icon name="megaphone" size="18"/>
          <span>Report a Problem</span>
        </button>
        <button class="btn btn-full res-qa-btn emergency" onclick="switchTab('issues'); setTimeout(startEmergency, 200);">
          <x-icon name="alert" size="18"/>
          <span>Emergency Alert</span>
        </button>
        <button class="btn btn-full res-qa-btn" onclick="switchTab('messages')">
          <x-icon name="mail" size="18"/>
          <span>Message the HOA</span>
        </button>
      </div>
    </div>

    <!-- 4. ANNOUNCEMENTS & HOUSEHOLD MEMBERS -->
    <div class="bento-grid">
      <!-- Announcements Box (Span 8 - Front & Center) -->
      <div class="card bento-col-8">
        <div class="card-title" style="display:flex;align-items:center;justify-content:space-between;">
          <span>Community Announcements</span>
          <span style="font-size:11.5px;color:var(--text-dim);font-weight:normal;">Latest bulletins</span>
        </div>
        <div class="card-subtitle">Neighborhood notices and official community updates.</div>
        <div id="announcementList"><div class="skeleton sk-card" style="margin-bottom:10px;"></div><div class="skeleton sk-card" style="margin-bottom:10px;"></div></div>
      </div>

      <!-- Household Members (Span 4) -->
      <div class="card bento-col-4">
        <div class="card-title">Household Members</div>
        <div class="card-subtitle">Add or update the list of residents living in your home.</div>
        <div class="member-grid" id="memberGrid"><div class="skeleton sk-card"></div><div class="skeleton sk-card"></div><div class="skeleton sk-card"></div></div>
        <div class="add-row">
          <input type="text" id="memberName" placeholder="Full name">
          <input type="text" id="memberRole" placeholder="Role (Head, Spouse…)">
          <button class="btn" onclick="addMember()">+ Add</button>
        </div>
      </div>

      
        <!-- HOA Policies Preview Card (Span 8) -->
        <div class="card bento-col-8">
          <div class="card-title" style="display:flex;justify-content:space-between;align-items:center;">
            <span>HOA Community Policies &amp; Regulations</span>
            <button class="btn btn-sm" onclick="switchTab('policies')"><x-icon name="scroll" size="14"/> View All Policies</button>
          </div>
          <div class="card-subtitle">Essential guidelines for property maintenance, sanitation, security, and peaceful community living.</div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;margin-top:14px;">
            <div style="padding:14px;background:rgba(250,125,9,0.06);border:1px solid rgba(250,125,9,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
              <div style="font-weight:600;color:var(--accent);margin-bottom:4px;">1. Membership &amp; Community Conduct</div>
              <div style="font-size:12px;color:var(--text-mid);line-height:1.5;">Resident rights, good-neighbor conduct, and community etiquette.</div>
            </div>
            <div style="padding:14px;background:rgba(250,125,9,0.06);border:1px solid rgba(250,125,9,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
              <div style="font-weight:600;color:var(--accent);margin-bottom:4px;">2. Property Use &amp; Structural Guidelines</div>
              <div style="font-size:12px;color:var(--text-mid);line-height:1.5;">Architectural guidelines, lawn upkeep, and construction clearance permits.</div>
            </div>
            <div style="padding:14px;background:rgba(250,125,9,0.06);border:1px solid rgba(250,125,9,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
              <div style="font-weight:600;color:var(--accent);margin-bottom:4px;">3. Noise, Conduct &amp; Community Standards</div>
              <div style="font-size:12px;color:var(--text-mid);line-height:1.5;">Standard quiet hours (10:00 PM – 6:00 AM) and social gathering guidelines.</div>
            </div>
            <div style="padding:14px;background:rgba(250,125,9,0.06);border:1px solid rgba(250,125,9,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
              <div style="font-weight:600;color:var(--accent);margin-bottom:4px;">4. Waste Management &amp; Sanitation</div>
              <div style="font-size:12px;color:var(--text-mid);line-height:1.5;">Garbage segregation, scheduled collection, and sanitation responsibilities.</div>
            </div>
          </div>
        </div>
    </div>
  </div>

  <!-- ═══════════ MESSAGES ═══════════ -->
  <div class="tab-panel" id="panel-messages">
    <div class="card" style="padding:24px; margin-top:0;">
      <div class="card-title">Community Group Chat</div>
      <div class="card-subtitle">Send a message to the HOA office or your neighbors. Replies appear here.</div>
      <div class="msg-layout">
        <!-- Sidebar threads -->
        <div class="msg-sidebar">
          <div class="msg-sidebar-title" style="display:flex;justify-content:space-between;align-items:center;">Conversations
            <button class="btn btn-sm" style="padding:2px 8px;font-size:11px;" onclick="openNewConvModal()">+ New</button>
          </div>
          <div class="msg-sidebar-threads">
            <div style="color:var(--text-dim);font-size:12px;padding:12px;">Loading…</div>
          </div>
        </div>

        <!-- Chat window -->
        <div class="msg-main">
          <div class="msg-header">
            <div class="msg-header-avatar ta-admin" id="chat-avatar">SA</div>
            <div>
              <div class="msg-header-name" id="chat-name">Subdivision Admin</div>
              <div class="msg-header-sub" id="chat-sub">Official communication record</div>
            </div>
          </div>
          <div class="msg-body" id="msgBody">
            <div style="color:var(--text-dim);text-align:center;padding:40px;">Select a conversation to start messaging.</div>
          </div>
          <div class="msg-compose">
            <textarea id="msgInput" placeholder="Type a message…" rows="1" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault(); sendMessage();}"></textarea>
            <button class="send-btn" onclick="sendMessage()"><x-icon name="send" size="16"/></button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════ ISSUES ═══════════ -->
  <div class="tab-panel" id="panel-issues">
    <div class="card" style="margin-top:0;">
      <button type="button" class="btn btn-full" id="emergencyBtn" onclick="startEmergency()"
        style="background:#e05555;border-color:#e05555;color:#fff;margin-bottom:14px;"><x-icon name="alert" size="15"/> Report an Emergency</button>
      <div id="issue-emergency-note" style="display:none;background:rgba(224,85,85,0.12);border:1px solid rgba(224,85,85,0.35);border-radius:8px;padding:8px 12px;margin-bottom:12px;font-size:13px;color:#c94a4a;">
        <x-icon name="alert" size="14"/> Emergency reports are marked <strong>Critical</strong> and alert HOA staff immediately. Use this only for urgent safety or security situations.
      </div>
      <div class="card-title">Report a Concern or Complaint</div>
      <div class="card-subtitle">Describe the problem below. Photos and the location help our team fix it faster.</div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Category</span>
          <select id="issueCategory" onchange="toggleEmergencyNote()">
            <option value="">Select category…</option>
            <option>Emergency</option>
            <option>Road / Pavement</option>
            <option>Drainage / Flooding</option>
            <option>Street Lighting</option>
            <option>Garbage / Sanitation</option>
            <option>Noise Complaint</option>
            <option>Security Concern</option>
            <option>Utilities</option>
            <option>Other</option>
          </select>
        </div>

      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Issue Title</span>
          <input type="text" id="issueTitle" placeholder="Brief description of the issue">
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Details</span>
          <textarea id="issueBody" placeholder="Describe the issue in detail — location, when it started, how it affects residents…" style="min-height:88px;resize:vertical;"></textarea>
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label"><x-icon name="camera" size="14"/> Photos (optional, up to 5, max 8 MB each)</span>
          <input type="file" id="issueImages" accept="image/*" multiple style="font-size:13px;padding:6px 0;">
          <div style="font-size:13px;color:var(--text-dim);margin-top:4px;">Attach photos to help our team understand the issue.</div>
          <div style="display:flex;align-items:center;gap:8px;margin-top:6px;flex-wrap:wrap;">
            <button type="button" class="btn" style="font-size:13px;padding:5px 14px;" onclick="document.getElementById('issueCamera').click()"><x-icon name="camera" size="14"/> Take Photo</button>
            <input type="file" id="issueCamera" accept="image/*" capture="environment" style="display:none">
            <span id="issueCameraCount" style="font-size:12.5px;color:var(--text-dim);"></span>
          </div>
          <div class="file-chips" id="issueImageChips"></div>
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label"><x-icon name="paperclip" size="14"/> Supporting Documents (optional, up to 5)</span>
          <input type="file" id="issueDocuments" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple style="font-size:13px;padding:6px 0;">
          <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">PDF, Word, or Excel files (max 10 MB each).</div>
          <div class="file-chips" id="issueDocChips"></div>
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label"><x-icon name="map-pin" size="14"/> Location (optional)</span>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:6px;">
            <button type="button" class="btn" style="font-size:12px;padding:4px 12px;" onclick="useMyLocation()">Use My GPS</button>
            <button type="button" class="btn" style="font-size:12px;padding:4px 12px;" onclick="openIssuePicker()">Pin on Map</button>
            <span id="issue-loc-display" style="font-size:12px;color:var(--text-dim);">No location set</span>
          </div>
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:6px;">Tip: click the map or use GPS, then drag the pin to fine-tune. The pin must stay inside the Terra Nova boundary.</div>
          <div id="issue-picker-map" style="display:none;height:240px;border-radius:8px;border:1px solid var(--glass-border);margin-bottom:8px;z-index:1;"></div>
          <input type="hidden" id="issue-lat">
          <input type="hidden" id="issue-lng">
        </div>
      </div>
      <button class="btn btn-full btn-orange" onclick="SubUI&&SubUI.busyClick?SubUI.busyClick(this,submitIssue):submitIssue()">Submit Report</button>
    </div>

    <div class="card">
      <div class="card-title" style="display:flex;justify-content:space-between;align-items:center;">My Submitted Reports</div>
      <div id="issueList"><div class="skeleton sk-card" style="margin-bottom:10px;"></div><div class="skeleton sk-card"></div></div>
    </div>
  </div>

  <!-- Issue Timeline Modal -->
  <div class="modal-overlay" id="modal-timeline" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="max-width:520px;">
      <div class="modal-title" id="timelineTitle">Report Timeline</div>
      <div id="timelineMeta" style="font-size:13px;color:var(--text-mid);margin-bottom:14px;"></div>
      <div id="timelineList" style="max-height:360px;overflow-y:auto;"></div>
      <div class="modal-actions">
        <button class="modal-close-btn" onclick="document.getElementById('modal-timeline').classList.remove('open')">Close</button>
      </div>
    </div>
  </div>

  <!-- Add Details Modal (append info/photos to an open report) -->
  <div class="modal-overlay" id="modal-addDetails" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="max-width:520px;">
      <div class="modal-title" id="addDetailsTitle">Add Details</div>
      <div id="addDetailsMeta" style="font-size:13px;color:var(--text-mid);margin-bottom:14px;"></div>
      <div class="f-row">
        <div class="f-field"><span class="f-label">More details (optional)</span>
          <textarea id="addDetailsText" placeholder="Add more context — what changed, when it happens, anything that helps the team…" style="min-height:90px;"></textarea>
        </div>
      </div>
      <div class="f-row">
        <div class="f-field"><span class="f-label"><x-icon name="camera" size="14"/> Photos (optional, up to 5, max 8 MB each)</span>
          <input type="file" id="addDetailsImages" accept="image/*" multiple>
          <div class="file-chips" id="addDetailsImageChips"></div>
        </div>
      </div>
      <div class="f-row">
        <div class="f-field"><span class="f-label"><x-icon name="paperclip" size="14"/> Documents (optional, up to 5)</span>
          <input type="file" id="addDetailsDocs" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple>
          <div class="file-chips" id="addDetailsDocChips"></div>
        </div>
      </div>
      <div style="font-size:12px;color:#f08080;margin-bottom:8px;" id="addDetailsMsg"></div>
      <div class="modal-actions">
        <button class="modal-close-btn" onclick="document.getElementById('modal-addDetails').classList.remove('open')">Cancel</button>
        <button class="btn btn-sm" onclick="SubUI&&SubUI.busyClick?SubUI.busyClick(this,submitAddDetails):submitAddDetails()">Submit</button>
      </div>
    </div>
  </div>

  <!-- ═══════════ RECOMMENDATIONS ═══════════ -->
  <div class="tab-panel" id="panel-recs">
    <div class="card" style="margin-top:0;">
      <div class="card-title">Share a Suggestion or Initiative</div>
      <div class="card-subtitle">Have an idea to improve the subdivision? Send it here and the HOA will review it.</div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Category</span>
          <select id="recCategory">
            <option value="">Select category…</option>
            <option>Infrastructure</option>
            <option>Environment / Greenery</option>
            <option>Security</option>
            <option>Community Events</option>
            <option>Waste Management</option>
            <option>Facilities &amp; Amenities</option>
            <option>Other</option>
          </select>
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Suggestion Title</span>
          <input type="text" id="recTitle" placeholder="One-line summary of your idea">
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Details</span>
          <textarea id="recBody" placeholder="Explain your suggestion — why it benefits the community, estimated impact, how it could be implemented…"></textarea>
        </div>
      </div>
      <button class="btn btn-full btn-orange" onclick="SubUI&&SubUI.busyClick?SubUI.busyClick(this,submitRec):submitRec()">Submit Suggestion</button>
    </div>

    <div class="card">
      <div class="card-title">Community Suggestions — Most Supported</div>
      <div id="recList"></div>
    </div>
  </div>

  <!-- ═══════════ MAP ═══════════ -->
  <div class="tab-panel" id="panel-map">
    <div class="card" style="margin-bottom:12px;padding:10px 16px;">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <div>
          <span style="font-size:14px;font-weight:600;color:var(--text);">Terra Nova Subdivision Map</span>
          <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">View community facilities, households, and reported neighborhood issues. The heatmap reveals thermal density and dominant issue categories per area.</div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
          <label class="map-toggle" title="Toggle block partition boundaries">
            <input type="checkbox" id="res-layer-blocks" onchange="toggleResidentBlocks(this.checked)"> Block Partitions
          </label>
          <label class="map-toggle is-sub-toggle" id="wrap-res-block-names" title="Toggle block name labels on the map">
            <input type="checkbox" id="res-layer-block-names" checked onchange="toggleResidentBlockNames(this.checked)"> Block Names
          </label>
          <label class="map-toggle is-sub-toggle" id="wrap-res-block-starts" title="Toggle lot starting point directional markers">
            <input type="checkbox" id="res-layer-block-starts" checked onchange="toggleResidentBlockStarts(this.checked)"> Lot Starts
          </label>
          <select id="res-filter-block" onchange="filterResidentBlocks(this.value)" class="btn btn-sm" style="padding:4px 8px;font-size:12px;font-weight:600;" title="Filter map to a specific block">
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
          <label class="map-toggle" title="Toggle facilities markers">
            <input type="checkbox" id="res-layer-facilities" checked onchange="toggleResidentFacilities(this.checked)"> Facilities
          </label>
          <label class="map-toggle" title="Toggle reported issue markers">
            <input type="checkbox" id="res-layer-issues" checked onchange="toggleResidentIssues(this.checked)"> Issue Pins
          </label>
          <label class="map-toggle" title="Toggle thermal issue heatmap and frequency categorization">
            <input type="checkbox" id="res-layer-heat" onchange="toggleResidentHeatmap(this.checked)"> Heatmap
          </label>
          <select id="res-heat-category" onchange="renderResidentHeatmap()" class="btn btn-sm" style="display:none;padding:4px 8px;font-size:12px;" title="Filter the heatmap by issue category">
            <option value="">All categories</option>
          </select>
          <button class="btn btn-sm" onclick="if(resMapInst)resMapInst.setView(RES_CENTER,17)">⌖ Reset View</button>
        </div>
      </div>
    </div>

    <!-- Resident Heatmap & Hotspot Analysis Bar -->
    <div id="res-heatmap-analysis-card" class="card" style="display:none;margin-bottom:12px;padding:12px 16px;background:var(--glass);border-left:4px solid #ef4444;transition:all 0.25s ease;">
      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:rgba(239,68,68,0.15);color:#ef4444;font-size:13px;">🔥</span>
            <span style="font-size:13px;font-weight:700;color:var(--text);">Subdivision Issue Heatmap &amp; Category Frequency</span>
            <span style="font-size:11px;padding:2px 8px;border-radius:12px;background:rgba(239,68,68,0.15);color:#ef4444;font-weight:700;">Live Heat</span>
          </div>
          <div id="res-heatmap-summary-line" style="font-size:12px;color:var(--text-mid);margin-top:4px;">
            Analyzing issue frequency across subdivision...
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--text-dim);" title="Thermal intensity ramps up as reports cluster in that area">
            <span>Low (1)</span>
            <div style="width:100px;height:9px;border-radius:4px;background:linear-gradient(to right, #38bdf8, #34d399, #facc15, #fb923c, #ef4444);box-shadow:inset 0 1px 2px rgba(0,0,0,0.25);"></div>
            <span style="color:#ef4444;font-weight:700;">Hotspot (4+)</span>
          </div>
        </div>
      </div>
      <div id="res-heatmap-category-pills" style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-top:10px;padding-top:8px;border-top:1px solid var(--glass-border);">
      </div>
    </div>
    <div class="card" style="padding:0;overflow:hidden;margin-top:0;">
      <div id="resident-map" style="width:100%;height:480px;"></div>
    </div>
    <div class="card" style="margin-top:12px;">
      <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:13px;color:var(--text-mid);align-items:center;">
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:3px;background:#2563eb;display:inline-block;"></span>Block Partitions</span>
        <span style="display:flex;align-items:center;gap:5px;"><span class="sub-start-dir-marker is-sample" style="display:inline-flex;position:static;vertical-align:middle;margin-right:4px;transform:none;"><span class="start-dir-arrow">↑</span><span class="start-dir-code">N</span></span>Lot 1 Start Point</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#4CAF50;display:inline-block;"></span>Facility</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#e05555;display:inline-block;"></span>Issue (Pending)</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#f5a623;display:inline-block;"></span>Issue (In Progress)</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#888;display:inline-block;"></span>Issue (Resolved, ≤7d)</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#e05555;border:2px solid #FFD700;display:inline-block;"></span>Your report</span>
        <span style="margin-left:auto;font-size:12px;color:var(--text-dim);">Issue pins are numbered by report sequence</span>
      </div>
    </div>
    <div class="card" style="margin-top:12px;border-left:3px solid var(--accent);">
      <div style="font-size:12px;color:var(--text-dim);line-height:1.6;">
        <strong>Map scope &amp; limitations.</strong> This map is limited to the Terra Nova subdivision boundary, which is approximate. For precise locations, please consult the official subdivision plans.
      </div>
    </div>
  </div>

  <!-- ═══════════ HOA EXPENSES ═══════════ -->
  <div class="tab-panel" id="panel-expenses">
    <div class="card">
      <div class="card-title">HOA Expenses &amp; Financials</div>
      <div class="card-subtitle">See how your dues are spent. The HOA office updates this list.</div>
      <div id="expensesList"><div class="empty-state">Loading expenses...</div></div>
    </div>
  </div>

  <!-- ═══════════ PAYMENTS (GCash) ═══════════ -->
  <div class="tab-panel" id="panel-payments">
    <!-- Top Hero Banner Card -->
    <div class="pay-hero-banner">
      <div class="pay-hero-content">
        <div class="pay-hero-icon-box">
          <x-icon name="credit-card" size="24"/>
        </div>
        <div>
          <h2 class="pay-hero-title">Payments</h2>
          <p class="pay-hero-subtitle">Submit payments, view your statements, and track your transaction history.</p>
        </div>
      </div>
      <div class="pay-hero-bg-visual" aria-hidden="true"></div>
    </div>

    <!-- Navigation Tabs Bar -->
    <div class="pay-tabs-bar" id="payTabs">
      <button class="pay-tab fin-tab active" onclick="payTab('pay',this)" id="payTabBtn-pay">
        <x-icon name="credit-card" size="15"/> Pay
      </button>
      <button class="pay-tab fin-tab" onclick="payTab('statement',this)" id="payTabBtn-statement">
        <x-icon name="file-text" size="15"/> My Statement
      </button>
      <button class="pay-tab fin-tab" onclick="payTab('transparency',this)" id="payTabBtn-transparency">
        <x-icon name="bar-chart-2" size="15"/> Transparency
      </button>
      <button class="pay-tab fin-tab" onclick="payTab('history',this)" id="payTabBtn-history">
        <x-icon name="clock" size="15"/> History
      </button>
    </div>

    <!-- 3 Overview KPI Cards -->
    <div class="pay-kpi-grid">
      <!-- Card 1: Outstanding Balance -->
      <div class="pay-kpi-card" id="cardKpiBalance">
        <div class="pay-kpi-icon-wrap peach">
          <x-icon name="credit-card" size="22"/>
        </div>
        <div class="pay-kpi-body">
          <div class="pay-kpi-label">Outstanding Balance</div>
          <div class="pay-kpi-val" id="payKpiBalance">₱0.00</div>
          <div class="pay-kpi-status" id="payKpiBalanceStatus">
            <span class="status-indicator green" style="display:inline-flex;align-items:center;gap:6px;color:#2E7D32;font-size:12px;font-weight:600;"><x-icon name="check-circle" size="13"/> No pending balance</span>
          </div>
        </div>
      </div>

      <!-- Card 2: Pending Payments -->
      <div class="pay-kpi-card" id="cardKpiPending">
        <div class="pay-kpi-icon-wrap amber">
          <x-icon name="clock" size="22"/>
        </div>
        <div class="pay-kpi-body">
          <div class="pay-kpi-label">Pending Payments</div>
          <div class="pay-kpi-val" id="payKpiPending">0</div>
          <div class="pay-kpi-status">
            <span class="pay-status-pill under-review"><x-icon name="clock" size="11"/> Under Review</span>
          </div>
        </div>
      </div>

      <!-- Card 3: Validated Payments -->
      <div class="pay-kpi-card" id="cardKpiValidated">
        <div class="pay-kpi-icon-wrap green">
          <x-icon name="check-circle" size="22"/>
        </div>
        <div class="pay-kpi-body">
          <div class="pay-kpi-label">Validated Payments</div>
          <div class="pay-kpi-val" id="payKpiValidated">0</div>
          <div class="pay-kpi-status">
            <span class="pay-kpi-subtext">Total verified transactions</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── TAB 1: PAY ── -->
    <div class="fin-section active" data-pay="pay">
      <div class="pay-main-grid">
        <!-- Left: Pay via GCash Card -->
        <div class="pay-card">
          <div class="pay-card-header">
            <div class="pay-card-icon gcash-blue">G</div>
            <div class="pay-card-title-wrap">
              <h3 class="pay-card-title">Pay via GCash</h3>
              <p class="pay-card-sub">Scan the QR code or send to the official HOA GCash account.</p>
            </div>
          </div>
          <div class="pay-gcash-inner">
            <div class="gcash-qr-col">
              <div class="gcash-logo-badge"><span class="g-icon">G</span> GCash</div>
              <div class="gcash-qr-frame" id="gcashQrBox">
                <div style="font-size:12px;color:var(--text-dim);">Loading QR code…</div>
              </div>
              <div class="gcash-qr-actions">
                <button type="button" class="pay-btn-subtle" onclick="enlargeGcashQr()">
                  <x-icon name="maximize-2" size="13"/> Enlarge
                </button>
                <button type="button" class="pay-btn-subtle" onclick="downloadGcashQr()">
                  <x-icon name="download" size="13"/> Download
                </button>
              </div>
            </div>
            <div class="gcash-info-col">
              <div class="pay-instructions-title">Payment Instructions</div>
              <div class="pay-steps-list">
                <div class="pay-step-row">
                  <span class="pay-step-badge">1</span>
                  <span>Open your GCash application.</span>
                </div>
                <div class="pay-step-row">
                  <span class="pay-step-badge">2</span>
                  <span>Scan the HOA QR code or send to the account above.</span>
                </div>
                <div class="pay-step-row">
                  <span class="pay-step-badge">3</span>
                  <span>Enter the correct payment amount.</span>
                </div>
                <div class="pay-step-row">
                  <span class="pay-step-badge">4</span>
                  <span>Complete the transaction.</span>
                </div>
                <div class="pay-step-row">
                  <span class="pay-step-badge">5</span>
                  <span>Save your payment receipt.</span>
                </div>
                <div class="pay-step-row">
                  <span class="pay-step-badge">6</span>
                  <span>Submit the receipt and reference number through SubSync.</span>
                </div>
              </div>
              <div class="gcash-acc-card" id="gcashPayDetails">
                <div style="font-size:12px;color:var(--text-dim);">Loading account details…</div>
              </div>
              <div class="pay-reminder-box">
                <x-icon name="info" size="16"/>
                <span><strong>Reminder:</strong> Please verify the recipient's account details before sending your payment.</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Submit Payment for Validation Card -->
        <div class="pay-card" id="payFormCard">
          <div class="pay-card-header">
            <div class="pay-card-icon orange">
              <x-icon name="file-text" size="20"/>
            </div>
            <div class="pay-card-title-wrap">
              <h3 class="pay-card-title">Submit Payment for Validation</h3>
              <p class="pay-card-sub">Upload your receipt and enter the payment details below.</p>
            </div>
          </div>
          <!-- Receipt Screenshot Upload -->
          <div class="receipt-upload-box">
            <label class="pay-form-label" style="display:block;margin-bottom:8px;">
              Receipt Screenshot <span style="font-size:12px;font-weight:normal;color:var(--text-dim);">(JPG, JPEG, PNG – Max 8 MB)</span> <span class="req">*</span>
            </label>
            <div class="receipt-drop-zone" id="receiptDropZone" onclick="document.getElementById('payReceipt').click()">
              <div class="receipt-drop-icon"><x-icon name="upload-cloud" size="32"/></div>
              <div class="receipt-drop-title">Click to upload <span style="font-weight:normal;color:var(--text-dim);">or drag and drop</span></div>
              <div class="receipt-drop-sub">JPG, JPEG, PNG (Max 8 MB)</div>
            </div>
            <div class="receipt-preview-box" id="receiptPreviewBox" style="display:none;">
              <img id="receiptThumbPreview" class="receipt-thumb" src="" alt="Receipt Preview">
              <div class="receipt-info">
                <div class="receipt-name" id="receiptFileName">receipt.jpg</div>
                <div class="receipt-size" id="receiptFileSize">0 KB</div>
              </div>
              <div class="receipt-btn-group">
                <button type="button" class="btn btn-sm btn-subtle" onclick="document.getElementById('payReceipt').click()">Replace</button>
                <button type="button" class="btn btn-sm btn-subtle" onclick="clearReceiptUpload()" style="color:#EF4444;">Remove</button>
              </div>
            </div>
            <input type="file" id="payReceipt" accept="image/jpeg,image/png,image/jpg" onchange="onReceiptFileSelected(this)" style="display:none;">
            <div id="ocrStatus" style="display:none;margin-top:10px;font-size:12.5px;padding:9px 14px;border-radius:8px;border:1px solid transparent;line-height:1.45;"></div>
          </div>

          <!-- Form Fields Grid -->
          <div class="pay-form-grid">
            <div class="pay-form-group">
              <label class="pay-form-label" for="payAmount">Amount (₱) <span class="req">*</span></label>
              <div class="currency-field-wrap">
                <span class="currency-tag">₱</span>
                <input type="number" id="payAmount" class="pay-input" min="1" step="0.01" placeholder="0.00" required>
              </div>
            </div>
            <div class="pay-form-group">
              <label class="pay-form-label" for="payReference">GCash Reference No. <span class="req">*</span></label>
              <input type="text" id="payReference" class="pay-input" maxlength="100" placeholder="e.g. 1002 3456 78901 (13 digits)" required>
            </div>
            <div class="pay-form-group">
              <label class="pay-form-label" for="paySender">Sender Name <span class="req">*</span></label>
              <input type="text" id="paySender" class="pay-input" maxlength="150" placeholder="Resident Name" required>
            </div>
            <div class="pay-form-group">
              <label class="pay-form-label" for="payDate">Date Paid <span class="req">*</span></label>
              <input type="date" id="payDate" class="pay-input" max="{{ date('Y-m-d') }}" required>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="button" class="pay-submit-btn" id="paySubmitBtn" onclick="SubUI&&SubUI.busyClick?SubUI.busyClick(this,submitPayment):submitPayment()">
            <x-icon name="send" size="16"/> Submit Payment
          </button>
        </div>
      </div>

      <!-- Bottom Card: Recent Payment Submissions -->
      <div class="pay-table-card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
          <div class="pay-card-header" style="margin-bottom:0;">
            <div class="pay-card-icon orange"><x-icon name="clock" size="18"/></div>
            <div class="pay-card-title-wrap">
              <h3 class="pay-card-title">Recent Payment Submissions</h3>
              <p class="pay-card-sub">Track the status of your submitted payments.</p>
            </div>
          </div>
          <a href="javascript:void(0)" onclick="payTab('history')" class="pay-view-all-link">
            View All History <x-icon name="arrow-right" size="14"/>
          </a>
        </div>
        <div class="pay-table-wrap">
          <table class="pay-table" id="recentPaymentsTable">
            <thead>
              <tr>
                <th>Date Submitted</th>
                <th>Reference No.</th>
                <th>Amount</th>
                <th>Date Paid</th>
                <th>Status</th>
                <th>Remarks</th>
                <th style="text-align:center;">Receipt</th>
              </tr>
            </thead>
            <tbody id="recentPaymentsTbody">
              <tr><td colspan="7" class="empty-state" style="text-align:center;padding:24px;">Loading submissions…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div><!-- /pay -->

    <!-- ── TAB 2: MY STATEMENT ── -->
    <div class="fin-section" data-pay="statement">
      <div class="pay-table-card" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:18px;border-bottom:1px solid var(--glass-border);padding-bottom:16px;">
          <div>
            <div style="font-size:12px;font-weight:700;color:#F58B1F;text-transform:uppercase;letter-spacing:.06em;">Official Record</div>
            <h3 class="pay-card-title" style="font-size:20px;margin-top:2px;">My Statement of Account</h3>
            <div style="font-size:13px;color:var(--text-dim);margin-top:3px;">
              Resident: <strong>{{ $residentName }}</strong> · Block/Lot: <strong>{{ $blockLot }}</strong>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:16px;">
            <div style="text-align:right;">
              <div style="font-size:11.5px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.06em;">Remaining Balance</div>
              <div id="statementBalance" style="font-size:26px;font-weight:800;color:var(--text);letter-spacing:-0.02em;">₱0.00</div>
            </div>
            <button type="button" class="pay-btn-subtle" onclick="window.print()" title="Print financial statement">
              <x-icon name="printer" size="14"/> Print Statement
            </button>
          </div>
        </div>
        <div id="statementAging" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px;"></div>
        <div id="statementTable"><div class="empty-state">Loading statement…</div></div>
      </div>
    </div><!-- /statement -->

    <!-- ── TAB 3: TRANSPARENCY ── -->
    <div class="fin-section" data-pay="transparency">
      <div class="pay-table-card" style="margin-bottom:16px;">
        <div class="pay-card-header">
          <div class="pay-card-icon orange"><x-icon name="bar-chart-2" size="20"/></div>
          <div class="pay-card-title-wrap">
            <h3 class="pay-card-title">Transparency — Where Your Money Goes</h3>
            <p class="pay-card-sub">Official HOA financial transparency report and expenditures breakdown.</p>
          </div>
        </div>
        <div id="transparencySummary"></div>
      </div>
    </div><!-- /transparency -->

    <!-- ── TAB 4: HISTORY ── -->
    <div class="fin-section" data-pay="history">
      <div class="pay-table-card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
          <div class="pay-card-header" style="margin-bottom:0;">
            <div class="pay-card-icon orange"><x-icon name="clock" size="18"/></div>
            <div class="pay-card-title-wrap">
              <h3 class="pay-card-title">Payment History</h3>
              <p class="pay-card-sub">Complete record of your submitted payments and their validation statuses.</p>
            </div>
          </div>
          <div class="history-filter-bar" style="margin-bottom:0;">
            <input type="text" id="payHistorySearch" class="history-search-input" placeholder="Search reference, amount, or date..." oninput="filterPaymentHistory()">
            <div class="history-filter-pills" id="historyFilterPills">
              <button type="button" class="history-pill active" onclick="setHistoryFilter('all', this)">All</button>
              <button type="button" class="history-pill" onclick="setHistoryFilter('Validated', this)">Validated</button>
              <button type="button" class="history-pill" onclick="setHistoryFilter('Pending', this)">Under Review</button>
              <button type="button" class="history-pill" onclick="setHistoryFilter('Rejected', this)">Rejected</button>
            </div>
          </div>
        </div>
        <div class="pay-table-wrap">
          <div id="myPaymentsList"><div class="empty-state">Loading history…</div></div>
        </div>
      </div>
    </div><!-- /history -->
  </div>


  <!-- ═══════════ HOA POLICIES ═══════════ -->
    <div class="tab-panel" id="panel-policies">
      <div class="card" style="margin-bottom:16px;background:linear-gradient(135deg,#FAF4E7,#FAF6F0);border-left:4px solid var(--accent);">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
          <div style="font-size:32px;"><x-icon name="scroll" size="32"/></div>
          <div>
            <div style="font-family:'DM Serif Display',serif;font-size:22px;color:#2B2927;margin-bottom:4px;">Terra Nova Homeowners Association</div>
            <div style="font-size:13px;color:var(--text-dim);">Community Rules, Regulations &amp; Policies — Effective Current Term</div>
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

  <!-- Calendar -->
  <div class="tab-panel" id="panel-calendar">
    <div class="card" style="margin-top:0;">
      <div class="card-title">Community Calendar</div>
      <div class="card-subtitle">Upcoming events and meetings. Let the HOA know if you're coming.</div>
      <div id="eventsList" style="margin-top:12px;"><div class="empty-state"><div class="empty-icon"><x-icon name="calendar" size="32"/></div>Loading…</div></div>
    </div>
  </div>

</main>
<script>window.__RESIDENT = { id: {{ $residentId ?? 'null' }}, houseId: {{ $houseId ?? 'null' }}, name: @json($residentName), balance: {{ (float)($balance ?? 0) }}, blockLot: @json($blockLot ?? '') };</script>
<script src="{{ asset('js/residents.js') . '?v=' . filemtime(public_path('js/residents.js')) }}"></script>
<script src="{{ asset('js/notifications.js') . '?v=' . filemtime(public_path('js/notifications.js')) }}"></script>

<script>
/* ══ LAZY LEAFLET LOADER — Leaflet loads from local origin only when the map is opened ══ */
let _leafletLoaded = false;
function loadLeaflet(){
  if(_leafletLoaded) return Promise.resolve();
  _leafletLoaded = true;
  const AB = window.APP_BASE || '';
  return new Promise((resolve, reject) => {
    // CSS (same-origin → instant, and cached 1yr via .htaccess)
    if(!document.querySelector('link[href*="leaflet.css"]')){
      const l = document.createElement('link');
      l.rel = 'stylesheet';
      l.href = AB + '/vendor/leaflet/leaflet.css';
      document.head.appendChild(l);
    }
    // JS (sequential to preserve dependency order)
    const srcs = [
      AB + '/vendor/leaflet/leaflet.js',
    ];
    let i = 0;
    (function next(){
      if(i >= srcs.length){
        // Point default marker icons at local images (issue-picker pin uses default marker)
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
/* ══ RESIDENT MAP ══ */
const RES_CENTER = [10.62269, 122.96134];
let resMapInst = null;
let resBlocksLayer = null;
let resBlockNamesLayer = null;
let resBlockStartsLayer = null;
let resFacilitiesLayer = null;
let resIssuesLayer = null;
let resHouseholdLayer = null;
let resHeatLayer = null;
let resHeatCanvasLayer = null;
let resBlocksData = [];
let resActiveBlock = '';
let resBlockPolygons = {};
let resMapIssuesData = [];

function updateResidentBlockSubTogglesUI(blocksActive) {
  const wrapNames = document.getElementById('wrap-res-block-names');
  const wrapStarts = document.getElementById('wrap-res-block-starts');
  const inputNames = document.getElementById('res-layer-block-names');
  const inputStarts = document.getElementById('res-layer-block-starts');
  if (wrapNames) wrapNames.classList.toggle('is-disabled', !blocksActive);
  if (wrapStarts) wrapStarts.classList.toggle('is-disabled', !blocksActive);
  if (inputNames) inputNames.disabled = !blocksActive;
  if (inputStarts) inputStarts.disabled = !blocksActive;
}

function toggleResidentFacilities(show) {
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('res-layer-facilities')?.checked);
  const toggle = document.getElementById('res-layer-facilities');
  if (toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  if (!resMapInst || !resFacilitiesLayer) return;
  if (isChecked) {
    if (!resMapInst.hasLayer(resFacilitiesLayer)) resMapInst.addLayer(resFacilitiesLayer);
  } else {
    if (resMapInst.hasLayer(resFacilitiesLayer)) resMapInst.removeLayer(resFacilitiesLayer);
  }
}

function toggleResidentIssues(show) {
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('res-layer-issues')?.checked);
  const toggle = document.getElementById('res-layer-issues');
  if (toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  if (!resMapInst || !resIssuesLayer) return;
  if (isChecked) {
    if (!resMapInst.hasLayer(resIssuesLayer)) resMapInst.addLayer(resIssuesLayer);
  } else {
    if (resMapInst.hasLayer(resIssuesLayer)) resMapInst.removeLayer(resIssuesLayer);
  }
}

function toggleResidentHeatmap(show) {
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('res-layer-heat')?.checked);
  const toggle = document.getElementById('res-layer-heat');
  if (toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  const analysisCard = document.getElementById('res-heatmap-analysis-card');
  const catEl = document.getElementById('res-heat-category');
  if (!resMapInst) return;
  if (!resHeatLayer) {
    resHeatLayer = L.layerGroup();
  }
  if (isChecked) {
    if (!resMapInst.hasLayer(resHeatLayer)) resMapInst.addLayer(resHeatLayer);
    if (analysisCard) analysisCard.style.display = 'block';
    if (catEl) catEl.style.display = 'inline-block';
    renderResidentHeatmap();
  } else {
    if (resMapInst.hasLayer(resHeatLayer)) resMapInst.removeLayer(resHeatLayer);
    if (analysisCard) analysisCard.style.display = 'none';
    if (catEl) catEl.style.display = 'none';
  }
}

async function ensureResidentLeafletHeat() {
  if (typeof L !== 'undefined' && typeof L.heatLayer === 'function') return;
  return new Promise(resolve => {
    const s = document.createElement('script');
    s.src = (window.APP_URL || '') + '/vendor/leaflet/leaflet-heat.js';
    s.onload = () => resolve();
    s.onerror = () => { console.warn('Could not load leaflet-heat.js'); resolve(); };
    document.head.appendChild(s);
  });
}

function normalizeResidentHeatmapCategory(rawCat) {
  const cat = String(rawCat || '').trim();
  if (!cat || cat.toLowerCase() === 'other' || cat.toLowerCase() === 'non-general' || cat.toLowerCase() === 'uncategorized' || cat.toLowerCase() === 'general') {
    return 'Other';
  }
  return cat;
}

async function renderResidentHeatmap() {
  if (!resMapInst || !resHeatLayer) return;
  resHeatLayer.clearLayers();
  resHeatCanvasLayer = null;

  const analysisCard = document.getElementById('res-heatmap-analysis-card');
  if (!resMapInst.hasLayer(resHeatLayer)) {
    if (analysisCard) analysisCard.style.display = 'none';
    return;
  }
  if (analysisCard) analysisCard.style.display = 'block';

  await ensureResidentLeafletHeat();

  const catEl = document.getElementById('res-heat-category');
  const selectedCat = catEl ? catEl.value : '';
  const allIssues = (resMapIssuesData || []).filter(i => {
    const lat = parseFloat(i.latitude), lng = parseFloat(i.longitude);
    return !isNaN(lat) && !isNaN(lng);
  });

  // Calculate overall category frequency across all mapped subdivision issues
  // Always include 'Other' so non-general reports always have a dedicated tag and filter option
  const overallCategoryCounts = { 'Other': 0 };
  allIssues.forEach(i => {
    const c = normalizeResidentHeatmapCategory(i.category);
    overallCategoryCounts[c] = (overallCategoryCounts[c] || 0) + 1;
  });

  const sortedOverallCats = Object.entries(overallCategoryCounts).sort((a, b) => b[1] - a[1]);
  const totalMappedIssues = allIssues.length;
  const topOverallCat = sortedOverallCats.length ? sortedOverallCats[0][0] : 'None';
  const topOverallCount = sortedOverallCats.length ? sortedOverallCats[0][1] : 0;
  const topOverallLabel = topOverallCat === 'Other' ? 'Other (Non-General)' : topOverallCat;

  if (catEl) {
    const prevVal = catEl.value;
    catEl.innerHTML = `<option value="">All categories (${totalMappedIssues} total)</option>`;
    sortedOverallCats.forEach(([catName, count], idx) => {
      const opt = document.createElement('option');
      opt.value = catName;
      const isOther = catName === 'Other';
      const displayLabel = isOther ? 'Other (Non-General Reports)' : catName;
      const star = (!isOther && idx === 0 && count > 0) ? '★ ' : '';
      const rank = count > 0 ? `#${idx + 1} ` : '';
      opt.textContent = `${star}${rank}${displayLabel} (${count}${star ? ' — Most Frequent' : ''})`;
      catEl.appendChild(opt);
    });
    catEl.value = prevVal;
  }

  const pillsContainer = document.getElementById('res-heatmap-category-pills');
  if (pillsContainer) {
    let pillsHtml = `
      <span style="font-size:11px;font-weight:700;color:var(--text-dim);margin-right:4px;">Filter Heatmap:</span>
      <button class="heatmap-pill-btn ${!selectedCat ? 'active' : ''}" onclick="filterResidentHeatmapToCategory('')" title="Show all issue categories">
        All Issues <span class="pill-count">${totalMappedIssues}</span>
      </button>
    `;
    sortedOverallCats.forEach(([catName, count], idx) => {
      const isSel = selectedCat === catName;
      const isOther = catName === 'Other';
      const star = (!isOther && idx === 0 && count > 0) ? '★ ' : '';
      const rank = count > 0 ? `#${idx + 1} ` : '';
      const displayLabel = isOther ? 'Other (Non-General)' : catName;
      const tooltip = isOther ? 'Filter to Other (Non-General Reports)' : `Filter to ${catName}`;
      pillsHtml += `
        <button class="heatmap-pill-btn ${isSel ? 'active' : ''}" onclick="filterResidentHeatmapToCategory('${catName.replace(/'/g, "\\'")}')" title="${tooltip}">
          ${star}${rank}${typeof escHtml === 'function' ? escHtml(displayLabel) : displayLabel} <span class="pill-count">${count}</span>
        </button>
      `;
    });
    pillsContainer.innerHTML = pillsHtml;
  }

  const activeIssues = allIssues.filter(i => {
    if (!selectedCat) return true;
    return normalizeResidentHeatmapCategory(i.category) === normalizeResidentHeatmapCategory(selectedCat);
  });
  const heatPoints = [];
  activeIssues.forEach(i => {
    const lat = parseFloat(i.latitude), lng = parseFloat(i.longitude);
    let intensity = 0.85;
    const st = (i.status || '').toLowerCase();
    if (st === 'in progress') intensity = 0.65;
    else if (st === 'resolved') intensity = 0.28;
    const pr = (i.priority || '').toLowerCase();
    if (pr === 'critical') intensity *= 1.4;
    else if (pr === 'high') intensity *= 1.2;
    else if (pr === 'low') intensity *= 0.8;
    heatPoints.push([lat, lng, intensity]);
  });

  if (typeof L.heatLayer === 'function' && heatPoints.length > 0) {
    resHeatCanvasLayer = L.heatLayer(heatPoints, {
      radius: 35,
      blur: 24,
      maxZoom: 18,
      max: 1.5,
      minOpacity: 0.35,
      gradient: {
        0.2: '#38bdf8',
        0.45: '#34d399',
        0.65: '#facc15',
        0.82: '#fb923c',
        1.0: '#ef4444'
      }
    });
    resHeatLayer.addLayer(resHeatCanvasLayer);
  }

  const summaryEl = document.getElementById('res-heatmap-summary-line');
  if (summaryEl) {
    if (!totalMappedIssues) {
      summaryEl.textContent = 'No reported concerns recorded in the subdivision boundary yet.';
    } else {
      const topCount = sortedOverallCats.length ? sortedOverallCats[0][1] : 0;
      const safeCat = typeof escHtml === 'function' ? escHtml(topOverallLabel) : topOverallLabel;
      const filterNotice = selectedCat ? `Filtered to <strong>${escHtml(selectedCat === 'Other' ? 'Other (Non-General Reports)' : selectedCat)}</strong> · ` : '';
      summaryEl.innerHTML = `${filterNotice}<strong>${activeIssues.length} issues</strong> thermal mapped. Most reported category: <strong>${safeCat}</strong> (${topCount} reports).`;
    }
  }
}

function filterResidentHeatmapToCategory(category) {
  const catEl = document.getElementById('res-heat-category');
  if (catEl) catEl.value = category;
  renderResidentHeatmap();
}

window.toggleResidentFacilities = toggleResidentFacilities;
window.toggleResidentIssues = toggleResidentIssues;
window.toggleResidentHeatmap = toggleResidentHeatmap;
window.renderResidentHeatmap = renderResidentHeatmap;
window.filterResidentHeatmapToCategory = filterResidentHeatmapToCategory;

function refreshResidentMapLayers() {
  if (!resMapInst) return;
  fetch('/api/map/all').then(r=>r.json()).then(data=>{
    resMapIssuesData = data.issues || [];
    if (resBlocksLayer) {
      resBlocksData = data.blocks || [];
      renderResidentBlocks();
    }
    if (resFacilitiesLayer) {
      resFacilitiesLayer.clearLayers();
      (data.facilities || []).forEach(f=>{
        let popupContent = '<strong>' + (typeof escHtml === 'function' ? escHtml(f.name) : f.name) + '</strong>';
        if (f.description) popupContent += '<br><small>' + (typeof escHtml === 'function' ? escHtml(f.description) : f.description) + '</small>';
        L.marker([parseFloat(f.latitude),parseFloat(f.longitude)],{icon:makeResCircle('#4CAF50')}).bindPopup(popupContent).addTo(resFacilitiesLayer);
      });
    }
    if (resIssuesLayer) {
      resIssuesLayer.clearLayers();
      const ownIssueIds=new Set((typeof issues!=='undefined'&&Array.isArray(issues)?issues:[]).map(x=>String(x.id)));
      (data.issues || []).forEach((i, index)=>{
        const color = i.status==='Resolved' ? '#888' : (i.status==='In Progress'?'#f5a623':'#e05555');
        const mine=ownIssueIds.has(String(i.id));
        const num = i.id != null ? i.id : (index + 1);
        const safeTitle = typeof escHtml === 'function' ? escHtml(i.title) : i.title;
        const safeStatus = typeof escHtml === 'function' ? escHtml(i.status) : i.status;
        let popupContent = '<strong>' + (mine?'Your report':'Report') + ' #' + num + ': ' + safeTitle + '</strong><br><small>' + safeStatus + '</small>';
        L.marker([parseFloat(i.latitude),parseFloat(i.longitude)],{icon:makeResIssueIcon(color, num, mine)}).bindPopup(popupContent).addTo(resIssuesLayer);
      });
    }
    if (resHouseholdLayer) {
      resHouseholdLayer.clearLayers();
      // Individual household lot pins omitted to keep map focused on block boundaries, lot counts, and Lot 1 direction
    }
    if (resHeatLayer && resMapInst && resMapInst.hasLayer(resHeatLayer)) {
      renderResidentHeatmap();
    }
  }).catch(err => console.error('Map data fetch error:', err));
}
window.refreshResidentMapLayers = refreshResidentMapLayers;

function makeResCircle(color) {
  return L.divIcon({
    className: '',
    html: `<div class="sub-map-dot" style="width:13px;height:13px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.85);box-shadow:0 1px 5px rgba(0,0,0,.5);"></div>`,
    iconSize:[13,13], iconAnchor:[6,6], popupAnchor:[0,-9]
  });
}

function makeResIssueIcon(color, num, mine) {
  return L.divIcon({
    className: '',
    html: `<div class="sub-map-pin" style="width:24px;height:24px;border-radius:50%;background:${color};border:2px solid ${mine?'#FFD700':'rgba(255,255,255,0.9)'};box-shadow:0 2px 6px rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;font-family:sans-serif;">${num}</div>`,
    iconSize:[24,24], iconAnchor:[12,12], popupAnchor:[0,-14]
  });
}

let mapSettings = null;
async function fetchMapSettings(){
  try{
    const r = await fetch('/api/map/settings',{headers:{'Accept':'application/json'}});
    mapSettings = await r.json();
  }catch(e){
    mapSettings = {min_lat:10.6195,max_lat:10.6260,min_lng:122.9580,max_lng:122.9650,center_lat:10.62269,center_lng:122.96134,zoom:17};
  }
  return mapSettings;
}

function initResidentMap() {
    loadLeaflet().then(async ()=>{
      if (resMapInst) { setTimeout(()=>resMapInst.invalidateSize(),100); return; }
      const s = await fetchMapSettings();
      const mapBounds = L.latLngBounds([
      [+s.min_lat, +s.min_lng],
      [+s.max_lat, +s.max_lng],
    ]);
    resMapInst = L.map('resident-map', {
      minZoom: 16,
      maxZoom: 20,
      maxBounds: mapBounds,
      maxBoundsViscosity: 1.0,
    }).fitBounds(mapBounds, {padding: [12, 12]});
    if (window.SubUI && SubUI.enableDynamicPinScaling) SubUI.enableDynamicPinScaling(resMapInst);
    
    // Using OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19
    }).addTo(resMapInst);

    const resBlocksChecked = Boolean(document.getElementById('res-layer-blocks')?.checked);
    const resNamesChecked = Boolean(document.getElementById('res-layer-block-names')?.checked ?? true);
    const resStartsChecked = Boolean(document.getElementById('res-layer-block-starts')?.checked ?? true);
    resBlocksLayer = L.layerGroup();
    resBlockNamesLayer = L.layerGroup();
    resBlockStartsLayer = L.layerGroup();
    if (resBlocksChecked) {
      resBlocksLayer.addTo(resMapInst);
      if (resNamesChecked) resBlockNamesLayer.addTo(resMapInst);
      if (resStartsChecked) resBlockStartsLayer.addTo(resMapInst);
    }
    updateResidentBlockSubTogglesUI(resBlocksChecked);

    const resFacChecked = document.getElementById('res-layer-facilities') ? document.getElementById('res-layer-facilities').checked : true;
    resFacilitiesLayer = L.layerGroup();
    if (resFacChecked) resFacilitiesLayer.addTo(resMapInst);

    const resIssuesChecked = document.getElementById('res-layer-issues') ? document.getElementById('res-layer-issues').checked : true;
    resIssuesLayer = L.layerGroup();
    if (resIssuesChecked) resIssuesLayer.addTo(resMapInst);

    resHeatLayer = L.layerGroup();
    if (document.getElementById('res-layer-heat')?.checked) resHeatLayer.addTo(resMapInst);

    resHouseholdLayer = L.layerGroup().addTo(resMapInst);

    if (s && s.overlay_enabled !== false && s.overlay_url) {
      const oMinLat = s.overlay_min_lat ?? s.min_lat;
      const oMaxLat = s.overlay_max_lat ?? s.max_lat;
      const oMinLng = s.overlay_min_lng ?? s.min_lng;
      const oMaxLng = s.overlay_max_lng ?? s.max_lng;
      const oBounds = L.latLngBounds([[oMinLat, oMinLng], [oMaxLat, oMaxLng]]);
      const resOverlay = L.imageOverlay(s.overlay_url, oBounds, {
        opacity: typeof s.overlay_opacity === 'number' ? s.overlay_opacity : 0.65,
        interactive: false,
        className: 'subdivision-blueprint-overlay'
      }).addTo(resMapInst);
      if (resOverlay.bringToBack) resOverlay.bringToBack();
      const applyRot = () => {
        const el = resOverlay.getElement();
        if (el && typeof s.overlay_rotation === 'number') {
          el.style.transformOrigin = 'center center';
          el.style.rotate = `${s.overlay_rotation}deg`;
        }
      };
      setTimeout(applyRot, 40);
      resMapInst.on('zoomend moveend', applyRot);
    }

    refreshResidentMapLayers();
    setTimeout(() => { if (resMapInst) resMapInst.invalidateSize(); }, 200);
    });
}

async function loadResidentBlocks() {
  try {
    const res = await fetch('/api/map/blocks');
    if (res.ok) {
      resBlocksData = await res.json();
      renderResidentBlocks();
    }
  } catch (e) {
    console.error('Failed to load blocks:', e);
  }
}
window.loadResidentBlocks = loadResidentBlocks;

function getBlockStartInfo(b){
  const lbl = String(b.start_label || '').trim();
  let code = 'N', label = 'North', arrow = '↑';
  if (/east/i.test(lbl)) { code = 'E'; label = 'East'; arrow = '→'; }
  else if (/west/i.test(lbl)) { code = 'W'; label = 'West'; arrow = '←'; }
  else if (/north/i.test(lbl)) { code = 'N'; label = 'North'; arrow = '↑'; }
  else if (/south/i.test(lbl)) { code = 'S'; label = 'South'; arrow = '↓'; }
  else if (String(b.block_number) === '1') { code = 'E'; label = 'East'; arrow = '→'; }
  else if (String(b.block_number) === '2') { code = 'E'; label = 'East'; arrow = '→'; }
  const matchLot = lbl.match(/Lot\s*(\d+)/i);
  const startLot = matchLot ? `Lot ${matchLot[1]}` : 'Lot 1';
  return { code, label, arrow, startLot };
}

function renderResidentBlocks() {
  if (!resMapInst || !resBlocksLayer) return;
  resBlocksLayer.clearLayers();
  if (resBlockNamesLayer) resBlockNamesLayer.clearLayers();
  if (resBlockStartsLayer) resBlockStartsLayer.clearLayers();
  resBlockPolygons = {};

  (resBlocksData || []).forEach(b => {
    if (!b.coordinates || !b.coordinates.length) return;
    const isFiltered = resActiveBlock && String(resActiveBlock) !== String(b.block_number);
    const isBlock3 = String(b.block_number) === '3';
    const isBlock2 = String(b.block_number) === '2';

    const poly = L.polygon(b.coordinates, {
      color: b.color || '#2563eb',
      weight: isFiltered ? 1 : 2.5,
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
    const blockDisplayName = isBlock2 ? '🏛 Clubhouse & Pool' : b.name;
    const lotCountText = isBlock2 ? 'Lot 15' : (b.total_lots ? `${b.total_lots} Lots` : '');
    const labelIcon = L.divIcon({
      className: 'sub-block-div-icon',
      html: `<div class="sub-block-pill${isBlock2 ? ' sub-block-pill-clubhouse' : ''}" style="background:${b.color || '#2563eb'};" title="${blockDisplayName}${lotCountText ? ' • ' + lotCountText : ''}"><span class="sub-block-name">${blockDisplayName}</span>${lotCountText ? `<span class="sub-block-count">• ${lotCountText}</span>` : ''}</div>`,
      iconSize: [0, 0],
      iconAnchor: [0, 0]
    });
    const labelMarker = L.marker(labelPos, { icon: labelIcon, interactive: true });
    labelMarker.on('click', () => poly.openPopup(labelMarker.getLatLng()));

    let startMarker = null;
    if (b.start_lat && b.start_lng) {
      const info = getBlockStartInfo(b);
      const startTitle = `${b.name} • Starting ${info.startLot} (${info.label})`;
      const startIcon = L.divIcon({
        className: 'sub-block-div-icon',
        html: `<div class="sub-start-dir-marker" style="border-color:${b.color || '#ffffff'};" title="${startTitle}"><span class="start-dir-arrow">${info.arrow}</span><span class="start-dir-code">${info.code}</span></div>`,
        iconSize: [0, 0],
        iconAnchor: [0, 0]
      });
      startMarker = L.marker([b.start_lat, b.start_lng], { icon: startIcon, interactive: true, zIndexOffset: 1350 });

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
    }

    const lotRangeHtml = b.lot_range 
      ? `<div style="font-size:12px;font-weight:700;color:#1e293b;margin:4px 0 2px;">Lot Range: <strong>${b.lot_range}</strong> (${b.total_lots || ''} lots)</div>` 
      : '';
    const popupContent = `
      <div style="min-width:180px;">
        <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
          <span style="width:12px;height:12px;border-radius:3px;background:${b.color};display:inline-block;"></span>
          <strong style="font-size:13px;">${b.name}</strong>
        </div>
        ${lotRangeHtml}
        <div style="font-size:11px;color:#64748b;margin-bottom:6px;">${b.description || ''}</div>
        <div style="font-size:11px;color:#475569;margin-top:2px;">
          <strong>Numbering:</strong> Starts at ${b.start_label || 'Lot 1'}
        </div>
        <button onclick="filterResidentBlocks('${b.block_number}')" style="margin-top:8px;width:100%;padding:4px 8px;font-size:11px;border-radius:4px;border:1px solid ${b.color};background:${b.color};color:#fff;cursor:pointer;font-weight:700;">Filter to ${b.name}</button>
      </div>
    `;
    poly.bindPopup(popupContent);

    poly.addTo(resBlocksLayer);
    if (!isFiltered) {
      if (resBlockNamesLayer) labelMarker.addTo(resBlockNamesLayer);
      else labelMarker.addTo(resBlocksLayer);
      if (startMarker) {
        if (resBlockStartsLayer) startMarker.addTo(resBlockStartsLayer);
        else startMarker.addTo(resBlocksLayer);
      }
    }

    resBlockPolygons[b.block_number] = poly;
  });

  const toggle = document.getElementById('res-layer-blocks');
  const namesChecked = Boolean(document.getElementById('res-layer-block-names')?.checked ?? true);
  const startsChecked = Boolean(document.getElementById('res-layer-block-starts')?.checked ?? true);
  if (toggle && resMapInst && resBlocksLayer) {
    if (toggle.checked) {
      if (!resMapInst.hasLayer(resBlocksLayer)) resMapInst.addLayer(resBlocksLayer);
      if (resBlockNamesLayer && namesChecked && !resMapInst.hasLayer(resBlockNamesLayer)) resMapInst.addLayer(resBlockNamesLayer);
      if (resBlockStartsLayer && startsChecked && !resMapInst.hasLayer(resBlockStartsLayer)) resMapInst.addLayer(resBlockStartsLayer);
    } else {
      if (resMapInst.hasLayer(resBlocksLayer)) resMapInst.removeLayer(resBlocksLayer);
      if (resBlockNamesLayer && resMapInst.hasLayer(resBlockNamesLayer)) resMapInst.removeLayer(resBlockNamesLayer);
      if (resBlockStartsLayer && resMapInst.hasLayer(resBlockStartsLayer)) resMapInst.removeLayer(resBlockStartsLayer);
    }
  }
  updateResidentBlockSubTogglesUI(Boolean(toggle?.checked));
}

function toggleResidentBlocks(show) {
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('res-layer-blocks')?.checked);
  const toggle = document.getElementById('res-layer-blocks');
  if (toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  if (!resMapInst || !resBlocksLayer) return;
  if (!resBlockNamesLayer) resBlockNamesLayer = L.layerGroup();
  if (!resBlockStartsLayer) resBlockStartsLayer = L.layerGroup();

  const namesChecked = Boolean(document.getElementById('res-layer-block-names')?.checked ?? true);
  const startsChecked = Boolean(document.getElementById('res-layer-block-starts')?.checked ?? true);
  updateResidentBlockSubTogglesUI(isChecked);

  if (isChecked) {
    if (!resMapInst.hasLayer(resBlocksLayer)) resMapInst.addLayer(resBlocksLayer);
    if (namesChecked && !resMapInst.hasLayer(resBlockNamesLayer)) resMapInst.addLayer(resBlockNamesLayer);
    if (startsChecked && !resMapInst.hasLayer(resBlockStartsLayer)) resMapInst.addLayer(resBlockStartsLayer);
    if (!resBlocksData || !resBlocksData.length) loadResidentBlocks();
  } else {
    if (resMapInst.hasLayer(resBlocksLayer)) resMapInst.removeLayer(resBlocksLayer);
    if (resMapInst.hasLayer(resBlockNamesLayer)) resMapInst.removeLayer(resBlockNamesLayer);
    if (resMapInst.hasLayer(resBlockStartsLayer)) resMapInst.removeLayer(resBlockStartsLayer);
  }
}

function toggleResidentBlockNames(show) {
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('res-layer-block-names')?.checked);
  const toggle = document.getElementById('res-layer-block-names');
  if (toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  if (!resMapInst || !resBlockNamesLayer) return;
  const blocksOn = Boolean(document.getElementById('res-layer-blocks')?.checked);
  if (isChecked && blocksOn) {
    if (!resMapInst.hasLayer(resBlockNamesLayer)) resMapInst.addLayer(resBlockNamesLayer);
  } else {
    if (resMapInst.hasLayer(resBlockNamesLayer)) resMapInst.removeLayer(resBlockNamesLayer);
  }
}

function toggleResidentBlockStarts(show) {
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('res-layer-block-starts')?.checked);
  const toggle = document.getElementById('res-layer-block-starts');
  if (toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  if (!resMapInst || !resBlockStartsLayer) return;
  const blocksOn = Boolean(document.getElementById('res-layer-blocks')?.checked);
  if (isChecked && blocksOn) {
    if (!resMapInst.hasLayer(resBlockStartsLayer)) resMapInst.addLayer(resBlockStartsLayer);
  } else {
    if (resMapInst.hasLayer(resBlockStartsLayer)) resMapInst.removeLayer(resBlockStartsLayer);
  }
}

window.toggleResidentBlocks = toggleResidentBlocks;
window.toggleResidentBlockNames = toggleResidentBlockNames;
window.toggleResidentBlockStarts = toggleResidentBlockStarts;
window.updateResidentBlockSubTogglesUI = updateResidentBlockSubTogglesUI;

function filterResidentBlocks(blockNum) {
  resActiveBlock = blockNum || '';
  const sel = document.getElementById('res-filter-block');
  if (sel) sel.value = resActiveBlock;

  if (resActiveBlock) {
    const toggle = document.getElementById('res-layer-blocks');
    if (toggle && !toggle.checked) {
      toggle.checked = true;
      toggleResidentBlocks(true);
    }
  }

  renderResidentBlocks();

  if (resActiveBlock && resBlockPolygons[resActiveBlock] && resMapInst) {
    const b = resBlockPolygons[resActiveBlock].getBounds();
    resMapInst.fitBounds(b, { padding: [30, 30], maxZoom: 19 });
  } else if (resMapInst && mapSettings) {
    resMapInst.fitBounds([
      [+mapSettings.min_lat, +mapSettings.min_lng],
      [+mapSettings.max_lat, +mapSettings.max_lng]
    ], { padding: [12, 12] });
  }
}

let issuePickerMap = null, issuePickerPin = null;

/* ── Emergency quick report ── */
function startEmergency() {
  const sel = document.getElementById('issueCategory');
  if (!sel) return;
  sel.value = 'Emergency';
  toggleEmergencyNote();
  const t = document.getElementById('issueTitle');
  if (t) { t.focus(); }
  sel.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
function toggleEmergencyNote() {
  const sel = document.getElementById('issueCategory');
  const isEmergency = !!(sel && sel.value === 'Emergency');
  const note = document.getElementById('issue-emergency-note');
  if (note) note.style.display = isEmergency ? 'block' : 'none';
  const t = document.getElementById('issueTitle');
  if (t) t.placeholder = isEmergency ? 'Emergency — what is happening?' : 'Brief description of the issue';
}

/* ── Camera photos (merged into the photos on submit) ── */
window._cameraIssueImages = [];
(function () {
  const cam = document.getElementById('issueCamera');
  const count = document.getElementById('issueCameraCount');
  if (!cam) return;
  cam.addEventListener('change', function () {
    const existing = (document.getElementById('issueImages') || {}).files;
    let used = (existing ? existing.length : 0) + window._cameraIssueImages.length;
    Array.from(cam.files || []).forEach(function (f) {
      if (used >= 5) { showToast('⚠ Up to 5 photos total.'); return; }
      window._cameraIssueImages.push(f);
      used++;
    });
    cam.value = '';
    if (count) {
      const n = window._cameraIssueImages.length;
      count.textContent = n ? (n + ' camera photo' + (n > 1 ? 's' : '') + ' added') : '';
    }
    if (typeof refreshIssueFileChips === 'function') refreshIssueFileChips();
  });
})();

function setIssuePin(lat,lng){
  document.getElementById('issue-lat').value=lat;
  document.getElementById('issue-lng').value=lng;
  document.getElementById('issue-loc-display').textContent=(+lat).toFixed(5) + ', ' + (+lng).toFixed(5);
}
function pickerPinIcon(){
  // Explicit divIcon: Leaflet's default marker image can 404 when its CSS is still
  // loading (lazy-loaded here), which showed a broken-image pin.
  return L.divIcon({
    className:'',
    html:'<div class="sub-map-picker-pin" style="width:20px;height:20px;border-radius:50% 50% 50% 0;background:#FA7D09;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4);"></div>',
    iconSize:[20,20], iconAnchor:[10,20], popupAnchor:[0,-18]
  });
}
function makePickerPin(lat,lng){
  issuePickerPin=L.marker([lat,lng],{draggable:true, icon:pickerPinIcon()}).addTo(issuePickerMap);
  issuePickerPin.on('dragend',()=>{ const ll=issuePickerPin.getLatLng(); setIssuePin(ll.lat,ll.lng); });
  return issuePickerPin;
}
function useMyLocation() {
  if (!navigator.geolocation) { showToast('Geolocation not supported.'); return; }
  navigator.geolocation.getCurrentPosition(pos=>{
    const lat=pos.coords.latitude, lng=pos.coords.longitude;
    if(issuePickerPin) issuePickerPin.setLatLng([lat,lng]);
    else if(issuePickerMap) makePickerPin(lat,lng);
    setIssuePin(lat,lng);
    if(issuePickerMap) issuePickerMap.setView([lat,lng], 18);
  }, ()=>showToast('Could not get location.'));
}

function openIssuePicker() {
  document.getElementById('issue-picker-map').style.display='block';
  loadLeaflet().then(async ()=>{
    if(!issuePickerMap) {
      const s = mapSettings || await fetchMapSettings();
      const mapBounds = L.latLngBounds([
        [+s.min_lat, +s.min_lng],
        [+s.max_lat, +s.max_lng],
      ]);
      issuePickerMap = L.map('issue-picker-map', {
        minZoom: 16,
        maxZoom: 20,
        maxBounds: mapBounds,
        maxBoundsViscosity: 1.0,
      }).fitBounds(mapBounds, {padding: [8, 8]});
      if (window.SubUI && SubUI.enableDynamicPinScaling) SubUI.enableDynamicPinScaling(issuePickerMap);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'}).addTo(issuePickerMap);
      if (s && s.overlay_enabled !== false && s.overlay_url) {
        const oMinLat = s.overlay_min_lat ?? s.min_lat;
        const oMaxLat = s.overlay_max_lat ?? s.max_lat;
        const oMinLng = s.overlay_min_lng ?? s.min_lng;
        const oMaxLng = s.overlay_max_lng ?? s.max_lng;
        const oBounds = L.latLngBounds([[oMinLat, oMinLng], [oMaxLat, oMaxLng]]);
        const pickerOverlay = L.imageOverlay(s.overlay_url, oBounds, {
          opacity: typeof s.overlay_opacity === 'number' ? s.overlay_opacity : 0.65,
          interactive: false,
          className: 'subdivision-blueprint-overlay'
        }).addTo(issuePickerMap);
        if (pickerOverlay.bringToBack) pickerOverlay.bringToBack();
        const applyRot = () => {
          const el = pickerOverlay.getElement();
          if (el && typeof s.overlay_rotation === 'number') {
            el.style.transformOrigin = 'center center';
            el.style.rotate = `${s.overlay_rotation}deg`;
          }
        };
        setTimeout(applyRot, 40);
        issuePickerMap.on('zoomend moveend', applyRot);
      }
      issuePickerMap.on('click', function(e){
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        if(issuePickerPin) issuePickerPin.setLatLng([lat,lng]);
        else makePickerPin(lat,lng);
        document.getElementById('issue-lat').value=lat.toFixed(7);
        document.getElementById('issue-lng').value=lng.toFixed(7);
        document.getElementById('issue-loc-display').textContent=lat.toFixed(5) + ', ' + lng.toFixed(5);
      });
    } else {
      setTimeout(()=>issuePickerMap.invalidateSize(),80);
    }
  });
}
function closeIssuePicker() {
  document.getElementById('issue-picker-map').style.display='none';
}
</script>

<!-- New Conversation Modal -->
<div id="modal-newConv" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;">
  <div style="background:#FFFFFF;border:1px solid var(--glass-border);border-radius:12px;padding:24px;width:min(420px,90vw);color:var(--text);">
    <div style="font-size:15px;font-weight:600;margin-bottom:16px;">New Message</div>
    <label style="font-size:11px;color:var(--text-dim,#888);display:block;margin-bottom:4px;">To</label>
    <select id="newConvRecipient"
      style="width:100%;padding:8px 10px;background:#FAF6F0;border:1px solid var(--glass-border);border-radius:6px;color:var(--text);font-size:13px;box-sizing:border-box;margin-bottom:12px;">
      <option value="admin|0">?? HOA Admin</option>
    </select>
    <label style="font-size:11px;color:var(--text-dim,#888);display:block;margin-bottom:4px;">Subject</label>
    <input type="text" id="newConvTitle" placeholder="e.g. Billing question, Gate pass request."
      style="width:100%;padding:8px 10px;background:#FAF6F0;border:1px solid var(--glass-border);border-radius:6px;color:var(--text);font-size:13px;box-sizing:border-box;margin-bottom:16px;">
    <div style="display:flex;gap:8px;justify-content:flex-end;">
      <button onclick="closeNewConvModal()" style="padding:7px 14px;background:#FAF6F0;border:1px solid var(--glass-border);border-radius:6px;color:var(--text);font-size:13px;cursor:pointer;">Cancel</button>
      <button onclick="startNewConversation()" style="padding:7px 14px;background:var(--accent,#4e8ef7);border:none;border-radius:6px;color:#fff;font-size:13px;cursor:pointer;font-weight:500;">Send</button>
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
      <button class="btn btn-sm" onclick="document.getElementById('modal-annView').classList.remove('open')">Close</button>
    </div>
  </div>
</div>

<!-- Payment Receipt View Modal -->
<div class="modal-overlay" id="modal-receiptView" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="pay-modal-box">
    <div class="pay-modal-header">
      <div>
        <h4 class="pay-modal-title" id="receiptModalTitle">Payment Receipt</h4>
        <div style="font-size:12px;color:var(--text-dim);margin-top:2px;" id="receiptModalSubtitle">—</div>
      </div>
      <button type="button" class="pay-modal-close" onclick="document.getElementById('modal-receiptView').classList.remove('open')">&times;</button>
    </div>
    <div class="receipt-modal-view-wrap">
      <img id="receiptModalImg" src="" alt="Payment Receipt" style="display:none;" onload="this.style.display='block';document.getElementById('receiptModalLoading').style.display='none';">
      <div id="receiptModalLoading" style="color:#FFFFFF;font-size:13px;padding:30px;">Loading receipt image…</div>
    </div>
    <div class="pay-modal-meta" id="receiptModalMeta"></div>
    <div class="pay-modal-actions">
      <a id="receiptModalDownloadBtn" href="#" download="payment_receipt.jpg" class="pay-btn-subtle" target="_blank" rel="noopener">
        <x-icon name="download" size="14"/> Download
      </a>
      <button type="button" class="btn btn-sm" onclick="document.getElementById('modal-receiptView').classList.remove('open')">Close</button>
    </div>
  </div>
</div>

<!-- GCash QR Enlarge Modal -->
<div class="modal-overlay" id="modal-gcashQrEnlarge" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="pay-modal-box" style="max-width:440px;text-align:center;">
    <div class="pay-modal-header">
      <div style="text-align:left;">
        <h4 class="pay-modal-title">Official HOA GCash QR Code</h4>
        <div style="font-size:12px;color:var(--text-dim);margin-top:2px;">Scan with your GCash app to pay</div>
      </div>
      <button type="button" class="pay-modal-close" onclick="document.getElementById('modal-gcashQrEnlarge').classList.remove('open')">&times;</button>
    </div>
    <div style="background:#FFFFFF;border-radius:12px;padding:16px;margin:12px auto;display:inline-block;box-shadow:0 4px 16px rgba(0,0,0,0.08);">
      <img id="gcashQrEnlargeImg" src="" alt="Enlarged GCash QR Code" style="max-width:280px;max-height:280px;display:block;">
    </div>
    <div style="margin-top:10px;font-size:13.5px;color:var(--text-mid);line-height:1.6;" id="gcashQrModalDetails"></div>
    <div class="pay-modal-actions" style="justify-content:center;">
      <a id="gcashQrDownloadLink" href="#" download="HOA_GCash_QR.jpg" class="pay-btn-subtle" target="_blank" rel="noopener">
        <x-icon name="download" size="14"/> Download QR
      </a>
      <button type="button" class="btn btn-sm" onclick="document.getElementById('modal-gcashQrEnlarge').classList.remove('open')">Close</button>
    </div>
  </div>
</div>
<script>
  
  // Safety-net: announcements poll every 60s (SSE handles real-time)
  setInterval(async ()=>{
    if(document.hidden) return;
    try {
      const anns = await fetch('/api/announcements').then(r=>r.json());
      if(Array.isArray(anns) && anns.length && anns[0].id > lastAnnouncementId){
        const newOnes = anns.filter(a=>a.id > lastAnnouncementId);
        lastAnnouncementId = anns[0].id;
        announcements = anns;
        renderAnnouncements();
        showAnnBanner(newOnes[0]);
      }
    }catch(e){}
  }, 60000);

  // Safety-net: my issues poll every 60s (SSE handles real-time)
  setInterval(async ()=>{
    if(document.hidden) return;
    try {
      const myIss = await fetch('/api/issues/my').then(r=>r.json());
      if(Array.isArray(myIss) && JSON.stringify(myIss) !== JSON.stringify(issues)){
        issues = myIss;
        renderIssues();
      }
    }catch(e){}
  }, 60000);
</script>

<!-- New-announcement banner (driven by residents.js / SubUI realtime) -->
<div id="annNotif">
  <span style="font-size:18px;"><x-icon name="megaphone" size="18"/></span>
  <div style="flex:1;min-width:0;">
    <div class="ann-meta">New Announcement</div>
    <div class="ann-notif-title" id="annNotifTitle">-</div>
  </div>
  <button class="btn-view" id="annNotifViewBtn">View</button>
  <button class="btn-close" aria-label="Dismiss" onclick="document.getElementById('annNotif').classList.remove('show')">✕</button>
</div>
</body>
</html>









