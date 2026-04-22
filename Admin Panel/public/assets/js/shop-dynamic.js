// === DYNAMIC SHOP JS ===
// Products supplied by server via window.SHOP_DATA
// localStorage is NOT used for cart or wishlist.

const SHOP       = window.SHOP_DATA || {};
const products   = Array.isArray(SHOP.products) ? SHOP.products : [];
const allVendors = Array.isArray(SHOP.vendors)  ? SHOP.vendors  : [];
const PRICE_RANGE = SHOP.priceRange || { min: 0, max: 50000 };
const productById = new Map(products.map(p => [String(p.id), p]));

// State
let selectedCategories = [];
let selectedVendors    = [];
let sortBy         = 'newest';
let perPage        = 8;
let currentPage    = 1;
let searchQuery    = '';
let filterMinPrice = null;
let filterMaxPrice = null;
let filterMinRating = 0;
let filterOnSale   = false;
let dismissed      = new Set();
let wishlist       = [];

// DOM refs
const productGrid    = document.getElementById('productGrid');
const pagination     = document.getElementById('pagination');
const productCount   = document.getElementById('productCount');
const sortSelect     = document.getElementById('sortSelect');
const perPageSelect  = document.getElementById('perPageSelect');
const sidebarToggle  = document.getElementById('sidebarToggle');
const sidebarFilters = document.getElementById('sidebarFilters');
const sidebarClose   = document.getElementById('sidebarClose');
const clearFilters   = document.getElementById('clearFilters');

const toast = document.createElement('div');
toast.id = 'toast';
document.body.appendChild(toast);

// Boot
renderFilters();
updateProducts();

// Event wiring
if (sidebarToggle) sidebarToggle.onclick = () => sidebarFilters.classList.toggle('collapsed');
if (sidebarClose)  sidebarClose.onclick  = () => sidebarFilters.classList.add('collapsed');

if (clearFilters) clearFilters.onclick = () => {
    selectedCategories = []; selectedVendors = [];
    filterMinPrice = null; filterMaxPrice = null;
    filterMinRating = 0; filterOnSale = false;
    document.querySelectorAll('#categoryFilters input, #vendorFilters input')
        .forEach(cb => (cb.checked = false));
    const pMin = document.getElementById('priceMin'); if (pMin) pMin.value = '';
    const pMax = document.getElementById('priceMax'); if (pMax) pMax.value = '';
    const oSale = document.getElementById('onSaleFilter'); if (oSale) oSale.checked = false;
    document.querySelectorAll('#ratingFilter .rating-btn').forEach(b => b.classList.remove('active'));
    currentPage = 1;
    updateProducts();
    updateFilterBadge();
};

if (sortSelect)    sortSelect.onchange    = e => { sortBy = e.target.value; updateProducts(); };
if (perPageSelect) perPageSelect.onchange = e => { perPage = +e.target.value; currentPage = 1; updateProducts(); };

function wireCheckboxGroup(containerId, cb) {
    const el = document.getElementById(containerId);
    if (el) el.addEventListener('change', () => {
        cb(Array.from(el.querySelectorAll('input:checked')).map(i => i.value));
        currentPage = 1; updateProducts(); updateFilterBadge();
    });
}
wireCheckboxGroup('categoryFilters', v => (selectedCategories = v));
wireCheckboxGroup('vendorFilters',   v => (selectedVendors    = v));

const applyPriceBtn = document.getElementById('applyPrice');
if (applyPriceBtn) applyPriceBtn.onclick = () => {
    const mn = document.getElementById('priceMin');
    const mx = document.getElementById('priceMax');
    filterMinPrice = mn && mn.value !== '' ? +mn.value : null;
    filterMaxPrice = mx && mx.value !== '' ? +mx.value : null;
    currentPage = 1; updateProducts(); updateFilterBadge();
};

const onSaleEl = document.getElementById('onSaleFilter');
if (onSaleEl) onSaleEl.onchange = () => {
    filterOnSale = onSaleEl.checked;
    currentPage = 1; updateProducts(); updateFilterBadge();
};

const headerSearch = document.getElementById('searchInput');
if (headerSearch) headerSearch.oninput = e => {
    searchQuery = e.target.value.toLowerCase().trim();
    currentPage = 1; updateProducts();
};

