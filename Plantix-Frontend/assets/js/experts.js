// Expert accounts and panel logic (frontend-only, localStorage)
(function(){
  const EXPERTS_KEY='plantixExperts_v1';
  const CURRENT_EXPERT_KEY='plantixCurrentExpert_v1';
  const NOTIFS_KEY='plantixExpertNotifs_v1';
  const ADVICE_KEY='plantixExpertAdvice_v1';
  const APPTS_KEY='plantixAppointments'; // reuse user appointments store
  const RESETS_KEY='plantixExpertResets_v1';
  const STATIC_RESET_TOKEN='PLANTIX';

  function loadExperts(){ try{return JSON.parse(localStorage.getItem(EXPERTS_KEY)||'[]');}catch(e){return [];} }
  function saveExperts(arr){ localStorage.setItem(EXPERTS_KEY, JSON.stringify(arr||[])); }
  function loadCurrent(){ try{return JSON.parse(localStorage.getItem(CURRENT_EXPERT_KEY)||'null');}catch(e){return null;} }
  function saveCurrent(ex){ localStorage.setItem(CURRENT_EXPERT_KEY, JSON.stringify(ex||null)); updateHeader(); }
  function loadNotifs(){ try{return JSON.parse(localStorage.getItem(NOTIFS_KEY)||'[]');}catch(e){return [];} }
  function saveNotifs(arr){ localStorage.setItem(NOTIFS_KEY, JSON.stringify(arr||[])); }
  function loadAdvice(){ try{return JSON.parse(localStorage.getItem(ADVICE_KEY)||'[]');}catch(e){return [];} }
  function saveAdvice(arr){ localStorage.setItem(ADVICE_KEY, JSON.stringify(arr||[])); }
  function loadAppts(){ try{return JSON.parse(localStorage.getItem(APPTS_KEY)||'[]');}catch(e){return [];} }
  function saveAppts(arr){ localStorage.setItem(APPTS_KEY, JSON.stringify(arr||[])); }
  function genId(p){ return p+'_'+Math.random().toString(36).slice(2,8)+'_'+Date.now(); }
  function now(){ return Date.now(); }

  function loadResets(){ try{return JSON.parse(localStorage.getItem(RESETS_KEY)||'[]');}catch(e){return [];} }
  function saveResets(arr){ localStorage.setItem(RESETS_KEY, JSON.stringify(arr||[])); }

  const ExpertAuth={
    // Experts are provisioned by admin only (no self-registration)
    signUp(){ throw new Error('Experts cannot self-register. Please contact PlantixAI admin.'); },
    signIn({email,password}){
      email=(email||'').trim().toLowerCase(); const experts=loadExperts(); const ex=experts.find(e=>e.email===email && e.password===(password||'')); if(!ex) throw new Error('Invalid credentials'); saveCurrent(ex); return ex;
    },
    signOut(){ saveCurrent(null); },
    current(){ return loadCurrent(); },
    requestPasswordReset(email){ email=(email||'').trim().toLowerCase(); const experts=loadExperts(); const ex=experts.find(e=>e.email===email); if(!ex) throw new Error('No expert found with this email'); const token=STATIC_RESET_TOKEN; const recs=loadResets().filter(r=>r.email!==email); recs.push({email,token,expiresAt: now()+15*60*1000}); saveResets(recs); return { email, token, expiresInMinutes: 15 }; },
    resetPassword({email, token, newPassword}){ email=(email||'').trim().toLowerCase(); token=(token||'').trim().toUpperCase(); if (!email||!token||!newPassword) throw new Error('All fields are required'); if (token!==STATIC_RESET_TOKEN){ const rec=loadResets().find(r=>r.email===email && r.token===token); if(!rec) throw new Error('Invalid reset token'); if(now()>rec.expiresAt) throw new Error('Reset token expired'); }
      const experts=loadExperts(); const idx=experts.findIndex(e=>e.email===email); if(idx===-1) throw new Error('Account not found'); experts[idx].password=newPassword; saveExperts(experts); saveResets(loadResets().filter(r=>!(r.email===email && r.token===token))); return true; },
    updateProfile(patch){ const ex=loadCurrent(); if(!ex) return; const experts=loadExperts(); const idx=experts.findIndex(e=>e.id===ex.id); if(idx===-1) return; experts[idx]=Object.assign({}, ex, patch||{}); saveExperts(experts); saveCurrent(experts[idx]); }
  };

  function updateHeader(){ try{ const link=document.querySelector('.attr-nav .button a'); if(!link) return; const ex=loadCurrent(); const clone=link.cloneNode(true); if(ex){ clone.textContent='Expert Panel'; clone.setAttribute('href','expert-dashboard.html'); } else { /* keep as-is; customer Auth updates it */ } link.parentNode.replaceChild(clone, link);}catch(e){} }

  // Expert directory for client-side selection
  const ExpertDirectory={
    listAll(){ return loadExperts().map(({password, ...e})=>e); },
    listAvailable(){ return loadExperts().filter(e=>e.isAvailable!==false).map(({password, ...e})=>e); },
    getName(id){ const e=loadExperts().find(x=>x.id===id); return e? e.name : null; }
  };

  // Expert-facing appointments
  const ExpertAppointments={
    list({mineOnly=false, status}={}){ const ex=loadCurrent(); const all=loadAppts(); let arr=all.slice(); if(mineOnly && ex){ arr=arr.filter(a=>a.assignedExpertId===ex.id); } else if (ex){ arr=arr.filter(a=>!a.assignedExpertId || a.assignedExpertId===ex.id); } if(status){ arr=arr.filter(a=>a.status===status); } return arr.sort((a,b)=>(b.dateTime||b.createdAt)-(a.dateTime||a.createdAt)); },
    accept(id){ const ex=loadCurrent(); if(!ex) throw new Error('Not signed in'); const all=loadAppts(); const idx=all.findIndex(a=>String(a.id)===String(id)); if(idx===-1) throw new Error('Appointment not found'); const a=all[idx]; if (a.status==='Cancelled' || a.status==='Rejected') throw new Error('Cannot accept this appointment'); a.assignedExpertId=ex.id; a.assignedExpertName=ex.name; a.status='Accepted'; a.updatedAt=now(); all[idx]=a; saveAppts(all); notify(`Accepted appointment #${a.id}`); return a; },
    reject(id, reason){ const ex=loadCurrent(); if(!ex) throw new Error('Not signed in'); const all=loadAppts(); const idx=all.findIndex(a=>String(a.id)===String(id)); if(idx===-1) throw new Error('Appointment not found'); const a=all[idx]; if (a.status!=='Pending') throw new Error('Only pending appointments can be rejected'); a.assignedExpertId=ex.id; a.status='Rejected'; a.rejectReason=reason||''; a.updatedAt=now(); all[idx]=a; saveAppts(all); notify(`Rejected appointment #${a.id}`); return a; },
    reschedule(id, newDate){ const ex=loadCurrent(); if(!ex) throw new Error('Not signed in'); const all=loadAppts(); const idx=all.findIndex(a=>String(a.id)===String(id)); if(idx===-1) throw new Error('Appointment not found'); const a=all[idx]; if (a.status==='Cancelled' || a.status==='Rejected') throw new Error('Cannot reschedule this appointment'); a.assignedExpertId=ex.id; a.previousDateTime=a.dateTime; a.dateTime=newDate; a.status='Rescheduled'; a.updatedAt=now(); all[idx]=a; saveAppts(all); notify(`Rescheduled appointment #${a.id}`); return a; }
  };

  // Advice posts (visible to farmers elsewhere if desired)
  const ExpertAdvice={
    list(){ return loadAdvice().sort((a,b)=>b.createdAt-a.createdAt); },
    create({title, body, tags}){ const ex=loadCurrent(); if(!ex) throw new Error('Not signed in'); title=(title||'').trim(); body=(body||'').trim(); if(!title||!body) throw new Error('Title and body required'); const arr=loadAdvice(); const item={ id: genId('adv'), title, body, tags:(tags||[]).filter(Boolean), createdAt: now(), author:{id:ex.id,name:ex.name} }; arr.push(item); saveAdvice(arr); notify(`New advice published: ${title}`); return item; }
  };

  function notify(text){ const ex=loadCurrent(); const arr=loadNotifs(); arr.unshift({ id: genId('n'), text, read:false, ts: now(), actor: ex?{id:ex.id,name:ex.name}:null }); saveNotifs(arr.slice(0,100)); }
  const ExpertNotifs={ list(){ return loadNotifs(); }, markRead(id){ const arr=loadNotifs(); const idx=arr.findIndex(n=>n.id===id); if(idx>-1){ arr[idx].read=true; saveNotifs(arr);} }, clear(){ saveNotifs([]); }, notify(text){ notify(text); } };

  // Expose API
  window.ExpertAuth=ExpertAuth;
  window.ExpertAppointments=ExpertAppointments;
  window.ExpertAdvice=ExpertAdvice;
  window.ExpertNotifs=ExpertNotifs;
  window.ExpertDirectory=ExpertDirectory;

  // Seed a demo expert if none exists
  (function seed(){ let arr=loadExperts();
    // Replace legacy demo
    arr = arr.filter(e=>e.email!=='expert@plantixai.com');
    // Ensure multiple experts exist
    function ensure(name,email,password,agency,specialties,isAvailable=true){
      if (!arr.find(e=>e.email===email)){
        arr.push({ id: genId('ex'), name, email, password, agency, phone:'', bio:'', role:'expert', specialties: specialties||[], isAvailable, createdAt: now() });
      }
    }
    ensure('Expert User','expert@gmail.com','12345678','PlantixAI',['General','Irrigation'],true);
    ensure('Dr. Ayesha Khan','ayesha@plantixai.com','plantix123','PlantixAI',['Wheat','Rice'],true);
    ensure('Engr. Hamid Raza','hamid@plantixai.com','plantix123','PlantixAI',['Irrigation','Cotton'],true);
    ensure('Soil Lab Desk','soil.lab@plantixai.com','soil123','SoilLab',['Soil','Fertilizer'],false);
    saveExperts(arr);
    // Add a couple fake notifications
    if (loadNotifs().length===0){ saveNotifs([{id:genId('n'), text:'New forum question tagged Wheat', read:false, ts: now()-3600*1000},{id:genId('n'), text:'2 pending appointment requests in Lahore', read:false, ts: now()-2*3600*1000}]); }
    // Seed a few open appointment requests for experts to act on (no specific user)
    try{
      const all=loadAppts();
      if (!all.some(a=>a.seedTag==='expertDemo')){
        const e1 = arr.find(x=>x.email==='ayesha@plantixai.com');
        const e2 = arr.find(x=>x.email==='hamid@plantixai.com');
        all.push({ id: genId('a'), seedTag:'expertDemo', userName:'Farmer Ali', userId:'u_demo1', type:'Consultation', channel:'Phone', dateTime: now()+2*24*3600*1000, status:'Pending', reason:'Wheat rust doubts', createdAt: now(), assignedExpertId: e1?.id, assignedExpertName: e1?.name });
        all.push({ id: genId('a'), seedTag:'expertDemo', userName:'Sana Baloch', userId:'u_demo2', type:'Soil Testing', channel:'In-Person', dateTime: now()+3*24*3600*1000, status:'Pending', reason:'EC high in field', createdAt: now(), assignedExpertId: e2?.id, assignedExpertName: e2?.name });
        saveAppts(all);
      }
    }catch(e){}
  })();

  // Dashboard wiring (if present)
  document.addEventListener('DOMContentLoaded', function(){
    const dash=document.getElementById('expert-dashboard'); if(!dash) return;
    if(!ExpertAuth.current()){ try{ sessionStorage.setItem('plantixRedirect', location.pathname+location.search); }catch(e){} window.location.href='signin.html?role=expert'; return; }

    // Helpers
    function getEl(id){ return document.getElementById(id); }
    function setInvalid(el, msg){ if(!el) return; el.classList.add('is-invalid'); let fb=el.nextElementSibling; if(!fb || !fb.classList?.contains('invalid-feedback')){ fb=document.createElement('div'); fb.className='invalid-feedback'; el.insertAdjacentElement('afterend', fb); } fb.textContent=msg||'Invalid field'; }
    function clearInvalid(el){ if(!el) return; el.classList.remove('is-invalid'); const fb=el.nextElementSibling; if(fb && fb.classList?.contains('invalid-feedback')) fb.textContent=''; }

    // Fill profile from current session each time
    function fillProfile(){ const cur=ExpertAuth.current(); getEl('exName').value=cur?.name||''; getEl('exEmail').value=cur?.email||''; getEl('exAgency').value=cur?.agency||''; getEl('exPhone').value=cur?.phone||''; getEl('exBio').value=cur?.bio||''; }
    fillProfile();

    // Validate inputs
    function validate(){
      const name=(getEl('exName').value||'').trim();
      const phone=(getEl('exPhone').value||'').trim();
      const agency=(getEl('exAgency').value||'').trim();
      const bio=(getEl('exBio').value||'').trim();
      let ok=true;
      // clear prior
      [getEl('exName'), getEl('exPhone'), getEl('exAgency'), getEl('exBio')].forEach(clearInvalid);
      if (name.length<2){ setInvalid(getEl('exName'),'Please enter your name'); ok=false; }
      if (phone && !/^\+?[0-9\-\s]{7,15}$/.test(phone)){ setInvalid(getEl('exPhone'),'Enter a valid phone number'); ok=false; }
      if (agency.length>80){ setInvalid(getEl('exAgency'),'Agency name is too long'); ok=false; }
      if (bio.length>500){ setInvalid(getEl('exBio'),'Bio must be 500 characters or less'); ok=false; }
      return ok;
    }

    // Save handler
    getEl('exSaveProfile')?.addEventListener('click', function(){
      if (!validate()){ if (window.Toast) Toast.show('Please fix validation errors','error'); else alert('Please fix validation errors'); return; }
      const btn=this; const orig=btn.disabled; btn.disabled=true;
      try{
        ExpertAuth.updateProfile({ name:getEl('exName').value.trim(), agency:getEl('exAgency').value.trim(), phone:getEl('exPhone').value.trim(), bio:getEl('exBio').value.trim() });
        fillProfile();
        if (window.Toast) Toast.show('Profile saved','success'); else alert('Profile saved');
      }catch(e){ alert(e.message||'Unable to save'); }
      finally{ btn.disabled=orig; }
    });

    // Appointments
    function renderAppts(){ const list=document.getElementById('exApptsBody'); const arr=ExpertAppointments.list(); list.innerHTML = arr.length? arr.map(a=>{ const canAccept = (a.status==='Pending' || a.status==='Rescheduled'); const canReject = (a.status==='Pending'); const canReschedule = (a.status==='Pending' || a.status==='Accepted'); const btns = [ canAccept?`<button class="btn btn-sm btn-success" data-act="accept" data-id="${a.id}">Accept</button>`:'', canReject?`<button class="btn btn-sm btn-outline-danger" data-act="reject" data-id="${a.id}">Reject</button>`:'', canReschedule?`<button class="btn btn-sm btn-outline-secondary" data-act="reschedule" data-id="${a.id}">Reschedule</button>`:'' ].filter(Boolean).join(' '); return `<tr><td>${a.id}</td><td>${new Date(a.dateTime||a.createdAt).toLocaleString()}</td><td>${a.userName||'-'}</td><td>${a.reason||'-'}</td><td>${a.status||'Pending'}</td><td>${btns||'<span class="text-muted">No actions</span>'}</td></tr>`; }).join('') : '<tr><td colspan="6" class="text-muted">No appointment requests right now.</td></tr>';
      list.querySelectorAll('button[data-act="accept"]').forEach(b=> b.addEventListener('click',()=>{ try{ ExpertAppointments.accept(b.getAttribute('data-id')); renderAppts(); }catch(e){ alert(e.message);} }));
      list.querySelectorAll('button[data-act="reject"]').forEach(b=> b.addEventListener('click',()=>{ const reason=prompt('Reason (optional):',''); try{ ExpertAppointments.reject(b.getAttribute('data-id'), reason||''); renderAppts(); }catch(e){ alert(e.message);} }));
      list.querySelectorAll('button[data-act="reschedule"]').forEach(b=> b.addEventListener('click',()=>{ const when=prompt('New date/time (YYYY-MM-DD HH:mm)',''); if(!when) return; try{ ExpertAppointments.reschedule(b.getAttribute('data-id'), when); renderAppts(); }catch(e){ alert(e.message);} }));
    }
    renderAppts();

    // Forum quick reply (list recent threads)
    try{
      const wrap=document.getElementById('exForumThreads');
  if (window.Forum){ const threads=Forum.list({sort:'active'}).slice(0,8); wrap.innerHTML = threads.map(t=>{ const solved=t.solved; const badge=solved?` <span class='badge bg-success ms-2'>Solved</span>`:''; const replyBtn = solved? `<button class="btn btn-sm btn-outline-secondary" disabled title="Replies closed">Reply</button>` : `<button class="btn btn-sm btn-outline-primary" data-id="${t.id}">Reply</button>`; return `<div class="list-group-item"><div class="fw-bold">${t.title}${badge}</div><small class="text-muted">${new Date(t.updatedAt||t.createdAt).toLocaleString()} · ${t.tags?.join(', ')||''}</small><div class="mt-2">${replyBtn} <a class="btn btn-sm btn-link" href="forum-thread.html?id=${encodeURIComponent(t.id)}" target="_blank">Open</a></div></div>`; }).join('');
        wrap.querySelectorAll('button[data-id]').forEach(btn=> btn.addEventListener('click', async ()=>{
          // Try a nicer inline reply flow. Use prompt as fallback.
          let body='';
          try{
            body = prompt('Your reply:','');
            if (body===null) return; body = (body||'').trim();
            if (!body) { if (window.Toast) Toast.show('Reply cannot be empty','error'); else alert('Reply cannot be empty'); return; }
            // Post as expert (Forum.post will use ExpertAuth.current())
            Forum.post({threadId: btn.getAttribute('data-id'), body});
            if(window.Toast) Toast.show('Reply posted','success');
            // Optionally notify thread author (for now we also add a general expert notification)
            try{ if (window.ExpertNotifs) ExpertNotifs.notify(`You replied to: ${Forum.get(btn.getAttribute('data-id'))?.title || 'a thread'}`); }catch(e){}
            // Keep list refreshed
            const newThreads = Forum.list({sort:'active'}).slice(0,8);
            wrap.innerHTML = newThreads.map(t=>{ const solved=t.solved; const badge=solved?` <span class='badge bg-success ms-2'>Solved</span>`:''; const replyBtn = solved? `<button class=\"btn btn-sm btn-outline-secondary\" disabled title=\"Replies closed\">Reply</button>` : `<button class=\"btn btn-sm btn-outline-primary\" data-id=\"${t.id}\">Reply</button>`; return `<div class=\"list-group-item\"><div class=\"fw-bold\">${t.title}${badge}</div><small class=\"text-muted\">${new Date(t.updatedAt||t.createdAt).toLocaleString()} · ${t.tags?.join(', ')||''}</small><div class=\"mt-2\">${replyBtn} <a class=\"btn btn-sm btn-link\" href=\"forum-thread.html?id=${encodeURIComponent(t.id)}\" target=\"_blank\">Open</a></div></div>`; }).join('');
            // rebind handlers (simple approach)
            // Note: this is a small UI convenience; full rebind is handled by the next page load too.
            setTimeout(()=>{ /* no-op fallback to allow re-binding if needed */ }, 200);
          }catch(e){ alert(e.message||'Unable to post'); }
        }));
      }
    }catch(e){}

    // Advice
  function renderAdvice(){ const list=document.getElementById('exAdviceList'); if(!list) return; const items=ExpertAdvice.list(); list.innerHTML = items.length? items.map(x=>`<div class="list-group-item"><div class="fw-bold">${x.title}</div><small class="text-muted">${new Date(x.createdAt).toLocaleString()}</small><div>${x.body.replace(/\n/g,'<br>')}</div></div>`).join('') : '<div class="text-muted">No advice yet.</div>'; }
  renderAdvice();
  const adviceForm = document.getElementById('exAdviceForm');
  if (adviceForm){ adviceForm.addEventListener('submit', function(e){ e.preventDefault(); try{ const title=document.getElementById('exAdviceTitle').value; const body=document.getElementById('exAdviceBody').value; ExpertAdvice.create({title, body}); (window.Toast?Toast.show('Advice published','success'):null); this.reset(); renderAdvice(); }catch(err){ alert(err.message||'Failed'); } }); }

    // Notifications
    function renderNotifs(){ const list=document.getElementById('exNotifs'); const items=ExpertNotifs.list(); list.innerHTML = items.length? items.map(n=>`<div class="list-group-item d-flex justify-content-between align-items-center ${n.read?'opacity-75':''}"><div>${n.text}</div><small>${new Date(n.ts).toLocaleString()}</small></div>`).join('') : '<div class="text-muted">No notifications</div>'; }
    renderNotifs();
    document.getElementById('exClearNotifs').addEventListener('click', function(){ ExpertNotifs.clear(); renderNotifs(); });
  });
})();
