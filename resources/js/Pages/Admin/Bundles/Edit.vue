<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, ArrowLeft, Package, Plus, Trash2, AlertCircle, CheckCircle } from 'lucide-vue-next';
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    bundle: Object,
    skus: Object
});

// Preparar datos iniciales
const bundle = props.bundle.data || props.bundle;

const form = useForm({
    _method: 'PUT',
    name: bundle.name || '',
    description: bundle.description || '',
    fixed_price: bundle.fixed_price || '',
    is_active: Boolean(bundle.is_active),
    image: null,
    items: bundle.items?.length ? bundle.items.map(item => ({
        sku_id: item.sku_id || item.sku?.id,
        quantity: item.quantity || 1
    })) : [{ sku_id: '', quantity: 1 }]
});

// REFS para manejar elementos DOM
const imageInputRef = ref(null);
const imagePreview = ref(null);

// Previsualización inicial si existe imagen
onMounted(() => {
    if (bundle.image_path) {
        imagePreview.value = bundle.image_path.startsWith('http') 
            ? bundle.image_path 
            : `/storage/${bundle.image_path}`;
    }
});

// Manejar clic en el área de imagen
const triggerImageInput = () => {
    if (imageInputRef.value) {
        imageInputRef.value.click();
    }
};

// Manejar cambio de imagen
const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        
        // Crear URL temporal para previsualización
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Limpiar imagen
const clearImage = () => {
    form.image = null;
    imagePreview.value = bundle.image_path 
        ? (bundle.image_path.startsWith('http') 
            ? bundle.image_path 
            : `/storage/${bundle.image_path}`)
        : null;
    
    // Resetear el input file
    if (imageInputRef.value) {
        imageInputRef.value.value = '';
    }
};

// Manejar items
const addItem = () => {
    form.items.push({ sku_id: '', quantity: 1 });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    } else {
        form.items[0] = { sku_id: '', quantity: 1 };
    }
};

// Enviar formulario
const submit = () => {
    form.post(route('admin.bundles.update', bundle.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Si se subió una nueva imagen, limpiar la previsualización
            if (form.image && imagePreview.value && imagePreview.value.startsWith('blob:')) {
                URL.revokeObjectURL(imagePreview.value);
            }
        }
    });
};

// Computed para calcular precio total
const totalPrice = ref('');
watch(() => form.fixed_price, (value) => {
    if (value) {
        totalPrice.value = parseFloat(value).toFixed(2);
    }
}, { immediate: true });
</script>

