<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';

const props = defineProps({
    addresses: { type: Array, default: () => [] },
    activeBranches: { type: Array, default: () => [] }
});

const mapRef = ref(null);
const zoom = ref(13);
const center = ref([-16.5000, -68.1500]); // Centro default

// --- 1. CONFIGURACIÓN DE ICONOS ---
const createCustomIcon = (isDefault) => {
    const colorClass = isDefault ? 'bg-primary text-primary-foreground' : 'bg-muted-foreground text-white';
    const zIndex = isDefault ? 'z-50' : 'z-10';
    // Optimizamos el SVG quitando el animate-ping para mejorar rendimiento (reduce Violations)
    const iconSvg = isDefault 
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>';

    return L.divIcon({
        className: 'custom-pin',
        html: `
            <div class="relative flex items-center justify-center w-8 h-8 ${zIndex}">
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

const mainIcon = createCustomIcon(true);
const secondaryIcon = createCustomIcon(false);

// --- 2. FUNCIÓN DE AJUSTE BLINDADA ---
const fitMapToMarkers = () => {
    // A. Validaciones básicas de existencia
    if (!mapRef.value || !mapRef.value.leafletObject) return;
    if (!props.addresses || props.addresses.length === 0) return;

    try {
        // B. Construcción manual y limpieza estricta de puntos
        const points = [];
        
        props.addresses.forEach(addr => {
            // Convertir a float explícitamente
            const lat = parseFloat(addr.latitude);
            const lng = parseFloat(addr.longitude);

            // C. Verificar que sean números finitos válidos (Evita NaN o Infinity)
            if (Number.isFinite(lat) && Number.isFinite(lng)) {
                points.push([lat, lng]);
            }
        });

        // D. Si después de limpiar no quedan puntos, abortamos silenciosamente
        if (points.length === 0) return;

        // E. Crear los límites usando Leaflet
        const bounds = L.latLngBounds(points);

        // F. Verificar si los límites son válidos geométricamente
        if (bounds.isValid()) {
            mapRef.value.leafletObject.fitBounds(bounds, { 
                padding: [50, 50], 
                maxZoom: 15,
                animate: true // Animación suave
            });
        }
    } catch (e) {
        // G. Captura silenciosa para no ensuciar la consola en casos borde
        // (El mapa seguirá funcionando, solo no hará zoom automático)
        // console.warn("Ajuste de mapa omitido:", e.message); 
    }
};

// --- 3. GESTIÓN DEL CICLO DE VIDA ---

const onMapReady = () => {
    // Un pequeño delay ayuda a que el contenedor termine de renderizarse
    setTimeout(() => {
        refresh();
    }, 300);
};

const refresh = async () => {
    await nextTick();
    if (mapRef.value && mapRef.value.leafletObject) {
        // IMPORTANTE: invalidateSize previene errores de cálculo si el mapa estaba oculto
        mapRef.value.leafletObject.invalidateSize();
        // Llamamos al ajuste
        fitMapToMarkers();
    }
};

defineExpose({ refresh });

// Observador para cuando llegan los datos de la API
watch(() => props.addresses, () => {
    // Usamos nextTick para asegurar que los markers ya se pintaron en el DOM virtual
    nextTick(() => {
        fitMapToMarkers();
    });
}, { deep: true });

</script>

<template>
    <div class="w-full h-56 md:h-72 rounded-2xl overflow-hidden border border-border shadow-sm relative z-0 bg-muted/20">
        
        <l-map ref="mapRef" 
               v-model:zoom="zoom" 
               v-model:center="center" 
               :use-global-leaflet="false"
               :options="{ scrollWheelZoom: false, dragging: true, tap: false }" 
               @ready="onMapReady"
               class="h-full w-full z-10">
            
            <l-tile-layer 
                url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
                attribution='&copy; CartoDB'
                class-name="map-tiles"
            />

            <template v-if="addresses && addresses.length > 0">
                <l-marker 
                    v-for="addr in addresses" 
                    :key="addr.id"
                    :lat-lng="[parseFloat(addr.latitude), parseFloat(addr.longitude)]"
                    :icon="addr.is_default ? mainIcon : secondaryIcon"
                >
                    <l-popup class="custom-popup">
                        <div class="text-xs">
                            <strong class="block text-sm font-black mb-1 text-foreground">
                                {{ addr.alias }}
                            </strong>
                            <span class="text-muted-foreground block mb-2 leading-tight">
                                {{ addr.address }}
                            </span>
                            <div v-if="addr.is_default" class="inline-block px-2 py-0.5 rounded bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider">
                                Principal
                            </div>
                        </div>
                    </l-popup>
                </l-marker>
            </template>
        </l-map>
        
        <div class="absolute inset-0 pointer-events-none rounded-2xl shadow-[inset_0_0_20px_rgba(0,0,0,0.05)] z-20"></div>
    </div>
</template>

<style>
/* Estilos globales (igual que antes) */
.dark .map-tiles {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
}
.leaflet-div-icon {
    background: transparent !important;
    border: none !important;
}
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