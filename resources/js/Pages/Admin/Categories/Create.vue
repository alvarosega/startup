<!-- resources/js/Pages/Admin/Categories/Create.vue -->
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
    ArrowRight, ArrowLeft, Save, AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    parents: Array
});

const currentStep = ref(1);
const categoryType = ref('parent');

const steps = [
    { id: 1, title: 'Jerarquía', icon: FolderTree, fields: ['name', 'external_code', 'parent_id'] },
    { id: 2, title: 'Contenido', icon: ImageIcon, fields: ['image', 'description', 'seo_title'] },
    { id: 3, title: 'Ajustes', icon: Settings, fields: ['tax_classification', 'requires_age_check'] },
];

const form = useForm({
    name: '',
    parent_id: '', 
    external_code: '',
    description: '',
    image: null,
    seo_title: '',
    seo_description: '',
    tax_classification: '',
    requires_age_check: false,
    is_active: true,
    is_featured: false,
});

// Resetear parent_id si cambiamos a "Categoría Principal"
watch(categoryType, (val) => {
    if (val === 'parent') form.parent_id = ''; 
});

// --- NAVEGACIÓN ---
const nextStep = () => {
    if (currentStep.value === 1) {
        if (!form.name) {
            form.setError('name', 'El nombre es obligatorio');
            return;
        }
        if (categoryType.value === 'child' && !form.parent_id) {
            form.setError('parent_id', 'Debes seleccionar una categoría padre');
            return;
        }
    }
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

// --- ENVÍO (Multipart/Form-Data) ---
const submit = () => {
    form.post(route('admin.categories.store'), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            const stepWithError = steps.find(step => 
                step.fields.some(f => errors[f])
            );
            if (stepWithError) currentStep.value = stepWithError.id;
        }
    });
};

const progressPercentage = computed(() => {
    return ((currentStep.value - 1) / (steps.length - 1)) * 100;
});

