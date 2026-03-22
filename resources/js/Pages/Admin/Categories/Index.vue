<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Plus, Layers, Settings, Trash2, 
    ShieldAlert, ReceiptText, Wifi, WifiOff, 
    Hash, ArrowUpDown, PackageSearch, ChevronRight,
    LayoutGrid
} from 'lucide-vue-next';
const props = defineProps({
    categories: Object,
    filters: Object, // Ahora el controlador lo envía correctamente
    can_manage: Boolean
});

// --- ESTADO DE RADAR (SERVER-SIDE ONLY) ---
const search = ref(props.filters?.search || '');

// --- NORMALIZACIÓN DE DATA ---
const categoriesData = computed(() => props.categories.data || []);

// --- ESTADÍSTICAS OPERATIVAS (CÁLCULO SOBRE DATA ACTUAL) ---
const stats = computed(() => [
    { label: 'Nodos de Catálogo', value: categoriesData.value.length, icon: Layers },
    { label: 'Estado: Online', value: categoriesData.value.filter(c => c.is_active).length, icon: Wifi },
    { label: 'Restricción +18', value: categoriesData.value.filter(c => c.requires_age_check).length, icon: ShieldAlert },
]);

// --- PROTOCOLO DE ELIMINACIÓN (TRANSACCIONAL) ---
const deleteCategory = (category) => {
    // UUIDv7 check implícito
    if (confirm(`CRITICAL: ¿Confirmar neutralización de la categoría "${category.name}"?`)) {
        router.delete(route('admin.categories.destroy', category.id), { 
            preserveScroll: true,
            onSuccess: () => {
                // Notificación opcional aquí
            }
        });
    }
};

// --- FILTRADO DE SERVIDOR (DEBOUNCED) ---
const performSearch = debounce(() => {
    router.get(route('admin.categories.index'), 
        { search: search.value }, 
        { preserveState: true, replace: true, preserveScroll: true }
    );
}, 400);

watch(search, () => {
    performSearch();
});

const resetRadar = () => {
    search.value = '';
};
</script>

