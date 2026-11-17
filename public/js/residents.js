/* ══ UTILS ══ */
/* ── Theme (Light / Dark) ── */
function toggleTheme(){
  const cur = document.documentElement.getAttribute('data-theme') || 'light';
  const next = cur === 'dark' ? 'light' : 'dark';
  setResidentTheme(next);
}
function setResidentTheme(theme){
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
        sessionStorage.removeItem('subsync_active_tab_resident');
        sessionStorage.removeItem('subsync_pay_tab_resident');
      } catch(e) {}
      document.getElementById('logoutForm').submit();
    }
  });
}
function changeImage(id,inp){
  const f=inp.files[0]; if(!f) return;
  const r=new FileReader();
  r.onload=e=>document.getElementById(id).src=e.target.result;
  r.readAsDataURL(f);
  if(id==='profilePreview'){
    const fd=new FormData(); fd.append('avatar', f);
    fetch('/api/residents/avatar',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},body:fd})
      .then(x=>x.json())
      .then(d=>{ if(d.success) showToast('✅ Profile photo updated.'); else showToast('⚠ Could not save photo.'); })
      .catch(()=>showToast('⚠ Could not save photo.'));
  }
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
  const panel = document.getElementById('panel-'+name);
  const tab = document.getElementById('tab-'+name);
  if(panel) panel.classList.add('active');
  if(tab) tab.classList.add('active');
  try {
    sessionStorage.setItem('subsync_active_tab_resident', name);
    if(window.location.hash !== '#' + name){
      history.replaceState(null, '', '#' + name);
    }
  } catch(e) {}
  if(name==='messages'){
    const b=document.getElementById('badge-messages');
    if(b) b.style.display='none';
    loadResidentThreads();
  }
  if(name==='map') setTimeout(function(){ if(typeof window.initResidentMap==='function') window.initResidentMap(); else if(typeof initResidentMap==='function') initResidentMap(); }, 80);
  if(name==='payments') loadPayments();
  if(name==='calendar') loadEvents();
  if(name==='issues'){ if(typeof loadIssues==='function') loadIssues(); }
  if(name==='expenses'){ if(typeof loadHOAExpenses==='function') loadHOAExpenses(); }
  if(name==='recs'){ if(typeof loadRecs==='function') loadRecs(); }
  if(name==='policies'){ if(typeof loadHOAPolicies==='function') loadHOAPolicies(); }
  
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
      <button class="member-remove" onclick="removeMember(${i})" aria-label="Remove member">×</button>
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
  if(!(await SubUI.confirm({ title:'Remove member?', message:`Remove "${m.name}" from your household?`, confirmText:'Remove', danger:true, icon:'!' }))) return;
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
  const notif = document.getElementById('annNotif');
  const title = document.getElementById('annNotifTitle');
  if(!notif || !ann) return;
  if(title) title.textContent = ann.title;
  notif.classList.add('show');
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

  // Fallback poll for the active thread (WebSocket pushes are the instant path)
  setInterval(()=>{
    if(document.hidden) return;
    if(document.getElementById('panel-messages')?.classList.contains('active')) refreshResidentMessages();
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
    document.getElementById('annV-meta').innerHTML = `<span>${escHtml(ann.created_at)}</span>${ann.target && ann.target !== 'All Residents' ? `<span> • ${escHtml(ann.target)}</span>` : ''}${ann.event_date ? `<span> • ${escHtml(ann.event_date)}</span>` : ''}`;
    document.getElementById('annV-content').textContent = ann.content;
    fetch('/api/announcements/'+id+'/view',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()}}).then(()=>{document.dispatchEvent(new Event('subsync:refresh-alerts'));}).catch(()=>{});
    document.getElementById('modal-annView').classList.add('open');
  }
}

