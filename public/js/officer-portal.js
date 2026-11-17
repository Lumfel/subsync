/* ══ UTILS ══ */
/* ── Theme (Light / Dark) ── */
function toggleTheme(){
  const cur = document.documentElement.getAttribute('data-theme') || 'light';
  const next = cur === 'dark' ? 'light' : 'dark';
  setOfficerTheme(next);
}
function setOfficerTheme(theme){
  document.documentElement.setAttribute('data-theme', theme);
  document.documentElement.setAttribute('data-theme-pref', theme);
  try {
    localStorage.setItem('subsync.theme', theme);
    const prefs = JSON.parse(localStorage.getItem('subsync.prefs') || '{}');
    prefs.theme = theme;
    localStorage.setItem('subsync.prefs', JSON.stringify(prefs));
  } catch(e){}
  updateThemeToggleBtn(theme);
  try {
    window.dispatchEvent(new CustomEvent('subsync:theme', { detail: { theme } }));
  } catch(e){}
}
function updateThemeToggleBtn(theme){
  const el = document.getElementById('themeToggleIcon');
  if(!el) return;
  if(theme === 'dark'){
    el.innerHTML = (window.SubUI && window.SubUI.icon) ? SubUI.icon('sun', 15) : '☀️';
  } else {
    el.innerHTML = (window.SubUI && window.SubUI.icon) ? SubUI.icon('moon', 15) : '🌙';
  }
}
function toggleUserMenu(){
  const pop = document.getElementById('userMenuPopover');
  const btn = document.getElementById('userMenuButton');
  if(!pop) return;
  const isOpen = pop.classList.contains('open');
  if(isOpen){
    pop.classList.remove('open');
    if(btn) btn.setAttribute('aria-expanded', 'false');
  } else {
    pop.classList.add('open');
    if(btn) btn.setAttribute('aria-expanded', 'true');
  }
}
document.addEventListener('click', function(e){
  const wrap = document.querySelector('.user-menu-wrap');
  const pop = document.getElementById('userMenuPopover');
  if(pop && pop.classList.contains('open') && wrap && !wrap.contains(e.target)){
    pop.classList.remove('open');
    const btn = document.getElementById('userMenuButton');
    if(btn) btn.setAttribute('aria-expanded', 'false');
  }
});
document.addEventListener('DOMContentLoaded', function(){
  const cur = document.documentElement.getAttribute('data-theme') || 'light';
  updateThemeToggleBtn(cur);
});

function toggleMobileSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  if (sidebar) sidebar.classList.toggle('open');
  if (overlay) overlay.classList.toggle('show');
}
function confirmLogout(){
  SubUI.confirm({
    title: 'Log out of SubSync?',
    message: 'You will be returned to the sign-in page. Any unsaved changes will be lost.',
    confirmText: 'Log Out',
    cancelText: 'Stay signed in',
    danger: true,
    icon: '!'
  }).then(ok => {
    if(ok){
      try {
        sessionStorage.removeItem('subsync_active_tab_officer');
      } catch(e) {}
      document.getElementById('logoutForm').submit();
    }
  });
}
function changeImg(id,inp){
  const f=inp.files[0]; if(!f) return;
  const r=new FileReader();
  r.onload=e=>document.getElementById(id).src=e.target.result;
  r.readAsDataURL(f);
  if(id==='avatarPreview'){
    const fd=new FormData(); fd.append('avatar', f);
    fetch('/api/officers/avatar',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:fd})
      .then(x=>x.json())
      .then(d=>{ if(d.success) showToast('✅ Profile photo updated.'); else showToast('⚠ Could not save photo.'); })
      .catch(()=>showToast('⚠ Could not save photo.'));
  }
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
  const panel = document.getElementById('panel-'+name);
  const tab = document.getElementById('tab-'+name);
  if(panel) panel.classList.add('active');
  if(tab) tab.classList.add('active');
  try {
    sessionStorage.setItem('subsync_active_tab_officer', name);
    if(window.location.hash !== '#' + name){
      history.replaceState(null, '', '#' + name);
    }
  } catch(e) {}
  if(name==='messages'){
    const b=document.getElementById('badge-messages');
    if(b) b.style.display='none';
    loadOfficerThreads();
  }
  if(name==='calendar') loadOfficerEvents();
  if(name==='expenses') loadOfficerExpenseRequests();
  if(name==='map') setTimeout(function(){ if(typeof window.initOfficerMonitorMap==='function') window.initOfficerMonitorMap(); else if(typeof initOfficerMonitorMap==='function') initOfficerMonitorMap(); }, 80);
  if(name==='reports') { if(typeof renderOfficerIssues==='function') renderOfficerIssues(); }
  if(name==='announcements') { if(typeof renderMyAnn==='function') renderMyAnn(); }
  if(name==='files') { if(typeof renderSentFiles==='function') renderSentFiles(); }
  
  // Auto-close sidebar on mobile
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  if (sidebar) sidebar.classList.remove('open');
  if (overlay) overlay.classList.remove('show');
}

/* ══ DATA ══ */
let adminAnnouncements=[];
let myAnnouncements=[];
let officerIssues=[];
let sentFiles=[];
let lastAnnouncementId=0;
let announcementMap={};

const csrfToken=()=>document.querySelector('meta[name=csrf-token]').content;
async function apiGet(url){const r=await fetch(url,{headers:{'Accept':'application/json'}});return r.json();}
async function apiPost(url,data){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:JSON.stringify(data)});return r.json();}
async function apiPut(url,data){const r=await fetch(url,{method:'PUT',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:JSON.stringify(data)});return r.json();}
async function apiDelete(url){const r=await fetch(url,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()}});return r.json();}
async function safeGetArray(url){
  try{
    const data=await apiGet(url);
    return Array.isArray(data)?data:[];
  }catch(e){
    return [];
  }
}
function openModal(id){const el=document.getElementById('modal-'+id);if(el)el.classList.add('active');}
function closeModal(id){const el=document.getElementById('modal-'+id);if(el)el.classList.remove('active');}

async function loadOfficerData(){
  const [ann, iss, res, drafts]=await Promise.all([
    safeGetArray('/api/announcements'),
    safeGetArray('/api/issues'),
    safeGetArray('/api/residents'),
    safeGetArray('/api/announcements?status=Draft'),
  ]);
  adminAnnouncements=ann;
  if(ann.length) lastAnnouncementId=ann[0].id;
  // Own posts include drafts (the Active feed never carries them).
  myAnnouncements=ann.filter(a=>a.posted_by&&a.posted_by===window.__OFFICER.name)
    .concat((Array.isArray(drafts)?drafts:[]).filter(a=>a.posted_by&&a.posted_by===window.__OFFICER.name))
    .sort((a,b)=>(b.id||0)-(a.id||0));
  // Create a map for quick lookup by id
  announcementMap = {};
  adminAnnouncements.forEach(a => {
    announcementMap[a.id] = a;
  });
  officerIssues=iss;
  renderOverviewAnn();
  renderMyAnn();
  renderOfficerIssues();
  loadOfficerCommonIssues();
  // Update overview stat cards
  const openIssues=iss.filter(i=>i.status!=='Resolved').length;
  if(document.getElementById('annCount'))         document.getElementById('annCount').textContent=myAnnCountThisMonth();
  if(document.getElementById('openReportsCount')) document.getElementById('openReportsCount').textContent=openIssues;
  if(document.getElementById('blockResidentsCount')) document.getElementById('blockResidentsCount').textContent=Array.isArray(res)?res.filter(r=>r.status==='Active').length:'—';
}

/* Announcements posted by this officer in the current month (overview "Notices"). */
function myAnnCountThisMonth(){
  const now=new Date();
  return myAnnouncements.filter(a=>{
    if(a.status==='Draft') return false;
    if(!a.created_at) return false;
    const d=new Date(a.created_at);
    return !isNaN(d)&&d.getMonth()===now.getMonth()&&d.getFullYear()===now.getFullYear();
  }).length;
}

// View announcement details
function viewAnnouncement(id) {
  const ann = announcementMap[id];
  if (ann) {
    const tag=ann.tag||'notice';
    document.getElementById('annV-tag').className = `pill pill-${tag}`;
    document.getElementById('annV-tag').textContent = tag.charAt(0).toUpperCase() + tag.slice(1);
    document.getElementById('annV-priority').style.display = ann.priority === 'High' ? '' : 'none';
    document.getElementById('annV-title').textContent = ann.title;
    document.getElementById('annV-by').textContent = ann.posted_by;
    document.getElementById('annV-meta').innerHTML = `<span>${escHtml(ann.created_at)}</span>${ann.target && ann.target !== 'All Residents' ? `<span> • ${escHtml(ann.target)}</span>` : ''}${ann.event_date ? `<span> • ${escHtml(ann.event_date)}</span>` : ''}`;
    document.getElementById('annV-content').textContent = ann.content;
    document.getElementById('modal-annView').classList.add('open');
  }
}

/* ══ MESSAGES ══ */
let threads={};
let currentThread=null;

async function loadOfficerThreads(){
  const [data,requests]=await Promise.all([
    fetch('/api/messages/threads',{headers:{'Accept':'application/json'}}).then(r=>r.json()).catch(()=>[]),
    fetch('/api/messages/requests',{headers:{'Accept':'application/json'}}).then(r=>r.json()).catch(()=>[])
  ]);
  const sidebar=document.querySelector('.msg-sidebar-threads');
  const reqs=Array.isArray(requests)?requests:[];
  const reqHtml=reqs.length?`<div class="msg-req-block">
    <div class="msg-req-title">Message Requests (${reqs.length})</div>
    ${reqs.map(q=>`<div class="msg-req-item">
      <div class="msg-thread-info">
        <div class="msg-thread-name">${escHtml(q.from||q.title||'Someone')}</div>
        <div class="msg-thread-preview">${escHtml(q.last_message||'No message')}</div>
      </div>
      <div class="msg-req-actions">
        <button class="btn btn-sm" onclick="acceptOfficerRequest(${q.id})">Accept</button>
        <button class="btn btn-sm btn-danger" onclick="rejectOfficerRequest(${q.id})">Reject</button>
      </div>
    </div>`).join('')}
  </div>`:'';
  const list=Array.isArray(data)?data:[];
  list.forEach(t=>{threads[t.id]={...t,msgs:[]};});
  const threadsHtml=list.length?list.map((t,i)=>`<div class="msg-thread-item${i===0?' active':''}" onclick="selectOfficerThread(${t.id},this)">
      <div class="msg-thread-avatar ta-admin">${escHtml((t.title||'?').substring(0,2).toUpperCase())}</div>
      <div class="msg-thread-info">
        <div class="msg-thread-name">${escHtml(t.title)}</div>
        <div class="msg-thread-preview">${escHtml(t.last_message||'No messages yet')}</div>
        ${t.last_time?`<div style="font-size:10px;color:var(--text-dim);margin-top:3px;">Updated ${escHtml(t.last_time)}</div>`:''}
      </div>
      <button class="msg-thread-del" aria-label="Delete conversation" onclick="event.stopPropagation();deleteOfficerConversation(${t.id})" title="Delete">&#10005;</button>
    </div>`).join(''):'<div style="color:var(--text-dim);font-size:12px;padding:12px;">No conversations yet.<br><button class="btn btn-sm" style="margin-top:8px;" onclick="openOfficerNewConv()">Start one</button></div>';
  if(sidebar) sidebar.innerHTML=reqHtml+threadsHtml;
  if(list.length){
    currentThread=list[0].id;
    document.getElementById('chat-name').textContent=list[0].title||'Admin';
    document.getElementById('chat-avatar').textContent=(list[0].title||'SA').substring(0,2).toUpperCase();
    const msgs=await fetch('/api/messages/'+currentThread,{headers:{'Accept':'application/json'}}).then(r=>r.json()).catch(()=>[]);
    threads[currentThread].msgs=msgs;
    renderMessages();
  }
}
async function acceptOfficerRequest(id){
  const res=await fetch('/api/messages/'+id+'/accept',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()}}).then(r=>r.json()).catch(()=>({success:false}));
  if(res&&res.success){ showToast('Message request accepted.'); await loadOfficerThreads(); }
  else { showToast('Could not accept the request.'); }
}
async function rejectOfficerRequest(id){
  if(!(await SubUI.confirm({title:'Reject message request?',message:'This permanently deletes the conversation for both of you.',confirmText:'Reject',danger:true,icon:'!'}))) return;
  const res=await fetch('/api/messages/'+id+'/reject',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()}}).then(r=>r.json()).catch(()=>({success:false}));
  if(res&&res.success){ showToast('Message request rejected.'); await loadOfficerThreads(); }
  else { showToast('Could not reject the request.'); }
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
async function deleteOfficerConversation(id){
  if(!(await SubUI.confirm({ title:'Delete conversation?', message:'This deletes the conversation and all its messages.', confirmText:'Delete', danger:true, icon:'!' }))) return;
    const res=await fetch('/api/messages/'+id,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()}}).then(r=>r.json()).catch(()=>({success:false}));
  if(res.success){
    delete threads[id];
    if(currentThread==id){
      currentThread=null;
      const remaining=Object.keys(threads);
      if(remaining.length){currentThread=remaining[0];loadOfficerThreads();}
      else{document.getElementById('msgBody').innerHTML='<div style="color:var(--text-dim);text-align:center;padding:20px;font-size:13px;">No conversations.</div>';document.getElementById('chat-name').textContent='';}
    }
    // Re-load threads list
    loadOfficerThreads();
  } else {
    alert('Failed to delete conversation.');
  }
}

