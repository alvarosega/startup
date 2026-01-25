<script setup>
import { ref, computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Package, Layers, Tag, Barcode, DollarSign, Scale, 
    Plus, Trash2, ArrowRight, ArrowLeft, Save, CheckCircle, 
    UploadCloud, Info, Wine, AlertCircle, Image as ImageIcon 
} from 'lucide-vue-next';

const props = defineProps({
    brands: Array,
    categories: Array
});

// --- REFS para manejo de imágenes ---
const masterImageInputRef = ref(null);
const masterImagePreview = ref(null);
const skuImageInputRefs = ref([]);
const skuImagePreviews = ref({});

// --- Stepper ---
const currentStep = ref(1);
const steps = [
    { id: 1, title: 'Concepto', icon: Package },
    { id: 2, title: 'Detalles', icon: Info },
    { id: 3, title: 'Variantes', icon: Barcode },
];
const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);

// --- Form ---
const form = useForm({
    name: '',
    brand_id: '',
    category_id: '',
    description: '',
    image: null,
    is_active: true,
    is_alcoholic: false,
    skus: [
        { 
            name: '', 
            code: '', 
            price: '', 
            conversion_factor: 1, 
            weight: 0,
            image: null 
        }
    ]
});

// --- Manejadores de imágenes ---
const triggerMasterImageInput = () => {
    if (masterImageInputRef.value) {
        masterImageInputRef.value.click();
    }
};

const handleMasterImage = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            masterImagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const triggerSkuImageInput = (index) => {
    if (skuImageInputRefs.value[index]) {
        skuImageInputRefs.value[index].click();
    }
};

