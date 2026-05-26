<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Resident Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
.banner-wrap {
  position: relative; height: 220px; overflow: hidden; cursor: pointer;
}
@media(max-width:480px){ .banner-wrap { height: 150px; } }
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
@media(max-width:480px){ .page { padding: 0 12px; } }
/* ───── Profile header ───── */
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
   flex: 0 0 auto; min-width: max-content; padding: 9px 16px;
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
.msg-sidebar-title { font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-dim); margin-bottom: 10px; }
.msg-thread-item {
  display: flex; align-items: center; gap: 9px; padding: 9px 10px;
  border-radius: var(--radius-xs); cursor: pointer; transition: background 0.15s;
  border: 1px solid transparent; margin-bottom: 3px;
}
.msg-thread-item:hover { background: var(--glass-hover); }
.msg-thread-item.active { background: var(--accent-dim); border-color: var(--accent-border); }
.msg-thread-del { display:none; background:none; border:none; cursor:pointer; font-size:13px; padding:2px 5px; color:var(--text-dim); border-radius:4px; flex-shrink:0; line-height:1; }
.msg-thread-del:hover { color:#f08080; }
.msg-thread-item:hover .msg-thread-del { display:block; }
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

.bubble { max-width:68%; max-width:min(68%, 340px); padding:10px 14px; border-radius:14px; font-size:13px; line-height:1.55; word-break:break-word; overflow-wrap:anywhere; }


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

@keyframes pulse-critical {
  0%,100% { box-shadow: 0 0 0 0 rgba(255,64,64,0); }
  50%      { box-shadow: 0 0 10px 4px rgba(255,64,64,0.28); }
}

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
/* ───── Live Announcement Banner ───── */
#annNotif {
  position: fixed; top: -90px; left: 50%; transform: translateX(-50%);
  background: linear-gradient(135deg,rgba(30,22,50,0.98),rgba(18,16,26,0.98));
  border: 1px solid rgba(122,180,240,0.35); backdrop-filter: blur(14px);
  color: var(--text); font-size: 13px; padding: 10px 14px 10px 16px;
  border-radius: 14px; z-index: 9999; display: flex; align-items: center; gap: 10px;
  max-width: 420px; box-shadow: 0 4px 24px rgba(0,0,0,0.45);
  transition: top 0.38s cubic-bezier(.22,.68,0,1.2);
}
#annNotif.show { top: 18px; }
#annNotif .ann-notif-title { font-weight:600; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
#annNotif .ann-meta { font-size:10px; color:var(--text-dim); text-transform:uppercase; letter-spacing:.06em; }
#annNotif .btn-view { background:var(--accent); color:#fff; border:none; padding:5px 11px; border-radius:8px; font-size:11px; cursor:pointer; }
#annNotif .btn-close { background:rgba(255,255,255,0.08); color:var(--text-dim); border:none; padding:5px 9px; border-radius:8px; font-size:11px; cursor:pointer; }
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
      <img id="profilePreview" src="https://ui-avatars.com/api/?name={{ urlencode($residentName) }}&background=c87941&color=fff&size=220&font-size=0.4" alt="Profile">
      <div class="avatar-edit">✎ Edit</div>
      <input type="file" id="profileInput" accept="image/*" onchange="changeImage('profilePreview',this)">
    </div>
    <div class="profile-meta">
      <h2 id="householdName" contenteditable="true" spellcheck="false" title="Click to edit your name">{{ $residentName }}</h2>
      <span class="location">📍 <span id="locationText">{{ $blockLot }}</span></span>
    </div>
    <form id="logoutForm" action="/logout" method="POST" style="display:none;">
      @csrf
    </form>
    <button onclick="confirmLogout()" style="margin-left:auto;align-self:flex-start;background:rgba(240,128,128,0.12);border:1px solid rgba(240,128,128,0.3);color:#f08080;padding:6px 14px;border-radius:8px;font-size:12px;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='rgba(240,128,128,0.22)'" onmouseout="this.style.background='rgba(240,128,128,0.12)'">⏻ Log Out</button>
  </div>

  <!-- Tab navigation -->
  <div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('overview')" id="tab-overview">🏠 Overview</button>
    <button class="tab-btn" onclick="switchTab('messages')" id="tab-messages">
      💬 Messages <span class="tab-badge" id="msg-badge">2</span>
    </button>
    <button class="tab-btn" onclick="switchTab('issues')" id="tab-issues">🔧 Issue Reports</button>
    <button class="tab-btn" onclick="switchTab('recs')" id="tab-recs">💡 Recommendations</button>
    <button class="tab-btn" onclick="switchTab('map')" id="tab-map">🗺️ Subdivision Map</button>
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

    <div class="card">
      <div class="card-title">My Account — Financial Records</div>
      <div style="display:flex;gap:12px;margin-bottom:14px;flex-wrap:wrap;">
        <div style="flex:1;min-width:140px;background:rgba(122,180,240,0.08);border:1px solid rgba(122,180,240,0.18);border-radius:8px;padding:12px 16px;">
          <div style="font-size:11px;color:var(--text-dim);letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">Outstanding Balance</div>
          <div style="font-size:22px;font-weight:600;color:#f08080;" id="resBalanceDisplay">₱{{ number_format($residentBalance ?? 0, 2) }}</div>
        </div>
      </div>
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead><tr style="border-bottom:1px solid rgba(255,255,255,0.08);">
          <th style="text-align:left;padding:6px 8px;color:var(--text-dim);font-weight:500;">Type</th>
          <th style="text-align:left;padding:6px 8px;color:var(--text-dim);font-weight:500;">Description</th>
          <th style="text-align:right;padding:6px 8px;color:var(--text-dim);font-weight:500;">Amount</th>
          <th style="text-align:right;padding:6px 8px;color:var(--text-dim);font-weight:500;">Date</th>
        </tr></thead>
        <tbody id="myFinancialRecords"><tr><td colspan="4" style="text-align:center;color:var(--text-dim);padding:20px;">Loading…</td></tr></tbody>
      </table>
    </div>

  </div>

  <!-- ═══════════ MESSAGES ═══════════ -->
  <div class="tab-panel" id="panel-messages">
    <div class="card" style="padding:16px;">
      <div class="card-title">Direct Messages</div>
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
            <option value="Critical">🚨 Critical / Emergency — Immediate danger</option>
            <option value="High">🔴 High — Urgent, same-day attention</option>
            <option value="Medium">🟡 Medium — Within a few days</option>
            <option value="Low">🟢 Low — General concern</option>
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
      <div class="f-row">
        <div class="f-field">
          <span class="f-label">📍 Location (optional)</span>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:6px;">
            <button type="button" class="btn" style="font-size:12px;padding:4px 12px;" onclick="useMyLocation()">Use My GPS</button>
            <button type="button" class="btn" style="font-size:12px;padding:4px 12px;" onclick="openIssuePicker()">Pin on Map</button>
            <span id="issue-loc-display" style="font-size:12px;color:var(--text-dim);">No location set</span>
          </div>
          <div id="issue-picker-map" style="display:none;height:240px;border-radius:8px;border:1px solid var(--glass-border);margin-bottom:8px;z-index:1;"></div>
          <input type="hidden" id="issue-lat">
          <input type="hidden" id="issue-lng">
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

  <!-- ═══════════ MAP ═══════════ -->
  <div class="tab-panel" id="panel-map">
    <div class="card" style="padding:0;overflow:hidden;">
      <div id="resident-map" style="width:100%;height:480px;"></div>
    </div>
    <div class="card" style="margin-top:12px;">
      <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:13px;color:var(--text-mid);align-items:center;">
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#4CAF50;display:inline-block;"></span>Facility</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#4287f5;display:inline-block;"></span>My Household</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#e05555;display:inline-block;"></span>Issue (Pending)</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#f5a623;display:inline-block;"></span>Issue (In Progress)</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:11px;height:11px;border-radius:50%;background:#888;display:inline-block;"></span>Issue (Resolved, ≤7d)</span>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin-left:auto;">
          <input type="checkbox" id="res-layer-heatmap" checked style="accent-color:var(--accent);">
          <span style="background:linear-gradient(to right,#1a003e,#5c0099,#0044bb,#cc4400,#ff2200);border-radius:4px;width:36px;height:11px;display:inline-block;"></span>
          Heatmap
        </label>
      </div>
    </div>
  </div>

