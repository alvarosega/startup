<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import debounce from 'lodash/debounce';
import { Loader2, Navigation, MapPin, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    modelValueLat: Number,
    modelValueLng: Number,
    modelValueAddress: String,
    activeBranches: { type: Array, default: () => [] },
    center: { type: Array, default: () => [-16.5000, -68.1500] },
    zoom: { type: Number, default: 15 }
});

const emit = defineEmits(['update:modelValueLat', 'update:modelValueLng', 'update:modelValueAddress', 'update:modelValueBranchId']);

// ESTADOS LOCALES PARA EVITAR MUTAR PROPS
const mapRef = ref(null);
const currentZoom = ref(props.zoom);
const currentCenter = ref(props.center);

const isLocating = ref(false);
// El pin nace muerto. Solo revive si hay datos previos válidos o el usuario pulsa el botón.
const locationActivated = ref(props.modelValueLat && Math.abs(props.modelValueLat) > 1);
const mapMoving = ref(false);
const showPrecisionHint = ref(false);
const geoError = ref(false);

const mapUrl = "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png";

// GEOLOCALIZACIÓN INVERSA (Nominatim original)
const reverseGeocode = debounce(async (lat, lng) => {
    if (mapMoving.value) return;
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
        if (!response.ok) throw new Error("Servicio de mapas no disponible");
        
        const data = await response.json();
        if (data?.address) {
            const a = data.address;
            const short = `${a.road || a.pedestrian || ''} ${a.house_number || ''}, ${a.neighbourhood || a.suburb || ''}`.trim();
            emit('update:modelValueAddress', short || data.display_name.split(',')[0]);
        } else {
            emit('update:modelValueAddress', "Punto seleccionado (Sin nombre de calle)");
        }
    } catch (e) { 
        console.error("GeoError:", e);
        emit('update:modelValueAddress', "Ubicación detectada (Ajuste manual)");
    }
}, 600);

const handleMapMove = () => {
    if (!locationActivated.value || !mapRef.value?.leafletObject) return;
    const center = mapRef.value.leafletObject.getCenter();
    
    // Sincronizar datos con el padre
    emit('update:modelValueLat', center.lat);
    emit('update:modelValueLng', center.lng);
    
    // Feedback de carga
    emit('update:modelValueAddress', 'Calculando dirección...');
    reverseGeocode(center.lat, center.lng);
};

const locateUser = () => {
    if (!navigator.geolocation) {
        alert("Tu navegador no tiene permisos de GPS.");
        return;
    }
    
    isLocating.value = true;
    geoError.value = false;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            
            // 1. Activamos la visibilidad del Pin
            locationActivated.value = true;
            
            // 2. Movemos el mapa
            currentCenter.value = [latitude, longitude];
            
            // Refresco de layout antes del vuelo
            nextTick(() => {
                if(mapRef.value?.leafletObject) {
                    mapRef.value.leafletObject.invalidateSize();
                    mapRef.value.leafletObject.flyTo([latitude, longitude], 18, { duration: 1.5 });
                }
            });
            
            // 3. Al terminar el vuelo, recalculamos todo
            setTimeout(() => {
                isLocating.value = false;
                showPrecisionHint.value = true;
                fixMapLayout(); // Refresco preventivo
                handleMapMove();
            }, 1600);
        },
        (error) => { 
            isLocating.value = false; 
            geoError.value = true;
            alert("Error: Debes activar el GPS y permitir el acceso."); 
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
};

// REGLA DE ORO: Recalcular tamaño de Leaflet para evitar pantallas blancas
const fixMapLayout = () => {
    nextTick(() => {
        if (mapRef.value?.leafletObject) {
            mapRef.value.leafletObject.invalidateSize();
        }
    });
};

// Si el padre nos cambia de paso, refrescamos el mapa
defineExpose({ fixMapLayout });

onMounted(() => {
    // Si ya viene con ubicación, inicializar
    if (locationActivated.value) {
        reverseGeocode(props.modelValueLat, props.modelValueLng);
    }
    fixMapLayout();
});
const activateManualSelection = () => {
    // Dictamen: Desbloqueo forzado en coordenadas de contingencia
    locationActivated.value = true;
    nextTick(() => {
        if(mapRef.value?.leafletObject) {
            mapRef.value.leafletObject.invalidateSize();
            // Centramos en el centro operativo predefinido (props.center)
            mapRef.value.leafletObject.setView(props.center, 16);
            handleMapMove();
        }
    });
};
</script>

