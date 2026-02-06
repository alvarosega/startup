<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, nextTick } from 'vue';
import { 
    Store, MapPin, Phone, Navigation, Save, 
    ArrowLeft, ArrowRight, Trash2, RotateCcw, CheckCircle, 
    Layers, Info, AlertTriangle 
} from 'lucide-vue-next';

// Importamos Leaflet y sus estilos
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPolygon, LTooltip, LCircleMarker, LControlZoom } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';

const props = defineProps({
    branch: Object
});

const currentStep = ref(1);

const steps = [
    { id: 1, title: 'Datos Básicos', icon: Store },
    { id: 2, title: 'Geolocalización', icon: MapPin },
    { id: 3, title: 'Detalles y Estado', icon: Phone },
];

// --- INICIALIZACIÓN DEL FORMULARIO ---
// Aseguramos tipos de datos correctos al cargar
const form = useForm({
    _method: 'PUT', // Importante para actualizaciones en Laravel/Inertia
    name: props.branch.name || '',
    phone: props.branch.phone || '',
    city: props.branch.city || 'La Paz',
    address: props.branch.address || '',
    latitude: parseFloat(props.branch.latitude) || -16.5000,
    longitude: parseFloat(props.branch.longitude) || -68.1500,
    // Asegurar que el polígono sea un array, incluso si viene null
    coverage_polygon: Array.isArray(props.branch.coverage_polygon) ? props.branch.coverage_polygon : [],
    is_active: Boolean(props.branch.is_active)
});

const zoom = ref(14);
// Centramos el mapa en la ubicación actual de la sucursal
const center = ref([form.latitude, form.longitude]);
const mapRef = ref(null);

// --- CONFIGURACIÓN LEAFLET (Fix Iconos) ---
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
    if (mapInstance && mapInstance.invalidateSize) {
        mapInstance.invalidateSize();
    }
};

// --- LÓGICA DEL MAPA (Edición) ---
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

