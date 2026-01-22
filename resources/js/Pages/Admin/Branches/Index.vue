<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link } from '@inertiajs/vue3';
    import "leaflet/dist/leaflet.css";
    import { LMap, LTileLayer, LMarker, LPopup, LPolygon } from "@vue-leaflet/vue-leaflet";
    import { ref } from 'vue';
    
    defineProps({
        branches: Array
    });
    
    // Map Configuration
    const zoom = ref(13);
    const center = ref([-16.5000, -68.1500]); // Centered on La Paz
    </script>
    
    <template>
        <AdminLayout>
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-white">Gestión de Sucursales</h1>
                <Link :href="route('admin.branches.create')" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-bold flex items-center">
                    <span class="mr-2 text-xl">+</span> Nueva Sucursal
                </Link>
            </div>
    
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-lg mb-8 h-96 w-full z-0 relative">
                <l-map ref="map" v-model:zoom="zoom" :center="center" :use-global-leaflet="false">
                    <l-tile-layer
                        url="https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}"
                        attribution="Tiles &copy; Esri"
                        layer-type="base"
                        name="Esri Calles"
                    ></l-tile-layer>
                        
                    <template v-for="branch in branches" :key="branch.id">
                        <l-polygon
                            v-if="branch.coverage_polygon && branch.coverage_polygon.length > 2"
                            :lat-lngs="branch.coverage_polygon"
                            color="#10b981" 
                            :fill="true"
                            :fill-opacity="0.2"
                            :weight="2"
                        >
                            <l-popup>Cobertura de: {{ branch.name }}</l-popup>
                        </l-polygon>
    
                        <l-marker 
                            v-if="branch.latitude && branch.longitude" 
                            :lat-lng="[branch.latitude, branch.longitude]"
                        >
                            <l-popup>
                                <div class="text-gray-900 text-sm">
                                    <strong>📍 {{ branch.name }}</strong><br>
                                    {{ branch.address }}<br>
                                    <span class="text-xs text-gray-500">Centro de Operaciones</span>
                                </div>
                            </l-popup>
                        </l-marker>
                    </template>
                </l-map>
            </div>
    
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-lg">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-700 text-gray-300 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-6 py-4">Nombre</th>
                            <th class="px-6 py-4">Ciudad / Dirección</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 text-gray-400">
                        <tr v-for="branch in branches" :key="branch.id" class="hover:bg-gray-750 transition">
                            <td class="px-6 py-4">
                                <p class="text-white font-bold">{{ branch.name }}</p>
                                <p class="text-xs">{{ branch.phone || 'Sin teléfono' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-900 px-2 py-1 rounded text-xs border border-gray-600">{{ branch.city }}</span>
                                <p class="text-xs mt-1 truncate max-w-xs">{{ branch.address }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="branch.is_active" class="bg-green-900 text-green-300 px-2 py-1 rounded-full text-xs font-bold border border-green-700">Activa</span>
                                <span v-else class="bg-red-900 text-red-300 px-2 py-1 rounded-full text-xs font-bold border border-red-700">Inactiva</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link 
                                    :href="route('admin.branches.edit', branch.id)" 
                                    class="text-blue-400 hover:text-white font-bold text-sm"
                                >
                                    Editar
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="branches.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                No hay sucursales registradas. Crea la primera.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    </template>