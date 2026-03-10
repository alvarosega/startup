<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StepProgress from '@/Components/StepProgress.vue';
import ImageUploader from '@/Components/ImageUploader.vue';
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Tag, Factory, Globe, ArrowRight, ArrowLeft, Save, 
    Search, Cpu, Terminal, CheckCircle, AlertTriangle,
    Wifi
} from 'lucide-vue-next';

const props = defineProps({
    providers: Array,
    categories: Array,
    zones: Array
});

// --- ESTADO ---
const currentStep = ref(1);
const categorySearch = ref('');

const steps = [
    { id: 1, title: 'IDENTIDAD', code: 'SEC_01', icon: Tag, fields: ['name', 'image'] }, // Quitar tier
    { id: 2, title: 'LOGÍSTICA', code: 'SEC_02', icon: Factory, fields: ['provider_id', 'market_zone_id'] }, // Actualizado
    { id: 3, title: 'SEGMENTACIÓN', code: 'SEC_03', icon: Globe, fields: ['category_id', 'website', 'description'] }, // Actualizado
];
const form = useForm({
    name: '',
    provider_id: '',
    category_id: '',
    market_zone_id: '',
    website: '',
    description: '',
    image: null,
    is_active: true,
    is_featured: false,
    sort_order: 0,
});

// Código temporal para nueva marca
const tempCode = computed(() => {
    return `BRD_NEW_${String(Math.floor(Math.random() * 1000)).padStart(3, '0')}`;
});

// --- FILTROS COMPUTADOS ---
const filteredCategories = computed(() => {
    const list = props.categories || [];
    if (!categorySearch.value) return list;
    const term = categorySearch.value.toLowerCase();
    return list.filter(c => c.name && c.name.toLowerCase().includes(term));
});

const selectCategory = (id) => {
    form.category_id = id;
};

// --- NAVEGACIÓN Y VALIDACIÓN ---
const validateStep = () => {
    form.clearErrors();
    let isValid = true;
    
    if (currentStep.value === 1 && !form.name) {
        form.setError('name', '// NOMBRE COMERCIAL REQUERIDO');
        isValid = false;
    }
    if (currentStep.value === 2) {
        if (!form.provider_id) { 
            form.setError('provider_id', '// DISTRIBUIDOR OFICIAL REQUERIDO'); 
            isValid = false; 
        }
        if (!form.market_zone_id) { 
            form.setError('market_zone_id', '// ZONA DE MERCADO REQUERIDA'); 
            isValid = false; 
        }
    }
    if (currentStep.value === 3 && !form.category_id) {
        form.setError('category_id', '// CATEGORÍA RAÍZ REQUERIDA');
        isValid = false;
    }
    
    if (!isValid) triggerShake();
    return isValid;
};

