<template>
    <!-- Mobile overlay: click outside sidebar to close -->
    <div v-if="localOpen" class="fixed inset-0 bg-black/40 z-30 md:hidden" @click="closeMobile"></div>

    <aside
        class="fixed inset-y-0 left-0 w-64 h-screen md:h-[calc(100vh-56px)]  bg-white text-slate-800 p-4 z-40 transform transition-transform duration-200 ease-in-out md:static md:inset-auto md:translate-x-0 md:z-auto"
        :class="localOpen ? 'translate-x-0' : '-translate-x-full'" @click.self="closeMobile">
        <div class="flex items-center gap-3 px-2">
            <button class="md:hidden p-2 rounded-md hover:bg-slate-100" @click="toggleSidebar" aria-label="Toggle menu"
                title="Open menu">
                <i class="pi pi-bars text-xl" aria-hidden="true"></i>
            </button>
            <div class=" md:hidden font-bold text-slate-800">JasaMedika</div>
        </div>

        <nav class="text-sm mt-4">
            <div v-for="(group, gidx) in menu" :key="gidx" class="mb-3">
                <div class="px-3 text-xs font-semibold text-slate-500 uppercase mb-2">{{ group.group }}</div>
                <ul class="space-y-1">
                    <li v-for="(item, idx) in group.items" :key="idx">
                        <div v-if="!item.children">
                            <Link :href="item.route"
                                class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-slate-50"
                                :class="isActive(item.route) ? 'text-emerald-600 font-medium' : 'text-slate-700'"
                                @click="closeMobile">
                            <i :class="item.icon + ' w-6 text-center'"></i>
                            <span>{{ item.title }}</span>
                            </Link>
                        </div>
                        <div v-else>
                            <details class="group">
                                <summary
                                    class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-slate-50 cursor-pointer text-slate-700">
                                    <i :class="item.icon + ' w-6 text-center'"></i>
                                    <span>{{ item.title }}</span>
                                    <span class="ml-auto text-xs text-slate-400">▾</span>
                                </summary>
                                <ul class="mt-1 ml-8 space-y-1">
                                    <li v-for="(child, cidx) in item.children" :key="cidx">
                                        <Link :href="child.route"
                                            class="block px-3 py-1 rounded-md hover:bg-slate-50 text-slate-700"
                                            @click="closeMobile">{{ child.title }}</Link>
                                    </li>
                                </ul>
                            </details>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </aside>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import getMenu from './menu'

const props = defineProps({
    open: { type: Boolean, default: false }
})
const emit = defineEmits(['update:open'])

const localOpen = ref(props.open)
watch(() => props.open, (v) => (localOpen.value = v))

const page = usePage()

const currentPath = ref('')
onMounted(() => {
    if (typeof window !== 'undefined') currentPath.value = window.location.pathname
})

// Build menu based on authenticated user role
const menu = computed(() => getMenu(page.props.auth?.user ?? null))

function closeMobile() {
    emit('update:open', false)
}

function isActive(route) {
    if (!currentPath.value) return false
    return currentPath.value === route || currentPath.value.startsWith(route)
}
</script>

<!-- Sidebar uses Tailwind utility classes; adjust icons/links as needed -->
