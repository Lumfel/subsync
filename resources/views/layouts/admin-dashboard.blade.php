<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SubSync — Admin Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
:root {
  --glass: #FFFFFF;
  --glass-hover: #FAF7F2;
  --glass-border: #E6DFD5;
  --accent: #A07D53;
  --accent-dim: #FAF6F0;
  --accent-border: #E6DFD5;
  --text: #2B2927;
  --text-dim: #7D7975;
  --text-mid: #4A4744;
  --danger: #BD5B5B;
  --blue: #5A7F9E;
  --blue-dim: #EEF3F6;
  --green: #6B8E70;
  --green-dim: #EAF2EC;
  --yellow: #C49646;
  --yellow-dim: #FAF4E7;
  --purple: #8C829E;
  --purple-dim: #F2EFF4;
  --radius: 12px;
  --radius-sm: 8px;
  --radius-xs: 6px;
  --sidebar-w: 230px;
}
*{ box-sizing:border-box; margin:0; padding:0; }
body {
  font-family: 'DM Sans', sans-serif;
  background: #FAF6F0;
  min-height: 100vh;
  color: var(--text);
  display: flex;
}

/* ── SCROLLBAR ── */
::-webkit-scrollbar { width: 6px; background: rgba(0,0,0,0.2); }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 6px; }

/* ── SIDEBAR ── */
.sidebar {
  width: var(--sidebar-w);
  flex-shrink: 0;
  background: #FFFFFF;
  border-right: 1px solid var(--glass-border);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
}
.sidebar-brand {
  padding: 18px 20px 14px;
  border-bottom: 1px solid var(--glass-border);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}
.brand-logo img {
  width: 130px;
  height: auto;
  display: block;
  filter: none;
}
.brand-sub {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.12em;
  text-transform: uppercase;
  text-align: center;
}
.sidebar-nav { flex: 1; padding: 12px 10px; }
.nav-section-label {
  font-size: 9px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--text-dim);
  padding: 12px 10px 6px;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 12px;
  border-radius: var(--radius-sm);
  cursor: pointer;
  font-size: 13px;
  color: var(--text-mid);
  border: 1px solid transparent;
  margin-bottom: 2px;
  transition: background 0.2s, color 0.2s;
  user-select: none;
}
.nav-item:hover { background: var(--glass-hover); color: var(--text); }
.nav-item.active {
  background: var(--accent-dim);
  color: var(--accent);
  border-color: var(--accent-border);
}
.nav-icon { font-size: 15px; flex-shrink: 0; }
.nav-badge {
  margin-left: auto;
  background: var(--accent);
  color: #1a100a;
  font-size: 10px;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 10px;
}
.sidebar-footer {
  padding: 14px;
  border-top: 1px solid var(--glass-border);
}
.admin-profile {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border-radius: var(--radius-sm);
  background: var(--glass);
  border: 1px solid var(--glass-border);
}
.admin-avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), #c87941);
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 600; color: #1a100a; flex-shrink: 0;
}
.admin-name { font-size: 13px; font-weight: 500; }
.admin-role { font-size: 10px; color: var(--text-dim); }

/* ── MAIN ── */
.main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 28px;
  border-bottom: 1px solid var(--glass-border);
  background: #FFFFFF;
  position: sticky;
  top: 0;
  z-index: 10;
}
.topbar-title { font-family: 'DM Serif Display', serif; font-size: 18px; color: var(--text); }
.topbar-actions { display: flex; align-items: center; gap: 10px; }
.topbar-btn {
  padding: 7px 14px;
  background: var(--glass);
  border: 1px solid var(--glass-border);
  color: var(--text-mid);
  border-radius: var(--radius-sm);
  font-size: 12px;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  transition: background 0.2s, color 0.2s;
}
.topbar-btn:hover { background: var(--glass-hover); color: var(--text); }
.topbar-btn.primary {
  background: var(--accent-dim);
  border-color: var(--accent-border);
  color: var(--accent);
}
.menu-toggle {
  display: none;
  background: none;
  border: none;
  color: var(--text);
  font-size: 20px;
  cursor: pointer;
}
.content { padding: 24px 28px; flex: 1; overflow-y: auto; }

/* ── PANELS ── */
.panel { display: none; animation: fadeUp 0.28s ease both; }
.panel.active { display: block; }
@keyframes fadeUp {
  from { opacity:0; transform:translateY(10px); }
  to { opacity:1; transform:translateY(0); }
}

/* ── CARD ── */
.card {
  background: var(--glass);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius);
  backdrop-filter: blur(16px);
  padding: 22px;
  margin-bottom: 16px;
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
}
.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(43, 41, 39, 0.04);
}
.card-title {
  font-size: 11px; font-weight: 600;
  letter-spacing: 0.11em; text-transform: uppercase;
  color: var(--text-dim); margin-bottom: 18px;
  display: flex; align-items: center; gap: 8px;
}
.card-title::after { content:''; flex:1; height:1px; background:var(--glass-border); }

/* ── STAT GRID ── */
.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}
.stat-card {
  background: var(--glass);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm);
  padding: 18px;
  transition: background 0.2s;
  cursor: pointer;
  user-select: none;
}
.stat-card:hover { background: var(--glass-hover); }
.stat-card:active { opacity: 0.85; }
.stat-label { font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-dim); margin-bottom: 10px; }
.stat-value { font-family: 'DM Serif Display', serif; font-size: 28px; color: var(--text); line-height: 1; margin-bottom: 4px; }
.stat-sub { font-size: 11px; color: var(--text-dim); }
.stat-green { color: var(--green); }
.stat-yellow { color: var(--yellow); }
.stat-blue { color: var(--blue); }
.stat-accent { color: var(--accent); }

/* ── TABLE ── */
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
  font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase;
  color: var(--text-dim); font-weight: 600; text-align: left;
  padding: 8px 12px; border-bottom: 1px solid var(--glass-border);
}
.data-table td {
  padding: 12px; font-size: 13px; color: var(--text-mid);
  border-bottom: 1px solid rgba(255,255,255,0.04);
}
.data-table tr:hover td { background: var(--glass-hover); }
.data-table td:first-child { font-weight: 500; color: var(--text); }

