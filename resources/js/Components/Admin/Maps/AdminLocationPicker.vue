<script setup>
import { ref, onMounted, nextTick } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LPolygon } from "@vue-leaflet/vue-leaflet";
import debounce from 'lodash/debounce';

const props = defineProps({
    modelValueLat: Number,
    modelValueLng: Number,
    modelValueAddress: String,
    activeBranches: { type: Array, default: () => [] },
    center: { type: Array, default: () => [-16.5000, -68.1500] }
});

const emit = defineEmits(['update:modelValueLat', 'update:modelValueLng', 'update:modelValueAddress', 'update:modelValueBranchId']);

const mapRef = ref(null);
const currentBranchId = ref(null);
const mapMoving = ref(false);

// Capa limpia corporativa universal
const mapUrl = "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png";

const isInsidePolygon = (lat, lng, polygon) => {
    const points = Array.isArray(polygon[0][0]) ? polygon[0] : polygon;
    let inside = false;
    for (let i = 0, j = points.length - 1; i < points.length; j = i++) {
        const xi = points[i][0], yi = points[i][1];
        const xj = points[j][0], yj = points[j][1];
        const intersect = ((yi > lng) !== (yj > lng)) && (lat < (xj - xi) * (lng - yi) / (yj - yi) + xi);
        if (intersect) inside = !inside;
    }
    return inside;
};

const checkCoverage = (lat, lng) => {
    let foundId = null;
    for (const branch of props.activeBranches) {
        if (branch.polygon && isInsidePolygon(lat, lng, branch.polygon)) {
            foundId = branch.id;
            break;
        }
    }
    currentBranchId.value = foundId;
    emit('update:modelValueBranchId', foundId);
};

const reverseGeocode = debounce(async (lat, lng) => {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`);
        const data = await response.json();
        emit('update:modelValueAddress', data.display_name || "COORDENADAS_FIJADAS");
    } catch (e) {
        emit('update:modelValueAddress', "ERROR_SISTEMA_GEODESICO");
    }
}, 800);

const handleMove = () => {
    if (!mapRef.value?.leafletObject) return;
    const center = mapRef.value.leafletObject.getCenter();
    emit('update:modelValueLat', center.lat);
    emit('update:modelValueLng', center.lng);
    checkCoverage(center.lat, center.lng);
    reverseGeocode(center.lat, center.lng);
};

const fixMap = () => {
    nextTick(() => mapRef.value?.leafletObject?.invalidateSize());
};

defineExpose({ fixMap });

onMounted(() => {
    if (props.modelValueLat) checkCoverage(props.modelValueLat, props.modelValueLng);
    fixMap();
});
</script>

<template>
    <div class="relative w-full h-full bg-neutral-100 overflow-hidden font-mono">
        
        <div class="absolute top-3 left-3 z-[1000] bg-card/95 border border-border p-3 rounded-md shadow-flat backdrop-blur-md min-w-[220px] select-none text-foreground">
            <div class="flex items-center gap-1.5 text-primary mb-2.5 border-b border-border/60 pb-1.5">
                <span class="material-symbols-rounded text-sm">distance</span>
                <span class="text-[10px] font-black tracking-wider uppercase">GEO_TELEMETRY</span>
            </div>
            <div class="space-y-1.5 text-[10px] font-medium">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">LAT:</span>
                    <span class="text-foreground font-semibold">{{ modelValueLat?.toFixed(6) || '0.000000' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">LNG:</span>
                    <span class="text-foreground font-semibold">{{ modelValueLng?.toFixed(6) || '0.000000' }}</span>
                </div>
                <div class="flex justify-between items-center mt-2.5 border-t border-border/60 pt-2">
                    <span class="text-muted-foreground flex items-center gap-1">SUCURSAL:</span>
                    <span :class="currentBranchId ? 'text-success font-bold' : 'text-error font-bold animate-pulse'">
                        {{ currentBranchId ? currentBranchId.split('-')[0].toUpperCase() : 'FUERA_COBERTURA' }}
                    </span>
                </div>
            </div>
        </div>

        <l-map ref="mapRef" :zoom="15" :center="[modelValueLat || center[0], modelValueLng || center[1]]"
               :use-global-leaflet="false" :options="{ zoomControl: false, attributionControl: false }"
               @movestart="mapMoving = true" @moveend="mapMoving = false; handleMove()"
               class="absolute inset-0 h-full w-full">
            <l-tile-layer :url="mapUrl" />
            
            <l-polygon v-for="branch in activeBranches" :key="branch.id"
                       :lat-lngs="branch.polygon"
                       :color="currentBranchId === branch.id ? '#2563eb' : '#a3a3a3'"
                       :fill-opacity="0.06" :weight="1.5" />
        </l-map>

        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-[1001] select-none">
            <div class="relative flex flex-col items-center -mt-5">
                <span class="material-symbols-rounded text-primary text-3xl filter drop-shadow-md">location_on</span>
                <div class="w-1.5 h-1.5 bg-foreground rounded-full border border-card -mt-1.5"></div>
            </div>
        </div>

        <div v-if="mapMoving" class="absolute bottom-3 right-3 z-[1000] flex items-center gap-2 bg-card border border-border px-3 py-1.5 text-muted-foreground text-[10px] font-bold uppercase tracking-wider rounded-sm shadow-flat select-none">
            <span class="material-symbols-rounded text-sm text-primary animate-spin">progress_activity</span>
            <span>Sincronizando...</span>
        </div>
    </div>
</template>