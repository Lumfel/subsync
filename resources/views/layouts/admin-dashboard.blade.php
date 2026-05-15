<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SubSync — Admin Dashboard</title>
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
  background: rgba(0,0,0,0.38);
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
  background:#0f1826;
  display:flex; align-items:center; justify-content:center;
  flex-direction:column; gap:12px; color:var(--text-dim); font-size:14px;
}
.map-badge { font-size:36px; }

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
  .sidebar { position:fixed; left:-100%; top:0; height:100%; z-index:50; transition:left 0.3s; }
  .sidebar.open { left:0; }
  .menu-toggle { display:block; }
  .main { margin-left:0; }
  .graph-grid { grid-template-columns:1fr; }
  .graph-grid-wide { grid-template-columns:1fr; }
  .finance-stats { grid-template-columns:1fr 1fr; }
}
</style>
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
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
    <div class="nav-item" onclick="nav('delinquents',this)"><span class="nav-icon">⚠️</span> Delinquents <span class="nav-badge">3</span></div>

    <div class="nav-section-label">Finance</div>
    <div class="nav-item" onclick="nav('finance',this)"><span class="nav-icon">💳</span> Finance</div>
    <div class="nav-item" onclick="nav('payments',this)"><span class="nav-icon">₱</span> Payments</div>

    <div class="nav-section-label">Communication</div>
    <div class="nav-item" onclick="nav('messages',this)"><span class="nav-icon">💬</span> Messages <span class="nav-badge" id="msgBadge">5</span></div>
    <div class="nav-item" onclick="nav('announcements',this)"><span class="nav-icon">📢</span> Announcements</div>

    <div class="nav-section-label">Management</div>
    <div class="nav-item" onclick="nav('issues',this)"><span class="nav-icon">🔧</span> Issue Reports <span class="nav-badge">3</span></div>
    <div class="nav-item" onclick="nav('manageusers',this)"><span class="nav-icon">⚙️</span> Manage Users</div>
    <div class="nav-item" onclick="nav('officers',this)"><span class="nav-icon">🛡️</span> Officers</div>
    <div class="nav-item" onclick="nav('analytics',this)"><span class="nav-icon">📈</span> Analytics</div>
    <div class="nav-item" onclick="nav('mapping',this)"><span class="nav-icon">🗺️</span> Maps</div>
    <div class="nav-item" onclick="nav('reports',this)"><span class="nav-icon">📁</span> Reports</div>
  </nav>
  <div class="sidebar-footer">
    <div class="admin-profile">
      <div class="admin-avatar">SA</div>
      <div>
        <div class="admin-name">Admin</div>
        <div class="admin-role">Subdivision Admin</div>
      </div>
    </div>
  </div>
</aside>

