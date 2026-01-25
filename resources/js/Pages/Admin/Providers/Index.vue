<!-- resources/js/Pages/Admin/Providers/Index.vue -->
<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import SearchInput from '@/Components/SearchInput.vue';
    import ProviderCard from '@/Components/ProviderCard.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import debounce from 'lodash/debounce';
    import { Plus, Search } from 'lucide-vue-next';
    
    const props = defineProps({ 
        providers: Object, 
        filters: Object,
        can_manage: Boolean 
    });
    
    const search = ref(props.filters.search || '');
    
    // Watcher para búsqueda
    watch(search, debounce((val) => {
        router.get(route('admin.providers.index'), { search: val }, { 
            preserveState: true, 
            replace: true,
            preserveScroll: true
        });
    }, 300));
    
    const handleDelete = (providerId) => {
        if (confirm('¿Estás seguro de eliminar este proveedor?')) {
            router.delete(route('admin.providers.destroy', providerId));
        }
    };
    </script>
    
    <template>
        <AdminLayout>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-display font-semibold text-foreground tracking-tight">
                        Proveedores
                    </h1>
                    <p class="text-muted-foreground text-sm mt-1">
                        Gestión de socios comerciales y cadena de suministro
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <div class="w-full md:w-64">
                        <SearchInput v-model="search" placeholder="Buscar proveedor..." />
                    </div>
    
                    <Link v-if="can_manage" :href="route('admin.providers.create')" 
                          class="btn btn-primary">
                        <Plus :size="16" />
                        <span>Nuevo</span>
                    </Link>
                </div>
            </div>
    
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                
                <ProviderCard 
                    v-for="provider in providers.data" 
                    :key="provider.id"
                    :provider="provider"
                    :can-manage="can_manage"
                    @delete="handleDelete"
                />
    
                <div v-if="providers.data.length === 0" 
                     class="col-span-full py-12 flex flex-col items-center justify-center text-muted-foreground border-2 border-dashed border-border rounded-xl">
                    <Search :size="48" class="mb-4 opacity-20" />
                    <p class="font-medium">No se encontraron proveedores</p>
                    <p class="text-sm opacity-70">Intenta con otro término de búsqueda</p>
                </div>
                
            </div>
    
            <!-- Paginación (si es necesaria) -->
            <div v-if="providers.links.length > 3" class="mt-8">
                <div class="flex flex-wrap justify-center gap-2">
                    <template v-for="(link, index) in providers.links" :key="index">
                        <Link 
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-1 rounded-lg border border-input text-sm font-medium transition-colors"
                            :class="{
                                'bg-primary text-primary-foreground border-primary': link.active,
                                'hover:bg-accent hover:text-accent-foreground': !link.active
                            }"
                            v-html="link.label"
                        />
                        <span v-else 
                              class="px-3 py-1 text-muted-foreground"
                              v-html="link.label">
                        </span>
                    </template>
                </div>
            </div>
            
        </AdminLayout>
    </template>