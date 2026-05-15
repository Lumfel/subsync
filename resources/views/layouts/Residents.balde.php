<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resident Portal</title>
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
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'DM Sans', sans-serif;
  background:
    radial-gradient(ellipse 80% 60% at 70% 10%, rgba(80,50,30,0.42), transparent),
    radial-gradient(ellipse 60% 50% at 20% 80%, rgba(30,30,60,0.48), transparent),
    linear-gradient(160deg, #0b0b14 0%, #12101a 40%, #1a120d 100%);
  min-height: 100vh;
  color: var(--text);
  padding-bottom: 60px;
}

/* ───── Banner ───── */
.banner-wrap { position: relative; height: 220px; overflow: hidden; cursor: pointer; }
.banner-wrap img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.4s ease, filter 0.3s;
  filter: brightness(0.72) saturate(1.1);
}
.banner-wrap:hover img { transform: scale(1.03); filter: brightness(0.58) saturate(1.2); }
.banner-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to bottom, transparent 35%, rgba(10,10,18,0.94) 100%);
  pointer-events: none;
}
.banner-edit-hint {
  position: absolute; top: 14px; right: 16px;
  background: rgba(0,0,0,0.45); border: 1px solid var(--glass-border);
  backdrop-filter: blur(10px); color: var(--text); font-size: 12px;
  padding: 5px 12px; border-radius: 20px;
  opacity: 0; transition: opacity 0.2s; pointer-events: none;
}
.banner-wrap:hover .banner-edit-hint { opacity: 1; }
#bannerInput, #profileInput { display: none; }

/* ───── Page ───── */
.page { max-width: 880px; margin: 0 auto; padding: 0 20px; }

/* ───── Profile header ───── */
.profile-header {
  display: flex; align-items: flex-end; gap: 22px;
  margin-top: -54px; padding-bottom: 20px;
  position: relative; z-index: 2;
}
.avatar-wrap { position: relative; flex-shrink: 0; cursor: pointer; }
.avatar-wrap img {
  width: 110px; height: 110px; border-radius: var(--radius);
  border: 3px solid rgba(255,255,255,0.15);
  object-fit: cover; display: block; transition: filter 0.25s;
}
.avatar-wrap:hover img { filter: brightness(0.55); }
.avatar-edit {
  position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
  border-radius: var(--radius); opacity: 0; transition: opacity 0.2s;
  font-size: 11px; color: #fff; background: rgba(0,0,0,0.32); letter-spacing: 0.04em;
}
.avatar-wrap:hover .avatar-edit { opacity: 1; }
.profile-meta { padding-bottom: 6px; }
.profile-meta h2 {
  font-family: 'DM Serif Display', serif;
  font-size: 24px; font-weight: 400; letter-spacing: 0.01em; margin-bottom: 6px;
}
.location {
  display: inline-flex; align-items: center; gap: 5px; font-size: 13px;
  color: var(--accent); background: var(--accent-dim);
  border: 1px solid var(--accent-border); padding: 3px 10px; border-radius: 20px;
}
[contenteditable]:focus { outline: none; }

/* ───── Tab nav ───── */
.tab-nav {
  display: flex; gap: 4px;
  background: rgba(0,0,0,0.25); border: 1px solid var(--glass-border);
  border-radius: var(--radius); padding: 5px; margin-top: 6px;
  overflow-x: auto; scrollbar-width: none;
}
.tab-nav::-webkit-scrollbar { display: none; }
.tab-btn {
  flex: 1; min-width: max-content; padding: 9px 16px;
  border-radius: var(--radius-sm); border: 1px solid transparent;
  background: transparent; color: var(--text-mid);
  font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500;
  cursor: pointer; transition: background 0.2s, color 0.2s;
  display: flex; align-items: center; justify-content: center; gap: 7px; white-space: nowrap;
}
.tab-btn:hover { background: var(--glass-hover); color: var(--text); }
.tab-btn.active { background: var(--accent-dim); color: var(--accent); border-color: var(--accent-border); }
.tab-badge {
  background: rgba(232,168,124,0.3); color: var(--accent);
  font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 10px;
}

