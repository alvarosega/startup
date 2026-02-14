<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import debounce from 'lodash/debounce';
import { Loader2, MapPin, CheckCircle2, AlertOctagon, Navigation } from 'lucide-vue-next';

const props = defineProps({
    modelValueLat: Number,
    modelValueLng: Number,
    modelValueAddress: String,
    activeBranches: { type: Array, default: () => [] },
    center: { type: Array, default: () => [-16.5000, -68.1500] }, // La Paz Default
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

// --- HELPERS ---
// Validamos que sea número y no sea 0 o valores por defecto "vacíos"
const isValidCoordinate = (val) => {
    return typeof val === 'number' && !isNaN(val) && val !== 0 && Math.abs(val) > 0.0001;
};

// Computed: ¿El usuario ya eligió una ubicación?
// Esto controla si mostramos el Pin y el texto de dirección
const hasSelectedLocation = computed(() => 
    isValidCoordinate(props.modelValueLat) && isValidCoordinate(props.modelValueLng)
);

// Posición visual del marcador
// Si hay datos válidos, los usa. Si no, usa el centro del mapa (pero lo ocultamos con v-if)
const markerPosition = ref(
    hasSelectedLocation.value
        ? [props.modelValueLat, props.modelValueLng] 
        : props.center
);

// --- ICONO TÁCTICO ---
const createTacticalIcon = () => {
    return L.divIcon({
        className: 'tactical-pin',
        html: `
            <div class="relative flex flex-col items-center justify-center -mt-[40px]">
                <div class="pin-head relative z-20 w-10 h-10 rounded-full bg-primary text-primary-foreground shadow-[0_4px_15px_rgba(0,0,0,0.3)] flex items-center justify-center border-[3px] border-white dark:border-gray-900 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div class="pin-point w-1 h-4 bg-primary/80 rounded-full mt-[-2px] z-10"></div>
                <div class="pin-shadow w-4 h-1.5 bg-black/20 rounded-[100%] blur-[2px] mt-[-1px]"></div>
            </div>
        `,
        iconSize: [40, 50],
        iconAnchor: [20, 50],
    });
};
const tacticalIcon = createTacticalIcon();

// --- LÓGICA PRINCIPAL ---

// 1. Centralizador de Actualización
const updateLocationData = (lat, lng) => {
    markerPosition.value = [lat, lng];
    // Emitimos al padre
    emit('update:modelValueLat', lat);
    emit('update:modelValueLng', lng);
    // Ejecutamos validaciones
    validateCoverage(lat, lng);
    reverseGeocode(lat, lng);
};

// 2. Click en el Mapa (Selección Manual)
const onMapClick = (event) => {
    if (!isDragging.value) {
        const { lat, lng } = event.latlng;
        updateLocationData(lat, lng);
    }
};

// 3. Geolocalización (Botón GPS)
const locateUser = () => {
    if (!navigator.geolocation) return alert("Tu navegador no soporta geolocalización.");
    
    isLocating.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            updateLocationData(latitude, longitude);
            
            // Volar hacia la ubicación
            if (mapRef.value && mapRef.value.leafletObject) {
                mapRef.value.leafletObject.flyTo([latitude, longitude], 17, { duration: 1.5 });
            }
            isLocating.value = false;
        },
        (error) => {
            console.error("Error GPS:", error);
            isLocating.value = false;
            // No hacemos alert intrusivo, solo log
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
};

// 4. Arrastrar Marcador
const onDragStart = () => { isDragging.value = true; };
const onDragEnd = (event) => {
    isDragging.value = false;
    const { lat, lng } = event.target.getLatLng();
    updateLocationData(lat, lng);
};

// --- UTILIDADES ---

// Refrescar Mapa (CRÍTICO: Llamado por el padre RegisterForm)
const refreshMap = () => {
    if (mapRef.value && mapRef.value.leafletObject) {
        mapRef.value.leafletObject.invalidateSize();
    }
};

// Validar Polígono
const isPointInPolygon = (point, vs) => {
    if (!vs || !Array.isArray(vs) || vs.length < 3) return false;
    // Normalizar si viene como array de objetos o array de arrays
    const formattedVs = vs.map(p => (p.lat && p.lng) ? [p.lat, p.lng] : p);
    
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

    if (props.activeBranches?.length > 0) {
        for (const branch of props.activeBranches) {
            // Parseamos si viene como string JSON
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
    emit('update:modelValueBranchId', detectedBranchId);
    emit('coverage-status-change', covered);
};

// Geocodificación (Debounced)
const reverseGeocode = debounce(async (lat, lng) => {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
            headers: { 'Accept-Language': 'es' }
        });
        const data = await response.json();
        if (data && data.display_name) {
            const addr = data.address;
            const street = addr.road || addr.pedestrian || '';
            const number = addr.house_number || '';
            const district = addr.neighbourhood || addr.suburb || addr.city_district || '';
            
            let shortAddress = street ? `${street} ${number}, ${district}`.trim() : data.display_name.split(',').slice(0, 2).join(',');
            if (shortAddress.endsWith(',')) shortAddress = shortAddress.slice(0, -1);
            
            emit('update:modelValueAddress', shortAddress);
        }
    } catch (error) { console.error("Error geocoding:", error); }
}, 800);

// --- CICLO DE VIDA ---
const onMapReady = () => {
    mapReady.value = true;
    // Pequeño delay para asegurar que el contenedor tenga tamaño
    setTimeout(() => {
        refreshMap();
        if (hasSelectedLocation.value) {
            mapRef.value?.leafletObject?.setView(markerPosition.value, props.zoom);
            validateCoverage(markerPosition.value[0], markerPosition.value[1]);
        }
    }, 400);
};

// Watch para cambios externos (Re-sincronización)
watch(() => [props.modelValueLat, props.modelValueLng], ([newLat, newLng]) => {
    if (isValidCoordinate(newLat) && isValidCoordinate(newLng)) {
        const newPos = [newLat, newLng];
        // Solo actualizamos si la diferencia es significativa (evita loops infinitos)
        if (Math.abs(markerPosition.value[0] - newLat) > 0.00001 || Math.abs(markerPosition.value[1] - newLng) > 0.00001) {
            markerPosition.value = newPos;
            if (mapReady.value && mapRef.value?.leafletObject) {
                mapRef.value.leafletObject.flyTo(newPos, props.zoom);
            }
            validateCoverage(newLat, newLng);
        }
    }
});

// EXPOSICIÓN (Vital para el padre)
defineExpose({
    refreshMap
});
</script>
<template>
    <div class="flex flex-col h-full gap-0">
        
        <div class="relative flex-1 bg-muted/20 isolate overflow-hidden">
            <l-map ref="mapRef" 
                   v-model:zoom="zoom" 
                   :center="center" 
                   :use-global-leaflet="false"
                   :options="{ zoomControl: false, tap: false }"
                   @ready="onMapReady"
                   @click="onMapClick" 
                   class="h-full w-full outline-none z-0 cursor-crosshair">
                
                <l-tile-layer 
                    url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
                    attribution='&copy; CartoDB'
                    class-name="map-tiles"
                />
                
                <l-marker 
                    v-if="hasSelectedLocation"
                    :lat-lng="markerPosition" 
                    :draggable="true" 
                    :icon="tacticalIcon"
                    :z-index-offset="1000"
                    @dragstart="onDragStart"
                    @dragend="onDragEnd"
                />
            </l-map>

            <div class="absolute top-3 left-0 right-0 z-[500] flex justify-center pointer-events-none">
                <Transition 
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform -translate-y-4 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-4 opacity-0"
                >
                    <div v-if="hasSelectedLocation" 
                         class="backdrop-blur-md border shadow-md rounded-full px-3 py-1.5 flex items-center gap-2"
                         :class="isInsideCoverage 
                            ? 'bg-success/90 border-success/30 text-white' 
                            : 'bg-destructive/90 border-destructive/30 text-white'">
                        
                        <CheckCircle2 v-if="isInsideCoverage" :size="12" stroke-width="3" />
                        <AlertOctagon v-else :size="12" stroke-width="3" />
                        
                        <span class="text-[10px] font-black uppercase tracking-widest">
                            {{ isInsideCoverage ? closestBranchName : 'Fuera de Cobertura' }}
                        </span>
                    </div>
                </Transition>
            </div>

            <div class="absolute bottom-3 right-3 z-[500]">
                <button 
                    type="button"
                    @click.prevent="locateUser"
                    class="w-10 h-10 rounded-xl bg-white dark:bg-zinc-800 text-primary shadow-lg shadow-black/10 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-zinc-700 active:scale-95 transition-all border border-gray-200 dark:border-zinc-700"
                    title="Usar mi ubicssssación actual"
                >
                    <Loader2 v-if="isLocating" :size="20" class="animate-spin" />
                    <Navigation v-else :size="20" fill="currentColor" class="opacity-100" />
                </button>
            </div>
        </div>

        <div class="bg-card border-t border-border p-3 flex items-center gap-3 shrink-0 relative z-[600]">
            <div class="p-2 rounded-full shrink-0 transition-colors" 
                 :class="hasSelectedLocation ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'">
                <MapPin :size="18" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-wider mb-0.5">
                    {{ hasSelectedLocation ? 'Ubicación seleccionada' : 'Sin ubicación' }}
                </p>
                <p class="text-xs font-medium text-foreground truncate leading-tight" :title="modelValueAddress">
                    {{ hasSelectedLocation ? (modelValueAddress || 'Cargando calle...') : 'Toca el mapa o usa el botón GPS para ubicarte.' }}
                </p>
            </div>
        </div>
    </div>
</template>

<style>
/* Animaciones y Estilos Globales Leaflet */

/* Modo Oscuro para Mapas */
.dark .map-tiles {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
}

.leaflet-div-icon {
    background: transparent !important;
    border: none !important;
}

/* Animación del Pin al Arrastrar */
.leaflet-marker-draggable {
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.leaflet-dragging .pin-head {
    transform: translateY(-10px) scale(1.1);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
}
.leaflet-dragging .pin-point {
    height: 10px;
    opacity: 0.5;
}
.leaflet-dragging .pin-shadow {
    opacity: 0.5;
    transform: scale(0.5);
}

@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}
.animate-bounce-slow {
    animation: bounce-slow 2s infinite;
}
</style>