<!-- ═══ MAIN ═══ -->
<main class="main">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
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
          <div class="stat-label">Total Households</div>
          <div class="stat-value stat-accent">148</div>
          <div class="stat-sub">Blk 1–5 registered</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Members</div>
          <div class="stat-value stat-blue">128</div>
          <div class="stat-sub">Across all households</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Payment Collection</div>
          <div class="stat-value stat-green">87%</div>
          <div class="stat-sub">May 2026 dues</div>
          <div class="progress-wrap"><div class="progress-bar" style="width:87%;background:var(--green);"></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Open Issues</div>
          <div class="stat-value stat-yellow">12</div>
          <div class="stat-sub">3 high priority</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Delinquents</div>
          <div class="stat-value" style="color:#f08080;">3</div>
          <div class="stat-sub">Flagged households</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Unread Messages</div>
          <div class="stat-value stat-blue">5</div>
          <div class="stat-sub">From residents</div>
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
              <span>Paid</span><span class="stat-green" style="font-weight:600;">129 households</span>
            </div>
            <div class="progress-wrap"><div class="progress-bar" style="width:87%;background:var(--green);"></div></div>
          </div>
          <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;">
              <span>Partial</span><span class="stat-yellow" style="font-weight:600;">9 households</span>
            </div>
            <div class="progress-wrap"><div class="progress-bar" style="width:6%;background:var(--yellow);"></div></div>
          </div>
          <div>
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;">
              <span>Unpaid</span><span style="color:#f08080;font-weight:600;">10 households</span>
            </div>
            <div class="progress-wrap"><div class="progress-bar" style="width:7%;background:#f08080;"></div></div>
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
          <div class="stat-value stat-blue">128</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Houses</div>
          <div class="stat-value stat-accent">96</div>
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
          <div class="stat-value stat-green">₱120,000</div>
          <div class="stat-sub">This month</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Expenses</div>
          <div class="stat-value" style="color:#f08080;">₱45,000</div>
          <div class="stat-sub">This month</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Balance</div>
          <div class="stat-value stat-accent">₱75,000</div>
          <div class="stat-sub">Net savings</div>
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
        </div>
      </div>

      <!-- Receipt scrollable container -->
      <div style="margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-dim);">Financial Receipts</div>
        <button class="btn btn-sm btn-green" onclick="showToast('📥 Exporting receipts…')">↓ Export</button>
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
          <div class="stat-value stat-green">₱148,000</div>
          <div class="stat-sub">May 2026</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Pending</div>
          <div class="stat-value stat-yellow">₱19,000</div>
          <div class="stat-sub">9 households</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Overdue</div>
          <div class="stat-value" style="color:#f08080;">₱10,000</div>
          <div class="stat-sub">10 households</div>
        </div>
      </div>
      <div class="card">
        <div class="card-title">Payment Records</div>
        <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
          <input type="text" placeholder="Search household…" style="flex:1;min-width:160px;">
          <select style="width:auto;"><option>All Status</option><option>Paid</option><option>Partial</option><option>Unpaid</option></select>
          <button class="btn btn-sm btn-green" onclick="showToast('📥 Exporting payment records…')">↓ Export Excel</button>
        </div>
        <div id="paymentList"></div>
      </div>
    </div>

    <!-- ══════════ MESSAGES ══════════ -->
    <div class="panel" id="panel-messages">
      <div class="msg-layout">
        <div class="msg-sidebar">
          <div class="msg-sidebar-head">Resident Conversations</div>
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
          <option value="pending">Pending</option>
          <option value="progress">In Progress</option>
          <option value="resolved">Resolved</option>
        </select>
        <select id="issuePriFilter" onchange="renderIssues()" style="width:auto;">
          <option value="">All Priority</option>
          <option value="high">High</option>
          <option value="medium">Medium</option>
          <option value="low">Low</option>
        </select>
        <button class="btn btn-sm btn-green" style="margin-left:auto;" onclick="showToast('📥 Exporting issue reports…')">↓ Export</button>
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
        <div class="manage-card" onclick="openModal('muAddUser')">
          <div class="manage-card-icon">➕</div>
          <h3>Add User</h3>
          <p>Create new system user account</p>
        </div>
        <div class="manage-card" onclick="openModal('muAddFamily')">
          <div class="manage-card-icon">👨‍👩‍👧</div>
          <h3>Add Family</h3>
          <p>Create a family record</p>
        </div>
        <div class="manage-card" onclick="openModal('muAddMember')">
          <div class="manage-card-icon">👤</div>
          <h3>Add Member</h3>
          <p>Add or update member roles</p>
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
          <table class="panel-table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
          <tbody id="userTbody"></tbody></table>
        </div>
        <div id="familiesPanel" class="table-panel">
          <div class="card-title">Families</div>
          <table class="panel-table"><thead><tr><th>ID</th><th>Family Name</th><th>Actions</th></tr></thead>
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

    <!-- ══════════ ANALYTICS ══════════ -->
    <div class="panel" id="panel-analytics">
      <div class="stat-grid">
        <div class="stat-card"><div class="stat-label">Members</div><div class="stat-value stat-blue">128</div></div>
        <div class="stat-card"><div class="stat-label">Houses</div><div class="stat-value stat-accent">96</div></div>
        <div class="stat-card"><div class="stat-label">Delinquents</div><div class="stat-value" style="color:#f08080;">3</div></div>
      </div>
      <div class="graph-grid">
        <div class="card">
          <div class="card-title">Collections</div>
          <div class="graph-placeholder"><div class="g-icon">📊</div><div>D3 Chart — Collections</div></div>
        </div>
        <div class="card">
          <div class="card-title">Delinquents</div>
          <div class="graph-placeholder"><div class="g-icon">📉</div><div>D3 Chart — Delinquents</div></div>
        </div>
        <div class="card">
          <div class="card-title">Complaints</div>
          <div class="graph-placeholder"><div class="g-icon">🔴</div><div>D3 Chart — Complaints</div></div>
        </div>
      </div>
      <div class="graph-grid-wide">
        <div class="card">
          <div class="card-title">Heat Map</div>
          <div class="graph-placeholder"><div class="g-icon">🗺️</div><div>D3 Heat Map</div></div>
        </div>
        <div class="card">
          <div class="card-title">Security Logs</div>
          <div class="graph-placeholder"><div class="g-icon">🔒</div><div>Security Events</div></div>
        </div>
      </div>
    </div>

    <!-- ══════════ MAPPING ══════════ -->
    <div class="panel" id="panel-mapping">
      <div class="card" style="margin-bottom:16px;">
        <div style="font-size:13px;color:var(--text-mid);">
          Interactive Leaflet map of Terra Nova subdivision. Click to drop markers for households.
        </div>
      </div>
      <div id="map-container">
        <div class="map-badge">🗺️</div>
        <div style="font-size:13px;">Leaflet map loads here when <code>leaflet.js</code> is available.</div>
        <div style="font-size:11px;color:var(--text-dim);">Center: 10.62269, 122.96134 · Zoom 17</div>
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
    <div class="modal-title">Add Resident Household</div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Household Name</span><input type="text" id="newResName" placeholder="e.g. Santos Family"></div>
      <div class="f-field"><span class="f-label">Block & Lot</span><input type="text" id="newResBlock" placeholder="e.g. Blk 2 Lot 5"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Head of Household</span><input type="text" id="newResHead" placeholder="Full name"></div>
      <div class="f-field"><span class="f-label">Contact Number</span><input type="text" id="newResContact" placeholder="09xx-xxx-xxxx"></div>
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
      <div class="f-field"><span class="f-label">Block</span>
        <select id="newOfficerBlock"><option>Block 1</option><option>Block 2</option><option>Block 3</option><option>Block 4</option><option>Block 5</option></select>
      </div>
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
      <select id="respondStatus"><option value="pending">Pending</option><option value="progress">In Progress</option><option value="resolved">Resolved</option></select>
    </div></div>
    <div class="f-row"><div class="f-field"><span class="f-label">Admin Response</span><textarea id="respondText" placeholder="Type your response to the resident…"></textarea></div></div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('respond')">Cancel</button>
      <button class="btn btn-sm" onclick="submitResponse()">Send Response</button>
    </div>
  </div>
</div>

<!-- Add Receipt -->
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
        <select id="statusHNew"><option>Active</option><option>Inactive</option><option>Suspended</option><option>Under Review</option></select>
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
          <option>Admin</option>
          <option>Super Admin</option>
        </select>
      </div>
      <div class="f-field"><span class="f-label">Linked Household</span>
        <select id="muUHousehold">
          <option value="">— None —</option>
          <option>Blk 3 Lot 12 · Dela Cruz Family</option>
          <option>Blk 1 Lot 4 · Santos Family</option>
          <option>Blk 5 Lot 1 · Torres Family</option>
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
        <select id="editURole"><option>Resident</option><option>Officer</option><option>Admin</option><option>Super Admin</option></select>
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

