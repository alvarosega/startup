<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'
import { 
    UserPlus, Truck, Bike, Car, Search, AlertTriangle, 
    ChevronRight, Radar, Wifi, WifiOff, Activity
} from 'lucide-vue-next'

const props = defineProps({ 
    drivers: Object, 
    filters: Object,
    // Sincronizado con los conteos que debe enviar el GetPaginatedDriversAction
    stats_counters: {
        type: Object,
        default: () => ({ total: 0, approved: 0, pending: 0, suspended: 0 })
    }
})

const search = ref(props.filters.search || '')
const currentTab = ref(props.filters.status || 'all')

// Vigilante de filtros: Sincroniza la URL con el estado de la UI
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
        // RECTIFICACIÓN: Vocabulario unificado
        pending: { bg: 'bg-amber-500', text: 'text-amber-500', label: 'PENDIENTE' },
        approved: { bg: 'bg-emerald-500', text: 'text-emerald-500', label: 'APROBADO' },
        suspended: { bg: 'bg-destructive', text: 'text-destructive', label: 'SUSPENDIDO' }
    }
    return themes[status] || { bg: 'bg-muted', text: 'text-muted-foreground', label: 'DESCONOCIDO' }
}

const getNetworkStatus = (driver) => {
    // Si no está aprobado, no puede estar online bajo ninguna circunstancia
    if (driver.status !== 'approved') {
        return { icon: AlertTriangle, class: 'text-destructive', label: 'BLOQUEADO' }
    }
    
    // Basado puramente en el boolean is_online (sin GPS)
    return driver.is_online 
        ? { icon: Wifi, class: 'text-emerald-500', label: 'CONECTADO' } 
        : { icon: WifiOff, class: 'text-muted-foreground', label: 'DESCONECTADO' }
}
</script>

<template>
    <AdminLayout>
        <div class="px-4 py-6 max-w-7xl mx-auto space-y-8">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-primary/30 pb-6 relative">
                <div>
                    <h1 class="text-3xl font-black tracking-widest text-primary uppercase italic">
                        Flota de <span class="text-foreground">Conductores</span>
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground font-bold tracking-widest mt-1 flex items-center gap-2">
                        <Radar :size="12" class="text-primary animate-pulse" />
                        GESTIÓN DE UNIDADES Y VERIFICACIÓN DE CREDENCIALES
                    </p>
                </div>
                
                <Link :href="route('admin.drivers.create')" 
                      class="px-6 py-3 bg-primary text-primary-foreground font-mono text-xs border border-primary/50 hover:shadow-neon-primary transition-all flex items-center gap-2 italic font-black uppercase tracking-widest">
                    <UserPlus :size="16" /> Registrar_Unidad
                </Link>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(val, label) in stats_counters" :key="label" class="border border-border/50 p-4 relative bg-card/30">
                    <div class="flex justify-between items-start">
                        <span class="text-[8px] font-mono text-primary/50 uppercase tracking-tighter">{{ label }}</span>
                        <Activity :size="14" class="text-primary/30" />
                    </div>
                    <p class="text-2xl font-mono font-bold mt-2">{{ String(val).padStart(2, '0') }}</p>
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex gap-1 p-1 border border-border/50 bg-background w-full md:w-auto">
                    <button v-for="tab in ['all', 'pending', 'approved', 'suspended']" :key="tab"
                            @click="currentTab = tab"
                            class="flex-1 md:flex-none px-5 py-2 text-[10px] font-mono font-bold transition-all uppercase"
                            :class="currentTab === tab ? 'bg-primary text-primary-foreground italic' : 'text-muted-foreground hover:text-primary'">
                        {{ tab }}
                    </button>
                </div>

                <div class="relative w-full md:w-80 group/search">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary" :size="16" />
                    <input v-model="search" type="text" placeholder="> BUSCAR POR NOMBRE O PLACA..." 
                           class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-xs focus:border-primary outline-none transition-all placeholder:opacity-30" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link v-for="driver in drivers.data" :key="driver.id" 
                      :href="route('admin.drivers.edit', driver.id)" 
                      class="group/card relative border border-border/50 bg-card/20 hover:border-primary/50 transition-all overflow-hidden">
                    
                    <div class="absolute left-0 top-0 bottom-0 w-1 transition-all group-hover/card:w-1.5" :class="getStatusTheme(driver.status).bg"></div>
                    
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 border" :class="driver.is_online && driver.status === 'approved' ? 'border-emerald-500 bg-emerald-500/10' : 'border-border bg-background'">
                                    <component :is="getVehicleIcon(driver.profile?.vehicle_type)" :size="20" 
                                               :class="driver.is_online && driver.status === 'approved' ? 'text-emerald-500' : 'text-muted-foreground'" />
                                </div>
                                <div>
                                    <h3 class="font-mono font-bold text-sm group-hover/card:text-primary transition-colors uppercase truncate w-40">
                                        {{ driver.profile?.first_name }} {{ driver.profile?.last_name }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-mono text-muted-foreground border border-border/30 px-1.5 py-0.5 uppercase tracking-tighter">
                                            PLACA: {{ driver.profile?.license_plate || 'S/P' }}
                                        </span>
                                        <span class="text-[9px] font-mono text-primary border border-primary/30 px-1.5 py-0.5 tracking-tighter uppercase">
                                            {{ driver.branch?.name || 'SIN_BASE' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t border-border/30">
                            <div class="flex items-center gap-2 text-[10px] font-mono uppercase font-black italic">
                                <component :is="getNetworkStatus(driver).icon" :size="12" :class="getNetworkStatus(driver).class" />
                                <span :class="getNetworkStatus(driver).class">{{ getNetworkStatus(driver).label }}</span>
                            </div>
                            <ChevronRight :size="14" class="text-muted-foreground group-hover/card:translate-x-1 transition-transform" />
                        </div>
                    </div>
                </Link>
            </div>

            <div v-if="drivers.data.length === 0" class="border border-dashed border-primary/30 p-20 text-center animate-pulse">
                <Truck :size="40" class="mx-auto text-muted-foreground/30 mb-4" />
                <p class="text-xs font-mono text-muted-foreground uppercase tracking-widest italic">// NO SE DETECTARON UNIDADES BAJO ESTOS CRITERIOS //</p>
            </div>

            <div v-if="drivers.links && drivers.links.length > 3" class="flex justify-center gap-2 mt-8">
                <Link v-for="(link, k) in drivers.links" :key="k" 
                      :href="link.url || '#'" 
                      v-html="link.label"
                      class="px-4 py-2 border font-mono text-[10px] transition-all"
                      :class="{
                          'bg-primary text-primary-foreground border-primary': link.active,
                          'bg-background text-muted-foreground border-border hover:border-primary': !link.active,
                          'opacity-30 pointer-events-none': !link.url
                      }" />
            </div>
        </div>
    </AdminLayout>
</template>