/* ── PILLS ── */
.pill {
  display: inline-block; font-size: 10px; font-weight: 700;
  letter-spacing: 0.07em; text-transform: uppercase;
  padding: 2px 9px; border-radius: 20px;
}
.pill-notice  { background:var(--blue-dim); color:var(--blue); border:1px solid rgba(122,180,240,0.25); }
.pill-urgent  { background:rgba(220,80,80,0.13); color:#f08080; border:1px solid rgba(220,80,80,0.25); }
.pill-event   { background:var(--green-dim); color:var(--green); border:1px solid rgba(130,201,138,0.25); }
.pill-pending { background:var(--yellow-dim); color:var(--yellow); border:1px solid rgba(240,192,96,0.25); }
.pill-progress{ background:var(--blue-dim); color:var(--blue); border:1px solid rgba(122,180,240,0.25); }
.pill-resolved{ background:var(--green-dim); color:var(--green); border:1px solid rgba(130,201,138,0.25); }
.pill-active  { background:var(--green-dim); color:var(--green); border:1px solid rgba(130,201,138,0.25); }
.pill-inactive{ background:rgba(220,80,80,0.13); color:#f08080; border:1px solid rgba(220,80,80,0.25); }

/* ── BUTTONS ── */
.btn {
  padding: 9px 18px;
  background: var(--accent-dim);
  border: 1px solid var(--accent-border);
  color: var(--accent);
  border-radius: var(--radius-sm);
  font-family: 'DM Sans', sans-serif;
  font-size: 13px; font-weight: 500;
  cursor: pointer;
  transition: background 0.2s, transform 0.15s;
  white-space: nowrap;
}
.btn:hover { background: rgba(232,168,124,0.26); transform: translateY(-1px); }
.btn:active { transform: scale(0.98); }
.btn-sm { padding: 6px 13px; font-size: 12px; }
.btn-danger { background:rgba(220,80,80,0.13); border-color:rgba(220,80,80,0.25); color:#f08080; }
.btn-blue { background:var(--blue-dim); border-color:rgba(122,180,240,0.25); color:var(--blue); }
.btn-green { background:var(--green-dim); border-color:rgba(130,201,138,0.25); color:var(--green); }
.btn-full { width:100%; }

/* ── FORMS ── */
.f-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px; }
.f-field { display:flex; flex-direction:column; gap:5px; flex:1; min-width:140px; }
.f-label { font-size:11px; color:var(--text-dim); letter-spacing:0.04em; }
input[type=text],input[type=date],input[type=month],input[type=email],input[type=password],textarea,select {
  background: rgba(255,255,255,0.07);
  border: 1px solid var(--glass-border);
  color: var(--text); padding: 9px 13px;
  border-radius: var(--radius-sm);
  font-family: 'DM Sans', sans-serif;
  font-size: 13px; outline: none; width: 100%;
  transition: border-color 0.2s, background 0.2s;
}
input:focus, textarea:focus, select:focus {
  background: rgba(255,255,255,0.1);
  border-color: rgba(232,168,124,0.4);
}
input::placeholder, textarea::placeholder { color: var(--text-dim); }
textarea { resize: vertical; min-height: 88px; line-height: 1.55; }
select { cursor: pointer; }
select option { background: #1a120d; color: var(--text); }

/* ── GRID HELPERS ── */
.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media(max-width:700px){ .grid-2{ grid-template-columns:1fr; } }

/* ── PROGRESS BAR ── */
.progress-wrap { background:rgba(255,255,255,0.08); border-radius:4px; height:6px; margin-top:6px; overflow:hidden; }
.progress-bar { height:100%; border-radius:4px; transition:width 0.6s ease; }

/* ── EMPTY STATE ── */
.empty-state { text-align:center; padding:36px 20px; color:var(--text-dim); font-size:13px; line-height:1.7; }
.empty-icon { font-size:30px; margin-bottom:8px; opacity:0.5; }
@keyframes pulse-critical {
  0%,100% { box-shadow: 0 0 0 0 rgba(255,64,64,0); }
  50%      { box-shadow: 0 0 12px 5px rgba(255,64,64,0.35); }
}
.issue-critical-row { border-left:3px solid #ff4040!important; animation:pulse-critical 2s ease-in-out infinite; }
/* ── EMERGENCY ALERT BANNER ── */
#emergencyAlert {
  display:none; position:fixed; top:0; left:0; right:0; z-index:9999;
  background:linear-gradient(90deg,#7a0000,#cc1400,#7a0000);
  background-size:200% 100%; animation:emergencyScroll 3s linear infinite;
  color:#fff; padding:12px 16px; text-align:center; font-size:14px; font-weight:700;
  box-shadow:0 2px 16px rgba(200,20,20,0.6);
}
@keyframes emergencyScroll { 0%{background-position:0 0} 100%{background-position:200% 0} }

/* ── MODAL ── */
.modal-overlay {
  display:none; position:fixed; inset:0;
  background:rgba(0,0,0,0.75); z-index:100;
  align-items:center; justify-content:center; padding:20px;
}
.modal-overlay.open { display:flex; }
.modal-box {
  background: linear-gradient(160deg, #12101a, #1a120d);
  border:1px solid var(--glass-border);
  border-radius:var(--radius);
  padding:28px;
  max-width:520px; width:100%;
  max-height:90vh; overflow-y:auto;
  animation:fadeUp 0.25s ease both;
}
.modal-box.wide { max-width: 760px; }
.modal-title {
  font-family:'DM Serif Display',serif;
  font-size:18px; margin-bottom:20px;
  color:var(--text);
}
.modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }
.modal-close-btn {
  background: var(--glass); border: 1px solid var(--glass-border);
  color: var(--text-mid); border-radius: var(--radius-sm);
  font-size: 13px; padding: 6px 13px; cursor: pointer;
  font-family: 'DM Sans', sans-serif;
}

/* ── TOAST ── */
#toast {
  position:fixed; bottom:30px; left:50%;
  transform:translateX(-50%) translateY(20px);
  background:rgba(18,16,26,0.96); border:1px solid var(--glass-border);
  backdrop-filter:blur(12px); color:var(--text); font-size:13px;
  padding:10px 22px; border-radius:20px;
  opacity:0; pointer-events:none;
  transition:opacity 0.25s, transform 0.25s; z-index:999;
}
#toast.show { opacity:1; transform:translateX(-50%) translateY(0); }

/* ── RESIDENTS PANEL ── */
.resident-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(240px,1fr)); gap:12px; }
.resident-card {
  background: var(--glass-hover);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm);
  padding: 16px;
  transition: background 0.2s, transform 0.2s;
  cursor: pointer;
}
.resident-card:hover { background: rgba(255,255,255,0.14); transform: translateY(-2px); }
.resident-card-top { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
.r-avatar { width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:600; color:#1a100a; flex-shrink:0; }
.r-name { font-size:14px; font-weight:500; margin-bottom:2px; }
.r-block { font-size:11px; color:var(--text-dim); }
.r-meta { display:flex; gap:6px; flex-wrap:wrap; }
.ta-r1 { background: rgba(232,168,124,0.25); color:var(--accent); }
.ta-r2 { background: rgba(122,180,240,0.22); color:var(--blue); }
.ta-r3 { background: rgba(130,201,138,0.22); color:var(--green); }
.ta-r4 { background: rgba(185,154,245,0.22); color:var(--purple); }
.ta-r5 { background: rgba(240,192,96,0.22); color:var(--yellow); }

/* ── DELINQUENTS (YT cards) ── */
.res-content {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
}
.res-card {
  flex: 1 1 210px;
  max-width: 250px;
  min-width: 180px;
  border-radius: var(--radius);
  background: var(--glass-hover);
  border: 1px solid var(--glass-border);
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s, background 0.2s;
}
.res-card:hover { transform: translateY(-4px); background: rgba(255,255,255,0.13); }
.res-thumb { width:100%; height:130px; background:#1a1a2a; }
.res-thumb img { width:100%; height:100%; object-fit:cover; }
.res-info { padding: 12px 14px; }
.res-info h4 { font-size:13px; font-weight:500; margin-bottom:4px; }
.res-reason { font-size:12px; color:#f08080; margin-bottom:3px; }
.res-reason.ok { color: var(--green); }
.res-reason.warning { color: var(--yellow); }
.res-location { font-size:11px; color:var(--text-dim); }

/* ── ISSUE ── */
.issue-item {
  background: rgba(255,255,255,0.04); border:1px solid var(--glass-border);
  border-radius:var(--radius-sm); padding:16px; margin-bottom:10px;
  transition: background 0.2s;
}
.issue-item:hover { background:rgba(255,255,255,0.07); }
.issue-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:6px; }
.issue-title { font-size:14px; font-weight:500; }
.issue-body { font-size:13px; color:var(--text-mid); line-height:1.6; margin-bottom:10px; }
.issue-meta { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.meta-txt { font-size:11px; color:var(--text-dim); }
.issue-actions { display:flex; gap:8px; margin-top:12px; flex-wrap:wrap; }
.priority-high { color:#f08080; font-size:11px; }
.priority-medium { color:var(--yellow); font-size:11px; }
.priority-low { color:var(--green); font-size:11px; }

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

/* ── PAYMENT ── */
.payment-row {
  display:flex; align-items:center; padding:12px;
  border-bottom:1px solid rgba(255,255,255,0.04);
  gap:14px; transition: background 0.2s;
}
.payment-row:hover { background:var(--glass-hover); border-radius:var(--radius-xs); }
.pay-household { flex:1; min-width:0; }
.pay-name { font-size:13px; font-weight:500; }
.pay-block { font-size:11px; color:var(--text-dim); }
.pay-amount { font-size:15px; font-weight:600; font-family:'DM Serif Display',serif; }
.pay-amount.paid { color:var(--green); }
.pay-amount.unpaid { color:#f08080; }
.pay-amount.partial { color:var(--yellow); }

/* ── MESSAGING ── */
.msg-layout { display:flex; height:520px; border:1px solid var(--glass-border); border-radius:var(--radius); overflow:hidden; }
.msg-sidebar {
  width:220px; flex-shrink:0;
  background: #FAF7F2;
  border-right:1px solid var(--glass-border);
  display:flex; flex-direction:column;
}
.msg-sidebar-head { padding:14px 16px 10px; border-bottom:1px solid var(--glass-border); font-size:10px; letter-spacing:.1em; text-transform:uppercase; color:var(--text-dim); }
.msg-search-wrap { padding:8px 10px; border-bottom:1px solid var(--glass-border); }
.msg-search { font-size:12px !important; padding:7px 10px !important; }
.msg-threads { flex:1; overflow-y:auto; padding:8px; scrollbar-width:thin; }
.thread-item { display:flex; align-items:center; gap:9px; padding:9px 10px; border-radius:var(--radius-xs); cursor:pointer; border:1px solid transparent; margin-bottom:3px; transition:background .15s; }
.thread-item:hover { background:var(--glass-hover); }
.thread-item.active { background:var(--accent-dim); border-color:var(--accent-border); }
.thread-del-btn { display:none; background:none; border:none; cursor:pointer; font-size:13px; padding:2px 5px; color:var(--text-dim); border-radius:4px; flex-shrink:0; line-height:1; }
.thread-del-btn:hover { color:#f08080; }
.thread-item:hover .thread-del-btn { display:block; }
.thread-avatar { width:34px; height:34px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; }
.thread-info { flex:1; min-width:0; }
.thread-name { font-size:12px; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.thread-preview { font-size:11px; color:var(--text-dim); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.thread-unread { min-width:18px; height:18px; border-radius:9px; background:var(--accent); color:#1a100a; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; padding:0 4px; flex-shrink:0; }
.msg-main { flex:1; display:flex; flex-direction:column; min-width:0; }
.msg-header { padding:12px 16px; display:flex; align-items:center; gap:10px; background: #FFFFFF; border-bottom:1px solid var(--glass-border); }
.msg-header-avatar { width:34px; height:34px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; }
.msg-header-name { font-size:14px; font-weight:500; }
.msg-header-sub { font-size:11px; color:var(--green); display:flex; align-items:center; gap:4px; }
.msg-header-sub::before { content:''; width:6px; height:6px; border-radius:50%; background:var(--green); display:inline-block; }
.msg-body { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:10px; scrollbar-width:thin; scrollbar-color:rgba(0,0,0,.15) transparent; background: #FAF6F0; }
.msg-bubble-wrap { display:flex; align-items:flex-end; gap:8px; }
.msg-bubble-wrap.mine { flex-direction:row-reverse; }
.bubble-avatar { width:26px; height:26px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; }
.bubble { max-width:68%; padding:10px 14px; border-radius:14px; font-size:13px; line-height:1.55; }
.from-admin { background: #FFFFFF; border:1px solid var(--glass-border); border-bottom-left-radius:4px; }
.from-mine { background:var(--accent-dim); border:1px solid var(--accent-border); border-bottom-right-radius:4px; }
.bubble-time { font-size:10px; color:var(--text-dim); margin-top:3px; display:block; }
.msg-bubble-wrap.mine .bubble-time { text-align:right; }
.date-divider { display:flex; align-items:center; gap:10px; font-size:10px; color:var(--text-dim); letter-spacing:.06em; text-transform:uppercase; }
.date-divider::before,.date-divider::after { content:''; flex:1; height:1px; background:var(--glass-border); }
.msg-compose { padding:12px; display:flex; gap:10px; align-items:flex-end; border-top:1px solid var(--glass-border); background: #FFFFFF; }
.msg-compose textarea { flex:1; min-height:40px; max-height:100px; resize:none; font-size:13px; }
.send-btn { width:40px; height:40px; border-radius:var(--radius-sm); background:var(--accent-dim); border:1px solid var(--accent-border); color:var(--accent); font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:background .2s; }
.send-btn:hover { background:rgba(232,168,124,0.28); }

/* ── MANAGE USERS ── */
.manage-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:14px; margin-bottom:20px; }
.manage-card {
  padding: 22px 18px;
  border-radius: var(--radius);
  background: var(--glass);
  border: 1px solid var(--glass-border);
  cursor: pointer;
  transition: background 0.2s, transform 0.2s, border-color 0.2s;
}
.manage-card:hover { background: var(--glass-hover); transform: translateY(-3px); border-color: var(--accent-border); }
.manage-card-icon { font-size: 24px; margin-bottom: 10px; }
.manage-card h3 { font-size:14px; font-weight:500; margin-bottom:5px; }
.manage-card p { font-size:11px; color:var(--text-dim); }

/* ── TABLE PANEL ── */
.table-panel { display:none; }
.table-panel.visible { display:block; }
.panel-table { width:100%; border-collapse:collapse; }
.panel-table th { font-size:10px; letter-spacing:.08em; text-transform:uppercase; color:var(--text-dim); font-weight:600; text-align:left; padding:8px 12px; border-bottom:1px solid var(--glass-border); }
.panel-table td { padding:11px 12px; font-size:13px; color:var(--text-mid); border-bottom:1px solid rgba(255,255,255,.04); }
.panel-table tr:hover td { background:var(--glass-hover); }
.panel-table td:first-child { color:var(--text); font-weight:500; }

/* ── ANALYTICS GRAPH GRID ── */
.graph-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:14px; }
.graph-grid-wide { display:grid; grid-template-columns:2fr 1fr; gap:14px; }
.graph-placeholder { height:280px; display:flex; align-items:center; justify-content:center; flex-direction:column; gap:10px; opacity:.35; font-size:13px; }
.graph-placeholder .g-icon { font-size:32px; }

/* ── FINANCE / RECEIPT ── */
.finance-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:16px; }

/* Receipt Container — horizontal scrollable row */
.receipt-container {
  display: flex;
  gap: 18px;
  overflow-x: auto;
  padding-bottom: 10px;
  scrollbar-width: thin;
  scrollbar-color: rgba(255,255,255,0.15) transparent;
  margin-bottom: 16px;
}

/* Individual receipt card — the "receipt-like layout" */
.receipt {
  flex: 0 0 360px;
  background: rgba(255,255,255,0.055);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius);
  padding: 28px 24px;
  backdrop-filter: blur(18px);
  min-height: 420px;
  position: relative;
  overflow: hidden;
  transition: transform 0.2s;
}
.receipt:hover { transform: translateY(-3px); }

/* Decorative top stripe */
.receipt::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--accent), transparent);
}

/* Perforated line between header and body */
.receipt-perforate {
  border: none;
  border-top: 1px dashed rgba(255,255,255,0.18);
  margin: 14px 0;
}

.receipt-title {
  font-family: 'DM Serif Display', serif;
  font-size: 17px;
  text-align: center;
  color: var(--text);
  margin-bottom: 3px;
}
.receipt-subtitle {
  text-align: center;
  font-size: 11px;
  color: var(--text-dim);
  letter-spacing: 0.06em;
  text-transform: uppercase;
}
.receipt-section-label {
  font-size: 9px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--accent);
  margin: 12px 0 6px;
  font-weight: 600;
}
.receipt-line {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  padding: 5px 0;
  font-size: 13px;
  color: var(--text-mid);
  border-bottom: 1px solid rgba(255,255,255,0.04);
}
.receipt-line:last-child { border-bottom: none; }
.receipt-line .r-label { flex: 1; }
.receipt-line .r-val { font-weight: 500; color: var(--text); font-family: 'DM Serif Display', serif; font-size: 14px; }
.receipt-line .r-val.positive { color: var(--green); }
.receipt-line .r-val.negative { color: #f08080; }

.receipt-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 14px;
  padding: 12px 14px;
  background: rgba(130,201,138,0.10);
  border: 1px solid rgba(130,201,138,0.22);
  border-radius: var(--radius-sm);
}
.receipt-total .rt-label { font-size:12px; color:var(--text-dim); text-transform:uppercase; letter-spacing:.06em; }
.receipt-total .rt-val { font-family:'DM Serif Display',serif; font-size:22px; color:var(--green); }

.receipt-add-btn {
  flex: 0 0 180px;
  min-height: 420px;
  border: 2px dashed rgba(255,255,255,0.12);
  border-radius: var(--radius);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  cursor: pointer;
  color: var(--text-dim);
  font-size: 13px;
  transition: border-color 0.2s, color 0.2s, background 0.2s;
  background: transparent;
  font-family: 'DM Sans', sans-serif;
}
.receipt-add-btn:hover { border-color: var(--accent-border); color: var(--accent); background: var(--accent-dim); }
.receipt-add-icon { font-size: 28px; }

/* Finance receipt modal (split layout) */
.receipt-modal-split { display:flex; gap:20px; }
.receipt-modal-split .preview { flex:1; }
.receipt-modal-split .form-col { flex:1; display:flex; flex-direction:column; gap:14px; }
.receipt-modal-split .form-col .form-block {
  background: rgba(255,255,255,0.04);
  border:1px solid var(--glass-border);
  border-radius:var(--radius-sm);
  padding:14px;
}
.receipt-modal-split .form-col .form-block h4 { font-size:12px; color:var(--text-dim); text-transform:uppercase; letter-spacing:.06em; margin-bottom:10px; }
@media(max-width:700px){ .receipt-modal-split { flex-direction:column; } }

/* Upload section */
.upload-zone {
  border: 2px dashed rgba(255,255,255,0.15);
  border-radius: var(--radius-sm);
  padding: 22px;
  text-align: center;
  color: var(--text-dim);
  font-size:13px;
  transition: border-color 0.2s, background 0.2s;
}
.upload-zone:hover { border-color: var(--accent-border); background: var(--accent-dim); color: var(--accent); }

/* ── MAPPING ── */
#map-container {
  width:100%; height:520px;
  border-radius:var(--radius);
  overflow:hidden;
  border:1px solid var(--glass-border);
  background:#1a2333;
  z-index:1;
}
.map-toolbar { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.map-toggle {
  display:flex; align-items:center; gap:5px;
  font-size:12px; color:var(--text-mid); cursor:pointer;
  padding:4px 10px; border-radius:6px;
  background:var(--glass); border:1px solid var(--glass-border);
  user-select:none;
}
.map-toggle:hover { background:var(--glass-hover); }
.map-toggle input { accent-color:var(--accent); cursor:pointer; }
.map-legend { display:flex; gap:14px; flex-wrap:wrap; font-size:12px; color:var(--text-mid); align-items:center; }
.map-legend span { display:flex; align-items:center; gap:5px; }
.map-dot { width:11px; height:11px; border-radius:50%; flex-shrink:0; }
#map-add-banner {
  display:none; background:rgba(232,168,124,0.12);
  border:1px solid var(--accent-border); border-radius:8px;
  padding:10px 14px; margin-top:12px;
  font-size:13px; color:var(--accent);
  align-items:center; gap:10px;
}

/* ── MEMBERS ── */
.members-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px,1fr)); gap:12px; }
.member-card {
  background:var(--glass); border:1px solid var(--glass-border);
  border-radius:var(--radius-sm); padding:16px;
  display:flex; align-items:center; gap:14px;
  transition:background 0.2s;
}
.member-card:hover { background:var(--glass-hover); }
.member-avatar { width:44px; height:44px; border-radius:50%; object-fit:cover; background:#222; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:700; }
.member-info h4 { font-size:13px; font-weight:500; margin-bottom:3px; }
.member-info p { font-size:11px; color:var(--text-dim); }
.member-info .m-type { font-size:10px; color:var(--accent); text-transform:uppercase; letter-spacing:.06em; margin-top:3px; }

/* ── RESPONSIVE ── */
@media(max-width:991px){
  .sidebar {
    position: fixed;
    left: -100%;
    top: 0;
    height: 100%;
    z-index: 200;
    transition: left 0.3s ease;
    background: rgba(0,0,0,0.72);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    box-shadow: 4px 0 32px rgba(0,0,0,0.6);
  }
  .sidebar.open { left: 0; }
  .sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 199;
    backdrop-filter: blur(2px);
  }
  .sidebar-overlay.show { display: block; }
  .menu-toggle { display: block; }
  .main { margin-left: 0; }
  .topbar { position: sticky; top: 0; z-index: 100; }
  .topbar-title { font-size: 14px; }
  .topbar-actions .topbar-btn:not(.primary) { display: none; }
  .content { padding: 16px 14px; }
  .graph-grid { grid-template-columns: 1fr; }
  .graph-grid-wide { grid-template-columns: 1fr; }
  .finance-stats { grid-template-columns: 1fr 1fr; }
  .stat-grid { grid-template-columns: 1fr 1fr; }
  .grid-2 { grid-template-columns: 1fr; }
  .msg-layout { flex-direction: column; height: auto; }
  .msg-sidebar { width: 100%; height: auto; max-height: 200px; }
  .msg-body { min-height: 260px; max-height: 320px; }
  #panel-dashboard > div { grid-template-columns: 1fr !important; }
}

/* SIDEBAR (PREMIUM VERTICAL STYLE) */
.sidebar {
    width: 250px;
    padding: 30px 24px;
    background: #FFFFFF;
    border-right: 1px solid #E6DFD5;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    z-index: 100;
}
.sidebar li a, .panel .cards ul li a, .cards-main a {
   text-decoration: none;
   color: inherit;
}
.sidebar ul, .panel .cards ul {
    list-style: none;
}
.sidebar li, .panel .cards li {
    padding: 12px 16px;
    margin-bottom: 8px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #7D7975;
    font-size: 14px;
    font-weight: 500;
    list-style: none;
}
.sidebar li:hover {
    background: #FAF4E7;
    color: #1C1A17;
}
.sidebar .active, .sidebar li.active {
    background: #1C1A17;
    color: #FFFFFF !important;
    box-shadow: 0 4px 12px rgba(28,26,23,0.1);
}

/* MAIN CONTENT AREA */
.main {
    flex: 1;
    margin-left: 250px;
    padding: 40px;
    min-height: 100vh;
}

/* BENTO GRID SYSTEM */
.bento-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 24px;
    margin-top: 24px;
}
.bento-col-12 { grid-column: span 12; }
.bento-col-8  { grid-column: span 8; }
.bento-col-6  { grid-column: span 6; }
.bento-col-4  { grid-column: span 4; }
.bento-col-3  { grid-column: span 3; }
.bento-metrics-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

@media(max-width: 991px) {
    .bento-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .bento-col-12, .bento-col-8, .bento-col-6, .bento-col-4, .bento-col-3 {
        grid-column: span 12;
    }
    .bento-metrics-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}
</style>
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
 <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo">
      <img src="{{ asset('emblem_1.svg') }}" alt="SubSync">
    </div>
    <div class="brand-sub">Terra Nova · Admin Portal</div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Overview</div>
    <div class="nav-item active" onclick="nav('dashboard',this)"><span class="nav-icon">📊</span> Dashboard</div>
    <div class="nav-item" onclick="nav('analytics',this)"><span class="nav-icon">📈</span> Analytics</div>

    <div class="nav-section-label">Directory</div>
    <div class="nav-item" onclick="nav('residents',this)"><span class="nav-icon">👥</span> Residents <span class="nav-badge" id="pendingBadge" style="display:none;background:#f59e0b;">0</span></div>
    <div class="nav-item" onclick="nav('members',this)"><span class="nav-icon">👤</span> Members</div>
    <div class="nav-item" onclick="nav('officers',this)"><span class="nav-icon">🛡️</span> Officers</div>

    <div class="nav-section-label">Operations</div>
    <div class="nav-item" onclick="nav('issues',this)"><span class="nav-icon">🔧</span> Issue Reports <span class="nav-badge" id="issueBadge">0</span></div>
    <div class="nav-item" onclick="nav('delinquents',this)"><span class="nav-icon">⚠️</span> Delinquents <span class="nav-badge" id="delinBadge">0</span></div>
    <div class="nav-item" onclick="nav('mapping',this)"><span class="nav-icon">🗺️</span> Subdivision Map</div>
    <div class="nav-item" onclick="nav('announcements',this)"><span class="nav-icon">📢</span> Announcements</div>

    <div class="nav-section-label">Finance</div>
    <div class="nav-item" onclick="nav('finance',this)"><span class="nav-icon">💳</span> Accounts &amp; Dues</div>
    <div class="nav-item" onclick="nav('payments',this)"><span class="nav-icon">₱</span> Payments</div>
    <div class="nav-item" onclick="nav('reports',this)"><span class="nav-icon">📁</span> Reports <span class="nav-badge" id="reportsBadge" style="display:none;">0</span></div>

    <div class="nav-section-label">Communication &amp; System</div>
    <div class="nav-item" onclick="nav('messages',this)"><span class="nav-icon">💬</span> Messages <span class="nav-badge" id="msgBadge">5</span></div>
    <div class="nav-item" onclick="nav('recommendations',this)"><span class="nav-icon">💡</span> Recommendations</div>
    <div class="nav-item" onclick="nav('manageusers',this)"><span class="nav-icon">⚙️</span> Manage Users</div>
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
    &nbsp;<button onclick="dismissEmergency()" style="background:transparent;border:none;color:rgba(255,255,255,0.7);font-size:16px;cursor:pointer;line-height:1;">✕</button>
  </div>
    <div style="display:flex;align-items:center;gap:12px;">
   <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()">☰</button>
      <div class="topbar-title" id="topbarTitle">Dashboard Overview</div>
    </div>
    <div class="topbar-actions">
      <button class="topbar-btn" onclick="showToast('🔔 No new system alerts.')">🔔 Alerts</button>
      <button class="topbar-btn primary" onclick="openModal('ann')">+ New Announcement</button>
      <form id="logoutForm" action="/logout" method="POST" style="display:none;">@csrf</form>
      <button class="topbar-btn" onclick="confirmLogout()" style="color:#f08080;border-color:rgba(240,128,128,0.3);">⏻ Log Out</button>
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
        <button class="btn btn-sm" style="background:#f59e0b;border-color:#f59e0b;" onclick="nav('residents',null)">Review Now →</button>
      </div>

      <!-- BENTO GRID -->
      <div class="bento-grid">
        <!-- Bento A: Greeting Banner (Span 12) -->
        <div class="card bento-col-12" style="background: linear-gradient(135deg, #FAF4E7, #FAF6F0); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
          <div>
            <h1 style="font-family:'DM Serif Display',serif; font-size:32px; font-weight:400; color:#2B2927; margin-bottom:6px;">Subdivision Control Tower</h1>
            <p style="font-size:13px; color:#7D7975;">Manage directories, financials, issues, and communications from a unified command deck.</p>
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
            <div class="card-title" style="justify-content:center;">Open Concerns</div>
            <div style="font-size:36px; font-weight:700; color:#f08080; margin: 10px 0 6px;" id="statIssues">—</div>
            <div style="font-size:11px; color:#7D7975;">requires resolution</div>
          </div>

          <div class="card" onclick="nav('delinquents',null)" style="cursor:pointer; text-align: center;">
            <div class="card-title" style="justify-content:center;">Delinquents</div>
            <div style="font-size:36px; font-weight:700; color:#2B2927; margin: 10px 0 6px;" id="statDelinq">—</div>
            <div style="font-size:11px; color:#7D7975;">outstanding balance</div>
          </div>
        </div>

        <!-- Bento C: Balance & Approvals (Span 4) -->
        <div class="bento-col-4" style="display: flex; flex-direction: column; gap: 24px;">
          <div class="card" onclick="nav('finance',null)" style="cursor:pointer; display: flex; align-items: center; justify-content: space-between; padding: 20px 24px;">
            <div>
              <div class="card-title" style="margin-bottom:4px;">Unpaid Dues</div>
              <div style="font-size:22px; font-weight:700; color:#2B2927;" id="statBalance">—</div>
            </div>
            <span style="font-size:24px;">💳</span>
          </div>

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

        <!-- Bento E: Collection Rate Stats (Span 4) -->
        <div class="card bento-col-4">
          <div class="card-title">Collection Rate</div>
          <div style="font-size:36px; font-weight:700; color:#2B2927; margin: 10px 0 6px;" id="statPayPct">—</div>
          <div style="font-size:11px; color:#7D7975; margin-bottom: 12px;" id="statPaySub">Dues summary</div>
          <div class="progress-wrap"><div class="progress-bar" id="statPayBar" style="width:0%;background:var(--accent);"></div></div>
          
          <div style="margin-top:20px; display:flex; flex-direction:column; gap:10px;">
            <div style="display:flex;justify-content:space-between;font-size:11px;">
              <span>Paid</span><span style="color:#2E6F40;font-weight:600;"><span id="payCountPaid">—</span> hh</span>
            </div>
            <div class="progress-wrap"><div class="progress-bar" id="payBarPaid" style="width:0%;background:rgba(46,111,64,0.75);"></div></div>
            
            <div style="display:flex;justify-content:space-between;font-size:11px;">
              <span>Partial</span><span style="color:#A07D53;font-weight:600;"><span id="payCountPartial">—</span> hh</span>
            </div>
            <div class="progress-wrap"><div class="progress-bar" id="payBarPartial" style="width:0%;background:rgba(160,125,83,0.75);"></div></div>
            
            <div style="display:flex;justify-content:space-between;font-size:11px;">
              <span>Unpaid</span><span style="color:var(--danger);font-weight:600;"><span id="payCountUnpaid">—</span> hh</span>
            </div>
            <div class="progress-wrap"><div class="progress-bar" id="payBarUnpaid" style="width:0%;background:var(--danger);"></div></div>
          </div>
          <button class="btn btn-sm btn-full" style="margin-top:20px;" onclick="nav('payments',null)">View Details</button>
        </div>

        <!-- Bento F: Issue Reports (Span 12) -->
        <div class="card bento-col-12">
          <div class="card-title">Recent Issue Reports</div>
          <div id="dashIssueList"></div>
          <button class="btn btn-sm" style="margin-top:16px;" onclick="nav('issues',null)">View All Issues</button>
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
          <div id="pendingGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;"></div>
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

    <!-- ══════════ DELINQUENTS ══════════ -->
    <div class="panel" id="panel-delinquents">
      <div class="card" style="margin-bottom:16px;">
        <div class="card-title">Flagged Households</div>
        <div style="font-size:13px;color:var(--text-dim);">Households with outstanding issues, unpaid bills, or violations. Click a card for details.</div>
      </div>
      <div class="res-content" id="delinquentGrid"></div>
    </div>

    <!-- ══════════ FINANCE ══════════ -->
    <div class="panel" id="panel-finance">
      <!-- Summary stats -->
      <div class="finance-stats">
        <div class="stat-card">
          <div class="stat-label">Total Collected</div>
          <div class="stat-value stat-green" id="finCollected">₱0</div>
          <div class="stat-sub">All time</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Dues</div>
          <div class="stat-value" style="color:#f08080;" id="finDues">₱0</div>
          <div class="stat-sub">All time</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Outstanding Balance</div>
          <div class="stat-value stat-accent" id="finBalance">₱0</div>
          <div class="stat-sub">Net owed</div>
        </div>
      </div>

      <!-- Upload + actions row -->
      <div class="card" style="margin-bottom:16px;">
        <div class="card-title">Upload & Import</div>
        <div style="display:flex;gap:14px;flex-wrap:wrap;align-items:center;">
          <div class="upload-zone" id="excelDropZone" style="flex:1;min-width:220px;position:relative;">
            <div style="font-size:22px;margin-bottom:6px;pointer-events:none;">📂</div>
            <div style="pointer-events:none;">Drop Excel file here or <label for="excelFile" style="color:var(--accent);cursor:pointer;text-decoration:underline;pointer-events:auto;">browse</label></div>
            <input type="file" id="excelFile" accept=".xlsx,.xls" style="display:none;" onchange="uploadExcel()">
            <div id="excelFileName" style="font-size:11px;margin-top:6px;color:var(--green);pointer-events:none;"></div>
          </div>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <button class="btn" onclick="openModal('addReceipt')">+ Add Receipt Manually</button>
            <button class="btn btn-green" onclick="openAddRecordModal()">+ Add Financial Record</button>
          </div>
        </div>
      </div>

      <!-- Receipt scrollable container -->
      <div style="margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-dim);">Financial Receipts</div>
        <button class="btn btn-sm btn-green" onclick="exportReceiptsCSV()">&#8595; Export</button>
      </div>
      <div class="receipt-container" id="receiptContainer">
        <!-- Receipts rendered by JS -->
        <button class="receipt-add-btn" onclick="openModal('addReceipt')">
          <span class="receipt-add-icon">+</span>
          <span>Add Receipt</span>
        </button>
      </div>

      <!-- Financial Records Table -->
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
          <div class="card-title" style="margin-bottom:0;">Financial Records</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <input type="text" id="finSearch" placeholder="Search resident…" style="width:150px;" oninput="filterFinRecords()">
            <select id="finTypeFilter" onchange="filterFinRecords()">
              <option value="">All Types</option>
              <option>Due</option><option>Payment</option><option>Penalty</option><option>Adjustment</option>
            </select>
            <button class="btn btn-sm btn-green" onclick="exportFinRecordsCSV()">⬇ Export CSV</button>
          </div>
        </div>
        <table class="panel-table" id="excelTable">
          <thead>
            <tr>
              <th>Resident</th><th>Type</th><th>Description</th><th>Amount</th><th>Date</th><th></th>
            </tr>
          </thead>
          <tbody><tr><td colspan="6" style="text-align:center;color:var(--text-dim);padding:20px;">No financial records yet.</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- ══════════ PAYMENTS ══════════ -->
    <div class="panel" id="panel-payments">
      <div class="stat-grid" style="margin-bottom:16px;">
        <div class="stat-card">
          <div class="stat-label">Total Collected</div>
          <div class="stat-value stat-green" id="payCollected">₱0</div>
          <div class="stat-sub">All time</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Dues</div>
          <div class="stat-value" style="color:#f08080;" id="payDues">₱0</div>
          <div class="stat-sub">All time</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Outstanding Balance</div>
          <div class="stat-value stat-accent" id="payBalance">₱0</div>
          <div class="stat-sub">Net owed</div>
        </div>
      </div>
      <div class="card">
        <div class="card-title">Payment Records</div>
        <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
          <input type="text" id="paySearch" placeholder="Search household…" style="flex:1;min-width:160px;" oninput="filterPayments()">
          <select id="payStatusFilter" style="width:auto;" onchange="filterPayments()">
            <option value="">All Status</option><option>Paid</option><option>Partial</option><option>Unpaid</option>
          </select>
          <button class="btn btn-sm btn-green" onclick="exportPaymentsCSV()">&#8595; Export Excel</button>
        </div>
        <div id="paymentList"></div>
      </div>
    </div>

    <!-- ══════════ MESSAGES ══════════ -->
    <div class="panel" id="panel-messages">
      <div class="msg-layout">
        <div class="msg-sidebar">
          <div class="msg-sidebar-head" style="display:flex;align-items:center;justify-content:space-between;">Resident Conversations
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
              <div class="msg-header-sub">Online</div>
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
      <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
        <button class="btn" onclick="openModal('ann')">+ New Announcement</button>
      </div>
      <div id="annList"></div>
    </div>

    <!-- ══════════ ISSUES ══════════ -->
    <div class="panel" id="panel-issues">
      <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
        <select id="issueFilter" onchange="renderIssues()" style="width:auto;">
          <option value="">All Status</option>
          <option value="Pending">Pending</option>
          <option value="In Progress">In Progress</option>
          <option value="Resolved">Resolved</option>
        </select>
        <select id="issuePriFilter" onchange="renderIssues()" style="width:auto;">
          <option value="">All Priority</option>
          <option value="High">High</option>
          <option value="Medium">Medium</option>
          <option value="Low">Low</option>
        </select>
        <button class="btn btn-sm btn-green" style="margin-left:auto;" onclick="exportIssuesCSV()">&#8595; Export</button>
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
            <option value="membersPanel">Members</option>
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
        <thead><tr><th>Name</th><th>Role</th><th>Block</th><th>Status</th><th>Actions</th></tr></thead>
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
        <div class="stat-card"><div class="stat-label">Delinquents</div><div class="stat-value" style="color:#f08080;" id="analyticDelinq">—</div></div>
      </div>
      <div class="graph-grid">
        <div class="card">
          <div class="card-title">Collections</div>
          <div id="chart-collections" style="width:100%;min-height:180px;"></div>
        </div>
        <div class="card">
          <div class="card-title">Resident Status</div>
          <div id="chart-delinquents" style="width:100%;min-height:180px;display:flex;align-items:center;justify-content:center;"></div>
        </div>
        <div class="card">
          <div class="card-title">Issues by Category</div>
          <div id="chart-complaints" style="width:100%;min-height:180px;"></div>
        </div>
      </div>
      <div class="graph-grid-wide">
        <div class="card">
          <div class="card-title">Neighborhood Disturbance Heatmap</div>
          <div id="analytics-heat-map" style="height:220px;border-radius:8px;overflow:hidden;background:rgba(0,0,0,0.2);"></div>
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
          <span style="font-size:13px;font-weight:500;color:var(--text);">Terra Nova Subdivision</span>
          <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <label class="map-toggle"><input type="checkbox" id="layer-households" checked> Households</label>
            <label class="map-toggle"><input type="checkbox" id="layer-facilities" checked> Facilities</label>
            <label class="map-toggle"><input type="checkbox" id="layer-issues" checked> Issues</label>
            <label class="map-toggle"><input type="checkbox" id="layer-heatmap" checked> Heatmap</label>
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
          <span><span class="map-dot" style="background:#f08080;"></span>Household (Delinquent)</span>
          <span><span class="map-dot" style="background:#e05555;"></span>Issue (Pending)</span>
          <span><span class="map-dot" style="background:#f5a623;"></span>Issue (In Progress)</span>
          <span><span class="map-dot" style="background:#888;"></span>Issue (Resolved, ≤7d)</span>
          <span><span class="map-dot" style="background:linear-gradient(to right,#1a003e,#5c0099,#0044bb,#cc4400,#ff2200);border-radius:2px;"></span>Issue Frequency</span>
        </div>
      </div>

      <!-- Map -->
      <div id="map-container"></div>

      <!-- Add-facility mode banner -->
      <div id="map-add-banner">
        <span>📍 Click anywhere on the map to place the facility pin</span>
        <button class="btn btn-sm" style="margin-left:auto;" onclick="cancelAddFacilityMode()">Cancel</button>
      </div>

    </div>

    <!-- ══════════ REPORTS ══════════ -->
    <div class="panel" id="panel-reports">
      <div class="grid-2">
        <div class="card">
          <div class="card-title">Files from Officers</div>
          <div id="officerFiles"></div>
        </div>
        <div class="card">
          <div class="card-title">Generate Reports</div>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <button class="btn" onclick="reportMonthlySummary()">📊 Monthly Summary</button>
            <button class="btn btn-blue" onclick="reportPayments()">💳 Payment Report</button>
            <button class="btn btn-green" onclick="reportIssues()">🔧 Issue Report</button>
            <button class="btn" style="background:var(--purple-dim);border-color:rgba(185,154,245,0.25);color:var(--purple);" onclick="reportResidentDirectory()">👥 Resident Directory</button>
          </div>
        </div>
      </div>
    </div>

  </div><!-- end .content -->
</main>

<!-- ═══ MODALS ═══ -->

<!-- Announcement -->
<div class="modal-overlay" id="modal-ann">
  <div class="modal-box">
    <div class="modal-title">Post Announcement</div>
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
    <div class="f-row"><div class="f-field"><span class="f-label">Message</span><textarea id="annBody" placeholder="Write your announcement…"></textarea></div></div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('ann')">Cancel</button>
      <button class="btn btn-sm" onclick="postAnnouncement()">Post Announcement</button>
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
      <button class="btn btn-sm" onclick="addResident()">Add Resident</button>
    </div>
  </div>
</div>

<!-- Add Officer -->
<div class="modal-overlay" id="modal-addOfficer">
  <div class="modal-box">
    <div class="modal-title">Add Officer</div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Full Name</span><input type="text" id="newOfficerName" placeholder="Full name"></div>
      <div class="f-field"><span class="f-label">Role</span>
        <select id="newOfficerRole">
          <option>President</option><option>Vice President</option>
          <option>Secretary</option><option>Treasurer</option>
          <option>Block Representative</option><option>Security Officer</option>
        </select>
      </div>
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

<div class="modal-overlay" id="modal-addFinRecord">
  <div class="modal-box">
    <div class="modal-title">Add Financial Record</div>
    <div class="f-row">
      <div class="f-field" style="flex:2"><span class="f-label">Resident</span>
        <select id="recResident"><option value="">— Select Resident —</option></select>
      </div>
      <div class="f-field"><span class="f-label">Record Type</span>
        <select id="recType">
          <option>Due</option>
          <option>Payment</option>
          <option>Penalty</option>
          <option>Adjustment</option>
        </select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Amount (₱)</span><input type="number" id="recAmount" min="0" step="0.01" placeholder="0.00"></div>
      <div class="f-field"><span class="f-label">Date</span><input type="date" id="recDate"></div>
    </div>
    <div class="f-row">
      <div class="f-field" style="flex:1"><span class="f-label">Description</span><input type="text" id="recDesc" placeholder="e.g. Monthly due – June 2026"></div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('addFinRecord')">Cancel</button>
      <button class="btn btn-sm" onclick="saveFinancialRecord()">Save Record</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-addReceipt">
  <div class="modal-box wide">
    <div class="modal-title">Add Financial Receipt</div>
    <div class="receipt-modal-split">
      <!-- Live preview -->
      <div class="preview">
        <div class="receipt" id="previewReceipt" style="min-height:360px;">
          <div class="receipt-title">Financial Report</div>
          <div class="receipt-subtitle" id="p-month">Month</div>
          <hr class="receipt-perforate">
          <div class="receipt-section-label">Previous Balance</div>
          <div class="receipt-line"><span class="r-label">Carried Over</span><span class="r-val" id="p-previous">₱0</span></div>
          <div class="receipt-section-label">Collections</div>
          <div id="p-collections"><div class="receipt-line"><span class="r-label">—</span><span class="r-val">₱0</span></div></div>
          <div class="receipt-section-label">Expenses</div>
          <div id="p-expenses"><div class="receipt-line"><span class="r-label">—</span><span class="r-val negative">₱0</span></div></div>
          <hr class="receipt-perforate">
          <div class="receipt-total">
            <div class="rt-label">Net Savings</div>
            <div class="rt-val" id="p-savings">₱0</div>
          </div>
        </div>
      </div>
      <!-- Form -->
      <div class="form-col">
        <div class="form-block">
          <h4>Report Details</h4>
          <div class="f-row">
            <div class="f-field"><span class="f-label">Month</span><input type="month" id="r-month" oninput="updatePreview()"></div>
            <div class="f-field"><span class="f-label">Previous Balance (₱)</span><input type="text" id="r-prev" placeholder="0" oninput="updatePreview()"></div>
          </div>
        </div>
        <div class="form-block">
          <h4>Add Collection Line</h4>
          <div class="f-row">
            <div class="f-field"><span class="f-label">Description</span><input type="text" id="c-name" placeholder="e.g. Monthly Dues"></div>
            <div class="f-field"><span class="f-label">Amount (₱)</span><input type="text" id="c-amount" placeholder="0"></div>
          </div>
          <button class="btn btn-sm btn-green btn-full" style="margin-top:6px;" onclick="addReceiptLine('collection')">+ Add Collection</button>
        </div>
        <div class="form-block">
          <h4>Add Expense Line</h4>
          <div class="f-row">
            <div class="f-field"><span class="f-label">Description</span><input type="text" id="e-name" placeholder="e.g. Street Repair"></div>
            <div class="f-field"><span class="f-label">Amount (₱)</span><input type="text" id="e-amount" placeholder="0"></div>
          </div>
          <button class="btn btn-sm btn-danger btn-full" style="margin-top:6px;" onclick="addReceiptLine('expense')">+ Add Expense</button>
        </div>
      </div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('addReceipt')">Cancel</button>
      <button class="btn" onclick="saveReceipt()">Save Receipt</button>
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
        <select id="muHStatus"><option>Active</option><option>Inactive</option></select>
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
        <select id="editHStatus"><option>Active</option><option>Inactive</option></select>
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
        <select id="statusHNew"><option>Active</option><option>Inactive</option><option>Delinquent</option></select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Reason for Status Change</span>
        <select id="statusHReason">
          <option>Unpaid dues</option>
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
      <div class="f-field"><span class="f-label">Role / Position</span><input type="text" id="editOfficerRole" placeholder="e.g. Secretary"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Account Status</span>
        <select id="editOfficerStatus"><option>Active</option><option>Inactive</option></select>
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

<script>
/* ══════════════ UTILS ══════════════ */
function nowTime(){ return new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'}); }
function todayStr(){ return new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}); }
function fmt(n){ return '₱'+Number(n||0).toLocaleString(); }