function escHtml(s){ const d=document.createElement('div'); d.textContent=s??''; return d.innerHTML; }
function renderMessages(){
  const body=document.getElementById('msgBody');
  const t=threads[currentThread];
  if(!t) return;
  const msgs=t.msgs||[];
  body.innerHTML=(msgs.length?'<div class="date-divider">Today</div>':'')+
    msgs.map(m=>{
      const isMe=m.is_me===true||m.is_me===1;
      const sender=isMe?'You':(m.sender_name||'Administration');
      return `<div class="msg-bubble-wrap ${isMe?'mine':''}">
        <div class="msg-sender">${escHtml(sender)}<span class="bubble-time">${escHtml(m.created_at||'')}</span></div>
        <div class="bubble ${isMe?'from-mine':'from-admin'}">${escHtml(m.content)}</div>
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
  } else {
    if(inp) inp.value=txt;
    showToast('Message not sent. Please try again.');
  }
}

/* New officer conversation */
async function openOfficerNewConv(){
  const el=document.getElementById('modal-officerNewConv');
  if(!el) return;
  document.getElementById('officerConvTitle').value='';
  el.classList.add('active');
  // Best-effort populate recipients; a failed lookup must not block the modal.
  const sel=document.getElementById('officerConvRecipient');
  if(sel){
    let residents=[];
    try{ const raw=await apiGet('/api/messages/residents'); residents=Array.isArray(raw)?raw:[]; }catch(e){ residents=[]; }
    sel.innerHTML='<option value="admin|0">🏢 HOA Admin</option>'+
      residents.map(r=>`<option value="resident|${r.id}">👤 ${r.name} (Resident)</option>`).join('');
  }
}
async function startOfficerConversation(){
  const sel=document.getElementById('officerConvRecipient');
  const [recipientType,recipientId]=sel?(sel.value||'admin|0').split('|'):['admin','0'];
  const recipientLabel=sel?sel.options[sel.selectedIndex].text:'HOA Admin';
  let title=document.getElementById('officerConvTitle').value.trim();
  if(!title) title=recipientLabel.replace(/^[^ ]+ /,'');
  const payload={title};
  if(recipientType==='resident'){ payload.recipient_type='resident'; payload.recipient_id=recipientId; }
  const res=await apiPost('/api/messages/start',payload);
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
    <div class="ann-item ann-item-${a.tag||'notice'}" onclick="viewAnnouncement(${a.id})" title="Click to view full announcement">
      <div class="ann-meta">
        <span class="pill pill-${a.tag||'notice'}">${tagLabel[a.tag]||'Notice'}</span>
        ${a.priority==='High'?'<span class="pill pill-urgent">High Priority</span>':''}
        <span class="ann-date">${escHtml(a.created_at)}</span>
      </div>
      <div class="ann-title">${escHtml(a.title)}</div>
      <div class="ann-body">${escHtml(a.content)}</div>
      ${a.target&&a.target!=='All Residents'?`<div style="font-size:11px;color:var(--text-dim);margin-top:4px;">🎯 ${escHtml(a.target)}</div>`:''}
      ${a.event_date?`<div style="font-size:11px;color:var(--text-dim);margin-top:2px;display:inline-flex;align-items:center;gap:4px;">${SubUI.icon('calendar',12)} ${escHtml(a.event_date)}</div>`:''}
      <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">
        <span class="pill ${a.status==='Archived'?'pill-inactive':'pill-active'}">${escHtml(a.status)}</span>
      </div>
      <div class="ann-click-hint">${SubUI.icon('search',12)} Click to view full details</div>
    </div>`).join(''):'<div class="empty-state">' + SubUI.icon('megaphone', 28) + 'No announcements yet.</div>';
}

function renderMyAnn(){
  const el=document.getElementById('myAnnList');
  const tagLabel={notice:'Notice',urgent:'Urgent',event:'Event'};
  if(!myAnnouncements.length){
    el.innerHTML='<div class="empty-state">' + SubUI.icon('megaphone', 28) + 'No announcements posted yet.</div>';
    return;
  }
  el.innerHTML=myAnnouncements.map(a=>`
    <div class="ann-item" onclick="viewAnnouncement(${a.id})">
      <div class="ann-meta">
        <span class="pill pill-${a.tag||'notice'}">${tagLabel[a.tag]||'Notice'}</span>
        ${a.priority==='High'?'<span class="pill pill-urgent">High Priority</span>':''}
        <span class="ann-date">${escHtml(a.created_at)}</span>
      </div>
      <div class="ann-title">${escHtml(a.title)}</div>
      <div class="ann-body">${escHtml(a.content)}</div>
      ${a.target&&a.target!=='All Residents'?`<div style="font-size:11px;color:var(--text-dim);margin-top:4px;">🎯 ${escHtml(a.target)}</div>`:''}
      ${a.event_date?`<div style="font-size:11px;color:var(--text-dim);margin-top:2px;display:inline-flex;align-items:center;gap:4px;">${SubUI.icon('calendar',12)} ${escHtml(a.event_date)}</div>`:''}
      <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">
        <span class="pill ${a.status==='Archived'?'pill-inactive':(a.status==='Draft'?'pill-pending':'pill-active')}">${escHtml(a.status)}</span>
      </div>
      <div class="ann-actions">
        ${a.status==='Draft'?`<button class="btn btn-sm btn-green" onclick="publishMyAnn(${a.id});event.stopPropagation();">${SubUI.icon('send',13)} Publish</button>`:''}
        <button class="btn btn-sm btn-danger" onclick="deleteMyAnn(${a.id});event.stopPropagation();">Delete</button>
      </div>
    </div>`).join('');
}
async function publishMyAnn(id){
  const res=await fetch('/api/announcements/'+id+'/publish',{method:'PATCH',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()}}).then(r=>r.json()).catch(()=>({success:false}));
  if(res&&res.success){
    showToast('✅ Announcement published to residents.');
    await loadOfficerData();
  } else {
    showToast('⚠ Could not publish the announcement.');
  }
}
async function deleteMyAnn(id){
  if(!(await SubUI.confirm({ title:'Delete announcement?', message:'Delete this announcement? This cannot be undone.', confirmText:'Delete', danger:true, icon:'!' }))) return;
  const res=await apiDelete('/api/announcements/'+id);
  if(res.success){
    adminAnnouncements=adminAnnouncements.filter(a=>a.id!==id);
    myAnnouncements=myAnnouncements.filter(a=>a.id!==id);
    renderMyAnn();
    renderOverviewAnn();
    showToast('🗑️ Announcement deleted.');
    if(document.getElementById('annCount')) document.getElementById('annCount').textContent=myAnnCountThisMonth();
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
    if(!draft) adminAnnouncements.unshift(res.announcement);
    myAnnouncements.unshift(res.announcement);
    document.getElementById('annTitle').value='';
    document.getElementById('annBody').value='';
    renderMyAnn();
    renderOverviewAnn();
    if(document.getElementById('annCount')) document.getElementById('annCount').textContent=myAnnCountThisMonth();
    showToast(draft?'Draft saved - publish it from your list below.':'Announcement posted!');
  } else {
    showToast('⚠ '+(res.message||'Failed to post announcement.'));
  }
}

function saveAnnDraft(){ postAnnouncement(true); }

/* Announcement quick-fill: built-in presets, replaced by DB templates when seeded. */
let _annTemplateList=[];
function applyAnnTemplate(type){
  const templates={
    notice:{tag:'notice',title:'Community Notice',body:'Dear residents,\n\nPlease be informed that [details here].\n\nThank you for your cooperation.'},
    urgent:{tag:'urgent',title:'Urgent Advisory',body:'Dear residents,\n\n[Urgent details here]. Please take immediate action.\n\nFor concerns, please contact the HOA office.'},
    event:{tag:'event',title:'Upcoming Event',body:'We are pleased to invite all residents to [Event Name].\n\nDate: [Date]\nVenue: [Location]\nTime: [Time]\n\nWe look forward to seeing you there!'},
  };
  const t=templates[type];
  if(!t) return;
  document.getElementById('annType').value=t.tag;
  document.getElementById('annTitle').value=t.title;
  document.getElementById('annBody').value=t.body;
  if(type==='event') document.getElementById('annDate').value='';
}
async function loadOfficerAnnTemplates(){
  try{ _annTemplateList=await apiGet('/api/announcement-templates'); }catch(e){ _annTemplateList=[]; }
  const row=document.getElementById('officerAnnTemplateRow');
  if(!row||!Array.isArray(_annTemplateList)||!_annTemplateList.length) return;
  row.innerHTML='<span style="font-size:11px;color:var(--text-dim);align-self:center;white-space:nowrap;">Template:</span>'+
    _annTemplateList.map(t=>`<button class="btn btn-sm" style="font-size:11px;padding:4px 10px;" onclick="applyAnnTemplateById(${t.id})">${escHtml(t.name)}</button>`).join('');
}
function applyAnnTemplateById(id){
  const t=(_annTemplateList||[]).find(x=>Number(x.id)===Number(id)); if(!t) return;
  if(t.type && ['notice','urgent','event'].includes(t.type)) document.getElementById('annType').value=t.type;
  document.getElementById('annTitle').value=t.subject_template||t.name||'';
  document.getElementById('annBody').value=t.body_template||'';
}

/* ══ COMMUNITY CALENDAR ══ */
async function loadOfficerEvents(){
  const el=document.getElementById('officerEventList');
  if(!el) return;
  let events=[];
  try{ events=await fetch('/api/events',{headers:{'Accept':'application/json'}}).then(r=>r.json()); }catch(e){ events=[]; }
  if(!Array.isArray(events)||!events.length){
    el.innerHTML='<div class="empty-state">'+SubUI.icon('calendar',28)+'No upcoming events.</div>';
    return;
  }
  el.innerHTML=events.map(e=>`
    <div class="report-item" style="margin-bottom:12px;">
      <div class="report-top">
        <div>
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:4px;">${SubUI.icon('calendar',12)} ${escHtml(e.starts_at||'')}${e.ends_at?' – '+escHtml(e.ends_at):''}</div>
          <div class="report-title">${escHtml(e.title)}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
          <span class="pill pill-notice">${parseInt(e.going,10)||0} going</span>
          ${e.maybe?`<span style="font-size:10px;color:var(--text-dim);">${parseInt(e.maybe,10)} maybe</span>`:''}
        </div>
      </div>
      ${e.location?`<div class="report-meta" style="font-size:13px;color:var(--text-mid);display:flex;align-items:center;gap:5px;">${SubUI.icon('map-pin',12)} ${escHtml(e.location)}</div>`:''}
      ${e.description?`<div class="report-meta" style="font-size:13px;color:var(--text-mid);margin-top:6px;line-height:1.55;">${escHtml(e.description)}</div>`:''}
    </div>`).join('');
}

/* ══ ISSUE RESPONSES ══ */
let officerRespondingId=null;

