<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import "leaflet/dist/leaflet.css";
    import { LMap, LTileLayer, LMarker, LPolygon, LTooltip, LCircleMarker } from "@vue-leaflet/vue-leaflet";
    
    const form = useForm({
        name: '',
        phone: '',
        city: 'La Paz',
        address: '',
        latitude: -16.5000, 
        longitude: -68.1500,
        coverage_polygon: [], 
        is_active: true
    });
    
    const zoom = ref(13);
    const center = ref([-16.5000, -68.1500]);
    
    // Arrastrar Pin
    const onMarkerDrag = (event) => {
        const { lat, lng } = event.target.getLatLng();
        form.latitude = lat;
        form.longitude = lng;
    };
    
    // Clic en Mapa (Dibuja puntos)
    const onMapClick = (event) => {
        if (event.latlng) {
            form.coverage_polygon.push([event.latlng.lat, event.latlng.lng]);
        }
    };
    
    const undoLastPoint = () => form.coverage_polygon.pop();
    const clearPolygon = () => form.coverage_polygon = [];
    
    const submit = () => {
        // Validación Frontend para evitar el error 422 del servidor
        if (form.coverage_polygon.length < 3) {
            alert("Debes marcar al menos 3 puntos en el mapa para definir el área de cobertura.");
            return;
        }
        form.post(route('admin.branches.store'));
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-5xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-white">Nueva Sucursal</h1>
                    <Link :href="route('admin.branches.index')" class="text-gray-400 hover:text-white transition">Cancelar</Link>
                </div>
    
                <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <div class="space-y-6">
                        <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg">
                            <h2 class="text-lg font-bold text-blue-400 mb-4">Información General</h2>
                            
                            <div class="mb-4">
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nombre</label>
                                <input v-model="form.name" type="text" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2 focus:border-blue-500" placeholder="Ej: Zona Sur">
                                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                            </div>
    
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Ciudad</label>
                                    <select v-model="form.city" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2">
                                        <option>La Paz</option>
                                        <option>El Alto</option>
                                        <option>Cochabamba</option>
                                        <option>Santa Cruz</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Teléfono</label>
                                    <input v-model="form.phone" type="text" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2">
                                </div>
                            </div>
    
                            <div class="mb-4">
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Dirección</label>
                                <textarea v-model="form.address" rows="2" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2"></textarea>
                            </div>
    
                            <div class="flex items-center bg-gray-900 p-3 rounded border border-gray-700">
                                <input v-model="form.is_active" type="checkbox" class="w-5 h-5 text-blue-600 bg-gray-800 border-gray-600 rounded">
                                <label class="ml-3 text-white text-sm font-bold">Sucursal Operativa</label>
                            </div>
                        </div>
                    </div>
    
                    <div class="bg-gray-800 p-1 rounded-lg border border-gray-700 flex flex-col shadow-lg">
                        <div class="p-4 bg-gray-800 rounded-t-lg z-10 flex justify-between items-start">
                            <div>
                                <h2 class="text-lg font-bold text-blue-400 flex items-center gap-2">📍 Cobertura</h2>
                                <p class="text-xs text-gray-400 mt-1">
                                    <span :class="form.coverage_polygon.length < 3 ? 'text-yellow-400 font-bold animate-pulse' : 'text-green-400'">
                                        Haz clic en el mapa: {{ form.coverage_polygon.length }} / 3 puntos mínimos.
                                    </span>
                                </p>
                            </div>
                            <div class="flex gap-2">
                                 <button type="button" @click="undoLastPoint" v-if="form.coverage_polygon.length > 0" class="bg-gray-700 hover:bg-gray-600 text-white text-xs px-2 py-1 rounded">Deshacer</button>
                                <button type="button" @click="clearPolygon" v-if="form.coverage_polygon.length > 0" class="bg-red-900/50 hover:bg-red-900 text-white text-xs px-2 py-1 rounded">Borrar</button>
                            </div>
                        </div>
                        
                        <div class="flex-1 bg-gray-900 relative h-96 w-full z-0 cursor-crosshair"> <l-map 
                                ref="map" 
                                v-model:zoom="zoom" 
                                :center="center" 
                                :use-global-leaflet="false"
                                @click="onMapClick"
                                class="h-full w-full rounded-b-lg"
                            >
                                <l-tile-layer
                                    url="https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"
                                    attribution="&copy; OpenStreetMap &copy; CARTO"
                                    layer-type="base"
                                    name="Dark Matter"
                                ></l-tile-layer>
    
                                <l-marker :lat-lng="[form.latitude, form.longitude]" draggable @dragend="onMarkerDrag">
                                    <l-tooltip :options="{ permanent: true, direction: 'top' }">Tienda</l-tooltip>
                                </l-marker>
    
                                <l-circle-marker v-for="(point, index) in form.coverage_polygon" :key="index" :lat-lng="point" :radius="4" color="#10b981" fill-color="#10b981" :fill-opacity="1" />
    
                                <l-polygon v-if="form.coverage_polygon.length > 0" :lat-lngs="form.coverage_polygon" color="#10b981" :fill="true" :fill-opacity="0.3"></l-polygon>
                            </l-map>
                        </div>
    
                        <div class="p-3 bg-red-900/20" v-if="form.errors.coverage_polygon">
                            <p class="text-red-400 text-xs font-bold text-center">⚠️ {{ form.errors.coverage_polygon }}</p>
                        </div>
                    </div>
    
                    <div class="col-span-1 lg:col-span-2 flex justify-end pt-4 border-t border-gray-700">
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-10 rounded-lg shadow-lg">
                            {{ form.processing ? 'Guardando...' : 'Guardar Sucursal' }}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    </template>