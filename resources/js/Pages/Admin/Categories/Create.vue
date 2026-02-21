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
    Hash, FileText, Plus, Trash2, Layers
} from 'lucide-vue-next';

const props = defineProps({
    parents: Array // Solo contiene {id, name}
});

const currentStep = ref(1);
const categoryType = ref('parent'); 

const steps = [
    { id: 1, title: 'Jerarquía', icon: FolderTree, fields: ['name', 'parent_id', 'children'] },
    { id: 2, title: 'Contenido', icon: ImageIcon, fields: ['image', 'description'] },
    { id: 3, title: 'Ajustes & SEO', icon: Settings, fields: ['tax_classification', 'seo_title'] },
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
    // Lógica jerárquica:
    children: [] 
});

// --- ACCIONES DE SUBCATEGORÍAS ---
const addChild = () => {
    form.children.push({ name: '', external_code: '' });
};

const removeChild = (index) => {
    form.children.splice(index, 1);
};

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
            form.setError('name', 'El nombre es obligatorio');
            return;
        }
        if (categoryType.value === 'child' && !form.parent_id) {
            form.setError('parent_id', 'Seleccione un padre');
            return;
        }
    }
    if (currentStep.value < steps.length) currentStep.value++;
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
        }
    });
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto pb-12">
            <div class="mb-8 flex justify-between items-end px-4 md:px-0">
                <div>
                    <h1 class="text-3xl font-black tracking-tight">Nueva Categoría</h1>
                    <p class="text-muted-foreground">Arquitectura de catálogo de alto rendimiento</p>
                </div>
                <Link :href="route('admin.categories.index')" class="btn btn-ghost text-muted-foreground">Cancelar</Link>
            </div>

            <StepProgress :steps="steps" :current-step="currentStep" :progress-percentage="progressPercentage" />

            <div class="card border border-border bg-card shadow-2xl mt-8">
                <form @submit.prevent="submit">
                    <div class="p-8 min-h-[450px]">
                        <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="space-y-6">
                                <CategoryTypeSelector v-model="categoryType" />

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="form-label">Nombre Principal</label>
                                        <input v-model="form.name" type="text" class="form-input text-lg font-bold" placeholder="Ej: Bebidas">
                                        <p v-if="form.errors.name" class="text-error text-xs mt-1">{{ form.errors.name }}</p>
                                    </div>

                                    <div v-if="categoryType === 'child'" class="md:col-span-2 animate-in fade-in slide-in-from-top-4">
                                        <label class="form-label text-primary">Asignar a Categoría Padre</label>
                                        <select v-model="form.parent_id" class="form-input border-primary/40">
                                            <option value="">-- Seleccionar Padre --</option>
                                            <option v-for="cat in parents" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                        </select>
                                    </div>

                                    <div v-if="categoryType === 'parent'" class="md:col-span-2 space-y-4 border-t pt-6">
                                        <div class="flex justify-between items-center">
                                            <label class="text-sm font-bold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                                                <Layers :size="16"/> Subcategorías Inmediatas
                                            </label>
                                            <button type="button" @click="addChild" class="btn btn-xs btn-primary">
                                                <Plus :size="14"/> Añadir Hijo
                                            </button>
                                        </div>
                                        
                                        <div v-if="form.children.length === 0" class="text-center py-8 border-2 border-dashed rounded-xl text-muted-foreground text-sm">
                                            Opcional: Define subcategorías para crearlas en bloque.
                                        </div>
                                        <div v-for="(child, index) in form.children" :key="index" 
                                                class="p-4 bg-muted/10 border border-border rounded-xl space-y-4 animate-in zoom-in-95">
                                                
                                                <div class="flex justify-between items-start">
                                                    <span class="text-[10px] font-black bg-primary/10 text-primary px-2 py-0.5 rounded">HIJO #{{ index + 1 }}</span>
                                                    <button type="button" @click="removeChild(index)" class="text-error hover:bg-error/10 p-1.5 rounded-lg transition-colors">
                                                        <Trash2 :size="16"/>
                                                    </button>
                                                </div>

                                                <div class="flex flex-col md:flex-row gap-4">
                                                    <div class="w-full md:w-24 h-24 shrink-0">
                                                        <ImageUploader v-model="child.image" class="h-full w-full rounded-lg" />
                                                    </div>

                                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div class="space-y-1">
                                                            <label class="text-[10px] uppercase font-bold text-muted-foreground">Nombre</label>
                                                            <input v-model="child.name" placeholder="Ej: Subcategoría A" class="form-input text-sm">
                                                            <p v-if="form.errors[`children.${index}.name`]" class="text-error text-[10px]">{{ form.errors[`children.${index}.name`] }}</p>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="text-[10px] uppercase font-bold text-muted-foreground">Código ERP</label>
                                                            <input v-model="child.external_code" placeholder="ERP-XXX" class="form-input text-sm font-mono">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button" @click="addChild" 
                                                    class="w-full py-4 border-2 border-dashed border-primary/20 rounded-xl text-primary/60 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-all flex items-center justify-center gap-2 text-sm font-bold">
                                                <Plus :size="18"/> Añadir otra subcategoría
                                            </button>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="currentStep === 2" key="2" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <div class="md:col-span-1">
                                    <label class="form-label">Imagen Representativa</label>
                                    <ImageUploader v-model="form.image" class="h-64 shadow-inner" />
                                </div>
                                <div class="md:col-span-2 space-y-4">
                                    <label class="form-label">Descripción Operativa</label>
                                    <textarea v-model="form.description" rows="8" class="form-input resize-none" placeholder="Detalles internos o comerciales..."></textarea>
                                </div>
                            </div>

                            <div v-else key="3" class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-4">
                                        <h3 class="text-sm font-black uppercase text-muted-foreground border-b pb-2">Configuración Base</h3>
                                        <BaseCheckbox v-model="form.is_active" label="Categoría Activa" help="Visible en el catálogo público" />
                                        <BaseCheckbox v-model="form.is_featured" label="Destacar en Home" help="Aparece en carruseles principales" />
                                        <BaseCheckbox v-model="form.requires_age_check" label="Restricción +18" help="Obliga validación de edad al comprar" />
                                    </div>
                                    <div class="space-y-4">
                                        <h3 class="text-sm font-black uppercase text-muted-foreground border-b pb-2">SEO & Impuestos</h3>
                                        <div>
                                            <label class="form-label text-xs">Clasificación Fiscal (ICE/IVA)</label>
                                            <input v-model="form.tax_classification" type="text" class="form-input font-mono" placeholder="GENERIC_TAX">
                                        </div>
                                        <div>
                                            <label class="form-label text-xs">SEO Title</label>
                                            <input v-model="form.seo_title" type="text" class="form-input" placeholder="Meta title para buscadores">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </Transition>
                    </div>

                    <div class="p-6 bg-muted/30 border-t flex justify-between items-center rounded-b-xl">
                        <button type="button" @click="prevStep" class="btn btn-ghost" :class="{'invisible': currentStep === 1}">
                            <ArrowLeft :size="18" class="mr-2"/> Anterior
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="btn btn-primary px-8">
                            Siguiente <ArrowRight :size="18" class="ml-2"/>
                        </button>

                        <button v-else type="submit" :disabled="form.processing" class="btn btn-primary px-10 shadow-lg shadow-primary/30">
                            <span v-if="form.processing" class="loading loading-spinner"></span>
                            <Save v-else :size="18" class="mr-2"/> Finalizar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.25s ease; }
.fade-enter-from { opacity: 0; transform: translateY(10px); }
.fade-leave-to { opacity: 0; transform: translateY(-10px); }
</style>