/* Roster for the issue "Assign To" picker (fetched once per session). */
let _officerRoster=null;
async function officerRoster(){
  if(_officerRoster) return _officerRoster;
  try{ const raw=await apiGet('/api/officers'); _officerRoster=Array.isArray(raw)?raw:[]; }catch(e){ _officerRoster=[]; }
  return _officerRoster;
}
async function loadOfficerCommonIssues(){
  const el=document.getElementById('officerCommonIssues');
  if(!el) return;
  let rows=[];
  try{ rows=await fetch('/api/issues/common',{headers:{'Accept':'application/json'}}).then(r=>r.json()); }catch(e){ rows=[]; }
  if(!Array.isArray(rows)||!rows.length){ el.innerHTML='<div class="empty-state" style="padding:8px;">No reported issues yet.</div>'; return; }
  const max=Math.max(...rows.map(r=>r.total))||1;
  el.innerHTML=rows.map((r,i)=>`
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:9px;">
      <div style="width:20px;text-align:right;font-weight:700;color:var(--accent);font-size:13px;">${i+1}</div>
      <div style="flex:1;min-width:0;">
        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text);margin-bottom:3px;">
          <span>${escHtml(r.category)}</span><span style="color:var(--text-dim);">${r.total}</span>
        </div>
        <div style="height:8px;border-radius:5px;background:#EDE7DC;overflow:hidden;"><div style="height:100%;width:${Math.round(r.total/max*100)}%;background:linear-gradient(90deg,#A07D53,#c87941);"></div></div>
      </div>
    </div>`).join('');
}
function renderOfficerIssues(){
  const sf=(document.getElementById('officerIssueFilter')||{}).value||'';
  const sort=(document.getElementById('officerIssueSort')||{}).value||'';
  const af=(document.getElementById('officerIssueAssigneeFilter')||{}).value||'';
  let filtered=officerIssues.filter(i=>!sf||i.status===sf);
  if(af==='mine'){
    const myId = window.__OFFICER_ID;
    filtered = filtered.filter(i => myId && Number(i.assigned_officer_id) === Number(myId));
  } else if(af==='unassigned'){
    filtered = filtered.filter(i => !i.assigned_officer_id);
  }
  if(sort==='urgent'){const order={Critical:0,High:1,Medium:2,Low:3};filtered=[...filtered].sort((a,b)=>(order[a.priority]??9)-(order[b.priority]??9));}
  const statusPill={Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'};
  const priColor={Critical:'#ff5555',High:'#f08080',Medium:'var(--yellow)',Low:'var(--green)'};
  const el=document.getElementById('officerIssueList');
  el.innerHTML=filtered.length?filtered.map(i=>{
    const isCritical=i.priority==='Critical';
    return `
    <div class="report-item ${isCritical?'issue-critical-row':''}" style="margin-bottom:12px;" onclick="openOfficerRespond(${i.id})" title="Click to view / respond to this issue">
      <div class="report-top">
        <div>
          <div style="font-size:13px;color:var(--text-dim);margin-bottom:4px;">${escHtml(i.resident)} · ${escHtml(i.block_lot)}</div>
          <div class="report-title">${isCritical?SubUI.icon('alert',12)+' ':''}${escHtml(i.title)}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
          ${statusPill[i.status]||''}
          ${i.priority?`<span style="font-size:12px;font-weight:700;color:${priColor[i.priority]||'var(--text-dim)'}">${escHtml(i.priority)}</span>`:''}
        </div>
      </div>
      <div class="report-meta" style="font-size:14px;color:var(--text-mid);margin-bottom:10px;line-height:1.55;">${escHtml(i.description)}</div>
      <div class="report-meta">
        <span class="pill pill-notice">${escHtml(i.category)}</span>
        ${i.assigned_officer?`<span class="pill pill-progress" title="Assigned officer">${SubUI.icon('user',12)} ${escHtml(i.assigned_officer)}</span>`:''}
        ${i.due_date?`<span class="pill ${i.overdue?'pill-urgent':'pill-notice'}" title="SLA due date">${i.overdue?SubUI.icon('clock',12)+' Overdue ':SubUI.icon('calendar',12)+' Due '}${escHtml(i.due_date)}</span>`:''}
        <span style="font-size:12.5px;color:var(--text-dim);">· #${i.id} · ${escHtml(i.created_at)}</span>
      </div>
      ${(i.media&&i.media.length)?`<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:10px;">${i.media.map(m=>{
        const isImg=/\.(png|jpe?g|gif|webp)$/i.test(m.name||m.url||'');
        return isImg
          ?`<a href="${escHtml(m.url)}" target="_blank" rel="noopener" onclick="event.stopPropagation();"><img src="${escHtml(m.url)}" alt="${escHtml(m.name)}" style="width:82px;height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--glass-border);display:block;"></a>`
          :`<a href="${escHtml(m.url)}" target="_blank" rel="noopener" onclick="event.stopPropagation();" style="display:inline-flex;align-items:center;gap:5px;font-size:12px;color:var(--accent);text-decoration:underline;">${SubUI.icon('paperclip',12)} ${escHtml(m.name)}</a>`;
      }).join('')}</div>`:''}
      ${(i.responses&&i.responses.length)?`<div style="margin-top:12px;padding:10px 12px;background:rgba(122,180,240,0.07);border-left:2px solid rgba(122,180,240,0.4);border-radius:0 6px 6px 0;">
        <div style="font-size:10px;color:var(--blue);font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">Response</div>
        <div style="font-size:13px;color:var(--text-mid);">${escHtml(i.responses[0].content)}</div>
      </div>`:''}
      <div class="report-actions" style="margin-top:10px;">
        ${i.status!=='Resolved'?`<button class="btn btn-sm" onclick="event.stopPropagation();openOfficerRespond(${i.id})">Respond</button>`:''}
        <button class="btn btn-sm" onclick="event.stopPropagation();openOfficerIssueMap(${i.id})">View on map</button>
        <span style="font-size:10px;color:var(--text-dim);align-self:center;">${SubUI.icon('search',12)} Click anywhere to open</span>
      </div>
    </div>`;
}).join(''):'<div class="empty-state">' + SubUI.icon('clipboard', 28) + 'No issues found.</div>';
  // Keep the monitoring map in sync when issue data refreshes
  if(officerMonitorMapInst && document.getElementById('panel-map')?.classList.contains('active')){
    renderOfficerMapPins();
  }
}
async function openOfficerRespond(id){
  officerRespondingId=id;
  const issue=officerIssues.find(i=>i.id===id);
  if(!issue) return;
  document.getElementById('officerRespondTitle').textContent=issue.title;
  document.getElementById('officerRespondStatus').value=issue.status;
  document.getElementById('officerRespondPriority').value=issue.priority||'Low';
  document.getElementById('officerRespondText').value=(issue.responses&&issue.responses[0])?issue.responses[0].content:'';
  // Show the resident's photo/document evidence inside the modal too.
  const mediaEl=document.getElementById('officerRespondMedia');
  if(mediaEl){
    mediaEl.innerHTML=(issue.media&&issue.media.length)
      ?'<div style="width:100%;font-size:10px;color:var(--text-dim);font-weight:700;letter-spacing:.06em;text-transform:uppercase;">Attachments</div>'+issue.media.map(m=>{
        const isImg=/\.(png|jpe?g|gif|webp)$/i.test(m.name||m.url||'');
        return isImg
          ?`<a href="${escHtml(m.url)}" target="_blank" rel="noopener"><img src="${escHtml(m.url)}" alt="${escHtml(m.name)}" style="width:96px;height:68px;object-fit:cover;border-radius:6px;border:1px solid var(--glass-border);display:block;"></a>`
          :`<a href="${escHtml(m.url)}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:5px;font-size:12px;color:var(--accent);text-decoration:underline;">${SubUI.icon('paperclip',12)} ${escHtml(m.name)}</a>`;
      }).join('')
      :'';
  }
  // Assignment + SLA due date, with a compact audit-trail preview.
  const assigneeSel=document.getElementById('officerRespondAssignee');
  if(assigneeSel){
    const roster=await officerRoster();
    assigneeSel.innerHTML='<option value="">Unassigned</option>'+roster.map(o=>`<option value="${o.id}">${escHtml(o.name)}${Number(o.id) === Number(window.__OFFICER_ID) ? ' (You)' : ''}</option>`).join('');
    assigneeSel.value=issue.assigned_officer_id?String(issue.assigned_officer_id):'';
  }
  const dueEl=document.getElementById('officerRespondDue');
  if(dueEl) dueEl.value=issue.due_date||'';
  const histEl=document.getElementById('officerRespondHistory');
  if(histEl){
    histEl.style.display='none'; histEl.innerHTML='';
    fetch('/api/issues/'+id+'/history',{headers:{'Accept':'application/json'}}).then(r=>r.json()).then(rows=>{
      if(!Array.isArray(rows)||!rows.length) return;
      histEl.innerHTML='<div style="font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-dim);margin-bottom:6px;">History</div>'+
        rows.slice(0,8).map(l=>`<div style="display:flex;justify-content:space-between;gap:10px;padding:3px 0;border-bottom:1px dashed var(--glass-border);">
          <span>${escHtml(l.action)}${(l.old||l.new)?': '+escHtml(String(l.old||'—'))+' → '+escHtml(String(l.new||'—')):''}</span>
          <span style="color:var(--text-dim);white-space:nowrap;">${escHtml(l.actor||'')} · ${escHtml(l.at||'')}</span>
        </div>`).join('');
      histEl.style.display='block';
    }).catch(()=>{});
  }
  openModal('officerRespond');
}
async function submitOfficerResponse(){
  const issue=officerIssues.find(i=>i.id===officerRespondingId);
  if(!issue) return;
  const newStatus=document.getElementById('officerRespondStatus').value;
  const newPriority=document.getElementById('officerRespondPriority').value;
  const content=document.getElementById('officerRespondText').value.trim();
  const assigneeSel=document.getElementById('officerRespondAssignee');
  const dueEl=document.getElementById('officerRespondDue');
  const newAssignee=assigneeSel&&assigneeSel.value?Number(assigneeSel.value):null;
  const newDue=dueEl&&dueEl.value?dueEl.value:null;
  const origAssignee=issue.assigned_officer_id?Number(issue.assigned_officer_id):null;
  const origDue=issue.due_date||null;
  const assignChanged=(newAssignee!==origAssignee)||(newDue!==origDue);
  const [statusRes,priorityRes,respondRes,assignRes]=await Promise.all([
    apiPut('/api/issues/'+officerRespondingId+'/status',{status:newStatus}),
    newPriority ? apiPut('/api/issues/'+officerRespondingId+'/priority',{priority:newPriority}) : Promise.resolve({success:true}),
    content ? apiPost('/api/issues/'+officerRespondingId+'/respond',{content}) : Promise.resolve({success:true}),
    assignChanged ? apiPut('/api/issues/'+officerRespondingId+'/assign',{assigned_officer_id:newAssignee,due_date:newDue}) : Promise.resolve({success:true}),
  ]);
  if(!statusRes||!statusRes.success){showToast('⚠ Failed to update status.');return;}
  if(newPriority && (!priorityRes||!priorityRes.success)){showToast('Failed to update priority.');return;}
  if(content && (!respondRes||!respondRes.success)){showToast('Failed to save the response.');return;}
  if(assignChanged && (!assignRes||!assignRes.success)){showToast('Failed to update the assignment.');return;}
  issue.status=newStatus;
  if(priorityRes&&priorityRes.success) issue.priority=newPriority;
  if(content) issue.responses=[{content}];
  if(assignChanged){
    issue.assigned_officer_id=newAssignee;
    issue.due_date=newDue;
    const match=(_officerRoster||[]).find(o=>Number(o.id)===Number(newAssignee));
    issue.assigned_officer=match?match.name:null;
    issue.overdue=newDue?newDue<new Date().toISOString().slice(0,10):false;
  }
  closeModal('officerRespond');
  renderOfficerIssues();
  showToast('✅ Response submitted.');
}

