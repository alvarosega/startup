<script setup>
import { ref, onMounted, watch } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LControlZoom, LTooltip } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import debounce from 'lodash/debounce';

const props = defineProps({
    modelValueLat: Number,
    modelValueLng: Number,
    modelValueAddress: String,
    activeBranches: { type: Array, default: () => [] },
    center: { type: Array, default: () => [-16.5000, -68.1500] },
    height: { type: String, default: '400px' },
    zoom: { type: Number, default: 15 }
});

const emit = defineEmits([
    'update:modelValueLat', 
    'update:modelValueLng', 
    'update:modelValueAddress', 
    'update:modelValueBranchId', 
    'coverage-status-change'
]);

const zoom = ref(props.zoom);
const mapRef = ref(null);
const center = ref(props.center);
const isInsideCoverage = ref(false);
const closestBranchName = ref('');

// Posición inicial segura
const markerPosition = ref(
    (isValidCoordinate(props.modelValueLat) && isValidCoordinate(props.modelValueLng))
        ? [props.modelValueLat, props.modelValueLng] 
        : props.center
);

// Función auxiliar para validar coordenadas numéricas
function isValidCoordinate(val) {
    return typeof val === 'number' && !isNaN(val) && val !== 0;
}

// --- ICONOS ---
const userIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

const storeIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

// --- LÓGICA ---

const onMarkerDragEnd = (event) => {
    const { lat, lng } = event.target.getLatLng();
    markerPosition.value = [lat, lng];
    emit('update:modelValueLat', lat);
    emit('update:modelValueLng', lng);
    validateCoverage(lat, lng);
    reverseGeocode(lat, lng);
};

// Watcher corregido para evitar bucles o datos inválidos
watch(
    () => [props.modelValueLat, props.modelValueLng], 
    ([newLat, newLng]) => {
        if (isValidCoordinate(newLat) && isValidCoordinate(newLng)) {
            const newPos = [newLat, newLng];
            markerPosition.value = newPos; 
            
            // Solo movemos el centro si está muy lejos (opcional)
            if (mapRef.value && mapRef.value.leafletObject) {
                mapRef.value.leafletObject.flyTo(newPos, 16);
            }
            
            validateCoverage(newLat, newLng);
        }
    }
);

// Validación de Cobertura
const isPointInPolygon = (point, vs) => {
    // Protección contra polígonos mal formados
    if (!vs || !Array.isArray(vs) || vs.length < 3) return false;

    const formattedVs = vs.map(p => Array.isArray(p) ? p : [p.lat, p.lng]);
    let x = point[0], y = point[1];
    let inside = false;
    for (let i = 0, j = formattedVs.length - 1; i < formattedVs.length; j = i++) {
        let xi = formattedVs[i][0], yi = formattedVs[i][1];
        let xj = formattedVs[j][0], yj = formattedVs[j][1];
        let intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
        if (intersect) inside = !inside;
    }
    return inside;
};

const validateCoverage = (lat, lng) => {
    let covered = false;
    let detectedBranchId = null;
    closestBranchName.value = '';

    if (props.activeBranches && props.activeBranches.length > 0) {
        for (const branch of props.activeBranches) {
            // Validamos que la sucursal tenga polígono válido
            if (branch.coverage_polygon) {
                if (isPointInPolygon([lat, lng], branch.coverage_polygon)) {
                    covered = true;
                    closestBranchName.value = branch.name;
                    detectedBranchId = branch.id;
                    break;
                }
            }
        }
    }

    isInsideCoverage.value = covered;
    emit('update:modelValueBranchId', detectedBranchId);
    emit('coverage-status-change', covered);
};

// Geocoding
const reverseGeocode = debounce(async (lat, lng) => {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
        const data = await response.json();
        if (data && data.display_name) {
            const shortAddress = data.display_name.split(',').slice(0, 3).join(',');
            emit('update:modelValueAddress', shortAddress);
        }
    } catch (error) {
        // Silencioso
    }
}, 500);

const refreshMap = () => {
    if (mapRef.value && mapRef.value.leafletObject) {
        mapRef.value.leafletObject.invalidateSize();
    }
};

onMounted(() => {
    // Si hay coordenadas iniciales válidas, validar
    if (isValidCoordinate(props.modelValueLat) && isValidCoordinate(props.modelValueLng)) {
        validateCoverage(props.modelValueLat, props.modelValueLng);
    }
});

defineExpose({ refreshMap });
</script>

<template>
    <div class="space-y-3 relative">
        <div class="rounded-xl border-2 border-indigo-100 overflow-hidden relative shadow-sm" :style="{ height: height }">
            <l-map ref="mapRef" 
                   v-model:zoom="zoom" 
                   :center="center" 
                   :use-global-leaflet="false"
                   class="h-full w-full z-0">
                
                <l-tile-layer 
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                    attribution='&copy; OpenStreetMap contributors'
                />
                
                <l-control-zoom position="bottomright" />

                <template v-for="branch in activeBranches" :key="'branch-'+branch.id">
                    <l-marker 
                        v-if="branch.latitude && branch.longitude" 
                        :lat-lng="[parseFloat(branch.latitude), parseFloat(branch.longitude)]"
                        :icon="storeIcon"
                    >
                        <l-tooltip>{{ branch.name }}</l-tooltip>
                    </l-marker>
                </template>
                
                <l-marker 
                    :lat-lng="markerPosition" 
                    :draggable="true" 
                    :icon="userIcon"
                    :z-index-offset="1000"
                    @dragend="onMarkerDragEnd"
                >
                    <l-tooltip :options="{ permanent: true, direction: 'top', offset: [0, -40] }">
                        <div class="text-center px-1">
                            <span v-if="isInsideCoverage" class="text-green-600 font-black text-xs block">
                                ✅ ¡Aquí entregamos!
                            </span>
                            <span v-else class="text-red-500 font-black text-xs block">
                                ❌ Sin cobertura
                            </span>
                            <span class="text-[10px] text-gray-500 font-normal">Arrástrame para ajustar</span>
                        </div>
                    </l-tooltip>
                </l-marker>
            </l-map>
            
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-[1000] w-max max-w-[90%]">
                <div v-if="isInsideCoverage" 
                     class="bg-green-600 text-white px-4 py-2 rounded-full text-xs font-bold shadow-xl flex items-center gap-2 animate-bounce-short">
                    <span>✓ Cobertura: {{ closestBranchName }}</span>
                </div>
                <div v-else 
                     class="bg-gray-800 text-white px-4 py-2 rounded-full text-xs font-bold shadow-xl flex items-center gap-2">
                    <span>⚠️ Solo Recojo en Tienda</span>
                </div>
            </div>
        </div>
        
        <p class="text-[10px] text-center text-gray-400">
            * Mueve el mapa o arrastra el pin rojo <span class="text-red-500 text-base leading-none">📍</span> hasta tu ubicación exacta.
        </p>
    </div>
</template>

<style scoped>
@keyframes bounce-short {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-3px); }
}
.animate-bounce-short {
  animation: bounce-short 1s infinite;
}
</style>