let _annShowAll=false;
function annItem(a){
  const tagLabel={notice:'Notice',urgent:'Urgent',event:'Event'};
  const meta=[];
  if(a.priority==='High') meta.push('<span style="color:#f08080;font-weight:700;font-size:12.5px;">⚡ High Priority</span>');
  if(a.target&&a.target!=='All Residents') meta.push('<span style="font-size:12.5px;color:var(--text-mid);">👥 '+escHtml(a.target)+'</span>');
  if(a.event_date) meta.push('<span style="font-size:12.5px;color:var(--text-mid);display:inline-flex;align-items:center;gap:4px;">'+SubUI.icon('calendar',13)+' '+escHtml(a.event_date)+'</span>');
  const statusClass = a.status === 'Archived' ? 'pill-inactive' : 'pill-active';
  meta.push('<span class="pill '+statusClass+'" style="font-size:12px;">'+a.status+'</span>');
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
  if(!announcements.length){ el.innerHTML='<div class="empty-state">' + SubUI.icon('megaphone', 28) + 'No announcements yet.</div>'; return; }
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
        <button class="btn btn-sm" onclick="acceptResRequest(${q.id})">Accept</button>
        <button class="btn btn-sm btn-danger" onclick="rejectResRequest(${q.id})">Reject</button>
      </div>
    </div>`).join('')}
  </div>`:'';
  const list=Array.isArray(data)?data:[];
  list.forEach(t=>{threads[t.id]={...t,messages:[]};});
  const sortedData=[...list].sort((a,b)=>{const g=x=>x.title==='Community Chat';return g(a)&&!g(b)?-1:(!g(a)&&g(b)?1:0);});
  const threadsHtml=sortedData.length?sortedData.map((t,i)=>`<div class="msg-thread-item${i===0?' active':''}" onclick="selectResThread(${t.id},this)">
        <div class="msg-thread-avatar ta-admin">${escHtml((t.title||'?').substring(0,2).toUpperCase())}</div>
        <div class="msg-thread-info">
          <div class="msg-thread-name">${escHtml(t.title)}</div>
          <div class="msg-thread-preview">${escHtml(t.last_message||'No messages yet')}</div>
        </div>
      <button class="msg-thread-del" aria-label="Delete conversation" onclick="event.stopPropagation();deleteResidentConversation(${t.id})" title="Delete">&#10005;</button>
    </div>`).join(''):'<div style="color:var(--text-dim);font-size:12px;padding:12px;">No conversations yet.<br><button class="btn btn-sm" style="margin-top:8px;" onclick="openNewConvModal()">Start one</button></div>';
  if(sidebar) sidebar.innerHTML=reqHtml+threadsHtml;
  if(sortedData.length){
    currentThread=sortedData[0].id;
    document.getElementById('chat-name').textContent=sortedData[0].title||'Admin';
    document.getElementById('chat-avatar').textContent=(sortedData[0].title||'SA').substring(0,2).toUpperCase();
    const msgs=await fetch('/api/messages/'+currentThread,{headers:{'Accept':'application/json'}}).then(r=>r.json()).catch(()=>[]);
    threads[currentThread].messages=msgs;
    renderMessages();
  }
}
async function acceptResRequest(id){
  const csrf=document.querySelector('meta[name=csrf-token]')?.content||'';
  const res=await fetch('/api/messages/'+id+'/accept',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}}).then(r=>r.json()).catch(()=>({success:false}));
  if(res&&res.success){ showToast('Message request accepted.'); await loadResidentThreads(); }
  else { showToast('Could not accept the request.'); }
}
async function rejectResRequest(id){
  if(!(await SubUI.confirm({title:'Reject message request?',message:'This permanently deletes the conversation for both of you.',confirmText:'Reject',danger:true,icon:'!'}))) return;
  const csrf=document.querySelector('meta[name=csrf-token]')?.content||'';
  const res=await fetch('/api/messages/'+id+'/reject',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}}).then(r=>r.json()).catch(()=>({success:false}));
  if(res&&res.success){ showToast('Message request rejected.'); await loadResidentThreads(); }
  else { showToast('Could not reject the request.'); }
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
  if(!(await SubUI.confirm({ title:'Delete conversation?', message:'This deletes the conversation and all its messages.', confirmText:'Delete', danger:true, icon:'!' }))) return;
    const res=await fetch('/api/messages/'+id,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()}}).then(r=>r.json()).catch(()=>({success:false}));
  if(res.success){
    delete threads[id];
    if(currentThread==id){
      currentThread=null;
      const remaining=Object.keys(threads);
      if(remaining.length){
        currentThread=remaining[0];
        await loadResidentThreads();
      } else {
        document.getElementById('msgBody').innerHTML='<div style="color:var(--text-dim);text-align:center;padding:20px;font-size:13px;">No conversations.</div>';
        document.getElementById('chat-name').textContent='';
      }
    } else {
      await loadResidentThreads();
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
  // Show the modal immediately so the button always works…
  const el=document.getElementById('modal-newConv');
  if(el) el.style.display='flex';
  // …then best-effort populate the officer list (a failed lookup must not block it).
  const sel=document.getElementById('newConvRecipient');
  if(sel){
    let officers=[];
    try{
      const raw=await fetch('/api/messages/officers').then(r=>r.json());
      officers=Array.isArray(raw)?raw:[];
    }catch(e){ officers=[]; }
    sel.innerHTML='<option value="admin|0">🏢 HOA Admin</option>'+
      officers.map(o=>`<option value="officer|${o.id}">🛡️ ${o.name} (Officer)</option>`).join('');
  }
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
  return `<span style="font-size:10px;font-weight:600;color:var(--text-dim);margin-left:6px;" title="Auto-deletes after 30 days">${SubUI.icon('trash',11)} deletes in ${daysLeft}d</span>`;
}

async function loadIssues(){
  issues = await fetch('/api/issues/my').then(r=>r.json());
  renderIssues();
}
function renderIssues(){
  const el=document.getElementById('issueList');
  if(!issues.length){
    el.innerHTML='<div class="empty-state">' + SubUI.icon('clipboard', 28) + 'No reports submitted yet.</div>';
    return;
  }
  const statusPill={Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'};
  const priStyle={Critical:'background:rgba(220,30,30,0.18);color:#ff5555;border:1px solid rgba(220,30,30,0.4);',High:'background:rgba(240,128,128,0.13);color:#f08080;border:1px solid rgba(240,128,128,0.3);',Medium:'background:rgba(245,166,35,0.13);color:var(--yellow);border:1px solid rgba(245,166,35,0.3);',Low:'background:rgba(76,175,80,0.13);color:var(--green);border:1px solid rgba(76,175,80,0.3);'};
  el.innerHTML=issues.map(i=>{
    const priLabel={Critical:'🚨 Critical',High:'🔴 High',Medium:'🟡 Medium',Low:'🟢 Low'}[i.priority]||'';
    const priSpan=i.priority?`<span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;${priStyle[i.priority]||''}">${priLabel}</span><span style="font-size:10px;color:var(--text-dim);margin-left:2px;">assigned</span>`:'';
    const isCritical=i.priority==='Critical';
      // Photo + document gallery
      const mediaItems=(i.media||[]);
      const gallery=mediaItems.length?`<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;align-items:center;">${mediaItems.map(m=>{
        const isImg=/\.(png|jpe?g|gif|webp)$/i.test(m.name||m.url||'');
        return isImg
          ?`<a href="${escHtml(m.url)}" target="_blank"><img src="${escHtml(m.url)}" style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid rgba(255,255,255,0.1);" loading="lazy"></a>`
          :`<a href="${escHtml(m.url)}" target="_blank" style="display:inline-flex;align-items:center;gap:5px;font-size:12px;color:var(--accent);text-decoration:underline;">${SubUI.icon('paperclip',12)} ${escHtml(m.name)}</a>`;
      }).join('')}</div>`:'';
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
        ${i.assigned_officer?`<span class="pill pill-progress" title="Assigned officer">${SubUI.icon('user',12)} ${escHtml(i.assigned_officer)}</span>`:''}
        ${i.due_date?`<span class="pill ${i.overdue?'pill-urgent':'pill-notice'}" title="Expected resolution">${i.overdue?'Overdue ':'ETA '}${escHtml(i.due_date)}</span>`:''}
        ${Number(i.repair_cost||0) > 0 ? `<span class="pill pill-resolved" title="Verified repair cost recorded in HOA Expenses">${SubUI.icon('coins',12)} Repair Cost: ₱${Number(i.repair_cost).toLocaleString(undefined,{minimumFractionDigits:2})}</span>` : ''}
        <span class="meta-txt">· #${i.id} · Filed ${i.created_at}</span>
      </div>
      ${i.response?`
        <div class="issue-response">
          <div class="issue-response-label">Admin Response</div>
          <div class="issue-response-text">${i.response}</div>
        </div>`:''}
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
          <button class="btn btn-sm" onclick="openTimeline(${i.id})">View timeline</button>
          ${i.status!=='Resolved'?`<button class="btn btn-sm" onclick="openAddDetails(${i.id})">${SubUI.icon('paperclip',13)} Add details</button>`:''}
        </div>
      </div>`}).join('');
}
function statusPillHtml(s){return {Pending:'<span class="pill pill-pending">Pending</span>','In Progress':'<span class="pill pill-progress">In Progress</span>',Resolved:'<span class="pill pill-resolved">Resolved</span>'}[s]||'';}
async function openTimeline(id){
  const data=await fetch('/api/issues/my/'+id+'/timeline').then(r=>r.json()).catch(()=>null);
  if(!data||!data.issue){showToast('⚠ Could not load timeline.');return;}
  const it=data.issue;
  document.getElementById('timelineTitle').textContent=it.title||'Report Timeline';
  document.getElementById('timelineMeta').innerHTML=
    statusPillHtml(it.status)
    +(it.priority?` <span style="font-size:11px;color:var(--text-dim);">· Priority ${escHtml(it.priority)}</span>`:'')
    +(it.assigned_officer?`<div style="margin-top:6px;">${SubUI.icon('user',12)} Assigned to <strong>${escHtml(it.assigned_officer)}</strong></div>`:'')
    +(it.due_date?`<div style="margin-top:4px;">${it.overdue?'<span style="color:#f08080;">' + SubUI.icon('clock',12) + ' Overdue</span>':SubUI.icon('calendar',12) + ' Expected by'} <strong>${escHtml(it.due_date)}</strong></div>`:'');
    const labels={status:'Status changed',priority:'Priority set',assign:'Assigned officer',due_date:'Due date set',archive:'Archive state',details:'You added details',attachments:'You added files'};
  const tl=(data.timeline||[]);
  document.getElementById('timelineList').innerHTML=tl.length?tl.map(t=>{
    const txt=labels[t.action]||t.action;
    const detail=t.action==='status'?`${escHtml(t.old||'—')} → <strong>${escHtml(t.new||'—')}</strong>`:(t.new?`<strong>${escHtml(t.new)}</strong>`:'');
    return `<div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--glass-border);">
      <span style="width:8px;height:8px;border-radius:50%;background:var(--accent);margin-top:6px;flex-shrink:0;"></span>
      <div><div style="font-size:13px;font-weight:600;">${txt}</div>${detail?`<div style="font-size:12px;color:var(--text-mid);">${detail}</div>`:''}<div style="font-size:11px;color:var(--text-dim);margin-top:2px;">${escHtml(t.at||'')} · ${escHtml(t.actor||'System')}</div></div>
    </div>`;
  }).join(''):'<div style="font-size:13px;color:var(--text-dim);padding:8px 0;">No changes yet — your report is with the HOA team.</div>';
  document.getElementById('modal-timeline').classList.add('open');
}
  /* ── Append details/photos to an open report (audit-trail safe) ── */
  let _addDetailsId=null;
  function openAddDetails(id){
    const i=(issues||[]).find(x=>String(x.id)===String(id));
    if(!i) return;
    _addDetailsId=id;
    document.getElementById('addDetailsTitle').textContent='Add Details to Report #'+id;
    document.getElementById('addDetailsMeta').textContent=i.title||'';
    document.getElementById('addDetailsText').value='';
      document.getElementById('addDetailsImages').value='';
      document.getElementById('addDetailsDocs').value='';
      document.getElementById('addDetailsMsg').textContent='';
      refreshAddDetailsChips();
      document.getElementById('modal-addDetails').classList.add('open');
  }
  async function submitAddDetails(){
    if(!_addDetailsId) return;
    const txt=document.getElementById('addDetailsText').value.trim();
    const imgs=document.getElementById('addDetailsImages').files;
    const docs=document.getElementById('addDetailsDocs').files;
    const msg=document.getElementById('addDetailsMsg');
    if(!txt && !imgs.length && !docs.length){ msg.textContent='Add a note or attach a photo/document first.'; return; }
    if(imgs.length>5 || docs.length>5){ msg.textContent='Up to 5 photos and 5 documents per update.'; return; }
    const fd=new FormData();
    if(txt) fd.append('details',txt);
    Array.from(imgs).forEach(f=>fd.append('images[]',f));
    Array.from(docs).forEach(f=>fd.append('documents[]',f));
    try{
      const res=await fetch('/api/issues/'+_addDetailsId+'/details',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:fd}).then(r=>r.json());
        if(res&&res.success){
          document.getElementById('modal-addDetails').classList.remove('open');
          document.getElementById('addDetailsImages').value='';
          document.getElementById('addDetailsDocs').value='';
          refreshAddDetailsChips();
          showToast('✅ Details added to your report.');
          loadIssues();
      } else {
        msg.textContent=(res&&res.message)||'Could not save. Please try again.';
      }
    }catch(e){ msg.textContent='Could not save. Please try again.'; }
  }

  /* First human-readable error from a Laravel JSON error response */
  function errMsg(data,fallback){
  if(data&&data.errors){ const keys=Object.keys(data.errors); if(keys.length&&Array.isArray(data.errors[keys[0]])&&data.errors[keys[0]][0]) return data.errors[keys[0]][0]; }
  return (data&&data.message)||fallback;
}
  /* ── Selected-file chips: remove one file so it can be replaced ── */
  function _fileChip(name, onRemove, camera){
    const chip=document.createElement('span');
    chip.className='file-chip';
    const label=document.createElement('span');
    label.textContent=(camera?'📷 ':'')+name;
    const x=document.createElement('button');
    x.type='button';
    x.title='Remove '+name;
    x.setAttribute('aria-label','Remove '+name);
    x.textContent='\u00d7';
    x.addEventListener('click', onRemove);
    chip.append(label, x);
    return chip;
  }
  function _setInputFiles(input, files){
    try {
      const dt=new DataTransfer();
      files.forEach(f=>dt.items.add(f));
      input.files=dt.files;
    } catch(e){ /* very old browsers: fall back to clearing everything */
      input.value='';
    }
  }
  function _removeFromInput(input, index){
    const arr=Array.from(input.files);
    arr.splice(index,1);
    _setInputFiles(input, arr);
  }
  function refreshIssueFileChips(){
    const imgInput=document.getElementById('issueImages');
    const docInput=document.getElementById('issueDocuments');
    const imgBox=document.getElementById('issueImageChips');
    const docBox=document.getElementById('issueDocChips');

    if(imgBox && imgInput){
      imgBox.innerHTML='';
      Array.from(imgInput.files).forEach((f,i)=>imgBox.append(
        _fileChip(f.name, ()=>{ _removeFromInput(imgInput,i); refreshIssueFileChips(); })
      ));
      (window._cameraIssueImages||[]).forEach((f,i)=>imgBox.append(
        _fileChip(f.name, ()=>{
          window._cameraIssueImages.splice(i,1);
          const c=document.getElementById('issueCameraCount');
          const n=window._cameraIssueImages.length;
          if(c) c.textContent=n?(n+' camera photo'+(n>1?'s':'')+' added'):'';
          refreshIssueFileChips();
        }, true)
      ));
    }

    if(docBox && docInput){
      docBox.innerHTML='';
      Array.from(docInput.files).forEach((f,i)=>docBox.append(
        _fileChip(f.name, ()=>{ _removeFromInput(docInput,i); refreshIssueFileChips(); })
      ));
    }
  }
  function refreshAddDetailsChips(){
    [['addDetailsImages','addDetailsImageChips'],['addDetailsDocs','addDetailsDocChips']].forEach(([inputId, boxId])=>{
      const input=document.getElementById(inputId);
      const box=document.getElementById(boxId);
      if(!input||!box) return;
      box.innerHTML='';
      Array.from(input.files).forEach((f,i)=>box.append(
        _fileChip(f.name, ()=>{ _removeFromInput(input,i); refreshAddDetailsChips(); })
      ));
    });
  }
  ['issueImages','issueDocuments'].forEach(id=>{
    const el=document.getElementById(id);
    if(el) el.addEventListener('change', refreshIssueFileChips);
  });
  ['addDetailsImages','addDetailsDocs'].forEach(id=>{
    const el=document.getElementById(id);
    if(el) el.addEventListener('change', refreshAddDetailsChips);
  });

  async function submitIssue(){
    const category=document.getElementById('issueCategory').value;
  const title=document.getElementById('issueTitle').value.trim();
  const description=document.getElementById('issueBody').value.trim();
    const latitude=document.getElementById('issue-lat').value||null;
    const longitude=document.getElementById('issue-lng').value||null;
    if(latitude&&longitude&&typeof mapSettings!=='undefined'&&mapSettings&&(
      parseFloat(latitude)<mapSettings.min_lat||parseFloat(latitude)>mapSettings.max_lat||
      parseFloat(longitude)<mapSettings.min_lng||parseFloat(longitude)>mapSettings.max_lng)){
      showToast('⚠ The pin must be inside the Terra Nova Subdivision boundary. Drag it back or clear it.');
      return;
    }
  if(!SubUI.required([[category,'issueCategory','Please choose a category.'],[title,'issueTitle','Please enter a title.'],[description,'issueBody','Please describe the concern.']])) return;
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
  const camImgs=window._cameraIssueImages||[];
  if(camImgs.length){
    const room=Math.max(0,5-(imgInput?imgInput.files.length:0));
    camImgs.slice(0,room).forEach(f=>fd.append('images[]',f));
  }
  const docInput=document.getElementById('issueDocuments');
  if(docInput&&docInput.files.length){
    Array.from(docInput.files).slice(0,5).forEach(f=>fd.append('documents[]',f));
  }
  const res=await fetch('/api/issues',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:fd});
  const data=await res.json();
  if(data.success){
    ['issueCategory','issueTitle','issueBody','issue-lat','issue-lng'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
    if(imgInput) imgInput.value='';
    if(docInput) docInput.value='';
      window._cameraIssueImages=[];
      const camCount=document.getElementById('issueCameraCount');
      if(camCount) camCount.textContent='';
      refreshIssueFileChips();
    if(typeof toggleEmergencyNote==='function') toggleEmergencyNote();
    const disp=document.getElementById('issue-loc-display');
    if(disp) disp.textContent='No location set';
    closeIssuePicker();
    await loadIssues();
  loadHOAExpenses();
  loadHOAPolicies();
    showToast('✅ Report submitted. The admin will review it shortly.');
  } else {
    showToast('⚠ '+errMsg(data,'Failed to submit report. Please try again.'));
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

/* ═══════════ PAYMENTS (GCash) ═══════════ */
let myPayments = [];
let gcashPayInfo = null;
let currentHistoryFilter = 'all';

function payCsrf(){ return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''; }

function formatPayDate(dStr, withTime = false){
  if(!dStr) return '—';
  try {
    const d = new Date(dStr);
    if(isNaN(d.getTime())) return dStr;
    const opts = { year: 'numeric', month: 'short', day: '2-digit' };
    if(withTime){
      opts.hour = '2-digit';
      opts.minute = '2-digit';
      opts.hour12 = true;
    }
    const formatted = d.toLocaleDateString('en-US', opts);
    if(withTime){
      return formatted.replace(/,\s(?=\d{1,2}:\d{2})/, ' · ');
    }
    return formatted;
  } catch(e){
    return dStr;
  }
}

function payStatusBadge(status){
  const st = (status || '').toLowerCase();
  if(st === 'validated'){
    return '<span class="pay-status-pill validated"><span class="status-dot"></span> Validated</span>';
  }
  if(st === 'pending' || st === 'under review'){
    return '<span class="pay-status-pill under-review"><span class="status-dot"></span> Under Review</span>';
  }
  if(st === 'rejected'){
    return '<span class="pay-status-pill rejected"><span class="status-dot"></span> Rejected</span>';
  }
  if(st === 'needs info'){
    return '<span class="pay-status-pill needs-info"><span class="status-dot"></span> Needs Info</span>';
  }
  return `<span class="pay-status-pill under-review">${escHtml(status || 'Submitted')}</span>`;
}

async function loadPayments(){
  loadGcashPayInfo();
  loadStatement();
  loadTransparency();
  await loadMyPayments();
  const senderInput = document.getElementById('paySender');
  const dateInput = document.getElementById('payDate');
  const amtInput = document.getElementById('payAmount');
  if(senderInput && !senderInput.value) senderInput.value = window.__RESIDENT?.name || RESIDENT_NAME || '';
  if(dateInput && !dateInput.value) dateInput.value = new Date().toISOString().split('T')[0];
  if(amtInput && !amtInput.value && Number(window.__RESIDENT?.balance || 0) > 0){
    amtInput.value = Number(window.__RESIDENT.balance).toFixed(2);
  }
  bindReceiptPasteAndDrop();
  try {
    const savedPayTab = sessionStorage.getItem('subsync_pay_tab_resident');
    if(savedPayTab && typeof payTab === 'function') payTab(savedPayTab);
  } catch(e) {}
}
window.loadPayments = loadPayments;

function quickPay(amount){
  switchTab('payments');
  payTab('pay');
  const bal = amount != null ? Number(amount) : Number(window.__RESIDENT?.balance || 0);
  const amtInput = document.getElementById('payAmount');
  const senderInput = document.getElementById('paySender');
  const dateInput = document.getElementById('payDate');
  if(amtInput && bal > 0) amtInput.value = bal.toFixed(2);
  if(senderInput && !senderInput.value) senderInput.value = window.__RESIDENT?.name || RESIDENT_NAME || '';
  if(dateInput && !dateInput.value) dateInput.value = new Date().toISOString().split('T')[0];
  const formCard = document.getElementById('payFormCard') || amtInput;
  if(formCard) formCard.scrollIntoView({behavior:'smooth', block:'center'});
}
window.quickPay = quickPay;

function payTab(name, el){
  document.querySelectorAll('#payTabs .fin-tab').forEach(t=>t.classList.remove('active'));
  if(el) el.classList.add('active');
  else {
    const btn = document.getElementById('payTabBtn-' + name);
    if(btn) btn.classList.add('active');
    else {
      document.querySelectorAll('#payTabs .fin-tab').forEach(t=>{
        if(t.textContent.trim().toLowerCase().includes(name.toLowerCase())) t.classList.add('active');
      });
    }
  }
  document.querySelectorAll('#panel-payments .fin-section').forEach(s=>s.classList.toggle('active', s.dataset.pay===name));
  try {
    sessionStorage.setItem('subsync_pay_tab_resident', name);
  } catch(e) {}
}
window.payTab = payTab;

async function loadStatement(){
  const tbl = document.getElementById('statementTable');
  let s = {balance:0,total_charges:0,total_payments:0,entries:[],aging:{buckets:{}}};
  try{ s = await fetch('/api/ledger/my',{headers:{'Accept':'application/json'}}).then(r=>r.json()); }catch(e){}

  const v = Number(s.balance || 0);
  const bal = document.getElementById('statementBalance');
  if(bal){
    bal.textContent = '₱' + Math.abs(v).toLocaleString(undefined,{minimumFractionDigits:2}) + (v < 0 ? ' CR' : '');
    bal.style.color = v < 0 ? 'var(--green,#6B8E70)' : 'var(--text)';
  }

  // Update Overview KPI Card 1: Outstanding Balance
  const kpiBal = document.getElementById('payKpiBalance');
  const kpiBalStatus = document.getElementById('payKpiBalanceStatus');
  if(kpiBal){
    kpiBal.textContent = (v <= 0) ? '₱0.00' : '₱' + v.toLocaleString(undefined, {minimumFractionDigits: 2});
  }
  if(kpiBalStatus){
    if(v <= 0){
      kpiBalStatus.innerHTML = `<span class="status-indicator green" style="display:inline-flex;align-items:center;gap:6px;color:#2E7D32;font-size:12px;font-weight:600;">${typeof SubUI !== 'undefined' ? SubUI.icon('check-circle', 13) : '✓'} No pending balance</span>`;
    } else {
      kpiBalStatus.innerHTML = `<span class="status-indicator red" style="display:inline-flex;align-items:center;gap:6px;color:#DC2626;font-size:12px;font-weight:600;">${typeof SubUI !== 'undefined' ? SubUI.icon('alert-circle', 13) : '!'} Payment required</span>`;
    }
  }

  // Auto-fill amount input if empty and there is an outstanding balance
  const amtInput = document.getElementById('payAmount');
  if(amtInput && !amtInput.value && v > 0){
    amtInput.value = v.toFixed(2);
  }

  const ag = document.getElementById('statementAging');
  if(ag){
    const b = (s.aging && s.aging.buckets) || {};
    const cell = (label,val) => `<div style="flex:1;min-width:88px;"><div style="font-size:12.5px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.04em;">${label}</div><div style="font-size:16px;font-weight:700;">₱${Number(val||0).toLocaleString(undefined,{minimumFractionDigits:2})}</div></div>`;
    ag.innerHTML = cell('Current',b.current)+cell('1–30d',b.d1_30)+cell('31–60d',b.d31_60)+cell('61–90d',b.d61_90)+cell('90d+',b.d90_plus);
  }

  if(!tbl) return;
  const entries = (s.entries || []);
  if(!entries.length){ tbl.innerHTML = '<div class="empty-state">No transactions yet.</div>'; return; }
  const peso = val => '₱' + Number(val||0).toLocaleString(undefined,{minimumFractionDigits:2});
  tbl.innerHTML = '<div style="overflow-x:auto;"><table style="width:100%;border-collapse:collapse;font-size:14px;">'+
    '<thead><tr><th style="text-align:left;padding:8px 10px;font-size:13px;">Date</th><th style="text-align:left;padding:8px 10px;font-size:13px;">Description</th><th style="text-align:right;padding:8px 10px;font-size:13px;">Charge</th><th style="text-align:right;padding:8px 10px;font-size:13px;">Payment</th><th style="text-align:right;padding:8px 10px;font-size:13px;">Balance</th></tr></thead><tbody>'+
    entries.map(e=>`<tr>
      <td style="padding:6px 8px;color:var(--text-dim);white-space:nowrap;">${escHtml(e.date||'')}</td>
      <td style="padding:6px 8px;">${escHtml(e.description||e.type)}${e.period?` <span style="color:var(--text-dim);font-size:11px;">· ${escHtml(e.period)}</span>`:''}</td>
      <td style="padding:6px 8px;text-align:right;">${e.charge?peso(e.charge):'—'}</td>
      <td style="padding:6px 8px;text-align:right;color:var(--green,#6B8E70);">${e.payment?peso(e.payment):'—'}</td>
      <td style="padding:6px 8px;text-align:right;font-weight:600;">${peso(e.balance)}</td>
    </tr>`).join('')+
    `</tbody><tfoot><tr style="border-top:1px solid var(--glass-border);">
      <td colspan="2" style="padding:6px 8px;font-weight:600;">Totals</td>
      <td style="padding:6px 8px;text-align:right;">${peso(s.total_charges)}</td>
      <td style="padding:6px 8px;text-align:right;">${peso(s.total_payments)}</td>
      <td style="padding:6px 8px;text-align:right;font-weight:700;">${peso(s.balance)}</td>
    </tr></tfoot></table></div>`;
}

async function loadGcashPayInfo(){
  try{ gcashPayInfo = await fetch('/api/gcash-settings',{headers:{'Accept':'application/json'}}).then(r=>r.json()); }catch(e){ gcashPayInfo = null; }
  const qr = document.getElementById('gcashQrBox');
  const det = document.getElementById('gcashPayDetails');
  if(!qr) return;

  if(gcashPayInfo && gcashPayInfo.qr_image_url){
    qr.innerHTML = `<img src="${gcashPayInfo.qr_image_url}" alt="GCash QR Code" style="max-width:160px;max-height:160px;display:block;border-radius:8px;">`;
  } else {
    qr.innerHTML = '<div style="font-size:12px;color:var(--text-dim);text-align:center;padding:16px;">No QR code available.<br>Please contact the HOA office.</div>';
  }

  if(det){
    const copyIcon = typeof SubUI !== 'undefined' ? SubUI.icon('copy', 12) : '';
    let html = '';
    if(gcashPayInfo && gcashPayInfo.account_name){
      html += `<div style="font-size:11.5px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Account Name</div>`;
      html += `<div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:8px;">${escHtml(gcashPayInfo.account_name)}</div>`;
    }
    if(gcashPayInfo && gcashPayInfo.account_number){
      html += `<div style="font-size:11.5px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em;font-weight:700;">GCash Mobile Number</div>`;
      html += `<div style="display:flex;align-items:center;gap:8px;font-size:16px;font-weight:700;color:#F58B1F;font-family:monospace;letter-spacing:0.04em;">` +
        `<span>${escHtml(gcashPayInfo.account_number)}</span>` +
        `<button type="button" class="pay-copy-chip" onclick="copyGcashNumber('${escHtml(gcashPayInfo.account_number)}')" title="Copy GCash number">` +
        `${copyIcon} Copy</button></div>`;
    }
    if(gcashPayInfo && gcashPayInfo.instructions){
      html += `<div style="margin-top:8px;font-size:12px;color:var(--text-dim);line-height:1.4;">${escHtml(gcashPayInfo.instructions)}</div>`;
    }
    if(!html){
      html = '<div style="font-size:12px;color:var(--text-dim);">Official GCash payment information not configured. Please contact the HOA office.</div>';
    }
    det.innerHTML = html;
  }
}

function copyGcashNumber(num){
  if(!num) return;
  if(navigator.clipboard && navigator.clipboard.writeText){
    navigator.clipboard.writeText(num).then(() => {
      showToast('📋 Copied GCash number to clipboard.');
    }).catch(() => {
      showToast('GCash number: ' + num);
    });
  } else {
    showToast('GCash number: ' + num);
  }
}
window.copyGcashNumber = copyGcashNumber;

function enlargeGcashQr(){
  const modal = document.getElementById('modal-gcashQrEnlarge');
  if(!modal) return;
  const img = document.getElementById('gcashQrEnlargeImg');
  const details = document.getElementById('gcashQrModalDetails');
  const dLink = document.getElementById('gcashQrDownloadLink');
  const qrUrl = gcashPayInfo?.qr_image_url || '';

  if(!qrUrl){
    showToast('No QR code image available.');
    return;
  }
  if(img) img.src = qrUrl;
  if(dLink) dLink.href = qrUrl;
  if(details){
    details.innerHTML = `<strong>${escHtml(gcashPayInfo?.account_name || 'Terra Nova HOA')}</strong><br>` +
      `<span style="color:#F58B1F;font-weight:700;font-family:monospace;">${escHtml(gcashPayInfo?.account_number || '')}</span>`;
  }
  modal.classList.add('open');
}
window.enlargeGcashQr = enlargeGcashQr;

function downloadGcashQr(){
  const qrUrl = gcashPayInfo?.qr_image_url;
  if(!qrUrl){
    showToast('No QR code image available to download.');
    return;
  }
  const a = document.createElement('a');
  a.href = qrUrl;
  a.download = 'HOA_GCash_QR.jpg';
  a.target = '_blank';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}
window.downloadGcashQr = downloadGcashQr;

function donutSVG(items, size){
  const total = items.reduce((a,i)=>a+(i.value||0),0) || 1;
  const r = size/2, thick = size*0.26, inner = r - thick, cx = r, cy = r;
  let angle = -Math.PI/2, body = '';
  items.forEach(it=>{
    const frac = (it.value||0)/total;
    const a0 = angle, a1 = angle + frac*Math.PI*2; angle = a1;
    if(frac <= 0) return;
    if(frac >= 0.9999){
      body += `<circle cx="${cx}" cy="${cy}" r="${(r+inner)/2}" fill="none" stroke="${it.color}" stroke-width="${thick}"/>`;
      return;
    }
    const large = frac > 0.5 ? 1 : 0;
    const x0=cx+inner*Math.cos(a0), y0=cy+inner*Math.sin(a0);
    const x1=cx+r*Math.cos(a0),     y1=cy+r*Math.sin(a0);
    const x2=cx+r*Math.cos(a1),     y2=cy+r*Math.sin(a1);
    const x3=cx+inner*Math.cos(a1), y3=cy+inner*Math.sin(a1);
    body += `<path d="M${x1} ${y1} A${r} ${r} 0 ${large} 1 ${x2} ${y2} L${x3} ${y3} A${inner} ${inner} 0 ${large} 0 ${x0} ${y0} Z" fill="${it.color}"/>`;
  });
  return `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" role="img" aria-label="Expense breakdown">${body}</svg>`;
}

async function loadTransparency(){
  const el = document.getElementById('transparencySummary'); if(!el) return;
  let s = {collected:0,spent:0,operating:0,reserve:0,net:0,balance:0,categories:[],period:null};
  try{ s = await fetch('/api/transparency/summary',{headers:{'Accept':'application/json'}}).then(r=>r.json()); }catch(e){}
  const peso = v => '₱' + Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2});
  const box = (label,val,color)=>`<div style="flex:1;min-width:110px;"><div style="font-size:13px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em;">${label}</div><div style="font-size:22px;font-weight:700;color:${color};">${peso(val)}</div></div>`;
  const fmt = d => d ? new Date(d).toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'numeric'}) : '';
  let html = '';
  if(s.period) html += `<div style="font-size:13px;font-weight:600;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Period · ${escHtml(fmt(s.period.from))} – ${escHtml(fmt(s.period.to))}</div>`;
  html += '<div style="display:flex;gap:16px;flex-wrap:wrap;width:100%;">' +
    box('Collected', s.collected, 'var(--green,#6B8E70)') +
    box('Operating', s.operating, '#c87941') +
    box('Reserve', s.reserve, '#5A7F9E') +
    box('Net', s.net, (Number(s.net) < 0 ? '#c87941' : 'var(--text)')) + '</div>';
  if(Number(s.net) < 0){
    html += `<div style="width:100%;margin-top:8px;font-size:13.5px;color:var(--text-dim);line-height:1.5;">Spending this period exceeded collections by ${peso(Math.abs(s.net))} — covered by reserve / prior funds.</div>`;
  }

  const cats = (s.categories || []).filter(c => (c.total||0) > 0 || (c.budget||0) > 0);
  if(cats.length){
    const palette = ['#A07D53','#c87941','#6B8E70','#5A7F9E','#8C829E','#C49646','#B5504A','#9E8C7A','#7D7975'];
    const total = cats.reduce((a,c)=>a+(c.total||0),0) || 1;
    const items = cats.map((c,i)=>({label:c.category||'Uncategorized', value:c.total||0, color:palette[i%palette.length]}));
    html += '<div style="width:100%;margin-top:16px;border-top:1px solid var(--glass-border);padding-top:16px;">' +
      '<div style="font-size:14px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em;margin-bottom:14px;">Where the Funds Go</div>' +
      '<div style="display:flex;gap:22px;align-items:center;flex-wrap:wrap;">' +
        '<div style="flex-shrink:0;position:relative;width:170px;height:170px;">' +
          donutSVG(items, 170) +
          '<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">' +
            '<div style="font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.08em;">Total spent</div>' +
            '<div style="font-size:16px;font-weight:700;color:var(--text);">' + peso(total) + '</div>' +
          '</div>' +
        '</div>' +
        '<div style="flex:1;min-width:220px;">' +
          cats.map((c,i)=>{
            const color = palette[i%palette.length];
            const budget = Number(c.budget||0), actual = Number(c.total||0);
            const pct = budget>0 ? Math.min(100, Math.round(actual/budget*100)) : 0;
            return `<div style="margin-bottom:10px;">
              <div style="display:flex;align-items:center;gap:8px;font-size:13px;">
                <span style="width:11px;height:11px;border-radius:3px;background:${color};display:inline-block;flex-shrink:0;"></span>
                <span style="flex:1;color:var(--text);">${escHtml(c.category||'Uncategorized')}</span>
                <span style="color:var(--text-dim);white-space:nowrap;">${peso(actual)}${budget>0?` / ${peso(budget)}`:''}</span>
              </div>
              ${budget>0?`<div style="height:5px;background:rgba(127,127,127,0.18);border-radius:3px;margin:4px 0 0 19px;overflow:hidden;"><div style="height:100%;width:${pct}%;background:${actual>budget?'#c87941':color};"></div></div>`:''}
            </div>`;
          }).join('') +
        '</div>' +
      '</div>' +
    '</div>';
  }
  el.innerHTML = html;
}

