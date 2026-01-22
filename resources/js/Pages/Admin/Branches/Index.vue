<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import MapComponent from '@/Components/Base/MapComponent.vue'; // Tu nuevo componente
    import { ref } from 'vue';
    
    // Iconos
    import { 
        MapPin, Phone, Edit, Building, Navigation, Plus 
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
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight">Red de Sucursales</h1>
                <p class="text-gray-400 text-sm mt-1">Gestión de puntos de venta y centros de distribución</p>
            </div>
            
            <Link :href="route('admin.branches.create')" 
                  class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-lg font-bold shadow-lg shadow-blue-900/20 transition-all hover:scale-105 active:scale-95">
                <Plus :size="18" />
                <span>Nueva Sucursal</span>
            </Link>
        </div>

        <div class="mb-8">
            <MapComponent 
                :markers="branches" 
                :center="mapCenter" 
                height="350px"
                :zoom="12"
            />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            
            <div v-for="branch in branches" :key="branch.id" 
                 class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden shadow-md hover:shadow-xl hover:border-gray-600 transition-all duration-300 group relative flex flex-col">
                
                <div class="absolute left-0 top-0 bottom-0 w-1" 
                     :class="branch.is_active ? 'bg-green-500' : 'bg-red-500'"></div>

                <div class="p-6 flex-1">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex gap-3 items-center">
                            <div class="w-10 h-10 rounded-lg bg-gray-700 flex items-center justify-center text-gray-400 border border-gray-600">
                                <Building :size="20" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white leading-tight">{{ branch.name }}</h3>
                                <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-gray-900 text-gray-400 border border-gray-700">
                                    {{ branch.city }}
                                </span>
                            </div>
                        </div>
                        
                        <div v-if="branch.is_active" class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-green-900/30 text-green-400 border border-green-800 text-[10px] font-bold uppercase">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Activa
                        </div>
                        <div v-else class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-red-900/30 text-red-400 border border-red-800 text-[10px] font-bold uppercase">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Cerrada
                        </div>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-3 text-gray-400">
                            <MapPin :size="16" class="mt-0.5 text-gray-500 shrink-0" />
                            <p class="leading-snug">{{ branch.address }}</p>
                        </div>
                        <div class="flex items-center gap-3 text-gray-400">
                            <Phone :size="16" class="text-gray-500 shrink-0" />
                            <p>{{ branch.phone || 'Sin teléfono directo' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-900/50 p-4 border-t border-gray-700 flex justify-between items-center">
                    <div class="text-xs text-gray-500 font-mono">
                        ID: {{ branch.id }}
                    </div>
                    
                    <div class="flex gap-2">
                        <a v-if="branch.latitude" 
                           :href="`https://www.google.com/maps/search/?api=1&query=${branch.latitude},${branch.longitude}`" 
                           target="_blank"
                           class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition"
                           title="Ver en Google Maps">
                            <Navigation :size="16" />
                        </a>

                        <Link :href="route('admin.branches.edit', branch.id)" 
                              class="flex items-center gap-2 px-4 py-2 bg-gray-700 hover:bg-blue-600 text-white rounded-lg text-xs font-bold transition-colors">
                            <Edit :size="14" /> Editar
                        </Link>
                    </div>
                </div>

            </div>

            <div v-if="branches.length === 0" class="col-span-full py-12 flex flex-col items-center justify-center border-2 border-dashed border-gray-700 rounded-xl bg-gray-800/50">
                <Building :size="48" class="text-gray-600 mb-4" />
                <p class="text-gray-400 font-medium">No hay sucursales registradas</p>
                <Link :href="route('admin.branches.create')" class="mt-4 text-blue-400 hover:text-blue-300 text-sm font-bold hover:underline">
                    Crear la primera sucursal
                </Link>
            </div>

        </div>
    </AdminLayout>
</template>