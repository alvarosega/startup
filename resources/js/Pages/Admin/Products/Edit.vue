<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Package, Info, Barcode, Scale, 
    Plus, Trash2, ArrowRight, ArrowLeft, Save, CheckCircle, 
    UploadCloud, Wine, Image as ImageIcon,
    Box, Cuboid, DollarSign
} from 'lucide-vue-next';

const props = defineProps({
    product: Object,
    brands: Array,
    categories: Array
});

// --- REFS IMÁGENES ---
const masterImageInputRef = ref(null);
const masterImagePreview = ref(null);
const skuImageInputRefs = ref([]);
const skuImagePreviews = ref([]); 
const skuListContainer = ref(null);

// --- STEPPER ---
const currentStep = ref(1);
const steps = [
    { id: 1, title: 'Concepto', icon: Package },
    { id: 2, title: 'Detalles', icon: Info },
    { id: 3, title: 'Variantes', icon: Barcode },
];

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);

// --- FORMULARIO ---
const form = useForm({
    _method: 'PUT',
    name: '',
    brand_id: '',
    category_id: '',
    description: '',
    image: null,
    is_active: true,
    is_alcoholic: false,
    skus: []
});

// --- INICIALIZACIÓN ---
onMounted(() => {
    if (props.product) {
        form.name = props.product.name || '';
        form.brand_id = props.product.brand_id || '';
        form.category_id = props.product.category_id || '';
        form.description = props.product.description || '';
        form.is_active = !!props.product.is_active;
        form.is_alcoholic = !!props.product.is_alcoholic;
        
        if (props.product.image_url) {
            masterImagePreview.value = props.product.image_url;
        }
        
        if (props.product.skus && props.product.skus.length > 0) {
            form.skus = props.product.skus.map(sku => ({
                id: sku.id,
                name: sku.name,
                code: sku.code,
                price: parseFloat(sku.price),
                conversion_factor: parseFloat(sku.conversion_factor),
                weight: parseFloat(sku.weight),
                existing_image: sku.image_url,
                image: null
            }));
            skuImageInputRefs.value = new Array(form.skus.length).fill(null);
            skuImagePreviews.value = new Array(form.skus.length).fill(null);
        } else {
            addSku(); 
        }
    }
});

// --- IMÁGENES ---
const triggerMasterImageInput = () => masterImageInputRef.value?.click();
const triggerSkuImageInput = (index) => skuImageInputRefs.value[index]?.click();

const handleImage = (e, type, index = null) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (ev) => {
        if (type === 'master') {
            form.image = file;
            masterImagePreview.value = ev.target.result;
        } else {
            form.skus[index].image = file;
            skuImagePreviews.value[index] = ev.target.result;
        }
    };
    reader.readAsDataURL(file);
};

const clearImage = (type, index = null) => {
    if (type === 'master') {
        form.image = null;
        masterImagePreview.value = props.product.image_url || null;
        if (masterImageInputRef.value) masterImageInputRef.value.value = '';
    } else {
        form.skus[index].image = null;
        skuImagePreviews.value[index] = null;
        if (skuImageInputRefs.value[index]) skuImageInputRefs.value[index].value = '';
    }
};

// --- LOGICA SKU (Al inicio) ---
const addSku = async () => {
    form.skus.unshift({
        id: null, name: '', code: '', price: 0, conversion_factor: 1, weight: 0, existing_image: null, image: null
    });
    skuImageInputRefs.value.unshift(null);
    skuImagePreviews.value.unshift(null);

    await nextTick();
    if (skuListContainer.value) skuListContainer.value.scrollTop = 0;
};

const removeSku = (index) => {
    if (form.skus.length <= 1) return alert("Debe haber al menos una variante.");
    form.skus.splice(index, 1);
    skuImageInputRefs.value.splice(index, 1);
    skuImagePreviews.value.splice(index, 1);
};