<!-- Add Family -->
<div class="modal-overlay" id="modal-muAddFamily">
  <div class="modal-box">
    <div class="modal-title">Add Family Record</div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Family Name</span><input type="text" id="muFName" placeholder="e.g. Reyes Family"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Head of Family</span><input type="text" id="muFHead" placeholder="Full name of head"></div>
      <div class="f-field"><span class="f-label">Linked Household</span>
        <select id="muFHousehold">
          <option value="">— None —</option>
          <option>Blk 3 Lot 12</option>
          <option>Blk 1 Lot 4</option>
          <option>Blk 5 Lot 1</option>
        </select>
      </div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Contact Number</span><input type="text" id="muFContact" placeholder="09xx-xxx-xxxx"></div>
      <div class="f-field"><span class="f-label">Email (optional)</span><input type="email" id="muFEmail" placeholder="family@email.com"></div>
    </div>
    <div class="f-row">
      <div class="f-field"><span class="f-label">Notes</span><textarea id="muFNotes" style="min-height:60px;" placeholder="Any relevant notes…"></textarea></div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('muAddFamily')">Cancel</button>
      <button class="btn btn-sm" onclick="saveAddFamily()">Add Family</button>
    </div>
  </div>
</div>

<!-- Edit Family -->
<div class="modal-overlay" id="modal-muEditFamily">
  <div class="modal-box">
    <div class="modal-title">Edit Family Record</div>
    <input type="hidden" id="editFIdx">
    <div class="f-row">
      <div class="f-field"><span class="f-label">Family Name</span><input type="text" id="editFName"></div>
    </div>
    <div class="modal-actions">
      <button class="modal-close-btn" onclick="closeModal('muEditFamily')">Cancel</button>
      <button class="btn btn-sm" onclick="saveEditFamily()">Save Changes</button>
    </div>
  </div>
</div>

<!-- Add Member -->
<div class="modal-overlay" id="modal-muAddMember">
  <div class="modal-box">
    <div class="modal-title">Add Member</div>
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
      <div class="f-field"><span class="f-label">Household (Block & Lot)</span><input type="text" id="muMHousehold" placeholder="e.g. Blk 3 Lot 12"></div>
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
      <div class="f-field"><span class="f-label">Member Type</span>
        <select id="editMType"><option>HOA Member</option><option>Officer</option><option>Tenant</option></select>
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
  manageusers:'Manage Users',officers:'Officers',analytics:'Analytics',
  mapping:'Maps · Terra Nova',reports:'Reports & Files'
};
function nav(name, el){
  document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  const panel=document.getElementById('panel-'+name);
  if(panel) panel.classList.add('active');
  if(el) el.classList.add('active');
  else document.querySelectorAll('.nav-item').forEach(n=>{
    if(n.textContent.trim().toLowerCase().includes(name)) n.classList.add('active');
  });
  document.getElementById('topbarTitle').textContent = panelTitles[name]||name;
  if(name==='messages') document.getElementById('msgBadge').style.display='none';
  if(name==='issues') renderIssues();
}

/* ══════════════ DATA ══════════════ */
const residents=[
  {name:'Dela Cruz Family',block:'Blk 3 Lot 12',head:'Juan Dela Cruz',status:'active',color:'ta-r1',initials:'DC',payStatus:'paid'},
  {name:'Santos Family',block:'Blk 1 Lot 4',head:'Maria Santos',status:'active',color:'ta-r2',initials:'SF',payStatus:'unpaid'},
  {name:'Reyes Family',block:'Blk 2 Lot 8',head:'Pedro Reyes',status:'active',color:'ta-r3',initials:'RF',payStatus:'paid'},
  {name:'Gomez Family',block:'Blk 4 Lot 3',head:'Ana Gomez',status:'active',color:'ta-r4',initials:'GF',payStatus:'partial'},
  {name:'Torres Family',block:'Blk 5 Lot 1',head:'Carlo Torres',status:'inactive',color:'ta-r5',initials:'TF',payStatus:'unpaid'},
  {name:'Villanueva Family',block:'Blk 1 Lot 9',head:'Rosa Villanueva',status:'active',color:'ta-r1',initials:'VF',payStatus:'paid'},
  {name:'Cruz Family',block:'Blk 3 Lot 7',head:'Ben Cruz',status:'active',color:'ta-r2',initials:'CF',payStatus:'paid'},
  {name:'Lim Family',block:'Blk 2 Lot 11',head:'Jenny Lim',status:'active',color:'ta-r3',initials:'LF',payStatus:'partial'},
];

const membersData=[
  {name:'Juan Dela Cruz',household:'Blk 3 Lot 12',type:'HOA Member',color:'ta-r1',initials:'JD'},
  {name:'Maria Santos',household:'Blk 1 Lot 4',type:'HOA Member',color:'ta-r2',initials:'MS'},
  {name:'Pedro Reyes',household:'Blk 2 Lot 8',type:'Officer',color:'ta-r3',initials:'PR'},
  {name:'Ana Gomez',household:'Blk 4 Lot 3',type:'HOA Member',color:'ta-r4',initials:'AG'},
  {name:'Carlo Torres',household:'Blk 5 Lot 1',type:'HOA Member',color:'ta-r5',initials:'CT'},
  {name:'Rosa Villanueva',household:'Blk 1 Lot 9',type:'HOA Member',color:'ta-r1',initials:'RV'},
  {name:'Ben Cruz',household:'Blk 3 Lot 7',type:'Officer',color:'ta-r2',initials:'BC'},
  {name:'Jenny Lim',household:'Blk 2 Lot 11',type:'HOA Member',color:'ta-r3',initials:'JL'},
];

const delinquents=[
  {name:'Dela Cruz Family',location:'Blk 3 Lot 12',reason:'Unpaid Bills',flagged:'May 1, 2026',color:'ta-r1'},
  {name:'Santos Family',location:'Blk 1 Lot 4',reason:'Noise Complaint',flagged:'May 5, 2026',color:'ta-r2'},
  {name:'Torres Family',location:'Blk 5 Lot 1',reason:'Unpaid Association Fee',flagged:'Apr 28, 2026',color:'ta-r5'},
];

const announcements=[
  {id:1,tag:'urgent',label:'Urgent',title:'Water Service Interruption — May 16',body:'Water supply halted 8:00 AM–5:00 PM for pipeline maintenance.',date:'May 15, 2026'},
  {id:2,tag:'event',label:'Event',title:'Community Clean-Up Drive',body:'All households invited May 18, 6:00 AM at the covered court.',date:'May 14, 2026'},
  {id:3,tag:'notice',label:'Notice',title:'Updated Subdivision Fee Schedule',body:'New monthly dues take effect June 2026. Visit the admin office.',date:'May 12, 2026'},
];

