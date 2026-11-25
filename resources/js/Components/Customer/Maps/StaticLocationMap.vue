<script setup>
import { computed } from 'vue';
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import "leaflet/dist/leaflet.css";

const props = defineProps({
    lat: {
        type: Number,
        required: true,
    },
    lng: {
        type: Number,
        required: true,
    },
    address: {
        type: String,
        default: '',
    },
});

const center = computed(() => [props.lat, props.lng]);
</script>

<template>
    <div class="h-48 w-full relative z-0">
        <LMap 
            :zoom="15" 
            :center="center" 
            :use-global-leaflet="false"
            :options="{ zoomControl: false, dragging: false, scrollWheelZoom: false, doubleClickZoom: false }"
            class="h-full w-full"
        >
            <LTileLayer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                layer-type="base"
                name="OpenStreetMap"
            />
            <LMarker :lat-lng="center" />
        </LMap>
    </div>
</template>
