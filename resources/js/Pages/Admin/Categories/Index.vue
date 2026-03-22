<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Plus, Layers, Settings, Trash2, 
    ShieldAlert, ReceiptText, Wifi, WifiOff, 
    Hash, ArrowUpDown, PackageSearch
} from 'lucide-vue-next';

const props = defineProps({
    categories: [Array, Object],
    filters: Object,
    can_manage: Boolean
});

// --- ESTADO ---
const search = ref(props.filters.search || '');
const hoveredCategory = ref(null);

// --- NORMALIZACIÓN (Pilar 3.C) ---
const categoriesList = computed(() => {
    return Array.isArray(props.categories) ? props.categories : (props.categories.data || []);
});

// --- FILTRADO LOCAL ---
const filteredCategories = computed(() => {
    if (!search.value) return categoriesList.value;
    const term = search.value.toLowerCase();
    return categoriesList.value.filter(c => 
        c.name.toLowerCase().includes(term) || 
        (c.external_code && c.external_code.toLowerCase().includes(term))
    );
});

// --- ESTADÍSTICAS ATÓMICAS ---
const stats = computed(() => [
    { label: 'Total categorías', value: categoriesList.value.length, icon: Layers },
    { label: 'Activas', value: categoriesList.value.filter(c => c.is_active).length, icon: Wifi },
    { label: '+18', value: categoriesList.value.filter(c => c.requires_age_check).length, icon: ShieldAlert },
]);

// --- ACCIONES ---
const deleteCategory = (category) => {
    if (confirm(`¿Eliminar la categoría "${category.name}"? Se validará que no tenga productos vinculados.`)) {
        router.delete(route('admin.categories.destroy', category.id), { preserveScroll: true });
    }
};

const updateFilters = debounce(() => {
    router.get(route('admin.categories.index'), { search: search.value }, { 
        preserveState: true, 
        replace: true, 
        preserveScroll: true 
    });
}, 300);

watch(search, updateFilters);

const clearSearch = () => search.value = '';
</script>

<template>
    <AdminLayout>
        <Head title="Categorías" />

        <div class="max-w-7xl mx-auto pb-24 px-4 sm:px-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-black text-foreground tracking-tighter italic uppercase">
                        Categorías
                    </h1>
                    <p class="text-xs font-mono text-muted-foreground mt-1 uppercase tracking-widest">
                        Arquitectura plana del catálogo maestro
                    </p>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <div class="relative flex-1 md:w-80">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                        <input v-model="search" type="text" placeholder="BUSCAR DEPARTAMENTO..."
                               class="w-full pl-10 pr-10 py-2.5 bg-background border-2 border-border focus:border-primary transition-all font-mono text-xs uppercase tracking-widest outline-none" />
                    </div>
                    <Link v-if="can_manage" :href="route('admin.categories.create')" 
                          class="bg-primary text-primary-foreground px-6 py-2.5 font-black uppercase text-xs tracking-tighter hover:bg-primary/90 transition-all flex items-center gap-2 shadow-f1-glow">
                        <Plus :size="18" stroke-width="3" />
                        <span>Nuevo</span>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div v-for="(stat, index) in stats" :key="index" 
                     class="border-2 border-border p-4 relative bg-card shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-primary/10 text-primary">
                            <component :is="stat.icon" :size="20" />
                        </div>
                        <div>
                            <p class="text-[8px] font-mono font-bold uppercase text-muted-foreground tracking-[0.2em]">{{ stat.label }}</p>
                            <p class="text-2xl font-black text-foreground mt-1 tabular-nums italic">{{ stat.value }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <TransitionGroup name="list">
                    <div v-for="category in filteredCategories" :key="category.id" 
                         class="border-2 border-border bg-card hover:border-primary transition-all duration-300 relative group/card flex flex-col">
                        
                        <div class="h-1.5 w-full" :style="{ backgroundColor: category.bg_color || '#3b82f6' }"></div>

                        <div class="p-6">
                            <div class="flex items-start justify-between mb-6">
                                <div class="w-16 h-16 bg-muted rounded-xl overflow-hidden border-2 border-border group-hover/card:border-primary/30 transition-colors">
                                    <img v-if="category.image_url" :src="category.image_url" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground/20">
                                        <Layers :size="32" />
                                    </div>
                                </div>

                                <div v-if="can_manage" class="flex gap-1">
                                    <Link :href="route('admin.categories.sku-order', category.id)" 
                                          class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/10 border border-transparent hover:border-primary/20 rounded-lg transition-all"
                                          title="Góndola de Variantes">
                                        <ArrowUpDown :size="18" />
                                    </Link>
                                    
                                    <Link :href="route('admin.categories.edit', category.id)" 
                                          class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-all">
                                        <Settings :size="18" />
                                    </Link>
                                    
                                    <button @click="deleteCategory(category)" 
                                            class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-all">
                                        <Trash2 :size="18" />
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h3 class="text-xl font-black italic uppercase tracking-tighter text-foreground group-hover/card:text-primary transition-colors">
                                    {{ category.name }}
                                </h3>
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="w-2 h-2 rounded-full" :class="category.is_active ? 'bg-success animate-pulse' : 'bg-destructive'"></div>
                                    <span class="text-[10px] font-bold uppercase tracking-widest" :class="category.is_active ? 'text-success' : 'text-destructive'">
                                        {{ category.is_active ? 'Online' : 'Offline' }}
                                    </span>
                                </div>
                            </div>

                            <p class="text-xs text-muted-foreground line-clamp-2 min-h-[32px] mb-6 font-medium">
                                {{ category.description || 'Sin descripción técnica.' }}
                            </p>

                            <div class="flex flex-wrap gap-2 pt-4 border-t border-border/50">
                                <div class="flex items-center gap-1.5 px-2.5 py-1 bg-muted rounded-md text-[9px] font-black uppercase tracking-tighter">
                                    <Hash :size="10" /> {{ category.external_code || 'N/A' }}
                                </div>
                                <div v-if="category.requires_age_check" 
                                     class="flex items-center gap-1.5 px-2.5 py-1 bg-warning/20 text-warning-foreground rounded-md text-[9px] font-black uppercase tracking-tighter">
                                    <ShieldAlert :size="10" /> +18
                                </div>
                                <div v-if="category.tax_classification" 
                                     class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-500/10 text-emerald-600 rounded-md text-[9px] font-black uppercase tracking-tighter">
                                    <ReceiptText :size="10" /> {{ category.tax_classification }}
                                </div>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>
            </div>

            <div v-if="filteredCategories.length === 0" class="py-24 text-center border-2 border-dashed border-border mt-8">
                <PackageSearch :size="48" class="mx-auto text-muted-foreground/20 mb-4" />
                <h3 class="text-lg font-black uppercase italic tracking-tighter">No se detectaron registros</h3>
                <button v-if="search" @click="clearSearch" class="mt-4 text-xs font-bold uppercase tracking-widest text-primary underline">Reiniciar radar</button>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.list-move, .list-enter-active, .list-leave-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(30px) scale(0.95); }
.list-leave-active { position: absolute; }
.shadow-f1-glow { box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.3); }
</style>