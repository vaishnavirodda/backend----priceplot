import { Auth } from './auth.js?v=3';
import { UI } from './ui.js?v=3';
import { API } from './api.js?v=3';

document.addEventListener('DOMContentLoaded', () => {

    // 1. Initialize Authentication and Route Guard
    Auth.init();
    const userJson = localStorage.getItem('user');
    if (userJson) {
        const user = JSON.parse(userJson);
        UI.setGreeting(user.username, user.email);
    }

    // 2. Auth Form Handlers
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const pass = document.getElementById('login-password').value;
        await Auth.login(email, pass);
        const savedUser = JSON.parse(localStorage.getItem('user'));
        if (savedUser) UI.setGreeting(savedUser.username, savedUser.email);
    });

    document.getElementById('register-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('reg-username').value;
        const email = document.getElementById('reg-email').value;
        const pass = document.getElementById('reg-password').value;
        await Auth.register(username, email, pass);
        const savedUser = JSON.parse(localStorage.getItem('user'));
        if (savedUser) UI.setGreeting(savedUser.username, savedUser.email);
    });

    // 3. Screen Switching Handlers (Auth toggle)
    document.querySelectorAll('[data-target]').forEach(el => {
        el.addEventListener('click', (e) => {
            const target = e.currentTarget.getAttribute('data-target');
            UI.showScreen(target);
        });
    });

    // 4. Bottom Navbar Handlers
    document.querySelectorAll('.nav-item').forEach(el => {
        el.addEventListener('click', (e) => {
            const viewId = e.currentTarget.getAttribute('data-view');
            UI.showView(viewId);
        });
    });

    // 5. Dynamic list removal events (Event Delegation)
    document.body.addEventListener('click', async (e) => {
        // Handle Logout
        if (e.target.classList.contains('logout-btn')) {
            Auth.logout();
        }

        // Handle Remove from Wishlist
        if (e.target.classList.contains('rm-wishlist')) {
            const id = e.target.getAttribute('data-id');
            window.removeFromWishlist(id);
        }

        // Handle Remove from Cart
        if (e.target.classList.contains('rm-cart')) {
            const id = e.target.getAttribute('data-id');
            UI.setLoading(true);
            const res = await API.removeFromCart(id);
            UI.setLoading(false);
            if(res.success) {
                UI.showToast('Removed from cart');
                UI.loadCart();
            } else {
                UI.showToast('Failed to remove');
            }
        }
    });

    // 6. Handle Track Price Button (Pasting URL)
    const trackBtn = document.getElementById('track-btn');
    if (trackBtn) {
        trackBtn.addEventListener('click', () => {
            const input = document.getElementById('track-input');
            const url = input.value.trim();
            if (!url) return UI.showToast('Please paste a valid product link first.');
            if (!url.startsWith('http')) return UI.showToast('URL must start with http:// or https://');

            window.trackProduct(url);
        });
    }

    window.trackProduct = async (url) => {
        UI.setLoading(true);
        const res = await API.fetchProduct(url);
        UI.setLoading(false);

        if (res.success && res.data) {
            UI.renderProductDetails(res.data);
            window.scrollTo(0, 0);
        } else {
            UI.showToast(res.error || 'Error: Could not track this product.', 'error');
        }
    };

    window.addToCart = async (productId, quantity = 1) => {
        UI.setLoading(true);
        const res = await API.addToCart(productId, quantity);
        UI.setLoading(false);
        if(res.success) {
            UI.showToast('Successfully added to Cart! 🛒');
            UI.showView('cart-view');
        } else {
            UI.showToast(res.error || 'Failed to add to cart', 'error');
        }
    };

    window.addToWishlist = async (productId) => {
        UI.setLoading(true);
        const res = await API.addToWishlist(productId);
        UI.setLoading(false);
        if(res.success) {
            UI.showToast('Successfully saved to Wishlist! ❤️');
            UI.showView('wishlist-view');
        } else {
            UI.showToast(res.error || 'Failed to add to wishlist', 'error');
        }
    };

    // Global Floating Action Button Logic
    const fabButton = document.getElementById('global-fab');
    const addProductModal = document.getElementById('add-product-modal');
    const modalCancelBtn = document.getElementById('modal-cancel-btn');
    const modalFetchBtn = document.getElementById('modal-fetch-btn');
    const modalTrackInput = document.getElementById('modal-track-input');

    if (fabButton) {
        fabButton.addEventListener('click', () => {
            addProductModal.classList.remove('d-none');
            // Check clipboard logic could go here, but native browser security restricts auto-paste without permission
            modalTrackInput.value = '';
            modalTrackInput.focus();
        });
    }

    if (modalCancelBtn) {
        modalCancelBtn.addEventListener('click', () => {
            addProductModal.classList.add('d-none');
        });
    }

    if (modalFetchBtn) {
        modalFetchBtn.addEventListener('click', () => {
            const url = modalTrackInput.value.trim();
            if (!url) return UI.showToast('Please enter a product URL');
            if (!url.startsWith('http')) return UI.showToast('URL must start with http:// or https://');
            
            addProductModal.classList.add('d-none');
            window.trackProduct(url);
        });
    }

});
