<script setup>
import { ref, nextTick, computed } from 'vue';
import ClientLocationPicker from '@/Components/Customer/Maps/ClientLocationPicker.vue';
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import { 
    Home, Briefcase, Users, MoreHorizontal, Navigation2, 
    MapPin, ArrowRight, ArrowLeft, Dumbbell, Heart, BookOpen, Building2,
    PencilLine // Icono añadido para indicar edición
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
    { id: 'familia', label: 'FAMILIA', icon: Users },
    { id: 'pareja', label: 'PAREJA', icon: Heart },
    { id: 'estudio', label: 'ESTUDIO', icon: BookOpen },
    { id: 'gym', label: 'GIMNASIO', icon: Dumbbell },
    { id: 'negocio', label: 'NEGOCIO', icon: Building2 },
    { id: 'otro', label: 'OTRO', icon: MoreHorizontal },
];

const previewIcon = L.divIcon({
    className: 'preview-pin',
    html: `<div class="w-8 h-8 bg-primary rounded-full border-[3px] border-white shadow-2xl flex items-center justify-center text-white"><div class="w-2 h-2 bg-white rounded-full animate-ping"></div></div>`,
    iconSize: [32, 32],
    iconAnchor: [16, 16]
});

// ESCUDO LÓGICO: Valida que la dirección sea real y no un texto de carga
const isAddressValid = computed(() => {
    const addr = props.form.address;
    return addr && 
           addr.trim().length > 3 && 
           !addr.includes('Calculando') && 
           !addr.includes('Analizando') &&
           !addr.includes('Triangulando');
});

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
        }, 300);
    });
};

const goBack = () => {
    if (internalStep.value === 2) {
        internalStep.value = 1;
        nextTick(() => {
            setTimeout(() => { if (mapComponentRef.value) mapComponentRef.value.fixMapLayout(); }, 300);
        });
    } else {
        emit('back');
    }
};
</script>

