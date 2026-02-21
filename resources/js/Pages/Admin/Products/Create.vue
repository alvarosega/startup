<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Package, Info, ArrowRight, Save, UploadCloud, Wine, Tag, Layers } from 'lucide-vue-next';
import axios from 'axios';
import debounce from 'lodash/debounce';

const props = defineProps({ brands: Array, categories: Array });

// 1. INICIALIZAR FORMULARIO PRIMERO
const form = useForm({
    name: '', 
    brand_id: '', 
    category_id: '', 
    description: '',
    image: null, 
    is_active: true, 
    is_alcoholic: false
});

// 2. REFS DE ESTADO PARA VALIDACIÓN
const isNameAvailable = ref(true);
const isCheckingName = ref(false);
const masterImagePreview = ref(null);
const selectedParentId = ref('');

// 3. LÓGICA DE VALIDACIÓN (Debounced)
const checkNameAvailability = debounce(async (name) => {
    if (name.length < 3) return;
    
    isCheckingName.value = true;
    try {
        const { data } = await axios.get(route('admin.products.check-name'), { params: { name } });
        isNameAvailable.value = data.available;
        
        if (!data.available) {
            form.setError('name', 'Este nombre ya está en uso en el catálogo.');
        } else {
            form.clearErrors('name');
        }
    } catch (e) {
        console.error("Error validando nombre");
    } finally {
        isCheckingName.value = false;
    }
}, 500);

// 4. WATCHERS
watch(() => form.name, (newVal) => {
    isNameAvailable.value = true;
    checkNameAvailability(newVal);
});

watch(selectedParentId, () => form.category_id = '');

// 5. COMPUTED & MÉTODOS
const availableSubcategories = computed(() => {
    const parent = props.categories.find(c => c.id === selectedParentId.value);
    return parent ? parent.children : [];
});

const handleImage = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.image = file;
    const reader = new FileReader();
    reader.onload = (ev) => masterImagePreview.value = ev.target.result;
    reader.readAsDataURL(file);
};

const submit = () => {
    form.post(route('admin.products.store'), {
        forceFormData: true,
        onError: (errors) => {
            console.error("ERRORES DE VALIDACIÓN:", errors);
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Nuevo Producto Maestro" />
        <div class="max-w-4xl mx-auto pb-12">
            <div class="mb-8">
                <h1 class="text-3xl font-black uppercase tracking-tighter">Nuevo Producto</h1>
                <p class="text-xs text-muted-foreground">PASO 1: CONFIGURACIÓN MAESTRA</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="card p-6 border-border shadow-xl space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-xs font-black uppercase">Nombre del Producto *</label>
                            <div class="relative">
                                <input v-model="form.name" type="text" 
                                       class="form-input font-bold text-lg w-full" 
                                       placeholder="Ej: Ron Abuelo 12 Años"
                                       :class="{'border-error': !isNameAvailable || form.errors.name}">
                                
                                <div v-if="isCheckingName" class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <span class="loading loading-spinner loading-xs text-primary"></span>
                                </div>
                            </div>
                            <p v-if="form.errors.name" class="text-error text-[10px] font-bold mt-1 uppercase">{{ form.errors.name }}</p>
                        </div>
                        
                        <div class="space-y-1.5">
                            <label class="text-xs font-black uppercase">Marca *</label>
                            <select v-model="form.brand_id" class="form-input" :class="{'border-error': form.errors.brand_id}">
                                <option value="" disabled>Seleccionar...</option>
                                <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                            <p v-if="form.errors.brand_id" class="text-error text-[10px] font-bold mt-1 uppercase">{{ form.errors.brand_id }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-black uppercase">Categoría *</label>
                                <select v-model="selectedParentId" class="form-input">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-black uppercase">Subcategoría *</label>
                                <select v-model="form.category_id" :disabled="!selectedParentId" class="form-input" :class="{'border-error': form.errors.category_id}">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="sc in availableSubcategories" :key="sc.id" :value="sc.id">{{ sc.name }}</option>
                                </select>
                                <p v-if="form.errors.category_id" class="text-error text-[10px] font-bold mt-1 uppercase">{{ form.errors.category_id }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <label class="text-xs font-black uppercase mb-1.5 block">Imagen Principal</label>
                            <div class="aspect-square border-2 border-dashed border-border rounded-2xl flex items-center justify-center cursor-pointer overflow-hidden relative group" @click="$refs.imgInput.click()">
                                <img v-if="masterImagePreview" :src="masterImagePreview" class="w-full h-full object-cover">
                                <UploadCloud v-else :size="32" class="text-muted-foreground" />
                                <input ref="imgInput" type="file" class="hidden" accept="image/*" @change="handleImage">
                            </div>
                        </div>
                        <div class="md:col-span-2 space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-black uppercase">Descripción General</label>
                                <textarea v-model="form.description" rows="4" class="form-input resize-none"></textarea>
                            </div>
                            <div class="flex gap-4">
                                <label class="flex-1 p-3 border border-border rounded-xl flex items-center justify-between cursor-pointer">
                                    <span class="text-xs font-bold uppercase">Alcohol</span>
                                    <input type="checkbox" v-model="form.is_alcoholic" class="checkbox checkbox-primary">
                                </label>
                                <label class="flex-1 p-3 border border-border rounded-xl flex items-center justify-between cursor-pointer">
                                    <span class="text-xs font-bold uppercase">Activo</span>
                                    <input type="checkbox" v-model="form.is_active" class="checkbox checkbox-success">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <Link :href="route('admin.products.index')" class="btn btn-ghost">Cancelar</Link>
                    <button type="submit" 
                            class="btn btn-primary px-8" 
                            :disabled="form.processing || !isNameAvailable || !form.name">
                        <span v-if="form.processing" class="loading loading-spinner loading-xs mr-2"></span>
                        Continuar a Variantes <ArrowRight :size="18" class="ml-2" />
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>