const handleSkuImage = (e, index) => {
    const file = e.target.files[0];
    if (file) {
        form.skus[index].image = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            skuImagePreviews.value[index] = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const clearMasterImage = () => {
    form.image = null;
    masterImagePreview.value = null;
    if (masterImageInputRef.value) {
        masterImageInputRef.value.value = '';
    }
};

const clearSkuImage = (index) => {
    form.skus[index].image = null;
    skuImagePreviews.value[index] = null;
    if (skuImageInputRefs.value[index]) {
        skuImageInputRefs.value[index].value = '';
    }
};

// --- SKUs ---
const addSku = () => {
    const newSku = { 
        name: form.name ? `${form.name} ` : '', 
        code: '', 
        price: '', 
        conversion_factor: 1, 
        weight: 0,
        image: null
    };
    form.skus.push(newSku);
    skuImageInputRefs.value.push(null);
};

const removeSku = (index) => {
    if (form.skus.length > 1) {
        form.skus.splice(index, 1);
        skuImageInputRefs.value.splice(index, 1);
        delete skuImagePreviews.value[index];
    } else {
        alert("Se requiere al menos una variante.");
    }
};

const copyNameToSku = () => {
    if (form.name && (!form.skus[0].name || form.skus[0].name.trim() === '')) {
        form.skus[0].name = `${form.name} (Unidad)`;
    }
};

// --- Validaciones ---
const validateConversionFactor = (index) => {
    const value = form.skus[index].conversion_factor;
    if (value > 1000) {
        form.skus[index].conversion_factor = 1000;
        alert("El factor de conversión no puede ser mayor a 1000");
    } else if (value < 0.001) {
        form.skus[index].conversion_factor = 0.001;
        alert("El factor de conversión debe ser al menos 0.001");
    }
};

const validateStep1 = () => {
    if (!form.name.trim()) {
        form.setError('name', 'El nombre del producto es obligatorio');
        return false;
    }
    if (!form.brand_id) {
        form.setError('brand_id', 'Selecciona una marca');
        return false;
    }
    if (!form.category_id) {
        form.setError('category_id', 'Selecciona una categoría');
        return false;
    }
    return true;
};

// --- Navegación ---
const nextStep = () => {
    if (currentStep.value === 1) {
        if (!validateStep1()) return;
        copyNameToSku();
    }
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => { 
    if (currentStep.value > 1) currentStep.value--; 
};

// --- Submit ---
const submit = () => {
    form.clearErrors();
    
    // Validación de códigos duplicados
    const codes = form.skus.map(s => s.code).filter(c => c && c.trim() !== '');
    if (new Set(codes).size !== codes.length) {
        alert("Error: Códigos EAN duplicados.");
        currentStep.value = 3;
        return;
    }

    form.post(route('admin.products.store'), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            const keys = Object.keys(errors);
            if (keys.some(k => k.startsWith('skus'))) currentStep.value = 3;
            else if (keys.includes('image') || keys.includes('description')) currentStep.value = 2;
            else currentStep.value = 1;
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Nuevo Producto" />
        
        <div class="max-w-6xl mx-auto py-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-black text-foreground tracking-tight">Alta de Producto</h1>
                        <p class="text-muted-foreground text-sm mt-1">Configuración Maestra y Variantes</p>
                    </div>
                    <Link 
                        :href="route('admin.products.index')" 
                        class="text-sm font-bold text-muted-foreground hover:text-error transition-colors"
                    >
                        Cancelar
                    </Link>
                </div>

                <!-- Stepper -->
                <div class="relative px-4">
                    <div class="absolute top-5 left-0 w-full h-1 bg-border -z-10 rounded-full"></div>
                    <div 
                        class="absolute top-5 left-0 h-1 bg-primary -z-10 rounded-full transition-all duration-500 ease-out" 
                        :style="{ width: progressPercentage + '%' }"
                    ></div>
                    
                    <div class="flex justify-between">
                        <div 
                            v-for="step in steps" 
                            :key="step.id" 
                            class="flex flex-col items-center gap-2 cursor-pointer"
                            @click="currentStep >= step.id ? currentStep = step.id : null"
                        >
                            <div 
                                class="w-10 h-10 rounded-full flex items-center justify-center border-2 bg-card transition-all"
                                :class="[
                                    currentStep === step.id ? 'border-primary text-primary scale-110 shadow-lg' : 
                                    currentStep > step.id ? 'border-success bg-success text-white' : 
                                    'border-border text-muted-foreground'
                                ]"
                            >
                                <CheckCircle v-if="currentStep > step.id" :size="20" />
                                <component v-else :is="step.icon" :size="18" />
                            </div>
                            <span 
                                class="text-[10px] font-bold uppercase tracking-wider bg-background px-2"
                                :class="currentStep >= step.id ? 'text-foreground' : 'text-muted-foreground'"
                            >
                                {{ step.title }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas de errores -->
            <div 
                v-if="Object.keys(form.errors).length > 0" 
                class="mb-6 alert alert-error"
            >
                <div class="flex items-start gap-3">
                    <AlertCircle :size="20" />
                    <div>
                        <h4 class="font-bold">Revisa el formulario</h4>
                        <p class="text-sm mt-1">Hay campos con errores en el paso actual.</p>
                    </div>
                </div>
            </div>

            <!-- Formulario principal -->
            <div class="card shadow-xl overflow-hidden min-h-[500px] flex flex-col">
                <form class="flex-1 flex flex-col" @submit.prevent>
                    <div class="p-8 flex-1">
                        <Transition name="fade" mode="out-in">
                            
                            <!-- Paso 1: Concepto -->
                            <div v-if="currentStep === 1" key="1" class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="col-span-2">
                                        <label class="form-label">
                                            Nombre Maestro *
                                        </label>
                                        <input 
                                            v-model="form.name" 
                                            type="text" 
                                            class="w-full text-lg font-bold"
                                            placeholder="Ej: Ron Abuelo"
                                            :class="{ 'border-error': form.errors.name }"
                                            @blur="copyNameToSku"
                                        />
                                        <p v-if="form.errors.name" class="form-error">
                                            {{ form.errors.name }}
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label class="form-label">Marca *</label>
                                        <select 
                                            v-model="form.brand_id" 
                                            class="w-full"
                                            :class="{ 'border-error': form.errors.brand_id }"
                                        >
                                            <option value="" disabled>Seleccionar...</option>
                                            <option 
                                                v-for="b in brands" 
                                                :key="b.id" 
                                                :value="b.id"
                                            >
                                                {{ b.name }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.brand_id" class="form-error">
                                            {{ form.errors.brand_id }}
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label class="form-label">Categoría *</label>
                                        <select 
                                            v-model="form.category_id" 
                                            class="w-full"
                                            :class="{ 'border-error': form.errors.category_id }"
                                        >
                                            <option value="" disabled>Seleccionar...</option>
                                            <option 
                                                v-for="c in categories" 
                                                :key="c.id" 
                                                :value="c.id"
                                            >
                                                {{ c.name }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.category_id" class="form-error">
                                            {{ form.errors.category_id }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Paso 2: Detalles -->
                            <div v-else-if="currentStep === 2" key="2" class="space-y-6">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                    <div class="lg:col-span-1">
                                        <label class="form-label">Imagen Maestra</label>
                                        
                                        <!-- Input oculto -->
                                        <input
                                            ref="masterImageInputRef"
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="handleMasterImage"
                                        />
                                        
                                        <!-- Área de drop/clic -->
                                        <div 
                                            class="relative w-full aspect-square border-2 border-dashed border-border bg-card rounded-lg flex flex-col items-center justify-center cursor-pointer group transition-all hover:border-primary"
                                            @click="triggerMasterImageInput"
                                            @dragover.prevent
                                            @drop.prevent="e => handleMasterImage({ target: { files: e.dataTransfer.files } })"
                                        >
                                            <img 
                                                v-if="masterImagePreview" 
                                                :src="masterImagePreview" 
                                                class="absolute inset-0 w-full h-full object-cover"
                                            />
                                            
                                            <div 
                                                v-else
                                                class="text-center p-4"
                                            >
                                                <UploadCloud :size="32" class="mx-auto text-muted-foreground mb-2" />
                                                <p class="text-sm font-medium text-muted-foreground">Subir Portada</p>
                                                <p class="text-xs text-muted-foreground mt-1">Haz clic o arrastra una imagen</p>
                                            </div>
                                            
                                            <!-- Overlay para cambiar imagen -->
                                            <div 
                                                v-if="masterImagePreview"
                                                class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                                            >
                                                <p class="text-white text-sm font-medium">Cambiar imagen</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Controles de imagen -->
                                        <div v-if="masterImagePreview" class="flex items-center justify-between mt-2">
                                            <button
                                                type="button"
                                                @click="clearMasterImage"
                                                class="text-xs text-error hover:text-error/80 transition-colors"
                                            >
                                                Eliminar imagen
                                            </button>
                                            <span class="text-xs text-success">
                                                Imagen cargada
                                            </span>
                                        </div>
                                        
                                        <p v-if="form.errors.image" class="form-error">
                                            {{ form.errors.image }}
                                        </p>
                                    </div>

                                    <div class="lg:col-span-2 space-y-6">
                                        <div>
                                            <label class="form-label">Descripción</label>
                                            <textarea
                                                v-model="form.description"
                                                rows="4"
                                                class="w-full resize-none"
                                                placeholder="Describe las características y beneficios del producto..."
                                                :class="{ 'border-error': form.errors.description }"
                                            ></textarea>
                                            <p v-if="form.errors.description" class="form-error">
                                                {{ form.errors.description }}
                                            </p>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <label class="flex items-center gap-3 p-3 border border-border rounded-lg cursor-pointer hover:bg-muted/20 transition">
                                                <input 
                                                    v-model="form.is_active" 
                                                    type="checkbox" 
                                                    class="w-5 h-5 text-primary rounded focus:ring-primary"
                                                />
                                                <span class="font-medium text-foreground">Activo</span>
                                            </label>
                                            
                                            <label class="flex items-center gap-3 p-3 border border-border rounded-lg cursor-pointer hover:bg-muted/20 transition">
                                                <input 
                                                    v-model="form.is_alcoholic" 
                                                    type="checkbox" 
                                                    class="w-5 h-5 text-warning rounded focus:ring-warning"
                                                />
                                                <span class="font-medium text-foreground flex items-center gap-2">
                                                    <Wine :size="14"/>
                                                    Contiene alcohol
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Paso 3: Variantes -->
                            <div v-else key="3" class="space-y-4">
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="text-lg font-bold text-foreground">Variantes (SKUs)</h3>
                                    <button 
                                        type="button" 
                                        @click="addSku"
                                        class="btn btn-outline btn-sm flex items-center gap-2"
                                    >
                                        <Plus :size="14" />
                                        Agregar Variante
                                    </button>
                                </div>

                                <!-- Lista de SKUs -->
                                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                                    <div 
                                        v-for="(sku, index) in form.skus" 
                                        :key="index" 
                                        class="bg-muted/10 border border-border rounded-lg p-4 relative hover:border-primary/30 transition-colors group"
                                    >
                                        <!-- Botón eliminar -->
                                        <button 
                                            v-if="form.skus.length > 1" 
                                            type="button" 
                                            @click="removeSku(index)"
                                            class="absolute top-2 right-2 p-1 text-muted-foreground hover:text-error hover:bg-error/10 rounded-lg transition-colors"
                                        >
                                            <Trash2 :size="16" />
                                        </button>

                                        <div class="flex gap-4 items-start">
                                            <!-- Imagen del SKU -->
                                            <div class="w-24 h-24 shrink-0">
                                                <!-- Input oculto -->
                                                <input
                                                    :ref="el => skuImageInputRefs[index] = el"
                                                    type="file"
                                                    accept="image/*"
                                                    class="hidden"
                                                    @change="(e) => handleSkuImage(e, index)"
                                                />
                                                
                                                <!-- Área de drop/clic -->
                                                <div 
                                                    class="relative w-full h-full border-2 border-dashed border-border bg-card rounded-lg cursor-pointer hover:border-primary transition overflow-hidden"
                                                    @click="triggerSkuImageInput(index)"
                                                >
                                                    <img 
                                                        v-if="skuImagePreviews[index]" 
                                                        :src="skuImagePreviews[index]" 
                                                        class="w-full h-full object-cover"
                                                    />
                                                    
                                                    <div 
                                                        v-else
                                                        class="w-full h-full flex flex-col items-center justify-center text-muted-foreground"
                                                    >
                                                        <ImageIcon :size="24" />
                                                        <span class="text-[9px] font-bold mt-1">FOTO</span>
                                                    </div>
                                                    
                                                    <!-- Overlay para cambiar imagen -->
                                                    <div 
                                                        v-if="skuImagePreviews[index]"
                                                        class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                                                    >
                                                        <p class="text-white text-xs font-medium">Cambiar</p>
                                                    </div>
                                                </div>
                                                
                                                <!-- Controles de imagen -->
                                                <div v-if="skuImagePreviews[index]" class="flex justify-between mt-2">
                                                    <button
                                                        type="button"
                                                        @click="clearSkuImage(index)"
                                                        class="text-[10px] text-error hover:text-error/80 transition-colors"
                                                    >
                                                        Eliminar
                                                    </button>
                                                </div>
                                                
                                                <p 
                                                    v-if="form.errors[`skus.${index}.image`]" 
                                                    class="text-[10px] text-error mt-1 text-center"
                                                >
                                                    {{ form.errors[`skus.${index}.image`] }}
                                                </p>
                                            </div>

                                            <!-- Campos del SKU -->
                                            <div class="flex-1 grid grid-cols-12 gap-4">
                                                <!-- Nombre -->
                                                <div class="col-span-12 md:col-span-4">
                                                    <label class="form-label text-xs">
                                                        Nombre Variante *
                                                    </label>
                                                    <input 
                                                        v-model="sku.name" 
                                                        type="text" 
                                                        class="w-full text-sm"
                                                        placeholder="Ej: Botella 750ml"
                                                        :class="{ 'border-error': form.errors[`skus.${index}.name`] }"
                                                    />
                                                    <p 
                                                        v-if="form.errors[`skus.${index}.name`]" 
                                                        class="form-error text-xs"
                                                    >
                                                        {{ form.errors[`skus.${index}.name`] }}
                                                    </p>
                                                </div>
                                                
                                                <!-- Código EAN -->
                                                <div class="col-span-6 md:col-span-3">
                                                    <label class="form-label text-xs">
                                                        Código EAN
                                                    </label>
                                                    <input 
                                                        v-model="sku.code" 
                                                        type="text" 
                                                        class="w-full text-sm font-mono"
                                                        placeholder="123456789012"
                                                        :class="{ 'border-error': form.errors[`skus.${index}.code`] }"
                                                    />
                                                    <p 
                                                        v-if="form.errors[`skus.${index}.code`]" 
                                                        class="form-error text-xs"
                                                    >
                                                        {{ form.errors[`skus.${index}.code`] }}
                                                    </p>
                                                </div>
                                                
                                                <!-- Factor de conversión -->
                                                <div class="col-span-6 md:col-span-2">
                                                    <label class="form-label text-xs text-center">
                                                        Factor de Conversión
                                                        <span class="text-[8px] text-muted-foreground block">
                                                            (ej: 12 = caja)
                                                        </span>
                                                    </label>
                                                    <input 
                                                        v-model.number="sku.conversion_factor" 
                                                        type="number" 
                                                        min="0.001" 
                                                        max="1000" 
                                                        step="0.001"
                                                        class="w-full text-sm text-center"
                                                        @change="validateConversionFactor(index)"
                                                        :class="{ 'border-error': form.errors[`skus.${index}.conversion_factor`] }"
                                                    />
                                                    <p 
                                                        v-if="form.errors[`skus.${index}.conversion_factor`]" 
                                                        class="form-error text-xs text-center"
                                                    >
                                                        {{ form.errors[`skus.${index}.conversion_factor`] }}
                                                    </p>
                                                </div>
                                                
                                                <!-- Precio -->
                                                <div class="col-span-12 md:col-span-3">
                                                    <label class="form-label text-xs text-right text-success">
                                                        Precio *
                                                    </label>
                                                    <input 
                                                        v-model.number="sku.price" 
                                                        type="number" 
                                                        step="0.01" 
                                                        min="0" 
                                                        class="w-full text-sm text-right font-bold text-success"
                                                        placeholder="0.00"
                                                        :class="{ 'border-error': form.errors[`skus.${index}.price`] }"
                                                    />
                                                    <p 
                                                        v-if="form.errors[`skus.${index}.price`]" 
                                                        class="form-error text-xs text-right"
                                                    >
                                                        {{ form.errors[`skus.${index}.price`] }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <!-- Navegación -->
                    <div class="px-8 py-4 bg-muted/50 border-t border-border flex justify-between items-center">
                        <button 
                            type="button" 
                            @click="prevStep" 
                            :disabled="currentStep === 1" 
                            class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-muted-foreground hover:text-foreground disabled:opacity-0 transition"
                        >
                            <ArrowLeft :size="16" />
                            Atrás
                        </button>

                        <button 
                            v-if="currentStep < steps.length" 
                            type="button" 
                            @click="nextStep" 
                            class="btn btn-outline btn-md flex items-center gap-2"
                        >
                            Siguiente
                            <ArrowRight :size="16" />
                        </button>

                        <button 
                            v-else 
                            type="button" 
                            @click="submit" 
                            :disabled="form.processing" 
                            class="btn btn-primary btn-md flex items-center gap-2"
                        >
                            <Save :size="18" />
                            {{ form.processing ? 'Creando...' : 'Crear Catálogo' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>