<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-100">
        <div class="w-full max-w-md px-6 py-8 bg-white rounded-2xl shadow-xl border border-slate-100">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-semibold text-slate-800">Welcome Back</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Silakan login untuk melanjutkan ke dashboard.
                </p>
            </div>

            <div v-if="form.errors.message" class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2.5">
                <p class="text-sm text-red-700">
                    {{ form.errors.message }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">
                        Email
                    </label>
                    <input v-model="form.email" type="email" required autocomplete="email" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800
                   placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                   disabled:bg-slate-100 disabled:cursor-not-allowed" placeholder="you@example.com" />
                    <p v-if="form.errors.email" class="text-xs text-red-600 mt-1">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-slate-700">
                            Password
                        </label>
                    </div>
                    <input v-model="form.password" type="password" required autocomplete="current-password" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800
                   placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                   disabled:bg-slate-100 disabled:cursor-not-allowed" placeholder="••••••••" />
                    <p v-if="form.errors.password" class="text-xs text-red-600 mt-1">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                        <input type="checkbox" v-model="form.remember"
                            class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
                        <span>Ingat saya</span>
                    </label>
                    <a href="#" class="text-xs text-primary-600 hover:text-primary-700">
                        Belum punya akun?
                    </a>
                </div>

                <div class="pt-2">
                    <button type="submit" :disabled="form.processing" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5
                   text-sm font-medium text-white shadow-sm
                   hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1
                   disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                        <span v-if="!form.processing">Sign in</span>
                        <span v-else>Memproses...</span>
                    </button>
                </div>

                <p class="text-[11px] text-center text-slate-400 mt-3">
                    © {{ new Date().getFullYear() }} Jasamedika. All rights reserved.
                </p>
            </form>
        </div>
    </div>
</template>


<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({ email: '', password: '', remember: false })

function submit() {
    form.post('/login', {
        onFinish: () => {
            // clear password after submit for security
            form.reset('password')
        }
    })
}
</script>
