<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
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
@media(max-width:480px){ .banner-wrap { height: 150px; } }
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
@media(max-width:480px){ .page { padding: 0 12px; } }
/* ── PROFILE ── */
.profile-header {
  display: flex; align-items: flex-end; gap: 22px;
  margin-top: -54px; padding-bottom: 20px;
  position: relative; z-index: 2;
  flex-wrap: wrap;
}
@media(max-width:480px){
  .profile-header { margin-top: -40px; gap:14px; }
  .avatar-wrap img { width:80px; height:80px; }
  .profile-meta h2 { font-size:18px; }
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
    flex: 0 0 auto; min-width: max-content;
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
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.active { display:flex; }
.modal-box { background:var(--card); border:1px solid var(--glass-border); border-radius:14px; padding:28px; min-width:360px; max-width:500px; width:90%; }
.modal-title { font-size:16px; font-weight:600; margin-bottom:16px; }
.modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:16px; }
.modal-close-btn { background:transparent; border:1px solid var(--glass-border); color:var(--text-mid); padding:8px 16px; border-radius:8px; cursor:pointer; }
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
@media(max-width:600px){
  .msg-layout { flex-direction:column; }
  .msg-sidebar { width:100%; }
  .msg-body { min-height:220px; max-height:280px; }
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
.bubble { max-width:68%; max-width:min(68%, 340px); padding:10px 14px; border-radius:14px; font-size:13px; line-height:1.55; word-break:break-word; overflow-wrap:anywhere; }
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
      <img id="avatarPreview" src="https://ui-avatars.com/api/?name={{ urlencode($officerName) }}&background=2a6acc&color=fff&size=220&font-size=0.4" alt="Officer">
      <div class="avatar-edit">✎ Edit</div>
      <input type="file" id="avatarInput" accept="image/*" onchange="changeImg('avatarPreview',this)">
    </div>
    <div class="profile-meta">
      <h2 id="officerName" contenteditable="true" spellcheck="false">{{ $officerName }}</h2>
      <span class="location">📍 <span id="blockText" contenteditable="true" spellcheck="false">Block 1, Lot 5</span></span>
      <span class="role-badge">🛡️ <span id="roleText" contenteditable="true" spellcheck="false">{{ $officerRole }}</span></span>
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
        <div style="font-family:'DM Serif Display',serif;font-size:28px;color:var(--blue);" id="annCount">—</div>
        <div style="font-size:11px;color:var(--text-dim);">Posted this month</div>
      </div>
      <div class="card" style="margin-top:0;padding:18px;">
        <div style="font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px;">Open Reports</div>
        <div style="font-family:'DM Serif Display',serif;font-size:28px;color:var(--yellow);" id="openReportsCount">—</div>
        <div style="font-size:11px;color:var(--text-dim);">Unresolved issues</div>
      </div>
      <div class="card" style="margin-top:0;padding:18px;">
        <div style="font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px;">Files Sent</div>
        <div style="font-family:'DM Serif Display',serif;font-size:28px;color:var(--green);" id="filesSentCount">—</div>
        <div style="font-size:11px;color:var(--text-dim);">To admin this month</div>
      </div>
      <div class="card" style="margin-top:0;padding:18px;">
        <div style="font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px;">Block Residents</div>
        <div id="blockResidentsCount" style="font-family:'DM Serif Display',serif;font-size:28px;color:var(--accent);">—</div>
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
    <div class="card" style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;flex-wrap:wrap;gap:10px;">
      <div class="card-title" style="margin:0;">Issue Reports</div>
      <div style="display:flex;gap:8px;">
        <select id="officerIssueFilter" onchange="renderOfficerIssues()" style="background:var(--glass);border:1px solid var(--glass-border);color:var(--text-main);padding:6px 10px;border-radius:6px;font-size:12px;">
          <option value="">All Statuses</option>
          <option value="Pending">Pending</option>
          <option value="In Progress">In Progress</option>
          <option value="Resolved">Resolved</option>
        </select>
      </div>
    </div>
    <div id="officerIssueList"></div>

    <!-- Respond Modal -->
    <div class="modal-overlay" id="modal-officerRespond">
      <div class="modal-box">
        <div class="modal-title">Respond to Issue</div>
        <div id="officerRespondTitle" style="font-size:14px;color:var(--text-mid);margin-bottom:12px;"></div>
        <div class="f-field" style="margin-bottom:12px;">
          <span class="f-label">Update Status</span>
          <select id="officerRespondStatus" style="background:var(--input-bg);border:1px solid var(--glass-border);color:var(--text-main);padding:8px;border-radius:6px;width:100%;">
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Resolved">Resolved</option>
          </select>
        </div>
        <div class="f-field">
          <span class="f-label">Response / Notes</span>
          <textarea id="officerRespondText" placeholder="Enter your response…" style="min-height:100px;"></textarea>
        </div>
        <div class="modal-actions">
          <button class="modal-close-btn" onclick="closeModal('officerRespond')">Cancel</button>
          <button class="btn btn-sm" onclick="submitOfficerResponse()">Submit</button>
        </div>
      </div>
    </div>

    <!-- New Conversation Modal -->
    <div class="modal-overlay" id="modal-officerNewConv">
      <div class="modal-box">
        <div class="modal-title">Start New Conversation</div>
        <div class="f-field" style="margin-bottom:16px;">
          <span class="f-label">Subject</span>
          <input type="text" id="officerConvTitle" placeholder="e.g. Block 2 maintenance request…">
        </div>
        <div class="modal-actions">
          <button class="modal-close-btn" onclick="closeModal('officerNewConv')">Cancel</button>
          <button class="btn btn-sm" onclick="startOfficerConversation()">Start</button>
        </div>
      </div>
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
    const b=document.getElementById('msg-badge');
    if(b) b.style.display='none';
    loadOfficerThreads();
  }
}

