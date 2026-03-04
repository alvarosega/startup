<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, nextTick } from 'vue';
import { 
    Store, MapPin, Phone, Navigation, Save, 
    ArrowLeft, ArrowRight, Trash2, RotateCcw, CheckCircle, 
    Info, Layers, Calculator
} from 'lucide-vue-next';

import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPolygon, LTooltip, LCircleMarker, LControlZoom } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';

const currentStep = ref(1);

const steps = [
    { id: 1, title: 'Datos Básicos', icon: Store },
    { id: 2, title: 'Geolocalización', icon: MapPin },
    { id: 3, title: 'Estado', icon: Phone },
    { id: 4, title: 'Logística', icon: Calculator },
];

const form = useForm({
    name: '',
    phone: '',
    city: 'La Paz',
    address: '',
    latitude: -16.5000,
    longitude: -68.1500,
    coverage_polygon: [],
    is_active: true,
    is_default: false,
    delivery_base_fee: 0.00,
    delivery_price_per_km: 0.00,
    surge_multiplier: 1.00,
    min_order_amount: 0.00,
    small_order_fee: 0.00,
    base_service_fee_percentage: 0.00
});

const zoom = ref(13);
const center = ref([-16.5000, -68.1500]);
const mapRef = ref(null);

// --- LEAFLET SETUP ---
onMounted(() => {
    if (typeof L !== 'undefined') {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });
    }
});

const onMapReady = async (mapInstance) => {
    await nextTick();
    if (mapInstance && mapInstance.invalidateSize) {
        mapInstance.invalidateSize();
    }
};

// --- INTERACCIÓN MAPA ---
const onMarkerDrag = (event) => {
    const { lat, lng } = event.target.getLatLng();
    form.latitude = lat;
    form.longitude = lng;
};

const onMapClick = (event) => {
    if (currentStep.value === 2 && event.latlng) {
        form.coverage_polygon.push([event.latlng.lat, event.latlng.lng]);
    }
};

const undoLastPoint = () => form.coverage_polygon.pop();
const clearPolygon = () => form.coverage_polygon = [];

