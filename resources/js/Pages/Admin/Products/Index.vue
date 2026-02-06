<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Package, Plus, Search, Edit, Trash2, 
    Tag, Layers, Image as ImageIcon, CheckCircle, 
    XCircle, ChevronDown, Barcode, Box, Cuboid,
    MoreVertical, ShoppingBag, DollarSign
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    filters: Object
});

const search = ref(props.filters?.search || '');
const expandedRows = ref([]);

// --- BÚSQUEDA ---
watch(search, debounce((val) => {
    router.get(route('admin.products.index'), { search: val }, {
        preserveState: true, replace: true, preserveScroll: true
    });
}, 300));

// --- ACCIONES ---
const toggleRow = (id) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
    }
};

const deleteProduct = (id, name) => {
    if (confirm(`⚠ ATENCIÓN: Eliminar "${name}" borrará permanentemente todas sus variantes. ¿Proceder?`)) {
        router.delete(route('admin.products.destroy', id));
    }
};

// --- HELPERS ---
const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    if (imagePath.startsWith('http')) return imagePath;
    if (imagePath.startsWith('/storage/')) return imagePath;
    if (imagePath.startsWith('storage/')) return `/${imagePath}`;
    return `/storage/${imagePath}`;
};

// --- KPIs COMPUTADOS (Basados en la página actual para demo visual) ---
const statsList = computed(() => {
    const total = props.products.total || 0;
    const currentCount = props.products.data.length;
    // Calculamos SKUs totales visibles en esta página
    const totalSkus = props.products.data.reduce((acc, prod) => acc + (prod.skus_count || 0), 0);
    
    return [
        { label: 'Total Productos', value: total, icon: Package, color: 'text-primary', bg: 'bg-primary/10' },
        { label: 'En esta pág.', value: currentCount, icon: Layers, color: 'text-info', bg: 'bg-info/10' },
        { label: 'SKUs Visibles', value: totalSkus, icon: Barcode, color: 'text-success', bg: 'bg-success/10' },
    ];
});
</script>

