<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, ArrowLeft, Package, Plus, Trash2, AlertCircle, CheckCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    skus: Object
});

const form = useForm({
    name: '',
    description: '',
    fixed_price: '',
    is_active: true,
    image: null,
    items: [{ sku_id: '', quantity: 1 }]
});

// REFS para manejar elementos DOM
const imageInputRef = ref(null);
const imagePreview = ref(null);

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
    imagePreview.value = null;
    
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
    form.post(route('admin.bundles.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Limpiar previsualización si existe
            if (imagePreview.value && imagePreview.value.startsWith('blob:')) {
                URL.revokeObjectURL(imagePreview.value);
            }
        }
    });
};

// Validar que haya al menos un item con SKU
const validateForm = () => {
    const hasValidItem = form.items.some(item => item.sku_id);
    if (!hasValidItem) {
        form.setError('items', 'Debe agregar al menos un producto al pack');
        return false;
    }
    if (!form.name.trim()) {
        form.setError('name', 'El nombre del pack es requerido');
        return false;
    }
    return true;
};

const handleSubmit = () => {
    if (validateForm()) {
        submit();
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Crear Nuevo Pack" />
        
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-6">
                    <Link 
                        :href="route('admin.bundles.index')"
                        class="btn btn-outline btn-sm flex items-center gap-2"
                    >
                        <ArrowLeft :size="16" />
                        Volver
                    </Link>
                    <div>
                        <h1 class="text-2xl font-black text-foreground">
                            Crear Nuevo Pack
                        </h1>
                        <p class="text-muted-foreground text-sm">
                            Crea una oferta combinada de productos
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Columna izquierda - Información general -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Card de información general -->
                    <div class="card animate-slide-up">
                        <div class="card-header">
                            <h3 class="font-bold text-foreground flex items-center gap-2">
                                <Package :size="18" />
                                Información del Pack
                            </h3>
                        </div>
                        
                        <div class="card-content space-y-6">
                            <!-- Nombre -->
                            <div class="form-group">
                                <label class="form-label">
                                    Nombre del Pack *
                                    <span class="text-xs text-muted-foreground ml-1">
                                        (Máximo 80 caracteres)
                                    </span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full"
                                    placeholder="Ej: Combo Oficina Básico"
                                    maxlength="80"
                                    :class="{ 'border-error': form.errors.name }"
                                />
                                <div class="flex justify-between items-center mt-1">
                                    <p v-if="form.errors.name" class="form-error">
                                        {{ form.errors.name }}
                                    </p>
                                    <span class="text-xs text-muted-foreground">
                                        {{ form.name.length }}/80
                                    </span>
                                </div>
                            </div>

                            <!-- Imagen -->
                            <div class="form-group">
                                <label class="form-label">
                                    Imagen del Pack
                                    <span class="text-xs text-muted-foreground ml-1">
                                        (Opcional)
                                    </span>
                                </label>
                                
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
                                        @drop.prevent="handleImageChange"
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
                                            <div class="mb-3 p-3 bg-muted/50 rounded-full">
                                                <Package :size="24" />
                                            </div>
                                            <p class="text-sm font-medium">Subir imagen del pack</p>
                                            <p class="text-xs mt-1 text-center px-4">
                                                Haz clic para seleccionar una imagen
                                            </p>
                                            <p class="text-xs text-muted-foreground/70 mt-2">
                                                Recomendado: 1200x800 px • PNG, JPG, WebP
                                            </p>
                                        </div>
                                        
                                        <!-- Overlay -->
                                        <div 
                                            v-if="imagePreview"
                                            class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center"
                                        >
                                            <CheckCircle :size="24" class="text-white mb-2" />
                                            <p class="text-white text-sm font-medium">Imagen lista</p>
                                            <p class="text-white text-xs mt-1 opacity-90">Haz clic para cambiar</p>
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
                                        <span class="text-xs text-success flex items-center gap-1">
                                            <CheckCircle :size="12" />
                                            Imagen cargada
                                        </span>
                                    </div>
                                </div>
                                
                                <p v-if="form.errors.image" class="form-error">
                                    {{ form.errors.image }}
                                </p>
                            </div>

                            <!-- Descripción -->
                            <div class="form-group">
                                <label class="form-label">
                                    Descripción
                                    <span class="text-xs text-muted-foreground ml-1">
                                        (Opcional)
                                    </span>
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full resize-none"
                                    placeholder="Describe los beneficios del pack, incluye detalles que atraigan a los clientes..."
                                    maxlength="500"
                                    :class="{ 'border-error': form.errors.description }"
                                ></textarea>
                                <div class="flex justify-between items-center mt-1">
                                    <p v-if="form.errors.description" class="form-error">
                                        {{ form.errors.description }}
                                    </p>
                                    <span class="text-xs text-muted-foreground">
                                        {{ form.description.length }}/500
                                    </span>
                                </div>
                            </div>

                            <!-- Precio y estado -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Precio fijo -->
                                <div class="form-group">
                                    <label class="form-label">
                                        Precio Fijo (Bs)
                                        <span class="text-xs text-muted-foreground ml-1">
                                            (Opcional)
                                        </span>
                                    </label>
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
                                    <p class="text-xs text-muted-foreground mt-1">
                                        Deja en blanco para precio dinámico
                                    </p>
                                </div>

                                <!-- Estado -->
                                <div class="form-group">
                                    <label class="form-label">Estado inicial</label>
                                    <label class="flex items-center gap-3 p-3 border border-input rounded-lg cursor-pointer hover:bg-muted/20 transition-colors">
                                        <input
                                            v-model="form.is_active"
                                            type="checkbox"
                                            class="w-5 h-5 text-primary rounded focus:ring-primary"
                                        />
                                        <div>
                                            <span class="font-medium text-foreground block">
                                                {{ form.is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                            <span class="text-xs text-muted-foreground">
                                                {{ form.is_active ? 'Visible en la tienda' : 'Oculto temporalmente' }}
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Consejo -->
                            <div class="bg-primary/5 p-4 rounded-lg border border-primary/20">
                                <div class="flex items-start gap-3">
                                    <CheckCircle :size="16" class="text-primary mt-0.5" />
                                    <div>
                                        <p class="text-sm font-medium text-foreground">
                                            Consejo para packs exitosos
                                        </p>
                                        <p class="text-xs text-muted-foreground mt-1">
                                            Los packs con descuento entre 15-30% y productos complementarios tienen mejor conversión.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha - Items del pack -->
                <div class="lg:col-span-7">
                    <div class="card h-full animate-slide-up">
                        <div class="card-header">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-foreground">
                                        Productos del Pack
                                    </h3>
                                    <p class="text-sm text-muted-foreground mt-1">
                                        Selecciona los productos que incluirá este pack
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="addItem"
                                    class="btn btn-outline btn-sm flex items-center gap-2"
                                >
                                    <Plus :size="14" />
                                    Agregar Producto
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-content">
                            <!-- Lista de items -->
                            <div class="space-y-3">
                                <div
                                    v-for="(item, index) in form.items"
                                    :key="index"
                                    class="flex items-center gap-3 p-4 bg-card rounded-lg border border-input hover:border-primary/30 transition-all duration-200 group"
                                >
                                    <!-- Selector de SKU -->
                                    <div class="flex-1">
                                        <label class="form-label text-xs mb-1">
                                            Producto {{ index + 1 }}
                                        </label>
                                        <select
                                            v-model="item.sku_id"
                                            class="w-full"
                                            :class="{ 'border-error': form.errors[`items.${index}.sku_id`] }"
                                            required
                                        >
                                            <option value="" disabled>
                                                Seleccionar producto...
                                            </option>
                                            <option
                                                v-for="sku in props.skus.data"
                                                :key="sku.id"
                                                :value="sku.id"
                                            >
                                                {{ sku.name }} ({{ sku.code }}) - Bs {{ sku.price }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors[`items.${index}.sku_id`]" class="form-error text-xs">
                                            {{ form.errors[`items.${index}.sku_id`] }}
                                        </p>
                                    </div>
                                    
                                    <!-- Cantidad -->
                                    <div class="w-28">
                                        <div class="form-group">
                                            <label class="form-label text-xs">Cantidad</label>
                                            <div class="flex items-center">
                                                <button
                                                    type="button"
                                                    @click="item.quantity > 1 ? item.quantity-- : null"
                                                    class="px-3 py-2 border border-input rounded-l-lg hover:bg-muted transition-colors"
                                                    :disabled="item.quantity <= 1"
                                                >
                                                    -
                                                </button>
                                                <input
                                                    v-model.number="item.quantity"
                                                    type="number"
                                                    min="1"
                                                    class="w-full text-center border-y border-input"
                                                />
                                                <button
                                                    type="button"
                                                    @click="item.quantity++"
                                                    class="px-3 py-2 border border-input rounded-r-lg hover:bg-muted transition-colors"
                                                >
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Botón eliminar -->
                                    <button
                                        type="button"
                                        @click="removeItem(index)"
                                        class="p-2 text-muted-foreground hover:text-error hover:bg-error/10 rounded-lg transition-colors mt-6"
                                        :disabled="form.items.length <= 1"
                                        :title="form.items.length <= 1 ? 'Debe haber al menos un producto' : 'Eliminar producto'"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </div>

                            <!-- Estado vacío -->
                            <div
                                v-if="!form.items.some(item => item.sku_id)"
                                class="py-10 text-center text-muted-foreground border-2 border-dashed border-border rounded-lg mt-4"
                            >
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-muted/30 flex items-center justify-center">
                                    <Package :size="24" />
                                </div>
                                <p class="font-medium">Sin productos agregados</p>
                                <p class="text-sm mt-1">
                                    Agrega productos para crear un pack atractivo
                                </p>
                                <button
                                    type="button"
                                    @click="addItem"
                                    class="btn btn-outline btn-sm mt-4"
                                >
                                    Agregar primer producto
                                </button>
                            </div>

                            <!-- Resumen -->
                            <div
                                v-if="form.items.some(item => item.sku_id)"
                                class="mt-6 pt-6 border-t border-border"
                            >
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-muted/10 p-4 rounded-lg">
                                        <p class="text-sm text-muted-foreground">
                                            Productos únicos
                                        </p>
                                        <p class="text-xl font-bold text-foreground mt-1">
                                            {{ new Set(form.items.filter(item => item.sku_id).map(item => item.sku_id)).size }}
                                        </p>
                                    </div>
                                    <div class="bg-muted/10 p-4 rounded-lg">
                                        <p class="text-sm text-muted-foreground">
                                            Items totales
                                        </p>
                                        <p class="text-xl font-bold text-foreground mt-1">
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
                                    <div v-if="form.errors.items" class="alert alert-error flex items-center gap-2">
                                        <AlertCircle :size="14" />
                                        {{ form.errors.items }}
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
                                        @click="handleSubmit"
                                        :disabled="form.processing || !form.items.some(item => item.sku_id) || !form.name.trim()"
                                        class="btn btn-primary btn-md flex items-center gap-2"
                                    >
                                        <Save :size="16" />
                                        {{ form.processing ? 'Creando...' : 'Crear Pack' }}
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
.animate-slide-up {
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>