function updatePaymentKpis(){
  const list = Array.isArray(myPayments) ? myPayments : [];
  const pendingCount = list.filter(p => {
    const st = (p.status || '').toLowerCase();
    return st === 'pending' || st === 'under review';
  }).length;
  const validatedCount = list.filter(p => (p.status || '').toLowerCase() === 'validated').length;

  const pendingEl = document.getElementById('payKpiPending');
  if(pendingEl) pendingEl.textContent = pendingCount;

  const valEl = document.getElementById('payKpiValidated');
  if(valEl) valEl.textContent = validatedCount;
}

function renderRecentPayments(){
  const tbody = document.getElementById('recentPaymentsTbody');
  if(!tbody) return;
  const list = Array.isArray(myPayments) ? myPayments : [];
  if(!list.length){
    tbody.innerHTML = '<tr><td colspan="7" class="empty-state" style="text-align:center;padding:28px;color:var(--text-dim);">No payment submissions yet.</td></tr>';
    return;
  }
  const top5 = list.slice(0, 5);
  tbody.innerHTML = top5.map(p => {
    const amt = '₱' + Number(p.amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2});
    const subDate = formatPayDate(p.created_at, true);
    const paidDate = formatPayDate(p.paid_at || p.created_at, false);
    const ref = p.reference_no ? escHtml(p.reference_no) : '—';
    const remarks = p.admin_response ? escHtml(p.admin_response) : (p.rejection_reason ? `<span style="color:#DC2626;">${escHtml(p.rejection_reason)}</span>` : '—');
    const eyeIcon = typeof SubUI !== 'undefined' ? SubUI.icon('eye', 14) : '👁';
    const receiptBtn = p.has_receipt
      ? `<button type="button" class="pay-eye-btn" onclick="viewReceiptModal(${p.id})" title="View Receipt Screenshot" aria-label="View Receipt">${eyeIcon}</button>`
      : '<span style="color:var(--text-dim);font-size:12px;">—</span>';

    return `<tr>
      <td style="white-space:nowrap;font-size:13px;color:var(--text-mid);">${subDate}</td>
      <td style="font-family:monospace;font-size:13px;font-weight:600;color:var(--text);">${ref}</td>
      <td style="font-weight:700;color:var(--text);">${amt}</td>
      <td style="white-space:nowrap;font-size:13px;color:var(--text-mid);">${paidDate}</td>
      <td>${payStatusBadge(p.status)}</td>
      <td style="font-size:12.5px;color:var(--text-dim);max-width:240px;overflow:hidden;text-overflow:ellipsis;">${remarks}</td>
      <td style="text-align:center;">${receiptBtn}</td>
    </tr>`;
  }).join('');
}

