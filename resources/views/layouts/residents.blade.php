<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Resident Portal</title>
<script>window.APP_BASE = '{{ request()->getBaseUrl() }}';</script>
<script src="{{ asset('js/portal-context.js') . '?v=' . filemtime(public_path('js/portal-context.js')) }}"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/residents.css') . '?v=' . filemtime(public_path('css/residents.css')) }}">
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
<body class="residents-body">

<!-- Mobile Responsive Header -->
<div class="mobile-header">
  <div style="font-family:'DM Serif Display',serif; font-size:20px; color:#1C1A17; display:flex; align-items:center; gap:8px;">
    <span style="font-size:24px;"><x-icon name="home" size="24"/></span> SubSync
  </div>
  <button class="btn" style="padding: 6px 12px; font-size: 13px;" onclick="toggleMobileSidebar()"><x-icon name="menu" size="16"/> Menu</button>
</div>

<div class="sidebar-overlay" onclick="toggleMobileSidebar()"></div>

<!-- Left Sidebar Navigation -->
<aside class="sidebar">
  <div class="logo">
    <div style="font-family:'DM Serif Display',serif; font-size:24px; font-weight:400; color:#1C1A17; display:flex; align-items:center; gap:8px;">
      <span style="font-size:28px;"><x-icon name="home" size="28"/></span> SubSync
    </div>
  </div>
  <ul style="margin-top:20px;">
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
  <div class="sidebar-tools">
    <button type="button" class="nc-btn" onclick="SubUI.openSearch()" title="Search (Ctrl+K)" aria-label="Search"><x-icon name="search" size="14"/></button>
    @include('partials.notification-center')
  </div>

  <!-- Sidebar Profile Card -->
  <div class="sidebar-profile" style="text-align:center;">
    <div class="avatar-wrap" onclick="document.getElementById('profileInput').click()" style="margin: 0 auto 12px; width:64px;">
        <img id="profilePreview" src="{{ $avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($residentName).'&background=c87941&color=fff&size=120&font-size=0.4' }}" alt="Profile" style="width:64px; height:64px; border-radius:50%; border: 2px solid var(--glass-border); object-fit:cover;">
      <input type="file" id="profileInput" accept="image/*" onchange="changeImage('profilePreview',this)" style="display:none;">
    </div>
    <div style="font-weight:600; font-size:14px; color:#2B2927; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" id="householdName" contenteditable="true" spellcheck="false" title="Click to edit name">{{ $residentName }}</div>
    <div style="font-size:13px; color:var(--text-dim); margin-top:2px;">Block/Lot: <span id="locationText" style="font-weight:600;color:var(--text);">{{ $blockLot }}</span></div>
    
    <form id="logoutForm" action="{{ url('logout') }}" method="POST" style="display:none;">@csrf<input type="hidden" name="portal" value="{{ $role ?? 'resident' }}"></form>
    <a href="{{ url('settings') }}?portal={{ $role ?? 'resident' }}" class="btn btn-sm btn-full" style="margin-top:14px; display:block;"><x-icon name="gear" size="13"/> Settings</a>
    <button onclick="confirmLogout()" class="btn btn-sm btn-full" style="margin-top:8px; background:rgba(240,128,128,0.1); border:1px solid rgba(240,128,128,0.25); color:#f08080;">⏻ Log Out</button>
  </div>
</aside>

