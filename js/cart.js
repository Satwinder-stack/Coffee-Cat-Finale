document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const mobileNav = document.getElementById('mobile-nav');
    if (hamburger && mobileNav) {
        hamburger.addEventListener('click', function() {
            mobileNav.classList.toggle('open');
            const expanded = hamburger.getAttribute('aria-expanded') === 'true';
            hamburger.setAttribute('aria-expanded', !expanded);
        });
    }

    const cartContent = document.getElementById('cart-content');
    const clearCartBtn = document.getElementById('clear-cart-btn');
    function getCartItems() {
        return JSON.parse(localStorage.getItem('coffeeCatCart') || '[]');
    }
    function saveCart(cart) {
        localStorage.setItem('coffeeCatCart', JSON.stringify(cart));
    }
    const MENU_VARIANTS = {
        'Cucumber Calico': [
            { variant: 'ICED', price: 120 },
            { variant: 'PITCHER', price: 250 }
        ],
        'Iced Tea': [
            { variant: 'ICED', price: 99 },
            { variant: 'PITCHER', price: 230 }
        ],
    };
    function renderCart() {
        const cart = getCartItems();
        if (cart.length === 0) {
            cartContent.innerHTML = '<div class="cart-empty">Your cart is empty. Add items from the <a href="menu.html" style="text-decoration: none; color: green;">Menu</a>.</div>';
            clearCartBtn.style.display = 'none';
            return;
        }
        let total = 0;
        let html = '<ul>';
        cart.forEach((item, idx) => {
            let itemVariants = MENU_VARIANTS[item.name] || [{ variant: item.variant || '', price: item.price }];
            let variantSelect = '';
            if (itemVariants.length > 1) {
                const chosenVariant = item.variant || itemVariants[0].variant;
                if (chosenVariant) {
                  variantSelect = `<span class="cart-variant">${chosenVariant}</span>`;
                }
            } else if (itemVariants[0].variant) {
                variantSelect = `<span class="cart-variant">${itemVariants[0].variant}</span>`;
            }
            let priceFromVariant = (itemVariants.find(v => v.variant === (item.variant||'')) || itemVariants[0] || {}).price;
            let fallbackPrice = (typeof item.price === 'string') ? parseInt(item.price.replace(/[^\d]/g, '')) : Number(item.price);
            let currentPrice = Number(priceFromVariant) || fallbackPrice || 0;
            total += currentPrice * Number(item.qty||1);
            html += `<li>
                <span class="cart-item-name">${item.name}</span>
                ${variantSelect}
                <span class="cart-price">₱${currentPrice}</span>
                <div class="cart-qty-control">
                    <button class="cart-qty-btn" data-index="${idx}" data-action="dec">-</button>
                    <input type="number" class="cart-qty-input" min="1" value="${item.qty}" data-index="${idx}">
                    <button class="cart-qty-btn" data-index="${idx}" data-action="inc">+</button>
                </div>
                <button class="cart-remove-btn" data-index="${idx}" title="Remove">×</button>
            </li>`;
        });
        html += '</ul>';
        html += `<div class="cart-total">Total: ₱${total}</div>`;
        cartContent.innerHTML = html;
        clearCartBtn.style.display = '';
        setupCartEvents();
    }
    function setupCartEvents() {
        document.querySelectorAll('.cart-qty-input').forEach(input => {
            input.addEventListener('change', function() {
                const idx = Number(input.dataset.index);
                let cart = getCartItems();
                let val = Number(input.value);
                if(val < 1) val = 1;
                cart[idx].qty = val;
                saveCart(cart);
                renderCart();
            });
        });
        document.querySelectorAll('.cart-qty-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = Number(btn.dataset.index);
                let cart = getCartItems();
                let act = btn.dataset.action;
                if(act=='inc'){cart[idx].qty+=1;}
                else if(act=='dec' && cart[idx].qty>1){cart[idx].qty-=1;}
                saveCart(cart);
                renderCart();
            });
        });
        document.querySelectorAll('.cart-variant-select').forEach(sel => {
            sel.addEventListener('change', function() {
                const idx = Number(sel.dataset.index);
                let cart = getCartItems();
                const selected = sel.options[sel.selectedIndex];
                cart[idx].variant = selected.value;
                cart[idx].price = Number(selected.getAttribute('data-price'));
                saveCart(cart);
                renderCart();
            });
        });
        document.querySelectorAll('.cart-remove-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = Number(btn.dataset.index);
                let cart = getCartItems();
                cart.splice(idx,1);
                saveCart(cart);
                renderCart();
            });
        });
    }
    clearCartBtn.addEventListener('click', function() {
        localStorage.removeItem('coffeeCatCart');
        renderCart();
    });
    renderCart();
});
