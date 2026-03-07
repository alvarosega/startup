<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Plus, Image as ImageIcon, Factory, 
    Tag, Edit, Trash2, LayoutGrid, CheckCircle2, 
    Zap, Terminal, Cpu, Filter, XCircle, MapPin, 
    Wifi, WifiOff, Star
} from 'lucide-vue-next';

const props = defineProps({ 
    brands: Object,    // Resource Collection: { data: [], links: [], meta: {} }
    filters: Object,   // Filters from Controller
    options: Object,   // { providers: [], categories: [], zones: [] }
    stats: Object,     // Global stats from Action
    can_manage: Boolean
});

// --- ESTADO DE FILTROS ---
const filters = ref({
    search: props.filters?.search || '',
    provider_id: props.filters?.provider_id || '',
    category_id: props.filters?.category_id || '',
    market_zone_id: props.filters?.market_zone_id || '',
});

// --- DATA UNWRAPPING BLINDADO ---
const brandsList = computed(() => props.brands?.data || []);

// --- SINCRONIZACIÓN DE RENDIMIENTO ---
watch(filters, debounce((val) => {
    // Limpiamos nulos y vacíos para no ensuciar la URL
    const cleanFilters = Object.fromEntries(Object.entries(val).filter(([_, v]) => v !== '' && v !== null));
    
    router.get(route('admin.brands.index'), cleanFilters, { 
        preserveState: true, 
        replace: true, 
        preserveScroll: true 
    });
}, 400), { deep: true });

// --- DASHBOARD KPIS ---
const displayStats = computed(() => [
    { label: 'SISTEMA_TOTAL', value: props.stats?.total || 0, icon: Tag, color: 'text-primary', bg: 'bg-primary/10' },
    { label: 'NODOS_ACTIVOS', value: props.stats?.active || 0, icon: Zap, color: 'text-cyan-500', bg: 'bg-cyan-500/10' },
    { label: 'DESTACADOS', value: props.stats?.featured || 0, icon: Star, color: 'text-emerald-500', bg: 'bg-emerald-500/10' },
]);

// --- ACCIONES ---
const resetFilters = () => {
    filters.value = { search: '', provider_id: '', category_id: '', market_zone_id: '' };
};

const deleteItem = (brand) => {
    if(confirm(`¿CONFIRMAR ELIMINACIÓN // ACTIVO: ${brand.name.toUpperCase()}? // ESTA ACCIÓN ES IRREVERSIBLE.`)) {
        router.delete(route('admin.brands.destroy', brand.id), { preserveScroll: true });
    }
};

const formatValue = (val) => String(val).padStart(2, '0');
</script>

