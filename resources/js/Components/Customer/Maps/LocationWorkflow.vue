<script setup>
import { ref, nextTick } from 'vue';
import ClientLocationPicker from '@/Components/Customer/Maps/ClientLocationPicker.vue';
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import { 
    Home, Briefcase, Users, MoreHorizontal, Navigation2, 
    MapPin, ArrowRight, ArrowLeft
} from 'lucide-vue-next';

const props = defineProps({
    form: Object, 
    activeBranches: Array,
    submitLabel: { type: String, default: 'CONTINUAR' }
});

const emit = defineEmits(['next', 'back']);

const internalStep = ref(1); 
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
    iconSize: [32, 32],
    iconAnchor: [16, 16]
});

const goToDetails = () => {
    if (props.form.address === 'Calculando...' || !props.form.address) return;
    
    // Forzamos el parseo a flotantes para evitar errores de tipo en Leaflet
    props.form.latitude = parseFloat(props.form.latitude);
    props.form.longitude = parseFloat(props.form.longitude);
    
    internalStep.value = 2;
    
    nextTick(() => {
        setTimeout(() => {
            if (previewMapRef.value?.leafletObject) {
                previewMapRef.value.leafletObject.invalidateSize();
                previewMapRef.value.leafletObject.setView([props.form.latitude, props.form.longitude], 17);
            }
        }, 300);
    });
};

const goBack = () => {
    if (internalStep.value === 2) {
        internalStep.value = 1;
        nextTick(() => {
            setTimeout(() => { 
                if (mapComponentRef.value) mapComponentRef.value.fixMapLayout(); 
            }, 300);
        });
    } else {
        emit('back');
    }
};
</script>

<template>
    <div class="flex-1 flex flex-col h-full w-full relative overflow-hidden">
        
        <div v-show="internalStep === 1" class="flex-1 relative flex flex-col h-full w-full animate-in fade-in duration-500">
            <div class="absolute inset-0">
                <ClientLocationPicker 
                    ref="mapComponentRef"
                    v-model:modelValueLat="form.latitude" 
                    v-model:modelValueLng="form.longitude" 
                    v-model:modelValueAddress="form.address" 
                    v-model:modelValueBranchId="form.branch_id" 
                    :activeBranches="activeBranches" 
                />
            </div>
            
            <div class="mt-auto z-[1001] p-6 animate-in slide-in-from-bottom-full duration-700">
                <div class="bg-background/80 backdrop-blur-2xl border border-white/10 rounded-[2.5rem] p-6 shadow-2xl max-w-lg mx-auto">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white shadow-f1-glow">
                            <Navigation2 :size="14" fill="currentColor" />
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Sensor de Ubicación</span>
                    </div>
                    <p class="text-base font-bold leading-snug min-h-[3rem] line-clamp-2 mb-6 border-l-2 border-primary/30 pl-4 italic uppercase">
                        {{ form.address || 'Detectando coordenadas...' }}
                    </p>
                    <div class="flex gap-3">
                        <button @click="goBack" class="h-14 px-6 rounded-2xl bg-foreground/5 text-foreground font-black uppercase text-[10px] tracking-widest">
                            <ArrowLeft :size="18" />
                        </button>
                        <button @click="goToDetails" :disabled="!form.address || form.address === 'Calculando...'" 
                                class="flex-1 h-14 bg-foreground text-background rounded-2xl font-black uppercase text-xs tracking-[0.2em] active:scale-95 transition-all disabled:opacity-30">
                            Confirmar Sector
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="internalStep === 2" class="flex-1 flex flex-col items-center justify-center p-8 max-w-xl mx-auto w-full animate-in fade-in slide-in-from-right-8 duration-700">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-black tracking-tighter uppercase italic leading-none mb-2">Detalles</h1>
                <p class="text-primary text-[10px] font-black uppercase tracking-[0.3em] opacity-80">[ LOGISTICS_METADATA ]</p>
            </div>

            <div class="w-full space-y-8">
                <div class="w-full rounded-[2.2rem] border border-border/40 overflow-hidden bg-foreground/5 shadow-2xl group relative">
                    <div class="relative w-full h-32">
                        <l-map ref="previewMapRef" :zoom="17" :center="[form.latitude || -16.5, form.longitude || -68.15]" :options="{ zoomControl: false, attributionControl: false, dragging: false, scrollWheelZoom: false }" class="h-full w-full grayscale-[0.4]">
                            <l-tile-layer url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png" />
                            <l-marker :lat-lng="[form.latitude || -16.5, form.longitude || -68.15]" :icon="previewIcon" />
                        </l-map>
                        <div class="absolute top-4 right-4 bg-primary/90 text-white px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-tighter z-[1000] shadow-f1-glow">SIGNAL_LOCKED</div>
                    </div>
                    <div class="p-5 bg-card/40 backdrop-blur-md flex items-center gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0 shadow-inner">
                            <MapPin :size="20" />
                        </div>
                        <p class="text-[11px] font-bold text-foreground leading-tight line-clamp-2 uppercase italic">{{ form.address }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 ml-1">Alias_ID</label>
                    <div class="grid grid-cols-4 gap-3">
                        <button v-for="p in presets" :key="p.id" type="button" @click="form.alias = p.label" 
                                class="flex flex-col items-center justify-center gap-2 p-4 rounded-2xl border transition-all" 
                                :class="form.alias === p.label ? 'border-primary bg-primary/10 text-primary scale-105 shadow-lg' : 'border-border/40 bg-foreground/[0.02] text-muted-foreground opacity-50'">
                            <component :is="p.icon" :size="18" stroke-width="3" />
                            <span class="text-[8px] font-black">{{ p.label }}</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 ml-1">Etiqueta Personalizada</label>
                        <input v-model="form.alias" type="text" class="w-full h-14 bg-foreground/[0.03] border border-border/40 rounded-[1.5rem] px-5 text-sm font-bold uppercase outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 ml-1">Instrucciones de Acceso</label>
                        <input v-model="form.details" type="text" class="w-full h-14 bg-foreground/[0.03] border border-border/40 rounded-[1.5rem] px-5 text-sm font-bold uppercase outline-none focus:ring-2 focus:ring-primary/20 transition-all" placeholder="PISO, COLOR DE PORTON, ETC...">
                    </div>
                </div>

                <div class="flex gap-4 pt-4">
                    <button @click="goBack" class="h-16 px-8 rounded-[1.8rem] bg-foreground/5 text-foreground font-black uppercase text-[10px] tracking-widest hover:bg-foreground/10 transition-colors">
                        ATRÁS
                    </button>
                    <button @click="emit('next')" :disabled="!form.alias" 
                            class="flex-1 h-16 bg-primary text-white rounded-[1.8rem] font-black uppercase text-[11px] tracking-[0.2em] shadow-xl shadow-primary/20 flex items-center justify-center gap-3 active:scale-95 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                        {{ submitLabel }} <ArrowRight :size="18" stroke-width="3" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>