const issues=[
  {id:'RPT-001',resident:'Dela Cruz Family',block:'Blk 3',category:'Road / Pavement',priority:'high',title:'Large pothole at Block 3 entrance',body:'A large pothole near the main gate of Block 3 is causing vehicle damage.',date:'May 10, 2026',status:'progress',response:'Maintenance team dispatched. Work scheduled for May 17.'},
  {id:'RPT-002',resident:'Santos Family',block:'Blk 1',category:'Street Lighting',priority:'medium',title:'Streetlights out — Lot 14 to 18',body:'Three consecutive streetlights non-functional along Lot 14–18 stretch.',date:'May 13, 2026',status:'pending',response:null},
  {id:'RPT-003',resident:'Reyes Family',block:'Blk 2',category:'Drainage / Flooding',priority:'high',title:'Clogged drainage on Block 2 main road',body:'Standing water accumulates during rain near Block 2 entrance.',date:'May 14, 2026',status:'pending',response:null},
  {id:'RPT-004',resident:'Gomez Family',block:'Blk 4',category:'Noise Complaint',priority:'low',title:'Excessive noise from construction',body:'Construction noise before 7 AM disrupts residents.',date:'May 11, 2026',status:'resolved',response:'Construction company has been notified and reminded of noise ordinances.'},
];

const officers=[
  {name:'Ricardo Aban',role:'HOA President',block:'Block 1',status:'active'},
  {name:'Luz Mendoza',role:'HOA Vice President',block:'Block 2',status:'active'},
  {name:'Fabian Cruz',role:'Secretary',block:'Block 3',status:'active'},
  {name:'Marlene Dizon',role:'Treasurer',block:'Block 1',status:'active'},
  {name:'Jerome Tan',role:'Block Representative',block:'Block 4',status:'inactive'},
];

const officerFiles=[
  {name:'May_2026_Incident_Report.xlsx',officer:'Fabian Cruz',date:'May 14, 2026',size:'24 KB'},
  {name:'April_Collection_Summary.xlsx',officer:'Marlene Dizon',date:'May 2, 2026',size:'38 KB'},
  {name:'Q1_Community_Report.xlsx',officer:'Ricardo Aban',date:'Apr 15, 2026',size:'62 KB'},
];

let currentThread='dc';
const threads={
  dc:{name:'Dela Cruz Family',initials:'DC',color:'ta-r1',msgs:[
    {from:'res',text:'Good morning! I wanted to report a pothole near Block 3.',time:'9:05 AM'},
    {from:'admin',text:'Thank you, Mr. Dela Cruz. We have noted the report and will dispatch a team.',time:'9:08 AM'},
    {from:'res',text:'Thank you! When can we expect it to be fixed?',time:'9:12 AM'},
  ]},
  sf:{name:'Santos Family',initials:'SF',color:'ta-r2',msgs:[
    {from:'res',text:'Good afternoon po. Kailan ang bayad ng dues?',time:'May 13'},
    {from:'admin',text:'Good afternoon! Ang deadline po ay May 31, 2026. Pwede po kayong mag-pay sa admin office.',time:'May 13'},
  ]},
  rf:{name:'Reyes Family',initials:'RF',color:'ta-r3',msgs:[
    {from:'res',text:'Ang drainage sa Block 2 ay nag-o-overflow na naman po.',time:'May 14, 9:00 AM'},
  ]},
  gf:{name:'Gomez Family',initials:'GF',color:'ta-r4',msgs:[
    {from:'res',text:'I submitted an issue report about the construction noise.',time:'May 11'},
    {from:'admin',text:'We have addressed this with the construction company. Thank you for reporting.',time:'May 12'},
  ]},
  tf:{name:'Torres Family',initials:'TF',color:'ta-r5',msgs:[
    {from:'res',text:'May tanong po ako tungkol sa aming association fees.',time:'May 9'},
  ]},
};

/* ══════════════ RENDER FUNCTIONS ══════════════ */

function renderDashboard(){
  // Recent issues (first 2)
  const statusPill={pending:'<span class="pill pill-pending">Pending</span>',progress:'<span class="pill pill-progress">In Progress</span>',resolved:'<span class="pill pill-resolved">Resolved</span>'};
  document.getElementById('dashIssueList').innerHTML=issues.slice(0,2).map(i=>`
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.05);">
      <div>
        <div style="font-size:13px;font-weight:500;">${i.title}</div>
        <div style="font-size:11px;color:var(--text-dim);">${i.resident} · ${i.block}</div>
      </div>
      ${statusPill[i.status]}
    </div>`).join('');
  // Activity feed
  const acts=[
    {icon:'💳',text:'Dela Cruz Family paid monthly dues',time:'10 min ago'},
    {icon:'🔧',text:'Issue RPT-001 assigned to maintenance',time:'1h ago'},
    {icon:'📢',text:'New announcement posted: Water Interruption',time:'2h ago'},
    {icon:'👤',text:'New member added: Rosa Villanueva',time:'Yesterday'},
    {icon:'⚠️',text:'Torres Family flagged as delinquent',time:'Yesterday'},
  ];
  document.getElementById('activityFeed').innerHTML=acts.map(a=>`
    <div style="display:flex;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04);">
      <span style="font-size:18px;">${a.icon}</span>
      <div style="flex:1;font-size:13px;">${a.text}</div>
      <span style="font-size:11px;color:var(--text-dim);white-space:nowrap;">${a.time}</span>
    </div>`).join('');
}

