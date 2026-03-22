<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { debounce } from 'lodash'
import { 
    UserPlus, Truck, Bike, Car, Search, AlertTriangle, 
    ChevronRight, Building2, Cpu, Terminal, Radar, Wifi,
    WifiOff, Shield, Phone, Activity
} from 'lucide-vue-next'

const props = defineProps({ 
    drivers: Object, 
    filters: Object,
    // REGLA: Los conteos globales deben venir del Backend para precisión absoluta
    stats_counters: {
        type: Object,
        default: () => ({ total: 0, online: 0, verified: 0, pending: 0 })
    }
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
    const icons = { moto: Bike, car: Car, truck: Truck }
    return icons[type] || Truck
}

const getStatusTheme = (status) => {
    const themes = {
        pending: { bg: 'bg-warning', text: 'text-warning', label: 'PENDIENTE' },
        active: { bg: 'bg-cyan-500', text: 'text-cyan-500', label: 'ACTIVO' },
        inactive: { bg: 'bg-destructive', text: 'text-destructive', label: 'INACTIVO' }
    }
    return themes[status] || themes.inactive
}

const getNetworkStatus = (driver) => {
    if (driver.status !== 'active') return { icon: AlertTriangle, class: 'text-warning', label: 'BLOCKED' }
    return driver.is_online 
        ? { icon: Wifi, class: 'text-cyan-500', label: 'ONLINE' } 
        : { icon: WifiOff, class: 'text-muted-foreground', label: 'OFFLINE' }
}
</script>

<template>
    <AdminLayout>
        <div class="px-4 py-6 max-w-7xl mx-auto space-y-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-primary/30 pb-6 relative group/header">
                <div class="relative z-10">
                    <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text" data-text="FLOTA DE CONDUCTORES">
                        FLOTA DE CONDUCTORES
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground font-bold tracking-widest mt-1 flex items-center gap-2">
                        <Radar :size="12" class="text-primary animate-pulse" />
                        SISTEMA DE VERIFICACIÓN Y CONTROL DE DESPACHO
                    </p>
                </div>
                
                <Link :href="route('admin.drivers.create')" 
                      class="px-6 py-3 bg-primary text-primary-foreground font-mono text-xs border border-primary/50 relative overflow-hidden group/btn">
                    <span class="flex items-center gap-2 relative z-10 font-black italic">
                        <UserPlus :size="16" /> NUEVO_CONDUCTOR
                    </span>
                    <span class="absolute inset-0 bg-white/10 translate-y-full group-hover/btn:translate-y-0 transition-transform" />
                </Link>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(val, label) in stats_counters" :key="label" class="border border-border/50 p-4 relative bg-card/30">
                    <div class="flex justify-between items-start">
                        <span class="text-[8px] font-mono text-primary/50 uppercase">{{ label }}</span>
                        <Activity :size="14" class="text-primary/30" />
                    </div>
                    <p class="text-2xl font-mono font-bold mt-2">{{ String(val).padStart(2, '0') }}</p>
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex gap-1 p-1 border border-border/50 bg-background w-full md:w-auto">
                    <button v-for="tab in ['all', 'pending', 'active']" :key="tab"
                            @click="currentTab = tab"
                            class="flex-1 md:flex-none px-6 py-2 text-[10px] font-mono font-bold transition-all uppercase"
                            :class="currentTab === tab ? 'bg-primary text-primary-foreground italic' : 'text-muted-foreground hover:text-primary'">
                        {{ tab }}
                    </button>
                </div>

                <div class="relative w-full md:w-80 group/search">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary" :size="16" />
                    <input v-model="search" type="text" placeholder="> BUSCAR POR NOMBRE O PLACA..." 
                           class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-xs focus:border-primary outline-none transition-all" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link v-for="driver in drivers.data" :key="driver.id" 
                      :href="route('admin.drivers.edit', driver.id)" 
                      class="group/card relative border border-border/50 bg-card/20 hover:border-primary/50 transition-all">
                    
                    <div class="absolute left-0 top-0 bottom-0 w-1" :class="getStatusTheme(driver.status).bg"></div>
                    
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 border" :class="driver.is_online ? 'border-cyan-500 bg-cyan-500/10' : 'border-border bg-background'">
                                    <component :is="getVehicleIcon(driver.profile?.vehicle_type)" :size="20" 
                                               :class="driver.is_online ? 'text-cyan-500' : 'text-muted-foreground'" />
                                </div>
                                <div>
                                    <h3 class="font-mono font-bold text-sm group-hover/card:text-primary transition-colors">
                                        {{ driver.profile?.first_name }} {{ driver.profile?.last_name }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-mono text-muted-foreground border border-border/30 px-1.5 py-0.5 uppercase">
                                            {{ driver.profile?.license_plate || 'NO_PLATE' }}
                                        </span>
                                        <span class="text-[9px] font-mono text-primary border border-primary/30 px-1.5 py-0.5">
                                            {{ driver.branch?.name || 'UNASSIGNED' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t border-border/30">
                            <div class="flex items-center gap-2 text-[10px] font-mono">
                                <component :is="getNetworkStatus(driver).icon" :size="12" :class="getNetworkStatus(driver).class" />
                                <span :class="getNetworkStatus(driver).class">{{ getNetworkStatus(driver).label }}</span>
                            </div>
                            <ChevronRight :size="14" class="text-muted-foreground group-hover/card:translate-x-1 transition-transform" />
                        </div>
                    </div>
                </Link>
            </div>

            <div v-if="drivers.data.length === 0" class="border border-dashed border-primary/30 p-20 text-center">
                <Truck :size="40" class="mx-auto text-muted-foreground/30 mb-4" />
                <p class="text-xs font-mono text-muted-foreground uppercase tracking-widest">// NO SE DETECTARON UNIDADES EN ESTE SECTOR //</p>
            </div>
        </div>
    </AdminLayout>
</template>