/* ══ FILE HANDLING ══ */
/* ══ ISSUE LOCATION MAP ══ */
let officerMapInst=null;
function loadOfficerLeaflet(){
  if(window.L && typeof L.heatLayer === 'function') return Promise.resolve();
  return new Promise((resolve,reject)=>{
    const b=(window.APP_BASE||'');
    if(!document.querySelector('link[href*="leaflet.css"]')){
      const css=document.createElement('link'); css.rel='stylesheet'; css.href=b+'/vendor/leaflet/leaflet.css'; document.head.appendChild(css);
    }
    const srcs = [
      b+'/vendor/leaflet/leaflet.js',
      b+'/vendor/leaflet/leaflet-heat.js'
    ];
    let i=0;
    (function next(){
      if(i>=srcs.length){
        if(window.L&&L.Icon&&L.Icon.Default){
          L.Icon.Default.mergeOptions({
            iconUrl:b+'/vendor/leaflet/images/marker-icon.png',
            iconRetinaUrl:b+'/vendor/leaflet/images/marker-icon-2x.png',
            shadowUrl:b+'/vendor/leaflet/images/marker-shadow.png'
          });
        }
        resolve(); return;
      }
      const s=document.createElement('script');
      s.src=srcs[i++];
      s.onload=next;
      s.onerror=()=>{ s.onerror=null; next(); };
      document.head.appendChild(s);
    })();
  });
}
/* ══ OFFICER MONITORING MAP ══ */
let officerMonitorMapInst=null, officerMonitorLayer=null, officerFacilitiesLayer=null, officerBlocksLayer=null, officerBlockNamesLayer=null, officerBlockStartsLayer=null, officerHeatLayer=null, officerMapSettings=null;
let officerBlocksData=[], officerActiveBlock='', officerBlockPolygons={};
async function initOfficerMonitorMap(){
  await loadOfficerLeaflet();
  if(officerMonitorMapInst){ setTimeout(()=>officerMonitorMapInst.invalidateSize(),100); return; }
  if(!officerIssues || !officerIssues.length){ try{ await loadOfficerData(); }catch(e){} }
  let b={};
  try{ b=await fetch('/api/map/settings',{headers:{'Accept':'application/json'}}).then(r=>r.json())||{}; }catch(e){ b={}; }
  officerMapSettings=b;
  const bounds=L.latLngBounds(
    [parseFloat(b.min_lat)||10.6195, parseFloat(b.min_lng)||122.9580],
    [parseFloat(b.max_lat)||10.6260, parseFloat(b.max_lng)||122.9650]
  );
  officerMonitorMapInst=L.map('officerMonitorMap',{maxBounds:bounds,maxBoundsViscosity:1.0,minZoom:14,maxZoom:20}).fitBounds(bounds,{padding:[16,16]});
  if(window.SubUI&&SubUI.enableDynamicPinScaling) SubUI.enableDynamicPinScaling(officerMonitorMapInst);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap'}).addTo(officerMonitorMapInst);
  if(b && b.overlay_enabled !== false && b.overlay_url){
    const oMinLat = parseFloat(b.overlay_min_lat) || parseFloat(b.min_lat) || 10.6195;
    const oMaxLat = parseFloat(b.overlay_max_lat) || parseFloat(b.max_lat) || 10.6260;
    const oMinLng = parseFloat(b.overlay_min_lng) || parseFloat(b.min_lng) || 122.9580;
    const oMaxLng = parseFloat(b.overlay_max_lng) || parseFloat(b.max_lng) || 122.9650;
    const oBounds = L.latLngBounds([[oMinLat, oMinLng], [oMaxLat, oMaxLng]]);
    const offOverlay = L.imageOverlay(b.overlay_url, oBounds, {
      opacity: typeof b.overlay_opacity === 'number' ? b.overlay_opacity : 0.65,
      interactive: false,
      className: 'subdivision-blueprint-overlay'
    }).addTo(officerMonitorMapInst);
    if(offOverlay.bringToBack) offOverlay.bringToBack();
    const applyRot = () => {
      const el = offOverlay.getElement();
      if(el && typeof b.overlay_rotation === 'number'){
        el.style.transformOrigin = 'center center';
        el.style.rotate = `${b.overlay_rotation}deg`;
      }
    };
    setTimeout(applyRot, 40);
    officerMonitorMapInst.on('zoomend moveend', applyRot);
  }
  L.rectangle(bounds,{color:'#A07D53',weight:2,dashArray:'6 4',fillColor:'#A07D53',fillOpacity:0.05}).addTo(officerMonitorMapInst);
  const officerBlocksChecked = Boolean(document.getElementById('officerLayerBlocks')?.checked);
  const officerNamesChecked = Boolean(document.getElementById('officerLayerBlockNames')?.checked ?? true);
  const officerStartsChecked = Boolean(document.getElementById('officerLayerBlockStarts')?.checked ?? true);
  officerBlocksLayer = L.layerGroup();
  officerBlockNamesLayer = L.layerGroup();
  officerBlockStartsLayer = L.layerGroup();
  if (officerBlocksChecked) {
    officerBlocksLayer.addTo(officerMonitorMapInst);
    if (officerNamesChecked) officerBlockNamesLayer.addTo(officerMonitorMapInst);
    if (officerStartsChecked) officerBlockStartsLayer.addTo(officerMonitorMapInst);
  }
  updateOfficerBlockSubTogglesUI(officerBlocksChecked);

  const officerFacChecked = document.getElementById('officerLayerFacilities') ? document.getElementById('officerLayerFacilities').checked : true;
  officerFacilitiesLayer = L.layerGroup();
  if (officerFacChecked) officerFacilitiesLayer.addTo(officerMonitorMapInst);

  const officerIssuesChecked = document.getElementById('officerLayerIssues') ? document.getElementById('officerLayerIssues').checked : true;
  officerMonitorLayer = L.layerGroup();
  if (officerIssuesChecked) officerMonitorLayer.addTo(officerMonitorMapInst);

  officerHeatLayer=L.layerGroup();
  loadOfficerBlocks();
  loadOfficerFacilities();
  renderOfficerMapPins();
  setTimeout(()=>officerMonitorMapInst.invalidateSize(),150);
}

function loadOfficerFacilities(){
  if(!officerFacilitiesLayer) return;
  fetch('/api/map/facilities').then(r=>r.json()).then(data=>{
    officerFacilitiesLayer.clearLayers();
    (data||[]).forEach(f=>{
      const lat=parseFloat(f.latitude), lng=parseFloat(f.longitude);
      if(isNaN(lat)||isNaN(lng)) return;
      const icon=L.divIcon({className:'',html:'<div class="sub-map-dot" style="width:13px;height:13px;border-radius:50%;background:#4CAF50;border:2px solid rgba(255,255,255,0.85);box-shadow:0 1px 5px rgba(0,0,0,.5);" title="Facility"></div>',iconSize:[13,13],iconAnchor:[6,6],popupAnchor:[0,-9]});
      let pop = '<strong>'+escHtml(f.name||'')+'</strong>';
      if(f.description) pop += '<br><small>'+escHtml(f.description)+'</small>';
      L.marker([lat,lng],{icon}).bindPopup(pop).addTo(officerFacilitiesLayer);
    });
  }).catch(()=>{});
}

function toggleOfficerFacilities(checked){
  if(!officerMonitorMapInst || !officerFacilitiesLayer) return;
  if(checked){
    if(!officerMonitorMapInst.hasLayer(officerFacilitiesLayer)) officerMonitorMapInst.addLayer(officerFacilitiesLayer);
    loadOfficerFacilities();
  } else {
    if(officerMonitorMapInst.hasLayer(officerFacilitiesLayer)) officerMonitorMapInst.removeLayer(officerFacilitiesLayer);
  }
}

function toggleOfficerIssues(checked){
  if(!officerMonitorMapInst || !officerMonitorLayer) return;
  if(checked){
    if(!officerMonitorMapInst.hasLayer(officerMonitorLayer)) officerMonitorMapInst.addLayer(officerMonitorLayer);
    renderOfficerMapPins();
  } else {
    if(officerMonitorMapInst.hasLayer(officerMonitorLayer)) officerMonitorMapInst.removeLayer(officerMonitorLayer);
  }
}
window.toggleOfficerFacilities = toggleOfficerFacilities;
window.toggleOfficerIssues = toggleOfficerIssues;
function renderOfficerMapPins(){
  if(!officerMonitorMapInst || !officerMonitorLayer) return;
  officerMonitorLayer.clearLayers();
  const sf=(document.getElementById('officerMapStatus')||{}).value||'';
  const pf=(document.getElementById('officerMapPriority')||{}).value||'';
  const filtered=(officerIssues||[]).filter(i=>{
    if(!i.latitude || !i.longitude) return false;
    if(sf && i.status!==sf) return false;
    if(pf && i.priority!==pf) return false;
    return true;
  });
  filtered.forEach((i,idx)=>{
    const color=i.status==='Resolved'?'#888':(i.status==='In Progress'?'#f5a623':'#e05555');
    const num=i.id!=null?i.id:(idx+1);
    const icon=L.divIcon({className:'',html:'<div class="sub-map-pin" style="width:24px;height:24px;border-radius:50%;background:'+color+';border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;">'+num+'</div>',iconSize:[24,24],iconAnchor:[12,12],popupAnchor:[0,-14]});
    const pri=i.priority?(' · '+escHtml(i.priority)):'';
    L.marker([parseFloat(i.latitude),parseFloat(i.longitude)],{icon})
      .addTo(officerMonitorLayer)
      .bindPopup('<strong>#'+num+' — '+escHtml(i.title||'')+'</strong><br>'+escHtml(i.category||'')+'<br><small>'+escHtml(i.status||'')+pri+' · '+escHtml(i.block_lot||'')+'</small><div style="margin-top:6px;"><button onclick="officerMapRespond('+i.id+')" style="padding:3px 9px;border:1px solid #ccc;border-radius:4px;cursor:pointer;font-size:11px;">Respond</button></div>');
  });
  if(officerHeatLayer && officerMonitorMapInst.hasLayer(officerHeatLayer)){
    renderOfficerHeatmap();
  }
}

function toggleOfficerHeatmap(checked){
  if(!officerMonitorMapInst) return;
  if(!officerHeatLayer) officerHeatLayer = L.layerGroup();
  const card = document.getElementById('officerHeatAnalysisCard');
  const catSel = document.getElementById('officerHeatCategory');
  if(checked){
    if(!officerMonitorMapInst.hasLayer(officerHeatLayer)) officerMonitorMapInst.addLayer(officerHeatLayer);
    if(card) card.style.display = 'block';
    if(catSel) catSel.style.display = 'inline-block';
    renderOfficerHeatmap();
  } else {
    officerMonitorMapInst.removeLayer(officerHeatLayer);
    if(card) card.style.display = 'none';
    if(catSel) catSel.style.display = 'none';
  }
}

function filterOfficerHeatmapToCategory(cat){
  const catSel = document.getElementById('officerHeatCategory');
  if(catSel){
    catSel.value = cat;
    renderOfficerHeatmap();
  }
}