// Build all sidebar filter panels
function renderFilters() {
    const catEl = document.getElementById('categoryFilters');
    if (catEl) {
        const cats = [...new Set(products.map(p => p.category).filter(Boolean))].sort();
        catEl.innerHTML = cats.length
            ? cats.map(c => `<label><input type="checkbox" value="${escapeHtml(c)}"> ${escapeHtml(c)}</label>`).join('')
            : '<span class="text-muted small">No categories</span>';
    }

    const vendorEl = document.getElementById('vendorFilters');
    if (vendorEl) {
        const list = allVendors.length
            ? allVendors
            : [...new Set(products.map(p => p.vendor).filter(Boolean))].sort().map(n => ({ name: n }));
        vendorEl.innerHTML = list.length
            ? list.map(v => `<label><input type="checkbox" value="${escapeHtml(v.name)}"> ${escapeHtml(v.name)}</label>`).join('')
            : '<span class="text-muted small">No stores</span>';
    }

    const ratingEl = document.getElementById('ratingFilter');
    if (ratingEl) {
        ratingEl.innerHTML = [4, 3, 2, 1].map(r =>
            `<button class="rating-btn" data-rating="${r}">${'<i class="fas fa-star"></i>'.repeat(r)}${'<i class="far fa-star"></i>'.repeat(5 - r)}<span class="text-muted small ms-1">& up</span></button>`
        ).join('');
        ratingEl.querySelectorAll('.rating-btn').forEach(btn => {
            btn.onclick = () => {
                const r = +btn.dataset.rating;
                if (filterMinRating === r) {
                    filterMinRating = 0;
                    btn.classList.remove('active');
                } else {
                    filterMinRating = r;
                    ratingEl.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                }
                currentPage = 1; updateProducts(); updateFilterBadge();
            };
        });
    }
}

function updateFilterBadge() {
    let n = selectedCategories.length + selectedVendors.length;
    if (filterMinPrice !== null || filterMaxPrice !== null) n++;
    if (filterMinRating > 0) n++;
    if (filterOnSale) n++;
    const badge = document.getElementById('filterBadge');
    if (badge) { badge.textContent = n; badge.style.display = n > 0 ? 'inline-flex' : 'none'; }
}

function updateProducts() {
    let filtered = products.filter(p => {
        if (dismissed.has(p.id)) return false;
        if (searchQuery) {
            const q = searchQuery;
            if (!(p.name.toLowerCase().includes(q) ||
                  (p.subtitle    && p.subtitle.toLowerCase().includes(q))    ||
                  (p.description && p.description.toLowerCase().includes(q)) ||
                  (p.vendor      && p.vendor.toLowerCase().includes(q)))) return false;
        }
        if (selectedCategories.length && !selectedCategories.includes(p.category)) return false;
        if (selectedVendors.length    && !selectedVendors.includes(p.vendor))      return false;
        if (filterMinPrice !== null   && p.effective_price < filterMinPrice)       return false;
        if (filterMaxPrice !== null   && p.effective_price > filterMaxPrice)       return false;
        if (filterMinRating > 0       && (p.rating_avg || 0) < filterMinRating)   return false;
        if (filterOnSale              && !p.is_on_sale)                            return false;
        return true;
    });

    switch (sortBy) {
        case 'price-low':  filtered.sort((a, b) => a.effective_price - b.effective_price); break;
        case 'price-high': filtered.sort((a, b) => b.effective_price - a.effective_price); break;
        case 'name-az':    filtered.sort((a, b) => a.name.localeCompare(b.name));          break;
        case 'popularity': filtered.sort((a, b) => (b.rating_avg || 0) - (a.rating_avg || 0)); break;
        default: break;
    }

    const total      = filtered.length;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    if (currentPage > totalPages) currentPage = totalPages;
    const paginated  = filtered.slice((currentPage - 1) * perPage, currentPage * perPage);

    renderProductGrid(paginated);
    renderPagination(total, totalPages);
    if (productCount) productCount.textContent = `${total} product${total !== 1 ? 's' : ''} found`;
}