const nextStep = () => {
    if (validateStep() && currentStep.value < steps.length) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const triggerShake = () => {
    const card = document.getElementById('wizard-card');
    if (card) {
        card.classList.add('shake');
        setTimeout(() => card.classList.remove('shake'), 400);
    }
};

const submit = () => {
    if (!validateStep()) return;
    
    form.post(route('admin.brands.store'), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            const stepWithError = steps.find(step => step.fields.some(f => errors[f]));
            if (stepWithError) currentStep.value = stepWithError.id;
            triggerShake();
        }
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto pb-12 px-4 md:px-0">
            
            <div class="mb-8 relative group/header">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10 flex justify-between items-end">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-[8px] font-mono border border-primary/30 bg-primary/5 text-primary px-2 py-1">
                                {{ tempCode }}
                            </span>
                            <span class="text-[8px] font-mono text-cyan-500 border border-cyan-500/30 bg-cyan-500/10 px-2 py-1 flex items-center gap-1">
                                <Wifi :size="10" /> PROTOCOLO DE ALTA
                            </span>
                        </div>
                        <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none" data-text="NUEVA MARCA">
                            NUEVA MARCA
                        </h1>
                        <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                            <Cpu :size="12" class="text-primary animate-pulse" /> REGISTRO DE ENTIDAD COMERCIAL PLANA
                            <Terminal :size="12" class="text-primary animate-pulse" />
                        </p>
                    </div>
                    
                    <Link :href="route('admin.brands.index')" class="px-4 py-2 border border-destructive/50 text-destructive font-mono text-xs hover:bg-destructive hover:text-destructive-foreground transition-all relative group/cancel">
                        CANCELAR
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                    </Link>
                </div>
            </div>

            <StepProgress :steps="steps" :current-step="currentStep" :progress-percentage="progressPercentage" class="mb-8" />

            <div id="wizard-card" class="border border-border/50 bg-background shadow-2xl overflow-hidden relative group/card min-h-[500px] flex flex-col">
                
                <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                <form @submit.prevent="submit" class="flex-1 flex flex-col">
                    <div class="p-8 flex-1 relative z-10">
                        <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="space-y-8 animate-in fade-in slide-in-from-left-4 duration-500">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                    <div class="md:col-span-1">
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-3 block">// LOGOTIPO CORPORATIVO</label>
                                        <ImageUploader v-model="form.image" class="h-48 border border-primary/30" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                            <Terminal :size="12" /> // NOMBRE COMERCIAL
                                        </label>
                                        <div class="relative group/input">
                                            <input v-model="form.name" type="text" class="w-full bg-background border border-border/50 pl-4 pr-4 py-3 font-mono text-lg font-bold focus:border-primary focus:shadow-neon-primary outline-none transition-all uppercase" :class="{'border-destructive/50': form.errors.name}" placeholder="EJ: COCA-COLA" />
                                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                        </div>
                                        <p v-if="form.errors.name" class="text-[10px] font-mono text-destructive mt-2 flex items-center gap-1">
                                            <AlertTriangle :size="10" /> {{ form.errors.name }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="currentStep === 2" key="2" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block flex items-center gap-1">
                                            <Factory :size="12" /> // DISTRIBUIDOR OFICIAL
                                        </label>
                                        <select v-model="form.provider_id" class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all uppercase" :class="{'border-destructive/50': form.errors.provider_id}">
                                            <option value="" disabled>-- SELECCIONA PROVEEDOR --</option>
                                            <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.company_name || p.commercial_name || p.name }}</option>
                                        </select>
                                        <p v-if="form.errors.provider_id" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                            <AlertTriangle :size="10" /> {{ form.errors.provider_id }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block flex items-center gap-1">
                                            <Globe :size="12" /> // ZONA DE MERCADO PERMITIDA
                                        </label>
                                        <select v-model="form.market_zone_id" class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all uppercase" :class="{'border-destructive/50': form.errors.market_zone_id}">
                                            <option value="" disabled>-- SELECCIONA ZONA --</option>
                                            <option v-for="z in zones" :key="z.id" :value="z.id">{{ z.name }}</option>
                                        </select>
                                        <p v-if="form.errors.market_zone_id" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                            <AlertTriangle :size="10" /> {{ form.errors.market_zone_id }}
                                        </p>
                                    </div>
                                    
                                    <div class="md:col-span-2 space-y-2">
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block">
                                            // WEBSITE CORPORATIVO (OPCIONAL)
                                        </label>
                                        <input v-model="form.website" type="url" class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary outline-none transition-all" placeholder="https://www.ejemplo.com" />
                                    </div>
                                </div>
                            </div>

                            <div v-else key="3" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                                <div class="border border-primary/30 p-6 relative bg-primary/5">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest">// CATEGORÍA RAÍZ (OBLIGATORIA)</h3>
                                        <div class="relative w-64">
                                            <Search :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                            <input v-model="categorySearch" type="text" placeholder="FILTRAR CATEGORÍAS..." class="w-full bg-background border border-primary/30 pl-9 p-2 text-xs font-mono focus:border-primary outline-none uppercase">
                                        </div>
                                    </div>
                                    <div class="bg-background p-4 border border-primary/30 h-48 overflow-y-auto flex flex-wrap gap-2">
                                        <button type="button" v-for="cat in filteredCategories" :key="cat.id" @click="selectCategory(cat.id)" class="px-3 py-1.5 text-[10px] font-mono border transition-all uppercase flex items-center gap-2" :class="form.category_id === cat.id ? 'bg-primary text-background border-primary shadow-neon-primary scale-105' : 'bg-background border-border/50 text-foreground hover:border-primary/50'">
                                            {{ cat.name }} <CheckCircle v-if="form.category_id === cat.id" :size="12" />
                                        </button>
                                    </div>
                                    <p v-if="form.errors.category_id" class="text-destructive text-[10px] mt-2 flex items-center gap-1">
                                        <AlertTriangle :size="10" /> {{ form.errors.category_id }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block">// DESCRIPCIÓN SEO / PÚBLICA</label>
                                        <textarea v-model="form.description" rows="4" class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-xs focus:border-primary outline-none transition-all resize-none" placeholder="DESCRIPCIÓN COMERCIAL DE LA MARCA..."></textarea>
                                    </div>
                                    <div class="space-y-4 border border-primary/30 p-6 relative">
                                        <BaseCheckbox v-model="form.is_active" label="MARCA ACTIVA" help="VISIBLE EN EL CATÁLOGO PÚBLICO" class="cyber-checkbox" />
                                        <BaseCheckbox v-model="form.is_featured" label="DESTACAR MARCA" help="PRIORIDAD EN CARRUSELES" class="cyber-checkbox" />
                                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <div class="p-6 bg-background/80 backdrop-blur-sm border-t border-primary/30 flex justify-between items-center z-20">
                        <button type="button" @click="prevStep" :class="{'invisible': currentStep === 1}" class="px-6 py-2 border border-border text-[10px] font-mono font-bold uppercase hover:border-primary hover:text-primary transition-all relative group/prev">
                            <span class="flex items-center gap-2"><ArrowLeft :size="14" class="group-hover/prev:-translate-x-1 transition-transform" /> ANTERIOR</span>
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        </button>
                        
                        <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/next overflow-hidden">
                            <span class="flex items-center gap-2 relative z-10">SIGUIENTE <ArrowRight :size="14" class="group-hover/next:translate-x-1 transition-transform" /></span>
                            <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/next:translate-y-0 transition-transform duration-500"></span>
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                        </button>
                        
                        <button v-else type="submit" :disabled="form.processing" class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/submit overflow-hidden">
                            <span v-if="form.processing" class="flex items-center gap-2 relative z-10"><Cpu :size="14" class="animate-spin" /> PROCESANDO...</span>
                            <span v-else class="flex items-center gap-2 relative z-10"><Save :size="14" /> FINALIZAR REGISTRO</span>
                            <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/submit:translate-y-0 transition-transform duration-500"></span>
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="mt-4 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // BRD_CREATE_{{ String(currentStep).padStart(2, '0') }} // {{ new Date().toISOString().slice(0,10) }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes shake { 10%, 90% { transform: translate3d(-1px, 0, 0); } 20%, 80% { transform: translate3d(2px, 0, 0); } 30%, 50%, 70% { transform: translate3d(-4px, 0, 0); } 40%, 60% { transform: translate3d(4px, 0, 0); } }
.shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
.glitch-text { position: relative; animation: glitch-skew 4s infinite linear alternate-reverse; }
.glitch-text::before, .glitch-text::after { content: attr(data-text); position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.8; }
.glitch-text::before { color: #0ff; z-index: -1; animation: glitch-anim-1 0.4s infinite linear alternate-reverse; }
.glitch-text::after { color: #f0f; z-index: -2; animation: glitch-anim-2 0.4s infinite linear alternate-reverse; }
@keyframes glitch-skew { 0%, 20%, 22%, 80%, 82%, 100% { transform: skew(0deg); } 21% { transform: skew(2deg); } 81% { transform: skew(-2deg); } }
@keyframes glitch-anim-1 { 0% { clip-path: inset(20% 0 30% 0); } 100% { clip-path: inset(40% 0 20% 0); } }
@keyframes glitch-anim-2 { 0% { clip-path: inset(60% 0 10% 0); } 100% { clip-path: inset(30% 0 40% 0); } }
.shadow-neon-primary { box-shadow: 0 0 20px hsl(var(--primary) / 0.3); }
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from { opacity: 0; transform: translateX(20px); }
.fade-leave-to { opacity: 0; transform: translateX(-20px); position: absolute; width: 100%; }
:deep(.cyber-checkbox) { font-family: 'JetBrains Mono', monospace; }
:deep(.cyber-checkbox label) { font-size: 11px; font-weight: bold; text-transform: uppercase; }
</style>