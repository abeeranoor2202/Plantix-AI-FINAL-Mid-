// === DYNAMIC SHOP ENHANCEMENTS JS ===
// Mock data: 24 products
const products = [
  {id:1,name:'FFC Sona Urea',subtitle:'46% N',price:3500,originalPrice:null,category:'Nitrogen',subcategory:'Urea',description:'High-quality nitrogen fertilizer for all crops.',isOnSale:false,rating:4.5,imageUrl:'assets/img/products/urea_sona.png'},
  {id:2,name:'Engro DAP',subtitle:'18-46-0',price:1500,originalPrice:1900,category:'Phosphorus',subcategory:'DAP',description:'Di-Ammonium Phosphate for better root development.',isOnSale:true,rating:4.8,imageUrl:'assets/img/products/dap_engro.png'},
  {id:3,name:'Sarsabz CAN',subtitle:'Calcium Ammonium Nitrate',price:3800,originalPrice:null,category:'Nitrogen',subcategory:'CAN',description:'Fast-acting nitrogen source with calcium.',isOnSale:false,rating:4.3,imageUrl:'assets/img/products/can_sarsabz.png'},
  {id:4,name:'MOP Potash',subtitle:'MOP 60% K2O',price:12000,originalPrice:null,category:'Potash',subcategory:'MOP',description:'Essential potassium for fruit quality.',isOnSale:false,rating:4.6,imageUrl:'assets/img/products/mop_potash.jpg'},
  {id:5,name:'SOP Potash',subtitle:'SOP 50% K2O',price:17500,originalPrice:null,category:'Potash',subcategory:'SOP',description:'Premium potassium without chloride.',isOnSale:false,rating:4.7,imageUrl:'assets/img/products/sop_potash.jpg'},
  {id:6,name:'NPK 15-15-15',subtitle:'Balanced Blend',price:11000,originalPrice:null,category:'NPK Blends',subcategory:'NPK',description:'Complete balanced nutrition for all crops.',isOnSale:false,rating:4.9,imageUrl:'assets/img/products/npk_15_15_15.jpg'},
  {id:7,name:'Zinc Sulphate',subtitle:'33% Zn',price:2800,originalPrice:null,category:'Micronutrients',subcategory:'Zinc',description:'Essential micronutrient for crop health.',isOnSale:false,rating:4.4,imageUrl:'assets/img/products/zinc_sulphate.jpg'},
  {id:8,name:'Agricultural Gypsum',subtitle:'Calcium Sulphate',price:1200,originalPrice:null,category:'Soil Conditioners',subcategory:'Gypsum',description:'Improves soil structure and water penetration.',isOnSale:false,rating:4.2,imageUrl:'assets/img/products/agricultural_gypsum.jpg'},
  {id:9,name:'FFC Premium Urea',subtitle:'46% N',price:3700,originalPrice:4000,category:'Nitrogen',subcategory:'Urea',description:'Premium grade urea with added nutrients.',isOnSale:true,rating:4.6,imageUrl:'assets/img/products/urea_premium.png'},
  {id:10,name:'Single Super Phosphate',subtitle:'SSP 16% P2O5',price:2500,originalPrice:null,category:'Phosphorus',subcategory:'SSP',description:'Affordable phosphorus source.',isOnSale:false,rating:4.1,imageUrl:'assets/img/products/ssp.jpg'},
  {id:11,name:'Triple Super Phosphate',subtitle:'TSP 46% P2O5',price:13500,originalPrice:null,category:'Phosphorus',subcategory:'TSP',description:'Concentrated phosphorus fertilizer.',isOnSale:false,rating:4.5,imageUrl:'assets/img/products/triple_super_phosphate.jpg'},
  {id:12,name:'NPK 20-20-20',subtitle:'Water Soluble',price:15000,originalPrice:16500,category:'NPK Blends',subcategory:'NPK',description:'Complete water-soluble fertilizer.',isOnSale:true,rating:4.8,imageUrl:'assets/img/products/npk_20.jpg'},
  {id:13,name:'Ammonium Sulphate',subtitle:'21% N, 24% S',price:3200,originalPrice:null,category:'Nitrogen',subcategory:'Sulphate',description:'Nitrogen with added sulphur.',isOnSale:false,rating:4.3,imageUrl:'assets/img/products/ammonium_sulphate.jpg'},
  {id:14,name:'Potassium Nitrate',subtitle:'13-0-46',price:18500,originalPrice:null,category:'Potash',subcategory:'Nitrate',description:'Premium potassium with nitrogen.',isOnSale:false,rating:4.7,imageUrl:'assets/img/products/potassium_nitrate.jpg'},
  {id:15,name:'Boron',subtitle:'17% B',price:2500,originalPrice:null,category:'Micronutrients',subcategory:'Boron',description:'Essential for flowering and fruiting.',isOnSale:false,rating:4.4,imageUrl:'assets/img/products/boron.png'},
  {id:16,name:'Iron Chelate',subtitle:'12% Fe',price:3500,originalPrice:null,category:'Micronutrients',subcategory:'Iron',description:'Prevents iron deficiency chlorosis.',isOnSale:false,rating:4.5,imageUrl:'assets/img/products/iron_chelate.jpg'},
  {id:17,name:'Calcium Nitrate',subtitle:'15.5-0-0 + 19% Ca',price:5800,originalPrice:6200,category:'Soil Conditioners',subcategory:'Calcium',description:'Water-soluble calcium with nitrogen.',isOnSale:true,rating:4.6,imageUrl:'assets/img/products/calcium_nitrate.png'},
  {id:18,name:'NPK 12-32-16',subtitle:'Starter Formula',price:9500,originalPrice:null,category:'NPK Blends',subcategory:'NPK',description:'High phosphorus for seedling growth.',isOnSale:false,rating:4.7,imageUrl:'assets/img/products/npk_starter.jpg'},
  {id:19,name:'Magnesium Sulphate',subtitle:'16% Mg, 13% S',price:2900,originalPrice:null,category:'Micronutrients',subcategory:'Magnesium',description:'Prevents magnesium deficiency.',isOnSale:false,rating:4.3,imageUrl:'assets/img/products/magnesium_sulphate.jpg'},
  {id:20,name:'Humic Acid Granules',subtitle:'50% Humic',price:4200,originalPrice:null,category:'Soil Conditioners',subcategory:'Organic',description:'Improves nutrient uptake and soil health.',isOnSale:false,rating:4.8,imageUrl:'assets/img/products/humic_acid.png'},
  {id:21,name:'Micronutrient Mix',subtitle:'Zn+Fe+Mn+Cu+B',price:3800,originalPrice:4100,category:'Micronutrients',subcategory:'Mixed',description:'Complete micronutrient blend.',isOnSale:true,rating:4.9,imageUrl:'assets/img/products/micronutrient_mix.jpg'},
  {id:22,name:'NPK 10-26-26',subtitle:'High PK Formula',price:10500,originalPrice:null,category:'NPK Blends',subcategory:'NPK',description:'For flowering and fruiting stages.',isOnSale:false,rating:4.6,imageUrl:'assets/img/products/npk_high_pk.jpg'},
  {id:23,name:'Superphosphate',subtitle:'Phosphorus',price:2500,originalPrice:3200,category:'Phosphorus',subcategory:'SSP',description:'Superphosphate for wheat.',isOnSale:true,rating:4.2,imageUrl:'assets/img/products/superphosphate.jpg'},
  {id:24,name:'Urea Gold',subtitle:'Nitrogen',price:3200,originalPrice:null,category:'Nitrogen',subcategory:'Urea',description:'Urea Gold for rice.',isOnSale:false,rating:4.3,imageUrl:'assets/img/products/urea_gold.png'}
];

