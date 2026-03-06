<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StepProgress from '@/Components/StepProgress.vue';
import CategoryTypeSelector from '@/Components/CategoryTypeSelector.vue';
import ImageUploader from '@/Components/ImageUploader.vue';
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { 
    FolderTree, Image as ImageIcon, Settings, 
    ArrowRight, ArrowLeft, Save, AlertCircle, 
    Hash, FileText, Plus, Trash2, Layers,
    Cpu, Terminal, Wifi, Zap, Tag, GitBranch,
    AlertTriangle, Eye, EyeOff, Shield, Info, Radar, CheckCircle,
} from 'lucide-vue-next';

const props = defineProps({
    parents: Array,
    zones: Array // AÑADIR: Para el selector de zona operativa
});

const currentStep = ref(1);
const categoryType = ref('parent'); 

const steps = [
    { id: 1, title: 'JERARQUÍA', code: 'SEC_01', icon: FolderTree, fields: ['name', 'parent_id', 'market_zone_id', 'children'] },
    { id: 2, title: 'CONTENIDO', code: 'SEC_02', icon: ImageIcon, fields: ['image', 'icon', 'description'] },
    { id: 3, title: 'AJUSTES', code: 'SEC_03', icon: Settings, fields: ['tax_classification', 'seo_title', 'seo_description', 'bg_color'] },
];

const form = useForm({
    market_zone_id: '', // AÑADIR: Mandatorio por DB
    parent_id: '', 
    name: '',
    external_code: '',
    description: '',
    image: null,
    icon: null, // AÑADIR: Según migración
    bg_color: '#3b82f6', // AÑADIR: Valor por defecto (azul primary)
    seo_title: '',
    seo_description: '', // AÑADIR: Según migración
    tax_classification: '',
    requires_age_check: false,
    is_active: true,
    is_featured: false,
    children: [] 
});

// Código temporal para nueva categoría
const tempCode = computed(() => {
    return `CAT_NEW_${String(Math.floor(Math.random() * 1000)).padStart(3, '0')}`;
});

// --- ACCIONES DE SUBCATEGORÍAS ---
const addChild = () => {
    form.children.push({ name: '', external_code: '' });
};

const removeChild = (index) => {
    form.children.splice(index, 1);
};
// Sincronizar zona si se elige un padre (The Law: Integridad)
watch(() => form.parent_id, (newParentId) => {
    if (newParentId) {
        const parent = props.parents.find(p => p.id === newParentId);
        if (parent && parent.market_zone_id) {
            form.market_zone_id = parent.market_zone_id;
        }
    }
});
// Resetear estados al cambiar tipo
watch(categoryType, (val) => {
    if (val === 'parent') {
        form.parent_id = '';
    } else {
        form.children = [];
    }
    form.clearErrors();
});

