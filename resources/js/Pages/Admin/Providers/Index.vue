<script setup>
import { ref, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Plus, Search, Phone, Mail, MapPin, 
    Truck, Edit, Trash2, Building2, 
    User, Globe, Cpu, Terminal, Radar,
    AlertTriangle, Wifi, WifiOff, Shield,
    Package, Clock, Zap, Target
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({ 
    providers: Object,
    filters: Object,
    can_manage: Boolean 
});

const search = ref(props.filters.search || '');
const hoveredProvider = ref(null);
const viewMode = ref('grid'); // 'grid' o 'list'

watch(search, debounce((val) => {
    router.get(route('admin.providers.index'), { search: val }, { 
        preserveState: true, replace: true, preserveScroll: true
    });
}, 300));

const handleDelete = (provider) => {
    const name = provider.name || provider.company_name || 'este proveedor';
    if (confirm(`¿CONFIRMAR ELIMINACIÓN // "${name}"?`)) {
        router.delete(route('admin.providers.destroy', provider.id));
    }
};

// MODIFICAR: Mejorar lógica de iniciales para ser más robusta
const getInitials = (provider) => {
    const name = provider.commercial_name || provider.company_name || 'P';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

// AÑADIR: Limpieza rápida de búsqueda
const clearSearch = () => {
    search.value = '';
};

// Estadísticas mejoradas
const stats = computed(() => {
    const total = props.providers.total || 0;
    const currentView = props.providers.data.length;
    const activeProviders = props.providers.data?.filter(p => p.is_active).length || 0;
    
    return [
        { 
            label: 'TOTAL_SOCIOS', 
            value: String(total).padStart(2, '0'), 
            icon: Building2, 
            color: 'text-primary', 
            bg: 'bg-primary/10',
            border: 'border-primary/30'
        },
        { 
            label: 'ACTIVOS', 
            value: String(activeProviders).padStart(2, '0'), 
            icon: Wifi, 
            color: 'text-cyan-500', 
            bg: 'bg-cyan-500/10',
            border: 'border-cyan-500/30'
        },
        { 
            label: 'EN_PANTALLA', 
            value: String(currentView).padStart(2, '0'), 
            icon: Radar, 
            color: 'text-emerald-500', 
            bg: 'bg-emerald-500/10',
            border: 'border-emerald-500/30'
        },
        { 
            label: 'ALCANCE', 
            value: 'GLOBAL', 
            icon: Globe, 
            color: 'text-indigo-500', 
            bg: 'bg-indigo-500/10',
            border: 'border-indigo-500/30',
            isText: true 
        },
    ];
});

// Código de proveedor
const getProviderCode = (id) => {
    return `PRV_${String(id).padStart(4, '0')}`;
};

// Estado del proveedor
const getProviderStatus = (provider) => {
    if (provider.is_active === false) return { icon: WifiOff, class: 'text-destructive', label: 'OFFLINE' };
    return { icon: Wifi, class: 'text-cyan-500', label: 'ONLINE' };
};
</script>

<template>
    <AdminLayout>
        <Head title="Proveedores" />

        <div class="px-4 md:px-6 lg:px-8 py-6 min-h-screen flex flex-col">
            
            <!-- Header -->
            <div class="mb-8 border-b border-primary/30 pb-6 relative group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10 flex flex-col gap-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                            data-text="PROVEEDORES">
                            PROVEEDORES
                        </h1>
                        <span class="text-xs font-mono font-bold text-primary border border-primary/30 px-2 py-1">
                            {{ String(providers.total).padStart(2, '0') }}
                        </span>
                    </div>
                    <p class="text-[10px] font-mono text-muted-foreground flex items-center gap-2">
                        <Cpu :size="12" class="text-primary animate-pulse" />
                        GESTIÓN ESTRATÉGICA DE LA CADENA DE SUMINISTRO
                        <Terminal :size="12" class="text-primary animate-pulse" />
                    </p>
                </div>
            </div>

            <!-- Stats Dashboard -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div v-for="(stat, index) in stats" :key="index" 
                     class="border border-border/50 p-4 relative group/stat">
                    <div class="flex items-center justify-between">
                        <component :is="stat.icon" :size="20" :class="stat.color" />
                        <span class="text-[8px] font-mono text-primary/50">{{ stat.label }}</span>
                    </div>
                    <p class="text-2xl font-mono font-bold text-foreground mt-2" :class="stat.isText ? 'text-sm mt-3' : ''">
                        {{ stat.value }}
                    </p>
                    <p class="text-[10px] text-muted-foreground font-mono">{{ stat.label }}</p>
                    
                    <!-- Esquinas -->
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="sticky top-2 z-30 group/search mb-6">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <Search class="h-4 w-4 text-muted-foreground group-focus-within/search:text-primary transition-colors" />
                </div>
                <input 
                    v-model="search"
                    type="text" 
                    class="block w-full pl-11 pr-12 py-3.5 bg-background/80 backdrop-blur-md border border-border/50 font-mono text-sm text-foreground focus:border-primary focus:shadow-neon-primary outline-none transition-all" 
                    placeholder="> BUSCAR_PROVEEDOR..." 
                />
                <button v-if="search" @click="clearSearch" 
                        class="absolute right-10 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                    <WifiOff :size="14" />
                </button>
                <div class="absolute right-4 top-1/2 -translate-y-1/2 w-1 h-4 bg-primary animate-pulse"></div>
            </div>
            <!-- View Toggle (opcional) -->
            <div class="flex justify-end mb-4">
                <div class="flex gap-1 border border-border/50 p-1">
                    <button @click="viewMode = 'grid'"
                            class="px-3 py-1 text-[10px] font-mono transition-all"
                            :class="viewMode === 'grid' ? 'bg-primary/10 text-primary border-b border-primary' : 'text-muted-foreground'">
                        GRID
                    </button>
                    <button @click="viewMode = 'list'"
                            class="px-3 py-1 text-[10px] font-mono transition-all"
                            :class="viewMode === 'list' ? 'bg-primary/10 text-primary border-b border-primary' : 'text-muted-foreground'">
                        LISTA
                    </button>
                </div>
            </
            div>

            <!-- Grid de Proveedores -->
            <div class="flex-1">
                <div v-if="providers.data.length > 0" 
                     class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    
                    <div v-for="provider in providers.data" :key="provider.id" 
                         @mouseenter="hoveredProvider = provider.id"
                         @mouseleave="hoveredProvider = null"
                         class="border border-border/50 hover:border-primary/50 hover:shadow-neon-primary transition-all duration-500 relative overflow-hidden group/card">
                        
                        <!-- Scanline superior -->
                        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                        
                        <div class="p-5">
                            <!-- Header con iniciales y acciones -->
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 border-2 border-primary/30 bg-primary/5 flex items-center justify-center text-primary font-mono font-black text-lg relative overflow-hidden group/avatar">
                                        <span class="relative z-10">{{ getInitials(provider.name || provider.company_name) }}</span>
                                        <!-- Efecto de escaneo en avatar -->
                                        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-primary/20 to-transparent translate-y-[-100%] group-hover/avatar:translate-y-[100%] transition-transform duration-700"></div>
                                    </div>
                                    <div>
                                        <h3 class="font-mono font-bold text-foreground text-base group-hover/card:text-primary transition-colors">
                                            {{ provider.name || provider.company_name }}
                                        </h3>
                                        <div class="flex items-center gap-2 mt-1">
                                            <component :is="getProviderStatus(provider).icon" 
                                                       :size="10" 
                                                       :class="getProviderStatus(provider).class" />
                                            <span class="text-[8px] font-mono" :class="getProviderStatus(provider).class">
                                                {{ getProviderStatus(provider).label }}
                                            </span>
                                            <span class="text-[8px] font-mono text-primary/50">
                                                {{ getProviderCode(provider.id) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="can_manage" class="flex items-center gap-1">
                                    <Link :href="route('admin.providers.edit', provider.id)" 
                                          class="p-1.5 text-muted-foreground hover:text-primary hover:bg-primary/10 transition-all border border-transparent hover:border-primary/30 relative group/edit">
                                        <Edit :size="14" />
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 text-[8px] font-mono text-primary opacity-0 group-hover/edit:opacity-100 whitespace-nowrap">
                                            EDITAR
                                        </span>
                                    </Link>
                                    <button @click="handleDelete(provider)" 
                                            class="p-1.5 text-muted-foreground hover:text-destructive hover:bg-destructive/10 transition-all border border-transparent hover:border-destructive/30 relative group/delete">
                                        <Trash2 :size="14" />
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 text-[8px] font-mono text-destructive opacity-0 group-hover/delete:opacity-100 whitespace-nowrap">
                                            ELIMINAR
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- Información de contacto -->
                            <div class="space-y-2.5 mb-4">
                                <div v-if="provider.contact_name" class="flex items-center gap-2.5 text-[11px] font-mono text-muted-foreground">
                                    <div class="p-1 border border-border/30 bg-background">
                                        <User :size="10" class="text-primary" />
                                    </div>
                                    <span class="truncate">{{ provider.contact_name }}</span>
                                </div>
                                
                                <div v-if="provider.email" class="flex items-center gap-2.5 text-[11px] font-mono text-muted-foreground">
                                    <div class="p-1 border border-border/30 bg-background">
                                        <Mail :size="10" class="text-primary" />
                                    </div>
                                    <span class="truncate">{{ provider.email }}</span>
                                </div>
                                
                                <div v-if="provider.address" class="flex items-center gap-2.5 text-[11px] font-mono text-muted-foreground">
                                    <div class="p-1 border border-border/30 bg-background">
                                        <MapPin :size="10" class="text-primary" />
                                    </div>
                                    <span class="truncate">{{ provider.address }}</span>
                                </div>
                            </div>

                            <!-- Métricas adicionales (si existen) -->
                            <div v-if="provider.products_count || provider.avg_lead_time" 
                                 class="grid grid-cols-2 gap-2 border-t border-border/30 pt-3 mb-2">
                                <div v-if="provider.products_count" class="flex items-center gap-1 text-[9px] font-mono">
                                    <Package :size="10" class="text-primary" />
                                    <span>{{ provider.products_count }} PRODUCTOS</span>
                                </div>
                                <div v-if="provider.avg_lead_time" class="flex items-center gap-1 text-[9px] font-mono">
                                    <Clock :size="10" class="text-primary" />
                                    <span>{{ provider.avg_lead_time }} DÍAS</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="grid grid-cols-2 border-t border-border/50 divide-x divide-border/50">
                            <a v-if="provider.phone" :href="`tel:${provider.phone}`" 
                               class="flex items-center justify-center gap-2 py-3 text-[9px] font-mono uppercase tracking-widest text-muted-foreground hover:text-primary hover:bg-primary/5 transition-all group/phone relative overflow-hidden">
                                <Phone :size="12" class="group-hover/phone:scale-110 transition-transform" />
                                LLAMAR
                                <span class="absolute inset-0 bg-primary/5 translate-y-full group-hover/phone:translate-y-0 transition-transform duration-300"></span>
                            </a>
                            <span v-else class="flex items-center justify-center gap-2 py-3 text-[9px] font-mono text-muted-foreground/30 cursor-not-allowed">
                                <Phone :size="12" /> N/A
                            </span>

                            <Link :href="route('admin.providers.edit', provider.id)" 
                                  class="flex items-center justify-center gap-2 py-3 text-[9px] font-mono uppercase tracking-widest text-muted-foreground hover:text-primary hover:bg-primary/5 transition-all group/details relative overflow-hidden">
                                <Truck :size="12" class="group-hover/details:scale-110 transition-transform" />
                                DETALLES
                                <span class="absolute inset-0 bg-primary/5 translate-y-full group-hover/details:translate-y-0 transition-transform duration-300"></span>
                            </Link>
                        </div>

                        <!-- Hover overlay -->
                        <div v-if="hoveredProvider === provider.id" 
                             class="absolute inset-0 border-2 border-primary/30 pointer-events-none"></div>
                    </div>
                </div>

                <!-- Estado vacío -->
                <div v-else class="border border-dashed border-primary/30 p-12 text-center relative">
                    <div class="w-24 h-24 border-2 border-dashed border-primary/30 mx-auto mb-6 flex items-center justify-center">
                        <Search :size="40" class="text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-mono font-bold text-foreground glitch-text" data-text="SIN_RESULTADOS">SIN_RESULTADOS</h3>
                    <p class="text-xs font-mono text-muted-foreground mt-2 max-w-xs mx-auto">
                        NO SE ENCONTRARON PROVEEDORES EN LA BASE DE DATOS.
                    </p>
                    <button v-if="search" @click="search = ''" 
                            class="mt-6 text-[10px] font-mono text-primary hover:text-primary/80 transition-colors">
                        LIMPIAR BÚSQUEDA
                    </button>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="providers.links && providers.links.length > 3" 
                 class="mt-8 flex justify-center pb-4">
                <div class="flex gap-1.5 overflow-x-auto max-w-full pb-2 no-scrollbar">
                    <template v-for="(link, k) in providers.links" :key="k">
                        <Link v-if="link.url"
                              :href="link.url"
                              v-html="link.label"
                              class="min-w-[40px] h-10 flex items-center justify-center text-xs font-mono font-bold border transition-all relative group/page"
                              :class="{
                                  'border-primary bg-primary/10 text-primary shadow-neon-primary': link.active,
                                  'border-border/50 text-muted-foreground hover:border-primary/50 hover:text-primary': !link.active
                              }"
                              :preserve-scroll="true" />
                        <span v-else
                              v-html="link.label"
                              class="min-w-[40px] h-10 flex items-center justify-center text-xs text-muted-foreground/30 border border-transparent">
                        </span>
                    </template>
                </div>
            </div>

            <!-- Botón flotante de creación -->
            <Teleport to="body">
                <Link v-if="can_manage" 
                      :href="route('admin.providers.create')" 
                      class="fixed bottom-24 right-4 md:right-8 z-[9999] group/create">
                    <div class="w-14 h-14 bg-primary text-primary-foreground border border-primary-foreground/50 shadow-neon-primary flex items-center justify-center relative overflow-hidden">
                        <Plus :size="28" class="group-hover/create:rotate-90 transition-transform duration-500 relative z-10" />
                        <!-- Efecto scan -->
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/create:translate-y-0 transition-transform duration-500"></span>
                        <!-- Esquinas -->
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                    </div>
                    <span class="sr-only">CREAR PROVEEDOR</span>
                </Link>
            </Teleport>

            <!-- Session ID -->
            <div class="mt-8 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // PROVIDERS_INDEX // {{ new Date().toISOString().slice(0,10) }}
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
    40% { clip-path: inset(10% 0 60% 0); }
    60% { clip-path: inset(80% 0 5% 0); }
    80% { clip-path: inset(40% 0 30% 0); }
    100% { clip-path: inset(30% 0 40% 0); }
}

/* Sombras neón */
.shadow-neon-primary {
    box-shadow: 0 0 20px hsl(var(--primary) / 0.3);
}

/* Ocultar scrollbar */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>