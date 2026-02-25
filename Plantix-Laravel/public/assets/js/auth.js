// Auth and Account Simulation (localStorage)
(function(){
  const USERS_KEY='plantixUsers';
  const CURRENT_KEY='plantixCurrentUser';
  const ORDERS_KEY='plantixOrders';
  const APPTS_KEY='plantixAppointments';
  const RESETS_KEY='plantixPasswordResets';
  const STATIC_RESET_TOKEN='PLANTIX';

  const state={
    users: JSON.parse(localStorage.getItem(USERS_KEY)||'[]'),
    current: JSON.parse(localStorage.getItem(CURRENT_KEY)||'null')
  };

  function saveUsers(){ localStorage.setItem(USERS_KEY, JSON.stringify(state.users)); }
  function setCurrent(u){ state.current=u; localStorage.setItem(CURRENT_KEY, JSON.stringify(u)); updateHeaderLink(); }
  function hash(p){ return p; } // mock
  function loadResets(){ return JSON.parse(localStorage.getItem(RESETS_KEY)||'[]'); }
  function saveResets(arr){ localStorage.setItem(RESETS_KEY, JSON.stringify(arr)); }
  function genToken(){ return STATIC_RESET_TOKEN; }

  // Public API
  const Auth={
    signUp({name,email,password,phone}){
      email=(email||'').trim().toLowerCase();
      if (!email || !password) throw new Error('Email and password required');
      if (state.users.find(u=>u.email===email)) throw new Error('Email already registered');
      const user={ id:'u_'+Date.now(), name:name||email, email, password:hash(password), phone:phone||'', address:{}, createdAt:Date.now() };
      state.users.push(user); saveUsers(); setCurrent(user); return user;
    },
    signIn({email,password}){
      email=(email||'').trim().toLowerCase();
      const u=state.users.find(u=>u.email===email && u.password===hash(password));
      if (!u) throw new Error('Invalid credentials');
      setCurrent(u); return u;
    },
    signOut(){ setCurrent(null); },
    current(){ return state.current; },
    updateProfile(patch){ if(!state.current) return; Object.assign(state.current,{name:patch.name||state.current.name, phone:patch.phone||state.current.phone}); state.current.address = Object.assign({}, state.current.address||{}, patch.address||{}); const idx=state.users.findIndex(u=>u.id===state.current.id); if(idx>-1){ state.users[idx]=state.current; saveUsers(); setCurrent(state.current);} },
    getOrders(){ const all=JSON.parse(localStorage.getItem(ORDERS_KEY)||'[]'); if(!state.current) return []; return all.filter(o=>o.userId===state.current.id); },
    recordOrder(order){ const all=JSON.parse(localStorage.getItem(ORDERS_KEY)||'[]'); all.push(order); localStorage.setItem(ORDERS_KEY, JSON.stringify(all)); localStorage.setItem('plantixLastOrderId', order.id); },
    updateOrder(orderId, patch){
      if (!state.current) throw new Error('Not authenticated');
      const all=JSON.parse(localStorage.getItem(ORDERS_KEY)||'[]');
      const idx = all.findIndex(o=>o.id===orderId && o.userId===state.current.id);
      if (idx===-1) throw new Error('Order not found');
      const updated = Object.assign({}, all[idx], patch);
      all[idx]=updated;
      localStorage.setItem(ORDERS_KEY, JSON.stringify(all));
      return updated;
    },
    cancelOrder(orderId){
      if (!state.current) throw new Error('Not authenticated');
      const all=JSON.parse(localStorage.getItem(ORDERS_KEY)||'[]');
      const idx = all.findIndex(o=>o.id===orderId && o.userId===state.current.id);
      if (idx===-1) throw new Error('Order not found');
      const o=all[idx];
      const withinMinute = (Date.now() - o.createdAt) <= 60000;
      if (o.status==='Cancelled') throw new Error('Order already cancelled');
      if (!withinMinute || o.status!=='Processing') throw new Error('Order can no longer be cancelled');
      o.status='Cancelled';
      o.cancelledAt = Date.now();
      all[idx]=o; localStorage.setItem(ORDERS_KEY, JSON.stringify(all));
      return o;
    },
    requestReturn(orderId, reason){
      if (!state.current) throw new Error('Not authenticated');
      const all=JSON.parse(localStorage.getItem(ORDERS_KEY)||'[]');
      const idx = all.findIndex(o=>o.id===orderId && o.userId===state.current.id);
      if (idx===-1) throw new Error('Order not found');
      const o=all[idx];
      if (o.status==='Cancelled') throw new Error('Cancelled orders cannot be returned');
      if (o.returnedAt) throw new Error('Return already requested');
      o.status = 'Return Requested';
      o.returnReason = reason||'';
      o.returnedAt = Date.now();
      all[idx]=o; localStorage.setItem(ORDERS_KEY, JSON.stringify(all));
      return o;
    },
    // Password reset (demo)
    requestPasswordReset(email){
      email=(email||'').trim().toLowerCase();
      const u=state.users.find(u=>u.email===email);
      if (!u) throw new Error('No account found with this email');
      const token = genToken();
      const resets = loadResets().filter(r=>r.email!==email); // invalidate previous
      resets.push({ email, token, expiresAt: Date.now() + 15*60*1000 });
      saveResets(resets);
      return { email, token, expiresInMinutes: 15 };
    },
    resetPassword({email, token, newPassword}){
      email=(email||'').trim().toLowerCase(); token=(token||'').trim().toUpperCase();
      if (!email || !token || !newPassword) throw new Error('All fields are required');
      // Allow static demo token even without a prior request
      let resets = loadResets();
      let rec = resets.find(r=>r.email===email && r.token===token);
      if (token !== STATIC_RESET_TOKEN) {
        if (!rec) throw new Error('Invalid reset token');
        if (Date.now() > rec.expiresAt) throw new Error('Reset token expired');
      } else {
        // If using static token, ensure account exists
        if (!state.users.find(u=>u.email===email)) throw new Error('Account not found');
      }
      const idx=state.users.findIndex(u=>u.email===email);
      if (idx===-1) throw new Error('Account not found');
      state.users[idx].password = hash(newPassword);
      saveUsers();
      // clear used token
      const remaining = (resets||[]).filter(r=> !(r.email===email && r.token===token));
      saveResets(remaining);
      return true;
    },
    // Appointments API
    getAppointments(){ const all=JSON.parse(localStorage.getItem(APPTS_KEY)||'[]'); if(!state.current) return []; return all.filter(a=>a.userId===state.current.id); },
    recordAppointment(appt){ const all=JSON.parse(localStorage.getItem(APPTS_KEY)||'[]'); all.push(appt); localStorage.setItem(APPTS_KEY, JSON.stringify(all)); localStorage.setItem('plantixLastApptId', appt.id); return appt; },
    updateAppointment(apptId, patch){ if(!state.current) throw new Error('Not authenticated'); const all=JSON.parse(localStorage.getItem(APPTS_KEY)||'[]'); const idx=all.findIndex(a=>a.id===apptId && a.userId===state.current.id); if(idx===-1) throw new Error('Appointment not found'); const updated=Object.assign({}, all[idx], patch, {updatedAt: Date.now()}); all[idx]=updated; localStorage.setItem(APPTS_KEY, JSON.stringify(all)); return updated; },
    cancelAppointment(apptId){ if(!state.current) throw new Error('Not authenticated'); const all=JSON.parse(localStorage.getItem(APPTS_KEY)||'[]'); const idx=all.findIndex(a=>a.id===apptId && a.userId===state.current.id); if(idx===-1) throw new Error('Appointment not found'); const a=all[idx]; if (a.status==='Cancelled') throw new Error('Already cancelled'); a.status='Cancelled'; a.cancelledAt=Date.now(); all[idx]=a; localStorage.setItem(APPTS_KEY, JSON.stringify(all)); return a; },
    rescheduleAppointment(apptId, newDateTime){ if(!state.current) throw new Error('Not authenticated'); const all=JSON.parse(localStorage.getItem(APPTS_KEY)||'[]'); const idx=all.findIndex(a=>a.id===apptId && a.userId===state.current.id); if(idx===-1) throw new Error('Appointment not found'); const a=all[idx]; if (a.status==='Cancelled') throw new Error('Cannot reschedule a cancelled appointment'); a.previousDateTime=a.dateTime; a.dateTime=newDateTime; a.status='Rescheduled'; a.rescheduledAt=Date.now(); all[idx]=a; localStorage.setItem(APPTS_KEY, JSON.stringify(all)); return a; }
  };
  window.Auth=Auth;

  // UI: Header link and lightweight navigation compatibility
  document.addEventListener('DOMContentLoaded',()=>{
    // Ensure ExpertAuth is available globally; then update header accordingly
    try{
      if (!window.ExpertAuth && !document.querySelector('script[data-experts]')){
        const s=document.createElement('script'); s.src='assets/js/experts.js'; s.async=true; s.setAttribute('data-experts','1'); s.onload=function(){ try{ updateHeaderLink(); }catch(e){} };
        document.body.appendChild(s);
      }
    }catch(e){}
    updateHeaderLink();
    addAppointmentsNavLink();
    addForumNavLink();
    patchFooterLinks();
    removeLegacyBlogNav();
    injectWeatherMarquee();
    loadChatbot();
    injectValidationScript();
    seedDefaultUser();
  });

  function seedDefaultUser() {
    try {
      const EMAIL = 'customer@gmail.com';
      const PASSWORD = '12345678';
      const exists = state.users.find(u => u.email === EMAIL);
      if (!exists) {
        // Create user without signing in automatically to preserve current session state unless empty
        const user = { 
          id: 'u_' + Date.now(), 
          name: 'Demo Customer', 
          email: EMAIL, 
          password: hash(PASSWORD), 
          phone: '12345678', 
          address: {}, 
          createdAt: Date.now() 
        };
        state.users.push(user); 
        saveUsers();
        console.log('Default user seeded:', EMAIL);
      }
    } catch (e) {
      console.error('Seeding failed', e);
    }
  }

  function injectValidationScript() {
    if (!document.querySelector('script[data-validation]')) {
      const s = document.createElement('script');
      s.src = 'assets/js/strict-validation.js';
      s.async = true;
      s.setAttribute('data-validation', '1');
      document.body.appendChild(s);
    }
  }

  function updateHeaderLink(){
    const link=document.querySelector('.attr-nav .button a');
    if (!link) return;
    // Prefer Expert session if present
    try{
      if (window.ExpertAuth && ExpertAuth.current()){
        link.textContent='Expert Panel';
        link.setAttribute('href','expert-dashboard.html');
        const clone=link.cloneNode(true); link.parentNode.replaceChild(clone, link);
        return;
      }
    }catch(e){}
    if (state.current) {
      link.textContent='Account';
      link.setAttribute('href','account-profile.html');
      // Remove any modal click handlers if present
      const clone=link.cloneNode(true); link.parentNode.replaceChild(clone, link);
    } else {
      link.textContent='Sign In';
      link.setAttribute('href','signin.html');
      const clone=link.cloneNode(true); link.parentNode.replaceChild(clone, link);
    }
  }

  function addAppointmentsNavLink(){
    try{
      const navUl = document.querySelector('#navbar-menu ul.navbar-nav') || document.querySelector('ul.navbar-nav.navbar-right');
      if (!navUl) return;
      const existing = navUl.querySelector('a[href="appointments.html"]');
      if (existing) { setActive(existing); return; }
      const li=document.createElement('li');
      const a=document.createElement('a');
      a.href='appointments.html'; a.textContent='Appointments';
      li.appendChild(a);
      // place after Shop if present
      const shopLi = Array.from(navUl.querySelectorAll('a')).find(x=>/shop\.html$/i.test(x.getAttribute('href')||''));
      if (shopLi && shopLi.parentElement && shopLi.parentElement.parentElement===navUl) {
        shopLi.parentElement.insertAdjacentElement('afterend', li);
      } else {
        navUl.appendChild(li);
      }
      setActive(a);
      function setActive(anchor){ const path=(location.pathname||'').toLowerCase(); const pages=['appointments.html','appointment-book.html','appointment-details.html']; if (pages.some(p=>path.endsWith('/'+p) || path.endsWith(p))) { anchor.classList.add('active'); } }
    }catch(e){}
  }

  function addForumNavLink(){
    try{
      const navUl = document.querySelector('#navbar-menu ul.navbar-nav') || document.querySelector('ul.navbar-nav.navbar-right');
      if (!navUl) return;
      // Replace any legacy blog links with Forum
      Array.from(navUl.querySelectorAll('a')).forEach(a=>{
        const href=(a.getAttribute('href')||'');
        if (/blog-(single-with-sidebar|with-sidebar)\.html$/i.test(href)) { a.setAttribute('href','forum.html'); a.textContent='Forum'; }
      });
      // Avoid duplicates: if a Forum link already exists, just set active and return
      const existingForum = Array.from(navUl.querySelectorAll('a')).find(a=>/forum\.html$/i.test(a.getAttribute('href')||''));
      if (existingForum){ setActive(existingForum); return; }
      // Otherwise add Forum link near Plantix-AI
      const li=document.createElement('li'); const a=document.createElement('a'); a.href='forum.html'; a.textContent='Forum'; li.appendChild(a);
      const after = Array.from(navUl.querySelectorAll('a')).find(x=>/plantix-ai\.html$/i.test(x.getAttribute('href')||''));
      if (after && after.parentElement && after.parentElement.parentElement===navUl){ after.parentElement.insertAdjacentElement('afterend', li);} else { navUl.appendChild(li); }
      setActive(a);
      function setActive(anchor){ const path=(location.pathname||'').toLowerCase(); const pages=['forum.html','forum-thread.html','forum-new.html']; if (pages.some(p=>path.endsWith('/'+p)||path.endsWith(p))) anchor.classList.add('active'); }
    }catch(e){}
  }

  function patchFooterLinks(){
    try{
      const sels = ['footer a[href$="blog-with-sidebar.html"]','footer a[href$="blog-single-with-sidebar.html"]'];
      sels.forEach(sel=>{
        document.querySelectorAll(sel).forEach(a=>{ a.setAttribute('href','forum.html'); a.textContent = a.textContent?.trim()?.toLowerCase()==='news & media' ? 'Forum' : (a.textContent||'Forum'); });
      });
    }catch(e){}
  }

  // Remove legacy blog entries from the navbar entirely if any remain
  function removeLegacyBlogNav(){
    try{
      const navUl = document.querySelector('#navbar-menu ul.navbar-nav') || document.querySelector('ul.navbar-nav.navbar-right');
      if (!navUl) return;
      Array.from(navUl.querySelectorAll('li > a')).forEach(a=>{
        const href = (a.getAttribute('href')||'');
        if (/blog-(single-with-sidebar|with-sidebar)\.html$/i.test(href)){
          const li=a.parentElement; if(li && li.parentElement===navUl){ li.remove(); }
        }
      });
    }catch(e){}
  }

  // Weather marquee injection: replaces opening-hours strip text with a scrolling forecast
  function injectWeatherMarquee(){
    try{
      if (document.querySelector('.top-bar-area .weather-marquee')) return; // already inserted
      const topBar = document.querySelector('.top-bar-area');
      if (!topBar) return;

      const container = document.createElement('div');
      container.className = 'weather-marquee';
      const track = document.createElement('div');
      track.className = 'marquee-track';

      const defaultCities = ['Lahore','Karachi','Islamabad','Multan','Faisalabad'];
      function chip(it){ return `<span class=\"weather-chip\"><i class=\"fas ${it.icon}\"></i> ${it.city}: ${it.temp} • ${it.text}</span>`; }
      function render(items){ track.innerHTML = items.map(chip).join('') + items.map(chip).join(''); }
      container.appendChild(track);

      // Try common layout: replace left column contents
      const leftCol = topBar.querySelector('.row .col-lg-8');
      if (leftCol){
        leftCol.innerHTML = '';
        leftCol.appendChild(container);
      } else {
        // Fallback: insert as first child inside the top bar container
        const tbContainer = topBar.querySelector('.container') || topBar;
        // Create a row/col wrapper if needed
        const row = tbContainer.querySelector('.row') || (()=>{ const r=document.createElement('div'); r.className='row'; tbContainer.prepend(r); return r; })();
        const fullCol = document.createElement('div');
        fullCol.className = 'col-12';
        fullCol.appendChild(container);
        row.prepend(fullCol);
      }

      // Real-time fetch support with cache; fallback to demo items
      const api = WeatherAPI();
      const cities = (window.WeatherMarquee && WeatherMarquee.getCities && WeatherMarquee.getCities()) || defaultCities;
      const apiKey = api.getApiKey();
      if (!apiKey){
        // Fallback demo
        render([
          { city:'Lahore', icon:'fa-sun', text:'Sunny', temp: '30°C' },
          { city:'Karachi', icon:'fa-cloud-sun', text:'Partly Cloudy', temp: '31°C' },
          { city:'Islamabad', icon:'fa-cloud', text:'Cloudy', temp: '27°C' },
          { city:'Multan', icon:'fa-wind', text:'Breezy', temp: '29°C' },
          { city:'Faisalabad', icon:'fa-cloud-sun-rain', text:'Light Showers', temp: '26°C' }
        ]);
      } else {
        api.load(cities).then(items=>{
          if (items && items.length){ render(items); }
        }).catch(()=>{
          // fallback to demo
          render([
            { city:'Lahore', icon:'fa-sun', text:'Sunny', temp: '30°C' },
            { city:'Karachi', icon:'fa-cloud-sun', text:'Partly Cloudy', temp: '31°C' },
            { city:'Islamabad', icon:'fa-cloud', text:'Cloudy', temp: '27°C' },
            { city:'Multan', icon:'fa-wind', text:'Breezy', temp: '29°C' },
            { city:'Faisalabad', icon:'fa-cloud-sun-rain', text:'Light Showers', temp: '26°C' }
          ]);
        });
      }
    }catch(e){}
  }

  // Lightweight OpenWeather helper with caching
  function WeatherAPI(){
    const CACHE_KEY='plantixWeatherCache_v1';
    // Storage key used to persist the API key locally (do not change without migration)
    const KEY_KEY='plantixWeatherApiKey';
    // Default API key provided by the user; used only if no override is found
    const DEFAULT_API_KEY='9c4c66a246394cc1544df2163dd9e54e';
    function getApiKey(){
      const k = (window.PLANTIX_WEATHER_API_KEY || localStorage.getItem(KEY_KEY) || DEFAULT_API_KEY || '').trim();
      return k;
    }

  function setApiKey(k){ if (k){ localStorage.setItem(KEY_KEY, k); } }
    function getCache(){ try{return JSON.parse(localStorage.getItem(CACHE_KEY)||'{}');}catch(e){return{};} }
    function setCache(obj){ localStorage.setItem(CACHE_KEY, JSON.stringify(obj||{})); }
    function faIcon(main){
      const m=(main||'').toLowerCase();
      if (m.includes('rain')) return 'fa-cloud-rain';
      if (m.includes('cloud')) return 'fa-cloud';
      if (m.includes('clear')) return 'fa-sun';
      if (m.includes('storm')||m.includes('thunder')) return 'fa-bolt';
      if (m.includes('snow')) return 'fa-snowflake';
      if (m.includes('wind')) return 'fa-wind';
      return 'fa-cloud-sun';
    }
    function toChip(city, data){
      try{
        const main=data.weather&&data.weather[0]&&data.weather[0].main || '';
        const icon=faIcon(main);
        const temp = Math.round(data.main && data.main.temp) + '°C';
        const text = data.weather && data.weather[0] && data.weather[0].description ? (data.weather[0].description[0].toUpperCase()+data.weather[0].description.slice(1)) : main;
        return { city, icon, text, temp };
      }catch(e){ return { city, icon:'fa-cloud-sun', text:'--', temp:'--' }; }
    }
    async function fetchCity(city){
      const key=getApiKey(); if (!key) throw new Error('No API key');
      const url=`https://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(city)},PK&appid=${encodeURIComponent(key)}&units=metric`;
      const res=await fetch(url, { cache:'no-cache' });
      if (!res.ok) throw new Error('Weather fetch failed');
      return await res.json();
    }
    async function load(cities){
      const cache=getCache(); const now=Date.now(); const TTL=60*60*1000; // 1 hour
      const out=[]; const misses=[];
      (cities||[]).forEach(c=>{
        const rec=cache[c];
        if (rec && (now - rec.ts) < TTL){ out.push(toChip(c, rec.data)); }
        else { misses.push(c); }
      });
      if (misses.length){
        for (const c of misses){
          try{
            const data=await fetchCity(c);
            cache[c]={ data, ts: Date.now() };
            out.push(toChip(c, data));
          }catch(e){ /* ignore single city error */ }
        }
        setCache(cache);
      }
      // Preserve original order
      const order=(cities||[]).slice();
      out.sort((a,b)=> order.indexOf(a.city)-order.indexOf(b.city));
      return out;
    }
    return { getApiKey, setApiKey, load };
  }

  // Public configuration API
  window.WeatherMarquee = window.WeatherMarquee || {
    setApiKey: function(k){ try{ WeatherAPI().setApiKey(k); }catch(e){} },
    setCities: function(arr){ try{ sessionStorage.setItem('plantixWeatherCities', JSON.stringify(arr||[])); }catch(e){} },
    getCities: function(){ try{ return JSON.parse(sessionStorage.getItem('plantixWeatherCities')||'null'); }catch(e){ return null; } },
    refresh: function(){ try{ const el=document.querySelector('.top-bar-area .weather-marquee'); if (el) el.remove(); injectWeatherMarquee(); }catch(e){} }
  };

  // Backward compatibility: expose navigation-based AuthUI
  function openAuthPage(){ try{ sessionStorage.setItem('plantixRedirect', location.pathname+location.search); }catch(e){} window.location.href='signin.html'; }
  function openAccountPage(){ window.location.href='account-profile.html'; }
  window.AuthUI={ openAuthModal: openAuthPage, openAccountModal: openAccountPage };

  // Load chatbot widget globally
  function loadChatbot(){
    try{
      if (window.PlantixChatbot) return; // already loaded
      // Ensure CSS is present early so fallback bubble is styled
      if (!document.querySelector('link[data-plantix-chatbot]')){
        const l=document.createElement('link'); l.rel='stylesheet'; l.href='assets/css/chatbot.css'; l.setAttribute('data-plantix-chatbot','1'); document.head.appendChild(l);
      }
      // Load script dynamically if not present
      if (!document.querySelector('script[data-plantix-chatbot]')){
        const s=document.createElement('script');
        s.src='assets/js/chatbot.js';
        s.async=true;
        s.setAttribute('data-plantix-chatbot','1');
        s.onload=function(){
          // If a fallback bubble exists, remove it once the real widget mounts
          const fb=document.querySelector('.plantix-chatbot-toggle[data-fallback="1"]');
          if (fb){ fb.remove(); }
          // Open immediately if requested
          if (window.PlantixChatbot && window.__plantixChatShouldOpen){ try{ window.PlantixChatbot.open(); }catch(e){} }
          window.__plantixChatShouldOpen=false;
        };
        document.body.appendChild(s);
        // Create a visible fallback bubble if widget isn't ready shortly
        setTimeout(()=>{
          if (!document.querySelector('.plantix-chatbot-toggle') && !window.PlantixChatbot){
            const b=document.createElement('button');
            b.className='plantix-chatbot-toggle';
            b.setAttribute('data-fallback','1');
            b.setAttribute('aria-label','Open chatbot');
            b.innerHTML='<i class="fas fa-comments"></i>';
            b.addEventListener('click', function(){ window.__plantixChatShouldOpen=true; loadChatbot(); });
            document.body.appendChild(b);
          }
        }, 800);
      }
    }catch(e){}
  }

  // Note: Chat widget opens via the floating button only (no navbar injection)
})();