/* ───── Tab panels ───── */
.tab-panel { display: none; animation: fadeUp 0.3s ease both; }
.tab-panel.active { display: block; }
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ───── Card ───── */
.card {
  background: var(--glass); border: 1px solid var(--glass-border);
  border-radius: var(--radius); backdrop-filter: blur(16px);
  padding: 22px; margin-top: 16px;
}
.card-title {
  font-size: 11px; font-weight: 600; letter-spacing: 0.11em; text-transform: uppercase;
  color: var(--text-dim); margin-bottom: 18px;
  display: flex; align-items: center; gap: 8px;
}
.card-title::after { content: ''; flex: 1; height: 1px; background: var(--glass-border); }

/* ───── Form helpers ───── */
.f-row { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
.f-field { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 140px; }
.f-label { font-size: 11px; color: var(--text-dim); letter-spacing: 0.04em; }
input[type=text], textarea, select {
  background: rgba(255,255,255,0.07); border: 1px solid var(--glass-border);
  color: var(--text); padding: 9px 13px; border-radius: var(--radius-sm);
  font-family: 'DM Sans', sans-serif; font-size: 13px; outline: none;
  transition: border-color 0.2s, background 0.2s; width: 100%;
}
input[type=text]:focus, textarea:focus, select:focus {
  background: rgba(255,255,255,0.1); border-color: rgba(232,168,124,0.4);
}
input::placeholder, textarea::placeholder { color: var(--text-dim); }
textarea { resize: vertical; min-height: 88px; line-height: 1.55; }
select { cursor: pointer; }
select option { background: #1a120d; color: var(--text); }

.btn {
  padding: 9px 18px; background: var(--accent-dim); border: 1px solid var(--accent-border);
  color: var(--accent); border-radius: var(--radius-sm);
  font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500;
  cursor: pointer; transition: background 0.2s, transform 0.15s; white-space: nowrap;
}
.btn:hover { background: rgba(232,168,124,0.26); transform: translateY(-1px); }
.btn:active { transform: scale(0.98); }
.btn-full { width: 100%; justify-content: center; margin-top: 12px; }

/* ───── Pills ───── */
.pill {
  display: inline-block; font-size: 10px; font-weight: 700;
  letter-spacing: 0.07em; text-transform: uppercase; padding: 2px 9px; border-radius: 20px;
}
.pill-notice   { background: var(--blue-dim);              color: var(--blue);   border: 1px solid rgba(122,180,240,0.25); }
.pill-urgent   { background: rgba(220,80,80,0.13);         color: #f08080;       border: 1px solid rgba(220,80,80,0.25); }
.pill-event    { background: var(--green-dim);             color: var(--green);  border: 1px solid rgba(130,201,138,0.25); }
.pill-pending  { background: var(--yellow-dim);            color: var(--yellow); border: 1px solid rgba(240,192,96,0.25); }
.pill-progress { background: var(--blue-dim);              color: var(--blue);   border: 1px solid rgba(122,180,240,0.25); }
.pill-resolved { background: var(--green-dim);             color: var(--green);  border: 1px solid rgba(130,201,138,0.25); }
.pill-idea     { background: var(--purple-dim);            color: var(--purple); border: 1px solid rgba(185,154,245,0.25); }
.pill-cat      { background: var(--accent-dim);            color: var(--accent); border: 1px solid var(--accent-border); }

/* ───── OVERVIEW: Members ───── */
.member-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(162px, 1fr));
  gap: 10px; margin-bottom: 18px;
}
.member-card {
  background: var(--glass-hover); border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm); padding: 14px 14px 12px;
  display: flex; flex-direction: column; gap: 8px;
  position: relative; transition: background 0.2s, transform 0.2s;
}
.member-card:hover { background: rgba(255,255,255,0.14); transform: translateY(-2px); }
.member-avatar {
  width: 38px; height: 38px; border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), #c87941);
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 600; color: #1a100a;
}
.member-name { font-size: 13px; font-weight: 500; }
.member-role {
  font-size: 11px; color: var(--accent); background: var(--accent-dim);
  border: 1px solid rgba(232,168,124,0.18); padding: 2px 8px; border-radius: 20px;
  display: inline-block; width: fit-content;
}
.member-remove {
  position: absolute; top: 8px; right: 8px; width: 21px; height: 21px;
  border-radius: 50%; background: transparent; border: 1px solid transparent;
  color: var(--text-dim); cursor: pointer; font-size: 14px; line-height: 21px;
  text-align: center; opacity: 0; transition: opacity 0.2s, background 0.2s, color 0.2s; padding: 0;
}
.member-card:hover .member-remove { opacity: 1; }
.member-remove:hover { background: var(--danger); color: #fff; border-color: transparent; }
.add-row { display: flex; gap: 10px; flex-wrap: wrap; }
.add-row input { flex: 1; min-width: 120px; }

/* ── Announcements ── */
.ann-item {
  padding: 15px; border-radius: var(--radius-sm);
  background: rgba(255,255,255,0.04); border: 1px solid var(--glass-border);
  margin-bottom: 9px; transition: background 0.2s;
}
.ann-item:hover { background: rgba(255,255,255,0.07); }
.ann-meta { display: flex; align-items: center; justify-content: space-between; margin-bottom: 7px; }
.ann-date { font-size: 11px; color: var(--text-dim); }
.ann-title { font-size: 14px; font-weight: 500; margin-bottom: 4px; }
.ann-body { font-size: 13px; color: var(--text-mid); line-height: 1.6; }

/* ───── MESSAGES ───── */
.msg-layout { display: flex; gap: 14px; }
.msg-sidebar {
  width: 196px; flex-shrink: 0; background: rgba(0,0,0,0.2);
  border: 1px solid var(--glass-border); border-radius: var(--radius-sm);
  padding: 12px; height: fit-content;
}
.msg-sidebar-title { font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-dim); margin-bottom: 10px; }
.msg-thread-item {
  display: flex; align-items: center; gap: 9px; padding: 9px 10px;
  border-radius: var(--radius-xs); cursor: pointer; transition: background 0.15s;
  border: 1px solid transparent; margin-bottom: 3px;
}
.msg-thread-item:hover { background: var(--glass-hover); }
.msg-thread-item.active { background: var(--accent-dim); border-color: var(--accent-border); }
.msg-thread-avatar {
  width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;
}
.ta-admin { background: rgba(122,180,240,0.22); color: var(--blue); }
.ta-hoa   { background: rgba(185,154,245,0.22); color: var(--purple); }
.msg-thread-info { overflow: hidden; flex: 1; }
.msg-thread-name { font-size: 12px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-thread-preview { font-size: 11px; color: var(--text-dim); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-unread {
  min-width: 18px; height: 18px; border-radius: 9px;
  background: var(--accent); color: #1a100a;
  font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center;
  padding: 0 4px; flex-shrink: 0;
}

.msg-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.msg-header {
  background: rgba(0,0,0,0.22); border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm) var(--radius-sm) 0 0;
  padding: 12px 16px; display: flex; align-items: center; gap: 10px;
}
.msg-header-avatar {
  width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;
}
.msg-header-name { font-size: 14px; font-weight: 500; }
.msg-header-sub { font-size: 11px; color: var(--green); display: flex; align-items: center; gap: 4px; }
.msg-header-sub::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--green); display: inline-block; }