// State
let selectedCategories = [];
let sortBy = 'price-low';
let perPage = 8;
let currentPage = 1;
let searchQuery = '';
let dismissed = new Set();
let wishlist = JSON.parse(localStorage.getItem('wishlist')||'[]');
let cart = JSON.parse(localStorage.getItem('cart')||'[]');

// DOM
const productGrid = document.getElementById('productGrid');
const pagination = document.getElementById('pagination');
const productCount = document.getElementById('productCount');
const sortSelect = document.getElementById('sortSelect');
const perPageSelect = document.getElementById('perPageSelect');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarFilters = document.getElementById('sidebarFilters');
const sidebarClose = document.getElementById('sidebarClose');
const categoryFilters = document.getElementById('categoryFilters');
const clearFilters = document.getElementById('clearFilters');
const toast = document.createElement('div');
toast.id = 'toast';
document.body.appendChild(toast);

// --- INIT ---
renderFilters();
updateProducts();
updateCartWishlistBadges();

// --- EVENT LISTENERS ---
if (sidebarToggle) sidebarToggle.onclick = () => sidebarFilters.classList.toggle('collapsed');
if (sidebarClose) sidebarClose.onclick = () => sidebarFilters.classList.add('collapsed');
if (clearFilters) clearFilters.onclick = () => {
  selectedCategories = [];
  document.querySelectorAll('#categoryFilters input[type=checkbox]').forEach(cb=>cb.checked=false);
  updateProducts();
};
if (sortSelect) sortSelect.onchange = e => { sortBy = e.target.value; updateProducts(); };
if (perPageSelect) perPageSelect.onchange = e => { perPage = +e.target.value; currentPage = 1; updateProducts(); };
if (categoryFilters) categoryFilters.onchange = e => {
  selectedCategories = Array.from(document.querySelectorAll('#categoryFilters input[type=checkbox]:checked')).map(cb=>cb.value);
  currentPage = 1;
  updateProducts();
};
// Header search bar enhancement
const headerSearch = document.getElementById('searchInput');
if (headerSearch) headerSearch.oninput = e => {
  searchQuery = e.target.value.toLowerCase();
  currentPage = 1;
  updateProducts();
};

