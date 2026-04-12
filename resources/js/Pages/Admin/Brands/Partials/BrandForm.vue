<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
//import StepProgress from '@/Components/StepProgress.vue';
//import ImageUploader from '@/Components/ImageUploader.vue';
//import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
import { 
    Tag, Factory, Globe, ArrowRight, ArrowLeft, Save, 
    Search, Cpu, Terminal, CheckCircle, AlertTriangle,
    Wifi, WifiOff, Star, MapPin, ChevronDown
} from 'lucide-vue-next';

const props = defineProps({
    brand: Object,   // Nulo en Create
    options: Object  // { providers, categories, zones, parents }
});

const isEdit = computed(() => !!props.brand);
const brandData = computed(() => props.brand?.data || props.brand);

// --- ESTADO DEL WIZARD ---
const currentStep = ref(1);
const categorySearch = ref('');
const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
const steps = [
    { id: 1, title: 'IDENTIDAD', code: 'SEC_01', icon: Tag, fields: ['name', 'image'] },
    { id: 2, title: 'LOGÍSTICA', code: 'SEC_02', icon: Factory, fields: ['provider_id', 'market_zone_ids'] },
    { id: 3, title: 'SEGMENTACIÓN', code: 'SEC_03', icon: Globe, fields: ['category_id', 'is_active'] },
];

// --- FORMULARIO UNIFICADO ---
const form = useForm({
    _method: isEdit.value ? 'PUT' : 'POST',
    name: brandData.value?.name || '',
    parent_id: brandData.value?.parent_id || '',
    provider_id: brandData.value?.provider_id || '',
    category_id: brandData.value?.category_id || '',
    // Relación M:N: Extraemos solo los IDs si estamos editando
    market_zone_ids: brandData.value?.market_zones?.map(z => z.id) || [], 
    website: brandData.value?.website || '',
    description: brandData.value?.description || '',
    image: null,
    is_active: isEdit.value ? Boolean(brandData.value?.is_active) : true,
    is_featured: Boolean(brandData.value?.is_featured) || false,
    sort_order: brandData.value?.sort_order || 0,
});

// --- LÓGICA DE FILTRADO ---
const filteredCategories = computed(() => {
    const list = props.options.categories || [];
    if (!categorySearch.value) return list;
    const term = categorySearch.value.toLowerCase();
    return list.filter(c => c.name.toLowerCase().includes(term));
});

const brandCode = computed(() => isEdit.value 
    ? `ID_${String(brandData.value.id).substring(0, 8).toUpperCase()}` 
    : `BRD_NEW_${Math.floor(Math.random() * 1000)}`
);

// --- NAVEGACIÓN ---
const nextStep = () => {
    form.clearErrors();
    if (currentStep.value === 1 && !form.name) return form.setError('name', '// IDENTIDAD REQUERIDA');
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => { if (currentStep.value > 1) currentStep.value--; };

const submit = () => {
    const url = isEdit.value ? route('admin.brands.update', brandData.value.id) : route('admin.brands.store');
    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onError: () => {
            const stepWithError = steps.find(s => s.fields.some(f => form.errors[f]));
            if (stepWithError) currentStep.value = stepWithError.id;
        }
    });
};
</script>