/* ══ DATA ══ */
let adminAnnouncements=[];
let myAnnouncements=[];
let officerIssues=[];
let sentFiles=[];

const csrfToken=()=>document.querySelector('meta[name=csrf-token]').content;
async function apiGet(url){const r=await fetch(url,{headers:{'Accept':'application/json'}});return r.json();}
async function apiPost(url,data){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:JSON.stringify(data)});return r.json();}
async function apiPut(url,data){const r=await fetch(url,{method:'PUT',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:JSON.stringify(data)});return r.json();}
async function apiDelete(url){const r=await fetch(url,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()}});return r.json();}
function openModal(id){const el=document.getElementById('modal-'+id);if(el)el.classList.add('active');}
function closeModal(id){const el=document.getElementById('modal-'+id);if(el)el.classList.remove('active');}

async function loadOfficerData(){
  const [ann, iss, res]=await Promise.all([
    fetch('/api/announcements').then(r=>r.json()),
    fetch('/api/issues').then(r=>r.json()),
    fetch('/api/residents').then(r=>r.json()).catch(()=>[]),
  ]);
  adminAnnouncements=ann;
  myAnnouncements=ann.filter(a=>a.posted_by&&a.posted_by==='{{ $officerName }}');
  officerIssues=iss;
  renderOverviewAnn();
  renderMyAnn();
  renderOfficerIssues();
  // Update overview stat cards
  const now=new Date();
  const thisMonth=`${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}`;
  const myAnnThisMonth=myAnnouncements.filter(a=>(a.created_at||'').startsWith(thisMonth)).length;
  const openIssues=iss.filter(i=>i.status!=='Resolved').length;
  if(document.getElementById('annCount'))         document.getElementById('annCount').textContent=myAnnouncements.length;
  if(document.getElementById('openReportsCount')) document.getElementById('openReportsCount').textContent=openIssues;
  if(document.getElementById('blockResidentsCount')) document.getElementById('blockResidentsCount').textContent=Array.isArray(res)?res.length:'—';
}

/* ══ MESSAGES ══ */
let threads={};
let currentThread=null;

