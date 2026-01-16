<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, computed, watch, onMounted } from 'vue';
    
    // Iconos
    import { 
        Search, Plus, FolderOpen, Image as ImageIcon, 
        Edit, Trash2, ChevronRight, CornerDownRight,
        Package, Hash, Layers
    } from 'lucide-vue-next';

    const props = defineProps({
        categories: Array,
        filters: Object,
        can_manage: Boolean // <--- RECIBE EL PERMISO DEL BACKEND
    });

    const search = ref('');
    const selectedParentId = ref(null);

    // --- 1. TRANSFORMACIÓN DE DATOS (Flat -> Tree) ---
    const treeData = computed(() => {
        const parents = props.categories.filter(c => !c.parent_id);
        
        return parents.map(parent => {
            return {
                ...parent,
                children: props.categories.filter(c => c.parent_id === parent.id)
            };
        });
    });

    // --- 2. BÚSQUEDA "PRO" (Filtrado en Cliente) ---
    const filteredTree = computed(() => {
        if (!search.value) return treeData.value;
        
        const term = search.value.toLowerCase();
        
        return treeData.value.filter(parent => {
            // Coincide el Padre...
            const matchParent = parent.name.toLowerCase().includes(term) || 
                                (parent.external_code && parent.external_code.toLowerCase().includes(term));
            
            // ...O coincide alguno de sus Hijos
            const matchChild = parent.children.some(child => 
                child.name.toLowerCase().includes(term) || 
                (child.external_code && child.external_code.toLowerCase().includes(term))
            );

            return matchParent || matchChild;
        });
    });

    // --- 3. LÓGICA DE SELECCIÓN ---
    const activeParent = computed(() => {
        return filteredTree.value.find(p => p.id === selectedParentId.value) || null;
    });

    const selectParent = (id) => {
        selectedParentId.value = id;
    };

    // Seleccionar automáticamente el primero si no hay selección
    onMounted(() => {
        if (filteredTree.value.length > 0 && !selectedParentId.value) {
            selectedParentId.value = filteredTree.value[0].id;
        }
    });

    const deleteCategory = (category) => {
        if(confirm(`¿Eliminar "${category.name}"?`)) {
            router.delete(route('admin.categories.destroy', category.id), {
                onSuccess: () => {
                    // Si borramos el padre activo, deseleccionar
                    if (selectedParentId.value === category.id) selectedParentId.value = null;
                }
            });
        }
    };
</script>

