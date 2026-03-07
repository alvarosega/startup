<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Plus, Layers, Eye, EyeOff, Settings, Trash2, 
    ShieldAlert, ReceiptText, Info, Cpu, Terminal, 
    Wifi, WifiOff, Zap, Hash, Palette, Image as ImageIcon
} from 'lucide-vue-next';

const props = defineProps({
    categories: [Array, Object], // Soporte para Resource Wrapper
    filters: Object,
    can_manage: Boolean
});

// --- ESTADO ---
const search = ref(props.filters.search || '');
const hoveredCategory = ref(null);

// --- NORMALIZACIÓN Y DESEMPAQUETADO (Regla 3.C) ---
const categoriesList = computed(() => {
    return Array.isArray(props.categories) ? props.categories : (props.categories.data || []);
});

// --- FILTRADO CLIENT-SIDE (Rendimiento Extremo) ---
const filteredCategories = computed(() => {
    if (!search.value) return categoriesList.value;
    const term = search.value.toLowerCase();
    return categoriesList.value.filter(c => 
        c.name.toLowerCase().includes(term) || 
        (c.external_code && c.external_code.toLowerCase().includes(term))
    );
});

// --- KPIS PLANOS ---
const statsList = computed(() => [
    { 
        label: 'CATEGORÍAS_TOTALES', 
        value: String(categoriesList.value.length).padStart(2, '0'), 
        icon: Layers, 
        color: 'text-primary', 
        bg: 'bg-primary/10'
    },
    { 
        label: 'NODOS_ACTIVOS', 
        value: String(categoriesList.value.filter(c => c.is_active).length).padStart(2, '0'), 
        icon: Wifi, 
        color: 'text-cyan-500', 
        bg: 'bg-cyan-500/10'
    },
    { 
        label: 'RESTRICCIÓN_EDAD', 
        value: String(categoriesList.value.filter(c => c.requires_age_check).length).padStart(2, '0'), 
        icon: ShieldAlert, 
        color: 'text-warning', 
        bg: 'bg-warning/10'
    },
]);

// --- ACCIONES ---
const deleteCategory = (category) => {
    if (confirm(`¿CONFIRMAR ELIMINACIÓN // CATEGORÍA: "${category.name.toUpperCase()}"?\n\n[!] ADVERTENCIA: Solo se procederá si no existen productos vinculados.`)) {
        router.delete(route('admin.categories.destroy', category.id), { preserveScroll: true });
    }
};

// Sincronización con Servidor (Debounced)
const updateFilters = debounce(() => {
    router.get(route('admin.categories.index'), { 
        search: search.value 
    }, { preserveState: true, replace: true, preserveScroll: true });
}, 300);

watch(search, updateFilters);
</script>