// --- NAVEGACIÓN WIZARD ---
const nextStep = () => {
    if (currentStep.value === 1) {
        if (!form.name.trim()) {
            alert('SYS_ERR: El identificador (Nombre) de la base es obligatorio.');
            return;
        }
        setTimeout(() => mapRef.value?.leafletObject?.invalidateSize(), 100);
    }
    if (currentStep.value === 2) {
        if (form.coverage_polygon.length > 0 && form.coverage_polygon.length < 3) {
            alert("SYS_ERR: Triangulación incompleta. Requiere al menos 3 vectores.");
            return;
        }
    }
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};
const jumpToStepWithError = (errors) => {
    const fieldMapping = {
        1: ['name', 'city'],
        2: ['latitude', 'longitude', 'coverage_polygon'],
        3: ['phone', 'address'],
        4: ['delivery_base_fee', 'delivery_price_per_km', 'surge_multiplier', 'min_order_amount', 'small_order_fee', 'base_service_fee_percentage']
    };

    for (let stepId = 1; stepId <= 4; stepId++) {
        if (fieldMapping[stepId].some(field => errors[field])) {
            currentStep.value = stepId;
            break;
        }
    }
};
const submit = () => {
    if (!form.name.trim()) {
        alert('SYS_ERR: El identificador de la base es obligatorio.');
        currentStep.value = 1;
        return;
    }
    
    form.post(route('admin.branches.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors) => {
            console.error('Fallo de Validación:', errors);
            jumpToStepWithError(errors);
        }
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <AdminLayout>
        
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2 pb-4 border-b border-border/50">
                <div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-background border border-primary flex items-center justify-center shadow-[inset_0_0_15px_hsl(var(--primary)/0.2)]">
                            <Store :size="24" class="icon-glow text-primary" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-display font-black text-primary uppercase tracking-widest glitch-text drop-shadow-[0_0_8px_hsl(var(--primary)/0.6)] leading-none">
                                Inicializar Base
                            </h1>
                            <p class="text-xs text-muted-foreground font-mono font-bold tracking-widest uppercase mt-1">
                                Configuración Táctica y Zona de Alcance
                            </p>
                        </div>
                    </div>
                </div>
                
                <Link :href="route('admin.branches.index')" class="btn btn-outline border-border hover:border-destructive hover:text-destructive hover:shadow-neon-destructive">
                    Abortar
                </Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto py-6">
            
            <div class="mb-10">
                <div class="flex justify-between items-end mb-2">
                    <div class="flex items-center gap-3">
                        <div class="px-2 py-1 bg-primary/10 border border-primary/50 text-primary font-mono font-black text-sm shadow-[inset_0_0_8px_hsl(var(--primary)/0.3)]">
                            SEQ 0{{ currentStep }}
                        </div>
                        <span class="text-sm font-display font-black uppercase tracking-widest text-foreground glitch-text">
                            {{ steps[currentStep - 1].title }}
                        </span>
                    </div>
                    <div class="text-[10px] font-mono text-muted-foreground font-bold tracking-widest uppercase">
                        Proceso {{ currentStep }}/{{ steps.length }}
                    </div>
                </div>
                
                <div class="h-[2px] bg-border w-full relative">
                    <div class="absolute top-0 left-0 h-full bg-primary shadow-neon-primary transition-all duration-500 ease-out"
                         :style="{ width: `${progressPercentage}%` }">
                    </div>
                </div>
            </div>

            <div class="bg-background border border-border relative shadow-[0_0_30px_rgba(0,0,0,0.5)]">
                
                <div class="flex items-center justify-between md:justify-start border-b border-border bg-background">
                    <div v-for="step in steps" :key="step.id" 
                         class="flex-1 md:flex-none flex items-center justify-center p-3 border-r border-border cursor-pointer transition-colors duration-300 hover:bg-primary/5"
                         :class="{ 'bg-primary/10 border-b-2 border-b-primary shadow-[inset_0_-5px_15px_hsl(var(--primary)/0.1)]': currentStep === step.id }"
                         @click="currentStep >= step.id ? currentStep = step.id : null">
                        
                        <div class="flex items-center gap-2">
                            <CheckCircle v-if="currentStep > step.id" :size="16" class="text-success" />
                            <span v-else class="font-mono font-bold text-[10px]" :class="currentStep === step.id ? 'text-primary' : 'text-muted-foreground'">
                                [ 0{{ step.id }} ]
                            </span>
                            <span class="hidden md:inline-block text-[10px] font-display font-black uppercase tracking-widest"
                                  :class="currentStep >= step.id ? 'text-foreground glitch-text' : 'text-muted-foreground'">
                                {{ step.title }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8 bg-scanlines min-h-[450px] relative">
                    
                    <div v-if="currentStep === 1" class="animate-in fade-in slide-in-from-left-4">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-2">Identificador Operativo *</label>
                                    <input v-model="form.name" type="text" class="w-full bg-background border border-border px-4 py-3 text-foreground font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" placeholder="Ej: SECTOR SUR" required />
                                    <p v-if="form.errors.name" class="text-[10px] text-destructive font-mono mt-1 uppercase">{{ form.errors.name }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-2">Nodo Principal (Ciudad) *</label>
                                    <select v-model="form.city" class="w-full bg-background border border-border px-4 py-3 text-foreground font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                                        <option>La Paz</option>
                                        <option>El Alto</option>
                                        <option>Cochabamba</option>
                                        <option>Santa Cruz</option>
                                        <option>Tarija</option>
                                    </select>
                                </div>
                                
                                <div class="p-4 border border-primary/50 bg-primary/5 flex items-start gap-3 shadow-[inset_0_0_10px_hsl(var(--primary)/0.1)]">
                                    <Navigation :size="16" class="text-primary mt-0.5 shrink-0 icon-glow" />
                                    <span class="text-xs font-mono text-foreground uppercase tracking-wider leading-relaxed">Traslada el vector de posición en el mapa para anclar las coordenadas físicas de la base.</span>
                                </div>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <div class="border border-primary/50 h-[400px] relative shadow-[0_0_20px_hsl(var(--primary)/0.1)] bg-background">
                                    <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary z-10 pointer-events-none"></div>
                                    <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary z-10 pointer-events-none"></div>
                                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary z-10 pointer-events-none"></div>
                                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary z-10 pointer-events-none"></div>
                                    
                                    <l-map ref="mapRef" v-model:zoom="zoom" :center="center" :use-global-leaflet="false" :options="{ zoomControl: false }" @ready="onMapReady" class="h-full w-full z-0">
                                        <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" attribution='© OpenStreetMap' layer-type="base" name="OpenStreetMap" />
                                        <l-control-zoom position="bottomright" />
                                        <l-marker :lat-lng="[form.latitude, form.longitude]" draggable @dragend="onMarkerDrag">
                                            <l-tooltip :options="{ permanent: true, direction: 'top', offset: [0, -20] }">
                                                <span class="font-mono font-bold text-xs uppercase tracking-widest text-primary">{{ form.name || 'TARGET_LOC' }}</span>
                                            </l-tooltip>
                                        </l-marker>
                                    </l-map>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 2" class="h-[450px] relative animate-in fade-in zoom-in duration-300">
                        
                        <div class="absolute top-0 left-0 w-full z-[500] bg-background border-b border-primary flex flex-col md:flex-row justify-between items-center p-3 shadow-neon-primary">
                            <div>
                                <h2 class="font-display font-black text-primary text-sm uppercase tracking-widest flex items-center gap-2 glitch-text">
                                    <MapPin :size="16" class="icon-glow" /> Perímetro Operativo
                                </h2>
                                <p class="text-[10px] font-mono text-muted-foreground uppercase tracking-widest mt-1">
                                    Input Táctico para Vértices. (Vectores Registrados: <span class="text-primary">{{ form.coverage_polygon.length }}</span>)
                                </p>
                            </div>
                            <div class="flex gap-2 w-full md:w-auto mt-3 md:mt-0">
                                <button type="button" @click="undoLastPoint" :disabled="form.coverage_polygon.length === 0" class="btn btn-outline flex-1 md:flex-none py-2 px-4 text-[10px]">
                                    <RotateCcw :size="12" class="icon-glow" /> Revertir Vector
                                </button>
                                <button type="button" @click="clearPolygon" :disabled="form.coverage_polygon.length === 0" class="btn btn-destructive flex-1 md:flex-none py-2 px-4 text-[10px]">
                                    <Trash2 :size="12" /> Purgar Área
                                </button>
                            </div>
                        </div>

                        <div class="h-full border border-border bg-background pt-[70px] md:pt-[60px]">
                            <l-map ref="mapRef" v-model:zoom="zoom" :center="center" :use-global-leaflet="false" :options="{ zoomControl: false }" @click="onMapClick" @ready="onMapReady" class="h-full w-full z-0">
                                <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" attribution='© OpenStreetMap' />
                                <l-control-zoom position="bottomright" />
                                <l-marker :lat-lng="[form.latitude, form.longitude]">
                                    <l-tooltip :options="{ permanent: true, direction: 'top' }">
                                        <span class="font-mono font-bold text-[10px] text-primary">BASE_ALPHA</span>
                                    </l-tooltip>
                                </l-marker>
                                <l-circle-marker v-for="(point, index) in form.coverage_polygon" :key="index" :lat-lng="point" :radius="4" color="hsl(var(--primary))" fill-color="hsl(var(--background))" :fill-opacity="1" :weight="2" />
                                <l-polygon v-if="form.coverage_polygon.length > 0" :lat-lngs="form.coverage_polygon" color="hsl(var(--primary))" fill-color="hsl(var(--primary))" :fill="true" :fill-opacity="0.2" :weight="2" />
                            </l-map>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 3" class="animate-in fade-in slide-in-from-right-4">
                        <div class="max-w-2xl mx-auto w-full space-y-6">
                            
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-2">Canal de Comunicación (Teléfono)</label>
                                <input v-model="form.phone" type="text" class="w-full bg-background border border-border px-4 py-3 text-foreground font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" placeholder="Ej: 70012345" />
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-2">Coordenadas Físicas (Dirección)</label>
                                <textarea v-model="form.address" rows="3" class="w-full bg-background border border-border px-4 py-3 text-foreground font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all resize-none" placeholder="Dirección completa del nodo..."></textarea>
                            </div>

                            <div class="pt-4 space-y-4">
                                
                                <div @click="form.is_active = !form.is_active" 
                                    class="flex items-center justify-between w-full p-4 border transition-all cursor-pointer select-none bg-background"
                                    :class="form.is_active ? 'border-primary shadow-[inset_0_0_20px_hsl(var(--primary)/0.1)] shadow-neon-primary' : 'border-border hover:border-primary/50'">
                                    <div class="flex flex-col">
                                        <span class="font-mono font-bold text-sm tracking-widest uppercase transition-colors"
                                            :class="form.is_active ? 'text-primary glitch-text' : 'text-muted-foreground'">
                                            {{ form.is_active ? '[ SYS_ONLINE ]' : '[ SYS_OFFLINE ]' }}
                                        </span>
                                        <span class="text-[10px] text-muted-foreground uppercase tracking-widest mt-1">Estado de transmisión de la base</span>
                                    </div>
                                    <div class="w-12 h-6 border flex items-center p-1 transition-all"
                                        :class="form.is_active ? 'border-primary bg-primary/20 justify-end' : 'border-border bg-muted justify-start'">
                                        <div class="w-4 h-4 transition-all"
                                            :class="form.is_active ? 'bg-primary shadow-[0_0_10px_hsl(var(--primary))]' : 'bg-muted-foreground'"></div>
                                    </div>
                                </div>

                                <div @click="form.is_default = !form.is_default" 
                                    class="flex items-center justify-between w-full p-4 border transition-all cursor-pointer select-none bg-background mt-4"
                                    :class="form.is_default ? 'border-secondary shadow-[inset_0_0_20px_hsl(var(--secondary)/0.1)] shadow-neon-secondary' : 'border-border hover:border-secondary/50'">
                                    <div class="flex flex-col">
                                        <span class="font-mono font-bold text-sm tracking-widest uppercase transition-colors"
                                            :class="form.is_default ? 'text-secondary glitch-text' : 'text-muted-foreground'">
                                            {{ form.is_default ? '[ NODE_MASTER ]' : '[ NODE_SLAVE ]' }}
                                        </span>
                                        <span class="text-[10px] text-muted-foreground uppercase tracking-widest mt-1">Protocolo de asignación por defecto</span>
                                    </div>
                                    <div class="w-12 h-6 border flex items-center p-1 transition-all"
                                        :class="form.is_default ? 'border-secondary bg-secondary/20 justify-end' : 'border-border bg-muted justify-start'">
                                        <div class="w-4 h-4 transition-all"
                                            :class="form.is_default ? 'bg-secondary shadow-[0_0_10px_hsl(var(--secondary))]' : 'bg-muted-foreground'"></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 4" class="animate-in fade-in slide-in-from-right-4">
                        <div class="max-w-3xl mx-auto w-full space-y-6">
                            
                            <div class="bg-background border border-primary p-4 flex gap-4 shadow-neon-primary">
                                <Calculator :size="24" class="text-primary shrink-0 icon-glow mt-1"/>
                                <div>
                                    <h3 class="font-display font-black text-sm text-primary uppercase tracking-widest glitch-text">Algoritmo de Tarifas</h3>
                                    <p class="text-[10px] font-mono text-muted-foreground mt-1 uppercase tracking-widest">
                                        Calibración de la matriz de precios dinámicos.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-2">Tarifa Base (Bs)</label>
                                    <input v-model="form.delivery_base_fee" type="number" step="0.5" min="0" class="w-full bg-background border border-border px-4 py-3 text-foreground font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-2">Costo x KM (Bs)</label>
                                    <input v-model="form.delivery_price_per_km" type="number" step="0.1" min="0" class="w-full bg-background border border-border px-4 py-3 text-foreground font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-warning mb-2">Multiplicador Demanda</label>
                                    <input v-model="form.surge_multiplier" type="number" step="0.1" min="1.0" class="w-full bg-background border border-warning/50 text-warning px-4 py-3 font-mono text-sm focus:border-warning focus:shadow-[0_0_15px_hsl(var(--warning))] outline-none transition-all" />
                                    <span class="text-[9px] font-mono text-muted-foreground mt-1 block uppercase">Normal = 1.0 | Saturación = 1.5+</span>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-2">Umbral Ticket Bajo (Bs)</label>
                                    <input v-model="form.min_order_amount" type="number" step="1" min="0" class="w-full bg-background border border-border px-4 py-3 text-foreground font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                                    <span class="text-[9px] font-mono text-muted-foreground mt-1 block uppercase">Subtotal mínimo evadiendo penalización.</span>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-destructive mb-2">Multa Ticket Bajo (Bs)</label>
                                    <input v-model="form.small_order_fee" type="number" step="0.5" min="0" class="w-full bg-background border border-destructive/50 text-destructive px-4 py-3 font-mono text-sm focus:border-destructive focus:shadow-neon-destructive outline-none transition-all" />
                                    <span class="text-[9px] font-mono text-muted-foreground mt-1 block uppercase">Penalización al usuario.</span>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-secondary mb-2">Fee Plataforma (%)</label>
                                    <input v-model="form.base_service_fee_percentage" type="number" step="0.1" min="0" max="100" class="w-full bg-background border border-secondary/50 text-secondary px-4 py-3 font-mono text-sm focus:border-secondary focus:shadow-neon-secondary outline-none transition-all" />
                                    <span class="text-[9px] font-mono text-muted-foreground mt-1 block uppercase">Retención de costos de operación.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-border bg-background p-4 flex justify-between items-center relative z-10">
                    <button type="button" @click="prevStep" :disabled="currentStep === 1" class="btn btn-outline" :class="{ 'opacity-0 pointer-events-none': currentStep === 1 }">
                        <ArrowLeft :size="16" class="mr-2" /> REBOBINAR
                    </button>

                    <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="btn btn-primary group px-8">
                        AVANZAR <ArrowRight :size="16" class="ml-2 transition-transform duration-150 group-hover:translate-x-2" />
                    </button>
                    
                    <button v-else @click="submit" :disabled="form.processing" class="btn btn-primary px-8 border-destructive hover:border-destructive hover:shadow-neon-destructive bg-destructive/10 text-destructive hover:text-destructive-foreground hover:bg-destructive">
                        <Save :size="16" class="mr-2" />
                        {{ form.processing ? 'EJECUTANDO...' : 'INICIALIZAR SISTEMA' }}
                    </button>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
/* Ocultar el checkbox nativo dentro del componente BaseCheckbox si aplica */
.hidden-checkbox-wrapper :deep(input[type="checkbox"]) {
    display: none;
}
</style>