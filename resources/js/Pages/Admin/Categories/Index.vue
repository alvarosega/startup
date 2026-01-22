<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch, computed } from 'vue';
    import debounce from 'lodash/debounce';
    
    // Iconos
    import { 
        Search, Plus, FolderTree, Image as ImageIcon, 
        AlertCircle, MoreVertical, Edit, Trash2, ChevronRight, Hash
    } from 'lucide-vue-next';

    const props = defineProps({
        categories: Array,
        filters: Object
    });

    const search = ref(props.filters.search || '');

    // --- LÓGICA DE AGRUPACIÓN (Transformar lista plana a Árbol) ---
    // Esto permite mostrar Padres con sus Hijos dentro, aunque vengan planos del backend.
    const structuredCategories = computed(() => {
        const parents = props.categories.filter(c => !c.parent_id);
        
        return parents.map(parent => {
            return {
                ...parent,
                children: props.categories.filter(c => c.parent_id === parent.id)
            };
        });
    });

    // Filtros de búsqueda (Backend)
    watch(search, debounce((val) => {
        router.get(route('admin.categories.index'), { search: val }, { 
            preserveState: true, replace: true, preserveScroll: true 
        });
    }, 300));
    
    const deleteCategory = (category) => {
        if(confirm(`¿Eliminar "${category.name}"? Si tiene productos o hijos, podría causar errores.`)) {
            router.delete(route('admin.categories.destroy', category.id));
        }
    };
</script>

<template>
    <AdminLayout>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-black text-skin-base tracking-tight">Categorías</h1>
                <p class="text-skin-muted text-sm mt-1">Organización jerárquica del catálogo</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <div class="relative w-full md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Search :size="16" class="text-skin-muted" />
                    </div>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Buscar categoría..." 
                        class="pl-10 w-full bg-skin-fill-card border border-skin-border text-skin-base text-sm rounded-global focus:ring-2 focus:ring-skin-primary focus:border-skin-primary placeholder-skin-muted/50 transition-all shadow-sm"
                    >
                </div>

                <Link :href="route('admin.categories.create')" 
                      class="flex items-center justify-center gap-2 bg-skin-primary text-skin-primary-text px-4 py-2 rounded-global text-sm font-bold shadow-sm hover:brightness-110 transition-all active:scale-95 whitespace-nowrap">
                    <Plus :size="16" />
                    <span>Nueva</span>
                </Link>
            </div>
        </div>

        

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 items-start">
            
            <div v-for="parent in structuredCategories" :key="parent.id" 
                 class="bg-skin-fill-card border border-skin-border rounded-global shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group">
                
                <div class="p-4 flex items-start gap-4 border-b border-skin-border/50 bg-skin-fill/30">
                    
                    <div class="w-16 h-16 rounded-global bg-skin-fill border border-skin-border overflow-hidden shrink-0 flex items-center justify-center relative">
                        <img v-if="parent.image_url" :src="parent.image_url" class="w-full h-full object-cover">
                        <ImageIcon v-else class="text-skin-muted/50" :size="24" />
                        
                        <div v-if="parent.requires_age_check" class="absolute top-0 right-0 bg-skin-danger text-white text-[8px] font-black px-1 py-0.5 rounded-bl">
                            +18
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-lg text-skin-base truncate" :title="parent.name">
                                {{ parent.name }}
                            </h3>
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Link :href="route('admin.categories.edit', parent.id)" class="text-skin-muted hover:text-skin-primary p-1">
                                    <Edit :size="14" />
                                </Link>
                                <button @click="deleteCategory(parent)" class="text-skin-muted hover:text-skin-danger p-1">
                                    <Trash2 :size="14" />
                                </button>
                            </div>
                        </div>
                        
                        <div class="text-[10px] font-mono text-skin-muted flex items-center gap-2 mt-1">
                            <span class="bg-skin-fill border border-skin-border px-1.5 rounded">ERP: {{ parent.external_code || 'N/A' }}</span>
                            <span :class="parent.is_active ? 'text-skin-success' : 'text-skin-muted'">
                                ● {{ parent.is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-skin-fill-card">
                    <div v-if="parent.children && parent.children.length > 0" class="space-y-2">
                        <p class="text-[10px] font-bold text-skin-muted uppercase tracking-widest mb-2">Subcategorías</p>
                        
                        <div v-for="child in parent.children" :key="child.id" 
                             class="flex items-center justify-between p-2 rounded-global hover:bg-skin-fill transition-colors border border-transparent hover:border-skin-border group/child">
                            
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-8 h-8 rounded bg-skin-fill border border-skin-border flex items-center justify-center shrink-0">
                                    <img v-if="child.image_url" :src="child.image_url" class="w-full h-full object-cover rounded-[inherit]">
                                    <span v-else class="text-[8px] text-skin-muted">IMG</span>
                                </div>
                                <div class="truncate">
                                    <p class="text-sm font-medium text-skin-base truncate">{{ child.name }}</p>
                                    <p class="text-[10px] text-skin-muted font-mono truncate">/{{ child.slug }}</p>
                                </div>
                            </div>

                            <div class="flex gap-2 opacity-0 group-hover/child:opacity-100 transition-opacity">
                                <Link :href="route('admin.categories.edit', child.id)" class="text-skin-muted hover:text-skin-primary" title="Editar">
                                    <Edit :size="12" />
                                </Link>
                                <button @click="deleteCategory(child)" class="text-skin-muted hover:text-skin-danger" title="Borrar">
                                    <Trash2 :size="12" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="py-4 text-center border-2 border-dashed border-skin-border/50 rounded-global">
                        <FolderTree :size="20" class="mx-auto text-skin-muted/40 mb-1" />
                        <p class="text-xs text-skin-muted">Sin subcategorías</p>
                        <Link :href="route('admin.categories.create', { parent: parent.id })" class="text-[10px] font-bold text-skin-primary hover:underline mt-1 block">
                            + Agregar Hija
                        </Link>
                    </div>
                </div>

            </div>

            <div v-if="structuredCategories.length === 0" class="col-span-full py-12 text-center">
                <p class="text-skin-muted">No se encontraron categorías.</p>
            </div>

        </div>
    </AdminLayout>
</template>