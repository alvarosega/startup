<script setup>
import { ref, computed, nextTick, watch } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminLocationPicker from '@/Components/Admin/Maps/AdminLocationPicker.vue';
import { 
    Store, MapPin, Phone, Navigation, Save, 
    ArrowLeft, ArrowRight, Trash2, RotateCcw, CheckCircle, 
    Layers, Calculator, Target, Cpu, Terminal, Wifi, WifiOff, 
    GitBranch, HardDrive, Activity, Crosshair, Radar, Eye
} from 'lucide-vue-next';

const props = defineProps({ branch: Object });

const currentStep = ref(1);
const mapRef = ref(null);
const zoom = ref(14);
const showCoverageHelp = ref(false);

const steps = [
    { id: 1, title: 'DATOS_BÁSICOS', code: 'SEC_01', icon: Store, fields: ['name', 'city'] },
    { id: 2, title: 'GEOLOCALIZACIÓN', code: 'SEC_02', icon: Crosshair, fields: ['latitude', 'longitude', 'coverage_polygon'] },
    { id: 3, title: 'ESTADO_RED', code: 'SEC_03', icon: Wifi, fields: ['phone', 'address', 'is_active', 'is_default'] },
    { id: 4, title: 'LOGÍSTICA', code: 'SEC_04', icon: Calculator, fields: ['delivery_base_fee', 'delivery_price_per_km', 'surge_multiplier', 'min_order_amount', 'small_order_fee', 'base_service_fee_percentage'] },
];

