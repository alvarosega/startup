<script setup>
import { ref, onMounted, nextTick } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup, LPolygon, LControlZoom, LTooltip } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';

const props = defineProps({
    markers: { type: Array, default: () => [] }, 
    height: { type: String, default: '400px' },
    center: { type: Array, default: () => [-16.5000, -68.1500] },
    zoom: { type: Number, default: 13 }
});

const currentZoom = ref(props.zoom);
const mapRef = ref(null);

// --- MARCADOR VECTORIAL PERSONALIZADO (CSS/Tailwind) ---
const createCustomIcon = (isActive) => {
    const colorClass = isActive ? 'bg-primary border-white dark:border-gray-900' : 'bg-muted-foreground border-white dark:border-gray-900';
    const pulse = isActive ? '<span class="absolute inline-flex h-full w-full rounded-full bg-primary opacity-50 animate-ping"></span>' : '';
    
    return L.divIcon({
        className: 'custom-pin',
        html: `
            <div class="relative flex items-center justify-center w-8 h-8">
                ${pulse}
                <div class="relative z-10 w-6 h-6 rounded-full border-2 ${colorClass} shadow-lg flex items-center justify-center text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/><path d="M17 21v-8H7v8"/></svg>
                </div>
                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[4px] border-l-transparent border-r-[4px] border-r-transparent border-t-[6px] border-t-white dark:border-t-gray-900"></div>
            </div>
        `,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });
};

const activeIcon = createCustomIcon(true);
const inactiveIcon = createCustomIcon(false);

// Forzar renderizado correcto al montar
onMounted(async () => {
    await nextTick();
    setTimeout(() => {
        mapRef.value?.leafletObject?.invalidateSize();
    }, 200);
});
</script>

<template>
    <div class="w-full rounded-2xl overflow-hidden shadow-inner border border-border bg-muted/20 relative z-0" 
         :style="{ height: height }">
        
        <l-map ref="mapRef" 
               v-model:zoom="currentZoom" 
               :center="center" 
               :use-global-leaflet="false"
               :options="{ scrollWheelZoom: false, dragging: true }"> 
               
               <l-tile-layer 
                    url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
                    attribution='&copy; CartoDB'
                    class-name="map-tiles"
               />

            <l-control-zoom position="bottomright" />

            <template v-for="item in markers" :key="item.id">
                
                <l-polygon
                    v-if="item.coverage_polygon && item.coverage_polygon.length > 2"
                    :lat-lngs="item.coverage_polygon"
                    color="var(--primary)" 
                    :fill="true"
                    :fill-opacity="0.1"
                    :weight="1"
                />

                <l-marker 
                    v-if="item.latitude && item.longitude" 
                    :lat-lng="[item.latitude, item.longitude]"
                    :icon="item.is_active ? activeIcon : inactiveIcon"
                >
                    <l-popup class="custom-popup">
                        <div class="min-w-[180px]">
                            <div class="flex items-center justify-between mb-2 pb-2 border-b border-gray-100 dark:border-gray-700">
                                <h3 class="font-black text-sm text-gray-800 dark:text-white">{{ item.name }}</h3>
                                <span class="text-[9px] px-1.5 py-0.5 rounded font-bold uppercase"
                                      :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                    {{ item.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 leading-snug">{{ item.address }}</p>
                            <a v-if="item.latitude" 
                               :href="`https://www.google.com/maps/search/?api=1&query=${item.latitude},${item.longitude}`" 
                               target="_blank"
                               class="block w-full text-center bg-gray-900 text-white dark:bg-white dark:text-gray-900 text-[10px] font-bold py-1.5 rounded hover:opacity-90">
                                Abrir en Google Maps
                            </a>
                        </div>
                    </l-popup>
                </l-marker>
            </template>

        </l-map>
        
        <div class="absolute inset-0 pointer-events-none shadow-[inset_0_0_20px_rgba(0,0,0,0.05)] z-[400]"></div>
    </div>
</template>

<style>
/* Estilos Globales para Leaflet */
.leaflet-div-icon {
    background: transparent !important;
    border: none !important;
}

/* Modo Oscuro para Mapas */
.dark .map-tiles {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
}

/* Popups personalizados */
.custom-popup .leaflet-popup-content-wrapper {
    @apply rounded-xl shadow-xl border border-gray-100 bg-white dark:bg-gray-800 dark:border-gray-700 p-0 overflow-hidden;
}
.custom-popup .leaflet-popup-content {
    margin: 12px;
    @apply font-sans;
}
.custom-popup .leaflet-popup-tip {
    @apply bg-white dark:bg-gray-800;
}
</style>