<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup, LPolygon, LControlZoom, LTooltip, LCircleMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import { Radar } from 'lucide-vue-next';

const props = defineProps({
    markers: { type: Array, default: () => [] },
    polygon: { type: Array, default: () => [] },
    center: { type: Array, default: () => [-16.5000, -68.1500] },
    zoom: { type: Number, default: 13 },
    height: { type: String, default: '400px' },
    draggable: { type: Boolean, default: false },
    clickable: { type: Boolean, default: false },
    draggableMarkerId: { type: [String, Number], default: null }
});

const emit = defineEmits(['marker-drag', 'map-click', 'update:center', 'update:zoom']);

const currentZoom = ref(props.zoom);
const mapInstance = ref(null);

// --- PROTOCOLO DE ICONOS (ESTILOS FIJOS PARA EVITAR PURGE DE CSS) ---
const createCustomIcon = (type = 'active') => {
    const config = {
        active: { color: '#00f0ff', bg: 'rgba(0, 240, 255, 0.2)' },
        inactive: { color: '#6b7280', bg: 'rgba(107, 114, 128, 0.2)' },
        base: { color: '#facc15', bg: 'rgba(250, 204, 21, 0.2)' },
    };
    const c = config[type] || config.active;

    return L.divIcon({
        className: 'custom-pin',
        html: `
            <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;">
                <div style="position: absolute; width: 100%; height: 100%; border-radius: 50%; background: ${c.color}; opacity: 0.3; animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
                <div style="position: relative; z-index: 10; width: 24px; height: 24px; background: #000; border: 2px solid ${c.color}; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 10px ${c.color};">
                    <svg viewBox="0 0 24 24" width="14" height="14" stroke="${c.color}" stroke-width="3" fill="none"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                </div>
            </div>
        `,
        iconSize: [32, 32],
        iconAnchor: [16, 16],
    });
};

const getIcon = (item) => {
    if (!item.is_active) return createCustomIcon('inactive');
    if (item.is_default) return createCustomIcon('base');
    return createCustomIcon('active');
};

// --- GESTIÓN DE REACTIVIDAD ---
const onMapReady = async (map) => {
    mapInstance.value = map;
    await nextTick();
    map.invalidateSize();
};

// CRÍTICO: Escuchar clics en el mapa para el Wizard (Paso 2)
const handleMapClick = (e) => {
    if (props.clickable) {
        emit('map-click', e);
    }
};

const onMarkerDrag = (event, markerId) => {
    const { lat, lng } = event.target.getLatLng();
    emit('marker-drag', { lat, lng, id: markerId });
};

// REGLA DE ORO: Sincronizar el centro cuando la prop cambia externamente
watch(() => props.center, (newCenter) => {
    if (mapInstance.value) {
        mapInstance.value.setView(newCenter, currentZoom.value);
    }
}, { deep: true });

// Forzar re-calculo de tamaño (Soluciona el problema de "mapa gris" en el Wizard)
watch(() => props.height, async () => {
    await nextTick();
    setTimeout(() => mapInstance.value?.invalidateSize(), 200);
});

defineExpose({
    leafletObject: mapInstance,
    invalidateSize: () => mapInstance.value?.invalidateSize()
});
</script>

<template>
    <div class="w-full overflow-hidden border border-primary/30 relative group/map bg-[#0a0a0a]" 
         :style="{ height: height }">
        
        <div class="absolute inset-0 pointer-events-none z-[1000] border-[1px] border-primary/10">
            <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-primary/20 to-transparent animate-scanline"></div>
        </div>

        <l-map ref="mapRef" 
               v-model:zoom="currentZoom" 
               :center="center" 
               :use-global-leaflet="false"
               :options="{ zoomControl: false, attributionControl: true }"
               @ready="onMapReady"
               @click="handleMapClick"> 
               
            <l-tile-layer 
                url="https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"
                attribution='&copy; CartoDB'
            />

            <l-control-zoom position="bottomright" />

            <l-polygon
                v-if="polygon && polygon.length > 0"
                :lat-lngs="polygon"
                color="#00f0ff"
                :fill-color="'#00f0ff'"
                :fill-opacity="0.2"
                :weight="2"
            />

            <l-circle-marker 
                v-for="(point, index) in polygon" 
                :key="'p-'+index" 
                :lat-lng="point" 
                :radius="4" 
                color="#00f0ff" 
                fill-color="#000" 
                :fill-opacity="1" 
            />

            <template v-for="item in markers" :key="item.id">
                
                <l-polygon
                    v-if="item.coverage_polygon && item.coverage_polygon.length > 2"
                    :lat-lngs="item.coverage_polygon"
                    :color="item.is_active ? '#00f0ff' : '#6b7280'"
                    :fill-opacity="0.05"
                    :weight="1"
                />

                <l-marker 
                    v-if="item.latitude && item.longitude" 
                    :lat-lng="[parseFloat(item.latitude), parseFloat(item.longitude)]"
                    :icon="getIcon(item)"
                    :draggable="draggable && (draggableMarkerId === item.id || draggableMarkerId === null)"
                    @dragend="(e) => onMarkerDrag(e, item.id)"
                >
                    <l-tooltip v-if="item.name" :options="{ permanent: draggable, direction: 'top', offset: [0, -10] }">
                        <span class="font-mono font-bold">{{ item.name }}</span>
                    </l-tooltip>
                    
                    <l-popup>
                        <div class="p-2 font-mono text-xs">
                            <h4 class="text-primary font-bold border-b border-primary/20 mb-2 uppercase">{{ item.name }}</h4>
                            <p class="text-muted-foreground">{{ item.address }}</p>
                            <div v-if="item.delivery_base_fee" class="mt-2 text-[10px] text-primary">
                                BASE: {{ item.delivery_base_fee }} Bs
                            </div>
                        </div>
                    </l-popup>
                </l-marker>
            </template>
        </l-map>

        <div class="absolute bottom-2 left-2 z-[1001] bg-black/80 border border-primary/30 px-2 py-1 text-[8px] font-mono text-primary flex items-center gap-1 backdrop-blur-sm">
            <Radar :size="10" class="animate-pulse" />
            LOC // {{ center[0].toFixed(4) }}, {{ center[1].toFixed(4) }}
        </div>
    </div>
</template>

<style>
@keyframes scanline {
    0% { top: 0%; }
    100% { top: 100%; }
}
.animate-scanline { animation: scanline 4s linear infinite; }

.leaflet-container { background: #0a0a0a !important; cursor: crosshair !important; }
.leaflet-popup-content-wrapper { background: #000 !important; color: #fff !important; border: 1px solid #00f0ff !important; border-radius: 0 !important; }
.leaflet-popup-tip { background: #00f0ff !important; }
.leaflet-tooltip { background: #000 !important; color: #00f0ff !important; border: 1px solid #00f0ff !important; font-family: 'JetBrains Mono', monospace; font-size: 10px; border-radius: 0; }
</style>