let officerHeatCanvas = null;
function renderOfficerHeatmap(){
  if(!officerMonitorMapInst || !officerHeatLayer) return;
  officerHeatLayer.clearLayers();
  officerHeatCanvas = null;

  const card = document.getElementById('officerHeatAnalysisCard');
  if(!officerMonitorMapInst.hasLayer(officerHeatLayer)){
    if(card) card.style.display = 'none';
    return;
  }
  if(card) card.style.display = 'block';

  const catSel = document.getElementById('officerHeatCategory');
  const selectedCat = catSel ? catSel.value : '';
  const allIssues = (officerIssues || []).filter(i => {
    const lat = parseFloat(i.latitude), lng = parseFloat(i.longitude);
    return !isNaN(lat) && !isNaN(lng);
  });

  const overallCategoryCounts = {};
  allIssues.forEach(i => {
    const c = i.category || 'Other';
    overallCategoryCounts[c] = (overallCategoryCounts[c] || 0) + 1;
  });

  const sortedOverallCats = Object.entries(overallCategoryCounts).sort((a,b)=>b[1]-a[1]);
  const totalMappedIssues = allIssues.length;
  const topOverallCat = sortedOverallCats.length ? sortedOverallCats[0][0] : 'None';
  const topOverallCount = sortedOverallCats.length ? sortedOverallCats[0][1] : 0;
  const topOverallPct = totalMappedIssues ? Math.round((topOverallCount / totalMappedIssues) * 100) : 0;

  if(catSel){
    const prev = catSel.value;
    catSel.innerHTML = `<option value="">All categories (${totalMappedIssues} total)</option>`;
    sortedOverallCats.forEach(([c, cnt], idx)=>{
      const opt = document.createElement('option');
      opt.value = c;
      opt.textContent = `${idx === 0 ? '★ ' : ''}#${idx+1} ${c} (${cnt}${idx === 0 ? ' — Most Frequent' : ''})`;
      catSel.appendChild(opt);
    });
    catSel.value = prev;
  }

  const pillsContainer = document.getElementById('officerHeatCategoryPills');
  if(pillsContainer){
    let html = `
      <span style="font-size:11px;font-weight:700;color:var(--text-dim);margin-right:4px;">Filter Heatmap:</span>
      <button class="heatmap-pill-btn ${!selectedCat ? 'active' : ''}" onclick="filterOfficerHeatmapToCategory('')">
        All Issues <span class="pill-count">${totalMappedIssues}</span>
      </button>
    `;
    sortedOverallCats.forEach(([catName, count], idx)=>{
      const isSel = selectedCat === catName;
      const rankBadge = idx === 0 ? '★ ' : `#${idx + 1} `;
      html += `
        <button class="heatmap-pill-btn ${isSel ? 'active' : ''}" onclick="filterOfficerHeatmapToCategory('${catName.replace(/'/g, "\\'")}')">
          ${rankBadge}${escHtml(catName)} <span class="pill-count">${count}</span>
        </button>
      `;
    });
    pillsContainer.innerHTML = html;
  }

  const activeIssues = allIssues.filter(i => !selectedCat || i.category === selectedCat);

  const heatPoints = [];
  activeIssues.forEach(i => {
    const lat = parseFloat(i.latitude), lng = parseFloat(i.longitude);
    let intensity = 0.85;
    const st = (i.status || '').toLowerCase();
    if(st === 'in progress') intensity = 0.65;
    else if(st === 'resolved') intensity = 0.28;

    const pr = (i.priority || '').toLowerCase();
    if(pr === 'critical') intensity *= 1.4;
    else if(pr === 'high') intensity *= 1.2;
    else if(pr === 'low') intensity *= 0.8;

    if(i.created_at){
      const ageDays = (Date.now() - new Date(i.created_at).getTime()) / (1000 * 60 * 60 * 24);
      if(ageDays <= 3) intensity += 0.20;
      else if(ageDays <= 7) intensity += 0.10;
    }
    heatPoints.push([lat, lng, Math.min(intensity, 1.8)]);
  });

  if(typeof L.heatLayer === 'function' && heatPoints.length > 0){
    officerHeatCanvas = L.heatLayer(heatPoints, {
      radius: 34,
      blur: 20,
      maxZoom: 19,
      max: 2.2,
      minOpacity: 0.28,
      gradient: {
        0.15: '#38bdf8',
        0.35: '#34d399',
        0.55: '#facc15',
        0.75: '#fb923c',
        0.90: '#f87171',
        1.00: '#dc2626'
      }
    });
    officerHeatLayer.addLayer(officerHeatCanvas);
  }

  const CLUSTER_THRESH_DEG = 0.00072;
  const clusters = [];
  activeIssues.forEach(issue => {
    const lat = parseFloat(issue.latitude), lng = parseFloat(issue.longitude);
    let matched = null;
    for(const cl of clusters){
      if(Math.hypot(lat - cl.centerLat, lng - cl.centerLng) <= CLUSTER_THRESH_DEG){
        matched = cl; break;
      }
    }
    if(matched){
      matched.issues.push(issue);
      matched.centerLat = matched.issues.reduce((s,it)=>s+parseFloat(it.latitude),0)/matched.issues.length;
      matched.centerLng = matched.issues.reduce((s,it)=>s+parseFloat(it.longitude),0)/matched.issues.length;
    } else {
      clusters.push({ centerLat: lat, centerLng: lng, issues: [issue] });
    }
  });

  const summaryLine = document.getElementById('officerHeatSummaryLine');
  if(summaryLine){
    if(activeIssues.length === 0){
      summaryLine.innerHTML = selectedCat ? `No active issues for category <strong>${escHtml(selectedCat)}</strong>.` : 'No issues mapped in subdivision.';
    } else {
      const filterNotice = selectedCat ? `Filtered to <strong>${escHtml(selectedCat)}</strong> (${activeIssues.length} issues) · ` : '';
      summaryLine.innerHTML = `
        ${filterNotice}
        🏆 <strong>Subdivision Most Frequent Issue:</strong> 
        <span style="color:#ef4444;font-weight:800;">${escHtml(topOverallCat)}</span> 
        (${topOverallCount} of ${totalMappedIssues} reports · ${topOverallPct}%) across <strong>${clusters.length}</strong> active hotspot area${clusters.length > 1 ? 's' : ''}.
      `;
    }
  }

  clusters.forEach(cl => {
    const total = cl.issues.length;
    const catCounts = {};
    cl.issues.forEach(it => {
      const c = it.category || 'Other';
      catCounts[c] = (catCounts[c] || 0) + 1;
    });

    const sortedCats = Object.entries(catCounts).sort((a,b)=>b[1]-a[1]);
    const topCat = sortedCats[0][0];
    const topCount = sortedCats[0][1];
    const topPct = Math.round((topCount / total) * 100);

    const severity = total >= 4 ? 'critical' : (total === 3 ? 'high' : (total === 2 ? 'moderate' : 'low'));
    const severityLabel = total >= 4 ? 'Critical Hotspot' : (total === 3 ? 'High Heat Area' : (total === 2 ? 'Moderate Activity' : 'Single Issue Area'));
    const severityColor = total >= 4 ? '#dc2626' : (total === 3 ? '#ea580c' : (total === 2 ? '#d97706' : '#0284c7'));
    const flameIcon = total >= 4 ? '🔥🔥' : (total >= 2 ? '🔥' : '📍');

    const badgeHtml = `
      <div class="sub-hotspot-pill heat-${severity}" title="${severityLabel}: ${escHtml(topCat)} is most frequent (${topCount}/${total})">
        <span>${flameIcon}</span>
        <span class="hotspot-count">${total}</span>
        <span class="hotspot-cat">${escHtml(topCat)}</span>
        <span class="hotspot-pct">${topPct}%</span>
      </div>
    `;

    const icon = L.divIcon({
      className: 'sub-hotspot-marker',
      html: badgeHtml,
      iconSize: [0, 0],
      iconAnchor: [0, 0]
    });

    const breakdownRows = sortedCats.map(([cat, cnt]) => {
      const pct = Math.round((cnt / total) * 100);
      const isTop = cat === topCat;
      return `
        <div class="sub-hotspot-breakdown-row">
          <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:2px;">
            <span style="font-weight:${isTop ? '700' : '500'};color:var(--text);">${isTop ? '★ ' : ''}${escHtml(cat)}</span>
            <span style="font-weight:700;color:var(--text-dim);">${cnt} (${pct}%)</span>
          </div>
          <div class="sub-hotspot-bar-track">
            <div class="sub-hotspot-bar-fill" style="width:${pct}%;background:${isTop ? severityColor : '#94a3b8'};"></div>
          </div>
        </div>
      `;
    }).join('');

    const popupHtml = `
      <div class="sub-hotspot-popup">
        <div class="sub-hotspot-popup-header">
          <span style="color:${severityColor};font-weight:800;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">${flameIcon} ${severityLabel}</span>
          <span style="font-size:11px;font-weight:700;background:var(--glass, rgba(0,0,0,0.06));padding:2px 7px;border-radius:10px;">${total} issue${total > 1 ? 's' : ''} nearby</span>
        </div>
        
        <div class="sub-hotspot-dominant-box">
          <div class="sub-hotspot-dominant-tag">★ Most Frequent Issue Category</div>
          <div class="sub-hotspot-dominant-title">${escHtml(topCat)}</div>
          <div class="sub-hotspot-dominant-stat">
            <strong>${topCount} of ${total} reports</strong> in this area (${topPct}%)
          </div>
        </div>
        
        <div style="font-size:11px;font-weight:700;color:var(--text-mid);margin-bottom:6px;">Category Breakdown</div>
        <div class="sub-hotspot-breakdown">
          ${breakdownRows}
        </div>

        <button class="btn btn-sm btn-green" onclick="filterOfficerHeatmapToCategory('${topCat.replace(/'/g, "\\'")}')" style="width:100%;font-size:11px;padding:4px 8px;justify-content:center;font-weight:600;">
          Focus Heatmap on "${escHtml(topCat)}"
        </button>
      </div>
    `;

    const marker = L.marker([cl.centerLat, cl.centerLng], { icon })
      .bindPopup(popupHtml, { maxWidth: 320 });

    officerHeatLayer.addLayer(marker);
  });
}
function officerMapRespond(id){
  switchTab('reports');
  setTimeout(()=>{ if((officerIssues||[]).find(i=>i.id===id)) openOfficerRespond(id); }, 120);
}

async function loadOfficerBlocks(force = false){
  if(!force && officerBlocksData && officerBlocksData.length){
    renderOfficerBlocks();
    return;
  }
  try{
    const res = await fetch('/api/map/blocks',{headers:{'Accept':'application/json'}});
    officerBlocksData = await res.json() || [];
    renderOfficerBlocks();
  }catch(e){
    console.warn('Could not load officer blocks:', e);
  }
}

function getBlockStartInfo(b){
  const lbl = String(b.start_label || '').trim();
  let code = 'N', label = 'North', arrow = '↑';
  if (/east/i.test(lbl)) { code = 'E'; label = 'East'; arrow = '→'; }
  else if (/west/i.test(lbl)) { code = 'W'; label = 'West'; arrow = '←'; }
  else if (/north/i.test(lbl)) { code = 'N'; label = 'North'; arrow = '↑'; }
  else if (/south/i.test(lbl)) { code = 'S'; label = 'South'; arrow = '↓'; }
  else if (String(b.block_number) === '1') { code = 'E'; label = 'East'; arrow = '→'; }
  else if (String(b.block_number) === '2') { code = 'E'; label = 'East'; arrow = '→'; }
  const matchLot = lbl.match(/Lot\s*(\d+)/i);
  const startLot = matchLot ? `Lot ${matchLot[1]}` : 'Lot 1';
  return { code, label, arrow, startLot };
}

