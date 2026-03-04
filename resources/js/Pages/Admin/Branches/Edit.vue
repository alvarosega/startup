<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, nextTick } from 'vue';
import { 
    Store, MapPin, Phone, Navigation, Save, 
    ArrowLeft, ArrowRight, Trash2, RotateCcw, CheckCircle, 
    Layers, Info, AlertTriangle, Calculator, Target 
} from 'lucide-vue-next';

import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPolygon, LTooltip, LCircleMarker, LControlZoom } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';

const props = defineProps({
    branch: Object
});

const currentStep = ref(1);
const mapRef = ref(null);
const zoom = ref(14);

// Configuración de pasos y mapeo de errores para el salto automático
const steps = [
    { id: 1, title: 'Datos Básicos', fields: ['name', 'city'] },
    { id: 2, title: 'Geolocalización', fields: ['latitude', 'longitude', 'coverage_polygon'] },
    { id: 3, title: 'Estado', fields: ['phone', 'address', 'is_active', 'is_default'] },
    { id: 4, title: 'Logística', fields: ['delivery_base_fee', 'delivery_price_per_km', 'surge_multiplier', 'min_order_amount', 'small_order_fee', 'base_service_fee_percentage'] },
];

const form = useForm({
    _method: 'PUT',
    name: props.branch.name || '',
    phone: props.branch.phone || '',
    city: props.branch.city || 'La Paz',
    address: props.branch.address || '',
    latitude: parseFloat(props.branch.latitude) || -16.5000,
    longitude: parseFloat(props.branch.longitude) || -68.1500,
    coverage_polygon: Array.isArray(props.branch.coverage_polygon) ? props.branch.coverage_polygon : [],
    is_active: Boolean(props.branch.is_active),
    is_default: Boolean(props.branch.is_default),
    delivery_base_fee: parseFloat(props.branch.delivery_base_fee) || 0.00,
    delivery_price_per_km: parseFloat(props.branch.delivery_price_per_km) || 0.00,
    surge_multiplier: parseFloat(props.branch.surge_multiplier) || 1.00,
    min_order_amount: parseFloat(props.branch.min_order_amount) || 0.00,
    small_order_fee: parseFloat(props.branch.small_order_fee) || 0.00,
    base_service_fee_percentage: parseFloat(props.branch.base_service_fee_percentage) || 0.00
});

onMounted(() => {
    if (typeof L !== 'undefined') {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
            iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
            shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
        });
    }
});

const onMapReady = async (mapInstance) => {
    await nextTick();
    if (mapInstance && mapInstance.invalidateSize) mapInstance.invalidateSize();
};

// --- LÓGICA DE MAPA ---
const syncCenter = (event) => {
    const { lat, lng } = event.target.getLatLng();
    form.latitude = lat;
    form.longitude = lng;
};

const onMapClick = (event) => {
    if (currentStep.value === 2 && event.latlng) {
        // Uso de spread operator para forzar reactividad en el polígono
        form.coverage_polygon = [...form.coverage_polygon, [event.latlng.lat, event.latlng.lng]];
    }
};

const undoLastPoint = () => form.coverage_polygon = form.coverage_polygon.slice(0, -1);
const clearPolygon = () => form.coverage_polygon = [];

// --- FLUJO Y VALIDACIÓN ---
const jumpToStepWithError = (errors) => {
    for (const step of steps) {
        if (step.fields.some(field => errors[field])) {
            currentStep.value = step.id;
            break;
        }
    }
};

const nextStep = () => {
    if (currentStep.value === 1 && !form.name.trim()) return alert('SYS_ERR: Nombre obligatorio.');
    if (currentStep.value === 2 && form.coverage_polygon.length > 0 && form.coverage_polygon.length < 3) {
        return alert('SYS_ERR: Perímetro incompleto (mínimo 3 puntos).');
    }
    currentStep.value++;
    if (currentStep.value <= 2) setTimeout(() => mapRef.value?.leafletObject?.invalidateSize(), 150);
};

