(function(){
  const STORAGE_KEY = 'plantixProductReviews';

  function load(){
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY)||'{}'); } catch(e){ return {}; }
  }
  function save(data){ localStorage.setItem(STORAGE_KEY, JSON.stringify(data||{})); }

  function normalizeId(id){ return String(id||'').trim(); }
  function now(){ return Date.now(); }

  function getReviews(productId){
    const db = load();
    const pid = normalizeId(productId);
    return Array.isArray(db[pid]) ? db[pid] : [];
  }

  function setReviews(productId, reviews){
    const db = load();
    const pid = normalizeId(productId);
    db[pid] = reviews;
    save(db);
    return reviews;
  }

  function getSummary(productId, opts={}){
    const list = getReviews(productId);
    const count = list.length;
    if (count === 0){
      const fallback = (opts && typeof opts.fallbackAvg === 'number') ? opts.fallbackAvg : null;
      return { count: 0, avg: fallback || 0 };
    }
    const sum = list.reduce((a,r)=> a + (Number(r.rating)||0), 0);
    return { count, avg: +(sum / count).toFixed(2) };
  }

  function renderStars(rating){
    const r = Math.max(0, Math.min(5, Math.round(Number(rating)||0)));
    return '<span class="stars">' +
      ('<i class="fas fa-star"></i>'.repeat(r)) +
      ('<i class="far fa-star"></i>'.repeat(5-r)) +
    '</span>';
  }

  function reviewerIdForInput({user, email}){
    if (user && user.id) return 'user:' + user.id;
    const em = String(email||'').trim().toLowerCase();
    if (em) return 'guest:' + em;
    return null;
  }

  function upsertReview(productId, { rating, comment, name, email }){
    const pid = normalizeId(productId);
    const user = (window.Auth && typeof window.Auth.current === 'function') ? window.Auth.current() : null;
    const rid = reviewerIdForInput({ user, email });
    if (!rid) throw new Error('Please sign in or provide an email to submit a review.');
    const displayName = (user && user.name) || (name || (email || '').split('@')[0] || 'Anonymous');
    const list = getReviews(pid);
    const existingIdx = list.findIndex(r => r.reviewerId === rid);
    const review = {
      id: existingIdx>-1 ? list[existingIdx].id : ('rvw_' + now()),
      reviewerId: rid,
      displayName,
      rating: Math.max(1, Math.min(5, Number(rating)||0)),
      comment: String(comment||'').trim(),
      createdAt: existingIdx>-1 ? list[existingIdx].createdAt : now(),
      updatedAt: now()
    };
    if (existingIdx>-1){ list[existingIdx] = review; } else { list.push(review); }
    setReviews(pid, list);
    return review;
  }

  function removeReview(productId, reviewId){
    const pid = normalizeId(productId);
    const list = getReviews(pid);
    const user = (window.Auth && typeof window.Auth.current === 'function') ? window.Auth.current() : null;
    const idx = list.findIndex(r => r.id === reviewId);
    if (idx === -1) throw new Error('Review not found');
    const canDelete = user && ('user:'+user.id) === list[idx].reviewerId;
    if (!canDelete) throw new Error('You can only delete your own review');
    list.splice(idx, 1);
    setReviews(pid, list);
    return true;
  }

  function formatDate(ts){ try{ return new Date(ts).toLocaleDateString(); }catch(e){ return ''; } }

  function renderReviewItem(r){
    return `<div class="item">
      <div class="thumb"><img src="assets/img/800x800.png" alt="Thumb"></div>
      <div class="info">
        <div class="rating">${renderStars(r.rating)}</div>
        <div class="review-date">${formatDate(r.updatedAt||r.createdAt)}</div>
        <div class="review-authro"><h5>${escapeHtml(r.displayName||'User')}</h5></div>
        <p>${escapeHtml(r.comment||'')}</p>
      </div>
    </div>`;
  }

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c])); }

  function renderReviewListHtml(productId){
    const list = getReviews(productId);
    if (!list.length) return '<p class="text-muted">No reviews yet. Be the first to review this product.</p>';
    return '<div class="review-items">' + list.slice().sort((a,b)=> (b.updatedAt||0)-(a.updatedAt||0)).map(renderReviewItem).join('') + '</div>';
  }

  window.Reviews = {
    getReviews,
    getSummary,
    upsertReview,
    removeReview,
    renderStars,
    renderReviewListHtml
  };
})();

// Seed demo reviews once (non-destructive if user already has data)
(function(){
  const FLAG='plantixProductReviewsSeeded_v1';
  try{
    if (localStorage.getItem(FLAG)) return;
    const raw = localStorage.getItem('plantixProductReviews');
    if (raw && raw !== '{}' && raw !== 'null') { localStorage.setItem(FLAG,'1'); return; }
    const now = Date.now();
    const make = (pid, items) => items.map((it,i)=>({
      id: 'rvw_'+pid+'_'+i+'_'+now,
      reviewerId: it.reviewerId || ('guest:'+it.email.toLowerCase()),
      displayName: it.name,
      rating: it.rating,
      comment: it.comment,
      createdAt: now - (i*86400000),
      updatedAt: now - (i*86400000)
    }));
    const db = {
      '1': make('1', [
        { name:'Ahmed Khan', email:'ahmed@example.com', rating:5, comment:'Yield improved noticeably. Granules were clean and consistent.' },
        { name:'Sana Tariq', email:'sana@example.com', rating:4, comment:'Good quality urea. Best applied before irrigation.' }
      ]),
      '2': make('2', [
        { name:'Bilal Arshad', email:'bilal@example.com', rating:5, comment:'Roots developed strongly after using this DAP.' },
        { name:'Nida R.', email:'nida@example.com', rating:4, comment:'Saw better tillering in wheat. Recommended.' }
      ]),
      '3': make('3', [
        { name:'Usman', email:'usman@example.com', rating:4, comment:'CAN worked well for mid-season N top-up.' }
      ]),
      '8': make('8', [
        { name:'Haris', email:'haris@example.com', rating:5, comment:'Helped with soil structure on hard clay. Water infiltration improved.' }
      ]),
      '14': make('14', [
        { name:'Shabnam', email:'shabnam@example.com', rating:5, comment:'Excellent for fruit quality and size. Worth the cost.' }
      ]),
      '17': make('17', [
        { name:'Imran', email:'imran@example.com', rating:4, comment:'Good results in fertigation. Convenient and reliable.' }
      ]),
      '21': make('21', [
        { name:'Ayesha', email:'ayesha@example.com', rating:5, comment:'Micronutrient deficiency symptoms disappeared in two weeks.' }
      ])
    };
    localStorage.setItem('plantixProductReviews', JSON.stringify(db));
    localStorage.setItem(FLAG,'1');
  }catch(e){}
})();