.msg-body {
  flex: 1; min-height: 300px; max-height: 360px; overflow-y: auto;
  background: rgba(0,0,0,0.14);
  border-left: 1px solid var(--glass-border); border-right: 1px solid var(--glass-border);
  padding: 18px 16px; display: flex; flex-direction: column; gap: 12px;
  scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.1) transparent;
}
.msg-body::-webkit-scrollbar { width: 4px; }
.msg-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

.msg-bubble-wrap { display: flex; align-items: flex-end; gap: 8px; }
.msg-bubble-wrap.mine { flex-direction: row-reverse; }
.bubble-avatar {
  width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;
}
.ba-admin  { background: rgba(122,180,240,0.2); color: var(--blue); }
.ba-hoa    { background: rgba(185,154,245,0.2); color: var(--purple); }
.ba-mine   { background: linear-gradient(135deg, var(--accent), #c87941); color: #1a100a; }
.bubble {
  max-width: 68%; padding: 10px 14px; border-radius: 14px; font-size: 13px; line-height: 1.55;
}
.bubble.from-admin { background: rgba(255,255,255,0.09); border: 1px solid var(--glass-border); border-bottom-left-radius: 4px; }
.bubble.from-mine  { background: var(--accent-dim); border: 1px solid var(--accent-border); border-bottom-right-radius: 4px; }
.bubble-time { font-size: 10px; color: var(--text-dim); margin-top: 3px; display: block; }
.bubble.from-mine .bubble-time { text-align: right; }

.msg-compose {
  background: rgba(0,0,0,0.22); border: 1px solid var(--glass-border); border-top: none;
  border-radius: 0 0 var(--radius-sm) var(--radius-sm);
  padding: 12px 14px; display: flex; gap: 10px; align-items: flex-end;
}
.msg-compose textarea {
  flex: 1; min-height: 44px; max-height: 120px; resize: none;
  background: rgba(255,255,255,0.07); border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm); padding: 10px 12px; font-size: 13px; line-height: 1.45;
}
.send-btn {
  width: 44px; height: 44px; border-radius: var(--radius-sm);
  background: var(--accent-dim); border: 1px solid var(--accent-border);
  color: var(--accent); font-size: 18px; cursor: pointer;
  transition: background 0.2s, transform 0.15s;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.send-btn:hover { background: rgba(232,168,124,0.28); transform: scale(1.05); }

.date-divider {
  display: flex; align-items: center; gap: 10px;
  font-size: 10px; color: var(--text-dim); letter-spacing: 0.06em; text-transform: uppercase;
}
.date-divider::before, .date-divider::after { content: ''; flex: 1; height: 1px; background: var(--glass-border); }

/* ───── ISSUES ───── */
.issue-item {
  background: rgba(255,255,255,0.04); border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm); padding: 16px; margin-bottom: 10px; transition: background 0.2s;
}
.issue-item:hover { background: rgba(255,255,255,0.07); }
.issue-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 6px; }
.issue-title { font-size: 14px; font-weight: 500; }
.issue-body { font-size: 13px; color: var(--text-mid); line-height: 1.6; margin-bottom: 10px; }
.issue-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.meta-txt { font-size: 11px; color: var(--text-dim); }
.priority-high   { color: #f08080; font-size: 11px; }
.priority-medium { color: var(--yellow); font-size: 11px; }
.priority-low    { color: var(--green); font-size: 11px; }
.issue-response {
  margin-top: 12px; padding: 10px 12px;
  background: rgba(122,180,240,0.07);
  border-left: 2px solid rgba(122,180,240,0.4);
  border-radius: 0 var(--radius-xs) var(--radius-xs) 0;
}
.issue-response-label { font-size: 10px; color: var(--blue); font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 4px; }
.issue-response-text { font-size: 13px; color: var(--text-mid); line-height: 1.55; }

/* ───── RECOMMENDATIONS ───── */
.rec-item {
  background: rgba(255,255,255,0.04); border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm); padding: 16px;
  display: flex; gap: 14px; margin-bottom: 10px; transition: background 0.2s;
}
.rec-item:hover { background: rgba(255,255,255,0.07); }
.rec-vote { display: flex; flex-direction: column; align-items: center; gap: 5px; flex-shrink: 0; padding-top: 2px; }
.vote-btn {
  width: 34px; height: 34px; border-radius: var(--radius-xs);
  background: var(--glass-hover); border: 1px solid var(--glass-border);
  color: var(--text-dim); font-size: 14px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.vote-btn:hover { background: var(--purple-dim); color: var(--purple); border-color: rgba(185,154,245,0.3); }
.vote-btn.voted { background: var(--purple-dim); color: var(--purple); border-color: rgba(185,154,245,0.3); }
.vote-count { font-size: 15px; font-weight: 600; }
.rec-content { flex: 1; min-width: 0; }
.rec-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 5px; }
.rec-title { font-size: 14px; font-weight: 500; }
.rec-body { font-size: 13px; color: var(--text-mid); line-height: 1.6; margin-bottom: 10px; }
.rec-meta { display: flex; align-items: center; gap: 8px; font-size: 11px; color: var(--text-dim); }

