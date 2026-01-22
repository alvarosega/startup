<script setup>
    import { ref, onMounted, watch } from 'vue';
    import "leaflet/dist/leaflet.css";
    import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
    import L from 'leaflet';
    
    const props = defineProps({
        modelValueLat: Number,
        modelValueLng: Number,
        modelValueAddress: String,
        // Nuevo Prop para v-model del branch_id
        modelValueBranchId: { type: [Number, String], default: null }, 
        activeBranches: { type: Array, default: () => [] } 
    });
    
    // Añadimos el emit 'update:modelValueBranchId'
    const emit = defineEmits(['update:modelValueLat', 'update:modelValueLng', 'update:modelValueAddress', 'update:modelValueBranchId', 'coverage-status-change']);
    
    // ... (Resto de la configuración de zoom, center, etc. igual que antes) ...
    const zoom = ref(15);
    const center = ref(props.modelValueLat ? [props.modelValueLat, props.modelValueLng] : [-16.5000, -68.1500]);
    const markerPosition = ref(props.modelValueLat ? [props.modelValueLat, props.modelValueLng] : [-16.5000, -68.1500]);
    const isInsideCoverage = ref(false);
    const closestBranchName = ref('');
    
    // --- ALGORITMO MATEMÁTICO: Ray Casting ---
    const isPointInPolygon = (point, vs) => {
        let x = point[0], y = point[1];
        let inside = false;
        for (let i = 0, j = vs.length - 1; i < vs.length; j = i++) {
            let xi = vs[i][0], yi = vs[i][1];
            let xj = vs[j][0], yj = vs[j][1];
            let intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
            if (intersect) inside = !inside;
        }
        return inside;
    };
    
    // --- VALIDACIÓN DE COBERTURA ---
    const validateCoverage = (lat, lng) => {
        let covered = false;
        let detectedBranchId = null; // Variable local para el ID
        closestBranchName.value = '';
    
        for (const branch of props.activeBranches) {
            if (branch.coverage_polygon && branch.coverage_polygon.length > 2) {
                if (isPointInPolygon([lat, lng], branch.coverage_polygon)) {
                    covered = true;
                    closestBranchName.value = branch.name;
                    detectedBranchId = branch.id; // ¡Capturamos el ID!
                    break; 
                }
            }
        }
    
        isInsideCoverage.value = covered;
        
        // Emitimos el ID encontrado (será un número si hay cobertura, o null si no)
        emit('update:modelValueBranchId', detectedBranchId);
        emit('coverage-status-change', covered);
    };
    
    // ... (Resto del onMarkerDragEnd y onMounted igual que antes) ...
    const onMarkerDragEnd = async (event) => {
        const { lat, lng } = event.target.getLatLng();
        markerPosition.value = [lat, lng];
        
        emit('update:modelValueLat', lat);
        emit('update:modelValueLng', lng);
    
        // Validamos y emitimos el ID
        validateCoverage(lat, lng);
    
        // Geocoding
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await response.json();
            if (data && data.display_name) {
                emit('update:modelValueAddress', data.display_name);
            }
        } catch (error) {
            console.error("Error geocoding:", error);
        }
    };
    
    onMounted(() => {
        // ... Fix iconos Leaflet ...
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
            iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
            shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
        });
        
        if (props.modelValueLat) {
            validateCoverage(props.modelValueLat, props.modelValueLng);
        }
    });
    </script>
    
    <template>
       <div class="space-y-3">
            <div class="h-[400px] rounded-lg border border-gray-300 overflow-hidden relative z-0 shadow-sm">
                <l-map v-model:zoom="zoom" :center="center" :use-global-leaflet="false">
                    <l-tile-layer url="https://tile.openstreetmap.org/{z}/{x}/{y}.png"></l-tile-layer>
                    <l-marker :lat-lng="markerPosition" draggable @dragend="onMarkerDragEnd"></l-marker>
                </l-map>
                <div class="absolute top-2 left-2 right-2 z-[1000] pointer-events-none">
                    <div v-if="isInsideCoverage" class="bg-green-500/90 text-white text-xs py-1 px-3 rounded-full inline-block shadow-md">
                        ✓ Cobertura Disponible ({{ closestBranchName }})
                    </div>
                </div>
            </div>
            <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="!isInsideCoverage" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded shadow-sm">
                     <p class="text-sm text-yellow-700 font-bold">Estás fuera de nuestra zona de reparto actual</p>
                     <p class="text-xs text-yellow-600 mt-1">Guardaremos tu dirección para futuras expansiones.</p>
                </div>
                <div v-else class="bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm">
                    <p class="text-sm text-green-700 font-bold">¡Tenemos cobertura!</p>
                    <p class="text-xs text-green-600 mt-1">Atendido por: <strong>{{ closestBranchName }}</strong></p>
                </div>
            </transition>
        </div>
    </template>