async function loadMyPayments(){
  try{ myPayments = await fetch('/api/payments/my',{headers:{'Accept':'application/json'}}).then(r=>r.json()); }catch(e){ myPayments = []; }
  updatePaymentKpis();
  renderRecentPayments();
  renderMyPayments();
}
window.loadMyPayments = loadMyPayments;

function setHistoryFilter(status, el){
  currentHistoryFilter = status;
  document.querySelectorAll('#historyFilterPills .history-pill').forEach(btn => btn.classList.remove('active'));
  if(el) el.classList.add('active');
  renderMyPayments();
}
window.setHistoryFilter = setHistoryFilter;

function filterPaymentHistory(){
  renderMyPayments();
}
window.filterPaymentHistory = filterPaymentHistory;

function renderMyPayments(){
  const el = document.getElementById('myPaymentsList');
  if(!el) return;
  const list = Array.isArray(myPayments) ? myPayments : [];

  const searchInput = document.getElementById('payHistorySearch');
  const query = searchInput ? searchInput.value.trim().toLowerCase() : '';

  const filtered = list.filter(p => {
    // Status filter
    if(currentHistoryFilter !== 'all'){
      const st = (p.status || '').toLowerCase();
      if(currentHistoryFilter === 'Pending' && st !== 'pending' && st !== 'under review') return false;
      if(currentHistoryFilter === 'Validated' && st !== 'validated') return false;
      if(currentHistoryFilter === 'Rejected' && st !== 'rejected') return false;
    }
    // Search query
    if(query){
      const matchRef = (p.reference_no || '').toLowerCase().includes(query);
      const matchAmt = String(p.amount || '').toLowerCase().includes(query);
      const matchDate = String(p.paid_at || '').toLowerCase().includes(query) || String(p.created_at || '').toLowerCase().includes(query);
      const matchRemarks = (p.admin_response || '').toLowerCase().includes(query) || (p.rejection_reason || '').toLowerCase().includes(query);
      if(!matchRef && !matchAmt && !matchDate && !matchRemarks) return false;
    }
    return true;
  });

  if(!filtered.length){
    el.innerHTML = `<div class="empty-state" style="text-align:center;padding:36px;color:var(--text-dim);">${query || currentHistoryFilter !== 'all' ? 'No transactions match your search or filter.' : 'No payments submitted yet.'}</div>`;
    return;
  }

  const eyeIcon = typeof SubUI !== 'undefined' ? SubUI.icon('eye', 14) : '👁';
  const fileTextIcon = typeof SubUI !== 'undefined' ? SubUI.icon('file-text', 12) : '';
  const arrowRightIcon = typeof SubUI !== 'undefined' ? SubUI.icon('arrow-right', 12) : '→';

  el.innerHTML = `<table class="pay-table" id="historyPaymentsTable">
    <thead>
      <tr>
        <th>Date Submitted</th>
        <th>Reference No.</th>
        <th>Amount</th>
        <th>Date Paid</th>
        <th>Status</th>
        <th>Remarks & Details</th>
        <th style="text-align:center;">Receipts</th>
      </tr>
    </thead>
    <tbody>
      ${filtered.map(p => {
        const amt = '₱' + Number(p.amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2});
        const subDate = formatPayDate(p.created_at, true);
        const paidDate = formatPayDate(p.paid_at || p.created_at, false);
        const ref = p.reference_no ? escHtml(p.reference_no) : '—';
        const isRejected = (p.status || '').toLowerCase() === 'rejected';

        let remarksHtml = '';
        if(p.admin_response){
          remarksHtml += `<div style="font-size:12.5px;color:var(--text);">${escHtml(p.admin_response)}</div>`;
        }
        if(isRejected){
          remarksHtml += `<div class="pay-rejection-callout">
            <div>
              <strong>Rejection reason:</strong> ${escHtml(p.rejection_reason || 'Information mismatch')}
            </div>
            <button type="button" class="btn-resubmit-link" onclick="resubmitPayment('${escHtml(p.reference_no||'')}', ${Number(p.amount||0)})">
              ${arrowRightIcon} Correct & Resubmit
            </button>
          </div>`;
        }
        if(!remarksHtml) remarksHtml = '<span style="color:var(--text-dim);font-size:12px;">—</span>';

        let actionsHtml = '<div style="display:inline-flex;align-items:center;gap:6px;">';
        if(p.has_receipt){
          actionsHtml += `<button type="button" class="pay-eye-btn" onclick="viewReceiptModal(${p.id})" title="View Uploaded Receipt" aria-label="View Receipt">${eyeIcon}</button>`;
        }
        if(p.has_official_receipt){
          actionsHtml += `<a href="/api/payments/${p.id}/official-receipt?portal=resident" target="_blank" rel="noopener" class="pay-btn-subtle" style="padding:4px 8px;font-size:11px;" title="Download Official HOA Receipt">${fileTextIcon} Official</a>`;
        }
        if(!p.has_receipt && !p.has_official_receipt){
          actionsHtml += '<span style="color:var(--text-dim);font-size:12px;">—</span>';
        }
        actionsHtml += '</div>';

        return `<tr>
          <td style="white-space:nowrap;font-size:13px;color:var(--text-mid);">${subDate}</td>
          <td style="font-family:monospace;font-size:13px;font-weight:600;color:var(--text);">${ref}</td>
          <td style="font-weight:700;color:var(--text);">${amt}</td>
          <td style="white-space:nowrap;font-size:13px;color:var(--text-mid);">${paidDate}</td>
          <td>${payStatusBadge(p.status)}</td>
          <td>${remarksHtml}</td>
          <td style="text-align:center;">${actionsHtml}</td>
        </tr>`;
      }).join('')}
    </tbody>
  </table>`;
}

