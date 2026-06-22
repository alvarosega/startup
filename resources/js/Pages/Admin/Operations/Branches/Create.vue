<script setup>
import { ref, computed, nextTick, watch } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminLocationPicker from '@/Components/Admin/Maps/AdminLocationPicker.vue';
import { 
    Store, MapPin, Phone, Navigation, Save, 
    ArrowLeft, ArrowRight, Trash2, RotateCcw, CheckCircle, 
    Layers, Calculator, Cpu, Terminal, Wifi, WifiOff, 
    GitBranch, Activity, HardDrive, Crosshair, Radar, Eye, AlertTriangle
} from 'lucide-vue-next';

const currentStep = ref(1);
const showCoverageHelp = ref(false);

const steps = [
    { id: 1, title: 'DATOS_BÁSICOS', code: 'SEC_01', icon: Store },
    { id: 2, title: 'GEOLOCALIZACIÓN', code: 'SEC_02', icon: Crosshair },
    { id: 3, title: 'ESTADO_RED', code: 'SEC_03', icon: Wifi },
    { id: 4, title: 'LOGÍSTICA', code: 'SEC_04', icon: Calculator },
];

const form = useForm({
    name: '',
    phone: '',
    city: 'LA PAZ',
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
const mapRef = ref(null);

const onMarkerDrag = (lat, lng) => {
    form.latitude = lat;
    form.longitude = lng;
};

watch(currentStep, async () => {
    await nextTick();
    mapRef.value?.fixMap();
});

const onMapClick = (event) => {
    if (currentStep.value === 2 && event.latlng) {
        form.coverage_polygon = [...form.coverage_polygon, [event.latlng.lat, event.latlng.lng]];
    }
};

const undoLastPoint = () => form.coverage_polygon = form.coverage_polygon.slice(0, -1);
const clearPolygon = () => form.coverage_polygon = [];

const nextStepWizard = () => {
    if (currentStep.value === 1) {
        if (!form.name.trim()) {
            form.setError('name', '// IDENTIFICADOR REQUERIDO');
            return;
        }
    }
    if (currentStep.value === 2) {
        if (form.coverage_polygon.length > 0 && form.coverage_polygon.length < 3) {
            alert("SYS_ERR: TRIANGULACIÓN INCOMPLETA // MÍNIMO 3 VECTORES");
            return;
        }
    }
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStepWizard = () => {
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
        form.setError('name', '// IDENTIFICADOR REQUERIDO');
        currentStep.value = 1;
        return;
    }
    
    form.post(route('admin.operations.branches.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors) => jumpToStepWithError(errors)
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <AdminLayout>
        <Head title="Nueva Sucursal" />
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2 pb-4 border-b border-neutral-200 dark:border-neutral-800 relative group">
                <div class="relative z-10 flex items-center gap-4 select-none">
                    <div class="w-12 h-12 border border-neutral-200 dark:border-neutral-800 rounded-md flex items-center justify-center bg-white dark:bg-neutral-900">
                        <Store :size="24" class="text-neutral-900 dark:text-neutral-50" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-neutral-900 dark:text-neutral-50 uppercase tracking-widest">INICIALIZAR NODO</h1>
                        <p class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 font-bold tracking-widest uppercase mt-1 flex items-center gap-2">
                            <Cpu :size="12" class="animate-pulse" /> CONFIGURACIÓN TÁCTICA <Terminal :size="12" class="animate-pulse" />
                        </p>
                    </div>
                </div>
                
                <Link :href="route('admin.operations.branches.index')" 
                      class="px-4 py-2 border border-rose-200 dark:border-rose-900 text-rose-600 dark:text-rose-400 font-mono text-xs hover:bg-rose-600 hover:text-white rounded-md transition-all select-none">
                    <span class="flex items-center gap-2">
                        <AlertTriangle :size="14" /> ABORTAR
                    </span>
                </Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto py-6">
            <div class="mb-10 select-none">
                <div class="flex justify-between items-end mb-2">
                    <div class="flex items-center gap-3">
                        <div class="px-2 py-0.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-900 dark:text-neutral-50 font-mono font-black text-xs rounded-sm">
                            SEQ_{{ String(currentStep).padStart(2, '0') }}
                        </div>
                        <span class="text-xs font-black uppercase tracking-widest text-neutral-900 dark:text-neutral-50">
                            {{ steps[currentStep - 1].title }}
                        </span>
                    </div>
                    <div class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 font-bold tracking-widest uppercase">
                        FASE {{ currentStep }}/{{ steps.length }}
                    </div>
                </div>
                <div class="h-[2px] bg-neutral-200 dark:bg-neutral-800 w-full relative">
                    <div class="absolute top-0 left-0 h-full bg-neutral-900 dark:bg-neutral-50 transition-all duration-500 ease-out" :style="{ width: `${progressPercentage}%` }"></div>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow-sm flex flex-col min-h-[450px] overflow-hidden rounded-md">
                <div class="flex items-center bg-neutral-50 dark:bg-neutral-950 border-b border-neutral-200 dark:border-neutral-800 select-none">
                    <button v-for="step in steps" :key="step.id" 
                            class="flex-1 p-3 border-r border-neutral-200 dark:border-neutral-800/40 text-[9px] font-mono font-black uppercase tracking-widest flex items-center justify-center gap-2 transition-colors "
                            :class="currentStep === step.id ? 'text-neutral-900 dark:text-white bg-white dark:bg-neutral-900 font-bold' : 'text-neutral-400 hover:text-neutral-900 dark:hover:text-white'"
                            :disabled="currentStep < step.id"
                            @click="currentStep = step.id">
                        <CheckCircle v-if="currentStep > step.id" :size="12" class="text-emerald-600 dark:text-emerald-400" />
                        <span v-else>[ {{ step.code }} ]</span>
                        <span class="hidden md:inline">{{ step.title }}</span>
                    </button>
                </div>

                <div class="p-6 md:p-8 flex-1">
                    <div v-if="currentStep === 1" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-mono font-bold uppercase tracking-widest text-neutral-400 dark:text-neutral-500 mb-2">// IDENTIFICADOR OPERATIVO *</label>
                                    <input v-model="form.name" type="text" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none uppercase font-mono transition-colors" placeholder="Ej. SECTOR_SUR" />
                                    <p v-if="form.errors.name" class="text-[10px] font-mono text-rose-600 mt-1">{{ form.errors.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-mono font-bold uppercase tracking-widest text-neutral-400 dark:text-neutral-500 mb-2">// NODO PRINCIPAL (CIUDAD) *</label>
                                    <select v-model="form.city" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-mono text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors">
                                        <option value="LA PAZ">LA PAZ</option>
                                        <option value="EL ALTO">EL ALTO</option>
                                        <option value="COCHABAMBA">COCHABAMBA</option>
                                        <option value="SANTA CRUZ">SANTA CRUZ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="lg:col-span-2">
                                <div class="border border-neutral-200 dark:border-neutral-800 h-[300px] rounded-md overflow-hidden relative">
                                    <AdminLocationPicker 
                                        ref="mapRef"
                                        v-model:modelValueLat="form.latitude"
                                        v-model:modelValueLng="form.longitude"
                                        v-model:modelValueAddress="form.address"
                                        class="w-full h-full"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 2" class="space-y-4">
                        <div class="flex justify-between items-center bg-neutral-50 dark:bg-neutral-950 p-2 border border-neutral-200 dark:border-neutral-800 rounded font-mono text-xs">
                            <span>VECTORES REGISTRADOS: <span class="font-bold text-neutral-900 dark:text-white">{{ form.coverage_polygon.length }}</span> (MÍNIMO 3)</span>
                            <div class="flex gap-2">
                                <button type="button" @click="undoLastPoint" :disabled="form.coverage_polygon.length === 0" class="px-3 py-1 border border-neutral-200 dark:border-neutral-800 text-[9px] hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded disabled:opacity-30">REVERTIR</button>
                                <button type="button" @click="clearPolygon" :disabled="form.coverage_polygon.length === 0" class="px-3 py-1 border border-rose-200 dark:border-rose-900 text-rose-600 rounded text-[9px] hover:bg-rose-50 dark:hover:bg-rose-950/20">PURGAR</button>
                            </div>
                        </div>
                        <div class="border border-neutral-200 dark:border-neutral-800 h-[320px] rounded-md overflow-hidden">
                            <AdminLocationPicker 
                                ref="mapRef"
                                v-model:modelValueLat="form.latitude"
                                v-model:modelValueLng="form.longitude"
                                v-model:modelValueAddress="form.address"
                                :active-branches="[{ id: 'temp', polygon: form.coverage_polygon }]"
                                class="w-full h-full"
                            />
                        </div>
                    </div>

                    <div v-else-if="currentStep === 3" class="max-w-2xl mx-auto space-y-4 font-mono">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-neutral-400 dark:text-neutral-500 mb-2">// CANAL DE COMUNICACIÓN</label>
                            <input v-model="form.phone" type="text" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" placeholder="70012345" />
                            <p v-if="form.errors.phone" class="text-[10px] text-rose-600 mt-1">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-neutral-400 dark:text-neutral-500 mb-2">// DIRECCIÓN FÍSICA</label>
                            <textarea v-model="form.address" rows="2" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors resize-none" placeholder="Especificar ubicación..."></textarea>
                            <p v-if="form.errors.address" class="text-[10px] text-rose-600 mt-1">{{ form.errors.address }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <label class="flex items-center justify-between p-3 border border-neutral-200 dark:border-neutral-800 rounded-md cursor-pointer bg-neutral-50/50 dark:bg-neutral-950">
                                <span class="text-xs font-bold uppercase tracking-tight text-neutral-700 dark:text-neutral-300">Habilitar Sucursal</span>
                                <input type="checkbox" v-model="form.is_active" class="w-4 h-4 text-neutral-900 border-neutral-300 dark:border-neutral-700 rounded-sm focus:ring-0" />
                            </label>
                            <label class="flex items-center justify-between p-3 border border-neutral-200 dark:border-neutral-800 rounded-md cursor-pointer bg-neutral-50/50 dark:bg-neutral-950">
                                <span class="text-xs font-bold uppercase tracking-tight text-neutral-700 dark:text-neutral-300">Fijar Por Defecto</span>
                                <input type="checkbox" v-model="form.is_default" class="w-4 h-4 text-neutral-900 border-neutral-300 dark:border-neutral-700 rounded-sm focus:ring-0" />
                            </label>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 4" class="max-w-3xl mx-auto space-y-4 font-mono text-xs">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// TARIFA BASE (Bs.)</label>
                                <input v-model.number="form.delivery_base_fee" type="number" step="0.5" min="0" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// COSTO POR KM (Bs.)</label>
                                <input v-model.number="form.delivery_price_per_km" type="number" step="0.1" min="0" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// MULTIPLICADOR DE DEMANDA</label>
                                <input v-model.number="form.surge_multiplier" type="number" step="0.1" min="1" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// UMBRAL PEDIDO MÍNIMO (Bs.)</label>
                                <input v-model.number="form.min_order_amount" type="number" step="1" min="0" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// MULTA PEDIDO PEQUEÑO (Bs.)</label>
                                <input v-model.number="form.small_order_fee" type="number" step="0.5" min="0" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// RETENCIÓN SERVICIO PLATAFORMA (%)</label>
                                <input v-model.number="form.base_service_fee_percentage" type="number" step="0.1" min="0" max="100" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950/50 flex justify-between items-center select-none shrink-0 font-mono text-xs">
                    <button type="button" @click="prevStepWizard" :disabled="currentStep === 1" class="px-4 py-1.5 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md text-neutral-700 dark:text-neutral-300 disabled:opacity-30 flex items-center gap-1">
                        <ArrowLeft :size="14" /> REBOBINAR
                    </button>
                    <button v-if="currentStep < 4" type="button" @click="nextStepWizard" class="px-6 py-1.5 bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 rounded-md flex items-center gap-1 font-black">
                        AVANZAR <ArrowRight :size="14" />
                    </button>
                    <button v-else type="button" @click="submit" :disabled="form.processing" class="px-6 py-1.5 bg-white dark:bg-neutral-900 border border-neutral-900 dark:border-white text-neutral-900 dark:text-white rounded-md font-black flex items-center gap-1 disabled:opacity-50">
                        <Save :size="14" /> INICIALIZAR NODO
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>