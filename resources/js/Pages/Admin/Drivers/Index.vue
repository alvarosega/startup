<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { debounce } from 'lodash'
import { 
    UserPlus, Truck, Bike, Car, Search, AlertTriangle, 
    ChevronRight, Building2, Cpu, Terminal, Radar, Wifi,
    WifiOff, Shield, MapPin, Phone, Activity, Zap
} from 'lucide-vue-next'

const props = defineProps({ 
    drivers: Object, 
    filters: Object,
    pending_count: Number
})

const search = ref(props.filters.search || '')
const currentTab = ref(props.filters.status || 'all')
const hoveredDriver = ref(null)

watch([search, currentTab], debounce(() => {
    router.get(route('admin.drivers.index'), { 
        search: search.value,
        status: currentTab.value === 'all' ? null : currentTab.value
    }, { preserveState: true, replace: true })
}, 300))
// Estadísticas computadas
const stats = computed(() => {
    const total = props.drivers?.total || 0
    // Conductores con su radar encendido
    const active = props.drivers?.data?.filter(d => d.is_online).length || 0 
    // Conductores con documentos aprobados
    const verified = props.drivers?.data?.filter(d => d.status === 'active').length || 0 
    const pending = props.pending_count || 0
    
    return { total, active, verified, pending }
})
const getVehicleIcon = (type) => {
    if (type === 'moto') return Bike
    if (type === 'car') return Car
    return Truck
}

const getVehicleLabel = (type) => {
    if (type === 'moto') return 'MOTOCICLETA'
    if (type === 'car') return 'AUTOMÓVIL'
    return 'CAMIONETA'
}
// CORRECCIÓN 1: Manejar el estado de Verificación / Aprobación (status)
const getStatusColor = (driver) => {
    // Si su estatus es pending, es un conductor nuevo que requiere revisión de documentos.
    if (driver.status === 'pending') {
        return { bg: 'bg-warning', text: 'text-warning', border: 'border-warning', label: 'PENDIENTE' }
    }
    // Si su estatus es active, sus documentos fueron aprobados.
    if (driver.status === 'active') {
        return { bg: 'bg-cyan-500', text: 'text-cyan-500', border: 'border-cyan-500', label: 'ACTIVO' }
    }
    // Cualquier otro estado (ej. suspendido, bloqueado).
    return { bg: 'bg-destructive', text: 'text-destructive', border: 'border-destructive', label: 'INACTIVO' }
}

// CORRECCIÓN 2: Manejar el estado de Red / GPS (is_online)
const getNetworkStatus = (driver) => {
    // Si es un conductor bloqueado o pendiente, nunca podrá estar online.
    if (driver.status !== 'active') {
        return { icon: AlertTriangle, class: 'text-warning', label: 'PENDING' }
    }
    
    // Si está activo, evaluamos si encendió el radar en su app.
    // Usamos !driver.is_online para verificar falso explícitamente.
    if (!driver.is_online) {
        return { icon: WifiOff, class: 'text-destructive', label: 'OFFLINE' }
    }
    
    // Está activo y con el radar encendido.
    return { icon: Wifi, class: 'text-cyan-500', label: 'ONLINE' }
}
// Código de conductor
const getDriverCode = (id) => {
    return `DRV_${String(id).padStart(4, '0')}`
}
</script>

