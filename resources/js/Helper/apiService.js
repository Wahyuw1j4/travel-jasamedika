import axios from 'axios';

// Base origin: gunakan env var VITE_API_BASE_URL jika tersedia, fallback ke localhost
const ORIGIN = import.meta.env.VITE_API_BASE_URL;
console.log('origin:', ORIGIN);
// Pastikan prefix /api selalu ada, tanpa double slash
const API_PREFIX = '';
// Hilangkan trailing slash dari ORIGIN lalu tambahkan /api
const API_BASE_URL = String(ORIGIN).replace(/\/+$/, '') + API_PREFIX;

const api = axios.create({
    baseURL: API_BASE_URL,
    timeout: 15000,
    // Allow sending/receiving cookies (useful when backend sets httpOnly auth cookies)
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
});

// Request interceptor: attach Bearer token dari localStorage kalau ada
api.interceptors.request.use(
    (config) => {
        try {
            const token = localStorage.getItem('auth_token');
            if (token) {
                config.headers = config.headers || {};
                config.headers.Authorization = `Bearer ${token}`;
            }
        } catch (e) {
            // ignore storage errors
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Response interceptor: coba refresh token saat 401 (sekali) lalu redirect ke /login jika gagal
api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error?.config;
        if (error?.response?.status === 401 && originalRequest && !originalRequest._retry) {
            // If the failing request is the login or refresh endpoint, don't attempt
            // to refresh or redirect here — let the caller handle the error so
            // UI (like the login page) can show a proper message without a full reload.
            const reqUrl = String(originalRequest.url || originalRequest.baseURL || '').toLowerCase();
            if (reqUrl.includes('/auth/login') || reqUrl.includes('/auth/refresh')) {
                // normalize error and reject so the component's catch block runs
                const err = error;
                if (err?.response) {
                    err.message = err.response?.data?.message || err.response?.statusText || err.message;
                    err.status = err.response?.status;
                    err.data = err.response?.data;
                }
                return Promise.reject(err);
            }
            originalRequest._retry = true;

            try {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('refresh_token');
            } catch (e) { }
            // Redirect to login for other endpoints that we couldn't refresh for.
            // This is a full-page navigation because auth state is invalid — keeping
            // this helps to ensure the app is reinitialized (you can change to a
            // router push if you'd prefer a SPA navigation).
            // window.location.href = '/login';
        }

        // Normalisasi error untuk konsumen
        const err = error;
        if (err?.response) {
            err.message = err.response?.data?.message || err.response?.statusText || err.message;
            err.status = err.response?.status;
            err.data = err.response?.data;
        }
        return Promise.reject(err);
    }
);

// Helper kecil
const request = (method, url, data = null, cfg = {}) => api.request({ method, url, data, ...cfg });

const get = (url, cfg) => request('get', url, null, cfg);
const post = (url, data, cfg) => request('post', url, data, cfg);
const put = (url, data, cfg) => request('put', url, data, cfg);
const del = (url, cfg) => request('delete', url, null, cfg);

export default api;
export { del, get, post, put, request };