const form = useForm({
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

const syncCenter = (lat, lng) => {
    form.latitude = lat;
    form.longitude = lng;
};

const onMapClick = (event) => {
    if (currentStep.value === 2 && event.latlng) {
        form.coverage_polygon = [...form.coverage_polygon, [event.latlng.lat, event.latlng.lng]];
    }
};

const undoLastPoint = () => form.coverage_polygon = form.coverage_polygon.slice(0, -1);
const clearPolygon = () => form.coverage_polygon = [];

const jumpToStepWithError = (errors) => {
    for (const step of steps) {
        if (step.fields.some(field => errors[field])) {
            currentStep.value = step.id;
            break;
        }
    }
};

const nextStepWizard = () => {
    if (currentStep.value === 1 && !form.name.trim()) {
        form.setError('name', '// NOMBRE REQUERIDO');
        return;
    }
    if (currentStep.value === 2 && form.coverage_polygon.length > 0 && form.coverage_polygon.length < 3) {
        return alert('SYS_ERR: PERÍMETRO INCOMPLETO // MÍNIMO 3 PUNTOS');
    }
    currentStep.value++;
};

const submit = () => {
    form.put(route('admin.operations.branches.update', props.branch.id), {
        preserveScroll: true,
        onError: (errors) => jumpToStepWithError(errors)
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <AdminLayout>
        <Head title="Editar Sucursal" />
        <template #header>
            <div class="flex items-center justify-between pt-1 mb-6 border-b border-neutral-200 dark:border-neutral-800 pb-4 select-none">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.operations.branches.index')" 
                          class="p-2 border border-neutral-200 dark:border-neutral-800 hover:border-neutral-400 dark:hover:border-neutral-700 bg-white dark:bg-neutral-900 rounded-md shadow-sm text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-all">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase leading-none">RECALIBRAR NODO</h1>
                        <p class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 mt-1 flex items-center gap-2">
                            <Cpu :size="12" class="animate-pulse" /> <span>NODE_{{ branch.id.slice(0,8).toUpperCase() }} // {{ branch.name }}</span> <Terminal :size="12" class="animate-pulse" />
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto pb-12">
            <div class="mb-8 px-4 font-mono select-none">
                <div class="flex justify-between items-end mb-2 text-[10px] font-black uppercase">
                    <span class="text-neutral-900 dark:text-white">FASE_{{ String(currentStep).padStart(2, '0') }} // {{ steps[currentStep-1].code }}</span>
                    <span class="text-neutral-400 dark:text-neutral-50">{{ steps[currentStep-1].title }}</span>
                </div>
                <div class="h-[2px] bg-neutral-200 dark:bg-neutral-800 relative">
                    <div class="absolute h-full bg-neutral-900 dark:bg-neutral-50 transition-all duration-500" :style="{ width: `${progressPercentage}%` }"></div>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow-sm flex flex-col min-h-[450px] overflow-hidden rounded-md">
                <div class="flex border-b border-neutral-200 dark:border-neutral-800/50 bg-neutral-50 dark:bg-neutral-950 select-none">
                    <button v-for="step in steps" :key="step.id" 
                            @click="currentStep = step.id"
                            class="flex-1 p-3 border-r border-neutral-200 dark:border-neutral-800/40 transition-all text-[9px] font-mono font-black uppercase tracking-widest flex items-center justify-center gap-2"
                            :class="currentStep === step.id ? 'text-neutral-900 dark:text-white bg-white dark:bg-neutral-900 font-bold' : 'text-neutral-400 hover:text-neutral-900 dark:hover:text-white'">
                        <component :is="step.icon" :size="12" />
                        <span class="hidden md:inline">{{ step.title }}</span>
                    </button>
                </div>

                <div class="p-6 md:p-8 flex-1">
                    <div v-if="currentStep === 1" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-mono font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest mb-2 block">// IDENTIFICADOR BASE</label>
                                    <input v-model="form.name" type="text" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs font-mono text-neutral-900 dark:text-neutral-50 rounded-md outline-none" />
                                    <p v-if="form.errors.name" class="text-[10px] font-mono text-rose-600 mt-1">{{ form.errors.name }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-mono font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest mb-2 block">// CIUDAD DE OPERACIÓN</label>
                                    <select v-model="form.city" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs font-mono text-neutral-900 dark:text-neutral-50 rounded-md outline-none">
                                        <option value="LA PAZ">LA PAZ</option>
                                        <option value="EL ALTO">EL ALTO</option>
                                        <option value="COCHABAMBA">COCHABAMBA</option>
                                        <option value="SANTA CRUZ">SANTA CRUZ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="lg:col-span-2 h-[300px] border border-neutral-200 dark:border-neutral-800 rounded-md overflow-hidden relative">
                                <AdminLocationPicker 
                                    ref="mapComponentRef"
                                    v-model:modelValueLat="form.latitude" 
                                    v-model:modelValueLng="form.longitude" 
                                    v-model:modelValueAddress="form.address" 
                                    v-model:modelValueBranchId="form.branch_id" 
                                    :activeBranches="activeBranches" 
                                />
                            </div>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 2" class="space-y-4">
                        <div class="flex flex-wrap justify-between items-center gap-2 bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 p-3 rounded font-mono text-xs select-none">
                            <span>VÉRTICES: <span class="font-bold text-neutral-900 dark:text-white">{{ form.coverage_polygon.length }}</span> (MÍNIMO 3)</span>
                            <div class="flex gap-2">
                                <button type="button" @click="undoLastPoint" :disabled="form.coverage_polygon.length === 0" class="px-3 py-1 border border-neutral-200 dark:border-neutral-800 text-[9px] hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded">REVERTIR</button>
                                <button type="button" @click="clearPolygon" :disabled="form.coverage_polygon.length === 0" class="px-3 py-1 border border-rose-200 dark:border-rose-900 text-rose-600 text-[9px] hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded">PURGAR</button>
                            </div>
                        </div>
                        <div class="border border-neutral-200 dark:border-neutral-800 h-[320px] rounded-md overflow-hidden">
                            <AdminLocationPicker 
                                ref="mapComponentRef"
                                v-model:modelValueLat="form.latitude" 
                                v-model:modelValueLng="form.longitude" 
                                v-model:modelValueAddress="form.address" 
                                v-model:modelValueBranchId="form.branch_id" 
                                :activeBranches="activeBranches" 
                            />
                        </div>
                    </div>

                    <div v-else-if="currentStep === 3" class="max-w-2xl mx-auto space-y-4 font-mono">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] font-mono font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest mb-2 block">// CANAL TELEFÓNICO</label>
                                <input v-model="form.phone" type="text" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 outline-none" placeholder="70012345" />
                                <p v-if="form.errors.phone" class="text-[10px] text-rose-600 mt-1">{{ form.errors.phone }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-[10px] font-mono font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest mb-2 block">// REFERENCIA DE DIRECCIÓN</label>
                                <textarea v-model="form.address" rows="2" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 outline-none resize-none" placeholder="DIRECCIÓN COMPLETA"></textarea>
                                <p v-if="form.errors.address" class="text-[10px] text-rose-600 mt-1">{{ form.errors.address }}</p>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-neutral-200 dark:border-neutral-800 grid grid-cols-2 gap-4">
                            <label class="flex items-center justify-between p-3 border border-neutral-200 dark:border-neutral-800 rounded-md cursor-pointer bg-neutral-50/50 dark:bg-neutral-950">
                                <span class="text-xs font-bold uppercase tracking-tight text-neutral-700 dark:text-neutral-300">Transmisión Activa</span>
                                <input type="checkbox" v-model="form.is_active" class="w-4 h-4 text-neutral-900 border-neutral-300 dark:border-neutral-700 rounded-sm focus:ring-0" />
                            </label>
                            <label class="flex items-center justify-between p-3 border border-neutral-200 dark:border-neutral-800 rounded-md cursor-pointer bg-neutral-50/50 dark:bg-neutral-950">
                                <span class="text-xs font-bold uppercase tracking-tight text-neutral-700 dark:text-neutral-300">Nodo Central (Defecto)</span>
                                <input type="checkbox" v-model="form.is_default" class="w-4 h-4 text-neutral-900 border-neutral-300 dark:border-neutral-700 rounded-sm focus:ring-0" />
                            </label>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 4" class="max-w-3xl mx-auto space-y-4 font-mono text-xs">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// BASE DELIVERY (Bs.)</label>
                                <input v-model.number="form.delivery_base_fee" type="number" step="0.01" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// SURGE MULTIPLIER</label>
                                <input v-model.number="form.surge_multiplier" type="number" step="0.1" min="1" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// FEE PLATAFORMA (%)</label>
                                <input v-model.number="form.base_service_fee_percentage" type="number" step="0.01" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// MULTA ORDEN PEQUEÑA (Bs.)</label>
                                <input v-model.number="form.small_order_fee" type="number" step="0.01" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// PRECIO POR KM (Bs.)</label>
                                <input v-model.number="form.delivery_price_per_km" type="number" step="0.01" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                            <div>
                                <label class="block font-bold text-neutral-400 dark:text-neutral-500 mb-1">// PEDIDO MÍNIMO (Bs.)</label>
                                <input v-model.number="form.min_order_amount" type="number" step="0.01" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 focus:ring-0 outline-none" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950/50 flex justify-between font-mono text-xs select-none">
                    <button type="button" @click="currentStep--" :disabled="currentStep === 1" class="px-4 py-1.5 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md text-neutral-700 dark:text-neutral-300 disabled:opacity-30 flex items-center gap-1">
                        <ArrowLeft :size="14" /> REBOBINAR
                    </button>
                    <button v-if="currentStep < 4" type="button" @click="nextStepWizard" class="px-6 py-1.5 bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 rounded-md flex items-center gap-1 font-black">
                        AVANZAR <ArrowRight :size="14" />
                    </button>
                    <button v-else @click="submit" :disabled="form.processing" class="px-6 py-1.5 bg-white dark:bg-neutral-900 border border-neutral-900 dark:border-white text-neutral-900 dark:text-white rounded-md font-black flex items-center gap-1 disabled:opacity-50">
                        <Save :size="14" /> EJECUTAR RECALIBRACIÓN
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>