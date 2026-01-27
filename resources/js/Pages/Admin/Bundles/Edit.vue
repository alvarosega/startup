<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, ArrowLeft, Package, Plus, Trash2, MapPin } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

const props = defineProps({
    bundle: Object,
    skus: Object,
    branches: Array // <--- Recibimos sucursales
});

const bundleData = props.bundle.data || props.bundle;

const form = useForm({
    _method: 'PUT',
    branch_id: bundleData.branch_id || '', // <--- CARGAMOS DATO
    name: bundleData.name,
    description: bundleData.description,
    fixed_price: bundleData.fixed_price,
    is_active: Boolean(bundleData.is_active),
    image: null,
    items: bundleData.items.map(i => ({ sku_id: i.sku_id, quantity: i.quantity }))
});

if (form.items.length === 0) form.items.push({ sku_id: '', quantity: 1 });

// Imagen logic... (Igual que Create)
const imageInputRef = ref(null);
const imagePreview = ref(null);
onMounted(() => { if (bundleData.image_path) imagePreview.value = bundleData.image_path.startsWith('http') ? bundleData.image_path : `/storage/${bundleData.image_path}`; });
const triggerImageInput = () => imageInputRef.value?.click();
const handleImageChange = (e) => { const f = e.target.files[0]; if (f) { form.image = f; const r = new FileReader(); r.onload = (ev) => imagePreview.value = ev.target.result; r.readAsDataURL(f); }};
const clearImage = () => { form.image = null; imagePreview.value = null; if (imageInputRef.value) imageInputRef.value.value = ''; };

// Items logic... (Igual que Create)
const addItem = () => form.items.push({ sku_id: '', quantity: 1 });
const removeItem = (i) => form.items.length > 1 ? form.items.splice(i, 1) : form.items[0] = { sku_id: '', quantity: 1 };

const submit = () => {
    form.post(route('admin.bundles.update', bundleData.id), { forceFormData: true, preserveScroll: true });
};
</script>

<template>
    <AdminLayout>
        <Head title="Editar Pack" />
        <div class="max-w-7xl mx-auto">
            
            <div class="mb-8 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.bundles.index')" class="btn btn-outline btn-sm gap-2"><ArrowLeft :size="16" /> Volver</Link>
                    <div>
                        <h1 class="text-2xl font-black text-foreground">Editar Pack</h1>
                        <p class="text-sm text-muted-foreground">{{ bundleData.name }}</p>
                    </div>
                </div>
                <button @click="submit" :disabled="form.processing" class="btn btn-primary btn-md gap-2">
                    <Save :size="16" /> Actualizar Pack
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-5 space-y-6">
                    <div class="card">
                        <div class="card-header"><h3 class="font-bold">Datos Generales</h3></div>
                        <div class="card-content space-y-4">
                            
                            <div class="form-group">
                                <label class="form-label flex items-center gap-1">Sucursal *</label>
                                <div class="relative">
                                    <MapPin :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                    <select v-model="form.branch_id" class="pl-10 w-full select select-bordered" :class="{ 'select-error': form.errors.branch_id }">
                                        <option value="" disabled>Selecciona una sucursal...</option>
                                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                </div>
                                <p v-if="form.errors.branch_id" class="text-error text-xs mt-1">{{ form.errors.branch_id }}</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nombre del Pack</label>
                                <input v-model="form.name" type="text" class="w-full input input-bordered" />
                                <p v-if="form.errors.name" class="text-error text-xs mt-1">{{ form.errors.name }}</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Imagen</label>
                                <div @click="triggerImageInput" class="w-full aspect-video bg-muted/20 border-2 border-dashed rounded-lg flex items-center justify-center cursor-pointer hover:border-primary overflow-hidden relative">
                                    <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" />
                                    <div v-else class="text-center text-muted-foreground"><Package class="mx-auto mb-2 opacity-50" /><span class="text-sm">Click para cambiar</span></div>
                                    <input ref="imageInputRef" type="file" accept="image/*" class="hidden" @change="handleImageChange" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Descripción</label>
                                <textarea v-model="form.description" class="w-full textarea textarea-bordered" rows="3"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Precio Fijo</label>
                                    <input v-model="form.fixed_price" type="number" step="0.01" class="w-full input input-bordered" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Estado</label>
                                    <div class="flex items-center gap-2 mt-3">
                                        <input v-model="form.is_active" type="checkbox" class="toggle toggle-primary" />
                                        <span class="text-sm">{{ form.is_active ? 'Activo' : 'Inactivo' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <div class="card h-full">
                        <div class="card-header flex justify-between items-center">
                            <h3 class="font-bold">Contenido</h3>
                            <button @click="addItem" class="btn btn-sm btn-outline"><Plus :size="14"/> Item</button>
                        </div>
                        <div class="card-content space-y-3">
                            <div v-for="(item, index) in form.items" :key="index" class="flex gap-2 items-start p-3 bg-muted/10 rounded-lg">
                                <div class="flex-1">
                                    <label class="text-xs text-muted-foreground block mb-1">Producto</label>
                                    <select v-model="item.sku_id" class="select select-bordered select-sm w-full">
                                        <option value="" disabled>Seleccionar...</option>
                                        <option v-for="sku in skus.data" :key="sku.id" :value="sku.id">{{ sku.name }} ({{ sku.code }})</option>
                                    </select>
                                </div>
                                <div class="w-24">
                                    <label class="text-xs text-muted-foreground block mb-1">Cant.</label>
                                    <input v-model="item.quantity" type="number" min="1" class="input input-bordered input-sm w-full text-center" />
                                </div>
                                <button @click="removeItem(index)" class="btn btn-square btn-ghost btn-sm mt-6 text-error" :disabled="form.items.length === 1"><Trash2 :size="16"/></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>