<template>
    <div class="relative w-full h-full min-h-[100%] bg-[#f0f2f5] overflow-hidden flex flex-col">
        
        <l-map ref="mapRef" 
               v-model:zoom="currentZoom" 
               v-model:center="currentCenter"
               :use-global-leaflet="false"
               :options="{ zoomControl: false, attributionControl: false }"
               @movestart="mapMoving = true; showPrecisionHint = false"
               @moveend="mapMoving = false; handleMapMove()"
               class="absolute inset-0 h-full w-full z-0 outline-none">
            <l-tile-layer :url="mapUrl" />
        </l-map>

        <div v-if="locationActivated" 
             class="absolute inset-0 flex items-center justify-center pointer-events-none z-[1000]">
            
            <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100">
                <div v-if="showPrecisionHint" 
                     class="absolute mb-44 bg-[#0A192F] text-white px-4 py-2 rounded-2xl shadow-2xl flex flex-col items-center">
                    <p class="text-[10px] font-black uppercase tracking-tight">¿Punto exacto?</p>
                    <p class="text-[8px] opacity-70 font-bold">MUEVE EL MAPA PARA AJUSTAR</p>
                    <div class="absolute -bottom-1 w-3 h-3 bg-[#0A192F] rotate-45"></div>
                </div>
            </Transition>

            <div class="relative flex flex-col items-center translate-y-[-22px] transition-transform duration-300"
                 :class="{ 'scale-110 -translate-y-10': mapMoving }">
                <div class="w-10 h-10 rounded-full bg-white shadow-2xl flex items-center justify-center border-[3px] border-primary">
                    <div class="w-2 h-2 bg-primary rounded-full" :class="{'animate-ping': !mapMoving}"></div>
                </div>
                <div class="w-1 h-6 bg-primary shadow-lg"></div>
                <div class="w-4 h-1.5 bg-black/20 rounded-full blur-[2px] mt-1 transition-all"
                     :class="mapMoving ? 'scale-150 opacity-20' : 'scale-100 opacity-100'"></div>
            </div>
        </div>

        <div v-if="!locationActivated" 
             class="absolute inset-0 bg-white/60 backdrop-blur-[8px] z-[1001] flex flex-col items-center justify-center p-8 text-center">
            <div class="w-24 h-24 bg-primary/10 rounded-[3rem] flex items-center justify-center text-primary mb-6 shadow-inner">
                <MapPin :size="48" stroke-width="2.5" />
            </div>
            <h2 class="text-3xl font-black text-[#0A192F] uppercase italic mb-3 tracking-tighter">Acceso a Ubicación</h2>
            <p class="text-xs text-muted-foreground font-bold uppercase tracking-widest mb-10 max-w-[280px]">
                Recomendamos GPS para máxima precisión, o puedes seleccionar manualmente.
            </p>
            
            <div class="flex flex-col w-full gap-3 max-w-[280px]">
                <button @click="locateUser" 
                        class="bg-primary text-white w-full py-5 rounded-[24px] font-black uppercase text-xs tracking-[0.2em] shadow-xl flex items-center justify-center gap-4 active:scale-95 transition-all">
                    <Navigation :size="20" fill="currentColor" /> Sincronizar GPS
                </button>
                
                <button @click="activateManualSelection" 
                        class="bg-white text-[#0A192F] border-2 border-[#0A192F]/10 w-full py-4 rounded-[24px] font-black uppercase text-[10px] tracking-[0.1em] active:scale-95 transition-all">
                    Selección Manual
                </button>
            </div>
        </div>
        <div v-if="isLocating" class="absolute inset-0 bg-white/90 backdrop-blur-md z-[2000] flex flex-col items-center justify-center">
            <Loader2 :size="40" class="text-primary animate-spin mb-4" />
            <p class="font-black text-[10px] uppercase tracking-[0.3em] text-[#0A192F] animate-pulse">Consultando Satélites...</p>
        </div>

        <button v-if="locationActivated && !isLocating" 
                @click="locateUser" 
                class="absolute bottom-6 right-6 z-[1000] w-12 h-12 bg-white rounded-2xl shadow-xl flex items-center justify-center text-primary border border-black/5 active:scale-90 transition-all">
            <Navigation :size="20" />
        </button>
    </div>
</template>