<template>
    <AdminLayout>
        <Head title="Categorías Maestro" />

        <div class="max-w-7xl mx-auto pb-24 px-4 sm:px-6">
            
            <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-8 border-b border-primary/30 pb-6 relative group/header">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                        data-text="CATEGORÍAS MAESTRO">
                        CATEGORÍAS MAESTRO
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                        <Cpu :size="12" class="text-primary animate-pulse" />
                        DESCRIPTOR GLOBAL PLANO DE ENTIDADES
                        <Terminal :size="12" class="text-primary animate-pulse" />
                    </p>
                </div>
                
                <div class="relative group/search w-full md:w-80 z-10">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary transition-colors" :size="16" />
                    <input v-model="search" 
                           type="text" 
                           placeholder="> BUSCAR POR NOMBRE O CÓDIGO..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all uppercase">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 w-1 h-4 bg-primary animate-pulse"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div v-for="(stat, index) in statsList" :key="index" 
                     class="border border-border/50 p-4 relative group/stat bg-background/50">
                    <div class="flex items-center gap-4">
                        <div :class="`p-3 ${stat.bg} ${stat.color}`">
                            <component :is="stat.icon" :size="20" />
                        </div>
                        <div>
                            <p class="text-[8px] font-mono font-bold uppercase text-muted-foreground">{{ stat.label }}</p>
                            <p class="text-2xl font-mono font-black text-foreground mt-1">{{ stat.value }}</p>
                        </div>
                    </div>
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <TransitionGroup name="list">
                    <div v-for="category in filteredCategories" :key="category.id" 
                         @mouseenter="hoveredCategory = category.id"
                         @mouseleave="hoveredCategory = null"
                         class="border border-border/50 bg-background hover:border-primary/30 hover:shadow-neon-primary transition-all duration-500 relative group/card flex flex-col overflow-hidden">
                        
                        <div class="h-1 w-full" :style="{ backgroundColor: category.bg_color || '#3b82f6' }"></div>

                        <div class="p-5 flex-1 flex flex-col relative z-10">
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-14 h-14 border border-primary/20 bg-background overflow-hidden shrink-0 relative group/image">
                                    <img v-if="category.image_url" :src="category.image_url" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-primary/5">
                                        <Layers :size="20" class="text-primary/30" />
                                    </div>
                                    <div v-if="!category.is_active" class="absolute inset-0 bg-background/80 flex items-center justify-center">
                                        <WifiOff :size="16" class="text-destructive" />
                                    </div>
                                </div>

                                <div v-if="can_manage" class="flex gap-1">
                                    <Link :href="route('admin.categories.edit', category.id)" 
                                          class="w-8 h-8 flex items-center justify-center border border-border/50 hover:border-primary hover:text-primary transition-all">
                                        <Settings :size="14" />
                                    </Link>
                                    <button @click="deleteCategory(category)" 
                                            class="w-8 h-8 flex items-center justify-center border border-border/50 hover:border-destructive hover:text-destructive transition-all">
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </div>

                            <h3 class="text-lg font-mono font-bold text-foreground truncate group-hover/card:text-primary transition-colors mb-1">
                                {{ category.name }}
                            </h3>

                            <p class="text-[10px] font-mono text-muted-foreground line-clamp-2 h-8 mb-4">
                                {{ category.description || 'SIN DESCRIPCIÓN OPERATIVA.' }}
                            </p>

                            <div class="flex flex-wrap items-center gap-2 mt-auto pt-4 border-t border-border/50">
                                <div class="flex items-center gap-1 px-2 py-1 bg-muted/30 border border-border/50">
                                    <Hash :size="10" class="text-muted-foreground"/>
                                    <span class="text-[8px] font-mono text-foreground">{{ category.external_code || 'S/C' }}</span>
                                </div>
                                <div v-if="category.requires_age_check" class="flex items-center gap-1 px-2 py-1 bg-warning/10 border border-warning/30 text-warning">
                                    <ShieldAlert :size="10" />
                                    <span class="text-[8px] font-mono font-black">+18</span>
                                </div>
                                <div v-if="category.tax_classification" class="flex items-center gap-1 px-2 py-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-500">
                                    <ReceiptText :size="10" />
                                    <span class="text-[8px] font-mono">{{ category.tax_classification }}</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="hoveredCategory === category.id" 
                             class="absolute inset-0 border-2 border-primary/30 pointer-events-none animate-pulse"></div>
                    </div>
                </TransitionGroup>
            </div>

            <div v-if="filteredCategories.length === 0" class="border border-dashed border-primary/30 p-16 text-center mt-8">
                <div class="w-16 h-16 mx-auto mb-4 border-2 border-dashed border-primary/30 flex items-center justify-center bg-primary/5">
                    <Layers :size="24" class="text-primary/50" />
                </div>
                <h3 class="text-sm font-mono font-bold text-foreground uppercase">Sin registros en el radar</h3>
                <p class="text-[10px] font-mono text-muted-foreground mt-2 max-w-xs mx-auto uppercase">
                    {{ search ? 'Ajusta los parámetros de búsqueda.' : 'Inicia la carga de categorías para habilitar el catálogo.' }}
                </p>
            </div>

            <Teleport to="body">
                <Link v-if="can_manage" 
                      :href="route('admin.categories.create')" 
                      class="fixed bottom-8 right-8 z-[9999] group/create">
                    <div class="w-14 h-14 bg-primary text-primary-foreground border border-primary-foreground/50 shadow-neon-primary flex items-center justify-center relative overflow-hidden">
                        <Plus :size="24" class="group-hover/create:rotate-90 transition-transform duration-500 relative z-10" />
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/create:translate-y-0 transition-transform duration-500"></span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                    </div>
                </Link>
            </Teleport>

            <div class="mt-12 text-center opacity-30">
                <p class="text-[7px] font-mono text-muted-foreground">
                    NODE_ID: CAT_FLAT_MASTER // SYSTEM_VERSION: 2.0_SENIOR // TIMESTAMP: {{ new Date().toISOString() }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
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
.glitch-text::before { color: #0ff; z-index: -1; animation: glitch-anim-1 0.4s infinite linear alternate-reverse; }
.glitch-text::after { color: #f0f; z-index: -2; animation: glitch-anim-2 0.4s infinite linear alternate-reverse; }

@keyframes glitch-skew {
    0%, 20%, 22%, 80%, 82%, 100% { transform: skew(0deg); }
    21% { transform: skew(2deg); }
    81% { transform: skew(-2deg); }
}

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); }
    100% { clip-path: inset(40% 0 20% 0); }
}

@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); }
    100% { clip-path: inset(30% 0 40% 0); }
}

.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }

.list-move, .list-enter-active, .list-leave-active { transition: all 0.4s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(20px); }
.list-leave-active { position: absolute; }
</style>