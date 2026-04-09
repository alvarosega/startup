<script setup>
import { ref, onMounted, nextTick } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import debounce from 'lodash/debounce';
import { Loader2, Navigation, MapPin, Bug, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
    modelValueLat: [Number, String],
    modelValueLng: [Number, String],
    modelValueAddress: String,
    activeBranches: { type: Array, default: () => [] },
    center: { type: Array, default: () => [-16.5000, -68.1500] },
    zoom: { type: Number, default: 15 }
});

const emit = defineEmits(['update:modelValueLat', 'update:modelValueLng', 'update:modelValueAddress', 'update:modelValueBranchId']);

const mapRef = ref(null);
const currentZoom = ref(props.zoom);
const currentCenter = ref(props.center);

const isLocating = ref(false);
const locationActivated = ref(props.modelValueLat && Math.abs(props.modelValueLat) > 1);
const mapMoving = ref(false);
const isFallbackMode = ref(false);
const currentBranchId = ref(null);

const mapUrl = "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png";

const isInsidePolygon = (lat, lng, polygon) => {
    const points = Array.isArray(polygon[0][0]) ? polygon[0] : polygon;
    if (!points || points.length < 3) return false;
    let inside = false;
    for (let i = 0, j = points.length - 1; i < points.length; j = i++) {
        const xi = points[i][0], yi = points[i][1];
        const xj = points[j][0], yj = points[j][1];
        const intersect = ((yi > lng) !== (yj > lng)) && (lat < (xj - xi) * (lng - yi) / (yj - yi) + xi);
        if (intersect) inside = !inside;
    }
    return inside;
};

const checkBranchCoverage = (lat, lng) => {
    currentBranchId.value = null; 
    for (const branch of props.activeBranches) {
        if (branch.polygon && isInsidePolygon(lat, lng, branch.polygon)) {
            currentBranchId.value = branch.id;
            break;
        }
    }
    emit('update:modelValueBranchId', currentBranchId.value);
};

// CORRECCIÓN: Mejor motor de Reverse Geocoding para LATAM
const reverseGeocode = debounce(async (lat, lng) => {
    if (mapMoving.value) return;
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
        const data = await response.json();
        
        if (data?.address) {
            const a = data.address;
            // Busca por jerarquía (Si no hay calle, saca el barrio o recinto)
            const streetName = a.road || a.pedestrian || a.path || a.suburb || a.neighbourhood || a.residential || '';
            const houseNumber = a.house_number || '';
            let short = `${streetName} ${houseNumber}`.trim();

            // Fallback absoluto: Si todo falla, extrae el primer segmento del nombre completo
            if (!short && data.display_name) {
                short = data.display_name.split(',')[0].trim();
            }

            emit('update:modelValueAddress', short || "Punto de entrega (Edite nombre)");
        } else {
            emit('update:modelValueAddress', "Punto seleccionado (Edite nombre)");
        }
    } catch (e) { 
        emit('update:modelValueAddress', "Detalle manual requerido"); 
    }
}, 600);

const handleMapMove = () => {
    if (!locationActivated.value || !mapRef.value?.leafletObject) return;
    const center = mapRef.value.leafletObject.getCenter();
    emit('update:modelValueLat', center.lat);
    emit('update:modelValueLng', center.lng);
    checkBranchCoverage(center.lat, center.lng);
    emit('update:modelValueAddress', 'Analizando punto satelital...');
    reverseGeocode(center.lat, center.lng);
};

const locateUser = () => {
    isLocating.value = true;
    if (!navigator.geolocation) { activateManualSelection(); return; }
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            locationActivated.value = true;
            currentCenter.value = [latitude, longitude];
            nextTick(() => {
                if(mapRef.value?.leafletObject) {
                    mapRef.value.leafletObject.invalidateSize();
                    mapRef.value.leafletObject.flyTo([latitude, longitude], 18);
                }
            });
            setTimeout(() => { isLocating.value = false; handleMapMove(); }, 1600);
        },
        () => { isFallbackMode.value = true; activateManualSelection(); },
        { enableHighAccuracy: true, timeout: 8000 }
    );
};

const activateManualSelection = () => {
    locationActivated.value = true;
    isLocating.value = false;
    nextTick(() => {
        if(mapRef.value?.leafletObject) {
            mapRef.value.leafletObject.invalidateSize();
            mapRef.value.leafletObject.setView(props.center, 16);
            handleMapMove();
        }
    });
};