function renderResidents(){
  const q=(document.getElementById('residentSearch')||{}).value||'';
  const b=(document.getElementById('residentBlock')||{}).value||'';
  const payColors={paid:'stat-green',unpaid:'','partial':'stat-yellow'};
  const payLabels={paid:'Paid',unpaid:'Unpaid',partial:'Partial'};
  const filtered=residents.filter(r=>{
    const matchQ=!q||r.name.toLowerCase().includes(q.toLowerCase())||r.block.toLowerCase().includes(q.toLowerCase());
    const matchB=!b||r.block.toLowerCase().includes(b.toLowerCase().replace('block ','blk '));
    return matchQ&&matchB;
  });
  document.getElementById('residentGrid').innerHTML=filtered.map(r=>`
    <div class="resident-card">
      <div class="resident-card-top">
        <div class="r-avatar ${r.color}">${r.initials}</div>
        <div>
          <div class="r-name">${r.name}</div>
          <div class="r-block">${r.block}</div>
        </div>
      </div>
      <div class="r-meta">
        <span class="pill ${r.status==='active'?'pill-active':'pill-inactive'}">${r.status}</span>
        <span class="pill ${r.payStatus==='paid'?'pill-resolved':r.payStatus==='partial'?'pill-pending':'pill-urgent'}">${payLabels[r.payStatus]}</span>
      </div>
      <div style="margin-top:10px;font-size:12px;color:var(--text-dim);">Head: ${r.head}</div>
    </div>`).join('');
}
function filterResidents(){ renderResidents(); }

function renderMembers(){
  document.getElementById('membersGrid').innerHTML=membersData.map(m=>`
    <div class="member-card">
      <div class="member-avatar ${m.color}">${m.initials}</div>
      <div class="member-info">
        <h4>${m.name}</h4>
        <p>${m.household}</p>
        <div class="m-type">${m.type}</div>
      </div>
    </div>`).join('');
}

function renderDelinquents(){
  document.getElementById('delinquentGrid').innerHTML=delinquents.map(d=>`
    <div class="res-card">
      <div class="res-thumb">
        <div style="width:100%;height:100%;background:${d.color==='ta-r1'?'rgba(232,168,124,0.15)':d.color==='ta-r2'?'rgba(122,180,240,0.15)':'rgba(240,192,96,0.15)'};display:flex;align-items:center;justify-content:center;font-size:36px;">🏠</div>
      </div>
      <div class="res-info">
        <h4>${d.name}</h4>
        <div class="res-reason">${d.reason}</div>
        <div class="res-location">${d.location}</div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:4px;">Flagged: ${d.flagged}</div>
      </div>
    </div>`).join('');
}

function renderPayments(){
  const payAmounts={paid:'₱1,000',partial:'₱500',unpaid:'₱0'};
  const payColors={paid:'paid',partial:'partial',unpaid:'unpaid'};
  document.getElementById('paymentList').innerHTML=residents.map(r=>`
    <div class="payment-row">
      <div class="r-avatar ${r.color}" style="width:36px;height:36px;font-size:12px;">${r.initials}</div>
      <div class="pay-household">
        <div class="pay-name">${r.name}</div>
        <div class="pay-block">${r.block}</div>
      </div>
      <span class="pill ${r.payStatus==='paid'?'pill-resolved':r.payStatus==='partial'?'pill-pending':'pill-urgent'}">${r.payStatus}</span>
      <div class="pay-amount ${payColors[r.payStatus]}">${payAmounts[r.payStatus]}</div>
      <button class="btn btn-sm" onclick="showToast('💳 Opening payment record for ${r.name}…')">View</button>
    </div>`).join('');
}

function renderThreads(){
  const unreadMap={dc:2,sf:1,rf:1,gf:0,tf:1};
  document.getElementById('threadList').innerHTML=Object.keys(threads).map(id=>{
    const th=threads[id];
    const last=th.msgs[th.msgs.length-1];
    const unread=unreadMap[id]||0;
    return `<div class="thread-item ${id===currentThread?'active':''}" onclick="selectThread('${id}',this)">
      <div class="thread-avatar ${th.color}">${th.initials}</div>
      <div class="thread-info">
        <div class="thread-name">${th.name}</div>
        <div class="thread-preview">${last.text.slice(0,28)}…</div>
      </div>
      ${unread>0?`<span class="thread-unread">${unread}</span>`:''}
    </div>`;
  }).join('');
}
function selectThread(id,el){
  currentThread=id;
  document.querySelectorAll('.thread-item').forEach(x=>x.classList.remove('active'));
  el.classList.add('active');
  const th=threads[id];
  document.getElementById('chatAvatar').textContent=th.initials;
  document.getElementById('chatAvatar').className='msg-header-avatar '+th.color;
  document.getElementById('chatName').textContent=th.name;
  renderChat();
}
function renderChat(){
  const th=threads[currentThread];
  const body=document.getElementById('msgBody');
  body.innerHTML='<div class="date-divider">Today</div>'+th.msgs.map(m=>`
    <div class="msg-bubble-wrap ${m.from==='admin'?'mine':''}">
      <div class="bubble-avatar ${m.from==='admin'?'ta-r1':th.color}">${m.from==='admin'?'SA':th.initials}</div>
      <div>
        <div class="bubble ${m.from==='admin'?'from-mine':'from-admin'}">${m.text}</div>
        <span class="bubble-time">${m.time}</span>
      </div>
    </div>`).join('');
  body.scrollTop=body.scrollHeight;
}
function adminSend(){
  const inp=document.getElementById('msgInput');
  const txt=inp.value.trim(); if(!txt) return;
  threads[currentThread].msgs.push({from:'admin',text:txt,time:nowTime()});
  inp.value=''; renderChat();
}
function filterThreads(q){
  document.querySelectorAll('.thread-item').forEach(el=>{
    el.style.display=el.textContent.toLowerCase().includes(q.toLowerCase())?'flex':'none';
  });
}

