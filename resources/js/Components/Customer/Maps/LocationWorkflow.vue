<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import ClientLocationPicker from '@/Components/Customer/Maps/ClientLocationPicker.vue';
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import { Home, Briefcase, Users, MoreHorizontal, Navigation2, ArrowRight, ArrowLeft, LocateFixed, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    form: { type: Object, required: true }, 
    activeBranches: { type: Array, required: true },
    collidingBranches: { type: Array, default: () => [] },
    isActive: { type: Boolean, required: true }, 
    submitLabel: { type: String, default: 'CONTINUAR' }
});

const emit = defineEmits(['next', 'back']);

const internalStep = ref(1); 
const mapComponentRef = ref(null);
const previewMapRef = ref(null);

const geoError = ref('');
const isLocating = ref(false);

const presets = [
    { id: 'casa', label: 'CASA', icon: Home }, 
    { id: 'trabajo', label: 'TRABAJO', icon: Briefcase },
    { id: 'familia', label: 'FAMILIA', icon: Users }, 
    { id: 'otro', label: 'OTRO', icon: MoreHorizontal },
];

const previewIcon = L.divIcon({
    className: 'preview-pin',
    html: `<div class="w-6 h-6 bg-primary rounded-full border-2 border-white shadow-f1-glow flex items-center justify-center text-white"><div class="w-1.5 h-1.5 bg-white rounded-full animate-ping"></div></div>`,
    iconSize: [24, 24], iconAnchor: [12, 12]
});

const isAddressValid = computed(() => {
    return props.form.latitude && props.form.longitude;
});

watch(
    () => [props.form.latitude, props.form.longitude],
    ([lat, lng]) => {
        if (lat && lng) {
            router.reload({
                data: { latitude: lat, longitude: lng },
                only: ['collidingBranches'],
                preserveState: true,
                preserveScroll: true
            });
        }
    }
);

watch(
    () => props.collidingBranches,
    (branches) => {
        if (!branches || branches.length === 0) {
            props.form.branch_id = null;
        } else if (branches.length === 1) {
            props.form.branch_id = branches[0].id;
        } else {
            const valid = branches.some(b => b.id === props.form.branch_id);
            if (!valid) props.form.branch_id = null;
        }
    },
    { deep: true }
);

const triggerGeolocation = () => {
    geoError.value = '';
    isLocating.value = true;

    if (!navigator.geolocation) {
        geoError.value = 'Hardware GPS no detectado. Seleccione manualmente.';
        isLocating.value = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            props.form.latitude = pos.coords.latitude;
            props.form.longitude = pos.coords.longitude;
            props.form.address = `Coordenadas: ${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
            isLocating.value = false;
        },
        () => {
            geoError.value = 'Señal GPS denegada. Arrastre el mapa para seleccionar.';
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
};

const goBack = () => {
    if (internalStep.value === 2) {
        internalStep.value = 1;
    } else {
        emit('back');
    }
};
</script>

<template>
    <div class="w-full h-full relative overflow-hidden">
        
        <div v-if="internalStep === 1" key="map-step-container" class="absolute inset-0 w-full h-full flex flex-col z-10">
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
                            
                            <div v-if="isAddressValid" class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded">
                                <span v-if="collidingBranches.length === 0" class="text-red-500 bg-red-500/10 px-2 py-1 rounded border border-red-500/20">Zona Fantasma (Sin Despacho)</span>
                                <span v-else-if="collidingBranches.length === 1" class="text-green-500 bg-green-500/10 px-2 py-1 rounded border border-green-500/20">Cobertura Confirmada</span>
                                <span v-else class="text-amber-500 bg-amber-500/10 px-2 py-1 rounded border border-amber-500/20">Intersección de Coberturas</span>
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
                                <button @click="triggerGeolocation" :disabled="isLocating" class="w-11 h-11 shrink-0 flex items-center justify-center rounded-xl bg-transparent border border-[#32323b] text-foreground hover:bg-foreground/5 transition-colors focus:outline-none disabled:opacity-50">
                                    <Loader2 v-if="isLocating" :size="16" class="animate-spin" />
                                    <LocateFixed v-else :size="16" class="text-primary" />
                                </button>
                                <input v-model="form.address" type="text" class="flex-1 w-full bg-transparent border border-[#32323b] border-l-2 border-l-primary rounded-xl py-2 px-3 text-[10px] font-bold text-foreground outline-none transition-all focus:border-primary/50" />
                            </div>
                            
                            <div class="flex gap-3 mt-1">
                                <button @click="goBack" class="h-11 px-5 rounded-xl bg-transparent border border-[#32323b] text-foreground font-black uppercase text-[10px] tracking-widest hover:bg-foreground/5 transition-colors">Atrás</button>
                                <button @click="goToDetails" :disabled="!isAddressValid" class="flex-1 h-11 btn-primary text-[10px] tracking-[0.2em] flex items-center justify-center">
                                    Confirmar Ubicación
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="internalStep === 2" key="details-step-container" class="absolute inset-0 w-full h-full flex flex-col items-center justify-center p-4 lg:p-8 z-20 bg-background/50 backdrop-blur-sm">
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

                    <div v-if="collidingBranches.length > 1" class="space-y-1 p-3 rounded-xl border border-amber-500/20 bg-amber-500/5">
                        <label class="text-[9px] font-black uppercase tracking-widest text-amber-500 ml-0.5">Sucursal de Despacho Requerida</label>
                        <select v-model="form.branch_id" class="w-full h-11 bg-[#15151e] border border-[#32323b] rounded-xl px-4 text-sm font-bold text-foreground focus:border-primary/50 transition-colors outline-none">
                            <option :value="null" disabled selected>Seleccione la sucursal de preferencia...</option>
                            <option v-for="branch in collidingBranches" :key="branch.id" :value="branch.id">
                                {{ branch.name }}
                            </option>
                        </select>
                        <p class="text-[8px] text-amber-400 font-bold uppercase mt-1">Su ubicación se encuentra en una intersección logística. Debe designar la sucursal.</p>
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
                        </div>
                        <div class="w-full">
                            <input v-model="form.details" type="text" class="w-full h-11 bg-transparent border border-[#32323b] rounded-xl px-4 text-sm font-bold text-foreground outline-none focus:border-primary/50 transition-colors placeholder:text-neutral-500" placeholder="Indicaciones de acceso">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button @click="goBack" class="h-11 px-5 rounded-xl bg-transparent border border-[#32323b] text-foreground font-black uppercase text-[10px] tracking-widest hover:bg-foreground/5 transition-colors">Atrás</button>
                        <button @click="emit('next')" :disabled="!form.alias || (collidingBranches.length > 1 && !form.branch_id)" class="flex-1 h-11 btn-primary flex items-center justify-center gap-2 disabled:opacity-30">
                            {{ submitLabel }} <ArrowRight :size="14" :stroke-width="3" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</template>