<!-- Right Main Workspace -->
<main class="main">
  <!-- ═══════════ OVERVIEW ═══════════ -->
  <div class="tab-panel active" id="panel-overview">
    <div class="bento-grid">
      <!-- Greeting card (Span 8) -->
      <div class="card bento-col-8" style="display:flex; flex-direction:column; justify-content:center;">
        <h1 style="font-family:'DM Serif Display',serif; font-size:32px; font-weight:400; color:#2B2927; margin-bottom:8px;" id="greetingText">Welcome back, {{ explode(' ', $residentName)[0] }} 👋</h1>
        <p style="font-size:13px; color:var(--text-dim); line-height:1.6;">Manage household members, report issues, and check account balances from here.</p>
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
      </div>

      <!-- Account balance (Span 4) -->
      <div class="card bento-col-4 res-balance-card">
        <div class="balance-label">Outstanding Balance</div>
        <div class="balance-val" id="residentBalance">₱{{ number_format((float)($balance ?? 0), 2) }}</div>
        <div style="font-size:13.5px; color:var(--text-dim); margin-top:6px;">Settle your dues via the Payments tab.</div>
        <button class="btn btn-sm" style="margin-top:12px; background:rgba(255,255,255,0.1); border-color:rgba(255,255,255,0.22); color:#fff;" onclick="quickPay()">Pay via GCash</button>
      </div>

      <!-- Announcements Box (Span 8 - Front & Center) -->
      <div class="card bento-col-8">
        <div class="card-title">Announcements</div>
        <div class="card-subtitle">Neighborhood notices and community calendar events.</div>
        <div id="announcementList"><div class="skeleton sk-card" style="margin-bottom:10px;"></div><div class="skeleton sk-card" style="margin-bottom:10px;"></div></div>
      </div>

      <!-- Quick Actions (Span 4, stacked like the officer portal) -->
      <div class="card bento-col-4" id="residentQuickActions">
        <div class="card-title">Quick Actions</div>
        <div class="card-subtitle">Common tasks, one tap away.</div>
        <div class="quick-stack">
          <button class="btn btn-full" onclick="switchTab('payments')"><x-icon name="credit-card" size="15"/> Pay My Dues</button>
          <button class="btn btn-full" onclick="switchTab('issues')"><x-icon name="megaphone" size="15"/> Report a Problem</button>
          <button class="btn btn-full" style="background:#e05555;border-color:#e05555;color:#fff;" onclick="switchTab('issues'); setTimeout(startEmergency, 200);"><x-icon name="alert" size="15"/> Emergency</button>
          <button class="btn btn-full" onclick="switchTab('messages')"><x-icon name="mail" size="15"/> Message the HOA</button>
        </div>
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
            <div style="padding:14px;background:rgba(160,125,83,0.06);border:1px solid rgba(160,125,83,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
              <div style="font-weight:600;color:var(--accent);margin-bottom:4px;">1. Membership &amp; Community Conduct</div>
              <div style="font-size:12px;color:var(--text-mid);line-height:1.5;">Resident rights, good-neighbor conduct, and community etiquette.</div>
            </div>
            <div style="padding:14px;background:rgba(160,125,83,0.06);border:1px solid rgba(160,125,83,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
              <div style="font-weight:600;color:var(--accent);margin-bottom:4px;">2. Property Use &amp; Structural Guidelines</div>
              <div style="font-size:12px;color:var(--text-mid);line-height:1.5;">Architectural guidelines, lawn upkeep, and construction clearance permits.</div>
            </div>
            <div style="padding:14px;background:rgba(160,125,83,0.06);border:1px solid rgba(160,125,83,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
              <div style="font-weight:600;color:var(--accent);margin-bottom:4px;">3. Noise, Conduct &amp; Community Standards</div>
              <div style="font-size:12px;color:var(--text-mid);line-height:1.5;">Standard quiet hours (10:00 PM – 6:00 AM) and social gathering guidelines.</div>
            </div>
            <div style="padding:14px;background:rgba(160,125,83,0.06);border:1px solid rgba(160,125,83,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
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
    <div class="card" style="padding:0;overflow:hidden;margin-top:0;">
      <div id="resident-map" style="width:100%;height:480px;"></div>
    </div>
    <div class="card" style="margin-top:12px;">
      <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:13px;color:var(--text-mid);align-items:center;">
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#4CAF50;display:inline-block;"></span>Facility</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#4287f5;display:inline-block;"></span>My Household</span>
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
    <div class="fin-tabs" id="payTabs">
      <button class="fin-tab active" onclick="payTab('pay',this)">Pay</button>
      <button class="fin-tab" onclick="payTab('statement',this)">My Statement</button>
      <button class="fin-tab" onclick="payTab('transparency',this)">Transparency</button>
      <button class="fin-tab" onclick="payTab('history',this)">History</button>
    </div>

    <div class="fin-section active" data-pay="pay">
    <div class="card" style="margin-bottom:12px;">
      <div class="card-title" style="margin:0 0 12px;">Pay via GCash</div>
      <div class="card-subtitle" style="margin:-6px 0 12px;">Scan the QR code or send to the number below, then submit the reference number for validation.</div>
      <div style="display:grid;grid-template-columns:200px 1fr;gap:16px;align-items:start;">
        <div id="gcashQrBox" style="text-align:center;border:1px dashed var(--glass-border);border-radius:8px;padding:12px;min-height:180px;display:flex;align-items:center;justify-content:center;background:#fff;">
          <div style="font-size:12px;color:var(--text-dim);">Loading…</div>
        </div>
        <div id="gcashPayDetails" style="font-size:14px;color:var(--text-mid);line-height:1.7;"></div>
      </div>
    </div>

    <div class="card" id="payFormCard" style="margin-bottom:12px;">
      <div class="card-title" style="margin:0 0 12px;">Submit Payment for Validation</div>
      <div class="f-row">
        <div class="f-field" style="width:100%;">
          <span class="f-label">Receipt Screenshot (photo, max 8 MB) — <em style="font-weight:normal;color:var(--accent);">Auto-reads Ref No. &amp; Amount</em></span>
          <input type="file" id="payReceipt" accept="image/*" onchange="onReceiptFileSelected(this)">
          <div id="ocrStatus" style="display:none;margin-top:8px;font-size:12px;padding:8px 12px;border-radius:6px;border:1px solid transparent;line-height:1.4;"></div>
        </div>
      </div>
      <div class="f-row">
        <div class="f-field"><span class="f-label">Amount (₱)</span><input type="number" id="payAmount" min="1" step="0.01" placeholder="0.00"></div>
        <div class="f-field"><span class="f-label">GCash Reference No.</span><input type="text" id="payReference" maxlength="100" placeholder="e.g. 1002 3456 78901 (13 digits)"></div>
      </div>
      <div class="f-row">
        <div class="f-field"><span class="f-label">Sender Name</span><input type="text" id="paySender" maxlength="150"></div>
        <div class="f-field"><span class="f-label">Date Paid</span><input type="date" id="payDate"></div>
      </div>
      <button class="btn btn-full" onclick="SubUI&&SubUI.busyClick?SubUI.busyClick(this,submitPayment):submitPayment()">Submit Payment</button>
    </div>
    </div><!-- /pay -->

    <div class="fin-section" data-pay="statement">
    <div class="card" style="margin-bottom:12px;">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
        <div class="card-title" style="margin:0;">My Statement</div>
        <div style="text-align:right;">
          <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.06em;">Balance</div>
          <div id="statementBalance" style="font-size:22px;font-weight:700;color:var(--text);">₱0.00</div>
        </div>
      </div>
      <div id="statementAging" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;"></div>
      <div id="statementTable"><div class="empty-state">Loading statement…</div></div>
    </div>
    </div><!-- /statement -->

    <div class="fin-section" data-pay="transparency">
    <div class="card" style="margin-bottom:12px;">
      <div class="card-title" style="margin:0 0 12px;">Transparency — Where Your Money Goes</div>
      <div id="transparencySummary" style="display:flex;gap:16px;flex-wrap:wrap;"></div>
    </div>
    </div><!-- /transparency -->

    <div class="fin-section" data-pay="history">
    <div class="card">
      <div class="card-title" style="margin:0 0 12px;">My Payments</div>
      <div id="myPaymentsList"><div class="empty-state">Loading…</div></div>
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
<script>window.__RESIDENT = { id: {{ $residentId ?? 'null' }}, houseId: {{ $houseId ?? 'null' }}, name: @json($residentName), balance: {{ (float)($balance ?? 0) }} };</script>
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

