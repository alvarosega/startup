<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
    import { ref, onMounted, computed, nextTick } from 'vue';
    import { 
        Store, MapPin, Phone, Navigation, Save, 
        ArrowLeft, ArrowRight, Trash2, RotateCcw, CheckCircle 
    } from 'lucide-vue-next';
    
    import "leaflet/dist/leaflet.css";
    import { LMap, LTileLayer, LMarker, LPolygon, LTooltip, LCircleMarker, LControlZoom } from "@vue-leaflet/vue-leaflet";
    import L from 'leaflet';
    
    const props = defineProps({
        branch: Object
    });
    
    const currentStep = ref(1);
    
    const steps = [
        { id: 1, title: 'Ubicación', icon: Store },
        { id: 2, title: 'Cobertura', icon: MapPin },
        { id: 3, title: 'Detalles', icon: Phone },
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
        is_active: Boolean(props.branch.is_active)
    });
    
    const zoom = ref(14);
    const center = computed(() => [form.latitude, form.longitude]);
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
        
        form.post(route('admin.branches.update', props.branch.id));
    };
    
    const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
    </script>
    
    <template>
        <AdminLayout>
            <!-- HEADER -->
            <template #header>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="animate-slide-up">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="avatar avatar-lg bg-gradient-to-br from-primary to-secondary text-primary-foreground shadow-lg">
                                <Store :size="24" />
                            </div>
                            <div>
                                <h1 class="text-3xl lg:text-4xl font-display font-black text-foreground tracking-tight">
                                    Editar Sucursal
                                </h1>
                                <p class="text-muted-foreground font-medium text-sm mt-1">
                                    Modificando: <span class="text-primary font-bold">{{ branch.name }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <Link :href="route('admin.branches.index')" 
                          class="btn btn-outline btn-md">
                        Cancelar
                    </Link>
                </div>
            </template>
    
            <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <!-- PROGRESS BAR -->
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
    
                <!-- WIZARD CONTENT -->
                <div class="card min-h-[500px] flex flex-col">
                    <!-- STEP INDICATORS -->
                    <div class="card-header border-b border-border/50">
                        <div class="flex items-center justify-center gap-8">
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
    
                    <!-- STEP CONTENT -->
                    <div class="card-content flex-1 p-6">
                        <!-- STEP 1: UBICACIÓN -->
                        <div v-if="currentStep === 1" class="animate-in">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- FORM COLUMN -->
                                <div class="space-y-6">
                                    <div>
                                        <label class="form-label">
                                            Nombre Sucursal *
                                        </label>
                                        <input v-model="form.name" 
                                               type="text" 
                                               class="form-input font-bold"
                                               required />
                                        <p v-if="form.errors.name" class="form-error">
                                            {{ form.errors.name }}
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label class="form-label">
                                            Ciudad Base *
                                        </label>
                                        <select v-model="form.city" 
                                                class="form-input">
                                            <option>La Paz</option>
                                            <option>El Alto</option>
                                            <option>Cochabamba</option>
                                            <option>Santa Cruz</option>
                                            <option>Tarija</option>
                                        </select>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <Navigation :size="16" />
                                        <span class="text-sm">
                                            Arrastra el pin en el mapa para ajustar la ubicación.
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- MAP COLUMN -->
                                <div class="lg:col-span-2">
                                    <div class="rounded-xl overflow-hidden border border-border h-[400px] relative">
                                        <l-map ref="mapRef" 
                                               v-model:zoom="zoom" 
                                               :center="center" 
                                               :use-global-leaflet="false" 
                                               :options="{ zoomControl: false }" 
                                               @ready="onMapReady" 
                                               class="h-full w-full">
                                            <l-tile-layer 
                                                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                            />
                                            <l-control-zoom position="bottomright" />
                                            <l-marker 
                                                :lat-lng="[form.latitude, form.longitude]" 
                                                draggable 
                                                @dragend="onMarkerDrag">
                                                <l-tooltip :options="{ permanent: true, direction: 'top', offset: [0, -20] }">
                                                    📍 {{ form.name }}
                                                </l-tooltip>
                                            </l-marker>
                                        </l-map>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <!-- STEP 2: COBERTURA -->
                        <div v-else-if="currentStep === 2" class="h-[450px] relative animate-in">
                            <!-- TOOLBAR -->
                            <div class="absolute top-4 left-4 right-4 z-[500] glass p-4 rounded-xl flex justify-between items-center shadow-lg">
                                <div>
                                    <h2 class="font-bold text-foreground text-sm flex items-center gap-2">
                                        <MapPin :size="16" class="text-success" />
                                        Zona de Reparto
                                    </h2>
                                    <p class="text-xs text-muted-foreground">
                                        Puntos: {{ form.coverage_polygon.length }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" 
                                            @click="undoLastPoint" 
                                            :disabled="form.coverage_polygon.length === 0"
                                            class="btn btn-outline btn-sm flex items-center gap-1">
                                        <RotateCcw :size="12" /> Deshacer
                                    </button>
                                    <button type="button" 
                                            @click="clearPolygon" 
                                            :disabled="form.coverage_polygon.length === 0"
                                            class="btn btn-error btn-sm flex items-center gap-1">
                                        <Trash2 :size="12" /> Limpiar
                                    </button>
                                </div>
                            </div>
    
                            <!-- MAP -->
                            <div class="h-full rounded-xl overflow-hidden border border-border">
                                <l-map ref="mapRef" 
                                       v-model:zoom="zoom" 
                                       :center="center" 
                                       :use-global-leaflet="false" 
                                       :options="{ zoomControl: false }" 
                                       @click="onMapClick" 
                                       @ready="onMapReady" 
                                       class="h-full w-full">
                                    <l-tile-layer 
                                        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                        attribution='&copy; OpenStreetMap'
                                    />
                                    <l-control-zoom position="bottomright" />
                                    
                                    <l-marker :lat-lng="[form.latitude, form.longitude]">
                                        <l-tooltip :options="{ permanent: true, direction: 'top' }">
                                            🏠 {{ form.name }}
                                        </l-tooltip>
                                    </l-marker>
    
                                    <l-circle-marker 
                                        v-for="(point, index) in form.coverage_polygon" 
                                        :key="index" 
                                        :lat-lng="point" 
                                        :radius="5" 
                                        color="#000000" 
                                        fill-color="#000000" 
                                        :fill-opacity="1" 
                                        :weight="2" 
                                    />
                                    <l-polygon 
                                        v-if="form.coverage_polygon.length > 0" 
                                        :lat-lngs="form.coverage_polygon" 
                                        color="#374151" 
                                        fill-color="#374151" 
                                        :fill="true" 
                                        :fill-opacity="0.5" 
                                        :weight="3"
                                    />
                                </l-map>
                            </div>
                        </div>
    
                        <!-- STEP 3: DETALLES -->
                        <div v-else class="animate-in">
                            <div class="max-w-2xl mx-auto w-full space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="form-label">
                                            Teléfono de Contacto
                                        </label>
                                        <input v-model="form.phone" 
                                               type="text" 
                                               class="form-input" 
                                               placeholder="Ej: 70012345" />
                                    </div>
                                    
                                    <BaseCheckbox 
                                        v-model="form.is_active" 
                                        class="p-4 border border-border rounded-xl hover:border-primary transition bg-muted/10 w-full"
                                    >
                                        <span class="text-sm font-bold text-foreground">Sucursal Operativa</span>
                                    </BaseCheckbox>
                                </div>
    
                                <div>
                                    <label class="form-label">
                                        Dirección Física Completa
                                    </label>
                                    <textarea v-model="form.address" 
                                              rows="3" 
                                              class="form-input resize-none"></textarea>
                                </div>
    
                                <!-- RESUMEN -->
                                <div class="card bg-muted/10">
                                    <div class="card-content">
                                        <h3 class="text-sm font-bold text-foreground mb-3">Resumen</h3>
                                        <ul class="space-y-2 text-sm">
                                            <li class="flex justify-between">
                                                <span class="text-muted-foreground">Nombre:</span>
                                                <span class="font-bold text-foreground">{{ form.name }}</span>
                                            </li>
                                            <li class="flex justify-between">
                                                <span class="text-muted-foreground">Ubicación:</span>
                                                <span class="font-bold text-foreground">
                                                    {{ form.city }} ({{ form.latitude.toFixed(4) }}, {{ form.longitude.toFixed(4) }})
                                                </span>
                                            </li>
                                            <li class="flex justify-between">
                                                <span class="text-muted-foreground">Zona de Reparto:</span>
                                                <span class="font-bold" :class="form.coverage_polygon.length > 0 ? 'text-success' : 'text-warning'">
                                                    {{ form.coverage_polygon.length > 0 ? `Definida (${form.coverage_polygon.length} ptos)` : 'Sin cobertura' }}
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- STEP NAVIGATION -->
                    <div class="card-footer border-t border-border/50">
                        <div class="flex justify-between items-center">
                            <button type="button" 
                                    @click="prevStep" 
                                    :disabled="currentStep === 1"
                                    class="btn btn-outline btn-md flex items-center gap-2"
                                    :class="{ 'opacity-0 invisible': currentStep === 1 }">
                                <ArrowLeft :size="16" />
                                <span>Atrás</span>
                            </button>
    
                            <button v-if="currentStep < steps.length" 
                                    type="button" 
                                    @click="nextStep" 
                                    class="btn btn-primary btn-md flex items-center gap-2 group">
                                <span>Siguiente</span>
                                <ArrowRight :size="16" class="transition-transform duration-fast group-hover:translate-x-1" />
                            </button>
                            
                            <button v-else 
                                    @click="submit" 
                                    :disabled="form.processing"
                                    class="btn btn-primary btn-md flex items-center gap-2">
                                <Save :size="18" />
                                <span>{{ form.processing ? 'Actualizando...' : 'Actualizar Sucursal' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    </template>
    
    <style scoped>
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.3s ease;
    }
    
    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }
    
    .leaflet-container {
        background-color: #f5f5f5;
    }
    
    .leaflet-tooltip {
        background-color: hsl(var(--background));
        border: 1px solid hsl(var(--border));
        color: hsl(var(--foreground));
        font-weight: 600;
        border-radius: var(--radius-md);
        padding: 4px 8px;
    }
    
    .leaflet-tooltip-top:before {
        border-top-color: hsl(var(--border));
    }
    </style>