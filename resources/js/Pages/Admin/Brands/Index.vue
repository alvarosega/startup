<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Admin/Pagination.vue'; // <-- IMPORTACIÓN AÑADIDA
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Plus, Image as ImageIcon, Factory, 
    Tag, Edit, Trash2, LayoutGrid, CheckCircle2, 
    Zap, Filter, XCircle, MapPin, 
    Wifi, WifiOff, Star, Cpu, Terminal
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

// --- DATA UNWRAPPING ---
const brandsList = computed(() => props.brands?.data || []);

// --- SINCRONIZACIÓN DE FILTROS ---
watch(filters, debounce((val) => {
    // Si queremos limpiar la URL, enviamos todos los filtros, Inertia/Laravel ignora los vacíos.
    router.get(route('admin.brands.index'), val, { 
        preserveState: true, 
        replace: true, 
        preserveScroll: true 
    });
}, 400), { deep: true });

// --- KPIS ---
const displayStats = computed(() => [
    { label: 'Total marcas', value: props.stats?.total || 0, icon: Tag },
    { label: 'Activas', value: props.stats?.active || 0, icon: Wifi },
    { label: 'Destacadas', value: props.stats?.featured || 0, icon: Star },
]);

// --- ACCIONES ---
const resetFilters = () => {
    filters.value = { search: '', provider_id: '', category_id: '', market_zone_id: '' };
};

const deleteItem = (brand) => {
    if(confirm(`¿Eliminar la marca "${brand.name}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.brands.destroy', brand.id), { preserveScroll: true });
    }
};

const clearSearch = () => {
    filters.value.search = '';
};
</script>

<template>
    <AdminLayout>
        <Head title="Marcas" />

        <div class="max-w-7xl mx-auto pb-24 px-4 sm:px-6">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-foreground tracking-tight">
                        Marcas
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Gestión de identidades comerciales
                    </p>
                </div>
                
                <Link v-if="can_manage" :href="route('admin.brands.create')" 
                      class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary/90 transition-all inline-flex items-center gap-2">
                    <Plus :size="18" />
                    <span>Nueva marca</span>
                </Link>
            </div>

            <!-- Filtros -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8 bg-card p-4 border border-border rounded-lg">
                <div class="relative md:col-span-1">
                    <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="filters.search" type="text" placeholder="Buscar marca..." 
                           class="w-full pl-9 pr-3 py-2 bg-background border border-border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    <button v-if="filters.search" @click="clearSearch" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                        ✕
                    </button>
                </div>

                <select v-model="filters.provider_id" 
                        class="bg-background border border-border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    <option value="">Todos los proveedores</option>
                    <option v-for="p in options.providers" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>

                <select v-model="filters.category_id" 
                        class="bg-background border border-border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    <option value="">Todas las categorías</option>
                    <option v-for="c in options.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>

                <select v-model="filters.market_zone_id" 
                        class="bg-background border border-border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    <option value="">Todas las zonas</option>
                    <option v-for="z in options.zones" :key="z.id" :value="z.id">{{ z.name }}</option>
                </select>

                <button @click="resetFilters" 
                        class="flex items-center justify-center gap-2 border border-destructive/50 text-destructive px-3 py-2 rounded-md text-sm font-medium hover:bg-destructive hover:text-destructive-foreground transition-all">
                    <XCircle :size="16" />
                    Limpiar filtros
                </button>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div v-for="(stat, index) in displayStats" :key="index" 
                     class="bg-card border border-border rounded-xl p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-primary/10 rounded-lg">
                            <component :is="stat.icon" :size="20" class="text-primary" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ stat.label }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ stat.value }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de marcas -->
            <div class="space-y-3">
                <div v-for="brand in brandsList" :key="brand.id" 
                     class="bg-card border border-border rounded-lg overflow-hidden hover:shadow-md transition-all duration-200">
                    
                    <div class="p-4 flex flex-col md:flex-row md:items-center gap-4">
                        <!-- Imagen y datos principales -->
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="w-12 h-12 bg-muted/20 rounded-lg flex items-center justify-center overflow-hidden border border-border">
                                <img v-if="brand.image_url" :src="brand.image_url" class="w-full h-full object-contain p-1" />
                                <ImageIcon v-else :size="20" class="text-muted-foreground/30" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-foreground truncate">{{ brand.name }}</h3>
                                    <Star v-if="brand.is_featured" :size="14" class="text-warning fill-warning" />
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-3 mt-1 text-xs text-muted-foreground">
                                    <span class="flex items-center gap-1">
                                        <Factory :size="12" class="text-primary/60" />
                                        {{ brand.provider_name }} </span>
                                    <span class="flex items-center gap-1">
                                        <LayoutGrid :size="12" class="text-primary/60" />
                                        {{ brand.category_name }} </span>
                                    <span class="flex items-center gap-1">
                                        <MapPin :size="12" class="text-primary/60" />
                                        {{ brand.market_zone }} </span>
                                </div>
                            </div>
                        </div>

                        <!-- Estado y acciones -->
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <div :class="brand.is_active ? 'bg-success' : 'bg-destructive'" class="w-2 h-2 rounded-full"></div>
                                <span class="text-xs" :class="brand.is_active ? 'text-success' : 'text-destructive'">
                                    {{ brand.is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </div>
                            
                            <div class="flex gap-1" v-if="can_manage">
                                <Link :href="route('admin.brands.edit', brand.id)" 
                                      class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/5 rounded-md transition-colors"
                                      title="Editar">
                                    <Edit :size="16" />
                                </Link>
                                <button @click="deleteItem(brand)" 
                                        class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/5 rounded-md transition-colors"
                                        title="Eliminar">
                                    <Trash2 :size="16" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado vacío -->
                <div v-if="brandsList.length === 0" class="py-20 text-center bg-card border border-dashed border-border rounded-lg">
                    <div class="w-16 h-16 bg-muted/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Tag :size="24" class="text-muted-foreground" />
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ filters.search || filters.provider_id || filters.category_id || filters.market_zone_id ? 'No se encontraron marcas con esos filtros.' : 'No hay marcas registradas.' }}
                    </p>
                </div>
            </div>
            <Pagination 
                v-if="props.brands?.meta && props.brands.meta.last_page > 1" 
                :meta="props.brands.meta" 
                class="mt-8"
            />
            <div class="mt-12 text-center opacity-30">
                <p class="text-[7px] font-mono text-muted-foreground uppercase tracking-[0.4em]">
                    BRD_IDX_NODE // v2.0 // TS: {{ new Date().toISOString() }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }

.glitch-text { position: relative; animation: glitch-skew 4s infinite linear alternate-reverse; }
.glitch-text::before, .glitch-text::after { content: attr(data-text); position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.8; }
.glitch-text::before { color: #0ff; z-index: -1; animation: glitch-anim-1 0.4s infinite linear alternate-reverse; }
.glitch-text::after { color: #f0f; z-index: -2; animation: glitch-anim-2 0.4s infinite linear alternate-reverse; }

@keyframes glitch-skew { 0%, 20%, 22%, 80%, 82%, 100% { transform: skew(0deg); } 21% { transform: skew(2deg); } 81% { transform: skew(-2deg); } }
@keyframes glitch-anim-1 { 0% { clip-path: inset(20% 0 30% 0); } 100% { clip-path: inset(40% 0 20% 0); } }
@keyframes glitch-anim-2 { 0% { clip-path: inset(60% 0 10% 0); } 100% { clip-path: inset(30% 0 40% 0); } }
</style>