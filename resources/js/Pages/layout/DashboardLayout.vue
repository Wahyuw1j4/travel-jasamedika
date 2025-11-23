<template>
    <div class="min-h-screen bg-slate-100">

        <header class="w-full bg-white fixed z-10">
            <div class="flex items-center justify-between gap-4 py-2 md:px-12 px-4">
                <div class="flex items-center gap-3 px-2">
                    <button class="md:hidden p-2 rounded-md hover:bg-slate-100" @click="toggleSidebar"
                        aria-label="Toggle menu" title="Open menu">
                        <i class="pi pi-bars text-xl" aria-hidden="true"></i>
                    </button>
                    <div class="font-bold text-slate-800">JasaMedika</div>
                </div>

                <div class="relative" ref="profileRef">
                    <button @click="toggleDropdown" class="flex items-center gap-2 p-2 rounded cursor-pointer">
                        <img :src="avatarUrl" alt="avatar" class="w-8 h-8 rounded-full object-cover" />
                    </button>

                    <div v-if="dropdownOpen"
                        class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-lg border border-slate-100 overflow-hidden animate-fadeIn z-10">
                        <div class="px-4 py-3 from-slate-50 to-white border-b border-slate-200">
                            <p class="text-sm font-medium text-slate-800">Hi, {{currentUser.name}}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{currentUser.role}}</p>
                        </div>
                        <div class="py-1">
                            <Link href="/profile"
                                class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all">
                            Profile
                            </Link>

                            <button @click="logout"
                                class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-red-50 hover:text-red-600 transition-all">
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="md:flex flex-1 relative">
            <div class="fixed top-14 w-64 hidden md:block">
                <Sidebar v-model:open="sidebarOpen" />
            </div>

            <main class="flex-1 pt-14 md:pl-64">
                <div class="md:hidden">
                    <Sidebar v-model:open="sidebarOpen" />
                </div>

                <div class="p-4">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Sidebar from './Sidebar.vue'
import axios from 'axios'

const sidebarOpen = ref(false)
function toggleSidebar() { sidebarOpen.value = !sidebarOpen.value }

// profile dropdown (PrimeVue Menu popup) — read authenticated user from Inertia page props
const page = usePage()
const currentUser = ref(page.props.auth.user || {})
const avatarUrl = computed(() => `https://api.dicebear.com/9.x/initials/svg?seed=${encodeURIComponent(currentUser.value?.name ?? 'User')}`)

// simple dropdown state and outside-click handler
const dropdownOpen = ref(false)
const profileRef = ref(null)
function toggleDropdown() { dropdownOpen.value = !dropdownOpen.value }

function handleDocClick(e) {
    if (!profileRef.value) return
    if (!profileRef.value.contains(e.target)) dropdownOpen.value = false
}

onMounted(() => document.addEventListener('click', handleDocClick))
onBeforeUnmount(() => document.removeEventListener('click', handleDocClick))

async function logout() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    try {
        // Use axios (configured in bootstrap.js) so CSRF and headers are handled consistently
        await axios.post('/logout', {}, {
            headers: {
                'X-CSRF-TOKEN': token || ''
            },
            withCredentials: true
        })


    } catch (e) {
        // ignore errors, still redirect
    } finally {
        window.location.href = '/login'
    }
}
</script>
