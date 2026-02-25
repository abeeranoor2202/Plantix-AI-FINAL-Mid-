// Simple static forum powered by localStorage
(function(){
  const KEY_THREADS='plantixForumThreads';
  function now(){ return Date.now(); }
  function uid(p){ return p+'_'+Math.random().toString(36).slice(2,8)+'_'+Date.now(); }
  function load(){ return JSON.parse(localStorage.getItem(KEY_THREADS)||'[]'); }
  function save(arr){ localStorage.setItem(KEY_THREADS, JSON.stringify(arr)); }
  function seed(){
    const arr=load();
    // Build seed data
    const t0 = now();
    const users = {
      expert1: { id:'expert_ayesha', name:'Dr. Ayesha Khan (Expert)' },
      expert2: { id:'expert_hamid', name:'Engr. Hamid Raza (Expert)' },
      farmer1: { id:'farmer_ali', name:'Muhammad Ali' },
      farmer2: { id:'farmer_sana', name:'Sana Baloch' },
      farmer3: { id:'farmer_imran', name:'Imran Khan' }
    };
    const pinned={ id: uid('t'), title:'Read before posting: Share clear details to get help fast', body:'Welcome to the forum!\n\n• Include crop, location, and recent weather.\n• Add clear photos where possible.\n• Mention fertilizers/pesticides recently used.\n• Be respectful and mark solved when you have an answer.\n\nHappy growing!', tags:['Guidelines'], createdAt: t0-1000*60*60*24*7, updatedAt: t0-1000*60*60*24*7, user:{id:'system', name:'Plantix-AI Team'}, votes:5, solved:false, posts:[], pinned:true };

    function thread({title, body, tags, user, minutesAgo, votes=0, solved=false, replies=[]}){
      const created = t0 - minutesAgo*60*1000;
      const id=uid('t');
      const posts = replies.map((r,i)=>({ id: uid('p'), body: r.body, createdAt: created + (i+1)*5*60*1000, user: r.user, votes: r.votes||0 }));
      const updated = posts.length? posts[posts.length-1].createdAt : created;
      return { id, title, body, tags, createdAt: created, updatedAt: updated, user, votes, solved, posts, pinned:false };
    }

    const extras = [
      thread({
        title:'Wheat rust in South Punjab - early signs?',
        body:'I\'m seeing orange-brown pustules on wheat leaves near Bahawalpur. Is this leaf rust? What\'s the immediate step to reduce spread? Any recommended fungicide and timing? Photos attached in comments.',
        tags:['Wheat','Punjab','Pest'], user: users.farmer1, minutesAgo: 60*24*3, votes: 12, solved: true,
        replies:[
          { body:'Yes, likely leaf rust. Start with triazole (e.g., propiconazole) at recommended label rate. Spray early morning, ensure good coverage. Rotate MOA if second spray needed in 10–14 days.', user: users.expert1, votes: 7 },
          { body:'Applied propiconazole as advised. Symptoms controlled in 5 days. Thank you!', user: users.farmer1, votes: 2 }
        ]
      }),
      thread({
        title:'Rice blast management advice for Sindh?',
        body:'Narrow leaves with diamond-shaped lesions in Larkana paddy. Suspecting blast. Field is dense. Any cultural fixes along with spray?',
        tags:['Rice','Sindh','Pest'], user: users.farmer2, minutesAgo: 60*24*5, votes: 8, solved: false,
        replies:[
          { body:'Thin the canopy if possible; maintain balanced nitrogen. For chemical, consider tricyclazole or azoxystrobin according to label. Improve field drainage to reduce humidity.', user: users.expert2, votes: 4 }
        ]
      }),
      thread({
        title:'Cotton pink bollworm control near Multan',
        body:'Two weeks of high catches in pheromone traps. Some internal boll damage observed. What threshold triggers spray and what to rotate?',
        tags:['Cotton','Punjab','Pest'], user: users.farmer3, minutesAgo: 60*24*9, votes: 10, solved: true,
        replies:[
          { body:'Use trap counts plus scouting: 5–10 moths/night coupled with 5–10% boll damage warrants action. Rotate spinosyns with IGRs; avoid repeating same MOA to limit resistance.', user: users.expert2, votes: 6 }
        ]
      }),
      thread({
        title:'Irrigation scheduling for Kharif maize (Punjab)',
        body:'Hot spell expected next week. At V6 stage. How often should I irrigate on loamy soil near Faisalabad?',
        tags:['Irrigation','Weather','Punjab'], user: users.farmer1, minutesAgo: 60*24*2, votes: 5, solved: false,
        replies:[
          { body:'Monitor soil moisture; with highs >40°C, consider 5–7 day interval on loam at V6, ensuring 50–60% available water maintained. Mulch can reduce evap losses.', user: users.expert1, votes: 3 }
        ]
      }),
      thread({
        title:'DAP vs Urea timing for maize top-dress',
        body:'Khyber Pakhtunkhwa farmer here. For sandy loam, what\'s the right split between basal and top-dress for DAP and Urea?',
        tags:['Fertilizer','Soil','Weather'], user: users.farmer3, minutesAgo: 60*24*12, votes: 3, solved: false,
        replies:[
          { body:'Keep most P (DAP) at basal to support roots; urea in 2–3 splits (V4/V8/pre-tassel) based on rainfall forecast. Add urease inhibitor if heavy dew expected.', user: users.expert1, votes: 2 }
        ]
      }),
      thread({
        title:'salinity patches in Badin (Sindh)',
        body:'White crust and poor stand in low spots. EC measured high. What reclamation steps are practical this season?',
        tags:['Soil','Sindh'], user: users.farmer2, minutesAgo: 60*24*15, votes: 9, solved: false,
        replies:[
          { body:'Gypsum application based on soil test (ESP) + leaching with good quality water. Improve surface drainage and consider salt-tolerant varieties next cycle.', user: users.expert2, votes: 5 }
        ]
      })
    ];

    if (arr.length === 0) { save([pinned, ...extras]); return; }
    if (arr.length === 1 && arr[0].pinned) { save([arr[0], ...extras]); return; }
    // Otherwise, do nothing to avoid duplicating seed data
  }

  const Forum={
    list({q='', tag='', sort='new'}={}){
      seed();
      let arr=load();
      if (q){ const s=q.toLowerCase(); arr=arr.filter(t=>t.title.toLowerCase().includes(s)||t.body.toLowerCase().includes(s)); }
      if (tag){ arr=arr.filter(t=> (t.tags||[]).includes(tag)); }
      if (sort==='new') arr=arr.sort((a,b)=>b.createdAt-a.createdAt);
      if (sort==='active') arr=arr.sort((a,b)=> (b.updatedAt||b.createdAt)-(a.updatedAt||a.createdAt));
      if (sort==='votes') arr=arr.sort((a,b)=> (b.votes||0)-(a.votes||0));
      // keep pinned at top
      arr = arr.sort((a,b)=> (b.pinned?1:0) - (a.pinned?1:0));
      return arr;
    },
    get(id){ return load().find(t=>String(t.id)===String(id)); },
    create({title, body, tags}){
      const actor = (window.ExpertAuth && ExpertAuth.current()) || (window.Auth && Auth.current());
      if (!actor) throw new Error('Sign in required');
      title=(title||'').trim(); body=(body||'').trim();
      if (!title || !body) throw new Error('Title and body are required');
      const name = (actor.role==='expert' || actor.id?.startsWith('ex_')) ? `${actor.name} (Expert)` : actor.name;
      const t={ id: uid('t'), title, body, tags: (tags||[]).filter(Boolean), createdAt: now(), updatedAt: now(), user:{id:actor.id, name}, votes:0, solved:false, posts:[], pinned:false };
      const all=load(); all.push(t); save(all); return t;
    },
    post({threadId, body, parentId=null}){
      const actor = (window.ExpertAuth && ExpertAuth.current()) || (window.Auth && Auth.current());
      if (!actor) throw new Error('Sign in required');
      body=(body||'').trim(); if(!body) throw new Error('Reply cannot be empty');
      const all=load(); const idx=all.findIndex(t=>String(t.id)===String(threadId)); if(idx===-1) throw new Error('Thread not found');
      if (all[idx].solved){ throw new Error('This thread is marked as solved. Replies are closed.'); }
      const name = (actor.role==='expert' || actor.id?.startsWith('ex_')) ? `${actor.name} (Expert)` : actor.name;
      const p={ id: uid('p'), body, createdAt: now(), user:{id:actor.id, name}, votes:0, parentId: parentId||null };
      all[idx].posts.push(p); all[idx].updatedAt=now(); save(all); return p;
    },
    voteThread(id, delta){
      const actor = (window.ExpertAuth && ExpertAuth.current()) || (window.Auth && Auth.current());
      if (!actor) throw new Error('Sign in required');
      delta = delta===1 ? 1 : (delta===-1 ? -1 : 0); if (!delta) return;
      const all=load(); const idx=all.findIndex(t=>String(t.id)===String(id)); if(idx===-1) return;
      const t=all[idx];
      t.voters = t.voters || {};
      // Preserve legacy count once
      if (typeof t.legacyVotes !== 'number') t.legacyVotes = Number.isFinite(t.votes) ? t.votes : 0;
      const uid = actor.id;
      const current = t.voters[uid] || 0;
      if (current === delta) { delete t.voters[uid]; } else { t.voters[uid] = delta; }
      const sum = Object.values(t.voters).reduce((a,b)=>a+(b||0),0);
      t.votes = (t.legacyVotes||0) + sum;
      save(all);
    },
    votePost(threadId, postId, delta){
      const actor = (window.ExpertAuth && ExpertAuth.current()) || (window.Auth && Auth.current());
      if (!actor) throw new Error('Sign in required');
      delta = delta===1 ? 1 : (delta===-1 ? -1 : 0); if (!delta) return;
      const all=load(); const t=all.find(t=>String(t.id)===String(threadId)); if(!t) return; const p=t.posts.find(p=>String(p.id)===String(postId)); if(!p) return;
      p.voters = p.voters || {};
      if (typeof p.legacyVotes !== 'number') p.legacyVotes = Number.isFinite(p.votes) ? p.votes : 0;
      const uid = actor.id;
      const current = p.voters[uid] || 0;
      if (current === delta) { delete p.voters[uid]; } else { p.voters[uid] = delta; }
      const sum = Object.values(p.voters).reduce((a,b)=>a+(b||0),0);
      p.votes = (p.legacyVotes||0) + sum;
      save(all);
    },
    markSolved(id){ const actor=(window.ExpertAuth&&ExpertAuth.current())||(window.Auth&&Auth.current()); if (!actor) throw new Error('Sign in required'); const all=load(); const t=all.find(t=>String(t.id)===String(id)); if(!t) throw new Error('Thread not found'); if (t.user.id!==actor.id) throw new Error('Only author can mark solved'); t.solved=true; t.updatedAt=now(); save(all); },
    deleteThread(id){ const actor=(window.ExpertAuth&&ExpertAuth.current())||(window.Auth&&Auth.current()); if (!actor) throw new Error('Sign in required'); let all=load(); const t=all.find(t=>String(t.id)===String(id)); if(!t) throw new Error('Thread not found'); if (t.user.id!==actor.id) throw new Error('Only author can delete'); all=all.filter(x=>x.id!==id); save(all); },
    togglePinned(id){ const actor=(window.ExpertAuth&&ExpertAuth.current())||(window.Auth&&Auth.current()); if (!actor) throw new Error('Sign in required'); const all=load(); const t=all.find(t=>String(t.id)===String(id)); if(!t) throw new Error('Thread not found'); if (t.user.id!==actor.id) throw new Error('Only author can pin/unpin'); t.pinned = !t.pinned; t.updatedAt=now(); save(all); }
  };
  window.Forum=Forum;
  window.ForumPresetTags = ['Wheat','Rice','Cotton','Pest','Irrigation','Fertilizer','Soil','Weather','Guidelines'];
})();