function viewReceiptModal(paymentId){
  const p = (myPayments || []).find(item => Number(item.id) === Number(paymentId));
  if(!p) return;
  const modal = document.getElementById('modal-receiptView');
  if(!modal) return;

  const titleEl = document.getElementById('receiptModalTitle');
  const subEl = document.getElementById('receiptModalSubtitle');
  const imgEl = document.getElementById('receiptModalImg');
  const loadEl = document.getElementById('receiptModalLoading');
  const metaEl = document.getElementById('receiptModalMeta');
  const dBtn = document.getElementById('receiptModalDownloadBtn');

  const amt = '₱' + Number(p.amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2});
  const ref = p.reference_no || '—';
  const receiptUrl = `/api/payments/${p.id}/receipt?portal=resident`;

  if(titleEl) titleEl.textContent = `Payment Receipt · ${amt}`;
  if(subEl) subEl.textContent = `Ref: ${ref} · Paid: ${formatPayDate(p.paid_at || p.created_at, false)}`;

  if(loadEl) loadEl.style.display = 'block';
  if(imgEl){
    imgEl.style.display = 'none';
    imgEl.src = receiptUrl;
  }
  if(dBtn){
    dBtn.href = receiptUrl;
    dBtn.download = `receipt_${ref.replace(/\s+/g, '_')}.jpg`;
  }
  if(metaEl){
    let metaHtml = `<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">` +
      `<div>Status: ${payStatusBadge(p.status)}</div>` +
      `<div style="font-size:12px;color:var(--text-dim);">Submitted: ${formatPayDate(p.created_at, true)}</div>` +
      `</div>`;
    if(p.admin_response){
      metaHtml += `<div style="margin-top:8px;font-size:13px;color:var(--text-mid);"><strong>Admin remarks:</strong> ${escHtml(p.admin_response)}</div>`;
    }
    if(p.rejection_reason){
      metaHtml += `<div style="margin-top:6px;font-size:13px;color:#DC2626;background:rgba(220,38,38,0.08);padding:8px 12px;border-radius:6px;border:1px solid rgba(220,38,38,0.2);"><strong>Rejection reason:</strong> ${escHtml(p.rejection_reason)}</div>`;
    }
    metaEl.innerHTML = metaHtml;
  }

  modal.classList.add('open');
}
window.viewReceiptModal = viewReceiptModal;

