<script setup>
import { ref, onMounted } from 'vue';
import { LMap, LTileLayer } from "@vue-leaflet/vue-leaflet";
import "leaflet/dist/leaflet.css";
import { LocateFixed } from 'lucide-vue-next';

const props = defineProps({
    modelValueLat: [Number, String],
    modelValueLng: [Number, String],
    modelValueAddress: String,
    modelValueBranchId: [Number, String],
    activeBranches: Array
});

const emit = defineEmits([
    'update:modelValueLat', 'update:modelValueLng', 
    'update:modelValueAddress', 'update:modelValueBranchId'
]);

const mapRef = ref(null);
const center = ref([-16.499691, -68.121544]); 

onMounted(() => {
    if (props.modelValueLat && props.modelValueLng) {
        center.value = [parseFloat(props.modelValueLat), parseFloat(props.modelValueLng)];
    }
    // Geolocation automática eliminada para forzar decisión explícita.
});

const onMapMoveEnd = (e) => {
    const newCenter = e.target.getCenter();
    emit('update:modelValueLat', newCenter.lat);
    emit('update:modelValueLng', newCenter.lng);
    
    // Solo sobrescribimos la dirección si el usuario no ha escrito nada personalizado
    if (!props.modelValueAddress || props.modelValueAddress.startsWith('Coordenadas:')) {
         emit('update:modelValueAddress', `Coordenadas: ${newCenter.lat.toFixed(5)}, ${newCenter.lng.toFixed(5)}`);
    }
};

const invalidateMap = () => {
    if (mapRef.value?.leafletObject) {
        mapRef.value.leafletObject.invalidateSize();
    }
};

const setCenter = (lat, lng) => {
    center.value = [lat, lng];
    if (mapRef.value?.leafletObject) {
        mapRef.value.leafletObject.setView(center.value, 17);
    }
};

defineExpose({ invalidateMap, setCenter });
</script>

<template>
    <div class="w-full h-full relative bg-[#15151e]">
        <l-map ref="mapRef" :zoom="16" :center="center" @moveend="onMapMoveEnd" :options="{ zoomControl: false }" class="h-full w-full dark-map-filter z-0">
            <l-tile-layer url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png" />
        </l-map>

        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-10 pointer-events-none flex flex-col items-center pb-8">
            <div class="bg-primary text-white p-2 rounded-full shadow-f1-glow border-2 border-white">
                <LocateFixed :size="18" />
            </div>
            <div class="w-1 h-8 bg-primary/80"></div>
            <div class="w-3 h-1 bg-black/40 rounded-[100%] blur-[1px]"></div>
        </div>
    </div>
</template>

<style scoped>
.dark-map-filter { filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%); }
</style>