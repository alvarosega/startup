<script setup>
    import { ref, onMounted } from 'vue';
    // 1. Estilos obligatorios de Leaflet
    import "leaflet/dist/leaflet.css";
    
    // 2. Componentes Vue-Leaflet
    import { LMap, LTileLayer, LMarker, LPopup, LPolygon, LControlZoom, LTooltip } from "@vue-leaflet/vue-leaflet";
    import L from 'leaflet';

    const props = defineProps({
        markers: { type: Array, default: () => [] }, 
        height: { type: String, default: '400px' },
        center: { type: Array, default: () => [-16.5000, -68.1500] }, // La Paz
        zoom: { type: Number, default: 13 }
    });

    const currentZoom = ref(props.zoom);
    const mapRef = ref(null);

    // --- FIX CRÍTICO: Iconos rotos en Producción (Vite) ---
    onMounted(() => {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
            iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
            shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
        });
    });
</script>

<template>
    <div class="rounded-global overflow-hidden shadow-lg border border-skin-border relative z-0" 
         :style="{ height: height }">
        
        <l-map ref="mapRef" 
               v-model:zoom="currentZoom" 
               :center="center" 
               :use-global-leaflet="false"
               :options="{ zoomControl: false }"> 
               
               <l-tile-layer url="https://tile.openstreetmap.org/{z}/{x}/{y}.png" attribution='© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>' layer-type="base" name="OpenStreetMap">
                
               </l-tile-layer>

            <l-control-zoom position="bottomright" />

            <template v-for="item in markers" :key="item.id">
                
                <l-polygon
                    v-if="item.coverage_polygon && item.coverage_polygon.length > 2"
                    :lat-lngs="item.coverage_polygon"
                    color="#10b981" 
                    :fill="true"
                    :fill-opacity="0.15"
                    :weight="1"
                />

                <l-marker 
                    v-if="item.latitude && item.longitude" 
                    :lat-lng="[item.latitude, item.longitude]"
                >
                    <l-tooltip>{{ item.name }}</l-tooltip>

                    <l-popup>
                        <div class="text-gray-800 font-sans min-w-[160px] p-1">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-sm text-gray-900">{{ item.name }}</h3>
                                <div class="w-2 h-2 rounded-full" :class="item.is_active ? 'bg-green-500' : 'bg-red-500'"></div>
                            </div>
                            <p class="text-xs text-gray-600 mb-1 leading-snug">{{ item.address }}</p>
                            <div class="mt-2 pt-2 border-t border-gray-200 flex justify-between items-center">
                                <span class="text-[10px] font-bold text-gray-400">Tel: {{ item.phone || 'N/A' }}</span>
                                <a v-if="item.latitude" 
                                   :href="`https://www.google.com/maps/search/?api=1&query=${item.latitude},${item.longitude}`" 
                                   target="_blank"
                                   class="text-[10px] text-blue-600 hover:underline font-bold">
                                    Ver en Google
                                </a>
                            </div>
                        </div>
                    </l-popup>
                </l-marker>
            </template>

        </l-map>
    </div>
</template>

<style>
/* Reset básico para que los popups no hereden estilos extraños de Tailwind */
.leaflet-popup-content-wrapper {
    border-radius: 8px;
    padding: 0;
    overflow: hidden;
}
.leaflet-popup-content {
    margin: 10px;
    line-height: 1.4;
}
/* Color de fondo del contenedor del mapa (evita flash blanco al cargar) */
.leaflet-container {
    background-color: #1f2937; 
}
</style>