function resubmitPayment(refNo, amount){
  payTab('pay');
  const amtInput = document.getElementById('payAmount');
  const refInput = document.getElementById('payReference');
  if(amtInput && amount > 0) amtInput.value = Number(amount).toFixed(2);
  if(refInput && refNo) refInput.value = refNo;
  const formCard = document.getElementById('payFormCard');
  if(formCard) formCard.scrollIntoView({behavior:'smooth', block:'center'});
  showToast('ℹ️ Please attach your corrected receipt and verify payment details.');
}
window.resubmitPayment = resubmitPayment;

async function onReceiptFileSelected(input){
  const file = input?.files?.[0];
  if(!file) return;

  if(file.size > 8 * 1024 * 1024){
    showToast('⚠ Receipt image file must be 8 MB or less.');
    input.value = '';
    return;
  }

  const dropZone = document.getElementById('receiptDropZone');
  const previewBox = document.getElementById('receiptPreviewBox');
  const thumb = document.getElementById('receiptThumbPreview');
  const nameEl = document.getElementById('receiptFileName');
  const sizeEl = document.getElementById('receiptFileSize');

  if(nameEl) nameEl.textContent = file.name;
  if(sizeEl) sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';

  const reader = new FileReader();
  reader.onload = (e) => {
    if(thumb) thumb.src = e.target.result;
    if(dropZone) dropZone.style.display = 'none';
    if(previewBox) previewBox.style.display = 'flex';
  };
  reader.readAsDataURL(file);

  await runReceiptOcr(file);
}
window.onReceiptFileSelected = onReceiptFileSelected;

function clearReceiptUpload(){
  const inp = document.getElementById('payReceipt');
  if(inp) inp.value = '';
  const dropZone = document.getElementById('receiptDropZone');
  const previewBox = document.getElementById('receiptPreviewBox');
  const thumb = document.getElementById('receiptThumbPreview');
  const ocrStatus = document.getElementById('ocrStatus');

  if(dropZone) dropZone.style.display = 'flex';
  if(previewBox) previewBox.style.display = 'none';
  if(thumb) thumb.src = '';
  if(ocrStatus){
    ocrStatus.style.display = 'none';
    ocrStatus.innerHTML = '';
  }
}
window.clearReceiptUpload = clearReceiptUpload;

