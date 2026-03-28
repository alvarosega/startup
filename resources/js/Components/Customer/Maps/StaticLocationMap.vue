<script setup>
import { ref, onMounted } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';

const props = defineProps({
    lat: Number,
    lng: Number,
    address: String,
    zoom: { type: Number, default: 16 }
});

const createTacticalIcon = () => {
    return L.divIcon({
        className: 'static-pin-container',
        html: `
            <div class="relative flex flex-col items-center">
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center border-2 border-white shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0A192F" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
            </div>
        `,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
    });
};
</script>

<template>
    <div class="w-full h-48 rounded-[24px] overflow-hidden border border-white/10 relative group">
        <l-map :zoom="zoom" 
               :center="[lat, lng]" 
               :use-global-leaflet="false"
               :options="{ 
                   zoomControl: false, 
                   attributionControl: false, 
                   dragging: false, 
                   touchZoom: false, 
                   scrollWheelZoom: false, 
                   doubleClickZoom: false 
               }"
               class="h-full w-full z-0">
            
            <l-tile-layer url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png" class-name="map-tiles" />
            
            <l-marker :lat-lng="[lat, lng]" :icon="createTacticalIcon()" />
        </l-map>

        <div class="absolute bottom-3 left-3 right-3 z-[400] bg-black/60 backdrop-blur-md border border-white/10 p-3 rounded-xl flex items-center gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-[9px] font-black text-white/40 uppercase tracking-widest leading-none mb-1">Destino Confirmado</p>
                <p class="text-xs font-bold text-white truncate">{{ address }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.dark .map-tiles { filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%); }
</style>