function renderAnnouncements(){
  document.getElementById('annList').innerHTML=announcements.map(a=>`
    <div class="ann-item">
      <div class="ann-meta">
        <span class="pill pill-${a.tag}">${a.label}</span>
        <span class="ann-date">${a.date}</span>
      </div>
      <div class="ann-title">${a.title}</div>
      <div class="ann-body">${a.body}</div>
      <div class="ann-actions">
        <button class="btn btn-sm btn-blue" onclick="showToast('✏️ Editing announcement…')">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteAnn(${a.id})">Delete</button>
      </div>
    </div>`).join('');
}
function deleteAnn(id){
  const i=announcements.findIndex(a=>a.id===id);
  if(i>-1){announcements.splice(i,1);renderAnnouncements();showToast('🗑️ Announcement deleted.');}
}
function postAnnouncement(){
  const type=document.getElementById('annType').value;
  const title=document.getElementById('annTitle').value.trim();
  const body=document.getElementById('annBody').value.trim();
  if(!title||!body){showToast('⚠ Please fill in all fields.');return;}
  const labels={notice:'Notice',urgent:'Urgent',event:'Event'};
  announcements.unshift({id:Date.now(),tag:type,label:labels[type],title,body,date:todayStr()});
  closeModal('ann');
  document.getElementById('annTitle').value='';
  document.getElementById('annBody').value='';
  renderAnnouncements();
  showToast('✅ Announcement posted to all residents.');
}

let respondingId=null;
function renderIssues(){
  const sf=(document.getElementById('issueFilter')||{}).value||'';
  const pf=(document.getElementById('issuePriFilter')||{}).value||'';
  const filtered=issues.filter(i=>(!sf||i.status===sf)&&(!pf||i.priority===pf));
  const statusPill={pending:'<span class="pill pill-pending">Pending</span>',progress:'<span class="pill pill-progress">In Progress</span>',resolved:'<span class="pill pill-resolved">Resolved</span>'};
  const priMap={high:'🔴 High',medium:'🟡 Medium',low:'🟢 Low'};
  document.getElementById('issueList').innerHTML=filtered.map(i=>`
    <div class="issue-item">
      <div class="issue-top">
        <div>
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:4px;">${i.resident} · ${i.block}</div>
          <div class="issue-title">${i.title}</div>
        </div>
        ${statusPill[i.status]||''}
      </div>
      <div class="issue-body">${i.body}</div>
      <div class="issue-meta">
        <span class="pill pill-notice">${i.category}</span>
        <span class="priority-${i.priority}">${priMap[i.priority]}</span>
        <span class="meta-txt">· ${i.id} · ${i.date}</span>
      </div>
      ${i.response?`<div style="margin-top:12px;padding:10px 12px;background:rgba(122,180,240,0.07);border-left:2px solid rgba(122,180,240,0.4);border-radius:0 6px 6px 0;">
        <div style="font-size:10px;color:var(--blue);font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">Admin Response</div>
        <div style="font-size:13px;color:var(--text-mid);">${i.response}</div>
      </div>`:''}
      <div class="issue-actions">
        ${i.status!=='resolved'?`<button class="btn btn-sm" onclick="openRespondModal('${i.id}')">Respond</button>`:''}
        <button class="btn btn-sm btn-blue" onclick="showToast('💬 Messaging ${i.resident}…')">Message Resident</button>
      </div>
    </div>`).join('');
  if(!filtered.length) document.getElementById('issueList').innerHTML='<div class="empty-state"><div class="empty-icon">📋</div>No issues match the filter.</div>';
}
function openRespondModal(id){
  respondingId=id;
  const issue=issues.find(i=>i.id===id);
  if(!issue) return;
  document.getElementById('respondIssueTitle').textContent=issue.title;
  document.getElementById('respondStatus').value=issue.status;
  document.getElementById('respondText').value=issue.response||'';
  openModal('respond');
}
function submitResponse(){
  const issue=issues.find(i=>i.id===respondingId);
  if(!issue) return;
  issue.status=document.getElementById('respondStatus').value;
  issue.response=document.getElementById('respondText').value.trim();
  closeModal('respond');
  renderIssues();
  showToast('✅ Response sent to '+issue.resident+'.');
}

function renderOfficers(){
  document.getElementById('officerTable').innerHTML=officers.map((o,i)=>`
    <tr>
      <td>${o.name}</td>
      <td style="color:var(--text-mid);">${o.role}</td>
      <td style="color:var(--text-mid);">${o.block}</td>
      <td><span class="pill ${o.status==='active'?'pill-active':'pill-inactive'}">${o.status}</span></td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn btn-sm btn-blue" onclick="showToast('✏️ Editing ${o.name}…')">Edit</button>
          <button class="btn btn-sm btn-danger" onclick="removeOfficer(${i})">Remove</button>
        </div>
      </td>
    </tr>`).join('');
}
function removeOfficer(i){ officers.splice(i,1); renderOfficers(); showToast('🗑️ Officer removed.'); }
function addOfficer(){
  const name=document.getElementById('newOfficerName').value.trim();
  const role=document.getElementById('newOfficerRole').value;
  const block=document.getElementById('newOfficerBlock').value;
  if(!name){showToast('⚠ Please enter a name.');return;}
  officers.push({name,role,block,status:'active'});
  closeModal('addOfficer');
  document.getElementById('newOfficerName').value='';
  renderOfficers();
  showToast('✅ Officer added.');
}

function addResident(){
  const name=document.getElementById('newResName').value.trim();
  const block=document.getElementById('newResBlock').value.trim();
  const head=document.getElementById('newResHead').value.trim();
  if(!name||!block||!head){showToast('⚠ Please fill in all fields.');return;}
  const colors=['ta-r1','ta-r2','ta-r3','ta-r4','ta-r5'];
  const inits=name.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
  residents.push({name,block,head,status:'active',color:colors[residents.length%5],initials:inits,payStatus:'unpaid'});
  closeModal('addResident');
  renderResidents();
  showToast('✅ Resident household added.');
}

function renderReports(){
  document.getElementById('officerFiles').innerHTML=officerFiles.map(f=>`
    <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
      <span style="font-size:22px;">📊</span>
      <div style="flex:1;">
        <div style="font-size:13px;font-weight:500;">${f.name}</div>
        <div style="font-size:11px;color:var(--text-dim);">By ${f.officer} · ${f.date} · ${f.size}</div>
      </div>
      <button class="btn btn-sm btn-green" onclick="showToast('📥 Downloading ${f.name}…')">↓</button>
    </div>`).join('');
}