function renderProductGrid(prods) {
    if (!productGrid) return;
    if (!prods.length) {
        productGrid.innerHTML = `<div class="col-12 text-center py-5">
            <i class="fas fa-box-open text-muted mb-3" style="font-size:48px;opacity:.4;display:block;"></i>
            <h5 class="fw-bold text-dark">No products found</h5>
            <p class="text-muted">Try adjusting your filters or search term.</p>
        </div>`;
        return;
    }
    productGrid.innerHTML = prods.map(p => {
        const rating  = parseFloat(p.rating_avg) || 0;
        const rounded = Math.round(rating);
        const stars   = '<i class=\'fas fa-star\'></i>'.repeat(Math.min(rounded, 5)) +
                        '<i class=\'far fa-star\'></i>'.repeat(Math.max(0, 5 - rounded));
        const liked   = wishlist.includes(p.id);
        return `<div class="card-agri product-card d-flex flex-column h-100" style="border:none;" data-id="${p.id}">
            <div class="position-relative p-4 text-center" style="background:var(--agri-bg);border-radius:var(--agri-radius-md) var(--agri-radius-md) 0 0;">
                <button class="btn btn-sm btn-light position-absolute rounded-circle"
                    style="top:10px;right:10px;width:32px;height:32px;z-index:10;" title="Dismiss"
                    onclick="dismissProduct(${p.id})"><i class="fas fa-times text-muted"></i></button>
                <button class="btn btn-sm position-absolute rounded-circle bg-white"
                    style="top:10px;left:10px;width:32px;height:32px;z-index:10;box-shadow:var(--agri-shadow-sm);"
                    title="Wishlist" onclick="toggleWishlist(${p.id})">
                    <i class="fa${liked ? 's text-danger' : 'r text-muted'} fa-heart"></i></button>
                ${p.is_on_sale ? '<span class="badge position-absolute" style="top:50px;left:10px;background:var(--agri-secondary);color:var(--agri-text-main);font-weight:bold;padding:5px 10px;">SALE!</span>' : ''}
                <a href="${p.url ?? '#'}">
                    <img src="${p.image_url ?? 'assets/img/products/default.png'}" alt="${escapeHtml(p.name)}"
                        style="height:180px;object-fit:contain;margin:0 auto;transition:transform .3s;" class="product-img-hover">
                </a>
            </div>
            <div class="p-4 d-flex flex-column flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-1">
                    <span class="badge bg-light text-success fw-medium px-2 py-1"
                        style="font-size:11px;text-transform:uppercase;">${escapeHtml(p.category ?? '')}</span>
                    ${p.vendor ? `<span class="text-muted" style="font-size:11px;white-space:nowrap;"><i class="fas fa-store me-1"></i>${escapeHtml(p.vendor)}</span>` : ''}
                </div>
                <div class="mb-2">
                    <span class="badge rounded-pill ${statusBadgeClass(p)}">${escapeHtml(String(statusLabel(p)).toUpperCase())}</span>
                </div>
                <h5 class="fw-bold text-dark mb-1">
                    <a href="${p.url ?? '#'}" class="text-decoration-none text-dark">${escapeHtml(p.name)}</a></h5>
                ${p.subtitle ? `<div class="text-muted small mb-2">${escapeHtml(p.subtitle)}</div>` : ''}
                <div class="product-rating mb-3" style="font-size:13px;">
                    <span class="text-warning">${stars}</span>
                    <span class="text-muted ms-1">(${rating.toFixed(1)})</span>
                </div>
                <div class="mt-auto">
                    <div class="mb-3">
                        ${p.is_on_sale ? `<span class="text-muted text-decoration-line-through small d-block">PKR ${formatPrice(p.price)}</span>` : ''}
                        <span class="fw-bold text-success fs-5">PKR ${formatPrice(p.effective_price)}</span>
                    </div>
                    <div class="d-flex gap-2">
                        ${canPurchase(p)
                            ? `<button class="btn-agri btn-agri-primary flex-grow-1"
                                style="padding:8px 10px;font-size:14px;" onclick="addToCart(${p.id})">
                                <i class="fas fa-shopping-cart me-1"></i> Add
                            </button>`
                            : `<button class="btn-agri flex-grow-1" style="padding:8px 10px;font-size:14px;background:#e5e7eb;color:#9ca3af;cursor:not-allowed;" disabled>${escapeHtml(statusLabel(p))}</button>`
                        }
                        <a class="btn-agri btn-agri-outline text-center" style="padding:8px 12px;font-size:14px;"
                            href="${p.url ?? '#'}" title="View details"><i class="far fa-eye"></i></a>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');
}

function renderPagination(total, totalPages) {
    if (!pagination) return;
    let html = '';
    if (totalPages > 1) {
        html += `<button ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">&lt;</button>`;
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || Math.abs(i - currentPage) <= 1) {
                html += `<button class="${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += '<button disabled>…</button>';
            }
        }
        html += `<button ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">&gt;</button>`;
    }
    pagination.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    updateProducts();
    window.scrollTo({ top: (productGrid?.offsetTop ?? 0) - 100, behavior: 'smooth' });
}

function formatPrice(p) { return new Intl.NumberFormat('en-PK').format(p ?? 0); }

function addToCart(productId) {
    const product = productById.get(String(productId));
    if (product && !canPurchase(product)) {
        showToast(statusLabel(product));
        return;
    }
    if (window.CartManager && typeof window.CartManager.addToCart === 'function') {
        window.CartManager.addToCart(productId, 1);
    } else {
        showToast('Please refresh the page.');
    }
}

function toggleWishlist(id) {
    const idx = wishlist.indexOf(id);
    if (idx === -1) { wishlist.push(id); showToast('Added to wishlist'); }
    else            { wishlist.splice(idx, 1); showToast('Removed from wishlist'); }
    updateProducts();
}

function dismissProduct(id) { dismissed.add(id); updateProducts(); }

function showToast(msg) {
    toast.textContent = msg;
    toast.className = 'visible';
    setTimeout(() => (toast.className = ''), 2500);
}

function escapeHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function stockState(product) {
    const trackStock = product?.track_stock !== false;
    const isAvailable = product?.is_available !== false;
    const quantity = Number(product?.stock_quantity ?? 0);

    if (!trackStock) {
        return 'In Stock';
    }

    if (!isAvailable) {
        return 'Unavailable';
    }

    if (quantity <= 0) {
        return 'Out of Stock';
    }

    return 'In Stock';
}

function statusLabel(product) {
    return stockState(product);
}

function statusBadgeClass(product) {
    const state = stockState(product);
    if (state === 'Unavailable') return 'bg-secondary';
    if (state === 'Out of Stock') return 'bg-danger';
    return 'bg-success';
}

function canPurchase(product) {
    return stockState(product) === 'In Stock';
}
