import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token || !!state.user,
    },
    actions: {
        setAuth(payload) {
            // payload may be { user, token } or a full response object
            this.user = payload.user ?? payload.user ?? null
            this.token = payload.token ?? payload.access_token ?? null

            try {
                if (this.token) localStorage.setItem('auth_token', this.token)
                if (this.user) localStorage.setItem('auth_user', JSON.stringify(this.user))
            } catch (e) {
                // ignore storage errors
            }
        },
        clearAuth() {
            this.user = null
            this.token = null
            try {
                localStorage.removeItem('auth_token')
                localStorage.removeItem('auth_user')
            } catch (e) {
                // ignore
            }
        },
        initFromStorage() {
            try {
                const token = localStorage.getItem('auth_token')
                const user = localStorage.getItem('auth_user')
                if (token) this.token = token
                if (user) this.user = JSON.parse(user)
            } catch (e) {
                // ignore
            }
        },
    },
})
