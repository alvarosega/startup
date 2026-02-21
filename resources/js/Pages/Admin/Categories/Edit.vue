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
    Info, Hash, FileText, BadgeCheck
} from 'lucide-vue-next';

const props = defineProps({
    category: Object,
    parents: Array
});

// --- ESTADO ---
const currentStep = ref(1);
const categoryType = ref(props.category.parent_id ? 'child' : 'parent');

const steps = [
    { id: 1, title: 'Jerarquía', icon: FolderTree, fields: ['name', 'external_code', 'parent_id'] },
    { id: 2, title: 'Contenido', icon: ImageIcon, fields: ['image', 'description'] },
    { id: 3, title: 'Ajustes & SEO', icon: Settings, fields: ['tax_classification', 'requires_age_check'] },
];

const form = useForm({
    _method: 'PUT', // Túnel para soporte de archivos
    name: props.category.name,
    parent_id: props.category.parent_id || '',
    external_code: props.category.external_code || '',
    description: props.category.description || '',
    slug: props.category.slug || '',
    image: null, 
    seo_title: props.category.seo_title || '',
    seo_description: props.category.seo_description || '',
    tax_classification: props.category.tax_classification || '',
    requires_age_check: Boolean(props.category.requires_age_check),
    is_active: Boolean(props.category.is_active),
    is_featured: Boolean(props.category.is_featured),
});

// --- LÓGICA ---

// Sincronizar tipo de categoría
watch(categoryType, (val) => {
    if (val === 'parent') {
        form.parent_id = '';
    }
});

const nextStep = () => {
    form.clearErrors();
    
    if (currentStep.value === 1) {
        if (!form.name.trim()) {
            form.setError('name', 'El nombre es obligatorio');
            return;
        }
        if (categoryType.value === 'child' && !form.parent_id) {
            form.setError('parent_id', 'Seleccione una categoría padre');
            return;
        }
    }
    
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
    // Usamos POST con _method: PUT para compatibilidad con archivos en Laravel
    form.post(route('admin.categories.update', props.category.id), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            const stepWithError = steps.find(step => step.fields.some(f => errors[f]));
            if (stepWithError) currentStep.value = stepWithError.id;
        }
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);

