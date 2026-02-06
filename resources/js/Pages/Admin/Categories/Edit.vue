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
    Info, Hash, FileText
} from 'lucide-vue-next';

const props = defineProps({
    category: Object,
    parents: Array
});

// --- ESTADO ---
const currentStep = ref(1);
// Detectar si es padre o hijo al cargar
const categoryType = ref(props.category.parent_id ? 'child' : 'parent');

const steps = [
    { id: 1, title: 'Jerarquía', icon: FolderTree, fields: ['name', 'external_code', 'parent_id'] },
    { id: 2, title: 'Contenido', icon: ImageIcon, fields: ['image', 'description', 'seo_title'] },
    { id: 3, title: 'Ajustes', icon: Settings, fields: ['tax_classification', 'requires_age_check'] },
];

const form = useForm({
    _method: 'PUT', // Importante para updates en Laravel
    name: props.category.name,
    parent_id: props.category.parent_id || '',
    external_code: props.category.external_code || '',
    description: props.category.description || '',
    image: null, // Solo enviamos si hay cambio
    seo_title: props.category.seo_title || '',
    seo_description: props.category.seo_description || '',
    tax_classification: props.category.tax_classification || '',
    requires_age_check: !!props.category.requires_age_check,
    is_active: !!props.category.is_active,
    is_featured: !!props.category.is_featured,
});

// --- LÓGICA ---

// Resetear parent_id si cambiamos a "Categoría Principal"
watch(categoryType, (val) => {
    if (val === 'parent') {
        form.parent_id = '';
        form.clearErrors('parent_id');
    }
});

