<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SubSync — Officer Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
<style>
:root {
  --glass: rgba(255,255,255,0.06);
  --glass-hover: rgba(255,255,255,0.11);
  --glass-border: rgba(255,255,255,0.12);
  --accent: #e8a87c;
  --accent-dim: rgba(232,168,124,0.16);
  --accent-border: rgba(232,168,124,0.28);
  --text: #f0ece4;
  --text-dim: rgba(240,236,228,0.5);
  --text-mid: rgba(240,236,228,0.75);
  --danger: rgba(220,80,80,0.85);
  --blue: #7ab4f0;
  --blue-dim: rgba(122,180,240,0.14);
  --green: #82c98a;
  --green-dim: rgba(130,201,138,0.14);
  --yellow: #f0c060;
  --yellow-dim: rgba(240,192,96,0.14);
  --purple: #b99af5;
  --purple-dim: rgba(185,154,245,0.14);
  --radius: 14px;
  --radius-sm: 9px;
  --radius-xs: 6px;
}
*{ box-sizing:border-box; margin:0; padding:0; }
body {
  font-family: 'DM Sans', sans-serif;
  background:
    radial-gradient(ellipse 80% 60% at 70% 10%, rgba(30,50,80,0.42), transparent),
    radial-gradient(ellipse 60% 50% at 20% 80%, rgba(30,30,60,0.48), transparent),
    linear-gradient(160deg, #0b0b14 0%, #0d1220 40%, #0a0e1a 100%);
  min-height: 100vh;
  color: var(--text);
  padding-bottom: 60px;
}

/* ── BANNER ── */
.banner-wrap {
  position: relative; height: 220px; overflow: hidden; cursor: pointer;
}
.banner-wrap img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.4s ease, filter 0.3s;
  filter: brightness(0.65) saturate(1.1);
}
.banner-wrap:hover img { transform: scale(1.03); filter: brightness(0.5) saturate(1.2); }
.banner-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to bottom, transparent 35%, rgba(10,14,26,0.96) 100%);
  pointer-events: none;
}
.banner-edit-hint {
  position: absolute; top: 14px; right: 16px;
  background: rgba(0,0,0,0.45); border: 1px solid var(--glass-border);
  backdrop-filter: blur(10px); color: var(--text); font-size: 12px;
  padding: 5px 12px; border-radius: 20px; opacity: 0;
  transition: opacity 0.2s; pointer-events: none;
}
.banner-wrap:hover .banner-edit-hint { opacity: 1; }
#bannerInput, #avatarInput { display: none; }

/* ── LAYOUT ── */
.page { max-width: 880px; margin: 0 auto; padding: 0 20px; }

/* ── PROFILE ── */
.profile-header {
  display: flex; align-items: flex-end; gap: 22px;
  margin-top: -54px; padding-bottom: 20px;
  position: relative; z-index: 2;
}
.avatar-wrap { position: relative; flex-shrink: 0; cursor: pointer; }
.avatar-wrap img {
  width: 110px; height: 110px;
  border-radius: var(--radius);
  border: 3px solid rgba(122,180,240,0.3);
  object-fit: cover; display: block;
  transition: filter 0.25s;
}
.avatar-wrap:hover img { filter: brightness(0.55); }
.avatar-edit {
  position: absolute; inset: 0; display: flex;
  align-items: center; justify-content: center;
  border-radius: var(--radius); opacity: 0;
  transition: opacity 0.2s; font-size: 11px; color: #fff;
  background: rgba(0,0,0,0.32); letter-spacing: 0.04em;
}
.avatar-wrap:hover .avatar-edit { opacity: 1; }
.profile-meta { padding-bottom: 6px; }
.profile-meta h2 {
  font-family: 'DM Serif Display', serif; font-size: 24px;
  font-weight: 400; letter-spacing: 0.01em; margin-bottom: 6px;
}
.location {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 13px; color: var(--blue);
  background: var(--blue-dim); border: 1px solid rgba(122,180,240,0.28);
  padding: 3px 10px; border-radius: 20px;
}
.role-badge {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 13px; color: var(--purple);
  background: var(--purple-dim); border: 1px solid rgba(185,154,245,0.28);
  padding: 3px 10px; border-radius: 20px; margin-left: 8px;
}
[contenteditable]:focus { outline: none; }

/* ── TABS ── */
.tab-nav {
  display: flex; gap: 4px;
  background: rgba(0,0,0,0.25); border: 1px solid var(--glass-border);
  border-radius: var(--radius); padding: 5px;
  margin-top: 6px; overflow-x: auto; scrollbar-width: none;
}
.tab-nav::-webkit-scrollbar { display: none; }
.tab-btn {
  flex: 1; min-width: max-content;
  padding: 9px 16px; border-radius: var(--radius-sm);
  border: 1px solid transparent; background: transparent;
  color: var(--text-mid); font-family: 'DM Sans', sans-serif;
  font-size: 13px; font-weight: 500; cursor: pointer;
  transition: background 0.2s, color 0.2s;
  display: flex; align-items: center; justify-content: center;
  gap: 7px; white-space: nowrap;
}
.tab-btn:hover { background: var(--glass-hover); color: var(--text); }
.tab-btn.active { background: var(--blue-dim); color: var(--blue); border-color: rgba(122,180,240,0.3); }
.tab-badge {
  background: rgba(122,180,240,0.3); color: var(--blue);
  font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 10px;
}

