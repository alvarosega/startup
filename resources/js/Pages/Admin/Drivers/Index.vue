<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'
import { 
  UserPlus, 
  Truck, 
  Bike, 
  Car, 
  Search,
  AlertTriangle, 
  ChevronRight 
} from 'lucide-vue-next'

const props = defineProps({ 
  drivers: Object, 
  filters: Object,
  pending_count: Number
})

const search = ref(props.filters.search || '')
const currentTab = ref(props.filters.status || 'all')

// Filtrado
watch([search, currentTab], debounce(() => {
  router.get(route('admin.drivers.index'), { 
    search: search.value,
    status: currentTab.value === 'all' ? null : currentTab.value
  }, { preserveState: true, replace: true })
}, 300))

// Helpers visuales
const getVehicleIcon = (type) => {
  if (type === 'moto') return Bike
  if (type === 'car') return Car
  return Truck
}

const getStatusColor = (driver) => {
  if (!driver.is_verified) return 'pending'
  if (driver.is_active) return 'success'
  return 'error'
}

const getStatusClasses = (driver) => {
  if (!driver.is_verified) return 'bg-warning/10 text-warning border-warning/20 hover:bg-warning/15'
  if (driver.is_active) return 'bg-success/10 text-success border-success/20 hover:bg-success/15'
  return 'bg-error/10 text-error border-error/20 hover:bg-error/15'
}
</script>

<template>
  <AdminLayout>
    <div class="container py-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
        <div class="space-y-1">
          <h1 class="text-3xl md:text-4xl font-display font-semibold">Flota de Conductores</h1>
          <p class="text-muted-foreground text-sm md:text-base">
            Verificación de identidad y gestión de vehículos
          </p>
        </div>
        
        <Link 
          :href="route('admin.drivers.create')" 
          class="btn btn-primary btn-lg flex items-center gap-2 shadow-lg hover:shadow-xl transition-all duration-200"
        >
          <UserPlus :size="18" />
          Nuevo Conductor
        </Link>
      </div>

      <!-- Filtros -->
      <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
        <!-- Tabs -->
        <div class="bg-muted p-1 rounded-xl flex gap-1">
          <button
            @click="currentTab = 'all'"
            class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200 ease-smooth"
            :class="[
              currentTab === 'all'
                ? 'bg-background text-primary shadow-sm'
                : 'text-muted-foreground hover:text-foreground'
            ]"
          >
            Todos los Conductores
          </button>
          <button
            @click="currentTab = 'pending'"
            class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200 ease-smooth flex items-center gap-2"
            :class="[
              currentTab === 'pending'
                ? 'bg-background text-primary shadow-sm'
                : 'text-muted-foreground hover:text-foreground'
            ]"
          >
            Pendientes de Verificación
            <span 
              v-if="pending_count > 0" 
              class="bg-warning text-warning-foreground text-[10px] px-1.5 py-0.5 rounded-full"
            >
              {{ pending_count }}
            </span>
          </button>
        </div>

        <!-- Search -->
        <div class="relative w-full md:w-64">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="16" />
          <input 
            v-model="search"
            type="text" 
            placeholder="Buscar por nombre o placa..."
            class="w-full pl-10 pr-4 py-2.5 bg-background border border-input rounded-xl text-sm focus:ring-2 focus:ring-ring/30 focus:border-ring transition-all ease-smooth duration-200 outline-none"
          />
        </div>
      </div>

      <!-- Grid de Conductores -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 animate-in">
        <Link 
          v-for="driver in drivers.data" 
          :key="driver.id" 
          :href="route('admin.drivers.edit', driver.id)"
          class="block group"
        >
          <div class="card cursor-pointer hover:border-primary/50 relative overflow-hidden">
            <!-- Status Indicator -->
            <div 
              class="absolute left-0 top-0 bottom-0 w-1.5" 
              :class="getStatusColor(driver) === 'pending' ? 'bg-warning' : getStatusColor(driver) === 'success' ? 'bg-success' : 'bg-error'" 
            />
            
            <div class="card-content">
              <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                  <div class="inline-flex items-center justify-center rounded-full overflow-hidden bg-muted border border-border w-10 h-10">
                    <component :is="getVehicleIcon(driver.vehicle_type)" class="text-muted-foreground" :size="20" />
                  </div>
                  <div>
                    <h3 class="font-semibold text-foreground">{{ driver.full_name }}</h3>
                    <p class="text-xs text-muted-foreground font-mono tracking-wide">
                      {{ driver.license_plate || 'Sin placa' }}
                    </p>
                  </div>
                </div>
                
                <!-- Status Badge -->
                <span 
                  v-if="!driver.is_verified"
                  class="badge badge-warning inline-flex items-center gap-1.5 animate-pulse"
                >
                  <AlertTriangle :size="12" />
                  Revisar
                </span>
              </div>

              <div class="border-t border-border/50 pt-3 flex justify-between items-center">
                <div class="text-xs space-x-2">
                  <span :class="driver.is_active ? 'text-success' : 'text-error'">
                    ● {{ driver.is_active ? 'Activo' : 'Inactivo' }}
                  </span>
                  <span class="text-border">|</span>
                  <span class="text-muted-foreground">{{ driver.phone }}</span>
                </div>
                <ChevronRight :size="16" class="text-muted-foreground group-hover:text-primary transition-colors duration-200" />
              </div>
            </div>
          </div>
        </Link>
      </div>

      <!-- Estado vacío -->
      <div v-if="drivers.data.length === 0" class="py-12 text-center animate-scale-in">
        <div class="w-16 h-16 bg-muted/20 rounded-full flex items-center justify-center mx-auto mb-4">
          <Truck :size="24" class="text-muted-foreground/50" />
        </div>
        <p class="text-muted-foreground font-medium">No se encontraron conductores</p>
        <p class="text-sm text-muted-foreground/70 mt-1">
          Intenta con otros filtros o 
          <Link :href="route('admin.drivers.create')" class="text-primary hover:underline">
            agrega un nuevo conductor
          </Link>
        </p>
      </div>

      <!-- Paginación -->
      <div v-if="drivers.meta && drivers.meta.total > drivers.meta.per_page" class="mt-8">
        <div class="flex justify-center">
          <nav class="flex gap-2">
            <Link
              v-for="(link, index) in drivers.meta.links"
              :key="index"
              :href="link.url || '#'"
              :class="[
                'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors duration-200',
                link.active 
                  ? 'bg-primary text-primary-foreground' 
                  : 'bg-muted text-muted-foreground hover:bg-muted/80'
              ]"
              v-html="link.label"
            />
          </nav>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>