</div><!-- end .page -->

<div id="toast"></div>
<div id="annNotif">
  <span style="font-size:18px;">📢</span>
  <div style="flex:1;min-width:0;">
    <div class="ann-meta">New Announcement</div>
    <div class="ann-notif-title" id="annNotifTitle">—</div>
  </div>
  <button class="btn-view" id="annNotifViewBtn">View</button>
  <button class="btn-close" onclick="document.getElementById('annNotif').classList.remove('show')">✕</button>
</div>

<script>
/* ══ UTILS ══ */
function confirmLogout(){
  if(confirm('Are you sure you want to log out?'))
    document.getElementById('logoutForm').submit();
}
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
    const b=document.getElementById('msg-badge');
    if(b) b.style.display='none';
    loadResidentThreads();
  }
  if(name==='map') setTimeout(initResidentMap,80);
}

/* ══ MEMBERS ══ */
let members=[];
async function loadHouseholdMembers(){
  if(!HOUSE_ID) return;
  try{
    const data=await fetch('/api/household-members?house_id='+HOUSE_ID,{headers:{'Accept':'application/json'}}).then(r=>r.json());
    if(Array.isArray(data)){
      members=data.map(m=>({id:m.id,name:m.name,role:m.relationship||'Member'}));
      renderMembers();
    }
  }catch(e){}
}
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
async function addMember(){
  const n=document.getElementById('memberName'),r=document.getElementById('memberRole');
  if(!n.value.trim()){n.focus();return;}
  const name=n.value.trim(),role=r.value.trim()||'Member';
  const res=await fetch('/api/household-members',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:JSON.stringify({house_id:HOUSE_ID,name,relationship:role})}).then(x=>x.json());
  if(res.success){
    members.push({id:res.member?.id,name,role});
    n.value=''; r.value=''; renderMembers();
  } else {
    showToast('⚠ '+(res.message||'Failed to add member.'));
  }
}
async function removeMember(i){
  const m=members[i];
  if(!m) return;
  if(!confirm(`Remove "${m.name}" from your household?`)) return;
  if(m.id){
    const res=await fetch('/api/household-members/'+m.id,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()}}).then(x=>x.json());
    if(!res.success){showToast('⚠ '+(res.message||'Failed to remove.'));return;}
  }
  members.splice(i,1); renderMembers();
}
document.getElementById('memberRole').addEventListener('keydown',e=>{if(e.key==='Enter')addMember();});