function makeResCircle(color) {
  return L.divIcon({
    className: '',
    html: `<div style="width:13px;height:13px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.85);box-shadow:0 1px 5px rgba(0,0,0,.5);"></div>`,
    iconSize:[13,13], iconAnchor:[6,6], popupAnchor:[0,-9]
  });
}

function makeResIssueIcon(color, num, mine) {
  return L.divIcon({
    className: '',
    html: `<div style="width:24px;height:24px;border-radius:50%;background:${color};border:2px solid ${mine?'#FFD700':'rgba(255,255,255,0.9)'};box-shadow:0 2px 6px rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;font-family:sans-serif;">${num}</div>`,
    iconSize:[24,24], iconAnchor:[12,12], popupAnchor:[0,-12]
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
    
    // Using OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19
    }).addTo(resMapInst);

    // Fetch all map data in a single request to prevent connection queuing
    fetch('/api/map/all').then(r=>r.json()).then(data=>{
      (data.facilities || []).forEach(f=>{
        let popupContent = '<strong>' + f.name + '</strong>';
        if (f.description) popupContent += '<br><small>' + f.description + '</small>';
        L.marker([parseFloat(f.latitude),parseFloat(f.longitude)],{icon:makeResCircle('#4CAF50')}).bindPopup(popupContent).addTo(resMapInst);
      });

      const ownIssueIds=new Set((typeof issues!=='undefined'&&Array.isArray(issues)?issues:[]).map(x=>String(x.id)));
      (data.issues || []).forEach((i, index)=>{
        const color = i.status==='Resolved' ? '#888' : (i.status==='In Progress'?'#f5a623':'#e05555');
        const mine=ownIssueIds.has(String(i.id));
        let popupContent = '<strong>' + (mine?'Your report':'Report') + ' #' + (index + 1) + ': ' + i.title + '</strong><br><small>' + i.status + '</small>';
        L.marker([parseFloat(i.latitude),parseFloat(i.longitude)],{icon:makeResIssueIcon(color, index + 1, mine)}).bindPopup(popupContent).addTo(resMapInst);
      });

      if(HOUSE_ID){
        const own = (data.households || []).find(h=>h.id===HOUSE_ID);
        if(own && own.latitude && own.longitude){
          L.marker([parseFloat(own.latitude),parseFloat(own.longitude)],{icon:makeResCircle('#4287f5')})
            .bindPopup('<strong>My Household</strong><br><small>'+(own.block_lot_number||'')+'</small>')
            .addTo(resMapInst);
        }
      }
    }).catch(err => console.error('Map data fetch error:', err));
    });
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
    html:'<div style="width:20px;height:20px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:#A07D53;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4);"></div>',
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
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'}).addTo(issuePickerMap);
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









