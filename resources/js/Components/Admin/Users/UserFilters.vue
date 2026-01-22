<script setup>
    import { ref, watch } from 'vue';
    import { Search, SlidersHorizontal, X } from 'lucide-vue-next';
    
    const props = defineProps({
        modelValue: Object, // { search, role_id, branch_id }
        roles: Array,
        branches: Array
    });
    
    const emit = defineEmits(['update:modelValue']);
    
    // Estado local para mutar
    const filters = ref({ ...props.modelValue });
    const showFilters = ref(false);
    
    // Sincronización bidireccional
    watch(filters, (val) => {
        emit('update:modelValue', val);
    }, { deep: true });
    
    const clearFilters = () => {
        filters.value.role_id = '';
        filters.value.branch_id = '';
        showFilters.value = false;
    };
    
    const hasActiveFilters = () => !!filters.value.role_id || !!filters.value.branch_id;
    </script>
    
    <template>
        <div class="relative w-full max-w-2xl z-20">
            <div class="flex items-center gap-2">
                <div class="relative flex-1 group">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-content-light group-focus-within:text-primary transition-colors duration-instant" :size="20" />
                    <input 
                        v-model="filters.search"
                        type="text" 
                        placeholder="Buscar por nombre, correo o teléfono..." 
                        class="w-full pl-10 pr-4 py-3 bg-surface border border-transparent focus:border-primary/30 rounded-box shadow-sm focus:shadow-md focus:ring-0 transition-all duration-instant ease-elastic placeholder:text-content-light/50 text-content"
                    >
                </div>
    
                <button 
                    @click="showFilters = !showFilters"
                    class="p-3 rounded-box border border-transparent transition-all duration-instant ease-elastic flex items-center gap-2 font-medium text-sm"
                    :class="[
                        showFilters || hasActiveFilters() 
                            ? 'bg-primary text-white shadow-md shadow-primary/20' 
                            : 'bg-surface text-content-light hover:text-primary hover:bg-surface/80'
                    ]"
                >
                    <SlidersHorizontal :size="18" />
                    <span class="hidden sm:inline">Filtros</span>
                    <span v-if="hasActiveFilters()" class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                </button>
            </div>
    
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-2 opacity-0"
            >
                <div v-if="showFilters" class="absolute top-full left-0 right-0 mt-2 p-4 bg-surface rounded-box shadow-xl border border-primary/5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <div v-if="branches.length > 1">
                        <label class="text-xs font-bold text-content-light uppercase tracking-wider mb-1 block">Sucursal</label>
                        <select v-model="filters.branch_id" class="w-full bg-base border border-surface rounded-md p-2 text-sm text-content focus:border-primary/50 focus:ring-0">
                            <option value="">Todas las sucursales</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                        </select>
                    </div>
    
                    <div>
                        <label class="text-xs font-bold text-content-light uppercase tracking-wider mb-1 block">Rol</label>
                        <select v-model="filters.role_id" class="w-full bg-base border border-surface rounded-md p-2 text-sm text-content focus:border-primary/50 focus:ring-0">
                            <option value="">Todos los roles</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.display_name }}</option>
                        </select>
                    </div>
    
                    <div class="sm:col-span-2 flex justify-end pt-2 border-t border-base">
                        <button @click="clearFilters" class="text-xs text-red-500 hover:underline flex items-center gap-1">
                            <X :size="12" /> Limpiar filtros
                        </button>
                    </div>
                </div>
            </transition>
        </div>
    </template>