onMounted(() => {
    if (locationActivated.value) {
        reverseGeocode(props.modelValueLat, props.modelValueLng);
        checkBranchCoverage(props.modelValueLat, props.modelValueLng);
    }
});
</script>

<template>
    <div class="relative w-full h-full min-h-[100%] overflow-hidden flex flex-col">
        
        <l-map ref="mapRef" 
               v-model:zoom="currentZoom" 
               v-model:center="currentCenter"
               :use-global-leaflet="false"
               :options="{ zoomControl: false, attributionControl: false }"
               @movestart="mapMoving = true"
               @moveend="mapMoving = false; handleMapMove()"
               class="absolute inset-0 h-full w-full z-0 dark-map-filter">
            <l-tile-layer :url="mapUrl" />
        </l-map>

        <div v-if="locationActivated" class="absolute inset-0 flex items-center justify-center pointer-events-none z-[1000]">
            <div class="relative flex flex-col items-center translate-y-[-22px]" :class="{ 'scale-110 -translate-y-10': mapMoving }">
                <div class="w-10 h-10 rounded-full bg-background shadow-2xl flex items-center justify-center border-[3px]"
                     :class="currentBranchId ? 'border-primary' : 'border-destructive'">
                    <div class="w-2 h-2 rounded-full" :class="currentBranchId ? 'bg-primary animate-ping' : 'bg-destructive'"></div>
                </div>
                <div class="w-1 h-6 transition-colors" :class="currentBranchId ? 'bg-primary' : 'bg-destructive'"></div>
            </div>
        </div>

        <div v-if="!locationActivated" 
             class="fixed inset-0 z-[10001] du-cyber-canvas flex flex-col items-center justify-center p-6 text-center animate-in fade-in duration-500">
            <div class="w-full max-w-[340px] glass-chassis p-8 flex flex-col items-center">
                <div class="w-20 h-20 bg-primary/10 rounded-[2.5rem] flex items-center justify-center text-primary mb-6 shadow-inner">
                    <MapPin :size="40" stroke-width="2.5" />
                </div>
                <h2 class="text-3xl font-black text-foreground uppercase italic mb-2 tracking-tighter">Ubicación</h2>
                <p class="text-[10px] text-foreground/40 font-black uppercase tracking-[0.2em] mb-10 leading-relaxed">
                    Recomendamos <span class="text-primary">GPS</span> para máxima precisión.
                </p>
                <div class="flex flex-col w-full gap-4">
                    <button @click="locateUser" class="bg-foreground text-background w-full h-14 rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-2xl flex items-center justify-center gap-4 active:scale-95 transition-all">
                        <Navigation :size="18" fill="currentColor" /> Sincronizar GPS
                    </button>
                    <button @click="activateManualSelection" class="bg-foreground/5 text-foreground border border-border/40 w-full h-14 rounded-2xl font-black uppercase text-[10px] tracking-[0.1em] active:scale-95 transition-all">
                        Selección Manual
                    </button>
                </div>
            </div>
        </div>
        
        <div v-if="isLocating" class="fixed inset-0 z-[10002] glass-chassis flex flex-col items-center justify-center animate-in fade-in">
            <Loader2 :size="48" class="text-primary animate-spin mb-6" />
            <p class="font-black text-[10px] uppercase tracking-[0.3em] text-foreground animate-pulse">Sincronizando satélites...</p>
        </div>

        <button v-if="locationActivated && !isLocating" @click="locateUser" 
                class="absolute bottom-[200px] md:bottom-[220px] right-6 z-[11000] w-12 h-12 bg-background rounded-2xl shadow-2xl flex items-center justify-center text-primary border border-border/40 active:scale-95 transition-all">
            <Navigation :size="20" />
        </button>
    </div>
</template>

<style scoped>
.du-cyber-canvas { background-color: hsl(var(--background)); background-image: linear-gradient(to bottom, hsl(var(--background)) 0%, transparent 100%), linear-gradient(to right, #0ed2da33, #5f29c733); }
.glass-chassis { background: hsl(var(--background) / 0.85); backdrop-filter: blur(40px) saturate(200%); border: 1px solid hsl(var(--border) / 0.4); border-radius: 2.5rem; }
.dark-map-filter { transition: filter 0.5s ease; }
.dark .dark-map-filter { filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%); }
</style>