<template>
    <AdminLayout>
        <Head title="Editar Pack" />
        
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <Link 
                            :href="route('admin.bundles.index')"
                            class="btn btn-outline btn-sm flex items-center gap-2"
                        >
                            <ArrowLeft :size="16" />
                            Volver
                        </Link>
                        <div>
                            <h1 class="text-2xl font-black text-foreground">
                                Editar Pack
                            </h1>
                            <p class="text-muted-foreground text-sm">
                                ID: {{ bundle.id }} • Última actualización: {{ bundle.updated_at }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <span 
                            class="badge px-3 py-1 text-xs"
                            :class="bundle.is_active ? 'badge-success' : 'badge-error'"
                        >
                            {{ bundle.is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Columna izquierda - Información general -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Card de información general -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="font-bold text-foreground flex items-center gap-2">
                                <Package :size="18" />
                                Información General
                            </h3>
                        </div>
                        
                        <div class="card-content space-y-6">
                            <!-- Nombre -->
                            <div class="form-group">
                                <label class="form-label">Nombre del Pack *</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full"
                                    placeholder="Ej: Combo Parrillero Premium"
                                    :class="{ 'border-error': form.errors.name }"
                                />
                                <p v-if="form.errors.name" class="form-error">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Imagen -->
                            <div class="form-group">
                                <label class="form-label">Imagen del Pack</label>
                                
                                <!-- Previsualización de imagen -->
                                <div class="mb-4">
                                    <!-- Input de archivo oculto -->
                                    <input
                                        ref="imageInputRef"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="handleImageChange"
                                    />
                                    
                                    <!-- Área de drop/clic -->
                                    <div 
                                        class="relative w-full aspect-video rounded-lg overflow-hidden border-2 border-dashed border-border bg-muted/20 group cursor-pointer transition-all hover:border-primary"
                                        @click="triggerImageInput"
                                        @dragover.prevent
                                        @drop.prevent="handleDrop"
                                    >
                                        <img 
                                            v-if="imagePreview"
                                            :src="imagePreview"
                                            class="w-full h-full object-cover"
                                            alt="Previsualización"
                                        />
                                        
                                        <div 
                                            v-else
                                            class="w-full h-full flex flex-col items-center justify-center text-muted-foreground"
                                        >
                                            <Package :size="48" class="mb-2 opacity-40" />
                                            <p class="text-sm font-medium">Haz clic para subir una imagen</p>
                                            <p class="text-xs mt-1">Recomendado: 1200x800 px</p>
                                        </div>
                                        
                                        <!-- Overlay para cambio de imagen -->
                                        <div 
                                            v-if="imagePreview"
                                            class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                                        >
                                            <p class="text-white text-sm font-medium">Cambiar imagen</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Controles de imagen -->
                                    <div v-if="imagePreview" class="flex items-center justify-between mt-2">
                                        <button
                                            type="button"
                                            @click="clearImage"
                                            class="text-xs text-error hover:text-error/80 transition-colors"
                                        >
                                            Eliminar imagen
                                        </button>
                                        <span class="text-xs text-muted-foreground">
                                            {{ form.image ? 'Nueva imagen seleccionada' : 'Imagen actual' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <p v-if="form.errors.image" class="form-error">
                                    {{ form.errors.image }}
                                </p>
                            </div>

                            <!-- Descripción -->
                            <div class="form-group">
                                <label class="form-label">Descripción</label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full resize-none"
                                    placeholder="Describe los beneficios y contenido del pack..."
                                    :class="{ 'border-error': form.errors.description }"
                                ></textarea>
                                <p v-if="form.errors.description" class="form-error">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Estado y precio -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Precio fijo -->
                                <div class="form-group">
                                    <label class="form-label">Precio Fijo (Bs)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">
                                            Bs
                                        </span>
                                        <input
                                            v-model="form.fixed_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="pl-10"
                                            placeholder="0.00"
                                            :class="{ 'border-error': form.errors.fixed_price }"
                                        />
                                    </div>
                                    <p v-if="form.errors.fixed_price" class="form-error">
                                        {{ form.errors.fixed_price }}
                                    </p>
                                </div>

                                <!-- Estado -->
                                <div class="form-group">
                                    <label class="form-label">Estado</label>
                                    <label class="flex items-center gap-3 p-3 border border-input rounded-lg cursor-pointer hover:bg-muted/20 transition-colors">
                                        <input
                                            v-model="form.is_active"
                                            type="checkbox"
                                            class="w-5 h-5 text-primary rounded focus:ring-primary"
                                        />
                                        <span class="font-medium text-foreground">
                                            {{ form.is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Precio total -->
                            <div v-if="totalPrice" class="bg-primary/5 p-4 rounded-lg border border-primary/20">
                                <p class="text-sm text-muted-foreground">Precio total del pack</p>
                                <p class="text-2xl font-bold text-primary">
                                    Bs {{ totalPrice }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha - Items del pack -->
                <div class="lg:col-span-7">
                    <div class="card h-full">
                        <div class="card-header">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-foreground">
                                    Contenido del Pack
                                </h3>
                                <button
                                    type="button"
                                    @click="addItem"
                                    class="btn btn-outline btn-sm flex items-center gap-2"
                                >
                                    <Plus :size="14" />
                                    Agregar Item
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-content">
                            <!-- Lista de items -->
                            <div class="space-y-3">
                                <div
                                    v-for="(item, index) in form.items"
                                    :key="index"
                                    class="flex items-center gap-3 p-3 bg-muted/10 rounded-lg border border-border/50 hover:border-border transition-colors"
                                >
                                    <!-- Selector de SKU -->
                                    <div class="flex-1">
                                        <select
                                            v-model="item.sku_id"
                                            class="w-full"
                                            :class="{ 'border-error': form.errors[`items.${index}.sku_id`] }"
                                            required
                                        >
                                            <option value="" disabled>Seleccionar producto...</option>
                                            <option
                                                v-for="sku in props.skus.data"
                                                :key="sku.id"
                                                :value="sku.id"
                                            >
                                                {{ sku.name }} ({{ sku.code }})
                                            </option>
                                        </select>
                                        <p v-if="form.errors[`items.${index}.sku_id`]" class="form-error text-xs">
                                            {{ form.errors[`items.${index}.sku_id`] }}
                                        </p>
                                    </div>
                                    
                                    <!-- Cantidad -->
                                    <div class="w-24">
                                        <div class="form-group">
                                            <label class="form-label text-xs">Cantidad</label>
                                            <input
                                                v-model="item.quantity"
                                                type="number"
                                                min="1"
                                                class="text-center"
                                                :class="{ 'border-error': form.errors[`items.${index}.quantity`] }"
                                            />
                                        </div>
                                    </div>
                                    
                                    <!-- Botón eliminar -->
                                    <button
                                        type="button"
                                        @click="removeItem(index)"
                                        class="p-2 text-muted-foreground hover:text-error hover:bg-error/10 rounded-lg transition-colors"
                                        :disabled="form.items.length <= 1"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </div>

                            <!-- Estado vacío -->
                            <div
                                v-if="form.items.length === 0 || form.items.every(item => !item.sku_id)"
                                class="py-10 text-center text-muted-foreground"
                            >
                                <Package :size="32" class="mx-auto mb-3 opacity-30" />
                                <p class="font-medium">El pack está vacío</p>
                                <p class="text-sm mt-1">Agrega productos para crear tu pack</p>
                            </div>

                            <!-- Resumen -->
                            <div class="mt-6 pt-6 border-t border-border">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-muted-foreground">
                                            {{ form.items.filter(item => item.sku_id).length }} productos agregados
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-muted-foreground">
                                            Cantidad total de items:
                                        </p>
                                        <p class="text-xl font-bold text-foreground">
                                            {{ form.items.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer con acciones -->
                        <div class="card-footer">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p v-if="form.processing" class="text-sm text-muted-foreground">
                                        Guardando cambios...
                                    </p>
                                    <div v-if="Object.keys(form.errors).length > 0" class="flex items-center gap-2 text-error text-sm">
                                        <AlertCircle :size="14" />
                                        Corrige los errores antes de guardar
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <Link
                                        :href="route('admin.bundles.index')"
                                        class="btn btn-outline btn-md"
                                    >
                                        Cancelar
                                    </Link>
                                    <button
                                        type="button"
                                        @click="submit"
                                        :disabled="form.processing"
                                        class="btn btn-primary btn-md flex items-center gap-2"
                                    >
                                        <Save :size="16" />
                                        {{ form.processing ? 'Guardando...' : 'Actualizar Pack' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Transiciones para la imagen */
img {
    transition: transform 0.3s ease;
}

.group:hover img {
    transform: scale(1.05);
}
</style>