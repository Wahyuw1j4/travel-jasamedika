<template>
    <DashboardLayout>
        <div class="min-h-[calc(100vh-5rem)] flex items-start justify-center bg-slate-50 py-8 px-4">
            <div class="w-full max-w-3xl">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-slate-800">Tambah Jadwal Travel</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Lengkapi informasi berikut untuk menambahkan jadwal keberangkatan baru.
                    </p>
                </div>

                <!-- Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-7">
                    <form @submit.prevent="submit" class="space-y-6">

                        <div class="  pt-4">
                            <h3 class="text-sm font-semibold text-slate-700 mb-3">
                                Informasi Rute
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                                        Asal
                                    </label>
                                    <Dropdown v-model="form.origin" :options="cities" optionLabel="label" optionValue="value" class="w-full" placeholder="Contoh: Jakarta" />
                                    <p v-if="form.errors.origin" class="text-xs text-red-600 mt-1">
                                        {{ form.errors.origin }}
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                                        Tujuan
                                    </label>
                                    <Dropdown v-model="form.destination" :options="cities" optionLabel="label" optionValue="value" placeholder="Pilih tujuan" class="w-full" />
                                    <p v-if="form.errors.destination" class="text-xs text-red-600 mt-1">
                                        {{ form.errors.destination }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5 border-t border-slate-100 pt-4">
                            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                                Nama Trip
                            </label>
                            <InputText v-model="form.name" @input="onNameInput" class="w-full" />
                            <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Tanggal & Waktu -->
                        <div class="border-t border-slate-100 pt-4">
                            <h3 class="text-sm font-semibold text-slate-700 mb-3">
                                Waktu Keberangkatan
                            </h3>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Tanggal & Waktu Berangkat
                                </label>
                                <Calendar v-model="localDeparture" showTime hourFormat="24" class="w-full"
                                    inputClass="w-full" :showIcon="true" />
                                <p v-if="form.errors.departure_datetime" class="text-xs text-red-600 mt-1">
                                    {{ form.errors.departure_datetime[0] }}
                                </p>
                            </div>
                        </div>

                        <!-- Kuota & Harga -->
                        <div class="border-t border-slate-100 pt-4">
                            <h3 class="text-sm font-semibold text-slate-700 mb-3">
                                Detail Kuota & Harga
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-1.5 md:col-span-1">
                                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                                        Kuota Total
                                    </label>
                                    <InputNumber v-model="form.quota_total" :min="1" class="w-full"
                                        inputClass="w-full" />
                                    <p v-if="form.errors.quota_total" class="text-xs text-red-600 mt-1">
                                        {{ form.errors.quota_total[0] }}
                                    </p>
                                </div>

                                <div class="space-y-1.5 md:col-span-2">
                                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                                        Harga (IDR)
                                    </label>
                                    <InputNumber v-model="form.price" mode="currency" currency="IDR"
                                        locale="id-ID" class="w-full" inputClass="w-full" />
                                    <p v-if="form.errors.price" class="text-xs text-red-600 mt-1">
                                        {{ form.errors.price[0] }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 mt-2">
                            <Button type="button" label="Batal" class="p-button-text" @click="cancel" />
                            <Button type="submit" label="Simpan" :disabled="form.processing" class="p-button-primary" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/layout/DashboardLayout.vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import { ref, onMounted, watch } from 'vue'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Calendar from 'primevue/calendar'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import axios from 'axios'

const form = useForm({
    name: '',
    origin: '',
    destination: '',
    departure_datetime: '',
    quota_total: 1,
    price: null,
})

// Calendar expects a Date object. Keep local Date and convert before submit.
const localDeparture = ref(null)
onMounted(() => {
    if (form.departure_datetime) {
        try {
            localDeparture.value = new Date(form.departure_datetime)
        } catch (e) {
            localDeparture.value = null
        }
    }
})

// token from Inertia page props
const page = usePage()
const token = ref(page.props.auth.token)

const cities = [
    'Bali','Yogyakarta','Surabaya','Medan','Makassar','Lombok','Malang','Bandung','Banda Aceh','Pontianak','Palembang','Kupang','Manado','Ambon','Padang','Balikpapan','Ternate','Raja Ampat','Belitung','Semarang'
].map(c => ({ label: c, value: c }))

// Auto-generate trip name from origin/destination unless user edits it
const nameEdited = ref(false)
function onNameInput() { nameEdited.value = true }

watch([
    () => form.origin,
    () => form.destination,
], ([origin, destination]) => {
    if (nameEdited.value) return
    if (origin && destination) {
        form.name = `Trip ${origin} to ${destination}`
    }
})

function submit() {
    // convert localDeparture to ISO string for backend
    form.departure_datetime = localDeparture.value ? new Date(localDeparture.value).toISOString() : ''
    form.processing = true
    form.errors = {}

    const payload = {
        name: form.name,
        origin: form.origin,
        destination: form.destination,
        departure_datetime: form.departure_datetime,
        quota_total: form.quota_total,
        price: form.price,
    }

    const headers = {}
    if (token.value) headers['Authorization'] = `Bearer ${token.value}`

    axios.post('/api/travels', payload, { headers })
        .then(() => {
            router.visit('/jadwal-travel')
        })
        .catch((err) => {
            if (err.response && err.response.data) {
                const data = err.response.data
                if (data.errors) {
                    form.errors = data.errors
                } else if (data.message) {
                    form.errors = { message: data.message }
                } else {
                    form.errors = { message: 'Terjadi kesalahan. Silakan coba lagi.' }
                }
            } else {
                form.errors = { message: 'Jaringan error. Periksa koneksi Anda.' }
            }
        })
        .finally(() => {
            form.processing = false
        })
}

function cancel() {
    router.visit('/jadwal-travel')
}
</script>