<template>
    <AdminLayout>
        <Head title="Control Maestro de Marcas" />

        <div class="max-w-7xl mx-auto pb-24 px-4 sm:px-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 border-b border-primary/30 pb-6 relative group/header">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-black text-primary uppercase tracking-tighter glitch-text" data-text="MAESTRO_DE_MARCAS">
                            MAESTRO_DE_MARCAS
                        </h1>
                        <span class="text-[10px] font-mono font-bold bg-primary/10 text-primary border border-primary/30 px-2 py-1 shadow-neon-primary">
                            INDEX_SYNC: OK
                        </span>
                    </div>
                    <p class="text-muted-foreground text-[10px] mt-1 font-mono uppercase flex items-center gap-2">
                        <Cpu :size="12" class="text-primary animate-pulse" />
                        ESTRUCTURA LINEAL DE IDENTIDADES COMERCIALES
                        <Terminal :size="12" class="text-primary animate-pulse" />
                    </p>
                </div>
                
                <Link v-if="can_manage" :href="route('admin.brands.create')" 
                      class="h-11 px-8 bg-primary text-background font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-primary/90 transition-all shadow-neon-primary relative overflow-hidden group/btn">
                    <Plus :size="18" stroke-width="3" class="relative z-10" />
                    <span class="relative z-10">REGISTRAR_MARCA</span>
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-300"></div>
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8 bg-primary/5 p-4 border border-primary/20 backdrop-blur-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
                
                <div class="relative group md:col-span-1">
                    <Search :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-primary/40 group-focus-within:text-primary transition-colors" />
                    <input v-model="filters.search" type="text" placeholder="[ BUSCAR_STRING ]" 
                           class="pl-9 w-full bg-background border border-primary/20 text-[10px] font-mono h-10 focus:border-primary outline-none text-foreground uppercase">
                </div>

                <select v-model="filters.provider_id" 
                        class="bg-background border border-primary/20 text-[10px] font-mono h-10 px-3 focus:border-primary outline-none text-primary uppercase cursor-pointer appearance-none custom-select">
                    <option value="">-- FILTRAR_PROVEEDOR --</option>
                    <option v-for="p in options.providers" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>

                <select v-model="filters.category_id" 
                        class="bg-background border border-primary/20 text-[10px] font-mono h-10 px-3 focus:border-primary outline-none text-primary uppercase cursor-pointer appearance-none custom-select">
                    <option value="">-- FILTRAR_CATEGORÍA --</option>
                    <option v-for="c in options.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>

                <select v-model="filters.market_zone_id" 
                        class="bg-background border border-primary/20 text-[10px] font-mono h-10 px-3 focus:border-primary outline-none text-primary uppercase cursor-pointer appearance-none custom-select">
                    <option value="">-- FILTRAR_ZONA --</option>
                    <option v-for="z in options.zones" :key="z.id" :value="z.id">{{ z.name }}</option>
                </select>

                <button @click="resetFilters" 
                        class="flex items-center justify-center gap-2 border border-destructive/50 text-destructive text-[9px] font-mono font-black hover:bg-destructive hover:text-white transition-all uppercase group/clear">
                    <XCircle :size="14" class="group-hover:rotate-90 transition-transform" /> REINICIAR_SISTEMA
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div v-for="(stat, index) in displayStats" :key="index" 
                     class="border border-primary/20 p-5 bg-background/50 relative overflow-hidden group/stat hover:border-primary/50 transition-all">
                    <div class="flex items-center gap-5">
                        <div :class="`p-4 ${stat.bg} ${stat.color} relative`">
                            <component :is="stat.icon" :size="24" />
                            <div class="absolute inset-0 bg-current opacity-10 animate-pulse"></div>
                        </div>
                        <div>
                            <p class="text-[9px] font-mono font-black uppercase text-muted-foreground tracking-widest">{{ stat.label }}</p>
                            <p class="text-3xl font-mono font-black text-foreground mt-1">{{ formatValue(stat.value) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div v-for="brand in brandsList" :key="brand.id" 
                     class="border border-primary/10 bg-background/40 group/brand hover:border-primary/40 hover:shadow-neon-primary transition-all duration-300 relative overflow-hidden">
                    
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary transform -translate-x-full group-hover/brand:translate-x-0 transition-transform"></div>

                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-6 flex-1 min-w-0">
                            <div class="w-14 h-14 border border-primary/20 bg-black flex items-center justify-center overflow-hidden shrink-0 relative group/img">
                                <img v-if="brand.image_url" :src="brand.image_url" class="w-full h-full object-contain p-1 transition-transform group-hover/img:scale-110" />
                                <ImageIcon v-else :size="20" class="text-primary/10" />
                                
                                <div v-if="!brand.is_active" class="absolute inset-0 bg-background/90 flex items-center justify-center">
                                    <WifiOff :size="16" class="text-destructive animate-pulse" />
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-3">
                                    <h3 class="font-black text-foreground text-base uppercase tracking-tighter group-hover/brand:text-primary transition-colors truncate">
                                        {{ brand.name }}
                                    </h3>
                                    <Star v-if="brand.is_featured" :size="14" class="text-warning fill-warning drop-shadow-[0_0_8px_rgba(234,179,8,0.5)]" />
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-4 mt-2">
                                    <span class="text-[9px] font-mono text-primary/70 flex items-center gap-1.5 uppercase">
                                        <Factory :size="12" /> {{ brand.provider_name }}
                                    </span>
                                    <span class="text-[9px] font-mono text-muted-foreground flex items-center gap-1.5 uppercase border-l border-primary/10 pl-4">
                                        <LayoutGrid :size="12" /> {{ brand.category_name }}
                                    </span>
                                    <span class="text-[9px] font-mono text-cyan-500 flex items-center gap-1.5 uppercase border-l border-primary/10 pl-4">
                                        <MapPin :size="12" /> {{ brand.market_zone }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="hidden sm:flex items-center gap-3 px-4 py-2 border border-primary/10 bg-primary/5">
                                <div :class="brand.is_active ? 'bg-primary shadow-[0_0_8px_#4ade80]' : 'bg-destructive shadow-[0_0_8px_#ef4444]'" 
                                     class="w-2 h-2 rounded-full animate-pulse"></div>
                                <span class="text-[10px] font-mono font-black tracking-widest" :class="brand.is_active ? 'text-primary' : 'text-destructive'">
                                    {{ brand.is_active ? 'SYSTEM_ONLINE' : 'NODE_OFFLINE' }}
                                </span>
                            </div>
                            
                            <div class="flex gap-2" v-if="can_manage">
                                <Link :href="route('admin.brands.edit', brand.id)" 
                                      class="p-2.5 text-muted-foreground hover:text-primary hover:bg-primary/10 transition-all border border-transparent hover:border-primary/20">
                                    <Edit :size="16" />
                                </Link>
                                <button @click="deleteItem(brand)" 
                                        class="p-2.5 text-muted-foreground hover:text-destructive hover:bg-destructive/10 transition-all border border-transparent hover:border-destructive/20">
                                    <Trash2 :size="16" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="brandsList.length === 0" class="py-20 text-center border border-dashed border-primary/20 bg-primary/5">
                    <p class="text-xs font-mono text-primary/40 uppercase tracking-[0.5em] animate-pulse">
                        [ NO_OBJECTS_FOUND_IN_THIS_SECTOR ]
                    </p>
                </div>
            </div>

            <div v-if="props.brands?.links && props.brands.links.length > 3" class="mt-12 flex justify-center">
                <nav class="flex gap-2">
                    <template v-for="(link, k) in props.brands.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" 
                              class="min-w-[45px] h-11 flex items-center justify-center text-[10px] font-mono font-black transition-all border"
                              :class="link.active ? 'bg-primary border-primary text-background shadow-neon-primary' : 'bg-background border-primary/20 text-primary/60 hover:border-primary hover:text-primary'"
                              :preserve-scroll="true" />
                        <span v-else v-html="link.label" 
                              class="min-w-[45px] h-11 flex items-center justify-center text-[10px] font-mono border border-transparent text-muted-foreground/20"></span>
                    </template>
                </nav>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }

.glitch-text { position: relative; }
.glitch-text::before, .glitch-text::after {
    content: attr(data-text);
    position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.8;
}
.glitch-text::before { color: #0ff; z-index: -1; animation: glitch-anim-1 0.4s infinite linear alternate-reverse; }
.glitch-text::after { color: #f0f; z-index: -2; animation: glitch-anim-2 0.4s infinite linear alternate-reverse; }

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); transform: translate(-2px); }
    100% { clip-path: inset(40% 0 20% 0); transform: translate(2px); }
}
@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); transform: translate(2px); }
    100% { clip-path: inset(30% 0 40% 0); transform: translate(-2px); }
}

/* Optimización de hardware */
.glitch-text, .glitch-text::before, .glitch-text::after, .group\/brand {
    will-change: transform, opacity, clip-path;
    backface-visibility: hidden;
}

.custom-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%234ade80' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}
</style>