async function loadOfficerThreads(){
  const data=await fetch('/api/messages/threads',{headers:{'Accept':'application/json'}}).then(r=>r.json()).catch(()=>[]);
  const sidebar=document.querySelector('.msg-sidebar-threads');
  if(!Array.isArray(data)||!data.length){
    if(sidebar) sidebar.innerHTML='<div style="color:var(--text-dim);font-size:12px;padding:12px;">No conversations yet.<br><button class="btn btn-sm" style="margin-top:8px;" onclick="openOfficerNewConv()">Start one</button></div>';
    return;
  }
  data.forEach(t=>{threads[t.id]={...t,msgs:[]};});
  if(sidebar){
    sidebar.innerHTML=data.map((t,i)=>`<div class="msg-thread-item${i===0?' active':''}" onclick="selectOfficerThread(${t.id},this)">
      <div class="msg-thread-avatar ta-admin">${(t.title||'?').substring(0,2).toUpperCase()}</div>
      <div class="msg-thread-info">
        <div class="msg-thread-name">${t.title}</div>
        <div class="msg-thread-preview">${t.last_message||'No messages yet'}</div>
      </div>
    </div>`).join('');
  }
  currentThread=data[0].id;
  document.getElementById('chat-name').textContent=data[0].title||'Admin';
  document.getElementById('chat-avatar').textContent=(data[0].title||'SA').substring(0,2).toUpperCase();
  const msgs=await fetch('/api/messages/'+currentThread,{headers:{'Accept':'application/json'}}).then(r=>r.json()).catch(()=>[]);
  threads[currentThread].msgs=msgs;
  renderMessages();
}
async function selectOfficerThread(id,el){
  currentThread=id;
  document.querySelectorAll('.msg-thread-item').forEach(x=>x.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('chat-name').textContent=threads[id]?.title||'Admin';
  document.getElementById('chat-avatar').textContent=(threads[id]?.title||'SA').substring(0,2).toUpperCase();
  const msgs=await fetch('/api/messages/'+id,{headers:{'Accept':'application/json'}}).then(r=>r.json()).catch(()=>[]);
  threads[id].msgs=msgs;
  renderMessages();
}
function selectThread(id,el){ /* legacy */ }

function renderMessages(){
  const body=document.getElementById('msgBody');
  const t=threads[currentThread];
  if(!t) return;
  const msgs=t.msgs||[];
  body.innerHTML=(msgs.length?'<div class="date-divider">Today</div>':'')+
    msgs.map(m=>{
      const isMe=m.sender_type==='officer';
      const avatar=isMe?'Me':(m.sender_name||'SA').substring(0,2).toUpperCase();
      return `<div class="msg-bubble-wrap ${isMe?'mine':''}">
        <div class="bubble-avatar ${isMe?'ba-mine':'ba-admin'}">${avatar}</div>
        <div>
          <div class="bubble ${isMe?'from-mine':'from-admin'}">${m.content}</div>
          <span class="bubble-time">${m.created_at||''}</span>
        </div>
      </div>`;
    }).join('');
  body.scrollTop=body.scrollHeight;
}

async function sendMessage(){
  const inp=document.getElementById('msgInput');
  const txt=inp.value.trim(); if(!txt||!currentThread) return;
  inp.value='';
  const res=await fetch('/api/messages/'+currentThread,{
    method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},
    body:JSON.stringify({content:txt}),
  }).then(r=>r.json()).catch(()=>null);
  if(res?.success){
    if(!threads[currentThread].msgs) threads[currentThread].msgs=[];
    threads[currentThread].msgs.push(res.message);
    renderMessages();
  }
}

/* New officer conversation */
function openOfficerNewConv(){
  const el=document.getElementById('modal-officerNewConv');
  if(el){document.getElementById('officerConvTitle').value='';el.classList.add('active');}
}
async function startOfficerConversation(){
  const title=document.getElementById('officerConvTitle').value.trim();
  if(!title){showToast('⚠ Please enter a subject.');return;}
  const res=await apiPost('/api/messages/start',{title});
  if(res.success){
    closeModal('officerNewConv');
    const t=res.conversation;
    threads[t.id]={...t,msgs:[]};
    await loadOfficerThreads();
    currentThread=t.id;
    showToast('✅ Conversation started.');
  } else {
    showToast('⚠ Could not start conversation.');
  }
}

/* ══ RENDER ══ */
function renderOverviewAnn(){
  const el=document.getElementById('overviewAnnList');
  const tagLabel={notice:'Notice',urgent:'Urgent',event:'Event'};
  el.innerHTML=adminAnnouncements.length?adminAnnouncements.slice(0,5).map(a=>`
    <div class="ann-item">
      <div class="ann-meta">
        <span class="pill pill-${a.tag||'notice'}">${tagLabel[a.tag]||'Notice'}</span>
        <span class="ann-date">${a.created_at}</span>
      </div>
      <div class="ann-title">${a.title}</div>
      <div class="ann-body">${a.content}</div>
    </div>`).join(''):'<div class="empty-state"><div class="empty-icon">📢</div>No announcements yet.</div>';
}

