/* ============================================================
   ALKOBLAN - CART.JS
   Server-backed cart (session-based). Add-to-cart buttons POST
   to /cart/add via fetch and update the nav badge + show a toast.
   The cart/checkout pages themselves are rendered server-side.
   ============================================================ */

const Cart = {
    csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    },

    async add(productId, variantId = null, qty = 1, name = '') {
        try {
            const res = await fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ product_id: productId, variant_id: variantId, quantity: qty }),
            });
            const data = await res.json();
            if (data.success) {
                this.updateBadge(data.count);
                this.showToast(name || data.product_name || 'المنتج');
            } else {
                this.showToast(data.message || 'حدث خطأ', true);
            }
        } catch (e) {
            this.showToast('تعذر الاتصال بالخادم', true);
        }
    },

    updateBadge(count) {
        document.querySelectorAll('.nav-cart-count, .cart-count-badge').forEach(el => {
            el.textContent = count;
        });
    },

    showToast(name, isError = false) {
        const old = document.querySelector('.cart-toast');
        if (old) old.remove();

        const toast = document.createElement('div');
        toast.className = 'cart-toast';
        toast.innerHTML = isError ? `
            <div class="cart-toast-inner" style="border-right-color:#c0392b">
                <i class="bi bi-exclamation-circle-fill" style="color:#c0392b"></i>
                <span>${name}</span>
            </div>
        ` : `
            <div class="cart-toast-inner">
                <i class="bi bi-check-circle-fill"></i>
                <span>تمت الإضافة: <strong>${name}</strong></span>
                <a href="/cart" class="cart-toast-link">عرض السلة <i class="bi bi-arrow-left"></i></a>
            </div>
        `;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    },

    bindButtons() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-add-to-cart]');
            if (!btn) return;
            e.preventDefault();
            const card = btn.closest('[data-product-id]');
            const productId = card ? card.dataset.productId : btn.dataset.productId;
            const name = card?.querySelector('.product-card-title, h3')?.textContent?.trim()
                || document.querySelector('h1.product-title')?.textContent?.trim() || '';
            if (productId) {
                this.add(productId, btn.dataset.variantId || null, 1, name);
            }
        });
    },
};

const toastStyle = document.createElement('style');
toastStyle.textContent = `
.cart-toast {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    z-index: 99999;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    pointer-events: none;
}
.cart-toast.show {
    transform: translateX(-50%) translateY(0);
    pointer-events: all;
}
.cart-toast-inner {
    background: var(--dark, #1a1a2e);
    color: #fff;
    padding: 14px 24px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    font-family: var(--font-ar, 'Cairo', sans-serif);
    font-size: 0.95rem;
    white-space: nowrap;
    min-width: 280px;
    justify-content: center;
    border-right: 4px solid #27ae60;
}
.cart-toast-inner .bi-check-circle-fill { color: #27ae60; font-size: 1.2rem; }
.cart-toast-link {
    color: var(--accent-gold, #ac9273);
    text-decoration: none;
    font-weight: 700;
    margin-right: 8px;
    white-space: nowrap;
}
.cart-toast-link:hover { text-decoration: underline; }
@media (max-width: 480px) {
    .cart-toast { bottom: 80px; width: 92%; left: 4%; transform: translateY(120px); }
    .cart-toast.show { transform: translateY(0); }
    .cart-toast-inner { min-width: unset; width: 100%; border-radius: 14px; flex-wrap: wrap; justify-content: center; }
}
`;
document.head.appendChild(toastStyle);

document.addEventListener('DOMContentLoaded', () => {
    Cart.bindButtons();

    const mainAddBtn = document.querySelector('#mainAddToCart');
    if (mainAddBtn) {
        mainAddBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const qty = parseInt(document.querySelector('#qtyInput')?.value || 1);
            const productId = mainAddBtn.dataset.productId;
            const variantId = document.querySelector('[name="variant_id"]:checked')?.value || null;
            const name = document.querySelector('h1.product-title')?.textContent?.trim() || '';
            Cart.add(productId, variantId, qty, name);
        });
    }
});
