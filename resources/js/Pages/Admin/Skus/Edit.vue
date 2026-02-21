<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Barcode, DollarSign, Scale, Box, Cuboid, 
    ArrowLeft, Save, UploadCloud, ImageIcon, Info,
    CheckCircle2, Package
} from 'lucide-vue-next';

const props = defineProps({
    sku: Object,      // Variant data (from SkuResource)
    product: Object   // Parent context (id, name)
});

// --- ESTADO LOCAL ---
const skuImageInputRef = ref(null);
const skuImagePreview = ref(null);

// --- FORMULARIO QUIRÚRGICO ---
const form = useForm({
    _method: 'PUT',
    name: '',
    code: '',
    base_price: 0,
    conversion_factor: 1,
    weight: 0,
    is_active: true,
    image: null,
});

onMounted(() => {
    if (props.sku) {
        form.name = props.sku.name || '';
        form.code = props.sku.code || '';
        form.base_price = parseFloat(props.sku.price) || 0;
        form.conversion_factor = parseInt(props.sku.conversion_factor) || 1;
        form.weight = parseFloat(props.sku.weight) || 0;
        form.is_active = !!props.sku.is_active;
        skuImagePreview.value = props.sku.image_url || null;
    }
});

const handleImage = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.image = file;
    const reader = new FileReader();
    reader.onload = (ev) => skuImagePreview.value = ev.target.result;
    reader.readAsDataURL(file);
};

const submit = () => {
    form.post(route('admin.skus.update', props.sku.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => console.log("Variante actualizada."),
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Editar SKU - ${sku.name}`" />

        <div class="max-w-5xl mx-auto pb-12">
            <div class="mb-8 px-4 md:px-0">
                <div class="flex items-center gap-2 mb-2 text-muted-foreground">
                    <Package :size="14" />
                    <span class="text-[10px] font-black uppercase tracking-widest">{{ product.name }}</span>
                </div>
                <div class="flex justify-between items-end">
                    <div>
                        <h1 class="text-3xl font-black uppercase tracking-tighter text-foreground">
                            Editar Variante
                        </h1>
                        <p class="text-xs font-mono text-muted-foreground mt-1">UUID: {{ sku.id }}</p>
                    </div>
                    <Link :href="route('admin.products.index')" class="btn btn-ghost btn-sm gap-2">
                        <ArrowLeft :size="16" /> Volver al Catálogo
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 space-y-6">
                    <div class="card p-6 border-border shadow-lg bg-card">
                        <label class="text-[10px] font-black uppercase tracking-widest block mb-4">Imagen de Variante</label>
                        <input ref="skuImageInputRef" type="file" class="hidden" accept="image/*" @change="handleImage">
                        
                        <div @click="skuImageInputRef.click()" 
                             class="relative aspect-square rounded-2xl border-2 border-dashed border-border bg-muted/10 flex items-center justify-center cursor-pointer group overflow-hidden transition-all hover:border-primary/50">
                            
                            <img v-if="skuImagePreview" :src="skuImagePreview" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
                            <div v-else class="flex flex-col items-center text-muted-foreground">
                                <UploadCloud :size="40" stroke-width="1.5" />
                                <span class="text-[10px] font-bold mt-2 uppercase">Subir Imagen</span>
                            </div>

                            <div v-if="skuImagePreview" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <ImageIcon class="text-white" :size="24" />
                            </div>
                        </div>
                        <p class="text-[9px] text-muted-foreground mt-4 text-center leading-relaxed">
                            Formatos: JPG, PNG. Máx 2MB. <br> Esta imagen es específica para este SKU.
                        </p>
                    </div>

                    <div class="card p-5 border-border bg-primary/5 border-primary/10">
                        <div class="flex gap-3">
                            <Info class="text-primary shrink-0" :size="18" />
                            <div>
                                <h4 class="text-xs font-black uppercase text-primary">Historial de Precios</h4>
                                <p class="text-[10px] text-primary/70 mt-1 leading-tight">
                                    Cualquier cambio en el <b>Precio Base</b> generará automáticamente un registro en el historial de precios.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="card p-8 border-border shadow-xl bg-card space-y-8">
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2 space-y-1.5">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Nombre de la Variante *</label>
                                        <input v-model="form.name" type="text" class="form-input text-lg font-bold w-full" :class="{'border-error': form.errors.name}">
                                        <p v-if="form.errors.name" class="text-error text-[10px] font-bold uppercase">{{ form.errors.name }}</p>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Código EAN / SKU</label>
                                        <div class="relative">
                                            <Barcode :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                            <input v-model="form.code" type="text" class="form-input pl-10 w-full font-mono" :class="{'border-error': form.errors.code}">
                                        </div>
                                        <p v-if="form.errors.code" class="text-error text-[10px] font-bold uppercase">{{ form.errors.code }}</p>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-success">Precio Base Operativo *</label>
                                        <div class="relative">
                                            <DollarSign :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-success" />
                                            <input v-model.number="form.base_price" type="number" step="0.01" class="form-input pl-10 w-full font-black text-success" :class="{'border-error': form.errors.base_price}">
                                        </div>
                                        <p v-if="form.errors.base_price" class="text-error text-[10px] font-bold uppercase">{{ form.errors.base_price }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="h-px bg-border/50 w-full"></div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Factor de Conversión</label>
                                    <div class="relative">
                                        <component :is="form.conversion_factor === 1 ? Cuboid : Box" :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                        <input v-model.number="form.conversion_factor" type="number" min="1" class="form-input pl-10 w-full font-bold" :class="{'border-error': form.errors.conversion_factor}">
                                    </div>
                                    <p class="text-[9px] text-muted-foreground uppercase font-bold mt-1">Unidades por empaque</p>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Peso Neto (kg)</label>
                                    <div class="relative">
                                        <Scale :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                        <input v-model.number="form.weight" type="number" step="0.001" class="form-input pl-10 w-full" :class="{'border-error': form.errors.weight}">
                                    </div>
                                </div>
                            </div>

                            <div @click="form.is_active = !form.is_active" 
                                 class="p-4 border rounded-2xl flex items-center justify-between cursor-pointer transition-colors"
                                 :class="form.is_active ? 'bg-success/5 border-success/20' : 'bg-muted/5 border-border'">
                                <div class="flex items-center gap-3">
                                    <CheckCircle2 :class="form.is_active ? 'text-success' : 'text-muted-foreground'" :size="20" />
                                    <span class="text-xs font-black uppercase tracking-widest">Variante Activa para Venta</span>
                                </div>
                                <div :class="`w-12 h-6 rounded-full p-1 transition-colors ${form.is_active ? 'bg-success' : 'bg-muted'}`">
                                    <div :class="`w-4 h-4 bg-white rounded-full shadow-sm transition-transform ${form.is_active ? 'translate-x-6' : 'translate-x-0'}`"></div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-8">
                            <Link :href="route('admin.products.index')" class="btn btn-ghost px-8 uppercase text-xs font-black tracking-widest">Descartar</Link>
                            <button type="submit" :disabled="form.processing" class="btn btn-primary px-12 shadow-xl shadow-primary/20 uppercase text-xs font-black tracking-widest">
                                <span v-if="form.processing" class="loading loading-spinner loading-xs mr-2"></span>
                                <Save v-else :size="18" class="mr-2" /> Actualizar Variante
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.form-input { @apply rounded-xl border-border bg-background focus:ring-2 focus:ring-primary/20 transition-all; }
.card { @apply rounded-3xl transition-all; }
</style>