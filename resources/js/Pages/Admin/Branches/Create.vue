<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, nextTick } from 'vue';
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
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
            alert('El nombre de la sucursal es obligatorio');
            return;
        }
        setTimeout(() => mapRef.value?.leafletObject?.invalidateSize(), 100);
    }
    if (currentStep.value === 2) {
        if (form.coverage_polygon.length > 0 && form.coverage_polygon.length < 3) {
            alert("Debes marcar al menos 3 puntos para cerrar el área.");
            return;
        }
    }
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
    if (!form.name.trim()) {
        alert('El nombre de la sucursal es obligatorio');
        return;
    }
    
    form.post(route('admin.branches.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors) => {
            console.error('Fallo de Validación:', errors);
            const errorMessages = Object.values(errors).join('\n- ');
            alert("No se pudo crear por los siguientes errores:\n\n- " + errorMessages);
        }
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div class="animate-slide-up">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="avatar avatar-lg bg-gradient-to-br from-primary to-secondary text-primary-foreground shadow-lg">
                            <Store :size="24" />
                        </div>
                        <div>
                            <h1 class="text-3xl lg:text-4xl font-display font-black text-foreground tracking-tight">
                                Nueva Sucursal
                            </h1>
                            <p class="text-muted-foreground font-medium text-sm mt-1">
                                Configura un punto de venta y su zona de reparto
                            </p>
                        </div>
                    </div>
                </div>
                
                <Link :href="route('admin.branches.index')" class="btn btn-outline btn-md">
                    Cancelar
                </Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="mb-10">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="text-sm font-bold text-primary">{{ currentStep }}/{{ steps.length }}</span>
                        </div>
                        <span class="text-sm font-bold text-foreground">
                            {{ steps[currentStep - 1].title }}
                        </span>
                    </div>
                    <div class="text-sm text-muted-foreground font-medium">
                        Paso {{ currentStep }} de {{ steps.length }}
                    </div>
                </div>
                <div class="h-2 bg-muted rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-primary to-secondary rounded-full transition-all duration-base ease-smooth"
                         :style="{ width: `${progressPercentage}%` }">
                    </div>
                </div>
            </div>

            <div class="card min-h-[500px] flex flex-col shadow-xl border border-border/60">
                <div class="card-header border-b border-border/50 bg-muted/5">
                    <div class="flex items-center justify-center gap-8 py-2">
                        <div v-for="step in steps" :key="step.id" 
                             class="flex flex-col items-center gap-2 cursor-pointer group"
                             @click="currentStep >= step.id ? currentStep = step.id : null">
                            <div :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all',
                                currentStep === step.id 
                                    ? 'border-primary bg-primary text-primary-foreground scale-110 shadow-md' 
                                    : currentStep > step.id 
                                        ? 'border-success bg-success text-white' 
                                        : 'border-border bg-muted text-muted-foreground'
                            ]">
                                <CheckCircle v-if="currentStep > step.id" :size="20" />
                                <component v-else :is="step.icon" :size="18" />
                            </div>
                            <span :class="[
                                'text-xs font-bold uppercase tracking-wider transition-colors',
                                currentStep >= step.id ? 'text-primary' : 'text-muted-foreground'
                            ]">
                                {{ step.title }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-content flex-1 p-6 relative">
                    <div v-if="currentStep === 1" class="animate-in fade-in slide-in-from-left-4">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="space-y-6">
                                <div>
                                    <label class="form-label">Nombre Sucursal *</label>
                                    <input v-model="form.name" type="text" class="form-input font-bold" placeholder="Ej: Zona Sur" required />
                                    <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
                                </div>
                                
                                <div>
                                    <label class="form-label">Ciudad Base *</label>
                                    <select v-model="form.city" class="form-input">
                                        <option>La Paz</option>
                                        <option>El Alto</option>
                                        <option>Cochabamba</option>
                                        <option>Santa Cruz</option>
                                        <option>Tarija</option>
                                    </select>
                                </div>
                                
                                <div class="alert alert-info">
                                    <Navigation :size="16" />
                                    <span class="text-sm">Arrastra el pin en el mapa para ubicar la tienda exacta.</span>
                                </div>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <div class="rounded-xl overflow-hidden border border-border h-[400px] relative shadow-inner">
                                    <l-map ref="mapRef" v-model:zoom="zoom" :center="center" :use-global-leaflet="false" :options="{ zoomControl: false }" @ready="onMapReady" class="h-full w-full">
                                        <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" attribution='© OpenStreetMap' layer-type="base" name="OpenStreetMap" />
                                        <l-control-zoom position="bottomright" />
                                        <l-marker :lat-lng="[form.latitude, form.longitude]" draggable @dragend="onMarkerDrag">
                                            <l-tooltip :options="{ permanent: true, direction: 'top', offset: [0, -20] }">
                                                📍 {{ form.name || 'Aquí' }}
                                            </l-tooltip>
                                        </l-marker>
                                    </l-map>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 2" class="h-[450px] relative animate-in fade-in zoom-in duration-300">
                        <div class="absolute top-4 left-4 right-4 z-[500] glass p-4 rounded-xl flex flex-col md:flex-row justify-between items-center gap-4 shadow-lg border border-white/20">
                            <div>
                                <h2 class="font-bold text-foreground text-sm flex items-center gap-2">
                                    <MapPin :size="16" class="text-success" /> Dibuja la Zona de Reparto
                                </h2>
                                <p class="text-xs text-muted-foreground">
                                    Haz clic en el mapa para añadir vértices. (Puntos: {{ form.coverage_polygon.length }})
                                </p>
                            </div>
                            <div class="flex gap-2 w-full md:w-auto">
                                <button type="button" @click="undoLastPoint" :disabled="form.coverage_polygon.length === 0" class="btn btn-outline btn-sm flex-1 md:flex-none flex items-center justify-center gap-1">
                                    <RotateCcw :size="12" /> Deshacer
                                </button>
                                <button type="button" @click="clearPolygon" :disabled="form.coverage_polygon.length === 0" class="btn btn-error btn-sm flex-1 md:flex-none flex items-center justify-center gap-1">
                                    <Trash2 :size="12" /> Limpiar
                                </button>
                            </div>
                        </div>

                        <div class="h-full rounded-xl overflow-hidden border border-border">
                            <l-map ref="mapRef" v-model:zoom="zoom" :center="center" :use-global-leaflet="false" :options="{ zoomControl: false }" @click="onMapClick" @ready="onMapReady" class="h-full w-full">
                                <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" attribution='© OpenStreetMap' />
                                <l-control-zoom position="bottomright" />
                                <l-marker :lat-lng="[form.latitude, form.longitude]">
                                    <l-tooltip :options="{ permanent: true, direction: 'top' }">🏠 Tienda</l-tooltip>
                                </l-marker>
                                <l-circle-marker v-for="(point, index) in form.coverage_polygon" :key="index" :lat-lng="point" :radius="5" color="#000000" fill-color="#000000" :fill-opacity="1" :weight="2" />
                                <l-polygon v-if="form.coverage_polygon.length > 0" :lat-lngs="form.coverage_polygon" color="#374151" fill-color="#374151" :fill="true" :fill-opacity="0.5" :weight="3" />
                            </l-map>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 3" class="animate-in fade-in slide-in-from-right-4">
                        <div class="max-w-2xl mx-auto w-full space-y-6">
                            <div>
                                <label class="form-label">Teléfono de Contacto</label>
                                <input v-model="form.phone" type="text" class="form-input" placeholder="Ej: 70012345" />
                            </div>

                            <div>
                                <label class="form-label">Dirección Física Completa</label>
                                <textarea v-model="form.address" rows="3" class="form-input resize-none" placeholder="Dirección completa de la sucursal..."></textarea>
                            </div>

                            <div class="pt-2">
                                <BaseCheckbox v-model="form.is_active" class="w-full">
                                    <div class="flex items-center justify-between w-full p-4 border border-border rounded-xl hover:border-primary transition-all bg-card shadow-sm cursor-pointer group">
                                        <div class="flex items-center gap-3">
                                            <div :class="`w-3 h-3 rounded-full ${form.is_active ? 'bg-success animate-pulse' : 'bg-muted'}`"></div>
                                            <div>
                                                <span class="block text-sm font-bold text-foreground group-hover:text-primary transition-colors">
                                                    {{ form.is_active ? 'Sucursal Operativa' : 'Sucursal Inactiva' }}
                                                </span>
                                                <span class="text-xs text-muted-foreground">Visible para pedidos</span>
                                            </div>
                                        </div>
                                        <div :class="`w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all ${form.is_active ? 'border-primary bg-primary text-white' : 'border-muted-foreground'}`">
                                            <CheckCircle v-if="form.is_active" :size="14" />
                                        </div>
                                    </div>
                                </BaseCheckbox>
                            </div>
                            <div class="pt-2">
                                <BaseCheckbox v-model="form.is_default" class="w-full">
                                    <div class="flex items-center justify-between w-full p-4 border border-border rounded-xl hover:border-primary transition-all bg-card shadow-sm cursor-pointer group" :class="{ 'border-primary bg-primary/5': form.is_default }">
                                        <div class="flex items-center gap-3">
                                            <div :class="`w-3 h-3 rounded-full ${form.is_default ? 'bg-primary animate-pulse' : 'bg-muted'}`"></div>
                                            <div>
                                                <span class="block text-sm font-bold text-foreground group-hover:text-primary transition-colors">Establecer como Principal (Default)</span>
                                                <span class="text-xs text-muted-foreground">Fallback para usuarios fuera de cobertura</span>
                                            </div>
                                        </div>
                                        <div :class="`w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all ${form.is_default ? 'border-primary bg-primary text-white' : 'border-muted-foreground'}`">
                                            <CheckCircle v-if="form.is_default" :size="14" />
                                        </div>
                                    </div>
                                </BaseCheckbox>
                            </div>
                            <div class="card bg-muted/20 border-dashed border border-border/50">
                                <div class="card-content p-5">
                                    <h3 class="text-xs font-black text-muted-foreground uppercase tracking-wider mb-4 flex items-center gap-2">
                                        <Info :size="14"/> Resumen
                                    </h3>
                                    <ul class="space-y-3 text-sm">
                                        <li class="flex justify-between border-b border-border/50 pb-2">
                                            <span class="text-muted-foreground">Nombre:</span>
                                            <span class="font-bold text-foreground">{{ form.name || 'No definido' }}</span>
                                        </li>
                                        <li class="flex justify-between border-b border-border/50 pb-2">
                                            <span class="text-muted-foreground">Ubicación:</span>
                                            <span class="font-mono text-xs bg-background px-2 py-1 rounded border border-border">
                                                {{ form.latitude.toFixed(4) }}, {{ form.longitude.toFixed(4) }}
                                            </span>
                                        </li>
                                        <li class="flex justify-between items-center pt-1">
                                            <span class="text-muted-foreground">Cobertura:</span>
                                            <span class="font-bold flex items-center gap-1" :class="form.coverage_polygon.length > 0 ? 'text-success' : 'text-warning'">
                                                <Layers :size="14"/>
                                                {{ form.coverage_polygon.length > 0 ? 'Definida' : 'Sin cobertura' }}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 4" class="p-6 md:p-8 animate-in fade-in slide-in-from-right-4">
                        <div class="max-w-3xl mx-auto w-full space-y-6">
                            
                            <div class="bg-primary/5 p-4 rounded-xl border border-primary/20 flex gap-3 mb-6">
                                <Calculator :size="24" class="text-primary shrink-0"/>
                                <div>
                                    <h3 class="font-bold text-sm text-foreground">Configuración Logística y Tarifas</h3>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        Estos parámetros controlan el algoritmo de precios dinámicos para esta sucursal.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label text-xs">Tarifa Base de Envío (Bs)</label>
                                    <input v-model="form.delivery_base_fee" type="number" step="0.5" min="0" class="form-input font-mono bg-background" />
                                </div>
                                <div>
                                    <label class="form-label text-xs">Costo por Kilómetro (Bs)</label>
                                    <input v-model="form.delivery_price_per_km" type="number" step="0.1" min="0" class="form-input font-mono bg-background" />
                                </div>
                                <div>
                                    <label class="form-label text-xs text-warning flex items-center gap-1">Multiplicador de Demanda</label>
                                    <input v-model="form.surge_multiplier" type="number" step="0.1" min="1.0" class="form-input font-mono font-bold text-warning border-warning/30 focus:border-warning" />
                                    <span class="text-[9px] text-muted-foreground mt-1 block">Normal = 1.0 | Lluvia/Saturación = 1.5+</span>
                                </div>
                                <div>
                                    <label class="form-label text-xs">Umbral Ticket Bajo (Bs)</label>
                                    <input v-model="form.min_order_amount" type="number" step="1" min="0" class="form-input font-mono bg-background" />
                                    <span class="text-[9px] text-muted-foreground mt-1 block">Subtotal mínimo sin penalización.</span>
                                </div>
                                <div>
                                    <label class="form-label text-xs text-destructive">Multa Ticket Bajo (Bs)</label>
                                    <input v-model="form.small_order_fee" type="number" step="0.5" min="0" class="form-input font-mono text-destructive border-destructive/30 focus:border-destructive" />
                                    <span class="text-[9px] text-muted-foreground mt-1 block">Se cobra si no superan el umbral.</span>
                                </div>
                                <div>
                                    <label class="form-label text-xs text-primary">Tarifa Servicio Plataforma (%)</label>
                                    <input v-model="form.base_service_fee_percentage" type="number" step="0.1" min="0" max="100" class="form-input font-mono text-primary border-primary/30 focus:border-primary" />
                                    <span class="text-[9px] text-muted-foreground mt-1 block">Porcentaje sobre el subtotal de productos.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer border-t border-border/50 p-4 bg-muted/5">
                    <div class="flex justify-between items-center">
                        <button type="button" @click="prevStep" :disabled="currentStep === 1" class="btn btn-ghost hover:bg-background gap-2 text-muted-foreground transition-all" :class="{ 'opacity-0 pointer-events-none': currentStep === 1 }">
                            <ArrowLeft :size="18" />
                            <span>Atrás</span>
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="btn btn-primary btn-md shadow-lg shadow-primary/20 flex items-center gap-2 group px-6">
                            <span>Siguiente</span>
                            <ArrowRight :size="16" class="transition-transform duration-fast group-hover:translate-x-1" />
                        </button>
                        
                        <button v-else @click="submit" :disabled="form.processing" class="btn btn-primary btn-md shadow-lg shadow-primary/20 flex items-center gap-2 px-8">
                            <Save :size="18" />
                            <span>{{ form.processing ? 'Creando...' : 'Crear Sucursal' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>