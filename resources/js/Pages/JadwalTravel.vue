<script setup>
import DashboardLayout from '@/layout/DashboardLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Calendar from 'primevue/calendar'
import Dropdown from 'primevue/dropdown'
import { computed, ref, watch, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  travel: {
    type: Object,
    required: true,
  },
})
const travel = computed(() => props.travel)
const metaKey = ref(false)
const selectedTravel = ref(null)
const page = usePage()
const currentUser = ref(page.props.auth.user || {})

const departureDate = ref(null)
const originFilter = ref(null)
const destinationFilter = ref(null)

const cities = ['Jakarta',
  'Bali', 'Yogyakarta', 'Surabaya', 'Medan', 'Makassar', 'Lombok', 'Malang', 'Bandung', 'Banda Aceh', 'Pontianak', 'Palembang', 'Kupang', 'Manado', 'Ambon', 'Padang', 'Balikpapan', 'Ternate', 'Raja Ampat', 'Belitung', 'Semarang'
].map(c => ({ label: c, value: c }))

onMounted(() => {
  if (typeof window !== 'undefined') {
    const params = new URLSearchParams(window.location.search)
    if (params.has('departure_date')) departureDate.value = params.get('departure_date')
    if (params.has('origin')) originFilter.value = params.get('origin')
    if (params.has('destination')) destinationFilter.value = params.get('destination')
  }
})

watch(selectedTravel, (val) => {
  if (val && val.id) {
    router.get(`/jadwal-travel/${val.id}`)
  }
})
const first = computed(
  () => (travel.value.current_page - 1) * travel.value.per_page
)
function rowNumber(_, options) {
  return first.value + options.rowIndex + 1
}
function onPage(event) {
  const pageNum = event.page + 1
  const filters = buildFilterParams()
  filters.page = pageNum

  console.debug('JadwalTravel paging params:', filters)
  router.get('/jadwal-travel', filters, {
    preserveScroll: true,
    preserveState: true,
  })
}

function search() {
  const filters = buildFilterParams()
  console.debug('JadwalTravel raw filters:', {
    departureDate: departureDate.value,
    originFilter: originFilter.value,
    originType: typeof originFilter.value,
    destinationFilter: destinationFilter.value,
    destinationType: typeof destinationFilter.value,
  })
  if (!Object.keys(filters).length) {
    console.debug('JadwalTravel search: no filters, navigating without query string')
    router.get('/jadwal-travel', {}, { preserveState: false, preserveScroll: true })
    return
  }

  filters.page = 1
  console.debug('JadwalTravel search params:', filters)
  router.get('/jadwal-travel', filters, { preserveState: false, preserveScroll: true })
}

function resetFilters() {
  departureDate.value = null
  originFilter.value = null
  destinationFilter.value = null
  search()
}

function buildFilterParams() {
  const params = {}

  function pad(n) {
    return n < 10 ? '0' + n : String(n)
  }

  function toLocalDateString(d) {
    const year = d.getFullYear()
    const month = pad(d.getMonth() + 1)
    const day = pad(d.getDate())
    return `${year}-${month}-${day}`
  }

  if (departureDate.value) {
    if (departureDate.value instanceof Date) {
      params.departure_date = toLocalDateString(departureDate.value)
    } else if (typeof departureDate.value === 'string') {
      if (departureDate.value.includes('T')) {
        const parsed = new Date(departureDate.value)
        if (!isNaN(parsed)) params.departure_date = toLocalDateString(parsed)
        else params.departure_date = departureDate.value
      } else {
        params.departure_date = departureDate.value
      }
    } else {
      const parsed = new Date(departureDate.value)
      if (!isNaN(parsed)) params.departure_date = toLocalDateString(parsed)
    }
  }

  if (originFilter.value) {
    if (typeof originFilter.value === 'object') {
      const v = originFilter.value.value ?? originFilter.value.label ?? null
      if (v !== null && String(v).trim() !== '') params.origin = v
    } else if (String(originFilter.value).trim() !== '') {
      params.origin = originFilter.value
    }
  }

  if (destinationFilter.value) {
    if (typeof destinationFilter.value === 'object') {
      const v = destinationFilter.value.value ?? destinationFilter.value.label ?? null
      if (v !== null && String(v).trim() !== '') params.destination = v
    } else if (String(destinationFilter.value).trim() !== '') {
      params.destination = destinationFilter.value
    }
  }

  const cleaned = {}
  Object.entries(params).forEach(([k, v]) => {
    if (v !== null && v !== undefined && String(v).trim() !== '') cleaned[k] = v
  })

  return cleaned
}


function rowClassName(rowData) {
  return 'cursor-pointer hover:bg-slate-50'
}

function createTravel() {
  router.get('/jadwal-travel/create')
}

function formatDate(value) {
  const d = new Date(value)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }) + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <DashboardLayout>
    <div class="space-y-4">
      <div class="mt-4 p-4 bg-white rounded shadow">
        <div class="flex justify-between">
          <h1 class="text-xl font-semibold">Jadwal Travel</h1>
          <div v-if="currentUser.role === 'admin'">
            <Button label="Tambah Jadwal" class="p-button-sm p-button-primary" @click="createTravel" />
          </div>
        </div>
        <!-- Filters -->
        <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
          <div>
            <label class="block text-xs text-slate-500">Tanggal Berangkat</label>
            <Calendar v-model="departureDate" dateFormat="yy-mm-dd" class="w-full" showIcon />
          </div>
          <div>
            <label class="block text-xs text-slate-500">Asal</label>
            <Dropdown v-model="originFilter" :options="cities" optionLabel="label" showClear
              placeholder="Pilih kota asal" class="w-full" />
          </div>
          <div>
            <label class="block text-xs text-slate-500">Tujuan</label>
            <Dropdown v-model="destinationFilter" :options="cities" optionLabel="label" showClear
              placeholder="Pilih kota tujuan" class="w-full" />
          </div>
          <div class="flex gap-2">
            <Button label="Cari" class="p-button-primary" @click="search" />
            <Button label="Reset" class="p-button-text" @click="resetFilters" />
          </div>
        </div>
        <DataTable v-model:selection="selectedTravel" :value="travel.data" selectionMode="single"
          :metaKeySelection="metaKey" dataKey="id" :lazy="true" paginator :rows="travel.per_page"
          :totalRecords="travel.total" :first="first" :pageLinkSize="5"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink" @page="onPage"
          :rowClassName="rowClassName" emptyMessage="Belum ada jadwal.">
          <Column field="departure_datetime" header="Tanggal Berangkat">
            <template #body="slotProps">
              {{ formatDate(slotProps.data.departure_datetime) }}
            </template>
          </Column>
          <Column field="origin" header="Asal" />
          <Column field="destination" header="Tujuan" />
          <Column header="Kursi Tersedia">
            <template #body="slotProps">
                {{slotProps.data.quota_total - slotProps.data.booked_tickets_sum }} / {{slotProps.data.quota_total}}
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
  </DashboardLayout>
</template>