<template>
    <AdminLayout>
        <div class="px-4 md:px-6 lg:px-8 py-6 max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8 border-b border-primary/30 pb-6 relative group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                        data-text="FLOTA DE CONDUCTORES">
                        FLOTA DE CONDUCTORES
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground font-bold tracking-widest uppercase mt-1 flex items-center gap-2">
                        <Radar :size="12" class="text-primary animate-pulse" />
                        VERIFICACIÓN DE IDENTIDAD Y GESTIÓN DE VEHÍCULOS
                        <Terminal :size="12" class="text-primary animate-pulse" />
                    </p>
                </div>
                
                <Link :href="route('admin.drivers.create')" 
                      class="px-6 py-3 bg-primary text-primary-foreground font-mono text-xs border border-primary/50 relative overflow-hidden group/btn">
                    <span class="flex items-center gap-2 relative z-10">
                        <UserPlus :size="16" /> NUEVO_CONDUCTOR
                    </span>
                    <!-- Efecto scan -->
                    <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-500"></span>
                    <!-- Esquinas -->
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                </Link>
            </div>

            <!-- Stats Dashboard -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="border border-border/50 p-4 relative group/stat">
                    <div class="flex items-center justify-between">
                        <Truck :size="20" class="text-primary" />
                        <span class="text-[8px] font-mono text-primary/50">TOTAL</span>
                    </div>
                    <p class="text-2xl font-mono font-bold text-foreground mt-2">{{ String(stats.total).padStart(2, '0') }}</p>
                    <p class="text-[10px] text-muted-foreground font-mono">CONDUCTORES REGISTRADOS</p>
                    <!-- Esquinas -->
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                </div>

                <div class="border border-border/50 p-4 relative group/stat">
                    <div class="flex items-center justify-between">
                        <Wifi :size="20" class="text-cyan-500" />
                        <span class="text-[8px] font-mono text-primary/50">ACTIVOS</span>
                    </div>
                    <p class="text-2xl font-mono font-bold text-cyan-500 mt-2">{{ String(stats.active).padStart(2, '0') }}</p>
                    <p class="text-[10px] text-muted-foreground font-mono">CONDUCTORES ONLINE</p>
                </div>

                <div class="border border-border/50 p-4 relative group/stat">
                    <div class="flex items-center justify-between">
                        <Shield :size="20" class="text-cyan-500" />
                        <span class="text-[8px] font-mono text-primary/50">VERIFICADOS</span>
                    </div>
                    <p class="text-2xl font-mono font-bold text-cyan-500 mt-2">{{ String(stats.verified).padStart(2, '0') }}</p>
                    <p class="text-[10px] text-muted-foreground font-mono">IDENTIDAD CONFIRMADA</p>
                </div>

                <div class="border border-border/50 p-4 relative group/stat">
                    <div class="flex items-center justify-between">
                        <AlertTriangle :size="20" class="text-warning" />
                        <span class="text-[8px] font-mono text-primary/50">PENDIENTES</span>
                    </div>
                    <p class="text-2xl font-mono font-bold text-warning mt-2">{{ String(stats.pending).padStart(2, '0') }}</p>
                    <p class="text-[10px] text-muted-foreground font-mono">REVISIÓN REQUERIDA</p>
                </div>
            </div>

            <!-- Filtros y Búsqueda -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
                <div class="flex gap-1 p-1 border border-border/50 bg-background">
                    <button @click="currentTab = 'all'" 
                            class="px-4 py-2 text-[10px] font-mono font-bold transition-all relative group/tab"
                            :class="currentTab === 'all' ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-primary'">
                        TODOS ({{ stats.total }})
                        <span class="absolute bottom-0 left-0 w-0 h-[1px] bg-primary group-hover/tab:w-full transition-all"></span>
                    </button>
                    <button @click="currentTab = 'pending'" 
                            class="px-4 py-2 text-[10px] font-mono font-bold transition-all relative group/tab flex items-center gap-2"
                            :class="currentTab === 'pending' ? 'text-warning border-b-2 border-warning' : 'text-muted-foreground hover:text-warning'">
                        PENDIENTES
                        <span v-if="pending_count > 0" 
                              class="bg-warning text-black text-[8px] px-1.5 py-0.5 font-mono">
                            {{ pending_count }}
                        </span>
                    </button>
                </div>

                <div class="relative w-full md:w-64 group/search">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary transition-colors" :size="16" />
                    <input v-model="search" 
                           type="text" 
                           placeholder="> BUSCAR POR NOMBRE O PLACA..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                    <!-- Efecto de escritura -->
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 w-1 h-4 bg-primary animate-pulse"></div>
                </div>
            </div>

            <!-- Grid de Conductores -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link v-for="driver in drivers.data" :key="driver.id" 
                      :href="route('admin.drivers.edit', driver.id)" 
                      @mouseenter="hoveredDriver = driver.id"
                      @mouseleave="hoveredDriver = null"
                      class="block group/card">
                    
                    <div class="border transition-all duration-500 relative overflow-hidden"
                         :class="[
                             driver.is_online ? 'border-border/50 hover:border-primary/50 hover:shadow-neon-primary' : 'border-border/30 opacity-70',
                             driver.status === 'pending' && 'border-warning/30 hover:border-warning'
                         ]">
                        
                        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                        
                        <div class="absolute left-0 top-0 bottom-0 w-1" :class="getStatusColor(driver).bg"></div>
                        
                        <div class="absolute top-2 right-2 z-20 flex gap-1">
                            <div v-if="driver.status === 'pending'"
                                 class="bg-warning/20 text-warning border border-warning/30 text-[8px] font-mono px-2 py-0.5 flex items-center gap-1">
                                <AlertTriangle :size="10" />
                                PENDING
                            </div>
                        </div>
                        
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="p-2 border transition-all duration-300"
                                             :class="[
                                                 driver.is_online ? 'border-primary bg-primary/10' : 'border-border bg-background',
                                                 driver.status === 'pending' && 'border-warning bg-warning/10'
                                             ]">
                                            <component :is="getVehicleIcon(driver.details?.vehicle_type)" 
                                                       :size="20" 
                                                       :class="driver.is_online ? 'text-primary' : 'text-muted-foreground'" />
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 w-3 h-3 rounded-full border-2 border-background"
                                             :class="getStatusColor(driver).bg"></div>
                                    </div>
                                    
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-mono font-bold text-foreground text-sm group-hover/card:text-primary transition-colors">
                                                {{ driver.details?.first_name }} {{ driver.details?.last_name }}
                                            </h3>
                                            <span class="text-[8px] font-mono text-primary/50">
                                                {{ getDriverCode(driver.id) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <p class="text-[9px] font-mono text-muted-foreground border border-border/30 px-1.5 py-0.5">
                                                {{ driver.details?.license_plate || 'SIN_PLACA' }}
                                            </p>
                                            <p class="text-[9px] font-mono flex items-center gap-1 px-1.5 py-0.5"
                                               :class="driver.branch ? 'text-primary border border-primary/30' : 'text-muted-foreground border border-border/30'">
                                                <Building2 :size="10" /> 
                                                {{ driver.branch ? driver.branch.name : 'SIN_BASE' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 border-t border-border/30 pt-3 mb-3">
                                <div class="flex items-center gap-1 text-[9px] font-mono text-muted-foreground">
                                    <component :is="getVehicleIcon(driver.details?.vehicle_type)" :size="10" class="text-primary" />
                                    <span>{{ getVehicleLabel(driver.details?.vehicle_type) }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-[9px] font-mono text-muted-foreground justify-end">
                                    <Phone :size="10" class="text-primary" />
                                    <span>{{ driver.phone }}</span>
                                </div>
                            </div>

                            <div class="flex justify-between items-center text-[9px] font-mono">
                                <div class="flex items-center gap-2">
                                    <component :is="getNetworkStatus(driver).icon" 
                                               :size="12" 
                                               :class="getNetworkStatus(driver).class" />
                                    <span :class="getNetworkStatus(driver).class">
                                        {{ getNetworkStatus(driver).label }}
                                    </span>
                                    <span v-if="driver.status === 'active'" class="text-cyan-500 ml-1">
                                        <Shield :size="10" class="inline" /> VERIFIED
                                    </span>
                                </div>
                                <ChevronRight :size="14" 
                                              class="text-muted-foreground group-hover/card:text-primary group-hover/card:translate-x-1 transition-all" />
                            </div>
                        </div>

                        <div v-if="hoveredDriver === driver.id" 
                             class="absolute inset-0 border-2 pointer-events-none"
                             :class="driver.is_online ? 'border-primary/30' : 'border-border/30'"></div>
                    </div>
                </Link>
            </div>

            <!-- Estado vacío -->
            <div v-if="drivers.data.length === 0" 
                 class="border border-dashed border-primary/30 p-12 text-center relative mt-8">
                <Truck :size="48" class="mx-auto text-muted-foreground mb-4" />
                <p class="text-sm font-mono text-foreground">// NO SE ENCONTRARON CONDUCTORES</p>
                <p class="text-[10px] font-mono text-muted-foreground mt-2">
                    {{ search ? 'NO COINCIDEN CON LA BÚSQUEDA' : 'BASE DE DATOS VACÍA' }}
                </p>
                <button @click="search = ''; currentTab = 'all'" 
                        class="mt-4 text-[10px] font-mono text-primary hover:text-primary/80 transition-colors"
                        v-if="search || currentTab !== 'all'">
                    LIMPIAR FILTROS <ChevronRight :size="12" class="inline" />
                </button>
            </div>

            <!-- Session ID -->
            <div class="mt-8 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // DRIVERS_INDEX // {{ new Date().toISOString().slice(0,10) }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Animaciones */
@keyframes scanline {
    0% { transform: translateY(-100%); }
    100% { transform: translateY(1000%); }
}

.animate-scanline {
    animation: scanline 8s linear infinite;
}

/* Efecto glitch */
.glitch-text {
    position: relative;
    animation: glitch-skew 4s infinite linear alternate-reverse;
}

.glitch-text::before,
.glitch-text::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.8;
}

.glitch-text::before {
    color: #0ff;
    z-index: -1;
    animation: glitch-anim-1 0.4s infinite linear alternate-reverse;
}

.glitch-text::after {
    color: #f0f;
    z-index: -2;
    animation: glitch-anim-2 0.4s infinite linear alternate-reverse;
}

@keyframes glitch-skew {
    0% { transform: skew(0deg); }
    20% { transform: skew(0deg); }
    21% { transform: skew(2deg); }
    22% { transform: skew(0deg); }
    80% { transform: skew(0deg); }
    81% { transform: skew(-2deg); }
    82% { transform: skew(0deg); }
    100% { transform: skew(0deg); }
}

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); }
    20% { clip-path: inset(50% 0 10% 0); }
    40% { clip-path: inset(10% 0 60% 0); }
    60% { clip-path: inset(80% 0 5% 0); }
    80% { clip-path: inset(30% 0 40% 0); }
    100% { clip-path: inset(40% 0 20% 0); }
}

@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); }
    20% { clip-path: inset(20% 0 50% 0); }
    40% { clip-path: inset(70% 0 5% 0); }
    60% { clip-path: inset(10% 0 70% 0); }
    80% { clip-path: inset(40% 0 30% 0); }
    100% { clip-path: inset(30% 0 40% 0); }
}

/* Sombras neón */
.shadow-neon-primary {
    box-shadow: 0 0 20px hsl(var(--primary) / 0.3);
}

.shadow-neon-warning {
    box-shadow: 0 0 20px rgba(255, 170, 0, 0.3);
}
/* Colores personalizados */
.text-warning { color: #ffaa00; }
.bg-warning { background-color: #ffaa00; }
.border-warning { border-color: #ffaa00; }

.text-cyan-500 { color: #00ffff; }
.bg-cyan-500 { background-color: #00ffff; }
.border-cyan-500 { border-color: #00ffff; }

/* Efecto de brillo para íconos */
.icon-glow {
    filter: drop-shadow(0 0 4px currentColor);
    transition: filter 0.3s ease;
}
</style>