<template>
    <AdminLayout>
        <Head title="Inventario Maestro" />

        <div class="pb-32 md:pb-10"> 
            
            <div class="mb-6 space-y-4">
                <div class="flex justify-between items-end">
                    <div>
                        <h1 class="text-2xl font-display font-black text-foreground tracking-tight">Catálogo</h1>
                        <p class="text-xs text-muted-foreground">Productos y Variantes</p>
                    </div>
                </div>

                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Buscar producto, marca, código..." 
                        class="w-full pl-10 pr-4 py-3 bg-background border border-border rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-sm shadow-sm"
                    />
                </div>
            </div>

            <div class="flex overflow-x-auto snap-x snap-mandatory gap-3 pb-4 mb-2 -mx-4 px-4 md:mx-0 md:px-0 no-scrollbar touch-pan-x">
                <div v-for="(stat, index) in statsList" :key="index" 
                     class="snap-start shrink-0 w-[130px] card !p-3 flex flex-col justify-between h-24 border border-border/60 shadow-sm bg-card">
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted-foreground">{{ stat.label }}</span>
                        <div :class="`p-1.5 rounded-full ${stat.bg} ${stat.color}`">
                            <component :is="stat.icon" :size="14" />
                        </div>
                    </div>
                    <span class="text-2xl font-display font-black text-foreground tracking-tight">{{ stat.value }}</span>
                </div>
            </div>

            <div class="space-y-3">
                <TransitionGroup name="list">
                    <div v-for="product in products.data" :key="product.id" 
                         class="card border-border/50 overflow-hidden transition-all duration-300 bg-card"
                         :class="expandedRows.includes(product.id) ? 'ring-2 ring-primary/20 shadow-md' : 'shadow-sm'">
                        
                        <div class="p-3 sm:p-4 flex items-center gap-3 cursor-pointer hover:bg-muted/30 transition-colors select-none relative"
                             @click="toggleRow(product.id)">
                            
                            <div class="relative shrink-0">
                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-muted to-muted/50 border border-border flex items-center justify-center overflow-hidden">
                                    <img v-if="getImageUrl(product.image_url)" 
                                         :src="getImageUrl(product.image_url)" 
                                         class="w-full h-full object-cover">
                                    <ImageIcon v-else :size="24" class="text-muted-foreground/40" />
                                </div>
                                <div v-if="!product.is_active" class="absolute -bottom-1 -right-1 bg-background border border-border rounded-full p-0.5 shadow-sm text-muted-foreground">
                                    <XCircle :size="12" />
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-foreground truncate text-sm sm:text-base leading-tight pr-6">
                                        {{ product.name }}
                                    </h3>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2">
                                        <ChevronDown :size="20" class="text-muted-foreground transition-transform duration-300" 
                                                     :class="expandedRows.includes(product.id) ? 'rotate-180 text-primary' : ''"/>
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                    <span class="text-[10px] font-medium bg-muted px-1.5 py-0.5 rounded text-muted-foreground flex items-center gap-1">
                                        <Tag :size="10"/> {{ product.brand?.name || 'S/M' }}
                                    </span>
                                    <span class="text-[10px] font-medium bg-muted px-1.5 py-0.5 rounded text-muted-foreground flex items-center gap-1">
                                        <Layers :size="10"/> {{ product.category?.name || 'S/C' }}
                                    </span>
                                    <span class="text-[10px] font-bold text-primary bg-primary/10 px-1.5 py-0.5 rounded ml-auto mr-8">
                                        {{ product.skus_count }} Variantes
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-show="expandedRows.includes(product.id)">
                            
                            <div class="flex border-t border-border/50 divide-x divide-border/50 bg-muted/10">
                                <Link :href="route('admin.products.edit', product.id)" 
                                      class="flex-1 py-3 flex items-center justify-center gap-2 text-xs font-bold text-foreground hover:bg-background transition-colors">
                                    <Edit :size="14"/> Editar Maestro
                                </Link>
                                <button @click="deleteProduct(product.id, product.name)"
                                        class="flex-1 py-3 flex items-center justify-center gap-2 text-xs font-bold text-error hover:bg-error/10 transition-colors">
                                    <Trash2 :size="14"/> Eliminar
                                </button>
                            </div>

                            <div class="bg-muted/5 border-t border-border/50 p-2 sm:p-3 space-y-2">
                                <div v-if="!product.skus || product.skus.length === 0" class="text-center py-4 text-xs text-muted-foreground italic">
                                    No hay variantes registradas.
                                </div>

                                <div v-for="sku in product.skus" :key="sku.id" 
                                     class="flex items-center gap-3 p-3 rounded-lg bg-background border border-border/40 shadow-sm relative overflow-hidden">
                                    
                                    <div class="w-10 h-10 rounded border border-border/50 overflow-hidden shrink-0 flex items-center justify-center bg-muted/20">
                                        <img v-if="getImageUrl(sku.image_url)" 
                                             :src="getImageUrl(sku.image_url)" 
                                             class="w-full h-full object-cover">
                                        <Barcode v-else :size="16" class="text-muted-foreground/30"/>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start">
                                            <p class="text-xs font-bold text-foreground truncate">{{ sku.name }}</p>
                                            <p class="text-xs font-mono font-bold text-success">Bs {{ parseFloat(sku.price).toFixed(2) }}</p>
                                        </div>
                                        
                                        <div class="flex items-center gap-2 mt-1">
                                            <p class="text-[10px] text-muted-foreground font-mono bg-muted px-1 rounded truncate">
                                                {{ sku.code || '---' }}
                                            </p>
                                            
                                            <span v-if="parseFloat(sku.conversion_factor) === 1" 
                                                  class="text-[9px] flex items-center gap-1 text-info font-medium">
                                                <Cuboid :size="10"/> Unidad
                                            </span>
                                            <span v-else class="text-[9px] flex items-center gap-1 text-warning font-medium">
                                                <Box :size="10"/> Pack x{{ parseFloat(sku.conversion_factor) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>

                <div v-if="products.data.length === 0" class="flex flex-col items-center justify-center py-16 text-center opacity-60">
                    <div class="w-16 h-16 bg-muted/50 rounded-full flex items-center justify-center mb-4">
                        <Package :size="32" class="text-muted-foreground" />
                    </div>
                    <p class="text-sm font-medium">No se encontraron productos</p>
                    <p class="text-xs text-muted-foreground mt-1">Intenta ajustar los filtros de búsqueda.</p>
                </div>

                <div v-if="products.meta?.last_page > 1" class="flex justify-center pt-6">
                    <div class="flex gap-1 overflow-x-auto max-w-full pb-2 no-scrollbar">
                        <Link v-for="(link, k) in products.meta.links" :key="k"
                              :href="link.url || '#'"
                              v-html="link.label"
                              class="px-3 py-2 rounded-lg text-xs font-bold transition-all min-w-[36px] flex items-center justify-center border"
                              :class="{
                                  'bg-primary text-primary-foreground border-primary': link.active,
                                  'bg-card text-foreground border-border hover:bg-muted': !link.active && link.url,
                                  'text-muted-foreground opacity-50 border-transparent pointer-events-none': !link.url
                              }"
                              :preserve-scroll="true" />
                    </div>
                </div>
            </div>

            <Link :href="route('admin.products.create')" 
                  class="fixed bottom-24 md:bottom-8 right-4 md:right-8 z-[100] flex items-center justify-center w-14 h-14 rounded-2xl bg-primary text-primary-foreground shadow-[0_8px_30px_rgba(var(--primary),0.4)] hover:scale-110 active:scale-95 transition-all duration-300 group border border-white/20">
                <Plus :size="28" stroke-width="3" class="group-hover:rotate-90 transition-transform duration-300"/>
                <span class="sr-only">Nuevo Producto</span>
            </Link>

        </div>
    </AdminLayout>
</template>

<style scoped>
/* Utilidad para ocultar scrollbar en carrusel móvil */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Animaciones de lista */
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>