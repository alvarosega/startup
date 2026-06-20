<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, nextTick, watch } from 'vue';
import { 
    Store, MapPin, Phone, Navigation, Save, 
    ArrowLeft, ArrowRight, Trash2, RotateCcw, CheckCircle, 
    Info, Layers, Calculator, Cpu, Terminal, Zap,
    Wifi, WifiOff, GitBranch, Activity, HardDrive,
    Crosshair, Radar, Eye, AlertTriangle
} from 'lucide-vue-next';

// Importamos nuestro componente de mapa unificado
//import MapComponent from '@/Components/Base/MapComponent.vue';

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

const onMarkerDrag = ({ lat, lng }) => {
    form.latitude = lat;
    form.longitude = lng;
};

// Asegúrate de que esté así en tu <script setup>
watch(currentStep, async () => {
    await nextTick(); // Espera a que el DOM se actualice (v-if)
    mapRef.value?.invalidateSize(); // Recalibra el contenedor de Leaflet
});
const onMapClick = (event) => {
    if (currentStep.value === 2 && event.latlng) {
        form.coverage_polygon = [...form.coverage_polygon, [event.latlng.lat, event.latlng.lng]];
    }
};

const undoLastPoint = () => form.coverage_polygon = form.coverage_polygon.slice(0, -1);
const clearPolygon = () => form.coverage_polygon = [];

// --- NAVEGACIÓN WIZARD ---
const nextStep = () => {
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
        form.setError('name', '// IDENTIFICADOR REQUERIDO');
        currentStep.value = 1;
        return;
    }
    
    form.post(route('admin.branches.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors) => {
            console.error('VALIDATION_ERROR:', errors);
            jumpToStepWithError(errors);
        }
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);

// Código temporal para nuevo nodo
const tempCode = computed(() => {
    return `NODE_NEW_${String(Math.floor(Math.random() * 1000)).padStart(3, '0')}`;
});
</script>