/* ══ ANNOUNCEMENTS ══ */
/* ══ ANNOUNCEMENTS ══ */
let announcements = [];
let lastAnnouncementId = 0;

async function loadAnnouncements(){
  const data = await fetch('/api/announcements').then(r=>r.json()).catch(()=>[]);
  announcements = Array.isArray(data) ? data : [];
  if(announcements.length) lastAnnouncementId = announcements[0].id;
  renderAnnouncements();
}

function showAnnBanner(ann){
  document.getElementById('annNotifTitle').textContent = ann.title;
  document.getElementById('annNotif').classList.add('show');
  const notif = document.getElementById('annNotif');
  clearTimeout(notif._t);
  notif._t = setTimeout(()=>notif.classList.remove('show'), 8000);
}

document.addEventListener('DOMContentLoaded', ()=>{
  document.getElementById('annNotifViewBtn').onclick = ()=>{
    document.getElementById('annNotif').classList.remove('show');
    document.getElementById('announcementList').closest('.card').scrollIntoView({behavior:'smooth'});
  };
  // Poll for new announcements every 15 s (skip if tab hidden)
  setInterval(async ()=>{
    if(document.hidden) return;
    try {
      const data = await fetch('/api/announcements').then(r=>r.json());
      if(!Array.isArray(data)||!data.length) return;
      if(data[0].id > lastAnnouncementId){
        const newOnes = data.filter(a=>a.id > lastAnnouncementId);
        lastAnnouncementId = data[0].id;
        announcements = data;
        renderAnnouncements();
        showAnnBanner(newOnes[0]);
      }
    } catch(e){}
  }, 15000);

  // Poll active message thread every 10s when messages tab is open
  setInterval(async ()=>{
    if(document.hidden || !currentThread) return;
    if(document.getElementById('panel-messages')?.classList.contains('active')){
      try{
        const msgs = await fetch('/api/messages/'+currentThread,{headers:{'Accept':'application/json'}}).then(r=>r.json());
        if(msgs.length !== (threads[currentThread]?.messages||[]).length){
          threads[currentThread].messages = msgs;
          renderMessages();
        }
      }catch(e){}
    }
  }, 10000);

  // Poll issues every 30s when issues tab is visible (catches admin status updates)
  setInterval(async ()=>{
    if(document.hidden) return;
    if(document.getElementById('panel-issues')?.classList.contains('active')){
      try{ issues = await fetch('/api/issues/my').then(r=>r.json()); renderIssues(); }catch(e){}
    }
  }, 30000);

  // Poll recommendations every 30s when recs tab is visible
  setInterval(async ()=>{
    if(document.hidden) return;
    if(document.getElementById('panel-recs')?.classList.contains('active')){
      try{ recs = await fetch('/api/recommendations/my').then(r=>r.json()); renderRecs(); }catch(e){}
    }
  }, 30000);

  // Poll financial records every 30s when finance tab is visible
  setInterval(async ()=>{
    if(document.hidden) return;
    if(document.getElementById('panel-finance')?.classList.contains('active')){
      try{ await loadResidentFinancials(); }catch(e){}
    }
  }, 30000);
});
function renderAnnouncements(){
  const tagLabel={notice:'Notice',urgent:'Urgent',event:'Event'};
  document.getElementById('announcementList').innerHTML=announcements.length?announcements.map(a=>{
    const meta=[];
    if(a.priority==='High') meta.push('<span style="color:#f08080;font-weight:700;font-size:11px;">⚡ High Priority</span>');
    if(a.target&&a.target!=='All Residents') meta.push('<span style="font-size:11px;color:var(--text-mid);">👥 '+a.target+'</span>');
    if(a.event_date) meta.push('<span style="font-size:11px;color:var(--text-mid);">📅 '+a.event_date+'</span>');
    return `
    <div class="ann-item">
      <div class="ann-meta">
        <span class="pill pill-${a.tag||'notice'}">${tagLabel[a.tag]||'Notice'}</span>
        <span class="ann-date">${a.created_at}</span>
      </div>
      ${meta.length?'<div style="display:flex;gap:10px;flex-wrap:wrap;margin:4px 0;">'+meta.join('')+'</div>':''}
      <div class="ann-title">${a.title}</div>
      <div class="ann-body">${a.content}</div>
    </div>`}).join(''):'<div class="empty-state"><div class="empty-icon">📢</div>No announcements yet.</div>';
}

