<script setup>
import { ref, onMounted, nextTick } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LPolygon } from "@vue-leaflet/vue-leaflet";
import debounce from 'lodash/debounce';
import { Loader2, Database, Cpu } from 'lucide-vue-next';

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

const mapUrl = "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png";

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
        emit('update:modelValueAddress', "ERROR_GEO_SERVICE");
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
    <div class="relative w-full h-full bg-zinc-950 overflow-hidden font-mono">
        <div class="absolute top-4 left-4 z-[1000] bg-zinc-900/90 border border-zinc-700 p-4 shadow-2xl backdrop-blur-md min-w-[240px]">
            <div class="flex items-center gap-2 text-primary mb-3 border-b border-zinc-800 pb-2">
                <Cpu :size="14" />
                <span class="text-[10px] font-black tracking-widest uppercase">Geo_Telemetry_System</span>
            </div>
            <div class="space-y-2 text-[10px]">
                <div class="flex justify-between">
                    <span class="text-zinc-500">LAT:</span>
                    <span class="text-zinc-200">{{ modelValueLat?.toFixed(6) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-zinc-500">LNG:</span>
                    <span class="text-zinc-200">{{ modelValueLng?.toFixed(6) }}</span>
                </div>
                <div class="flex justify-between items-center mt-4 border-t border-zinc-800 pt-2">
                    <span class="text-zinc-500 flex items-center gap-1"><Database :size="10" /> SUCURSAL:</span>
                    <span :class="currentBranchId ? 'text-emerald-400 font-bold' : 'text-red-500 animate-pulse'">
                        {{ currentBranchId ? currentBranchId.split('-')[0].toUpperCase() : 'NULL_OVERFLOW' }}
                    </span>
                </div>
            </div>
        </div>

        <l-map ref="mapRef" :zoom="15" :center="[modelValueLat || center[0], modelValueLng || center[1]]"
               :use-global-leaflet="false" :options="{ zoomControl: false, attributionControl: false }"
               @movestart="mapMoving = true" @moveend="mapMoving = false; handleMove()"
               class="absolute inset-0 h-full w-full grayscale-[0.6] brightness-[0.8]">
            <l-tile-layer :url="mapUrl" />
            <l-polygon v-for="branch in activeBranches" :key="branch.id"
                       :lat-lngs="branch.polygon"
                       :color="currentBranchId === branch.id ? '#00f2ff' : '#3f3f46'"
                       :fill-opacity="0.1" :weight="1" />
        </l-map>

        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-[1001]">
            <div class="relative flex flex-col items-center">
                <div class="w-12 h-12 border border-primary/40 rounded-full animate-pulse absolute"></div>
                <div class="w-8 h-8 border-2 border-white flex items-center justify-center bg-zinc-900 shadow-neon-zinc">
                    <div class="w-1 h-1 bg-primary rounded-full"></div>
                </div>
                <div class="h-6 w-[2px] bg-white"></div>
                <div class="w-4 h-[1px] bg-black/50 mt-1 blur-[1px]"></div>
            </div>
        </div>

        <div v-if="mapMoving" class="absolute bottom-4 right-4 z-[1000] flex items-center gap-2 bg-zinc-900 border border-zinc-700 px-3 py-1.5 text-zinc-400 text-[9px] font-black uppercase tracking-tighter shadow-xl">
            <Loader2 :size="12" class="animate-spin text-primary" /> Recalculando_Vectores
        </div>
    </div>
</template>

<style scoped>
.shadow-neon-zinc { box-shadow: 0 0 15px rgba(255, 255, 255, 0.2); }
</style>