// Appointments pages logic
(function(){
  function qs(s){ return document.querySelector(s); }
  function fmtDateTime(ms){ try{ const d=new Date(ms); return d.toLocaleString(); }catch(e){ return '-'; } }
  function getParam(k){ const u=new URL(location.href); return u.searchParams.get(k); }

  document.addEventListener('DOMContentLoaded', function(){
    wireList();
    wireBook();
    wireDetails();
  });

  function guard(){ if (!window.Auth || !Auth.current()){ window.location.href='signin.html'; return false; } return true; }

  // List page
  function wireList(){
    const container = qs('#appointments-page'); if(!container) return;
    if(!guard()) return;
    const tbody = qs('#appointmentsTableBody');
    const appts = Auth.getAppointments();
    if (!tbody) return;
    if (appts.length===0){ tbody.innerHTML = '<tr><td colspan="6">No appointments yet.</td></tr>'; return; }
    tbody.innerHTML = appts.map(a=>{
      const actions = [
        a.status!=='Cancelled' ? `<button class="btn btn-sm btn-outline-secondary me-1" data-action="reschedule" data-id="${a.id}">Reschedule</button>` : '',
        a.status!=='Cancelled' ? `<button class="btn btn-sm btn-outline-danger" data-action="cancel" data-id="${a.id}">Cancel</button>` : ''
      ].join('');
      const exName = a.assignedExpertName || (window.ExpertDirectory? (a.assignedExpertId? ExpertDirectory.getName(a.assignedExpertId):'Any available') : (a.assignedExpertId?'Assigned expert':'Any available'));
      return `<tr>
        <td><a href="appointment-details.html?id=${encodeURIComponent(a.id)}">${a.id}</a></td>
        <td>${fmtDateTime(a.dateTime)}</td>
        <td>${a.type||'-'}</td>
        <td>${exName||'-'}</td>
        <td>${a.status||'-'}</td>
        <td>${actions}</td>
      </tr>`;
    }).join('');
    tbody.querySelectorAll('button[data-action="cancel"]').forEach(btn=>{
      btn.addEventListener('click', function(){ const id=this.getAttribute('data-id'); const proceed=window.Dialog?Dialog.confirm({title:'Cancel appointment', message:'Are you sure you want to cancel this appointment?'}):Promise.resolve(confirm('Cancel this appointment?')); proceed.then(ok=>{ if(!ok) return; try{ Auth.cancelAppointment(id); (window.Toast?Toast.show('Appointment cancelled','success'):alert('Cancelled')); wireList(); }catch(e){ (window.Toast?Toast.show(e.message||'Unable to cancel','error'):alert(e.message||'Unable to cancel')); } }); });
    });
    tbody.querySelectorAll('button[data-action="reschedule"]').forEach(btn=>{
      btn.addEventListener('click', function(){ const id=this.getAttribute('data-id'); const ask=window.Dialog?Dialog.prompt({title:'Reschedule', message:'Enter new date and time (YYYY-MM-DD HH:MM)', placeholder:'2025-10-25 14:30'}):Promise.resolve(prompt('Enter new date and time (YYYY-MM-DD HH:MM)')); ask.then(val=>{ if(val===null) return; const ms=toMs(val); if(!ms){ (window.Toast?Toast.show('Invalid date/time','error'):alert('Invalid date/time')); return;} try{ Auth.rescheduleAppointment(id, ms); (window.Toast?Toast.show('Appointment rescheduled','success'):alert('Rescheduled')); wireList(); }catch(e){ (window.Toast?Toast.show(e.message||'Unable to reschedule','error'):alert(e.message||'Unable to reschedule')); } }); });
    });
  }

  function toMs(s){ try{ if(!s) return 0; if(/T/.test(s)) return Date.parse(s); const parts=s.trim().split(/\s+/); const d=parts[0]; const t=parts[1]||'00:00'; return Date.parse(`${d}T${t}`); }catch(e){ return 0; } }

  // Book page
  function wireBook(){
    const container = qs('#appointment-book-page'); if(!container) return; if(!guard()) return;
    // Prefill date to tomorrow
    const d=new Date(); d.setDate(d.getDate()+1); const dateStr=d.toISOString().slice(0,10); const timeStr='10:00';
    const dateInput=qs('#apptDate'); const timeInput=qs('#apptTime'); if(dateInput) dateInput.value=dateStr; if(timeInput) timeInput.value=timeStr;
    // Prefill address from profile
    const u=Auth.current()||{}; const a=u.address||{}; const line1=qs('#apptAddress1'); const line2=qs('#apptAddress2'); if(line1) line1.value=a.line1||''; if(line2) line2.value=a.line2||'';
    // Populate experts dropdown (available only)
    try{
      const sel = qs('#apptExpert');
      if (sel && window.ExpertDirectory){
        const experts = ExpertDirectory.listAvailable();
        // Reset options (keep placeholder if present)
        const placeholder = sel.querySelector('option[value=""]');
        sel.innerHTML = '';
        if (placeholder){ sel.appendChild(placeholder); }
        (experts||[]).forEach(e=>{
          const opt=document.createElement('option');
          opt.value = e.id;
          const specs = Array.isArray(e.specialties)? e.specialties.join(', ') : '';
          opt.textContent = specs? `${e.name} — ${specs}` : e.name;
          sel.appendChild(opt);
        });
        if (!experts || experts.length===0){
          const opt=document.createElement('option'); opt.disabled=true; opt.textContent='No experts available'; sel.appendChild(opt); sel.setAttribute('data-empty','1');
        } else {
          sel.removeAttribute('data-empty');
        }
      }
    }catch(e){}
    qs('#appointment-form')?.addEventListener('submit', function(e){ e.preventDefault();
      const type=qs('#apptType').value; const channel=qs('#apptChannel').value; const date=qs('#apptDate').value; const time=qs('#apptTime').value; const notes=(qs('#apptNotes').value||'').trim();
      const addr1=qs('#apptAddress1').value||''; const addr2=qs('#apptAddress2').value||'';
      const expertSel = qs('#apptExpert'); const selectedExpertId = expertSel? (expertSel.value||'') : '';
      // Validate date/time and not in the past
      const ms = toMs(`${date} ${time}`);
      if (!ms){ (window.Toast?Toast.show('Please enter a valid date/time','error'):alert('Please enter a valid date/time')); return; }
      if (ms < Date.now()) { (window.Toast?Toast.show('Please choose a future date/time','error'):alert('Please choose a future date/time')); return; }
      // Validate address for in-person
      if (channel==='In-Person' && !addr1.trim()) { (window.Toast?Toast.show('Address is required for in-person visits','error'):alert('Address is required for in-person visits')); return; }
      // Expert selection: if none chosen, auto-assign first available
      let assignedExpertId = selectedExpertId; let assignedExpertName='';
      if (window.ExpertDirectory){
        const avail = ExpertDirectory.listAvailable();
        if (!assignedExpertId){ assignedExpertId = (avail&&avail[0]&&avail[0].id) || ''; }
        if (assignedExpertId){ assignedExpertName = ExpertDirectory.getName(assignedExpertId) || ''; }
      }
      // If no experts available at all, block submission
      const selEmpty = expertSel && expertSel.getAttribute('data-empty')==='1';
      if (selEmpty || (!assignedExpertId)){
        (window.Toast?Toast.show('No experts are currently available. Please try again later.','error'):alert('No experts are currently available. Please try again later.'));
        return;
      }
      const appt={ id:'a_'+Date.now(), userId:u.id, userName:u.name||u.email||'Customer', dateTime:ms, type, channel, notes, reason: notes||type, address:{line1:addr1, line2:addr2}, status:'Pending', createdAt:Date.now(), assignedExpertId, assignedExpertName };
  Auth.recordAppointment(appt);
  try{ if (window.ExpertNotifs){ ExpertNotifs.notify(`New appointment request #${appt.id} for ${assignedExpertName||'Any expert'}`); } }catch(e){}
      (window.Toast?Toast.show('Appointment booked','success'):alert('Appointment booked'));
      window.location.href = `appointment-details.html?id=${encodeURIComponent(appt.id)}`;
    });
  }

  // Details page
  function wireDetails(){
    const container = qs('#appointment-details-page'); if(!container) return; if(!guard()) return;
    const id = getParam('id'); if(!id){ window.location.href='appointments.html'; return; }
  const appts = Auth.getAppointments(); const a=appts.find(x=>String(x.id)===String(id)); if(!a){ window.location.href='appointments.html'; return; }
  qs('#ad-id').textContent=a.id; qs('#ad-datetime').textContent=fmtDateTime(a.dateTime); qs('#ad-type').textContent=a.type||'-'; qs('#ad-channel').textContent=a.channel||'-'; qs('#ad-status').textContent=a.status||'-';
  const exName = a.assignedExpertName || (window.ExpertDirectory? (a.assignedExpertId? ExpertDirectory.getName(a.assignedExpertId):'Any available') : (a.assignedExpertId?'Assigned expert':'Any available'));
  const exEl=document.getElementById('ad-expert'); if (exEl) exEl.textContent = exName || '-';
    const u=Auth.current()||{}; const addr=a.address||{}; qs('#ad-address').innerHTML=`${u.name||''}<br>${addr.line1||''}${addr.line2?`, ${addr.line2}`:''}`;
    qs('#ad-notes').textContent=a.notes||'-';
    const canModify = a.status!=='Cancelled';
    const cancelBtn=qs('#ad-cancel'); const resBtn=qs('#ad-reschedule');
    if (canModify){ cancelBtn.classList.remove('hidden'); resBtn.classList.remove('hidden'); }
    cancelBtn?.addEventListener('click', function(){ const proceed=window.Dialog?Dialog.confirm({title:'Cancel appointment', message:'Are you sure you want to cancel this appointment?'}):Promise.resolve(confirm('Cancel this appointment?')); proceed.then(ok=>{ if(!ok) return; try{ Auth.cancelAppointment(a.id); (window.Toast?Toast.show('Appointment cancelled','success'):alert('Cancelled')); location.reload(); }catch(e){ (window.Toast?Toast.show(e.message||'Unable to cancel','error'):alert(e.message||'Unable to cancel')); } }); });
    resBtn?.addEventListener('click', function(){ const ask=window.Dialog?Dialog.prompt({title:'Reschedule', message:'Enter new date and time (YYYY-MM-DD HH:MM)', placeholder:'2025-10-25 14:30'}):Promise.resolve(prompt('Enter new date and time (YYYY-MM-DD HH:MM)')); ask.then(val=>{ if(val===null) return; const ms=toMs(val); if(!ms){ (window.Toast?Toast.show('Invalid date/time','error'):alert('Invalid date/time')); return; } try{ Auth.rescheduleAppointment(a.id, ms); (window.Toast?Toast.show('Appointment rescheduled','success'):alert('Rescheduled')); location.reload(); }catch(e){ (window.Toast?Toast.show(e.message||'Unable to reschedule','error'):alert(e.message||'Unable to reschedule')); } }); });
  }
})();
