<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import debounce from 'lodash/debounce';
import { Locate, Loader2, MapPin, CheckCircle2, AlertOctagon, Navigation } from 'lucide-vue-next';

const props = defineProps({
    modelValueLat: Number,
    modelValueLng: Number,
    modelValueAddress: String,
    activeBranches: { type: Array, default: () => [] },
    center: { type: Array, default: () => [-16.5000, -68.1500] },
    height: { type: String, default: '100%' },
    zoom: { type: Number, default: 16 } 
});

const emit = defineEmits([
    'update:modelValueLat', 
    'update:modelValueLng', 
    'update:modelValueAddress', 
    'update:modelValueBranchId', 
    'coverage-status-change'
]);

const isLocating = ref(false);
const isDragging = ref(false); // Nuevo estado para animación
const zoom = ref(props.zoom);
const mapRef = ref(null);
const center = ref(props.center);
const isInsideCoverage = ref(false);
const closestBranchName = ref('');
const mapReady = ref(false);

function isValidCoordinate(val) {
    return typeof val === 'number' && !isNaN(val) && val !== 0;
}

const markerPosition = ref(
    (isValidCoordinate(props.modelValueLat) && isValidCoordinate(props.modelValueLng))
        ? [props.modelValueLat, props.modelValueLng] 
        : props.center
);

// --- PIN TÁCTICO VECTORIAL (Tailwind CSS) ---
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

// --- LÓGICA DE GEOLOCALIZACIÓN ---
const locateUser = () => {
    if (!navigator.geolocation) {
        alert("Tu navegador no soporta geolocalización.");
        return;
    }
    isLocating.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            updateLocationData(latitude, longitude);
            if (mapRef.value && mapRef.value.leafletObject) {
                mapRef.value.leafletObject.flyTo([latitude, longitude], 17, { duration: 1.5 });
            }
            isLocating.value = false;
        },
        (error) => {
            console.error("Error ubicación:", error);
            isLocating.value = false;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
};

const onMapReady = () => {
    mapReady.value = true;
    setTimeout(() => {
        if (mapRef.value && mapRef.value.leafletObject) {
            mapRef.value.leafletObject.invalidateSize();
            if (isValidCoordinate(props.modelValueLat)) {
                mapRef.value.leafletObject.setView([props.modelValueLat, props.modelValueLng], props.zoom);
            }
        }
    }, 400); // Aumentado ligeramente para asegurar render en modales
};

// Eventos de Arrastre para Animación
const onDragStart = () => { isDragging.value = true; };
const onDragEnd = (event) => {
    isDragging.value = false;
    const { lat, lng } = event.target.getLatLng();
    updateLocationData(lat, lng);
};

const updateLocationData = (lat, lng) => {
    markerPosition.value = [lat, lng];
    emit('update:modelValueLat', lat);
    emit('update:modelValueLng', lng);
    validateCoverage(lat, lng);
    reverseGeocode(lat, lng);
};

// Watch para sincronización externa
watch(() => [props.modelValueLat, props.modelValueLng], ([newLat, newLng]) => {
    if (isValidCoordinate(newLat) && isValidCoordinate(newLng)) {
        const newPos = [newLat, newLng];
        if (markerPosition.value[0] !== newLat || markerPosition.value[1] !== newLng) {
            markerPosition.value = newPos; 
            if (mapReady.value && mapRef.value?.leafletObject) {
                mapRef.value.leafletObject.flyTo(newPos, props.zoom);
            }
            validateCoverage(newLat, newLng);
        }
    }
});

// Validación Polígonos (Lógica)
const isPointInPolygon = (point, vs) => {
    if (!vs || !Array.isArray(vs) || vs.length < 3) return false;
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
            if (branch.coverage_polygon && isPointInPolygon([lat, lng], branch.coverage_polygon)) {
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
            const district = addr.neighbourhood || addr.suburb || addr.city_district || addr.city || '';
            
            let shortAddress = street ? `${street} ${number}, ${district}`.trim() : data.display_name.split(',').slice(0, 3).join(',');
            if (shortAddress.endsWith(',')) shortAddress = shortAddress.slice(0, -1);
            
            emit('update:modelValueAddress', shortAddress);
        }
    } catch (error) { console.error("Error geocoding:", error); }
}, 800);

