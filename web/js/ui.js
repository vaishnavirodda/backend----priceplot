import { API } from './api.js?v=3';

export const UI = {
    setLoading: (show) => {
        const el = document.getElementById('loading-overlay');
        if (show) el.classList.remove('d-none');
        else el.classList.add('d-none');
    },

    showToast: (message) => {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerText = message;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    },

    showScreen: (screenId) => {
        ['login-screen', 'register-screen', 'main-screen'].forEach(id => {
            document.getElementById(id).classList.add('d-none');
        });
        document.getElementById(screenId).classList.remove('d-none');
    },

    setGreeting: (name, email) => {
        const h = new Date().getHours();
        let greet = 'Hello';
        if (h >= 5 && h < 12) greet = 'Good morning';
        else if (h >= 12 && h < 17) greet = 'Good afternoon';
        else if (h >= 17 && h < 21) greet = 'Good evening';
        document.getElementById('greeting-text').innerText = `${greet}, ${name} ⚡`;
        
        document.getElementById('profile-name').innerText = name;
        document.getElementById('profile-email').innerText = email;
    },

    showView: (viewId) => {
        document.querySelectorAll('.view-section').forEach(el => el.classList.add('d-none'));
        document.getElementById(viewId).classList.remove('d-none');
        
        document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
        const activeNav = document.querySelector(`.nav-item[data-view="${viewId}"]`);
        if (activeNav) activeNav.classList.add('active');

        // Dynamically load data based on the view
        if (viewId === 'home-view') UI.loadDeals();
        if (viewId === 'wishlist-view') UI.loadWishlist();
        if (viewId === 'cart-view') UI.loadCart();
        if (viewId === 'searches-view') UI.loadSearches();
    },

    renderProductDetails: (product) => {
        document.querySelectorAll('.view-section').forEach(el => el.classList.add('d-none'));
        document.getElementById('product-view').classList.remove('d-none');
        
        const container = document.getElementById('product-details-container');
        
        let cheapestPlatform = product.bestPrice ? product.bestPrice.platform : null;

        const pricesHtml = (product.prices || []).map(p => {
            const isCheapest = p.platform === cheapestPlatform;
            const rowBg = isCheapest ? '#ECFCCB' : 'transparent';
            const nameColor = isCheapest ? 'var(--rs-green)' : 'var(--text-dark)';
            const priceColor = isCheapest ? 'var(--rs-green)' : 'var(--orange-accent)';
            const badgeHtml = isCheapest ? `<span style="background:var(--rs-green);color:white;padding:3px 8px;border-radius:12px;font-size:11px;margin-left:8px;font-weight:700;line-height:1;">Cheapest</span>` : '';
            
            return `
            <div style="display:flex; justify-content:space-between; padding:16px 8px; border-bottom:1px solid var(--divider); align-items:center; background:${rowBg}; border-radius:${isCheapest ? '8px' : '0'}; margin-bottom:${isCheapest ? '8px' : '0'};">
                <div style="flex:1;">
                    <div style="font-weight:600; font-size:16px; display:flex; align-items:center; color:${nameColor};">
                        ${p.platform} ${badgeHtml}
                    </div>
                    <div style="color:var(--rs-green); font-size:12px; margin-top:4px;">${p.availability || 'In Stock'}</div>
                </div>
                <div style="display:flex; align-items:center; gap:20px;">
                    <div style="color:${priceColor}; font-weight:700; font-size:18px;">₹${p.price}</div>
                    <a href="${p.link}" target="_blank" class="btn-primary" style="padding:8px 24px; text-decoration:none; font-size:14px; border-radius:8px;">Visit</a>
                </div>
            </div>
            `;
        }).join('');

        const bestPriceHtml = product.bestPrice ? `
            <div style="background:var(--rs-green); padding:24px; border-radius:16px; color:white; display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                <div>
                    <div style="font-size:14px; opacity:0.9; margin-bottom:4px;">Cheapest Option</div>
                    <div style="font-size:36px; font-weight:800; margin-bottom:4px; letter-spacing: -0.5px;">₹${product.bestPrice.price}</div>
                    <div style="font-size:14px; opacity:0.9;">on ${product.bestPrice.platform}</div>
                </div>
                <div style="font-size:56px;">🏆</div>
            </div>
        ` : '';

        container.innerHTML = `
            <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.1); border: 1px solid var(--divider);">
                
                <!-- Top Blue Header -->
                <div style="background: var(--nav-blue); padding: 24px 24px 32px 24px; text-align: center;">
                    
                    <!-- Back Button Row -->
                    <div style="display: flex; align-items: center; margin-bottom: 32px;" onclick="UI.showView('home-view')">
                        <div style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; cursor: pointer; font-size: 20px;">
                            ←
                        </div>
                        <h2 style="color: white; font-size: 20px; font-weight: 600; margin-left: 16px;">Price Comparison</h2>
                    </div>

                    <div style="background: white; border-radius: 16px; padding: 24px; display: inline-block; margin-bottom: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
                        <img src="${product.productImage || ''}" style="height: 180px; width: 180px; object-fit: contain;" onerror="this.style.display='none'">
                    </div>
                    <h2 style="color: white; font-size: 18px; line-height: 1.5; font-weight: 500; text-align: center;">${product.productName || 'Product Details'}</h2>
                </div>

                <!-- Middle Content -->
                <div style="padding: 24px;">
                    ${bestPriceHtml}
                    
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin-bottom: 16px;">All Prices</h3>
                    <div style="display: flex; flex-direction: column; gap: 0;">
                        ${pricesHtml || '<div style="padding:16px;">No prices available</div>'}
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div style="padding: 24px; border-top: 1px solid var(--divider); display: flex; gap: 16px; justify-content: center; background: #F8FAFC;">
                    <button class="btn-primary" style="flex:1; background: var(--orange-accent); font-size: 16px; border-radius: 12px; padding: 14px;" onclick="window.addToCart(${product.productId})">🛒 Add to Cart</button>
                    <button class="btn-primary" style="flex:1; background: var(--nav-blue); font-size: 16px; border-radius: 12px; padding: 14px;" onclick="window.addToWishlist(${product.productId})">❤️ Add to Wishlist</button>
                </div>
            </div>
        `;
    },

    loadDeals: async () => {
        UI.setLoading(true);
        const res = await API.getFlashDeals();
        UI.setLoading(false);
        const container = document.getElementById('deals-container');
        container.innerHTML = '';
        if (res.success && res.data && res.data.length > 0) {
            res.data.forEach(deal => {
                const el = document.createElement('div');
                el.className = 'web-card';
                el.innerHTML = `
                    <div class="web-card-img">${deal.emoji || '🔥'}</div>
                    <div class="web-card-title">${deal.title}</div>
                    <div class="web-card-price-row">
                        <span class="web-price">$${deal.price}</span>
                        <span class="web-old-price">$${deal.original_price}</span>
                    </div>
                    <div class="web-badge">${deal.platform} - ${deal.discount_pct}% OFF</div>
                `;
                container.appendChild(el);
            });
        } else {
            container.innerHTML = '<p style="color:var(--text-light)">No deals currently available.</p>';
        }
    },

    loadWishlist: async () => {
        UI.setLoading(true);
        const res = await API.getWishlist();
        UI.setLoading(false);
        const container = document.getElementById('wishlist-container');
        container.innerHTML = '';
        if (res.success && res.data && res.data.length > 0) {
            res.data.forEach(item => {
                const el = document.createElement('div');
                el.className = 'web-card';
                const imgSrc = item.product_image_url ? `<img src="${item.product_image_url}">` : '🛍️';
                el.innerHTML = `
                    <div class="web-card-img">${imgSrc}</div>
                    <div class="web-card-title">${item.product_name}</div>
                    <div class="web-card-price-row">
                        <span class="web-price">₹${item.current_price || '0'}</span>
                    </div>
                    <div class="web-badge" style="background:var(--divider);color:var(--text-medium);">${item.platform || 'General'}</div>
                    <button class="web-rm-btn rm-wishlist" data-id="${item.product_id}">Remove Item</button>
                `;
                container.appendChild(el);
            });
        } else {
            container.innerHTML = '<p style="color:var(--text-light); text-align:center; grid-column: 1/-1;">Your wishlist is completely empty.</p>';
        }
    },
    
    loadCart: async () => {
        UI.setLoading(true);
        const res = await API.getCart();
        UI.setLoading(false);
        const container = document.getElementById('cart-container');
        container.innerHTML = '';
        if (res.success && res.data && res.data.length > 0) {
            res.data.forEach(item => {
                const el = document.createElement('div');
                el.className = 'web-card';
                const imgSrc = item.product_image_url ? `<img src="${item.product_image_url}">` : '🛒';
                el.innerHTML = `
                    <div class="web-card-img">${imgSrc}</div>
                    <div class="web-card-title">${item.product_name}</div>
                    <div class="web-card-price-row">
                        <span class="web-price">₹${item.current_price || '0'}</span>
                    </div>
                    <div style="font-size:14px; font-weight:600; color:var(--text-dark); margin-bottom:auto;">Quantity: ${item.quantity}</div>
                    <button class="web-rm-btn rm-cart" data-id="${item.product_id}">Remove Item</button>
                `;
                container.appendChild(el);
            });
        } else {
            container.innerHTML = '<p style="color:var(--text-light); text-align:center; grid-column: 1/-1;">Your cart is entirely empty.</p>';
        }
    },

    loadSearches: async () => {
        UI.setLoading(true);
        const res = await API.getSearchHistory();
        UI.setLoading(false);
        const container = document.getElementById('searches-container');
        container.innerHTML = '';
        if (res.success && res.data && res.data.length > 0) {
            res.data.forEach(item => {
                const el = document.createElement('div');
                el.className = 'web-card';
                const imgSrc = item.product_image_url ? `<img src="${item.product_image_url}">` : '⏱️';
                el.innerHTML = `
                    <div class="web-card-img">${imgSrc}</div>
                    <div class="web-card-title">${item.product_name}</div>
                    <div style="margin-top:auto; font-size:12px; color:var(--text-light);">Searched: ${item.searched_at}</div>
                    <button class="btn-primary" style="margin-top:12px; font-size:13px; padding:6px 12px;" onclick="window.trackProduct('${item.original_url}')">View Comparison</button>
                `;
                container.appendChild(el);
            });
        } else {
            container.innerHTML = '<p style="color:var(--text-light); text-align:center; grid-column: 1/-1;">You have no prior search history.</p>';
        }
    }
};