<template>
    <AdminLayout>
        
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2 pb-4 border-b border-primary/30 relative group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 border-2 border-primary flex items-center justify-center shadow-neon-primary bg-background/50">
                            <Store :size="24" class="icon-glow text-primary" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-display font-black text-primary uppercase tracking-widest glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                                data-text="INICIALIZAR NODO">
                                INICIALIZAR NODO
                            </h1>
                            <p class="text-[10px] font-mono text-muted-foreground font-bold tracking-widest uppercase mt-1 flex items-center gap-2">
                                <Cpu :size="12" class="text-primary animate-pulse" />
                                {{ tempCode }} // CONFIGURACIÓN TÁCTICA
                                <Terminal :size="12" class="text-primary animate-pulse" />
                            </p>
                        </div>
                    </div>
                </div>
                
                <Link :href="route('admin.branches.index')" 
                      class="px-4 py-2 border border-destructive/50 text-destructive font-mono text-xs hover:bg-destructive hover:text-destructive-foreground transition-all relative group/abort">
                    <span class="flex items-center gap-2">
                        <AlertTriangle :size="14" /> ABORTAR
                    </span>
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-destructive opacity-0 group-hover/abort:opacity-100"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-destructive opacity-0 group-hover/abort:opacity-100"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-destructive opacity-0 group-hover/abort:opacity-100"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-destructive opacity-0 group-hover/abort:opacity-100"></span>
                </Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto py-6">
            
            <!-- Progress Bar -->
            <div class="mb-10">
                <div class="flex justify-between items-end mb-2">
                    <div class="flex items-center gap-3">
                        <div class="px-2 py-1 bg-primary/10 border border-primary/30 text-primary font-mono font-black text-sm shadow-neon-primary">
                            SEQ_{{ String(currentStep).padStart(2, '0') }}
                        </div>
                        <span class="text-sm font-display font-black uppercase tracking-widest text-foreground glitch-text">
                            {{ steps[currentStep - 1].title }}
                        </span>
                    </div>
                    <div class="text-[10px] font-mono text-muted-foreground font-bold tracking-widest uppercase">
                        FASE {{ currentStep }}/{{ steps.length }} // {{ steps[currentStep - 1].code }}
                    </div>
                </div>
                
                <div class="h-[2px] bg-border w-full relative">
                    <div class="absolute top-0 left-0 h-full bg-primary shadow-neon-primary transition-all duration-500 ease-out"
                         :style="{ width: `${progressPercentage}%` }">
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="bg-background border border-border/50 relative shadow-[0_0_30px_rgba(0,0,0,0.5)] group/card">
                
                <!-- Scanline superior -->
                <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                
                <!-- Step Tabs -->
                <div class="flex items-center justify-between md:justify-start border-b border-border/50 bg-background/80 backdrop-blur-sm">
                    <div v-for="step in steps" :key="step.id" 
                         class="flex-1 md:flex-none flex items-center justify-center p-3 border-r border-border/50 cursor-pointer transition-all duration-300 hover:bg-primary/5 relative group/tab"
                         :class="{ 
                             'bg-primary/10 border-b-2 border-b-primary shadow-[inset_0_-5px_15px_hsl(var(--primary)/0.1)]': currentStep === step.id,
                             'opacity-50': currentStep < step.id && currentStep !== step.id
                         }"
                         @click="currentStep >= step.id ? currentStep = step.id : null">
                        
                        <div class="flex items-center gap-2">
                            <CheckCircle v-if="currentStep > step.id" :size="16" class="text-cyan-500" />
                            <span v-else class="font-mono font-bold text-[10px]" :class="currentStep === step.id ? 'text-primary' : 'text-muted-foreground'">
                                [ {{ step.code }} ]
                            </span>
                            <span class="hidden md:inline-block text-[10px] font-display font-black uppercase tracking-widest"
                                  :class="currentStep >= step.id ? 'text-foreground' : 'text-muted-foreground'">
                                {{ step.title }}
                            </span>
                        </div>
                        
                        <!-- Tooltip con código -->
                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 text-[8px] font-mono text-primary opacity-0 group-hover/tab:opacity-100 transition-opacity whitespace-nowrap">
                            {{ step.code }}
                        </span>
                    </div>
                </div>

                <div class="p-6 md:p-8 bg-gradient-to-b from-transparent to-background/50 min-h-[450px] relative">
                    
                    <!-- Step 1: Datos Básicos -->
                    <div v-if="currentStep === 1" class="animate-in fade-in slide-in-from-left-4 duration-500">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-mono font-bold uppercase tracking-widest text-primary mb-2 flex items-center gap-1">
                                        <Terminal :size="12" /> // IDENTIFICADOR OPERATIVO *
                                    </label>
                                    <input v-model="form.name" 
                                           type="text" 
                                           class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                           placeholder="SECTOR_SUR" />
                                    <p v-if="form.errors.name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                        <AlertTriangle :size="10" /> {{ form.errors.name }}
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-[10px] font-mono font-bold uppercase tracking-widest text-primary mb-2 flex items-center gap-1">
                                        <MapPin :size="12" /> // NODO PRINCIPAL (CIUDAD) *
                                    </label>
                                    <select v-model="form.city" 
                                            class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                                        <option>LA PAZ</option>
                                        <option>EL ALTO</option>
                                        <option>COCHABAMBA</option>
                                        <option>SANTA CRUZ</option>
                                        <option>TARIJA</option>
                                    </select>
                                </div>
                                
                                <!-- Info Box -->
                                <div class="border border-primary/30 bg-primary/5 p-4 flex items-start gap-3 relative overflow-hidden">
                                    <Navigation :size="16" class="text-primary mt-0.5 shrink-0 icon-glow" />
                                    <span class="text-[10px] font-mono text-foreground uppercase tracking-wider leading-relaxed">
                                        TRASLADA EL VECTOR DE POSICIÓN EN EL MAPA PARA ANCLAR LAS COORDENADAS FÍSICAS DE LA BASE.
                                    </span>
                                    <div class="absolute bottom-0 left-0 w-full h-[1px] bg-primary/30"></div>
                                </div>
                            </div>
                            
                            <!-- Mapa con componente unificado -->
                            <div class="lg:col-span-2">
                                <div class="border border-primary/30 h-[400px] relative shadow-[0_0_25px_hsl(var(--primary)/0.2)] bg-background group/map">
                                    <!-- Esquinas decorativas -->
                                    <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/50 z-10 pointer-events-none"></div>
                                    <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/50 z-10 pointer-events-none"></div>
                                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/50 z-10 pointer-events-none"></div>
                                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/50 z-10 pointer-events-none"></div>
                                    
                                    <!-- Overlay de escaneo -->
                                    <div class="absolute inset-0 pointer-events-none opacity-0 group-hover/map:opacity-100 transition-opacity">
                                        <div class="absolute top-0 left-0 w-full h-[2px] bg-primary/30 animate-scanline"></div>
                                    </div>
                                    
                                    <MapComponent 
                                        ref="mapRef"
                                        :markers="[{
                                            id: 'base',
                                            latitude: form.latitude,
                                            longitude: form.longitude,
                                            name: form.name || 'TARGET_LOC'
                                        }]"
                                        :center="[form.latitude, form.longitude]"
                                        v-model:zoom="zoom" 
                                        height="400px"
                                        :draggable="true"
                                        @marker-drag="onMarkerDrag"
                                        class="w-full h-full grayscale group-hover/map:grayscale-0 transition-all duration-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Geolocalización (Perímetro) -->
                    <div v-else-if="currentStep === 2" class="h-[450px] relative animate-in fade-in zoom-in duration-500">
                        
                        <!-- Panel de control -->
                        <div class="absolute top-0 left-0 w-full z-[500] bg-background/90 border-b border-primary/30 flex flex-col md:flex-row justify-between items-center p-3 shadow-neon-primary backdrop-blur-sm">
                            <div>
                                <h2 class="font-display font-black text-primary text-sm uppercase tracking-widest flex items-center gap-2">
                                    <Radar :size="16" class="icon-glow" /> PERÍMETRO OPERATIVO
                                </h2>
                                <p class="text-[8px] font-mono text-muted-foreground uppercase tracking-widest mt-1">
                                    VECTORES REGISTRADOS: <span class="text-primary">{{ form.coverage_polygon.length }}</span> // MÍNIMO 3
                                </p>
                            </div>
                            <div class="flex gap-2 w-full md:w-auto mt-3 md:mt-0">
                                <button type="button" @click="undoLastPoint" 
                                        :disabled="form.coverage_polygon.length === 0"
                                        class="px-4 py-2 border border-border text-[10px] font-mono hover:text-primary hover:border-primary transition-all disabled:opacity-30 disabled:pointer-events-none relative group/undo">
                                    <RotateCcw :size="12" class="inline mr-1" /> REVERTIR
                                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/undo:opacity-100"></span>
                                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/undo:opacity-100"></span>
                                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/undo:opacity-100"></span>
                                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/undo:opacity-100"></span>
                                </button>
                                <button type="button" @click="clearPolygon" 
                                        :disabled="form.coverage_polygon.length === 0"
                                        class="px-4 py-2 border border-destructive/50 text-destructive text-[10px] font-mono hover:bg-destructive hover:text-white transition-all disabled:opacity-30 disabled:pointer-events-none relative group/clear">
                                    <Trash2 :size="12" class="inline mr-1" /> PURGAR
                                </button>
                                <button type="button" @click="showCoverageHelp = !showCoverageHelp"
                                        class="px-3 py-2 border border-primary/30 text-primary text-[10px] font-mono">
                                    <Eye :size="12" class="inline" />
                                </button>
                            </div>
                        </div>

                        <!-- Help tooltip -->
                        <div v-if="showCoverageHelp" 
                             class="absolute top-20 left-1/2 -translate-x-1/2 z-[600] bg-background border border-primary p-4 shadow-neon-primary max-w-md">
                            <p class="text-[10px] font-mono text-primary mb-2">// INSTRUCCIONES</p>
                            <p class="text-[8px] font-mono text-muted-foreground">
                                1. HAGA CLIC EN EL MAPA PARA AÑADIR VÉRTICES<br/>
                                2. MÍNIMO 3 PUNTOS PARA FORMAR UN POLÍGONO<br/>
                                3. EL ÁREA DEFINIRÁ LA ZONA DE COBERTURA
                            </p>
                            <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 rotate-45 bg-primary"></div>
                        </div>

                        <!-- Mapa para polígono -->
                        <div class="h-full border border-primary/30 bg-background pt-[70px] md:pt-[60px]">
                            <MapComponent 
                                ref="mapRef"
                                :markers="[{
                                    id: 'base',
                                    latitude: form.latitude,
                                    longitude: form.longitude,
                                    name: 'BASE_ALPHA'
                                }]"
                                :polygon="form.coverage_polygon"
                                :center="[form.latitude, form.longitude]"
                                v-model:zoom="zoom"
                                height="390px"
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
                    <div v-else-if="currentStep === 3" class="animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="max-w-2xl mx-auto w-full space-y-6">
                            
                            <div>
                                <label class="block text-[10px] font-mono font-bold uppercase tracking-widest text-primary mb-2 flex items-center gap-1">
                                    <Phone :size="12" /> // CANAL DE COMUNICACIÓN
                                </label>
                                <input v-model="form.phone" 
                                       type="text" 
                                       class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                       placeholder="70012345" />
                            </div>

                            <div>
                                <label class="block text-[10px] font-mono font-bold uppercase tracking-widest text-primary mb-2 flex items-center gap-1">
                                    <MapPin :size="12" /> // COORDENADAS FÍSICAS
                                </label>
                                <textarea v-model="form.address" 
                                          rows="3" 
                                          class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all resize-none"
                                          placeholder="DIRECCIÓN COMPLETA DEL NODO..."></textarea>
                            </div>

                            <!-- Toggles -->
                            <div class="pt-4 space-y-4">
                                
                                <!-- Active Toggle -->
                                <div @click="form.is_active = !form.is_active" 
                                     class="flex items-center justify-between w-full p-4 border cursor-pointer select-none bg-background transition-all group/toggle"
                                     :class="form.is_active ? 'border-primary shadow-neon-primary' : 'border-border/50 hover:border-primary/30'">
                                    <div class="flex flex-col">
                                        <span class="font-mono font-bold text-sm tracking-widest uppercase flex items-center gap-2"
                                              :class="form.is_active ? 'text-primary' : 'text-muted-foreground'">
                                            <component :is="form.is_active ? Wifi : WifiOff" :size="16" />
                                            {{ form.is_active ? '[ SYS_ONLINE ]' : '[ SYS_OFFLINE ]' }}
                                        </span>
                                        <span class="text-[8px] font-mono text-muted-foreground uppercase tracking-widest mt-1">
                                            ESTADO DE TRANSMISIÓN DE LA BASE
                                        </span>
                                    </div>
                                    <div class="w-12 h-6 border border-border/50 flex items-center p-1 transition-all"
                                         :class="form.is_active ? 'justify-end border-primary' : 'justify-start'">
                                        <div class="w-4 h-4 transition-all"
                                             :class="form.is_active ? 'bg-primary shadow-neon-primary' : 'bg-muted-foreground'"></div>
                                    </div>
                                </div>

                                <!-- Master Toggle -->
                                <div @click="form.is_default = !form.is_default" 
                                     class="flex items-center justify-between w-full p-4 border cursor-pointer select-none bg-background transition-all group/toggle"
                                     :class="form.is_default ? 'border-secondary shadow-neon-secondary' : 'border-border/50 hover:border-secondary/30'">
                                    <div class="flex flex-col">
                                        <span class="font-mono font-bold text-sm tracking-widest uppercase flex items-center gap-2"
                                              :class="form.is_default ? 'text-secondary' : 'text-muted-foreground'">
                                            <GitBranch :size="16" />
                                            {{ form.is_default ? '[ NODE_MASTER ]' : '[ NODE_SLAVE ]' }}
                                        </span>
                                        <span class="text-[8px] font-mono text-muted-foreground uppercase tracking-widest mt-1">
                                            PROTOCOLO DE ASIGNACIÓN POR DEFECTO
                                        </span>
                                    </div>
                                    <div class="w-12 h-6 border border-border/50 flex items-center p-1 transition-all"
                                         :class="form.is_default ? 'justify-end border-secondary' : 'justify-start'">
                                        <div class="w-4 h-4 transition-all"
                                             :class="form.is_default ? 'bg-secondary shadow-neon-secondary' : 'bg-muted-foreground'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Logística -->
                    <div v-else-if="currentStep === 4" class="animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="max-w-3xl mx-auto w-full space-y-6">
                            
                            <!-- Info Box -->
                            <div class="border border-primary/30 bg-primary/5 p-4 flex gap-4 shadow-neon-primary relative overflow-hidden">
                                <Calculator :size="24" class="text-primary shrink-0 icon-glow mt-1"/>
                                <div>
                                    <h3 class="font-display font-black text-sm text-primary uppercase tracking-widest glitch-text">ALGORITMO DE TARIFAS</h3>
                                    <p class="text-[8px] font-mono text-muted-foreground mt-1 uppercase tracking-widest">
                                        CALIBRACIÓN DE LA MATRIZ DE PRECIOS DINÁMICOS
                                    </p>
                                </div>
                                <div class="absolute bottom-0 left-0 w-full h-[1px] bg-primary/30"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[8px] font-mono font-bold uppercase tracking-widest text-primary mb-2 flex items-center gap-1">
                                        <HardDrive :size="12" /> // TARIFA BASE (BS)
                                    </label>
                                    <input v-model="form.delivery_base_fee" 
                                           type="number" step="0.5" min="0" 
                                           class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                                </div>
                                
                                <div>
                                    <label class="block text-[8px] font-mono font-bold uppercase tracking-widest text-primary mb-2 flex items-center gap-1">
                                        <Navigation :size="12" /> // COSTO X KM (BS)
                                    </label>
                                    <input v-model="form.delivery_price_per_km" 
                                           type="number" step="0.1" min="0" 
                                           class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                                </div>
                                
                                <div>
                                    <label class="block text-[8px] font-mono font-bold uppercase tracking-widest text-warning mb-2 flex items-center gap-1">
                                        <Activity :size="12" /> // MULTIPLICADOR DEMANDA
                                    </label>
                                    <input v-model="form.surge_multiplier" 
                                           type="number" step="0.1" min="1.0" 
                                           class="w-full bg-background border border-warning/30 text-warning px-4 py-3 font-mono text-sm focus:border-warning focus:shadow-[0_0_20px_rgba(255,170,0,0.3)] outline-none transition-all" />
                                    <span class="text-[7px] font-mono text-muted-foreground mt-1 block uppercase">NORMAL = 1.0 // SATURACIÓN = 1.5+</span>
                                </div>
                                
                                <div>
                                    <label class="block text-[8px] font-mono font-bold uppercase tracking-widest text-primary mb-2 flex items-center gap-1">
                                        <Layers :size="12" /> // UMBRAL TICKET BAJO (BS)
                                    </label>
                                    <input v-model="form.min_order_amount" 
                                           type="number" step="1" min="0" 
                                           class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                                    <span class="text-[7px] font-mono text-muted-foreground mt-1 block uppercase">SUB TOTAL MÍNIMO EVADIENDO PENALIZACIÓN</span>
                                </div>
                                
                                <div>
                                    <label class="block text-[8px] font-mono font-bold uppercase tracking-widest text-destructive mb-2 flex items-center gap-1">
                                        <AlertTriangle :size="12" /> // MULTA TICKET BAJO (BS)
                                    </label>
                                    <input v-model="form.small_order_fee" 
                                           type="number" step="0.5" min="0" 
                                           class="w-full bg-background border border-destructive/30 text-destructive px-4 py-3 font-mono text-sm focus:border-destructive focus:shadow-[0_0_20px_rgba(255,0,0,0.3)] outline-none transition-all" />
                                    <span class="text-[7px] font-mono text-muted-foreground mt-1 block uppercase">PENALIZACIÓN AL USUARIO</span>
                                </div>
                                
                                <div>
                                    <label class="block text-[8px] font-mono font-bold uppercase tracking-widest text-secondary mb-2 flex items-center gap-1">
                                        <Cpu :size="12" /> // FEE PLATAFORMA (%)
                                    </label>
                                    <input v-model="form.base_service_fee_percentage" 
                                           type="number" step="0.1" min="0" max="100" 
                                           class="w-full bg-background border border-secondary/30 text-secondary px-4 py-3 font-mono text-sm focus:border-secondary focus:shadow-neon-secondary outline-none transition-all" />
                                    <span class="text-[7px] font-mono text-muted-foreground mt-1 block uppercase">RETENCIÓN DE COSTOS DE OPERACIÓN</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="border-t border-border/50 bg-background/80 backdrop-blur-sm p-4 flex justify-between items-center relative z-10">
                    <button type="button" @click="prevStep" :disabled="currentStep === 1" 
                            class="px-6 py-2 border border-border text-[10px] font-mono font-bold uppercase hover:border-primary hover:text-primary transition-all disabled:opacity-30 disabled:pointer-events-none relative group/prev">
                        <span class="flex items-center gap-2">
                            <ArrowLeft :size="14" /> REBOBINAR
                        </span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                    </button>

                    <button v-if="currentStep < steps.length" type="button" @click="nextStep" 
                            class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/next overflow-hidden">
                        <span class="flex items-center gap-2 relative z-10">
                            AVANZAR <ArrowRight :size="14" class="group-hover/next:translate-x-1 transition-transform" />
                        </span>
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/next:translate-y-0 transition-transform duration-500"></span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                    </button>
                    
                    <button v-else @click="submit" :disabled="form.processing"
                            class="px-8 py-2 bg-destructive/10 text-destructive border border-destructive/50 text-[10px] font-mono font-black uppercase hover:bg-destructive hover:text-destructive-foreground transition-all relative group/submit overflow-hidden">
                        <span v-if="form.processing" class="flex items-center gap-2">
                            <RotateCcw :size="14" class="animate-spin" /> EJECUTANDO...
                        </span>
                        <span v-else class="flex items-center gap-2 relative z-10">
                            <Save :size="14" /> INICIALIZAR SISTEMA
                        </span>
                        <span class="absolute inset-0 bg-destructive/20 translate-y-full group-hover/submit:translate-y-0 transition-transform duration-500"></span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-destructive"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-destructive"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-destructive"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-destructive"></span>
                    </button>
                </div>
            </div>

            <!-- Session ID -->
            <div class="mt-4 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // {{ tempCode }}_CREATE_{{ String(currentStep).padStart(2, '0') }} // {{ new Date().toISOString().slice(0,10) }}
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

.shadow-neon-destructive {
    box-shadow: 0 0 20px hsl(var(--destructive) / 0.3);
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