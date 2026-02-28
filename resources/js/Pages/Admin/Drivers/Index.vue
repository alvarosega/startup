<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'
import { UserPlus, Truck, Bike, Car, Search, AlertTriangle, ChevronRight } from 'lucide-vue-next'

const props = defineProps({ 
  drivers: Object, 
  filters: Object,
  pending_count: Number
})

const search = ref(props.filters.search || '')
const currentTab = ref(props.filters.status || 'all')

watch([search, currentTab], debounce(() => {
  router.get(route('admin.drivers.index'), { 
    search: search.value,
    status: currentTab.value === 'all' ? null : currentTab.value
  }, { preserveState: true, replace: true })
}, 300))

const getVehicleIcon = (type) => {
  if (type === 'moto') return Bike
  if (type === 'car') return Car
  return Truck
}

const getStatusColor = (driver) => {
  if (!driver.is_verified) return 'bg-amber-500'
  if (driver.is_active) return 'bg-green-500'
  return 'bg-red-500'
}
</script>

<template>
  <AdminLayout>
    <div class="container py-8 max-w-7xl mx-auto">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
        <div class="space-y-1">
          <h1 class="text-3xl font-semibold">Flota de Conductores</h1>
          <p class="text-gray-500 text-sm">Verificación de identidad y gestión de vehículos</p>
        </div>
        
        <Link :href="route('admin.drivers.create')" class="bg-black text-white px-6 py-3 rounded-lg flex items-center gap-2 shadow-lg transition-all hover:bg-gray-800">
          <UserPlus :size="18" /> Nuevo Conductor
        </Link>
      </div>

      <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
        <div class="bg-gray-100 p-1 rounded-xl flex gap-1">
          <button @click="currentTab = 'all'" class="px-4 py-2 rounded-lg text-xs font-bold transition-all" :class="currentTab === 'all' ? 'bg-white shadow-sm text-black' : 'text-gray-500'">Todos</button>
          <button @click="currentTab = 'pending'" class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2" :class="currentTab === 'pending' ? 'bg-white shadow-sm text-black' : 'text-gray-500'">
            Pendientes
            <span v-if="pending_count > 0" class="bg-amber-500 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ pending_count }}</span>
          </button>
        </div>

        <div class="relative w-full md:w-64">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" :size="16" />
          <input v-model="search" type="text" placeholder="Buscar por nombre o placa..." class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm outline-none focus:border-black" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Link v-for="driver in drivers.data" :key="driver.id" :href="route('admin.drivers.edit', driver.id)" class="block group">
          <div class="bg-white border rounded-xl p-4 cursor-pointer hover:border-black transition-colors relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1.5" :class="getStatusColor(driver)"></div>
            
            <div class="pl-4">
              <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                  <div class="bg-gray-50 border p-2 rounded-full">
                    <component :is="getVehicleIcon(driver.vehicle_type)" class="text-gray-500" :size="20" />
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-900 truncate max-w-[150px]">{{ driver.full_name }}</h3>
                    <p class="text-xs text-gray-500 font-mono">{{ driver.license_plate }}</p>
                  </div>
                </div>
                
                <span v-if="!driver.is_verified" class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-1 rounded flex items-center gap-1 uppercase">
                  <AlertTriangle :size="12" /> Revisar
                </span>
              </div>

              <div class="border-t pt-3 flex justify-between items-center text-xs">
                <div class="space-x-2 font-medium">
                  <span :class="driver.is_active ? 'text-green-600' : 'text-red-600'">● {{ driver.is_active ? 'Activo' : 'Inactivo' }}</span>
                  <span class="text-gray-300">|</span>
                  <span class="text-gray-500 font-mono">{{ driver.phone }}</span>
                </div>
                <ChevronRight :size="16" class="text-gray-400 group-hover:text-black" />
              </div>
            </div>
          </div>
        </Link>
      </div>

      <div v-if="drivers.data.length === 0" class="py-12 text-center border-2 border-dashed rounded-xl bg-gray-50 mt-4">
        <Truck :size="48" class="text-gray-300 mx-auto mb-4" />
        <p class="text-gray-500 font-medium">No se encontraron conductores en la base de datos.</p>
      </div>
    </div>
  </AdminLayout>
</template>