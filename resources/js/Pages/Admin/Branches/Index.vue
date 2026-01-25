<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link } from '@inertiajs/vue3';
    import MapComponent from '@/Components/Base/MapComponent.vue';
    import { ref } from 'vue';
    import { 
        MapPin, Phone, Edit, Building, Navigation, Plus, CheckCircle, XCircle
    } from 'lucide-vue-next';
    
    const props = defineProps({
        branches: Array
    });
    
    // Calcular el centro del mapa basado en la primera sucursal o default La Paz
    const mapCenter = ref([-16.5000, -68.1500]);
    if (props.branches.length > 0 && props.branches[0].latitude) {
        mapCenter.value = [props.branches[0].latitude, props.branches[0].longitude];
    }
    </script>
    
    <template>
        <AdminLayout>
            <!-- HEADER -->
            <template #header>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="animate-slide-up">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="avatar avatar-lg bg-gradient-to-br from-primary to-secondary text-primary-foreground shadow-lg">
                                <Building :size="24" />
                            </div>
                            <div>
                                <h1 class="text-3xl lg:text-4xl font-display font-black text-foreground tracking-tight">
                                    Red de Sucursales
                                </h1>
                                <p class="text-muted-foreground font-medium text-sm mt-1">
                                    Gestión de puntos de venta y centros de distribución
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <Link :href="route('admin.branches.create')" 
                          class="btn btn-primary btn-lg flex items-center gap-2 group">
                        <Plus :size="18" class="transition-transform duration-fast group-hover:scale-110" />
                        <span>Nueva Sucursal</span>
                    </Link>
                </div>
            </template>
    
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <!-- MAPA OVERVIEW -->
                <div class="mb-8">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-display font-bold text-foreground flex items-center gap-2">
                                <MapPin :size="20" class="text-primary" />
                                Vista General de Sucursales
                            </h2>
                        </div>
                        <div class="card-content p-0">
                            <MapComponent 
                                :markers="branches" 
                                :center="mapCenter" 
                                height="350px"
                                :zoom="12"
                            />
                        </div>
                    </div>
                </div>
    
                <!-- LISTA DE SUCURSALES -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <div v-for="branch in branches" :key="branch.id" 
                         class="card hover-lift-lg group">
                        
                        <!-- STATUS INDICATOR -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-xl" 
                             :class="branch.is_active ? 'bg-success' : 'bg-error'"></div>
    
                        <div class="p-6">
                            <!-- HEADER -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex gap-3 items-center">
                                    <div class="avatar avatar-md bg-gradient-to-br from-primary to-secondary text-primary-foreground shadow-md">
                                        <Building :size="18" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-display font-bold text-foreground leading-tight">
                                            {{ branch.name }}
                                        </h3>
                                        <div class="badge badge-outline mt-1">
                                            {{ branch.city }}
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- STATUS BADGE -->
                                <div v-if="branch.is_active" class="badge badge-success flex items-center gap-1.5">
                                    <CheckCircle :size="12" />
                                    <span>Activa</span>
                                </div>
                                <div v-else class="badge badge-error flex items-center gap-1.5">
                                    <XCircle :size="12" />
                                    <span>Cerrada</span>
                                </div>
                            </div>
    
                            <!-- INFO -->
                            <div class="space-y-3 text-sm">
                                <div class="flex items-start gap-3 text-muted-foreground">
                                    <MapPin :size="16" class="mt-0.5 shrink-0" />
                                    <p class="leading-snug">{{ branch.address || 'Sin dirección registrada' }}</p>
                                </div>
                                <div class="flex items-center gap-3 text-muted-foreground">
                                    <Phone :size="16" class="shrink-0" />
                                    <p>{{ branch.phone || 'Sin teléfono directo' }}</p>
                                </div>
                            </div>
                        </div>
    
                        <!-- FOOTER -->
                        <div class="px-6 py-4 border-t border-border/50 bg-muted/5 flex justify-between items-center">
                            <div class="text-xs text-muted-foreground font-mono">
                                ID: {{ branch.id }}
                            </div>
                            
                            <div class="flex gap-2">
                                <a v-if="branch.latitude" 
                                   :href="`https://www.google.com/maps/search/?api=1&query=${branch.latitude},${branch.longitude}`" 
                                   target="_blank"
                                   class="btn btn-ghost btn-sm"
                                   title="Ver en Google Maps">
                                    <Navigation :size="16" />
                                </a>
    
                                <Link :href="route('admin.branches.edit', branch.id)" 
                                      class="btn btn-primary btn-sm flex items-center gap-2">
                                    <Edit :size="14" />
                                    <span>Editar</span>
                                </Link>
                            </div>
                        </div>
                    </div>
    
                    <!-- EMPTY STATE -->
                    <div v-if="branches.length === 0" class="col-span-full">
                        <div class="card border-dashed border-2">
                            <div class="card-content text-center py-12">
                                <Building :size="48" class="text-muted-foreground mx-auto mb-4" />
                                <h3 class="text-lg font-display font-bold text-foreground mb-2">
                                    No hay sucursales registradas
                                </h3>
                                <p class="text-muted-foreground mb-6">
                                    Comienza creando tu primera sucursal
                                </p>
                                <Link :href="route('admin.branches.create')" 
                                      class="btn btn-primary btn-md">
                                    Crear primera sucursal
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    </template>