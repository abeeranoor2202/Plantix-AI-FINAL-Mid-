/**
 * E-Commerce Cart Management System
 * Handles add/remove/update cart items with localStorage persistence
 */

const CartManager = {
    
    // Get cart from localStorage
    getCart: function() {
        const cart = localStorage.getItem('plantixCart');
        return cart ? JSON.parse(cart) : [];
    },

    // Save cart to localStorage
    saveCart: function(cart) {
        localStorage.setItem('plantixCart', JSON.stringify(cart));
        this.updateCartBadge();
        this.updateMiniCart();
    },

    // Add item to cart
    addToCart: function(product) {
        let cart = this.getCart();
        
        // Check if product already exists
        const existingIndex = cart.findIndex(item => item.id === product.id);
        
        if (existingIndex > -1) {
            // Increase quantity
            cart[existingIndex].quantity += product.quantity || 1;
        } else {
            // Add new item
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image,
                quantity: product.quantity || 1,
                tags: product.tags || []
            });
        }
        
        this.saveCart(cart);
        this.showNotification('Product added to cart!', 'success');
        return cart;
    },

    // Remove item from cart
    removeFromCart: function(productId) {
        let cart = this.getCart();
        cart = cart.filter(item => item.id !== productId);
        this.saveCart(cart);
        this.showNotification('Product removed from cart', 'info');
        return cart;
    },

    // Update quantity
    updateQuantity: function(productId, quantity) {
        let cart = this.getCart();
        const item = cart.find(item => item.id === productId);
        
        if (item) {
            if (quantity > 0) {
                item.quantity = quantity;
            } else {
                return this.removeFromCart(productId);
            }
        }
        
        this.saveCart(cart);
        return cart;
    },

    // Clear cart
    clearCart: function() {
        localStorage.removeItem('plantixCart');
        this.updateCartBadge();
        this.updateMiniCart();
    },

    // Get cart total
    getCartTotal: function() {
        const cart = this.getCart();
        return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    },

    // Get total after applying coupon (if any)
    getCartTotalWithDiscount: function() {
        const total = this.getCartTotal();
        const coupon = this.getCoupon();
        if (!coupon) return total;

        // support percent type coupon for now
        if (coupon.type === 'percent') {
            const discount = (total * (parseFloat(coupon.value) || 0)) / 100;
            return Math.max(0, total - discount);
        }

        // support fixed amount
        if (coupon.type === 'fixed') {
            return Math.max(0, total - (parseFloat(coupon.value) || 0));
        }

        return total;
    },

    // Get cart item count
    getCartCount: function() {
        const cart = this.getCart();
        return cart.reduce((count, item) => count + item.quantity, 0);
    },

    // Update cart badge
    updateCartBadge: function() {
        const count = this.getCartCount();
        const badges = document.querySelectorAll('.attr-nav .badge');
        badges.forEach(badge => {
            badge.textContent = count;
        });
    },

    // Update mini cart dropdown
    updateMiniCart: function() {
        const cart = this.getCart();
        const cartList = document.querySelector('.dropdown-menu.cart-list');
        
        if (!cartList) return;

        // Clear existing items except total
        const existingItems = cartList.querySelectorAll('li:not(.total)');
        existingItems.forEach(item => item.remove());

        // Add cart items
        if (cart.length === 0) {
            const emptyLi = document.createElement('li');
            emptyLi.innerHTML = '<p style="padding: 20px; text-align: center;">Your cart is empty</p>';
            cartList.insertBefore(emptyLi, cartList.querySelector('.total'));
        } else {
            cart.slice(0, 3).forEach(item => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <div class="thumb">
                        <a href="shop-single.html" class="photo">
                            <img src="${item.image}" alt="${item.name}">
                        </a>
                        <a href="#" class="remove-product" data-id="${item.id}">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                    <div class="info">
                        <h6><a href="shop-single.html">${item.name}</a></h6>
                        <p>${item.quantity}x - <span class="price">PKR ${item.price.toLocaleString()}</span></p>
                    </div>
                `;
                cartList.insertBefore(li, cartList.querySelector('.total'));
            });
        }

        // Update total and show coupon if present
        const totalSpan = cartList.querySelector('.total .pull-right');
        const coupon = this.getCoupon();
        if (totalSpan) {
            const subtotal = this.getCartTotal();
            if (coupon) {
                let discountAmount = 0;
                if (coupon.type === 'percent') {
                    discountAmount = (subtotal * (parseFloat(coupon.value) || 0)) / 100;
                } else if (coupon.type === 'fixed') {
                    discountAmount = parseFloat(coupon.value) || 0;
                }
                const totalAfter = this.getCartTotalWithDiscount();
                totalSpan.innerHTML = `
                    <div style="text-align:right">
                        <div><strong>Subtotal</strong>: PKR ${subtotal.toLocaleString()}</div>
                        <div style="color:#28a745"><strong>Coupon (${coupon.code})</strong>: -PKR ${discountAmount.toLocaleString()}</div>
                        <div style="font-size:16px; margin-top:6px;"><strong>Total</strong>: PKR ${totalAfter.toLocaleString()}</div>
                    </div>
                `;
            } else {
                totalSpan.innerHTML = `<strong>Total</strong>: PKR ${subtotal.toLocaleString()}`;
            }
        }

        // Attach remove event listeners
        cartList.querySelectorAll('.remove-product').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.getAttribute('data-id');
                this.removeFromCart(productId);
            });
        });
    },

    // Coupon helpers
    setCoupon: function(couponObj) {
        // couponObj should be { code: 'PLANTIX-10', type: 'percent'|'fixed', value: number }
        if (!couponObj || !couponObj.code) return;
        // normalize code to alphanumeric uppercase for compatibility with existing promo logic
        const normalized = (couponObj.code||'').toString().replace(/[^A-Z0-9]/gi,'').toUpperCase();
        const stored = Object.assign({}, couponObj, { code: normalized });
        localStorage.setItem('plantixCoupon', JSON.stringify(stored));
        // also write legacy key used by cart/checkout pages
        try { localStorage.setItem('plantixPromo', JSON.stringify({ code: normalized })); } catch(e) {}
        this.updateMiniCart();
    },

    getCoupon: function() {
        const c = localStorage.getItem('plantixCoupon');
        if (c) return JSON.parse(c);
        // fallback to legacy plantixPromo key used by cart/checkout pages
        try {
            const legacy = JSON.parse(localStorage.getItem('plantixPromo') || 'null');
            if (legacy && legacy.code) {
                const code = (legacy.code||'').toString().replace(/[^A-Z0-9]/gi,'').toUpperCase();
                // map known promos to coupon shapes
                if (code === 'PLANTIX10') return { code, type: 'percent', value: 10 };
                if (code === 'FREESHIP') return { code, type: 'freeship', value: 0 };
                if (code === 'SAVE500') return { code, type: 'flat', value: 500 };
                // unknown promo: return basic code only
                return { code };
            }
        } catch (e) {
            // ignore parse errors
        }
        return null;
    },

    removeCoupon: function() {
        localStorage.removeItem('plantixCoupon');
        this.updateMiniCart();
    },

    // Show notification
    showNotification: function(message, type = 'success') {
        // Remove existing notification
        const existing = document.querySelector('.cart-notification');
        if (existing) existing.remove();

        // Create notification
        const notification = document.createElement('div');
        notification.className = `cart-notification alert-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : '#17a2b8'};
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideInRight 0.3s ease;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    },

    // Initialize cart
    init: function() {
        this.updateCartBadge();
        // Ensure default coupon exists in localStorage (client-side only)
        try {
            if (!this.getCoupon()) {
                // silently set default coupon PLANTIX10 = 10% off (normalize to alphanumeric)
                this.setCoupon({ code: 'PLANTIX10', type: 'percent', value: 10 });
            }
        } catch (e) {
            // ignore storage errors
            console.warn('Coupon init error', e);
        }
        this.updateMiniCart();

        // Handle Add to Cart buttons
        document.querySelectorAll('.cart-btn, .btn-cart-add').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Get product data from button's parent product card
                const productCard = btn.closest('.product, .product-contents, .single-product-contents');
                if (!productCard) return;

                const product = {
                    id: productCard.getAttribute('data-product-id') || 'product-' + Date.now(),
                    name: productCard.querySelector('.product-title a, h2.product-title')?.textContent.trim() || 'Product',
                    price: parseFloat(productCard.querySelector('.price span:not(del)')?.textContent.replace(/[^0-9.]/g, '') || 0),
                    image: productCard.querySelector('img')?.getAttribute('src') || 
                           document.querySelector('.product-thumb .carousel-item.active img')?.getAttribute('src') || 
                           'assets/img/products/1.png',
                    quantity: parseInt(productCard.querySelector('#quantity')?.value || 1),
                    tags: Array.from(productCard.querySelectorAll('.product-tags a')).map(a => a.textContent.trim())
                };

                this.addToCart(product);
            });
        });

        // Link mini-cart buttons to cart/checkout pages
        document.querySelectorAll('.btn-cart').forEach(btn => {
            if (btn.textContent.includes('Cart')) {
                btn.setAttribute('href', 'cart.html');
            } else if (btn.textContent.includes('Checkout')) {
                btn.setAttribute('href', 'checkout.html');
            }
        });
    }
};

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => CartManager.init());
} else {
    CartManager.init();
}

// Export for use in other scripts
window.CartManager = CartManager;
