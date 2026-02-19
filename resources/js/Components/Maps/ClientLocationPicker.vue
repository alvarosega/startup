<script setup>
import { ref, onMounted, watch, computed, onUnmounted } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import debounce from 'lodash/debounce';
import { Loader2, MapPin, CheckCircle2, AlertOctagon, Navigation, Crosshair } from 'lucide-vue-next';
const coverageMessage = ref('');

const props = defineProps({
    modelValueLat: Number,
    modelValueLng: Number,
    modelValueAddress: String,
    activeBranches: { type: Array, default: () => [] },
    center: { type: Array, default: () => [-16.5000, -68.1500] },
    zoom: { type: Number, default: 16 }
});

const emit = defineEmits([
    'update:modelValueLat',
    'update:modelValueLng',
    'update:modelValueAddress',
    'update:modelValueBranchId',
    'coverage-status-change'
]);

// --- ESTADOS ---
const mapRef = ref(null);
const zoom = ref(props.zoom);
const mapReady = ref(false);
const isLocating = ref(false);
const isDragging = ref(false);
const isInsideCoverage = ref(false);
const closestBranchName = ref('');

const isValidCoordinate = (val) => typeof val === 'number' && !isNaN(val) && Math.abs(val) > 0.0001;

const hasSelectedLocation = computed(() => 
    isValidCoordinate(props.modelValueLat) && isValidCoordinate(props.modelValueLng)
);

const markerPosition = ref(
    hasSelectedLocation.value ? [props.modelValueLat, props.modelValueLng] : props.center
);

// --- ICONO TÁCTICO NEÓN ---
const createTacticalIcon = () => {
    return L.divIcon({
        className: 'tactical-pin-container',
        html: `
            <div class="relative flex flex-col items-center">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center border-[3px] border-white shadow-[0_0_20px_rgba(0,240,255,0.6)] animate-in zoom-in duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0A192F" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div class="w-1 h-4 bg-primary shadow-[0_0_10px_rgba(0,240,255,0.4)] -mt-1"></div>
            </div>
        `,
        iconSize: [40, 50],
        iconAnchor: [20, 50],
    });
};
const tacticalIcon = createTacticalIcon();

// --- LÓGICA ---

const updateLocationData = (lat, lng) => {
    markerPosition.value = [lat, lng];
    emit('update:modelValueLat', lat);
    emit('update:modelValueLng', lng);
    validateCoverage(lat, lng);
    reverseGeocode(lat, lng);
};

const onMapClick = (event) => {
    if (!isDragging.value) {
        updateLocationData(event.latlng.lat, event.latlng.lng);
    }
};

const locateUser = () => {
    if (!navigator.geolocation) return;
    isLocating.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            updateLocationData(latitude, longitude);
            mapRef.value?.leafletObject?.flyTo([latitude, longitude], 17, { duration: 1.5 });
            isLocating.value = false;
        },
        () => { isLocating.value = false; },
        { enableHighAccuracy: true, timeout: 10000 }
    );
};

const refreshMap = () => {
    if (mapRef.value?.leafletObject) {
        mapRef.value.leafletObject.invalidateSize();
    }
};

// Evitar que el mapa se rompa al redimensionar la ventana o el contenedor
let resizeObserver;
onMounted(() => {
    resizeObserver = new ResizeObserver(() => refreshMap());
    if (mapRef.value?.$el) resizeObserver.observe(mapRef.value.$el);
});

onUnmounted(() => {
    if (resizeObserver) resizeObserver.disconnect();
});

const isPointInPolygon = (point, vs) => {
    const formattedVs = vs.map(p => (p.lat && p.lng) ? [p.lat, p.lng] : p);
    let x = point[0], y = point[1], inside = false;
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
    
    if (props.activeBranches?.length > 0) {
        for (const branch of props.activeBranches) {
            // Ajuste de seguridad para el polígono
            const polygon = typeof branch.coverage_polygon === 'string' 
                ? JSON.parse(branch.coverage_polygon) 
                : branch.coverage_polygon;
                
            if (polygon && isPointInPolygon([lat, lng], polygon)) {
                covered = true;
                closestBranchName.value = branch.name;
                detectedBranchId = branch.id;
                break;
            }
        }
    }

    isInsideCoverage.value = covered;
    // ACTUALIZAR MENSAJE DE UX
    coverageMessage.value = covered 
        ? `📍 Estás en la zona de cobertura de ${closestBranchName.value}` 
        : `⚠️ Estás fuera de zona: Tu pedido será gestionado por la Central.`;

    emit('update:modelValueBranchId', detectedBranchId);
    emit('coverage-status-change', covered);
};