/* ───── Empty state ───── */
.empty-state { text-align: center; padding: 36px 20px; color: var(--text-dim); font-size: 13px; line-height: 1.7; }
.empty-icon { font-size: 30px; margin-bottom: 8px; opacity: 0.5; }

/* ───── Toast ───── */
#toast {
  position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(20px);
  background: rgba(18,16,26,0.96); border: 1px solid var(--glass-border);
  backdrop-filter: blur(12px); color: var(--text); font-size: 13px;
  padding: 10px 22px; border-radius: 20px;
  opacity: 0; pointer-events: none; transition: opacity 0.25s, transform 0.25s;
  z-index: 999; white-space: nowrap;
}
#toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>
</head>
<body>

<!-- Banner -->
<div class="banner-wrap" onclick="document.getElementById('bannerInput').click()">
  <img id="bannerPreview" src="https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=1200&q=80" alt="Community Banner">
  <div class="banner-overlay"></div>
  <div class="banner-edit-hint">✎ Change banner</div>
  <input type="file" id="bannerInput" accept="image/*" onchange="changeImage('bannerPreview',this)">
</div>

<div class="page">

  <!-- Profile header -->
  <div class="profile-header">
    <div class="avatar-wrap" onclick="document.getElementById('profileInput').click()">
      <img id="profilePreview" src="https://ui-avatars.com/api/?name=Dela+Cruz&background=c87941&color=fff&size=220&font-size=0.4" alt="Profile">
      <div class="avatar-edit">✎ Edit</div>
      <input type="file" id="profileInput" accept="image/*" onchange="changeImage('profilePreview',this)">
    </div>
    <div class="profile-meta">
      <h2 id="householdName" contenteditable="true" spellcheck="false">Dela Cruz Family</h2>
      <span class="location">📍 <span id="locationText" contenteditable="true" spellcheck="false">Blk 3 Lot 12</span></span>
    </div>
  </div>

  <!-- Tab navigation -->
  <div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('overview')" id="tab-overview">🏠 Overview</button>
    <button class="tab-btn" onclick="switchTab('messages')" id="tab-messages">
      💬 Messages <span class="tab-badge" id="msg-badge">2</span>
    </button>
    <button class="tab-btn" onclick="switchTab('issues')" id="tab-issues">🔧 Issue Reports</button>
    <button class="tab-btn" onclick="switchTab('recs')" id="tab-recs">💡 Recommendations</button>
  </div>

  <!-- ═══════════ OVERVIEW ═══════════ -->
  <div class="tab-panel active" id="panel-overview">

    <div class="card">
      <div class="card-title">Household Members</div>
      <div class="member-grid" id="memberGrid"></div>
      <div class="add-row">
        <input type="text" id="memberName" placeholder="Full name">
        <input type="text" id="memberRole" placeholder="Role (Head, Spouse…)">
        <button class="btn" onclick="addMember()">+ Add</button>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Homeowners Announcements & Events</div>
      <div id="announcementList"></div>
    </div>

  </div>

  <!-- ═══════════ MESSAGES ═══════════ -->
  <div class="tab-panel" id="panel-messages">
    <div class="card" style="padding:16px;">
      <div class="card-title">Direct Messages</div>
      <div class="msg-layout">

        <!-- Sidebar threads -->
        <div class="msg-sidebar">
          <div class="msg-sidebar-title">Conversations</div>
          <div class="msg-thread-item active" onclick="selectThread('admin',this)">
            <div class="msg-thread-avatar ta-admin">SA</div>
            <div class="msg-thread-info">
              <div class="msg-thread-name">Subdivision Admin</div>
              <div class="msg-thread-preview">Got it! We'll…</div>
            </div>
            <span class="msg-unread" id="admin-unread">2</span>
          </div>
          <div class="msg-thread-item" onclick="selectThread('hoa',this)">
            <div class="msg-thread-avatar ta-hoa">HOA</div>
            <div class="msg-thread-info">
              <div class="msg-thread-name">HOA Committee</div>
              <div class="msg-thread-preview">Meeting on May…</div>
            </div>
          </div>
        </div>

        <!-- Chat window -->
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

  <!-- ═══════════ ISSUES ═══════════ -->
  <div class="tab-panel" id="panel-issues">

    <div class="card">
      <div class="card-title">Report a Concern or Complaint</div>
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">Category</span>
          <select id="issueCategory">
            <option value="">Select category…</option>
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
        <div class="f-field">
          <span class="f-label">Priority Level</span>
          <select id="issuePriority">
            <option value="">Select priority…</option>
            <option value="high">🔴 High — Immediate attention</option>
            <option value="medium">🟡 Medium — Within a few days</option>
            <option value="low">🟢 Low — General concern</option>
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
          <textarea id="issueBody" placeholder="Describe the issue in detail — location, when it started, how it affects residents…"></textarea>
        </div>
      </div>
      <button class="btn btn-full" onclick="submitIssue()">Submit Report</button>
    </div>

    <div class="card">
      <div class="card-title">My Submitted Reports</div>
      <div id="issueList"></div>
    </div>

  </div>

  <!-- ═══════════ RECOMMENDATIONS ═══════════ -->
  <div class="tab-panel" id="panel-recs">

    <div class="card">
      <div class="card-title">Share a Suggestion or Initiative</div>
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
      <button class="btn btn-full" style="color:var(--purple);background:var(--purple-dim);border-color:rgba(185,154,245,0.3);" onclick="submitRec()">Submit Suggestion</button>
    </div>

    <div class="card">
      <div class="card-title">Community Suggestions — Most Supported</div>
      <div id="recList"></div>
    </div>

  </div>

