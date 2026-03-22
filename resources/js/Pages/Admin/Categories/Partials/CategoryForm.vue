<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import StepProgress from '@/Components/StepProgress.vue';
import ImageUploader from '@/Components/ImageUploader.vue';
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
import { 
    FolderTree, Image as ImageIcon, Settings, ArrowRight, ArrowLeft, 
    Save, Hash, FileText, Cpu, Terminal, Wifi, WifiOff, Zap, Tag, 
    AlertTriangle, Eye, Info, ChevronDown
} from 'lucide-vue-next';

const props = defineProps({
    category: Object, // Opcional (null en create)
    parents: Array    // Lista de categorías para el selector de jerarquía
});

const isEdit = computed(() => !!props.category);
const catData = computed(() => props.category?.data || props.category);

// --- ESTADO DEL WIZARD ---
const currentStep = ref(1);
const steps = [
    { id: 1, title: 'IDENTIDAD', code: 'SEC_01', icon: FolderTree, fields: ['name', 'parent_id', 'external_code', 'bg_color'] },
    { id: 2, title: 'CONTENIDO', code: 'SEC_02', icon: ImageIcon, fields: ['image', 'icon', 'description', 'seo_title', 'seo_description'] },
    { id: 3, title: 'AJUSTES', code: 'SEC_03', icon: Settings, fields: ['tax_classification', 'requires_age_check', 'is_active', 'is_featured'] },
];

// --- FORMULARIO UNIFICADO ---
const form = useForm({
    _method: isEdit.value ? 'PUT' : 'POST',
    name: catData.value?.name || '',
    parent_id: catData.value?.parent_id || '',
    external_code: catData.value?.external_code || '',
    description: catData.value?.description || '',
    slug: catData.value?.slug || '',
    bg_color: catData.value?.bg_color || '#3b82f6',
    tax_classification: catData.value?.tax_classification || '',
    requires_age_check: Boolean(catData.value?.requires_age_check) || false,
    is_active: isEdit.value ? Boolean(catData.value?.is_active) : true,
    is_featured: Boolean(catData.value?.is_featured) || false,
    image: null,
    icon: null,
    seo_title: catData.value?.seo_title || '',
    seo_description: catData.value?.seo_description || '',
});

const categoryCode = computed(() => {
    return isEdit.value 
        ? `ID_${String(catData.value.id).substring(0, 8).toUpperCase()}` 
        : `CAT_NEW_${Math.floor(Math.random() * 1000)}`;
});