async function runReceiptOcr(file){
  const statusEl = document.getElementById('ocrStatus');
  if(!statusEl) return;
  if(!file || !file.type.startsWith('image/')) return;

  statusEl.style.display = 'block';
  statusEl.style.background = 'rgba(74, 144, 226, 0.08)';
  statusEl.style.borderColor = 'rgba(74, 144, 226, 0.25)';
  statusEl.style.color = 'var(--text-mid)';
  statusEl.innerHTML = '<span style="display:inline-block;">⏳</span> Scanning receipt with OCR to auto-fill details...';

  try {
    if(typeof Tesseract === 'undefined'){
      await new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
        s.onload = resolve;
        s.onerror = () => reject(new Error('OCR engine unavailable'));
        document.head.appendChild(s);
      });
    }

    const worker = await Tesseract.createWorker('eng');
    const ret = await worker.recognize(file);
    await worker.terminate();
    const rawText = ret?.data?.text || '';

    // 1. Reference Number extraction (GCash references are typically 13 digits, e.g. 1002 3456 78901)
    let refNo = null;
    const refMatch = rawText.match(/(?:ref(?:\.?\s*no\.?|\s*number)?|reference(?:\s*no\.?)?)[:\s#]*([0-9\s]{9,20})/i);
    if(refMatch){
      const digits = refMatch[1].replace(/\s+/g, '');
      if(digits.length >= 9 && digits.length <= 16) refNo = digits;
    }
    if(!refNo){
      const standalone = rawText.match(/\b(1\d{3}\s?\d{4}\s?\d{4,5}|\d{4}\s?\d{4}\s?\d{4,5})\b/);
      if(standalone) refNo = standalone[1].replace(/\s+/g, '');
    }

    // 2. Amount extraction (e.g. PHP 500.00, ₱500.00, Total: 500.00)
    let amount = null;
    const amtMatch = rawText.match(/(?:amount|total(?:\s*amount)?|paid|php|₱)\s*[:#]?\s*[₱P]?\s*([0-9,]+\.[0-9]{2})/i);
    if(amtMatch){
      const val = parseFloat(amtMatch[1].replace(/,/g, ''));
      if(!isNaN(val) && val > 0) amount = val;
    }
    if(!amount){
      const fallbackAmt = rawText.match(/[₱P]\s*([0-9,]+\.[0-9]{2})/);
      if(fallbackAmt){
        const val = parseFloat(fallbackAmt[1].replace(/,/g, ''));
        if(!isNaN(val) && val > 0) amount = val;
      }
    }

    // 3. Date extraction (e.g. 09 Nov 2026, Nov 09, 2026, 2026-11-09)
    let dateStr = null;
    const months = {jan:'01',feb:'02',mar:'03',apr:'04',may:'05',jun:'06',jul:'07',aug:'08',sep:'09',oct:'10',nov:'11',dec:'12'};
    const dMatch1 = rawText.match(/\b(\d{1,2})\s+([A-Za-z]{3,9})\s+(\d{4})\b/);
    if(dMatch1){
      const m = dMatch1[2].toLowerCase().substring(0, 3);
      if(months[m]) dateStr = `${dMatch1[3]}-${months[m]}-${dMatch1[1].padStart(2, '0')}`;
    }
    if(!dateStr){
      const dMatch2 = rawText.match(/\b([A-Za-z]{3,9})\s+(\d{1,2}),?\s+(\d{4})\b/);
      if(dMatch2){
        const m = dMatch2[1].toLowerCase().substring(0, 3);
        if(months[m]) dateStr = `${dMatch2[3]}-${months[m]}-${dMatch2[2].padStart(2, '0')}`;
      }
    }
    if(!dateStr){
      const dMatch3 = rawText.match(/\b(\d{4})[-/](\d{2})[-/](\d{2})\b/);
      if(dMatch3) dateStr = `${dMatch3[1]}-${dMatch3[2]}-${dMatch3[3]}`;
    }

    // Apply auto-filled values
    const detected = [];
    if(refNo){
      const refInput = document.getElementById('payReference');
      if(refInput){ refInput.value = refNo; detected.push('Ref: ' + refNo); }
    }
    if(amount){
      const amtInput = document.getElementById('payAmount');
      if(amtInput){ amtInput.value = amount.toFixed(2); detected.push('₱' + amount.toLocaleString(undefined, {minimumFractionDigits: 2})); }
    }
    if(dateStr){
      const dateInput = document.getElementById('payDate');
      if(dateInput){ dateInput.value = dateStr; detected.push('Date: ' + dateStr); }
    }

    if(detected.length > 0){
      statusEl.style.background = 'rgba(46, 125, 50, 0.08)';
      statusEl.style.borderColor = 'rgba(46, 125, 50, 0.25)';
      statusEl.style.color = '#2e7d32';
      statusEl.innerHTML = `<strong>✨ Auto-detected:</strong> ${escHtml(detected.join(' · '))}. <span style="font-size:11px;color:var(--text-dim);">(Please verify before submitting)</span>`;
    } else {
      statusEl.style.background = 'rgba(239, 108, 0, 0.08)';
      statusEl.style.borderColor = 'rgba(239, 108, 0, 0.25)';
      statusEl.style.color = '#e65100';
      statusEl.innerHTML = 'Could not auto-read reference number or amount from this image. Please check and enter the fields manually.';
    }
  } catch(err) {
    statusEl.style.background = 'rgba(239, 108, 0, 0.08)';
    statusEl.style.borderColor = 'rgba(239, 108, 0, 0.25)';
    statusEl.style.color = '#e65100';
    statusEl.innerHTML = 'OCR preview unavailable. Please verify and enter the fields manually.';
  }
}

function bindReceiptPasteAndDrop(){
  const card = document.getElementById('payFormCard');
  if(!card || card.__ocrBound) return;
  card.__ocrBound = true;

  card.addEventListener('paste', (e) => {
    const items = e.clipboardData?.items;
    if(!items) return;
    for(let i = 0; i < items.length; i++){
      if(items[i].type.indexOf('image') !== -1){
        const file = items[i].getAsFile();
        if(file){
          const dt = new DataTransfer();
          dt.items.add(file);
          const inp = document.getElementById('payReceipt');
          if(inp){
            inp.files = dt.files;
            onReceiptFileSelected(inp);
            showToast('📋 Receipt pasted from clipboard.');
          }
        }
        break;
      }
    }
  });

  ['dragenter', 'dragover'].forEach(evtName => {
    card.addEventListener(evtName, (e) => {
      e.preventDefault();
      card.style.borderColor = 'var(--accent, #F58B1F)';
    });
  });
  ['dragleave', 'drop'].forEach(evtName => {
    card.addEventListener(evtName, (e) => {
      e.preventDefault();
      card.style.borderColor = '';
    });
  });
  card.addEventListener('drop', (e) => {
    const file = e.dataTransfer?.files?.[0];
    if(file && file.type.startsWith('image/')){
      const dt = new DataTransfer();
      dt.items.add(file);
      const inp = document.getElementById('payReceipt');
      if(inp){
        inp.files = dt.files;
        onReceiptFileSelected(inp);
        showToast('📁 Receipt attached.');
      }
    }
  });
}

async function submitPayment(){
  const amtEl = document.getElementById('payAmount');
  const refEl = document.getElementById('payReference');
  const senderEl = document.getElementById('paySender');
  const dateEl = document.getElementById('payDate');
  const receiptInp = document.getElementById('payReceipt');

  const amount = amtEl?.value;
  const receipt = receiptInp?.files?.[0];
  const reference = refEl?.value.trim() || '';
  const gcashSender = senderEl?.value.trim() || '';
  const datePaid = dateEl?.value || '';

  if(!SubUI.required([
    [amount, 'payAmount', 'Please enter the amount paid.'],
    [receipt, 'payReceipt', 'Please attach the GCash receipt.'],
    [reference, 'payReference', 'Please enter the GCash reference number.'],
    [gcashSender, 'paySender', 'Please enter the sender name.'],
    [datePaid, 'payDate', 'Please specify the date paid.']
  ])) return;

  const numAmt = parseFloat(amount);
  if(isNaN(numAmt) || numAmt <= 0){
    showToast('⚠ Please enter a valid payment amount greater than zero.');
    amtEl.focus();
    return;
  }

  const cleanRef = reference.replace(/[\s-]/g, '');
  if(cleanRef.length < 8){
    showToast('⚠ Please enter a valid GCash reference number (typically 13 digits).');
    refEl.focus();
    return;
  }

  const fd = new FormData();
  fd.append('amount', numAmt);
  fd.append('reference_no', reference);
  fd.append('gcash_sender_name', gcashSender);
  fd.append('paid_at', datePaid);
  fd.append('receipt', receipt);

  const submitBtn = document.getElementById('paySubmitBtn');
  const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
  if(submitBtn){
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.7';
    submitBtn.innerHTML = '<span>⏳ Submitting payment...</span>';
  }

  try{
    const res = await fetch('/api/payments', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': payCsrf()
      },
      body: fd
    });
    const data = await res.json();
    if(data.success){
      if(amtEl) amtEl.value = '';
      if(refEl) refEl.value = '';
      clearReceiptUpload();
      await loadMyPayments();
      await loadStatement();
      showToast('✅ Payment submitted successfully! Awaiting administrator verification.');
    } else {
      showToast('⚠ ' + errMsg(data, 'Could not submit payment.'));
    }
  } catch(e){
    showToast('⚠ Could not submit payment. Please check your connection and try again.');
  } finally {
    if(submitBtn){
      submitBtn.disabled = false;
      submitBtn.style.opacity = '';
      submitBtn.innerHTML = originalBtnHtml;
    }
  }
}
window.submitPayment = submitPayment;