const nextStep = () => {
    form.clearErrors();
    
    if (currentStep.value === 1) {
        if (!form.name.trim()) {
            form.setError('name', '// NOMBRE REQUERIDO');
            return;
        }
        if (categoryType.value === 'child' && !form.parent_id) {
            form.setError('parent_id', '// SELECCIONE PADRE');
            return;
        }
    }
    
    if (currentStep.value < steps.length) {
        currentStep.value++;
    } else {
        const card = document.getElementById('wizard-card');
        card?.classList.add('shake');
        setTimeout(() => card?.classList.remove('shake'), 400);
    }
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
    form.post(route('admin.categories.store'), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            const stepWithError = steps.find(step => step.fields.some(f => Object.keys(errors).some(key => key.startsWith(f))));
            if (stepWithError) currentStep.value = stepWithError.id;
            
            const card = document.getElementById('wizard-card');
            card?.classList.add('shake');
            setTimeout(() => card?.classList.remove('shake'), 400);
        }
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto pb-12 px-4 md:px-0">
            
            <!-- Header -->
            <div class="mb-8 relative group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10 flex justify-between items-end">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-[8px] font-mono border border-primary/30 bg-primary/5 text-primary px-2 py-1">
                                {{ tempCode }}
                            </span>
                            <span class="text-[8px] font-mono text-cyan-500 border border-cyan-500/30 bg-cyan-500/10 px-2 py-1 flex items-center gap-1">
                                <Wifi :size="10" /> ACTIVO POR DEFECTO
                            </span>
                        </div>
                        <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                            data-text="NUEVA CATEGORÍA">
                            NUEVA CATEGORÍA
                        </h1>
                        <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                            <Cpu :size="12" class="text-primary animate-pulse" />
                            ARQUITECTURA DE CATÁLOGO DE ALTO RENDIMIENTO
                            <Terminal :size="12" class="text-primary animate-pulse" />
                        </p>
                    </div>
                    
                    <Link :href="route('admin.categories.index')" 
                          class="px-4 py-2 border border-destructive/50 text-destructive font-mono text-xs hover:bg-destructive hover:text-destructive-foreground transition-all relative group/cancel">
                        CANCELAR
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                    </Link>
                </div>
            </div>

            <!-- Step Progress -->
            <StepProgress 
                :steps="steps" 
                :current-step="currentStep" 
                :progress-percentage="progressPercentage"
                class="mb-8"
            />

            <!-- Main Card -->
            <div id="wizard-card" class="border border-border/50 bg-background shadow-2xl overflow-hidden relative group/card">
                
                <!-- Scanline superior -->
                <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                
                <!-- Efecto de glow en esquinas -->
                <div class="absolute top-0 left-0 w-20 h-20 bg-primary/5 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-20 h-20 bg-primary/5 blur-3xl"></div>
                
                <!-- Esquinas decorativas grandes -->
                <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                <form @submit.prevent="submit">
                    <div class="p-8 min-h-[450px] relative z-10">
                        <Transition name="fade" mode="out-in">
                            
                            <!-- Step 1: Jerarquía -->
                            <div v-if="currentStep === 1" key="1" class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-500">
                                <div class="md:col-span-2 border-b border-primary/20 pb-6 mb-2">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-3 block flex items-center gap-2">
                                        <Radar :size="14" class="text-primary"/> // 01. ESPECIFICAR ZONA DE MERCADO OPERATIVA
                                    </label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <button v-for="zone in zones" :key="zone.id" type="button"
                                                @click="form.market_zone_id = zone.id"
                                                class="p-3 border transition-all flex flex-col items-center gap-2 relative overflow-hidden group/z"
                                                :class="form.market_zone_id === zone.id ? 'border-primary bg-primary/10' : 'border-border/50 hover:border-primary/30'">
                                            <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: zone.hex_color }"></div>
                                            <span class="text-[9px] font-mono font-black uppercase text-center leading-tight">{{ zone.name }}</span>
                                            <div v-if="form.market_zone_id === zone.id" class="absolute top-0 right-0 p-0.5 bg-primary text-primary-foreground">
                                                <CheckCircle :size="10" />
                                            </div>
                                        </button>
                                    </div>
                                    <p v-if="form.errors.market_zone_id" class="text-[8px] font-mono text-destructive mt-2">{{ form.errors.market_zone_id }}</p>
                                </div>
                                <CategoryTypeSelector v-model="categoryType" />

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Nombre Principal -->
                                    <div class="md:col-span-2">
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                            <Terminal :size="12" /> // NOMBRE PRINCIPAL
                                        </label>
                                        <div class="relative group/input">
                                            <Tag :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/input:text-primary transition-colors"/>
                                            <input v-model="form.name" 
                                                   type="text" 
                                                   class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-lg font-bold focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                                   :class="{'border-destructive/50': form.errors.name}"
                                                   placeholder="EJ: BEBIDAS" />
                                            <!-- Esquinas decorativas en focus -->
                                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                        </div>
                                        <p v-if="form.errors.name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                            <AlertTriangle :size="10" /> {{ form.errors.name }}
                                        </p>
                                    </div>

                                    <!-- Código ERP -->
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                            <Hash :size="12" /> // CÓDIGO ERP
                                        </label>
                                        <input v-model="form.external_code" 
                                               type="text" 
                                               class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm uppercase focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                               placeholder="CAT-001" />
                                    </div>

                                    <!-- Selector de Padre (si es child) -->
                                    <div v-if="categoryType === 'child'" class="md:col-span-2 animate-in zoom-in-95 duration-500">
                                        <div class="border border-primary/30 bg-primary/5 p-6 relative group/parent">
                                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/10 to-transparent translate-x-[-100%] group-hover/parent:translate-x-[100%] transition-transform duration-700"></div>
                                            
                                            <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-4 block flex items-center gap-2">
                                                <FolderTree :size="14" /> // ASIGNAR A CATEGORÍA PADRE
                                            </label>
                                            
                                            <div class="relative">
                                                <Layers class="absolute left-3 top-1/2 -translate-y-1/2 text-primary/50" :size="16"/>
                                                <select v-model="form.parent_id" 
                                                        class="w-full pl-10 pr-4 py-3 bg-background border border-primary/30 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                                        :class="{'border-destructive/50': form.errors.parent_id}">
                                                    <option value="">-- SELECCIONAR PADRE --</option>
                                                    <option v-for="cat in parents" :key="cat.id" :value="cat.id">
                                                        {{ cat.name }}
                                                    </option>
                                                </select>
                                            </div>
                                            <p v-if="form.errors.parent_id" class="text-[10px] font-mono text-destructive mt-2 flex items-center gap-1">
                                                <AlertTriangle :size="10" /> {{ form.errors.parent_id }}
                                            </p>
                                            
                                            <!-- Esquinas -->
                                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                                        </div>
                                    </div>

                                    <!-- Subcategorías Inmediatas (si es parent) -->
                                    <div v-if="categoryType === 'parent'" class="md:col-span-2 space-y-4 border-t border-primary/30 pt-6">
                                        <div class="flex justify-between items-center">
                                            <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest flex items-center gap-2">
                                                <GitBranch :size="14"/> SUBCATEGORÍAS INMEDIATAS
                                            </label>
                                            <button type="button" @click="addChild" 
                                                    class="px-3 py-1 border border-primary/30 text-primary text-[8px] font-mono hover:bg-primary/10 transition-all flex items-center gap-1">
                                                <Plus :size="12"/> AÑADIR HIJO
                                            </button>
                                        </div>
                                        
                                        <div v-if="form.children.length === 0" 
                                             class="text-center py-8 border-2 border-dashed border-primary/30 text-muted-foreground text-[10px] font-mono">
                                            OPCIONAL: DEFINE SUBCATEGORÍAS PARA CREARLAS EN BLOQUE.
                                        </div>
                                        
                                        <div v-for="(child, index) in form.children" :key="index" 
                                             class="border border-primary/30 bg-primary/5 p-4 space-y-4 animate-in zoom-in-95 duration-300 relative group/child">
                                            
                                            <!-- Scanline interna -->
                                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/10 to-transparent translate-x-[-100%] group-hover/child:translate-x-[100%] transition-transform duration-700"></div>
                                            
                                            <div class="flex justify-between items-start relative z-10">
                                                <span class="text-[8px] font-mono bg-primary/20 text-primary px-2 py-0.5 border border-primary/30">
                                                    HIJO #{{ String(index + 1).padStart(2, '0') }}
                                                </span>
                                                <button type="button" @click="removeChild(index)" 
                                                        class="p-1 text-destructive hover:bg-destructive/10 transition-colors border border-transparent hover:border-destructive/30">
                                                    <Trash2 :size="14"/>
                                                </button>
                                            </div>

                                            <div class="flex flex-col md:flex-row gap-4 relative z-10">
                                                <!-- Image Uploader para subcategoría -->
                                                <div class="w-full md:w-24 h-24 shrink-0 border border-primary/30 overflow-hidden">
                                                    <ImageUploader v-model="child.image" class="h-full w-full" />
                                                </div>

                                                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div class="space-y-1">
                                                        <label class="text-[8px] font-mono text-primary uppercase">NOMBRE</label>
                                                        <input v-model="child.name" 
                                                               placeholder="SUBCATEGORÍA A" 
                                                               class="w-full bg-background border border-primary/30 px-3 py-2 font-mono text-xs focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                                                        <p v-if="form.errors[`children.${index}.name`]" 
                                                           class="text-[8px] font-mono text-destructive flex items-center gap-1">
                                                            <AlertTriangle :size="8" /> {{ form.errors[`children.${index}.name`] }}
                                                        </p>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="text-[8px] font-mono text-primary uppercase">CÓDIGO ERP</label>
                                                        <input v-model="child.external_code" 
                                                               placeholder="ERP-XXX" 
                                                               class="w-full bg-background border border-primary/30 px-3 py-2 font-mono text-xs uppercase focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Esquinas -->
                                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                                        </div>

                                        <button type="button" @click="addChild" 
                                                class="w-full py-4 border-2 border-dashed border-primary/30 text-primary/60 hover:text-primary hover:border-primary/50 hover:bg-primary/5 transition-all flex items-center justify-center gap-2 text-[10px] font-mono relative group/add">
                                            <Plus :size="16"/> AÑADIR OTRA SUBCATEGORÍA
                                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/add:opacity-100"></span>
                                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/add:opacity-100"></span>
                                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/add:opacity-100"></span>
                                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/add:opacity-100"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Contenido -->
                            <div v-else-if="currentStep === 2" key="2" class="grid grid-cols-1 md:grid-cols-3 gap-8 animate-in fade-in slide-in-from-right-4 duration-500">
                                <div class="md:col-span-1 space-y-6">
                                    <div>
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-3 block">// IMAGEN BANNER</label>
                                        <ImageUploader v-model="form.image" class="h-48 border border-primary/30" />
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-3 block">// ICONO MINIATURA</label>
                                        <ImageUploader v-model="form.icon" class="h-24 border border-primary/30" />
                                    </div>
                                </div>
                                <div class="md:col-span-2 space-y-4">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block">
                                        // DESCRIPCIÓN OPERATIVA
                                    </label>
                                    <textarea v-model="form.description" 
                                              rows="8" 
                                              class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all resize-none"
                                              placeholder="DETALLES INTERNOS O COMERCIALES..."></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                        <Radar :size="12" /> // ZONA DE MERCADO OPERATIVA
                                    </label>
                                    <select v-model="form.market_zone_id" 
                                            class="w-full bg-background border border-primary/30 px-4 py-3 font-mono text-sm focus:border-primary outline-none transition-all appearance-none"
                                            :class="{'border-destructive/50': form.errors.market_zone_id}">
                                        <option value="">-- SELECCIONAR ZONA --</option>
                                        <option v-for="zone in $page.props.zones" :key="zone.id" :value="zone.id">
                                            {{ zone.name.toUpperCase() }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.market_zone_id" class="text-[8px] font-mono text-destructive mt-1">
                                        {{ form.errors.market_zone_id }}
                                    </p>
                                </div>
                            </div>

                            <!-- Step 3: Ajustes -->
                            <div v-else key="3" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <!-- Configuración Base -->
                                    <div class="space-y-4 border border-primary/30 p-6 relative group/base">
                                        <h3 class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-4 flex items-center gap-2">
                                            <Eye :size="14" /> CONFIGURACIÓN BASE
                                        </h3>
                                        <BaseCheckbox 
                                            v-model="form.is_active" 
                                            label="CATEGORÍA ACTIVA" 
                                            help="VISIBLE EN EL CATÁLOGO PÚBLICO" 
                                            class="cyber-checkbox"
                                        />
                                        <BaseCheckbox 
                                            v-model="form.is_featured" 
                                            label="DESTACAR EN HOME" 
                                            help="APARECE EN CARRUSELES PRINCIPALES" 
                                            class="cyber-checkbox"
                                        />
                                        <BaseCheckbox 
                                            v-model="form.requires_age_check" 
                                            label="RESTRICCIÓN +18" 
                                            help="OBLIGA VALIDACIÓN DE EDAD AL COMPRAR" 
                                            class="cyber-checkbox"
                                        />
                                        <!-- Esquinas -->
                                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                                    </div>

                                    <!-- SEO & Impuestos -->
                                    <div class="space-y-4 border border-primary/30 p-6 relative group/seo">
                                        <h3 class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-4 flex items-center gap-2">
                                            <Info :size="14" /> SEO & IMPUESTOS
                                        </h3>
                                        <div>
                                            <label class="text-[8px] font-mono text-primary uppercase tracking-wider mb-2 block">
                                                CLASIFICACIÓN FISCAL (ICE/IVA)
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[8px] font-mono text-primary">TAX:</span>
                                                <input v-model="form.tax_classification" 
                                                       type="text" 
                                                       class="w-full pl-12 pr-4 py-3 bg-background border border-primary/30 font-mono text-sm uppercase focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                                       placeholder="GENERIC_TAX">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-[8px] font-mono text-primary uppercase tracking-wider mb-2 block">
                                                SEO TITLE
                                            </label>
                                            <input v-model="form.seo_title" 
                                                   type="text" 
                                                   class="w-full bg-background border border-primary/30 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                                   placeholder="META TITLE PARA BUSCADORES">
                                        </div>
                                        <!-- Esquinas -->
                                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <!-- Footer Actions -->
                    <div class="p-6 bg-background/80 backdrop-blur-sm border-t border-primary/30 flex justify-between items-center">
                        <button type="button" @click="prevStep" 
                                :class="{'invisible': currentStep === 1}"
                                class="px-6 py-2 border border-border text-[10px] font-mono font-bold uppercase hover:border-primary hover:text-primary transition-all relative group/prev">
                            <span class="flex items-center gap-2">
                                <ArrowLeft :size="14" class="group-hover/prev:-translate-x-1 transition-transform" />
                                ANTERIOR
                            </span>
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep"
                                class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/next overflow-hidden">
                            <span class="flex items-center gap-2 relative z-10">
                                SIGUIENTE <ArrowRight :size="14" class="group-hover/next:translate-x-1 transition-transform" />
                            </span>
                            <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/next:translate-y-0 transition-transform duration-500"></span>
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                        </button>

                        <button v-else type="submit" :disabled="form.processing"
                                class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/submit overflow-hidden">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <Cpu :size="14" class="animate-spin" /> PROCESANDO...
                            </span>
                            <span v-else class="flex items-center gap-2 relative z-10">
                                <Save :size="14" /> FINALIZAR REGISTRO
                            </span>
                            <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/submit:translate-y-0 transition-transform duration-500"></span>
                        </button>
                    </div>
                </form>
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
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}

.shake {
    animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
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

/* Transiciones */
.fade-enter-active, .fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from {
    opacity: 0;
    transform: translateX(20px);
}
.fade-leave-to {
    opacity: 0;
    transform: translateX(-20px);
    position: absolute;
    width: 100%;
}

/* Estilos para checkboxes */
:deep(.cyber-checkbox) {
    font-family: 'JetBrains Mono', monospace;
}

:deep(.cyber-checkbox label) {
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}
</style>