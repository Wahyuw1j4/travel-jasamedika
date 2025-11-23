<template>
    <DashboardLayout>
        <div class="space-y-4">
            <div class="mt-4 p-4 bg-white rounded shadow">
                <div class="">
                    <h1 class="text-2xl font-bold mb-4">Tiket Saya</h1>

                    <div v-if="bookings && bookings.length">
                        <DataTable v-model:selection="selectedTravel" :value="bookings" class="p-datatable-sm"
                            dataKey="id" selectionMode="single" :metaKeySelection="metaKey">
                            <Column header="Kode">
                                <template #body="slot">{{ slot.data.booking_code }}</template>
                            </Column>
                            <Column header="Perjalanan">
                                <template #body="slot">{{ slot.data.travel?.name || '-' }}</template>
                            </Column>
                            <Column header="Keberangkatan">
                                <template #body="slot">{{ formatDate(slot.data.travel?.departure_datetime) }}</template>
                            </Column>
                            <Column header="Tickets">
                                <template #body="slot">{{ slot.data.ticket_count }}</template>
                            </Column>
                            <Column header="Total">
                                <template #body="slot">{{ formatCurrency(slot.data.total_price) }}</template>
                            </Column>
                            <Column header="Status">
                                <template #body="slot">
                                    <span class="text-xs px-2 py-1 rounded-full"
                                        :class="statusClass(slot.data.status)">{{
                                            slot.data.status }}</span>
                                </template>
                            </Column>
                        </DataTable>
                    </div>

                    <div v-else class="p-4 bg-slate-100 rounded">Anda belum memiliki tiket.</div>

                    <Dialog v-model:visible="detailsDialog" header="Detail Booking" :modal="true"
                        :style="{ width: '700px' }">
                        <div v-if="selected">
                            <div class="mb-3">
                                <div class="text-sm text-slate-600">Kode: <strong>{{ selected.booking_code }}</strong>
                                </div>
                                <div class="text-sm text-slate-500">Keberangkatan: <strong>{{
                                    formatDate(selected.travel?.departure_datetime) }}</strong></div>
                                <div class="text-sm text-slate-500">Status: <strong>{{ selected.status }}</strong></div>
                                <div class="text-sm text-slate-500">Tickets: {{ selected.ticket_count }} — Total: {{
                                    formatCurrency(selected.total_price) }}</div>
                            </div>

                            <div class="space-y-2">
                                <div v-for="(d, idx) in selected.details || []" :key="d.id || idx"
                                    class="p-3 bg-slate-200 rounded flex justify-between items-center">
                                    <div>
                                        <div class="text-sm font-medium">{{ d.penumpang_name }}</div>
                                        <div class="text-xs text-slate-500">NIK: {{ d.penumpang_nik }}</div>
                                    </div>
                                    <div class="text-sm font-semibold text-emerald-600">{{ formatCurrency(d.price) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Dialog>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/layout/DashboardLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    bookings: { type: Array, required: true }
})

const detailsDialog = ref(false)
const selected = ref(null)

function openDetails(b) {
    selected.value = b
    detailsDialog.value = true
}

const metaKey = ref(false)
const selectedTravel = ref(null)

// navigate to show when a row is selected
watch(selectedTravel, (val) => {
    if (val && val.id) {
        router.get(`/jadwal-travel/${val.travel.id}`)
    }
})

function formatCurrency(value) {
    if (value == null) return '-'
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value)
}

function formatDate(iso) {
    if (!iso) return '-'
    try {
        const d = new Date(iso)
        return new Intl.DateTimeFormat('id-ID', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(d)
    } catch (e) {
        return iso
    }
}

function statusClass(status) {
    return {
        'bg-amber-100 text-amber-800': status === 'pending',
        'bg-sky-100 text-sky-800': status === 'waiting_payment',
        'bg-emerald-100 text-emerald-800': status === 'paid',
        'bg-red-100 text-red-800': status === 'cancelled'
    }
}
</script>