<template>
    <AdminLayout>
        
        <div class="flex flex-col md:flex-row h-[calc(100vh-6rem)] gap-6 overflow-hidden">
            
            <div class="w-full md:w-1/3 lg:w-1/4 flex flex-col bg-skin-fill-card border border-skin-border rounded-global shadow-sm overflow-hidden h-full">
                
                <div class="p-4 border-b border-skin-border bg-skin-fill/50 shrink-0">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="font-black text-lg text-skin-base tracking-tight">Catálogo</h2>
                        
                        <Link v-if="can_manage" :href="route('admin.categories.create')" class="p-2 bg-skin-primary text-skin-primary-text rounded-global hover:brightness-110 transition shadow-sm" title="Nueva Categoría Padre">
                            <Plus :size="16" />
                        </Link>
                    </div>

                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search :size="14" class="text-skin-muted group-focus-within:text-skin-primary transition-colors" />
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Filtrar..." 
                            class="pl-9 w-full bg-skin-fill border border-skin-border text-skin-base text-xs font-medium rounded-global py-2 focus:ring-2 focus:ring-skin-primary/50 focus:border-skin-primary outline-none transition-all"
                        >
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
                    <div v-for="parent in filteredTree" :key="parent.id"
                         @click="selectParent(parent.id)"
                         class="flex items-center gap-3 p-3 rounded-global cursor-pointer transition-all duration-200 border border-transparent group"
                         :class="selectedParentId === parent.id 
                            ? 'bg-skin-primary/10 border-skin-primary/20 shadow-sm' 
                            : 'hover:bg-skin-fill hover:border-skin-border'">
                        
                        <div class="w-10 h-10 rounded bg-skin-fill border border-skin-border overflow-hidden shrink-0 flex items-center justify-center relative">
                            <img v-if="parent.image_url" :src="parent.image_url" class="w-full h-full object-cover">
                            <Layers v-else :size="16" class="text-skin-muted" />
                            
                            <div v-if="parent.requires_age_check" class="absolute top-0 right-0 w-2 h-2 bg-skin-danger rounded-full ring-1 ring-white"></div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center">
                                <h3 class="font-bold text-sm truncate"
                                    :class="selectedParentId === parent.id ? 'text-skin-primary' : 'text-skin-base'">
                                    {{ parent.name }}
                                </h3>
                                <ChevronRight v-if="selectedParentId === parent.id" :size="14" class="text-skin-primary" />
                            </div>
                            <p class="text-[10px] text-skin-muted flex gap-2">
                                <span>{{ parent.children.length }} Subcat.</span>
                                <span v-if="parent.external_code" class="font-mono opacity-75">#{{ parent.external_code }}</span>
                            </p>
                        </div>
                    </div>

                    <div v-if="filteredTree.length === 0" class="text-center py-8 text-skin-muted">
                        <p class="text-xs">No hay coincidencias</p>
                    </div>
                </div>
            </div>


            <div class="flex-1 flex flex-col h-full overflow-hidden bg-skin-fill rounded-global border border-transparent">
                
                <Transition name="fade" mode="out-in">
                    
                    <div v-if="activeParent" :key="activeParent.id" class="flex flex-col h-full">
                        
                        <div class="bg-skin-fill-card border border-skin-border rounded-global shadow-sm p-6 mb-6 shrink-0 relative overflow-hidden group">
                            <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none transform group-hover:scale-110 transition-transform duration-500">
                                <FolderOpen :size="150" />
                            </div>

                            <div class="flex justify-between items-start relative z-10">
                                <div class="flex gap-5">
                                    <div class="w-20 h-20 rounded-lg bg-skin-fill border-2 border-skin-border shadow-inner flex items-center justify-center overflow-hidden">
                                        <img v-if="activeParent.image_url" :src="activeParent.image_url" class="w-full h-full object-cover">
                                        <ImageIcon v-else :size="32" class="text-skin-muted/40" />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-skin-fill border border-skin-border text-skin-muted">
                                                Categoría Padre
                                            </span>
                                            <span v-if="activeParent.is_active" class="px-2 py-0.5 rounded text-[10px] font-bold bg-skin-success/10 text-skin-success">ACTIVO</span>
                                            <span v-else class="px-2 py-0.5 rounded text-[10px] font-bold bg-skin-muted/20 text-skin-muted">INACTIVO</span>
                                        </div>
                                        <h1 class="text-3xl font-black text-skin-base tracking-tight mb-2">{{ activeParent.name }}</h1>
                                        <div class="flex items-center gap-4 text-xs text-skin-muted font-mono">
                                            <span class="flex items-center gap-1"><Hash :size="12" /> Slug: /{{ activeParent.slug }}</span>
                                            <span v-if="activeParent.external_code" class="flex items-center gap-1"><Package :size="12" /> ERP: {{ activeParent.external_code }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="can_manage" class="flex gap-2">
                                    <Link :href="route('admin.categories.edit', activeParent.id)" 
                                          class="flex items-center gap-2 px-4 py-2 bg-skin-fill border border-skin-border hover:border-skin-primary text-skin-base hover:text-skin-primary rounded-global text-sm font-bold transition-all shadow-sm">
                                        <Edit :size="16" /> Editar
                                    </Link>
                                    <button @click="deleteCategory(activeParent)" 
                                            class="flex items-center gap-2 px-4 py-2 bg-skin-fill border border-skin-border hover:border-skin-danger text-skin-base hover:text-skin-danger rounded-global text-sm font-bold transition-all shadow-sm">
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mb-4 px-1 shrink-0">
                            <h3 class="font-bold text-lg text-skin-base flex items-center gap-2">
                                Subcategorías
                                <span class="bg-skin-primary/10 text-skin-primary text-xs px-2 py-0.5 rounded-full">{{ activeParent.children.length }}</span>
                            </h3>
                            
                            <Link v-if="can_manage" :href="route('admin.categories.create', { parent: activeParent.id })" 
                                  class="text-xs font-bold text-skin-primary hover:underline flex items-center gap-1">
                                + Agregar Subcategoría
                            </Link>
                        </div>

                        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
                            <div v-if="activeParent.children.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                
                                <div v-for="child in activeParent.children" :key="child.id" 
                                     class="group bg-skin-fill-card border border-skin-border hover:border-skin-primary/50 rounded-global p-4 flex items-center gap-4 transition-all hover:shadow-md relative overflow-hidden">
                                    
                                    <div class="w-12 h-12 rounded bg-skin-fill border border-skin-border flex items-center justify-center shrink-0 overflow-hidden">
                                        <img v-if="child.image_url" :src="child.image_url" class="w-full h-full object-cover">
                                        <CornerDownRight v-else :size="16" class="text-skin-muted/50" />
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-sm text-skin-base truncate">{{ child.name }}</h4>
                                        <div class="flex items-center gap-2 text-[10px] text-skin-muted mt-0.5">
                                            <span v-if="child.external_code" class="bg-skin-fill px-1 rounded border border-skin-border">{{ child.external_code }}</span>
                                            <span class="truncate">/{{ child.slug }}</span>
                                        </div>
                                    </div>

                                    <div v-if="can_manage" class="absolute right-2 top-1/2 -translate-y-1/2 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-all transform translate-x-4 group-hover:translate-x-0">
                                        <Link :href="route('admin.categories.edit', child.id)" class="p-1.5 bg-skin-fill shadow-sm rounded text-skin-muted hover:text-skin-primary hover:scale-110 transition">
                                            <Edit :size="12" />
                                        </Link>
                                        <button @click="deleteCategory(child)" class="p-1.5 bg-skin-fill shadow-sm rounded text-skin-muted hover:text-skin-danger hover:scale-110 transition">
                                            <Trash2 :size="12" />
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <div v-else class="h-64 flex flex-col items-center justify-center border-2 border-dashed border-skin-border/50 rounded-global bg-skin-fill/20">
                                <p class="text-sm font-bold text-skin-muted mb-2">Esta categoría no tiene hijos</p>
                                
                                <Link v-if="can_manage" :href="route('admin.categories.create', { parent: activeParent.id })" class="px-4 py-2 bg-skin-fill border border-skin-border rounded-global text-xs font-bold hover:bg-white hover:text-skin-primary transition shadow-sm">
                                    Crear primera subcategoría
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-else class="h-full flex flex-col items-center justify-center text-skin-muted opacity-50">
                        <FolderOpen :size="64" class="mb-4 text-skin-border" />
                        <p class="font-bold text-lg">Selecciona una categoría</p>
                        <p class="text-sm">Elige una opción del panel izquierdo para ver detalles</p>
                    </div>

                </Transition>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
</style>