function confirmLogout(){
  if(confirm('Are you sure you want to log out?'))
    document.getElementById('logoutForm').submit();
}

function showToast(msg){
  const t=document.getElementById('toast');
  t.textContent=msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),2800);
}
function openModal(id){
  if(id==='addMember'){
    // populate household dropdown
    const sel=document.getElementById('addMemHouse');
    sel.innerHTML='<option value="">— Select household —</option>';
    households.forEach(h=>{ const opt=document.createElement('option'); opt.value=h.id; opt.textContent=h.block_lot_number||h.block_lot||'House #'+h.id; sel.appendChild(opt); });
    document.getElementById('addMemName').value='';
    document.getElementById('addMemRel').value='';
    document.getElementById('addMemPhone').value='';
    document.getElementById('addMemMsg').textContent='';
  }
  document.getElementById('modal-'+id).classList.add('open');
}
function closeModal(id){ document.getElementById('modal-'+id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(o=>{
  o.addEventListener('click',e=>{ if(e.target===o) o.classList.remove('open'); });
});

/* ══════════════ NAVIGATION ══════════════ */
const panelTitles={
  dashboard:'Dashboard Overview',residents:'Resident Management',members:'Members',
  delinquents:'Delinquents',finance:'Finance',payments:'Payment Records',
  messages:'Resident Messaging',announcements:'Announcements',issues:'Issue Reports',
  manageusers:'Manage Users',officers:'Officers',recommendations:'Recommendations',analytics:'Analytics',
  mapping:'Maps · Terra Nova',reports:'Reports & Files'
};

function nav(name, el){
  if(window.innerWidth <= 991) closeSidebar();
  document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  const panel=document.getElementById('panel-'+name);
  if(panel) panel.classList.add('active');
  if(el) el.classList.add('active');
  else document.querySelectorAll('.nav-item').forEach(n=>{
    if(n.textContent.trim().toLowerCase().includes(name)) n.classList.add('active');
  });
  document.getElementById('topbarTitle').textContent = panelTitles[name]||name;
  if(name==='messages'){document.getElementById('msgBadge').style.display='none';loadThreads();}
  if(name==='issues') renderIssues();
  if(name==='members') renderMembers();
  if(name==='finance') loadReceipts();
  if(name==='analytics') setTimeout(renderAnalytics, 80);
  if(name==='reports') renderReports();
  if(name==='mapping') setTimeout(initMap, 80);
  if(name==='manageusers') loadManageData();
}

/* ══════════════ DATA ══════════════ */
/* ══════════════ DATA STORE ══════════════ */
let residents = [];
let households = [];
let announcements = [];
let issues = [];
let officers = [];
let recommendations = [];
let payments = [];
let membersData = [];
let delinquents = [];
let threads = {};
let currentThread = null;

const COLOR_CYCLE = ['ta-r1','ta-r2','ta-r3','ta-r4','ta-r5'];
function initials(name){ return (name||'?').split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase(); }
function colorFor(i){ return COLOR_CYCLE[i % COLOR_CYCLE.length]; }

/* ══════════════ API FETCH HELPERS ══════════════ */
async function apiGet(url){ const r=await fetch(url,{headers:{'Accept':'application/json'}}); return r.json(); }
async function apiPost(url,data){ const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(data)}); return r.json(); }
async function apiPut(url,data){ const r=await fetch(url,{method:'PUT',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(data)}); return r.json(); }
async function apiDelete(url){ const r=await fetch(url,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}); return r.json(); }

/* ══════════════ LOAD ALL DATA ══════════════ */
async function loadAllData(){
  const d = await apiGet('/api/dashboard-data');

  residents = (d.residents||[]).map((x,i)=>({...x, color:colorFor(i), initials:initials(x.name)}));
  households = Array.isArray(d.households) ? d.households : [];
  hhMembers  = Array.isArray(d.hhMembers)  ? d.hhMembers  : [];
  finRecs    = Array.isArray(d.finRecs)    ? d.finRecs    : [];
  announcements = d.announcements||[];
  issues        = d.issues||[];
  officers      = (d.officers||[]).map((x,i)=>({...x, color:colorFor(i), initials:initials(x.name)}));
  recommendations = d.recommendations||[];
  delinquents   = Array.isArray(d.delinquents) ? d.delinquents : [];
  payments      = residents; // same data, pay_status computed in renderPayments()

  // Reuse for Manage panel — no extra API calls needed
  muHouseholds = households;
  muResidents  = residents;
  muMembers    = hhMembers;
  muFamilies   = Array.isArray(d.families) ? d.families : [];

  const fin = d.finSummary||{};

  // update stat cards
  if(document.getElementById('statResidents')) document.getElementById('statResidents').textContent = residents.length;
  if(document.getElementById('statActive'))    document.getElementById('statActive').textContent    = residents.filter(r=>r.status==='Active').length;
  if(document.getElementById('statDelinq'))    document.getElementById('statDelinq').textContent    = delinquents.length;
  if(document.getElementById('delinBadge'))     document.getElementById('delinBadge').textContent    = delinquents.length;
  const pendingCount = residents.filter(r=>r.status==='Pending').length;
  if(document.getElementById('statPending'))      document.getElementById('statPending').textContent = pendingCount;
  if(document.getElementById('pendingNoticeCount')) document.getElementById('pendingNoticeCount').textContent = pendingCount;
  const notice = document.getElementById('pendingNotice');
  if(notice) notice.style.display = pendingCount > 0 ? 'flex' : 'none';
  if(document.getElementById('statBalance'))   document.getElementById('statBalance').textContent   = '₱'+Number(fin.total_balance||0).toLocaleString();
  if(document.getElementById('finCollected'))   document.getElementById('finCollected').textContent  = '₱'+Number(fin.total_collected||0).toLocaleString();
  if(document.getElementById('finDues'))        document.getElementById('finDues').textContent       = '₱'+Number(fin.total_dues||0).toLocaleString();
  if(document.getElementById('finBalance'))     document.getElementById('finBalance').textContent    = '₱'+Number(fin.total_balance||0).toLocaleString();
  if(document.getElementById('payCollected'))   document.getElementById('payCollected').textContent  = '₱'+Number(fin.total_collected||0).toLocaleString();
  if(document.getElementById('payDues'))        document.getElementById('payDues').textContent       = '₱'+Number(fin.total_dues||0).toLocaleString();
  if(document.getElementById('payBalance'))     document.getElementById('payBalance').textContent    = '₱'+Number(fin.total_balance||0).toLocaleString();
  if(document.getElementById('statIssues'))    document.getElementById('statIssues').textContent    = issues.filter(i=>i.status!=='Resolved').length;
  if(document.getElementById('issueBadge'))    document.getElementById('issueBadge').textContent    = issues.filter(i=>i.status!=='Resolved').length;
  if(document.getElementById('msgBadge'))      document.getElementById('msgBadge').textContent      = d.msgCount||0;

  // Payment collection stats computed from payments array
  const totalPay=payments.length||1;
  const paidC=payments.filter(p=>p.current_balance<=0).length;
  const partialC=payments.filter(p=>p.current_balance>0&&p.current_balance<1000).length;
  const unpaidC=payments.filter(p=>p.current_balance>=1000).length;
  const pctPaid=Math.round(paidC/totalPay*100);
  if(document.getElementById('statPayPct'))    document.getElementById('statPayPct').textContent=pctPaid+'%';
  if(document.getElementById('statPayBar'))    document.getElementById('statPayBar').style.width=pctPaid+'%';
  if(document.getElementById('payCountPaid'))  document.getElementById('payCountPaid').textContent=paidC;
  if(document.getElementById('payBarPaid'))    document.getElementById('payBarPaid').style.width=Math.round(paidC/totalPay*100)+'%';
  if(document.getElementById('payCountPartial'))document.getElementById('payCountPartial').textContent=partialC;
  if(document.getElementById('payBarPartial')) document.getElementById('payBarPartial').style.width=Math.round(partialC/totalPay*100)+'%';
  if(document.getElementById('payCountUnpaid'))document.getElementById('payCountUnpaid').textContent=unpaidC;
  if(document.getElementById('payBarUnpaid'))  document.getElementById('payBarUnpaid').style.width=Math.round(unpaidC/totalPay*100)+'%';

  // Members panel stats
  // (handled by renderMembers())

  renderDashboard();
  renderResidents();
  renderMembers();
  renderDelinquents();
  renderPayments();
  renderAnnouncements();
  renderIssues();
  renderOfficers();
  renderRecommendations();
  renderFinancialRecords(finRecs);
  renderManageTables();
}