<template>
    <AdminLayout>
        <Head title="Base de Datos: Categorías" />

        <div class="max-w-7xl mx-auto pb-24 px-4 sm:px-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
                <div>
                    <h1 class="text-4xl font-black text-foreground tracking-tighter italic uppercase flex items-center gap-3">
                        <LayoutGrid class="text-primary" :size="32" stroke-width="3" />
                        Categorías
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground mt-2 uppercase tracking-[0.3em] flex items-center gap-2">
                        <span class="w-2 h-2 bg-primary animate-pulse"></span>
                        Exploración de arquitectura maestra de catálogo
                    </p>
                </div>

                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="relative flex-1 md:w-96">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground" :size="16" />
                        <input v-model="search" type="text" placeholder="BUSCAR CATEGORÍA O CÓDIGO EXTERNO..."
                               class="w-full pl-11 pr-4 py-3 bg-muted/30 border-2 border-border focus:border-primary transition-all font-mono text-[10px] uppercase tracking-widest outline-none focus:ring-0">
                    </div>
                    
                    <Link v-if="can_manage" :href="route('admin.categories.create')" 
                          class="bg-primary text-primary-foreground px-8 py-3.5 font-black uppercase text-xs tracking-widest hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-3 shadow-f1-glow">
                        <Plus :size="18" stroke-width="4" />
                        <span>Nueva</span>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div v-for="(stat, index) in stats" :key="index" 
                     class="bg-card border-l-4 border-primary p-6 shadow-sm flex items-center justify-between group hover:bg-muted/50 transition-colors">
                    <div>
                        <p class="text-[9px] font-mono font-bold uppercase text-muted-foreground tracking-widest mb-2">{{ stat.label }}</p>
                        <p class="text-3xl font-black text-foreground tabular-nums tracking-tighter italic">{{ stat.value }}</p>
                    </div>
                    <component :is="stat.icon" class="text-primary/20 group-hover:text-primary/40 transition-colors" :size="40" stroke-width="1.5" />
                </div>
            </div>

            <div v-if="categoriesData.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <TransitionGroup name="list">
                    <div v-for="category in categoriesData" :key="category.id" 
                         class="flex flex-col border-2 border-border bg-card hover:border-primary/50 transition-all duration-300 relative group/card">
                        
                        <div class="h-2 w-full" :style="{ backgroundColor: category.bg_color || '#3b82f6' }"></div>

                        <div class="p-8 flex-1 flex flex-col">
                            <div class="flex items-start justify-between mb-8">
                                <div class="relative">
                                    <div class="w-20 h-20 bg-muted border-2 border-border group-hover/card:border-primary transition-colors flex items-center justify-center overflow-hidden">
                                        <img v-if="category.image_url" :src="category.image_url" class="w-full h-full object-cover" />
                                        <Layers v-else class="text-muted-foreground/20" :size="40" />
                                    </div>
                                    <div v-if="category.parent" class="absolute -top-3 -right-3 bg-foreground text-background text-[8px] font-black px-2 py-1 uppercase tracking-tighter flex items-center gap-1 border border-background">
                                        SUB
                                    </div>
                                </div>

                                <div v-if="can_manage" class="flex flex-col gap-2">
                                    <Link :href="route('admin.categories.sku-order', category.id)" 
                                          class="p-2.5 bg-muted text-muted-foreground hover:bg-primary hover:text-primary-foreground transition-all"
                                          title="ORDENAR GÓNDOLA">
                                        <ArrowUpDown :size="16" />
                                    </Link>
                                    <Link :href="route('admin.categories.edit', category.id)" 
                                          class="p-2.5 bg-muted text-muted-foreground hover:bg-foreground hover:text-background transition-all">
                                        <Settings :size="16" />
                                    </Link>
                                    <button @click="deleteCategory(category)" 
                                            class="p-2.5 bg-muted text-muted-foreground hover:bg-destructive hover:text-destructive-foreground transition-all">
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </div>

                            <div class="mb-6">
                                <p v-if="category.parent" class="text-[8px] font-mono text-primary font-bold uppercase mb-1 flex items-center gap-1">
                                    {{ category.parent.name }} <ChevronRight :size="8" />
                                </p>
                                <p v-else class="text-[8px] font-mono text-muted-foreground uppercase mb-1 italic">
                                    Categoría Raíz
                                </p>

                                <h3 class="text-2xl font-black italic uppercase tracking-tighter text-foreground group-hover/card:text-primary transition-colors leading-none">
                                    {{ category.name }}
                                </h3>
                            </div>

                            <p class="text-xs text-muted-foreground line-clamp-2 min-h-[40px] mb-8 font-medium leading-relaxed">
                                {{ category.description || 'SIN ESPECIFICACIÓN TÉCNICA REGISTRADA.' }}
                            </p>

                            <div class="mt-auto pt-6 border-t border-border/50 flex flex-wrap gap-2">
                                <span class="flex items-center gap-1.5 px-3 py-1.5 bg-muted rounded-none text-[9px] font-black uppercase tracking-tighter border-r-2 border-primary/30">
                                    <Hash :size="10" /> {{ category.external_code || '---' }}
                                </span>
                                
                                <span v-if="category.requires_age_check" 
                                      class="flex items-center gap-1.5 px-3 py-1.5 bg-warning/10 text-warning-foreground text-[9px] font-black uppercase tracking-tighter">
                                    <ShieldAlert :size="10" /> +18
                                </span>

                                <span v-if="category.tax_classification" 
                                      class="flex items-center gap-1.5 px-3 py-1.5 bg-success/10 text-success text-[9px] font-black uppercase tracking-tighter">
                                    <ReceiptText :size="10" /> {{ category.tax_classification }}
                                </span>
                                
                                <span :class="[
                                    'ml-auto flex items-center gap-1 text-[9px] font-black uppercase tracking-widest',
                                    category.is_active ? 'text-success' : 'text-destructive'
                                ]">
                                    <component :is="category.is_active ? Wifi : WifiOff" :size="10" />
                                    {{ category.is_active ? 'Active' : 'Offline' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>
            </div>

            <div v-else class="py-40 text-center border-4 border-dashed border-border group">
                <PackageSearch :size="64" class="mx-auto text-muted-foreground/20 mb-6 group-hover:scale-110 transition-transform duration-500" />
                <h3 class="text-xl font-black uppercase italic tracking-tighter text-muted-foreground">Radar sin registros activos</h3>
                <p class="text-xs font-mono text-muted-foreground/60 mt-2 uppercase tracking-widest">Ajuste los parámetros de búsqueda o cree un nuevo nodo</p>
                <button v-if="search" @click="resetRadar" 
                        class="mt-8 text-[10px] font-black uppercase tracking-[0.3em] text-primary hover:underline">
                    Reiniciar sistemas de búsqueda
                </button>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* PROTOCOLO DE ANIMACIÓN LIST-REACTIVE */
.list-move, .list-enter-active, .list-leave-active { transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1); }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(40px) scale(0.9); filter: blur(10px); }
.list-leave-active { position: absolute; width: 100%; }

.shadow-f1-glow { box-shadow: 0 0 30px rgba(var(--primary-rgb), 0.2); }

/* OCULTAR SCROLLBAR PARA FONTS MONO */
input::-webkit-inner-spin-button, input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>