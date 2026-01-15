<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref, onMounted, computed, nextTick } from 'vue';
    
    // Iconos
    import { 
        Store, MapPin, Phone, Navigation, Save, 
        ArrowLeft, ArrowRight, Trash2, RotateCcw, AlertTriangle, CheckCircle 
    } from 'lucide-vue-next';

    import "leaflet/dist/leaflet.css";
    import { LMap, LTileLayer, LMarker, LPolygon, LTooltip, LCircleMarker, LControlZoom } from "@vue-leaflet/vue-leaflet";
    import L from 'leaflet';

    const currentStep = ref(1);
    
    const steps = [
        { id: 1, title: 'Ubicación', icon: Store, fields: ['name', 'city', 'latitude'] }, // Paso 1: Datos y Pin
        { id: 2, title: 'Cobertura', icon: MapPin, fields: ['coverage_polygon'] }, // Paso 2: Dibujar Zona
        { id: 3, title: 'Detalles', icon: Phone, fields: ['phone', 'address', 'is_active'] }, // Paso 3: Contacto
    ];

    const form = useForm({
        name: '',
        phone: '',
        city: 'La Paz',
        address: '',
        latitude: -16.5000, 
        longitude: -68.1500,
        coverage_polygon: [], 
        is_active: true
    });

    const zoom = ref(13);
    const center = ref([-16.5000, -68.1500]);
    const mapRef = ref(null);

    // --- LEAFLET SETUP ---
    onMounted(() => {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
            iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
            shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
        });
    });

    const onMapReady = async (mapInstance) => {
        await nextTick();
        mapInstance.invalidateSize(); 
    };

    // --- INTERACCIÓN MAPA ---
    const onMarkerDrag = (event) => {
        const { lat, lng } = event.target.getLatLng();
        form.latitude = lat;
        form.longitude = lng;
    };

    const onMapClick = (event) => {
        // Solo permitimos dibujar en el paso 2
        if (currentStep.value === 2 && event.latlng) {
            form.coverage_polygon.push([event.latlng.lat, event.latlng.lng]);
        }
    };

    const undoLastPoint = () => form.coverage_polygon.pop();
    const clearPolygon = () => form.coverage_polygon = [];

    // --- NAVEGACIÓN WIZARD ---
    const nextStep = () => {
        if (currentStep.value === 1) {
            if (!form.name) { form.setError('name', 'Nombre requerido'); return; }
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
        form.post(route('admin.branches.store'));
    };

    const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <AdminLayout>
        <div class="max-w-5xl mx-auto py-6">
            
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-black text-skin-base tracking-tight">Nueva Sucursal</h1>
                        <p class="text-skin-muted text-sm mt-1">Configura un punto de venta y su zona de reparto</p>
                    </div>
                    <Link :href="route('admin.branches.index')" class="text-sm font-bold text-skin-muted hover:text-skin-danger transition-colors">Cancelar</Link>
                </div>

                <div class="relative px-4">
                    <div class="absolute top-5 left-0 w-full h-1 bg-skin-border -z-10 rounded-full"></div>
                    <div class="absolute top-5 left-0 h-1 bg-skin-primary -z-10 rounded-full transition-all duration-500 ease-out" :style="{ width: progressPercentage + '%' }"></div>

                    <div class="flex justify-between">
                        <div v-for="step in steps" :key="step.id" 
                             class="flex flex-col items-center gap-2 cursor-pointer group"
                             @click="currentStep >= step.id ? currentStep = step.id : null">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all bg-skin-fill-card"
                                 :class="[currentStep === step.id ? 'border-skin-primary text-skin-primary scale-110 shadow-lg' : currentStep > step.id ? 'border-skin-success bg-skin-success text-white' : 'border-skin-border text-skin-muted']">
                                <CheckCircle v-if="currentStep > step.id" :size="20" />
                                <component v-else :is="step.icon" :size="18" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-skin-fill px-1" :class="currentStep >= step.id ? 'text-skin-base' : 'text-skin-muted'">{{ step.title }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-skin-fill-card border border-skin-border rounded-global shadow-xl overflow-hidden min-h-[500px] flex flex-col">
                <form class="flex-1 flex flex-col">
                    <div class="flex-1 relative"> <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="p-8 h-full flex flex-col lg:flex-row gap-8">
                                <div class="w-full lg:w-1/3 space-y-6 z-10">
                                    <div>
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Nombre Sucursal *</label>
                                        <input v-model="form.name" type="text" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 focus:ring-2 focus:ring-blue-500 outline-none font-bold" placeholder="Ej: Zona Sur">
                                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Ciudad Base *</label>
                                        <select v-model="form.city" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-blue-500">
                                            <option>La Paz</option>
                                            <option>El Alto</option>
                                            <option>Cochabamba</option>
                                            <option>Santa Cruz</option>
                                            <option>Tarija</option>
                                        </select>
                                    </div>
                                    <div class="bg-blue-900/10 border border-blue-500/20 p-4 rounded-global">
                                        <h4 class="text-blue-400 font-bold text-xs uppercase mb-2 flex items-center gap-2"><Navigation :size="14"/> Instrucción</h4>
                                        <p class="text-xs text-blue-200/70">Arrastra el pin en el mapa para ubicar la tienda exacta.</p>
                                    </div>
                                </div>

                                <div class="flex-1 rounded-global overflow-hidden border border-skin-border h-[400px] lg:h-auto relative z-0">
                                    <l-map ref="mapRef" v-model:zoom="zoom" :center="center" :use-global-leaflet="false" :options="{ zoomControl: false }" @ready="onMapReady" class="h-full w-full bg-gray-200">
                                        <l-tile-layer url="https://tile.openstreetmap.org/{z}/{x}/{y}.png" attribution='&copy; OpenStreetMap'></l-tile-layer>
                                        <l-control-zoom position="bottomright" />
                                        <l-marker :lat-lng="[form.latitude, form.longitude]" draggable @dragend="onMarkerDrag">
                                            <l-tooltip :options="{ permanent: true, direction: 'top', offset: [0, -20] }">📍 {{ form.name || 'Aquí' }}</l-tooltip>
                                        </l-marker>
                                    </l-map>
                                </div>
                            </div>

                            <div v-else-if="currentStep === 2" key="2" class="h-[500px] relative flex flex-col">
                                <div class="absolute top-4 left-4 right-4 z-[500] bg-skin-fill-card/90 backdrop-blur border border-skin-border p-3 rounded-global flex justify-between items-center shadow-lg">
                                    <div>
                                        <h2 class="text-skin-base font-bold text-sm flex items-center gap-2"><MapPin :size="16" class="text-green-500"/> Dibuja la Zona de Reparto</h2>
                                        <p class="text-[10px] text-skin-muted">Haz clic en el mapa para añadir puntos. (Actual: {{ form.coverage_polygon.length }})</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" @click="undoLastPoint" :disabled="form.coverage_polygon.length === 0" class="flex items-center gap-1 bg-skin-fill border border-skin-border hover:bg-skin-border text-skin-base text-xs px-3 py-1.5 rounded transition disabled:opacity-50"><RotateCcw :size="12" /> Deshacer</button>
                                        <button type="button" @click="clearPolygon" :disabled="form.coverage_polygon.length === 0" class="flex items-center gap-1 bg-red-900/20 border border-red-900/50 hover:bg-red-900/40 text-red-400 text-xs px-3 py-1.5 rounded transition disabled:opacity-50"><Trash2 :size="12" /> Limpiar</button>
                                    </div>
                                </div>

                                <l-map ref="mapRef" v-model:zoom="zoom" :center="center" :use-global-leaflet="false" :options="{ zoomControl: false }" @click="onMapClick" @ready="onMapReady" class="h-full w-full bg-gray-200">
                                    <l-tile-layer url="https://tile.openstreetmap.org/{z}/{x}/{y}.png" attribution='&copy; OpenStreetMap'></l-tile-layer>
                                    <l-control-zoom position="bottomright" />
                                    
                                    <l-marker :lat-lng="[form.latitude, form.longitude]">
                                        <l-tooltip :options="{ permanent: true, direction: 'top' }">🏠 Tienda</l-tooltip>
                                    </l-marker>

                                    <l-circle-marker v-for="(point, index) in form.coverage_polygon" :key="index" :lat-lng="point" :radius="5" color="#000000" fill-color="#000000" :fill-opacity="1" :weight="2" />
                                    <l-polygon v-if="form.coverage_polygon.length > 0" :lat-lngs="form.coverage_polygon" color="#374151" fill-color="#374151" :fill="true" :fill-opacity="0.5" :weight="3"></l-polygon>
                                </l-map>
                            </div>

                            <div v-else key="3" class="p-8 h-full flex flex-col items-center justify-center max-w-2xl mx-auto w-full">
                                <div class="w-full space-y-6">
                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Teléfono de Contacto</label>
                                            <input v-model="form.phone" type="text" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-blue-500">
                                        </div>
                                        <div class="flex items-end">
                                            <label class="flex items-center gap-3 p-3 border border-skin-border rounded-global cursor-pointer hover:border-blue-500 transition bg-skin-fill/50 w-full h-[46px]">
                                                <input v-model="form.is_active" type="checkbox" class="w-5 h-5 accent-blue-500">
                                                <span class="text-sm font-bold text-skin-base">Sucursal Operativa</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Dirección Física Completa</label>
                                        <textarea v-model="form.address" rows="3" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-blue-500 resize-none"></textarea>
                                    </div>

                                    <div class="p-4 bg-skin-fill/30 border border-skin-border rounded-global text-sm text-skin-muted">
                                        <h4 class="font-bold text-skin-base mb-2">Resumen:</h4>
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Nombre: <strong>{{ form.name }}</strong></li>
                                            <li>Ubicación: <strong>{{ form.city }}</strong> ({{ form.latitude.toFixed(4) }}, {{ form.longitude.toFixed(4) }})</li>
                                            <li>Zona de Reparto: <strong>{{ form.coverage_polygon.length > 0 ? 'Definida' : 'Sin cobertura' }}</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </Transition>
                    </div>

                    <div class="px-8 py-4 bg-skin-fill/50 border-t border-skin-border flex justify-between items-center z-20 relative">
                        <button type="button" @click="prevStep" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-skin-muted hover:text-skin-base disabled:opacity-0" :disabled="currentStep === 1">
                            <ArrowLeft :size="16" /> Atrás
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="flex items-center gap-2 px-6 py-2.5 bg-skin-fill-card border border-skin-border hover:border-skin-primary text-skin-base rounded-global text-sm font-bold shadow-sm active:scale-95 transition-all">
                            Siguiente <ArrowRight :size="16" />
                        </button>

                        <button v-else type="button" @click="submit" :disabled="form.processing" class="flex items-center gap-2 px-8 py-2.5 bg-skin-primary hover:bg-skin-primary-hover text-skin-primary-text rounded-global text-sm font-bold shadow-lg shadow-skin-primary/30 active:scale-95 transition-all disabled:opacity-50">
                            <Save :size="18" /> {{ form.processing ? 'Guardando...' : 'Crear Sucursal' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.leaflet-container { background-color: #e5e7eb; }
.leaflet-tooltip {
    background-color: #1f2937;
    border: 1px solid #374151;
    color: white;
    font-weight: bold;
    border-radius: 4px;
}
.leaflet-tooltip-top:before { border-top-color: #374151; }
</style>