</div><!-- end .page -->

<div id="toast"></div>

<script>
/* ══ UTILS ══ */
function changeImage(id,inp){
  const f=inp.files[0]; if(!f) return;
  const r=new FileReader();
  r.onload=e=>document.getElementById(id).src=e.target.result;
  r.readAsDataURL(f);
}
function initials(n){ return n.trim().split(/\s+/).map(w=>w[0]).slice(0,2).join('').toUpperCase(); }
function nowTime(){ return new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'}); }
function todayStr(){ return new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}); }
function showToast(msg){
  const t=document.getElementById('toast');
  t.textContent=msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),2800);
}

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

/* ══ MEMBERS ══ */
const members=[
  {name:'Juan Dela Cruz',role:'Head'},
  {name:'Maria Dela Cruz',role:'Spouse'},
  {name:'Jose Dela Cruz',role:'Child'},
];
function renderMembers(){
  const g=document.getElementById('memberGrid');
  if(!members.length){ g.innerHTML='<div class="empty-state">No members added yet.</div>'; return; }
  g.innerHTML=members.map((m,i)=>`
    <div class="member-card">
      <button class="member-remove" onclick="removeMember(${i})">×</button>
      <div class="member-avatar">${initials(m.name)}</div>
      <div class="member-name">${m.name}</div>
      <span class="member-role">${m.role||'Member'}</span>
    </div>`).join('');
}
function addMember(){
  const n=document.getElementById('memberName'),r=document.getElementById('memberRole');
  if(!n.value.trim()){n.focus();return;}
  members.push({name:n.value.trim(),role:r.value.trim()||'Member'});
  n.value=''; r.value=''; renderMembers();
}
function removeMember(i){ members.splice(i,1); renderMembers(); }
document.getElementById('memberRole').addEventListener('keydown',e=>{if(e.key==='Enter')addMember();});

