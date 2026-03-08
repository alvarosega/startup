<script setup>
import { ref, watch, computed } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, Plus, ChevronDown, ChevronRight, Edit3, Trash2, 
    AlertTriangle, CheckCircle2, Filter, PackageSearch, Barcode,
    Layers, Tag, Wifi, WifiOff, Hash, Package, Box, DollarSign,
    Scale, XCircle, ImageIcon, Wine
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,  // Resource Collection { data: [], links: [], meta: {} }
    filters: Object,   // Estado de filtros del backend
    options: Object,   // { brands: [], categories: [] }
    stats: Object      // Estadísticas globales del catálogo
});

// --- DATA UNWRAPPING ---
const productsList = computed(() => props.products?.data || []);

// --- ESTADO REACTIVO ---
const filtersForm = ref({
    search: props.filters?.search || '',
    brand: props.filters?.brand || '',
    category: props.filters?.category || '',
    status: props.filters?.status || ''
});

const expandedRows = ref(new Set());
const hoveredProduct = ref(null);
const viewMode = ref('table');

// --- LÓGICA DE ACORDEÓN ---
const toggleRow = (id) => {
    if (expandedRows.value.has(id)) {
        expandedRows.value.delete(id);
    } else {
        expandedRows.value.add(id);
    }
};

// --- MOTOR DE BÚSQUEDA Y FILTRADO ---
watch(filtersForm, debounce((val) => {
    const cleanFilters = Object.fromEntries(Object.entries(val).filter(([_, v]) => v !== '' && v !== null));
    router.get(route('admin.products.index'), cleanFilters, { 
        preserveState: true, 
        replace: true,
        preserveScroll: true
    });
}, 400), { deep: true });

const resetFilters = () => {
    filtersForm.value = { search: '', brand: '', category: '', status: '' };
};