// --- LÓGICA DE NAVEGACIÓN ---
const nextStep = () => {
    form.clearErrors();
    if (currentStep.value === 1 && !form.name.trim()) {
        form.setError('name', '// IDENTIFICADOR REQUERIDO');
        return;
    }
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => { if (currentStep.value > 1) currentStep.value--; };

const submit = () => {
    const url = isEdit.value 
        ? route('admin.categories.update', catData.value.id) 
        : route('admin.categories.store');

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            const stepWithError = steps.find(s => s.fields.some(f => errors[f]));
            if (stepWithError) currentStep.value = stepWithError.id;
            document.getElementById('wizard-card')?.classList.add('shake');
            setTimeout(() => document.getElementById('wizard-card')?.classList.remove('shake'), 400);
        }
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <div class="mb-8 relative group/header">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
        <div class="relative z-10 flex justify-between items-end">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-[8px] font-mono border border-primary/30 bg-primary/5 text-primary px-2 py-1">{{ categoryCode }}</span>
                    <span class="text-[8px] font-mono text-cyan-500 border border-cyan-500/30 bg-cyan-500/10 px-2 py-1 flex items-center gap-1">
                        <component :is="form.is_active ? Wifi : WifiOff" :size="10" /> {{ form.is_active ? 'ONLINE' : 'OFFLINE' }}
                    </span>
                </div>
                <h1 class="text-3xl font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none" :data-text="isEdit ? 'EDITAR NODO' : 'NUEVA CATEGORÍA'">
                    {{ isEdit ? 'EDITAR NODO' : 'NUEVA CATEGORÍA' }}
                </h1>
                <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                    <Cpu :size="12" class="text-primary animate-pulse" />
                    {{ isEdit ? `MODIFICANDO: ${catData.name}` : 'ARQUITECTURA DE CATÁLOGO MAESTRO' }}
                </p>
            </div>
            <Link :href="route('admin.categories.index')" class="px-4 py-2 border border-destructive/50 text-destructive font-mono text-xs hover:bg-destructive hover:text-destructive-foreground transition-all relative group/cancel">
                CANCELAR <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
            </Link>
        </div>
    </div>

    <StepProgress :steps="steps" :current-step="currentStep" :progress-percentage="progressPercentage" class="mb-8" />

    <div id="wizard-card" class="border border-border/50 bg-background shadow-2xl overflow-hidden relative group/card min-h-[500px]">
        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
        
        <form @submit.prevent="submit">
            <div class="p-8 relative z-10">
                <Transition name="fade" mode="out-in">
                    <div v-if="currentStep === 1" key="1" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest flex items-center gap-1"><Terminal :size="12" /> // NOMBRE</label>
                                <div class="relative group/input">
                                    <Tag :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/input:text-primary transition-colors"/>
                                    <input v-model="form.name" type="text" class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-lg font-bold focus:border-primary outline-none transition-all uppercase" placeholder="EJ: BEBIDAS" />
                                </div>
                                <p v-if="form.errors.name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1"><AlertTriangle :size="10" /> {{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest flex items-center gap-1"><FolderTree :size="12" /> // CATEGORÍA PADRE</label>
                                <div class="relative">
                                    <select v-model="form.parent_id" class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm uppercase appearance-none focus:border-primary outline-none">
                                        <option value="">-- CATEGORÍA RAÍZ --</option>
                                        <option v-for="p in parents" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                    <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="16" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest flex items-center gap-1"><Hash :size="12" /> // CÓDIGO EXTERNO</label>
                                <input v-model="form.external_code" type="text" class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm uppercase focus:border-primary outline-none" placeholder="ERP-ID" />
                            </div>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 2" key="2" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-1 space-y-6">
                            <div>
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-3 block">// BANNER</label>
                                <ImageUploader v-model="form.image" :existing-image="catData?.image_url" class="h-48 border border-primary/30" />
                            </div>
                            <div>
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-3 block">// ICONO</label>
                                <ImageUploader v-model="form.icon" :existing-image="catData?.icon_url" class="h-24 border border-primary/30" />
                            </div>
                        </div>
                        <div class="md:col-span-2 space-y-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block">// DESCRIPCIÓN PÚBLICA</label>
                                <textarea v-model="form.description" rows="4" class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary outline-none resize-none"></textarea>
                            </div>
                            <div class="border border-primary/30 bg-primary/5 p-6 space-y-4">
                                <h3 class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest flex items-center gap-2"><Info :size="14" /> METADATOS SEO</h3>
                                <input v-model="form.seo_title" type="text" class="w-full bg-background border border-primary/30 px-4 py-3 font-mono text-sm focus:border-primary outline-none" placeholder="SEO TITLE">
                                <textarea v-model="form.seo_description" rows="2" class="w-full bg-background border border-primary/30 px-4 py-3 font-mono text-sm focus:border-primary outline-none resize-none" placeholder="SEO DESCRIPTION"></textarea>
                            </div>
                        </div>
                    </div>

                    <div v-else key="3" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4 border border-primary/30 p-6 relative">
                                <h3 class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-4 flex items-center gap-2"><Eye :size="14" /> VISIBILIDAD</h3>
                                <BaseCheckbox v-model="form.is_active" label="CATEGORÍA ACTIVA" help="VISIBLE EN CATÁLOGO" />
                                <BaseCheckbox v-model="form.is_featured" label="DESTACAR" help="MOSTRAR EN HOME" />
                                <BaseCheckbox v-model="form.requires_age_check" label="MAYORÍA DE EDAD" help="VALIDAR +18" />
                            </div>
                            <div class="space-y-4 border border-primary/30 p-6 relative">
                                <h3 class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-4 flex items-center gap-2"><FileText :size="14" /> FISCAL</h3>
                                <div class="space-y-2">
                                    <label class="text-[8px] font-mono text-primary uppercase">MAPEO TAX (ICE/IVA)</label>
                                    <input v-model="form.tax_classification" type="text" class="w-full bg-background border border-primary/30 px-4 py-3 font-mono text-sm uppercase focus:border-primary outline-none" placeholder="GENERIC_TAX">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[8px] font-mono text-primary uppercase">COLOR IDENTIDAD</label>
                                    <input v-model="form.bg_color" type="color" class="w-full h-12 bg-background border border-primary/30 p-1 cursor-pointer">
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <div class="p-6 bg-background/80 backdrop-blur-sm border-t border-primary/30 flex justify-between items-center">
                <button type="button" @click="prevStep" :class="{'invisible': currentStep === 1}" class="px-6 py-2 border border-border text-[10px] font-mono font-bold uppercase hover:text-primary transition-all flex items-center gap-2">
                    <ArrowLeft :size="14" /> ANTERIOR
                </button>

                <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all flex items-center gap-2">
                    SIGUIENTE <ArrowRight :size="14" />
                </button>

                <button v-else type="submit" :disabled="form.processing" class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all flex items-center gap-2">
                    <Save :size="14" /> {{ isEdit ? 'GUARDAR CAMBIOS' : 'FINALIZAR REGISTRO' }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}
.shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
.shadow-neon-primary { box-shadow: 0 0 20px hsl(var(--primary) / 0.3); }
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from { opacity: 0; transform: translateX(20px); }
.fade-leave-to { opacity: 0; transform: translateX(-20px); position: absolute; width: 100%; }
</style>