/* ── PANELS ── */
.tab-panel { display: none; animation: fadeUp 0.3s ease both; }
.tab-panel.active { display: block; }
@keyframes fadeUp {
  from { opacity:0; transform:translateY(10px); }
  to { opacity:1; transform:translateY(0); }
}

/* ── CARDS ── */
.card {
  background: var(--glass); border: 1px solid var(--glass-border);
  border-radius: var(--radius); backdrop-filter: blur(16px);
  padding: 22px; margin-top: 16px;
}
.card-title {
  font-size: 11px; font-weight: 600; letter-spacing: 0.11em;
  text-transform: uppercase; color: var(--text-dim);
  margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
}
.card-title::after { content:''; flex:1; height:1px; background:var(--glass-border); }

/* ── FORMS ── */
.f-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px; }
.f-field { display:flex; flex-direction:column; gap:5px; flex:1; min-width:140px; }
.f-label { font-size:11px; color:var(--text-dim); letter-spacing:0.04em; }
input[type=text],input[type=date],textarea,select {
  background: rgba(255,255,255,0.07); border: 1px solid var(--glass-border);
  color: var(--text); padding: 9px 13px; border-radius: var(--radius-sm);
  font-family: 'DM Sans', sans-serif; font-size: 13px;
  outline: none; transition: border-color 0.2s, background 0.2s; width: 100%;
}
input:focus, textarea:focus, select:focus {
  background: rgba(255,255,255,0.1); border-color: rgba(122,180,240,0.4);
}
input::placeholder, textarea::placeholder { color: var(--text-dim); }
textarea { resize: vertical; min-height: 88px; line-height: 1.55; }
select { cursor: pointer; }
select option { background: #0a0e1a; color: var(--text); }

/* ── BUTTONS ── */
.btn {
  padding: 9px 18px; background: var(--blue-dim);
  border: 1px solid rgba(122,180,240,0.28);
  color: var(--blue); border-radius: var(--radius-sm);
  font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500;
  cursor: pointer; transition: background 0.2s, transform 0.15s; white-space: nowrap;
}
.btn:hover { background: rgba(122,180,240,0.26); transform: translateY(-1px); }
.btn:active { transform: scale(0.98); }
.btn-full { width: 100%; justify-content: center; margin-top: 12px; }
.btn-accent { background:var(--accent-dim); border-color:var(--accent-border); color:var(--accent); }
.btn-green { background:var(--green-dim); border-color:rgba(130,201,138,0.25); color:var(--green); }
.btn-purple { background:var(--purple-dim); border-color:rgba(185,154,245,0.25); color:var(--purple); }
.btn-danger { background:rgba(220,80,80,0.13); border-color:rgba(220,80,80,0.25); color:#f08080; }
.btn-sm { padding: 6px 13px; font-size: 12px; }

/* ── PILLS ── */
.pill {
  display:inline-block; font-size:10px; font-weight:700;
  letter-spacing:0.07em; text-transform:uppercase;
  padding:2px 9px; border-radius:20px;
}
.pill-notice  { background:var(--blue-dim); color:var(--blue); border:1px solid rgba(122,180,240,0.25); }
.pill-urgent  { background:rgba(220,80,80,0.13); color:#f08080; border:1px solid rgba(220,80,80,0.25); }
.pill-event   { background:var(--green-dim); color:var(--green); border:1px solid rgba(130,201,138,0.25); }
.pill-pending { background:var(--yellow-dim); color:var(--yellow); border:1px solid rgba(240,192,96,0.25); }
.pill-progress{ background:var(--blue-dim); color:var(--blue); border:1px solid rgba(122,180,240,0.25); }
.pill-resolved{ background:var(--green-dim); color:var(--green); border:1px solid rgba(130,201,138,0.25); }
.pill-idea    { background:var(--purple-dim); color:var(--purple); border:1px solid rgba(185,154,245,0.25); }

/* ── ANNOUNCEMENTS ── */
.ann-item {
  padding:15px; border-radius:var(--radius-sm);
  background:rgba(255,255,255,0.04); border:1px solid var(--glass-border);
  margin-bottom:9px; transition: background 0.2s;
}
.ann-item:hover { background:rgba(255,255,255,0.07); }
.ann-meta { display:flex; align-items:center; justify-content:space-between; margin-bottom:7px; }
.ann-date { font-size:11px; color:var(--text-dim); }
.ann-title { font-size:14px; font-weight:500; margin-bottom:4px; }
.ann-body { font-size:13px; color:var(--text-mid); line-height:1.6; }
.ann-actions { display:flex; gap:8px; margin-top:10px; }

/* ── REPORTS ── */
.report-item {
  background:rgba(255,255,255,0.04); border:1px solid var(--glass-border);
  border-radius:var(--radius-sm); padding:16px; margin-bottom:10px;
  transition: background 0.2s;
}
.report-item:hover { background:rgba(255,255,255,0.07); }
.report-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:8px; }
.report-title { font-size:14px; font-weight:500; }
.report-meta { font-size:11px; color:var(--text-dim); margin-bottom:10px; }
.report-actions { display:flex; gap:8px; flex-wrap:wrap; }

/* ── UPLOAD ZONE ── */
.upload-zone {
  border: 2px dashed rgba(122,180,240,0.3);
  border-radius: var(--radius-sm);
  padding: 36px 20px; text-align: center;
  cursor: pointer; transition: border-color 0.2s, background 0.2s;
  margin-bottom: 16px;
}
.upload-zone:hover, .upload-zone.drag-over {
  border-color: rgba(122,180,240,0.6);
  background: rgba(122,180,240,0.06);
}
.upload-icon { font-size: 36px; margin-bottom: 10px; opacity: 0.7; }
.upload-label { font-size: 14px; font-weight: 500; color: var(--blue); margin-bottom: 4px; }
.upload-sub { font-size: 12px; color: var(--text-dim); }
#fileUploadInput { display: none; }

.file-item {
  display:flex; align-items:center; gap:12px;
  padding:12px; border-radius:var(--radius-sm);
  background:rgba(255,255,255,0.04); border:1px solid var(--glass-border);
  margin-bottom:8px; transition: background 0.2s;
}
.file-item:hover { background:rgba(255,255,255,0.07); }
.file-icon { font-size:24px; flex-shrink:0; }
.file-info { flex:1; min-width:0; }
.file-name { font-size:13px; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.file-meta { font-size:11px; color:var(--text-dim); margin-top:2px; }
.file-status {
  font-size:10px; font-weight:700; letter-spacing:0.06em;
  text-transform:uppercase; padding:3px 10px; border-radius:20px;
  flex-shrink:0;
}
.file-sent { background:var(--green-dim); color:var(--green); border:1px solid rgba(130,201,138,0.25); }
.file-pending-send { background:var(--yellow-dim); color:var(--yellow); border:1px solid rgba(240,192,96,0.25); }

/* ── MESSAGES ── */
.msg-layout { display:flex; gap:14px; }
.msg-sidebar {
  width: 196px; flex-shrink:0;
  background: rgba(0,0,0,0.2); border:1px solid var(--glass-border);
  border-radius:var(--radius-sm); padding:12px; height:fit-content;
}
.msg-sidebar-title { font-size:10px; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-dim); margin-bottom:10px; }
.msg-thread-item {
  display:flex; align-items:center; gap:9px;
  padding:9px 10px; border-radius:var(--radius-xs);
  cursor:pointer; transition:background 0.15s;
  border:1px solid transparent; margin-bottom:3px;
}
.msg-thread-item:hover { background:var(--glass-hover); }
.msg-thread-item.active { background:var(--blue-dim); border-color:rgba(122,180,240,0.3); }
.msg-thread-avatar { width:32px; height:32px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; }
.ta-admin { background:rgba(122,180,240,0.22); color:var(--blue); }
.ta-hoa   { background:rgba(185,154,245,0.22); color:var(--purple); }
.msg-thread-info { overflow:hidden; flex:1; }
.msg-thread-name { font-size:12px; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.msg-thread-preview { font-size:11px; color:var(--text-dim); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.msg-unread { min-width:18px; height:18px; border-radius:9px; background:var(--blue); color:#0a0e1a; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; padding:0 4px; flex-shrink:0; }
.msg-main { flex:1; display:flex; flex-direction:column; min-width:0; }
.msg-header { background:rgba(0,0,0,0.22); border:1px solid var(--glass-border); border-radius:var(--radius-sm) var(--radius-sm) 0 0; padding:12px 16px; display:flex; align-items:center; gap:10px; }
.msg-header-avatar { width:34px; height:34px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; }
.msg-header-name { font-size:14px; font-weight:500; }
.msg-header-sub { font-size:11px; color:var(--green); display:flex; align-items:center; gap:4px; }
.msg-header-sub::before { content:''; width:6px; height:6px; border-radius:50%; background:var(--green); display:inline-block; }
.msg-body { flex:1; min-height:300px; max-height:360px; overflow-y:auto; background:rgba(0,0,0,0.14); border-left:1px solid var(--glass-border); border-right:1px solid var(--glass-border); padding:18px 16px; display:flex; flex-direction:column; gap:12px; scrollbar-width:thin; scrollbar-color:rgba(255,255,255,0.1) transparent; }
.msg-bubble-wrap { display:flex; align-items:flex-end; gap:8px; }
.msg-bubble-wrap.mine { flex-direction:row-reverse; }
.bubble-avatar { width:28px; height:28px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; }
.ba-admin { background:rgba(122,180,240,0.2); color:var(--blue); }
.ba-mine  { background:linear-gradient(135deg,var(--blue),#5090d0); color:#0a0e1a; }
.bubble { max-width:68%; padding:10px 14px; border-radius:14px; font-size:13px; line-height:1.55; }
.from-admin { background:rgba(255,255,255,0.09); border:1px solid var(--glass-border); border-bottom-left-radius:4px; }
.from-mine { background:var(--blue-dim); border:1px solid rgba(122,180,240,0.28); border-bottom-right-radius:4px; }
.bubble-time { font-size:10px; color:var(--text-dim); margin-top:3px; display:block; }
.msg-compose { background:rgba(0,0,0,0.22); border:1px solid var(--glass-border); border-top:none; border-radius:0 0 var(--radius-sm) var(--radius-sm); padding:12px 14px; display:flex; gap:10px; align-items:flex-end; }
.msg-compose textarea { flex:1; min-height:44px; max-height:120px; resize:none; background:rgba(255,255,255,0.07); border:1px solid var(--glass-border); border-radius:var(--radius-sm); padding:10px 12px; font-size:13px; line-height:1.45; }
.send-btn { width:44px; height:44px; border-radius:var(--radius-sm); background:var(--blue-dim); border:1px solid rgba(122,180,240,0.28); color:var(--blue); font-size:18px; cursor:pointer; transition:background 0.2s, transform 0.15s; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.send-btn:hover { background:rgba(122,180,240,0.26); transform:scale(1.05); }
.date-divider { display:flex; align-items:center; gap:10px; font-size:10px; color:var(--text-dim); letter-spacing:0.06em; text-transform:uppercase; }
.date-divider::before, .date-divider::after { content:''; flex:1; height:1px; background:var(--glass-border); }

/* ── EMPTY ── */
.empty-state { text-align:center; padding:36px 20px; color:var(--text-dim); font-size:13px; line-height:1.7; }
.empty-icon { font-size:30px; margin-bottom:8px; opacity:0.5; }

/* ── TOAST ── */
#toast { position:fixed; bottom:30px; left:50%; transform:translateX(-50%) translateY(20px); background:rgba(18,16,26,0.96); border:1px solid var(--glass-border); backdrop-filter:blur(12px); color:var(--text); font-size:13px; padding:10px 22px; border-radius:20px; opacity:0; pointer-events:none; transition:opacity 0.25s, transform 0.25s; z-index:999; white-space:nowrap; }
#toast.show { opacity:1; transform:translateX(-50%) translateY(0); }
</style>
</head>
<body>

<!-- BANNER -->
<div class="banner-wrap" onclick="document.getElementById('bannerInput').click()">
  <img id="bannerPreview" src="https://images.unsplash.com/photo-1486325212027-8081e485255e?w=1200&q=80" alt="Officer Banner">
  <div class="banner-overlay"></div>
  <div class="banner-edit-hint">✎ Change banner</div>
  <input type="file" id="bannerInput" accept="image/*" onchange="changeImg('bannerPreview',this)">
</div>

<div class="page">
  <!-- PROFILE -->
  <div class="profile-header">
    <div class="avatar-wrap" onclick="document.getElementById('avatarInput').click()">
      <img id="avatarPreview" src="https://ui-avatars.com/api/?name=Ricardo+Aban&background=2a6acc&color=fff&size=220&font-size=0.4" alt="Officer">
      <div class="avatar-edit">✎ Edit</div>
      <input type="file" id="avatarInput" accept="image/*" onchange="changeImg('avatarPreview',this)">
    </div>
    <div class="profile-meta">
      <h2 id="officerName" contenteditable="true" spellcheck="false">Ricardo Aban</h2>
      <span class="location">📍 <span id="blockText" contenteditable="true" spellcheck="false">Block 1, Lot 5</span></span>
      <span class="role-badge">🛡️ <span id="roleText" contenteditable="true" spellcheck="false">HOA President</span></span>
    </div>
  </div>

  <!-- TABS -->
  <div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('overview')" id="tab-overview">🏠 Overview</button>
    <button class="tab-btn" onclick="switchTab('announcements')" id="tab-announcements">📢 Announcements</button>
    <button class="tab-btn" onclick="switchTab('reports')" id="tab-reports">📋 Reports</button>
    <button class="tab-btn" onclick="switchTab('files')" id="tab-files">📊 Send Files</button>
    <button class="tab-btn" onclick="switchTab('messages')" id="tab-messages">
      💬 Messages <span class="tab-badge" id="msg-badge">1</span>
    </button>
  </div>

  <!-- ══ OVERVIEW ══ -->
  <div class="tab-panel active" id="panel-overview">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-top:16px;">
      <div class="card" style="margin-top:0;padding:18px;">
        <div style="font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px;">My Announcements</div>
        <div style="font-family:'DM Serif Display',serif;font-size:28px;color:var(--blue);" id="annCount">3</div>
        <div style="font-size:11px;color:var(--text-dim);">Posted this month</div>
      </div>
      <div class="card" style="margin-top:0;padding:18px;">
        <div style="font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px;">Open Reports</div>
        <div style="font-family:'DM Serif Display',serif;font-size:28px;color:var(--yellow);">4</div>
        <div style="font-size:11px;color:var(--text-dim);">2 submitted by me</div>
      </div>
      <div class="card" style="margin-top:0;padding:18px;">
        <div style="font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px;">Files Sent</div>
        <div style="font-family:'DM Serif Display',serif;font-size:28px;color:var(--green);">2</div>
        <div style="font-size:11px;color:var(--text-dim);">To admin this month</div>
      </div>
      <div class="card" style="margin-top:0;padding:18px;">
        <div style="font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px;">Block Residents</div>
        <div style="font-family:'DM Serif Display',serif;font-size:28px;color:var(--accent);">28</div>
        <div style="font-size:11px;color:var(--text-dim);">Block 1 households</div>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Recent Announcements from Admin</div>
      <div id="overviewAnnList"></div>
    </div>

    <div class="card">
      <div class="card-title">My Quick Actions</div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <button class="btn" onclick="switchTab('announcements')">📢 Post Announcement</button>
        <button class="btn btn-accent" onclick="switchTab('reports')">📋 New Report</button>
        <button class="btn btn-green" onclick="switchTab('files')">📊 Send Excel</button>
        <button class="btn btn-purple" onclick="switchTab('messages')">💬 Message Admin</button>
      </div>
    </div>
  </div>

  <!-- ══ ANNOUNCEMENTS ══ -->
  <div class="tab-panel" id="panel-announcements">
    <div class="card">
      <div class="card-title">Create Announcement</div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Type</span>
          <select id="annType">
            <option value="notice">📋 Notice</option>
            <option value="urgent">🚨 Urgent</option>
            <option value="event">🎉 Event</option>
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
      <button class="btn btn-full" onclick="postAnnouncement()">📢 Post Announcement</button>
    </div>

    <div class="card">
      <div class="card-title">My Posted Announcements</div>
      <div id="myAnnList"></div>
    </div>
  </div>

  <!-- ══ REPORTS ══ -->
  <div class="tab-panel" id="panel-reports">
    <div class="card">
      <div class="card-title">Create New Report</div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Report Type</span>
          <select id="repType">
            <option>Incident Report</option>
            <option>Maintenance Report</option>
            <option>Financial Report</option>
            <option>Security Report</option>
            <option>Community Concern Report</option>
            <option>Monthly Summary Report</option>
          </select>
        </div>
        <div class="f-field">
          <span class="f-label">Priority</span>
          <select id="repPriority">
            <option value="high">🔴 High</option>
            <option value="medium" selected>🟡 Medium</option>
            <option value="low">🟢 Low</option>
          </select>
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Report Title</span>
          <input type="text" id="repTitle" placeholder="Brief description of the report">
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Location / Area Affected</span>
          <input type="text" id="repLocation" placeholder="e.g. Block 1 main road, near Gate 2">
        </div>
        <div class="f-field">
          <span class="f-label">Date of Incident</span>
          <input type="date" id="repDate">
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Details</span>
          <textarea id="repBody" placeholder="Describe the incident or situation in detail…"></textarea>
        </div>
      </div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Recommended Action</span>
          <textarea id="repAction" placeholder="What action do you recommend the admin take?" style="min-height:64px;"></textarea>
        </div>
      </div>
      <button class="btn btn-full btn-accent" onclick="submitReport()">📋 Submit Report to Admin</button>
    </div>

    <div class="card">
      <div class="card-title">My Submitted Reports</div>
      <div id="myReportList"></div>
    </div>
  </div>

  <!-- ══ FILES ══ -->
  <div class="tab-panel" id="panel-files">
    <div class="card">
      <div class="card-title">Send Excel File to Admin</div>
      <div class="upload-zone" id="uploadZone"
        onclick="document.getElementById('fileUploadInput').click()"
        ondragover="handleDragOver(event)"
        ondragleave="handleDragLeave(event)"
        ondrop="handleDrop(event)">
        <div class="upload-icon">📊</div>
        <div class="upload-label">Click to upload or drag & drop</div>
        <div class="upload-sub">Supports .xlsx, .xls, .csv · Max 10 MB</div>
        <input type="file" id="fileUploadInput" accept=".xlsx,.xls,.csv" onchange="handleFileSelect(this)">
      </div>

      <div id="selectedFilePreview" style="display:none;" class="file-item">
        <span class="file-icon">📊</span>
        <div class="file-info">
          <div class="file-name" id="selectedFileName">—</div>
          <div class="file-meta" id="selectedFileMeta">—</div>
        </div>
        <button class="btn btn-sm" style="background:var(--glass);border-color:var(--glass-border);color:var(--text-dim);" onclick="clearFileSelection()">✕ Remove</button>
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
      <button class="btn btn-full btn-green" onclick="sendFile()">📤 Send to Admin</button>
    </div>

    <div class="card">
      <div class="card-title">Sent Files History</div>
      <div id="sentFilesList"></div>
    </div>
  </div>

  <!-- ══ MESSAGES ══ -->
  <div class="tab-panel" id="panel-messages">
    <div class="card" style="padding:16px;">
      <div class="card-title">Messages</div>
      <div class="msg-layout">
        <div class="msg-sidebar">
          <div class="msg-sidebar-title">Conversations</div>
          <div class="msg-thread-item active" onclick="selectThread('admin',this)">
            <div class="msg-thread-avatar ta-admin">SA</div>
            <div class="msg-thread-info">
              <div class="msg-thread-name">Subdivision Admin</div>
              <div class="msg-thread-preview">Good morning! Please…</div>
            </div>
            <span class="msg-unread" id="admin-unread">1</span>
          </div>
          <div class="msg-thread-item" onclick="selectThread('hoa',this)">
            <div class="msg-thread-avatar ta-hoa">HOA</div>
            <div class="msg-thread-info">
              <div class="msg-thread-name">HOA Group</div>
              <div class="msg-thread-preview">Meeting minutes…</div>
            </div>
          </div>
        </div>
        <div class="msg-main">
          <div class="msg-header">
            <div class="msg-header-avatar ta-admin" id="chat-avatar">SA</div>
            <div>
              <div class="msg-header-name" id="chat-name">Subdivision Admin</div>
              <div class="msg-header-sub" id="chat-sub">Online</div>
            </div>
          </div>
          <div class="msg-body" id="msgBody"></div>
          <div class="msg-compose">
            <textarea id="msgInput" placeholder="Type a message…" rows="1"
              onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}"></textarea>
            <button class="send-btn" onclick="sendMessage()" title="Send">➤</button>
          </div>
        </div>
      </div>
    </div>
  </div>

</div><!-- end .page -->

<div id="toast"></div>

<script>
/* ══ UTILS ══ */
function changeImg(id,inp){
  const f=inp.files[0]; if(!f) return;
  const r=new FileReader();
  r.onload=e=>document.getElementById(id).src=e.target.result;
  r.readAsDataURL(f);
}
function nowTime(){ return new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'}); }
function todayStr(){ return new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}); }
function showToast(msg){
  const t=document.getElementById('toast');
  t.textContent=msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),2800);
}
function fmtBytes(b){ if(b<1024)return b+'B'; if(b<1048576)return (b/1024).toFixed(1)+'KB'; return (b/1048576).toFixed(1)+'MB'; }

/* ══ TABS ══ */
function switchTab(name){
  document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('panel-'+name).classList.add('active');
  document.getElementById('tab-'+name).classList.add('active');
  if(name==='messages'){
    const u=document.getElementById('admin-unread');
    const b=document.getElementById('msg-badge');
    if(u) u.style.display='none';
    if(b) b.style.display='none';
  }
}

/* ══ DATA ══ */
const adminAnnouncements=[
  {tag:'urgent',label:'Urgent',title:'Water Service Interruption — May 16',
   body:'Water supply halted 8:00 AM–5:00 PM for pipeline maintenance. Please store sufficient water.',date:'May 15, 2026'},
  {tag:'event',label:'Event',title:'Community Clean-Up Drive',
   body:'All households invited May 18, 6:00 AM at the covered court. Gloves and bags provided.',date:'May 14, 2026'},
];

const myAnnouncements=[
  {id:1,tag:'notice',label:'Notice',target:'Block 1 Only',
   title:'Block 1 Road Resurfacing Schedule',
   body:'The main road in Block 1 will undergo resurfacing on May 20–21. Please use the alternate path.',
   date:'May 13, 2026'},
  {id:2,tag:'event',label:'Event',target:'All Residents',
   title:'HOA Monthly Meeting — May 20',
   body:'Mandatory meeting for all household representatives at the function hall, 3:00 PM sharp.',
   date:'May 10, 2026'},
];

const myReports=[
  {id:'REP-001',type:'Maintenance Report',priority:'high',
   title:'Broken streetlights on Block 1 main road',
   location:'Blk 1, Lots 14–18',date:'May 13, 2026',
   body:'Three consecutive streetlights have been non-functional for over a week.',
   action:'Request immediate replacement from maintenance team.',
   status:'progress',adminNote:'Maintenance team dispatched, ETA May 17.'},
  {id:'REP-002',type:'Security Report',priority:'medium',
   title:'Unauthorized vehicles at secondary gate',
   location:'Block 5 secondary gate',date:'May 11, 2026',
   body:'Several unregistered vehicles have been parking at the secondary gate overnight.',
   action:'Recommend reinforcing guard rotation at night shift.',
   status:'pending',adminNote:null},
];

const sentFiles=[
  {name:'May_2026_Block1_Collection.xlsx',category:'Monthly Collection Report',period:'May 2026',date:'May 14, 2026',size:'38 KB',status:'sent'},
  {name:'April_Incident_Summary.xlsx',category:'Incident Summary',period:'April 2026',date:'May 2, 2026',size:'22 KB',status:'sent'},
];

/* ══ MESSAGES ══ */
const threads={
  admin:{
    name:'Subdivision Admin',avatar:'SA',sub:'Online',avatarClass:'ta-admin',
    msgs:[
      {from:'admin',text:'Good morning, Officer Aban! Please submit the Block 1 collection report for May when ready.',time:'9:00 AM'},
      {from:'me',text:'Good morning po! Yes, I will upload it today. We have collected 96% so far.',time:'9:08 AM'},
      {from:'admin',text:'That is great! Looking forward to the report. Thank you.',time:'9:10 AM'},
    ]
  },
  hoa:{
    name:'HOA Group',avatar:'HOA',sub:'3 members',avatarClass:'ta-hoa',
    msgs:[
      {from:'admin',text:'Meeting minutes from April 30 are now uploaded in the shared drive.',time:'May 1'},
    ]
  }
};
let currentThread='admin';

function selectThread(id,el){
  currentThread=id;
  document.querySelectorAll('.msg-thread-item').forEach(x=>x.classList.remove('active'));
  el.classList.add('active');
  const t=threads[id];
  const av=document.getElementById('chat-avatar');
  av.textContent=t.avatar;
  av.className='msg-header-avatar '+t.avatarClass;
  document.getElementById('chat-name').textContent=t.name;
  document.getElementById('chat-sub').textContent=t.sub;
  renderMessages();
}

function renderMessages(){
  const body=document.getElementById('msgBody');
  const t=threads[currentThread];
  body.innerHTML='<div class="date-divider">Today</div>'+
    t.msgs.map(m=>`
      <div class="msg-bubble-wrap ${m.from==='me'?'mine':''}">
        <div class="bubble-avatar ${m.from==='me'?'ba-mine':'ba-admin'}">${m.from==='me'?'RA':t.avatar.slice(0,2)}</div>
        <div>
          <div class="bubble ${m.from==='me'?'from-mine':'from-admin'}">${m.text}</div>
          <span class="bubble-time">${m.time}</span>
        </div>
      </div>`).join('');
  body.scrollTop=body.scrollHeight;
}

function sendMessage(){
  const inp=document.getElementById('msgInput');
  const txt=inp.value.trim(); if(!txt) return;
  threads[currentThread].msgs.push({from:'me',text:txt,time:nowTime()});
  inp.value=''; renderMessages();
  setTimeout(()=>{
    threads[currentThread].msgs.push({from:'admin',text:'Message received. We will get back to you shortly.',time:nowTime()});
    renderMessages();
  },1600);
}

/* ══ RENDER ══ */
function renderOverviewAnn(){
  document.getElementById('overviewAnnList').innerHTML=adminAnnouncements.map(a=>`
    <div class="ann-item">
      <div class="ann-meta">
        <span class="pill pill-${a.tag}">${a.label}</span>
        <span class="ann-date">${a.date}</span>
      </div>
      <div class="ann-title">${a.title}</div>
      <div class="ann-body">${a.body}</div>
    </div>`).join('');
}

let nextAnnId=10;
function renderMyAnn(){
  const el=document.getElementById('myAnnList');
  if(!myAnnouncements.length){
    el.innerHTML='<div class="empty-state"><div class="empty-icon">📢</div>No announcements posted yet.</div>';
    return;
  }
  el.innerHTML=myAnnouncements.map(a=>`
    <div class="ann-item">
      <div class="ann-meta">
        <div style="display:flex;align-items:center;gap:8px;">
          <span class="pill pill-${a.tag}">${a.label}</span>
          <span style="font-size:10px;color:var(--text-dim);">→ ${a.target}</span>
        </div>
        <span class="ann-date">${a.date}</span>
      </div>
      <div class="ann-title">${a.title}</div>
      <div class="ann-body">${a.body}</div>
      <div class="ann-actions">
        <button class="btn btn-sm" onclick="showToast('✏️ Editing announcement…')">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteMyAnn(${a.id})">Delete</button>
      </div>
    </div>`).join('');
}
function deleteMyAnn(id){
  const i=myAnnouncements.findIndex(a=>a.id===id);
  if(i>-1){myAnnouncements.splice(i,1);renderMyAnn();showToast('🗑️ Announcement deleted.');}
  document.getElementById('annCount').textContent=myAnnouncements.length;
}
function postAnnouncement(){
  const type=document.getElementById('annType').value;
  const target=document.getElementById('annTarget').value;
  const title=document.getElementById('annTitle').value.trim();
  const body=document.getElementById('annBody').value.trim();
  if(!title||!body){showToast('⚠ Please fill in all fields.');return;}
  const labels={notice:'Notice',urgent:'Urgent',event:'Event'};
  myAnnouncements.unshift({id:nextAnnId++,tag:type,label:labels[type],target,title,body,date:todayStr()});
  document.getElementById('annTitle').value='';
  document.getElementById('annBody').value='';
  renderMyAnn();
  document.getElementById('annCount').textContent=myAnnouncements.length;
  showToast('✅ Announcement posted! Admin has been notified.');
}

let nextRepId=10;
function renderReports(){
  const el=document.getElementById('myReportList');
  if(!myReports.length){
    el.innerHTML='<div class="empty-state"><div class="empty-icon">📋</div>No reports submitted yet.</div>';
    return;
  }
  const statusPill={pending:'<span class="pill pill-pending">Pending Review</span>',progress:'<span class="pill pill-progress">In Progress</span>',resolved:'<span class="pill pill-resolved">Resolved</span>'};
  const priMap={high:'🔴 High',medium:'🟡 Medium',low:'🟢 Low'};
  el.innerHTML=myReports.map(r=>`
    <div class="report-item">
      <div class="report-top">
        <div>
          <div style="font-size:10px;color:var(--text-dim);margin-bottom:4px;">${r.type} · ${r.id}</div>
          <div class="report-title">${r.title}</div>
        </div>
        ${statusPill[r.status]||''}
      </div>
      <div class="report-meta">📍 ${r.location} · Filed ${r.date} · ${priMap[r.priority]}</div>
      <div style="font-size:13px;color:var(--text-mid);line-height:1.6;margin-bottom:8px;">${r.body}</div>
      <div style="font-size:12px;color:var(--text-dim);margin-bottom:10px;">Recommended: ${r.action}</div>
      ${r.adminNote?`<div style="padding:10px 12px;background:rgba(122,180,240,0.07);border-left:2px solid rgba(122,180,240,0.4);border-radius:0 6px 6px 0;margin-bottom:10px;">
        <div style="font-size:10px;color:var(--blue);font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">Admin Response</div>
        <div style="font-size:13px;color:var(--text-mid);">${r.adminNote}</div>
      </div>`:''}
      <div class="report-actions">
        <button class="btn btn-sm" onclick="showToast('✏️ Editing report…')">Edit</button>
        <button class="btn btn-sm btn-green" onclick="showToast('📥 Downloading report…')">↓ Download</button>
      </div>
    </div>`).join('');
}
function submitReport(){
  const type=document.getElementById('repType').value;
  const priority=document.getElementById('repPriority').value;
  const title=document.getElementById('repTitle').value.trim();
  const location=document.getElementById('repLocation').value.trim();
  const body=document.getElementById('repBody').value.trim();
  const action=document.getElementById('repAction').value.trim();
  if(!title||!body){showToast('⚠ Please fill in at least the title and details.');return;}
  const id='REP-00'+(myReports.length+1);
  myReports.unshift({id,type,priority,title,location:location||'Not specified',date:todayStr(),body,action:action||'—',status:'pending',adminNote:null});
  document.getElementById('repTitle').value='';
  document.getElementById('repLocation').value='';
  document.getElementById('repBody').value='';
  document.getElementById('repAction').value='';
  renderReports();
  showToast('✅ Report submitted to admin successfully.');
}

/* ══ FILE HANDLING ══ */
let selectedFile=null;
function handleFileSelect(inp){
  const f=inp.files[0]; if(!f) return;
  selectedFile=f;
  document.getElementById('selectedFileName').textContent=f.name;
  document.getElementById('selectedFileMeta').textContent=fmtBytes(f.size)+' · '+f.type.split('/')[1]?.toUpperCase()||'File';
  document.getElementById('selectedFilePreview').style.display='flex';
}
function clearFileSelection(){
  selectedFile=null;
  document.getElementById('fileUploadInput').value='';
  document.getElementById('selectedFilePreview').style.display='none';
}
function handleDragOver(e){
  e.preventDefault();
  document.getElementById('uploadZone').classList.add('drag-over');
}
function handleDragLeave(){
  document.getElementById('uploadZone').classList.remove('drag-over');
}
function handleDrop(e){
  e.preventDefault();
  document.getElementById('uploadZone').classList.remove('drag-over');
  const f=e.dataTransfer.files[0];
  if(!f){return;}
  if(!f.name.match(/\.(xlsx|xls|csv)$/i)){showToast('⚠ Please upload an Excel or CSV file.');return;}
  selectedFile=f;
  document.getElementById('selectedFileName').textContent=f.name;
  document.getElementById('selectedFileMeta').textContent=fmtBytes(f.size)+' · Spreadsheet';
  document.getElementById('selectedFilePreview').style.display='flex';
}
function sendFile(){
  const cat=document.getElementById('fileCategory').value;
  const period=document.getElementById('filePeriod').value.trim();
  const notes=document.getElementById('fileNotes').value.trim();
  const fname=selectedFile?selectedFile.name:null;
  if(!fname){showToast('⚠ Please select a file to send.');return;}
  sentFiles.unshift({
    name:fname, category:cat, period:period||todayStr(),
    date:todayStr(), size:selectedFile?fmtBytes(selectedFile.size):'—', status:'sent'
  });
  clearFileSelection();
  document.getElementById('filePeriod').value='';
  document.getElementById('fileNotes').value='';
  renderSentFiles();
  showToast('✅ File sent to admin: '+fname);
}

function renderSentFiles(){
  const el=document.getElementById('sentFilesList');
  if(!sentFiles.length){
    el.innerHTML='<div class="empty-state"><div class="empty-icon">📊</div>No files sent yet.</div>';
    return;
  }
  el.innerHTML=sentFiles.map(f=>`
    <div class="file-item">
      <span class="file-icon">📊</span>
      <div class="file-info">
        <div class="file-name">${f.name}</div>
        <div class="file-meta">${f.category} · ${f.period} · ${f.date} · ${f.size}</div>
      </div>
      <span class="file-status ${f.status==='sent'?'file-sent':'file-pending-send'}">${f.status==='sent'?'Sent':'Pending'}</span>
    </div>`).join('');
}

/* ══ INIT ══ */
renderOverviewAnn();
renderMyAnn();
renderReports();
renderSentFiles();
renderMessages();
</script>
</body>
</html>
