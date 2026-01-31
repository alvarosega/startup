<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MapComponent from '@/Components/Base/MapComponent.vue';
import { 
    MapPin, Phone, Edit, Building, Navigation, Plus, 
    Copy, ExternalLink, Search
} from 'lucide-vue-next';

const props = defineProps({
    branches: Array
});

// Estado de carga simulado para mostrar el Skeleton (Mejora UX)
const isLoading = ref(true);

// Simulación de carga de red para feedback visual
onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 800);
});

// Calcular centro del mapa
const mapCenter = ref([-16.5000, -68.1500]);
if (props.branches.length > 0 && props.branches[0].latitude) {
    mapCenter.value = [props.branches[0].latitude, props.branches[0].longitude];
}

// Utilidad para copiar ID (Micro-interacción)
const copyId = (id) => {
    navigator.clipboard.writeText(id);
    // Aquí podrías disparar un toast notification si tuvieras el sistema importado
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div class="animate-slide-up">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="avatar avatar-lg bg-gradient-to-br from-primary to-secondary text-white shadow-xl shadow-primary/20 ring-4 ring-background">
                                <Building :size="28" />
                            </div>
                            <div class="absolute -bottom-1 -right-1 bg-background rounded-full p-1">
                                <div class="bg-success w-3 h-3 rounded-full border-2 border-background"></div>
                            </div>
                        </div>
                        
                        <div>
                            <h1 class="text-3xl lg:text-4xl font-display font-black text-foreground tracking-tight">
                                Red de Sucursales
                            </h1>
                            <p class="text-muted-foreground font-medium text-sm mt-1 flex items-center gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-muted-foreground/50"></span>
                                Gestión operativa de puntos de venta
                            </p>
                        </div>
                    </div>
                </div>
                
                <Link :href="route('admin.branches.create')" 
                      class="btn btn-primary btn-lg shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all duration-300 group">
                    <Plus :size="20" class="group-hover:rotate-90 transition-transform duration-300" />
                    <span class="font-bold">Nueva Sucursal</span>
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-8">
            
            <div class="animate-in fade-in zoom-in duration-500 delay-100">
                <div class="card overflow-hidden border-border/60 shadow-md">
                    <div class="card-header bg-muted/20 border-b border-border/40 py-3 px-5 flex justify-between items-center">
                        <h2 class="text-sm font-bold text-muted-foreground uppercase tracking-wider flex items-center gap-2">
                            <MapPin :size="16" />
                            Geolocalización Global
                        </h2>
                        <span class="text-xs font-mono text-muted-foreground bg-muted px-2 py-1 rounded">
                            {{ branches.length }} Puntos Activos
                        </span>
                    </div>
                    <div class="card-content p-0 relative group">
                        <MapComponent 
                            :markers="branches" 
                            :center="mapCenter" 
                            height="400px" 
                            :zoom="12"
                            class="w-full h-full grayscale-[20%] group-hover:grayscale-0 transition-all duration-700"
                        />
                        <div class="absolute inset-x-0 top-0 h-12 bg-gradient-to-b from-black/5 to-transparent pointer-events-none"></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                
                <template v-if="isLoading">
                    <div v-for="n in 3" :key="`skeleton-${n}`" class="card border-border/40 shadow-none">
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between">
                                <div class="flex gap-4">
                                    <div class="skeleton w-12 h-12 rounded-full"></div>
                                    <div class="space-y-2">
                                        <div class="skeleton h-5 w-32 rounded"></div>
                                        <div class="skeleton h-4 w-20 rounded"></div>
                                    </div>
                                </div>
                                <div class="skeleton w-3 h-3 rounded-full"></div>
                            </div>
                            <div class="space-y-2 mt-4">
                                <div class="skeleton h-4 w-full rounded"></div>
                                <div class="skeleton h-4 w-2/3 rounded"></div>
                            </div>
                        </div>
                        <div class="h-14 bg-muted/10 border-t border-border/50"></div>
                    </div>
                </template>

                <template v-else>
                    <div v-for="branch in branches" :key="branch.id" 
                         class="card group hover:border-primary/50 hover:shadow-lg hover:shadow-primary/5 transition-all duration-300 relative overflow-hidden">
                        
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                        <div class="p-6 relative z-10">
                            <div class="flex justify-between items-start mb-5">
                                <div class="flex gap-4 items-center">
                                    <div class="relative">
                                        <div class="avatar avatar-md bg-muted text-foreground border border-border group-hover:border-primary/30 group-hover:bg-primary/10 group-hover:text-primary transition-colors duration-300">
                                            <Building :size="20" stroke-width="2" />
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <h3 class="text-lg font-display font-bold text-foreground leading-none group-hover:text-primary transition-colors duration-200">
                                            {{ branch.name }}
                                        </h3>
                                        <p class="text-sm font-medium text-muted-foreground mt-1.5">
                                            {{ branch.city }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2" :title="branch.is_active ? 'Sucursal Activa' : 'Sucursal Cerrada'">
                                    <span v-if="branch.is_active" class="relative flex h-3 w-3">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-3 w-3 bg-success border border-white dark:border-background"></span>
                                    </span>
                                    <span v-else class="h-3 w-3 rounded-full bg-muted-foreground/30 border border-border"></span>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-start gap-3 p-2.5 rounded-lg bg-muted/30 group-hover:bg-muted/50 transition-colors">
                                    <MapPin :size="16" class="mt-0.5 text-primary shrink-0" />
                                    <span class="text-sm text-foreground/80 leading-snug line-clamp-2">
                                        {{ branch.address || 'Sin dirección registrada' }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-3 p-2.5 rounded-lg bg-muted/30 group-hover:bg-muted/50 transition-colors">
                                    <Phone :size="16" class="text-primary shrink-0" />
                                    <span class="text-sm font-mono text-foreground/80">
                                        {{ branch.phone || '---' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-3 border-t border-border/50 bg-muted/5 backdrop-blur-sm flex justify-between items-center group-hover:bg-muted/10 transition-colors">
                            
                            <button @click="copyId(branch.id)" 
                                    class="flex items-center gap-1.5 text-[10px] font-mono text-muted-foreground/60 hover:text-primary transition-colors cursor-pointer"
                                    title="Copiar ID de sucursal">
                                <span class="opacity-50">ID:</span>
                                <span>{{ branch.id }}</span>
                                <Copy :size="10" />
                            </button>

                            <div class="flex items-center gap-2">
                                <a v-if="branch.latitude" 
                                   :href="`https://www.google.com/maps/search/?api=1&query=${branch.latitude},${branch.longitude}`" 
                                   target="_blank"
                                   class="btn btn-ghost btn-sm h-8 w-8 p-0 rounded-full text-muted-foreground hover:text-primary hover:bg-primary/10"
                                   title="Abrir en Google Maps">
                                    <ExternalLink :size="16" />
                                </a>

                                <Link :href="route('admin.branches.edit', branch.id)" 
                                      class="btn btn-outline btn-sm hover:bg-primary hover:text-primary-foreground hover:border-primary transition-all duration-200 text-xs px-4">
                                    Administrar
                                </Link>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div v-if="!isLoading && branches.length === 0" class="animate-scale-in">
                <div class="card border-dashed border-2 border-muted-foreground/20 bg-muted/5">
                    <div class="card-content flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mb-4">
                            <Search :size="32" class="text-muted-foreground" />
                        </div>
                        <h3 class="text-xl font-display font-bold text-foreground">Sin Sucursales</h3>
                        <p class="text-muted-foreground max-w-sm mt-2 mb-6">
                            No hemos encontrado sucursales activas en tu base de datos. Comienza expandiendo tu red hoy mismo.
                        </p>
                        <Link :href="route('admin.branches.create')" class="btn btn-primary">
                            Registrar primera sucursal
                        </Link>
                    </div>
                </div>
            </div>
            
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Utilidad específica para el efecto de elevación suave */
.hover-lift-sm:hover {
    transform: translateY(-2px);
}
</style>