<template>
    <div class="mb-8 relative group/header">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
        <div class="relative z-10 flex justify-between items-end">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-[8px] font-mono border border-primary/30 bg-primary/5 text-primary px-2 py-1">{{ brandCode }}</span>
                    <span class="text-[8px] font-mono border border-cyan-500/30 bg-cyan-500/10 text-cyan-500 px-2 py-1 flex items-center gap-1">
                        <component :is="form.is_active ? Wifi : WifiOff" :size="10" /> {{ form.is_active ? 'ONLINE' : 'OFFLINE' }}
                    </span>
                </div>
                <h1 class="text-3xl font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none" :data-text="isEdit ? 'EDITAR MARCA' : 'ALTA DE MARCA'">
                    {{ isEdit ? 'EDITAR MARCA' : 'ALTA DE MARCA' }}
                </h1>
                <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2 uppercase">
                    <Cpu :size="12" class="text-primary animate-pulse" /> Registro de Identidad Comercial Plana
                </p>
            </div>
            <Link :href="route('admin.brands.index')" class="px-4 py-2 border border-destructive/50 text-destructive font-mono text-xs hover:bg-destructive hover:text-destructive-foreground transition-all relative">
                CANCELAR
            </Link>
        </div>
    </div>

    <StepProgress :steps="steps" :current-step="currentStep" :progress-percentage="progressPercentage" class="mb-8" />

    <div id="wizard-card" class="border border-border/50 bg-background shadow-2xl relative min-h-[500px] flex flex-col overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>

        <form @submit.prevent="submit" class="flex-1 flex flex-col">
            <div class="p-8 flex-1 relative z-10">
                <Transition name="fade" mode="out-in">
                    
                    <div v-if="currentStep === 1" key="1" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="md:col-span-1">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-3 block">// LOGOTIPO</label>
                                <ImageUploader v-model="form.image" :existing-image="brandData?.image_url" class="h-48 border border-primary/30" />
                            </div>
                            <div class="md:col-span-2 space-y-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest flex items-center gap-1"><Terminal :size="12" /> // NOMBRE COMERCIAL</label>
                                    <input v-model="form.name" type="text" class="w-full bg-background border border-border/50 p-4 font-mono text-xl font-black focus:border-primary outline-none uppercase" />
                                    <p v-if="form.errors.name" class="text-[10px] font-mono text-destructive">{{ form.errors.name }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest flex items-center gap-1"><Star :size="12" /> // MARCA MADRE (OPCIONAL)</label>
                                    <select v-model="form.parent_id" class="w-full bg-background border border-border/50 p-3 font-mono text-sm uppercase outline-none focus:border-primary">
                                        <option value="">-- MARCA INDEPENDIENTE --</option>
                                        <option v-for="p in options.parents" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 2" key="2" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest flex items-center gap-1"><Factory :size="12" /> // DISTRIBUIDOR OFICIAL</label>
                                <select v-model="form.provider_id" class="w-full bg-background border border-border/50 p-3 font-mono text-sm uppercase focus:border-primary">
                                    <option value="" disabled>-- SELECCIONAR --</option>
                                    <option v-for="p in options.providers" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-4">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest flex items-center gap-1"><MapPin :size="12" /> // ZONAS ESTRATÉGICAS (M:N)</label>
                                <div class="grid grid-cols-2 gap-3 bg-muted/20 p-4 border border-primary/20">
                                    <label v-for="z in options.zones" :key="z.id" class="flex items-center gap-2 cursor-pointer group">
                                        <input type="checkbox" :value="z.id" v-model="form.market_zone_ids" class="w-4 h-4 rounded-none border-primary bg-background text-primary focus:ring-0">
                                        <span class="text-[10px] font-mono font-bold uppercase group-hover:text-primary transition-colors">{{ z.name }}</span>
                                    </label>
                                </div>
                                <p v-if="form.errors.market_zone_ids" class="text-[10px] font-mono text-destructive">{{ form.errors.market_zone_ids }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-else key="3" class="space-y-6">
                        <div class="border border-primary/30 p-6 bg-primary/5">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest">// CATEGORÍA MAESTRA</h3>
                                <input v-model="categorySearch" type="text" placeholder="FILTRAR..." class="bg-background border border-primary/30 p-2 text-[10px] font-mono focus:border-primary outline-none">
                            </div>
                            <div class="h-32 overflow-y-auto flex flex-wrap gap-2 p-2 bg-background border border-primary/10">
                                <button type="button" v-for="cat in filteredCategories" :key="cat.id" @click="form.category_id = cat.id" 
                                    class="px-3 py-1 text-[9px] font-mono border uppercase transition-all"
                                    :class="form.category_id === cat.id ? 'bg-primary text-background border-primary' : 'border-border text-muted-foreground hover:border-primary'">
                                    {{ cat.name }}
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <BaseCheckbox v-model="form.is_active" label="MARCA ACTIVA" help="DISPONIBLE EN APP" />
                                <BaseCheckbox v-model="form.is_featured" label="DESTACAR" help="MOSTRAR EN HOME" />
                            </div>
                            <div class="space-y-4">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block">// DESCRIPCIÓN SEO</label>
                                <textarea v-model="form.description" rows="3" class="w-full bg-background border border-border/50 p-3 font-mono text-xs focus:border-primary outline-none resize-none"></textarea>
                            </div>
                        </div>
                    </div>

                </Transition>
            </div>

            <div class="p-6 bg-background/80 border-t border-primary/30 flex justify-between">
                <button type="button" @click="prevStep" :class="{'invisible': currentStep === 1}" class="px-6 py-2 border border-border text-[10px] font-mono font-bold uppercase hover:text-primary transition-all flex items-center gap-2">
                    <ArrowLeft :size="14" /> ANTERIOR
                </button>
                <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase hover:bg-primary/90 transition-all flex items-center gap-2 shadow-neon-primary">
                    SIGUIENTE <ArrowRight :size="14" />
                </button>
                <button v-else type="submit" :disabled="form.processing" class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase hover:bg-primary/90 transition-all flex items-center gap-2 shadow-neon-primary">
                    <Save :size="14" /> {{ isEdit ? 'GUARDAR CAMBIOS' : 'FINALIZAR REGISTRO' }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from { opacity: 0; transform: translateX(20px); }
.fade-leave-to { opacity: 0; transform: translateX(-20px); position: absolute; width: 100%; }
.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }
@keyframes shake { 10%, 90% { transform: translate3d(-1px, 0, 0); } 30%, 70% { transform: translate3d(-4px, 0, 0); } 40%, 60% { transform: translate3d(4px, 0, 0); } }
.shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
</style>