onMounted(() => {
    if (props.modelValueLat === -16.5000 && props.modelValueLng === -68.1500) {
        locateUser();
    } else if (isValidCoordinate(props.modelValueLat)) {
        validateCoverage(props.modelValueLat, props.modelValueLng);
    }
});
</script>

<template>
    <div class="relative w-full h-full bg-muted/20 group">
        
        <l-map ref="mapRef" 
               v-model:zoom="zoom" 
               :center="center" 
               :use-global-leaflet="false"
               :options="{ zoomControl: false }"
               @ready="onMapReady"
               class="h-full w-full outline-none z-0">
            
            <l-tile-layer 
                url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
                attribution='&copy; CartoDB'
                class-name="map-tiles"
            />
            
            <l-marker 
                :lat-lng="markerPosition" 
                :draggable="true" 
                :icon="tacticalIcon"
                :z-index-offset="1000"
                @dragstart="onDragStart"
                @dragend="onDragEnd"
            />
        </l-map>

        <div class="absolute top-4 left-4 right-4 z-[400] flex justify-center pointer-events-none">
            <div class="backdrop-blur-md border shadow-lg rounded-full px-4 py-2 flex items-center gap-2 transition-all duration-300"
                 :class="isInsideCoverage 
                    ? 'bg-success/10 border-success/30 text-success' 
                    : 'bg-destructive/10 border-destructive/30 text-destructive'">
                
                <div class="p-1 rounded-full shrink-0" :class="isInsideCoverage ? 'bg-success text-white' : 'bg-destructive text-white'">
                    <CheckCircle2 v-if="isInsideCoverage" :size="12" stroke-width="3" />
                    <AlertOctagon v-else :size="12" stroke-width="3" />
                </div>
                
                <div class="flex flex-col leading-none">
                    <span class="text-[10px] font-black uppercase tracking-widest opacity-80">
                        {{ isInsideCoverage ? 'Zona Cubierta' : 'Fuera de Zona' }}
                    </span>
                    <span v-if="isInsideCoverage" class="text-xs font-bold truncate max-w-[150px]">
                        {{ closestBranchName }}
                    </span>
                </div>
            </div>
        </div>

        <div class="absolute bottom-4 left-4 right-4 z-[400] flex items-end gap-3 pointer-events-none">
            
            <div class="flex-1 bg-card/90 backdrop-blur-xl border border-border p-3 rounded-2xl shadow-xl pointer-events-auto min-w-0">
                <div class="flex items-start gap-2.5">
                    <div class="mt-0.5 text-primary animate-bounce-slow">
                        <MapPin :size="18" fill="currentColor" class="opacity-20" />
                        <MapPin :size="18" class="absolute top-0 left-0" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] text-muted-foreground font-black uppercase tracking-wider mb-0.5">Ubicación del Pin</p>
                        <p class="text-sm font-bold text-foreground leading-tight line-clamp-2">
                            {{ modelValueAddress || 'Arrastra el pin para ubicarte...' }}
                        </p>
                    </div>
                </div>
            </div>

            <button 
                @click.prevent="locateUser"
                class="w-12 h-12 rounded-2xl bg-primary text-primary-foreground shadow-xl shadow-primary/30 flex items-center justify-center hover:bg-primary/90 active:scale-90 transition-all pointer-events-auto border border-white/20"
                title="Mi Ubicación"
            >
                <Loader2 v-if="isLocating" :size="24" class="animate-spin" />
                <Navigation v-else :size="24" fill="currentColor" class="opacity-100" />
            </button>
        </div>

        <div class="absolute inset-0 pointer-events-none shadow-[inset_0_0_60px_rgba(0,0,0,0.1)] z-[100] rounded-xl"></div>
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