/* ══ MESSAGES ══ */
let threads={};
let currentThread=null;

async function loadResidentThreads(){
  const data = await fetch('/api/messages/threads',{headers:{'Accept':'application/json'}}).then(r=>r.json()).catch(()=>[]);
  const sidebar = document.querySelector('.msg-sidebar-threads');
  if(!Array.isArray(data)||!data.length){
    if(sidebar) sidebar.innerHTML='<div style="color:var(--text-dim);font-size:12px;padding:12px;">No conversations yet.<br><button class="btn btn-sm" style="margin-top:8px;" onclick="openNewConvModal()">Start one</button></div>';
    return;
  }
  data.forEach((t,i)=>{threads[t.id]={...t,messages:[]};});
  if(sidebar){
    sidebar.innerHTML=data.map((t,i)=>`<div class="msg-thread-item${i===0?' active':''}" onclick="selectResThread(${t.id},this)">
      <div class="msg-thread-avatar ta-admin">${(t.title||'?').substring(0,2).toUpperCase()}</div>
      <div class="msg-thread-info">
        <div class="msg-thread-name">${t.title}</div>
        <div class="msg-thread-preview">${t.last_message||'No messages yet'}</div>
      </div>
      <button class="msg-thread-del" onclick="event.stopPropagation();deleteResidentConversation(${t.id})" title="Delete">🗑</button>
    </div>`).join('');
  }
  // Load first thread
  currentThread=data[0].id;
  document.getElementById('chat-name').textContent=data[0].title||'Admin';
  document.getElementById('chat-avatar').textContent=(data[0].title||'SA').substring(0,2).toUpperCase();
  const msgs=await fetch('/api/messages/'+currentThread,{headers:{'Accept':'application/json'}}).then(r=>r.json()).catch(()=>[]);
  threads[currentThread].messages=msgs;
  renderMessages();
}
async function selectResThread(id, el){
  currentThread=id;
  document.querySelectorAll('.msg-thread-item').forEach(x=>x.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('chat-name').textContent=threads[id]?.title||'Admin';
  document.getElementById('chat-avatar').textContent=(threads[id]?.title||'SA').substring(0,2).toUpperCase();
  const msgs=await fetch('/api/messages/'+id,{headers:{'Accept':'application/json'}}).then(r=>r.json()).catch(()=>[]);
  threads[id].messages=msgs;
  renderMessages();
}

function selectThread(id, el){ /* legacy – kept for old static threads */ }

async function deleteResidentConversation(id){
  if(!confirm('Delete this conversation and all its messages?')) return;
  const csrfToken=document.querySelector('meta[name=csrf-token]')?.content||'';
  const res=await fetch('/api/messages/'+id,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken}}).then(r=>r.json()).catch(()=>({success:false}));
  if(res.success){
    delete threads[id];
    if(currentThread==id){
      currentThread=null;
      const remaining=Object.keys(threads);
      if(remaining.length){
        currentThread=remaining[0];
        await loadThreads();
      } else {
        document.getElementById('msgBody').innerHTML='<div style="color:var(--text-dim);text-align:center;padding:20px;font-size:13px;">No conversations.</div>';
        document.getElementById('chat-name').textContent='';
      }
    } else {
      await loadThreads();
    }
  } else {
    alert('Failed to delete conversation.');
  }
}

