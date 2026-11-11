/* ══════════════ UTILS ══════════════ */
function nowTime(){ return new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'}); }
function todayStr(){ return new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}); }
function fmt(n){ return '₱'+Number(n||0).toLocaleString(); }

function confirmLogout(){
  SubUI.confirm({
    title: 'Log out of SubSync?',
    message: 'You will be returned to the sign-in page. Any unsaved changes will be lost.',
    confirmText: 'Log Out',
    cancelText: 'Stay signed in',
    danger: true,
    icon: '!'
  }).then(ok => { if(ok) document.getElementById('logoutForm').submit(); });
}

function showToast(msg){
  const t=document.getElementById('toast');
  t.textContent=msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),2800);
}

function formatAlertTime(value){
  const date=new Date(value);
  if(Number.isNaN(date.getTime())) return '';
  return date.toLocaleString('en-PH',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'});
}
function alertTab(tab){
  const item=document.querySelector(`.nav-item[onclick*="nav('${tab}'"]`);
  nav(tab,item||null);
  document.getElementById('alertsPopover')?.classList.remove('open');
  document.getElementById('alertsButton')?.setAttribute('aria-expanded','false');
}
async function loadAlerts(showLoading=false){
  const list=document.getElementById('alertsList');
  if(!list) return;
  if(showLoading) list.innerHTML='<div class="alerts-empty">Loading alerts…</div>';
  try{
    const data=await apiGet('/api/alerts');
    const alerts=Array.isArray(data.alerts)?data.alerts:[];
    const badge=document.getElementById('alertsBadge');
    if(badge){ badge.textContent=data.unread_count||0; badge.style.display=data.unread_count?'inline-flex':'none'; }
    list.innerHTML=alerts.length?alerts.map(a=>`
      <div class="alert-item" onclick="alertTab('${a.tab}')">
        <span class="alert-mark ${a.severity||'notice'}"></span>
        <div><div class="alert-title">${escHtml(a.title)}</div><div class="alert-message">${escHtml(a.message)}</div><div class="alert-time">${formatAlertTime(a.created_at)}</div></div>
      </div>`).join(''):'<div class="alerts-empty">No alerts require your attention.</div>';
  }catch(e){ list.innerHTML='<div class="alerts-empty">Alerts could not be loaded. Refresh and try again.</div>'; }
}
function toggleAlerts(){
  const popover=document.getElementById('alertsPopover');
  const button=document.getElementById('alertsButton');
  if(!popover||!button) return;
  const open=popover.classList.toggle('open');
  button.setAttribute('aria-expanded',open?'true':'false');
  if(open) loadAlerts();
}
document.addEventListener('click',event=>{
  const wrap=document.querySelector('.alerts-wrap');
  if(wrap&&!wrap.contains(event.target)){
    document.getElementById('alertsPopover')?.classList.remove('open');
    document.getElementById('alertsButton')?.setAttribute('aria-expanded','false');
  }
});
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
  if(id==='ann') loadAnnTemplates();
  document.getElementById('modal-'+id).classList.add('open');
}
function closeModal(id){ document.getElementById('modal-'+id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(o=>{
  o.addEventListener('click',e=>{ if(e.target===o) o.classList.remove('open'); });
});

/* ══════════════ NAVIGATION ══════════════ */
const panelTitles={
  dashboard:'Dashboard Overview',residents:'Residents',members:'Household Members',
  messages:'Messages',announcements:'Announcements',issues:'Issues',
  manageusers:'Households & Users',officers:'Officers',recommendations:'Suggestions',analytics:'Analytics',
  mapping:'Map',reports:'Documents',policies:'Policies',
  gcash:'Finance', archives:'Archives'
};

function nav(name, el){
  if(window.innerWidth <= 991) closeSidebar();
  document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  const panel=document.getElementById('panel-'+name);
  if(panel) panel.classList.add('active');
  if(el) el.classList.add('active');
  else document.querySelectorAll('.nav-item').forEach(n=>{
    if((n.getAttribute('onclick')||'').indexOf("nav('"+name+"'") !== -1 || n.textContent.trim().toLowerCase().indexOf(name) !== -1) n.classList.add('active');
  });
  document.getElementById('topbarTitle').textContent = panelTitles[name]||name;
  if(name==='messages'){
    document.getElementById('msgBadge').style.display='none';
    loadThreads();
    // Set header to Community Chat by default, will be updated when thread is selected
    document.getElementById('msgHeaderTitle').textContent = 'Community Chat';
    // Start real-time polling for new messages (every 10 seconds)
    if(!window.messagePollInterval){
      window.messagePollInterval = setInterval(() => {
        // Only refresh if we're viewing the messages panel and have a thread selected
        if(document.getElementById('panel-messages').classList.contains('active')) refreshAdminMessages();
      }, 10000);
    }
  }
  if(name==='issues') renderIssues();
  if(name==='members') renderMembers();
    if(name==='analytics') loadD3().then(function(){ setTimeout(function(){ renderAnalytics(); loadAnalyticsSecurityLogs(); }, 30); }).catch(function(){});
  if(name==='reports'){ renderReports(); loadReportTemplatesAdmin(); loadIssueData(); }
  if(name==='announcements') loadAdminEvents();
  if(name==='mapping') setTimeout(initMap, 80);
  if(name==='gcash'){ loadGcashSettings(); loadPaymentsQueue(); loadAdminExpenses(); loadAssessments(); loadAging(); loadBudgets(); financeTab('overview'); loadFinanceOverview(); }
  if(name==='policies') loadAdminPolicies();
  if(name==='archives') loadArchives('issues');
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
let expenses = [];
let membersData = [];
let payments = [];
let paymentStatuses = [];
let residentCap = 24;
let issueCap = 30;
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
async function apiPatch(url,data={}){ const r=await fetch(url,{method:'PATCH',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(data)}); return r.json(); }

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
  expenses       = d.expenses||[];
  window.__subData = d;

  // Reuse for Manage panel — no extra API calls needed
  muHouseholds = households;
  muResidents  = residents;
  muMembers    = hhMembers;
  muFamilies   = Array.isArray(d.families) ? d.families : [];

  const fin = d.finSummary||{};

  // update stat cards
  if(document.getElementById('statResidents')) document.getElementById('statResidents').textContent = residents.length;
  if(document.getElementById('statActive'))    document.getElementById('statActive').textContent    = residents.filter(r=>r.status==='Active').length;
  if(document.getElementById('statPaymentStatus'))    document.getElementById('statPaymentStatus').textContent    = paymentStatuses.length;
  if(document.getElementById('paymentStatusBadge'))     document.getElementById('paymentStatusBadge').textContent    = paymentStatuses.length;
  const pendingCount = residents.filter(r=>r.status==='Pending').length;
  if(document.getElementById('statPending'))      document.getElementById('statPending').textContent = pendingCount;
  if(document.getElementById('pendingNoticeCount')) document.getElementById('pendingNoticeCount').textContent = pendingCount;
  const notice = document.getElementById('pendingNotice');
  if(notice) notice.style.display = pendingCount > 0 ? 'flex' : 'none';

  // Update Daily Action Center
  const pendingPays = Number(d.pendingPaymentsCount || 0);
  const criticalOnly = issues.filter(i => i.status !== 'Resolved' && (i.priority === 'Critical' || i.overdue)).length;
  const totalActions = pendingCount + pendingPays + criticalOnly;
  const actionCenter = document.getElementById('adminActionCenter');
  if(actionCenter){
    const itemRes = document.getElementById('actionItemRegistrations');
    const itemPay = document.getElementById('actionItemPayments');
    const itemIss = document.getElementById('actionItemIssues');
    if(itemRes){
      itemRes.style.display = pendingCount > 0 ? 'flex' : 'none';
      const c = document.getElementById('actionPendingResCount');
      if(c) c.textContent = pendingCount;
    }
    if(itemPay){
      itemPay.style.display = pendingPays > 0 ? 'flex' : 'none';
      const c = document.getElementById('actionPendingPayCount');
      if(c) c.textContent = pendingPays;
    }
    if(itemIss){
      itemIss.style.display = criticalOnly > 0 ? 'flex' : 'none';
      const c = document.getElementById('actionUrgentIssueCount');
      if(c) c.textContent = criticalOnly;
    }
    const summary = document.getElementById('actionCenterSummary');
    if(summary) summary.textContent = totalActions > 0 ? `${totalActions} task(s) requiring attention` : 'All caught up';
    actionCenter.style.display = totalActions > 0 ? 'block' : 'none';
  }
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

  // Members panel stats
  // (handled by renderMembers())

  renderDashboard();
  renderResidents();
  renderMembers();
  renderAnnouncements();
  renderIssues();
  renderOfficers();
  renderRecommendations();
  renderManageTables();

  // Baseline for real-time polls — set AFTER initial render so polls won't trigger on existing data
  if(announcements.length) _adminLastAnnId = announcements[0].id;
  _adminLastIssueCount = issues.length;
}

/* ══════════════ RENDER FUNCTIONS ══════════════ */

function renderDashboard(){
  const statusPill={Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'};
  const recent=issues.slice(0,2);
  document.getElementById('dashIssueList').innerHTML=recent.length?recent.map(i=>`
    <div onclick="nav('issues');setTimeout(()=>openIssueDetail(${i.id}),300)" style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.05);cursor:pointer;transition:background .15s;border-radius:4px;padding-left:4px;" onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background=''" title="Click to view issue details">
      <div>
        <div style="font-size:13px;font-weight:500;">${i.title}</div>
        <div style="font-size:11px;color:var(--text-dim);">${i.resident} · ${i.block_lot}</div>
      </div>
      ${statusPill[i.status]||''}
    </div>`).join(''):'<div style="color:var(--text-dim);font-size:13px;padding:10px 0;">No issues yet.</div>';

  document.getElementById('activityFeed').innerHTML=(()=>{
    const events=[];
    issues.slice(0,3).forEach(i=>events.push({ts:i.created_at||'',icon:'alert',color:'var(--yellow)',text:`<b>${i.resident||'A resident'}</b> filed an issue: <i>${i.title}</i>`,pill:i.status==='Resolved'?'<span class="pill pill-resolved">Resolved</span>':i.status==='In Progress'?'<span class="pill pill-progress">In Progress</span>':'<span class="pill pill-pending">Pending</span>'  }));
    announcements.slice(0,2).forEach(a=>events.push({ts:a.created_at||'',icon:'megaphone',color:'var(--accent)',text:`Announcement posted: <b>${a.title||a.message?.substring(0,50)+'...'}</b>`,pill:''}));
    finRecs.slice(0,3).forEach(f=>events.push({ts:f.record_date||'',icon:f.record_type==='Payment'?'coins':'file',color:f.record_type==='Payment'?'var(--green)':'#f08080',text:`<b>${f.resident||'Resident'}</b> — ${f.record_type}: ₱${Number(f.amount).toLocaleString()}`,pill:''}));
    if(!events.length) return '<div style="color:var(--text-dim);font-size:13px;padding:10px 0;">No recent activity.</div>';
    return events.slice(0,6).map(e=>`
      <div style="display:flex;align-items:flex-start;gap:10px;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.05);">
        <span style="flex-shrink:0;color:${e.color};">${SubUI.icon(e.icon,17)}</span>
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
          <input type="checkbox" class="pending-check" value="${r.id}" style="width:16px;height:16px;flex-shrink:0;">
          <div class="r-avatar ${r.color}" style="width:34px;height:34px;font-size:13px;">${r.initials}</div>
          <div>
            <div style="font-weight:600;font-size:13px;">${r.name}</div>
            <div style="font-size:11px;color:var(--text-dim);">${r.email||'—'}</div>
          </div>
        </div>
        ${r.contact_number?`<div style="font-size:11px;color:var(--text-dim);margin-bottom:10px;">${SubUI.icon('phone',12)} ${r.contact_number}</div>`:''}
        <div style="display:flex;gap:8px;">
          <button class="btn btn-sm" style="background:#22c55e;border-color:#22c55e;color:#062b12;flex:1;" onclick="approveRegistration(${r.id})">${SubUI.icon('check',13)} Approve</button>
          <button class="btn btn-sm btn-danger" style="flex:1;" onclick="rejectRegistration(${r.id})">${SubUI.icon('x',13)} Reject</button>
        </div>
      </div>`).join('');
  }
  const selectAll=document.getElementById('pendingSelectAll');
  if(selectAll) selectAll.checked=false;
  const filtered=active.filter(r=>{
    const matchQ=!q||r.name.toLowerCase().includes(q.toLowerCase())||(r.block_lot||'').toLowerCase().includes(q.toLowerCase());
    const matchB=!b||(r.block_lot||'').toLowerCase().includes(b.toLowerCase().replace('block ','blk '));
    return matchQ&&matchB;
  });
  const payLabel=bal=>bal<=0?'Paid':(bal<1000?'Partial':'Unpaid');
  const payPill=bal=>bal<=0?'pill-resolved':(bal<1000?'pill-pending':'pill-urgent');
  const _shown=filtered.slice(0,residentCap); const _more=filtered.length-_shown.length;
  document.getElementById('residentGrid').innerHTML=filtered.length?_shown.map(r=>`
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
      <div style="margin-top:10px;font-size:12px;color:var(--text-dim);">Balance:
        <button type="button" class="btn btn-sm" style="padding:1px 9px;font-size:12px;" title="View this resident's ledger statement"
          onclick="event.stopPropagation();openLedgerModal(${r.id})">${SubUI.money(r.current_balance)} ${SubUI.icon('scroll',12)}</button>
      </div>
      <div style="margin-top:10px;display:flex;gap:8px;">
        <button class="btn btn-sm" onclick="event.stopPropagation();SubUI.exportCsv('/api/residents/${r.id}/statement')">${SubUI.icon('download',13)} Statement</button>
        <button class="btn btn-sm" onclick="event.stopPropagation();remindResident(${r.id})">${SubUI.icon('bell',13)} Remind</button>
      </div>
    </div>`).join(''):'<div class="empty-state">' + SubUI.icon('home', 28) + 'No residents found.</div>';
  if(_more>0){ document.getElementById('residentGrid').insertAdjacentHTML('beforeend', `<div style="grid-column:1/-1;text-align:center;padding:12px;"><button class="btn btn-sm" onclick="residentCap+=24;renderResidents();">Show ${Math.min(_more,24)} more (${_more} remaining)</button></div>`); }
}
function filterResidents(){ residentCap=24; renderResidents(); }

/* Opens a resident's full ledger statement (the source of truth behind the
   cached balance figure on the card). */
async function openLedgerModal(id){
  const r = residents.find(x => x.id === id);
  const title = document.getElementById('ledgerTitle');
  if (title) title.textContent = 'Ledger — ' + (r ? r.name : 'Resident');
  const sum = document.getElementById('ledgerSummary');
  const body = document.getElementById('ledgerBody');
  if (sum) sum.textContent = '';
  if (body) body.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-dim);padding:14px;">Loading…</td></tr>';

  let s = null;
  try { s = await apiGet('/api/ledger/' + id); } catch (e) {}

  if (!s) {
    if (body) body.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-dim);padding:14px;">Could not load the statement.</td></tr>';
    openModal('ledger');
    return;
  }

  if (sum) {
    sum.textContent = 'Outstanding: ' + SubUI.money(s.balance)
      + ' · Charges: ' + SubUI.money(s.total_charges)
      + ' · Payments: ' + SubUI.money(s.total_payments);
  }

  const entries = Array.isArray(s.entries) ? s.entries : [];
  if (body) {
    body.innerHTML = entries.length ? entries.slice().reverse().map(e => `
      <tr>
        <td style="white-space:nowrap;">${escHtml(e.date || '—')}</td>
        <td>${escHtml(e.description || e.type || '')}</td>
        <td style="text-align:right;">${e.charge ? SubUI.money(e.charge) : '—'}</td>
        <td style="text-align:right;">${e.payment ? SubUI.money(e.payment) : '—'}</td>
        <td style="text-align:right;font-weight:600;">${SubUI.money(e.balance)}</td>
      </tr>`).join('')
      : '<tr><td colspan="5" style="text-align:center;color:var(--text-dim);padding:14px;">No ledger entries yet.</td></tr>';
  }

  openModal('ledger');
}
function toggleAllPending(cb){ document.querySelectorAll('.pending-check').forEach(c=>c.checked=cb.checked); }
async function bulkPending(action){
  const ids=[...document.querySelectorAll('.pending-check:checked')].map(c=>Number(c.value));
  if(!ids.length){ showToast('Select at least one registration.'); return; }
  const ok=await SubUI.confirm({title:'Confirm',message:`${action==='approve'?'Approve':'Reject'} ${ids.length} registration(s)?`,danger:action==='reject',confirmText:action==='approve'?'Approve':'Reject'});
  if(!ok) return;
  const res=await apiPost('/api/residents/bulk-'+action,{ids});
  if(res&&res.success){
    if(action==='approve'){ residents.forEach(r=>{ if(ids.includes(r.id)) r.status='Active'; }); }
    else { residents=residents.filter(r=>!ids.includes(r.id)); }
    renderResidents();
    showToast(action==='approve'?`✅ Approved ${res.updated}.`:`✅ Rejected ${res.deleted}.`);
  } else showToast('⚠ Bulk action failed.');
}
async function remindResident(id){
  const res=await apiPost('/api/residents/'+id+'/remind',{});
  if(res&&res.success) showToast('✅ '+res.message);
  else showToast('⚠ Reminder could not be sent.');
}

function renderMembers(){
  const colorIdx=(n)=>{let h=0;for(let c of(n||''))h=(h*31+c.charCodeAt(0))>>>0;return h%5;};
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
    document.getElementById('membersGrid').innerHTML='<div class="empty-state">' + SubUI.icon('users', 28) + ''+(hhMembers.length?'No members match the filter.':'No household members yet.')+'</div>';
    return;
  }
  document.getElementById('membersGrid').innerHTML=filtered.map(m=>{
    const initStr=ini(m.name);
    const col=colorFor(colorIdx(m.name));
    const rel=m.relationship||'Member';
    const relStyle=`color:${relColor[rel]||'var(--text-dim)'};`;
    const encoded=encodeURIComponent(JSON.stringify(m)).replace(/'/g,'%27');
    return `<div class="member-card" style="cursor:pointer;" onclick="openMemberDetail('${encoded}')" title="Click to view details">
      <div class="member-avatar ${col}">${initStr}</div>
      <div class="member-info">
        <h4>${escHtml(m.name||'—')}</h4>
        <p>${escHtml(m.block_lot||'—')}</p>
        <div class="m-type" style="${relStyle}">${rel}</div>
        ${m.contact_number?`<div style="font-size:10px;color:var(--text-dim);margin-top:2px;">${SubUI.icon('phone',12)} ${escHtml(m.contact_number||'')}</div>`:''}
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

async function deleteMember(paramId){
  const id = paramId || (document.getElementById('modal-memberDetail') ? document.getElementById('modal-memberDetail').dataset.id : null);
  if(!id) return;
  if(!(await SubUI.confirm({ title:'Remove household member?', message:'Remove this member? This cannot be undone.', confirmText:'Remove', danger:true, icon:'!' }))) return;
  try{
    const res=await fetch(`/api/household-members/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});
    const d=await res.json();
    if(d.success){
      hhMembers=hhMembers.filter(m=>m.id!=id);
      renderMembers();
      const modal = document.getElementById('modal-memberDetail');
      if(modal) modal.classList.remove('open');
      if(typeof loadManageData === 'function') loadManageData();
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
        ${t.last_time?`<div style="font-size:10px;color:var(--text-dim);margin-top:3px;">${t.last_time}</div>`:''}
      </div>
      <button class="thread-del-btn" onclick="event.stopPropagation();deleteConversation(${id})" title="Delete conversation">${SubUI.icon('trash',13)}</button>
    </div>`;
  }).join('');
}
async function openThread(id){
  currentThread=id;
  const threadTitle = threads[id]?.title||'';
  document.getElementById('chatName').textContent=threadTitle;
  document.getElementById('chatAvatar').textContent=(threadTitle||'??').substring(0,2).toUpperCase();
  document.getElementById('msgHeaderTitle').textContent = threadTitle.includes('Community Chat') ? 'Community Chat' : threadTitle;
  const status=document.getElementById('messageStatus');
  if(status) status.textContent='Correspondence record selected';
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
    const isMe=m.is_me===true||m.is_me===1||m.sender_type==='admin';
    const sender=isMe?'You':(m.sender_name||'Resident');
    return `<div class="msg-bubble-wrap ${isMe?'mine':''}">
      <div class="msg-sender">${escHtml(sender)}<span class="bubble-time">${escHtml(m.created_at||'')}</span></div>
      <div class="bubble ${isMe?'from-mine':'from-admin'}">${escHtml(m.content)}</div>
    </div>`;
  }).join(''):'<div class="empty-state" style="padding:20px;">No messages yet.</div>';
  body.scrollTop=body.scrollHeight;
}
function escHtml(value){ const d=document.createElement('div'); d.textContent=value??''; return d.innerHTML; }
async function loadThreads(){
  const data=await apiGet('/api/messages/threads');
  if(!Array.isArray(data)){
    const status=document.getElementById('messageStatus');
    if(status) status.textContent='Unable to load correspondence records';
    document.getElementById('threadList').innerHTML='<div style="color:var(--danger);font-size:12px;padding:12px;">Correspondence records could not be loaded. Refresh and try again.</div>';
    return;
  }
  data.forEach((t,i)=>{threads[t.id]={...t,idx:i,msgs:[]};});
  // Try to find and select the Community Chat (group chat) as the main conversation
  const communityChat = data.find(t => t.title && t.title.includes('Community Chat'));
  if(!currentThread && data.length) {
    currentThread = communityChat ? communityChat.id : data[0].id;
  }
  renderThreads();
  if(currentThread) openThread(currentThread);

  // Fallback poll for the open thread (WebSocket pushes are the instant path)
  if(!window.messagePollInterval){
    window.messagePollInterval = setInterval(() => {
      const messagesPanel = document.getElementById('panel-messages');
      if(currentThread && messagesPanel && messagesPanel.classList.contains('active')){
        refreshAdminMessages();
      }
    }, 10000);
  }
}
async function deleteConversation(id){
  if(!(await SubUI.confirm({ title:'Delete conversation?', message:'This deletes the conversation and all its messages.', confirmText:'Delete', danger:true, icon:'!' }))) return;
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
  const statusFilter=(document.getElementById('annStatusFilter')||{}).value||'Active';
  const searchQ=((document.getElementById('annSearch')||{}).value||'').toLowerCase();
  let filtered=statusFilter==='all'?announcements:announcements.filter(a=>(a.status||'Active')===statusFilter);
  if(searchQ) filtered=filtered.filter(a=>(a.title||'').toLowerCase().includes(searchQ)||(a.content||'').toLowerCase().includes(searchQ));
    document.getElementById('annList').innerHTML=filtered.length?filtered.map(a=>{
      const isArchived=(a.status||'Active')==='Archived';
      const isDraft=(a.status||'Active')==='Draft';
    // Format date formally
    let postedDate='';
    try{ postedDate=new Date(a.created_at).toLocaleString('en-PH',{year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit'}); }catch(e){ postedDate=a.created_at; }
    const meta=[];
    if(a.priority==='High') meta.push('<span style="color:#f08080;font-weight:700;font-size:11px;">⚡ High Priority</span>');
    if(a.target&&a.target!=='All Residents') meta.push('<span style="font-size:11px;color:var(--text-mid);">👥 '+escHtml(a.target)+'</span>');
      if(a.event_date) meta.push('<span style="font-size:11px;color:var(--text-mid);display:inline-flex;align-items:center;gap:4px;">'+SubUI.icon('calendar',12)+' Event: '+escHtml(a.event_date)+'</span>');
      if(isArchived) meta.push('<span style="font-size:11px;font-weight:700;color:var(--text-dim);letter-spacing:.05em;text-transform:uppercase;">ARCHIVED</span>');
      if(isDraft) meta.push('<span style="font-size:11px;font-weight:700;color:#f59e0b;letter-spacing:.05em;text-transform:uppercase;">DRAFT — not visible to residents</span>');
    return `
    <div class="ann-item" onclick="openAnnView(${a.id})" style="cursor:pointer;${isArchived?'opacity:0.65;':''}">
      <div class="ann-meta" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;margin-bottom:6px;">
        <div style="display:flex;align-items:center;gap:8px;">
          <span class="pill pill-${a.tag||'notice'}">${tagLabel[a.tag]||'Notice'}</span>
          ${meta.length?'<span style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">'+meta.join('')+'</span>':''}
        </div>
        <span class="ann-date" style="font-size:11px;color:var(--text-dim);">Posted: ${postedDate}</span>
      </div>
      <div class="ann-title" style="font-size:15px;font-weight:600;margin-bottom:4px;text-align:center;">${a.title}</div>
      <div class="ann-body" style="font-size:13px;color:var(--text-mid);white-space:pre-line;line-height:1.65;text-align:center;">${a.content}</div>
      <div style="font-size:11px;color:var(--text-dim);margin-top:6px;border-top:1px solid var(--glass-border);padding-top:6px;">Issued by: ${a.posted_by} &nbsp;·&nbsp; Audience: ${a.target||'All Residents'}</div>
        <div class="ann-actions">
          ${isDraft
            ? `<button class="btn btn-sm btn-green" onclick="event.stopPropagation();publishAnn(${a.id})">${SubUI.icon('send',13)} Publish</button>`
            : `<button class="btn btn-sm" onclick="event.stopPropagation();archiveAnn(${a.id})">${isArchived?SubUI.icon('refresh',13)+' Restore':SubUI.icon('archive',13)+' Archive'}</button>`}
          <button class="btn btn-sm btn-danger" onclick="event.stopPropagation();deleteAnn(${a.id})">Delete</button>
        </div>
      </div>`}).join(''):'<div class="empty-state">' + SubUI.icon('megaphone', 28) + 'No announcements found.</div>';
  }
async function archiveAnn(id){
  const res=await apiPatch('/api/announcements/'+id+'/archive');
  if(res&&res.success){
    const a=announcements.find(x=>x.id===id);
    if(a) a.status=res.status;
    renderAnnouncements();
    showToast(res.status==='Archived'?'📦 Announcement archived.':'♻ Announcement restored.');
  } else {
    showToast('⚠ Failed to update status.');
  }
}
  async function publishAnn(id){
    const res=await apiPatch('/api/announcements/'+id+'/publish');
    if(res&&res.success){
      const a=announcements.find(x=>x.id===id);
      if(a) a.status='Active';
      renderAnnouncements();
      showToast('Announcement published to residents.');
    } else {
      showToast('Failed to publish the announcement.');
    }
  }
  let viewingAnnId=null;
function openAnnView(id){
  const a=announcements.find(x=>x.id===id);
  if(!a) return;
  viewingAnnId=id;
  const tagLabel={notice:'Notice',urgent:'Urgent',event:'Event'};
  document.getElementById('annV-tag').textContent=tagLabel[a.tag]||'Notice';
  document.getElementById('annV-tag').className='pill pill-'+(a.tag||'notice');
  document.getElementById('annV-priority').style.display=a.priority==='High'?'inline':'none';
  document.getElementById('annV-title').textContent=a.title;
  document.getElementById('annV-content').textContent=a.content;
  document.getElementById('annV-by').textContent='Posted by '+a.posted_by+' on '+a.created_at;
  const meta=[];
  if(a.target&&a.target!=='All Residents') meta.push('Audience: '+escHtml(a.target));
  if(a.event_date) meta.push('Event date: '+escHtml(a.event_date));
  document.getElementById('annV-meta').textContent=meta.join(' · ')||'All Residents';
  document.getElementById('annV-delBtn').onclick=()=>{ deleteAnn(a.id); document.getElementById('modal-annView').classList.remove('open'); };
  const archBtn=document.getElementById('annV-archBtn');
  if(archBtn){
      archBtn.innerHTML=(a.status||'Active')==='Archived'?SubUI.icon('refresh',12)+' Unarchive':((a.status||'Active')==='Draft'?SubUI.icon('send',12)+' Publish':SubUI.icon('archive',12)+' Archive');
  }
  document.getElementById('modal-annView').classList.add('open');
}
async function toggleAnnArchive(){
  if(!viewingAnnId) return;
  await archiveAnn(viewingAnnId);
  document.getElementById('modal-annView').classList.remove('open');
}
async function deleteAnn(id){
  if(!(await SubUI.confirm({title:'Delete announcement?',message:'This permanently removes the announcement for everyone.',confirmText:'Delete',danger:true,icon:'!'}))) return;
  const res=await apiDelete('/api/announcements/'+id);
  if(res.success){
    announcements=announcements.filter(a=>a.id!==id);
    renderAnnouncements();
    showToast('🗑️ Announcement deleted.');
  } else {
    showToast('⚠ Failed to delete announcement.');
  }
}
  async function postAnnouncement(draft){
    const tag=document.getElementById('annType').value;
  const title=document.getElementById('annTitle').value.trim();
  const content=document.getElementById('annBody').value.trim();
  const target=document.getElementById('annTarget').value;
  const priority=document.getElementById('annPriority').value;
  const event_date=document.getElementById('annDate').value||null;
  if(!SubUI.required([[title,'annTitle','Please enter a title.'],[content,'annBody','Please enter the announcement body.']])) return;
  if(!title||!content){showToast('⚠ Please fill in all fields.');return;}
    const res=await apiPost('/api/announcements',{tag,title,content,target,priority,event_date,status:draft?'Draft':'Active'});
  if(res.success){
    announcements.unshift(res.announcement);
    closeModal('ann');
    document.getElementById('annTitle').value='';
    document.getElementById('annBody').value='';
    document.getElementById('annDate').value='';
    renderAnnouncements();
    showToast(draft?'Draft saved - publish it from the list.':'Announcement posted to all residents.');
  } else {
    showToast('⚠ '+(res.message||'Failed to post announcement.'));
  }
}
  async function saveAnnDraft(){ await postAnnouncement(true); }

  // Announcement template quick-fill
  function applyAnnTemplate(type){
  const templates={
    notice:{tag:'notice',title:'Community Notice',body:'Dear residents,\n\nPlease be informed that [details here].\n\nThank you for your cooperation.\n\nSUBSYNC Administration'},
    announcement:{tag:'notice',title:'General Announcement',body:'Attention all residents,\n\n[Announcement details here].\n\nFor questions, please contact the HOA office.\n\nSUBSYNC Administration'},
    event:{tag:'event',title:'Upcoming Event',body:'We are pleased to invite all residents to [Event Name].\n\n📅 Date: [Date]\n📍 Venue: [Location]\n🕐 Time: [Time]\n\nWe look forward to seeing you there!\n\nSUBSYNC Administration'},
  };
  const t=templates[type];
  if(!t) return;
  document.getElementById('annType').value=t.tag;
  document.getElementById('annTitle').value=t.title;
  document.getElementById('annBody').value=t.body;
  if(type==='event') document.getElementById('annDate').value='';
}

/* Announcement templates (DB-backed) */
let annTemplates = [];
async function loadAnnTemplates(){
  try{ annTemplates = await apiGet('/api/announcement-templates'); }catch(e){ annTemplates = []; }
  renderAnnTemplateButtons();
}
function renderAnnTemplateButtons(){
  const row = document.getElementById('annTemplateRow');
  if(!row || !annTemplates.length) return;
  row.innerHTML = '<span style="font-size:11px;color:var(--text-dim);align-self:center;white-space:nowrap;">Template:</span>' +
    annTemplates.map(t=>`<button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="applyAnnTemplateById(${t.id})">${escHtml(t.name)}</button>`).join('');
}
function applyAnnTemplateById(id){
  const t = annTemplates.find(x=>x.id===id);
  if(!t) return;
  const typeSel = document.getElementById('annType');
  if(typeSel && t.type) typeSel.value = t.type;
  document.getElementById('annTitle').value = t.subject_template || '';
  document.getElementById('annBody').value = t.body_template || '';
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
function deleteInBadge(i){
  if(i.status!=='Resolved') return '';
  const base = i.resolved_at || i.created_at;
  if(!base) return '';
  const d = new Date(String(base).length<=10 ? base+'T00:00:00' : base);
  if(isNaN(d.getTime())) return '';
  const daysLeft = Math.max(0, Math.ceil(30 - (Date.now()-d.getTime())/86400000));
  if(daysLeft<=0) return '';
  return `<span style="font-size:10px;font-weight:600;color:var(--text-dim);" title="Auto-archives after 30 days">${SubUI.icon('archive',11)} archives in ${daysLeft}d</span>`;
}
function renderIssues(){
  const sf=(document.getElementById('issueFilter')||{}).value||'';
  const pf=(document.getElementById('issuePriFilter')||{}).value||'';
  const df=(document.getElementById('issueDateFrom')||{}).value||'';
  const dt=(document.getElementById('issueDateTo')||{}).value||'';
  const sq=((document.getElementById('issueSearch')||{}).value||'').toLowerCase();
  const filtered=issues.filter(i=>{
    if(sf&&i.status!==sf) return false;
    const pri=i.priority||issuePriority(i);
    if(pf&&pri!==pf) return false;
    if(sq&&!(i.title||'').toLowerCase().includes(sq)&&!(i.resident||'').toLowerCase().includes(sq)) return false;
    if(df||dt){
      const d=i.created_at?new Date(i.created_at):null;
      if(d){
        if(df&&d<new Date(df)) return false;
        if(dt&&d>new Date(dt+'T23:59:59')) return false;
      }
    }
    return true;
  });
  const statusPill={Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'};
  const priColor={Critical:'#ff5555',High:'#f08080',Medium:'var(--yellow)',Low:'var(--green)'};
  const priBg={Critical:'rgba(255,85,85,0.12)',High:'rgba(240,128,128,0.12)',Medium:'rgba(196,150,70,0.12)',Low:'rgba(107,142,112,0.12)'};
  const _shown=filtered.slice(0,issueCap); const _more=filtered.length-_shown.length;
  document.getElementById('issueList').innerHTML=filtered.length?_shown.map(i=>{
    const pri=i.priority||issuePriority(i);
    const isCritical=pri==='Critical';
    let filedDate='';
    try{ filedDate=new Date(i.created_at).toLocaleString('en-PH',{year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit'}); }catch(e){ filedDate=i.created_at; }
    return `
    <div class="issue-item ${isCritical?'issue-critical-row':''}" onclick="openIssueDetail(${i.id})" style="cursor:pointer;" title="Click for full details">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:8px;">
        <div style="flex:1;min-width:0;">
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:3px;letter-spacing:.03em;">Report #${i.id} &nbsp;·&nbsp; Filed: ${filedDate}</div>
          <div class="issue-title" style="font-size:15px;">${isCritical?SubUI.icon('alert',12)+' ':''}${i.title}</div>
          <div style="font-size:12px;color:var(--text-dim);margin-top:2px;">By: ${i.resident} &nbsp;·&nbsp; ${i.block_lot}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px;flex-shrink:0;">
          ${statusPill[i.status]||''}${deleteInBadge(i)}
          ${pri?`<span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;background:${priBg[pri]||'transparent'};color:${priColor[pri]||'var(--text-dim)'};">${pri}</span>`:''}
        </div>
      </div>
      <div class="issue-body" style="font-size:13px;">${i.description}</div>
      <div class="issue-meta" style="margin-top:8px;">
        <span class="pill pill-notice">${i.category}</span>
        ${i.assigned_officer?`<span class="pill pill-progress" title="Assigned officer">${SubUI.icon('user',12)} ${escHtml(i.assigned_officer)}</span>`:''}
        ${i.due_date?`<span class="pill ${i.overdue?'pill-urgent':'pill-notice'}" title="SLA due date">${i.overdue?SubUI.icon('clock',12)+' Overdue ':SubUI.icon('calendar',12)+' Due '}${escHtml(i.due_date)}</span>`:''}
      </div>
      ${(i.responses&&i.responses.length)?`<div style="margin-top:10px;padding:10px 12px;background:rgba(122,180,240,0.07);border-left:2px solid rgba(122,180,240,0.4);border-radius:0 6px 6px 0;">
        <div style="font-size:10px;color:var(--blue);font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">Admin Response</div>
        <div style="font-size:13px;color:var(--text-mid);">${i.responses[0].content}</div>
      </div>`:''}
      <div class="issue-actions">
        ${i.status!=='Resolved'?`<button class="btn btn-sm" onclick="event.stopPropagation();openRespondModal(${i.id})">Respond</button>`:''}
        <button class="btn btn-sm" onclick="event.stopPropagation();openAssignModal(${i.id})">Assign</button>
        <button class="btn btn-sm" onclick="event.stopPropagation();archiveIssue(${i.id})">${i.archived_at?'Unarchive':'Archive'}</button>
        <label style="font-size:11px;color:var(--text-dim);display:flex;align-items:center;gap:4px;margin-left:auto;cursor:pointer;" onclick="event.stopPropagation();">
          <input type="checkbox" class="issue-check" value="${i.id}"> select
        </label>
      </div>
    </div>`}).join(''):'<div class="empty-state">' + SubUI.icon('clipboard', 28) + 'No issues match the selected filters.</div>';
  if(_more>0){ document.getElementById('issueList').insertAdjacentHTML('beforeend', `<div style="text-align:center;padding:12px;"><button class="btn btn-sm" onclick="issueCap+=30;renderIssues();">Show ${Math.min(_more,30)} more (${_more} remaining)</button></div>`); }
}
async function bulkArchiveSelected(){
  const ids=[...document.querySelectorAll('.issue-check:checked')].map(c=>Number(c.value));
  if(!ids.length){ showToast('Select at least one issue.'); return; }
  const ok=await SubUI.confirm({title:'Confirm',message:`Archive ${ids.length} issue(s)?`,confirmText:'Archive'});
  if(!ok) return;
  const res=await apiPatch('/api/issues/bulk-archive',{ids});
  if(res&&res.success){
    issues=issues.filter(i=>!ids.includes(i.id));
    renderIssues();
    showToast(`✅ Archived ${res.archived}.`);
  } else showToast('⚠ Bulk archive failed.');
}
let assigningId=null;
function openAssignModal(id){
  assigningId=id;
  const issue=issues.find(i=>i.id===id);
  if(!issue) return;
  document.getElementById('assignIssueTitle').textContent=issue.title;
  const sel=document.getElementById('assignOfficer');
  sel.innerHTML='<option value="">— Unassigned —</option>'+(officers||[]).map(o=>`<option value="${o.id}"${issue.assigned_officer_id===o.id?' selected':''}>${escHtml(o.name)}</option>`).join('');
  document.getElementById('assignDueDate').value=issue.due_date||'';
  document.getElementById('modal-assign').classList.add('open');
}
async function submitAssign(){
  const issue=issues.find(i=>i.id===assigningId);
  if(!issue) return;
  const officerId=document.getElementById('assignOfficer').value;
  const due=document.getElementById('assignDueDate').value;
  const res=await apiPut('/api/issues/'+assigningId+'/assign',{assigned_officer_id:officerId?Number(officerId):null,due_date:due||null});
  if(res&&res.success){
    issue.assigned_officer_id=res.assigned_officer_id;
    issue.assigned_officer=res.assigned_officer;
    issue.due_date=res.due_date;
    issue.overdue=!!(res.due_date&&new Date(res.due_date)<new Date()&&issue.status!=='Resolved');
    closeModal('assign');
    renderIssues();
    showToast('✅ Assignment saved.');
  } else showToast('⚠ Could not save assignment.');
}
let showingArchivedIssues=false;
async function toggleArchivedIssues(){
  showingArchivedIssues=!showingArchivedIssues;
  try{
    const data=await apiGet('/api/issues'+(showingArchivedIssues?'?archived=1':''));
    issues=Array.isArray(data)?data:[];
  }catch(e){ issues=[]; }
  const btn=document.getElementById('issueArchiveToggle');
  if(btn) btn.textContent=showingArchivedIssues?'Show Active':'Show Archived';
  renderIssues();
}
async function archiveIssue(id){
  const res=await fetch('/api/issues/'+id+'/archive',{method:'PATCH',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}}).then(r=>r.json()).catch(()=>null);
  if(res&&res.success){
    issues=issues.filter(i=>i.id!==id);
    renderIssues();
    showToast(res.archived?'Issue archived.':'Issue restored.');
  } else showToast('⚠ Could not update archive state.');
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
  const tb=document.getElementById('officerTable');
  if(!tb) return;

  // Header stat chips stay in sync with the table.
  const set=(id,v)=>{ const el=document.getElementById(id); if(el) el.textContent=v; };
  set('officerStatTotal', officers.length);
  set('officerStatActive', officers.filter(o=>o.status==='Active').length);
  set('officerStatPending', officers.filter(o=>o.status==='Pending').length);

  const qEl=document.getElementById('officerSearch');
  const q=(qEl?(qEl.value||''):'').trim().toLowerCase();
  const list=q?officers.filter(o=>(o.name||'').toLowerCase().includes(q)||(o.email||'').toLowerCase().includes(q)):officers;

  if(!list.length){
    tb.innerHTML=`<tr><td colspan="6"><div class="empty-state">${SubUI.icon('shield', 28)}<div class="empty-title">${q?'No matches':'No officers yet'}</div><div class="empty-hint">${q?'Try a different name or email.':'Use "+ Add Officer" to create the first account.'}</div></div></td></tr>`;
    return;
  }

  const pill=s=>s==='Active'?'pill-active':(s==='Pending'?'pill-pending':'pill-inactive');
  const joined=o=>{ try{ return o.created_at?new Date(o.created_at).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}):'—'; }catch(e){ return '—'; } };
  tb.innerHTML=list.map(o=>`
    <tr>
      <td>${escHtml(o.name)}</td>
      <td style="color:var(--text-mid);">${escHtml(o.email||'-')}</td>
      <td style="color:var(--text-mid);">${escHtml(o.contact_number||'—')}</td>
      <td style="color:var(--text-mid);">${escHtml(joined(o))}</td>
      <td><span class="pill ${pill(o.status)}">${escHtml(o.status||'Active')}</span></td>
      <td>
        <div style="display:flex;gap:6px;justify-content:flex-end;">
            ${o.status==='Pending'?`<button class="btn btn-sm btn-green" onclick="approveOfficer(${o.id})">Approve</button>`:''}
            <button class="btn btn-sm btn-blue" onclick="openEditOfficer(${o.id})">Edit</button>
            <button class="btn btn-sm btn-danger" onclick="removeOfficer(${o.id})">Remove</button>
          </div>
      </td>
    </tr>`).join('');
}
async function approveOfficer(id){
  const res=await apiPut('/api/officers/'+id,{status:'Active'});
  if(res&&res.success){ const o=officers.find(x=>x.id===id); if(o) o.status='Active'; renderOfficers(); showToast('✅ Officer approved.'); }
  else showToast('⚠ Could not approve officer.');
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
  if(!(await SubUI.confirm({title:'Deactivate officer?',message:'The officer account will be deactivated.',confirmText:'Deactivate',danger:true,icon:'!'}))) return;
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
  document.getElementById('editOfficerStatus').value=o.status||'Active';
  document.getElementById('editOfficerPass').value='';
  openModal('editOfficer');
}
async function saveEditOfficer(){
  const id=document.getElementById('editOfficerIdx').value;
  const name=document.getElementById('editOfficerName').value.trim();
  const email=document.getElementById('editOfficerEmail').value.trim();
  const contact_number=document.getElementById('editOfficerContact').value.trim();
  const status=document.getElementById('editOfficerStatus').value;
  if(!name||!email){showToast('⚠ Name and email required.');return;}
  const payload={name,email,contact_number,status};
  const newPass=document.getElementById('editOfficerPass').value;
  if(newPass) payload.password=newPass;
  if(!(await SubUI.confirm({ title:'Save officer changes?', message:'Update this officer account? Please review the details before saving.', confirmText:'Save Changes', icon:'?' }))) return;
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
  document.getElementById('editHStatus').value=h.status||'Occupied';
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
  if(!(await SubUI.confirm({ title:'Save household changes?', message:'Update this household? Please review the details before saving.', confirmText:'Save Changes', icon:'?' }))) return;
  const res=await apiPut('/api/households/'+id,{block_lot_number:loc,status,family_name,members});
  if(res.success){closeModal('muEditHousehold');await loadManageData();showToast('✅ Household updated.');}
  else showToast('⚠ '+(res.message||'Failed to update household.'));
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
  if(!(await SubUI.confirm({ title:'Save resident changes?', message:'Update this resident account? Please review the details before saving.', confirmText:'Save Changes', icon:'?' }))) return;
  const res=await apiPut('/api/residents/'+id,payload);
  if(res.success){closeModal('muEditUser');await loadManageData();loadAllData();showToast('✅ User updated.');}
  else showToast('⚠ '+(res.message||'Failed to update user.'));
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
  if(!(await SubUI.confirm({ title:'Change household status?', message:'Set this household to "'+status+'"? This is recorded and visible to residents.', confirmText:'Confirm', danger:true, icon:'!' }))) return;
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
  document.getElementById('statusHLoc').textContent='Current status: '+(h.status||'Occupied');
  document.getElementById('statusHNew').value=h.status||'Occupied';
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
    el.innerHTML='<div class="empty-state">' + SubUI.icon('lightbulb', 28) + 'No recommendations yet.</div>';
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
  const res=await apiPut('/api/recommendations/'+id+'/status',{status});
  if(!res||!res.success){ showToast('Could not update status.'); return; }
  const rec=recommendations.find(r=>r.id===id);
  if(rec) rec.status=status;
  renderRecommendations();
  showToast('✅ Status updated.');
}

// ── DETAIL POPUP FUNCTIONS ──────────────────────────────────────────────────
function openResidentDetail(id){
  const r=residents.find(x=>x.id===id);
  if(!r) return;
  document.getElementById('resD-avatar').className='r-avatar '+r.color;
  document.getElementById('resD-avatar').textContent=r.initials;
  document.getElementById('resD-name').textContent=r.name;
  document.getElementById('resD-block').textContent=r.block_lot||'—';
  document.getElementById('resD-status').textContent=r.status;
  document.getElementById('resD-status').className='pill '+(r.status==='Active'?'pill-active':'pill-inactive');
  document.getElementById('resD-household').textContent=r.block_lot||'—';
  document.getElementById('resD-email').textContent=r.email||'—';
  document.getElementById('resD-phone').textContent=r.contact_number||'—';
  const msgBtn=document.getElementById('resD-msgBtn');
  msgBtn.onclick=()=>{
    document.getElementById('modal-residentDetail').classList.remove('open');
    messageResident(r.id);
  };
  document.getElementById('modal-residentDetail').classList.add('open');
}

function messageResident(id){
  openNewThreadModal();
  const sel=document.getElementById('newThreadResident');
  if(sel) sel.value=String(id);
  const title=document.getElementById('newThreadTitle');
  const r=residents.find(x=>x.id===id);
  if(title && r) title.value='Message: '+(r.name||'');
}

let detailIssueId=null;
function openIssueDetail(id){
  const i=issues.find(x=>x.id===id);
  if(!i) return;
  detailIssueId=id;
  const pri=i.priority||issuePriority(i);
  const priColor={Critical:'#ff5555',High:'#f08080',Medium:'var(--yellow)',Low:'var(--green)'};
  const statusPill={Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'};
  document.getElementById('issD-title').textContent=(pri==='Critical'?'🚨 ':'')+i.title;
  document.getElementById('issD-status').innerHTML=(statusPill[i.status]||'')+(pri?` <span style="font-size:11px;font-weight:700;color:${priColor[pri]||'var(--text-dim)'};">${pri}</span>`:'');
  document.getElementById('issD-who').textContent=i.resident+' · '+i.block_lot;
  document.getElementById('issD-cat').textContent=(i.category||'')+(i.category?' · ':'')+'#'+i.id+' · '+i.created_at;
  document.getElementById('issD-desc').textContent=i.description;
  // Set priority dropdown to current value
  const priSel=document.getElementById('issD-prioritySel');
  if(priSel) priSel.value=i.priority||'';
  // Photo gallery
  const mediaEl=document.getElementById('issD-media');
  if(mediaEl){
    const photos=i.media||[];
    mediaEl.innerHTML=photos.length?photos.map(m=>/\.(jpg|jpeg|png|gif|webp)$/i.test(m.name||m.url)?`<a href="${m.url}" target="_blank"><img src="${m.url}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--glass-border);cursor:pointer;" title="Click to open full size"></a>`:`<a href="${m.url}" target="_blank" style="display:inline-block;padding:8px 10px;border:1px solid var(--glass-border);border-radius:8px;color:var(--blue);font-size:12px;">📎 ${escHtml(m.name||'Attachment')}</a>`).join(''):'';
  }
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
async function saveIssuePriority(){
  if(!detailIssueId) return;
  const priority=document.getElementById('issD-prioritySel').value;
  if(!priority){showToast('⚠ Please select a priority.');return;}
  const res=await apiPut('/api/issues/'+detailIssueId+'/priority',{priority});
  if(res&&res.success){
    const i=issues.find(x=>x.id===detailIssueId);
    if(i) i.priority=priority;
    renderIssues();
    showToast('✅ Priority set to '+priority+'.');
    // Update status display in modal
    const priColor={Critical:'#ff5555',High:'#f08080',Medium:'var(--yellow)',Low:'var(--green)'};
    const statusPill={Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'};
    if(i) document.getElementById('issD-status').innerHTML=(statusPill[i.status]||'')+` <span style="font-size:11px;font-weight:700;color:${priColor[priority]||'var(--text-dim)'};">${priority}</span>`;
  } else {
    showToast('⚠ '+(res&&res.message||'Failed to set priority.'));
  }
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




async function addOfficer(){
  const name=document.getElementById('newOfficerName').value.trim();
  const email=document.getElementById('newOfficerEmail')?document.getElementById('newOfficerEmail').value.trim():'';
  const password=document.getElementById('newOfficerPass')?document.getElementById('newOfficerPass').value:'';
  if(!name){showToast('⚠ Please enter a name.');return;}
  if(!password){showToast('⚠ Password is required.');return;}
  const res=await apiPost('/api/officers',{name,email,password});
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
      const hRes=await apiPost('/api/households',{block_lot_number,status:'Occupied'});
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
          <div style="font-size:13px;font-weight:500;margin-bottom:2px;">${escHtml(f.original_name)}</div>
          <div style="font-size:11px;color:var(--text-dim);">${escHtml(f.officer_name)} &middot; ${escHtml(f.category)} &middot; ${f.period||'&mdash;'}</div>
          ${f.notes?`<div style="font-size:11px;color:var(--text-mid);margin-top:3px;">${escHtml(f.notes)}</div>`:''}
          <div style="font-size:10px;color:var(--text-dim);margin-top:3px;">${new Date(f.created_at).toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'numeric'})}</div>
        </div>
        <div style="display:flex;gap:6px;flex-shrink:0;align-items:center;">
          ${f.file_path?`<a href="${(window.APP_BASE||'')}/api/officer-files/${f.id}/download" class="btn btn-sm">&darr; Download</a>`:''}
          <button class="btn btn-sm" onclick="event.stopPropagation();toggleAdminFileArchive(${f.id})">📦 Archive</button>
        </div>
      </div>`).join('');
  } catch(e) {
    el.innerHTML='<div style="color:var(--text-dim);font-size:13px;padding:8px 0;">Could not load files.</div>';
  }
}

async function toggleAdminFileArchive(id){
  const res=await fetch('/api/officer-files/'+id+'/archive',{method:'PATCH',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}}).then(r=>r.json()).catch(()=>null);
  if(res?.success){ showToast(res.archived?'📦 File archived.':'♻ File restored.'); renderReports(); }
  else showToast('⚠ Could not update file status.');
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

  function getReportDateRange(){
    let from=(document.getElementById('reportDateFrom')||{}).value||'';
    let to=(document.getElementById('reportDateTo')||{}).value||'';
    if(from && to && from > to){ [from,to]=[to,from]; }
    return {from, to};
  }
function inDateRange(dateStr, from, to){
  if(!from&&!to) return true;
  const d=new Date(dateStr);
  if(isNaN(d)) return true;
  if(from&&d<new Date(from)) return false;
  if(to&&d>new Date(to+'T23:59:59')) return false;
  return true;
}

function reportMonthlySummary(){
  if(!residents||!residents.length){ showToast('⚠ No resident data loaded yet.'); return; }
  const {from,to}=getReportDateRange();
  const now = new Date();
  const label = now.toLocaleDateString('en-PH',{month:'long',year:'numeric'});
  const fname = `monthly-summary-${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}.csv`;
  const rows = residents.map(r=>{
    const recs = Array.isArray(finRecs)?finRecs.filter(f=>String(f.resident_id)===String(r.id)&&inDateRange(f.date||f.created_at,from,to)):[];
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
  const {from,to}=getReportDateRange();
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
  const {from,to}=getReportDateRange();
  const filtered=issues.filter(i=>inDateRange(i.created_at,from,to));
  const fname = `issue-report-${new Date().toISOString().slice(0,10)}.csv`;
  const rows = filtered.map(i=>[
    i.id, i.category||'', i.title||i.description||'', i.resident||i.resident_name||'',
    i.block_lot||'', i.status||'', i.priority||'',
    i.created_at?new Date(i.created_at).toLocaleDateString('en-PH'):''
  ]);
  downloadCSV(fname, ['ID','Category','Title','Resident','Block/Lot','Status','Priority','Date Filed'], rows);
  showToast(`🔧 Issue Report downloaded! (${filtered.length} record${filtered.length!==1?'s':''})`);
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

async function uploadAdminReport(){
  const fileEl=document.getElementById('adminReportFile');
  const note=(document.getElementById('adminReportNote')||{}).value||'';
  if(!fileEl||!fileEl.files.length){ showToast('⚠ Please select a file to upload.'); return; }
  const fd=new FormData();
  fd.append('file', fileEl.files[0]);
  fd.append('notes', note);
  fd.append('category', 'Admin Document');
  try{
    const res=await fetch('/api/officer-files',{
      method:'POST',
      headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
      body:fd
    }).then(r=>r.json());
    if(res.success||res.id){ showToast('✅ Document uploaded successfully.'); fileEl.value=''; document.getElementById('adminReportNote').value=''; renderReports(); }
    else showToast('⚠ Upload failed. '+(res.message||''));
  }catch(e){ showToast('⚠ Upload error. Please try again.'); }
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
        <td><span class="pill ${h.status==='Occupied'?'pill-active':'pill-inactive'}">${h.status}</span></td>
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
        <td>${m.id}</td><td>${m.name?escHtml(m.name):'—'}</td><td>${escHtml(String(m.block_lot||m.house_id||'—'))}</td><td>${escHtml(m.relationship||'Member')}</td>
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

async function deleteHousehold(id){ if(!(await SubUI.confirm({title:'Mark household Vacant?',message:'The household will be marked Vacant.',confirmText:'Mark Vacant',danger:true,icon:'!'}))) return; await apiPut('/api/households/'+id,{status:'Vacant'}); await loadManageData(); showToast('✅ Household deactivated.'); }
async function deactivateResident(id){ if(!(await SubUI.confirm({title:'Deactivate resident?',message:'The resident account will be deactivated and unable to log in.',confirmText:'Deactivate',danger:true,icon:'!'}))) return; await apiDelete('/api/residents/'+id); await loadManageData(); loadAllData(); showToast('✅ Resident deactivated.'); }

async function approveRegistration(id){
  const res=await apiPost('/api/residents/'+id+'/approve',{});
  if(res?.success){ showToast('✅ Registration approved. Resident can now log in.'); loadAllData(); }
  else showToast('⚠ Could not approve registration.');
}
async function rejectRegistration(id){
  if(!(await SubUI.confirm({ title:'Reject registration?', message:'Reject and permanently delete this pending registration?', confirmText:'Reject', danger:true, icon:'!' }))) return;
  const res=await apiDelete('/api/residents/'+id+'/reject');
  if(res?.success){ showToast('✅ Registration rejected and removed.'); loadAllData(); }
  else showToast('⚠ Could not reject registration.');
}
// async function deleteMember(id){ await apiDelete('/api/household-members/'+id); await loadManageData(); showToast('🗑️ Member removed.'); }
async function deleteFamily(id){ if(!(await SubUI.confirm({title:'Remove family?',message:'This removes the family grouping. Households stay but are detached.',confirmText:'Remove',danger:true,icon:'!'}))) return; await apiDelete('/api/families/'+id); await loadManageData(); showToast('🗑️ Family removed.'); }

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
  const status=document.getElementById('muHStatus').value||'Occupied';
  const family_name=document.getElementById('muHFamily').value.trim();
  const members=document.getElementById('muHMembers').value.trim();
  const family_head=document.getElementById('muHHead').value.trim();
  if(!loc){showToast('⚠ Please fill in required fields.');return;}
  const res=await apiPost('/api/households',{block_lot_number:loc,status,family_name,members,family_head});
  if(!res||!res.success){ showToast('Could not add household.'); return; }
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
    res=await apiPost('/api/officers',{name,email,password:pass});
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
  if(typeof d3 === 'undefined') return;
  drawResidentStatusChart();
  drawIssuesStatusChart();
  drawRecsStatusChart();
  drawExpensesCatChart();
  drawIssuesTrendChart();
  drawComplaintsChart();
  loadCommonIssues();
}

/* ══ COMMON ISSUES (ranked by frequency) ══ */
async function loadCommonIssues(){
  const el=document.getElementById('analytics-common-issues');
  if(!el) return;
  let rows=[];
  try{ rows=await apiGet('/api/issues/common'); }catch(e){ rows=[]; }
  if(!rows.length){ el.innerHTML='<div style="color:var(--text-dim);font-size:13px;text-align:center;padding:40px 0;">No reported issues yet.</div>'; return; }
  const max=Math.max(...rows.map(r=>r.total))||1;
  el.innerHTML=rows.map((r,i)=>`
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
      <div style="width:22px;text-align:right;font-weight:700;color:var(--accent);font-size:13px;">${i+1}</div>
      <div style="flex:1;min-width:0;">
        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text);margin-bottom:3px;">
          <span>${escHtml(r.category)}</span>
          <span style="color:var(--text-dim);">${r.total} <span style="font-size:11px;">(${r.open} open)</span></span>
        </div>
        <div style="height:8px;border-radius:5px;background:#EDE7DC;overflow:hidden;">
          <div style="height:100%;width:${Math.round(r.total/max*100)}%;background:linear-gradient(90deg,#A07D53,#c87941);"></div>
        </div>
      </div>
    </div>`).join('');
}

/* Theme-aware, muted palette for the analytics widgets. */
  async function loadAnalyticsSecurityLogs(){
    const el=document.getElementById('analytics-security-logs');
    if(!el) return;
    let logs=[];
    try{ const res=await apiGet('/api/activity-logs'); logs=(res&&Array.isArray(res.logs))?res.logs:[]; }catch(e){ logs=[]; }
    if(!logs.length){ el.innerHTML='<div class="empty-state" style="padding:14px;">No recorded activity yet.</div>'; return; }
    const security=/export|erasure|backup|password|login|account|setting/i;
    const relevant=logs.filter(l=>security.test(String(l.action||'')+' '+String(l.description||'')));
    const list=(relevant.length?relevant:logs).slice(0,6);
    el.innerHTML=list.map(l=>`<div style="display:flex;justify-content:space-between;gap:10px;padding:7px 0;border-bottom:1px solid var(--glass-border);font-size:12.5px;">
      <span>${escHtml(l.description||l.action||'')}</span>
      <span style="color:var(--text-dim);white-space:nowrap;">${escHtml(l.actor||'')} · ${escHtml(l.time||'')}</span>
    </div>`).join('');
  }
  function chartColors(){
  const dark = document.documentElement.getAttribute('data-theme') === 'dark';
  return {
    dark,
    text: dark ? '#E9E3D8' : '#2B2927',
    dim:  dark ? '#A9A294' : '#5F5A53',
    grid: dark ? 'rgba(255,255,255,0.10)' : 'rgba(43,41,39,0.10)',
    axis: dark ? 'rgba(255,255,255,0.20)' : 'rgba(43,41,39,0.20)',
    track: dark ? 'rgba(255,255,255,0.06)' : 'rgba(43,41,39,0.06)',
    accent: '#A07D53',
    palette: ['#A07D53','#3E6A8F','#4F7A57','#946E1E','#6E6486','#A84545','#7D7975','#8C6A4A'],
    status: { 'Pending':'#946E1E','In Progress':'#3E6A8F','Resolved':'#4F7A57','Reviewed':'#3E6A8F','Approved':'#4F7A57','Rejected':'#A84545' },
  };
}
function noData(el, c, msg){ el.innerHTML = `<div style="color:${c.dim};font-size:13px;text-align:center;padding:40px 0;">${msg}</div>`; }

function drawDonut(elId, data, centerValue, centerLabel){
  const el = document.getElementById(elId);
  if(!el) return;
  el.innerHTML='';
  const c = chartColors();
  data = (data || []).filter(d => d.value > 0);
  if(!data.length){ noData(el, c, 'No data yet.'); return; }
  const W = Math.min(el.clientWidth||220, 260), H = 190;
  const R = Math.min(W,H)/2 - 22;
  const svg = d3.select(el).append('svg').attr('width',W).attr('height',H);
  const g = svg.append('g').attr('transform',`translate(${W/2},${H/2-8})`);
  const inner = R*0.62;
  g.append('circle').attr('r',(R+inner)/2).attr('fill','none').attr('stroke',c.track).attr('stroke-width',R-inner);
  const pie = d3.pie().value(d=>d.value).sort(null);
  const arc = d3.arc().innerRadius(inner).outerRadius(R).padAngle(0.015);
  g.selectAll('path').data(pie(data)).join('path').attr('d',arc).attr('fill',d=>d.data.color);
  const total = data.reduce((a,d)=>a+d.value,0);
  g.append('text').attr('text-anchor','middle').attr('dy','0.15em')
    .attr('fill',c.text).style('font-size','22px').style('font-weight','700').text(centerValue!==undefined?centerValue:total);
  if(centerLabel) g.append('text').attr('text-anchor','middle').attr('dy','1.6em')
    .attr('fill',c.dim).style('font-size','10px').style('letter-spacing','.08em').text(centerLabel.toUpperCase());
  const leg = svg.append('g').attr('transform',`translate(${W/2},${H-8})`);
  data.forEach((d,i)=>{
    const lx = (i-(data.length-1)/2)*102;
    leg.append('rect').attr('x',lx-18).attr('y',-5).attr('width',9).attr('height',9).attr('rx',2).attr('fill',d.color);
    leg.append('text').attr('x',lx-5).attr('y',3).attr('fill',c.dim).style('font-size','11px').text(`${d.label} (${d.value})`);
  });
}

function drawIssuesStatusChart(){
  const c=chartColors();
  const counts={}; issues.forEach(i=>{ counts[i.status]=(counts[i.status]||0)+1; });
  const data=Object.entries(counts).map(([label,value])=>({label,value,color:c.status[label]||c.palette[6]}));
  drawDonut('chart-issue-status', data, data.reduce((a,d)=>a+d.value,0), 'issues');
}

function drawRecsStatusChart(){
  const c=chartColors();
  const counts={}; recommendations.forEach(r=>{ counts[r.status]=(counts[r.status]||0)+1; });
  const data=Object.entries(counts).map(([label,value])=>({label,value,color:c.status[label]||c.palette[6]}));
  drawDonut('chart-recs-status', data, data.reduce((a,d)=>a+d.value,0), 'total');
}

function drawExpensesCatChart(){
  const el = document.getElementById('chart-expenses-cat');
  if(!el) return;
  el.innerHTML='';
  const c = chartColors();
  const cats={}; (expenses||[]).forEach(e=>{ cats[e.category||'Other']=(cats[e.category||'Other']||0)+(parseFloat(e.amount)||0); });
  const data=Object.entries(cats).map(([cat,amount])=>({cat,amount})).sort((a,b)=>b.amount-a.amount).slice(0,6);
  if(!data.length){ noData(el, c, 'No expenses recorded.'); return; }
  const W=el.clientWidth||320, H=190;
  const m={top:12,right:16,bottom:30,left:56};
  const w=W-m.left-m.right, h=H-m.top-m.bottom;
  const svg=d3.select(el).append('svg').attr('width',W).attr('height',H);
  const g=svg.append('g').attr('transform',`translate(${m.left},${m.top})`);
  const x=d3.scaleBand().domain(data.map(d=>d.cat)).range([0,w]).padding(0.35);
  const y=d3.scaleLinear().domain([0,d3.max(data,d=>d.amount)*1.15||1]).range([h,0]);
  g.append('g').call(d3.axisLeft(y).ticks(4).tickSize(-w).tickFormat(''))
    .call(ax=>{ax.select('.domain').remove();ax.selectAll('.tick line').attr('stroke',c.grid);});
  g.selectAll('rect').data(data).join('rect')
    .attr('x',d=>x(d.cat)).attr('y',d=>y(d.amount))
    .attr('width',x.bandwidth()).attr('height',d=>h-y(d.amount))
    .attr('fill',c.accent).attr('rx',3);
  g.append('g').attr('transform',`translate(0,${h})`).call(d3.axisBottom(x))
    .call(ax=>{ax.select('.domain').attr('stroke',c.axis);ax.selectAll('.tick line').remove();ax.selectAll('text').attr('fill',c.dim).style('font-size','10px');});
  g.append('g').call(d3.axisLeft(y).ticks(4).tickFormat(v=>'₱'+(v>=1000?(v/1000).toFixed(0)+'k':v)))
    .call(ax=>{ax.select('.domain').remove();ax.selectAll('.tick line').remove();ax.selectAll('text').attr('fill',c.dim).style('font-size','10px');});
}

function drawIssuesTrendChart(){
  const el = document.getElementById('chart-issue-trend');
  if(!el) return;
  el.innerHTML='';
  const c = chartColors();
  const months=[];
  for(let i=5;i>=0;i--){ const d=new Date(); d.setMonth(d.getMonth()-i); months.push(d.toLocaleDateString('en-US',{month:'short',year:'2-digit'})); }
  const counts={}; months.forEach(m=>counts[m]=0);
  issues.forEach(i=>{
    const d=new Date(i.created_at||Date.now());
    if(isNaN(d)) return;
    const key=d.toLocaleDateString('en-US',{month:'short',year:'2-digit'});
    if(key in counts) counts[key]++;
  });
  const data=months.map(m=>({month:m,count:counts[m]||0}));
  if(!data.reduce((a,d)=>a+d.count,0)){ noData(el, c, 'No issues filed in the last 6 months.'); return; }
  const W=el.clientWidth||320, H=190;
  const m={top:12,right:16,bottom:30,left:34};
  const w=W-m.left-m.right, h=H-m.top-m.bottom;
  const svg=d3.select(el).append('svg').attr('width',W).attr('height',H);
  const grad=svg.append('defs').append('linearGradient').attr('id','trendFill').attr('x1','0').attr('y1','0').attr('x2','0').attr('y2','1');
  grad.append('stop').attr('offset','0%').attr('stop-color',c.accent).attr('stop-opacity',0.20);
  grad.append('stop').attr('offset','100%').attr('stop-color',c.accent).attr('stop-opacity',0);
  const g=svg.append('g').attr('transform',`translate(${m.left},${m.top})`);
  const x=d3.scalePoint().domain(months).range([0,w]).padding(0.4);
  const y=d3.scaleLinear().domain([0,d3.max(data,d=>d.count)+1]).range([h,0]);
  g.append('path').datum(data).attr('fill','url(#trendFill)').attr('stroke','none')
    .attr('d', d3.area().x(d=>x(d.month)).y0(h).y1(d=>y(d.count)).curve(d3.curveMonotoneX));
  g.append('path').datum(data).attr('fill','none').attr('stroke',c.accent).attr('stroke-width',2.2)
    .attr('d', d3.line().x(d=>x(d.month)).y(d=>y(d.count)).curve(d3.curveMonotoneX));
  g.selectAll('circle').data(data).join('circle')
    .attr('cx',d=>x(d.month)).attr('cy',d=>y(d.count)).attr('r',3.5).attr('fill','#FFFFFF').attr('stroke',c.accent).attr('stroke-width',1.6);
  g.append('g').attr('transform',`translate(0,${h})`).call(d3.axisBottom(x).tickSize(0))
    .call(ax=>{ax.select('.domain').attr('stroke',c.axis);ax.selectAll('.tick line').remove();ax.selectAll('text').attr('fill',c.dim).style('font-size','10px');});
  g.append('g').call(d3.axisLeft(y).ticks(3).tickFormat(d3.format('d')))
    .call(ax=>{ax.select('.domain').remove();ax.selectAll('.tick line').remove();ax.selectAll('text').attr('fill',c.dim).style('font-size','10px');});
}

function drawResidentStatusChart(){
  const c=chartColors();
  const activeCount=residents.filter(r=>r.status==='Active').length;
  const nonActiveCount=residents.filter(r=>r.status!=='Active').length;
  const total=activeCount+nonActiveCount||1;
  drawDonut('chart-payment-statuses',
    [{label:'Active',value:activeCount,color:c.palette[2]},{label:'Non-active',value:nonActiveCount,color:c.palette[5]}],
    Math.round(activeCount/total*100)+'%', 'active');
}

function drawComplaintsChart(){
  const el=document.getElementById('chart-complaints');
  if(!el) return;
  el.innerHTML='';
  const c=chartColors();
  if(!issues.length){ noData(el, c, 'No issue reports yet.'); return; }
  const cats={};
  issues.forEach(i=>{ cats[i.category]=(cats[i.category]||0)+1; });
  const data=Object.entries(cats).map(([cat,count])=>({cat,count})).sort((a,b)=>b.count-a.count).slice(0,7);
  const W=el.clientWidth||320, H=190;
  const m={top:12,right:28,bottom:30,left:Math.min(120,Math.max(...data.map(d=>d.cat.length))*7+12)};
  const w=W-m.left-m.right, h=H-m.top-m.bottom;
  const svg=d3.select(el).append('svg').attr('width',W).attr('height',H);
  const g=svg.append('g').attr('transform',`translate(${m.left},${m.top})`);
  const y=d3.scaleBand().domain(data.map(d=>d.cat)).range([0,h]).padding(0.3);
  const x=d3.scaleLinear().domain([0,d3.max(data,d=>d.count)+0.6]).range([0,w]);
  g.selectAll('rect').data(data).join('rect')
    .attr('x',0).attr('y',d=>y(d.cat))
    .attr('width',d=>x(d.count)).attr('height',y.bandwidth())
    .attr('fill',c.palette[1]).attr('rx',3);
  g.selectAll('text.val').data(data).join('text').attr('class','val')
    .attr('x',d=>x(d.count)+6).attr('y',d=>y(d.cat)+y.bandwidth()/2+3)
    .attr('fill',c.dim).style('font-size','10px').text(d=>d.count);
  g.append('g').call(d3.axisLeft(y).tickSize(0))
    .call(ax=>{ax.select('.domain').remove();ax.selectAll('text').attr('fill',c.dim).style('font-size','11px').attr('dx','-4px');});
  g.append('g').attr('transform',`translate(0,${h})`).call(d3.axisBottom(x).ticks(3).tickFormat(d3.format('d')))
    .call(ax=>{ax.select('.domain').attr('stroke',c.axis);ax.selectAll('.tick line').remove();ax.selectAll('text').attr('fill',c.dim).style('font-size','11px');});
}

async function adminSend(){
  const inp=document.getElementById('msgInput');
  const txt=inp.value.trim(); if(!txt||!currentThread) return;
  inp.value='';
  const res=await apiPost('/api/messages/'+currentThread,{content:txt});
  if(!res||!res.success){ if(inp) inp.value=txt; showToast('Message not sent. Please try again.'); return; }
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
function exportIssuesCSV(){
  if(!issues.length){showToast('⚠ No issue data.');return;}
  const rows=[['ID','Title','Category','Resident','Block','Status','Date'],
    ...issues.map(i=>[i.id,i.title,i.category,i.resident,i.block_lot,i.status,i.created_at])];
  exportCSV(rows,'issues_'+new Date().toISOString().slice(0,10)+'.csv');
  showToast('✅ Issues exported.');
}

/* Analytics heatmap removed in favour of numbered issue pins (see Subdivision Map). */

/* ══════════════ INIT ══════════════ */
const ADMIN_ID = window.__ADMIN.id;
const ADMIN_NAME = window.__ADMIN.name;

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

// Fallback only — the .officer-file socket event refreshes instantly.
setInterval(function(){ if(!document.hidden && !_rtWsReady) pollOfficerFiles(); }, 30000);

/* ══════════════════════════════════════════════════════════
   REAL-TIME: Server-Sent Events (SSE)
   Replaces all polling intervals. The server pushes events
   within ~1 second of a DB change. JS then fetches full data
   from existing REST endpoints.
   ══════════════════════════════════════════════════════════ */
let _lastUrgentId = null;
function dismissEmergency(){ document.getElementById('emergencyAlert').style.display='none'; }

// ── Notification banner helpers ──────────────────────────
let _adminLastAnnId = 0;
let _adminLastIssueCount = 0;

function showAnnBanner(ann){
  document.getElementById('annNotifTitle').textContent = ann.title;
  const notif = document.getElementById('annNotif');
  notif.classList.add('show');
  clearTimeout(notif._t);
  notif._t = setTimeout(()=>notif.classList.remove('show'), 9000);
}
function showIssueBanner(issue){
  document.getElementById('issueNotifTitle').textContent = issue.title + (issue.resident ? ' — ' + issue.resident : '');
  const notif = document.getElementById('issueNotif');
  notif.classList.add('show');
  clearTimeout(notif._t);
  notif._t = setTimeout(()=>notif.classList.remove('show'), 9000);
}
document.getElementById('annNotifViewBtn').onclick = ()=>{
  document.getElementById('annNotif').classList.remove('show');
  nav('announcements');
};
document.getElementById('issueNotifViewBtn').onclick = ()=>{
  document.getElementById('issueNotif').classList.remove('show');
  nav('issues');
};

// ── SSE connection (fallback — Reverb WebSocket is the primary channel) ──
let _adminSseAnnId   = 0;
let _adminSseIssueId = 0;
let _adminSsePolicyId = 0;
let _adminSseEventId  = 0;
let _rtWsReady = false;   // true while the Reverb socket is connected
let _rtSse = null;        // the fallback EventSource (null when WS is primary)

/* Re-fetch the open thread; used by the WS push and the fallback polls. */
async function refreshAdminMessages(){
  if(!currentThread) return;
  try{
    const msgs = await apiGet('/api/messages/'+currentThread);
    if(Array.isArray(msgs) && msgs.length !== (threads[currentThread]?.msgs||[]).length){
      threads[currentThread].msgs = msgs;
      renderChat();
    }
  }catch(e){}
}

/* Refresh the conversation list (new senders appear named) without switching
   the open thread — used when a message arrives for a conversation we're not
   currently viewing. */
async function refreshAdminThreadList(){
  const data = await apiGet('/api/messages/threads');
  if(!Array.isArray(data)) return;
  const seen = {};
  data.forEach((t,i)=>{
    seen[t.id] = 1;
    const prev = threads[t.id];
    threads[t.id] = {...t, idx:i, msgs: prev ? prev.msgs : []};
  });
  Object.keys(threads).forEach(id=>{ if(!seen[id]) delete threads[id]; });
  if(!currentThread && data.length){
    const cc = data.find(t => t.title && t.title.includes('Community Chat'));
    currentThread = cc ? cc.id : data[0].id;
  }
  renderThreads();
}

function connectAdminSSE(){
  if(_rtWsReady) return; // WebSocket is live — SSE is only the fallback
  if(document.hidden){ setTimeout(connectAdminSSE, 2000); return; }

  const url = `/api/stream/updates?lastEventId=${_adminSseEventId}&lastAnnId=${_adminSseAnnId}&lastIssueId=${_adminSseIssueId}&lastPolicyId=${_adminSsePolicyId}`;
  const es  = new EventSource(url);
  _rtSse = es;

  // ── Fresh connection — load initial data once ─────────────
  es.addEventListener('sync', () => {
    if(!issues || !issues.length) loadAllData().catch(()=>{});
  });

  // ── New / changed announcement ───────────────────────────
  es.addEventListener('announcement', async (e) => {
    const evt = JSON.parse(e.data);
    _adminSseAnnId = evt.latestId;
    try{
      const data = await apiGet('/api/announcements');
      if(!Array.isArray(data) || !data.length) return;
      const newOnes = data.filter(a => a.id > _adminLastAnnId);
      _adminLastAnnId = data[0].id;
      announcements = data;
      renderAnnouncements();
      renderDashboard();
      if(newOnes.length) showAnnBanner(newOnes[0]);
    }catch(err){}
  });

  // ── New / changed policy ──────────────────────────────────
  es.addEventListener('policy', async (e) => {
    const evt = JSON.parse(e.data);
    _adminSsePolicyId = evt.latestId;
    try{
      if(typeof loadAdminPolicies === 'function') await loadAdminPolicies();
    }catch(err){}
  });

  // ── New / changed issue ──────────────────────────────────
  es.addEventListener('issue', async (e) => {
    const evt = JSON.parse(e.data);
    _adminSseIssueId = evt.latestId;
    try{
      const d = await apiGet('/api/dashboard-data');
      const newIssues = d.issues||[];
      const newRecs   = d.recommendations||[];
      const hadNew    = evt.isNew && _adminLastIssueCount > 0;
      if(hadNew) showIssueBanner({title: evt.title});
      issues          = newIssues;
      recommendations = newRecs;
      _adminLastIssueCount = newIssues.length;
      renderIssues();
      renderRecommendations();
      renderDashboard();
      const openCount = issues.filter(i=>i.status!=='Resolved').length;
      if(document.getElementById('statIssues')) document.getElementById('statIssues').textContent = openCount;
      if(document.getElementById('issueBadge')) document.getElementById('issueBadge').textContent = openCount;
    }catch(err){}
  });

  // ── Urgent / critical issue ──────────────────────────────
  es.addEventListener('urgent', (e) => {
    const evt = JSON.parse(e.data);
    if(evt.id === _lastUrgentId) return;
    _lastUrgentId = evt.id;
    const el  = document.getElementById('emergencyAlert');
    const txt = document.getElementById('emergencyAlertText');
    if(txt) txt.textContent = `🚨 CRITICAL ISSUE — "${evt.title}"`;
    if(el)  el.style.display = 'block';
  });

  // ── HOA expense update ───────────────────────────────────
  es.addEventListener('hoa-expense', async () => {
    try{
      if(typeof loadAdminExpenses === 'function') await loadAdminExpenses();
    }catch(err){}
  });

  // ── Reconnect cycle ──────────────────────────────────────
  es.addEventListener('reconnect', (e) => {
    const evt = JSON.parse(e.data);
    if(evt.lastEventId) _adminSseEventId = evt.lastEventId;
    es.close();
    _rtSse = null;
    if(_rtWsReady) return; // WS came up while this cycle ran — stop the SSE loop
    // Refresh on every cycle so updates still land through buffering tunnels (e.g. ngrok)
    loadAllData().catch(()=>{});
    setTimeout(connectAdminSSE, 200);
  });

  // ── Error — retry after 3 s ──────────────────────────────
  es.onerror = () => {
    es.close();
    _rtSse = null;
    if(!_rtWsReady) setTimeout(connectAdminSSE, 3000);
  };

  document.addEventListener('visibilitychange', () => {
    if(!document.hidden && es.readyState === EventSource.CLOSED && !_rtWsReady){
      setTimeout(connectAdminSSE, 500);
    }
  }, { once: true });
}

// ── Message thread fallback poll (WS pushes are the instant path) ──
setInterval(()=>{
  if(document.hidden) return;
  if(!document.getElementById('panel-messages')?.classList.contains('active')) return;
  refreshAdminMessages();
}, 10000);

// ── Safety-net: 60s fallback poll in case SSE is blocked ──
setInterval(async ()=>{
  if(document.hidden) return;
  try{
    const [annData, d] = await Promise.all([
      apiGet('/api/announcements'),
      apiGet('/api/dashboard-data'),
    ]);
    if(Array.isArray(annData) && annData.length && annData[0].id > _adminLastAnnId){
      const newOnes = annData.filter(a => a.id > _adminLastAnnId);
      _adminLastAnnId = annData[0].id; _adminSseAnnId = annData[0].id;
      announcements = annData;
      renderAnnouncements(); renderDashboard();
      if(newOnes.length) showAnnBanner(newOnes[0]);
    }
    const newIssues = d.issues||[];
    if(newIssues.length !== issues.length){
      if(newIssues.length > issues.length && _adminLastIssueCount > 0) showIssueBanner(newIssues[0]);
      issues = newIssues; _adminLastIssueCount = newIssues.length; _adminSseIssueId = newIssues[0]?.id||0;
      renderIssues(); renderDashboard();
      const openCount = issues.filter(i=>i.status!=='Resolved').length;
      if(document.getElementById('statIssues')) document.getElementById('statIssues').textContent = openCount;
      if(document.getElementById('issueBadge')) document.getElementById('issueBadge').textContent = openCount;
    }
  }catch(e){}
}, 60000);

loadAllData().then(() => {
  // Start SSE after initial data load — baseline IDs already set in loadAllData()
  _adminSseAnnId   = _adminLastAnnId;
  _adminSseIssueId = issues[0]?.id || 0;
  connectAdminSSE();
});
loadAlerts();
setInterval(function(){ if(!document.hidden) loadAlerts(); }, 30000);

/* ══ REVERB (WebSocket) real-time — primary channel; SSE only as fallback ══ */
(function initSubSyncRealtime(attempt){
  attempt = attempt || 0;
  if(!window.Echo){
    if(attempt < 20){ setTimeout(function(){ initSubSyncRealtime(attempt + 1); }, Math.min(300 * (attempt + 1), 3000)); }
    return;
  }
  try {
    const ch = window.Echo.private('subsync');
    const refresh = () => { if(typeof loadAllData === 'function') loadAllData().catch(function(){}); };
    ch.listen('.announcement', refresh).listen('.issue', refresh).listen('.policy', refresh).listen('.hoa-expense', refresh);
    ch.listen('.message', (evt) => {
      if(!evt || !evt.conversationId) return;
      const isOpen = String(evt.conversationId) === String(currentThread);
      const panelOpen = document.getElementById('panel-messages')?.classList.contains('active');
      if(evt.senderType === 'admin'){ if(isOpen) refreshAdminMessages(); return; }
      if(isOpen && panelOpen){ refreshAdminMessages(); return; }
      const _tName=(threads[evt.conversationId]&&threads[evt.conversationId].title)||'';
      showToast(_tName ? 'New message from ' + _tName + '.' : 'New message.');
      const badge = document.getElementById('msgBadge');
      if(badge){ badge.textContent = (parseInt(badge.textContent,10)||0) + 1; badge.style.display = 'inline-flex'; }
      refreshAdminThreadList();
      if(isOpen) refreshAdminMessages();
    });

    // Approvals & membership: the pending queue, badges and stat cards update live.
    const refreshStaffData = () => {
      loadAlerts();
      const ids = ['residents','manageusers','officers','dashboard','recommendations'];
      if(ids.some(id => document.getElementById('panel-'+id)?.classList.contains('active'))){
        loadAllData().catch(function(){});
      } else {
        const b = document.getElementById('pendingBadge');
        if(b) loadAlerts();
      }
    };
    ch.listen('.resident', refreshStaffData).listen('.officer', refreshStaffData).listen('.recommendation', refreshStaffData);

    // GCash payments: the validation queue updates live; the bell refreshes either way.
    ch.listen('.payment', () => {
      loadAlerts();
      if(document.getElementById('panel-gcash')?.classList.contains('active')){
        loadPaymentsQueue();
        loadFinanceOverview();
      }
    });

    // Documents officers send up: replaces the fallback poll while the socket is live.
    ch.listen('.officer-file', () => { if(!document.hidden && typeof pollOfficerFiles === 'function') pollOfficerFiles(); });

    // WebSocket-first: while Reverb is connected the SSE fallback stays off;
    // if the socket drops, bring the fallback back.
    const conn = window.Echo.connector && window.Echo.connector.pusher && window.Echo.connector.pusher.connection;
    if(conn){
      const wsUp = () => { _rtWsReady = true; if(_rtSse){ try{ _rtSse.close(); }catch(e){} _rtSse = null; } };
      const wsDown = () => { if(_rtWsReady){ _rtWsReady = false; connectAdminSSE(); } };
      if(conn.state === 'connected') wsUp();
      conn.bind('connected', wsUp);
      conn.bind('disconnected', wsDown);
      conn.bind('unavailable', wsDown);
    }
  } catch(e) {}
})();

/* Keep Leaflet maps correctly sized on resize / mobile orientation change */
window.addEventListener('resize', function(){
  clearTimeout(window.__mapResizeT);
  window.__mapResizeT = setTimeout(function(){
    try{ if(typeof mapInst!=='undefined' && mapInst) mapInst.invalidateSize(); }catch(e){}
    try{ if(typeof analyticsMapInst!=='undefined' && analyticsMapInst) analyticsMapInst.invalidateSize(); }catch(e){}
  }, 200);
});
window.addEventListener('orientationchange', function(){ setTimeout(function(){ try{ if(mapInst) mapInst.invalidateSize(); }catch(e){} }, 300); });

/* ══════════════ ARCHIVES ══════════════ */
let archiveKind='issues';
function archiveTab(){
  ['issues','files','announcements','policies'].forEach(x=>{const b=document.getElementById('arch-tab-'+x); if(b) b.className='btn btn-sm'+(x===archiveKind?' btn-primary':'');});
}
async function loadArchives(kind){
  archiveKind=kind||'issues';
  archiveTab();
  const el=document.getElementById('archiveList'); if(!el) return;
  el.innerHTML='<div class="empty-state" style="padding:12px;">Loading…</div>';
  let rows=[], render=null;
  try{
    if(archiveKind==='issues'){
      const d=await apiGet('/api/issues?archived=1'); rows=Array.isArray(d)?d:[];
      render=r=>`<tr><td>${escHtml(r.title)}</td><td>${escHtml(r.category||'')}</td><td>${escHtml(r.status||'')}</td><td style="text-align:right;"><button class="btn btn-sm" onclick="restoreArchive(${r.id})">Restore</button></td></tr>`;
    } else if(archiveKind==='files'){
      const d=await apiGet('/api/officer-files?archived=1'); rows=Array.isArray(d)?d:[];
      render=r=>`<tr><td>${escHtml(r.original_name||'')}</td><td>${escHtml(r.category||'')}</td><td>${escHtml(r.officer_name||'')}</td><td style="text-align:right;"><button class="btn btn-sm" onclick="restoreArchive(${r.id})">Restore</button></td></tr>`;
    } else if(archiveKind==='announcements'){
      const d=await apiGet('/api/announcements?status=Archived'); rows=Array.isArray(d)?d:[];
      render=r=>`<tr><td>${escHtml(r.title)}</td><td>${escHtml(r.posted_by||'')}</td><td>${escHtml(r.created_at||'')}</td><td style="text-align:right;"><button class="btn btn-sm" onclick="restoreArchive(${r.id})">Restore</button></td></tr>`;
    } else {
      const d=await apiGet('/api/hoa-policies'); rows=(Array.isArray(d)?d:[]).filter(p=>p.status==='Archived');
      render=r=>`<tr><td>${escHtml(r.title)}</td><td>${escHtml(r.category||'')}</td><td>${escHtml(r.effective_date||'')}</td><td style="text-align:right;"><button class="btn btn-sm" onclick="restoreArchive(${r.id})">Restore</button></td></tr>`;
    }
  }catch(e){ rows=[]; }
  window.__archRows=rows;
  if(!rows.length){ el.innerHTML='<div class="empty-state" style="padding:12px;">Nothing archived here.</div>'; return; }
  el.innerHTML='<table class="panel-table"><thead><tr><th>Title</th><th>Category</th><th>Info</th><th></th></tr></thead><tbody>'+rows.map(render).join('')+'</tbody></table>';
}
async function restoreArchive(id){
  try{
    if(archiveKind==='issues') await apiPatch('/api/issues/'+id+'/archive');
    else if(archiveKind==='files') await apiPatch('/api/officer-files/'+id+'/archive');
    else if(archiveKind==='announcements') await apiPatch('/api/announcements/'+id+'/archive');
    else if(archiveKind==='policies'){
      const p=(window.__archRows||[]).find(x=>x.id===id);
      if(p) await apiPut('/api/hoa-policies/'+id,{title:p.title,description:p.description,category:p.category,effective_date:p.effective_date,status:'Active'});
    }
    showToast('✅ Restored.');
    await loadArchives(archiveKind);
  }catch(e){ showToast('⚠ Could not restore.'); }
}

/* Allows the shared global search to navigate panels by name */
window.PortalNav = { go: function (name) { nav(name); } };

/* Re-render analytics charts when the theme changes (light/dark axis colours). */
window.addEventListener('subsync:theme', function () {
  const p = document.getElementById('panel-analytics');
  if (p && p.classList.contains('active') && typeof d3 !== 'undefined') {
    try { renderAnalytics(); } catch (e) {}
  }
});
/* ── REPORT TEMPLATES (admin/officer) ── */
let reportTemplatesAdmin=[];
async function loadReportTemplatesAdmin(){
  const box=document.getElementById('reportTemplateAdminList');
  if(!box) return;
  const data=await apiGet('/api/report-templates').catch(()=>[]);
  reportTemplatesAdmin=Array.isArray(data)?data:[];
  if(!reportTemplatesAdmin.length){ box.innerHTML='<div class="empty-state" style="padding:12px;">No templates yet.</div>'; return; }
  box.innerHTML=reportTemplatesAdmin.map(t=>`<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid var(--glass-border);">
      <div><div style="font-weight:600;font-size:13px;">${escHtml(t.name)}</div>
      <div style="font-size:11px;color:var(--text-dim);">${escHtml(t.type||'')}${(t.variables&&t.variables.length)?' &middot; '+t.variables.map(v=>escHtml(v)).join(', '):''}</div></div>
      <button class="btn btn-sm btn-danger" onclick="deleteReportTemplate(${t.id})">Delete</button>
    </div>`).join('');
}
async function saveReportTemplate(){
  const name=(document.getElementById('rtName')||{}).value?.trim();
  const type=(document.getElementById('rtType')||{}).value?.trim()||'General';
  const subject=(document.getElementById('rtSubject')||{}).value?.trim();
  const body=(document.getElementById('rtBody')||{}).value?.trim();
  const vars=(document.getElementById('rtVars')||{}).value||'';
  if(!name||!subject||!body){ showToast('Name, subject and body are required.'); return; }
  const variables=vars.split(',').map(v=>v.trim()).filter(Boolean);
  const res=await apiPost('/api/report-templates',{name,type,subject_template:subject,body_template:body,variables});
  if(res&&res.success){
    ['rtName','rtType','rtSubject','rtBody','rtVars'].forEach(id=>{const el=document.getElementById(id); if(el) el.value='';});
    showToast('Template added.');
    loadReportTemplatesAdmin();
  } else showToast((res&&(res.message||res.error))||'Could not add the template.');
}
async function deleteReportTemplate(id){
  if(!(await SubUI.confirm({title:'Delete template?',message:'Residents will no longer be able to generate this report.',confirmText:'Delete',danger:true,icon:'!'}))) return;
  const res=await apiDelete('/api/report-templates/'+id);
  if(res&&res.success){ showToast('Template deleted.'); loadReportTemplatesAdmin(); }
  else showToast('Could not delete the template.');
}

/* ── ISSUE CERTIFICATES / REPORTS ── */
async function loadIssueData(){
  const rSel=document.getElementById('issueResidentSelect');
  if(rSel){
    const data=await apiGet('/api/residents').catch(()=>[]);
    const list=Array.isArray(data)?data:[];
    rSel.innerHTML='<option value="">Select resident...</option>'+list.map(r=>`<option value="${r.id}">${escHtml(r.name)}${r.block_lot?' ('+escHtml(r.block_lot)+')':''}</option>`).join('');
  }
  // Templates reuse the same endpoint as the template manager.
  if(!reportTemplatesAdmin.length){ await loadReportTemplatesAdmin(); }
  const tSel=document.getElementById('issueTemplateSelect');
  if(tSel){
    tSel.innerHTML='<option value="">Select template...</option>'+reportTemplatesAdmin.map(t=>`<option value="${t.id}">${escHtml(t.name)}</option>`).join('');
  }
  renderIssueVars();
  loadIssuedReports();
}
function renderIssueVars(){
  const sel=document.getElementById('issueTemplateSelect');
  const box=document.getElementById('issueVarsContainer');
  if(!sel||!box) return;
  const t=reportTemplatesAdmin.find(x=>String(x.id)===String(sel.value));
  const vars=(t&&Array.isArray(t.variables))?t.variables:[];
  box.innerHTML=vars.map(v=>`<div class="f-field" style="margin-top:10px;"><span class="f-label">${escHtml(String(v).replace(/_/g,' '))}</span><input type="text" data-ivar="${escHtml(v)}" placeholder="Enter ${escHtml(String(v).replace(/_/g,' '))}"></div>`).join('');
}
async function issueReport(){
  const resident_id=(document.getElementById('issueResidentSelect')||{}).value;
  const template_id=(document.getElementById('issueTemplateSelect')||{}).value;
  if(!resident_id||!template_id){ showToast('Select a resident and a template.'); return; }
  const variables={};
  document.querySelectorAll('#issueVarsContainer [data-ivar]').forEach(i=>{ variables[i.dataset.ivar]=i.value; });
  const res=await apiPost('/api/reports/issue',{resident_id:Number(resident_id),template_id:Number(template_id),variables});
  if(res&&res.success){
    showToast('Document issued: '+(res.report&&res.report.reference_no));
    renderIssueVars();
    loadIssuedReports();
  } else showToast((res&&(res.error||res.message))||'Could not issue the document.');
}
async function loadIssuedReports(){
  const box=document.getElementById('issuedReportsList');
  if(!box) return;
  const data=await apiGet('/api/reports').catch(()=>[]);
  const list=Array.isArray(data)?data:[];
  if(!list.length){ box.innerHTML='<div class="empty-state" style="padding:12px;">No documents issued yet.</div>'; return; }
  box.innerHTML=list.map(r=>`<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid var(--glass-border);">
      <div><div style="font-weight:600;font-size:13px;">${escHtml(r.subject||'-')}</div>
      <div style="font-size:11px;color:var(--text-dim);">${escHtml(r.reference_no||'')} &middot; ${escHtml(r.resident||'')} &middot; ${escHtml(r.created_at||'')}${r.issued_by?' &middot; by '+escHtml(r.issued_by):''}</div></div>
      <a class="btn btn-sm" href="/reports/${r.id}/print?portal=admin" target="_blank" rel="noopener">Print</a>
    </div>`).join('');
}

/* ── IMPORT RESIDENTS (CSV) ── */
async function importResidents(){
  const input=document.getElementById('residentImportFile');
  const file=input&&input.files&&input.files[0];
  if(!file){ showToast('Choose a CSV file first.'); return; }
  const fd=new FormData(); fd.append('file',file);
  const csrf=document.querySelector('meta[name="csrf-token"]')?.content||'';
  const res=await fetch('/api/import/residents',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},body:fd}).then(r=>r.json()).catch(()=>({success:false}));
  if(res&&res.success){
    showToast('Imported '+res.imported+', skipped '+res.skipped+'.');
    if(input) input.value='';
    if(typeof loadAllData==='function') loadAllData();
  } else showToast((res&&(res.message||res.error))||'Import failed.');
}

/* ── COMMUNITY EVENTS (admin/officer) ── */
let adminEvents=[];
async function loadAdminEvents(){
  const box=document.getElementById('adminEventList');
  if(!box) return;
  const data=await apiGet('/api/events').catch(()=>[]);
  adminEvents=Array.isArray(data)?data:[];
  if(!adminEvents.length){ box.innerHTML='<div class="empty-state" style="padding:12px;">No upcoming events.</div>'; return; }
  box.innerHTML=adminEvents.map(e=>`<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid var(--glass-border);">
      <div><div style="font-weight:600;font-size:13px;">${escHtml(e.title)}</div>
      <div style="font-size:11px;color:var(--text-dim);">${escHtml(e.starts_at||'')}${e.location?' &middot; '+escHtml(e.location):''} &middot; ${e.going} going</div></div>
      <button class="btn btn-sm btn-danger" onclick="deleteEvent(${e.id})">Delete</button>
    </div>`).join('');
}
async function saveEvent(){
  const title=(document.getElementById('evTitle')||{}).value?.trim();
  const location=(document.getElementById('evLocation')||{}).value?.trim();
  const starts=(document.getElementById('evStarts')||{}).value;
  const ends=(document.getElementById('evEnds')||{}).value;
  const description=(document.getElementById('evDesc')||{}).value?.trim();
  if(!title||!starts){ showToast('Title and start time are required.'); return; }
  const res=await apiPost('/api/events',{title,location,starts_at:starts,ends_at:ends||null,description});
  if(res&&res.success){
    ['evTitle','evLocation','evStarts','evEnds','evDesc'].forEach(id=>{const el=document.getElementById(id); if(el) el.value='';});
    showToast('Event added.'); loadAdminEvents();
  } else showToast((res&&(res.message||res.error))||'Could not add the event.');
}
async function deleteEvent(id){
  if(!(await SubUI.confirm({title:'Delete event?',message:'This removes it from the community calendar.',confirmText:'Delete',danger:true,icon:'!'}))) return;
  const res=await apiDelete('/api/events/'+id);
  if(res&&res.success){ showToast('Event deleted.'); loadAdminEvents(); } else showToast('Could not delete the event.');
}