// --- NAVEGACIÓN ---
const nextStep = () => {
    if (currentStep.value === 1) {
        if (!form.name.trim()) return alert('El nombre es obligatorio');
        setTimeout(() => mapRef.value?.leafletObject?.invalidateSize(), 100);
    }
    if (currentStep.value === 2) {
        // Validación opcional: advertir si no hay polígono
        if (form.coverage_polygon.length > 0 && form.coverage_polygon.length < 3) {
            return alert("El área debe tener al menos 3 puntos.");
        }
    }
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
    form.post(route('admin.branches.update', props.branch.id), {
        preserveScroll: true
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <AdminLayout>
        
        <template #header>
            <div class="flex items-center gap-3 pt-1 mb-6">
                <Link :href="route('admin.branches.index')" class="p-2 rounded-xl bg-card border border-border text-muted-foreground hover:text-foreground transition-colors">
                    <ArrowLeft :size="20" />
                </Link>
                <div>
                    <h1 class="text-2xl font-display font-black tracking-tight text-foreground leading-none">
                        Editar Sucursal
                    </h1>
                    <p class="text-[10px] text-muted-foreground font-medium mt-0.5 flex items-center gap-1">
                        Editando: <span class="text-primary font-bold">{{ props.branch.name }}</span>
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto pb-32 md:pb-12">
            
            <div class="mb-8 px-4">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-xs font-black text-primary uppercase tracking-widest">Paso {{ currentStep }}</span>
                    <span class="text-[10px] font-medium text-muted-foreground">{{ steps[currentStep-1].title }}</span>
                </div>
                <div class="h-1.5 bg-muted rounded-full overflow-hidden">
                    <div class="h-full bg-primary transition-all duration-500 ease-out rounded-full"
                         :style="{ width: `${progressPercentage}%` }"></div>
                </div>
            </div>

            <div class="bg-card rounded-3xl border border-border shadow-xl overflow-hidden flex flex-col min-h-[550px] relative">
                
                <div class="flex-1 relative">
                    
                    <div v-if="currentStep === 1" class="p-6 md:p-8 space-y-6 animate-in fade-in slide-in-from-left-4">
                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Nombre Comercial</label>
                                <input v-model="form.name" type="text" class="form-input text-lg font-bold" placeholder="Ej: Sucursal Central" />
                                <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Ciudad</label>
                                    <select v-model="form.city" class="form-input">
                                        <option>La Paz</option>
                                        <option>El Alto</option>
                                        <option>Cochabamba</option>
                                        <option>Santa Cruz</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Teléfono / Referencia</label>
                                    <input v-model="form.phone" type="text" class="form-input" placeholder="Ej: 77712345" />
                                </div>
                            </div>

                            <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 p-4 rounded-xl text-xs flex gap-3 items-start border border-blue-100 dark:border-blue-800">
                                <Info :size="18" class="shrink-0 mt-0.5"/>
                                <p>Estás editando la información base. Si cambias la ciudad, recuerda actualizar la ubicación en el mapa en el siguiente paso.</p>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 2" class="absolute inset-0 z-0 animate-in fade-in zoom-in duration-300">
                        
                        <div class="absolute top-4 left-4 right-4 z-[500] flex flex-col gap-2 pointer-events-none">
                            <div class="bg-background/90 backdrop-blur-md p-3 rounded-xl border border-border shadow-lg pointer-events-auto flex justify-between items-center">
                                <div>
                                    <h3 class="font-bold text-xs flex items-center gap-2">
                                        <MapPin :size="14" class="text-primary"/> Ubicación & Cobertura
                                    </h3>
                                    <p class="text-[10px] text-muted-foreground">Arrastra el pin y toca para dibujar zona.</p>
                                </div>
                                <div class="bg-primary/10 text-primary px-2 py-1 rounded text-[10px] font-bold">
                                    {{ form.coverage_polygon.length }} Puntos
                                </div>
                            </div>
                            
                            <div class="flex gap-2 pointer-events-auto">
                                <button type="button" @click="undoLastPoint" :disabled="!form.coverage_polygon.length"
                                        class="flex-1 btn btn-sm bg-background/90 backdrop-blur border border-border shadow-sm text-[10px] h-9">
                                    <RotateCcw :size="12" class="mr-1"/> Deshacer
                                </button>
                                <button type="button" @click="clearPolygon" :disabled="!form.coverage_polygon.length"
                                        class="flex-1 btn btn-sm bg-background/90 backdrop-blur border border-border shadow-sm text-destructive hover:bg-destructive/10 text-[10px] h-9">
                                    <Trash2 :size="12" class="mr-1"/> Borrar Zona
                                </button>
                            </div>
                        </div>

                        <l-map ref="mapRef" 
                               v-model:zoom="zoom" 
                               :center="center" 
                               :use-global-leaflet="false" 
                               :options="{ zoomControl: false }" 
                               @click="onMapClick" 
                               @ready="onMapReady" 
                               class="h-full w-full bg-muted">
                            
                            <l-tile-layer 
                                url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
                                attribution='&copy; CartoDB'
                            />
                            
                            <l-marker :lat-lng="[form.latitude, form.longitude]" draggable @dragend="onMarkerDrag">
                                <l-tooltip :options="{ permanent: true, direction: 'top', offset: [0, -35] }">
                                    <span class="font-bold text-xs">{{ form.name || 'Sucursal' }}</span>
                                </l-tooltip>
                            </l-marker>

                            <l-polygon 
                                v-if="form.coverage_polygon.length > 0" 
                                :lat-lngs="form.coverage_polygon" 
                                color="var(--primary)" 
                                :fill="true" 
                                :fill-opacity="0.2" 
                                :weight="2"
                            />
                            
                            <l-circle-marker 
                                v-for="(point, i) in form.coverage_polygon" :key="i" 
                                :lat-lng="point" :radius="4" color="white" fill-color="var(--primary)" :fill-opacity="1" :weight="2"
                            />
                        </l-map>
                    </div>

                    <div v-else class="p-6 md:p-8 space-y-6 animate-in fade-in slide-in-from-right-4">
                        
                        <div class="space-y-2">
                            <label class="form-label flex items-center gap-2">
                                <Navigation :size="16" class="text-primary"/> Dirección Física
                            </label>
                            <textarea v-model="form.address" rows="3" 
                                      class="form-input resize-none text-sm" 
                                      placeholder="Calle, número, zona y referencias..."></textarea>
                        </div>

                        <div class="pt-4">
                            <label class="flex items-center gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all hover:bg-muted/30 w-full"
                                   :class="form.is_active ? 'border-success/50 bg-success/5' : 'border-border bg-card'">
                                
                                <div class="relative flex items-center shrink-0">
                                    <input type="checkbox" v-model="form.is_active" class="peer sr-only">
                                    <div class="w-11 h-6 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success"></div>
                                </div>

                                <div>
                                    <span class="block text-sm font-bold text-foreground">
                                        {{ form.is_active ? 'Sucursal Operativa' : 'Sucursal Inactiva' }}
                                    </span>
                                    <span class="text-[10px] text-muted-foreground leading-tight">
                                        Determina si la tienda aparece visible para recibir pedidos.
                                    </span>
                                </div>
                            </label>
                        </div>

                        <div class="rounded-xl bg-muted/20 border border-border p-4 text-xs space-y-2">
                            <h4 class="font-bold text-foreground mb-2 flex items-center gap-2">
                                <Layers :size="14"/> Resumen Geográfico
                            </h4>
                            <div class="flex justify-between border-b border-border/50 pb-2">
                                <span class="text-muted-foreground">Latitud:</span>
                                <span class="font-mono">{{ form.latitude.toFixed(5) }}</span>
                            </div>
                            <div class="flex justify-between border-b border-border/50 pb-2">
                                <span class="text-muted-foreground">Longitud:</span>
                                <span class="font-mono">{{ form.longitude.toFixed(5) }}</span>
                            </div>
                            <div class="flex justify-between pt-1">
                                <span class="text-muted-foreground">Cobertura:</span>
                                <span :class="form.coverage_polygon.length > 2 ? 'text-success font-bold' : 'text-warning font-bold'">
                                    {{ form.coverage_polygon.length > 2 ? 'Definida' : 'Sin Polígono' }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="p-4 border-t border-border bg-background/95 backdrop-blur-md sticky bottom-0 z-[800] flex justify-between gap-4">
                    <button v-if="currentStep > 1" @click="prevStep" type="button" class="btn btn-ghost text-muted-foreground hover:text-foreground">
                        <ArrowLeft :size="18" class="mr-1"/> Atrás
                    </button>
                    <div v-else></div>

                    <button v-if="currentStep < 3" @click="nextStep" type="button" class="btn btn-primary shadow-lg shadow-primary/20 px-6">
                        Siguiente <ArrowRight :size="18" class="ml-1"/>
                    </button>
                    
                    <button v-else @click="submit" :disabled="form.processing" type="button" class="btn btn-primary shadow-lg shadow-primary/20 px-8 w-full md:w-auto">
                        <span v-if="form.processing">Guardando...</span>
                        <span v-else class="flex items-center justify-center gap-2">
                            <Save :size="18" /> Guardar Cambios
                        </span>
                    </button>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<style>
/* Ajustes Leaflet */
.leaflet-div-icon { background: transparent !important; border: none !important; }
</style>