// --- VALIDACIÓN ---
const validateStep = () => {
    form.clearErrors();
    if (currentStep.value === 1) {
        if (!form.name) return form.setError('name', 'Requerido') && false;
        if (!form.brand_id) return form.setError('brand_id', 'Requerido') && false;
        if (!form.category_id) return form.setError('category_id', 'Requerido') && false;
    }
    return true;
};

const nextStep = () => {
    if (validateStep() && currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
    form.post(route('admin.products.update', props.product.id), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            if (Object.keys(errors).some(k => k.startsWith('skus'))) currentStep.value = 3;
            else if (errors.image || errors.description) currentStep.value = 2;
            else currentStep.value = 1;
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Editar ${product.name}`" />
        
        <div class="max-w-5xl mx-auto pb-24 md:pb-12">
            
            <div class="mb-6 px-4 md:px-0">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="badge badge-primary text-[10px] h-5 px-1.5 font-mono">ID: {{ product.id }}</span>
                            <span v-if="!form.is_active" class="badge badge-error text-[10px] h-5 px-1.5">INACTIVO</span>
                        </div>
                        <h1 class="text-xl md:text-3xl font-display font-black text-foreground tracking-tight">
                            Editar Producto
                        </h1>
                    </div>
                    <Link :href="route('admin.products.index')" class="btn btn-ghost btn-sm text-muted-foreground hover:text-error">
                        Cancelar
                    </Link>
                </div>

                <div class="relative px-2">
                    <div class="absolute top-1/2 left-0 w-full h-1 bg-border -z-10 -translate-y-1/2 rounded-full"></div>
                    <div class="absolute top-1/2 left-0 h-1 bg-primary -z-10 -translate-y-1/2 rounded-full transition-all duration-500 ease-out" :style="{ width: progressPercentage + '%' }"></div>
                    <div class="flex justify-between">
                        <div v-for="step in steps" :key="step.id" 
                             class="flex flex-col items-center gap-1 cursor-pointer"
                             @click="currentStep >= step.id ? currentStep = step.id : null">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center border-2 transition-all bg-card shadow-sm"
                                 :class="currentStep === step.id ? 'border-primary text-primary scale-110 shadow-md' : currentStep > step.id ? 'border-success bg-success text-white' : 'border-border text-muted-foreground'">
                                <CheckCircle v-if="currentStep > step.id" :size="16" />
                                <component v-else :is="step.icon" :size="16" />
                            </div>
                            <span class="text-[9px] md:text-[10px] font-bold uppercase bg-background px-1" :class="currentStep >= step.id ? 'text-foreground' : 'text-muted-foreground'">{{ step.title }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden border border-border shadow-xl min-h-[450px] flex flex-col mx-3 md:mx-0">
                <form class="flex-1 flex flex-col" @submit.prevent>
                    <div class="p-4 md:p-8 flex-1 relative">
                        <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="space-y-5 animate-in slide-in-from-right-4 fade-in">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                    <div class="md:col-span-2 space-y-1.5">
                                        <label class="form-label text-sm">Nombre del Producto *</label>
                                        <input v-model="form.name" type="text" class="form-input text-base font-bold" placeholder="Ej: Ron Abuelo 12 Años" :class="{ 'border-error': form.errors.name }">
                                        <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="form-label text-sm">Marca *</label>
                                        <select v-model="form.brand_id" class="form-input w-full bg-background" :class="{ 'border-error': form.errors.brand_id }">
                                            <option value="" disabled>Seleccionar...</option>
                                            <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                                        </select>
                                        <p v-if="form.errors.brand_id" class="form-error">{{ form.errors.brand_id }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="form-label text-sm">Categoría *</label>
                                        <select v-model="form.category_id" class="form-input w-full bg-background" :class="{ 'border-error': form.errors.category_id }">
                                            <option value="" disabled>Seleccionar...</option>
                                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </select>
                                        <p v-if="form.errors.category_id" class="form-error">{{ form.errors.category_id }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="currentStep === 2" key="2" class="space-y-5 animate-in slide-in-from-right-4 fade-in">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div class="lg:col-span-1 space-y-2">
                                        <label class="form-label text-sm">Imagen Principal</label>
                                        <input ref="masterImageInputRef" type="file" class="hidden" accept="image/*" @change="e => handleImage(e, 'master')" />
                                        <div class="relative w-full aspect-square border-2 border-dashed border-border bg-muted/10 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-primary transition overflow-hidden group" @click="triggerMasterImageInput">
                                            <img v-if="masterImagePreview" :src="masterImagePreview" class="w-full h-full object-cover">
                                            <div v-else class="flex flex-col items-center text-muted-foreground p-4 text-center">
                                                <UploadCloud :size="24" class="mb-2" />
                                                <span class="text-xs font-bold">Subir</span>
                                            </div>
                                            <div v-if="masterImagePreview" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                                <span class="text-white text-xs font-bold uppercase">Cambiar</span>
                                            </div>
                                        </div>
                                        <button v-if="masterImagePreview" type="button" @click="clearImage('master')" class="text-xs text-error font-bold w-full text-center">Eliminar</button>
                                    </div>

                                    <div class="lg:col-span-2 space-y-4">
                                        <div class="space-y-1.5">
                                            <label class="form-label text-sm">Descripción</label>
                                            <textarea v-model="form.description" rows="3" class="form-input resize-none" placeholder="Detalles..."></textarea>
                                        </div>
                                        <div class="grid grid-cols-1 gap-3">
                                            <div class="p-3 border border-border rounded-lg flex items-center justify-between cursor-pointer" @click="form.is_active = !form.is_active">
                                                <span class="text-sm font-bold">Producto Activo</span>
                                                <div :class="`w-10 h-5 rounded-full p-0.5 transition-colors ${form.is_active ? 'bg-success' : 'bg-muted'}`">
                                                    <div :class="`w-4 h-4 bg-white rounded-full shadow-sm transition-transform ${form.is_active ? 'translate-x-5' : 'translate-x-0'}`"></div>
                                                </div>
                                            </div>
                                            <div class="p-3 border border-border rounded-lg flex items-center justify-between cursor-pointer" @click="form.is_alcoholic = !form.is_alcoholic">
                                                <span class="text-sm font-bold flex items-center gap-2"><Wine :size="14" /> Con Alcohol</span>
                                                <div :class="`w-10 h-5 rounded-full p-0.5 transition-colors ${form.is_alcoholic ? 'bg-warning' : 'bg-muted'}`">
                                                    <div :class="`w-4 h-4 bg-white rounded-full shadow-sm transition-transform ${form.is_alcoholic ? 'translate-x-5' : 'translate-x-0'}`"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else key="3" class="space-y-4 animate-in slide-in-from-right-4 fade-in">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-bold text-base">Variantes (SKUs)</h3>
                                    <button type="button" @click="addSku" class="btn btn-outline btn-xs gap-1">
                                        <Plus :size="12"/> Agregar
                                    </button>
                                </div>

                                <div ref="skuListContainer" class="space-y-3 max-h-[500px] overflow-y-auto pr-1 custom-scrollbar scroll-smooth">
                                    <div v-for="(sku, index) in form.skus" :key="index" 
                                         class="bg-card border border-border rounded-xl p-3 relative shadow-sm animate-in slide-in-from-top-2">
                                        
                                        <button v-if="form.skus.length > 1" type="button" @click="removeSku(index)" 
                                                class="absolute top-2 right-2 p-1 text-muted-foreground hover:text-error transition z-10">
                                            <Trash2 :size="14"/>
                                        </button>

                                        <div class="flex gap-3">
                                            <div class="shrink-0 w-20">
                                                <input :ref="el => skuImageInputRefs[index] = el" type="file" class="hidden" accept="image/*" @change="e => handleImage(e, 'sku', index)" />
                                                <div class="w-20 h-20 rounded-lg border border-dashed border-border bg-muted/5 flex items-center justify-center cursor-pointer overflow-hidden relative"
                                                     @click="triggerSkuImageInput(index)">
                                                    <img v-if="skuImagePreviews[index] || sku.existing_image" :src="skuImagePreviews[index] || sku.existing_image" class="w-full h-full object-cover">
                                                    <ImageIcon v-else :size="18" class="text-muted-foreground/50"/>
                                                </div>
                                            </div>

                                            <div class="flex-1 grid grid-cols-2 gap-3">
                                                <div class="col-span-2">
                                                    <label class="text-[10px] font-bold text-muted-foreground block mb-0.5">Nombre Variante</label>
                                                    <input v-model="sku.name" type="text" class="form-input w-full h-8 text-xs font-bold" placeholder="Ej: Botella 750ml" :class="{'border-error': form.errors[`skus.${index}.name`]}">
                                                </div>

                                                <div>
                                                    <label class="text-[10px] font-bold text-muted-foreground block mb-0.5">Código EAN</label>
                                                    <div class="relative">
                                                        <Barcode :size="12" class="absolute left-2 top-1/2 -translate-y-1/2 text-muted-foreground"/>
                                                        <input v-model="sku.code" type="text" class="form-input w-full h-8 text-xs pl-6 font-mono" placeholder="770..." :class="{'border-error': form.errors[`skus.${index}.code`]}">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-bold text-muted-foreground block mb-0.5">Factor (Unidades)</label>
                                                    <div class="relative">
                                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-muted-foreground">
                                                            <component :is="sku.conversion_factor == 1 ? Cuboid : Box" :size="12"/>
                                                        </span>
                                                        <input v-model="sku.conversion_factor" type="number" step="1" min="1" class="form-input w-full h-8 text-xs pl-6 text-center" :class="{'border-error': form.errors[`skus.${index}.conversion_factor`]}">
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="text-[10px] font-bold text-success block mb-0.5">Precio Base</label>
                                                    <div class="relative">
                                                        <DollarSign :size="10" class="absolute left-2 top-1/2 -translate-y-1/2 text-success"/>
                                                        <input v-model="sku.price" type="number" step="0.01" class="form-input w-full h-8 text-xs pl-6 text-success font-bold" :class="{'border-error': form.errors[`skus.${index}.price`]}">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-bold text-muted-foreground block mb-0.5">Peso (kg)</label>
                                                    <div class="relative">
                                                        <Scale :size="12" class="absolute left-2 top-1/2 -translate-y-1/2 text-muted-foreground"/>
                                                        <input v-model="sku.weight" type="number" step="0.01" class="form-input w-full h-8 text-xs pl-6" :class="{'border-error': form.errors[`skus.${index}.weight`]}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="form.errors.skus" class="alert alert-error text-xs p-2">{{ form.errors.skus }}</div>
                            </div>

                        </Transition>
                    </div>

                    <div class="p-3 bg-background/90 backdrop-blur border-t border-border flex justify-between items-center sticky bottom-0 z-20">
                        <button type="button" @click="prevStep" 
                                class="btn btn-ghost btn-sm text-muted-foreground"
                                :class="{'invisible': currentStep === 1}">
                            <ArrowLeft :size="16" class="mr-1"/> Atrás
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep"
                                class="btn btn-primary btn-sm shadow-md">
                            Siguiente <ArrowRight :size="16" class="ml-1"/>
                        </button>

                        <button v-else type="button" @click="submit"
                                :disabled="form.processing"
                                class="btn btn-primary btn-sm shadow-md px-6">
                            <span v-if="form.processing" class="loading loading-spinner loading-xs mr-2"></span>
                            <Save v-else :size="16" class="mr-1"/> 
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fade-enter-from { opacity: 0; transform: translateX(5px); }
.fade-leave-to { opacity: 0; transform: translateX(-5px); position: absolute; width: 100%; }
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--muted-foreground)/0.3); border-radius: 4px; }
</style>