function renderOfficerBlocks(){
  if(!officerMonitorMapInst || !officerBlocksLayer) return;
  officerBlocksLayer.clearLayers();
  if(officerBlockNamesLayer) officerBlockNamesLayer.clearLayers();
  if(officerBlockStartsLayer) officerBlockStartsLayer.clearLayers();
  officerBlockPolygons = {};

  (officerBlocksData || []).forEach(b => {
    if(!b.coordinates || !b.coordinates.length) return;
    const isFiltered = officerActiveBlock && String(officerActiveBlock) !== String(b.block_number);
    const isBlock3 = String(b.block_number) === '3';
    const isBlock2 = String(b.block_number) === '2';

    const poly = L.polygon(b.coordinates, {
      color: b.color || '#2563eb',
      weight: isFiltered ? 1 : 2.5,
      dashArray: isBlock3 ? '6 4' : null,
      opacity: isFiltered ? 0.2 : 0.9,
      fillColor: b.color || '#2563eb',
      fillOpacity: isFiltered ? 0.03 : (isBlock3 ? 0.08 : 0.18),
      className: 'subdivision-block-poly'
    });

    if(isBlock3 && poly.bringToBack){
      poly.bringToBack();
    }

    const center = poly.getBounds().getCenter();
    const labelPos = (b.label_lat && b.label_lng) ? [b.label_lat, b.label_lng] : center;
    const blockDisplayName = isBlock2 ? '🏛 Clubhouse & Pool' : b.name;
    const lotCountText = isBlock2 ? 'Lot 15' : (b.total_lots ? `${b.total_lots} Lots` : '');
    const labelIcon = L.divIcon({
      className: 'sub-block-div-icon',
      html: `<div class="sub-block-pill${isBlock2 ? ' sub-block-pill-clubhouse' : ''}" style="background:${b.color || '#2563eb'};" title="${blockDisplayName}${lotCountText ? ' • ' + lotCountText : ''}"><span class="sub-block-name">${blockDisplayName}</span>${lotCountText ? `<span class="sub-block-count">• ${lotCountText}</span>` : ''}</div>`,
      iconSize: [0, 0],
      iconAnchor: [0, 0]
    });
    const labelMarker = L.marker(labelPos, { icon: labelIcon, interactive: true });
    labelMarker.on('click', () => poly.openPopup(labelMarker.getLatLng()));

    let startMarker = null;
    if(b.start_lat && b.start_lng){
      const info = getBlockStartInfo(b);
      const startTitle = `${b.name} • Starting ${info.startLot} (${info.label})`;
      const startIcon = L.divIcon({
        className: 'sub-block-div-icon',
        html: `<div class="sub-start-dir-marker" style="border-color:${b.color || '#ffffff'};" title="${startTitle}"><span class="start-dir-arrow">${info.arrow}</span><span class="start-dir-code">${info.code}</span></div>`,
        iconSize: [0, 0],
        iconAnchor: [0, 0]
      });
      startMarker = L.marker([b.start_lat, b.start_lng], { icon: startIcon, interactive: true, zIndexOffset: 1350 });

      const startPopupHtml = `
        <div class="sub-start-popup" style="min-width:145px;padding:3px 2px;">
          <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;padding-bottom:4px;border-bottom:1px solid rgba(148,163,184,0.25);">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:${b.color || '#0f172a'};color:#fff;font-size:10px;font-weight:800;">${info.code}</span>
            <div>
              <strong style="font-size:12px;color:var(--text, #0f172a);display:block;line-height:1.2;">${b.name}</strong>
              <span style="font-size:10px;color:var(--text-dim, #64748b);">Starting Point</span>
            </div>
          </div>
          <div style="font-size:11px;line-height:1.5;color:var(--text, #334155);">
            <div style="display:flex;justify-content:space-between;gap:8px;">
              <span style="color:var(--text-dim, #64748b);">Starting Lot:</span>
              <strong style="color:var(--text, #0f172a);">${info.startLot}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;gap:8px;margin-top:2px;">
              <span style="color:var(--text-dim, #64748b);">Starting Direction:</span>
              <strong style="color:var(--text, #0f172a);">${info.label} (${info.code})</strong>
            </div>
            ${b.total_lots ? `
            <div style="display:flex;justify-content:space-between;gap:8px;margin-top:2px;padding-top:2px;border-top:1px dashed rgba(148,163,184,0.2);">
              <span style="color:var(--text-dim, #64748b);">Total Lots:</span>
              <span style="font-weight:600;">${b.total_lots} lots</span>
            </div>` : ''}
          </div>
        </div>
      `;
      startMarker.bindPopup(startPopupHtml, { maxWidth: 220, offset: [0, -4] });
    }

    const lotRangeHtml = b.lot_range 
      ? `<div style="font-size:12px;font-weight:700;color:#1e293b;margin:4px 0 2px;">Lot Range: <strong>${b.lot_range}</strong> (${b.total_lots || ''} lots)</div>` 
      : '';
    const popupContent = `
      <div style="min-width:180px;">
        <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
          <span style="width:12px;height:12px;border-radius:3px;background:${b.color};display:inline-block;"></span>
          <strong style="font-size:13px;">${b.name}</strong>
        </div>
        ${lotRangeHtml}
        <div style="font-size:11px;color:#64748b;margin-bottom:6px;">${b.description || ''}</div>
        <div style="font-size:11px;margin-top:6px;padding:4px 0;border-top:1px solid #e2e8f0;">
          <strong>Households:</strong> ${b.households_count || 0} (${b.occupied_count || 0} Occupied, ${b.vacant_count || 0} Vacant)
        </div>
        <div style="font-size:11px;color:#475569;margin-top:2px;">
          <strong>Numbering:</strong> Starts at ${b.start_label || 'Lot 1'}
        </div>
        <button onclick="filterOfficerBlocks('${b.block_number}')" style="margin-top:8px;width:100%;padding:4px 8px;font-size:11px;border-radius:4px;border:1px solid ${b.color};background:${b.color};color:#fff;cursor:pointer;font-weight:700;">Filter to ${b.name}</button>
      </div>
    `;
    poly.bindPopup(popupContent);

    poly.addTo(officerBlocksLayer);
    if(!isFiltered){
      if(officerBlockNamesLayer) labelMarker.addTo(officerBlockNamesLayer);
      else labelMarker.addTo(officerBlocksLayer);
      if(startMarker){
        if(officerBlockStartsLayer) startMarker.addTo(officerBlockStartsLayer);
        else startMarker.addTo(officerBlocksLayer);
      }
    }

    officerBlockPolygons[b.block_number] = poly;
  });

  const toggle = document.getElementById('officerLayerBlocks');
  const namesChecked = Boolean(document.getElementById('officerLayerBlockNames')?.checked ?? true);
  const startsChecked = Boolean(document.getElementById('officerLayerBlockStarts')?.checked ?? true);
  if(toggle && officerMonitorMapInst && officerBlocksLayer){
    if(toggle.checked){
      if(!officerMonitorMapInst.hasLayer(officerBlocksLayer)) officerMonitorMapInst.addLayer(officerBlocksLayer);
      if(officerBlockNamesLayer && namesChecked && !officerMonitorMapInst.hasLayer(officerBlockNamesLayer)) officerMonitorMapInst.addLayer(officerBlockNamesLayer);
      if(officerBlockStartsLayer && startsChecked && !officerMonitorMapInst.hasLayer(officerBlockStartsLayer)) officerMonitorMapInst.addLayer(officerBlockStartsLayer);
    }else{
      if(officerMonitorMapInst.hasLayer(officerBlocksLayer)) officerMonitorMapInst.removeLayer(officerBlocksLayer);
      if(officerBlockNamesLayer && officerMonitorMapInst.hasLayer(officerBlockNamesLayer)) officerMonitorMapInst.removeLayer(officerBlockNamesLayer);
      if(officerBlockStartsLayer && officerMonitorMapInst.hasLayer(officerBlockStartsLayer)) officerMonitorMapInst.removeLayer(officerBlockStartsLayer);
    }
  }
  updateOfficerBlockSubTogglesUI(Boolean(toggle?.checked));
}

function updateOfficerBlockSubTogglesUI(blocksActive){
  const wrapNames = document.getElementById('wrap-officer-block-names');
  const wrapStarts = document.getElementById('wrap-officer-block-starts');
  const inputNames = document.getElementById('officerLayerBlockNames');
  const inputStarts = document.getElementById('officerLayerBlockStarts');
  if(wrapNames) wrapNames.classList.toggle('is-disabled', !blocksActive);
  if(wrapStarts) wrapStarts.classList.toggle('is-disabled', !blocksActive);
  if(inputNames) inputNames.disabled = !blocksActive;
  if(inputStarts) inputStarts.disabled = !blocksActive;
}

function toggleOfficerBlocks(show){
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('officerLayerBlocks')?.checked);
  const toggle = document.getElementById('officerLayerBlocks');
  if(toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  if(!officerMonitorMapInst || !officerBlocksLayer) return;
  if(!officerBlockNamesLayer) officerBlockNamesLayer = L.layerGroup();
  if(!officerBlockStartsLayer) officerBlockStartsLayer = L.layerGroup();

  const namesChecked = Boolean(document.getElementById('officerLayerBlockNames')?.checked ?? true);
  const startsChecked = Boolean(document.getElementById('officerLayerBlockStarts')?.checked ?? true);
  updateOfficerBlockSubTogglesUI(isChecked);

  if(isChecked){
    if(!officerMonitorMapInst.hasLayer(officerBlocksLayer)) officerMonitorMapInst.addLayer(officerBlocksLayer);
    if(namesChecked && !officerMonitorMapInst.hasLayer(officerBlockNamesLayer)) officerMonitorMapInst.addLayer(officerBlockNamesLayer);
    if(startsChecked && !officerMonitorMapInst.hasLayer(officerBlockStartsLayer)) officerMonitorMapInst.addLayer(officerBlockStartsLayer);
    if(!officerBlocksData || !officerBlocksData.length) loadOfficerBlocks();
  }else{
    if(officerMonitorMapInst.hasLayer(officerBlocksLayer)) officerMonitorMapInst.removeLayer(officerBlocksLayer);
    if(officerMonitorMapInst.hasLayer(officerBlockNamesLayer)) officerMonitorMapInst.removeLayer(officerBlockNamesLayer);
    if(officerMonitorMapInst.hasLayer(officerBlockStartsLayer)) officerMonitorMapInst.removeLayer(officerBlockStartsLayer);
  }
}

function toggleOfficerBlockNames(show){
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('officerLayerBlockNames')?.checked);
  const toggle = document.getElementById('officerLayerBlockNames');
  if(toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  if(!officerMonitorMapInst || !officerBlockNamesLayer) return;
  const blocksOn = Boolean(document.getElementById('officerLayerBlocks')?.checked);
  if(isChecked && blocksOn){
    if(!officerMonitorMapInst.hasLayer(officerBlockNamesLayer)) officerMonitorMapInst.addLayer(officerBlockNamesLayer);
  }else{
    if(officerMonitorMapInst.hasLayer(officerBlockNamesLayer)) officerMonitorMapInst.removeLayer(officerBlockNamesLayer);
  }
}

function toggleOfficerBlockStarts(show){
  const isChecked = typeof show === 'boolean' ? show : Boolean(document.getElementById('officerLayerBlockStarts')?.checked);
  const toggle = document.getElementById('officerLayerBlockStarts');
  if(toggle && toggle.checked !== isChecked) toggle.checked = isChecked;
  if(!officerMonitorMapInst || !officerBlockStartsLayer) return;
  const blocksOn = Boolean(document.getElementById('officerLayerBlocks')?.checked);
  if(isChecked && blocksOn){
    if(!officerMonitorMapInst.hasLayer(officerBlockStartsLayer)) officerMonitorMapInst.addLayer(officerBlockStartsLayer);
  }else{
    if(officerMonitorMapInst.hasLayer(officerBlockStartsLayer)) officerMonitorMapInst.removeLayer(officerBlockStartsLayer);
  }
}
window.toggleOfficerBlocks = toggleOfficerBlocks;
window.toggleOfficerBlockNames = toggleOfficerBlockNames;
window.toggleOfficerBlockStarts = toggleOfficerBlockStarts;
window.updateOfficerBlockSubTogglesUI = updateOfficerBlockSubTogglesUI;

function filterOfficerBlocks(blockNum){
  officerActiveBlock = blockNum || '';
  const sel = document.getElementById('officerFilterBlock');
  if(sel) sel.value = officerActiveBlock;

  if (officerActiveBlock) {
    const toggle = document.getElementById('officerLayerBlocks');
    if (toggle && !toggle.checked) {
      toggle.checked = true;
      toggleOfficerBlocks(true);
    }
  }

  renderOfficerBlocks();

  if(officerActiveBlock && officerBlockPolygons[officerActiveBlock] && officerMonitorMapInst){
    const b = officerBlockPolygons[officerActiveBlock].getBounds();
    officerMonitorMapInst.fitBounds(b, { padding: [30, 30], maxZoom: 19 });
  }else if(officerMonitorMapInst && officerMapSettings){
    const bounds = L.latLngBounds(
      [parseFloat(officerMapSettings.min_lat) || 10.6195, parseFloat(officerMapSettings.min_lng) || 122.9580],
      [parseFloat(officerMapSettings.max_lat) || 10.6260, parseFloat(officerMapSettings.max_lng) || 122.9650]
    );
    officerMonitorMapInst.fitBounds(bounds, { padding: [16, 16] });
  }
}