const submit = () => {
    form.put(route('admin.branches.update', props.branch.id), {
        preserveScroll: true,
        onError: (errors) => jumpToStepWithError(errors)
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between pt-1 mb-6 border-b border-border/50 pb-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.branches.index')" class="p-2 border border-border hover:border-primary text-muted-foreground transition-all">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-display font-black text-primary tracking-tight uppercase glitch-text">RECALIBRAR_NODO</h1>
                        <p class="text-[10px] text-muted-foreground font-mono font-bold uppercase">{{ props.branch.name }} // V_2.0</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto pb-32">
            <div class="mb-8 px-4 font-mono">
                <div class="flex justify-between items-end mb-2 text-[10px] font-black uppercase">
                    <span class="text-primary">FASE_0{{ currentStep }}</span>
                    <span class="text-muted-foreground">{{ steps[currentStep-1].title }}</span>
                </div>
                <div class="h-[2px] bg-muted relative">
                    <div class="absolute h-full bg-primary shadow-neon-primary transition-all duration-500" :style="{ width: `${progressPercentage}%` }"></div>
                </div>
            </div>

            <div class="bg-background border border-border flex flex-col min-h-[580px] shadow-2xl relative">
                
                <div class="flex border-b border-border bg-background z-20">
                    <button v-for="step in steps" :key="step.id" 
                            @click="currentStep = step.id"
                            class="flex-1 p-3 border-r border-border transition-all text-[9px] font-black uppercase tracking-widest"
                            :class="currentStep === step.id ? 'bg-primary/10 text-primary border-b-2 border-b-primary' : 'text-muted-foreground hover:bg-primary/5'">
                        {{ step.title }}
                    </button>
                </div>

                <div class="flex-1 p-6 md:p-8 bg-scanlines relative">
                    
                    <div v-if="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-left-4">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] text-primary font-black uppercase tracking-widest mb-2 block">Identificador Base</label>
                                    <input v-model="form.name" type="text" class="w-full bg-background border border-border px-4 py-3 font-mono text-sm focus:border-primary outline-none" />
                                    <p v-if="form.errors.name" class="text-[10px] text-destructive mt-1 uppercase">{{ form.errors.name }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] text-primary font-black uppercase tracking-widest mb-2 block">Ciudad de Operación</label>
                                    <select v-model="form.city" class="w-full bg-background border border-border px-4 py-3 font-mono text-sm focus:border-primary outline-none">
                                        <option>La Paz</option><option>El Alto</option><option>Cochabamba</option><option>Santa Cruz</option>
                                    </select>
                                </div>
                                <div class="p-4 border border-primary/20 bg-primary/5 font-mono text-[9px] text-primary uppercase leading-relaxed shadow-[inset_0_0_10px_rgba(0,240,255,0.05)]">
                                    [ INFO ]: Arrastre el marcador central para geolocalizar el punto de despacho de esta sucursal.
                                </div>
                            </div>
                            <div class="lg:col-span-2 h-[350px] border border-border relative group">
                                <l-map ref="mapRef" :zoom="15" :center="[form.latitude, form.longitude]" :use-global-leaflet="false" @ready="onMapReady" class="h-full w-full grayscale group-hover:grayscale-0 transition-all duration-500">
                                    <l-tile-layer url="https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png" />
                                    <l-marker :lat-lng="[form.latitude, form.longitude]" draggable @dragend="syncCenter">
                                        <l-tooltip :options="{ permanent: true, direction: 'top' }">PUNTO_DE_BASE</l-tooltip>
                                    </l-marker>
                                </l-map>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 2" class="h-[480px] relative animate-in fade-in zoom-in duration-300">
                        <div class="absolute top-4 left-4 right-4 z-[500] flex justify-between items-center bg-background/90 border border-primary p-3 shadow-neon-primary font-mono">
                            <div>
                                <span class="text-[10px] text-primary font-black uppercase tracking-tighter">Perímetro de Entrega</span>
                                <p class="text-[9px] text-muted-foreground uppercase">Haga clic para añadir vértices</p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="undoLastPoint" class="px-3 py-1 border border-border text-[9px] font-bold hover:text-primary transition-colors">REVERTIR</button>
                                <button type="button" @click="clearPolygon" class="px-3 py-1 border border-destructive text-destructive text-[9px] font-bold hover:bg-destructive hover:text-white transition-all">PURGAR</button>
                            </div>
                        </div>
                        <l-map ref="mapRef" v-model:zoom="zoom" :center="[form.latitude, form.longitude]" :use-global-leaflet="false" class="h-full w-full grayscale" @click="onMapClick" @ready="onMapReady">
                            <l-tile-layer url="https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png" />
                            <l-marker :lat-lng="[form.latitude, form.longitude]">
                                <l-tooltip>ORIGEN_DE_DATOS</l-tooltip>
                            </l-marker>
                            <l-polygon v-if="form.coverage_polygon.length > 0" :lat-lngs="form.coverage_polygon" color="#00f0ff" :fill="true" :fill-opacity="0.3" :weight="2" />
                            <l-circle-marker v-for="(point, i) in form.coverage_polygon" :key="i" :lat-lng="point" :radius="4" color="#00f0ff" fill-color="#000" :fill-opacity="1" :weight="2" />
                        </l-map>
                    </div>

                    <div v-else-if="currentStep === 3" class="space-y-6 animate-in fade-in slide-in-from-right-4 font-mono">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] text-primary font-black uppercase tracking-widest mb-2 block">Canal Telefónico</label>
                                <input v-model="form.phone" type="text" class="w-full bg-background border border-border px-4 py-3 text-sm focus:border-primary outline-none" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-[10px] text-primary font-black uppercase tracking-widest mb-2 block">Referencia de Dirección</label>
                                <textarea v-model="form.address" rows="2" class="w-full bg-background border border-border px-4 py-3 text-sm focus:border-primary outline-none resize-none"></textarea>
                            </div>
                        </div>

                        <div class="pt-6 space-y-4 border-t border-border/40">
                            <div @click="form.is_active = !form.is_active" 
                                 class="flex items-center justify-between p-4 border transition-all cursor-pointer select-none bg-background"
                                 :class="form.is_active ? 'border-primary shadow-neon-primary' : 'border-border opacity-50'">
                                <div class="flex flex-col">
                                    <span class="font-bold text-xs uppercase" :class="form.is_active ? 'text-primary' : 'text-muted-foreground'">
                                        {{ form.is_active ? '[ SYS_ONLINE ]' : '[ SYS_OFFLINE ]' }}
                                    </span>
                                    <span class="text-[9px] text-muted-foreground uppercase mt-1">Transmisión operativa de inventario</span>
                                </div>
                                <div class="w-12 h-6 border border-border relative flex items-center p-1">
                                    <div class="w-4 h-4 transition-all duration-300" :class="form.is_active ? 'translate-x-6 bg-primary shadow-neon-primary' : 'translate-x-0 bg-muted-foreground'"></div>
                                </div>
                            </div>

                            <div @click="form.is_default = !form.is_default" 
                                 class="flex items-center justify-between p-4 border transition-all cursor-pointer select-none bg-background"
                                 :class="form.is_default ? 'border-secondary shadow-neon-secondary' : 'border-border opacity-50'">
                                <div class="flex flex-col">
                                    <span class="font-bold text-xs uppercase" :class="form.is_default ? 'text-secondary' : 'text-muted-foreground'">
                                        {{ form.is_default ? '[ NODE_MASTER ]' : '[ NODE_SLAVE ]' }}
                                    </span>
                                    <span class="text-[9px] text-muted-foreground uppercase mt-1">Sucursal de reserva para tráfico global</span>
                                </div>
                                <div class="w-12 h-6 border border-border relative flex items-center p-1">
                                    <div class="w-4 h-4 transition-all duration-300" :class="form.is_default ? 'translate-x-6 bg-secondary shadow-neon-secondary' : 'translate-x-0 bg-muted-foreground'"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 4" class="space-y-6 animate-in fade-in slide-in-from-right-4 font-mono">
                        <div class="bg-primary/5 p-4 border border-primary/20 flex gap-4">
                            <Calculator :size="24" class="text-primary icon-glow" />
                            <p class="text-[10px] text-foreground uppercase tracking-widest leading-relaxed">
                                Calibración de parámetros financieros. Estos valores afectan directamente el cálculo del Checkout en la zona de cobertura.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] text-primary font-black uppercase block mb-2">Base Delivery (Bs)</label>
                                <input v-model="form.delivery_base_fee" type="number" step="any" class="w-full bg-background border border-border px-4 py-3 text-sm focus:border-primary outline-none" />
                            </div>
                            <div>
                                <label class="text-[10px] text-warning font-black uppercase block mb-2">Surge Multiplier (x1.0)</label>
                                <input v-model="form.surge_multiplier" type="number" step="0.1" class="w-full bg-background border border-warning/30 text-warning px-4 py-3 text-sm focus:border-warning outline-none" />
                            </div>
                            <div>
                                <label class="text-[10px] text-primary font-black uppercase block mb-2">Fee Plataforma (%)</label>
                                <input v-model="form.base_service_fee_percentage" type="number" step="any" class="w-full bg-background border border-border px-4 py-3 text-sm focus:border-primary outline-none" />
                            </div>
                            <div>
                                <label class="text-[10px] text-destructive font-black uppercase block mb-2">Multa Orden Pequeña (Bs)</label>
                                <input v-model="form.small_order_fee" type="number" step="any" class="w-full bg-background border border-destructive/20 text-destructive px-4 py-3 text-sm focus:border-destructive outline-none" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-border bg-background flex justify-between font-mono z-20">
                    <button type="button" @click="currentStep--" :disabled="currentStep === 1" class="px-6 py-2 border border-border text-[10px] font-bold uppercase hover:bg-border transition-all disabled:opacity-20">
                        REBOBINAR
                    </button>
                    <button v-if="currentStep < 4" type="button" @click="nextStep" class="px-8 py-2 bg-primary text-background text-[10px] font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all">
                        AVANZAR
                    </button>
                    <button v-else @click="submit" :disabled="form.processing" class="px-10 py-2 border-2 border-primary text-primary hover:bg-primary hover:text-background transition-all text-[10px] font-black shadow-neon-primary uppercase">
                        {{ form.processing ? 'COMMIT_IN_PROGRESS...' : 'EJECUTAR_RECALIBRACIÓN' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style>
.leaflet-container { z-index: 1 !important; cursor: crosshair !important; }
.leaflet-tooltip { 
    background: #000 !important; 
    border: 1px solid #00f0ff !important; 
    color: #00f0ff !important; 
    font-family: 'JetBrains Mono', monospace !important; 
    font-size: 9px !important;
    text-transform: uppercase;
}
.leaflet-tooltip-top:before { border-top-color: #00f0ff !important; }
</style>