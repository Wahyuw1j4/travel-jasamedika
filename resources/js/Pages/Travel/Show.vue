<template>
  <DashboardLayout>
    <Toast position="top-right" />
    <div class="max-w-4xl mx-auto py-8">
      <!-- Header -->
      <div class="mb-6 flex items-start justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-slate-800">{{ travel.name }}</h1>
          <p class="text-sm text-slate-500 mt-1">{{ travel.origin }} → {{ travel.destination }}</p>
          <div class="mt-3 text-sm text-slate-600">
            <span class="inline-flex items-center gap-2">
              {{ formatDate(travel.departure_datetime) }}
            </span>
          </div>
        </div>

        <div class="flex flex-col items-end gap-3">
          <div class="inline-flex items-center gap-3">
            <div class="text-right">
              <div class="text-sm text-slate-500">Harga</div>
              <div class="text-xl font-semibold text-emerald-600">{{ formatCurrency(travel.price) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card -->
      <div class="bg-white p-6 rounded-xl shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Details -->
          <div class="md:col-span-2">
            <div class="mb-4">
              <h2 class="text-sm text-slate-500">Detail Perjalanan</h2>
              <p class="text-lg font-medium text-slate-800 mt-1">{{ travel.name }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
              <div>
                <div class="text-xs text-slate-400">Asal</div>
                <div class="font-medium text-slate-700">{{ travel.origin || '-' }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-400">Tujuan</div>
                <div class="font-medium text-slate-700">{{ travel.destination || '-' }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-400">Tanggal</div>
                <div class="font-medium text-slate-700">{{ formatDate(travel.departure_datetime) }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-400">Durasi</div>
                <div class="font-medium text-slate-700">-</div>
              </div>
            </div>

            <div class="mt-6">
              <div class="flex items-center justify-between">
                <div class="text-xs text-slate-400">Kuota</div>
                <div class="text-xs text-slate-500">{{ (travel.bookings || []).filter(b => b.status !== 'cancelled').length }} / {{ travel.quota_total }}</div>
              </div>
              <div class="w-full bg-slate-100 h-3 rounded-full mt-2 overflow-hidden">
                <div class="h-3 bg-emerald-500" :style="{ width: progressPercent + '%' }"></div>
              </div>
              <div class="text-xs text-slate-500 mt-1">{{ progressPercent }}% terisi</div>
            </div>
          </div>

          <!-- Actions / Summary -->
          <div class="space-y-4">
            <div class="p-4 bg-slate-100 rounded-lg">
              <div class="text-xs text-slate-400">Harga per orang</div>
              <div class="text-lg font-semibold text-emerald-600">{{ formatCurrency(travel.price) }}</div>
            </div>

            <div class="flex flex-col gap-2">
              <template v-if="isAdmin">
                <Button class="w-full" label="Edit" @click="editTravel" />
                <Button class="w-full p-button-danger" label="Hapus" @click="deleteTravel" />
                <Button class="w-full p-button-secondary" label="Export CSV" @click="exportCsv" />
              </template>
              <template v-if="!isAdmin && bookings && bookings.length === 0">
                <Button class="w-full p-button-primary" label="Pesan Sekarang" @click="openOrderModal" />
              </template>
            </div>
          </div>
          <template v-if="isAdmin">
            <div class="md:col-span-3 mt-6">
              <h3 class="text-lg font-semibold mb-3">Bookings</h3>

              <DataTable :value="bookings || []" dataKey="id" class="p-datatable-sm">
                <Column field="booking_code" header="Kode" />
                <Column field="user_id" header="User" />
                <Column field="ticket_count" header="Tickets" />

                <Column header="Total">
                  <template #body="slot">
                    {{ formatCurrency(slot.data.total_price) }}
                  </template>
                </Column>

                <Column header="Status">
                  <template #body="slot">
                    <span class="text-xs px-2 py-1 rounded-full" :class="{
                      'bg-amber-100 text-amber-800': slot.data.status === 'pending',
                      'bg-sky-100 text-sky-800': slot.data.status === 'waiting_payment',
                      'bg-emerald-100 text-emerald-800': slot.data.status === 'paid',
                      'bg-red-100 text-red-800': slot.data.status === 'cancelled'
                    }">{{ slot.data.status }}</span>
                  </template>
                </Column>

                <Column header="Actions">
                  <template #body="slot">
                    <div class="flex gap-2">
                      <Button size="small" class="p-button-text" label="View"
                        @click="() => fetchBookingDetails(slot.data)" />
                      <Button size="small" class="p-button-danger" label="Delete"
                        @click="() => deleteBooking(slot.data.id)" />
                    </div>
                  </template>
                </Column>

                <!-- details shown in dialog when View is clicked -->
              </DataTable>
            </div>
          </template>
          <template v-else>
            <div class="md:col-span-3 mt-4 bg-slate-100 p-4 rounded">
              <div v-if="bookings && bookings.length > 0">
                <h4 class="text-sm font-semibold mb-2">Anda telah memesan tiket ini</h4>
                <p>Nama Pemesan: {{ bookings[0].user.name }}</p>
                <p>Status: 
                  <span class="text-xs px-2 py-1 rounded-full" :class="{
                    'bg-amber-100 text-amber-800': bookings[0].status === 'pending',
                    'bg-sky-100 text-sky-800': bookings[0].status === 'waiting_payment',
                    'bg-emerald-100 text-emerald-800': bookings[0].status === 'paid',
                    'bg-red-100 text-red-800': bookings[0].status === 'cancelled'
                  }">{{ bookings[0].status }}</span>
                </p>
                <p>Jumlah Tiket: {{ bookings[0].ticket_count }}</p>
                <p>Jumlah Pembayaran: {{ bookings[0].payment ? formatCurrency(bookings[0].payment.paid_amount) : 'Belum dibayar' }}</p>
                <div class="space-y-2">
                  <div v-for="(d, idx) in bookings[0].details" :key="d.id || idx"
                    class="p-3 bg-slate-200 rounded flex justify-between items-center">
                    <div>
                      <div class="text-sm font-medium">{{ d.penumpang_name }}</div>
                      <div class="text-xs text-slate-500">NIK: {{ d.penumpang_nik }}</div>
                    </div>
                    <div class="text-sm font-semibold text-emerald-600">{{ formatCurrency(d.price) }}</div>
                  </div>
                </div>
                <div v-if="bookings && bookings[0].status != 'paid'" class="flex justify-end gap-2 mt-3">
                  <Button label="Pembayaran" class="p-button-success" @click="openPaymentDialog" />
                </div>
                <div v-else class="flex justify-end gap-2 mt-3">
                  <Button label="Cetak Tiket" class="p-button-success" @click="downloadTicket" />
                </div>
              </div>
              <div v-else>
                <p class="text-sm text-slate-600">Anda belum memesan tiket untuk perjalanan ini.</p>
              </div>
            </div>
          </template>

          <!-- Booking Detail Dialog -->
          <Dialog v-model:visible="bookingDialog" header="Detail Booking" :modal="true" :style="{ width: '700px' }"
            @hide="closeBookingDialog">
            <div v-if="selectedBooking">
              <div class="mb-3">
                <div class="text-sm text-slate-600">Kode: <strong>{{ selectedBooking.booking_code }}</strong></div>
                <div class="text-sm text-slate-500">Status: <span class="font-medium">{{ selectedBooking.status
                }}</span></div>
                <div class="text-sm text-slate-500">Tickets: {{ selectedBooking.ticket_count }} — Total: {{
                  formatCurrency(selectedBooking.total_price) }}</div>
              </div>

              <div class="overflow-x-auto">
                <DataTable :value="selectedBooking.details || []" class="p-datatable-sm" showGridlines>
                  <Column header="#">
                    <template #body="{ rowIndex }">{{ rowIndex + 1 }}</template>
                  </Column>
                  <Column field="penumpang_name" header="Nama" />
                  <Column field="penumpang_email" header="Email" />
                  <Column field="penumpang_nik" header="NIK" />
                  <Column header="Price">
                    <template #body="slot">{{ formatCurrency(slot.data.price) }}</template>
                  </Column>
                </DataTable>
              </div>
            </div>
          </Dialog>

          <!-- Payment Dialog -->
          <Dialog v-model:visible="paymentDialog" header="Pembayaran" :modal="true" :style="{ width: '520px' }"
            @hide="() => { paymentDialog = false }">
            <div>
              <div class="mb-4">
                <div class="text-sm text-slate-600">Booking: <strong>{{ selectedBooking.booking_code }}</strong></div>
                <div class="text-sm text-slate-500">Jumlah Tiket: <strong>{{ selectedBooking.ticket_count }}</strong>
                </div>
                <div class="text-lg font-semibold text-emerald-600 mt-3">Total: <strong>{{
                  formatCurrency(selectedBooking.total_price || (selectedBooking.details || []).reduce((s, d) => s +
                    (d.price ||
                    0), 0)) }}</strong></div>
              </div>

              <div class="text-sm text-slate-500">
                <p class="mb-2">Lakukan pembayaran ke rekening berikut:</p>
                <div class="mt-2 font-mono p-3 text-slate-900 bg-slate-100 rounded text-center">
                  BANK BCA — 123-456-7890<br />a.n. JasaMedika
                </div>
                <p class="mt-3">Unggah bukti pembayaran Anda di bawah ini.</p>
              </div>
              <div class="mt-4">
                <div class="relative border-dashed border-2 rounded p-4 text-center cursor-pointer"
                  :class="{ 'border-blue-400 bg-blue-50': dragActive }" @dragover.prevent
                  @dragenter.prevent="dragActive = true" @dragleave.prevent="dragActive = false"
                  @drop.prevent="onDropHandler">
                  <input ref="fileInputRef" type="file" accept="image/*"
                    style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;"
                    @change="onImageChange" />
                  <div v-if="!selectedImageFile">
                    <div class="text-sm text-gray-600">Drag & drop an image here, or click to select</div>
                    <div class="text-xs text-gray-400 mt-1">Max size: 5 MB. Supported: images</div>
                  </div>
                  <div v-else class="flex items-center justify-center gap-3">
                    <img :src="previewUrl" alt="preview" class="w-32 h-24 object-cover border rounded" />
                    <div class="text-left">
                      <div class="font-medium">{{ selectedImageFile.name }}</div>
                      <div class="text-xs text-gray-500">{{ formatBytes(selectedImageFile.size) }}</div>
                      <div class="mt-2 flex items-center gap-2">
                        <button type="button" class="p-1 text-sm text-gray-700 border rounded"
                          @click.stop="removeSelectedImage">Remove</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <template #footer>
              <div class="flex justify-end gap-2">
                <Button label="Batal" class="p-button-text" @click="() => { paymentDialog = false }" />
                <Button :disabled="paymentProcessing" label="Bayar" class="p-button-primary" @click="submitPayment" />
              </div>
            </template>
          </Dialog>

          <!-- Order / Booking Modal for passengers -->
          <Dialog v-model:visible="orderDialog" header="Pesan Tiket" :modal="true" :style="{ width: '720px' }"
            @hide="() => { orderDialog = false }">
            <div>
              <div class="mb-3">
                <div class="text-sm text-slate-600">Jadwal: <strong>{{ travel.name }}</strong></div>
                <div class="text-sm text-slate-500">Sisa Kuota: <strong>{{ availableSeats }}</strong></div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="md:col-span-1">
                  <label class="block text-xs text-slate-500 mb-1">Jumlah Tiket</label>
                  <InputNumber v-model="orderForm.ticket_count" :min="1" :max="availableSeats" class="w-full" />
                </div>
              </div>

              <div class="space-y-3">
                <h4 class="text-sm font-medium">Data Penumpang</h4>
                <div v-for="(p, idx) in orderForm.details" :key="idx"
                  class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                  <div class="md:col-span-2">
                    <label class="block text-xs text-slate-500">Nama</label>
                    <InputText v-model="p.penumpang_name" class="w-full" />
                  </div>
                  <div>
                    <label class="block text-xs text-slate-500">NIK</label>
                    <InputText v-model="p.penumpang_nik" class="w-full" />
                  </div>
                </div>
              </div>
            </div>
            <template #footer>
              <div class="flex justify-end gap-2">
                <Button label="Batal" class="p-button-text" @click="() => { orderDialog = false }" />
                <Button :disabled="orderProcessing" label="Pesan" class="p-button-primary" @click="submitOrder" />
              </div>
            </template>
          </Dialog>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/layout/DashboardLayout.vue'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import { usePage, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'


const props = defineProps({
  travel: {
    type: Object,
    required: true,
  },
  user: {
    type: Object,
    required: false,
  }
})

const travel = props.travel
const bookings = ref(props.bookings ?? [])

const page = usePage()
const isAdmin = ref(page.props.auth.user?.role === 'admin')
// const isAdmin = ref(true)
const token = ref(page.props.auth.token)
const toast = useToast()

async function fetchBookings() {
  try {
    const res = await axios.get(`/api/travels/${travel.id}/bookings`, {
      headers: {
        Authorization: `Bearer ${token.value}`,
      }
    })
    bookings.value = res.data || []
  } catch (e) {
    // ignore or optionally show error; keep bookings empty
    bookings.value = []
  }
}

onMounted(() => {
  fetchBookings()
})
// current user from Inertia page props (may be undefined for guest)


const progressPercent = computed(() => {
  const total = travel.quota_total || 0
  if (total <= 0) return 0
  const used = (travel.bookings || []).filter(b => b.status !== 'cancelled').length
  return Math.round((used / total) * 100)
})

// DataTable state (no row expansion)

// Dialog state for viewing a booking
const bookingDialog = ref(false)
const selectedBooking = ref({
  booking_code: '',
  id: null,
  status: '',
  ticket_count: 0,
  total_price: 0,
  details: []
})

// Order modal state
const orderDialog = ref(false)
const orderProcessing = ref(false)
const orderForm = ref({
  ticket_count: 1,
  details: [{ penumpang_name: '', penumpang_nik: '' }]
})

// Payment dialog state
const paymentDialog = ref(false)
const paymentProcessing = ref(false)
// proof-of-payment file (optional) and dropzone state
const selectedImageFile = ref(null)
const fileInputRef = ref(null)
const dragActive = ref(false)
const previewUrl = ref(null)

function onImageChange(e) {
  const f = e.target.files && e.target.files[0]
  setSelectedFile(f)
}

function onDropHandler(e) {
  dragActive.value = false
  const f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]
  setSelectedFile(f)
}

function setSelectedFile(f) {
  if (previewUrl.value) {
    try { URL.revokeObjectURL(previewUrl.value) } catch (e) { /* ignore */ }
    previewUrl.value = null
  }
  if (!f) {
    selectedImageFile.value = null
    return
  }
  // optional: limit size to 5MB
  const max = 5 * 1024 * 1024
  if (f.size > max) {
    toast.add({ severity: 'warn', summary: 'File terlalu besar', detail: 'Ukuran file maksimal 5 MB', life: 4000 })
    selectedImageFile.value = null
    if (fileInputRef.value) fileInputRef.value.value = null
    return
  }
  selectedImageFile.value = f
  previewUrl.value = URL.createObjectURL(f)
}

function removeSelectedImage() {
  if (previewUrl.value) {
    try { URL.revokeObjectURL(previewUrl.value) } catch (e) { /* ignore */ }
    previewUrl.value = null
  }
  selectedImageFile.value = null
  if (fileInputRef.value) fileInputRef.value.value = null
}

function formatBytes(bytes) {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  let i = 0
  let num = bytes
  while (num >= 1024 && i < units.length - 1) {
    num /= 1024
    i++
  }
  return `${num.toFixed(2)} ${units[i]}`
}

const availableSeats = computed(() => {
  const total = travel.quota_total || 0
  // sum booked tickets (from bookings list, may be empty for passenger view)
  const bookedSum = (bookings.value || []).reduce((s, b) => s + (b.ticket_count || 0), 0)
  return Math.max(0, total - bookedSum)
})

function openOrderModal() {
  // initialize based on available seats
  orderForm.value.ticket_count = availableSeats.value > 0 ? 1 : 0
  orderForm.value.details = []
  for (let i = 0; i < Math.max(1, orderForm.value.ticket_count); i++) {
    orderForm.value.details.push({ penumpang_name: '', penumpang_nik: '' })
  }
  orderDialog.value = true
}

watch(() => orderForm.value.ticket_count, (n) => {
  const desired = Math.max(0, Math.min(n || 0, availableSeats.value))
  // adjust details length
  while (orderForm.value.details.length < desired) orderForm.value.details.push({ penumpang_name: '', penumpang_nik: '' })
  while (orderForm.value.details.length > desired) orderForm.value.details.pop()
})

async function submitOrder() {
  if (orderProcessing.value) return
  if (!orderForm.value.ticket_count || orderForm.value.ticket_count < 1) {
    toast.add({ severity: 'warn', summary: 'Validasi', detail: 'Jumlah tiket harus minimal 1', life: 3000 })
    return
  }

  orderProcessing.value = true
  try {
    const payload = {
      travel_id: travel.id,
      ticket_count: orderForm.value.ticket_count,
      details: orderForm.value.details.map(d => ({ penumpang_name: d.penumpang_name, penumpang_nik: d.penumpang_nik }))
    }
    const headers = {}
    if (token.value) headers['Authorization'] = `Bearer ${token.value}`
    await axios.post('/api/bookings', payload, { headers })
    orderDialog.value = false
    // refresh bookings
    await fetchBookings()
    toast.add({ severity: 'success', summary: 'Sukses', detail: 'Pemesanan berhasil', life: 3000 })
  } catch (e) {
    console.error('Order failed', e)
    if (e.response && e.response.data && e.response.data.message) {
      toast.add({ severity: 'error', summary: 'Gagal', detail: e.response.data.message, life: 5000 })
    } else {
      toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal memesan tiket. Silakan coba lagi.', life: 5000 })
    }
  } finally {
    orderProcessing.value = false
  }
}

// Always fetch booking details from API (used by the View button)
async function fetchBookingDetails(booking) {
  try {
    const headers = {}
    if (token.value) headers['Authorization'] = `Bearer ${token.value}`
    const res = await axios.get(`/api/travels/${travel.id}/bookings/${booking.id}/booking-details`, { headers })
    selectedBooking.value.details = res.data
    selectedBooking.value.booking_code = booking.booking_code
    selectedBooking.value.status = booking.status
    selectedBooking.value.ticket_count = booking.ticket_count
    selectedBooking.value.total_price = booking.total_price
    bookingDialog.value = true
  } catch (e) {
    console.error('Failed to fetch booking details', e)
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengambil detail booking. Silakan coba lagi.', life: 5000 })
  }
}

function formatDate(iso) {
  if (!iso) return '-'
  try {
    const d = new Date(iso)
    return new Intl.DateTimeFormat('id-ID', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    }).format(d)
  } catch (e) {
    return iso
  }
}

function formatCurrency(value) {
  if (value == null) return '-'
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value)
}

async function deleteTravel() {
  if (!confirm('Hapus travel ini? Tindakan ini tidak dapat dibatalkan.')) return
  try {
    const headers = {}
    if (token.value) headers['Authorization'] = `Bearer ${token.value}`
    await axios.delete(`/api/travels/${travel.id}`, { headers })
    // After successful delete, go back to list
    router.visit('/jadwal-travel')
  } catch (e) {
    // show a simple error message; details available in console
    console.error('Failed to delete travel', e)
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal menghapus travel. Silakan coba lagi.', life: 5000 })
  }
}

function editTravel() {
  router.get(`/jadwal-travel/${travel.id}/edit`)
}

function exportCsv() {
  router.get(`/jadwal-travel/${travel.id}/export`)
}

function bookNow() {
  router.get(`/jadwal-travel/${travel.id}/book`)
}

function goBack() {
  router.visit('/jadwal-travel')
}

function downloadTicket() {
  // determine booking id to download
  const bookingId = selectedBooking.value && selectedBooking.value.id ? selectedBooking.value.id : (bookings.value && bookings.value[0] && bookings.value[0].id ? bookings.value[0].id : null)
  if (!bookingId) {
    toast.add({ severity: 'warn', summary: 'Cetak tiket', detail: 'Tidak ada booking yang dapat dicetak.', life: 3000 })
    return
  }

  // open PDF route in new tab
  const url = `/tickets/${bookingId}/pdf`
  window.open(url, '_blank')
}

function deleteBooking(id) {
  if (!confirm('Hapus booking ini?')) return
  router.delete(`/bookings/${id}`, {
    onFinish: () => {
      // reload the current page to refresh bookings
      router.reload()
    }
  })
}

function closeBookingDialog() {
  bookingDialog.value = false
  selectedBooking.value = {
    booking_code: '',
    id: null,
    status: '',
    ticket_count: 0,
    total_price: 0,
    details: []
  }
}

function openPaymentDialog() {
  // prefill selectedBooking from first booking if not set (passenger view)
  if ((!selectedBooking.value || !selectedBooking.value.booking_code) && bookings.value && bookings.value.length) {
    const b = bookings.value[0]
    selectedBooking.value.booking_code = b.booking_code
    selectedBooking.value.id = b.id
    selectedBooking.value.status = b.status
    selectedBooking.value.ticket_count = b.ticket_count
    selectedBooking.value.total_price = b.total_price
    selectedBooking.value.details = b.details || []
  }
  selectedImageFile.value = null
  if (previewUrl.value) { try { URL.revokeObjectURL(previewUrl.value) } catch (e) { } previewUrl.value = null }
  paymentDialog.value = true
}

async function submitPayment() {
  if (paymentProcessing.value) return
  paymentProcessing.value = true
  try {
    const headers = {}
    if (token.value) headers['Authorization'] = `Bearer ${token.value}`

    // If user uploaded a file, send multipart/form-data to /api/payments
    if (selectedImageFile.value) {
      const form = new FormData()
      const bookingId = selectedBooking.value.id || (bookings.value[0] && bookings.value[0].id)
      form.append('booking_id', bookingId)
      const amount = selectedBooking.value.total_price || (selectedBooking.value.details || []).reduce((s, d) => s + (d.price || 0), 0)
      form.append('paid_amount', amount)
      form.append('proof', selectedImageFile.value)

      await axios.post('/api/payments', form, {
        headers: {
          ...headers,
          'Content-Type': 'multipart/form-data'
        }
      })

      toast.add({ severity: 'success', summary: 'Pembayaran', detail: 'Bukti pembayaran berhasil diunggah.', life: 4000 })
    } else {
      // Demo fallback if no file provided
      await new Promise(r => setTimeout(r, 700))
      toast.add({ severity: 'success', summary: 'Pembayaran', detail: 'Pembayaran berhasil (demo)', life: 4000 })
    }

    paymentDialog.value = false
    bookingDialog.value = false
    // refresh bookings so statuses/summary update if needed
    await fetchBookings()
  } catch (e) {
    console.error('Payment failed', e)
    if (e.response && e.response.data && e.response.data.message) {
      toast.add({ severity: 'error', summary: 'Gagal', detail: e.response.data.message, life: 5000 })
    } else {
      toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal melakukan pembayaran.', life: 5000 })
    }
  } finally {
    paymentProcessing.value = false
  }
}
</script>