// --- ACCIONES ---
const deleteProduct = (product) => {
    if (confirm(`¿Eliminar el producto "${product.name}" y todas sus variantes? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.products.destroy', product.id), { preserveScroll: true });
    }
};

// --- UTILIDADES ---
const formatPrice = (price) => {
    return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(price || 0);
};

// --- KPIS ---
const displayStats = computed(() => [
    { label: 'Total productos', value: props.stats?.total ?? 0, icon: Package },
    { label: 'Activos', value: props.stats?.active ?? 0, icon: CheckCircle2 },
    { label: 'Total SKUs', value: props.stats?.total_skus ?? 0, icon: Hash },
    { label: 'Incompletos', value: props.stats?.incomplete ?? 0, icon: AlertTriangle },
]);

// --- LIMPIAR BÚSQUEDA ---
const clearSearch = () => {
    filtersForm.value.search = '';
};
</script>

<template>
    <AdminLayout>
        <Head title="Catálogo de Productos" />

        <div class="max-w-7xl mx-auto space-y-6 pb-20 px-4 md:px-0">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-foreground tracking-tight">
                        Catálogo de Productos
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Gestión de productos maestros y variantes
                    </p>
                </div>
                
                <Link :href="route('admin.products.create')" 
                      class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary/90 transition-all inline-flex items-center gap-2">
                    <Plus :size="18" />
                    Nuevo producto
                </Link>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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

            <!-- Filtros -->
            <div class="flex flex-col lg:flex-row gap-4 bg-card border border-border rounded-lg p-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                    <input v-model="filtersForm.search" type="text" placeholder="Buscar por producto o código de variante..." 
                           class="w-full pl-10 pr-10 py-2 bg-background border border-border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    <button v-if="filtersForm.search" @click="clearSearch" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                        ✕
                    </button>
                </div>
                
                <select v-model="filtersForm.brand" class="bg-background border border-border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary min-w-[150px]">
                    <option value="">Todas las marcas</option>
                    <option v-for="b in options?.brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>

                <select v-model="filtersForm.category" class="bg-background border border-border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary min-w-[150px]">
                    <option value="">Todas las categorías</option>
                    <option v-for="c in options?.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>

                <select v-model="filtersForm.status" class="bg-background border border-border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary min-w-[150px]">
                    <option value="">Todos los estados</option>
                    <option value="complete">Completos (con SKU)</option>
                    <option value="incomplete">Incompletos (sin SKU)</option>
                </select>

                <button @click="resetFilters" class="border border-destructive/50 text-destructive px-3 py-2 rounded-md text-sm font-medium hover:bg-destructive hover:text-destructive-foreground transition-all inline-flex items-center gap-2 whitespace-nowrap">
                    <XCircle :size="16" />
                    Limpiar
                </button>
            </div>

            <!-- Tabla de productos -->
            <div class="border border-border rounded-xl overflow-hidden bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-muted/30 border-b border-border">
                            <tr class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                <th class="px-6 py-4 w-12 text-center">Est.</th>
                                <th class="px-6 py-4">Producto</th>
                                <th class="px-6 py-4">Jerarquía</th>
                                <th class="px-6 py-4 text-center">SKUs</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        
                        <tbody class="divide-y divide-border">
                            <tr v-if="productsList.length === 0">
                                <td colspan="5" class="py-12 text-center text-muted-foreground">
                                    No se encontraron productos.
                                </td>
                            </tr>

                            <template v-for="product in productsList" :key="product.id">
                                <tr @mouseenter="hoveredProduct = product.id"
                                    @mouseleave="hoveredProduct = null"
                                    class="hover:bg-muted/5 transition-colors group/row">
                                    
                                    <td class="px-6 py-4 text-center">
                                        <div class="w-2.5 h-2.5 rounded-full mx-auto" 
                                             :class="!product.skus || product.skus.length === 0 ? 'bg-destructive' : 'bg-success'">
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-muted/20 rounded-lg flex items-center justify-center overflow-hidden border border-border">
                                                <img v-if="product.image_url" :src="product.image_url" class="object-contain w-full h-full p-1">
                                                <PackageSearch v-else :size="20" class="text-muted-foreground/30" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-foreground">{{ product.name }}</p>
                                                <div class="flex items-center gap-3 mt-1 text-xs text-muted-foreground">
                                                    <span>ID: {{ product.id.substring(0,8) }}...</span>
                                                    <span v-if="product.is_alcoholic" class="text-warning flex items-center gap-1">
                                                        <Wine :size="12" /> Alcohólico
                                                    </span>
                                                    <span v-if="!product.is_active" class="text-destructive flex items-center gap-1">
                                                        <WifiOff :size="12" /> Inactivo
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <div class="flex items-center gap-2">
                                                <Layers :size="14" class="text-muted-foreground" />
                                                <span>{{ product.category?.name || 'Sin categoría' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <Tag :size="14" class="text-muted-foreground" />
                                                <span>{{ product.brand?.name || 'Sin marca' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <button @click="toggleRow(product.id)" 
                                                class="inline-flex items-center gap-2 px-3 py-1.5 border border-border rounded-md text-sm hover:bg-primary/5 hover:border-primary/30 transition-colors"
                                                :class="!product.skus || product.skus.length === 0 ? 'text-destructive border-destructive/30' : ''">
                                            <Box :size="16" />
                                            <span class="font-medium">{{ product.skus?.length || 0 }}</span>
                                            <component :is="expandedRows.has(product.id) ? ChevronDown : ChevronRight" :size="16" />
                                        </button>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('admin.products.skus.create', product.id)" 
                                                  class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/5 rounded-md transition-colors"
                                                  title="Añadir variante">
                                                <Plus :size="18" />
                                            </Link>
                                            <Link :href="route('admin.products.edit', product.id)" 
                                                  class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/5 rounded-md transition-colors"
                                                  title="Editar producto">
                                                <Edit3 :size="18" />
                                            </Link>
                                            <button @click="deleteProduct(product)" 
                                                    class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/5 rounded-md transition-colors"
                                                    title="Eliminar">
                                                <Trash2 :size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Fila expandida con variantes -->
                                <tr v-if="expandedRows.has(product.id)" class="bg-muted/5">
                                    <td colspan="5" class="p-0">
                                        <div class="px-8 py-6 border-t border-border">
                                            <div v-if="!product.skus || product.skus.length === 0" 
                                                 class="flex items-center justify-between p-4 bg-destructive/5 border border-destructive/30 rounded-lg">
                                                <div class="flex items-center gap-3">
                                                    <AlertTriangle :size="20" class="text-destructive" />
                                                    <span class="text-sm font-medium text-destructive">
                                                        Producto incompleto: necesita al menos una variante para comercializarse.
                                                    </span>
                                                </div>
                                                <Link :href="route('admin.products.skus.create', product.id)" 
                                                      class="bg-primary text-primary-foreground px-4 py-2 rounded-md text-sm font-medium hover:bg-primary/90 transition-colors">
                                                    Crear variante
                                                </Link>
                                            </div>

                                            <table v-else class="w-full text-sm">
                                                <thead class="text-xs font-medium text-muted-foreground border-b border-border">
                                                    <tr>
                                                        <th class="py-2 text-left">Variante</th>
                                                        <th class="py-2 text-left">Código (EAN)</th>
                                                        <th class="py-2 text-center">Factor / Peso</th>
                                                        <th class="py-2 text-right">Precio ref.</th>
                                                        <th class="py-2 text-right"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-border">
                                                    <tr v-for="sku in product.skus" :key="sku.id" class="hover:bg-muted/5 transition-colors group/sku">
                                                        <td class="py-3">
                                                            <div class="flex items-center gap-3">
                                                                <div class="w-8 h-8 bg-muted/20 rounded border border-border flex items-center justify-center overflow-hidden">
                                                                    <img v-if="sku.image_url" :src="sku.image_url" class="object-cover w-full h-full">
                                                                    <Barcode v-else :size="14" class="text-muted-foreground/30" />
                                                                </div>
                                                                <span class="font-medium">{{ sku.name }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="py-3 font-mono text-muted-foreground">{{ sku.code || '—' }}</td>
                                                        <td class="py-3 text-center text-muted-foreground">
                                                            {{ sku.conversion_factor }}x · {{ sku.weight }}kg
                                                        </td>
                                                        <td class="py-3 text-right font-medium text-success">
                                                            ${{ formatPrice(sku.base_price) }}
                                                        </td>
                                                        <td class="py-3 text-right">
                                                            <Link :href="route('admin.skus.edit', sku.id)" class="p-1.5 text-muted-foreground hover:text-primary hover:bg-primary/5 rounded transition-colors">
                                                                <Edit3 :size="16" />
                                                            </Link>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="props.products?.links && props.products.links.length > 3" class="flex justify-center">
                <nav class="flex gap-2">
                    <template v-for="(link, k) in props.products.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" 
                              class="min-w-[36px] h-9 flex items-center justify-center text-sm border rounded-md transition-all"
                              :class="link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-card text-muted-foreground border-border hover:border-primary/30 hover:text-foreground'"
                              :preserve-scroll="true" />
                        <span v-else v-html="link.label" 
                              class="min-w-[36px] h-9 flex items-center justify-center text-sm text-muted-foreground/30 border border-transparent"></span>
                    </template>
                </nav>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Estilos mínimos necesarios, sin elementos ciberpunk */
</style>