function escHtml(s){ const d=document.createElement('div'); d.textContent=s??''; return d.innerHTML; }
function renderMessages(){
  const body=document.getElementById('msgBody');
  const t=threads[currentThread];
  if(!t||!t.messages) return;
  body.innerHTML=(t.messages.length?'<div class="date-divider">Today</div>':'')+
    t.messages.map(m=>{
      const isMe=m.sender_type==='resident';
      const avatar=isMe?'Me':(m.sender_name||'SA').substring(0,2).toUpperCase();
      return `<div class="msg-bubble-wrap ${isMe?'mine':''}">
        <div class="bubble-avatar ${isMe?'ba-mine':'ba-admin'}">${escHtml(avatar)}</div>
        <div>
          <div class="bubble ${isMe?'from-mine':'from-admin'}">${escHtml(m.content)}</div>
          <span class="bubble-time">${escHtml(m.created_at||'')}</span>
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
    if(!threads[currentThread].messages) threads[currentThread].messages=[];
    threads[currentThread].messages.push(res.message);
    threads[currentThread].last_message=txt;
    renderMessages();
  } else {
    showToast('⚠ Could not send message.');
  }
}

async function openNewConvModal(){
  const inp=document.getElementById('newConvTitle');
  if(inp) inp.value='';
  // Populate recipient dropdown with Admin + officers
  const sel=document.getElementById('newConvRecipient');
  if(sel){
    const officers=await fetch('/api/officers').then(r=>r.json()).catch(()=>[]);
    sel.innerHTML='<option value="admin|0">🏢 HOA Admin</option>'+
      officers.map(o=>`<option value="officer|${o.id}">🛡️ ${o.name} (Officer)</option>`).join('');
  }
  const el=document.getElementById('modal-newConv');
  if(el) el.style.display='flex';
}
function closeNewConvModal(){
  const el=document.getElementById('modal-newConv');
  if(el) el.style.display='none';
}
async function startNewConversation(){
  const sel=document.getElementById('newConvRecipient');
  const [recipientType,recipientId]=sel?(sel.value||'admin|0').split('|'):['admin','0'];
  const recipientLabel=sel?sel.options[sel.selectedIndex].text:'HOA Admin';
  let title=document.getElementById('newConvTitle').value.trim();
  if(!title) title=recipientLabel.replace(/^[^ ]+ /,''); // default to recipient name
  const payload={title};
  if(recipientType==='officer') { payload.recipient_type='officer'; payload.recipient_id=recipientId; }
  const res=await fetch('/api/messages/start',{
    method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},
    body:JSON.stringify(payload),
  }).then(r=>r.json()).catch(()=>null);
  if(res?.success){
    closeNewConvModal();
    const t=res.conversation;
    threads[t.id]={...t,messages:[]};
    await loadResidentThreads();
    currentThread=t.id;
    showToast('✅ Conversation started.');
  } else {
    showToast('⚠ Could not start conversation.');
  }
}

/* ══ ISSUES ══ */
let issues = [];

const csrfToken = () => document.querySelector('meta[name=csrf-token]').content;

async function loadIssues(){
  issues = await fetch('/api/issues/my').then(r=>r.json());
  renderIssues();
}
function renderIssues(){
  const el=document.getElementById('issueList');
  if(!issues.length){
    el.innerHTML='<div class="empty-state"><div class="empty-icon">📋</div>No reports submitted yet.</div>';
    return;
  }
  const statusPill={Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'};
  const priStyle={Critical:'background:rgba(220,30,30,0.18);color:#ff5555;border:1px solid rgba(220,30,30,0.4);',High:'background:rgba(240,128,128,0.13);color:#f08080;border:1px solid rgba(240,128,128,0.3);',Medium:'background:rgba(245,166,35,0.13);color:var(--yellow);border:1px solid rgba(245,166,35,0.3);',Low:'background:rgba(76,175,80,0.13);color:var(--green);border:1px solid rgba(76,175,80,0.3);'};
  el.innerHTML=issues.map(i=>{
    const priLabel={Critical:'🚨 Critical',High:'🔴 High',Medium:'🟡 Medium',Low:'🟢 Low'}[i.priority]||'';
    const priSpan=i.priority?`<span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;${priStyle[i.priority]||''}">${priLabel}</span>`:'';
    const isCritical=i.priority==='Critical';
    return `
    <div class="issue-item" style="${isCritical?'border-left:3px solid #ff4040;animation:pulse-critical 2s ease-in-out infinite;':''}">
      <div class="issue-top">
        <div class="issue-title">${i.title}</div>
        <div style="display:flex;gap:6px;align-items:center;">${priSpan}${statusPill[i.status]||''}</div>
      </div>
      <div class="issue-body">${i.description}</div>
      <div class="issue-meta">
        <span class="pill pill-notice">${i.category}</span>
        <span class="meta-txt">· #${i.id} · Filed ${i.created_at}</span>
      </div>
      ${i.response?`
        <div class="issue-response">
          <div class="issue-response-label">Admin Response</div>
          <div class="issue-response-text">${i.response}</div>
        </div>`:''}
    </div>`}).join('');
}

