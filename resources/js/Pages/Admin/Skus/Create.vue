<script setup>
import { ref, nextTick } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Plus, Trash2, Save, Barcode, DollarSign, 
    Box, Cuboid, Scale, ImageIcon, Info // <--- AÑADE "Info" AQUÍ
} from 'lucide-vue-next';

const props = defineProps({ product: Object });

const skuImagePreviews = ref([]);

const form = useForm({
    skus: [
        { name: `${props.product.name} (Unidad)`, code: '', price: 0, conversion_factor: 1, weight: 0, image: null }
    ]
});

const addSku = async () => {
    form.skus.unshift({ name: '', code: '', price: 0, conversion_factor: 1, weight: 0, image: null });
    skuImagePreviews.value.unshift(null);
};

const removeSku = (index) => {
    if (form.skus.length > 1) {
        form.skus.splice(index, 1);
        skuImagePreviews.value.splice(index, 1);
    }
};

const handleSkuImage = (e, index) => {
    const file = e.target.files[0];
    if (!file) return;
    form.skus[index].image = file;
    const reader = new FileReader();
    reader.onload = (ev) => skuImagePreviews.value[index] = ev.target.result;
    reader.readAsDataURL(file);
};

const submit = () => form.post(route('admin.products.skus.store', props.product.id));
</script>

<template>
    <AdminLayout>
        <Head :title="`Variantes - ${product.name}`" />
        <div class="max-w-5xl mx-auto pb-12">
            <div class="mb-8 flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tighter">Variantes de Producto</h1>
                    <p class="text-sm font-bold text-primary">{{ product.name }}</p>
                </div>
                <button @click="addSku" class="btn btn-outline btn-sm gap-2">
                    <Plus :size="16" /> Agregar Variante
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div v-for="(sku, index) in form.skus" :key="index" 
                     class="card p-4 border-border bg-card shadow-sm relative group animate-in slide-in-from-top-4">
                    
                    <button type="button" @click="removeSku(index)" v-if="form.skus.length > 1"
                            class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-error text-white flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                        <Trash2 :size="14" />
                    </button>

                    <div class="flex gap-6">
                        <div class="w-24">
                            <div class="aspect-square border border-dashed border-border rounded-xl flex items-center justify-center cursor-pointer overflow-hidden bg-muted/30" @click="$refs.skuImg[index].click()">
                                <img v-if="skuImagePreviews[index]" :src="skuImagePreviews[index]" class="w-full h-full object-cover">
                                <ImageIcon v-else :size="20" class="text-muted-foreground/40" />
                                <input ref="skuImg" type="file" class="hidden" @change="e => handleSkuImage(e, index)">
                            </div>
                        </div>

                        <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2 space-y-1">
                                <label class="text-[10px] font-black uppercase">Nombre de Variante</label>
                                <input v-model="sku.name" type="text" class="form-input h-9 text-sm font-bold" placeholder="Ej: Botella 750ml">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase">Código EAN / SKU</label>
                                <div class="relative">
                                    <Barcode :size="14" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                    <input v-model="sku.code" type="text" 
                                        class="form-input h-9 pl-9 text-sm font-mono"
                                        :class="{'border-error': form.errors[`skus.${index}.code`]}"> </div>
                                <p v-if="form.errors[`skus.${index}.code`]" class="text-[9px] text-error font-bold uppercase mt-1">
                                    {{ form.errors[`skus.${index}.code`] }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-success">Precio Base</label>
                                <div class="relative">
                                    <DollarSign :size="14" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-success" />
                                    <input v-model.number="sku.price" type="number" step="0.01" class="form-input h-9 pl-9 text-sm font-black text-success">
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase">Factor de Conversión</label>
                                <div class="relative">
                                    <component :is="sku.conversion_factor == 1 ? Cuboid : Box" :size="14" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                    <input v-model.number="sku.conversion_factor" type="number" step="1" class="form-input h-9 pl-9 text-sm text-center font-bold">
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase">Peso (kg)</label>
                                <div class="relative">
                                    <Scale :size="14" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                    <input v-model.number="sku.weight" type="number" step="0.001" class="form-input h-9 pl-9 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-6 border-t border-border">
                    <div class="flex items-center gap-2 text-warning" v-if="form.skus.length === 0">
                        <Info :size="16" />
                        <span class="text-xs font-bold uppercase">Sin variantes, el producto será marcado como incompleto.</span>
                    </div>
                    <div v-else></div>
                    
                    <div class="flex gap-4">
                        <Link :href="route('admin.products.index')" class="btn btn-ghost">Omitir por ahora</Link>
                        <button type="submit" class="btn btn-primary px-10 shadow-lg shadow-primary/20" :disabled="form.processing">
                            <Save :size="18" class="mr-2" /> Finalizar Catálogo
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>