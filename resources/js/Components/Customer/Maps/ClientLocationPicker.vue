<script setup>
import { ref, onMounted, nextTick } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import debounce from 'lodash/debounce';
import { Loader2, Navigation, MapPin, Bug, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
    modelValueLat: [Number, String], // Aceptar String temporalmente para evitar el Warn
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
const showPrecisionHint = ref(false);
const isFallbackMode = ref(false); // Detecta si entramos por fallo de GPS

const debugLat = ref(props.modelValueLat || currentCenter.value[0]);
const debugLng = ref(props.modelValueLng || currentCenter.value[1]);
const currentBranchId = ref(null);

const mapUrl = "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png";

// Lógica de Geofencing (Sin cambios, es correcta)
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

const reverseGeocode = debounce(async (lat, lng) => {
    if (mapMoving.value) return;
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`);
        const data = await response.json();
        if (data?.address) {
            const a = data.address;
            const short = `${a.road || a.pedestrian || 'Calle sin nombre'} ${a.house_number || ''}`.trim();
            emit('update:modelValueAddress', short);
        } else {
            emit('update:modelValueAddress', "Punto seleccionado");
        }
    } catch (e) { 
        emit('update:modelValueAddress', "Ubicación manual");
    }
}, 600);

const handleMapMove = () => {
    if (!locationActivated.value || !mapRef.value?.leafletObject) return;
    const center = mapRef.value.leafletObject.getCenter();
    debugLat.value = center.lat;
    debugLng.value = center.lng;
    emit('update:modelValueLat', center.lat);
    emit('update:modelValueLng', center.lng);
    checkBranchCoverage(center.lat, center.lng);
    emit('update:modelValueAddress', 'Calculando dirección...');
    reverseGeocode(center.lat, center.lng);
};

/**
 * RECTIFICACIÓN: Función de Localización Resiliente
 */
const locateUser = () => {
    isLocating.value = true;
    isFallbackMode.value = false;

    if (!navigator.geolocation) {
        activateManualSelection(); // Fallback inmediato
        return;
    }

    // Temporizador de seguridad (8 segundos)
    const fallbackTimer = setTimeout(() => {
        if (isLocating.value) {
            console.warn("GPS Timeout: Activando modo manual.");
            isFallbackMode.value = true;
            activateManualSelection();
        }
    }, 8000);

    navigator.geolocation.getCurrentPosition(
        (position) => {
            clearTimeout(fallbackTimer); // Cancelamos el fallback si el GPS responde
            const { latitude, longitude } = position.coords;
            locationActivated.value = true;
            currentCenter.value = [latitude, longitude];
            
            nextTick(() => {
                if(mapRef.value?.leafletObject) {
                    mapRef.value.leafletObject.invalidateSize();
                    mapRef.value.leafletObject.flyTo([latitude, longitude], 18, { duration: 1.5 });
                }
            });
            
            setTimeout(() => {
                isLocating.value = false;
                showPrecisionHint.value = true;
                handleMapMove();
            }, 1600);
        },
        (error) => {
            clearTimeout(fallbackTimer);
            console.error("GPS Error:", error.message);
            isFallbackMode.value = true;
            activateManualSelection(); // Fallback por error de permiso o hardware
        },
        { enableHighAccuracy: true, timeout: 7500, maximumAge: 0 }
    );
};

const activateManualSelection = () => {
    locationActivated.value = true;
    isLocating.value = false;
    nextTick(() => {
        if(mapRef.value?.leafletObject) {
            mapRef.value.leafletObject.invalidateSize();
            // Centramos en el default center si no hay coordenadas previas
            mapRef.value.leafletObject.setView(props.center, 16);
            handleMapMove();
        }
    });
};

defineExpose({ fixMapLayout: () => mapRef.value?.leafletObject?.invalidateSize() });

onMounted(() => {
    if (locationActivated.value) {
        reverseGeocode(props.modelValueLat, props.modelValueLng);
        checkBranchCoverage(props.modelValueLat, props.modelValueLng);
    }
});
</script>

<template>
    <div class="relative w-full h-full min-h-[100%] bg-[#f0f2f5] overflow-hidden flex flex-col">
        
        <div v-if="locationActivated" class="absolute top-4 left-4 z-[2000] bg-[#1e1e1e] border-l-4 p-3 shadow-2xl max-w-[280px]"
             :class="currentBranchId ? 'border-primary' : 'border-red-500'">
            <p class="text-[10px] font-black uppercase mb-1 flex items-center gap-2" :class="currentBranchId ? 'text-primary' : 'text-red-400'">
                <Bug :size="12" /> Geo_Debug
            </p>
            <pre class="text-[#a3ffa3] text-[9px] font-mono leading-tight">Branch: {{ currentBranchId || 'NULL' }}</pre>
        </div>

        <div v-if="isFallbackMode" class="absolute top-4 right-4 left-4 z-[2000] animate-in slide-in-from-top-4">
            <div class="bg-amber-500 text-white p-3 rounded-2xl shadow-2xl flex items-center gap-3">
                <AlertTriangle :size="18" />
                <p class="text-[10px] font-black uppercase tracking-widest">GPS no disponible. Ajusta el punto manualmente.</p>
            </div>
        </div>

        <l-map ref="mapRef" 
               v-model:zoom="currentZoom" 
               v-model:center="currentCenter"
               :use-global-leaflet="false"
               :options="{ zoomControl: false, attributionControl: false }"
               @movestart="mapMoving = true; showPrecisionHint = false"
               @moveend="mapMoving = false; handleMapMove()"
               class="absolute inset-0 h-full w-full z-0">
            <l-tile-layer :url="mapUrl" />
        </l-map>

        <div v-if="locationActivated" class="absolute inset-0 flex items-center justify-center pointer-events-none z-[1000]">
            <div class="relative flex flex-col items-center translate-y-[-22px] transition-transform duration-300"
                 :class="{ 'scale-110 -translate-y-10': mapMoving }">
                <div class="w-10 h-10 rounded-full bg-white shadow-2xl flex items-center justify-center border-[3px]"
                     :class="currentBranchId ? 'border-primary' : 'border-red-500'">
                    <div class="w-2 h-2 rounded-full" :class="[currentBranchId ? 'bg-primary' : 'bg-red-500', {'animate-ping': !mapMoving}]"></div>
                </div>
                <div class="w-1 h-6" :class="currentBranchId ? 'bg-primary' : 'bg-red-500'"></div>
            </div>
        </div>

        <div v-if="!locationActivated" class="absolute inset-0 bg-white/60 backdrop-blur-[8px] z-[1001] flex flex-col items-center justify-center p-8 text-center">
            <div class="w-24 h-24 bg-primary/10 rounded-[3rem] flex items-center justify-center text-primary mb-6 shadow-inner">
                <MapPin :size="48" stroke-width="2.5" />
            </div>
            <h2 class="text-3xl font-black text-[#0A192F] uppercase italic mb-3">Tu Ubicación</h2>
            <p class="text-xs text-muted-foreground font-bold uppercase tracking-widest mb-10 max-w-[280px]">
                Recomendamos GPS para máxima precisión en la entrega.
            </p>
            
            <div class="flex flex-col w-full gap-3 max-w-[280px]">
                <button @click="locateUser" 
                        class="bg-primary text-white w-full py-5 rounded-[24px] font-black uppercase text-xs tracking-[0.2em] shadow-xl flex items-center justify-center gap-4 active:scale-95 transition-all">
                    <Navigation :size="20" fill="currentColor" /> Sincronizar GPS
                </button>
                <button @click="activateManualSelection" 
                        class="bg-white text-[#0A192F] border-2 border-[#0A192F]/10 w-full py-4 rounded-[24px] font-black uppercase text-[10px] tracking-[0.1em] active:scale-95 transition-all">
                    Selección Manual
                </button>
            </div>
        </div>
        
        <div v-if="isLocating" class="absolute inset-0 bg-white/90 backdrop-blur-md z-[2000] flex flex-col items-center justify-center">
            <Loader2 :size="40" class="text-primary animate-spin mb-4" />
            <p class="font-black text-[10px] uppercase tracking-[0.3em] text-[#0A192F] animate-pulse">Triangulando Posición...</p>
        </div>

        <button v-if="locationActivated && !isLocating" @click="locateUser" 
                class="absolute bottom-6 right-6 z-[1000] w-12 h-12 bg-white rounded-2xl shadow-xl flex items-center justify-center text-primary border border-black/5">
            <Navigation :size="20" />
        </button>
    </div>
</template>