function renderMyAnn(){
  const el=document.getElementById('myAnnList');
  const tagLabel={notice:'Notice',urgent:'Urgent',event:'Event'};
  if(!myAnnouncements.length){
    el.innerHTML='<div class="empty-state"><div class="empty-icon">📢</div>No announcements posted yet.</div>';
    return;
  }
  el.innerHTML=myAnnouncements.map(a=>`
    <div class="ann-item">
      <div class="ann-meta">
        <span class="pill pill-${a.tag||'notice'}">${tagLabel[a.tag]||'Notice'}</span>
        <span class="ann-date">${a.created_at}</span>
      </div>
      <div class="ann-title">${a.title}</div>
      <div class="ann-body">${a.content}</div>
      <div class="ann-actions">
        <button class="btn btn-sm btn-danger" onclick="deleteMyAnn(${a.id})">Delete</button>
      </div>
    </div>`).join('');
}
async function deleteMyAnn(id){
  await apiDelete('/api/announcements/'+id);
  adminAnnouncements=adminAnnouncements.filter(a=>a.id!==id);
  myAnnouncements=myAnnouncements.filter(a=>a.id!==id);
  renderMyAnn();
  renderOverviewAnn();
  showToast('🗑️ Announcement deleted.');
  if(document.getElementById('annCount')) document.getElementById('annCount').textContent=myAnnouncements.length;
}
async function postAnnouncement(){
  const tag=document.getElementById('annType').value;
  const title=document.getElementById('annTitle').value.trim();
  const content=document.getElementById('annBody').value.trim();
  const target=document.getElementById('annTarget').value;
  const priority=document.getElementById('annPriority').value;
  const event_date=document.getElementById('annDate').value||null;
  if(!title||!content){showToast('⚠ Please fill in all fields.');return;}
  const res=await apiPost('/api/announcements',{tag,title,content,target,priority,event_date});
  if(res.success){
    adminAnnouncements.unshift(res.announcement);
    myAnnouncements.unshift(res.announcement);
    document.getElementById('annTitle').value='';
    document.getElementById('annBody').value='';
    renderMyAnn();
    renderOverviewAnn();
    if(document.getElementById('annCount')) document.getElementById('annCount').textContent=myAnnouncements.length;
    showToast('✅ Announcement posted!');
  } else {
    showToast('⚠ '+(res.message||'Failed to post announcement.'));
  }
}

