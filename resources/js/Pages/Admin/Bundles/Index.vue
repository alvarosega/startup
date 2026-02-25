<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Package, Plus, Edit, Trash2, Search, MapPin, 
    Layers, ShoppingBag, Tag, MoreHorizontal, AlertCircle 
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    bundles: Object,
    branches: Array
});

const search = ref('');
const branchFilter = ref('');

// --- LÓGICA ORIGINAL (INTACTA) ---
const updateParams = debounce(() => {
    router.get(route('admin.bundles.index'), { 
        search: search.value,
        branch_id: branchFilter.value 
    }, { preserveState: true, replace: true });
}, 300);

watch([search, branchFilter], updateParams);

const deleteBundle = (id) => {
    if (confirm('¿Estás seguro de eliminar este pack permanentemente?')) {
        router.delete(route('admin.bundles.destroy', id));
    }
};

const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    if (imagePath.startsWith('http')) return imagePath;
    return `/storage/${imagePath}`;
};

// --- HELPERS VISUALES ---
const hasActiveFilters = computed(() => search.value !== '' || branchFilter.value !== '');
</script>

<template>
    <AdminLayout>
        <Head title="Gestión de Packs" />

        <div class="pb-40 md:pb-12 min-h-screen flex flex-col">

            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur-md border-b border-border/60 transition-all duration-300">
                <div class="px-4 py-4 md:px-0 space-y-4 max-w-7xl mx-auto">
                    
                    <div class="flex justify-between items-end">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-display font-black text-foreground tracking-tighter flex items-center gap-2">
                                <Package class="text-primary hidden md:block" :size="28" stroke-width="2.5" />
                                Packs & Bundles
                                <span class="text-xs font-bold text-muted-foreground bg-muted px-2 py-0.5 rounded-full border border-border">
                                    {{ bundles.total }}
                                </span>
                            </h1>
                            <p class="text-xs text-muted-foreground font-medium mt-0.5">Ofertas compuestas y promociones.</p>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-3">
                        <div class="relative flex-1 group">
                            <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input v-model="search" type="text" placeholder="Buscar pack..." 
                                   class="w-full pl-10 pr-4 py-2.5 bg-muted/30 border border-border rounded-xl text-sm focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none placeholder:text-muted-foreground/50" />
                        </div>
                        
                        <div class="relative w-full md:w-64 group">
                            <MapPin :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <select v-model="branchFilter" 
                                    class="w-full pl-10 pr-8 py-2.5 bg-muted/30 border border-border rounded-xl text-sm focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none appearance-none cursor-pointer">
                                <option value="">Todas las sucursales</option>
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground">
                                <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1L5 5L9 1"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 md:px-0 py-6 max-w-7xl mx-auto w-full">
                
                <div v-if="bundles.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <div v-for="bundle in bundles.data" :key="bundle.id" 
                         class="card group bg-card border border-border hover:shadow-lg hover:border-primary/40 transition-all duration-300 overflow-hidden flex flex-col relative">
                        
                        <div class="aspect-video w-full bg-muted/10 relative overflow-hidden">
                            <img v-if="bundle.image_url" :src="bundle.image_url"
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                            <div v-else class="w-full h-full flex flex-col items-center justify-center text-muted-foreground/30 gap-2">
                                <Package :size="32" stroke-width="1.5" />
                                <span class="text-[10px] font-bold uppercase tracking-wider">Sin Imagen</span>
                            </div>

                            <div class="absolute top-3 left-3">
                                <span class="badge shadow-sm backdrop-blur-md border-0 text-[10px] font-black uppercase tracking-wider"
                                      :class="bundle.is_active ? 'bg-success/90 text-white' : 'bg-muted/90 text-muted-foreground'">
                                    {{ bundle.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            <div class="absolute bottom-3 right-3">
                                <span v-if="bundle.fixed_price" class="badge bg-background/90 backdrop-blur text-foreground font-black border-primary/30 shadow-lg">
                                    Bs {{ parseFloat(bundle.fixed_price).toFixed(2) }}
                                </span>
                                <span v-else class="badge bg-background/90 backdrop-blur text-muted-foreground text-[10px] font-bold border-border shadow-sm">
                                    Dinámico
                                </span>
                            </div>
                        </div>

                        <div class="p-5 flex-1 flex flex-col gap-3">
                            <div>
                                <h3 class="font-bold text-lg text-foreground leading-tight line-clamp-1 group-hover:text-primary transition-colors">
                                    {{ bundle.name }}
                                </h3>
                                <div class="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground">
                                    <MapPin :size="12" />
                                    <span class="font-medium">{{ bundle.branch?.name || 'Global' }}</span>
                                </div>
                            </div>

                            <p class="text-xs text-muted-foreground line-clamp-2 h-8">
                                {{ bundle.description || 'Sin descripción disponible.' }}
                            </p>

                            <div class="mt-auto pt-3 border-t border-border/50">
                                <div class="flex items-center gap-2 mb-2">
                                    <Layers :size="12" class="text-primary"/>
                                    <span class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Contenido del Pack</span>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <template v-if="bundle.items && bundle.items.length > 0">
                                        <div v-for="sku in bundle.items.slice(0, 3)" :key="sku.sku_id" 
                                            class="px-2 py-1 rounded bg-muted/30 border border-border text-[10px] font-medium flex items-center gap-1">
                                            <span class="truncate max-w-[80px]">{{ sku.name }}</span>
                                            <span class="bg-primary/10 text-primary px-1 rounded-[2px] font-bold">x{{ sku.quantity }}</span>
                                        </div>
                                        <span v-if="bundle.items.length > 3" class="px-2 py-1 rounded bg-muted/30 border border-border text-[10px] font-medium text-muted-foreground">
                                            +{{ bundle.items.length - 3 }} más
                                        </span>
                                    </template>
                                    <span v-else class="text-xs text-muted-foreground italic flex items-center gap-1">
                                        <AlertCircle :size="12"/> Sin items asignados
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 border-t border-border divide-x divide-border bg-muted/5">
                            <Link :href="route('admin.bundles.edit', bundle.id)" 
                                  class="flex items-center justify-center gap-2 py-3 text-xs font-bold text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors">
                                <Edit :size="14" /> Editar
                            </Link>
                            <button @click="deleteBundle(bundle.id)" 
                                    class="flex items-center justify-center gap-2 py-3 text-xs font-bold text-muted-foreground hover:text-error hover:bg-error/5 transition-colors">
                                <Trash2 :size="14" /> Eliminar
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-20 text-center opacity-70 animate-in zoom-in-95 duration-500">
                    <div class="w-20 h-20 bg-muted/30 rounded-full flex items-center justify-center mb-4 border border-border">
                        <Package :size="40" class="text-muted-foreground/50" />
                    </div>
                    <h3 class="text-lg font-bold text-foreground">No hay packs encontrados</h3>
                    <p class="text-sm text-muted-foreground mt-1 max-w-[250px]">
                        {{ hasActiveFilters ? 'Intenta cambiar los filtros de búsqueda.' : 'Crea tu primer pack promocional.' }}
                    </p>
                </div>

                <div v-if="bundles.links && bundles.links.length > 3" class="mt-8 flex justify-center">
                    <div class="flex gap-1 overflow-x-auto max-w-full pb-2 no-scrollbar px-2">
                        <template v-for="(link, k) in bundles.links" :key="k">
                            <Link v-if="link.url" :href="link.url" v-html="link.label"
                                  class="min-w-[36px] h-9 flex items-center justify-center rounded-lg text-xs font-bold border transition-all"
                                  :class="link.active 
                                    ? 'bg-primary text-primary-foreground border-primary shadow-md' 
                                    : 'bg-card text-muted-foreground border-border hover:border-primary/50 hover:text-foreground'" />
                            <span v-else v-html="link.label" 
                                  class="min-w-[36px] h-9 flex items-center justify-center rounded-lg text-xs text-muted-foreground/30 border border-transparent" />
                        </template>
                    </div>
                </div>

            </div>

            <Teleport to="body">
                <Link :href="route('admin.bundles.create')" 
                      class="fixed bottom-24 right-4 md:right-8 z-[9999] group predictive-aura">
                    <div class="w-14 h-14 rounded-full bg-primary text-primary-foreground shadow-[0_8px_30px_rgba(0,240,255,0.4)] flex items-center justify-center transition-transform duration-300 group-hover:scale-110 group-active:scale-95 border-2 border-white/10 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-tr from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <Plus :size="28" stroke-width="3" class="group-hover:rotate-90 transition-transform duration-300"/>
                    </div>
                    <span class="sr-only">Nuevo Pack</span>
                </Link>
            </Teleport>

        </div>
    </AdminLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>