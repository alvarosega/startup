<script setup>
import { ref, watch, nextTick } from 'vue';
import AdminLocationPicker from './AdminLocationPicker.vue';
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';

const props = defineProps({
    form: Object, 
    activeBranches: Array
});

const step = ref(1); // 1 = Captura en Mapa, 2 = Metadatos de Acceso
const mapComponentRef = ref(null);
const previewMapRef = ref(null);

const presets = [
    { id: 'casa', label: 'CASA', icon: 'home' },
    { id: 'trabajo', label: 'TRABAJO', icon: 'work' },
    { id: 'amigos', label: 'AMIGOS', icon: 'group' },
    { id: 'otro', label: 'OTRO', icon: 'more_horiz' },
];

const previewIcon = L.divIcon({
    className: 'preview-pin',
    html: `<div class="w-6 h-6 bg-primary rounded-full border-2 border-white shadow-md flex items-center justify-center"></div>`,
    iconSize: [24, 24], iconAnchor: [12, 12]
});

watch(step, (newVal) => {
    if (newVal === 2) {
        nextTick(() => {
            setTimeout(() => {
                if (previewMapRef.value?.leafletObject) {
                    previewMapRef.value.leafletObject.invalidateSize();
                    previewMapRef.value.leafletObject.setView([props.form.latitude, props.form.longitude], 16);
                }
            }, 250);
        });
    }
});

const handleConfirmSector = () => {
    props.form.latitude = parseFloat(props.form.latitude);
    props.form.longitude = parseFloat(props.form.longitude);
    step.value = 2;
};
</script>

<template>
    <div class="w-full border border-border rounded-md bg-card overflow-hidden min-h-[440px] flex flex-col relative">
        
        <div v-show="step === 1" class="flex-1 flex flex-col h-full min-h-[440px] relative">
            <div class="absolute inset-0 bottom-32">
                <AdminLocationPicker 
                    ref="mapComponentRef"
                    v-model:modelValueLat="form.latitude" 
                    v-model:modelValueLng="form.longitude" 
                    v-model:modelValueAddress="form.address" 
                    v-model:modelValueBranchId="form.branch_id" 
                    :activeBranches="activeBranches" 
                />
            </div>
            
            <div class="absolute bottom-0 left-0 right-0 h-32 bg-card border-t border-border p-4 flex flex-col justify-between select-none z-[1001]">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-rounded text-primary text-lg shrink-0 mt-0.5">navigation</span>
                    <p class="text-xs font-mono text-foreground leading-tight line-clamp-2 uppercase">
                        {{ form.address || 'Calculando vectores de posicionamiento...' }}
                    </p>
                </div>
                <button type="button" @click="handleConfirmSector" :disabled="!form.address || form.address.includes('Calculando')" 
                        class="admin-btn-primary w-full py-2 text-xs font-bold uppercase tracking-wider disabled:opacity-40">
                    Confirmar Sector Geográfico
                </button>
            </div>
        </div>

        <div v-show="step === 2" class="p-5 flex-1 flex flex-col justify-between min-h-[440px] select-none">
            <div class="space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-border/60">
                    <span class="material-symbols-rounded text-primary text-lg">layers</span>
                    <h3 class="text-xs font-mono font-black text-foreground tracking-wider uppercase">Metadata Logística</h3>
                </div>

                <div class="w-full border border-border rounded-md overflow-hidden bg-neutral-50 dark:bg-neutral-900">
                    <div class="w-full h-24 relative z-10">
                        <l-map ref="previewMapRef" :zoom="16" :center="[form.latitude || -16.5, form.longitude || -68.15]" :options="{ zoomControl: false, attributionControl: false, dragging: false, scrollWheelZoom: false }" class="h-full w-full">
                            <l-tile-layer url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png" />
                            <l-marker :lat-lng="[form.latitude || -16.5, form.longitude || -68.15]" :icon="previewIcon" />
                        </l-map>
                        <div class="absolute top-2 right-2 bg-success/10 border border-success/30 text-success px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider z-[1000] rounded-sm">
                            SIGNAL_LOCKED
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-2">
                    <button v-for="p in presets" :key="p.id" type="button" @click="form.alias = p.label" 
                            class="flex flex-col items-center gap-1 p-2 border rounded-md transition-colors bg-neutral-50 dark:bg-neutral-800/20" 
                            :class="form.alias === p.label ? 'border-primary text-primary bg-primary/5' : 'border-border text-muted-foreground hover:border-neutral-400'">
                        <span class="material-symbols-rounded text-lg">{{ p.icon }}</span>
                        <span class="text-[9px] font-bold tracking-tight">{{ p.label }}</span>
                    </button>
                </div>
                
                <div class="space-y-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider">Etiqueta de Ubicación</label>
                        <input v-model="form.alias" type="text" class="admin-input py-1.5 px-3" required placeholder="Ej. Oficina Principal" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider">Instrucciones de Acceso</label>
                        <input v-model="form.details" type="text" class="admin-input py-1.5 px-3" placeholder="Piso, Nro de puerta, indicaciones adicionales..." />
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-border/40 mt-4">
                <button type="button" @click="step = 1" class="px-4 py-2 border border-border bg-card rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-xs font-bold uppercase tracking-wider transition-colors">
                    Atrás
                </button>
                <div class="flex-1 text-right">
                    <span class="text-[10px] font-mono text-muted-foreground uppercase font-bold bg-neutral-100 dark:bg-neutral-800 px-2 py-1 rounded-sm border border-border">
                        Nodo Listo
                    </span>
                </div>
            </div>
        </div>

    </div>
</template>