// --- FUNCTIONS ---
function renderFilters() {
  if (!categoryFilters) return;
  const cats = [
    {label:'Nitrogen (Urea)', value:'Urea'},
    {label:'Nitrogen (CAN)', value:'CAN'},
    {label:'Phosphorus (DAP)', value:'DAP'},
    {label:'Phosphorus (SSP)', value:'SSP'},
    {label:'Phosphorus (TSP)', value:'TSP'},
    {label:'Potash (MOP)', value:'MOP'},
    {label:'Potash (SOP)', value:'SOP'},
    {label:'NPK Blends', value:'NPK'},
    {label:'Micronutrients (Zinc)', value:'Zinc'},
    {label:'Micronutrients (Boron)', value:'Boron'},
    {label:'Micronutrients (Iron)', value:'Iron'},
    {label:'Micronutrients (Magnesium)', value:'Magnesium'},
    {label:'Micronutrients (Mixed)', value:'Mixed'},
    {label:'Soil Conditioners (Gypsum)', value:'Gypsum'},
    {label:'Soil Conditioners (Calcium)', value:'Calcium'},
    {label:'Soil Conditioners (Organic)', value:'Organic'}
  ];
  categoryFilters.innerHTML = cats
    .map(
      (cat) =>
        `<label><input type="checkbox" value="${cat.value}" data-label="${cat.label}"> ${cat.label}</label>`
    )
    .join("");
}
function updateProducts() {
  let filtered = products.filter(p =>
    (!searchQuery ||
      p.name.toLowerCase().includes(searchQuery) ||
      (p.subtitle && p.subtitle.toLowerCase().includes(searchQuery)) ||
      (p.description && p.description.toLowerCase().includes(searchQuery))
    ) &&
    (selectedCategories.length === 0 || selectedCategories.includes(p.subcategory)) &&
    !dismissed.has(p.id)
  );
  // Sort
  switch(sortBy) {
    case 'price-low': filtered.sort((a,b)=>a.price-b.price); break;
    case 'price-high': filtered.sort((a,b)=>b.price-a.price); break;
    case 'name-az': filtered.sort((a,b)=>a.name.localeCompare(b.name)); break;
    case 'popularity':
      filtered.sort((a,b)=>{
        const aAvg = (window.Reviews && Reviews.getSummary) ? Reviews.getSummary(a.id, { fallbackAvg: a.rating||0 }).avg : (a.rating||0);
        const bAvg = (window.Reviews && Reviews.getSummary) ? Reviews.getSummary(b.id, { fallbackAvg: b.rating||0 }).avg : (b.rating||0);
        return bAvg - aAvg;
      });
      break;
  }
  // Pagination
  const total = filtered.length;
  const totalPages = Math.max(1, Math.ceil(total/perPage));
  if (currentPage > totalPages) currentPage = totalPages;
  const start = (currentPage-1)*perPage;
  const pageProds = filtered.slice(start, start+perPage);
  // Render
  renderProductGrid(pageProds);
  renderPagination(total, totalPages);
  if (productCount) productCount.textContent = `${total} product${total!==1?'s':''} found`;
}
function renderProductGrid(prods) {
  if (!productGrid) return;
  productGrid.innerHTML = prods.map(p => {
    const sum = (window.Reviews && Reviews.getSummary) ? Reviews.getSummary(p.id, { fallbackAvg: p.rating }) : { avg: p.rating||0, count: 0 };
  const rounded = Math.round(sum.avg);
  const stars = `<span class="stars">${'<i class=\'fas fa-star\'></i>'.repeat(rounded)}${'<i class=\'far fa-star\'></i>'.repeat(5-rounded)}</span>`;
    const latest = (window.Reviews && Reviews.getReviews) ? (Reviews.getReviews(p.id).slice().sort((a,b)=> (b.updatedAt||0)-(a.updatedAt||0))[0]) : null;
    const snippet = latest ? (latest.comment||'').slice(0, 70) + ((latest.comment||'').length>70?'…':'') : '';
    return `
    <div class="card-agri product-card d-flex flex-column h-100" style="animation-delay:${Math.random()*0.2}s; border: none;" data-id="${p.id}">
      <div class="position-relative p-4 text-center" style="background: var(--agri-bg); border-radius: var(--agri-radius-md) var(--agri-radius-md) 0 0;">
          <button class="btn btn-sm btn-light position-absolute rounded-circle" style="top: 10px; right: 10px; width: 32px; height: 32px; z-index: 10;" title="Dismiss" onclick="dismissProduct(${p.id})"><i class="fas fa-times text-muted"></i></button>
          <button class="btn btn-sm position-absolute rounded-circle bg-white" style="top: 10px; left: 10px; width: 32px; height: 32px; z-index: 10; box-shadow: var(--agri-shadow-sm);" title="Wishlist" onclick="toggleWishlist(${p.id})"><i class="fa${wishlist.includes(p.id)?'s text-danger':'r text-muted'} fa-heart"></i></button>
          ${p.isOnSale?'<span class="badge position-absolute" style="top: 50px; left: 10px; background: var(--agri-secondary); color: var(--agri-text-main); font-weight: bold; padding: 5px 10px;">SALE!</span>':''}
          <a href="{{ route('shop.single') }}">
              <img src="${p.imageUrl}" alt="${p.name}" style="height: 180px; object-fit: contain; margin: 0 auto; transition: transform 0.3s;" class="product-img-hover">
          </a>
      </div>
      <div class="p-4 d-flex flex-column flex-grow-1">
          <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="badge bg-light text-success fw-medium px-2 py-1" style="font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase;">${p.category}</span>
          </div>
          <h5 class="fw-bold text-dark mb-1"><a href="{{ route('shop.single') }}" class="text-decoration-none text-dark">${p.name}</a></h5>
          <div class="text-muted small mb-2">${p.subtitle||''}</div>
          <div class="product-rating mb-3" title="${sum.avg} average rating" style="font-size: 13px;">
            <span class="text-warning">${stars}</span> <span class="text-muted ms-1">(${sum.avg})</span>
          </div>
          <div class="mt-auto">
              <div class="d-flex justify-content-between align-items-center mb-3">
                  <div class="product-price">
                      ${p.originalPrice?`<span class='text-muted text-decoration-line-through small d-block'>PKR ${formatPrice(p.originalPrice)}</span>`:''}
                      <span class="fw-bold text-success fs-5">PKR ${formatPrice(p.price)}</span>
                  </div>
              </div>
              <div class="d-flex gap-2">
                  <button class="btn-agri btn-agri-primary flex-grow-1" style="padding: 8px 10px; font-size: 14px;" onclick="addToCart(${p.id})"><i class="fas fa-shopping-cart me-1"></i> Add</button>
                  <a class="btn-agri btn-agri-outline text-center" style="padding: 8px 12px; font-size: 14px;" href="{{ route('shop.single') }}" title="View details"><i class="far fa-eye"></i></a>
              </div>
          </div>
      </div>
    </div>
  `}).join('');
}
function renderPagination(total, totalPages) {
  if (!pagination) return;
  let html = '';
  if (totalPages > 1) {
    html += `<button ${currentPage===1?'disabled':''} onclick="changePage(${currentPage-1})">&lt;</button>`;
    for (let i=1; i<=totalPages; i++) {
      if (i===1||i===totalPages||Math.abs(i-currentPage)<=1) {
        html += `<button class="${i===currentPage?'active':''}" onclick="changePage(${i})">${i}</button>`;
      } else if (i===currentPage-2||i===currentPage+2) {
        html += '<button disabled>...</button>';
      }
    }
    html += `<button ${currentPage===totalPages?'disabled':''} onclick="changePage(${currentPage+1})">&gt;</button>`;
  }
  pagination.innerHTML = html;
}
function changePage(page) {
  currentPage = page;
  updateProducts();
  window.scrollTo({top:document.getElementById('productGrid').offsetTop-100,behavior:'smooth'});
}
function formatPrice(p) {
  return new Intl.NumberFormat('en-PK').format(p);
}
function addToCart(id) {
  try {
    const p = products.find(x=>x.id===id);
    if (!p) return;
    if (window.CartManager && typeof window.CartManager.addToCart === 'function') {
      window.CartManager.addToCart({
        id: String(p.id),
        name: `${p.name}${p.subtitle?` (${p.subtitle})`:''}`,
        price: p.price,
        image: p.imageUrl,
        quantity: 1,
        tags: [p.category, p.subcategory].filter(Boolean)
      });
    } else {
      // Fallback to local storage array if CartManager not present
      if (!cart.includes(id)) cart.push(id);
      localStorage.setItem('cart', JSON.stringify(cart));
    }
    updateCartWishlistBadges();
    showToast('Added to cart!');
  } catch (e) {
    console.error('Add to cart failed', e);
  }
}
function toggleWishlist(id) {
  const idx = wishlist.indexOf(id);
  if (idx>-1) wishlist.splice(idx,1); else wishlist.push(id);
  localStorage.setItem('wishlist',JSON.stringify(wishlist));
  updateCartWishlistBadges();
  updateProducts();
}
function dismissProduct(id) {
  dismissed.add(id);
  updateProducts();
}
function showToast(msg) {
  toast.textContent = msg;
  toast.className = 'show';
  setTimeout(()=>toast.className='',2000);
}
function updateCartWishlistBadges() {
  const cartBadge = document.getElementById('cartCount');
  const wishBadge = document.getElementById('wishlistCount');
  if (cartBadge) cartBadge.textContent = cart.length;
  if (wishBadge) wishBadge.textContent = wishlist.length;
}
function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c])); }
// Expose for inline onclick
window.changePage = changePage;
window.toggleWishlist = toggleWishlist;
window.addToCart = addToCart;
window.dismissProduct = dismissProduct;
