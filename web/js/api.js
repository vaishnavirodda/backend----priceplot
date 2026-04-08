export const BASE_URL = '/price_plot';

function getToken() {
    return localStorage.getItem('auth_token');
}

async function request(endpoint, options = {}) {
    const url = `${BASE_URL}/${endpoint}`;
    const headers = {
        'Content-Type': 'application/json',
        ...(options.headers || {})
    };
    
    const token = getToken();
    if (token) {
        headers['X-Auth-Token'] = token;
    }

    const config = {
        ...options,
        headers
    };

    try {
        const response = await fetch(url, config);
        return await response.json();
    } catch (error) {
        console.error(`API Error on ${endpoint}:`, error);
        return { success: false, error: `Error: Could not reach ${url}. Check if server is running.` };
    }
}

export const API = {
    // Auth
    login: (email, password) => request('api/auth/login.php', { method: 'POST', body: JSON.stringify({ email, password }) }),
    register: (username, email, password) => request('api/auth/register.php', { method: 'POST', body: JSON.stringify({ username, email, password }) }),
    updateProfile: (username, email) => request('api/auth/update_profile.php', { method: 'POST', body: JSON.stringify({ username, email }) }),
    logout: () => request('api/auth/logout.php', { method: 'POST' }),

    // Products
    fetchProduct: (productUrl) => request('api/products/fetch_product.php', { method: 'POST', body: JSON.stringify({ productUrl: productUrl, forceRefresh: false }) }),
    getPriceHistory: (productId) => request(`api/products/price_history.php?product_id=${productId}`, { method: 'GET' }),
    
    // Search History
    getSearchHistory: () => request('api/search_history/get.php', { method: 'GET' }),
    saveSearchHistory: (productId) => request('api/search_history/save.php', { method: 'POST', body: JSON.stringify({ product_id: productId }) }),

    // Flash Deals
    getFlashDeals: () => request('api/flash_deals/get.php', { method: 'GET' }),

    // Wishlist
    getWishlist: () => request('api/wishlist/get.php', { method: 'GET' }),
    addToWishlist: (productId, targetPrice = null) => request('api/wishlist/add.php', { method: 'POST', body: JSON.stringify({ product_id: productId, target_price: targetPrice }) }),
    removeFromWishlist: (productId) => request('api/wishlist/remove.php', { method: 'POST', body: JSON.stringify({ product_id: productId }) }),

    // Cart
    getCart: () => request('api/cart/get.php', { method: 'GET' }),
    addToCart: (productId, quantity = 1) => request('api/cart/add.php', { method: 'POST', body: JSON.stringify({ product_id: productId, quantity }) }),
    updateCart: (productId, quantity) => request('api/cart/update.php', { method: 'POST', body: JSON.stringify({ product_id: productId, quantity }) }),
    removeFromCart: (productId) => request('api/cart/remove.php', { method: 'POST', body: JSON.stringify({ product_id: productId }) }),

    // Notifications
    getNotifications: () => request('api/notifications/get.php', { method: 'GET' }),
    markNotificationRead: (notificationId = null) => request('api/notifications/mark_read.php', { method: 'POST', body: JSON.stringify({ notification_id: notificationId }) })
};