/* ══ ANNOUNCEMENTS ══ */
const announcements=[
  {tag:'urgent',label:'Urgent',title:'Water Service Interruption — May 16',
   body:'Water supply halted 8:00 AM–5:00 PM for pipeline maintenance. Please store sufficient water in advance.',date:'May 15, 2026'},
  {tag:'event',label:'Event',title:'Community Clean-Up Drive',
   body:'All households are invited to join on May 18, 6:00 AM at the covered court. Gloves and bags provided.',date:'May 14, 2026'},
  {tag:'notice',label:'Notice',title:'Updated Subdivision Fee Schedule',
   body:'New monthly dues take effect June 2026. Visit the admin office for updated rates and payment options.',date:'May 12, 2026'},
];
function renderAnnouncements(){
  document.getElementById('announcementList').innerHTML=announcements.map(a=>`
    <div class="ann-item">
      <div class="ann-meta">
        <span class="pill pill-${a.tag}">${a.label}</span>
        <span class="ann-date">${a.date}</span>
      </div>
      <div class="ann-title">${a.title}</div>
      <div class="ann-body">${a.body}</div>
    </div>`).join('');
}

/* ══ MESSAGES ══ */
const threads={
  admin:{
    name:'Subdivision Admin', avatar:'SA', sub:'Online',
    avatarClass:'ta-admin', bubbleClass:'ba-admin',
    messages:[
      {from:'admin',text:'Good day, Mr. Dela Cruz! How can we assist you today?',time:'9:02 AM'},
      {from:'me',text:'Good morning! I wanted to report a pothole near the entrance of Block 3.',time:'9:05 AM'},
      {from:'admin',text:'Thank you for letting us know. Our maintenance team will check it within 48 hours. You may also file it formally under Issue Reports.',time:'9:08 AM'},
      {from:'me',text:"Great, I'll do that. Thank you!",time:'9:10 AM'},
      {from:'admin',text:"Got it! We'll update you once it's addressed. Have a good day! 😊",time:'9:11 AM'},
    ]
  },
  hoa:{
    name:'HOA Committee', avatar:'HOA', sub:'Usually replies within a day',
    avatarClass:'ta-hoa', bubbleClass:'ba-hoa',
    messages:[
      {from:'admin',text:'Good day! This is a reminder that the HOA monthly meeting is on May 20, 2026 at 3:00 PM in the function hall. Please confirm your attendance.',time:'May 13'},
    ]
  }
};
let currentThread='admin';

