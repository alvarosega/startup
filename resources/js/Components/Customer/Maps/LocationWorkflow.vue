<script setup>
import { ref, nextTick, computed, watch } from 'vue';
import ClientLocationPicker from '@/Components/Customer/Maps/ClientLocationPicker.vue';
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import { Home, Briefcase, Users, MoreHorizontal, Navigation2, MapPin, ArrowRight, ArrowLeft, Dumbbell, Heart, BookOpen, Building2, LocateFixed, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    form: Object, 
    activeBranches: Array,
    isActive: Boolean, 
    submitLabel: { type: String, default: 'CONTINUAR' }
});

const emit = defineEmits(['next', 'back']);

const internalStep = ref(1); 
const mapComponentRef = ref(null);
const previewMapRef = ref(null);

const geoError = ref('');
const isLocating = ref(false);

const presets = [
    { id: 'casa', label: 'CASA', icon: Home }, { id: 'trabajo', label: 'TRABAJO', icon: Briefcase },
    { id: 'familia', label: 'FAMILIA', icon: Users }, { id: 'pareja', label: 'PAREJA', icon: Heart },
    { id: 'estudio', label: 'ESTUDIO', icon: BookOpen }, { id: 'gym', label: 'GIMNASIO', icon: Dumbbell },
    { id: 'negocio', label: 'NEGOCIO', icon: Building2 }, { id: 'otro', label: 'OTRO', icon: MoreHorizontal },
];

const previewIcon = L.divIcon({
    className: 'preview-pin',
    html: `<div class="w-6 h-6 bg-primary rounded-full border-2 border-white shadow-f1-glow flex items-center justify-center text-white"><div class="w-1.5 h-1.5 bg-white rounded-full animate-ping"></div></div>`,
    iconSize: [24, 24], iconAnchor: [12, 12]
});

const isAddressValid = computed(() => {
    return props.form.latitude && props.form.longitude;
});

watch(() => props.isActive, (newVal) => {
    if (newVal) {
        nextTick(() => {
            setTimeout(() => { 
                if (mapComponentRef.value) mapComponentRef.value.invalidateMap(); 
                if (previewMapRef.value?.leafletObject) previewMapRef.value.leafletObject.invalidateSize();
            }, 300);
        });
    }
});