/* ══ ISSUE RESPONSES ══ */
let officerRespondingId=null;
function renderOfficerIssues(){
  const sf=(document.getElementById('officerIssueFilter')||{}).value||'';
  const filtered=officerIssues.filter(i=>!sf||i.status===sf);
  const statusPill={Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'};
  const el=document.getElementById('officerIssueList');
  el.innerHTML=filtered.length?filtered.map(i=>`
    <div class="issue-item" style="margin-bottom:12px;">
      <div class="issue-top">
        <div>
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:4px;">${i.resident} · ${i.block_lot}</div>
          <div class="issue-title">${i.title}</div>
        </div>
        ${statusPill[i.status]||''}
      </div>
      <div class="issue-body">${i.description}</div>
      <div class="issue-meta">
        <span class="pill pill-notice">${i.category}</span>
        <span class="meta-txt">· #${i.id} · ${i.created_at}</span>
      </div>
      ${(i.responses&&i.responses.length)?`<div style="margin-top:12px;padding:10px 12px;background:rgba(122,180,240,0.07);border-left:2px solid rgba(122,180,240,0.4);border-radius:0 6px 6px 0;">
        <div style="font-size:10px;color:var(--blue);font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">Response</div>
        <div style="font-size:13px;color:var(--text-mid);">${i.responses[0].content}</div>
      </div>`:''}
      <div class="issue-actions">
        ${i.status!=='Resolved'?`<button class="btn btn-sm" onclick="openOfficerRespond(${i.id})">Respond</button>`:''}
      </div>
    </div>`).join(''):'<div class="empty-state"><div class="empty-icon">📋</div>No issues found.</div>';
}
async function openOfficerRespond(id){
  officerRespondingId=id;
  const issue=officerIssues.find(i=>i.id===id);
  if(!issue) return;
  document.getElementById('officerRespondTitle').textContent=issue.title;
  document.getElementById('officerRespondStatus').value=issue.status;
  document.getElementById('officerRespondText').value=(issue.responses&&issue.responses[0])?issue.responses[0].content:'';
  openModal('officerRespond');
}
async function submitOfficerResponse(){
  const issue=officerIssues.find(i=>i.id===officerRespondingId);
  if(!issue) return;
  const newStatus=document.getElementById('officerRespondStatus').value;
  const content=document.getElementById('officerRespondText').value.trim();
  await Promise.all([
    apiPut('/api/issues/'+officerRespondingId+'/status',{status:newStatus}),
    content ? apiPost('/api/issues/'+officerRespondingId+'/respond',{content}) : Promise.resolve(),
  ]);
  issue.status=newStatus;
  if(content) issue.responses=[{content}];
  closeModal('officerRespond');
  renderOfficerIssues();
  showToast('✅ Response submitted.');
}

/* ══ FILE HANDLING ══ */
let selectedFile=null;
function handleFileSelect(inp){
  const f=inp.files[0]; if(!f) return;
  selectedFile=f;
  document.getElementById('selectedFileName').textContent=f.name;
  document.getElementById('selectedFileMeta').textContent=fmtBytes(f.size)+' · '+(f.type.split('/')[1]||'File').toUpperCase();
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
async function sendFile(){
  const cat    = document.getElementById('fileCategory').value;
  const period = document.getElementById('filePeriod').value.trim();
  const notes  = document.getElementById('fileNotes').value.trim();
  const input  = document.getElementById('fileUploadInput');
  if(!input.files.length){showToast('⚠ Please select a file to send.'); return;}

  const fd = new FormData();
  fd.append('file',     input.files[0]);
  fd.append('category', cat);
  fd.append('period',   period || todayStr());
  fd.append('notes',    notes);

  showToast('📤 Uploading…');
  try {
    const r   = await fetch('/api/officer-files', {
      method: 'POST',
      headers: {'Accept':'application/json','X-CSRF-TOKEN': csrfToken()},
      body: fd,
    });
    const res = await r.json();
    if(res.success){
      sentFiles.unshift({
        id: res.file.id, name: res.file.original_name, category: res.file.category,
        period: res.file.period, date: todayStr(),
        size: fmtBytes(res.file.size_bytes), status: 'sent',
      });
      clearFileSelection();
      document.getElementById('filePeriod').value = '';
      document.getElementById('fileNotes').value  = '';
      renderSentFiles();
      showToast('✅ File sent to admin: ' + res.file.original_name);
    } else {
      showToast('⚠ Upload failed.');
    }
  } catch(e) {
    showToast('⚠ Upload error: ' + e.message);
  }
}

function renderSentFiles(){
  const el=document.getElementById('sentFilesList');
  if(!sentFiles.length){
    el.innerHTML='<div class="empty-state"><div class="empty-icon">📊</div>No files sent yet.</div>';
  } else {
    el.innerHTML=sentFiles.map((f,i)=>`
      <div class="file-item">
        <span class="file-icon">📊</span>
        <div class="file-info">
          <div class="file-name">${f.name}</div>
          <div class="file-meta">${f.category} · ${f.period} · ${f.date} · ${f.size}</div>
        </div>
        <span class="file-status ${f.status==='sent'?'file-sent':'file-pending-send'}">${f.status==='sent'?'Sent':'Pending'}</span>
        ${f.id?`<button class="btn btn-sm btn-danger" style="margin-left:8px;" onclick="deleteSentFile(${f.id})">Delete</button>`:''}
      </div>`).join('');
  }
  if(document.getElementById('filesSentCount')) document.getElementById('filesSentCount').textContent=sentFiles.length;
}

async function deleteSentFile(id){
  const res=await apiDelete('/api/officer-files/'+id).catch(()=>({}));
  if(res.success){
    const idx=sentFiles.findIndex(f=>f.id===id);
    if(idx>-1) sentFiles.splice(idx,1);
    renderSentFiles();
    showToast('✅ File removed.');
  }
}

/* ══ INIT ══ */
loadOfficerData();
// Load previously sent files from server
(async()=>{
  try{
    const files=await apiGet('/api/officer-files');
    if(Array.isArray(files)&&files.length){
      sentFiles=files.map(f=>({
        id:f.id,
        name:f.original_name,
        category:f.category,
        period:f.period||'—',
        date:f.created_at?new Date(f.created_at).toLocaleDateString('en-PH'):'',
        size:f.size_bytes?Math.round(f.size_bytes/1024)+'KB':'—',
        status:'sent',
      }));
      renderSentFiles();
    }
  }catch(e){}
})();
</script>
</body>
</html>