function selectThread(id, el){
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
    t.messages.map(m=>`
      <div class="msg-bubble-wrap ${m.from==='me'?'mine':''}">
        <div class="bubble-avatar ${m.from==='me'?'ba-mine':t.bubbleClass}">${m.from==='me'?'DC':t.avatar.slice(0,2)}</div>
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
  threads[currentThread].messages.push({from:'me',text:txt,time:nowTime()});
  inp.value=''; renderMessages();
  setTimeout(()=>{
    threads[currentThread].messages.push({from:'admin',text:'Thank you for your message. We will get back to you as soon as possible.',time:nowTime()});
    renderMessages();
  },1600);
}

/* ══ ISSUES ══ */
const issues=[
  {id:'RPT-001',category:'Road / Pavement',priority:'high',
   title:'Large pothole at Block 3 entrance',
   body:'There is a large pothole near the main gate of Block 3 causing vehicle damage. Present since early May.',
   date:'May 10, 2026',status:'progress',
   response:'Maintenance team dispatched. Work scheduled for May 17. Thank you for your patience.'},
  {id:'RPT-002',category:'Street Lighting',priority:'medium',
   title:'Streetlights out — Lot 14 to 18',
   body:'Three consecutive streetlights are non-functional along the Lot 14–18 stretch since last week.',
   date:'May 13, 2026',status:'pending',response:null},
];

function renderIssues(){
  const el=document.getElementById('issueList');
  if(!issues.length){
    el.innerHTML='<div class="empty-state"><div class="empty-icon">📋</div>No reports submitted yet.</div>';
    return;
  }
  const statusPill={pending:'<span class="pill pill-pending">Pending</span>',progress:'<span class="pill pill-progress">In Progress</span>',resolved:'<span class="pill pill-resolved">Resolved</span>'};
  const priMap={high:'🔴 High',medium:'🟡 Medium',low:'🟢 Low'};
  el.innerHTML=issues.map(i=>`
    <div class="issue-item">
      <div class="issue-top">
        <div class="issue-title">${i.title}</div>
        ${statusPill[i.status]||''}
      </div>
      <div class="issue-body">${i.body}</div>
      <div class="issue-meta">
        <span class="pill pill-notice">${i.category}</span>
        <span class="priority-${i.priority}">${priMap[i.priority]||i.priority}</span>
        <span class="meta-txt">· ${i.id}</span>
        <span class="meta-txt">· Filed ${i.date}</span>
      </div>
      ${i.response?`
        <div class="issue-response">
          <div class="issue-response-label">Admin Response</div>
          <div class="issue-response-text">${i.response}</div>
        </div>`:''}
    </div>`).join('');
}

function submitIssue(){
  const cat=document.getElementById('issueCategory').value;
  const pri=document.getElementById('issuePriority').value;
  const ttl=document.getElementById('issueTitle').value.trim();
  const body=document.getElementById('issueBody').value.trim();
  if(!cat||!pri||!ttl||!body){showToast('⚠ Please fill in all fields.');return;}
  const id='RPT-00'+(issues.length+1);
  issues.unshift({id,category:cat,priority:pri,title:ttl,body,date:todayStr(),status:'pending',response:null});
  document.getElementById('issueCategory').value='';
  document.getElementById('issuePriority').value='';
  document.getElementById('issueTitle').value='';
  document.getElementById('issueBody').value='';
  renderIssues();
  showToast('✅ Report submitted. The admin will review it shortly.');
}

/* ══ RECOMMENDATIONS ══ */
const recs=[
  {id:1,category:'Environment / Greenery',title:'Install shade trees along the main road',
   body:'Planting trees along the main road would reduce heat, improve air quality, and beautify the subdivision. A community tree-planting day could kick it off.',
   author:'Santos Family',date:'May 8, 2026',votes:24,voted:false},
  {id:2,category:'Security',title:'Add CCTV cameras at secondary gates',
   body:'The secondary exit at Block 5 has no camera coverage. One or two cameras there would significantly improve perimeter security.',
   author:'Reyes Family',date:'May 11, 2026',votes:18,voted:false},
  {id:3,category:'Facilities & Amenities',title:'Covered waiting shed near the main gate',
   body:'Residents waiting for rides are exposed to rain and sun. A simple roofed shed near the guard post would help especially children and the elderly.',
   author:'Gomez Family',date:'May 13, 2026',votes:31,voted:false},
];
let nextRecId=10;

function renderRecs(){
  const el=document.getElementById('recList');
  if(!recs.length){
    el.innerHTML='<div class="empty-state"><div class="empty-icon">💡</div>No suggestions yet. Be the first to share an idea!</div>';
    return;
  }
  const sorted=[...recs].sort((a,b)=>b.votes-a.votes);
  el.innerHTML=sorted.map(r=>`
    <div class="rec-item">
      <div class="rec-vote">
        <button class="vote-btn ${r.voted?'voted':''}" onclick="toggleVote(${r.id})" title="${r.voted?'Remove vote':'Upvote'}">▲</button>
        <div class="vote-count">${r.votes}</div>
      </div>
      <div class="rec-content">
        <div class="rec-top">
          <div class="rec-title">${r.title}</div>
          <span class="pill pill-idea">${r.category}</span>
        </div>
        <div class="rec-body">${r.body}</div>
        <div class="rec-meta">
          <span>by ${r.author}</span>
          <span>·</span>
          <span>${r.date}</span>
        </div>
      </div>
    </div>`).join('');
}

function toggleVote(id){
  const r=recs.find(x=>x.id===id); if(!r) return;
  r.voted=!r.voted; r.votes+=r.voted?1:-1;
  renderRecs();
}

function submitRec(){
  const cat=document.getElementById('recCategory').value;
  const ttl=document.getElementById('recTitle').value.trim();
  const body=document.getElementById('recBody').value.trim();
  if(!cat||!ttl||!body){showToast('⚠ Please fill in all fields.');return;}
  const houseName=document.getElementById('householdName').textContent.trim()||'Anonymous';
  recs.unshift({id:nextRecId++,category:cat,title:ttl,body,author:houseName,date:todayStr(),votes:0,voted:false});
  document.getElementById('recCategory').value='';
  document.getElementById('recTitle').value='';
  document.getElementById('recBody').value='';
  renderRecs();
  showToast('✅ Suggestion posted! Other residents can now vote on it.');
}

/* ══ INIT ══ */
renderMembers();
renderAnnouncements();
renderMessages();
renderIssues();
renderRecs();
</script>
</body>
</html>
