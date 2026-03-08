<script setup>
import { ref, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Plus, Search, Phone, Mail, MapPin, 
    Truck, Edit, Trash2, Building2, 
    User, Globe, Wifi, WifiOff, Shield,
    Package, Clock
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({ 
    providers: Object,
    filters: Object,
    can_manage: Boolean 
});

const search = ref(props.filters.search || '');
const hoveredProvider = ref(null);
const viewMode = ref('grid'); // 'grid' o 'list'

watch(search, debounce((val) => {
    router.get(route('admin.providers.index'), { search: val }, { 
        preserveState: true, replace: true, preserveScroll: true
    });
}, 300));

const handleDelete = (provider) => {
    const name = provider.name || provider.company_name || 'este proveedor';
    if (confirm(`¿Confirmar eliminación de "${name}"?`)) {
        router.delete(route('admin.providers.destroy', provider.id));
    }
};

const getInitials = (provider) => {
    const name = provider.commercial_name || provider.company_name || 'P';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const clearSearch = () => {
    search.value = '';
};

// Estadísticas
const stats = computed(() => {
    const total = props.providers.total || 0;
    const currentView = props.providers.data?.length || 0;
    const activeProviders = props.providers.data?.filter(p => p.is_active).length || 0;
    
    return [
        { 
            label: 'Total proveedores', 
            value: total, 
            icon: Building2
        },
        { 
            label: 'Activos', 
            value: activeProviders, 
            icon: Wifi
        },
        { 
            label: 'En pantalla', 
            value: currentView, 
            icon: Globe
        }
    ];
});

// Código de proveedor
const getProviderCode = (id) => {
    return `PRV-${String(id).padStart(4, '0')}`;
};

// Estado del proveedor
const getProviderStatus = (provider) => {
    if (provider.is_active === false) return { icon: WifiOff, class: 'text-destructive', label: 'Inactivo' };
    return { icon: Wifi, class: 'text-success', label: 'Activo' };
};
</script>

<template>
    <AdminLayout>
        <Head title="Proveedores" />

        <div class="px-4 md:px-6 lg:px-8 py-6 min-h-screen flex flex-col max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-foreground tracking-tight">
                        Proveedores
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Gestión de la cadena de suministro
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-muted-foreground bg-muted/30 px-3 py-1.5 rounded-md border border-border">
                        Total: {{ providers.total }}
                    </span>
                </div>
            </div>

            <!-- Stats Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div v-for="(stat, index) in stats" :key="index" 
                     class="bg-card border border-border rounded-xl p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-primary/10 rounded-lg">
                            <component :is="stat.icon" :size="20" class="text-primary" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ stat.label }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ stat.value }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar y View Toggle -->
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                    <input 
                        v-model="search"
                        type="text" 
                        class="w-full pl-10 pr-10 py-2.5 bg-background border border-border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" 
                        placeholder="Buscar proveedor por nombre, email o teléfono..." 
                    />
                    <button v-if="search" @click="clearSearch" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                        <span class="text-xs">✕</span>
                    </button>
                </div>
                
                <div class="flex gap-2">
                    <div class="bg-card border border-border rounded-lg p-1 flex">
                        <button @click="viewMode = 'grid'"
                                class="px-3 py-1.5 text-sm font-medium rounded-md transition-all"
                                :class="viewMode === 'grid' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground'">
                            Grid
                        </button>
                        <button @click="viewMode = 'list'"
                                class="px-3 py-1.5 text-sm font-medium rounded-md transition-all"
                                :class="viewMode === 'list' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground'">
                            Lista
                        </button>
                    </div>
                    
                    <Link v-if="can_manage" 
                          :href="route('admin.providers.create')" 
                          class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary/90 transition-all flex items-center gap-2">
                        <Plus :size="18" />
                        <span class="hidden md:inline">Nuevo proveedor</span>
                    </Link>
                </div>
            </div>

            <!-- Grid de Proveedores -->
            <div class="flex-1">
                <div v-if="providers.data?.length > 0" 
                     class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    
                    <div v-for="provider in providers.data" :key="provider.id" 
                         @mouseenter="hoveredProvider = provider.id"
                         @mouseleave="hoveredProvider = null"
                         class="bg-card border border-border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200">
                        
                        <div class="p-5">
                            <!-- Header con iniciales y acciones -->
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-lg flex items-center justify-center font-bold text-lg">
                                        {{ getInitials(provider) }}
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-foreground text-base">
                                            {{ provider.name || provider.company_name }}
                                        </h3>
                                        <div class="flex items-center gap-2 mt-1">
                                            <component :is="getProviderStatus(provider).icon" 
                                                       :size="12" 
                                                       :class="getProviderStatus(provider).class" />
                                            <span class="text-xs" :class="getProviderStatus(provider).class">
                                                {{ getProviderStatus(provider).label }}
                                            </span>
                                            <span class="text-xs text-muted-foreground">
                                                {{ getProviderCode(provider.id) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="can_manage" class="flex items-center gap-1">
                                    <Link :href="route('admin.providers.edit', provider.id)" 
                                          class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"
                                          title="Editar">
                                        <Edit :size="16" />
                                    </Link>
                                    <button @click="handleDelete(provider)" 
                                            class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/5 rounded-lg transition-colors"
                                            title="Eliminar">
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </div>

                            <!-- Información de contacto -->
                            <div class="space-y-2 mb-4">
                                <div v-if="provider.contact_name" class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <User :size="14" class="text-primary/60" />
                                    <span class="truncate">{{ provider.contact_name }}</span>
                                </div>
                                
                                <div v-if="provider.email" class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Mail :size="14" class="text-primary/60" />
                                    <span class="truncate">{{ provider.email }}</span>
                                </div>
                                
                                <div v-if="provider.address" class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <MapPin :size="14" class="text-primary/60" />
                                    <span class="truncate">{{ provider.address }}</span>
                                </div>
                            </div>

                            <!-- Métricas adicionales -->
                            <div v-if="provider.products_count || provider.avg_lead_time" 
                                 class="grid grid-cols-2 gap-2 border-t border-border pt-3">
                                <div v-if="provider.products_count" class="flex items-center gap-1 text-xs text-muted-foreground">
                                    <Package :size="12" class="text-primary" />
                                    <span>{{ provider.products_count }} productos</span>
                                </div>
                                <div v-if="provider.avg_lead_time" class="flex items-center gap-1 text-xs text-muted-foreground">
                                    <Clock :size="12" class="text-primary" />
                                    <span>{{ provider.avg_lead_time }} días</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="grid grid-cols-2 border-t border-border">
                            <a v-if="provider.phone" :href="`tel:${provider.phone}`" 
                               class="flex items-center justify-center gap-2 py-3 text-sm text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors">
                                <Phone :size="14" /> Llamar
                            </a>
                            <span v-else class="flex items-center justify-center gap-2 py-3 text-sm text-muted-foreground/40 cursor-not-allowed">
                                <Phone :size="14" /> Sin teléfono
                            </span>

                            <Link :href="route('admin.providers.edit', provider.id)" 
                                  class="flex items-center justify-center gap-2 py-3 text-sm text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors">
                                <Truck :size="14" /> Detalles
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Estado vacío -->
                <div v-else class="bg-card border border-border rounded-xl p-12 text-center">
                    <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                        <Search :size="24" class="text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-semibold text-foreground mb-2">No hay proveedores</h3>
                    <p class="text-sm text-muted-foreground max-w-md mx-auto">
                        {{ search ? 'No se encontraron resultados para tu búsqueda.' : 'Comienza agregando tu primer proveedor.' }}
                    </p>
                    <button v-if="search" @click="clearSearch" 
                            class="mt-4 text-sm text-primary hover:text-primary/80 transition-colors">
                        Limpiar búsqueda
                    </button>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="providers.links && providers.links.length > 3" 
                 class="mt-8 flex justify-center">
                <div class="flex gap-2">
                    <template v-for="(link, k) in providers.links" :key="k">
                        <Link v-if="link.url"
                              :href="link.url"
                              v-html="link.label"
                              class="min-w-[36px] h-9 flex items-center justify-center text-sm font-medium border rounded-md transition-all"
                              :class="link.active 
                                  ? 'bg-primary text-primary-foreground border-primary' 
                                  : 'bg-card text-muted-foreground border-border hover:border-primary/30 hover:text-foreground'"
                              :preserve-scroll="true" />
                        <span v-else
                              v-html="link.label"
                              class="min-w-[36px] h-9 flex items-center justify-center text-sm text-muted-foreground/30 border border-transparent">
                        </span>
                    </template>
                </div>
            </div>

            <!-- Botón flotante de creación (móvil) -->
            <Teleport to="body">
                <Link v-if="can_manage" 
                      :href="route('admin.providers.create')" 
                      class="md:hidden fixed bottom-6 right-6 z-50 w-14 h-14 bg-primary text-primary-foreground rounded-full shadow-lg flex items-center justify-center hover:bg-primary/90 active:scale-95 transition-all">
                    <Plus :size="24" />
                </Link>
            </Teleport>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Sin estilos personalizados - todo usa clases de Tailwind */
</style>