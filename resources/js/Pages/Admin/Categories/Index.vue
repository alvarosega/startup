<!-- resources/js/Pages/Admin/Categories/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SearchInput from '@/Components/SearchInput.vue';
import CategoryCard from '@/Components/CategoryCard.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Plus, FolderOpen, ChevronRight, 
    Folder, Layers, BarChart3, Filter, Grid, 
    List, TrendingUp, AlertCircle, Hash, Users,
    Download, Eye, EyeOff, Settings, Shield
} from 'lucide-vue-next';

const props = defineProps({
    categories: Array,
    filters: Object,
    can_manage: Boolean
});

const search = ref(props.filters.search || '');
const selectedParentId = ref(null);
const viewMode = ref('grid'); // 'grid' o 'list'
const showInactive = ref(true);

// --- TRANSFORMACIÓN DE DATOS (Flat -> Tree) ---
const treeData = computed(() => {
    const parents = props.categories.filter(c => !c.parent_id);
    
    return parents.map(parent => ({
        ...parent,
        children: props.categories.filter(c => c.parent_id === parent.id)
    }));
});

// --- BÚSQUEDA MEJORADA ---
const filteredTree = computed(() => {
    let result = treeData.value;
    
    // Filtro de búsqueda
    if (search.value) {
        const term = search.value.toLowerCase();
        result = result.filter(parent => {
            const matchParent = parent.name.toLowerCase().includes(term) || 
                               (parent.external_code && parent.external_code.toLowerCase().includes(term));
            
            const matchChild = parent.children.some(child => 
                child.name.toLowerCase().includes(term) || 
                (child.external_code && child.external_code.toLowerCase().includes(term))
            );

            return matchParent || matchChild;
        });
    }
    
    // Filtro de inactivos
    if (!showInactive.value) {
        result = result.filter(parent => parent.is_active);
    }
    
    return result;
});

// --- LÓGICA DE SELECCIÓN ---
const activeParent = computed(() => {
    return filteredTree.value.find(p => p.id === selectedParentId.value) || null;
});

const selectParent = (id) => {
    selectedParentId.value = id;
};

// --- ESTADÍSTICAS MEJORADAS ---
const stats = computed(() => {
    const total = props.categories.length;
    const parents = props.categories.filter(c => !c.parent_id).length;
    const children = total - parents;
    const active = props.categories.filter(c => c.is_active).length;
    const featured = props.categories.filter(c => c.is_featured).length;
    const withAgeRestriction = props.categories.filter(c => c.requires_age_check).length;
    const withImages = props.categories.filter(c => c.image_url).length;
    
    return { 
        total, parents, children, active, 
        featured, withAgeRestriction, withImages,
        inactive: total - active
    };
});

// --- FUNCIONES ---
const deleteCategory = (category) => {
    if (confirm(`¿Eliminar "${category.name}" y ${category.children?.length || 0} subcategorías?`)) {
        router.delete(route('admin.categories.destroy', category.id), {
            onSuccess: () => {
                if (selectedParentId.value === category.id) {
                    selectedParentId.value = filteredTree.value.length > 0 ? filteredTree.value[0].id : null;
                }
            }
        });
    }
};

// Watcher para búsqueda
watch(search, debounce((val) => {
    router.get(route('admin.categories.index'), { 
        search: val,
        show_inactive: showInactive.value
    }, { 
        preserveState: true, 
        replace: true,
        preserveScroll: true
    });
}, 300));

// Seleccionar automáticamente el primero
onMounted(() => {
    if (filteredTree.value.length > 0 && !selectedParentId.value) {
        selectedParentId.value = filteredTree.value[0].id;
    }
});
</script>

