<script setup>
import { ref, watch, nextTick } from 'vue';
import AdminLocationPicker from './AdminLocationPicker.vue';
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import { Home, Briefcase, Users, MoreHorizontal, Navigation2, MapPin, ArrowRight, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    form: Object, 
    activeBranches: Array,
    step: { type: Number, required: true } // 1 = Mapa, 2 = Detalles
});

const emit = defineEmits(['next', 'back']);
const mapComponentRef = ref(null);
const previewMapRef = ref(null);

const presets = [
    { id: 'casa', label: 'CASA', icon: Home },
    { id: 'trabajo', label: 'TRABAJO', icon: Briefcase },
    { id: 'amigos', label: 'AMIGOS', icon: Users },
    { id: 'otro', label: 'OTRO', icon: MoreHorizontal },
];

const previewIcon = L.divIcon({
    className: 'preview-pin',
    html: `<div class="w-8 h-8 bg-primary rounded-full border-[3px] border-white shadow-2xl flex items-center justify-center text-white"><div class="w-2 h-2 bg-white rounded-full animate-ping"></div></div>`,
    iconSize: [32, 32], iconAnchor: [16, 16]
});

// Observamos el paso para renderizar el minimapa correctamente
watch(() => props.step, (newVal) => {
    if (newVal === 2) {
        nextTick(() => {
            setTimeout(() => {
                if (previewMapRef.value?.leafletObject) {
                    previewMapRef.value.leafletObject.invalidateSize();
                    previewMapRef.value.leafletObject.setView([props.form.latitude, props.form.longitude], 17);
                }
            }, 300);
        });
    }
});

const handleConfirmSector = () => {
    props.form.latitude = parseFloat(props.form.latitude);
    props.form.longitude = parseFloat(props.form.longitude);
    emit('next');
};
</script>

<template>
    <div class="flex-1 flex flex-col h-full w-full relative overflow-hidden bg-zinc-950">
        
        <div v-show="step === 1" class="flex-1 relative flex flex-col h-full w-full animate-in fade-in duration-500">
            <div class="absolute inset-0">
                <AdminLocationPicker 
                    ref="mapComponentRef"
                    v-model:modelValueLat="form.latitude" 
                    v-model:modelValueLng="form.longitude" 
                    v-model:modelValueAddress="form.address" 
                    v-model:modelValueBranchId="form.branch_id" 
                    :activeBranches="activeBranches" 
                />
            </div>
            <div class="mt-auto z-[1001] p-6">
                <div class="bg-zinc-900 border border-zinc-800 p-6 shadow-2xl max-w-lg mx-auto">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-primary/20 border border-primary rounded-none flex items-center justify-center text-primary">
                            <Navigation2 :size="14" />
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400">Sensor de Ubicación</span>
                    </div>
                    <p class="text-sm font-mono text-white leading-snug min-h-[3rem] line-clamp-2 mb-6 border-l-2 border-primary pl-4 uppercase">
                        {{ form.address || 'Calculando vectores...' }}
                    </p>
                    <div class="flex gap-3">
                        <button type="button" @click="emit('back')" class="h-14 px-6 bg-zinc-800 text-zinc-400 font-black uppercase text-[10px] tracking-widest hover:bg-zinc-700 transition-colors">
                            <ArrowLeft :size="18" />
                        </button>
                        <button type="button" @click="handleConfirmSector" :disabled="!form.address || form.address === 'Calculando...'" 
                                class="flex-1 h-14 bg-primary text-white font-black uppercase text-xs tracking-[0.2em] active:scale-95 transition-all disabled:opacity-30 disabled:bg-zinc-800">
                            Confirmar Sector
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="step === 2" class="flex-1 flex flex-col items-center justify-center p-8 max-w-xl mx-auto w-full animate-in fade-in slide-in-from-right-8 duration-700">
            <div class="w-full text-left border-l-2 border-primary pl-4 mb-8">
                <h1 class="text-xl font-black uppercase italic text-white tracking-tighter">Metadata Logística</h1>
                <p class="text-primary text-[9px] font-mono tracking-[0.3em]">[ LOG_METADATA_REQUIRED ]</p>
            </div>

            <div class="w-full space-y-6">
                <div class="w-full border border-zinc-800 bg-zinc-900 shadow-2xl relative">
                    <div class="relative w-full h-32">
                        <l-map ref="previewMapRef" :zoom="17" :center="[form.latitude || -16.5, form.longitude || -68.15]" :options="{ zoomControl: false, attributionControl: false, dragging: false, scrollWheelZoom: false }" class="h-full w-full grayscale-[0.6]">
                            <l-tile-layer url="https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png" />
                            <l-marker :lat-lng="[form.latitude || -16.5, form.longitude || -68.15]" :icon="previewIcon" />
                        </l-map>
                        <div class="absolute top-2 right-2 bg-primary text-white px-2 py-1 text-[8px] font-black uppercase tracking-widest z-[1000]">SIGNAL_LOCKED</div>
                    </div>
                    <div class="p-4 flex items-center gap-4 border-t border-zinc-800">
                        <MapPin :size="20" class="text-primary shrink-0" />
                        <p class="text-[10px] font-mono text-zinc-400 uppercase line-clamp-2">{{ form.address }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-4 gap-2">
                        <button v-for="p in presets" :key="p.id" type="button" @click="form.alias = p.label" 
                                class="flex flex-col items-center gap-2 p-3 border transition-all bg-zinc-950" 
                                :class="form.alias === p.label ? 'border-primary text-primary' : 'border-zinc-800 text-zinc-600 hover:border-zinc-600'">
                            <component :is="p.icon" :size="16" />
                            <span class="text-[8px] font-mono font-black">{{ p.label }}</span>
                        </button>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-mono font-black text-zinc-500 uppercase tracking-widest ml-1">// ETIQUETA PERSONALIZADA</label>
                        <input v-model="form.alias" type="text" class="w-full bg-zinc-950 border border-zinc-800 focus:border-primary px-4 py-3 text-sm font-mono text-white outline-none transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-mono font-black text-zinc-500 uppercase tracking-widest ml-1">// INSTRUCCIONES DE ACCESO</label>
                        <input v-model="form.details" type="text" class="w-full bg-zinc-950 border border-zinc-800 focus:border-primary px-4 py-3 text-sm font-mono text-white outline-none transition-all" placeholder="Piso, Puerta, Timbre...">
                    </div>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="button" @click="emit('back')" class="h-14 px-8 bg-zinc-800 text-zinc-400 font-black uppercase text-[10px] tracking-widest hover:bg-zinc-700 transition-colors">ATRÁS</button>
                    <button type="button" @click="emit('next')" :disabled="!form.alias" class="flex-1 h-14 bg-primary text-white font-black uppercase text-[10px] tracking-[0.2em] flex items-center justify-center gap-2 active:scale-95 transition-all disabled:opacity-30 disabled:bg-zinc-800">
                        CONTINUAR <ArrowRight :size="16" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>