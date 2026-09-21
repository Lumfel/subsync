<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Resident Portal</title>
<script>
window.APP_BASE = '{{ request()->getBaseUrl() }}';
(function () {
    var base = window.APP_BASE || '';
    if (!base) return;
    var prefix = function (url) {
        if (typeof url === 'string' && url.charAt(0) === '/' && url.charAt(1) !== '/') return base + url;
        return url;
    };
    var origFetch = window.fetch;
    window.fetch = function (url, opts) { return origFetch.call(this, prefix(url), opts); };
    var OrigES = window.EventSource;
    window.EventSource = function (url, cfg) { return new OrigES(prefix(url), cfg); };
    window.EventSource.prototype = OrigES.prototype;
    window.EventSource.CONNECTING = 0; window.EventSource.OPEN = 1; window.EventSource.CLOSED = 2;
})();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap"></noscript>
<link rel="preconnect" href="https://unpkg.com" crossorigin>
<style>
/* Leaflet CSS is loaded lazily only when the map is opened */
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
  --radius: 16px;
  --radius-sm: 10px;
  --radius-xs: 6px;
}
* { box-sizing: border-box; margin: 0; padding: 0; }


body {
  font-family: 'Times New Roman', serif;
  background: #FAF6F0;
  min-height: 100vh;
  color: var(--text);
  font-size: 18px;
  line-height: 1.75;
}


/* ───── Page ───── */
.page { max-width: 1400px; width: 95%; margin: 0 auto; padding: 0 24px; }
@media(max-width:480px){ .page { padding: 0 12px; } }
[contenteditable]:focus { outline: none; }