function renderHOAExpenses(){
  const el = document.getElementById('expensesList');
  if(!el) return;
  if(!hoaExpenses.length){
    el.innerHTML = '<div class="empty-state">No expenses recorded yet.</div>';
    return;
  }
  let html = '<table class="data-table" style="width:100%;"><thead><tr><th>Date</th><th>Category</th><th>Title</th><th>Amount</th><th>Status</th><th>Receipt</th></tr></thead><tbody>';
  hoaExpenses.forEach(e => {
    let receiptCell = '—';
    if(e.receipt_url){
      const portal = window.__PORTAL || 'resident';
      const u = e.receipt_url + (e.receipt_url.indexOf('?') < 0 ? '?' : '&') + 'portal=' + portal;
      receiptCell = `<a href="${u}" target="_blank" rel="noopener">View</a>`;
    }
    html += `<tr>
      <td>${e.expense_date}</td>
      <td>${e.category}</td>
      <td>
        <span style="font-weight:600;">${escHtml(e.title)}</span>
        ${e.issue_id ? `<span class="pill pill-progress" style="font-size:10px;margin-left:6px;" title="Linked to Issue #${e.issue_id}">#Issue ${e.issue_id}</span>` : ''}
      </td>
      <td>₱${parseFloat(e.amount).toLocaleString(undefined, {minimumFractionDigits:2})}</td>
      <td>${e.status}</td>
      <td>${receiptCell}</td>
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
      <td style="font-weight:500;color:var(--text);"><span class="icon" style="color:var(--accent);margin-right:8px;">${SubUI.icon('scroll',14)}</span>${p.title}</td>
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
    el.innerHTML='<div class="empty-state">' + SubUI.icon('lightbulb', 28) + 'No suggestions yet. Be the first to share an idea!</div>';
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
  if(!SubUI.required([[category,'recCategory','Please choose a category.'],[title,'recTitle','Please enter a title.'],[description,'recBody','Please describe your suggestion.']])) return;
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

let RESIDENT_ID = window.__RESIDENT.id;
let HOUSE_ID = window.__RESIDENT.houseId;
let RESIDENT_NAME = window.__RESIDENT.name;

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

            // Update outstanding balance
            const balEl = document.getElementById('residentBalance');
            if (balEl && data.resident.balance != null) {
                balEl.textContent = '₱' + Number(data.resident.balance).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
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

/* ── Real-time: WebSocket (Reverb) first, SSE only as fallback ── */
let _sseEventId = 0;
let _rtWsReady = false;   // true while the Reverb socket is connected
let _rtSse = null;        // the fallback EventSource (null when WS is primary)

/* Re-fetch the open thread; used by the WS push and the 10s fallback poll. */
async function refreshResidentMessages(){
  if(!currentThread) return;
  try{
    const msgs = await fetch('/api/messages/'+currentThread,{headers:{'Accept':'application/json'}}).then(r=>r.json());
    if(Array.isArray(msgs) && msgs.length !== (threads[currentThread]?.messages||[]).length){
      threads[currentThread].messages = msgs;
      renderMessages();
    }
  }catch(e){}
}

/* Refresh just the "Message Requests" block — who is trying to reach you. */
async function refreshResidentRequestBlock(){
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
        <button class="btn btn-sm" onclick="acceptResRequest(${q.id})">Accept</button>
        <button class="btn btn-sm btn-danger" onclick="rejectResRequest(${q.id})">Reject</button>
      </div>
    </div>`).join('')}
  </div>`;
  if(existing) existing.outerHTML = html; else sidebar.insertAdjacentHTML('afterbegin', html);
}

function connectResidentSSE(){
  if(_rtWsReady) return; // WebSocket is live — SSE is only the fallback
  if(document.hidden){ setTimeout(connectResidentSSE, 2000); return; }
  const url = `/api/stream/updates?lastEventId=${_sseEventId}`;
  const es  = new EventSource(url);
  _rtSse = es;

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

  // Subdivision block partition / boundary update (live on map)
  es.addEventListener('block', () => {
    try{
      if(typeof loadResidentBlocks === 'function') loadResidentBlocks();
      else if(typeof renderResidentBlocks === 'function') renderResidentBlocks();
    }catch(err){}
  });

  // Community events & RSVPs
  es.addEventListener('event', () => { if(typeof loadEvents === 'function') loadEvents(); });
  es.addEventListener('rsvp', () => { if(typeof loadEvents === 'function') loadEvents(); });

  // Interactive map updates: facilities and blueprint settings
  es.addEventListener('facility', () => { if(typeof refreshResidentMapLayers === 'function') refreshResidentMapLayers(); });
  es.addEventListener('map-setting', () => {
    if(typeof refreshResidentMapLayers === 'function') refreshResidentMapLayers();
    if(typeof loadResidentBlocks === 'function') loadResidentBlocks();
  });

  // Reconnect cycle — server sends lastEventId
  es.addEventListener('reconnect', (e) => {
    try{
      const evt = JSON.parse(e.data);
      if(evt.lastEventId) _sseEventId = evt.lastEventId;
    }catch(err){}
    es.close();
    _rtSse = null;
    if(_rtWsReady) return; // WS came up while this cycle ran — stop the SSE loop
    // Refresh on every cycle so updates still land through buffering tunnels (e.g. ngrok)
    loadAnnouncements();
    loadIssues();
    loadHOAExpenses();
    loadHOAPolicies();
    setTimeout(connectResidentSSE, 200);
  });

  es.onerror = () => {
    es.close();
    _rtSse = null;
    if(!_rtWsReady) setTimeout(connectResidentSSE, 3000);
  };

  document.addEventListener('visibilitychange', () => {
    if(!document.hidden && es.readyState === EventSource.CLOSED && !_rtWsReady){
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
      loadAnnouncements(); loadIssues(); loadHOAExpenses(); loadHOAPolicies();
      if(typeof refreshResidentMapLayers === 'function') refreshResidentMapLayers();
      document.dispatchEvent(new Event('subsync:refresh-alerts'));
    };
    ch.listen('.announcement', refresh).listen('.issue', refresh).listen('.policy', refresh).listen('.hoa-expense', refresh).listen('.block', () => { if(typeof loadResidentBlocks === 'function') loadResidentBlocks(); else if(typeof renderResidentBlocks === 'function') renderResidentBlocks(); });
    ch.listen('.facility', () => { if(typeof refreshResidentMapLayers === 'function') refreshResidentMapLayers(); })
      .listen('.map-setting', () => {
        if(typeof refreshResidentMapLayers === 'function') refreshResidentMapLayers();
        if(typeof loadResidentBlocks === 'function') loadResidentBlocks();
      });

    // Real-time data changes relevant to this resident.
    ch.listen('.payment', (evt) => {
      if(!evt || Number(evt.residentId) !== Number(RESIDENT_ID)) return;
      showToast('Payment update: ' + (evt.status || 'updated') + '.');
      loadPayments();
      document.dispatchEvent(new Event('subsync:refresh-alerts'));
    });
    ch.listen('.recommendation', (evt) => {
      if(evt && Number(evt.residentId) === Number(RESIDENT_ID)) loadRecs();
    });
    ch.listen('.event', () => loadEvents());
    ch.listen('.rsvp', () => loadEvents());
    ch.listen('.resident', (evt) => {
      if(evt && Number(evt.latestId) === Number(RESIDENT_ID)) loadPortalData();
    });
    ch.listen('.message', (evt) => {
      if(!evt || !evt.conversationId) return;
      const isOpen = String(evt.conversationId) === String(currentThread);
      const panelOpen = document.getElementById('panel-messages')?.classList.contains('active');
      const mine = evt.senderType === 'resident' && Number(evt.senderId) === Number(RESIDENT_ID);
      if(mine){ if(isOpen) refreshResidentMessages(); return; }
      if(isOpen && panelOpen){ refreshResidentMessages(); return; }
      const _tName=(threads[evt.conversationId]&&threads[evt.conversationId].title)||'';
      showToast(_tName ? 'New message from ' + _tName + '.' : 'New message.');
      const badge = document.getElementById('badge-messages');
      if(badge){ badge.textContent = (parseInt(badge.textContent,10)||0) + 1; badge.style.display = 'inline-flex'; }
      refreshResidentRequestBlock();
      if(isOpen) refreshResidentMessages();
    });

    // WebSocket-first: while Reverb is connected the SSE fallback stays off;
    // if the socket drops, bring the fallback back.
    const conn = window.Echo.connector && window.Echo.connector.pusher && window.Echo.connector.pusher.connection;
    if(conn){
      const wsUp = () => { _rtWsReady = true; if(_rtSse){ try{ _rtSse.close(); }catch(e){} _rtSse = null; } };
      const wsDown = () => { if(_rtWsReady){ _rtWsReady = false; connectResidentSSE(); } };
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
    try{ if(typeof resMapInst!=='undefined' && resMapInst) resMapInst.invalidateSize(); }catch(e){}
  }, 200);
});
window.addEventListener('orientationchange', function(){ setTimeout(function(){ try{ if(resMapInst) resMapInst.invalidateSize(); }catch(e){} }, 300); });

/* Allows the shared global search to navigate panels by name */
window.PortalNav = { go: function (name) { switchTab(name); } };
/* ── CALENDAR / EVENTS ── */
let communityEvents=[];
async function loadEvents(){
  const box=document.getElementById('eventsList');
  if(!box) return;
  const data=await fetch('/api/events',{headers:{'Accept':'application/json'}}).then(r=>r.json()).catch(()=>[]);
  communityEvents=Array.isArray(data)?data:[];
  renderEvents();
}
function renderEvents(){
  const box=document.getElementById('eventsList');
  if(!box) return;
  if(!communityEvents.length){ box.innerHTML='<div class="empty-state">' + SubUI.icon('calendar', 28) + 'No upcoming events.</div>'; return; }
  box.innerHTML=communityEvents.map(e=>{
    const mine=e.my_status||'';
    const btn=(label,val)=>`<button class="btn btn-sm" style="margin-right:6px;${mine===val?'background:var(--accent);border-color:var(--accent);color:#fff;':''}" onclick="rsvpEvent(${e.id},'${val}')">${label}</button>`;
    return `<div class="issue-item">
      <div class="issue-title" style="font-size:15px;">${escHtml(e.title)}</div>
      <div style="font-size:12px;color:var(--text-dim);margin-top:3px;">${escHtml(e.starts_at||'')}${e.location?' &middot; '+escHtml(e.location):''}</div>
      ${e.description?`<div style="font-size:13px;margin-top:6px;">${escHtml(e.description)}</div>`:''}
      <div style="margin-top:8px;">${btn('Going','going')}${btn('Maybe','maybe')}${btn("Can't go",'not_going')}
        <span style="font-size:11px;color:var(--text-dim);margin-left:6px;">${e.going} going &middot; ${e.maybe} maybe</span></div>
    </div>`;
  }).join('');
}
async function rsvpEvent(id,status){
  const res=await fetch('/api/events/'+id+'/rsvp',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},body:JSON.stringify({status})}).then(r=>r.json()).catch(()=>({success:false}));
  if(res&&res.success){ const e=communityEvents.find(x=>String(x.id)===String(id)); if(e) e.my_status=status; loadEvents(); }
  else showToast('Could not save your RSVP.');
}

/* ══ TAB PERSISTENCE ACROSS REFRESH ══ */
function normalizeResidentTab(target){
  if(!target) return '';
  const aliases = {
    'mapping': 'map',
    'recommendations': 'recs',
    'suggestions': 'recs'
  };
  return aliases[target] || target;
}
function restoreResidentTab(){
  try {
    const rawHash = (window.location.hash || '').replace(/^#/, '').trim().toLowerCase();
    const saved = sessionStorage.getItem('subsync_active_tab_resident');
    let target = normalizeResidentTab(rawHash || saved);
    if(target && document.getElementById('panel-' + target)){
      switchTab(target);
      if(target === 'map'){
        setTimeout(function(){
          if(typeof window.initResidentMap === 'function') window.initResidentMap();
          else if(typeof initResidentMap === 'function') initResidentMap();
        }, 120);
      }
    }
  } catch(e) {}
}
restoreResidentTab();
if(document.readyState === 'loading'){
  document.addEventListener('DOMContentLoaded', restoreResidentTab);
}
window.addEventListener('hashchange', function(){
  try {
    const hash = (window.location.hash || '').replace(/^#/, '').trim().toLowerCase();
    let target = normalizeResidentTab(hash);
    if(target && document.getElementById('panel-' + target)){
      switchTab(target);
    }
  } catch(e) {}
});