/* ══════════════ MANAGE USERS TABLES ══════════════ */
const sampleHouseholds=[
  {id:1,location:'Blk 3 Lot 12',family:'Dela Cruz Family',members:4,status:'Active'},
  {id:2,location:'Blk 1 Lot 4',family:'Santos Family',members:3,status:'Active'},
  {id:3,location:'Blk 5 Lot 1',family:'Torres Family',members:5,status:'Inactive'},
];
const sampleUsers=[
  {id:1,name:'Admin',email:'admin@terranoval.com'},
  {id:2,name:'Juan Dela Cruz',email:'jdc@email.com'},
  {id:3,name:'Maria Santos',email:'msantos@email.com'},
];
const sampleFamilies=[
  {id:1,name:'Dela Cruz Family'},{id:2,name:'Santos Family'},{id:3,name:'Torres Family'},
];
function renderManageTables(){
  document.getElementById('householdTbody').innerHTML=sampleHouseholds.map((h,i)=>`
    <tr>
      <td>${h.id}</td><td>${h.location}</td><td>${h.family}</td><td>${h.members}</td>
      <td><span class="pill ${h.status==='Active'?'pill-active':'pill-inactive'}">${h.status}</span></td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn btn-sm btn-blue" onclick="openEditHousehold(${i})">Edit</button>
          <button class="btn btn-sm ${h.status==='Active'?'btn-danger':''}" style="${h.status!=='Active'?'background:var(--green-dim);border-color:rgba(130,201,138,0.25);color:var(--green);':''}" onclick="openChangeStatus(${i})">${h.status==='Active'?'Deactivate':'Activate'}</button>
        </div>
      </td>
    </tr>`).join('');
  document.getElementById('userTbody').innerHTML=sampleUsers.map((u,i)=>`
    <tr>
      <td>${u.id}</td><td>${u.name}</td><td style="color:var(--text-dim);">${u.email}</td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn btn-sm btn-blue" onclick="openEditUser(${i})">Edit</button>
          <button class="btn btn-sm btn-danger" onclick="deleteUser(${i})">Remove</button>
        </div>
      </td>
    </tr>`).join('');
  document.getElementById('familyTbody').innerHTML=sampleFamilies.map((f,i)=>`
    <tr>
      <td>${f.id}</td><td>${f.name}</td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn btn-sm btn-blue" onclick="openEditFamily(${i})">Edit</button>
          <button class="btn btn-sm btn-danger" onclick="deleteFamily(${i})">Remove</button>
        </div>
      </td>
    </tr>`).join('');
  document.getElementById('memberTbody').innerHTML=membersData.slice(0,5).map((m,i)=>`
    <tr>
      <td>${i+1}</td><td>${m.name}</td><td>${m.household}</td><td>${m.type}</td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn btn-sm btn-blue" onclick="openEditMember(${i})">Edit</button>
          <button class="btn btn-sm btn-danger" onclick="showToast('🗑️ Member removed.')">Remove</button>
        </div>
      </td>
    </tr>`).join('');
}
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

/* ══════════════ FINANCE / RECEIPTS ══════════════ */
let receiptCollections=[];
let receiptExpenses=[];
const savedReceipts=[
  {month:'April 2026',previous:50000,collections:[{name:'Monthly Dues',amt:120000}],expenses:[{name:'Street Lighting',amt:18000},{name:'Maintenance',amt:27000}]},
  {month:'March 2026',previous:38000,collections:[{name:'Monthly Dues',amt:115000},{name:'Sticker Fees',amt:5000}],expenses:[{name:'Security',amt:25000},{name:'Cleaning',amt:12000}]},
];