const triggerGeolocation = () => {
    geoError.value = '';
    isLocating.value = true;

    if (!navigator.geolocation) {
        geoError.value = 'Hardware GPS no detectado. Seleccione manualmente en el mapa.';
        isLocating.value = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            props.form.latitude = lat;
            props.form.longitude = lng;
            props.form.address = `Coordenadas: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            isLocating.value = false;
            
            if (mapComponentRef.value) {
                mapComponentRef.value.setCenter(lat, lng);
            }
        },
        (err) => {
            geoError.value = 'Señal GPS denegada o débil. Arrastre el mapa para seleccionar.';
            isLocating.value = false;
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
};

const goToDetails = () => {
    if (!isAddressValid.value) return;
    
    props.form.latitude = parseFloat(props.form.latitude);
    props.form.longitude = parseFloat(props.form.longitude);
    internalStep.value = 2;
    
    nextTick(() => {
        setTimeout(() => {
            if (previewMapRef.value?.leafletObject) {
                previewMapRef.value.leafletObject.invalidateSize();
                previewMapRef.value.leafletObject.setView([props.form.latitude, props.form.longitude], 17);
            }
        }, 150);
    });
};

const goBack = () => {
    if (internalStep.value === 2) {
        internalStep.value = 1;
        nextTick(() => {
            setTimeout(() => { if (mapComponentRef.value) mapComponentRef.value.invalidateMap(); }, 150);
        });
    } else {
        emit('back');
    }
};
</script>

<template>
    <div class="w-full h-full relative overflow-hidden">
        
        <div v-show="internalStep === 1" class="absolute inset-0 w-full h-full flex flex-col z-10">
            <div class="relative w-full h-full flex-1">
                
                <div class="absolute inset-0 z-0">
                    <ClientLocationPicker 
                        ref="mapComponentRef"
                        v-model:modelValueLat="form.latitude" 
                        v-model:modelValueLng="form.longitude" 
                        v-model:modelValueAddress="form.address" 
                        v-model:modelValueBranchId="form.branch_id" 
                        :activeBranches="activeBranches" 
                    />
                </div>
                
                <div class="absolute bottom-0 left-0 w-full z-[1000] p-4 lg:p-8 bg-gradient-to-t from-background via-background/40 to-transparent flex justify-center pointer-events-none">
                    <div class="w-full max-w-xl bg-background dark:bg-[#15151e] border border-[#32323b] p-5 rounded-xl shadow-2xl pointer-events-auto">
                        
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded flex items-center justify-center text-white" :class="form.latitude ? 'bg-primary' : 'bg-neutral-500'">
                                    <Navigation2 :size="12" fill="currentColor" />
                                </div>
                                <span class="text-[9px] font-black uppercase tracking-[0.2em]" :class="form.latitude ? 'text-primary' : 'text-neutral-500'">Ubicación GPS</span>
                            </div>
                        </div>
                        
                        <div v-if="geoError" class="mb-4 p-2 rounded-lg bg-red-500/10 border border-red-500/20">
                            <p class="text-[8px] text-red-500 font-bold uppercase">{{ geoError }}</p>
                        </div>
                        
                        <div v-if="!form.latitude" class="flex flex-col gap-3">
                            <p class="text-[10px] text-neutral-400 font-bold uppercase text-center mb-1">Inicie el escaneo satelital o arrastre el mapa manualmente</p>
                            <div class="flex gap-3">
                                <button @click="goBack" class="h-11 w-11 flex items-center justify-center rounded-xl bg-transparent border border-[#32323b] text-foreground hover:bg-foreground/5 transition-colors"><ArrowLeft :size="16" /></button>
                                <button @click="triggerGeolocation" :disabled="isLocating" class="flex-1 h-11 btn-primary flex items-center justify-center gap-2 disabled:opacity-50">
                                    <Loader2 v-if="isLocating" :size="14" class="animate-spin" />
                                    <template v-else>Geolocalizar Ahora <LocateFixed :size="14" /></template>
                                </button>
                            </div>
                        </div>

                        <div v-else class="flex flex-col gap-3">
                            <div class="flex gap-2">
                                <button @click="triggerGeolocation" :disabled="isLocating" title="Relocalizar GPS" class="w-11 h-11 shrink-0 flex items-center justify-center rounded-xl bg-transparent border border-[#32323b] text-foreground hover:bg-foreground/5 transition-colors focus:outline-none disabled:opacity-50">
                                    <Loader2 v-if="isLocating" :size="16" class="animate-spin" />
                                    <LocateFixed v-else :size="16" class="text-primary" />
                                </button>
                                <input v-model="form.address" type="text" class="flex-1 w-full bg-transparent border border-[#32323b] border-l-2 border-l-primary rounded-xl py-2 px-3 text-[10px] font-bold text-foreground outline-none transition-all focus:border-primary/50" />
                            </div>
                            
                            <div class="flex gap-3 mt-1">
                                <button @click="goBack" class="h-11 px-5 rounded-xl bg-transparent border border-[#32323b] text-foreground font-black uppercase text-[10px] tracking-widest hover:bg-foreground/5 transition-colors">Atrás</button>
                                <button @click="goToDetails" :disabled="!isAddressValid" class="flex-1 h-11 btn-primary text-[10px] tracking-[0.2em] disabled:opacity-30 flex items-center justify-center">
                                    Confirmar Ubicación
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div v-show="internalStep === 2" class="absolute inset-0 w-full h-full flex flex-col items-center justify-center p-4 lg:p-8 z-20 bg-background/50 backdrop-blur-sm">
            <div class="w-full max-w-xl bg-background dark:bg-[#15151e] border border-[#32323b] rounded-xl p-6 shadow-2xl relative">
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-black tracking-tighter uppercase italic text-foreground">Detalles</h1>
                    <p class="text-primary text-[9px] font-black uppercase tracking-[0.3em] mt-1">[ LOGISTICS_METADATA ]</p>
                </div>

                <div class="w-full space-y-4">
                    <div class="w-full rounded-xl border border-[#32323b] overflow-hidden">
                        <div class="relative w-full h-24">
                            <l-map ref="previewMapRef" :zoom="17" :center="[form.latitude || -16.5, form.longitude || -68.15]" :options="{ zoomControl: false, attributionControl: false, dragging: false, scrollWheelZoom: false }" class="h-full w-full dark-map-filter">
                                <l-tile-layer url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png" />
                                <l-marker :lat-lng="[form.latitude || -16.5, form.longitude || -68.15]" :icon="previewIcon" />
                            </l-map>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-2">
                        <button v-for="p in presets" :key="p.id" type="button" @click="form.alias = p.label" 
                                class="flex flex-col items-center justify-center gap-1.5 p-2 rounded-lg border transition-colors outline-none focus:outline-none" 
                                :class="form.alias === p.label ? 'border-primary bg-primary/10 text-primary' : 'border-[#32323b] bg-transparent opacity-50'">
                            <component :is="p.icon" :size="14" :stroke-width="2.5" />
                            <span class="text-[8px] font-black uppercase tracking-tighter">{{ p.label }}</span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div class="w-full">
                            <input v-model="form.alias" type="text" class="w-full h-11 bg-transparent border border-[#32323b] rounded-xl px-4 text-sm font-bold text-foreground outline-none focus:border-primary/50 transition-colors placeholder:text-neutral-500" placeholder="Alias (Ej: Casa de Campo)">
                            <p v-if="form.errors.alias" class="text-[8px] text-red-500 font-bold uppercase mt-1">{{ form.errors.alias }}</p>
                        </div>
                        <div class="w-full">
                            <input v-model="form.details" type="text" class="w-full h-11 bg-transparent border border-[#32323b] rounded-xl px-4 text-sm font-bold text-foreground outline-none focus:border-primary/50 transition-colors placeholder:text-neutral-500" placeholder="Indicaciones de acceso">
                            <p v-if="form.errors.details" class="text-[8px] text-red-500 font-bold uppercase mt-1">{{ form.errors.details }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button @click="goBack" class="h-11 px-5 rounded-xl bg-transparent border border-[#32323b] text-foreground font-black uppercase text-[10px] tracking-widest hover:bg-foreground/5 transition-colors">Atrás</button>
                        <button @click="emit('next')" :disabled="!form.alias" class="flex-1 h-11 btn-primary flex items-center justify-center gap-2 disabled:opacity-30">
                            {{ submitLabel }} <ArrowRight :size="14" :stroke-width="3" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</template>

<style scoped>
.dark-map-filter { filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%); }
</style>