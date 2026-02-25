(function(){
  const STORAGE_KEY='plantixChatbotHistory_v1';
  const ENABLED_KEY='plantixChatbotEnabled';
  const SESSION_KEY='plantixChatSession_v1';
  const OPEN_KEY='plantixChatOpen_v1';
  const DRAFT_KEY='plantixChatDraft_v1';

  function loadHistory(){
    try{ return JSON.parse(localStorage.getItem(STORAGE_KEY)||'[]'); }catch(e){ return []; }
  }
  function saveHistory(h){ localStorage.setItem(STORAGE_KEY, JSON.stringify(h||[])); }

  function loadSession(){ try{ return JSON.parse(localStorage.getItem(SESSION_KEY)||'{}'); }catch(e){ return {}; } }
  function saveSession(s){ localStorage.setItem(SESSION_KEY, JSON.stringify(s||{})); }
  function resetSession(){ saveSession({}); }

  // Simple dummy knowledge base (no backend)
  const KB=[
    // Specific agri Q&A intents
    { q:/(wheat).*(punjab).*(sow|sowing|plant)/i, a:'In Punjab, sow Wheat in Oct–Nov. Aim for ~20–25°C at sowing, 22.5 cm row spacing, and ~50–60 kg seed/acre.' },
    { q:/(soil).*(fertility|health).*(improve|increase|boost)|improve soil fertility/i, a:'Add compost/FYM, rotate with legumes, use cover crops, avoid over‑tillage, and fertilize as per soil test.' },
    { q:/\bnpk\b|nitrogen.*phosph.*potas|what is npk/i, a:'NPK = Nitrogen–Phosphorus–Potassium. Example: 10‑26‑26 has 10% N, 26% P₂O₅, 26% K₂O by weight.' },
    { q:/(save|conserve).*(water)|irrigation.*water.*(save|less)/i, a:'Irrigate early/late in the day, mulch, fix leaks, use furrows/drip, and water only at crop‑critical stages.' },
    { q:/(nitrogen).*(deficien|shortage)|older leaves.*yellow/i, a:'Nitrogen deficiency: older leaves yellow first and plants are stunted. Apply recommended N in split doses.' },
    { q:/(tomato).*(blight|late blight)/i, a:'For tomato blight: ensure airflow, avoid wet foliage, stake plants, remove infected leaves, and use preventives as labeled.' },
    { q:/(pesticide|spray).*(safe|safety|ppe)/i, a:'Wear PPE, read the label, follow dose/PHI/REI, avoid windy hours, and never exceed recommended dose.' },
    { q:/(grain|wheat|rice).*(moisture).*(store|storage)/i, a:'Dry to ~12–14% grain moisture for safe storage; use clean, airtight bins and keep off the floor.' },
    { q:/\bcrop rotation\b|rotate crops/i, a:'Crop rotation breaks pest cycles, balances nutrients, and improves soil structure—alternate cereals, legumes, and broadleaf crops.' },
    { q:/(maize|corn).*(irrigation|water).*(stage)/i, a:'Maize critical irrigations: knee‑high, tasseling/silking, and grain filling. Avoid stress at these stages.' },
    { q:/(rice|paddy).*(transplant|sow|plant).*(when|month)/i, a:'Rice transplanting in many plains is June–July; maintain 2–5 cm water in vegetative phase and drain before harvest.' },
    { q:/(soil).*(pH).*(check|test)/i, a:'Use a soil test kit or lab for accurate pH; ideal ranges depend on crop (many prefer 6.0–7.5). Amend with lime (low pH) or gypsum/OM as needed.' },
    { q:/(seed).*(treat|treatment)/i, a:'Treat seed with recommended fungicide/inoculant before sowing; follow label and ensure even coating.' },
    { q:/\b(hi|hello|hey)\b/i, a:'Hello! I\'m Plantix Assistant. How can I help you today?' },
    { q:/\b(weather|forecast)\b/i, a:'You can see live weather in the top marquee. Want to change cities? Use: WeatherMarquee.setCities([\'Lahore\', \'Karachi\']); WeatherMarquee.refresh();' },
    { q:/\b(fertilizer|npk|dose|nutrients?)\b/i, a:'Use the Fertilizer Recommendation page: enter NPK, pH and rainfall to get a plan. I can also give basics if you tell me the crop.' },
    { q:/\b(disease|pest|blight|rust|curl|identif(y|ication))\b/i, a:'Open Disease Identification, upload a clear leaf photo, and I\'ll suggest likely issues and remedies.' },
    { q:/\b(order|cancel|return)\b/i, a:'Orders can be cancelled within 1 minute after placing and returns can be requested later from your account orders page.' },
    { q:/\b(ship(ping)?|deliver(y|ies)?|tracking)\b/i, a:'Shipping is typically 2–5 days. You\'ll see tracking in your Orders page once dispatched.' },
    { q:/\bpromo|coupon\b/i, a:'Apply your promo code during checkout—your discount appears in the order summary.' },
    { q:/\bappointment|booking\b/i, a:'Book an appointment from the Appointments page. You can reschedule or cancel from your account.' },
    { q:/\bforum|discussion\b/i, a:'Visit the Forum from the navbar. Vote on replies and mark solutions on helpful answers.' },
    { q:/\breset|password|forgot\b/i, a:'Use the Forgot Password page. The demo reset code is “PLANTIX”.' },
    { q:/\bstripe|payment|pay\b/i, a:'Payments are simulated via our Stripe-like page. No real charges are made.' },
    { q:/\breview|rating|stars?\b/i, a:'On product pages, leave a rating and comment. Your feedback updates averages shown in the shop.' },
    { q:/\b(contact|support|help|email|phone)\b/i, a:'You can reach us at info@plantixai.com or use the Contact page form. I\'m happy to help here too.' },
    { q:/\b(cart|checkout|buy|purchase)\b/i, a:'Add items to cart and proceed to Checkout; you\'ll confirm address and payment (simulated) before placing the order.' },
    { q:/.*/, a:'I\'m still learning. Ask about weather, fertilizer, disease ID, orders, shipping, promo codes, appointments, forum, password reset, reviews—or type a crop name to get a grow plan.' }
  ];

  function replyFor(text){
    const t=(text||'').trim();
    const item=KB.find(x=>x.q.test(t))||KB[KB.length-1];
    return item.a;
  }

  function nowTime(){
    const d=new Date();
    return d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
  }

  function createEl(tag, cls){ const el=document.createElement(tag); if(cls) el.className=cls; return el; }

  function showSnackbar(message, options){
    // options: { undoText?: string, onUndo?: fn, timeout?: ms }
    const existing=document.querySelector('.plantix-snackbar');
    if (existing) existing.remove();
    const bar=createEl('div','plantix-snackbar');
    bar.innerHTML=`<span>${message}</span>`;
    if (options && options.onUndo){
      const b=createEl('button'); b.textContent=options.undoText||'Undo';
      b.addEventListener('click', ()=>{ try{ options.onUndo(); }catch(e){} bar.remove(); });
      bar.appendChild(b);
    }
    const close=createEl('button','secondary'); close.textContent='Dismiss'; close.addEventListener('click',()=>bar.remove());
    bar.appendChild(close);
    document.body.appendChild(bar);
    setTimeout(()=>{ try{ bar.remove(); }catch(e){} }, (options && options.timeout)||5000);
  }

  function renderMsg(container, who, text, opts){
    const wrap=createEl('div', 'msg '+who);
    if (opts && opts.html) { wrap.innerHTML = text; }
    else { wrap.textContent=text; }
    container.appendChild(wrap);
    const time=createEl('span','time'); time.textContent=nowTime(); wrap.appendChild(time);
    container.scrollTop=container.scrollHeight;
  }

  function typingNode(){
    const n=createEl('div','msg bot');
    const t=createEl('span','typing');
    t.innerHTML='<span class="dot"></span><span class="dot"></span><span class="dot"></span>';
    n.appendChild(t);
    return n;
  }

  function mountWidget(){
    if (document.querySelector('.plantix-chatbot')) return; // already mounted
    // Toggle button
    const btn=createEl('button','plantix-chatbot-toggle');
    btn.setAttribute('aria-label','Open chatbot');
    btn.innerHTML='<i class="fas fa-comments"></i>';
    document.body.appendChild(btn);
    // attention pulse until interacted
    btn.classList.add('attention');

    // Panel
    const panel=createEl('div','plantix-chatbot hidden');
  const header=createEl('div','pcb-header');
  header.innerHTML='<div class="pcb-title"><i class="fas fa-seedling"></i> Plantix Assistant</div>';
  const actions=createEl('div','actions');
  const clearBtn=createEl('button'); clearBtn.classList.add('danger'); clearBtn.setAttribute('title','Clear chat'); clearBtn.setAttribute('aria-label','Clear chat'); clearBtn.innerHTML='<i class="fas fa-trash"></i>';
  const closeBtn=createEl('button'); closeBtn.setAttribute('title','Close'); closeBtn.setAttribute('aria-label','Close'); closeBtn.innerHTML='<i class="fas fa-times"></i>';
    actions.appendChild(clearBtn); actions.appendChild(closeBtn);
    header.appendChild(actions);
  const body=createEl('div','pcb-body');
  // Hero prompt with farmer avatar
  const hero=createEl('div','pcb-hero');
  const avatar=createEl('div','pcb-avatar'); avatar.innerHTML='<i class="fas fa-hat-cowboy"></i>';
  const heroText=createEl('div','pcb-hero-text'); heroText.innerHTML='<strong>What do you want me to grow?</strong><div class="sub">Tell me a crop name and I\'ll guide you.</div>';
  hero.appendChild(avatar); hero.appendChild(heroText);
  body.appendChild(hero);

  // Removed suggestion chips per request; we'll show a FAQ list instead in the stream.

  // Message stream
  const stream=createEl('div','pcb-stream');
  body.appendChild(stream);

  // Chat input (bottom)
  const inputBar=createEl('div','pcb-input');
  const input=createEl('input'); input.placeholder='Ask anything or type a crop name (e.g., Wheat)'; input.type='text';
  const send=createEl('button'); send.innerHTML='<i class="fas fa-paper-plane"></i>';
  inputBar.appendChild(input); inputBar.appendChild(send);
  panel.appendChild(header); panel.appendChild(body); panel.appendChild(inputBar);
    document.body.appendChild(panel);

  function open(){ panel.classList.remove('hidden'); input.focus(); hideNudge(); btn.classList.remove('attention'); localStorage.setItem(OPEN_KEY,'1'); }
    function close(){ panel.classList.add('hidden'); localStorage.setItem(OPEN_KEY,'0'); }
  btn.addEventListener('click',()=>{ if(panel.classList.contains('hidden')) open(); else close(); });
    closeBtn.addEventListener('click', close);
    // Clear with confirm + undo
    clearBtn.addEventListener('click', ()=>{
      const has=loadHistory();
      if (!has.length && !Object.keys(loadSession()).length){ return; }
      const ok = window.confirm('Clear chat history?');
      if (!ok) return;
      const prevHist=loadHistory(); const prevSession=loadSession();
      saveHistory([]); resetSession(); stream.innerHTML=''; seedIntro();
      showSnackbar('Chat cleared', { onUndo: ()=>{ saveHistory(prevHist); saveSession(prevSession); stream.innerHTML=''; replayHistory(stream, prevHist); if(stream.children.length===0) seedIntro(); } });
    });

    // Frequently asked questions (displayed on first load)
    const FAQ=[
      {q:'Best time to sow Wheat in Punjab?', a:'October–November. Maintain ~20–25°C at sowing, 22.5 cm row spacing, seed rate ~50–60 kg/acre.'},
      {q:'How do I improve soil fertility sustainably?', a:'Add compost/FYM, practice crop rotation with legumes, use cover crops, and apply fertilizer based on soil test results.'},
      {q:'What does NPK mean on fertilizers?', a:'It stands for Nitrogen–Phosphorus–Potassium. Example: 10‑26‑26 has 10% N, 26% P₂O₅, 26% K₂O by weight.'},
      {q:'How to save water in irrigation?', a:'Irrigate early morning/evening, use mulching, fix leaks, prefer furrow/drip where possible, and water at crop‑critical stages.'},
      {q:'Common nitrogen deficiency symptom?', a:'Uniform yellowing of older leaves first; stunted growth. Apply recommended nitrogen split doses.'},
      {q:'How to manage tomato blight risk?', a:'Ensure airflow, avoid wet foliage, stake plants, and apply preventives when weather is cool and humid as per label.'},
      {q:'Safe pesticide use tips?', a:'Read the label, wear PPE, follow PHI/REI, avoid windy hours, and never exceed recommended dose.'},
      {q:'Optimal grain moisture for storage?', a:'Cereals around 12–14% moisture; dry thoroughly and store in clean, airtight bins.'},
      {q:'What is crop rotation and why?', a:'Growing different crops in sequence to break pest/disease cycles, balance nutrients, and improve soil structure.'},
      {q:'When should I irrigate Maize?', a:'Critical stages: knee‑high, tasseling/silking, and grain filling. Avoid water stress at these stages.'}
    ];

    function faqHtml(){
      return '<div class="pcb-faq"><div class="faq-title"><i class="fas fa-question-circle"></i> Popular questions</div>'+
        FAQ.map(it=>`<div class="faq-item"><div class="faq-q">Q. ${it.q}</div><div class="faq-a">A. ${it.a}</div></div>`).join('')+
        '<div class="faq-foot">Tip: Ask me anything (e.g., “best fertilizer for rice?” or type a crop like “Wheat” to get a grow plan).</div></div>';
    }

    function seedIntro(){
      if (stream.children.length===0){
        renderMsg(stream,'bot','Hi farmer! I\'m your Plantix Assistant.');
        renderMsg(stream,'bot', faqHtml(), {html:true});
        const hh=loadHistory(); hh.push({who:'bot', text:stripHtml(faqHtml()), ts:Date.now(), html:true}); saveHistory(hh);
      }
    }

    function isCropName(txt){
      const t=(txt||'').toLowerCase();
      return /(wheat|rice|cotton|maize|corn|sugarcane|tomato|potato|onion|chickpea|soybean)\b/.test(t);
    }

    function sendGeneral(text){
      const txt=String(text||'').trim(); if(!txt) return;
      const h=loadHistory(); h.push({who:'user', text:txt, ts:Date.now()}); saveHistory(h);
      renderMsg(stream,'user',txt);
      const ans=replyFor(txt); const tNode=typingNode(); stream.appendChild(tNode); stream.scrollTop=stream.scrollHeight;
      setTimeout(()=>{ tNode.remove(); renderMsg(stream,'bot',ans); const hh=loadHistory(); hh.push({who:'bot', text:ans, ts:Date.now()}); saveHistory(hh); }, 300);
    }

    function startCropFlow(crop){
      resetSession(); const s={ crop:String(crop), region:null, month:null }; saveSession(s);
      const h=loadHistory(); h.push({who:'user', text:s.crop, ts:Date.now()}); saveHistory(h);
      renderMsg(stream,'user',s.crop); askRegion(s.crop);
    }

    function handleSend(textFromChip){
      const txt=(textFromChip!=null? String(textFromChip): input.value).trim();
      if(!txt) return; input.value='';
      try{ localStorage.setItem(DRAFT_KEY,''); }catch(e){}
      const h=loadHistory(); h.push({who:'user', text:txt, ts:Date.now()}); saveHistory(h);
      renderMsg(stream,'user',txt);

      const session = Object.assign({},{ crop:null, region:null, month:null }, loadSession());

      // Step resolution based on current session state
      if (!session.crop){
        if (!isCropName(txt)){
          const ans=replyFor(txt); const tNode=typingNode(); stream.appendChild(tNode); stream.scrollTop=stream.scrollHeight;
          setTimeout(()=>{ tNode.remove(); renderMsg(stream,'bot',ans); const hh=loadHistory(); hh.push({who:'bot', text:ans, ts:Date.now()}); saveHistory(hh); }, 300);
          return;
        }
        session.crop = txt; saveSession(session);
        askRegion(session.crop);
        return;
      }
      if (session.crop && !session.region){
        const reg = detectRegion(txt);
        if (reg){ session.region=reg; saveSession(session); askMonth(session.crop, reg); return; }
        // If not detected, ask again
        askRegion(session.crop, true); return;
      }
      if (session.crop && session.region && !session.month){
        const mo = detectMonth(txt, session.crop);
        if (mo){ session.month=mo; saveSession(session); finalizePlan(session); return; }
        askMonth(session.crop, session.region, true); return;
      }
      // If session complete, reset on any new message and treat as new crop
      resetSession();
      const s2={ crop: txt, region:null, month:null }; saveSession(s2);
      askRegion(txt);
    }

    // Dynamic prompt: ask for region
    function askRegion(crop, retry){
      const tNode=typingNode(); stream.appendChild(tNode); stream.scrollTop=stream.scrollHeight;
      setTimeout(()=>{
        tNode.remove();
        const regions=['Punjab','Sindh','KPK','Balochistan'];
        const html = `<div class="chip-group"><div><strong>${retry? 'Please choose a region:' : 'Which region are you in?'}</strong></div>`+
          regions.map(r=>`<button class="chip-btn" data-act="choose-region" data-val="${r}">${r}</button>`).join('')+
          `</div>`;
        renderMsg(stream,'bot',html,{html:true});
        const hh=loadHistory(); hh.push({who:'bot', text:stripHtml(html), ts:Date.now()}); saveHistory(hh);
      }, 350);
    }

    function askMonth(crop, region, retry){
      const tNode=typingNode(); stream.appendChild(tNode); stream.scrollTop=stream.scrollHeight;
      setTimeout(()=>{
        tNode.remove();
        const months = monthOptionsFor(crop);
        const html = `<div class="chip-group"><div><strong>${retry? 'Pick a sowing month:' : 'Great! Pick a sowing month:'}</strong></div>`+
          months.map(m=>`<button class="chip-btn" data-act="choose-month" data-val="${m}">${m}</button>`).join('')+
          `</div>`;
        renderMsg(stream,'bot',html,{html:true});
        const hh=loadHistory(); hh.push({who:'bot', text:stripHtml(html), ts:Date.now()}); saveHistory(hh);
      }, 350);
    }

    function finalizePlan(session){
      const tNode=typingNode(); stream.appendChild(tNode); stream.scrollTop=stream.scrollHeight;
      setTimeout(()=>{
        tNode.remove();
        const ansHtml = suggestForCrop(session.crop, { region: session.region, month: session.month });
        const suffix = `<div class="chip-group" style="margin-top:6px"><button class="chip-btn" data-act="restart">Start over</button></div>`;
        renderMsg(stream,'bot',ansHtml+suffix,{html:true});
        const hh=loadHistory(); hh.push({who:'bot', text:stripHtml(ansHtml), ts:Date.now(), html:true}); saveHistory(hh);
      }, 400);
    }

  send.addEventListener('click', ()=>handleSend(null));
  input.addEventListener('keydown', e=>{ if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); handleSend(null); } else if (e.key==='Escape'){ close(); }});
  // Persist draft
  try{ const d=localStorage.getItem(DRAFT_KEY)||''; if(d){ input.value=d; } }catch(e){}
  input.addEventListener('input', ()=>{ try{ localStorage.setItem(DRAFT_KEY, input.value||''); }catch(e){} });
  // ESC to close globally when panel open
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !panel.classList.contains('hidden')){ close(); }});

    // Delegation for inline chips
    stream.addEventListener('click', (e)=>{
      const btn = e.target.closest && e.target.closest('.chip-btn');
      if (!btn) return;
      const act = btn.getAttribute('data-act');
      const val = btn.getAttribute('data-val');
      if (act==='choose-region'){
        const s=Object.assign({},{ crop:null, region:null, month:null }, loadSession());
        if (!s.crop) return; s.region = val; saveSession(s); askMonth(s.crop, s.region);
      } else if (act==='choose-month'){
        const s=Object.assign({},{ crop:null, region:null, month:null }, loadSession());
        if (!s.crop || !s.region) return; s.month = val; saveSession(s); finalizePlan(s);
      } else if (act==='restart'){
        resetSession(); stream.innerHTML=''; seedIntro();
      }
    });

    // expose minimal API
  window.PlantixChatbot={ open, close, clear: ()=>{ const prevHist=loadHistory(); const prevSession=loadSession(); saveHistory([]); resetSession(); stream.innerHTML=''; seedIntro(); showSnackbar('Chat cleared', { onUndo: ()=>{ saveHistory(prevHist); saveSession(prevSession); stream.innerHTML=''; replayHistory(stream, prevHist); if(stream.children.length===0) seedIntro(); } }); } };

    // first-load nudge badge
    let nudgeEl=null; const NUDGE_KEY='plantixChatbotNudged_v1';
    function showNudge(){
      if (localStorage.getItem(NUDGE_KEY)==='1') return;
      if (nudgeEl) return;
      nudgeEl=createEl('div','plantix-chatbot-nudge');
      nudgeEl.textContent='Need help? Chat with us';
      document.body.appendChild(nudgeEl);
      setTimeout(()=>{ hideNudge(); }, 7000);
    }
    function hideNudge(){
      if (nudgeEl){ nudgeEl.remove(); nudgeEl=null; localStorage.setItem(NUDGE_KEY,'1'); }
    }
    // show after a short delay
    setTimeout(showNudge, 1800);

    // On first mount: restore history and open state
    function replayHistory(container, hist){
      (hist||[]).forEach(item=>{ renderMsg(container, item.who||'bot', item.text||'', { html:false }); });
      if (container.children.length===0) seedIntro();
    }
    replayHistory(stream, loadHistory());
    try{ if (localStorage.getItem(OPEN_KEY)==='1'){ open(); } }catch(e){}
  }

  function ensureAssets(){
    // Load CSS only once
    if (!document.querySelector('link[data-plantix-chatbot]')){
      const l=document.createElement('link'); l.rel='stylesheet'; l.href='assets/css/chatbot.css'; l.setAttribute('data-plantix-chatbot','1'); document.head.appendChild(l);
    }
  }

  function init(){
    try{
      // URL override: ?chat=1 to force-enable, ?chat=0 to disable
      try{
        const qs=new URLSearchParams(location.search||'');
        if (qs.has('chat')){
          const v=(qs.get('chat')||'').toLowerCase();
          if (v==='0' || v==='false' || v==='off' || v==='no'){
            localStorage.setItem(ENABLED_KEY,'false');
          } else {
            localStorage.setItem(ENABLED_KEY,'true');
          }
        }
      }catch(e){}

      // Allow disabling via storage for troubleshooting
      const enabled = localStorage.getItem(ENABLED_KEY);
      if (enabled==='false') return;
      ensureAssets();
      mountWidget();
    }catch(e){}
  }

  if (document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

// Crop suggestion engine (very lightweight, static)
function suggestForCrop(raw, ctx){
  const t=(raw||'').toLowerCase();
  const region = ctx && ctx.region ? ctx.region : null;
  const month = ctx && ctx.month ? ctx.month : null;
  function block(title, lines){
    const rLine = region ? `Region: ${region}` : null;
    const mLine = month ? `Target sowing: ${month}` : null;
    const extra = [rLine,mLine].filter(Boolean);
    const all = extra.length? lines.concat(['']).concat(extra): lines;
    return `<div><strong>${title}</strong><br>${all.map(l=>`• ${l}`).join('<br>')}</div>`;
  }
  function links(){
    return `<div style="margin-top:6px">Related: <a href="crop-planning.html">Crop Planning</a> · <a href="fertilizer-recommendation.html">Fertilizer</a> · <a href="disease-identification.html">Disease ID</a></div>`;
  }
  if (/wheat|gandum/.test(t)){
    return block('Wheat (Pakistan)', [
      'Sowing: Oct–Nov (Punjab); temp ~20–25°C',
      'Spacing: ~22.5 cm rows; seed rate 50–60 kg/acre',
      'Fertilizer: ~120–100–60 kg/ha (N–P–K) split doses',
      'Irrigation: CRI, tillering, heading stages critical'
    ]) + links();
  }
  if (/rice|paddy/.test(t)){
    return block('Rice', [
      'Transplant: June–July in many plains',
      'Fertilizer: Balanced NPK based on soil test; split N',
      'Water: Keep field flooded 2–5 cm in vegetative phase',
      'Watch for BLB, blast; use resistant varieties'
    ]) + links();
  }
  if (/maize|corn/.test(t)){
    return block('Maize', [
      'Sowing: Spring (Feb–Mar) or Autumn (Aug–Sep)',
      'Spacing: 60–75 cm rows; 20–25 cm plant spacing',
      'Fertilizer: N in splits; ensure P and K at sowing',
      'Irrigation: Critical at tasseling and grain fill'
    ]) + links();
  }
  if (/cotton/.test(t)){
    return block('Cotton', [
      'Sowing: April–May (temperature above 20°C)',
      'Use Bt/resistant varieties where suitable',
      'Fertilizer: Balanced NPK + micronutrients as needed',
      'Scout pests (bollworm, whitefly) weekly'
    ]) + links();
  }
  if (/sugarcane|ganna/.test(t)){
    return block('Sugarcane', [
      'Planting: Autumn (Sep–Oct) or Spring (Feb–Mar)',
      'Setts: Healthy 2–3 bud pieces; treat before planting',
      'Irrigation: Frequent early; avoid waterlogging',
      'NPK: Follow soil test; split nitrogen applications'
    ]) + links();
  }
  // Fallback generic guidance
  return block('Let’s plan your crop', [
    'Tell me the crop name (e.g., Wheat, Rice, Maize, Cotton, Sugarcane)',
    'I’ll suggest sowing window, spacing, fertilizer and key practices'
  ]) + links();
}

function monthOptionsFor(crop){
  const c=(crop||'').toLowerCase();
  if (/wheat|gandum/.test(c)) return ['Oct','Nov','Dec'];
  if (/rice|paddy/.test(c)) return ['Jun','Jul','Aug'];
  if (/maize|corn/.test(c)) return ['Feb','Mar','Aug','Sep'];
  if (/cotton/.test(c)) return ['Apr','May','Jun'];
  if (/sugarcane|ganna/.test(c)) return ['Sep','Oct','Feb','Mar'];
  return ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
}

function detectRegion(text){
  const t=(text||'').toLowerCase();
  if (/punjab/.test(t)) return 'Punjab';
  if (/sindh/.test(t)) return 'Sindh';
  if (/(kpk|khyber|khyber pakhtunkhwa|kp|kpk|kpk)/.test(t)) return 'KPK';
  if (/bal(o)?chistan/.test(t)) return 'Balochistan';
  return null;
}

function detectMonth(text){
  const t=(text||'').toLowerCase();
  const map = { jan:'Jan', feb:'Feb', mar:'Mar', apr:'Apr', may:'May', jun:'Jun', jul:'Jul', aug:'Aug', sep:'Sep', sept:'Sep', oct:'Oct', nov:'Nov', dec:'Dec' };
  for (const k in map){ if (new RegExp(`\\b${k}\\b`).test(t)) return map[k]; }
  return null;
}

function stripHtml(s){ const div=document.createElement('div'); div.innerHTML=s; return (div.textContent||'').trim(); }