<template>
    <div class="flex-1 flex flex-col h-full w-full relative overflow-hidden">
        
        <div v-show="internalStep === 1" class="absolute inset-0 flex flex-col animate-in fade-in duration-500">
            <div class="flex-1 relative w-full h-full">
                
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
                
                <div v-if="form.latitude" 
                     class="fixed bottom-0 left-0 w-full z-[10000] p-4 lg:p-8 pb-safe bg-gradient-to-t from-background via-background/20 to-transparent pointer-events-none flex justify-center">
                    <div class="pointer-events-auto w-full max-w-xl glass-chassis p-5 md:p-6 shadow-2xl animate-in slide-in-from-bottom-8 duration-500">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-primary rounded-xl flex items-center justify-center text-white shadow-f1-glow">
                                    <Navigation2 :size="14" fill="currentColor" />
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Sensor de Ubicación</span>
                            </div>
                            <PencilLine :size="14" class="text-foreground/30 animate-pulse" />
                        </div>
                        
                        <div class="relative w-full mb-5 group">
                            <input 
                                v-model="form.address" 
                                type="text" 
                                placeholder="Analizando punto satelital..."
                                class="w-full bg-foreground/5 border border-transparent border-l-2 border-l-primary/50 hover:border-border/30 focus:border-primary/50 focus:bg-foreground/10 rounded-r-xl py-3 px-4 text-sm font-bold uppercase text-foreground italic outline-none transition-all shadow-inner"
                            />
                        </div>
                        
                        <div class="flex gap-3 relative">
                            <button @click="goBack" class="h-14 px-6 rounded-2xl bg-foreground/5 border border-border/30 text-foreground font-black uppercase text-[10px] tracking-widest active:scale-95 transition-all hover:bg-foreground/10">
                                <ArrowLeft :size="18" />
                            </button>
                            
                            <button @click="goToDetails" 
                                    :disabled="!isAddressValid"
                                    class="flex-1 h-14 bg-foreground text-background rounded-2xl font-black uppercase text-xs tracking-[0.2em] active:scale-95 transition-all duration-500 shadow-xl disabled:opacity-0 disabled:translate-y-4 disabled:pointer-events-none"
                                    :class="isAddressValid ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                                Confirmar Ubicación
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="internalStep === 2" class="flex-1 flex flex-col items-center justify-center p-4 lg:p-8 w-full animate-in fade-in slide-in-from-right-8 duration-700 z-[20000]">
            <div class="w-full max-w-xl glass-chassis rounded-[2.5rem] p-6 md:p-10 shadow-2xl relative">
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-black tracking-tighter uppercase italic leading-none mb-2 text-foreground">Detalles</h1>
                    <p class="text-primary text-[10px] font-black uppercase tracking-[0.3em] opacity-80">[ LOGISTICS_METADATA ]</p>
                </div>

                <div class="w-full space-y-6">
                    <div class="w-full rounded-[2rem] border border-border/40 overflow-hidden bg-foreground/5 shadow-inner">
                        <div class="relative w-full h-28 md:h-32">
                            <l-map ref="previewMapRef" :zoom="17" :center="[form.latitude || -16.5, form.longitude || -68.15]" :options="{ zoomControl: false, attributionControl: false, dragging: false, scrollWheelZoom: false }" class="h-full w-full dark-map-filter">
                                <l-tile-layer url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png" />
                                <l-marker :lat-lng="[form.latitude || -16.5, form.longitude || -68.15]" :icon="previewIcon" />
                            </l-map>
                        </div>
                        <div class="p-4 bg-background/60 backdrop-blur-md flex items-center gap-4">
                            <MapPin :size="18" class="text-primary shrink-0" />
                            <p class="text-[10px] font-bold text-foreground leading-tight uppercase italic line-clamp-2">{{ form.address }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-2">
                        <button v-for="p in presets" :key="p.id" type="button" @click="form.alias = p.label" 
                                class="flex flex-col items-center justify-center gap-2 p-3 rounded-2xl border transition-all" 
                                :class="form.alias === p.label ? 'border-primary bg-primary/10 text-primary' : 'border-border/30 bg-foreground/5 opacity-50'">
                            <component :is="p.icon" :size="16" stroke-width="2.5" />
                            <span class="text-[7px] font-black uppercase tracking-tighter">{{ p.label }}</span>
                        </button>
                    </div>

                    <input v-model="form.alias" type="text" class="w-full h-14 bg-foreground/5 border border-border/30 rounded-2xl px-5 text-sm font-bold uppercase text-foreground outline-none focus:border-primary/50 transition-all shadow-inner" placeholder="ALIAS (EJ: CASA DE CAMPO)">
                    <input v-model="form.details" type="text" class="w-full h-14 bg-foreground/5 border border-border/30 rounded-2xl px-5 text-sm font-bold uppercase text-foreground outline-none focus:border-primary/50 transition-all shadow-inner" placeholder="INDICACIONES DE ACCESO">

                    <div class="flex gap-4 pt-2">
                        <button @click="goBack" class="h-14 px-6 md:px-8 rounded-2xl bg-foreground/5 border border-border/40 text-foreground font-black uppercase text-[10px] tracking-widest hover:bg-foreground/10 transition-colors">Atrás</button>
                        <button @click="emit('next')" :disabled="!form.alias" class="flex-1 h-14 bg-foreground text-background rounded-2xl font-black uppercase text-xs tracking-[0.2em] active:scale-95 transition-all disabled:opacity-30">
                            {{ submitLabel }} <ArrowRight :size="18" stroke-width="3" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.glass-chassis { background: hsl(var(--background) / 0.8); backdrop-filter: blur(40px) saturate(200%); border: 1px solid hsl(var(--border) / 0.4); border-radius: 2.5rem; }
.dark .glass-chassis { background: hsl(var(--background) / 0.9); }
.dark-map-filter { filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%); }
.pb-safe { padding-bottom: calc(env(safe-area-inset-bottom) + 1rem); }
</style>