/* ══════════════ RENDER FUNCTIONS ══════════════ */

function renderDashboard(){
  const statusPill={Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'};
  const recent=issues.slice(0,2);
  document.getElementById('dashIssueList').innerHTML=recent.length?recent.map(i=>`
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.05);">
      <div>
        <div style="font-size:13px;font-weight:500;">${i.title}</div>
        <div style="font-size:11px;color:var(--text-dim);">${i.resident} · ${i.block_lot}</div>
      </div>
      ${statusPill[i.status]||''}
    </div>`).join(''):'<div style="color:var(--text-dim);font-size:13px;padding:10px 0;">No issues yet.</div>';

  document.getElementById('activityFeed').innerHTML=(()=>{
    const events=[];
    issues.slice(0,3).forEach(i=>events.push({ts:i.created_at||'',icon:'🚨',color:'var(--yellow)',text:`<b>${i.resident||'A resident'}</b> filed an issue: <i>${i.title}</i>`,pill:i.status==='Resolved'?'<span class="pill pill-resolved">Resolved</span>':i.status==='In Progress'?'<span class="pill pill-progress">In Progress</span>':'<span class="pill pill-pending">Pending</span>'  }));
    announcements.slice(0,2).forEach(a=>events.push({ts:a.created_at||'',icon:'📢',color:'var(--accent)',text:`Announcement posted: <b>${a.title||a.message?.substring(0,50)+'...'}</b>`,pill:''}));
    finRecs.slice(0,3).forEach(f=>events.push({ts:f.record_date||'',icon:f.record_type==='Payment'?'💰':'📄',color:f.record_type==='Payment'?'var(--green)':'#f08080',text:`<b>${f.resident||'Resident'}</b> — ${f.record_type}: ₱${Number(f.amount).toLocaleString()}`,pill:''}));
    if(!events.length) return '<div style="color:var(--text-dim);font-size:13px;padding:10px 0;">No recent activity.</div>';
    return events.slice(0,6).map(e=>`
      <div style="display:flex;align-items:flex-start;gap:10px;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.05);">
        <span style="font-size:18px;flex-shrink:0;">${e.icon}</span>
        <div style="flex:1;font-size:12px;color:var(--text-mid);line-height:1.5;">${e.text}</div>
        ${e.pill}
      </div>`).join('');
  })();
}

function renderResidents(){
  const q=(document.getElementById('residentSearch')||{}).value||'';
  const b=(document.getElementById('residentBlock')||{}).value||'';
  // Separate pending from active residents
  const pending=residents.filter(r=>r.status==='Pending');
  const active=residents.filter(r=>r.status!=='Pending');
  // Update pending badge and section
  const pBadge=document.getElementById('pendingBadge');
  const pSection=document.getElementById('pendingSection');
  const pCount=document.getElementById('pendingCount');
  if(pBadge){pBadge.textContent=pending.length;pBadge.style.display=pending.length?'inline-flex':'none';}
  if(pCount) pCount.textContent=pending.length;
  if(pSection) pSection.style.display=pending.length?'block':'none';
  if(document.getElementById('pendingGrid')){
    document.getElementById('pendingGrid').innerHTML=pending.map(r=>`
      <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:10px;padding:14px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
          <div class="r-avatar ${r.color}" style="width:34px;height:34px;font-size:13px;">${r.initials}</div>
          <div>
            <div style="font-weight:600;font-size:13px;">${r.name}</div>
            <div style="font-size:11px;color:var(--text-dim);">${r.email||'—'}</div>
          </div>
        </div>
        ${r.contact_number?`<div style="font-size:11px;color:var(--text-dim);margin-bottom:10px;">📞 ${r.contact_number}</div>`:''}
        <div style="display:flex;gap:8px;">
          <button class="btn btn-sm" style="background:#22c55e;border-color:#22c55e;flex:1;" onclick="approveRegistration(${r.id})">✓ Approve</button>
          <button class="btn btn-sm btn-danger" style="flex:1;" onclick="rejectRegistration(${r.id})">✕ Reject</button>
        </div>
      </div>`).join('');
  }
  const filtered=active.filter(r=>{
    const matchQ=!q||r.name.toLowerCase().includes(q.toLowerCase())||(r.block_lot||'').toLowerCase().includes(q.toLowerCase());
    const matchB=!b||(r.block_lot||'').toLowerCase().includes(b.toLowerCase().replace('block ','blk '));
    return matchQ&&matchB;
  });
  const payLabel=bal=>bal<=0?'Paid':(bal<1000?'Partial':'Unpaid');
  const payPill=bal=>bal<=0?'pill-resolved':(bal<1000?'pill-pending':'pill-urgent');
  document.getElementById('residentGrid').innerHTML=filtered.length?filtered.map(r=>`
    <div class="resident-card" onclick="openResidentDetail(${r.id})" style="cursor:pointer;" title="Click for details">
      <div class="resident-card-top">
        <div class="r-avatar ${r.color}">${r.initials}</div>
        <div>
          <div class="r-name">${r.name}</div>
          <div class="r-block">${r.block_lot}</div>
        </div>
      </div>
      <div class="r-meta">
        <span class="pill ${r.status==='Active'?'pill-active':'pill-inactive'}">${r.status}</span>
        <span class="pill ${payPill(r.current_balance)}">${payLabel(r.current_balance)}</span>
      </div>
      <div style="margin-top:10px;font-size:12px;color:var(--text-dim);">Balance: ₱${Number(r.current_balance||0).toLocaleString()}</div>
    </div>`).join(''):'<div class="empty-state"><div class="empty-icon">🏠</div>No residents found.</div>';
}
function filterResidents(){ renderResidents(); }

function renderMembers(){
  const colorFor2=(n)=>{const colors=['bg-teal','bg-blue','bg-purple','bg-orange','bg-green','bg-red'];let h=0;for(let c of(n||''))h=(h*31+c.charCodeAt(0))%colors.length;return colors[h];};
  const ini=n=>{const p=(n||'').trim().split(/\s+/);return(p[0]?.[0]||'')+(p[1]?.[0]||'');};
  const relColor={Owner:'var(--blue)',Spouse:'var(--purple)',Child:'var(--green)',Tenant:'var(--yellow)',Parent:'var(--accent)',Other:'var(--text-dim)'};
  const search=(document.getElementById('memberSearch')||{}).value?.toLowerCase()||'';
  const relF=(document.getElementById('memberRelFilter')||{}).value||'';
  const filtered=hhMembers.filter(m=>{
    const matchSearch=!search||(m.name||'').toLowerCase().includes(search)||(m.block_lot||'').toLowerCase().includes(search);
    const matchRel=!relF||m.relationship===relF;
    return matchSearch&&matchRel;
  });
  if(document.getElementById('statTotalMembers')) document.getElementById('statTotalMembers').textContent=hhMembers.length;
  if(document.getElementById('statTotalHouses')) document.getElementById('statTotalHouses').textContent=new Set(hhMembers.map(m=>m.house_id)).size;
  if(!filtered.length){
    document.getElementById('membersGrid').innerHTML='<div class="empty-state"><div class="empty-icon">👥</div>'+(hhMembers.length?'No members match the filter.':'No household members yet.')+'</div>';
    return;
  }
  document.getElementById('membersGrid').innerHTML=filtered.map(m=>{
    const initStr=ini(m.name);
    const col=colorFor2(m.name);
    const rel=m.relationship||'Member';
    const relStyle=`color:${relColor[rel]||'var(--text-dim)'};`;
    const encoded=encodeURIComponent(JSON.stringify(m));
    return `<div class="member-card" style="cursor:pointer;" onclick="openMemberDetail('${encoded}')" title="Click to view details">
      <div class="member-avatar ${col}">${initStr}</div>
      <div class="member-info">
        <h4>${m.name||'—'}</h4>
        <p>${m.block_lot||'—'}</p>
        <div class="m-type" style="${relStyle}">${rel}</div>
        ${m.contact_number?`<div style="font-size:10px;color:var(--text-dim);margin-top:2px;">📞 ${m.contact_number}</div>`:''}
      </div>
    </div>`;
  }).join('');
}

function openMemberDetail(encoded){
  const m=JSON.parse(decodeURIComponent(encoded));
  document.getElementById('memDetailName').value=m.name||'';
  document.getElementById('memDetailRel').value=m.relationship||'';
  document.getElementById('memDetailPhone').value=m.contact_number||'';
  document.getElementById('memDetailBlock').textContent=m.block_lot||'—';
  document.getElementById('memDetailInitials').textContent=((m.name||'').trim().split(/\s+/).map(p=>p[0]||'').join('').substring(0,2)).toUpperCase()||'?';
  document.getElementById('modal-memberDetail').dataset.id=m.id;
  document.getElementById('memDetailMsg').textContent='';
  document.getElementById('modal-memberDetail').classList.add('open');
}

async function saveMemberDetail(){
  const id=document.getElementById('modal-memberDetail').dataset.id;
  const payload={
    name:document.getElementById('memDetailName').value.trim(),
    relationship:document.getElementById('memDetailRel').value.trim(),
    contact_number:document.getElementById('memDetailPhone').value.trim(),
  };
  if(!payload.name){showToast('Name is required.');return;}
  const msg=document.getElementById('memDetailMsg');
  msg.textContent='Saving…';
  try{
    const res=await fetch(`/api/household-members/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify(payload)});
    const d=await res.json();
    if(d.success){
      const idx=hhMembers.findIndex(m=>m.id==id);
      if(idx!==-1){hhMembers[idx]={...hhMembers[idx],...payload};}
      renderMembers();
      document.getElementById('modal-memberDetail').classList.remove('open');
      showToast('Member updated.');
    } else { msg.textContent='Update failed.'; }
  }catch(e){msg.textContent='Error saving.';}
}

async function deleteMember(){
  const id=document.getElementById('modal-memberDetail').dataset.id;
  if(!confirm('Delete this member? This cannot be undone.')) return;
  try{
    const res=await fetch(`/api/household-members/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});
    const d=await res.json();
    if(d.success){
      hhMembers=hhMembers.filter(m=>m.id!=id);
      renderMembers();
      document.getElementById('modal-memberDetail').classList.remove('open');
      showToast('Member removed.');
    }
  }catch(e){showToast('Delete failed.');}
}

async function addMemberSubmit(){
  const houseId=document.getElementById('addMemHouse').value;
  const name=document.getElementById('addMemName').value.trim();
  const rel=document.getElementById('addMemRel').value.trim();
  const phone=document.getElementById('addMemPhone').value.trim();
  if(!houseId||!name){showToast('House and name are required.');return;}
  const msg=document.getElementById('addMemMsg');
  msg.textContent='Adding…';
  try{
    const res=await fetch('/api/household-members',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({house_id:parseInt(houseId),name,relationship:rel||null,contact_number:phone||null})});
    const d=await res.json();
    if(d.success){
      const hh=households.find(h=>h.id==houseId);
      hhMembers.push({id:d.member.id,house_id:d.member.house_id,name:d.member.name,relationship:d.member.relationship,contact_number:d.member.contact_number,block_lot:hh?.block_lot_number||hh?.block_lot||'—'});
      renderMembers();
      closeModal('addMember');
      showToast('Member added.');
    } else { msg.textContent='Failed to add.'; }
  }catch(e){msg.textContent='Error adding member.';}
}

function renderDelinquents(){
  document.getElementById('delinquentGrid').innerHTML=delinquents.length?delinquents.map(d=>{
    const residentNames=(d.residents||[]).map(r=>r.name).join(', ')||'—';
    const totalBal=(d.residents||[]).reduce((s,r)=>s+(r.current_balance||0),0);
    const encoded=encodeURIComponent(JSON.stringify(d));
    return `
    <div class="res-card" onclick="openDelinquentDetail('${encoded}')" title="Click for details">
      <div class="res-thumb">
        <div style="width:100%;height:100%;background:rgba(240,100,100,0.1);display:flex;align-items:center;justify-content:center;font-size:36px;">🏠</div>
      </div>
      <div class="res-info">
        <h4>${d.block_lot}</h4>
        <div class="res-reason">${d.reason||'—'}</div>
        <div class="res-location">Residents: ${residentNames}</div>
        <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">Outstanding: ₱${Number(totalBal).toLocaleString()} · Flagged: ${d.date_flagged||'—'}</div>
      </div>
    </div>`;
  }).join(''):'<div class="empty-state"><div class="empty-icon">✅</div>No delinquent households.</div>';
}

function openDelinquentDetail(encoded){
  const d=JSON.parse(decodeURIComponent(encoded));
  const totalBal=(d.residents||[]).reduce((s,r)=>s+(r.current_balance||0),0);
  const residentsHtml=(d.residents||[]).length
    ?(d.residents||[]).map(r=>`
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:rgba(255,255,255,0.04);border-radius:8px;margin-bottom:8px;">
        <div>
          <div style="font-weight:600;font-size:13px;">${r.name}</div>
          ${r.contact_number?`<div style="font-size:11px;color:var(--text-dim);margin-top:2px;">📞 ${r.contact_number}</div>`:''}
        </div>
        <div style="text-align:right;">
          <div style="font-size:12px;color:${r.current_balance>0?'#f08080':'var(--green)'}; font-weight:700;">
            ${r.current_balance>0?'₱'+Number(r.current_balance).toLocaleString()+' due':'Settled'}
          </div>
        </div>
      </div>`).join('')
    :'<div style="color:var(--text-dim);font-size:13px;">No resident records.</div>';
  document.getElementById('delin-modal-block').textContent=d.block_lot||'—';
  document.getElementById('delin-modal-reason').textContent=d.reason||'No reason provided';
  document.getElementById('delin-modal-flagged').textContent=d.date_flagged||'—';
  document.getElementById('delin-modal-total').textContent='₱'+Number(totalBal).toLocaleString();
  document.getElementById('delin-modal-residents').innerHTML=residentsHtml;
  document.getElementById('modal-delinquent').classList.add('open');
}
function closeDelinquent(){ document.getElementById('modal-delinquent').classList.remove('open'); }

function renderPayments(){
  const payLabel=bal=>bal<=0?'Paid':(bal<1000?'Partial':'Unpaid');
  const payPill=bal=>bal<=0?'pill-resolved':(bal<1000?'pill-pending':'pill-urgent');
  document.getElementById('paymentList').innerHTML=payments.map(r=>`
    <div class="payment-row">
      <div class="r-avatar ${r.color}" style="width:36px;height:36px;font-size:12px;">${r.initials}</div>
      <div class="pay-household">
        <div class="pay-name">${r.name}</div>
        <div class="pay-block">${r.block_lot}</div>
      </div>
      <span class="pill ${payPill(r.current_balance)}">${payLabel(r.current_balance)}</span>
      <div class="pay-amount">${r.current_balance<=0?'₱0 owed':'₱'+Number(r.current_balance).toLocaleString()+' due'}</div>
      <button class="btn btn-sm" onclick="openPaymentDetail(${r.id})">View</button>
    </div>`).join('');
}

function renderThreads(){
  const list=document.getElementById('threadList');
  if(!list) return;
  const keys=Object.keys(threads);
  if(!keys.length){list.innerHTML='<div style="color:var(--text-dim);font-size:12px;padding:12px;">No conversations yet.</div>';return;}
  list.innerHTML=keys.map(id=>{
    const t=threads[id];
    const col=colorFor(t.idx||0);
    return `<div class="thread-item${currentThread==id?' active':''}" onclick="openThread(${id})">
      <div class="thread-avatar" style="background:${col};">${(t.title||'?').substring(0,2).toUpperCase()}</div>
      <div class="thread-info">
        <div class="thread-name">${t.title}</div>
        <div class="thread-preview">${t.last_message||'No messages yet'}</div>
      </div>
      <button class="thread-del-btn" onclick="event.stopPropagation();deleteConversation(${id})" title="Delete conversation">🗑</button>
    </div>`;
  }).join('');
}
async function openThread(id){
  currentThread=id;
  document.getElementById('chatName').textContent=threads[id]?.title||'';
  document.getElementById('chatAvatar').textContent=(threads[id]?.title||'??').substring(0,2).toUpperCase();
  renderThreads();
  const msgs=await apiGet('/api/messages/'+id);
  threads[id].msgs=msgs;
  renderChat();
}
function renderChat(){
  const body=document.getElementById('msgBody');
  if(!body||!currentThread) return;
  const msgs=(threads[currentThread]?.msgs||[]);
  body.innerHTML=msgs.length?msgs.map(m=>{
    const isMe=m.sender_type==='admin';
    return `<div style="display:flex;justify-content:${isMe?'flex-end':'flex-start'};margin-bottom:10px;">
      <div style="max-width:70%;background:${isMe?'var(--accent-dim)':'var(--surface)'};border:1px solid var(--glass-border);border-radius:12px;padding:8px 12px;">
        ${!isMe?`<div style="font-size:10px;color:var(--text-dim);margin-bottom:3px;">${m.sender_name}</div>`:''}
        <div style="font-size:13px;">${m.content}</div>
        <div style="font-size:10px;color:var(--text-dim);text-align:right;margin-top:2px;">${m.created_at||''}</div>
      </div>
    </div>`;
  }).join(''):'<div style="color:var(--text-dim);text-align:center;padding:20px;font-size:13px;">No messages yet.</div>';
  body.scrollTop=body.scrollHeight;
}
async function loadThreads(){
  const data=await apiGet('/api/messages/threads');
  data.forEach((t,i)=>{threads[t.id]={...t,idx:i,msgs:[]};});
  if(!currentThread&&data.length) currentThread=data[0].id;
  renderThreads();
  if(currentThread) openThread(currentThread);
}
async function deleteConversation(id){
  if(!confirm('Delete this conversation and all its messages?')) return;
  const res=await apiDelete('/api/messages/'+id);
  if(res.success){
    delete threads[id];
    if(currentThread==id){
      currentThread=null;
      const remaining=Object.keys(threads);
      if(remaining.length){currentThread=remaining[0];openThread(remaining[0]);}
      else{document.getElementById('msgBody').innerHTML='<div style="color:var(--text-dim);text-align:center;padding:20px;font-size:13px;">No conversations.</div>';document.getElementById('chatName').textContent='';}
    }
    renderThreads();
    showToast('🗑️ Conversation deleted.');
  } else {
    showToast('⚠ Failed to delete conversation.');
  }
}

function renderAnnouncements(){
  const tagLabel={notice:'Notice',urgent:'Urgent',event:'Event'};
  document.getElementById('annList').innerHTML=announcements.length?announcements.map(a=>{
    const meta=[];
    if(a.priority==='High') meta.push('<span style="color:#f08080;font-weight:700;font-size:11px;">⚡ High Priority</span>');
    if(a.target&&a.target!=='All Residents') meta.push('<span style="font-size:11px;color:var(--text-mid);">👥 '+a.target+'</span>');
    if(a.event_date) meta.push('<span style="font-size:11px;color:var(--text-mid);">📅 '+a.event_date+'</span>');
    return `
    <div class="ann-item" onclick="openAnnView(${a.id})" style="cursor:pointer;" title="Click to view">
      <div class="ann-meta">
        <span class="pill pill-${a.tag||'notice'}">${tagLabel[a.tag]||'Notice'}</span>
        <span class="ann-date">${a.created_at}</span>
      </div>
      ${meta.length?'<div style="display:flex;gap:10px;flex-wrap:wrap;margin:4px 0;">'+meta.join('')+'</div>':''}
      <div class="ann-title">${a.title}</div>
      <div class="ann-body">${a.content}</div>
      <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">By: ${a.posted_by}</div>
      <div class="ann-actions">
        <button class="btn btn-sm btn-danger" onclick="event.stopPropagation();deleteAnn(${a.id})">Delete</button>
      </div>
    </div>`}).join(''):'<div class="empty-state"><div class="empty-icon">📢</div>No announcements yet.</div>';
}
async function deleteAnn(id){
  const res=await apiDelete('/api/announcements/'+id);
  if(res.success){
    announcements=announcements.filter(a=>a.id!==id);
    renderAnnouncements();
    showToast('🗑️ Announcement deleted.');
  } else {
    showToast('⚠ Failed to delete announcement.');
  }
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
    announcements.unshift(res.announcement);
    closeModal('ann');
    document.getElementById('annTitle').value='';
    document.getElementById('annBody').value='';
    document.getElementById('annDate').value='';
    renderAnnouncements();
    showToast('✅ Announcement posted to all residents.');
  } else {
    showToast('⚠ '+(res.message||'Failed to post announcement.'));
  }
}

