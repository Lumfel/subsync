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
  --sidebar-w: 230px;
}
*{ box-sizing:border-box; margin:0; padding:0; }
body {
  font-family: 'DM Sans', sans-serif;
  background:
    radial-gradient(ellipse 80% 60% at 70% 10%, rgba(80,50,30,0.42), transparent),
    radial-gradient(ellipse 60% 50% at 20% 80%, rgba(30,30,60,0.48), transparent),
    linear-gradient(160deg, #0b0b14 0%, #12101a 40%, #1a120d 100%);
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
  background: rgba(0,0,0,0.55);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
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
  filter: drop-shadow(0 2px 8px rgba(0,0,0,0.4));
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
  background: rgba(0,0,0,0.22);
  backdrop-filter: blur(8px);
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
}
.stat-card:hover { background: var(--glass-hover); }
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
  background:rgba(0,0,0,0.28);
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
.thread-avatar { width:34px; height:34px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; }
.thread-info { flex:1; min-width:0; }
.thread-name { font-size:12px; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.thread-preview { font-size:11px; color:var(--text-dim); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.thread-unread { min-width:18px; height:18px; border-radius:9px; background:var(--accent); color:#1a100a; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; padding:0 4px; flex-shrink:0; }
.msg-main { flex:1; display:flex; flex-direction:column; min-width:0; }
.msg-header { padding:12px 16px; display:flex; align-items:center; gap:10px; background:rgba(0,0,0,0.22); border-bottom:1px solid var(--glass-border); }
.msg-header-avatar { width:34px; height:34px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; }
.msg-header-name { font-size:14px; font-weight:500; }
.msg-header-sub { font-size:11px; color:var(--green); display:flex; align-items:center; gap:4px; }
.msg-header-sub::before { content:''; width:6px; height:6px; border-radius:50%; background:var(--green); display:inline-block; }
.msg-body { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:10px; scrollbar-width:thin; scrollbar-color:rgba(255,255,255,.1) transparent; }
.msg-bubble-wrap { display:flex; align-items:flex-end; gap:8px; }
.msg-bubble-wrap.mine { flex-direction:row-reverse; }
.bubble-avatar { width:26px; height:26px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; }
.bubble { max-width:68%; padding:10px 14px; border-radius:14px; font-size:13px; line-height:1.55; }
.from-admin { background:rgba(255,255,255,0.09); border:1px solid var(--glass-border); border-bottom-left-radius:4px; }
.from-mine { background:var(--accent-dim); border:1px solid var(--accent-border); border-bottom-right-radius:4px; }
.bubble-time { font-size:10px; color:var(--text-dim); margin-top:3px; display:block; }
.msg-bubble-wrap.mine .bubble-time { text-align:right; }
.date-divider { display:flex; align-items:center; gap:10px; font-size:10px; color:var(--text-dim); letter-spacing:.06em; text-transform:uppercase; }
.date-divider::before,.date-divider::after { content:''; flex:1; height:1px; background:var(--glass-border); }
.msg-compose { padding:12px; display:flex; gap:10px; align-items:flex-end; border-top:1px solid var(--glass-border); background:rgba(0,0,0,0.18); }
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
@media(max-width:768px){
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
}
</style>
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
 <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo">
      <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzNzYuMTYgNDk2Ljg2Ij4KICA8ZGVmcz4KICAgIDxzdHlsZT4KICAgICAgLmNscy0xLCAuY2xzLTIgewogICAgICAgIGZpbGw6ICNmZmY7CiAgICAgIH0KCiAgICAgIC5jbHMtMiB7CiAgICAgICAgZm9udC1mYW1pbHk6IFN1Z2FyQmFieS1SZWd1bGFyLCAnU3VnYXIgQmFieSc7CiAgICAgICAgZm9udC1zaXplOiAxMDguMTZweDsKICAgICAgfQoKICAgICAgLmNscy0zIHsKICAgICAgICBsZXR0ZXItc3BhY2luZzogLS4wNGVtOwogICAgICB9CiAgICA8L3N0eWxlPgogIDwvZGVmcz4KICA8cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik03Ny4wOSwyNzkuODloMTk3LjY2YzMuODYsMCwzLjg3LTYsMC02SDc3LjA5Yy0zLjg2LDAtMy44Nyw2LDAsNmgwWiIvPgogIDxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTTU4LjA3LDM0Mi41N3YtODcuMDNsLTMsM2M3NC4zMiwwLDE0OC42NCwwLDIyMi45NiwwLDUuNCwwLDEwLjgxLDAsMTYuMjEsMCwzLjg2LDAsMy44Ny02LDAtNi01NC4wNCwwLTEwOC4wOSwwLTE2Mi4xMywwLTI1LjY4LDAtNTEuMzYsMC03Ny4wNCwwLTEuNjIsMC0zLDEuMzctMywzdjg3LjAzYzAsMy44Niw2LDMuODcsNiwwaDBaIi8+CiAgPHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMjcxLjc1LDI3Ni44OWMwLDIyLjc3LDAsNDUuNTUsMCw2OC4zMiwwLDMuODYsNiwzLjg3LDYsMCwwLTIyLjc3LDAtNDUuNTUsMC02OC4zMiwwLTMuODYtNi0zLjg3LTYsMGgwWiIvPgogIDxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTTE4MS43LDE0MS43MmMwLTE0LjksMC0yOS44LDAtNDQuN2wtMy44NCwxLjU5YzM2LjMyLDI4LjgzLDcyLjYzLDU3LjY3LDEwOC45NSw4Ni41bDI4LjAyLDIyLjI0YzIuMDYsMS42NCw0LjI3LDMuMTgsNi4yMyw0Ljk0LDIuNTEsMi4yNywyLjQ1LDMuNDgsMi40NSw2LjgzdjE2Ni42NmMwLDIuOSw0LjUsMi45LDQuNSwwVjIxNy4xNGMwLTEuOTcsLjM2LTMuODUtLjY2LTUuNTYtMS4wOC0xLjgyLTQuNTMtMy42LTYuMjMtNC45NGwtNi4yMy00Ljk0Yy00LjkzLTMuOTEtOS44Ni03LjgzLTE0Ljc5LTExLjc0bC01Ni4wMy00NC40OWMtMjEuMDEtMTYuNjgtNDIuMDItMzMuMzYtNjMuMDMtNTAuMDUtMS40Ni0xLjE2LTMuODQtLjUyLTMuODQsMS41OSwwLDE0LjksMCwyOS44LDAsNDQuNywwLDIuOSw0LjUsMi45LDQuNSwwaDBaIi8+CiAgPHRleHQgY2xhc3M9ImNscy0yIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDQzNS4zNSkiPjx0c3BhbiB4PSIwIiB5PSIwIj5TdWI8L3RzcGFuPjx0c3BhbiBjbGFzcz0iY2xzLTMiIHg9IjE3NC4wMiIgeT0iMCI+czwvdHNwYW4+PHRzcGFuIHg9IjIxNC44IiB5PSIwIj55bmM8L3RzcGFuPjwvdGV4dD4KICA8cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xMzQuNCwxNTcuMjljMTUuNDMtNi42MywyNy4yNi0xOS41MiwyNy4yOS0zOC41NywwLTUwLjM3LTg3LjQyLTQ5LjAyLTg3LjQyLTg5LDAtMTUuMTMsMTIuODgtMjYuMiwzMi41My0yNi4yczMyLjc2LDExLjUyLDMyLjc2LDI3Ljc4YzAsNC45NywyLjk0LDguMTMsNy42OCw4LjEzLDUuODcsMCwxMC4zOS00LjUyLDEwLjM5LTExLjUyLTQuNDUtMzkuOTQtMTAyLjk2LTM4LjE2LTEwMi41NiwxMi42NSwwLDIwLjAyLDE1LjczLDMxLjM5LDMzLjg0LDQxLjA0LTE2LjQyLDUuOS0yOC42NSwxOC42LTI4LjY1LDM2LjksMCw0Ny4yMSw4MS40NSw0Ni4zMSw4MS40NSw4OC4zMiwwLDE3Ljg0LTE0LjA5LDMwLjI3LTMxLjE1LDMwLjI3LTI1LjY4LDAtNDIuNTEtMjcuNzgtMzEuNTYtNTUuNzksMS42OC00LjI5LS42My04LjgxLTYuNTMtOC44MS03Ljc5LDAtMTMuMjYsNy45MS0xMy4yNiwyMS4yMywwLDI2LjIsMjIuMzEsNDYuNzYsNTAuNTEsNDYuNzYsMjMuOTksMCw0OS44Ny0xNC42OCw0OS44Ny00My44MiwwLTE4LjY0LTExLjE0LTMwLjE5LTI1LjE5LTM5LjM4Wm0tNTYuMjYtNDkuNjJjMC0xMC42Myw1LjkyLTE5LjI1LDE1Ljc5LTIzLjQ0LDIzLjM0LDExLjkxLDQ4LjU2LDIyLjA5LDQ4LjU2LDQ0LjY2LDAsMTAuNzYtNS41LDE5LjU1LTEzLjg3LDI0LjgyLTIzLjAzLTEzLjU1LTUwLjQ4LTIyLjgzLTUwLjQ4LTQ2LjA0WiIvPgogIDxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTTE3Ny40NSwxMTEuOTJjMCw0Ny45OSwwLDk1Ljk4LDAsMTQzLjk3LDAsMi45LDQuNSwyLjksNC41LDAsMC00Ny45OSwwLTk1Ljk4LDAtMTQzLjk3LDAtMi45LTQuNS0yLjktNC41LDBoMFoiLz4KPC9zdmc+" alt="SubSync">
    </div>
    <div class="brand-sub">Terra Nova · Admin Portal</div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Overview</div>
    <div class="nav-item active" onclick="nav('dashboard',this)"><span class="nav-icon">📊</span> Dashboard</div>

    <div class="nav-section-label">Residents</div>
    <div class="nav-item" onclick="nav('residents',this)"><span class="nav-icon">👥</span> Residents</div>
    <div class="nav-item" onclick="nav('members',this)"><span class="nav-icon">👤</span> Members</div>
    <div class="nav-item" onclick="nav('delinquents',this)"><span class="nav-icon">⚠️</span> Delinquents <span class="nav-badge" id="delinBadge">0</span></div>

    <div class="nav-section-label">Finance</div>
    <div class="nav-item" onclick="nav('finance',this)"><span class="nav-icon">💳</span> Finance</div>
    <div class="nav-item" onclick="nav('payments',this)"><span class="nav-icon">₱</span> Payments</div>

    <div class="nav-section-label">Communication</div>
    <div class="nav-item" onclick="nav('messages',this)"><span class="nav-icon">💬</span> Messages <span class="nav-badge" id="msgBadge">5</span></div>
    <div class="nav-item" onclick="nav('announcements',this)"><span class="nav-icon">📢</span> Announcements</div>

    <div class="nav-section-label">Management</div>
    <div class="nav-item" onclick="nav('issues',this)"><span class="nav-icon">🔧</span> Issue Reports <span class="nav-badge" id="issueBadge">0</span></div>
    <div class="nav-item" onclick="nav('manageusers',this)"><span class="nav-icon">⚙️</span> Manage Users</div>
    <div class="nav-item" onclick="nav('officers',this)"><span class="nav-icon">🛡️</span> Officers</div>
    <div class="nav-item" onclick="nav('recommendations',this)"><span class="nav-icon">💡</span> Recommendations</div>
    <div class="nav-item" onclick="nav('analytics',this)"><span class="nav-icon">📈</span> Analytics</div>
    <div class="nav-item" onclick="nav('mapping',this)"><span class="nav-icon">🗺️</span> Maps</div>
    <div class="nav-item" onclick="nav('reports',this)"><span class="nav-icon">📁</span> Reports</div>
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
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
   <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()">☰</button>
      <div class="topbar-title" id="topbarTitle">Dashboard Overview</div>
    </div>
    <div class="topbar-actions">
      <button class="topbar-btn" onclick="showToast('🔔 No new system alerts.')">🔔 Alerts</button>
      <button class="topbar-btn primary" onclick="openModal('ann')">+ New Announcement</button>
    </div>
  </div>

  <div class="content">

    <!-- ══════════ DASHBOARD ══════════ -->
    <div class="panel active" id="panel-dashboard">
      <div class="stat-grid">
        <div class="stat-card">
          <div class="stat-label">Total Residents</div>
          <div class="stat-value stat-accent" id="statResidents">—</div>
          <div class="stat-sub">Blk 1–5 registered</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Active Residents</div>
          <div class="stat-value stat-blue" id="statActive">—</div>
          <div class="stat-sub">Currently active</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Payment Collection</div>
          <div class="stat-value stat-green" id="statPayPct">—</div>
          <div class="stat-sub" id="statPaySub">May 2026 dues</div>
          <div class="progress-wrap"><div class="progress-bar" id="statPayBar" style="width:0%;background:var(--green);"></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Open Issues</div>
          <div class="stat-value stat-yellow" id="statIssues">—</div>
          <div class="stat-sub">Pending reports</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Delinquents</div>
          <div class="stat-value" style="color:#f08080;" id="statDelinq">—</div>
          <div class="stat-sub">Flagged households</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Outstanding Balance</div>
          <div class="stat-value stat-blue" id="statBalance">—</div>
          <div class="stat-sub">Total unpaid dues</div>
        </div>
      </div>

      <div class="grid-2">
        <div class="card">
          <div class="card-title">Recent Issue Reports</div>
          <div id="dashIssueList"></div>
          <button class="btn btn-sm" style="margin-top:10px;" onclick="nav('issues',null)">View All Issues</button>
        </div>
        <div class="card">
          <div class="card-title">Payment Summary — May 2026</div>
          <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;">
              <span>Paid</span><span class="stat-green" style="font-weight:600;"><span id="payCountPaid">—</span> households</span>
            </div>
            <div class="progress-wrap"><div class="progress-bar" id="payBarPaid" style="width:0%;background:var(--green);"></div></div>
          </div>
          <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;">
              <span>Partial</span><span class="stat-yellow" style="font-weight:600;"><span id="payCountPartial">—</span> households</span>
            </div>
            <div class="progress-wrap"><div class="progress-bar" id="payBarPartial" style="width:0%;background:var(--yellow);"></div></div>
          </div>
          <div>
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;">
              <span>Unpaid</span><span style="color:#f08080;font-weight:600;"><span id="payCountUnpaid">—</span> households</span>
            </div>
            <div class="progress-wrap"><div class="progress-bar" id="payBarUnpaid" style="width:0%;background:#f08080;"></div></div>
          </div>
          <button class="btn btn-sm" style="margin-top:16px;" onclick="nav('payments',null)">View Payments</button>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Recent Activity</div>
        <div id="activityFeed"></div>
      </div>
    </div>

    <!-- ══════════ RESIDENTS ══════════ -->
    <div class="panel" id="panel-residents">
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
    <div class="panel" id="panel-members">
      <div class="stat-grid" style="grid-template-columns:repeat(2,1fr);max-width:400px;">
        <div class="stat-card">
          <div class="stat-label">Total Members</div>
          <div class="stat-value stat-blue" id="statTotalMembers">—</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Houses</div>
          <div class="stat-value stat-accent" id="statTotalHouses">—</div>
        </div>
      </div>
      <div class="members-grid" id="membersGrid"></div>
    </div>

    <!-- ══════════ DELINQUENTS ══════════ -->
    <div class="panel" id="panel-delinquents">
      <div class="card" style="margin-bottom:16px;">
        <div class="card-title">Flagged Households</div>
        <div style="font-size:13px;color:var(--text-dim);">Households with outstanding issues, unpaid bills, or violations.</div>
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
          <div class="upload-zone" style="flex:1;min-width:220px;">
            <div style="font-size:22px;margin-bottom:6px;">📂</div>
            <div>Drop Excel file here or <label for="excelFile" style="color:var(--accent);cursor:pointer;text-decoration:underline;">browse</label></div>
            <input type="file" id="excelFile" accept=".xlsx,.xls" style="display:none;" onchange="uploadExcel()">
            <div id="excelFileName" style="font-size:11px;margin-top:6px;color:var(--green);"></div>
          </div>
          <button class="btn" onclick="openModal('addReceipt')">+ Add Receipt Manually</button>
          <button class="btn btn-green" onclick="openAddRecordModal()">+ Add Financial Record</button>
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

      <!-- Excel Table -->
      <div class="card">
        <div class="card-title">Imported Records</div>
        <table class="panel-table" id="excelTable">
          <thead>
            <tr>
              <th>Name</th>
              <th>Amount</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody><tr><td colspan="3" style="text-align:center;color:var(--text-dim);padding:20px;">No records uploaded yet.</td></tr></tbody>
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
          <input type="text" placeholder="Search household…" style="flex:1;min-width:160px;">
          <select style="width:auto;"><option>All Status</option><option>Paid</option><option>Partial</option><option>Unpaid</option></select>
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
        <div class="manage-card" onclick="openModal('muChangeStatus')">
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
          <span><span class="map-dot" style="background:#888;"></span>Issue (Resolved)</span>
          <span><span class="map-dot" style="background:linear-gradient(to right,#0000ff,#00ff00,#ff0000);border-radius:2px;"></span>Issue Density</span>
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
            <button class="btn" onclick="showToast('📊 Generating Monthly Summary Report…')">📊 Monthly Summary</button>
            <button class="btn btn-blue" onclick="showToast('💳 Generating Payment Report…')">💳 Payment Report</button>
            <button class="btn btn-green" onclick="showToast('🔧 Generating Issue Report…')">🔧 Issue Report</button>
            <button class="btn" style="background:var(--purple-dim);border-color:rgba(185,154,245,0.25);color:var(--purple);" onclick="showToast('👥 Generating Resident Report…')">👥 Resident Directory</button>
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
      <div class="f-field"><span class="f-label">Target</span>
        <select><option>All Residents</option><option>Block 1</option><option>Block 2</option><option>Block 3</option><option>Block 4</option><option>Block 5</option></select>
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
      <div class="f-field"><span class="f-label">Password</span><input type="password" id="newResPass" placeholder="Leave blank for default"></div>
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
      <div class="f-field"><span class="f-label">Password</span><input type="password" id="newOfficerPass" placeholder="Leave blank for default"></div>
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
      <div class="f-field"><span class="f-label">Role</span>
        <select id="editURole"><option>Resident</option><option>Officer</option></select>
      </div>
      <div class="f-field"><span class="f-label">Account Status</span>
        <select id="editUStatus"><option>Active</option><option>Suspended</option><option>Deactivated</option></select>
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

function showToast(msg){
  const t=document.getElementById('toast');
  t.textContent=msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),2800);
}
function openModal(id){ document.getElementById('modal-'+id).classList.add('open'); }
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
  if(window.innerWidth <= 768) closeSidebar();
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
  const [r, a, iss, off, recs, pay, fin, finRecsData, hh] = await Promise.all([
    apiGet('/api/residents'),
    apiGet('/api/announcements'),
    apiGet('/api/issues'),
    apiGet('/api/officers'),
    apiGet('/api/recommendations'),
    apiGet('/api/financial/payments'),
    apiGet('/api/financial/summary'),
    apiGet('/api/financial'),
    apiGet('/api/households'),
  ]);
  residents = r.map((x,i)=>({...x, color:colorFor(i), initials:initials(x.name)}));
  households = Array.isArray(hh) ? hh : [];
  finRecs = Array.isArray(finRecsData) ? finRecsData : [];
  announcements = a;
  issues = iss;
  officers = off.map((x,i)=>({...x, color:colorFor(i), initials:initials(x.name)}));
  recommendations = recs;
  payments = pay.map((x,i)=>({...x, color:colorFor(i), initials:initials(x.name)}));
  delinquents = residents.filter(r=>r.status==='Delinquent'||r.current_balance>0);

  // update stat cards
  if(document.getElementById('statResidents')) document.getElementById('statResidents').textContent = residents.length;
  if(document.getElementById('statActive'))    document.getElementById('statActive').textContent    = residents.filter(r=>r.status==='Active').length;
  if(document.getElementById('statDelinq'))    document.getElementById('statDelinq').textContent    = delinquents.length;
  if(document.getElementById('statBalance'))   document.getElementById('statBalance').textContent   = '₱'+Number(fin.total_balance||0).toLocaleString();
  if(document.getElementById('finCollected'))   document.getElementById('finCollected').textContent  = '₱'+Number(fin.total_collected||0).toLocaleString();
  if(document.getElementById('finDues'))        document.getElementById('finDues').textContent       = '₱'+Number(fin.total_dues||0).toLocaleString();
  if(document.getElementById('finBalance'))     document.getElementById('finBalance').textContent    = '₱'+Number(fin.total_balance||0).toLocaleString();
  if(document.getElementById('payCollected'))   document.getElementById('payCollected').textContent  = '₱'+Number(fin.total_collected||0).toLocaleString();
  if(document.getElementById('payDues'))        document.getElementById('payDues').textContent       = '₱'+Number(fin.total_dues||0).toLocaleString();
  if(document.getElementById('payBalance'))     document.getElementById('payBalance').textContent    = '₱'+Number(fin.total_balance||0).toLocaleString();
  if(document.getElementById('delinBadge'))     document.getElementById('delinBadge').textContent    = fin.delinquent_count||0;
  if(document.getElementById('statIssues'))    document.getElementById('statIssues').textContent    = issues.filter(i=>i.status!=='Resolved').length;
  if(document.getElementById('issueBadge'))    document.getElementById('issueBadge').textContent    = issues.filter(i=>i.status!=='Resolved').length;
  if(document.getElementById('msgBadge')) document.getElementById('msgBadge').textContent = await (async()=>{try{const t=await apiGet('/api/messages/threads');return t.length||0;}catch(e){return 0;}})();

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
  if(document.getElementById('statTotalMembers')) document.getElementById('statTotalMembers').textContent=residents.length;
  if(document.getElementById('statTotalHouses'))  document.getElementById('statTotalHouses').textContent=households.length;

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
  loadManageData();
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

  document.getElementById('activityFeed').innerHTML='<div style="color:var(--text-dim);font-size:13px;padding:10px 0;">Activity feed powered by live data.</div>';
}

function renderResidents(){
  const q=(document.getElementById('residentSearch')||{}).value||'';
  const b=(document.getElementById('residentBlock')||{}).value||'';
  const filtered=residents.filter(r=>{
    const matchQ=!q||r.name.toLowerCase().includes(q.toLowerCase())||(r.block_lot||'').toLowerCase().includes(q.toLowerCase());
    const matchB=!b||(r.block_lot||'').toLowerCase().includes(b.toLowerCase().replace('block ','blk '));
    return matchQ&&matchB;
  });
  const payLabel=bal=>bal<=0?'Paid':(bal<1000?'Partial':'Unpaid');
  const payPill=bal=>bal<=0?'pill-resolved':(bal<1000?'pill-pending':'pill-urgent');
  document.getElementById('residentGrid').innerHTML=filtered.length?filtered.map(r=>`
    <div class="resident-card">
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
  document.getElementById('membersGrid').innerHTML=residents.map(m=>`
    <div class="member-card">
      <div class="member-avatar ${m.color}">${m.initials}</div>
      <div class="member-info">
        <h4>${m.name}</h4>
        <p>${m.block_lot}</p>
        <div class="m-type">Resident</div>
      </div>
    </div>`).join('');
}

function renderDelinquents(){
  document.getElementById('delinquentGrid').innerHTML=delinquents.length?delinquents.map(d=>`
    <div class="res-card">
      <div class="res-thumb">
        <div style="width:100%;height:100%;background:rgba(240,100,100,0.1);display:flex;align-items:center;justify-content:center;font-size:36px;">🏠</div>
      </div>
      <div class="res-info">
        <h4>${d.name}</h4>
        <div class="res-reason">Balance: ₱${Number(d.current_balance||0).toLocaleString()}</div>
        <div class="res-location">${d.block_lot}</div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:4px;">Status: ${d.status}</div>
      </div>
    </div>`).join(''):'<div class="empty-state"><div class="empty-icon">✅</div>No delinquent residents.</div>';
}

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

function renderAnnouncements(){
  const tagLabel={notice:'Notice',urgent:'Urgent',event:'Event'};
  document.getElementById('annList').innerHTML=announcements.length?announcements.map(a=>`
    <div class="ann-item">
      <div class="ann-meta">
        <span class="pill pill-${a.tag||'notice'}">${tagLabel[a.tag]||'Notice'}</span>
        <span class="ann-date">${a.created_at}</span>
      </div>
      <div class="ann-title">${a.title}</div>
      <div class="ann-body">${a.content}</div>
      <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">By: ${a.posted_by}</div>
      <div class="ann-actions">
        <button class="btn btn-sm btn-danger" onclick="deleteAnn(${a.id})">Delete</button>
      </div>
    </div>`).join(''):'<div class="empty-state"><div class="empty-icon">📢</div>No announcements yet.</div>';
}
async function deleteAnn(id){
  await apiDelete('/api/announcements/'+id);
  announcements=announcements.filter(a=>a.id!==id);
  renderAnnouncements();
  showToast('🗑️ Announcement deleted.');
}
async function postAnnouncement(){
  const tag=document.getElementById('annType').value;
  const title=document.getElementById('annTitle').value.trim();
  const content=document.getElementById('annBody').value.trim();
  if(!title||!content){showToast('⚠ Please fill in all fields.');return;}
  const res=await apiPost('/api/announcements',{tag,title,content});
  if(res.success){
    announcements.unshift(res.announcement);
    closeModal('ann');
    document.getElementById('annTitle').value='';
    document.getElementById('annBody').value='';
    renderAnnouncements();
    showToast('✅ Announcement posted to all residents.');
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
  const priColor={High:'#f08080',Medium:'var(--yellow)',Low:'var(--green)'};
  document.getElementById('issueList').innerHTML=filtered.length?filtered.map(i=>{
    const pri=issuePriority(i);
    return `
    <div class="issue-item">
      <div class="issue-top">
        <div>
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:4px;">${i.resident} · ${i.block_lot}</div>
          <div class="issue-title">${i.title}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
          ${statusPill[i.status]||''}
          <span style="font-size:10px;font-weight:700;color:${priColor[pri]}">${pri}</span>
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
        ${i.status!=='Resolved'?`<button class="btn btn-sm" onclick="openRespondModal(${i.id})">Respond</button>`:''}
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
  document.getElementById('newThreadTitle').value='';
  openModal('newThread');
}
async function adminStartThread(){
  const title=document.getElementById('newThreadTitle').value.trim();
  const resident_id=document.getElementById('newThreadResident').value;
  if(!title){showToast('⚠ Please enter a subject.');return;}
  const res=await apiPost('/api/messages/start',{title,resident_id:resident_id||undefined});
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

async function removeOfficer(id){ await apiDelete('/api/officers/'+id); officers=officers.filter(o=>o.id!==id); renderOfficers(); showToast('🗑️ Officer removed.'); }

async function openEditHousehold(id){
  const h=muHouseholds.find(x=>x.id===id);
  if(!h) return;
  document.getElementById('editHIdx').value=id;
  document.getElementById('editHLoc').value=h.block_lot_number||'';
  document.getElementById('editHStatus').value=h.status||'Active';
  openModal('muEditHousehold');
}
async function saveEditHousehold(){
  const id=document.getElementById('editHIdx').value;
  const loc=document.getElementById('editHLoc').value.trim();
  const status=document.getElementById('editHStatus').value;
  if(!loc){showToast('⚠ Block & Lot is required.');return;}
  const res=await apiPut('/api/households/'+id,{block_lot_number:loc,status});
  if(res.success){closeModal('muEditHousehold');await loadManageData();showToast('✅ Household updated.');}
}
async function openEditUser(id){
  const u=muResidents.find(x=>x.id===id);
  if(!u) return;
  document.getElementById('editUIdx').value=id;
  document.getElementById('editUName').value=u.name||'';
  document.getElementById('editUEmail').value=u.email||'';
  document.getElementById('editUStatus').value=u.status==='Active'?'Active':'Deactivated';
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
  const res=await apiPut('/api/households/'+id,{status,reason});
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
    <div class="issue-item" style="margin-bottom:12px;">
      <div class="issue-top">
        <div>
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:4px;">${r.resident} · ${r.created_at}</div>
          <div class="issue-title">${r.title}</div>
        </div>
        ${statusPill[r.status]||''}
      </div>
      <div class="issue-body">${r.description}</div>
      <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
        <select onchange="updateRecStatus(${r.id},this.value)" style="font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text-main);">
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

function renderFinancialRecords(records){
  if(!records||!Array.isArray(records)) return;
  const tbody=document.querySelector('#excelTable tbody');
  if(!tbody) return;
  if(!records.length){
    tbody.innerHTML='<tr><td colspan="5" style="text-align:center;color:var(--text-dim);padding:20px;">No financial records yet.</td></tr>';
    return;
  }
  tbody.innerHTML=records.map(r=>`<tr>
    <td>${r.resident||'—'}</td>
    <td><span class="pill ${r.record_type==='Payment'?'pill-resolved':'pill-pending'}">${r.record_type}</span></td>
    <td>${r.description||'—'}</td>
    <td style="text-align:right;">₱${Number(r.amount).toLocaleString()}</td>
    <td>${r.record_date ? new Date(r.record_date).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : '—'}</td>
  </tr>`).join('');
  // Update table headers
  const thead=document.querySelector('#excelTable thead tr');
  if(thead) thead.innerHTML='<th>Resident</th><th>Type</th><th>Description</th><th>Amount</th><th>Date</th>';
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
    const r=res.resident;
    residents.push({...r,block_lot:block_lot_number||'—',color:colorFor(residents.length),initials:initials(r.name)});
    closeModal('addResident');
    renderResidents();
    showToast('✅ Resident added successfully.');
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
        <td>${h.id}</td><td>${h.block_lot_number}</td><td>—</td><td>${mc||'—'}</td>
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
  if(!loc){showToast('⚠ Please fill in required fields.');return;}
  const res=await apiPost('/api/households',{block_lot_number:loc,status});
  if(res.success){
    closeModal('muAddHousehold');
    document.getElementById('muHLoc').value='';
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

/* Excel Upload */
function uploadExcel(){
  const file=document.getElementById('excelFile').files[0];
  if(!file){return;}
  const fn=document.getElementById('excelFileName');
  if(fn) fn.textContent='Selected: '+file.name;
  showToast('⚠ Excel import is not yet supported. Use “+ Add Financial Record” to enter records manually.');
  document.getElementById('excelFile').value='';
  if(fn) fn.textContent='';
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
// Combines status urgency × category severity → used as leaflet.heat point weight
function heatWeight(issue) {
  const sw = {Pending: 1.0, 'In Progress': 0.65, Resolved: 0.2}[issue.status] ?? 0.7;
  const cw = {
    Security: 1.5, Vandalism: 1.4, 'Illegal Parking': 1.3,
    Sanitation: 1.2, Noise: 1.1, Maintenance: 1.0, Other: 0.9,
    'Street Lights': 0.9, Cleanliness: 1.1, Infrastructure: 1.0,
  }[issue.category] ?? 1.0;
  return sw * cw;  // max ~1.5 for Security+Pending; higher frequency naturally stacks
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
  layerHeatmap    = L.heatLayer([], {radius: 30, blur: 20, maxZoom: 17, max: 1.5, gradient: {0.4:'#0000ff', 0.65:'#00ff00', 0.85:'#ffff00', 1:'#ff0000'}}).addTo(mapInst);

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
</body>
</html>
