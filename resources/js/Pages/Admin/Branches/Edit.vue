<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, nextTick } from 'vue';
import { 
    Store, MapPin, Phone, Navigation, Save, 
    ArrowLeft, ArrowRight, Trash2, RotateCcw, CheckCircle, 
    Layers, Info, AlertTriangle, Calculator, Target,
    Cpu, Terminal, Zap, Wifi, WifiOff, GitBranch,
    HardDrive, Activity, Crosshair, Radar, Eye
} from 'lucide-vue-next';

// Importamos nuestro componente de mapa unificado
import MapComponent from '@/Components/Base/MapComponent.vue';

const props = defineProps({
    branch: Object
});

const currentStep = ref(1);
const mapRef = ref(null);
const zoom = ref(14);
const showCoverageHelp = ref(false);

// Configuración de pasos con códigos
const steps = [
    { id: 1, title: 'DATOS_BÁSICOS', code: 'SEC_01', icon: Store, fields: ['name', 'city'] },
    { id: 2, title: 'GEOLOCALIZACIÓN', code: 'SEC_02', icon: Crosshair, fields: ['latitude', 'longitude', 'coverage_polygon'] },
    { id: 3, title: 'ESTADO_RED', code: 'SEC_03', icon: Wifi, fields: ['phone', 'address', 'is_active', 'is_default'] },
    { id: 4, title: 'LOGÍSTICA', code: 'SEC_04', icon: Calculator, fields: ['delivery_base_fee', 'delivery_price_per_km', 'surge_multiplier', 'min_order_amount', 'small_order_fee', 'base_service_fee_percentage'] },
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

// --- LÓGICA DE MAPA CON COMPONENTE UNIFICADO ---
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
    form.put(route('admin.branches.update', props.branch.id), {
        preserveScroll: true,
        onError: (errors) => jumpToStepWithError(errors)
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);

// Código del nodo
const nodeCode = computed(() => {
    return `NODE_${String(props.branch.id).padStart(4, '0')}`;
});
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between pt-1 mb-6 border-b border-primary/30 pb-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.branches.index')" 
                          class="p-2 border border-border hover:border-primary hover:shadow-neon-primary transition-all relative group/back">
                        <ArrowLeft :size="20" class="group-hover/back:text-primary transition-colors" />
                        <!-- Esquinas decorativas -->
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/back:opacity-100"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/back:opacity-100"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/back:opacity-100"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/back:opacity-100"></span>
                    </Link>
                    <div class="relative group/title">
                        <h1 class="text-2xl font-display font-black tracking-tight text-primary uppercase glitch-text leading-none"
                            data-text="RECALIBRAR NODO">
                            RECALIBRAR NODO
                        </h1>
                        <p class="text-[10px] font-mono text-primary mt-1 flex items-center gap-2">
                            <Cpu :size="12" class="animate-pulse" />
                            <span>{{ nodeCode }} // {{ props.branch.name }}</span>
                            <Terminal :size="12" class="animate-pulse" />
                        </p>
                        <!-- Línea de escaneo -->
                        <div class="absolute -bottom-2 left-0 w-0 h-[1px] bg-primary group-hover/title:w-full transition-all duration-700"></div>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto pb-32">
            
            <!-- Progress Bar -->
            <div class="mb-8 px-4 font-mono">
                <div class="flex justify-between items-end mb-2 text-[10px] font-black uppercase">
                    <span class="text-primary">FASE_{{ String(currentStep).padStart(2, '0') }} // {{ steps[currentStep-1].code }}</span>
                    <span class="text-muted-foreground">{{ steps[currentStep-1].title }}</span>
                </div>
                <div class="h-[2px] bg-border relative">
                    <div class="absolute h-full bg-primary shadow-neon-primary transition-all duration-500" :style="{ width: `${progressPercentage}%` }"></div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="bg-background border border-border/50 shadow-2xl flex flex-col min-h-[580px] relative group/card">
                
                <!-- Scanline superior -->
                <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                
                <!-- Step Tabs -->
                <div class="flex border-b border-border/50 bg-background/80 backdrop-blur-sm z-20">
                    <button v-for="step in steps" :key="step.id" 
                            @click="currentStep = step.id"
                            class="flex-1 p-3 border-r border-border/50 transition-all text-[9px] font-mono font-black uppercase tracking-widest relative group/tab"
                            :class="currentStep === step.id 
                                ? 'text-primary border-b-2 border-b-primary bg-primary/5' 
                                : 'text-muted-foreground hover:text-primary hover:bg-primary/5'">
                        <span class="flex items-center justify-center gap-2">
                            <component :is="step.icon" :size="12" />
                            {{ step.title }}
                        </span>
                        <!-- Indicador de paso completado -->
                        <span v-if="currentStep > step.id" 
                              class="absolute -top-1 -right-1 w-2 h-2 bg-primary rounded-full animate-pulse"></span>
                    </button>
                </div>

                <div class="flex-1 p-6 md:p-8 bg-gradient-to-b from-transparent to-background/50 relative">
                    
                    <!-- Step 1: Datos Básicos -->
                    <div v-if="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-500">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Formulario -->
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                        <Terminal :size="12" /> // IDENTIFICADOR BASE
                                    </label>
                                    <input v-model="form.name" 
                                           type="text" 
                                           class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                           placeholder="INGRESAR NOMBRE" />
                                    <p v-if="form.errors.name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                        <AlertTriangle :size="10" /> {{ form.errors.name }}
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                        <MapPin :size="12" /> // CIUDAD DE OPERACIÓN
                                    </label>
                                    <select v-model="form.city" 
                                            class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                                        <option>LA PAZ</option>
                                        <option>EL ALTO</option>
                                        <option>COCHABAMBA</option>
                                        <option>SANTA CRUZ</option>
                                    </select>
                                </div>

                                <!-- Info Box -->
                                <div class="border border-primary/30 bg-primary/5 p-4 font-mono text-[9px] text-primary uppercase leading-relaxed relative">
                                    <Info :size="12" class="absolute top-2 right-2 text-primary/50" />
                                    [ SYS_INFO ]: ARRASTRE EL MARCADOR EN EL MAPA PARA GEOLOCALIZAR EL PUNTO DE DESPACHO.
                                    <!-- Scanline -->
                                    <div class="absolute bottom-0 left-0 w-full h-[1px] bg-primary/30"></div>
                                </div>
                            </div>

                            <!-- Mapa con componente unificado -->
                            <div class="lg:col-span-2 h-[350px] border border-primary/30 relative group/map">
                                <div class="absolute top-2 left-2 z-10 bg-background/90 border border-primary/30 px-2 py-1 text-[8px] font-mono text-primary">
                                    <Radar :size="10" class="inline mr-1" /> POSICIÓN_ACTUAL
                                </div>
                                <MapComponent 
                                    ref="mapRef"
                                    :markers="[{
                                        id: 'current',
                                        latitude: form.latitude,
                                        longitude: form.longitude,
                                        name: 'PUNTO_BASE'
                                    }]"
                                    :center="[form.latitude, form.longitude]"
                                    :zoom="15"
                                    height="350px"
                                    :draggable="true"
                                    @marker-drag="syncCenter"
                                    class="w-full h-full grayscale group-hover/map:grayscale-0 transition-all duration-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Geolocalización (Perímetro) -->
                    <div v-else-if="currentStep === 2" class="h-[480px] relative animate-in fade-in zoom-in duration-500">
                        <!-- Panel de control -->
                        <div class="absolute top-4 left-4 right-4 z-[500] flex flex-wrap justify-between items-center gap-2 bg-background/90 border border-primary/30 p-3 shadow-neon-primary font-mono backdrop-blur-sm">
                            <div>
                                <span class="text-[10px] text-primary font-black uppercase tracking-tighter flex items-center gap-1">
                                    <Target :size="12" /> PERÍMETRO DE ENTREGA
                                </span>
                                <p class="text-[8px] text-muted-foreground uppercase">
                                    VÉRTICES: {{ form.coverage_polygon.length }} // MÍNIMO 3
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="undoLastPoint" 
                                        :disabled="form.coverage_polygon.length === 0"
                                        class="px-3 py-1 border border-border text-[9px] font-mono hover:text-primary hover:border-primary transition-all disabled:opacity-30 disabled:pointer-events-none">
                                    REVERTIR
                                </button>
                                <button type="button" @click="clearPolygon" 
                                        :disabled="form.coverage_polygon.length === 0"
                                        class="px-3 py-1 border border-destructive text-destructive text-[9px] font-mono hover:bg-destructive hover:text-white transition-all disabled:opacity-30 disabled:pointer-events-none">
                                    PURGAR
                                </button>
                                <button type="button" @click="showCoverageHelp = !showCoverageHelp"
                                        class="px-3 py-1 border border-primary/30 text-primary text-[9px] font-mono">
                                    <Eye :size="12" class="inline" />
                                </button>
                            </div>
                        </div>

                        <!-- Help tooltip -->
                        <div v-if="showCoverageHelp" 
                             class="absolute top-20 left-1/2 -translate-x-1/2 z-[600] bg-background border border-primary p-4 shadow-neon-primary max-w-md">
                            <p class="text-[10px] font-mono text-primary mb-2">// INSTRUCCIONES</p>
                            <p class="text-[9px] font-mono text-muted-foreground">
                                1. HAGA CLIC EN EL MAPA PARA AÑADIR VÉRTICES<br/>
                                2. MÍNIMO 3 PUNTOS PARA FORMAR UN POLÍGONO<br/>
                                3. EL ÁREA DEFINIRÁ LA ZONA DE COBERTURA
                            </p>
                            <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 rotate-45 bg-primary"></div>
                        </div>

                        <!-- Mapa para polígono -->
                        <div class="h-full w-full border border-primary/30 relative">
                            <MapComponent 
                                ref="mapRef"
                                :markers="[{
                                    id: 'base',
                                    latitude: form.latitude,
                                    longitude: form.longitude,
                                    name: 'BASE'
                                }]"
                                :polygon="form.coverage_polygon"
                                :center="[form.latitude, form.longitude]"
                                :zoom="zoom"
                                height="480px"
                                :clickable="true"
                                @map-click="onMapClick"
                                class="w-full h-full grayscale"
                            />
                            
                            <!-- Overlay de escaneo -->
                            <div class="absolute inset-0 pointer-events-none">
                                <div class="absolute top-0 left-0 w-full h-[2px] bg-primary/30 animate-scanline"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Estado Red -->
                    <div v-else-if="currentStep === 3" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500 font-mono">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Phone :size="12" /> // CANAL TELEFÓNICO
                                </label>
                                <input v-model="form.phone" 
                                       type="text" 
                                       class="w-full bg-background border border-border/50 px-4 py-3 text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                       placeholder="70012345" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <MapPin :size="12" /> // REFERENCIA DE DIRECCIÓN
                                </label>
                                <textarea v-model="form.address" 
                                          rows="2" 
                                          class="w-full bg-background border border-border/50 px-4 py-3 text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all resize-none"
                                          placeholder="DIRECCIÓN COMPLETA"></textarea>
                            </div>
                        </div>

                        <!-- Toggles -->
                        <div class="pt-6 space-y-4 border-t border-primary/30">
                            <!-- Active Toggle -->
                            <div @click="form.is_active = !form.is_active" 
                                 class="flex items-center justify-between p-4 border cursor-pointer select-none bg-background transition-all group/toggle"
                                 :class="form.is_active ? 'border-primary shadow-neon-primary' : 'border-border/50 hover:border-primary/30'">
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-xs uppercase flex items-center gap-2"
                                          :class="form.is_active ? 'text-primary' : 'text-muted-foreground'">
                                        <component :is="form.is_active ? Wifi : WifiOff" :size="14" />
                                        {{ form.is_active ? '[ SYS_ONLINE ]' : '[ SYS_OFFLINE ]' }}
                                    </span>
                                    <span class="text-[8px] font-mono text-muted-foreground uppercase mt-1">
                                        TRANSMISIÓN OPERATIVA DE INVENTARIO
                                    </span>
                                </div>
                                <div class="w-12 h-6 border border-border/50 relative flex items-center p-1">
                                    <div class="w-4 h-4 transition-all duration-300" 
                                         :class="form.is_active ? 'translate-x-6 bg-primary shadow-neon-primary' : 'translate-x-0 bg-muted-foreground'"></div>
                                </div>
                            </div>

                            <!-- Master Toggle -->
                            <div @click="form.is_default = !form.is_default" 
                                 class="flex items-center justify-between p-4 border cursor-pointer select-none bg-background transition-all group/toggle"
                                 :class="form.is_default ? 'border-secondary shadow-neon-secondary' : 'border-border/50 hover:border-primary/30'">
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-xs uppercase flex items-center gap-2"
                                          :class="form.is_default ? 'text-secondary' : 'text-muted-foreground'">
                                        <GitBranch :size="14" />
                                        {{ form.is_default ? '[ NODE_MASTER ]' : '[ NODE_SLAVE ]' }}
                                    </span>
                                    <span class="text-[8px] font-mono text-muted-foreground uppercase mt-1">
                                        SUCURSAL PRINCIPAL PARA TRÁFICO GLOBAL
                                    </span>
                                </div>
                                <div class="w-12 h-6 border border-border/50 relative flex items-center p-1">
                                    <div class="w-4 h-4 transition-all duration-300" 
                                         :class="form.is_default ? 'translate-x-6 bg-secondary shadow-neon-secondary' : 'translate-x-0 bg-muted-foreground'"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Logística -->
                    <div v-else-if="currentStep === 4" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500 font-mono">
                        <!-- Info Box -->
                        <div class="border border-primary/30 bg-primary/5 p-4 relative overflow-hidden">
                            <div class="flex gap-4 items-center">
                                <Calculator :size="24" class="text-primary icon-glow" />
                                <p class="text-[9px] font-mono text-foreground uppercase tracking-widest leading-relaxed">
                                    CALIBRACIÓN DE PARÁMETROS FINANCIEROS. ESTOS VALORES AFECTAN DIRECTAMENTE EL CÁLCULO DEL CHECKOUT.
                                </p>
                            </div>
                            <!-- Scanline -->
                            <div class="absolute bottom-0 left-0 w-full h-[1px] bg-primary/30"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[8px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <HardDrive :size="12" /> // BASE DELIVERY (BS)
                                </label>
                                <input v-model="form.delivery_base_fee" 
                                       type="number" step="0.01" 
                                       class="w-full bg-background border border-border/50 px-4 py-3 text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                            </div>
                            
                            <div>
                                <label class="text-[8px] font-mono font-bold text-warning uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Activity :size="12" /> // SURGE MULTIPLIER
                                </label>
                                <input v-model="form.surge_multiplier" 
                                       type="number" step="0.1" min="1" 
                                       class="w-full bg-background border border-warning/30 text-warning px-4 py-3 text-sm focus:border-warning focus:shadow-[0_0_20px_rgba(255,170,0,0.3)] outline-none transition-all" />
                            </div>

                            <div>
                                <label class="text-[8px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Cpu :size="12" /> // FEE PLATAFORMA (%)
                                </label>
                                <input v-model="form.base_service_fee_percentage" 
                                       type="number" step="0.01" 
                                       class="w-full bg-background border border-border/50 px-4 py-3 text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                            </div>

                            <div>
                                <label class="text-[8px] font-mono font-bold text-destructive uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <AlertTriangle :size="12" /> // MULTIA ORDEN PEQUEÑA (BS)
                                </label>
                                <input v-model="form.small_order_fee" 
                                       type="number" step="0.01" 
                                       class="w-full bg-background border border-destructive/30 text-destructive px-4 py-3 text-sm focus:border-destructive focus:shadow-[0_0_20px_rgba(255,0,0,0.3)] outline-none transition-all" />
                            </div>

                            <div>
                                <label class="text-[8px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Navigation :size="12" /> // PRECIO POR KM (BS)
                                </label>
                                <input v-model="form.delivery_price_per_km" 
                                       type="number" step="0.01" 
                                       class="w-full bg-background border border-border/50 px-4 py-3 text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                            </div>

                            <div>
                                <label class="text-[8px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Layers :size="12" /> // PEDIDO MÍNIMO (BS)
                                </label>
                                <input v-model="form.min_order_amount" 
                                       type="number" step="0.01" 
                                       class="w-full bg-background border border-border/50 px-4 py-3 text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="p-4 border-t border-border/50 bg-background/80 backdrop-blur-sm flex justify-between font-mono z-20">
                    <button type="button" @click="currentStep--" :disabled="currentStep === 1" 
                            class="px-6 py-2 border border-border text-[10px] font-mono font-bold uppercase hover:border-primary hover:text-primary transition-all disabled:opacity-30 disabled:pointer-events-none relative group/prev">
                        <span class="flex items-center gap-2">
                            <ArrowLeft :size="14" /> REBOBINAR
                        </span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                    </button>

                    <button v-if="currentStep < 4" type="button" @click="nextStep" 
                            class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/next overflow-hidden">
                        <span class="flex items-center gap-2 relative z-10">
                            AVANZAR <ArrowRight :size="14" />
                        </span>
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/next:translate-y-0 transition-transform duration-500"></span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                    </button>

                    <button v-else @click="submit" :disabled="form.processing"
                            class="px-10 py-2 border-2 border-primary text-primary hover:bg-primary hover:text-primary-foreground transition-all text-[10px] font-mono font-black shadow-neon-primary uppercase relative group/submit overflow-hidden">
                        <span v-if="form.processing" class="flex items-center gap-2">
                            <RotateCcw :size="14" class="animate-spin" />
                            COMMIT...
                        </span>
                        <span v-else class="flex items-center gap-2 relative z-10">
                            <Save :size="14" /> EJECUTAR RECALIBRACIÓN
                        </span>
                        <span class="absolute inset-0 bg-primary/10 translate-y-full group-hover/submit:translate-y-0 transition-transform duration-500"></span>
                    </button>
                </div>
            </div>

            <!-- Session ID -->
            <div class="mt-4 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // {{ nodeCode }}_EDIT_{{ String(currentStep).padStart(2, '0') }} // {{ new Date().toISOString().slice(0,10) }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Animaciones */
@keyframes scanline {
    0% { transform: translateY(-100%); }
    100% { transform: translateY(1000%); }
}

.animate-scanline {
    animation: scanline 8s linear infinite;
}

/* Efecto glitch */
.glitch-text {
    position: relative;
    animation: glitch-skew 4s infinite linear alternate-reverse;
}

.glitch-text::before,
.glitch-text::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.8;
}