let respondingId=null;
// Derive priority client-side from category (no DB column needed)
function issuePriority(i){
  const high=['Security','Vandalism'];
  const medium=['Illegal Parking','Sanitation','Noise','Cleanliness'];
  if(high.includes(i.category)) return 'High';
  if(medium.includes(i.category)) return 'Medium';
  return 'Low';
}
function renderIssues(){
  const sf=(document.getElementById('issueFilter')||{}).value||'';
  const pf=(document.getElementById('issuePriFilter')||{}).value||'';
  const filtered=issues.filter(i=>{
    if(sf&&i.status!==sf) return false;
    if(pf&&issuePriority(i)!==pf) return false;
    return true;
  });
  const statusPill={Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'};
  const priColor={Critical:'#ff5555',High:'#f08080',Medium:'var(--yellow)',Low:'var(--green)'};
  document.getElementById('issueList').innerHTML=filtered.length?filtered.map(i=>{
    const pri=i.priority||issuePriority(i);
    const isCritical=pri==='Critical';
    return `
    <div class="issue-item ${isCritical?'issue-critical-row':''}" onclick="openIssueDetail(${i.id})" style="cursor:pointer;" title="Click for full details">
      <div class="issue-top">
        <div>
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:4px;">${i.resident} · ${i.block_lot}</div>
          <div class="issue-title">${isCritical?'🚨 ':''}${i.title}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
          ${statusPill[i.status]||''}
          <span style="font-size:10px;font-weight:700;color:${priColor[pri]||'var(--text-dim)'}">${pri||''}</span>
        </div>
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
        ${i.status!=='Resolved'?`<button class="btn btn-sm" onclick="event.stopPropagation();openRespondModal(${i.id})">Respond</button>`:''}
      </div>
    </div>`}).join(''):'<div class="empty-state"><div class="empty-icon">📋</div>No issues match the filter.</div>';
}
async function openRespondModal(id){
  respondingId=id;
  const issue=issues.find(i=>i.id===id);
  if(!issue) return;
  document.getElementById('respondIssueTitle').textContent=issue.title;
  document.getElementById('respondStatus').value=issue.status;
  document.getElementById('respondText').value=(issue.responses&&issue.responses[0])?issue.responses[0].content:'';
  openModal('respond');
}
async function submitResponse(){
  const issue=issues.find(i=>i.id===respondingId);
  if(!issue) return;
  const newStatus=document.getElementById('respondStatus').value;
  const responseText=document.getElementById('respondText').value.trim();
  const [statusRes, respondRes] = await Promise.all([
    apiPut('/api/issues/'+respondingId+'/status',{status:newStatus}),
    responseText ? apiPost('/api/issues/'+respondingId+'/respond',{content:responseText}) : Promise.resolve({success:true}),
  ]);
  if(statusRes.success){
    issue.status=newStatus;
    if(responseText) issue.responses=[{content:responseText}];
    closeModal('respond');
    renderIssues();
    showToast('✅ Response sent.');
  } else {
    showToast('⚠ Failed to update status.');
  }
}

function renderOfficers(){
  document.getElementById('officerTable').innerHTML=officers.map(o=>`
    <tr>
      <td>${o.name}</td>
      <td style="color:var(--text-mid);">${o.role_description||'—'}</td>
      <td style="color:var(--text-mid);">${o.email||'—'}</td>
      <td><span class="pill ${o.status==='Active'?'pill-active':'pill-inactive'}">${o.status||'Active'}</span></td>
      <td>
        <div style="display:flex;gap:6px;">
            <button class="btn btn-sm btn-blue" onclick="openEditOfficer(${o.id})">Edit</button>
            <button class="btn btn-sm btn-danger" onclick="removeOfficer(${o.id})">Remove</button>
          </div>
      </td>
    </tr>`).join('');
}
function openNewThreadModal(){
  const sel=document.getElementById('newThreadResident');
  if(sel){
    sel.innerHTML='<option value="">— Select Resident —</option>'+
      residents.map(r=>`<option value="${r.id}">${r.name}</option>`).join('');
  }
  const osel=document.getElementById('newThreadOfficer');
  if(osel){
    osel.innerHTML='<option value="">— No specific officer —</option>'+
      (officers||[]).map(o=>`<option value="${o.id}">${o.name}</option>`).join('');
  }
  document.getElementById('newThreadTitle').value='';
  openModal('newThread');
}
async function adminStartThread(){
  const title=document.getElementById('newThreadTitle').value.trim();
  const resident_id=document.getElementById('newThreadResident').value;
  const officer_id=document.getElementById('newThreadOfficer')?.value||'';
  if(!title){showToast('⚠ Please enter a subject.');return;}
  const payload={title};
  if(resident_id) payload.resident_id=resident_id;
  if(officer_id) payload.officer_id=officer_id;
  const res=await apiPost('/api/messages/start',payload);
  if(res.success){
    closeModal('newThread');
    const t=res.conversation;
    threads[t.id]={...t,msgs:[]};
    renderThreads();
    openThread(t.id);
    showToast('✅ Conversation started.');
  } else {
    showToast('⚠ Could not start conversation.');
  }
}

async function removeOfficer(id){
  const res=await apiDelete('/api/officers/'+id);
  if(res.success){officers=officers.filter(o=>o.id!==id);renderOfficers();showToast('🗑️ Officer removed.');}
  else{showToast('⚠ Failed to remove officer.');}
}

function openEditOfficer(id){
  const o=officers.find(x=>x.id===id);
  if(!o) return;
  document.getElementById('editOfficerIdx').value=id;
  document.getElementById('editOfficerName').value=o.name||'';
  document.getElementById('editOfficerEmail').value=o.email||'';
  document.getElementById('editOfficerContact').value=o.contact_number||'';
  document.getElementById('editOfficerRole').value=o.role_description||'';
  document.getElementById('editOfficerStatus').value=o.status==='Active'?'Active':'Inactive';
  document.getElementById('editOfficerPass').value='';
  openModal('editOfficer');
}
async function saveEditOfficer(){
  const id=document.getElementById('editOfficerIdx').value;
  const name=document.getElementById('editOfficerName').value.trim();
  const email=document.getElementById('editOfficerEmail').value.trim();
  const contact_number=document.getElementById('editOfficerContact').value.trim();
  const role_description=document.getElementById('editOfficerRole').value.trim();
  const status=document.getElementById('editOfficerStatus').value;
  if(!name||!email){showToast('⚠ Name and email required.');return;}
  const payload={name,email,contact_number,role_description,status};
  const newPass=document.getElementById('editOfficerPass').value;
  if(newPass) payload.password=newPass;
  const res=await apiPut('/api/officers/'+id,payload);
  if(res.success){
    const idx=officers.findIndex(o=>o.id===parseInt(id));
    if(idx>=0) officers[idx]=res.officer;
    closeModal('editOfficer');
    renderOfficers();
    showToast('✅ Officer updated.');
  } else {
    showToast('⚠ '+(res.message||'Failed to update officer.'));
  }
}

async function openEditHousehold(id){
  const h=muHouseholds.find(x=>x.id===id);
  if(!h) return;
  document.getElementById('editHIdx').value=id;
  document.getElementById('editHLoc').value=h.block_lot_number||'';
  document.getElementById('editHStatus').value=h.status||'Active';
  document.getElementById('editHFamily').value=h.family?.family_name||'';
  document.getElementById('editHMembers').value=h.family?.members||'';
  openModal('muEditHousehold');
}
async function saveEditHousehold(){
  const id=document.getElementById('editHIdx').value;
  const loc=document.getElementById('editHLoc').value.trim();
  const status=document.getElementById('editHStatus').value;
  const family_name=document.getElementById('editHFamily').value.trim();
  const members=document.getElementById('editHMembers').value.trim();
  if(!loc){showToast('⚠ Block & Lot is required.');return;}
  const res=await apiPut('/api/households/'+id,{block_lot_number:loc,status,family_name,members});
  if(res.success){closeModal('muEditHousehold');await loadManageData();showToast('✅ Household updated.');}
}
async function openEditUser(id){
  const u=muResidents.find(x=>x.id===id);
  if(!u) return;
  document.getElementById('editUIdx').value=id;
  document.getElementById('editUName').value=u.name||'';
  document.getElementById('editUEmail').value=u.email||'';
  document.getElementById('editUStatus').value=u.status==='Active'?'Active':'Inactive';
  document.getElementById('editUPass').value='';
  openModal('muEditUser');
}
async function saveEditUser(){
  const id=document.getElementById('editUIdx').value;
  const name=document.getElementById('editUName').value.trim();
  const email=document.getElementById('editUEmail').value.trim();
  const status=document.getElementById('editUStatus').value;
  if(!name||!email){showToast('⚠ Name and email required.');return;}
  const payload={name,email,status};
  const newPass=document.getElementById('editUPass').value;
  if(newPass) payload.password=newPass;
  const res=await apiPut('/api/residents/'+id,payload);
  if(res.success){closeModal('muEditUser');await loadManageData();loadAllData();showToast('✅ User updated.');}
}
function populateMemberHouseholdSelect(){
  const sel=document.getElementById('muMHousehold');
  if(!sel) return;
  sel.innerHTML='<option value="">— Select Household —</option>'+
    muHouseholds.map(h=>`<option value="${h.id}">${h.block_lot_number}</option>`).join('');
}

function openAddUserModal(){
  const sel=document.getElementById('muUHousehold');
  if(sel){
    sel.innerHTML='<option value="">— None —</option>'+
      muHouseholds.map(h=>`<option value="${h.id}">${h.block_lot_number}</option>`).join('');
  }
  openModal('muAddUser');
}

async function saveChangeStatus(){
  const id=document.getElementById('statusHIdx').value;
  const status=document.getElementById('statusHNew').value;
  const remarks=document.getElementById('statusHRemarks').value.trim();
  if(!id){showToast('⚠ No household selected.');return;}
  const reason=document.getElementById('statusHReason').value;
  const fullReason=remarks ? reason+': '+remarks : reason;
  const res=await apiPut('/api/households/'+id,{status,reason:fullReason});
  if(res.success){
    closeModal('muChangeStatus');
    await loadManageData();
    loadAllData();
    showToast('✅ Household status updated to '+status+'.');
  } else {
    showToast('⚠ '+(res.message||'Failed to update status.'));
  }
}

function openChangeStatusModal(id){
  const h=muHouseholds.find(x=>x.id===id);
  if(!h) return;
  document.getElementById('statusHIdx').value=id;
  document.getElementById('statusHName').textContent=h.block_lot_number;
  document.getElementById('statusHLoc').textContent='Current status: '+(h.status||'Active');
  document.getElementById('statusHNew').value=h.status||'Active';
  document.getElementById('statusHRemarks').value='';
  openModal('muChangeStatus');
}

function filterThreads(q){
  const term=(q||'').toLowerCase();
  document.querySelectorAll('#threadList .thread-item').forEach(el=>{
    const name=(el.querySelector('.thread-name')||{}).textContent||'';
    const prev=(el.querySelector('.thread-preview')||{}).textContent||'';
    el.style.display=(name+prev).toLowerCase().includes(term)?'':'none';
  });
}

/* ══ RECOMMENDATIONS ══ */
function renderRecommendations(){
  const el=document.getElementById('recsAdminList');
  if(!recommendations.length){
    el.innerHTML='<div class="empty-state"><div class="empty-icon">💡</div>No recommendations yet.</div>';
    return;
  }
  const statusPill={Pending:'<span class="pill pill-pending">Pending</span>',Reviewed:'<span class="pill pill-notice">Reviewed</span>',Approved:'<span class="pill pill-event">Approved</span>',Rejected:'<span class="pill pill-urgent">Rejected</span>'};
  el.innerHTML=recommendations.map(r=>`
    <div class="issue-item" style="margin-bottom:12px;cursor:pointer;" onclick="openRecDetail(${r.id})" title="Click for details">
      <div class="issue-top">
        <div>
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:4px;">${r.resident} · ${r.created_at}${r.category?' · '+r.category:''}</div>
          <div class="issue-title">${r.title}</div>
        </div>
        ${statusPill[r.status]||''}
      </div>
      <div class="issue-body">${r.description}</div>
      <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
        <select onchange="event.stopPropagation();updateRecStatus(${r.id},this.value)" onclick="event.stopPropagation()" style="font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text-main);">
          <option value="Pending" ${r.status==='Pending'?'selected':''}>Pending</option>
          <option value="Reviewed" ${r.status==='Reviewed'?'selected':''}>Reviewed</option>
          <option value="Approved" ${r.status==='Approved'?'selected':''}>Approved</option>
          <option value="Rejected" ${r.status==='Rejected'?'selected':''}>Rejected</option>
        </select>
      </div>
    </div>`).join('');
}
async function updateRecStatus(id,status){
  await apiPut('/api/recommendations/'+id+'/status',{status});
  const rec=recommendations.find(r=>r.id===id);
  if(rec) rec.status=status;
  renderRecommendations();
  showToast('✅ Status updated.');
}

// ── DETAIL POPUP FUNCTIONS ──────────────────────────────────────────────────
function openResidentDetail(id){
  const r=residents.find(x=>x.id===id);
  if(!r) return;
  const payLabel=bal=>bal<=0?'Paid':(bal<1000?'Partial':'Unpaid');
  const payColor=bal=>bal<=0?'var(--green)':(bal<1000?'var(--yellow)':'#f08080');
  document.getElementById('resD-avatar').className='r-avatar '+r.color;
  document.getElementById('resD-avatar').textContent=r.initials;
  document.getElementById('resD-name').textContent=r.name;
  document.getElementById('resD-block').textContent=r.block_lot||'—';
  document.getElementById('resD-status').textContent=r.status;
  document.getElementById('resD-status').className='pill '+(r.status==='Active'?'pill-active':'pill-inactive');
  document.getElementById('resD-balance').textContent='₱'+Number(r.current_balance||0).toLocaleString();
  document.getElementById('resD-balance').style.color=payColor(r.current_balance||0);
  document.getElementById('resD-payStatus').textContent=payLabel(r.current_balance||0);
  document.getElementById('resD-email').textContent=r.email||'—';
  document.getElementById('resD-phone').textContent=r.contact_number||'—';
  document.getElementById('resD-viewPayBtn').onclick=()=>{
    document.getElementById('modal-residentDetail').classList.remove('open');
    nav('payments',null);
    setTimeout(()=>{ const s=document.getElementById('paymentSearch'); if(s){s.value=r.name;filterPayments&&filterPayments();} },200);
  };
  document.getElementById('modal-residentDetail').classList.add('open');
}

function openIssueDetail(id){
  const i=issues.find(x=>x.id===id);
  if(!i) return;
  const pri=i.priority||issuePriority(i);
  const priColor={Critical:'#ff5555',High:'#f08080',Medium:'var(--yellow)',Low:'var(--green)'};
  const statusPill={Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'};
  document.getElementById('issD-title').textContent=(pri==='Critical'?'🚨 ':'')+i.title;
  document.getElementById('issD-status').innerHTML=(statusPill[i.status]||'')+(pri?` <span style="font-size:11px;font-weight:700;color:${priColor[pri]||'var(--text-dim)'};">${pri}</span>`:'');
  document.getElementById('issD-who').textContent=i.resident+' · '+i.block_lot;
  document.getElementById('issD-cat').textContent=(i.category||'')+(i.category?' · ':'')+'#'+i.id+' · '+i.created_at;
  document.getElementById('issD-desc').textContent=i.description;
  const hasLoc=i.latitude&&i.longitude;
  document.getElementById('issD-location').innerHTML=hasLoc?`<a href="https://maps.google.com/?q=${i.latitude},${i.longitude}" target="_blank" style="color:var(--blue);font-size:12px;">📍 View on map</a>`:'';
  const responses=i.responses||[];
  const rcEl=document.getElementById('issD-responseCount'); if(rcEl) rcEl.textContent=responses.length;
  document.getElementById('issD-responses').innerHTML=responses.length?responses.map(r=>`
    <div style="padding:10px 12px;background:rgba(122,180,240,0.07);border-left:2px solid rgba(122,180,240,0.4);border-radius:0 6px 6px 0;margin-bottom:8px;">
      <div style="font-size:10px;color:var(--blue);font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">${r.type||'Response'}</div>
      <div style="font-size:13px;color:var(--text-mid);">${r.content}</div>
    </div>`).join(''):'<div style="color:var(--text-dim);font-size:13px;padding:4px 0;">No responses yet.</div>';
  const btn=document.getElementById('issD-respondBtn');
  if(btn){
    if(i.status!=='Resolved'){ btn.style.display='inline-block'; btn.onclick=()=>{ document.getElementById('modal-issueDetail').classList.remove('open'); openRespondModal(i.id); }; }
    else { btn.style.display='none'; }
  }
  document.getElementById('modal-issueDetail').classList.add('open');
}

function openRecDetail(id){
  const r=recommendations.find(x=>x.id===id);
  if(!r) return;
  document.getElementById('recD-who').textContent=r.resident+' · '+(r.block_lot||'—');
  document.getElementById('recD-cat').textContent=(r.category||'—')+' · '+r.created_at;
  document.getElementById('recD-title').textContent=r.title;
  document.getElementById('recD-desc').textContent=r.description;
  document.getElementById('recD-statusSel').value=r.status||'Pending';
  document.getElementById('recD-updateBtn').onclick=async()=>{
    const s=document.getElementById('recD-statusSel').value;
    await updateRecStatus(r.id,s);
    document.getElementById('modal-recDetail').classList.remove('open');
  };
  document.getElementById('modal-recDetail').classList.add('open');
}

function openAnnView(id){
  const a=announcements.find(x=>x.id===id);
  if(!a) return;
  const tagLabel={notice:'Notice',urgent:'🚨 Urgent',event:'📅 Event'};
  document.getElementById('annV-tag').textContent=tagLabel[a.tag]||'Notice';
  document.getElementById('annV-tag').className='pill pill-'+(a.tag||'notice');
  document.getElementById('annV-priority').style.display=a.priority==='High'?'inline':'none';
  document.getElementById('annV-title').textContent=a.title;
  document.getElementById('annV-content').textContent=a.content;
  document.getElementById('annV-by').textContent='Posted by '+a.posted_by+' on '+a.created_at;
  const meta=[];
  if(a.target&&a.target!=='All Residents') meta.push('👥 '+a.target);
  if(a.event_date) meta.push('📅 Event date: '+a.event_date);
  document.getElementById('annV-meta').textContent=meta.join(' · ')||'All Residents';
  document.getElementById('annV-delBtn').onclick=()=>{ deleteAnn(a.id); document.getElementById('modal-annView').classList.remove('open'); };
  document.getElementById('modal-annView').classList.add('open');
}

function renderFinancialRecords(records){
  if(!records||!Array.isArray(records)) return;
  const thead=document.querySelector('#excelTable thead tr');
  const tbody=document.querySelector('#excelTable tbody');
  if(!tbody) return;
  if(thead) thead.innerHTML='<th>Resident</th><th>Type</th><th>Description</th><th style="text-align:right">Amount</th><th>Date</th><th></th>';
  if(!records.length){
    tbody.innerHTML='<tr><td colspan="6" style="text-align:center;color:var(--text-dim);padding:20px;">No financial records yet.</td></tr>';
    return;
  }
  tbody.innerHTML=records.map(r=>`<tr data-res="${(r.resident||'').toLowerCase()}" data-type="${r.record_type}">
    <td>
      <div style="font-size:13px;">${r.resident||'—'}</div>
      ${r.block_lot&&r.block_lot!=='—'?`<div style="font-size:10px;color:var(--text-dim);">${r.block_lot}</div>`:''}
    </td>
    <td><span class="pill ${r.record_type==='Payment'?'pill-resolved':r.record_type==='Penalty'?'pill-urgent':'pill-pending'}">${r.record_type}</span></td>
    <td style="font-size:12px;">${r.description||'—'}</td>
    <td style="text-align:right;font-weight:500;color:${r.record_type==='Payment'?'var(--green)':r.record_type==='Adjustment'&&r.amount<0?'var(--green)':'#f08080'}">
      ${r.record_type==='Payment'?'−':'+'}&#8369;${Number(Math.abs(r.amount)).toLocaleString()}
    </td>
    <td style="font-size:12px;">${r.record_date?new Date(r.record_date).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}):'—'}</td>
    <td><button class="btn btn-sm btn-danger" onclick="deleteFinRecord(${r.id})">Delete</button></td>
  </tr>`).join('');
}


async function addOfficer(){
  const name=document.getElementById('newOfficerName').value.trim();
  const role=document.getElementById('newOfficerRole').value;
  const email=document.getElementById('newOfficerEmail')?document.getElementById('newOfficerEmail').value.trim():'';
  const password=document.getElementById('newOfficerPass')?document.getElementById('newOfficerPass').value:'';
  if(!name){showToast('⚠ Please enter a name.');return;}
  if(!password){showToast('⚠ Password is required.');return;}
  const res=await apiPost('/api/officers',{name,role_description:role,email,password});
  if(res.success){
    officers.push({...res.officer,color:colorFor(officers.length),initials:initials(name)});
    closeModal('addOfficer');
    document.getElementById('newOfficerName').value='';
    renderOfficers();
    showToast('✅ Officer added.');
  } else {
    showToast('⚠ '+(res.message||'Failed to add officer.'));
  }
}

async function addResident(){
  const name=document.getElementById('newResName').value.trim();
  const block_lot_number=document.getElementById('newResBlock').value.trim();
  const email=document.getElementById('newResEmail')?document.getElementById('newResEmail').value.trim():'';
  const password=document.getElementById('newResPass')?document.getElementById('newResPass').value:'';
  const contact_number=document.getElementById('newResContact')?document.getElementById('newResContact').value.trim():'';
  if(!name||!email){showToast('⚠ Please enter name and email.');return;}

  let house_id=null;
  if(block_lot_number){
    const existing=households.find(h=>h.block_lot_number.toLowerCase()===block_lot_number.toLowerCase());
    if(existing){
      house_id=existing.id;
    } else {
      const hRes=await apiPost('/api/households',{block_lot_number,status:'Active'});
      if(hRes.success) house_id=hRes.household.id;
    }
  }

  if(!password){showToast('⚠ Password is required.');return;}
  const res=await apiPost('/api/residents',{name,email,password:password,house_id,contact_number});
  if(res.success){
    closeModal('addResident');
    await loadManageData();
    loadAllData();
    showToast('✅ Resident added successfully.');
  } else {
    showToast('⚠ '+(res.message||'Failed to add resident.'));
  }
}

async function renderReports(){
  const el = document.getElementById('officerFiles');
  if(!el) return;
  el.innerHTML='<div style="color:var(--text-dim);font-size:13px;padding:8px 0;">Loading…</div>';
  try {
    const data = await apiGet('/api/officer-files');
    if(!Array.isArray(data)||!data.length){
      el.innerHTML='<div style="color:var(--text-dim);font-size:13px;padding:16px 0;">No files from officers yet.</div>';
      return;
    }
    el.innerHTML=data.map(f=>`
      <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid var(--glass-border);">
        <span style="font-size:22px;flex-shrink:0">📊</span>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:500;margin-bottom:2px;">${f.original_name}</div>
          <div style="font-size:11px;color:var(--text-dim);">${f.officer_name} &middot; ${f.category} &middot; ${f.period||'&mdash;'}</div>
          ${f.notes?`<div style="font-size:11px;color:var(--text-mid);margin-top:3px;">${f.notes}</div>`:''}
          <div style="font-size:10px;color:var(--text-dim);margin-top:3px;">${new Date(f.created_at).toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'numeric'})}</div>
        </div>
        ${f.file_path?`<a href="/storage/${f.file_path}" download="${f.original_name}" class="btn btn-sm" style="flex-shrink:0;">&darr; Download</a>`:''}
      </div>`).join('');
  } catch(e) {
    el.innerHTML='<div style="color:var(--text-dim);font-size:13px;padding:8px 0;">Could not load files.</div>';
  }
}

/* ══════════════ REPORT GENERATION (CSV downloads) ══════════════ */
function downloadCSV(filename, headers, rows){
  const escape = v => '"'+String(v==null?'':v).replace(/"/g,'""')+'"';
  const csv = [headers.map(escape).join(','), ...rows.map(r=>r.map(escape).join(','))].join('\r\n');
  const blob = new Blob(['\uFEFF'+csv], {type:'text/csv;charset=utf-8;'});
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(a.href);
}

function reportMonthlySummary(){
  if(!residents||!residents.length){ showToast('⚠ No resident data loaded yet.'); return; }
  const now = new Date();
  const label = now.toLocaleDateString('en-PH',{month:'long',year:'numeric'});
  const fname = `monthly-summary-${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}.csv`;
  const rows = residents.map(r=>{
    const recs = Array.isArray(finRecs)?finRecs.filter(f=>String(f.resident_id)===String(r.id)):[];
    const dues = recs.filter(f=>['Due','Penalty'].includes(f.record_type)).reduce((s,f)=>s+Number(f.amount||0),0);
    const paid = recs.filter(f=>f.record_type==='Payment').reduce((s,f)=>s+Number(f.amount||0),0);
    return [r.id, r.name, r.block_lot||r.block_lot_number||'', dues.toFixed(2), paid.toFixed(2), Number(r.current_balance||0).toFixed(2), r.status||''];
  });
  downloadCSV(fname, ['ID','Name','Block/Lot','Total Dues (₱)','Total Paid (₱)','Outstanding (₱)','Status'], rows);
  showToast('📊 Monthly Summary downloaded!');
}

function reportPayments(){
  const src = Array.isArray(payments)&&payments.length ? payments : residents;
  if(!src||!src.length){ showToast('⚠ No payment data loaded yet.'); return; }
  const fname = `payment-report-${new Date().toISOString().slice(0,10)}.csv`;
  const rows = src.map(r=>{
    const bal = Number(r.current_balance||0);
    const payStatus = bal<=0?'Paid':(bal<500?'Partial':'Unpaid');
    return [r.id, r.name, r.block_lot||r.block_lot_number||'', r.email||'', r.contact_number||'', bal.toFixed(2), payStatus, r.status||''];
  });
  downloadCSV(fname, ['ID','Name','Block/Lot','Email','Contact','Balance (₱)','Payment Status','Account Status'], rows);
  showToast('💳 Payment Report downloaded!');
}

function reportIssues(){
  if(!issues||!issues.length){ showToast('⚠ No issue data loaded yet.'); return; }
  const fname = `issue-report-${new Date().toISOString().slice(0,10)}.csv`;
  const rows = issues.map(i=>[
    i.id, i.category||'', i.title||i.description||'', i.resident||i.resident_name||'',
    i.block_lot||'', i.status||'', i.priority||'',
    i.created_at?new Date(i.created_at).toLocaleDateString('en-PH'):''
  ]);
  downloadCSV(fname, ['ID','Category','Title','Resident','Block/Lot','Status','Priority','Date Filed'], rows);
  showToast('🔧 Issue Report downloaded!');
}

function reportResidentDirectory(){
  if(!residents||!residents.length){ showToast('⚠ No resident data loaded yet.'); return; }
  const fname = `resident-directory-${new Date().toISOString().slice(0,10)}.csv`;
  const rows = residents.map(r=>[
    r.id, r.name, r.email||'', r.contact_number||'',
    r.block_lot||r.block_lot_number||'', r.status||'',
    Number(r.current_balance||0).toFixed(2)
  ]);
  downloadCSV(fname, ['ID','Name','Email','Contact','Block/Lot','Status','Balance (₱)'], rows);
  showToast('👥 Resident Directory downloaded!');
}

/* ══════════════ MANAGE USERS TABLES ══════════════ */
let muHouseholds=[];
let muResidents=[];
let muMembers=[];
let muFamilies=[];

async function loadManageData(){
  const [h,r,m,f]=await Promise.all([
    apiGet('/api/households'),
    apiGet('/api/residents'),
    apiGet('/api/household-members'),
    apiGet('/api/families').catch(()=>[]),
  ]);
  muHouseholds=h;
  muResidents=r;
  muMembers=m;
  muFamilies=Array.isArray(f)?f:[];
  renderManageTables();
}

function renderManageTables(){
  if(document.getElementById('householdTbody'))
    document.getElementById('householdTbody').innerHTML=muHouseholds.map((h,i)=>{const mc=muMembers.filter(m=>m.house_id===h.id).length;return `
      <tr>
        <td>${h.id}</td><td>${h.block_lot_number}</td><td>${h.family?.family_name||'—'}</td><td>${mc||h.family?.members||'—'}</td>
        <td><span class="pill ${h.status==='Active'?'pill-active':'pill-inactive'}">${h.status}</span></td>
        <td>
          <div style="display:flex;gap:6px;">
            <button class="btn btn-sm btn-blue" onclick="openEditHousehold(${h.id})">Edit</button>
            <button class="btn btn-sm" onclick="openChangeStatusModal(${h.id})">Status</button>
            <button class="btn btn-sm btn-danger" onclick="deleteHousehold(${h.id})">Deactivate</button>
          </div>
        </td>
      </tr>`}).join('');

  if(document.getElementById('userTbody'))
    document.getElementById('userTbody').innerHTML=muResidents.map((u,i)=>`
      <tr>
        <td>${u.id}</td><td>${u.name}</td><td style="color:var(--text-dim);">${u.email||'—'}</td>
        <td><span class="pill ${u.status==='Active'?'pill-active':'pill-inactive'}">${u.status}</span></td>
        <td>
          <div style="display:flex;gap:6px;">
            <button class="btn btn-sm btn-blue" onclick="openEditUser(${u.id})">Edit</button>
            <button class="btn btn-sm btn-danger" onclick="deactivateResident(${u.id})">Deactivate</button>
          </div>
        </td>
      </tr>`).join('');

  if(document.getElementById('memberTbody'))
    document.getElementById('memberTbody').innerHTML=muMembers.map((m,i)=>`
      <tr>
        <td>${m.id}</td><td>${m.name||'—'}</td><td>${m.block_lot||m.house_id||'—'}</td><td>${m.relationship||'Member'}</td>
        <td>
          <div style="display:flex;gap:6px;">
            <button class="btn btn-sm btn-blue" onclick="openEditMember(${m.id})">Edit</button>
            <button class="btn btn-sm btn-danger" onclick="deleteMember(${m.id})">Remove</button>
          </div>
        </td>
      </tr>`).join('') || '<tr><td colspan="5" style="text-align:center;color:var(--text-dim);">No members yet.</td></tr>';

  if(document.getElementById('familyTbody')){
    const fRows=muFamilies.length
      ?muFamilies.map(f=>`<tr>
        <td>${f.id}</td><td>${f.family_name||'—'}</td><td>${f.family_head||'—'}</td><td>${f.members||'—'}</td>
        <td><div style="display:flex;gap:6px;">
          <button class="btn btn-sm btn-danger" onclick="deleteFamily(${f.id})">Remove</button>
        </div></td>
      </tr>`).join('')
      :'<tr><td colspan="5" style="text-align:center;color:var(--text-dim);">No families yet.</td></tr>';
    document.getElementById('familyTbody').innerHTML=fRows;
  }
}

async function deleteHousehold(id){ await apiPut('/api/households/'+id,{status:'Inactive'}); await loadManageData(); showToast('✅ Household deactivated.'); }
async function deactivateResident(id){ await apiDelete('/api/residents/'+id); await loadManageData(); loadAllData(); showToast('✅ Resident deactivated.'); }

async function approveRegistration(id){
  const res=await apiPost('/api/residents/'+id+'/approve',{});
  if(res?.success){ showToast('✅ Registration approved. Resident can now log in.'); loadAllData(); }
  else showToast('⚠ Could not approve registration.');
}
async function rejectRegistration(id){
  if(!confirm('Reject and permanently delete this pending registration?')) return;
  const res=await apiDelete('/api/residents/'+id+'/reject');
  if(res?.success){ showToast('✅ Registration rejected and removed.'); loadAllData(); }
  else showToast('⚠ Could not reject registration.');
}
async function deleteMember(id){ await apiDelete('/api/household-members/'+id); await loadManageData(); showToast('🗑️ Member removed.'); }
async function deleteFamily(id){ await apiDelete('/api/families/'+id); await loadManageData(); showToast('🗑️ Family removed.'); }

function switchManagePanel(){
  const val=document.getElementById('tableSelector').value;
  document.querySelectorAll('.table-panel').forEach(p=>p.classList.remove('visible'));
  document.getElementById(val).classList.add('visible');
}
function filterManageTable(){
  const q=document.getElementById('manageSearch').value.toLowerCase();
  document.querySelectorAll('.panel-table tbody tr').forEach(row=>{
    row.style.display=row.textContent.toLowerCase().includes(q)?'':'none';
  });
}

/* ══════════════ MANAGE USERS MODAL FUNCTIONS (Households & Residents) ══════════════ */
async function saveAddHousehold(){
  const loc=document.getElementById('muHLoc').value.trim();
  const status=document.getElementById('muHStatus').value||'Active';
  const family_name=document.getElementById('muHFamily').value.trim();
  const members=document.getElementById('muHMembers').value.trim();
  const family_head=document.getElementById('muHHead').value.trim();
  if(!loc){showToast('⚠ Please fill in required fields.');return;}
  const res=await apiPost('/api/households',{block_lot_number:loc,status,family_name,members,family_head});
  if(res.success){
    closeModal('muAddHousehold');
    ['muHLoc','muHFamily','muHMembers','muHHead','muHContact'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
    await loadManageData();
    showToast('✅ Household added successfully.');
  }
}

async function saveAddUser(){
  const name=document.getElementById('muUName').value.trim();
  const email=document.getElementById('muUEmail').value.trim();
  const role=document.getElementById('muURole').value;
  const pass=document.getElementById('muUPass').value;
  const pass2=document.getElementById('muUPass2').value;
  const house_id=document.getElementById('muUHousehold').value||null;
  if(!name||!email){showToast('⚠ Name and email are required.');return;}
  if(!pass){showToast('⚠ Password is required.');return;}
  if(pass!==pass2){showToast('⚠ Passwords do not match.');return;}
  let res;
  if(role==='Officer'){
    res=await apiPost('/api/officers',{name,email,password:pass,role_description:'Officer'});
  } else {
    res=await apiPost('/api/residents',{name,email,password:pass,house_id});
  }
  if(res.success){
    closeModal('muAddUser');
    ['muUName','muUEmail','muUPass','muUPass2'].forEach(id=>document.getElementById(id).value='');
    await loadManageData();loadAllData();
    showToast('✅ '+(role==='Officer'?'Officer':'User')+' account created.');
  } else {
    showToast('⚠ '+(res.message||'Failed to create account.'));
  }
}

async function saveAddMember(){
  const name=document.getElementById('muMName').value.trim();
  const house_id=document.getElementById('muMHousehold').value;
  const relationship=document.getElementById('muMRelation').value;
  const contact_number=document.getElementById('muMContact')?document.getElementById('muMContact').value.trim():'';
  if(!name||!house_id){showToast('⚠ Name and household are required.');return;}
  const res=await apiPost('/api/household-members',{name,house_id,relationship,contact_number});
  if(res.success){
    closeModal('muAddMember');
    document.getElementById('muMName').value='';
    await loadManageData();
    showToast('✅ Member added.');
  } else {
    showToast('⚠ '+(res.message||'Failed to add member.'));
  }
}

function openEditMember(id){
  const m=muMembers.find(x=>x.id===id);
  if(!m) return;
  document.getElementById('editMIdx').value=id;
  document.getElementById('editMName').value=m.name||'';
  document.getElementById('editMHousehold').value=m.block_lot||'';
  document.getElementById('editMType').value=m.relationship||'Spouse';
  openModal('muEditMember');
}

async function saveEditMember(){
  const id=document.getElementById('editMIdx').value;
  const name=document.getElementById('editMName').value.trim();
  const relationship=document.getElementById('editMType').value;
  if(!name){showToast('⚠ Name is required.');return;}
  const res=await apiPut('/api/household-members/'+id,{name,relationship});
  if(res.success){
    closeModal('muEditMember');
    await loadManageData();
    showToast('✅ Member updated.');
  } else {
    showToast('⚠ '+(res.message||'Failed to update member.'));
  }
}

/* ══════════════ ANALYTICS ══════════════ */
let finRecs = [];
let hhMembers = [];

function renderAnalytics(){
  if(document.getElementById('analyticMembers')) document.getElementById('analyticMembers').textContent = residents.length||'—';
  if(document.getElementById('analyticHouses'))  document.getElementById('analyticHouses').textContent  = households.length||'—';
  if(document.getElementById('analyticDelinq'))  document.getElementById('analyticDelinq').textContent  = delinquents.length||'0';
  if(typeof d3 === 'undefined') return;
  drawCollectionsChart();
  drawDelinquentsChart();
  drawComplaintsChart();
  setTimeout(renderAnalyticsHeatMap, 120);
}

function drawCollectionsChart(){
  const el = document.getElementById('chart-collections');
  if(!el) return;
  el.innerHTML='';
  const payments = finRecs.filter(r=>r.record_type==='Payment');
  if(!payments.length){
    el.innerHTML='<div style="color:var(--text-dim);font-size:13px;text-align:center;padding:40px 0;">No payment records yet.</div>';
    return;
  }
  const byMonth={};
  payments.forEach(r=>{
    const d=new Date(r.record_date||r.date);
    const key=isNaN(d)?r.record_date:d.toLocaleDateString('en-PH',{month:'short',year:'numeric'});
    byMonth[key]=(byMonth[key]||0)+parseFloat(r.amount||0);
  });
  const data=Object.entries(byMonth).map(([month,amount])=>({month,amount}));
  const W=el.clientWidth||320, H=180;
  const m={top:16,right:16,bottom:36,left:52};
  const w=W-m.left-m.right, h=H-m.top-m.bottom;
  const svg=d3.select(el).append('svg').attr('width',W).attr('height',H);
  const g=svg.append('g').attr('transform',`translate(${m.left},${m.top})`);
  const x=d3.scaleBand().domain(data.map(d=>d.month)).range([0,w]).padding(0.35);
  const y=d3.scaleLinear().domain([0,d3.max(data,d=>d.amount)*1.25||1]).range([h,0]);
  g.selectAll('rect').data(data).join('rect')
    .attr('x',d=>x(d.month)).attr('y',d=>y(d.amount))
    .attr('width',x.bandwidth()).attr('height',d=>h-y(d.amount))
    .attr('fill','#82c98a').attr('rx',3);
  g.append('g').attr('transform',`translate(0,${h})`).call(d3.axisBottom(x))
    .call(ax=>{ax.select('.domain').attr('stroke','rgba(255,255,255,0.15)');ax.selectAll('.tick line').remove();ax.selectAll('text').attr('fill','rgba(240,236,228,0.6)').style('font-size','11px');});
  g.append('g').call(d3.axisLeft(y).ticks(3).tickFormat(v=>'₱'+(v>=1000?(v/1000).toFixed(0)+'k':v)))
    .call(ax=>{ax.select('.domain').remove();ax.selectAll('.tick line').attr('stroke','rgba(255,255,255,0.1)').attr('x2',w);ax.selectAll('text').attr('fill','rgba(240,236,228,0.6)').style('font-size','11px');});
}

function drawDelinquentsChart(){
  const el=document.getElementById('chart-delinquents');
  if(!el) return;
  el.innerHTML='';
  const activeCount=residents.filter(r=>r.status==='Active').length;
  const delinqCount=delinquents.length;
  const total=activeCount+delinqCount||1;
  const data=[{label:'Active',value:activeCount,color:'#82c98a'},{label:'Delinquent',value:delinqCount,color:'#f08080'}];
  const W=Math.min(el.clientWidth||200,240), H=180;
  const R=Math.min(W,H)/2-16;
  const svg=d3.select(el).append('svg').attr('width',W).attr('height',H);
  const g=svg.append('g').attr('transform',`translate(${W/2},${H/2})`);
  const pie=d3.pie().value(d=>d.value).sort(null);
  const arc=d3.arc().innerRadius(R*0.52).outerRadius(R);
  g.selectAll('path').data(pie(data)).join('path')
    .attr('d',arc).attr('fill',d=>d.data.color).attr('stroke','rgba(0,0,0,0.2)').attr('stroke-width',1);
  g.append('text').attr('text-anchor','middle').attr('dy','0.35em')
    .attr('fill','rgba(240,236,228,0.9)').style('font-size','15px').style('font-weight','600')
    .text(Math.round(activeCount/total*100)+'%');
  // legend below
  const leg=svg.append('g').attr('transform',`translate(${W/2},${H-6})`);
  data.forEach((d,i)=>{
    const lx=(i-0.5)*80;
    leg.append('circle').attr('cx',lx-12).attr('cy',-2).attr('r',5).attr('fill',d.color);
    leg.append('text').attr('x',lx-4).attr('y',2).attr('fill','rgba(240,236,228,0.6)').style('font-size','10px').text(`${d.label} (${d.value})`);
  });
}

function drawComplaintsChart(){
  const el=document.getElementById('chart-complaints');
  if(!el) return;
  el.innerHTML='';
  if(!issues.length){
    el.innerHTML='<div style="color:var(--text-dim);font-size:13px;text-align:center;padding:40px 0;">No issue reports yet.</div>';
    return;
  }
  const cats={};
  issues.forEach(i=>{ cats[i.category]=(cats[i.category]||0)+1; });
  const data=Object.entries(cats).map(([cat,count])=>({cat,count})).sort((a,b)=>b.count-a.count);
  const W=el.clientWidth||320, H=180;
  const m={top:10,right:20,bottom:36,left:Math.min(110,Math.max(...data.map(d=>d.cat.length))*7+10)};
  const w=W-m.left-m.right, h=H-m.top-m.bottom;
  const svg=d3.select(el).append('svg').attr('width',W).attr('height',H);
  const g=svg.append('g').attr('transform',`translate(${m.left},${m.top})`);
  const y=d3.scaleBand().domain(data.map(d=>d.cat)).range([0,h]).padding(0.3);
  const x=d3.scaleLinear().domain([0,d3.max(data,d=>d.count)+0.5]).range([0,w]);
  g.selectAll('rect').data(data).join('rect')
    .attr('x',0).attr('y',d=>y(d.cat))
    .attr('width',d=>x(d.count)).attr('height',y.bandwidth())
    .attr('fill','#7ab4f0').attr('rx',3);
  g.append('g').call(d3.axisLeft(y).tickSize(0))
    .call(ax=>{ax.select('.domain').remove();ax.selectAll('text').attr('fill','rgba(240,236,228,0.7)').style('font-size','10px').attr('dx','-4px');});
  g.append('g').attr('transform',`translate(0,${h})`).call(d3.axisBottom(x).ticks(3).tickFormat(d3.format('d')))
    .call(ax=>{ax.select('.domain').attr('stroke','rgba(255,255,255,0.15)');ax.selectAll('.tick line').remove();ax.selectAll('text').attr('fill','rgba(240,236,228,0.6)').style('font-size','11px');});
}

/* ══════════════ FINANCIAL RECORDS ══════════════ */
function openAddRecordModal(){
  const sel=document.getElementById('recResident');
  if(sel){
    sel.innerHTML='<option value="">— Select Resident —</option>'+
      residents.map(r=>`<option value="${r.id}">${r.name}${r.block_lot&&r.block_lot!=='—'?' ('+r.block_lot+')':''}</option>`).join('');
  }
  openModal('addFinRecord');
}
async function saveFinancialRecord(){
  const resident_id=document.getElementById('recResident').value;
  const record_type=document.getElementById('recType').value;
  const description=document.getElementById('recDesc').value.trim();
  const amount=parseFloat(document.getElementById('recAmount').value)||0;
  const record_date=document.getElementById('recDate').value;
  if(!resident_id){showToast('⚠ Please select a resident.');return;}
  if(!amount){showToast('⚠ Please enter an amount.');return;}
  if(!record_date){showToast('⚠ Please enter a date.');return;}
  const res=await apiPost('/api/financial',{resident_id,record_type,description,amount,record_date});
  if(res.success){
    closeModal('addFinRecord');
    ['recDesc','recAmount','recDate'].forEach(id=>document.getElementById(id).value='');
    const newFinData=await apiGet('/api/financial');
    finRecs=Array.isArray(newFinData)?newFinData:[];
    renderFinancialRecords(finRecs);
    const summary=await apiGet('/api/financial/summary');
    if(document.getElementById('finCollected')) document.getElementById('finCollected').textContent='₱'+Number(summary.total_collected||0).toLocaleString();
    if(document.getElementById('finDues'))      document.getElementById('finDues').textContent='₱'+Number(summary.total_dues||0).toLocaleString();
    if(document.getElementById('finBalance'))   document.getElementById('finBalance').textContent='₱'+Number(summary.total_balance||0).toLocaleString();
    if(document.getElementById('payCollected')) document.getElementById('payCollected').textContent='₱'+Number(summary.total_collected||0).toLocaleString();
    if(document.getElementById('payDues'))      document.getElementById('payDues').textContent='₱'+Number(summary.total_dues||0).toLocaleString();
    if(document.getElementById('payBalance'))   document.getElementById('payBalance').textContent='₱'+Number(summary.total_balance||0).toLocaleString();
    showToast('✅ Financial record added.');
  } else {
    showToast('⚠ '+(res.message||'Failed to save record.'));
  }
}

/* ══════════════ FINANCE / RECEIPTS ══════════════ */
let receiptCollections=[];
let receiptExpenses=[];
const savedReceipts=[];

async function loadReceipts(){
  const data=await apiGet('/api/financial-reports').catch(()=>[]);
  if(Array.isArray(data)&&data.length){
    savedReceipts.length=0;
    data.forEach(r=>savedReceipts.push({id:r.id,month:r.month,previous:r.previous_balance,collections:r.collections,expenses:r.expenses}));
    renderReceipts();
  }
}

function renderReceipts(){
  const container=document.getElementById('receiptContainer');
  container.querySelectorAll('.receipt').forEach(r=>r.remove());
  const addBtn=container.querySelector('.receipt-add-btn');
  savedReceipts.forEach(r=>{
    const totalCollect=r.collections.reduce((a,c)=>a+c.amt,0);
    const totalExpense=r.expenses.reduce((a,e)=>a+e.amt,0);
    const savings=r.previous+totalCollect-totalExpense;
    const el=document.createElement('div');
    el.className='receipt';
    el.innerHTML=`
      <div class="receipt-title">Financial Report</div>
      <div class="receipt-subtitle">${r.month}</div>
      <hr class="receipt-perforate">
      <div class="receipt-section-label">Previous Balance</div>
      <div class="receipt-line"><span class="r-label">Carried Over</span><span class="r-val">${fmt(r.previous)}</span></div>
      <div class="receipt-section-label">Collections</div>
      ${r.collections.map(c=>`<div class="receipt-line"><span class="r-label">${c.name}</span><span class="r-val positive">${fmt(c.amt)}</span></div>`).join('')}
      <div class="receipt-section-label">Expenses</div>
      ${r.expenses.map(e=>`<div class="receipt-line"><span class="r-label">${e.name}</span><span class="r-val negative">−${fmt(e.amt)}</span></div>`).join('')}
      <hr class="receipt-perforate">
      <div class="receipt-total">
        <div class="rt-label">Net Savings</div>
        <div class="rt-val">${fmt(savings)}</div>
      </div>
      ${r.id?`<button class="btn btn-sm btn-danger" style="margin-top:10px;width:100%;" onclick="deleteReceipt(${r.id})">Delete</button>`:''}`;
    container.insertBefore(el, addBtn);
  });
}

function updatePreview(){
  const month=document.getElementById('r-month').value;
  const prev=parseFloat(document.getElementById('r-prev').value)||0;
  document.getElementById('p-month').textContent=month?new Date(month+'-01').toLocaleDateString('en-PH',{month:'long',year:'numeric'}):'Month';
  document.getElementById('p-previous').textContent=fmt(prev);
  const totalC=receiptCollections.reduce((a,c)=>a+c.amt,0);
  const totalE=receiptExpenses.reduce((a,e)=>a+e.amt,0);
  document.getElementById('p-savings').textContent=fmt(prev+totalC-totalE);
  document.getElementById('p-collections').innerHTML=receiptCollections.length
    ?receiptCollections.map(c=>`<div class="receipt-line"><span class="r-label">${c.name}</span><span class="r-val positive">${fmt(c.amt)}</span></div>`).join('')
    :'<div class="receipt-line"><span class="r-label">—</span><span class="r-val">₱0</span></div>';
  document.getElementById('p-expenses').innerHTML=receiptExpenses.length
    ?receiptExpenses.map(e=>`<div class="receipt-line"><span class="r-label">${e.name}</span><span class="r-val negative">−${fmt(e.amt)}</span></div>`).join('')
    :'<div class="receipt-line"><span class="r-label">—</span><span class="r-val negative">₱0</span></div>';
}
function addReceiptLine(type){
  if(type==='collection'){
    const name=document.getElementById('c-name').value.trim();
    const amt=parseFloat(document.getElementById('c-amount').value)||0;
    if(!name){showToast('⚠ Enter a collection name.');return;}
    receiptCollections.push({name,amt});
    document.getElementById('c-name').value='';document.getElementById('c-amount').value='';
  } else {
    const name=document.getElementById('e-name').value.trim();
    const amt=parseFloat(document.getElementById('e-amount').value)||0;
    if(!name){showToast('⚠ Enter an expense name.');return;}
    receiptExpenses.push({name,amt});
    document.getElementById('e-name').value='';document.getElementById('e-amount').value='';
  }
  updatePreview();
}
async function saveReceipt(){
  const month=document.getElementById('r-month').value;
  const prev=parseFloat(document.getElementById('r-prev').value)||0;
  if(!month){showToast('⚠ Please select a month.');return;}
  const payload={
    month,
    previous_balance:prev,
    collections:[...receiptCollections],
    expenses:[...receiptExpenses]
  };
  const res=await apiPost('/api/financial-reports',payload);
  if(res.success){
    savedReceipts.unshift({id:res.report.id,month:new Date(month+'-01').toLocaleDateString('en-PH',{month:'long',year:'numeric'}),previous:prev,collections:[...receiptCollections],expenses:[...receiptExpenses]});
    receiptCollections=[];receiptExpenses=[];
    closeModal('addReceipt');
    renderReceipts();
    showToast('✅ Receipt saved.');
  } else {
    showToast('⚠ '+(res.message||'Failed to save receipt.'));
  }
}
async function deleteReceipt(id){
  const res=await apiDelete('/api/financial-reports/'+id);
  if(res.success){
    const idx=savedReceipts.findIndex(r=>r.id===id);
    if(idx>-1) savedReceipts.splice(idx,1);
    renderReceipts();
    showToast('✅ Receipt deleted.');
  }
}

/* Excel Upload — document-level guard prevents browser navigating to the file */
document.addEventListener('dragover',function(e){e.preventDefault();});
document.addEventListener('drop',function(e){
  e.preventDefault();
  const zone=document.getElementById('excelDropZone');
  if(!zone) return;
  const file=e.dataTransfer&&e.dataTransfer.files[0];
  if(!file) return;
  const ext=file.name.split('.').pop().toLowerCase();
  if(ext==='xlsx'||ext==='xls'){
    zone.style.borderColor='';
    processExcelFile(file);
  }
});
/* Drop zone highlight */
const _dz=document.getElementById('excelDropZone');
if(_dz){
  _dz.addEventListener('dragover',function(e){e.preventDefault();this.style.borderColor='var(--accent)';this.style.background='rgba(120,180,255,0.08)';});
  _dz.addEventListener('dragleave',function(){this.style.borderColor='';this.style.background='';});
  _dz.addEventListener('drop',function(e){e.preventDefault();this.style.borderColor='';this.style.background='';const f=e.dataTransfer&&e.dataTransfer.files[0];if(f) processExcelFile(f);});
}
function handleExcelDrop(e){
  e.preventDefault();
  document.getElementById('excelDropZone').style.borderColor='';
  const file=e.dataTransfer.files[0];
  if(!file) return;
  processExcelFile(file);
}
function uploadExcel(){
  const file=document.getElementById('excelFile').files[0];
  if(!file) return;
  processExcelFile(file);
  document.getElementById('excelFile').value='';
}
let pendingImportRows=[];
function processExcelFile(file){
  if(!window.XLSX){showToast('⚠ Excel library not loaded yet. Please try again.');return;}
  const fn=document.getElementById('excelFileName');
  if(fn) fn.textContent='Reading: '+file.name+'…';
  const reader=new FileReader();
  reader.onload=function(e){
    try{
      const wb=XLSX.read(e.target.result,{type:'array',cellDates:true});
      const ws=wb.Sheets[wb.SheetNames[0]];
      // Get all rows as arrays to find the actual header row (handles title/spacer rows)
      const allRows=XLSX.utils.sheet_to_json(ws,{header:1,defval:''});
      // Find the row index that contains 'resident_id' (case-insensitive)
      const headerIdx=allRows.findIndex(row=>row.some(cell=>cell!=null&&cell.toString().toLowerCase().trim()==='resident_id'));
      // If no header row found, fall back to row 0
      const startIdx=headerIdx>=0?headerIdx:0;
      const headers=allRows[startIdx].map(h=>h.toString().toLowerCase().trim().replace(/[^a-z0-9_]/g,'_'));
      const dataRows=allRows.slice(startIdx+1).filter(row=>row.some(c=>c!==''&&c!=null));
      const raw=dataRows.map(row=>{
        const obj={};
        headers.forEach((h,i)=>{ obj[h]=row[i]!==undefined?row[i]:''; });
        return obj;
      });
      if(!raw.length){showToast('⚠ No data rows found.');if(fn) fn.textContent='';return;}
      const rows=raw;
      const validTypes=['Due','Payment','Penalty','Adjustment'];
      pendingImportRows=[];
      const errors=[];
      rows.forEach((r,i)=>{
        const rowNum=i+1;
        const rid=parseInt(r['resident_id']||r['residentid']||r['id']);
        const rtype=(r['record_type']||r['recordtype']||r['type']||'').toString().trim();
        const desc=(r['description']||r['desc']||r['notes']||'').toString().trim();
        const amt=parseFloat(r['amount']||r['amt']||0);
        let rdate=r['record_date']||r['recorddate']||r['date']||'';
        if(rdate instanceof Date){const d=rdate;rdate=d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');}
        else{rdate=rdate.toString().trim();}
        if(!rid){errors.push('Row '+rowNum+': missing resident_id');return;}
        if(!validTypes.includes(rtype)){errors.push('Row '+rowNum+': invalid record_type "'+rtype+'" (must be Due/Payment/Penalty/Adjustment)');return;}
        if(!amt){errors.push('Row '+rowNum+': missing or zero amount');return;}
        if(!rdate){errors.push('Row '+rowNum+': missing record_date');return;}
        pendingImportRows.push({resident_id:rid,record_type:rtype,description:desc,amount:amt,record_date:rdate});
      });
      if(fn) fn.textContent=file.name+' — '+pendingImportRows.length+' valid row(s)'+(errors.length?' | '+errors.length+' skipped':'');
      renderImportPreview(pendingImportRows,errors);
    }catch(err){
      showToast('⚠ Could not read file: '+err.message);
      if(fn) fn.textContent='';
    }
  };
  reader.readAsArrayBuffer(file);
}
function renderImportPreview(rows,errors){
  const thead=document.querySelector('#excelTable thead tr');
  const tbody=document.querySelector('#excelTable tbody');
  if(thead) thead.innerHTML='<th>#</th><th>Resident ID</th><th>Type</th><th>Description</th><th style="text-align:right">Amount</th><th>Date</th><th>Status</th>';
  if(tbody) tbody.innerHTML=rows.map((r,i)=>`<tr data-import-row="${i}">
    <td>${i+1}</td>
    <td>${r.resident_id}</td>
    <td><span class="pill ${r.record_type==='Payment'?'pill-resolved':'pill-pending'}">${r.record_type}</span></td>
    <td>${r.description||'—'}</td>
    <td style="text-align:right">₱${Number(r.amount).toLocaleString()}</td>
    <td>${r.record_date}</td>
    <td class="import-status" style="color:var(--green);font-size:11px;">✓ Ready</td>
  </tr>`).join('')+
  (errors.length?errors.map(e=>`<tr><td colspan="7" style="color:var(--red,#f08080);font-size:11px;padding:4px 8px;">⚠ ${e}</td></tr>`).join(''):'');
  const card=document.querySelector('#excelTable').closest('.card');
  let bar=card.querySelector('.import-confirm-bar');
  if(!bar){
    bar=document.createElement('div');
    bar.className='import-confirm-bar';
    bar.style.cssText='display:flex;gap:10px;align-items:center;margin-top:12px;';
    card.appendChild(bar);
  }
  bar.innerHTML=rows.length
    ?`<button class="btn btn-green" onclick="confirmImport()">⬆ Import ${rows.length} Record${rows.length>1?'s':''}</button>
       <button class="btn" onclick="cancelImport()">Cancel</button>
       <span style="font-size:11px;color:var(--text-dim);">${errors.length?errors.length+' row(s) skipped due to errors':''}</span>`
    :`<span style="font-size:12px;color:var(--red,#f08080);">No valid rows to import.</span>
      <button class="btn" onclick="cancelImport()">Clear</button>`;
}
async function confirmImport(){
  if(!pendingImportRows.length){showToast('⚠ Nothing to import.');return;}
  const btn=document.querySelector('.import-confirm-bar .btn-green');
  if(btn){btn.disabled=true;btn.textContent='Importing…';}
  const tbody=document.querySelector('#excelTable tbody');
  const trs=tbody?[...tbody.querySelectorAll('tr[data-import-row]')]:[];
  let ok=0,fail=0;
  const results=[];
  for(let i=0;i<pendingImportRows.length;i++){
    const row=pendingImportRows[i];
    const res=await apiPost('/api/financial',row);
    const tr=trs[i];
    if(res&&res.success){
      ok++;
      results.push({ok:true});
      if(tr)tr.querySelector('.import-status').innerHTML='<span style="color:var(--green);">✓ Saved</span>';
    } else {
      fail++;
      const msg=res.message||(res.errors?Object.values(res.errors).flat()[0]:'Failed');
      results.push({ok:false,msg});
      if(tr)tr.querySelector('.import-status').innerHTML='<span style="color:var(--red,#f08080);font-size:11px;">✗ '+msg+'</span>';
    }
  }
  pendingImportRows=pendingImportRows.filter((_,i)=>!results[i].ok);
  const newFinData=await apiGet('/api/financial');
  finRecs=Array.isArray(newFinData)?newFinData:[];
  const summary=await apiGet('/api/financial/summary');
  if(document.getElementById('finCollected')) document.getElementById('finCollected').textContent='₱'+Number(summary.total_collected||0).toLocaleString();
  if(document.getElementById('finDues'))      document.getElementById('finDues').textContent='₱'+Number(summary.total_dues||0).toLocaleString();
  if(document.getElementById('finBalance'))   document.getElementById('finBalance').textContent='₱'+Number(summary.total_balance||0).toLocaleString();
  if(document.getElementById('payCollected')) document.getElementById('payCollected').textContent='₱'+Number(summary.total_collected||0).toLocaleString();
  if(document.getElementById('payDues'))      document.getElementById('payDues').textContent='₱'+Number(summary.total_dues||0).toLocaleString();
  if(document.getElementById('payBalance'))   document.getElementById('payBalance').textContent='₱'+Number(summary.total_balance||0).toLocaleString();
  const bar=document.querySelector('.import-confirm-bar');
  if(bar){
    if(fail>0){
      bar.innerHTML=`<span style="font-size:12px;color:var(--green)">✅ ${ok} imported</span>
        <span style="font-size:12px;color:var(--red,#f08080);">⚠ ${fail} failed — fix errors above then re-import</span>
        <button class="btn" onclick="cancelImport()">Clear</button>`;
      renderFinancialRecords(finRecs);
    } else {
      cancelImport();
    }
  }
  showToast(ok>0?'✅ Imported '+ok+' record'+(ok>1?'s':'')+(fail>0?' | '+fail+' failed (see table)':'')+'.':(fail>0?'⚠ All '+fail+' records failed. Check the error details in the table.':''));
}
function cancelImport(){
  pendingImportRows=[];
  const bar=document.querySelector('.import-confirm-bar');
  if(bar) bar.remove();
  const fn=document.getElementById('excelFileName');
  if(fn) fn.textContent='';
  renderFinancialRecords(finRecs);
}
async function adminSend(){
  const inp=document.getElementById('msgInput');
  const txt=inp.value.trim(); if(!txt||!currentThread) return;
  inp.value='';
  const res=await apiPost('/api/messages/'+currentThread,{content:txt});
  if(res.success){
    if(!threads[currentThread].msgs) threads[currentThread].msgs=[];
    threads[currentThread].msgs.push(res.message);
    threads[currentThread].last_message=txt;
    renderChat();
    renderThreads();
  }
}
/* ══ CSV EXPORTS ══ */
function exportCSV(rows, filename){
  const csv=rows.map(r=>r.map(v=>'"'+String(v).replace(/"/g,'""')+'"').join(',')).join('\n');
  const a=document.createElement('a');
  a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(csv);
  a.download=filename; a.click();
}
/* ══ FINANCIAL RECORDS — delete, filter, export ══ */
async function deleteFinRecord(id){
  if(!confirm('Delete this financial record? The resident balance will be adjusted.')) return;
  const res=await apiDelete('/api/financial/'+id);
  if(res&&res.success){
    finRecs=finRecs.filter(r=>r.id!==id);
    renderFinancialRecords(finRecs);
    filterFinRecords();
    const summary=await apiGet('/api/financial/summary');
    if(document.getElementById('finCollected')) document.getElementById('finCollected').textContent='₱'+Number(summary.total_collected||0).toLocaleString();
    if(document.getElementById('finDues'))      document.getElementById('finDues').textContent='₱'+Number(summary.total_dues||0).toLocaleString();
    if(document.getElementById('finBalance'))   document.getElementById('finBalance').textContent='₱'+Number(summary.total_balance||0).toLocaleString();
    if(document.getElementById('payCollected')) document.getElementById('payCollected').textContent='₱'+Number(summary.total_collected||0).toLocaleString();
    if(document.getElementById('payDues'))      document.getElementById('payDues').textContent='₱'+Number(summary.total_dues||0).toLocaleString();
    if(document.getElementById('payBalance'))   document.getElementById('payBalance').textContent='₱'+Number(summary.total_balance||0).toLocaleString();
    showToast('✅ Record deleted and balance adjusted.');
  } else {
    showToast('⚠ '+(res&&res.message||'Failed to delete record.'));
  }
}
function filterFinRecords(){
  const q=(document.getElementById('finSearch')||{}).value?.toLowerCase()||'';
  const t=(document.getElementById('finTypeFilter')||{}).value||'';
  document.querySelectorAll('#excelTable tbody tr[data-res]').forEach(row=>{
    const matchQ=!q||row.dataset.res.includes(q);
    const matchT=!t||row.dataset.type===t;
    row.style.display=(matchQ&&matchT)?'':'none';
  });
}
function exportFinRecordsCSV(){
  if(!finRecs.length){showToast('⚠ No records to export.');return;}
  const rows=[['Resident','Block/Lot','Type','Description','Amount','Date'],
    ...finRecs.map(r=>[r.resident,r.block_lot,r.record_type,r.description||'',r.amount,r.record_date])];
  exportCSV(rows,'financial_records_'+new Date().toISOString().slice(0,10)+'.csv');
  showToast('✅ Financial records exported.');
}
/* ══ PAYMENTS — filter ══ */
function filterPayments(){
  const q=(document.getElementById('paySearch')||{}).value?.toLowerCase()||'';
  const s=(document.getElementById('payStatusFilter')||{}).value||'';
  const payLabel=bal=>bal<=0?'Paid':(bal<1000?'Partial':'Unpaid');
  document.querySelectorAll('#paymentList .payment-row').forEach(row=>{
    const name=row.querySelector('.pay-name')?.textContent.toLowerCase()||'';
    const block=row.querySelector('.pay-block')?.textContent.toLowerCase()||'';
    const pill=row.querySelector('.pill')?.textContent||'';
    const matchQ=!q||(name.includes(q)||block.includes(q));
    const matchS=!s||pill===s;
    row.style.display=(matchQ&&matchS)?'':'none';
  });
}
function exportPaymentsCSV(){
  if(!payments.length){showToast('⚠ No payment data.');return;}
  const rows=[['Name','Block & Lot','Balance','Status'],
    ...payments.map(r=>[r.name,r.block_lot,r.current_balance,r.current_balance<=0?'Paid':(r.current_balance<1000?'Partial':'Unpaid')])];
  exportCSV(rows,'payments_'+new Date().toISOString().slice(0,10)+'.csv');
  showToast('✅ Payments exported.');
}
function exportIssuesCSV(){
  if(!issues.length){showToast('⚠ No issue data.');return;}
  const rows=[['ID','Title','Category','Resident','Block','Status','Date'],
    ...issues.map(i=>[i.id,i.title,i.category,i.resident,i.block_lot,i.status,i.created_at])];
  exportCSV(rows,'issues_'+new Date().toISOString().slice(0,10)+'.csv');
  showToast('✅ Issues exported.');
}
function exportReceiptsCSV(){
  if(!savedReceipts.length){showToast('⚠ No receipts.');return;}
  const rows=[['Month','Previous Balance','Total Collections','Total Expenses','Net']];
  savedReceipts.forEach(r=>{
    const tc=r.collections.reduce((a,c)=>a+Number(c.amt),0);
    const te=r.expenses.reduce((a,e)=>a+Number(e.amt),0);
    rows.push([r.month,r.previous,tc,te,r.previous+tc-te]);
  });
  exportCSV(rows,'receipts_'+new Date().toISOString().slice(0,10)+'.csv');
  showToast('✅ Receipts exported.');
}

/* ══ PAYMENT DETAIL MODAL ══ */
async function openPaymentDetail(residentId){
  const r=payments.find(x=>x.id===residentId);
  if(!r) return;
  const records=await apiGet('/api/financial').catch(()=>[]);
  const mine=Array.isArray(records)?records.filter(rec=>rec.resident_id===residentId):[];
  const body=mine.length
    ?mine.map(rec=>`<div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:13px;">
        <span>${rec.description||rec.record_type} <span style="font-size:10px;color:var(--text-dim);">${rec.record_date?new Date(rec.record_date).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}):''}</span></span>
        <span style="color:${rec.record_type==='Payment'?'var(--green)':'#f08080'};font-weight:600;">
          ${rec.record_type==='Payment'?'-':'+'}&#8369;${Number(rec.amount).toLocaleString()}
        </span></div>`).join('')
    :'<div style="color:var(--text-dim);padding:12px 0;">No financial records yet.</div>';
  const el=document.getElementById('payDetailBody');
  if(el){
    el.innerHTML=`<div style="font-weight:600;margin-bottom:6px;">${r.name}</div>
      <div style="font-size:11px;color:var(--text-dim);margin-bottom:12px;">${r.block_lot} &mdash; Balance: &#8369;${Number(r.current_balance).toLocaleString()}</div>${body}`;
  }
  openModal('payDetail');
}

/* ══ HEATMAP WEIGHT ══ */
// Each issue contributes a flat weight of 1 — heat intensity reflects issue frequency only.
// max:4 means 4+ overlapping issues → full red; fewer issues → cooler colours.
function heatWeight(issue) {
  return 1.0;
}

/* ══════════════ ANALYTICS HEATMAP (MINI MAP) ══════════════ */
function renderAnalyticsHeatMap(){
  const el = document.getElementById('analytics-heat-map');
  if (!el) return;
  if (analyticsMapInst) { setTimeout(() => analyticsMapInst.invalidateSize(), 100); return; }
  analyticsMapInst = L.map('analytics-heat-map', {
    zoomControl: false, attributionControl: false
  }).setView(MAP_CENTER, 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 20}).addTo(analyticsMapInst);
  // Use all issues with lat/lng (including from module-level issues array)
  const pts = issues
    .filter(i => i.latitude && i.longitude)
    .map(i => [parseFloat(i.latitude), parseFloat(i.longitude), heatWeight(i)]);
  if (pts.length > 0) {
    L.heatLayer(pts, {radius: 35, blur: 25, maxZoom: 18, max: 1.5, gradient: {0.4:'#0000ff', 0.65:'#00ff00', 0.85:'#ffff00', 1:'#ff0000'}}).addTo(analyticsMapInst);
  } else {
    // Show message overlay when no geo-tagged issues exist
    el.insertAdjacentHTML('beforeend',
      '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.45);color:rgba(240,236,228,0.7);font-size:13px;z-index:500;">No geo-tagged issues yet</div>'
    );
  }
  setTimeout(() => analyticsMapInst.invalidateSize(), 150);
}

/* ══════════════ INIT ══════════════ */
const ADMIN_ID = {{ $adminId ?? 'null' }};
const ADMIN_NAME = @json($adminName);

/* — Live polling: officer file uploads — */
let lastOfficerFileCount = -1; // -1 = baseline not yet set
let newOfficerFileCount  = 0;

async function pollOfficerFiles(){
  try {
    const data = await apiGet('/api/officer-files');
    if(!Array.isArray(data)) return;
    const count = data.length;
    if(lastOfficerFileCount === -1){ lastOfficerFileCount = count; return; }
    if(count > lastOfficerFileCount){
      const added = count - lastOfficerFileCount;
      newOfficerFileCount += added;
      lastOfficerFileCount = count;
      // Update badge on nav item
      const badge = document.getElementById('reportsBadge');
      if(badge){
        badge.textContent = newOfficerFileCount;
        badge.style.display = '';
      }
      // If reports panel is open, refresh the list silently
      const panel = document.getElementById('panel-reports');
      if(panel && panel.classList.contains('active')){
        newOfficerFileCount = 0;
        if(badge){ badge.style.display = 'none'; }
        renderReports();
      }
      // Toast notification
      const latest = data[0];
      showToast(`📁 New file from ${latest.officer_name||'officer'}: ${latest.original_name||'report'}`);
    }
  } catch(e){}
}

// Clear badge when admin opens Reports panel
const _origNav = window.nav;
window.nav = function(name, el){
  _origNav(name, el);
  if(name === 'reports'){
    newOfficerFileCount = 0;
    const badge = document.getElementById('reportsBadge');
    if(badge){ badge.style.display = 'none'; }
  }
};

setInterval(pollOfficerFiles, 30000);

// Fast poll: check for critical/high unresolved issues every 5s
let _lastUrgentId = null;
function dismissEmergency(){ document.getElementById('emergencyAlert').style.display='none'; }
setInterval(async ()=>{
  if(document.hidden) return;
  try{
    const u = await apiGet('/api/issues/urgent-check');
    if(u.count > 0 && u.latest_id !== _lastUrgentId){
      _lastUrgentId = u.latest_id;
      const el = document.getElementById('emergencyAlert');
      const txt = document.getElementById('emergencyAlertText');
      if(txt && u.latest) txt.textContent = `🚨 CRITICAL ISSUE — "${u.latest.title}" by ${u.latest.resident}`;
      if(el) el.style.display = 'block';
      // also immediately refresh issues list
      const d = await apiGet('/api/dashboard-data');
      issues = d.issues||[];
      renderIssues();
      renderDashboard();
      const openCount = issues.filter(i=>i.status!=='Resolved').length;
      if(document.getElementById('statIssues')) document.getElementById('statIssues').textContent = openCount;
      if(document.getElementById('issueBadge')) document.getElementById('issueBadge').textContent = openCount;
    }
  }catch(e){}
}, 5000);
setInterval(async ()=>{
  if(document.hidden || !currentThread) return;
  if(!document.getElementById('panel-messages')?.classList.contains('active')) return;
  try{
    const msgs = await apiGet('/api/messages/'+currentThread);
    if(msgs.length !== (threads[currentThread]?.msgs||[]).length){
      threads[currentThread].msgs = msgs;
      openThread(currentThread);
    }
  }catch(e){}
}, 10000);

// Poll for new issues + recommendations every 30s (resident submissions)
setInterval(async ()=>{
  if(document.hidden) return;
  try{
    const d = await apiGet('/api/dashboard-data');
    const newIssues = d.issues||[];
    const newRecs   = d.recommendations||[];
    const issueCountChanged = newIssues.length !== issues.length;
    const recCountChanged   = newRecs.length   !== recommendations.length;
    if(issueCountChanged || recCountChanged){
      issues          = newIssues;
      recommendations = newRecs;
      renderIssues();
      renderRecommendations();
      renderDashboard();
      const openCount = issues.filter(i=>i.status!=='Resolved').length;
      if(document.getElementById('statIssues'))  document.getElementById('statIssues').textContent  = openCount;
      if(document.getElementById('issueBadge'))  document.getElementById('issueBadge').textContent  = openCount;
    }
  }catch(e){}
}, 30000);

loadAllData();
loadReceipts();
renderReceipts();
</script>

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
    <div class="f-row"><div class="f-field"><span class="f-label">Status</span>
      <select id="edit-fac-status"><option value="Active">Active</option><option value="Inactive">Inactive</option></select>
    </div></div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('editFacility')">Cancel</button>
      <button class="btn btn-sm" onclick="saveEditFacility()">Save Changes</button>
    </div>
  </div>
</div>

<script src="/js/d3.v7.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
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
const MAP_CENTER = [10.62269, 122.96134];
let mapInst = null, layerHouseholds, layerFacilities, layerIssues, layerHeatmap;
let analyticsMapInst = null;
let addFacilityMode = false, pendingPin = null;
let editFacilityId = null;

function makeCircleIcon(color) {
  return L.divIcon({
    className: '',
    html: `<div style="width:13px;height:13px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.85);box-shadow:0 1px 5px rgba(0,0,0,0.5);"></div>`,
    iconSize: [13, 13], iconAnchor: [6, 6], popupAnchor: [0, -9]
  });
}

function initMap() {
  if (mapInst) { setTimeout(() => mapInst.invalidateSize(), 100); return; }
  mapInst = L.map('map-container').setView(MAP_CENTER, 17);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 20,
  }).addTo(mapInst);

  layerHouseholds = L.layerGroup().addTo(mapInst);
  layerFacilities = L.layerGroup().addTo(mapInst);
  layerIssues     = L.layerGroup().addTo(mapInst);
  layerHeatmap    = L.heatLayer([], {
    radius: 42, blur: 32, maxZoom: 17, max: 4, minOpacity: 0.0,
    gradient: {
      0.15: '#1a003e',   // dark indigo — sparse
      0.35: '#5c0099',   // deep purple
      0.5:  '#0044bb',   // cool blue
      0.65: '#cc4400',   // amber — warming
      0.82: '#cc1400',   // orange-red
      1.0:  '#ff2200'    // hot red — dense cluster
    }
  }).addTo(mapInst);
  // tone down canvas so map tiles stay readable beneath the heat
  setTimeout(() => { if (layerHeatmap._canvas) layerHeatmap._canvas.style.opacity = '0.70'; }, 150);

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
  document.getElementById('layer-heatmap').addEventListener('change', function() {
    this.checked ? mapInst.addLayer(layerHeatmap) : mapInst.removeLayer(layerHeatmap);
  });
}

function loadMapData() {
  fetch('/api/map/households').then(r => r.json()).then(data => {
    layerHouseholds.clearLayers();
    data.forEach(h => {
      const color = h.status === 'Delinquent' ? '#f08080' : '#4287f5';
      L.marker([parseFloat(h.latitude), parseFloat(h.longitude)], {icon: makeCircleIcon(color)})
        .bindPopup(`<strong>${h.block_lot_number}</strong><br>Status: ${h.status}`)
        .addTo(layerHouseholds);
    });
  });

  fetch('/api/map/facilities').then(r => r.json()).then(data => {
    layerFacilities.clearLayers();
    data.forEach(f => {
      L.marker([parseFloat(f.latitude), parseFloat(f.longitude)], {icon: makeCircleIcon('#4CAF50')})
        .bindPopup(
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
    data.forEach(i => {
      const color = i.status === 'Resolved' ? '#888' : (i.status === 'In Progress' ? '#f5a623' : '#e05555');
      L.marker([parseFloat(i.latitude), parseFloat(i.longitude)], {icon: makeCircleIcon(color)})
        .bindPopup(`<strong>${i.title}</strong><br>${i.category}<br><small>Status: ${i.status}</small>`)
        .addTo(layerIssues);
    });
    if (layerHeatmap) {
      layerHeatmap.setLatLngs(data.map(i => [parseFloat(i.latitude), parseFloat(i.longitude), heatWeight(i)]));
    }
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

function saveNewFacility() {
  const name = document.getElementById('fac-name').value.trim();
  const lat  = document.getElementById('fac-lat').value;
  const lng  = document.getElementById('fac-lng').value;
  if (!name) { showToast('Please enter a facility name.'); return; }
  if (!lat || !lng) { showToast('Click the map to pin a location first.'); return; }

  fetch('/api/map/facility', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
    body: JSON.stringify({ name, description: document.getElementById('fac-desc').value.trim(), latitude: lat, longitude: lng }),
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) {
      cancelAddFacilityMode();
      closeModal('addFacility');
      document.getElementById('fac-name').value = '';
      document.getElementById('fac-desc').value = '';
      loadMapData();
      showToast('Facility pinned!');
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
    openModal('editFacility');
  });
}

function saveEditFacility() {
  fetch(`/api/map/facility/${editFacilityId}`, {
    method: 'PUT',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
    body: JSON.stringify({
      name:        document.getElementById('edit-fac-name').value.trim(),
      description: document.getElementById('edit-fac-desc').value.trim(),
      status:      document.getElementById('edit-fac-status').value,
    }),
  })
  .then(r => r.json())
  .then(d => { if (d.success) { closeModal('editFacility'); loadMapData(); showToast('Facility updated.'); } });
}

function deleteFacilityById(id) {
  if (!confirm('Delete this facility?')) return;
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
        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:4px;">Payment</div>
        <div id="resD-payStatus" style="font-size:13px;font-weight:600;">—</div>
      </div>
    </div>
    <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:14px;margin-bottom:14px;text-align:center;">
      <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:4px;">Outstanding Balance</div>
      <div id="resD-balance" style="font-size:22px;font-weight:700;">₱0</div>
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
      <button class="btn btn-sm" id="resD-viewPayBtn">View Payments →</button>
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
    <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:14px;margin-bottom:14px;">
      <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:6px;">Description</div>
      <div id="issD-desc" style="font-size:13px;line-height:1.6;white-space:pre-wrap;"></div>
    </div>
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

<!-- Delinquent Detail Modal -->
<div class="modal-overlay" id="modal-delinquent">
  <div class="modal-box wide" style="max-width:520px;">
    <div class="modal-title" style="display:flex;align-items:center;gap:10px;">
      <span style="font-size:22px;">⚠️</span>
      <span>Delinquent Household — <span id="delin-modal-block" style="color:#f08080;">—</span></span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px;">
      <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:14px;">
        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:4px;">Reason Flagged</div>
        <div id="delin-modal-reason" style="font-size:13px;color:#f08080;font-weight:600;">—</div>
      </div>
      <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:14px;">
        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:4px;">Date Flagged</div>
        <div id="delin-modal-flagged" style="font-size:13px;font-weight:600;">—</div>
      </div>
    </div>

    <div style="background:rgba(240,128,128,0.08);border:1px solid rgba(240,128,128,0.25);border-radius:10px;padding:14px;margin-bottom:18px;text-align:center;">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:4px;">Total Outstanding Balance</div>
      <div id="delin-modal-total" style="font-size:22px;font-weight:700;color:#f08080;">₱0</div>
    </div>

    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:10px;">Residents in Household</div>
    <div id="delin-modal-residents" style="max-height:260px;overflow-y:auto;"></div>

    <div class="modal-actions" style="margin-top:20px;">
      <button class="btn btn-sm" onclick="closeDelinquent()">Close</button>
    </div>
  </div>
</div>
</body>
</html>