async function submitIssue(){
  const category=document.getElementById('issueCategory').value;
  const priority=document.getElementById('issuePriority').value;
  const title=document.getElementById('issueTitle').value.trim();
  const description=document.getElementById('issueBody').value.trim();
  const latitude=document.getElementById('issue-lat').value||null;
  const longitude=document.getElementById('issue-lng').value||null;
  if(!category||!title||!description){showToast('⚠ Please fill in all fields.');return;}
  if(priority==='Critical'&&!confirm('🚨 You are about to submit a CRITICAL / EMERGENCY issue.\n\nThis will immediately alert the admin and officers.\n\nContinue?')) return;
  const res=await fetch('/api/issues',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:JSON.stringify({category,title,description,latitude,longitude,priority:priority||null})});
  const data=await res.json();
  if(data.success){
    ['issueCategory','issuePriority','issueTitle','issueBody','issue-lat','issue-lng'].forEach(id=>{const el=document.getElementById(id);if(el)el.value=''});
    const disp=document.getElementById('issue-loc-display');
    if(disp) disp.textContent='No location set';
    closeIssuePicker();
    await loadIssues();
    if(priority==='Critical'){
      showToast('🚨 CRITICAL issue submitted. Admin has been notified.');
    } else {
      showToast('✅ Report submitted. The admin will review it shortly.');
    }
  } else {
    showToast('⚠ '+(data.message||'Failed to submit report. Please try again.'));
  }
}

/* ══ RECOMMENDATIONS ══ */
let recs = [];

async function loadRecs(){
  recs = await fetch('/api/recommendations/my').then(r=>r.json());
  renderRecs();
}
function renderRecs(){
  const el=document.getElementById('recList');
  if(!recs.length){
    el.innerHTML='<div class="empty-state"><div class="empty-icon">💡</div>No suggestions yet. Be the first to share an idea!</div>';
    return;
  }
  const statusLabel={Pending:'Pending',Reviewed:'Reviewed',Approved:'✅ Approved',Rejected:'❌ Rejected'};
  el.innerHTML=recs.map(r=>`
    <div class="rec-item">
      <div class="rec-content">
        <div class="rec-top">
          <div class="rec-title">${r.title}</div>
          <span class="pill pill-idea">${statusLabel[r.status]||r.status}</span>
        </div>
        ${r.category?`<div style="font-size:11px;color:var(--text-dim);margin-bottom:4px;">${r.category}</div>`:''}
        <div class="rec-body">${r.description}</div>
        <div class="rec-meta">
          <span>${r.created_at ? new Date(r.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : '—'}</span>
          <span>· Status: ${statusLabel[r.status]||r.status}</span>
        </div>
      </div>
    </div>`).join('');
}

async function submitRec(){
  const category=document.getElementById('recCategory').value;
  const title=document.getElementById('recTitle').value.trim();
  const description=document.getElementById('recBody').value.trim();
  if(!category||!title||!description){showToast('⚠ Please fill in all fields.');return;}
  const res=await fetch('/api/recommendations',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:JSON.stringify({title,description,category})});
  const data=await res.json();
  if(data.success){
    document.getElementById('recCategory').value='';
    document.getElementById('recTitle').value='';
    document.getElementById('recBody').value='';
    await loadRecs();
    showToast('✅ Suggestion submitted!');
  } else {
    showToast('⚠ '+(data.message||'Failed to submit suggestion. Please try again.'));
  }
}

const RESIDENT_ID = {{ $residentId ?? 'null' }};
const HOUSE_ID = {{ $houseId ?? 'null' }};
const RESIDENT_NAME = @json($residentName);
const RESIDENT_BALANCE = {{ $residentBalance ?? 0 }};

