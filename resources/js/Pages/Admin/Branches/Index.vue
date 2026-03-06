<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MapComponent from '@/Components/Base/MapComponent.vue';
import { 
    MapPin, Phone, Edit, Building, Plus, 
    Copy, ExternalLink, Search, Globe, 
    Zap, DollarSign, Target, ShieldCheck, ArrowRight,
    Radio, Cpu, Radar, Crosshair, Navigation, TowerControl,
    Wifi, WifiOff, AlertTriangle, CheckCircle2, GitBranch,
    HardDrive, Server, Activity, Clock, Terminal
} from 'lucide-vue-next';

const props = defineProps({
    branches: Array,
    auth: Object // Para verificar permisos super_admin
});

const isLoading = ref(true);
const mapRef = ref(null);
const mapCenter = ref([-16.5000, -68.1500]);
const mapZoom = ref(12);
const hoveredBranch = ref(null);
const selectedBranchType = ref('all'); // 'all', 'active', 'inactive'

onMounted(() => {
    setTimeout(() => { isLoading.value = false; }, 600);
    const defaultBranch = props.branches?.find(b => b.is_default);
    if (defaultBranch) {
        focusOnMap(defaultBranch);
    }
});

const focusOnMap = (branch) => {
    if (branch.latitude && branch.longitude) {
        mapCenter.value = [parseFloat(branch.latitude), parseFloat(branch.longitude)];
        mapZoom.value = 16;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const copyId = (id) => {
    navigator.clipboard.writeText(id);
    // Aquí iría la lógica de toast
};

// Filtrado de sucursales
const filteredBranches = computed(() => {
    if (selectedBranchType.value === 'all') return props.branches;
    if (selectedBranchType.value === 'active') {
        return props.branches.filter(b => b.is_active);
    }
    return props.branches.filter(b => !b.is_active);
});

// Estadísticas
const stats = computed(() => {
    const total = props.branches?.length || 0;
    const active = props.branches?.filter(b => b.is_active).length || 0;
    const inactive = total - active;
    const surgeZones = props.branches?.filter(b => b.surge_multiplier > 1).length || 0;
    
    return { total, active, inactive, surgeZones };
});

const fmt = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);

// Estado de red para cada nodo
const getNetworkStatus = (branch) => {
    if (!branch.is_active) return { icon: WifiOff, class: 'text-destructive', label: 'OFFLINE' };
    if (branch.surge_multiplier > 1.5) return { icon: Activity, class: 'text-warning', label: 'ALERTA // SURGE' };
    return { icon: Wifi, class: 'text-cyan-500', label: 'ONLINE' };
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="pt-0 pb-4 border-b border-primary/30 flex justify-between items-end relative group/header">
                <!-- Efecto de escaneo en el header -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                        data-text="NODOS OPERATIVOS">
                        NODOS OPERATIVOS
                    </h1>
                    <p class="text-[10px] text-muted-foreground font-mono font-bold tracking-widest uppercase mt-1 flex items-center gap-2">
                        <TowerControl :size="12" class="text-primary" />
                        {{ stats.total }} UNIDADES EN RED // {{ stats.active }} ACTIVAS
                        <Terminal :size="12" class="text-primary" />
                    </p>
                </div>
                
                <Link :href="route('admin.branches.create')" 
                      class="px-4 py-2 bg-primary text-primary-foreground font-mono text-xs border border-primary/50 relative overflow-hidden group/btn">
                    <span class="flex items-center gap-2 relative z-10">
                        <Plus :size="14" /> INICIALIZAR NODO
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
        </template>

        <div class="space-y-6 pb-32 md:pb-12 relative mt-4">
            
            <!-- Stats Dashboard -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="border border-border/50 p-4 relative group/stat">
                    <div class="flex items-center justify-between">
                        <Server :size="20" class="text-primary" />
                        <span class="text-[8px] font-mono text-primary/50">TOTAL</span>
                    </div>
                    <p class="text-2xl font-mono font-bold text-foreground mt-2">{{ String(stats.total).padStart(2, '0') }}</p>
                    <p class="text-[10px] text-muted-foreground font-mono">NODOS INSTALADOS</p>
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
                    <p class="text-[10px] text-muted-foreground font-mono">NODOS ONLINE</p>
                </div>

                <div class="border border-border/50 p-4 relative group/stat">
                    <div class="flex items-center justify-between">
                        <WifiOff :size="20" class="text-destructive" />
                        <span class="text-[8px] font-mono text-primary/50">INACTIVOS</span>
                    </div>
                    <p class="text-2xl font-mono font-bold text-destructive mt-2">{{ String(stats.inactive).padStart(2, '0') }}</p>
                    <p class="text-[10px] text-muted-foreground font-mono">NODOS OFFLINE</p>
                </div>

                <div class="border border-border/50 p-4 relative group/stat">
                    <div class="flex items-center justify-between">
                        <Activity :size="20" class="text-warning" />
                        <span class="text-[8px] font-mono text-primary/50">SURGE</span>
                    </div>
                    <p class="text-2xl font-mono font-bold text-warning mt-2">{{ String(stats.surgeZones).padStart(2, '0') }}</p>
                    <p class="text-[10px] text-muted-foreground font-mono">ZONAS DE ALERTA</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="flex items-center gap-2 border-b border-primary/30 pb-2">
                <button @click="selectedBranchType = 'all'"
                        class="px-3 py-1 text-[10px] font-mono transition-all relative group/filter"
                        :class="selectedBranchType === 'all' ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-primary'">
                    TODOS ({{ stats.total }})
                    <span class="absolute bottom-0 left-0 w-0 h-[2px] bg-primary group-hover/filter:w-full transition-all"></span>
                </button>
                <button @click="selectedBranchType = 'active'"
                        class="px-3 py-1 text-[10px] font-mono transition-all relative group/filter"
                        :class="selectedBranchType === 'active' ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-primary'">
                    ACTIVOS ({{ stats.active }})
                </button>
                <button @click="selectedBranchType = 'inactive'"
                        class="px-3 py-1 text-[10px] font-mono transition-all relative group/filter"
                        :class="selectedBranchType === 'inactive' ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-primary'">
                    INACTIVOS ({{ stats.inactive }})
                </button>
                <div class="flex-1"></div>
                <span class="text-[8px] font-mono text-muted-foreground">
                    <Clock :size="10" class="inline" /> ÚLTIMA SINCRONIZACIÓN: {{ new Date().toLocaleTimeString() }}
                </span>
            </div>

            <!-- Mapa -->
            <div class="animate-in fade-in zoom-in duration-500">
                <div class="relative overflow-hidden border border-primary/30 shadow-[0_0_25px_hsl(var(--primary)/0.2)] group/map bg-background">
                    <div class="absolute top-0 left-0 z-[400] bg-background/90 border-b border-r border-primary/30 px-3 py-1.5 flex items-center gap-2 pointer-events-none backdrop-blur-sm">
                        <Radar :size="14" class="text-primary icon-glow animate-pulse"/>
                        <span class="text-[10px] text-primary font-mono font-bold uppercase tracking-widest">GEOFENCING // RADAR ACTIVO</span>
                    </div>
                    
                    <!-- Overlay de escaneo -->
                    <div class="absolute inset-0 pointer-events-none z-[300] opacity-30">
                        <div class="absolute top-0 left-0 w-full h-[2px] bg-primary/30 animate-scanline"></div>
                    </div>
                    
                    <MapComponent 
                        ref="mapRef"
                        :markers="filteredBranches" 
                        :center="mapCenter" 
                        :zoom="mapZoom"
                        height="320px" 
                        class="w-full h-full opacity-90 group-hover/map:opacity-100 transition-opacity"
                    />
                </div>
            </div>

            <!-- Grid de Nodos -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 font-mono">
                
                <!-- Skeletons -->
                <template v-if="isLoading">
                    <div v-for="n in 3" :key="`skel-${n}`" 
                          class="h-[420px] border border-primary/20 p-6 animate-pulse relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] animate-shimmer"></div>
                    </div>
                </template>

                <template v-else>
                    <div v-for="branch in filteredBranches" :key="branch.id" 
                         @mouseenter="hoveredBranch = branch.id"
                         @mouseleave="hoveredBranch = null"
                         class="group relative border transition-all duration-500 flex flex-col overflow-hidden"
                         :class="[
                             branch.is_default 
                                ? 'border-primary shadow-neon-primary z-10' 
                                : 'border-border/50 hover:border-primary/50 hover:shadow-neon-primary',
                             !branch.is_active && 'opacity-70'
                         ]">
                        
                        <!-- Scanline superior -->
                        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                        
                        <!-- Badges -->
                        <div class="absolute top-2 right-2 z-20 flex gap-1">
                            <div v-if="branch.is_default" 
                                 class="bg-primary text-primary-foreground text-[8px] font-mono px-2 py-0.5 flex items-center gap-1">
                                <GitBranch :size="10" />
                                MASTER NODE
                            </div>
                            <div v-if="branch.surge_multiplier > 1"
                                 class="bg-warning/20 text-warning border border-warning/30 text-[8px] font-mono px-2 py-0.5 flex items-center gap-1">
                                <Activity :size="10" />
                                SURGE x{{ branch.surge_multiplier }}
                            </div>
                        </div>

                        <!-- Header -->
                        <div class="p-5 border-b border-border/50 relative">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="text-lg font-black text-foreground uppercase tracking-tighter truncate w-3/4 group-hover:text-primary transition-colors">
                                        {{ branch.name }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <component :is="getNetworkStatus(branch).icon" 
                                                   :size="12" 
                                                   :class="getNetworkStatus(branch).class" />
                                        <span class="text-[8px] font-mono" :class="getNetworkStatus(branch).class">
                                            {{ getNetworkStatus(branch).label }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <Link :href="route('admin.branches.edit', branch.id)" 
                                          class="p-1.5 text-muted-foreground hover:text-primary hover:bg-primary/10 transition-all border border-transparent hover:border-primary/30 relative group/edit">
                                        <Edit :size="16" />
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 text-[8px] font-mono text-primary opacity-0 group-hover/edit:opacity-100 whitespace-nowrap">
                                            EDITAR
                                        </span>
                                    </Link>
                                </div>
                            </div>
                            <span class="text-[9px] font-mono text-primary flex items-center gap-1">
                                <MapPin :size="10" /> {{ branch.city }} // ID:{{ String(branch.id).slice(-4).toUpperCase() }}
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="p-5 flex-1 space-y-4 bg-gradient-to-b from-transparent to-background/50 text-[11px]">
                            <!-- Métricas principales -->
                            <div class="grid grid-cols-2 gap-4 border-b border-border/30 pb-4">
                                <div class="space-y-1">
                                    <span class="text-muted-foreground block text-[8px] font-mono uppercase tracking-wider flex items-center gap-1">
                                        <DollarSign :size="10" /> BASE DELIVERY
                                    </span>
                                    <span class="text-foreground font-mono font-bold text-sm">{{ fmt(branch.delivery_base_fee) }}</span>
                                </div>
                                <div class="space-y-1 text-right">
                                    <span class="text-muted-foreground block text-[8px] font-mono uppercase tracking-wider flex items-center justify-end gap-1">
                                        <Activity :size="10" /> SURGE
                                    </span>
                                    <span class="font-mono font-bold text-sm"
                                          :class="branch.surge_multiplier > 1 ? 'text-warning' : 'text-foreground'">
                                        x{{ branch.surge_multiplier }}
                                    </span>
                                </div>
                            </div>

                            <!-- Métricas secundarias -->
                            <div class="grid grid-cols-2 gap-4 border-b border-border/30 pb-4">
                                <div class="space-y-1">
                                    <span class="text-muted-foreground block text-[8px] font-mono uppercase tracking-wider flex items-center gap-1">
                                        <HardDrive :size="10" /> PEDIDO MÍN
                                    </span>
                                    <span class="text-foreground font-mono font-bold">{{ fmt(branch.min_order_amount) }}</span>
                                </div>
                                <div class="space-y-1 text-right">
                                    <span class="text-muted-foreground block text-[8px] font-mono uppercase tracking-wider flex items-center justify-end gap-1">
                                        <Cpu :size="10" /> SERVICE FEE
                                    </span>
                                    <span class="text-foreground font-mono font-bold">{{ branch.base_service_fee_percentage }}%</span>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="flex items-start gap-2 text-muted-foreground group/address">
                                <MapPin :size="14" class="shrink-0 mt-0.5 group-hover/address:text-primary transition-colors" />
                                <span class="font-mono text-[9px] leading-tight line-clamp-2 uppercase tracking-wide">
                                    {{ branch.address }}
                                </span>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="grid grid-cols-2 border-t border-border/50 divide-x divide-border/50">
                            <button @click="focusOnMap(branch)" 
                                    class="flex items-center justify-center gap-2 py-3 text-[9px] font-mono uppercase tracking-widest text-muted-foreground hover:text-primary transition-all group/radar relative overflow-hidden">
                                <Target :size="14" class="group-hover/radar:scale-125 transition-transform" />
                                RADAR
                                <span class="absolute inset-0 bg-primary/5 translate-y-full group-hover/radar:translate-y-0 transition-transform duration-300"></span>
                            </button>
                            <button @click="copyId(branch.id)"
                                    class="flex items-center justify-center gap-2 py-3 text-[9px] font-mono uppercase tracking-widest text-muted-foreground hover:text-primary transition-all group/copy relative overflow-hidden">
                                <Copy :size="14" class="group-hover/copy:scale-125 transition-transform" />
                                ID_HEX
                                <span class="absolute inset-0 bg-primary/5 translate-y-full group-hover/copy:translate-y-0 transition-transform duration-300"></span>
                            </button>
                        </div>

                        <!-- Hover overlay -->
                        <div v-if="hoveredBranch === branch.id" 
                             class="absolute inset-0 border-2 border-primary/30 pointer-events-none animate-pulse"></div>
                    </div>
                </template>
            </div>

            <!-- Mensaje sin resultados -->
            <div v-if="!isLoading && filteredBranches.length === 0" 
                 class="border border-dashed border-primary/30 p-12 text-center relative">
                <Radar :size="40" class="mx-auto text-muted-foreground mb-4" />
                <p class="text-sm font-mono text-foreground">// SIN NODOS EN ESTA CATEGORÍA</p>
                <p class="text-[10px] font-mono text-muted-foreground mt-2">
                    {{ selectedBranchType === 'active' ? 'NO HAY NODOS ACTIVOS' : 'NO HAY NODOS INACTIVOS' }}
                </p>
                <button @click="selectedBranchType = 'all'" 
                        class="mt-4 text-[10px] font-mono text-primary hover:text-primary/80 transition-colors">
                    VER TODOS <ArrowRight :size="12" class="inline" />
                </button>
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

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.animate-shimmer {
    animation: shimmer 2s infinite;
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

/* Efecto de brillo para íconos */
.icon-glow {
    filter: drop-shadow(0 0 4px currentColor);
    transition: filter 0.3s ease;
}

.icon-glow:hover {
    filter: drop-shadow(0 0 8px currentColor);
}

/* Colores personalizados */
.text-warning { color: #ffaa00; }
.bg-warning { background-color: #ffaa00; }
.border-warning { border-color: #ffaa00; }
</style>