function renderReceipts(){
  const container=document.getElementById('receiptContainer');
  // Remove old receipt divs but keep the add button
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
      </div>`;
    container.insertBefore(el, addBtn);
  });
}

function updatePreview(){
  const month=document.getElementById('r-month').value;
  const prev=parseFloat(document.getElementById('r-prev').value)||0;
  document.getElementById('p-month').textContent=month||'Month';
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
function saveReceipt(){
  const month=document.getElementById('r-month').value;
  const prev=parseFloat(document.getElementById('r-prev').value)||0;
  if(!month){showToast('⚠ Please select a month.');return;}
  savedReceipts.unshift({
    month: new Date(month+'-01').toLocaleDateString('en-PH',{month:'long',year:'numeric'}),
    previous:prev,
    collections:[...receiptCollections],
    expenses:[...receiptExpenses]
  });
  receiptCollections=[];receiptExpenses=[];
  closeModal('addReceipt');
  renderReceipts();
  showToast('✅ Receipt saved.');
}

/* Excel Upload */
function uploadExcel(){
  const file=document.getElementById('excelFile').files[0];
  if(!file){showToast('⚠ Select a file first.');return;}
  document.getElementById('excelFileName').textContent='Loaded: '+file.name;
  showToast('📂 File loaded: '+file.name);
  // Real parsing requires SheetJS — placeholder rows shown
  const tbody=document.querySelector('#excelTable tbody');
  tbody.innerHTML=`<tr><td>Sample Name</td><td>₱1,000</td><td>May 2026</td></tr>
    <tr><td colspan="3" style="color:var(--text-dim);font-size:12px;text-align:center;">Import SheetJS (xlsx.full.min.js) for full Excel parsing.</td></tr>`;
}

/* ══════════════ MANAGE USERS MODAL FUNCTIONS ══════════════ */

/* — Add Household — */
function saveAddHousehold(){
  const loc=document.getElementById('muHLoc').value.trim();
  const fam=document.getElementById('muHFamily').value.trim();
  const members=parseInt(document.getElementById('muHMembers').value)||0;
  const status=document.getElementById('muHStatus').value;
  if(!loc||!fam){showToast('⚠ Please fill in required fields.');return;}
  const newId=sampleHouseholds.length+1;
  sampleHouseholds.push({id:newId,location:loc,family:fam,members,status});
  // Also add to families list
  sampleFamilies.push({id:newId,name:fam});
  closeModal('muAddHousehold');
  ['muHLoc','muHFamily','muHMembers','muHNotes'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('muHStatus').value='Active';
  renderManageTables();
  showToast('✅ Household added successfully.');
}

/* — Edit Household — */
function openEditHousehold(i){
  const h=sampleHouseholds[i];
  document.getElementById('editHIdx').value=i;
  document.getElementById('editHLoc').value=h.location;
  document.getElementById('editHFamily').value=h.family;
  document.getElementById('editHMembers').value=h.members;
  document.getElementById('editHStatus').value=h.status;
  openModal('muEditHousehold');
}
function saveEditHousehold(){
  const i=parseInt(document.getElementById('editHIdx').value);
  sampleHouseholds[i].location=document.getElementById('editHLoc').value.trim();
  sampleHouseholds[i].family=document.getElementById('editHFamily').value.trim();
  sampleHouseholds[i].members=parseInt(document.getElementById('editHMembers').value)||0;
  sampleHouseholds[i].status=document.getElementById('editHStatus').value;
  closeModal('muEditHousehold');
  renderManageTables();
  showToast('✅ Household updated.');
}

/* — Change Status — */
function openChangeStatus(i){
  const h=sampleHouseholds[i];
  document.getElementById('statusHIdx').value=i;
  document.getElementById('statusHName').textContent=h.family;
  document.getElementById('statusHLoc').textContent=h.location;
  document.getElementById('statusHNew').value=h.status==='Active'?'Inactive':'Active';
  openModal('muChangeStatus');
}
function saveChangeStatus(){
  const i=parseInt(document.getElementById('statusHIdx').value);
  const newStatus=document.getElementById('statusHNew').value;
  sampleHouseholds[i].status=newStatus;
  closeModal('muChangeStatus');
  document.getElementById('statusHRemarks').value='';
  renderManageTables();
  showToast(`✅ Status changed to "${newStatus}".`);
}

/* — Add User — */
function saveAddUser(){
  const name=document.getElementById('muUName').value.trim();
  const email=document.getElementById('muUEmail').value.trim();
  const pass=document.getElementById('muUPass').value;
  const pass2=document.getElementById('muUPass2').value;
  if(!name||!email){showToast('⚠ Name and email are required.');return;}
  if(pass&&pass!==pass2){showToast('⚠ Passwords do not match.');return;}
  const newId=sampleUsers.length+1;
  sampleUsers.push({id:newId,name,email});
  closeModal('muAddUser');
  ['muUName','muUEmail','muUPass','muUPass2'].forEach(id=>document.getElementById(id).value='');
  renderManageTables();
  showToast('✅ User account created.');
}

/* — Edit User — */
function openEditUser(i){
  const u=sampleUsers[i];
  document.getElementById('editUIdx').value=i;
  document.getElementById('editUName').value=u.name;
  document.getElementById('editUEmail').value=u.email;
  openModal('muEditUser');
}
function saveEditUser(){
  const i=parseInt(document.getElementById('editUIdx').value);
  sampleUsers[i].name=document.getElementById('editUName').value.trim();
  sampleUsers[i].email=document.getElementById('editUEmail').value.trim();
  closeModal('muEditUser');
  document.getElementById('editUPass').value='';
  renderManageTables();
  showToast('✅ User updated.');
}
function deleteUser(i){
  if(i===0){showToast('⚠ Cannot remove the primary admin account.');return;}
  sampleUsers.splice(i,1);
  renderManageTables();
  showToast('🗑️ User removed.');
}

/* — Add Family — */
function saveAddFamily(){
  const name=document.getElementById('muFName').value.trim();
  if(!name){showToast('⚠ Family name is required.');return;}
  const newId=sampleFamilies.length+1;
  sampleFamilies.push({id:newId,name});
  closeModal('muAddFamily');
  ['muFName','muFHead','muFContact','muFEmail','muFNotes'].forEach(id=>document.getElementById(id).value='');
  renderManageTables();
  showToast('✅ Family record added.');
}

/* — Edit Family — */
function openEditFamily(i){
  document.getElementById('editFIdx').value=i;
  document.getElementById('editFName').value=sampleFamilies[i].name;
  openModal('muEditFamily');
}
function saveEditFamily(){
  const i=parseInt(document.getElementById('editFIdx').value);
  sampleFamilies[i].name=document.getElementById('editFName').value.trim();
  closeModal('muEditFamily');
  renderManageTables();
  showToast('✅ Family record updated.');
}
function deleteFamily(i){
  sampleFamilies.splice(i,1);
  renderManageTables();
  showToast('🗑️ Family removed.');
}

/* — Add Member — */
function saveAddMember(){
  const name=document.getElementById('muMName').value.trim();
  const household=document.getElementById('muMHousehold').value.trim();
  const type=document.getElementById('muMType').value;
  if(!name||!household){showToast('⚠ Name and household are required.');return;}
  const colors=['ta-r1','ta-r2','ta-r3','ta-r4','ta-r5'];
  const initials=name.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
  membersData.push({name,household,type,color:colors[membersData.length%5],initials});
  closeModal('muAddMember');
  ['muMName','muMHousehold','muMContact'].forEach(id=>document.getElementById(id).value='');
  renderManageTables();
  renderMembers();
  showToast('✅ Member added.');
}

/* — Edit Member — */
function openEditMember(i){
  const m=membersData[i];
  document.getElementById('editMIdx').value=i;
  document.getElementById('editMName').value=m.name;
  document.getElementById('editMHousehold').value=m.household;
  document.getElementById('editMType').value=m.type;
  openModal('muEditMember');
}
function saveEditMember(){
  const i=parseInt(document.getElementById('editMIdx').value);
  membersData[i].name=document.getElementById('editMName').value.trim();
  membersData[i].household=document.getElementById('editMHousehold').value.trim();
  membersData[i].type=document.getElementById('editMType').value;
  const initials=membersData[i].name.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
  membersData[i].initials=initials;
  closeModal('muEditMember');
  renderManageTables();
  renderMembers();
  showToast('✅ Member updated.');
}

/* ══════════════ INIT ══════════════ */
renderDashboard();
renderResidents();
renderMembers();
renderDelinquents();
renderPayments();
renderThreads();
renderChat();
renderAnnouncements();
renderIssues();
renderOfficers();
renderReports();
renderManageTables();
renderReceipts();
</script>
</body>
</html>