const nextStep = () => {
    form.clearErrors();
    
    // Validación manual paso 1
    if (currentStep.value === 1) {
        let valid = true;
        if (!form.name.trim()) {
            form.setError('name', 'El nombre es obligatorio');
            valid = false;
        }
        if (categoryType.value === 'child' && !form.parent_id) {
            form.setError('parent_id', 'Debes seleccionar una categoría padre');
            valid = false;
        }
        // Evitar que una categoría sea padre de sí misma (validación frontend básica)
        if (form.parent_id === props.category.id) {
            form.setError('parent_id', 'Una categoría no puede ser padre de sí misma');
            valid = false;
        }
        if (!valid) return;
    }
    
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
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
    if (stepId < currentStep.value) {
        currentStep.value = stepId;
    }
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto pb-24 md:pb-12">
            
            <div class="mb-8 px-4 md:px-0">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="badge badge-primary text-[10px] h-5 px-1.5 font-mono">ID: {{ category.id }}</span>
                            <span v-if="!category.is_active" class="badge badge-error text-[10px] h-5 px-1.5 font-bold">INACTIVO</span>
                            <span v-if="category.is_featured" class="badge badge-warning text-[10px] h-5 px-1.5 font-bold">DESTACADO</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-display font-black text-foreground tracking-tight flex items-center gap-2">
                            Editar: <span class="text-primary truncate max-w-[200px] md:max-w-md">{{ category.name }}</span>
                        </h1>
                    </div>
                    <Link :href="route('admin.categories.index')" 
                          class="btn btn-ghost btn-sm text-muted-foreground hover:text-error">
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

            <div class="card overflow-hidden border border-border shadow-xl min-h-[500px] flex flex-col mx-4 md:mx-0">
                <form class="flex-1 flex flex-col" @submit.prevent>
                    
                    <div class="p-6 md:p-8 flex-1 relative">
                        <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="space-y-8 animate-in slide-in-from-right-4 fade-in">
                                <CategoryTypeSelector v-model="categoryType" />

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2 space-y-2">
                                        <label class="form-label text-base">Nombre de la Categoría *</label>
                                        <input v-model="form.name" type="text" 
                                               class="form-input text-lg font-bold py-3"
                                               :class="{'border-error ring-error/20': form.errors.name}">
                                        <p v-if="form.errors.name" class="form-error flex items-center gap-1">
                                            <AlertCircle :size="14"/> {{ form.errors.name }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="form-label flex items-center gap-2">
                                            <Hash :size="14" class="text-muted-foreground"/> Código ERP / SKU
                                        </label>
                                        <input v-model="form.external_code" type="text" 
                                               class="form-input font-mono text-sm">
                                    </div>

                                    <div v-if="categoryType === 'child'" 
                                         class="md:col-span-2 bg-primary/5 p-4 rounded-xl border border-primary/20 animate-in zoom-in-95">
                                        <label class="form-label text-primary font-bold mb-2 block">Categoría Padre *</label>
                                        <div class="relative">
                                            <FolderTree class="absolute left-3 top-1/2 -translate-y-1/2 text-primary/50" :size="18"/>
                                            <select v-model="form.parent_id" 
                                                    class="form-input pl-10 w-full bg-background"
                                                    :class="{'border-error': form.errors.parent_id}">
                                                <option value="" disabled>-- Seleccionar --</option>
                                                <option v-for="cat in parents.filter(p => p.id !== category.id)" 
                                                        :key="cat.id" :value="cat.id">
                                                    {{ cat.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <p v-if="form.errors.parent_id" class="form-error mt-1">{{ form.errors.parent_id }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="currentStep === 2" key="2" class="space-y-8 animate-in slide-in-from-right-4 fade-in">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                    <div class="lg:col-span-1 space-y-2">
                                        <label class="form-label">Imagen de Portada</label>
                                        <ImageUploader v-model="form.image" :existing-image="category.image_url" class="h-64 w-full" />
                                        <p v-if="form.errors.image" class="form-error text-center">{{ form.errors.image }}</p>
                                    </div>

                                    <div class="lg:col-span-2 space-y-6">
                                        <div class="space-y-2">
                                            <label class="form-label flex items-center gap-2">
                                                <FileText :size="14"/> Descripción Corta
                                            </label>
                                            <textarea v-model="form.description" rows="4" 
                                                      class="form-input resize-none leading-relaxed"></textarea>
                                        </div>

                                        <div class="bg-muted/30 p-5 rounded-xl border border-border/50">
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="p-1.5 bg-success/10 rounded-md">
                                                    <Info :size="16" class="text-success"/>
                                                </div>
                                                <h3 class="text-xs font-bold text-foreground uppercase tracking-wider">Optimización SEO</h3>
                                            </div>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="text-xs font-medium text-muted-foreground mb-1 block">Meta Título</label>
                                                    <input v-model="form.seo_title" type="text" class="form-input text-sm h-9">
                                                </div>
                                                <div>
                                                    <label class="text-xs font-medium text-muted-foreground mb-1 block">Meta Descripción</label>
                                                    <textarea v-model="form.seo_description" rows="2" class="form-input text-sm resize-none"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else key="3" class="space-y-6 animate-in slide-in-from-right-4 fade-in">
                                <div class="bg-card border border-border rounded-xl divide-y divide-border overflow-hidden">
                                    <div class="p-4 bg-muted/20">
                                        <h3 class="font-bold text-sm text-foreground flex items-center gap-2">
                                            <Settings :size="16" class="text-primary"/> Configuración de Visibilidad
                                        </h3>
                                    </div>

                                    <div class="p-4">
                                        <BaseCheckbox v-model="form.is_active" class="w-full">
                                            <div class="flex items-center justify-between group cursor-pointer">
                                                <div>
                                                    <span class="block text-sm font-bold text-foreground group-hover:text-primary transition-colors">Categoría Activa</span>
                                                    <span class="text-xs text-muted-foreground">Visible en catálogo y puntos de venta</span>
                                                </div>
                                                <div :class="`w-12 h-6 rounded-full p-1 transition-colors ${form.is_active ? 'bg-success' : 'bg-muted'}`">
                                                    <div :class="`w-4 h-4 bg-white rounded-full shadow-sm transition-transform ${form.is_active ? 'translate-x-6' : 'translate-x-0'}`"></div>
                                                </div>
                                            </div>
                                        </BaseCheckbox>
                                    </div>

                                    <div class="p-4">
                                        <BaseCheckbox v-model="form.is_featured" class="w-full">
                                            <div class="flex items-center justify-between group cursor-pointer">
                                                <div>
                                                    <span class="block text-sm font-bold text-foreground group-hover:text-warning transition-colors">Destacada</span>
                                                    <span class="text-xs text-muted-foreground">Aparecerá en carruseles principales</span>
                                                </div>
                                                <div :class="`w-12 h-6 rounded-full p-1 transition-colors ${form.is_featured ? 'bg-warning' : 'bg-muted'}`">
                                                    <div :class="`w-4 h-4 bg-white rounded-full shadow-sm transition-transform ${form.is_featured ? 'translate-x-6' : 'translate-x-0'}`"></div>
                                                </div>
                                            </div>
                                        </BaseCheckbox>
                                    </div>

                                    <div class="p-4">
                                        <BaseCheckbox v-model="form.requires_age_check" class="w-full">
                                            <div class="flex items-center justify-between group cursor-pointer">
                                                <div>
                                                    <span class="block text-sm font-bold text-foreground group-hover:text-error transition-colors flex items-center gap-2">
                                                        Restricción de Edad <AlertCircle :size="14" class="text-error"/>
                                                    </span>
                                                    <span class="text-xs text-muted-foreground">Requiere validación +18</span>
                                                </div>
                                                <div :class="`w-12 h-6 rounded-full p-1 transition-colors ${form.requires_age_check ? 'bg-error' : 'bg-muted'}`">
                                                    <div :class="`w-4 h-4 bg-white rounded-full shadow-sm transition-transform ${form.requires_age_check ? 'translate-x-6' : 'translate-x-0'}`"></div>
                                                </div>
                                            </div>
                                        </BaseCheckbox>
                                    </div>
                                </div>

                                <div class="space-y-2 pt-2">
                                    <label class="form-label text-xs uppercase tracking-wider text-muted-foreground">Facturación</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-mono text-xs">ICE:</span>
                                        <input v-model="form.tax_classification" type="text" 
                                               class="form-input pl-10 font-mono text-sm uppercase">
                                    </div>
                                    <p class="text-[10px] text-muted-foreground">Código de clasificación para impuestos especiales.</p>
                                </div>
                            </div>

                        </Transition>
                    </div>

                    <div class="p-4 bg-background/80 backdrop-blur-md border-t border-border flex justify-between items-center sticky bottom-0 z-10">
                        <button type="button" @click="prevStep" 
                                class="btn btn-ghost hover:bg-muted text-muted-foreground"
                                :class="{'invisible': currentStep === 1}">
                            <ArrowLeft :size="18" class="mr-2"/> Atrás
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep"
                                class="btn btn-primary px-6 shadow-lg shadow-primary/20">
                            Siguiente <ArrowRight :size="18" class="ml-2"/>
                        </button>

                        <button v-else type="button" @click="submit"
                                :disabled="form.processing"
                                class="btn btn-primary px-8 shadow-lg shadow-primary/20">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <span class="loading loading-spinner loading-sm"></span> Guardando...
                            </span>
                            <span v-else class="flex items-center gap-2">
                                <Save :size="18"/> Actualizar
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-enter-from {
    opacity: 0;
    transform: translateX(10px);
}
.fade-leave-to {
    opacity: 0;
    transform: translateX(-10px);
    position: absolute;
    width: 100%;
}
</style>