.glitch-text::before {
    color: #0ff;
    z-index: -1;
    animation: glitch-anim-1 0.4s infinite linear alternate-reverse;
}

.glitch-text::after {
    color: #f0f;
    z-index: -2;
    animation: glitch-anim-2 0.4s infinite linear alternate-reverse;
}

@keyframes glitch-skew {
    0% { transform: skew(0deg); }
    20% { transform: skew(0deg); }
    21% { transform: skew(2deg); }
    22% { transform: skew(0deg); }
    80% { transform: skew(0deg); }
    81% { transform: skew(-2deg); }
    82% { transform: skew(0deg); }
    100% { transform: skew(0deg); }
}

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); }
    20% { clip-path: inset(50% 0 10% 0); }
    40% { clip-path: inset(10% 0 60% 0); }
    60% { clip-path: inset(80% 0 5% 0); }
    80% { clip-path: inset(30% 0 40% 0); }
    100% { clip-path: inset(40% 0 20% 0); }
}

@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); }
    20% { clip-path: inset(20% 0 50% 0); }
    40% { clip-path: inset(70% 0 5% 0); }
    60% { clip-path: inset(10% 0 70% 0); }
    80% { clip-path: inset(40% 0 30% 0); }
    100% { clip-path: inset(30% 0 40% 0); }
}

/* Sombras neón */
.shadow-neon-primary {
    box-shadow: 0 0 20px hsl(var(--primary) / 0.3);
}

.shadow-neon-secondary {
    box-shadow: 0 0 20px hsl(var(--secondary) / 0.3);
}

/* Efecto de brillo para íconos */
.icon-glow {
    filter: drop-shadow(0 0 4px currentColor);
    transition: filter 0.3s ease;
}

.icon-glow:hover {
    filter: drop-shadow(0 0 8px currentColor);
}

/* Colores personalizados */
.text-warning { color: #ffaa00; }
.bg-warning { background-color: #ffaa00; }
.border-warning { border-color: #ffaa00; }
</style>