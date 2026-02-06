<script setup>
import { ref, onMounted, nextTick } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';

const props = defineProps({
    addresses: { type: Array, default: () => [] },
    activeBranches: { type: Array, default: () => [] }
});

const mapRef = ref(null);
const zoom = ref(13);
const center = ref([-16.5000, -68.1500]); 

// --- GENERADOR DE ICONOS CSS (Tailwind nativo) ---
// Esto permite que los iconos respeten el color 'primary' del tema
const createCustomIcon = (isDefault) => {
    const colorClass = isDefault ? 'bg-primary text-primary-foreground' : 'bg-muted-foreground text-white';
    const pulseEffect = isDefault ? '<span class="absolute inline-flex h-full w-full rounded-full bg-primary opacity-75 animate-ping"></span>' : '';
    const zIndex = isDefault ? 'z-50' : 'z-10';
    const iconSvg = isDefault 
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>' // Estrella
        : '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>'; // Pin

    return L.divIcon({
        className: 'custom-pin', // Clase vacía para quitar estilos default de Leaflet
        html: `
            <div class="relative flex items-center justify-center w-8 h-8 ${zIndex}">
                ${pulseEffect}
                <div class="relative inline-flex items-center justify-center w-8 h-8 rounded-full border-2 border-white dark:border-gray-900 shadow-lg ${colorClass}">
                    ${iconSvg}
                </div>
                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[8px] border-t-white dark:border-t-gray-900"></div>
            </div>
        `,
        iconSize: [32, 42],
        iconAnchor: [16, 42],
        popupAnchor: [0, -40]
    });
};

// Instancias de iconos
const mainIcon = createCustomIcon(true);
const secondaryIcon = createCustomIcon(false);

const fitMapToMarkers = () => {
    if (mapRef.value && mapRef.value.leafletObject && props.addresses.length > 0) {
        const markers = props.addresses
            .filter(a => a.latitude && a.longitude)
            .map(a => [parseFloat(a.latitude), parseFloat(a.longitude)]);
        
        if (markers.length > 0) {
            const bounds = L.latLngBounds(markers);
            if (bounds.isValid()) {
                // 'maxZoom' evita que si hay solo 1 punto, el zoom se vaya al máximo (nivel calle pegado)
                mapRef.value.leafletObject.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
            }
        }
    }
};

const onMapReady = () => {
    // Esperar un poco más en la carga inicial
    setTimeout(() => {
        mapRef.value?.leafletObject?.invalidateSize();
        fitMapToMarkers();
    }, 500);
};
const refresh = async () => {
    // 1. Esperamos a que Vue termine de renderizar el DOM
    await nextTick();
    
    if (mapRef.value && mapRef.value.leafletObject) {
        // 2. OBLIGATORIO: Decirle a Leaflet "¡Oye, tu contenedor cambió de tamaño!"
        // Esto arregla las zonas grises.
        mapRef.value.leafletObject.invalidateSize();
        
        // 3. Esperamos un micro-momento para que el resize surta efecto y luego centramos
        setTimeout(() => {
            fitMapToMarkers();
        }, 200);
    }
};

defineExpose({ refresh });
</script>

<template>
    <div class="w-full h-56 md:h-72 rounded-2xl overflow-hidden border border-border shadow-sm relative z-0 bg-muted/20">
        
        <l-map ref="mapRef" 
               v-model:zoom="zoom" 
               :center="center" 
               :use-global-leaflet="false"
               :options="{ scrollWheelZoom: false, dragging: true }" 
               @ready="onMapReady"
               class="h-full w-full z-10"> <l-tile-layer 
                url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
                attribution='&copy; CartoDB'
                class-name="map-tiles"
            />

            <l-marker 
                v-for="addr in addresses" 
                :key="addr.id"
                :lat-lng="[parseFloat(addr.latitude), parseFloat(addr.longitude)]"
                :icon="addr.is_default ? mainIcon : secondaryIcon"
            >
                <l-popup class="custom-popup">
                    </l-popup>
            </l-marker>
        </l-map>
        
        <div class="absolute inset-0 pointer-events-none rounded-2xl shadow-[inset_0_0_20px_rgba(0,0,0,0.05)] z-20"></div>
    </div>
</template>

<style>
/* CSS Global necesario para Leaflet + Dark Mode */

/* 1. Modo Oscuro Inteligente para Mapas */
.dark .map-tiles {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
}

/* 2. Limpieza de contenedores de iconos */
.leaflet-div-icon {
    background: transparent !important;
    border: none !important;
}

/* 3. Estilización de Popups para que coincidan con el tema */
.custom-popup .leaflet-popup-content-wrapper {
    @apply rounded-xl shadow-xl border border-gray-100 bg-white dark:bg-gray-800 dark:border-gray-700;
    padding: 0;
}
.custom-popup .leaflet-popup-content {
    margin: 12px 16px;
    @apply font-sans;
}
.custom-popup .leaflet-popup-tip {
    @apply bg-white dark:bg-gray-800;
}
</style>