const handleStepClick = (stepId) => {
    if (stepId < currentStep.value) currentStep.value = stepId;
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto pb-12">
            
            <div class="mb-8 px-4 md:px-0">
                <div class="flex justify-between items-start mb-6">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono bg-muted px-2 py-0.5 rounded border border-border">UUID: {{ category.id }}</span>
                            <span v-if="category.is_active" class="flex items-center gap-1 text-[10px] font-black text-success uppercase"><BadgeCheck :size="12"/> Activo</span>
                        </div>
                        <h1 class="text-3xl font-black tracking-tighter">
                            Editar <span class="text-primary">{{ category.name }}</span>
                        </h1>
                    </div>
                    <Link :href="route('admin.categories.index')" class="btn btn-ghost text-muted-foreground hover:text-error transition-colors">
                        Cancelar
                    </Link>
                </div>

                <StepProgress 
                    :steps="steps" 
                    :current-step="currentStep" 
                    :progress-percentage="progressPercentage"
                    @step-click="handleStepClick"
                />
            </div>

            <div class="card border border-border bg-card shadow-2xl overflow-hidden min-h-[550px] flex flex-col mx-4 md:mx-0">
                <form @submit.prevent="submit" class="flex-1 flex flex-col">
                    
                    <div class="p-8 flex-1">
                        <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="space-y-8 animate-in slide-in-from-right-4 fade-in">
                                <CategoryTypeSelector v-model="categoryType" />

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="md:col-span-2 space-y-2">
                                        <label class="form-label font-bold uppercase tracking-widest text-xs text-muted-foreground">Nombre de la Categoría</label>
                                        <input v-model="form.name" type="text" class="form-input text-xl font-black py-4" :class="{'border-error': form.errors.name}">
                                        <p v-if="form.errors.name" class="text-error text-xs flex items-center gap-1 mt-1"><AlertCircle :size="14"/> {{ form.errors.name }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="form-label text-xs uppercase font-bold text-muted-foreground flex items-center gap-2">
                                            <Hash :size="14"/> Código ERP / SKU
                                        </label>
                                        <input v-model="form.external_code" type="text" class="form-input font-mono uppercase" placeholder="Ej: CAT-001">
                                    </div>

                                    <div v-if="categoryType === 'child'" class="md:col-span-2 space-y-4 animate-in zoom-in-95">
                                        <div class="p-5 bg-primary/5 rounded-2xl border border-primary/20">
                                            <label class="form-label text-primary font-black mb-2 block uppercase tracking-tighter">Vincular a Padre</label>
                                            <div class="relative">
                                                <FolderTree class="absolute left-4 top-1/2 -translate-y-1/2 text-primary/40" :size="20"/>
                                                <select v-model="form.parent_id" class="form-input pl-12 bg-background border-primary/20 focus:border-primary">
                                                    <option value="">-- Sin Padre (Principal) --</option>
                                                    <option v-for="cat in parents" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                                </select>
                                            </div>
                                            <p v-if="form.errors.parent_id" class="text-error text-xs mt-2 font-bold">{{ form.errors.parent_id }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="currentStep === 2" key="2" class="space-y-8 animate-in slide-in-from-right-4 fade-in">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                    <div class="lg:col-span-1">
                                        <label class="form-label mb-3 block text-xs font-bold uppercase">Imagen de Identidad</label>
                                        <ImageUploader 
                                            v-model="form.image" 
                                            :existing-image="category.image_url" 
                                            class="h-64 w-full shadow-inner" 
                                        />
                                        <p v-if="form.errors.image" class="text-error text-xs text-center mt-2 font-bold">{{ form.errors.image }}</p>
                                    </div>

                                    <div class="lg:col-span-2 space-y-6">
                                        <div class="space-y-2">
                                            <label class="form-label flex items-center gap-2 text-xs font-bold uppercase">
                                                <FileText :size="14"/> Descripción Pública
                                            </label>
                                            <textarea v-model="form.description" rows="6" class="form-input resize-none leading-relaxed" placeholder="Describe la categoría para los clientes..."></textarea>
                                        </div>

                                        <div class="bg-muted/30 p-6 rounded-2xl border border-border">
                                            <div class="flex items-center gap-2 mb-4">
                                                <Info :size="16" class="text-primary"/>
                                                <h3 class="text-xs font-black uppercase tracking-widest text-foreground">Metadatos SEO</h3>
                                            </div>
                                            <div class="space-y-4">
                                                <input v-model="form.seo_title" type="text" class="form-input text-sm" placeholder="Título para buscadores">
                                                <textarea v-model="form.seo_description" rows="2" class="form-input text-sm resize-none" placeholder="Descripción para buscadores"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else key="3" class="space-y-8 animate-in slide-in-from-right-4 fade-in">
                                <div class="bg-card border border-border rounded-2xl divide-y divide-border shadow-sm">
                                    <div class="p-6">
                                        <h3 class="font-black text-sm uppercase tracking-widest text-muted-foreground mb-6 flex items-center gap-2">
                                            <Settings :size="16"/> Visibilidad & Control
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                            <BaseCheckbox v-model="form.is_active" label="Categoría Activa" help="Habilita la visibilidad en toda la plataforma." />
                                            <BaseCheckbox v-model="form.is_featured" label="Destacar Categoría" help="Promociona esta categoría en el catálogo frontal." />
                                            <BaseCheckbox v-model="form.requires_age_check" label="Restricción +18" help="Activa la validación de mayoría de edad." />
                                        </div>
                                    </div>

                                    <div class="p-6 bg-muted/5">
                                        <label class="form-label text-xs uppercase font-black text-muted-foreground mb-4 block tracking-widest">Parámetros de Negocio</label>
                                        <div class="max-w-md">
                                            <div class="relative">
                                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-primary uppercase">TAX:</span>
                                                <input v-model="form.tax_classification" type="text" class="form-input pl-14 font-mono text-sm uppercase" placeholder="EJ: ALCOHOL_VAT">
                                            </div>
                                            <p class="text-[10px] text-muted-foreground mt-2 italic">Mapeo fiscal para facturación automática.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </Transition>
                    </div>

                    <div class="p-6 bg-background/50 backdrop-blur-sm border-t border-border flex justify-between items-center sticky bottom-0">
                        <button type="button" @click="prevStep" class="btn btn-ghost font-bold" :class="{'invisible': currentStep === 1}">
                            <ArrowLeft :size="18" class="mr-2"/> Atrás
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="btn btn-primary px-10 shadow-xl shadow-primary/20">
                            Continuar <ArrowRight :size="18" class="ml-2"/>
                        </button>

                        <button v-else type="submit" :disabled="form.processing" class="btn btn-primary px-12 shadow-xl shadow-primary/30">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <span class="loading loading-spinner loading-xs"></span> Procesando
                            </span>
                            <span v-else class="flex items-center gap-2 font-black">
                                <Save :size="20"/> GUARDAR CAMBIOS
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.2s ease-in-out; }
.fade-enter-from { opacity: 0; transform: translateY(5px); }
.fade-leave-to { opacity: 0; transform: translateY(-5px); }
</style>