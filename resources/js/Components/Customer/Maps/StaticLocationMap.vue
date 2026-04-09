<script setup>
import { ref } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import { MapPin } from 'lucide-vue-next';

const props = defineProps({
    lat: [Number, String], // Tolerancia para evitar fallos si llega como string
    lng: [Number, String],
    address: String,
    zoom: { type: Number, default: 16 }
});

// Estandarización de icono al radar táctico de la app
const createTacticalIcon = () => {
    return L.divIcon({
        className: 'static-preview-pin',
        html: `<div class="w-8 h-8 bg-primary rounded-full border-[3px] border-white dark:border-[#0A192F] shadow-2xl flex items-center justify-center text-white"><div class="w-2 h-2 bg-white rounded-full"></div></div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 16],
    });
};
</script>

<template>
    <div class="w-full h-48 md:h-56 rounded-[2rem] overflow-hidden border border-border/40 relative group shadow-inner bg-foreground/5">
        
        <l-map :zoom="zoom" 
               :center="[Number(lat || 0), Number(lng || 0)]" 
               :use-global-leaflet="false"
               :options="{ 
                   zoomControl: false, 
                   attributionControl: false, 
                   dragging: false, 
                   touchZoom: false, 
                   scrollWheelZoom: false, 
                   doubleClickZoom: false 
               }"
               class="h-full w-full z-0 dark-map-filter">
            
            <l-tile-layer url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png" />
            
            <l-marker v-if="lat && lng" :lat-lng="[Number(lat), Number(lng)]" :icon="createTacticalIcon()" />
        </l-map>

        <div class="absolute bottom-3 left-3 right-3 md:bottom-4 md:left-4 md:right-4 z-[400] bg-background/85 backdrop-blur-md border border-border/40 p-3 md:p-4 rounded-[1.5rem] flex items-center gap-4 shadow-xl transition-all duration-300">
            
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 shadow-inner">
                <MapPin :size="18" stroke-width="2.5" />
            </div>
            
            <div class="flex-1 min-w-0">
                <p class="text-[9px] font-black text-foreground/40 uppercase tracking-[0.2em] leading-none mb-1">
                    Destino Confirmado
                </p>
                <p class="text-xs md:text-sm font-bold text-foreground truncate uppercase italic">
                    {{ address || 'COORDENADA ESTÁTICA' }}
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Filtro paramétrico para invertir el mapa de Light All a Dark Táctico en Modo Oscuro */
.dark-map-filter {
    transition: filter 0.5s ease;
}

.dark .dark-map-filter {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
}
</style>