async function loadResidentFinancials(){
  const tbody = document.getElementById('myFinancialRecords');
  if(!tbody) return;
  try {
    const records = await fetch('/api/financial/my',{headers:{'Accept':'application/json'}}).then(r=>r.json());
    if(!Array.isArray(records)||!records.length){
      tbody.innerHTML='<tr><td colspan="4" style="text-align:center;color:var(--text-dim);padding:16px;">No financial records yet.</td></tr>';
      return;
    }
    tbody.innerHTML=records.map(r=>`<tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
      <td style="padding:7px 8px;"><span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;background:${r.record_type==='Payment'?'rgba(130,201,138,0.15)':'rgba(240,128,128,0.15)'};color:${r.record_type==='Payment'?'#82c98a':'#f08080'};">${r.record_type}</span></td>
      <td style="padding:7px 8px;color:var(--text-mid);">${r.description||'—'}</td>
      <td style="padding:7px 8px;text-align:right;color:${r.record_type==='Payment'?'#82c98a':'#f08080'};">${r.record_type==='Payment'?'+':'-'}₱${Number(r.amount).toLocaleString()}</td>
      <td style="padding:7px 8px;text-align:right;color:var(--text-dim);font-size:12px;">${r.record_date?new Date(r.record_date).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}):'—'}</td>
    </tr>`).join('');
  } catch(e){
    tbody.innerHTML='<tr><td colspan="4" style="text-align:center;color:var(--text-dim);padding:16px;">Could not load records.</td></tr>';
  }
}

/* ══ INIT ══ */
loadHouseholdMembers();
loadAnnouncements();
loadResidentFinancials();
renderMessages();
loadIssues();
loadRecs();

/* ── Save resident name on blur ── */
const _nameEl = document.getElementById('householdName');
if(_nameEl && RESIDENT_ID){
  let _nameSaved = _nameEl.textContent.trim();
  _nameEl.addEventListener('blur', async()=>{
    const n = _nameEl.textContent.trim();
    if(!n || n === _nameSaved){ _nameEl.textContent=_nameSaved||n; return; }
    try{
      const r = await fetch('/api/residents/'+RESIDENT_ID,{
        method:'PUT',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},
        body: JSON.stringify({name: n}),
      });
      const res = await r.json();
      if(res.success){ _nameSaved=n; showToast('✅ Name updated.'); }
      else { _nameEl.textContent=_nameSaved; showToast('⚠ Could not save name.'); }
    } catch(e){ _nameEl.textContent=_nameSaved; showToast('⚠ Network error.'); }
  });
  _nameEl.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); _nameEl.blur(); } });
}

/* ── Live balance refresh ── */
async function loadResidentBalance(){
  if(!RESIDENT_ID) return;
  try{
    const r = await fetch('/api/residents/me',{headers:{'Accept':'application/json'}});
    const d = await r.json();
    const el = document.getElementById('resBalanceDisplay');
    if(el && d.balance!=null)
      el.textContent = '₱' + Number(d.balance).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2});
  } catch(e){}
}
loadResidentBalance();
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
/* ══ RESIDENT MAP ══ */
const RES_CENTER = [10.62269, 122.96134];
let resMapInst = null, resLayerHeatmap = null;

function makeResCircle(color) {
  return L.divIcon({
    className: '',
    html: `<div style="width:13px;height:13px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.85);box-shadow:0 1px 5px rgba(0,0,0,.5);"></div>`,
    iconSize:[13,13], iconAnchor:[6,6], popupAnchor:[0,-9]
  });
}

