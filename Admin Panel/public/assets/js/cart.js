/**
 * Plantix CartManager -- backend-wired cart.
 * All state lives on the server (DB + session).
 * localStorage is NOT used for cart or coupon data.
 */

const CartManager = {

    get csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    },

    route(name) {
        return (window.CART_ROUTES ?? {})[name] ?? null;
    },

    async _request(url, method = 'GET', body = null) {
        const headers = { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken };
        if (body) headers['Content-Type'] = 'application/json';
        const resp = await fetch(url, { method, headers, body: body ? JSON.stringify(body) : undefined });
        const json = await resp.json().catch(() => ({}));
        if (!resp.ok) {
            const firstValidationError = json?.errors
                ? Object.values(json.errors).flat()[0]
                : null;
            throw new Error(firstValidationError ?? json.error ?? json.message ?? `HTTP ${resp.status}`);
        }
        return json;
    },

    async addToCart(productId, quantity = 1) {
        try {
            if (window.CART_ROUTES?.auth === false) {
                this.showNotification('Please sign in to add items to your cart.', 'info');
                setTimeout(() => { window.location.href = window.CART_ROUTES?.loginUrl ?? '/signin'; }, 900);
                return;
            }
            const url = this.route('cart.add') ?? '/cart/add';
            const data = await this._request(url, 'POST', { product_id: productId, quantity });
            this.updateCartBadge(data.cart_count ?? null);
            this.showNotification('Added to cart!', 'success');
            this.refreshMiniCart();
            return data;
        } catch (err) {
            this.showNotification(err.message || 'Could not add to cart.', 'error');
        }
    },

    async removeFromCart(itemId) {
        try {
            const base = this.route('cart.remove') ?? '/cart/{id}';
            const url = base.replace(/\{[^}]+\}/, itemId);
            const data = await this._request(url, 'DELETE');
            this.updateCartBadge(null);
            this.showNotification('Item removed.', 'info');
            this.refreshMiniCart();
            return data;
        } catch (err) {
            this.showNotification(err.message || 'Could not remove item.', 'error');
        }
    },

    async updateQuantity(itemId, quantity) {
        try {
            const base = this.route('cart.update') ?? '/cart/{id}';
            const url = base.replace(/\{[^}]+\}/, itemId);
            const data = await this._request(url, 'PATCH', { quantity });
            this.updateCartBadge(null);
            this.refreshMiniCart();
            return data;
        } catch (err) {
            this.showNotification(err.message || 'Could not update quantity.', 'error');
        }
    },

    async fetchCount() {
        try {
            const url = this.route('cart.count') ?? '/cart/count';
            const data = await this._request(url);
            return data.count ?? 0;
        } catch { return 0; }
    },

    updateCartBadge(count) {
        const update = n => document.querySelectorAll('.attr-nav .badge').forEach(b => b.textContent = n);
        if (count !== null && count !== undefined) { update(count); return; }
        this.fetchCount().then(update);
    },

    async refreshMiniCart() {
        const dropdown = document.querySelector('.dropdown-menu.cart-list');
        if (!dropdown) return;
        try {
            const url = this.route('cart.mini') ?? '/cart/mini';
            const data = await this._request(url);
            const count = data.count ?? 0;
            dropdown.innerHTML = '';
            if (!count || !data.items?.length) {
                dropdown.innerHTML = '<li><p class="text-center p-3 text-muted small mb-0">Your cart is empty.</p></li>';
            } else {
                data.items.forEach(item => {
                    const li = document.createElement('li');
                    li.style.cssText = 'padding:10px 15px;border-bottom:1px solid #f0f0f0;';
                    li.innerHTML = `<div class="d-flex align-items-center gap-2">
                        ${item.image ? `<img src="${item.image}" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:6px;border:1px solid #eee;">` : `<div style="width:44px;height:44px;border-radius:6px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;"><i class="fas fa-seedling text-muted"></i></div>`}
                        <div class="flex-grow-1" style="min-width:0;">
                            <a href="${item.url}" class="text-dark text-decoration-none fw-medium" style="font-size:13px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.name}</a>
                            <span class="text-muted" style="font-size:12px;">${item.quantity}x PKR ${Number(item.price).toLocaleString()}</span>
                        </div>
                        <button class="btn btn-sm text-danger bg-transparent border-0 p-0 ms-1" data-remove-item="${item.id}" title="Remove"><i class="fas fa-times" style="font-size:11px;"></i></button>
                    </div>`;
                    dropdown.appendChild(li);
                });
                const totalLi = document.createElement('li');
                totalLi.className = 'total';
                totalLi.style.padding = '12px 15px';
                totalLi.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted" style="font-size:13px;">Subtotal</span>
                        <strong>PKR ${Number(data.subtotal).toLocaleString()}</strong>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="/cart"     class="btn btn-sm flex-grow-1 border text-dark bg-white"                        style="font-size:12px;">View Cart</a>
                        <a href="/checkout" class="btn btn-sm flex-grow-1 text-white"                        style="font-size:12px;background:var(--agri-primary,#1e7e34);">Checkout</a>
                    </div>`;
                dropdown.appendChild(totalLi);
            }
            dropdown.querySelectorAll('[data-remove-item]').forEach(btn => {
                btn.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); this.removeFromCart(btn.dataset.removeItem); });
            });
            this.updateCartBadge(count);
        } catch { /* fail silently */ }
    },

    showNotification(message, type = 'success') {
        document.querySelector('.cart-notification')?.remove();
        const colors = { success: '#1e7e34', error: '#dc3545', info: '#0077cc' };
        const el = document.createElement('div');
        el.className = 'cart-notification';
        el.style.cssText = `position:fixed;top:100px;right:20px;z-index:99999;background:${colors[type]??colors.success};color:#fff;padding:12px 20px;border-radius:8px;font-size:14px;font-weight:500;box-shadow:0 4px 16px rgba(0,0,0,0.15);animation:cartNotifIn 0.3s ease;max-width:300px;`;
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => { el.style.animation = 'cartNotifOut 0.3s ease forwards'; setTimeout(() => el.remove(), 300); }, 3000);
    },

    init() {
        this.refreshMiniCart();
        document.addEventListener('click', e => {
            const btn = e.target.closest('[data-add-to-cart]');
            if (!btn) return;
            e.preventDefault();
            const productId = btn.dataset.addToCart;
            const qtyInput  = document.getElementById('productQty') ?? document.querySelector(`[data-product-qty="${productId}"]`);
            const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value) || 1) : 1;
            this.addToCart(productId, qty);
        });
    },
};

// Animations
(() => {
    const s = document.createElement('style');
    s.textContent = `@keyframes cartNotifIn{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}@keyframes cartNotifOut{from{transform:translateX(0);opacity:1}to{transform:translateX(120%);opacity:0}}#toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#1e7e34;color:#fff;padding:10px 22px;border-radius:24px;font-size:14px;font-weight:500;box-shadow:0 4px 16px rgba(0,0,0,.2);opacity:0;pointer-events:none;transition:opacity .3s;z-index:99998;max-width:90vw;text-align:center;}#toast.show{opacity:1;}`;
    document.head.appendChild(s);
})();

if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', () => CartManager.init()); } else { CartManager.init(); }
window.CartManager = CartManager;