<template>
    <AdminLayout>
        <!-- Header con estadísticas -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-display font-semibold text-foreground tracking-tight">
                        Gestión de Categorías
                    </h1>
                    <p class="text-muted-foreground text-sm mt-1">
                        Organiza jerárquicamente tu catálogo de productos
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <div class="w-full md:w-64">
                        <SearchInput v-model="search" placeholder="Buscar por nombre o código..." />
                    </div>

                    <Link v-if="can_manage" :href="route('admin.categories.create')" 
                          class="btn btn-primary shadow-md hover:shadow-lg">
                        <Plus :size="18" />
                        <span>Nueva Categoría</span>
                    </Link>
                </div>
            </div>

            <!-- Panel de estadísticas -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
                <div class="card !p-4 bg-gradient-to-br from-primary/5 to-primary/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-primary uppercase tracking-wide">Total</p>
                            <p class="text-2xl font-display font-bold text-foreground mt-1">{{ stats.total }}</p>
                        </div>
                        <div class="p-2 rounded-full bg-primary/20 text-primary">
                            <Layers :size="20" />
                        </div>
                    </div>
                </div>

                <div class="card !p-4 bg-gradient-to-br from-success/5 to-success/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-success uppercase tracking-wide">Activas</p>
                            <p class="text-2xl font-display font-bold text-foreground mt-1">{{ stats.active }}</p>
                        </div>
                        <div class="p-2 rounded-full bg-success/20 text-success">
                            <Eye :size="20" />
                        </div>
                    </div>
                </div>

                <div class="card !p-4 bg-gradient-to-br from-secondary/5 to-secondary/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-secondary uppercase tracking-wide">Padres</p>
                            <p class="text-2xl font-display font-bold text-foreground mt-1">{{ stats.parents }}</p>
                        </div>
                        <div class="p-2 rounded-full bg-secondary/20 text-secondary">
                            <Folder :size="20" />
                        </div>
                    </div>
                </div>

                <div class="card !p-4 bg-gradient-to-br from-warning/5 to-warning/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-warning uppercase tracking-wide">Destacadas</p>
                            <p class="text-2xl font-display font-bold text-foreground mt-1">{{ stats.featured }}</p>
                        </div>
                        <div class="p-2 rounded-full bg-warning/20 text-warning">
                            <TrendingUp :size="20" />
                        </div>
                    </div>
                </div>

                <div class="card !p-4 bg-gradient-to-br from-error/5 to-error/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-error uppercase tracking-wide">+18</p>
                            <p class="text-2xl font-display font-bold text-foreground mt-1">{{ stats.withAgeRestriction }}</p>
                        </div>
                        <div class="p-2 rounded-full bg-error/20 text-error">
                            <AlertCircle :size="20" />
                        </div>
                    </div>
                </div>

                <div class="card !p-4 bg-gradient-to-br from-info/5 to-info/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-info uppercase tracking-wide">Con Imagen</p>
                            <p class="text-2xl font-display font-bold text-foreground mt-1">{{ stats.withImages }}</p>
                        </div>
                        <div class="p-2 rounded-full bg-info/20 text-info">
                            <BarChart3 :size="20" />
                        </div>
                    </div>
                </div>

                <div class="card !p-4 bg-gradient-to-br from-muted to-muted/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Inactivas</p>
                            <p class="text-2xl font-display font-bold text-foreground mt-1">{{ stats.inactive }}</p>
                        </div>
                        <div class="p-2 rounded-full bg-muted text-muted-foreground">
                            <EyeOff :size="20" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor principal de dos paneles -->
        <div class="flex flex-col lg:flex-row gap-6">
            
            <!-- Panel izquierdo: Navegación de categorías padre -->
            <div class="lg:w-1/3 xl:w-1/4">
                <div class="card sticky top-6 overflow-hidden border-border/50">
                    <div class="card-header !pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h2 class="font-display font-semibold text-lg text-foreground">
                                    Navegación
                                </h2>
                                <p class="text-xs text-muted-foreground mt-1">{{ filteredTree.length }} categorías padre</p>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <button @click="showInactive = !showInactive" 
                                        class="btn btn-ghost btn-sm !p-2"
                                        :class="showInactive ? 'text-success' : 'text-muted-foreground'"
                                        title="Mostrar/ocultar inactivas">
                                    <Eye v-if="showInactive" :size="16" />
                                    <EyeOff v-else :size="16" />
                                </button>
                                
                                <Link v-if="can_manage" :href="route('admin.categories.create')" 
                                      class="btn btn-primary btn-sm !px-3 shadow-sm"
                                      title="Nueva categoría padre">
                                    <Plus :size="16" />
                                </Link>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="space-y-3">
                            <SearchInput v-model="search" placeholder="Filtrar categorías..." />
                            
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-muted-foreground">
                                    Mostrando {{ filteredTree.length }} de {{ treeData.length }}
                                </span>
                                <button class="text-primary hover:underline font-medium">
                                    <Filter :size="12" class="inline mr-1" />
                                    Filtros avanzados
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de categorías padre -->
                    <div class="max-h-[calc(100vh-400px)] overflow-y-auto scrollbar-thin p-2">
                        <div v-for="parent in filteredTree" :key="parent.id"
                             @click="selectParent(parent.id)"
                             class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-all duration-200 mb-2 group/item"
                             :class="selectedParentId === parent.id 
                                ? 'bg-gradient-to-r from-primary/10 to-primary/5 border border-primary/20 shadow-sm' 
                                : 'hover:bg-muted/50 border border-transparent hover:border-input'">
                            
                            <!-- Ícono/Imagen -->
                            <div class="relative">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary/10 to-secondary/10 border border-input overflow-hidden flex items-center justify-center group-hover/item:scale-105 transition-transform">
                                    <img v-if="parent.image_url" 
                                         :src="parent.image_url" 
                                         class="w-full h-full object-cover">
                                    <Folder v-else :size="20" class="text-primary" />
                                </div>
                                
                                <!-- Indicador de restricción de edad -->
                                <div v-if="parent.requires_age_check" 
                                     class="absolute -top-1 -right-1 w-5 h-5 bg-error rounded-full flex items-center justify-center ring-2 ring-background">
                                    <span class="text-[8px] font-bold text-error-foreground">18</span>
                                </div>
                            </div>

                            <!-- Información -->
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-medium text-foreground truncate"
                                        :class="selectedParentId === parent.id ? 'text-primary font-semibold' : ''">
                                        {{ parent.name }}
                                    </h3>
                                    <ChevronRight v-if="selectedParentId === parent.id" 
                                                  :size="16" 
                                                  class="text-primary shrink-0 ml-2 transform group-hover/item:translate-x-1 transition-transform" />
                                </div>
                                
                                <div class="flex items-center gap-3 text-xs text-muted-foreground mt-1">
                                    <span class="flex items-center gap-1">
                                        <FolderOpen :size="12" />
                                        {{ parent.children.length }} subcat.
                                    </span>
                                    
                                    <span v-if="parent.external_code" class="font-mono bg-muted px-1.5 py-0.5 rounded">
                                        {{ parent.external_code }}
                                    </span>
                                    
                                    <span v-if="!parent.is_active" class="text-error font-bold">
                                        INACTIVO
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Estado vacío -->
                        <div v-if="filteredTree.length === 0" class="text-center py-12 px-4">
                            <Search :size="48" class="mx-auto mb-4 text-muted-foreground/20" />
                            <p class="font-medium text-foreground mb-2">No se encontraron categorías</p>
                            <p class="text-sm text-muted-foreground mb-4">
                                {{ search ? 'Intenta con otro término de búsqueda' : 'No hay categorías padre registradas' }}
                            </p>
                            <Link v-if="can_manage" :href="route('admin.categories.create')" 
                                  class="btn btn-outline btn-sm">
                                Crear primera categoría
                            </Link>
                        </div>
                    </div>
                    
                    <!-- Footer del panel -->
                    <div class="p-4 border-t border-border/30 bg-muted/20">
                        <div class="text-xs text-muted-foreground flex items-center justify-between">
                            <span>Total: {{ props.categories.length }} categorías</span>
                            <Link :href="route('admin.categories.index')" 
                                  class="text-primary hover:underline font-medium">
                                Ver todas
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel derecho: Detalles y subcategorías -->
            <div class="lg:w-2/3 xl:w-3/4">
                <Transition name="fade" mode="out-in">
                    <!-- Vista con categoría seleccionada -->
                    <div v-if="activeParent" :key="activeParent.id" class="space-y-6 animate-in">
                        <!-- Header de categoría padre -->
                        <div class="card overflow-hidden border-border/50 relative">
                            <div class="absolute top-0 right-0 p-6 opacity-5 pointer-events-none">
                                <FolderOpen :size="140" />
                            </div>

                            <div class="card-header !pb-0">
                                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                                    <div class="flex items-start gap-4">
                                        <!-- Imagen -->
                                        <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-primary/10 to-secondary/10 border-2 border-input overflow-hidden flex items-center justify-center shrink-0">
                                            <img v-if="activeParent.image_url" 
                                                 :src="activeParent.image_url" 
                                                 class="w-full h-full object-cover">
                                            <FolderOpen v-else :size="32" class="text-primary/40" />
                                        </div>

                                        <!-- Información -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                                <span class="badge badge-primary font-bold">
                                                    <Shield :size="12" class="mr-1" />
                                                    Categoría Padre
                                                </span>
                                                
                                                <span v-if="activeParent.is_active" 
                                                      class="badge badge-success font-bold">
                                                    <Eye :size="12" class="mr-1" />
                                                    ACTIVO
                                                </span>
                                                
                                                <span v-else 
                                                      class="badge badge-error font-bold">
                                                    <EyeOff :size="12" class="mr-1" />
                                                    INACTIVO
                                                </span>
                                                
                                                <span v-if="activeParent.is_featured" 
                                                      class="badge badge-warning font-bold">
                                                    <Star :size="12" class="mr-1" />
                                                    DESTACADO
                                                </span>
                                                
                                                <span v-if="activeParent.requires_age_check" 
                                                      class="badge badge-error font-bold">
                                                    <AlertCircle :size="12" class="mr-1" />
                                                    RESTRICCIÓN +18
                                                </span>
                                            </div>
                                            
                                            <h1 class="text-2xl md:text-3xl font-display font-bold text-foreground tracking-tight mb-2">
                                                {{ activeParent.name }}
                                            </h1>
                                            
                                            <div class="flex flex-wrap items-center gap-4 text-sm">
                                                <div class="flex items-center gap-2 text-muted-foreground">
                                                    <Hash :size="14" />
                                                    <span class="font-mono font-bold text-foreground">
                                                        {{ activeParent.external_code || 'Sin código' }}
                                                    </span>
                                                </div>
                                                
                                                <div class="flex items-center gap-2 text-muted-foreground">
                                                    <Globe :size="14" />
                                                    <span class="font-medium">
                                                        /{{ activeParent.slug }}
                                                    </span>
                                                </div>
                                                
                                                <div class="flex items-center gap-2 text-muted-foreground">
                                                    <Layers :size="14" />
                                                    <span class="font-medium">
                                                        {{ activeParent.children.length }} subcategorías
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Acciones -->
                                    <div v-if="can_manage" class="flex flex-col sm:flex-row gap-2 shrink-0">
                                        <Link :href="route('admin.categories.create', { parent: activeParent.id })" 
                                              class="btn btn-outline btn-sm">
                                            + Subcategoría
                                        </Link>
                                        
                                        <Link :href="route('admin.categories.edit', activeParent.id)" 
                                              class="btn btn-primary btn-sm shadow-sm">
                                            <Settings :size="14" class="mr-2" />
                                            Editar
                                        </Link>
                                        
                                        <button @click="deleteCategory(activeParent)" 
                                                class="btn btn-error btn-sm">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>

                                <!-- Descripción y SEO -->
                                <div class="mt-6 pt-6 border-t border-border/30">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <!-- Descripción -->
                                        <div v-if="activeParent.description">
                                            <h3 class="text-sm font-medium text-foreground mb-2 flex items-center gap-2">
                                                <FileText :size="14" />
                                                Descripción
                                            </h3>
                                            <p class="text-sm text-muted-foreground leading-relaxed">
                                                {{ activeParent.description }}
                                            </p>
                                        </div>

                                        <!-- SEO -->
                                        <div v-if="activeParent.seo_title || activeParent.seo_description">
                                            <h3 class="text-sm font-medium text-foreground mb-2 flex items-center gap-2">
                                                <TrendingUp :size="14" class="text-success" />
                                                Optimización SEO
                                            </h3>
                                            
                                            <div class="space-y-2">
                                                <div v-if="activeParent.seo_title">
                                                    <div class="text-xs text-muted-foreground">Meta Título</div>
                                                    <div class="text-sm font-medium text-foreground line-clamp-2">
                                                        {{ activeParent.seo_title }}
                                                    </div>
                                                </div>
                                                
                                                <div v-if="activeParent.seo_description">
                                                    <div class="text-xs text-muted-foreground">Meta Descripción</div>
                                                    <div class="text-sm text-muted-foreground line-clamp-3">
                                                        {{ activeParent.seo_description }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subcategorías -->
                        <div class="card border-border/50">
                            <div class="card-header">
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                    <div>
                                        <h2 class="font-display font-semibold text-xl text-foreground flex items-center gap-2">
                                            Subcategorías
                                            <span class="badge badge-outline font-bold">
                                                {{ activeParent.children.length }}
                                            </span>
                                        </h2>
                                        <p class="text-sm text-muted-foreground mt-1">
                                            Organizadas bajo "{{ activeParent.name }}"
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center gap-3">
                                        <!-- Toggle de vista -->
                                        <div class="flex border border-input rounded-lg overflow-hidden">
                                            <button @click="viewMode = 'grid'" 
                                                    class="p-2 transition-colors"
                                                    :class="viewMode === 'grid' ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
                                                    title="Vista de cuadrícula">
                                                <Grid :size="16" />
                                            </button>
                                            <button @click="viewMode = 'list'" 
                                                    class="p-2 transition-colors"
                                                    :class="viewMode === 'list' ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
                                                    title="Vista de lista">
                                                <List :size="16" />
                                            </button>
                                        </div>
                                        
                                        <!-- Acciones -->
                                        <Link v-if="can_manage" 
                                              :href="route('admin.categories.create', { parent: activeParent.id })" 
                                              class="btn btn-primary btn-sm shadow-sm">
                                            <Plus :size="14" class="mr-2" />
                                            Nueva Subcategoría
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenido de subcategorías -->
                            <div class="card-content !pt-0">
                                <!-- Grid/List de subcategorías -->
                                <div v-if="activeParent.children.length > 0" 
                                     :class="[
                                        'transition-all duration-300',
                                        viewMode === 'grid' 
                                            ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4' 
                                            : 'space-y-3'
                                     ]">
                                    
                                    <CategoryCard v-for="child in activeParent.children" 
                                                 :key="child.id"
                                                 :category="child"
                                                 :can-manage="can_manage"
                                                 @delete="deleteCategory" 
                                                 :class="viewMode === 'list' ? '!flex-row h-auto items-center' : ''" />
                                </div>

                                <!-- Estado vacío para subcategorías -->
                                <div v-else class="text-center py-12">
                                    <div class="max-w-md mx-auto">
                                        <FolderOpen :size="64" class="mx-auto mb-6 text-muted-foreground/20" />
                                        <h3 class="font-display font-semibold text-xl text-foreground mb-3">
                                            No hay subcategorías
                                        </h3>
                                        <p class="text-muted-foreground mb-6">
                                            Las subcategorías te permiten organizar productos de manera más específica dentro de esta categoría.
                                        </p>
                                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                            <Link v-if="can_manage" 
                                                  :href="route('admin.categories.create', { parent: activeParent.id })" 
                                                  class="btn btn-primary">
                                                <Plus :size="16" class="mr-2" />
                                                Crear primera subcategoría
                                            </Link>
                                            
                                            <Link :href="route('admin.categories.index')" 
                                                  class="btn btn-outline">
                                                Ver todas las categorías
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado sin selección -->
                    <div v-else class="card text-center py-16 border-border/50">
                        <div class="max-w-md mx-auto">
                            <FolderOpen :size="80" class="mx-auto mb-8 text-muted-foreground/10" />
                            <h2 class="text-2xl font-display font-semibold text-foreground mb-4">
                                Selecciona una categoría
                            </h2>
                            <p class="text-muted-foreground mb-8 leading-relaxed">
                                Elige una categoría padre del panel izquierdo para ver sus detalles completos, 
                                gestionar sus subcategorías y configurar su visibilidad.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                <Link v-if="can_manage" :href="route('admin.categories.create')" 
                                      class="btn btn-primary shadow-md">
                                    <Plus :size="18" class="mr-2" />
                                    Crear nueva categoría
                                </Link>
                                
                                <button @click="selectedParentId = filteredTree[0]?.id" 
                                        v-if="filteredTree.length > 0"
                                        class="btn btn-outline">
                                    Seleccionar primera categoría
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { 
    transition: opacity 0.3s var(--ease-smooth), transform 0.3s var(--ease-elastic); 
}
.fade-enter-from, .fade-leave-to { 
    opacity: 0; 
    transform: translateY(20px) scale(0.98); 
}
</style>