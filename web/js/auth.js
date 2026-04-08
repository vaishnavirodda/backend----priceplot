import { API } from './api.js?v=3';
import { UI } from './ui.js?v=3';

export const Auth = {
    init: () => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            UI.showScreen('main-screen');
            UI.loadDeals();
        } else {
            UI.showScreen('login-screen');
        }
    },
    login: async (email, password) => {
        UI.setLoading(true);
        const res = await API.login(email, password);
        UI.setLoading(false);
        if (res.success) {
            localStorage.setItem('auth_token', res.data.auth_token);
            localStorage.setItem('user', JSON.stringify(res.data));
            UI.showScreen('main-screen');
            UI.loadDeals();
        } else {
            UI.showToast(res.error || 'Login failed', 'error');
        }
    },
    register: async (username, email, password) => {
        UI.setLoading(true);
        const res = await API.register(username, email, password);
        UI.setLoading(false);
        if (res.success) {
            localStorage.setItem('auth_token', res.data.auth_token);
            localStorage.setItem('user', JSON.stringify(res.data));
            UI.showScreen('main-screen');
            UI.loadDeals();
        } else {
            UI.showToast(res.error || 'Registration failed', 'error');
        }
    },
    logout: async () => {
        await API.logout();
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        UI.showScreen('login-screen');
    }
};