/* ───── Tab nav ───── */
.tab-nav {
  display: flex; gap: 4px;
  background: #FFFFFF; border: 1px solid var(--glass-border);
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
.tab-btn.active { background: #2B2927; color: #FFFFFF; border-color: #2B2927; box-shadow: 0 4px 12px rgba(43,41,39,0.12); }
.tab-badge {
  background: #FAF6F0; color: #A07D53; border: 1px solid #E6DFD5;
  font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 10px;
}

/* ───── Tab panels ───── */
.tab-panel { display: none; animation: fadeUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) both; }
.tab-panel.active { display: block; }
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(6px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ───── Card ───── */
.card {
  background: #FFFFFF; border: 1px solid rgba(230,223,213,0.65);
  border-radius: var(--radius);
  padding: 28px 30px; margin-top: 20px;
  box-shadow: 0 4px 20px rgba(43,41,39,0.02);
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(43,41,39,0.04);
}
.card-title {
  font-size: 13px; font-weight: 600;
  color: var(--text); margin-bottom: 4px;
}
.card-subtitle {
  font-size: 13px; color: var(--text-dim);
  margin-bottom: 20px; line-height: 1.65;
}
.card-divider { height: 1px; background: rgba(230,223,213,0.65); margin: 16px 0; }

/* ───── Form helpers ───── */
.f-row { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
.f-field { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 140px; }
.f-label { font-size: 12px; color: var(--text-dim); letter-spacing: 0.03em; }
input[type=text], textarea, select {
  background: #FFFFFF; border: 1px solid var(--glass-border);
  color: var(--text); padding: 10px 14px; border-radius: var(--radius-sm);
  font-family: 'DM Sans', sans-serif; font-size: 14px; outline: none;
  transition: border-color 0.2s, background 0.2s; width: 100%;
}
input[type=text]:focus, textarea:focus, select:focus {
  background: #FFFFFF; border-color: rgba(160,125,83,0.4);
}
input::placeholder, textarea::placeholder { color: var(--text-dim); }
textarea { resize: vertical; min-height: 88px; line-height: 1.65; }
select { cursor: pointer; }
select option { background: #FFFFFF; color: var(--text); }

.btn {
  padding: 10px 20px; background: var(--accent-dim); border: 1px solid var(--accent-border);
  color: var(--accent); border-radius: var(--radius-sm);
  font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500;
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
  display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 18px; margin-bottom: 24px;
}
.member-card {
  background: #FAF6F0; border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm); padding: 14px;
  display: flex; flex-direction: column; gap: 8px;
  position: relative; transition: all 0.22s;
}
.member-card:hover { background: #FFFFFF; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(43,41,39,0.03); }
.member-avatar {
  width: 38px; height: 38px; border-radius: 50%;
  background: #FFFFFF; border: 1px solid var(--glass-border);
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 600; color: var(--accent);
}
.member-name { font-size: 13px; font-weight: 600; color: var(--text); }
.member-role {
  font-size: 10px; color: var(--accent); background: #FFFFFF;
  border: 1px solid var(--glass-border); padding: 2px 8px; border-radius: 20px;
  display: inline-block; width: fit-content;
  text-transform: uppercase; letter-spacing: 0.02em;
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
  padding: 16px; border-radius: var(--radius-sm);
  background: #FFFFFF; border: 1px solid var(--glass-border);
  margin-bottom: 12px; transition: all 0.2s;
  border-left: 3px solid var(--glass-border);
}
.ann-item.ann-item-notice { border-left-color: var(--blue); }
.ann-item.ann-item-urgent { border-left-color: var(--danger); }
.ann-item.ann-item-event  { border-left-color: var(--green); }
.ann-item:hover { transform: translateY(-1.5px); box-shadow: 0 6px 16px rgba(43,41,39,0.03); }
.ann-meta { display: flex; align-items: center; justify-content: space-between; margin-bottom: 7px; }
.ann-date { font-size: 11px; color: var(--text-dim); }
.ann-title { font-size: 14px; font-weight: 500; margin-bottom: 4px; text-align: center; }
.ann-body { font-size: 13px; color: var(--text-mid); line-height: 1.6; text-align: center; }

/* ── Dues Card ── */
.res-balance-card {
  background: #2B2927;
  color: #FFFFFF !important;
  border-radius: var(--radius);
  padding: 22px;
  border: 1px solid #2B2927;
  box-shadow: 0 10px 30px rgba(43,41,39,0.06);
  position: relative;
  overflow: hidden;
  margin-top: 12px;
}
.res-balance-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 80% 20%, rgba(160,125,83,0.18), transparent 60%);
  pointer-events: none;
}
.res-balance-card .balance-label {
  font-size: 10px;
  color: #FAF6F0;
  opacity: 0.65;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 6px;
}
.res-balance-card .balance-val {
  font-family: 'DM Sans', sans-serif;
  font-size: 26px;
  font-weight: 600;
  color: #FFFFFF;
}

/* ───── MESSAGES ───── */
.msg-layout { display:flex; gap:14px; }
.msg-sidebar {
  width: 196px; flex-shrink:0;
  background: #FAF7F2; border:1px solid var(--glass-border);
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
  background: #FFFFFF; border: 1px solid var(--glass-border);
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
  background: #FAF6F0;
  border-left: 1px solid var(--glass-border); border-right: 1px solid var(--glass-border);
  padding: 18px 16px; display: flex; flex-direction: column; gap: 12px;
  scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.15) transparent;
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


.bubble.from-admin { background: #FFFFFF; border: 1px solid var(--glass-border); border-bottom-left-radius: 4px; }
.bubble.from-mine  { background: var(--accent-dim); border: 1px solid var(--accent-border); border-bottom-right-radius: 4px; }
.bubble-time { font-size: 10px; color: var(--text-dim); margin-top: 3px; display: block; }
.bubble.from-mine .bubble-time { text-align: right; }

.msg-compose {
  background: #FFFFFF; border: 1px solid var(--glass-border); border-top: none;
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
  background: #2B2927; border: 1px solid rgba(43,41,39,0.1);
  color: #FAF6F0; font-size: 13px;
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
@media(max-width:768px) {
  .page-grid {
    grid-template-columns: 1fr !important;
  }
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

/* Mobile Header & Overlay Styles */
.mobile-header {
    display: none;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    background: #FFFFFF;
    border-bottom: 1px solid #E6DFD5;
    position: sticky;
    top: 0;
    z-index: 99;
}
.sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(43,41,39,0.3);
    z-index: 98;
    backdrop-filter: blur(2px);
}
.sidebar-overlay.show {
    display: block;
}

@media(max-width: 991px) {
    .bento-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .bento-col-12, .bento-col-8, .bento-col-6, .bento-col-4, .bento-col-3 {
        grid-column: span 12;
    }
    .mobile-header {
        display: flex;
    }
    .sidebar {
        left: -250px;
        transition: left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .sidebar.open {
        left: 0;
    }
    .main {
        margin-left: 0;
        padding: 24px 20px;
    }
}
</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/ui-polish.css') . '?v=' . filemtime(public_path('css/ui-polish.css')) }}">
<script src="{{ asset('js/ui.js') . '?v=' . filemtime(public_path('js/ui.js')) }}"></script>
</head>
<body>

<!-- Mobile Responsive Header -->
<div class="mobile-header">
  <div style="font-family:'DM Serif Display',serif; font-size:20px; color:#1C1A17; display:flex; align-items:center; gap:8px;">
    <span style="font-size:24px;">🏡</span> SubSync
  </div>
  <button class="btn" style="padding: 6px 12px; font-size: 13px;" onclick="toggleMobileSidebar()">☰ Menu</button>
</div>

<div class="sidebar-overlay" onclick="toggleMobileSidebar()"></div>

<!-- Left Sidebar Navigation -->
<aside class="sidebar">
  <div class="logo">
    <div style="font-family:'DM Serif Display',serif; font-size:24px; font-weight:400; color:#1C1A17; display:flex; align-items:center; gap:8px;">
      <span style="font-size:28px;">🏡</span> SubSync
    </div>
  </div>
  <ul style="margin-top:20px;">
    <li class="tab-btn active" onclick="switchTab('overview')" id="tab-overview">🏠 Overview</li>
    <li class="tab-btn" onclick="switchTab('issues')" id="tab-issues">📢 Concerns</li>
    <li class="tab-btn" onclick="switchTab('recs')" id="tab-recs">💡 Suggestions</li>
    <li class="tab-btn" onclick="switchTab('messages')" id="tab-messages">💬 Messages <span class="tab-badge" id="badge-messages" style="display:none;">0</span></li>
    <li class="tab-btn" onclick="switchTab('map')" id="tab-map">🗺️ Map</li>
    <li class="tab-btn" onclick="switchTab('expenses')" id="tab-expenses">💰 Expenses</li>
    <li class="tab-btn" onclick="switchTab('policies')" id="tab-policies">📜 Policies</li>
  </ul>

  <!-- Sidebar Profile Card -->
  <div style="position:absolute; bottom:30px; left:24px; right:24px; text-align:center;">
    <div class="avatar-wrap" onclick="document.getElementById('profileInput').click()" style="margin: 0 auto 12px; width:64px;">
      <img id="profilePreview" src="https://ui-avatars.com/api/?name={{ urlencode($residentName) }}&amp;background=c87941&amp;color=fff&amp;size=120&amp;font-size=0.4" alt="Profile" style="width:64px; height:64px; border-radius:50%; border: 2px solid var(--glass-border);">
      <input type="file" id="profileInput" accept="image/*" onchange="changeImage('profilePreview',this)" style="display:none;">
    </div>
    <div style="font-weight:600; font-size:14px; color:#2B2927; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" id="householdName" contenteditable="true" spellcheck="false" title="Click to edit name">{{ $residentName }}</div>
    <div style="font-size:11px; color:var(--text-dim); margin-top:2px;">Block/Lot: <span id="locationText">{{ $blockLot }}</span></div>
    
    <form id="logoutForm" action="{{ url('logout') }}" method="POST" style="display:none;">@csrf</form>
    <button onclick="confirmLogout()" class="btn btn-sm btn-full" style="margin-top:14px; background:rgba(240,128,128,0.1); border:1px solid rgba(240,128,128,0.25); color:#f08080;">⏻ Log Out</button>
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
        <p style="font-size:13px; color:var(--text-dim); line-height:1.6;">Manage household members, submit concerns, and check account balances from here.</p>
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

      <!-- Announcements Box (Span 8 - Front & Center) -->
      <div class="card bento-col-8">
        <div class="card-title">Announcements</div>
        <div class="card-subtitle">Neighborhood notices and community calendar events.</div>
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

      
        <!-- HOA Policies Preview Card (Span 12) -->
        <div class="card bento-col-12">
          <div class="card-title" style="display:flex;justify-content:space-between;align-items:center;">
            <span>HOA Community Policies &amp; Regulations</span>
            <button class="btn btn-sm" onclick="switchTab('policies')">View All Policies 📜</button>
          </div>
          <div class="card-subtitle">Essential guidelines for property maintenance, sanitation, security, and peaceful community living.</div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;margin-top:14px;">
            <div style="padding:14px;background:rgba(160,125,83,0.06);border:1px solid rgba(160,125,83,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
              <div style="font-weight:600;color:var(--accent);margin-bottom:4px;">1. Waste Management &amp; Sanitation</div>
              <div style="font-size:12px;color:var(--text-mid);line-height:1.5;">Garbage segregation, scheduled collection, and sanitation responsibilities.</div>
            </div>
            <div style="padding:14px;background:rgba(160,125,83,0.06);border:1px solid rgba(160,125,83,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
              <div style="font-weight:600;color:var(--accent);margin-bottom:4px;">2. Property Maintenance &amp; Building</div>
              <div style="font-size:12px;color:var(--text-mid);line-height:1.5;">Architectural guidelines, lawn upkeep, and construction clearance permits.</div>
            </div>
            <div style="padding:14px;background:rgba(160,125,83,0.06);border:1px solid rgba(160,125,83,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
              <div style="font-weight:600;color:var(--accent);margin-bottom:4px;">4. Quiet Hours &amp; Noise Control</div>
              <div style="font-size:12px;color:var(--text-mid);line-height:1.5;">Standard quiet hours (10:00 PM – 6:00 AM) and social gathering guidelines.</div>
            </div>
            <div style="padding:14px;background:rgba(160,125,83,0.06);border:1px solid rgba(160,125,83,0.2);border-radius:var(--radius-sm);cursor:pointer;" onclick="switchTab('policies')">
              <div style="font-weight:600;color:var(--accent);margin-bottom:4px;">5. Security, Gates &amp; Visitors</div>
              <div style="font-size:12px;color:var(--text-mid);line-height:1.5;">Gate access procedures, visitor registration, and vehicle RFID guidelines.</div>
            </div>
          </div>
        </div>
    </div>
  </div>

  <!-- ═══════════ MESSAGES ═══════════ -->
  <div class="tab-panel" id="panel-messages">
    <div class="card" style="padding:24px; margin-top:0;">
      <div class="card-title">Community Group Chat</div>
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
          <div class="msg-body" id="msgBody">
            <div style="color:var(--text-dim);text-align:center;padding:40px;">Select a conversation to start messaging.</div>
          </div>
          <div class="msg-compose">
            <textarea id="msgInput" placeholder="Type a message…" rows="1" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault(); sendMessage();}"></textarea>
            <button class="send-btn" onclick="sendMessage()">➔</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════ ISSUES ═══════════ -->
  <div class="tab-panel" id="panel-issues">
    <div class="card" style="margin-top:0;">
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
          <span class="f-label">📸 Photos (optional, up to 5)</span>
          <input type="file" id="issueImages" accept="image/*" multiple style="font-size:13px;padding:6px 0;">
          <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">Attach photos to help our team understand the issue.</div>
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
      <div class="card-title" style="display:flex;justify-content:space-between;align-items:center;">My Submitted Reports</div>
      <div id="issueList"><div class="skeleton sk-card" style="margin-bottom:10px;"></div><div class="skeleton sk-card"></div></div>
    </div>
  </div>

  <!-- ═══════════ RECOMMENDATIONS ═══════════ -->
  <div class="tab-panel" id="panel-recs">
    <div class="card" style="margin-top:0;">
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
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin-left:auto;">
          <input type="checkbox" id="res-layer-heatmap" checked style="accent-color:var(--accent);">
          <span style="background:linear-gradient(to right,#1a003e,#5c0099,#0044bb,#cc4400,#ff2200);border-radius:4px;width:36px;height:11px;display:inline-block;"></span>
          Heatmap
        </label>
      </div>
    </div>
  </div>

  <!-- ═══════════ HOA EXPENSES ═══════════ -->
  <div class="tab-panel" id="panel-expenses">
    <div class="card">
      <div class="card-title">HOA Expenses &amp; Financials</div>
      <div id="expensesList"><div class="empty-state">Loading expenses...</div></div>
    </div>
  </div>


  <!-- ═══════════ HOA POLICIES ═══════════ -->
    <div class="tab-panel" id="panel-policies">
      <div class="card" style="margin-bottom:16px;background:linear-gradient(135deg,#FAF4E7,#FAF6F0);border-left:4px solid var(--accent);">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
          <div style="font-size:32px;">📜</div>
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
          <li>Residents are encouraged to report subdivision concerns through SubSync's Issue Reports feature. All submissions are documented and reviewed by the Administration.</li>
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



</main>
<script>
/* ══ UTILS ══ */
function toggleMobileSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  if (sidebar) sidebar.classList.toggle('open');
  if (overlay) overlay.classList.toggle('show');
}
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
function showToast(msg, type){
  if(window.SubUI && typeof SubUI.toast==='function'){ SubUI.toast(msg, type||'info'); return; }
  const t=document.getElementById('toast');
  if(!t) return;
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
  
  // Auto-close sidebar on mobile
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  if (sidebar) sidebar.classList.remove('open');
  if (overlay) overlay.classList.remove('show');
}

/* ══ MEMBERS ══ */
let members=[];
async function loadHouseholdMembers(){
  if(!HOUSE_ID){ renderMembers(); return; }
  try{
    const data=await fetch('/api/household-members?house_id='+HOUSE_ID,{headers:{'Accept':'application/json'}}).then(r=>r.json());
    members=Array.isArray(data)?data.map(m=>({id:m.id,name:m.name,role:m.relationship||'Member'})):[];
  }catch(e){ members=[]; }
  renderMembers();
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
  const _annNotifView = document.getElementById('annNotifViewBtn');
  if (_annNotifView) {
    _annNotifView.onclick = ()=>{
      const _n = document.getElementById('annNotif');
      if (_n) _n.classList.remove('show');
      document.getElementById('announcementList').closest('.card').scrollIntoView({behavior:'smooth'});
    };
  }
  // Safety-net: announcements poll every 60s (SSE handles real-time)
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
  }, 60000);

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
});
function viewAnnouncement(id) {
  const ann = announcements.find(a => a.id === id);
  if (ann) {
    document.getElementById('annV-tag').className = `pill pill-${ann.tag||'notice'}`;
    document.getElementById('annV-tag').textContent = ann.tag.charAt(0).toUpperCase() + ann.tag.slice(1);
    document.getElementById('annV-priority').style.display = ann.priority === 'High' ? '' : 'none';
    document.getElementById('annV-title').textContent = ann.title;
    document.getElementById('annV-by').textContent = ann.posted_by;
    document.getElementById('annV-meta').innerHTML = `<span>${ann.created_at}</span>${ann.target && ann.target !== 'All Residents' ? `<span> • ${ann.target}</span>` : ''}${ann.event_date ? `<span> • ${ann.event_date}</span>` : ''}`;
    document.getElementById('annV-content').textContent = ann.content;
    document.getElementById('modal-annView').classList.add('open');
  }
}

let _annShowAll=false;
function annItem(a){
  const tagLabel={notice:'Notice',urgent:'Urgent',event:'Event'};
  const meta=[];
  if(a.priority==='High') meta.push('<span style="color:#f08080;font-weight:700;font-size:11px;">⚡ High Priority</span>');
  if(a.target&&a.target!=='All Residents') meta.push('<span style="font-size:11px;color:var(--text-mid);">👥 '+a.target+'</span>');
  if(a.event_date) meta.push('<span style="font-size:11px;color:var(--text-mid);">📅 '+a.event_date+'</span>');
  const statusClass = a.status === 'Archived' ? 'pill-inactive' : 'pill-active';
  meta.push('<span class="pill '+statusClass+'" style="font-size:11px;">'+a.status+'</span>');
  return `
  <div class="ann-item ann-item-${a.tag||'notice'}" onclick="viewAnnouncement(${a.id})">
    <div class="ann-meta">
      <span class="pill pill-${a.tag||'notice'}">${tagLabel[a.tag]||'Notice'}</span>
      <span class="ann-date">${a.created_at}</span>
    </div>
    ${meta.length?'<div style="display:flex;gap:10px;flex-wrap:wrap;margin:4px 0;">'+meta.join('')+'</div>':''}
    <div class="ann-title">${a.title}</div>
    <div class="ann-body">${a.content}</div>
  </div>`;
}
function renderAnnouncements(){
  const el=document.getElementById('announcementList');
  if(!announcements.length){ el.innerHTML='<div class="empty-state"><div class="empty-icon">📢</div>No announcements yet.</div>'; return; }
  const SHOW=5;
  const list=_annShowAll?announcements:announcements.slice(0,SHOW);
  el.innerHTML=list.map(annItem).join('');
  if(announcements.length>SHOW){
    const remaining=announcements.length-SHOW;
    el.insertAdjacentHTML('beforeend',
      `<button class="btn btn-sm" style="width:100%;margin-top:12px;justify-content:center;" onclick="toggleAnnShowAll()">${_annShowAll?'Show fewer':'Show all announcements ('+remaining+' more)'}</button>`);
  }
}
function toggleAnnShowAll(){ _annShowAll=!_annShowAll; renderAnnouncements(); }

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
  // Sort threads to put group chat first (Community Chat)
  const sortedData = [...data].sort((a, b) => {
    const isGroupA = a.title === 'Community Chat';
    const isGroupB = b.title === 'Community Chat';
    if (isGroupA && !isGroupB) return -1;
    if (!isGroupA && isGroupB) return 1;
    return 0;
  });
  if(sidebar){
    sidebar.innerHTML=sortedData.map((t,i)=>`<div class="msg-thread-item${i===0?' active':''}" onclick="selectResThread(${t.id},this)">
      <div class="msg-thread-avatar ta-admin">${(t.title||'?').substring(0,2).toUpperCase()}</div>
      <div class="msg-thread-info">
        <div class="msg-thread-name">${t.title}</div>
        <div class="msg-thread-preview">${t.last_message||'No messages yet'}</div>
      </div>
      <button class="msg-thread-del" onclick="event.stopPropagation();deleteResidentConversation(${t.id})" title="Delete">🗑</button>
    </div>`).join('');
  }
  // Load first thread (will be group chat if it exists)
  currentThread=sortedData[0].id;
  document.getElementById('chat-name').textContent=sortedData[0].title||'Admin';
  document.getElementById('chat-avatar').textContent=(sortedData[0].title||'SA').substring(0,2).toUpperCase();
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
const sendMsg = sendMessage; // alias for old inline handlers

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

function deleteInBadge(i){
  if(i.status!=='Resolved') return '';
  const base = i.resolved_at || i.created_at;
  if(!base) return '';
  const d = new Date(base.length<=10 ? base+'T00:00:00' : base);
  if(isNaN(d.getTime())) return '';
  const daysLeft = Math.max(0, Math.ceil(30 - (Date.now()-d.getTime())/86400000));
  if(daysLeft<=0) return '';
  return `<span style="font-size:10px;font-weight:600;color:var(--text-dim);margin-left:6px;" title="Auto-deletes after 30 days">🗑 deletes in ${daysLeft}d</span>`;
}

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
    const priSpan=i.priority?`<span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;${priStyle[i.priority]||''}">${priLabel}</span><span style="font-size:10px;color:var(--text-dim);margin-left:2px;">assigned</span>`:'';
    const isCritical=i.priority==='Critical';
    // Photo gallery
    const photos=(i.media||[]);
    const gallery=photos.length?`<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;">${photos.map(m=>`<a href="${m.url}" target="_blank"><img src="${m.url}" style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid rgba(255,255,255,0.1);" loading="lazy"></a>`).join('')}</div>`:'';
    return `
    <div class="issue-item" style="${isCritical?'border-left:3px solid #ff4040;':''}">
      <div class="issue-top">
        <div class="issue-title">${i.title}</div>
        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">${priSpan}${statusPill[i.status]||''}${deleteInBadge(i)}</div>
      </div>
      <div class="issue-body">${i.description}</div>
      ${gallery}
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
  const title=document.getElementById('issueTitle').value.trim();
  const description=document.getElementById('issueBody').value.trim();
  const latitude=document.getElementById('issue-lat').value||null;
  const longitude=document.getElementById('issue-lng').value||null;
  if(!category||!title||!description){showToast('⚠ Please fill in all fields.');return;}
  const fd=new FormData();
  fd.append('category',category);
  fd.append('title',title);
  fd.append('description',description);
  if(latitude) fd.append('latitude',latitude);
  if(longitude) fd.append('longitude',longitude);
  const imgInput=document.getElementById('issueImages');
  if(imgInput&&imgInput.files.length){
    Array.from(imgInput.files).slice(0,5).forEach(f=>fd.append('images[]',f));
  }
  const res=await fetch('/api/issues',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:fd});
  const data=await res.json();
  if(data.success){
    ['issueCategory','issueTitle','issueBody','issue-lat','issue-lng'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
    if(imgInput) imgInput.value='';
    const disp=document.getElementById('issue-loc-display');
    if(disp) disp.textContent='No location set';
    closeIssuePicker();
    await loadIssues();
  loadHOAExpenses();
  loadHOAPolicies();
    showToast('✅ Report submitted. The admin will review it shortly.');
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
let hoaExpenses = [];
async function loadHOAExpenses(){
  try {
    hoaExpenses = await fetch('/api/hoa-expenses',{headers:{'Accept':'application/json'}}).then(r=>r.json());
    renderHOAExpenses();
  } catch(e){}
}
window.loadHOAExpenses = loadHOAExpenses;

function renderHOAExpenses(){
  const el = document.getElementById('expensesList');
  if(!el) return;
  if(!hoaExpenses.length){
    el.innerHTML = '<div class="empty-state">No expenses recorded yet.</div>';
    return;
  }
  let html = '<table class="data-table" style="width:100%;"><thead><tr><th>Date</th><th>Category</th><th>Title</th><th>Amount</th><th>Status</th></tr></thead><tbody>';
  hoaExpenses.forEach(e => {
    html += `<tr>
      <td>${e.expense_date}</td>
      <td>${e.category}</td>
      <td>${e.title}</td>
      <td>₱${parseFloat(e.amount).toLocaleString(undefined, {minimumFractionDigits:2})}</td>
      <td>${e.status}</td>
    </tr>`;
  });
  html += '</tbody></table>';
  el.innerHTML = html;
}

let hoaPolicies = [];
async function loadHOAPolicies(){
  try {
    const res = await fetch('/api/hoa-policies', {headers:{'Accept':'application/json'}});
    const data = await res.json();
    hoaPolicies = Array.isArray(data) ? data : [];
    renderHOAPoliciesPreview();
  } catch(e){}
}
window.loadHOAPolicies = loadHOAPolicies;

function renderHOAPoliciesPreview(){
  const overviewEl = document.getElementById('overviewHoaPolicies');
  if(!overviewEl) return;
  
  if(!hoaPolicies.length){
    overviewEl.innerHTML = '<tr><td colspan="3" style="text-align:center;color:var(--text-dim);padding:20px;">No HOA policies recorded yet.</td></tr>';
    return;
  }

  let html = '';
  hoaPolicies.slice(0, 3).forEach(p => {
    html += `<tr>
      <td style="font-weight:500;color:var(--text);"><span class="icon" style="color:var(--accent);margin-right:8px;">📜</span>${p.title}</td>
      <td><span class="pill" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">${p.category}</span></td>
      <td>${p.status}</td>
    </tr>`;
  });
  overviewEl.innerHTML = html;
}

setInterval(async () => {
  if(document.hidden) return;
  if(document.getElementById('panel-expenses')?.classList.contains('active')){
    try{ await loadHOAExpenses(); }catch(e){}
  }
}, 60000);

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

/* ══ INIT ══ */
async function loadPortalData() {
    try {
        const data = await fetch('/api/resident/portal-data', {headers: {'Accept': 'application/json'}}).then(r => r.json());
        
        if (data.resident) {
            // Update resident info
            RESIDENT_ID = data.resident.id;
            HOUSE_ID = data.resident.house_id;
            RESIDENT_NAME = data.resident.name;
            
            // Update greeting
            const greetEl = document.getElementById('greetingText');
            if (greetEl) {
                const firstName = data.resident.name.split(' ')[0];
                const hrs = new Date().getHours();
                let greet = "Welcome back";
                if (hrs < 12) greet = "Good morning";
                else if (hrs < 18) greet = "Good afternoon";
                else greet = "Good evening";
                greetEl.innerHTML = `${greet}, ${firstName} 👋`;
            }

            // Update household name and location
            const nameEl = document.getElementById('householdName');
            if (nameEl) nameEl.textContent = data.resident.name;
            const locEl = document.getElementById('locationText');
            if (locEl) locEl.textContent = data.resident.block_lot;
        }

        if (data.members) {
            members = data.members.map(m => ({ id: m.id, name: m.name, role: m.role }));
        } else {
            members = [];
        }
        renderMembers();

        if (data.announcements) {
            announcements = data.announcements;
            if (announcements.length) lastAnnouncementId = announcements[0].id;
            renderAnnouncements();
        }

        if (data.issues) {
            issues = data.issues;
            renderIssues();
        }

        if (data.recommendations) {
            recs = data.recommendations;
            renderRecs();
        }

        if (data.expenses) {
            hoaExpenses = data.expenses;
            renderHOAExpenses();
        }

        if (data.policies) {
            hoaPolicies = data.policies;
            renderHOAPoliciesPreview();
        }
    } catch (e) {
        console.error('Failed to load portal data:', e);
        // Fallback to individual loads
        loadHouseholdMembers();
        loadAnnouncements();
        loadIssues();
        loadRecs();
        loadHOAExpenses();
        loadHOAPolicies();
    }
}

loadPortalData();
renderMessages();

/* ── Real-time SSE (replaces aggressive polling) ── */
let _sseEventId = 0;

function connectResidentSSE(){
  if(document.hidden){ setTimeout(connectResidentSSE, 2000); return; }
  const url = `/api/stream/updates?lastEventId=${_sseEventId}`;
  const es  = new EventSource(url);

  // Fresh connection — grab latest data once
  es.addEventListener('sync', () => {
    loadAnnouncements();
    loadIssues();
    loadHOAExpenses();
    loadHOAPolicies();
  });

  // New / changed announcement
  es.addEventListener('announcement', async () => {
    try{
      const data = await fetch('/api/announcements').then(r=>r.json());
      if(!Array.isArray(data) || !data.length) return;
      const newOnes = data.filter(a => a.id > lastAnnouncementId);
      if(newOnes.length) showAnnBanner(newOnes[0]);
      lastAnnouncementId = data[0].id;
      announcements = data;
      renderAnnouncements();
    }catch(e){}
  });

  // New / changed issue (refresh my issues so status updates show instantly)
  es.addEventListener('issue', async () => {
    try{
      const myIss = await fetch('/api/issues/my',{headers:{'Accept':'application/json'}}).then(r=>r.json());
      if(Array.isArray(myIss)){ issues = myIss; renderIssues(); }
    }catch(e){}
  });

  // HOA expense update
  es.addEventListener('hoa-expense', () => {
    loadHOAExpenses();
  });

  // Reconnect cycle — server sends lastEventId
  es.addEventListener('reconnect', (e) => {
    try{
      const evt = JSON.parse(e.data);
      if(evt.lastEventId) _sseEventId = evt.lastEventId;
    }catch(err){}
    es.close();
    setTimeout(connectResidentSSE, 200);
  });

  es.onerror = () => {
    es.close();
    setTimeout(connectResidentSSE, 3000);
  };

  document.addEventListener('visibilitychange', () => {
    if(!document.hidden && es.readyState === EventSource.CLOSED){
      setTimeout(connectResidentSSE, 500);
    }
  }, { once: true });
}
setTimeout(connectResidentSSE, 2000);

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
</script>

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
      AB + '/vendor/leaflet/leaflet-heat.js',
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
let resMapInst = null, resLayerHeatmap = null;

function makeResCircle(color) {
  return L.divIcon({
    className: '',
    html: `<div style="width:13px;height:13px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.85);box-shadow:0 1px 5px rgba(0,0,0,.5);"></div>`,
    iconSize:[13,13], iconAnchor:[6,6], popupAnchor:[0,-9]
  });
}

function makeResIssueIcon(color, num) {
  return L.divIcon({
    className: '',
    html: `<div style="width:24px;height:24px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.9);box-shadow:0 2px 6px rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;font-family:sans-serif;">${num}</div>`,
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

    fetch('/api/map/all').then(r=>r.json()).then(data=>{
      (data.facilities || []).forEach(f=>{
        let popupContent = '<strong>' + f.name + '</strong>';
        if (f.description) popupContent += '<br><small>' + f.description + '</small>';
        L.marker([parseFloat(f.latitude),parseFloat(f.longitude)],{icon:makeResCircle('#4CAF50')}).bindPopup(popupContent).addTo(resMapInst);
      });

      (data.issues || []).forEach((i, index)=>{
        const color = i.status==='Resolved' ? '#888' : (i.status==='In Progress'?'#f5a623':'#e05555');
        let popupContent = '<strong>Report #' + (index + 1) + ': ' + i.title + '</strong><br><small>' + i.status + '</small>';
        L.marker([parseFloat(i.latitude),parseFloat(i.longitude)],{icon:makeResIssueIcon(color, index + 1)}).bindPopup(popupContent).addTo(resMapInst);
      });

      if(HOUSE_ID){
        const own = (data.households || []).find(h=>h.id===HOUSE_ID);
        if(own && own.latitude && own.longitude){
          L.marker([parseFloat(own.latitude),parseFloat(own.longitude)],{icon:makeResCircle('#4287f5')})
            .bindPopup('<strong>My Household</strong><br><small>'+(own.block_lot_number||'')+'</small>')
            .addTo(resMapInst);
        }
      }
      resLayerHeatmap.setLatLngs((data.issues || []).map(i => [parseFloat(i.latitude), parseFloat(i.longitude), 1.0]));
    }).catch(err => console.error('Map data fetch error:', err));
    });
}

let issuePickerMap = null, issuePickerPin = null;

function useMyLocation() {
  if (!navigator.geolocation) { showToast('Geolocation not supported.'); return; }
  navigator.geolocation.getCurrentPosition(pos=>{
    const lat=pos.coords.latitude, lng=pos.coords.longitude;
    document.getElementById('issue-lat').value=lat;
    document.getElementById('issue-lng').value=lng;
    document.getElementById('issue-loc-display').textContent='📍 ' + lat.toFixed(5) + ', ' + lng.toFixed(5);
    if(issuePickerPin) issuePickerPin.setLatLng([lat,lng]);
    else if(issuePickerMap) issuePickerPin=L.marker([lat,lng]).addTo(issuePickerMap);
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
        else issuePickerPin=L.marker([lat,lng]).addTo(issuePickerMap);
        document.getElementById('issue-lat').value=lat.toFixed(7);
        document.getElementById('issue-lng').value=lng.toFixed(7);
        document.getElementById('issue-loc-display').textContent='📍 ' + lat.toFixed(5) + ', ' + lng.toFixed(5);
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
      <button class="btn btn-sm" id="annV-archBtn" style="background:var(--yellow-dim);border-color:rgba(196,150,70,0.3);color:var(--yellow);" onclick="toggleAnnArchive()">📦 Archive</button>
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
</body>
</html>