function openOfficerIssueMap(id){
  const issue=officerIssues.find(i=>i.id===id);
  if(!issue) return;
  document.getElementById('officerMapTitle').textContent='Report #'+issue.id+' · '+issue.title;
  openModal('officerMap');
  const lat=parseFloat(issue.latitude), lng=parseFloat(issue.longitude);
  loadOfficerLeaflet().then(()=>{
    setTimeout(()=>{
      if(officerMapInst){ officerMapInst.remove(); officerMapInst=null; }
      const canvas=document.getElementById('officerMapCanvas');
      if(!lat||!lng){
        canvas.innerHTML='<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-dim);font-size:13px;">No location was recorded for this issue.</div>';
        return;
      }
      officerMapInst=L.map('officerMapCanvas').setView([lat,lng],18);
      if(window.SubUI&&SubUI.enableDynamicPinScaling) SubUI.enableDynamicPinScaling(officerMapInst);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap'}).addTo(officerMapInst);
      const color=issue.priority==='Critical'?'#e05555':(issue.priority==='High'?'#f08080':'#f5a623');
      const icon=L.divIcon({className:'',html:'<div class="sub-map-pin" style="width:24px;height:24px;border-radius:50%;background:'+color+';border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;">'+issue.id+'</div>',iconSize:[24,24],iconAnchor:[12,12],popupAnchor:[0,-14]});
      L.marker([lat,lng],{icon}).addTo(officerMapInst).bindPopup('<strong>'+issue.title+'</strong><br>'+issue.category).openPopup();
      setTimeout(()=>officerMapInst.invalidateSize(),150);
    },80);
  }).catch(()=>{ document.getElementById('officerMapCanvas').innerHTML='<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-dim);">Could not load the map.</div>'; });
}

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
  if(!f.name.match(/\.(xlsx|xls|csv|pdf|doc|docx|png|jpg|jpeg|gif|webp)$/i)){showToast('⚠ Please upload an Excel, CSV, PDF, Word, or image file.');return;}
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
  if(input.files[0].size > 10 * 1024 * 1024){showToast('⚠ File is larger than the 10 MB limit.'); return;}

  const fd = new FormData();
  fd.append('file',     input.files[0]);
  fd.append('category', cat);
  fd.append('period',   period || todayStr());
  fd.append('notes',    notes);

  const sendBtn = document.querySelector('[onclick="sendFile()"]');
  if(sendBtn){ sendBtn.disabled=true; sendBtn.textContent='Uploading…'; }
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
      showToast('⚠ ' + (res.message || 'Upload failed.'));
    }
  } catch(e) {
    showToast('⚠ Upload error: ' + e.message);
  } finally {
    if(sendBtn){ sendBtn.disabled=false; sendBtn.innerHTML=SubUI.icon('send',15)+' Send to Admin'; }
  }
}

function renderSentFiles(){
  const el=document.getElementById('sentFilesList');
  if(!sentFiles.length){
    el.innerHTML='<div class="empty-state">' + SubUI.icon('folder', 28) + 'No files sent yet.</div>';
  } else {
    el.innerHTML=sentFiles.map((f,i)=>`
      <div class="file-item">
        <span class="file-icon">${SubUI.icon('folder',14)}</span>
        <div class="file-info">
          <div class="file-name">${f.name}</div>
          <div class="file-meta">${f.category} · ${f.period} · ${f.date} · ${f.size}</div>
        </div>
        <span class="file-status ${f.status==='sent'?'file-sent':'file-pending-send'}">${f.status==='sent'?'Sent':'Pending'}</span>
        ${f.id?`<a class="btn btn-sm" style="margin-left:8px;" href="/api/officer-files/${f.id}/download">${SubUI.icon('download',13)} Download</a>`:''}
        ${f.id?`<button class="btn btn-sm btn-danger" style="margin-left:8px;" onclick="deleteSentFile(${f.id})">Delete</button>`:''}
      </div>`).join('');
  }
  if(document.getElementById('filesSentCount')) document.getElementById('filesSentCount').textContent=sentFiles.length;
}

async function deleteSentFile(id){
  if(!(await SubUI.confirm({ title:'Delete file?', message:'Delete this sent file? This cannot be undone.', confirmText:'Delete', danger:true, icon:'!' }))) return;
  const res=await apiDelete('/api/officer-files/'+id).catch(()=>({}));
  if(res.success){
    const idx=sentFiles.findIndex(f=>f.id===id);
    if(idx>-1) sentFiles.splice(idx,1);
    renderSentFiles();
    showToast('✅ File removed.');
  }
}

/* ══ EXPENSE CLAIMS & REIMBURSEMENTS ══ */
let officerExpenseRequests = [];

async function loadOfficerExpenseRequests(){
  const el = document.getElementById('officerExpenseList');
  if(!el) return;
  try {
    officerExpenseRequests = await apiGet('/api/expense-requests');
  } catch(e) {
    officerExpenseRequests = [];
  }
  renderOfficerExpenseList();
}

function renderOfficerExpenseList(){
  const el = document.getElementById('officerExpenseList');
  if(!el) return;
  if(!Array.isArray(officerExpenseRequests) || !officerExpenseRequests.length){
    el.innerHTML = '<div class="empty-state">' + SubUI.icon('coins', 28) + '<div style="margin-top:8px;">No expense claims submitted yet. Click "+ Submit Expense Claim" above.</div></div>';
    return;
  }

  const statusBadge = s => {
    if(s === 'Approved') return '<span class="pill pill-resolved">Approved</span>';
    if(s === 'Rejected') return '<span class="pill pill-urgent">Rejected</span>';
    return '<span class="pill pill-pending">Pending Review</span>';
  };

  const peso = v => '₱' + Number(v||0).toLocaleString(undefined, {minimumFractionDigits:2});

  el.innerHTML = '<table class="panel-table" style="width:100%;"><thead><tr>' +
    '<th>Date</th><th>Category</th><th>Fund</th><th>Title / Purpose</th><th>Amount</th><th>Linked Issue</th><th>Status</th><th>Receipt</th><th>Notes</th><th></th>' +
    '</tr></thead><tbody>' +
    officerExpenseRequests.map(r => `<tr>
      <td>${escHtml(r.expense_date||'—')}</td>
      <td><span class="pill pill-notice">${escHtml(r.category||'Maintenance')}</span></td>
      <td style="font-size:12px;color:var(--text-dim);">${escHtml(r.fund||'Operating')}</td>
      <td>
        <div style="font-weight:600;color:var(--text);">${escHtml(r.title)}</div>
        <div style="font-size:12px;color:var(--text-dim);">${escHtml(r.description||'')}</div>
      </td>
      <td style="font-weight:600;color:#c87941;">${peso(r.amount)}</td>
      <td>${r.issue_id ? `<span class="pill pill-progress" title="${escHtml(r.issue_title||'')}">#${r.issue_id}</span>` : '—'}</td>
      <td>${statusBadge(r.status)}</td>
      <td>${r.receipt_url ? `<a href="${r.receipt_url}" target="_blank" rel="noopener" class="btn btn-sm" style="display:inline-flex;align-items:center;gap:4px;">${SubUI.icon('search',12)} View</a>` : '—'}</td>
      <td style="font-size:11px;color:var(--text-dim);max-width:180px;">${r.admin_notes ? escHtml(r.admin_notes) : (r.status==='Approved' ? '✅ Posted to HOA ledger' : '—')}</td>
      <td style="text-align:right;">
        ${r.status === 'Pending' ? `<button class="btn btn-sm btn-danger" onclick="cancelExpenseRequest(${r.id})">Cancel</button>` : ''}
      </td>
    </tr>`).join('') +
    '</tbody></table>';
}

function openExpenseClaimModal(preselectedIssueId = null){
  document.getElementById('officerExpTitle').value = '';
  document.getElementById('officerExpCategory').value = 'Maintenance';
  document.getElementById('officerExpAmount').value = '';
  document.getElementById('officerExpDate').value = new Date().toISOString().slice(0, 10);
  document.getElementById('officerExpFund').value = 'Operating';
  document.getElementById('officerExpDescription').value = '';
  document.getElementById('officerExpReceipt').value = '';
  document.getElementById('officerExpMsg').textContent = '';

  // Populate issue dropdown
  const issueSelect = document.getElementById('officerExpIssue');
  if(issueSelect){
    let opts = '<option value="">— Not linked to an issue —</option>';
    (officerIssues || []).forEach(iss => {
      const sel = (preselectedIssueId && Number(preselectedIssueId) === Number(iss.id)) ? 'selected' : '';
      opts += `<option value="${iss.id}" ${sel}>#${iss.id} — ${escHtml(iss.title)} (${iss.status})</option>`;
    });
    issueSelect.innerHTML = opts;
  }

  openModal('officerExpenseClaim');
}

function openClaimForCurrentIssue(){
  closeModal('officerRespond');
  setTimeout(() => {
    openExpenseClaimModal(officerRespondingId);
  }, 120);
}

async function submitOfficerExpenseClaim(){
  const title = document.getElementById('officerExpTitle').value.trim();
  const cat = document.getElementById('officerExpCategory').value;
  const amt = document.getElementById('officerExpAmount').value;
  const date = document.getElementById('officerExpDate').value;
  const fund = document.getElementById('officerExpFund').value;
  const issueId = document.getElementById('officerExpIssue').value;
  const desc = document.getElementById('officerExpDescription').value.trim();
  const receipt = document.getElementById('officerExpReceipt').files[0];
  const msg = document.getElementById('officerExpMsg');

  if(!title || !amt || !date || !desc){
    msg.textContent = 'Please fill in Title, Amount, Date, and Details.';
    return;
  }
  if(Number(amt) <= 0){
    msg.textContent = 'Amount must be greater than zero.';
    return;
  }

  const fd = new FormData();
  fd.append('title', title);
  fd.append('category', cat);
  fd.append('amount', amt);
  fd.append('expense_date', date);
  fd.append('fund', fund);
  fd.append('description', desc);
  if(issueId) fd.append('issue_id', issueId);
  if(receipt) fd.append('receipt', receipt);

  msg.textContent = 'Submitting claim…';
  try {
    const res = await fetch('/api/expense-requests', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
      },
      body: fd,
    }).then(r => r.json());

    if(res.success){
      closeModal('officerExpenseClaim');
      await loadOfficerExpenseRequests();
      showToast('✅ Expense claim submitted for admin approval.');
    } else {
      msg.textContent = res.message || 'Could not submit claim.';
    }
  } catch(e) {
    msg.textContent = 'Error submitting claim. Please try again.';
  }
}

async function cancelExpenseRequest(id){
  const ok = await SubUI.confirm({
    title: 'Cancel this claim?',
    message: 'Are you sure you want to cancel this pending expense claim?',
    confirmText: 'Cancel Claim',
    danger: true,
    icon: '!',
  });
  if(!ok) return;

  const res = await apiDelete('/api/expense-requests/' + id).catch(() => ({}));
  if(res && res.success){
    await loadOfficerExpenseRequests();
    showToast('Expense claim cancelled.');
  } else {
    showToast('⚠ Could not cancel claim.');
  }
}

/* ══ INIT ══ */
const OFFICER_ID = window.__OFFICER.id;
loadOfficerData();
loadOfficerAnnTemplates();

/* ── Save officer name on blur ── */
const _oNameEl = document.getElementById('officerName');
if(_oNameEl && OFFICER_ID){
  let _oNameSaved = _oNameEl.textContent.trim();
  _oNameEl.addEventListener('blur', async()=>{
    const n = _oNameEl.textContent.trim();
    if(!n || n === _oNameSaved){ _oNameEl.textContent=_oNameSaved||n; return; }
    try{
      const r = await fetch('/api/officers/'+OFFICER_ID,{
        method:'PUT',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},
        body: JSON.stringify({name: n}),
      });
      const res = await r.json();
      if(res.success){ _oNameSaved=n; showToast('✅ Name updated.'); }
      else { _oNameEl.textContent=_oNameSaved; showToast('⚠ Could not save name.'); }
    } catch(e){ _oNameEl.textContent=_oNameSaved; showToast('⚠ Network error.'); }
  });
  _oNameEl.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); _oNameEl.blur(); } });
}


function showAnnBanner(ann){
  document.getElementById('annNotifTitle').textContent=ann.title;
  const notif=document.getElementById('annNotif');
  notif.classList.add('show');
  clearTimeout(notif._t);
  notif._t=setTimeout(()=>notif.classList.remove('show'),8000);
}

document.getElementById('annNotifViewBtn').onclick=()=>{
  document.getElementById('annNotif').classList.remove('show');
  switchTab('announcements');
};

/* ══════════════════════════════════════════════════════════
   REAL-TIME: Server-Sent Events (SSE)
   The server pushes a tiny JSON event the instant the DB
   changes (~1 s resolution). JS then fetches full data from
   the existing REST endpoints.
   ══════════════════════════════════════════════════════════ */
let _lastUrgentId = null;
function dismissEmergency(){ const el = document.getElementById('emergencyAlert'); if (el) el.style.display='none'; }

let _sseAnnId   = 0;
let _sseIssueId = 0;
let _sseEventId = 0;
let _rtWsReady = false;   // true while the Reverb socket is connected
let _rtSse = null;        // the fallback EventSource (null when WS is primary)

/* Re-fetch the open thread; used by the WS push and the 10s fallback poll. */
async function refreshOfficerMessages(){
  if(!currentThread) return;
  try{
    const msgs = await fetch('/api/messages/'+currentThread,{headers:{'Accept':'application/json'}}).then(r=>r.json());
    if(Array.isArray(msgs) && msgs.length !== (threads[currentThread]?.msgs||[]).length){
      threads[currentThread].msgs = msgs;
      renderMessages();
    }
  }catch(e){}
}