const handleStepClick = (stepId) => {
    if (currentStep.value >= stepId) {
        currentStep.value = stepId;
    }
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto py-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-display font-semibold text-foreground tracking-tight">
                            Nueva Categoría
                        </h1>
                        <p class="text-muted-foreground text-sm mt-1">Define la estructura de tu catálogo</p>
                    </div>
                    <Link :href="route('admin.categories.index')" 
                          class="text-sm font-medium text-muted-foreground hover:text-error transition-colors">
                        Cancelar
                    </Link>
                </div>

                <!-- Progress Steps -->
                <StepProgress 
                    :steps="steps" 
                    :current-step="currentStep" 
                    :progress-percentage="progressPercentage"
                    @step-click="handleStepClick"
                />
            </div>

            <!-- Form Container -->
            <div class="card min-h-[500px] flex flex-col">
                <form class="flex-1 flex flex-col">
                    <!-- Form Content -->
                    <div class="p-6 md:p-8 flex-1">
                        <Transition name="fade" mode="out-in">
                            <!-- Step 1: Jerarquía -->
                            <div v-if="currentStep === 1" key="1" class="space-y-8 animate-in">
                                <!-- Selector de tipo de categoría -->
                                <CategoryTypeSelector v-model="categoryType" />
                                
                                <!-- Campos del formulario -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="form-group md:col-span-2">
                                        <label class="form-label">Nombre *</label>
                                        <input v-model="form.name" type="text" 
                                               class="font-bold text-lg"
                                               :class="{'border-error': form.errors.name}"
                                               placeholder="Ej: Vinos Tintos">
                                        <p v-if="form.errors.name" class="form-error">
                                            <AlertCircle class="inline mr-1" :size="12"/> 
                                            {{ form.errors.name }}
                                        </p>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Código ERP</label>
                                        <input v-model="form.external_code" type="text" 
                                               class="font-mono"
                                               placeholder="CAT-00X">
                                    </div>

                                    <!-- Selector de categoría padre (solo para subcategorías) -->
                                    <div v-if="categoryType === 'child'" 
                                         class="md:col-span-2 bg-muted/30 p-4 rounded-lg border border-input animate-in">
                                        <label class="form-label text-primary">Pertenece a:</label>
                                        <select v-model="form.parent_id" 
                                                class="w-full bg-card"
                                                :class="{'border-error': form.errors.parent_id}">
                                            <option value="" disabled>-- Selecciona una Categoría Padre --</option>
                                            <option v-for="cat in parents" :key="cat.id" :value="cat.id">
                                                {{ cat.name }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.parent_id" class="form-error">{{ form.errors.parent_id }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Contenido -->
                            <div v-else-if="currentStep === 2" key="2" class="space-y-6 animate-in">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                    <!-- Uploader de imagen -->
                                    <div class="lg:col-span-1">
                                        <ImageUploader v-model="form.image" />
                                        <p v-if="form.errors.image" class="form-error text-center">{{ form.errors.image }}</p>
                                    </div>

                                    <!-- Descripción y SEO -->
                                    <div class="lg:col-span-2 space-y-4">
                                        <div class="form-group">
                                            <label class="form-label">Descripción Corta</label>
                                            <textarea v-model="form.description" rows="3" 
                                                      class="resize-none"
                                                      placeholder="Breve descripción para mostrar en la app..."></textarea>
                                        </div>

                                        <!-- SEO Section -->
                                        <div class="bg-muted/30 p-4 rounded-lg border border-input">
                                            <h3 class="text-xs font-medium text-success uppercase mb-3 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-success"></span> 
                                                Optimización SEO
                                            </h3>
                                            <div class="space-y-3">
                                                <div class="form-group">
                                                    <label class="form-label text-xs">Meta Título (Google)</label>
                                                    <input v-model="form.seo_title" type="text">
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label text-xs">Meta Descripción (Google)</label>
                                                    <textarea v-model="form.seo_description" rows="2" 
                                                              class="resize-none"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Ajustes -->
                            <div v-else key="3" class="space-y-6 animate-in">
                                <div class="bg-muted/30 p-6 rounded-xl border border-border">
                                    <h3 class="text-sm font-bold text-foreground mb-4 flex items-center gap-2">
                                        <Settings :size="18" class="text-primary" /> 
                                        Configuración General
                                    </h3>
                                    
                                    <div class="space-y-3">
                                        <BaseCheckbox v-model="form.is_active" 
                                                      class="w-full p-3 bg-background border border-input rounded-xl hover:border-primary transition-colors">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-foreground">Categoría Activa</span>
                                                <span class="text-xs text-muted-foreground">Visible en catálogo y POS</span>
                                            </div>
                                        </BaseCheckbox>

                                        <BaseCheckbox v-model="form.is_featured" 
                                                      class="w-full p-3 bg-background border border-input rounded-xl hover:border-warning transition-colors">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-foreground">Destacada</span>
                                                <span class="text-xs text-muted-foreground">Mostrar en el inicio de la App</span>
                                            </div>
                                        </BaseCheckbox>

                                        <BaseCheckbox v-model="form.requires_age_check" 
                                                      class="w-full p-3 bg-background border border-input rounded-xl hover:border-error transition-colors">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-foreground flex items-center gap-2">
                                                    Restricción de Edad 
                                                    <AlertCircle :size="14" class="text-error"/>
                                                </span>
                                                <span class="text-xs text-muted-foreground">Requiere verificación +18 para comprar</span>
                                            </div>
                                        </BaseCheckbox>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Clasificación Fiscal (ICE)</label>
                                    <input v-model="form.tax_classification" type="text" 
                                           class="form-input font-mono text-sm"
                                           placeholder="Ej: ALCOHOL_ICE_GENERIC">
                                    <p class="form-helper">Código para facturación electrónica.</p>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <!-- Form Footer / Navigation -->
                    <div class="px-6 md:px-8 py-4 bg-muted/30 border-t border-input flex justify-between items-center">
                        <button type="button" @click="prevStep" 
                                class="btn btn-ghost btn-sm"
                                :disabled="currentStep === 1">
                            <ArrowLeft :size="16" /> Atrás
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep"
                                class="btn btn-outline">
                            Siguiente <ArrowRight :size="16" />
                        </button>

                        <button v-else type="button" @click="submit"
                                :disabled="form.processing"
                                class="btn btn-primary">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <span class="spinner spinner-sm"></span> Guardando...
                            </span>
                            <span v-else class="flex items-center gap-2">
                                <Save :size="18" /> Guardar Categoría
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
    transition: opacity 0.2s var(--ease-smooth); 
}
.fade-enter-from, .fade-leave-to { 
    opacity: 0; 
}
</style>