const reverseGeocode = debounce(async (lat, lng) => {
    try {
        // LLAMADA AL PROXY INTERNO (Resuelve CORS y 403)
        const response = await fetch(`/api/geo/reverse?lat=${lat}&lng=${lng}`);
        const data = await response.json();
        
        if (data?.address) {
            const a = data.address;
            const short = `${a.road || a.pedestrian || ''} ${a.house_number || ''}, ${a.neighbourhood || a.suburb || ''}`.trim();
            emit('update:modelValueAddress', short || data.display_name.split(',')[0]);
        }
    } catch (e) { 
        console.error("Error en Geocodificación:", e); 
    }
}, 800);

const onMapReady = () => {
    mapReady.value = true;
    setTimeout(refreshMap, 300);
    if (hasSelectedLocation.value) {
        validateCoverage(props.modelValueLat, props.modelValueLng);
    }
};

defineExpose({ refreshMap });
</script>

<template>
    <div class="flex flex-col h-full bg-background overflow-hidden border border-border/50">
        
        <div class="relative flex-1 bg-muted/10 isolate">
            <l-map ref="mapRef" 
                   v-model:zoom="zoom" 
                   :center="center" 
                   :use-global-leaflet="false"
                   :options="{ zoomControl: false, attributionControl: false }"
                   @ready="onMapReady"
                   @click="onMapClick" 
                   class="h-full w-full outline-none z-0">
                
                <l-tile-layer 
                    url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
                    class-name="map-tiles"
                />
                
                <l-marker 
                    v-if="hasSelectedLocation"
                    :lat-lng="markerPosition" 
                    :draggable="true" 
                    :icon="tacticalIcon"
                    :z-index-offset="1000"
                    @dragend="(e) => updateLocationData(e.target.getLatLng().lat, e.target.getLatLng().lng)"
                />
            </l-map>

            <div class="absolute top-4 left-0 right-0 z-[500] flex justify-center pointer-events-none px-4">
                <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform -translate-y-4 opacity-0" enter-to-class="transform translate-y-0 opacity-100">
                    <div v-if="hasSelectedLocation" 
                         class="backdrop-blur-xl border shadow-2xl rounded-2xl px-4 py-2 flex items-center gap-3 transition-all duration-500"
                         :class="isInsideCoverage ? 'bg-success/20 border-success/40 text-success' : 'bg-error/20 border-error/40 text-error'">
                        <div class="relative flex items-center justify-center">
                            <span class="absolute w-3 h-3 rounded-full bg-current animate-ping opacity-40"></span>
                            <CheckCircle2 v-if="isInsideCoverage" :size="16" stroke-width="3" />
                            <AlertOctagon v-else :size="16" stroke-width="3" />
                        </div>
                        <span class="text-[11px] font-black uppercase tracking-[0.1em]">
                            {{ coverageMessage }}
                        </span>
                    </div>
                </Transition>
            </div>

            <div class="absolute bottom-4 right-4 z-[500] flex flex-col gap-2">
                <button type="button" @click.prevent="locateUser"
                        class="w-12 h-12 rounded-2xl bg-card border border-border shadow-xl flex items-center justify-center text-primary hover:scale-105 active:scale-95 transition-all group">
                    <Loader2 v-if="isLocating" :size="24" class="animate-spin" />
                    <Navigation v-else :size="24" class="group-hover:drop-shadow-[0_0_8px_rgba(0,240,255,0.6)]" fill="currentColor" />
                </button>
            </div>
        </div>

        <div class="bg-card/80 backdrop-blur-md border-t border-border p-4 flex items-center gap-4 shrink-0 relative z-[600]">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors shadow-inner shrink-0" 
                 :class="hasSelectedLocation ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'">
                <MapPin :size="20" stroke-width="2.5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-1 leading-none">
                    {{ hasSelectedLocation ? 'Punto de Entrega Confirmado' : 'Esperando Ubicación' }}
                </p>
                <p class="text-sm font-bold text-navy truncate leading-tight">
                    {{ hasSelectedLocation ? (modelValueAddress || 'Buscando dirección...') : 'Selecciona un punto en el mapa' }}
                </p>
            </div>
            <div v-if="!hasSelectedLocation" class="animate-pulse">
                <Crosshair :size="18" class="text-primary/40" />
            </div>
        </div>
    </div>
</template>

<style>
/* Filtro para el mapa en modo oscuro */
.dark .map-tiles {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
}

.leaflet-container {
    background: transparent !important;
}

.leaflet-div-icon {
    background: transparent !important;
    border: none !important;
}

/* Evitar que el mapa robe el foco de eventos touch del padre */
.leaflet-control-container {
    pointer-events: auto;
}
</style>