function initResidentMap() {
  if (resMapInst) { setTimeout(()=>resMapInst.invalidateSize(),100); return; }
  resMapInst = L.map('resident-map').setView(RES_CENTER, 17);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 20,
  }).addTo(resMapInst);

  fetch('/api/map/facilities').then(r=>r.json()).then(data=>{
    data.forEach(f=>{
      L.marker([parseFloat(f.latitude),parseFloat(f.longitude)],{icon:makeResCircle('#4CAF50')})
        .bindPopup(`<strong>${f.name}</strong>${f.description?'<br><small>'+f.description+'</small>':''}`)
        .addTo(resMapInst);
    });
  });

  resLayerHeatmap = L.heatLayer([], {
    radius: 42, blur: 32, maxZoom: 17, max: 4, minOpacity: 0.0,
    gradient: {
      0.15: '#1a003e',
      0.35: '#5c0099',
      0.5:  '#0044bb',
      0.65: '#cc4400',
      0.82: '#cc1400',
      1.0:  '#ff2200'
    }
  }).addTo(resMapInst);
  setTimeout(() => { if (resLayerHeatmap._canvas) resLayerHeatmap._canvas.style.opacity = '0.70'; }, 150);

  document.getElementById('res-layer-heatmap').addEventListener('change', function() {
    this.checked ? resMapInst.addLayer(resLayerHeatmap) : resMapInst.removeLayer(resLayerHeatmap);
  });

  fetch('/api/map/issues').then(r=>r.json()).then(data=>{
    data.forEach(i=>{
      const color=i.status==='Resolved'?'#888':(i.status==='In Progress'?'#f5a623':'#e05555');
      L.marker([parseFloat(i.latitude),parseFloat(i.longitude)],{icon:makeResCircle(color)})
        .bindPopup(`<strong>${i.title}</strong><br><small>${i.status}</small>`)
        .addTo(resMapInst);
    });
    resLayerHeatmap.setLatLngs(data.map(i => [parseFloat(i.latitude), parseFloat(i.longitude), 1.0]));
  });

  // Place own household pin
  if(HOUSE_ID){
    fetch('/api/map/households',{headers:{'Accept':'application/json'}}).then(r=>r.json()).then(data=>{
      const own=data.find(h=>h.id===HOUSE_ID);
      if(own&&own.latitude&&own.longitude){
        L.marker([parseFloat(own.latitude),parseFloat(own.longitude)],{icon:makeResCircle('#4287f5')})
          .bindPopup(`<strong>My Household</strong><br><small>${own.block_lot_number||''}</small>`)
          .addTo(resMapInst);
      }
    }).catch(()=>{});
  }
}

/* ══ ISSUE LOCATION PICKER ══ */
let issuePickerMap = null, issuePickerPin = null;

function useMyLocation() {
  if (!navigator.geolocation) { showToast('Geolocation not supported.'); return; }
  navigator.geolocation.getCurrentPosition(pos=>{
    const lat=pos.coords.latitude, lng=pos.coords.longitude;
    document.getElementById('issue-lat').value=lat;
    document.getElementById('issue-lng').value=lng;
    document.getElementById('issue-loc-display').textContent=`📍 ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
    showToast('Location set via GPS.');
  }, ()=>showToast('Could not get location. Try pinning on map instead.'));
}

function openIssuePicker() {
  const box = document.getElementById('issue-picker-map');
  box.style.display = 'block';
  if (!issuePickerMap) {
    issuePickerMap = L.map('issue-picker-map').setView(RES_CENTER, 17);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:20}).addTo(issuePickerMap);
    issuePickerMap.on('click', function(e){
      const {lat,lng}=e.latlng;
      if(issuePickerPin) issuePickerPin.setLatLng([lat,lng]);
      else issuePickerPin=L.marker([lat,lng]).addTo(issuePickerMap);
      document.getElementById('issue-lat').value=lat.toFixed(7);
      document.getElementById('issue-lng').value=lng.toFixed(7);
      document.getElementById('issue-loc-display').textContent=`📍 ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
    });
  } else {
    setTimeout(()=>issuePickerMap.invalidateSize(),80);
  }
}

function closeIssuePicker() {
  document.getElementById('issue-picker-map').style.display='none';
}
</script>

<!-- New Conversation Modal -->
<div id="modal-newConv" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;">
  <div style="background:var(--surface,#1e2530);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:24px;width:min(420px,90vw);">
    <div style="font-size:15px;font-weight:600;margin-bottom:16px;">New Message</div>
    <label style="font-size:11px;color:var(--text-dim,#888);display:block;margin-bottom:4px;">To</label>
    <select id="newConvRecipient"
      style="width:100%;padding:8px 10px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:6px;color:inherit;font-size:13px;box-sizing:border-box;margin-bottom:12px;">
      <option value="admin|0">🏢 HOA Admin</option>
    </select>
    <label style="font-size:11px;color:var(--text-dim,#888);display:block;margin-bottom:4px;">Subject</label>
    <input type="text" id="newConvTitle" placeholder="e.g. Billing question, Gate pass request…"
      style="width:100%;padding:8px 10px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:6px;color:inherit;font-size:13px;box-sizing:border-box;margin-bottom:16px;">
    <div style="display:flex;gap:8px;justify-content:flex-end;">
      <button onclick="closeNewConvModal()" style="padding:7px 14px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:6px;color:inherit;font-size:13px;cursor:pointer;">Cancel</button>
      <button onclick="startNewConversation()" style="padding:7px 14px;background:var(--accent,#4e8ef7);border:none;border-radius:6px;color:#fff;font-size:13px;cursor:pointer;font-weight:500;">Send</button>
    </div>
  </div>
</div>
</body>
</html>
