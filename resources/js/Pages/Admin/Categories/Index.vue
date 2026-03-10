<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Plus, Layers, Eye, EyeOff, Settings, Trash2, 
    ShieldAlert, ReceiptText, Info, Wifi, WifiOff, 
    Hash, Palette, Image as ImageIcon
} from 'lucide-vue-next';

const props = defineProps({
    categories: [Array, Object],
    filters: Object,
    can_manage: Boolean
});

// --- ESTADO ---
const search = ref(props.filters.search || '');
const hoveredCategory = ref(null);

// --- NORMALIZACIÓN ---
const categoriesList = computed(() => {
    return Array.isArray(props.categories) ? props.categories : (props.categories.data || []);
});

// --- FILTRADO LOCAL (rápido) ---
const filteredCategories = computed(() => {
    if (!search.value) return categoriesList.value;
    const term = search.value.toLowerCase();
    return categoriesList.value.filter(c => 
        c.name.toLowerCase().includes(term) || 
        (c.external_code && c.external_code.toLowerCase().includes(term))
    );
});

// --- ESTADÍSTICAS ---
const stats = computed(() => {
    const total = categoriesList.value.length;
    const active = categoriesList.value.filter(c => c.is_active).length;
    const ageRestricted = categoriesList.value.filter(c => c.requires_age_check).length;
    
    return [
        { label: 'Total categorías', value: total, icon: Layers },
        { label: 'Activas', value: active, icon: Wifi },
        { label: '+18', value: ageRestricted, icon: ShieldAlert },
    ];
});

// --- ACCIONES ---
const deleteCategory = (category) => {
    if (confirm(`¿Eliminar la categoría "${category.name}"? Se validará que no tenga productos vinculados.`)) {
        router.delete(route('admin.categories.destroy', category.id), { preserveScroll: true });
    }
};

// --- BÚSQUEDA EN SERVIDOR (debounced) ---
const updateFilters = debounce(() => {
    router.get(route('admin.categories.index'), { search: search.value }, { preserveState: true, replace: true, preserveScroll: true });
}, 300);

watch(search, updateFilters);

// --- UTILIDAD ---
const clearSearch = () => {
    search.value = '';
};
</script>

<template>
    <AdminLayout>
        <Head title="Categorías" />

        <div class="max-w-7xl mx-auto pb-24 px-4 sm:px-6">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-foreground tracking-tight">
                        Categorías
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Gestión plana de categorías del catálogo
                    </p>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <!-- Buscador -->
                    <div class="relative flex-1 md:w-80">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                        <input 
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nombre o código..."
                            class="w-full pl-10 pr-10 py-2.5 bg-background border border-border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                        />
                        <button v-if="search" @click="clearSearch" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                            ✕
                        </button>
                    </div>
                    <!-- Botón nueva categoría (visible en desktop) -->
                    <Link v-if="can_manage" 
                          :href="route('admin.categories.create')" 
                          class="hidden md:inline-flex bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-primary/90 transition-all items-center gap-2">
                        <Plus :size="18" />
                        <span>Nueva categoría</span>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div v-for="(stat, index) in stats" :key="index" 
                     class="border border-border/50 p-4 relative group/stat bg-background/50">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-primary/10 text-primary">
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
            <!-- Grid de categorías -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <TransitionGroup name="list">
                    <div v-for="category in filteredCategories" :key="category.id" 
                         @mouseenter="hoveredCategory = category.id"
                         @mouseleave="hoveredCategory = null"
                         class="border border-border/50 bg-background hover:border-primary/30 hover:shadow-neon-primary transition-all duration-500 relative group/card flex flex-col overflow-hidden">
                        
                        <div class="h-1 w-full" :style="{ backgroundColor: category.bg_color || '#3b82f6' }"></div>

                        <div class="p-5 flex-1 flex flex-col relative z-10">
                            <!-- Cabecera con imagen y acciones -->
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="w-14 h-14 bg-primary/5 rounded-lg overflow-hidden flex items-center justify-center border border-border">
                                    <img v-if="category.image_url" 
                                         :src="category.image_url" 
                                         class="w-full h-full object-cover"
                                         alt="" />
                                    <Layers v-else :size="24" class="text-primary/30" />
                                </div>

                                <div v-if="can_manage" class="flex gap-1">
                                    <Link :href="route('admin.categories.edit', category.id)" 
                                          class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"
                                          title="Editar">
                                        <Settings :size="16" />
                                    </Link>
                                    <button @click="deleteCategory(category)" 
                                            class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/5 rounded-lg transition-colors"
                                            title="Eliminar">
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </div>

                            <!-- Nombre y estado -->
                            <div class="mb-2">
                                <h3 class="text-lg font-semibold text-foreground truncate">{{ category.name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <component :is="category.is_active ? Wifi : WifiOff" 
                                               :size="14" 
                                               :class="category.is_active ? 'text-success' : 'text-destructive'" />
                                    <span class="text-xs" :class="category.is_active ? 'text-success' : 'text-destructive'">
                                        {{ category.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <p class="text-sm text-muted-foreground line-clamp-2 mb-4">
                                {{ category.description || 'Sin descripción' }}
                            </p>

                            <!-- Etiquetas (código, +18, impuestos) -->
                            <div class="flex flex-wrap gap-2 pt-4 border-t border-border">
                                <div class="inline-flex items-center gap-1 px-2 py-1 bg-muted/30 rounded-md text-xs">
                                    <Hash :size="12" class="text-muted-foreground" />
                                    <span>{{ category.external_code || 'S/C' }}</span>
                                </div>
                                <div v-if="category.requires_age_check" 
                                     class="inline-flex items-center gap-1 px-2 py-1 bg-warning/10 text-warning rounded-md text-xs font-medium">
                                    <ShieldAlert :size="12" />
                                    <span>+18</span>
                                </div>
                                <div v-if="category.tax_classification" 
                                     class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-500/10 text-emerald-500 rounded-md text-xs">
                                    <ReceiptText :size="12" />
                                    <span>{{ category.tax_classification }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>
            </div>

            <!-- Estado vacío -->
            <div v-if="filteredCategories.length === 0" 
                 class="bg-card border border-dashed border-border rounded-xl p-12 text-center mt-8">
                <div class="w-16 h-16 bg-muted/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Layers :size="24" class="text-muted-foreground" />
                </div>
                <h3 class="text-lg font-semibold text-foreground mb-2">No hay categorías</h3>
                <p class="text-sm text-muted-foreground max-w-md mx-auto">
                    {{ search ? 'No se encontraron resultados para tu búsqueda.' : 'Comienza creando tu primera categoría.' }}
                </p>
                <button v-if="search" @click="clearSearch" 
                        class="mt-4 text-sm text-primary hover:text-primary/80 transition-colors">
                    Limpiar búsqueda
                </button>
            </div>

            <!-- Botón flotante móvil -->
            <Teleport to="body">
                <Link v-if="can_manage" 
                      :href="route('admin.categories.create')" 
                      class="fixed bottom-8 right-8 z-[9999] group/create">
                    <div class="w-14 h-14 bg-primary text-primary-foreground border border-primary-foreground/50 shadow-neon-primary flex items-center justify-center relative overflow-hidden">
                        <Plus :size="24" class="group-hover/create:rotate-90 transition-transform duration-500 relative z-10" />
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/create:translate-y-0 transition-transform duration-500"></span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                    </div>
                </Link>
            </Teleport>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Transiciones para el TransitionGroup */
.list-move,
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
.list-leave-active {
    position: absolute;
}
.shadow-neon-primary {
    box-shadow: 0 0 15px hsl(var(--primary) / 0.3);
}

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
</style>