/* Refresh just the "Message Requests" block — who is trying to reach you. */
async function refreshOfficerRequestBlock(){
  const sidebar = document.querySelector('.msg-sidebar-threads');
  if(!sidebar) return;
  let reqs = [];
  try{ reqs = await fetch('/api/messages/requests',{headers:{'Accept':'application/json'}}).then(r=>r.json()); }catch(e){}
  reqs = Array.isArray(reqs) ? reqs : [];
  const existing = sidebar.querySelector('.msg-req-block');
  if(!reqs.length){ if(existing) existing.remove(); return; }
  const html = `<div class="msg-req-block">
    <div class="msg-req-title">Message Requests (${reqs.length})</div>
    ${reqs.map(q=>`<div class="msg-req-item">
      <div class="msg-thread-info">
        <div class="msg-thread-name">${escHtml(q.from||q.title||'Someone')}</div>
        <div class="msg-thread-preview">${escHtml(q.last_message||'No message')}</div>
      </div>
      <div class="msg-req-actions">
        <button class="btn btn-sm" onclick="acceptOfficerRequest(${q.id})">Accept</button>
        <button class="btn btn-sm btn-danger" onclick="rejectOfficerRequest(${q.id})">Reject</button>
      </div>
    </div>`).join('')}
  </div>`;
  if(existing) existing.outerHTML = html; else sidebar.insertAdjacentHTML('afterbegin', html);
}

function connectSSE(){
  if(_rtWsReady) return; // WebSocket is live — SSE is only the fallback
  if(document.hidden) { setTimeout(connectSSE, 2000); return; }

  // Pick up latest IDs from current data (set by loadOfficerData)
  _sseAnnId   = lastAnnouncementId;
  _sseIssueId = officerIssues.length ? officerIssues[0].id : 0;

  const url = `/api/stream/updates?lastEventId=${_sseEventId}&lastAnnId=${_sseAnnId}&lastIssueId=${_sseIssueId}`;
  const es  = new EventSource(url);
  _rtSse = es;

  // ── Fresh connection — load initial data once ─────────────
  es.addEventListener('sync', () => {
    if(!officerIssues || !officerIssues.length){
      if(typeof loadOfficerData === 'function') loadOfficerData().catch(()=>{});
    }
  });

  // ── Announcement pushed ──────────────────────────────────
  es.addEventListener('announcement', async (e) => {
    const evt = JSON.parse(e.data);
    _sseAnnId = evt.latestId;
    try {
      const data = await safeGetArray('/api/announcements');
      if(!Array.isArray(data)||!data.length) return;
      const newOnes = data.filter(a => a.id > lastAnnouncementId);
      lastAnnouncementId = data[0].id;
      adminAnnouncements = data;
      myAnnouncements    = data.filter(a => a.posted_by && a.posted_by === window.__OFFICER.name);
      renderOverviewAnn();
      renderMyAnn();
      if(newOnes.length) showAnnBanner(newOnes[0]);
    } catch(err){}
  });

  // ── Issue pushed ─────────────────────────────────────────
  es.addEventListener('issue', async (e) => {
    const evt = JSON.parse(e.data);
    _sseIssueId = evt.latestId;
    try {
      const data = await safeGetArray('/api/issues');
      if(!Array.isArray(data)) return;
      const prevCount = officerIssues.length;
      officerIssues   = data;
      renderOfficerIssues();
      if(evt.isNew && prevCount > 0){
        showToast(`🆕 New issue: "${evt.title}" — ${evt.priority||''}`);
      }
    } catch(err){}
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
      if(typeof loadOfficerData === 'function') await loadOfficerData();
    }catch(err){}
  });

  // ── Block partition / boundary update (live on map) ──────
  es.addEventListener('block', () => {
    try{
      if(typeof loadOfficerBlocks === 'function') loadOfficerBlocks(true);
    }catch(err){}
  });

  // ── Community events ─────────────────────────────────────
  es.addEventListener('event', () => {
    if(typeof loadOfficerData === 'function') loadOfficerData().catch(()=>{});
    if(document.getElementById('panel-calendar')?.classList.contains('active')) loadOfficerEvents().catch(()=>{});
    document.dispatchEvent(new Event('subsync:refresh-alerts'));
  });
  es.addEventListener('rsvp', () => {
    if(document.getElementById('panel-calendar')?.classList.contains('active')) loadOfficerEvents().catch(()=>{});
  });

  // ── Officer expense claims ───────────────────────────────
  es.addEventListener('expense-request', () => {
    if(typeof loadOfficerExpenseRequests === 'function') loadOfficerExpenseRequests();
    document.dispatchEvent(new Event('subsync:refresh-alerts'));
  });

  // ── Map updates ──────────────────────────────────────────
  es.addEventListener('facility', () => {
    if(typeof loadOfficerData === 'function') loadOfficerData().catch(()=>{});
  });
  es.addEventListener('map-setting', () => {
    if(typeof loadOfficerBlocks === 'function') loadOfficerBlocks(true);
  });

  // ── Server asks client to reconnect ──────────────────────
  es.addEventListener('reconnect', (e) => {
    const evt = JSON.parse(e.data);
    if(evt.lastEventId) _sseEventId = evt.lastEventId;
    es.close();
    _rtSse = null;
    if(_rtWsReady) return; // WS came up while this cycle ran — stop the SSE loop
    // Refresh on every cycle so updates still land through buffering tunnels (e.g. ngrok)
    if(typeof loadOfficerData === 'function') loadOfficerData().catch(()=>{});
    setTimeout(connectSSE, 200);  // reconnect almost instantly
  });

  // ── Connection error — retry after 3 s ──────────────────
  es.onerror = () => {
    es.close();
    _rtSse = null;
    if(!_rtWsReady) setTimeout(connectSSE, 3000);
  };

  // Resume SSE when tab becomes visible again
  document.addEventListener('visibilitychange', () => {
    if(!document.hidden && es.readyState === EventSource.CLOSED && !_rtWsReady){
      setTimeout(connectSSE, 500);
    }
  }, { once: true });
}

// Start SSE after initial data load so we have baseline IDs
document.addEventListener('DOMContentLoaded', () => setTimeout(connectSSE, 1500));

// ── Safety-net: light poll every 60 s in case SSE is blocked by a proxy
setInterval(async()=>{
  if(document.hidden) return;
  try{
    const [anns, isss] = await Promise.all([
      safeGetArray('/api/announcements'),
      safeGetArray('/api/issues'),
    ]);
    if(Array.isArray(anns) && anns.length && anns[0].id > lastAnnouncementId){
      const newOnes = anns.filter(a => a.id > lastAnnouncementId);
      lastAnnouncementId = anns[0].id; _sseAnnId = anns[0].id;
      adminAnnouncements = anns;
      myAnnouncements    = anns.filter(a => a.posted_by && a.posted_by === window.__OFFICER.name);
      renderOverviewAnn(); renderMyAnn();
      if(newOnes.length) showAnnBanner(newOnes[0]);
    }
    if(Array.isArray(isss)){ officerIssues = isss; _sseIssueId = isss[0]?.id||0; renderOfficerIssues(); }
  }catch(e){}
}, 60000);

// Fallback poll for the active thread (WebSocket pushes are the instant path)
setInterval(()=>{
  if(document.hidden) return;
  if(document.getElementById('panel-messages')?.classList.contains('active')) refreshOfficerMessages();
},10000);

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
    }
  }catch(e){}
  renderSentFiles();
})();

/* ══ REVERB (WebSocket) real-time — primary channel; SSE only as fallback ══ */
(function initSubSyncRealtime(attempt){
  attempt = attempt || 0;
  if(!window.Echo){
    if(attempt < 20){ setTimeout(function(){ initSubSyncRealtime(attempt + 1); }, Math.min(300 * (attempt + 1), 3000)); }
    return;
  }
  try {
    const ch = window.Echo.private('subsync');
    const refresh = () => {
      if(typeof loadOfficerData === 'function') loadOfficerData().catch(function(){});
      if(document.getElementById('panel-calendar')?.classList.contains('active')) loadOfficerEvents().catch(function(){});
      document.dispatchEvent(new Event('subsync:refresh-alerts'));
    };
    ch.listen('.announcement', refresh).listen('.issue', refresh).listen('.policy', refresh).listen('.hoa-expense', refresh).listen('.block', () => { if(typeof loadOfficerBlocks === 'function') loadOfficerBlocks(true); });
    ch.listen('.event', refresh).listen('.rsvp', refresh);
    ch.listen('.expense-request', () => {
      if(typeof loadOfficerExpenseRequests === 'function') loadOfficerExpenseRequests();
      document.dispatchEvent(new Event('subsync:refresh-alerts'));
    });
    ch.listen('.facility', () => {
      if(typeof loadOfficerData === 'function') loadOfficerData().catch(()=>{});
    });
    ch.listen('.map-setting', () => {
      if(typeof loadOfficerBlocks === 'function') loadOfficerBlocks(true);
    });
    ch.listen('.message', (evt) => {
      if(!evt || !evt.conversationId) return;
      const isOpen = String(evt.conversationId) === String(currentThread);
      const panelOpen = document.getElementById('panel-messages')?.classList.contains('active');
      const mine = evt.senderType === 'officer' && Number(evt.senderId) === Number(OFFICER_ID);
      if(mine){ if(isOpen) refreshOfficerMessages(); return; }
      if(isOpen && panelOpen){ refreshOfficerMessages(); return; }
      const _tName=(threads[evt.conversationId]&&threads[evt.conversationId].title)||'';
      showToast(_tName ? 'New message from ' + _tName + '.' : 'New message.');
      const badge = document.getElementById('badge-messages');
      if(badge){ badge.textContent = (parseInt(badge.textContent,10)||0) + 1; badge.style.display = 'inline-flex'; }
      refreshOfficerRequestBlock();
      if(isOpen) refreshOfficerMessages();
    });

    // WebSocket-first: while Reverb is connected the SSE fallback stays off;
    // if the socket drops, bring the fallback back.
    const conn = window.Echo.connector && window.Echo.connector.pusher && window.Echo.connector.pusher.connection;
    if(conn){
      const wsUp = () => { _rtWsReady = true; if(_rtSse){ try{ _rtSse.close(); }catch(e){} _rtSse = null; } };
      const wsDown = () => { if(_rtWsReady){ _rtWsReady = false; connectSSE(); } };
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
    try{ if(typeof officerMonitorMapInst!=='undefined' && officerMonitorMapInst) officerMonitorMapInst.invalidateSize(); }catch(e){}
    try{ if(typeof officerMapInst!=='undefined' && officerMapInst) officerMapInst.invalidateSize(); }catch(e){}
  }, 200);
});
window.addEventListener('orientationchange', function(){ setTimeout(function(){ try{ if(officerMonitorMapInst) officerMonitorMapInst.invalidateSize(); }catch(e){} }, 300); });

/* Allows the shared global search to navigate panels by name */
window.PortalNav = { go: function (name) { switchTab(name); } };

/* ══ TAB PERSISTENCE ACROSS REFRESH ══ */
function normalizeOfficerTab(target){
  if(!target) return '';
  const aliases = {
    'mapping': 'map',
    'issues': 'reports',
    'documents': 'files'
  };
  return aliases[target] || target;
}
function restoreOfficerTab(){
  try {
    const rawHash = (window.location.hash || '').replace(/^#/, '').trim().toLowerCase();
    const saved = sessionStorage.getItem('subsync_active_tab_officer');
    let target = normalizeOfficerTab(rawHash || saved);
    if(target && document.getElementById('panel-' + target)){
      switchTab(target);
      if(target === 'map'){
        setTimeout(function(){
          if(typeof initOfficerMonitorMap === 'function') initOfficerMonitorMap();
        }, 120);
      }
    }
  } catch(e) {}
}
restoreOfficerTab();
if(document.readyState === 'loading'){
  document.addEventListener('DOMContentLoaded', restoreOfficerTab);
}
window.addEventListener('hashchange', function(){
  try {
    const hash = (window.location.hash || '').replace(/^#/, '').trim().toLowerCase();
    let target = normalizeOfficerTab(hash);
    if(target && document.getElementById('panel-' + target)){
      switchTab(target);
    }
  } catch(e) {}
});
