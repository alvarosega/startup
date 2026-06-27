<script setup>
import { ref } from 'vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    product: {
        type: Object,
        default: null
    },
    brands: {
        type: Array,
        required: true
    },
    categories: {
        type: Array,
        required: true
    },
    idempKey: {
        type: String,
        default: ''
    }
});

const isEditMode = ref(props.product !== null);
const activeTab = ref('master');
const showSkuModal = ref(false);

// Formulario 1: Persistencia del Producto Maestro (Sostiene la idempotencia)
const productForm = useForm({
    name: props.product?.name || '',
    brand_id: props.product?.brand_id || '',
    category_id: props.product?.category_id || '',
    description: props.product?.description || '',
    is_active: props.product ? Boolean(props.product.is_active) : true,
    is_alcoholic: props.product ? Boolean(props.product.is_alcoholic) : false,
    idempotency_key: props.product ? '' : props.idempKey,
    image: null
});

// Formulario 2: Inserción Masiva de Variantes Físicas (Sólo Alfanuméricos y Numéricos)
const bulkSkuForm = useForm({
    skus: []
});

// Formulario 3: Modificación Unitaria de Variante en Modal Flotante
const singleSkuForm = useForm({
    name: '',
    code: '',
    base_price: 0.00,
    conversion_factor: 1.000,
    weight: 0.000,
    is_active: true,
    image: null
});

const selectedSkuForEdit = ref(null);

/**
 * Añade una fila de variante limpia a la matriz dinámica de inserción masiva.
 */
const addBulkSkuRow = () => {
    if (bulkSkuForm.skus.length < 50) {
        bulkSkuForm.skus.push({
            name: `${productForm.name} `,
            code: '',
            base_price: 0.00,
            conversion_factor: 1.000,
            weight: 0.000,
            is_active: true
        });
    }
};

/**
 * Elimina una fila específica de la matriz dinámica previa inyección.
 */
const removeBulkSkuRow = (index) => {
    bulkSkuForm.skus.splice(index, 1);
};

/**
 * Despacha la creación o actualización del nodo maestro.
 */
const submitProductMaster = () => {
    if (isEditMode.value) {
        productForm.put(route('admin.catalog.products.update', { product: props.product.id }));
    } else {
        productForm.post(route('admin.catalog.products.store'));
    }
};

/**
 * Despacha el lote masivo de variantes hacia el servidor.
 */
const submitBulkSkus = () => {
    bulkSkuForm.post(route('admin.catalog.products.skus.store', { product: props.product.id }), {
        onSuccess: () => bulkSkuForm.reset()
    });
};

/**
 * Hidrata y activa el modal flotante de edición unitaria de variantes.
 */
const openSingleSkuEdit = (sku) => {
    selectedSkuForEdit.value = sku;
    singleSkuForm.name = sku.name;
    singleSkuForm.code = sku.code;
    singleSkuForm.base_price = sku.base_price;
    singleSkuForm.conversion_factor = sku.conversion_factor;
    singleSkuForm.weight = sku.weight;
    singleSkuForm.is_active = Boolean(sku.is_active);
    singleSkuForm.image = null;
    showSkuModal.value = true;
};

/**
 * Despacha la mutación de la variante comercial al endpoint de SkuController.
 */
const updateSingleSku = () => {
    singleSkuForm.put(route('admin.catalog.skus.update', { sku: selectedSkuForEdit.value.id }), {
        onSuccess: () => {
            showSkuModal.value = false;
            selectedSkuForEdit.value = null;
        }
    });
};

/**
 * Extrae de circulación lógicamente una variante comercial individual.
 */
const deleteSingleSku = (skuId) => {
    if (window.confirm('¿Desea extraer de circulación esta variante comercial de forma permanente?')) {
        form.delete(route('admin.catalog.skus.destroy', { sku: skuId }));
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            {{ isEditMode ? `Workspace: ${productForm.name}` : 'Creación de Producto Maestro' }}
        </template>

        <div class="space-y-6 max-w-5xl">
            <div class="flex border-b border-border select-none">
                <button type="button" @click="activeTab = 'master'" :class="[activeTab === 'master' ? 'border-primary text-primary font-bold' : 'border-transparent text-muted-foreground font-semibold hover:text-foreground']" class="border-b-2 px-4 py-2.5 text-xs uppercase tracking-wider transition-colors">
                    Información Base
                </button>
                <button type="button" :disabled="!isEditMode" @click="activeTab = 'skus'" :class="[activeTab === 'skus' ? 'border-primary text-primary font-bold' : 'border-transparent text-muted-foreground font-semibold hover:text-foreground disabled:opacity-40 disabled:cursor-not-allowed']" class="border-b-2 px-4 py-2.5 text-xs uppercase tracking-wider transition-colors">
                    Configuración de Variantes (SKUs)
                </button>
            </div>

            <div v-show="activeTab === 'master'" class="bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
                <form @submit.prevent="submitProductMaster" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Nombre Comercial Maestro *</label>
                            <input v-model="productForm.name" type="text" required class="admin-input" :class="{ 'border-error': productForm.errors.name }" placeholder="Jugo de Naranja 1L" />
                            <p v-if="productForm.errors.name" class="text-error text-xs font-medium mt-1">{{ productForm.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Marca Vinculada *</label>
                                <select v-model="productForm.brand_id" required class="admin-input" :class="{ 'border-error': productForm.errors.brand_id }">
                                    <option value="" disabled>Seleccione...</option>
                                    <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                                <p v-if="productForm.errors.brand_id" class="text-error text-xs font-medium mt-1">{{ productForm.errors.brand_id }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Categoría Raíz *</label>
                                <select v-model="productForm.category_id" required class="admin-input" :class="{ 'border-error': productForm.errors.category_id }">
                                    <option value="" disabled>Seleccione...</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <p v-if="productForm.errors.category_id" class="text-error text-xs font-medium mt-1">{{ productForm.errors.category_id }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Descripción del Producto</label>
                        <textarea v-model="productForm.description" rows="3" class="admin-input" :class="{ 'border-error': productForm.errors.description }" placeholder="Detalles de manufactura o ingredientes..."></textarea>
                        <p v-if="productForm.errors.description" class="text-error text-xs font-medium mt-1">{{ productForm.errors.description }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center border-t border-border/60 pt-3">
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block">Imagen Representativa del Maestro (WebP, JPG, PNG - Máx 2MB)</label>
                            <input type="file" accept="image/webp, image/jpeg, image/png" class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-secondary-foreground file:cursor-pointer" @input="productForm.image = $event.target.files[0]" />
                            <p v-if="productForm.errors.image" class="text-error text-xs font-medium mt-1">{{ productForm.errors.image }}</p>
                        </div>

                        <div class="flex items-center gap-6 sm:justify-end">
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input v-model="productForm.is_active" type="checkbox" class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" />
                                <span class="text-xs font-bold text-foreground uppercase tracking-wide">Producto Maestro Activo</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input v-model="productForm.is_alcoholic" type="checkbox" class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" />
                                <span class="text-xs font-bold text-foreground uppercase tracking-wide text-purple-600">Contiene Grado Alcohólico</span>
                            </label>
                        </div>
                    </div>
                    <p v-if="productForm.errors.product" class="text-error text-xs font-bold mt-2">{{ productForm.errors.product }}</p>

                    <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                        <Link :href="route('admin.catalog.products.index')" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                            Volver al índice
                        </Link>
                        <button type="submit" :disabled="productForm.processing" class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider">
                            <span>{{ productForm.processing ? 'Persistiendo Maestro...' : (isEditMode ? 'Sincronizar Maestro' : 'Guardar e Iniciar SKUs') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <div v-show="activeTab === 'skus' && isEditMode" class="space-y-6">
                <div class="bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
                    <div class="border-b border-border pb-2 flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Lote de Nuevas Variantes (Matriz Bulk)</h3>
                            <p class="text-[11px] text-muted-foreground mt-0.5">Defina múltiples presentaciones numéricas en bloque. Máximo 50 filas por despacho.</p>
                        </div>
                        <button type="button" @click="addBulkSkuRow" :disabled="bulkSkuForm.skus.length >= 50" class="px-2 py-1 bg-secondary text-secondary-foreground border border-border text-xs font-bold uppercase tracking-wider hover:bg-neutral-200 rounded disabled:opacity-40">
                            + Añadir Fila
                        </button>
                    </div>

                    <form @submit.prevent="submitBulkSkus" class="space-y-4">
                        <div v-if="bulkSkuForm.skus.length === 0" class="text-xs text-muted-foreground text-center py-6 border border-dashed border-border rounded">
                            Matriz vacía. Haga clic en "+ Añadir Fila" para estructurar nuevas presentaciones comerciales.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-border text-[10px] font-bold text-muted-foreground uppercase tracking-wider">
                                        <th class="pb-2">Nombre de Variante *</th>
                                        <th class="pb-2 w-32">Código EAN (Opcional)</th>
                                        <th class="pb-2 w-24 text-right">Precio Base *</th>
                                        <th class="pb-2 w-24 text-center">Fact. Conversión *</th>
                                        <th class="pb-2 w-24 text-center">Peso (Kg) *</th>
                                        <th class="pb-2 w-16 text-center">Estado</th>
                                        <th class="pb-2 w-10 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(sku, index) in bulkSkuForm.skus" :key="index" class="border-b border-border/40 last:border-0">
                                        <td class="py-2 pr-2">
                                            <input v-model="sku.name" type="text" required class="admin-input text-xs" :class="{ 'border-error': bulkSkuForm.errors[`skus.${index}.name`] }" />
                                        </td>
                                        <td class="py-2 pr-2">
                                            <input v-model="sku.code" type="text" class="admin-input text-xs font-mono" :class="{ 'border-error': bulkSkuForm.errors[`skus.${index}.code`] }" placeholder="Autogenerar" />
                                        </td>
                                        <td class="py-2 pr-2">
                                            <input v-model.number="sku.base_price" type="number" step="0.01" min="0" required class="admin-input text-xs font-mono text-right" :class="{ 'border-error': bulkSkuForm.errors[`skus.${index}.base_price`] }" />
                                        </td>
                                        <td class="py-2 pr-2">
                                            <input v-model.number="sku.conversion_factor" type="number" step="0.001" min="0.001" required class="admin-input text-xs font-mono text-center" :class="{ 'border-error': bulkSkuForm.errors[`skus.${index}.conversion_factor`] }" />
                                        </td>
                                        <td class="py-2 pr-2">
                                            <input v-model.number="sku.weight" type="number" step="0.001" min="0" required class="admin-input text-xs font-mono text-center" :class="{ 'border-error': bulkSkuForm.errors[`skus.${index}.weight`] }" />
                                        </td>
                                        <td class="py-2 text-center pr-2">
                                            <input v-model="sku.is_active" type="checkbox" class="rounded border-input text-primary w-4 h-4" />
                                        </td>
                                        <td class="py-2 text-center">
                                            <button type="button" @click="removeBulkSkuRow(index)" class="p-1 text-destructive hover:bg-destructive/5 rounded">
                                                <span class="material-symbols-rounded text-base block">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="bulkSkuForm.skus.length > 0" class="pt-3 border-t border-border/60 flex justify-end">
                            <button type="submit" :disabled="bulkSkuForm.processing" class="admin-btn-primary text-xs font-bold uppercase tracking-wider">
                                <span>{{ bulkSkuForm.processing ? 'Persistiendo Lote...' : 'Procesar Inserción Masiva' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
                    <div class="border-b border-border pb-1">
                        <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Variantes Comerciales Sincronizadas</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th class="admin-table-th w-14 text-center">Imagen</th>
                                    <th class="admin-table-th">Código EAN</th>
                                    <th class="admin-table-th">Nombre de Presentación</th>
                                    <th class="admin-table-th text-right">Precio Base</th>
                                    <th class="admin-table-th text-center">Factor</th>
                                    <th class="admin-table-th text-center">Peso (Kg)</th>
                                    <th class="admin-table-th text-center">Precios Relacionales</th>
                                    <th class="admin-table-th text-center">Estado</th>
                                    <th class="admin-table-th text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!product?.skus || product.skus.length === 0">
                                    <td colspan="9" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                        No existen variantes físicas asociadas a este producto maestro en el catálogo.
                                    </td>
                                </tr>
                                <tr v-for="sku in product?.skus" :key="sku.id" class="admin-table-tr">
                                    <td class="admin-table-td text-center py-1">
                                        <div class="w-8 h-8 bg-neutral-100 border border-border rounded flex items-center justify-center overflow-hidden mx-auto">
                                            <img v-if="sku.image_path" :src="`/storage/${sku.image_path}`" class="w-full h-full object-contain" alt="" />
                                            <span v-else class="material-symbols-rounded text-muted-foreground/30 text-base">image</span>
                                        </div>
                                    </td>
                                    <td class="admin-table-td font-mono text-xs font-bold text-foreground">{{ sku.code }}</td>
                                    <td class="admin-table-td text-xs text-foreground font-semibold">{{ sku.name }}</td>
                                    <td class="admin-table-td font-mono text-xs text-right text-foreground font-bold">{{ sku.base_price.toFixed(2) }}</td>
                                    <td class="admin-table-td font-mono text-xs text-center text-muted-foreground">{{ sku.conversion_factor.toFixed(3) }}</td>
                                    <td class="admin-table-td font-mono text-xs text-center text-muted-foreground">{{ sku.weight.toFixed(3) }}</td>
                                    <td class="admin-table-td text-center font-mono text-[11px] text-muted-foreground">
                                        {{ sku.prices?.length || 0 }} perfiles vinculados
                                    </td>
                                    <td class="admin-table-td text-center">
                                        <span :class="sku.is_active ? 'badge-success text-[10px]' : 'badge-error text-[10px]'">
                                            {{ sku.is_active ? 'Operativo' : 'Suspendido' }}
                                        </span>
                                    </td>
                                    <td class="admin-table-td text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" @click="openSingleSkuEdit(sku)" class="px-2 py-0.5 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200">
                                                Configurar
                                            </button>
                                            <button type="button" @click="deleteSingleSku(sku.id)" class="px-2 py-0.5 bg-destructive/5 text-destructive border border-destructive/15 text-xs font-semibold rounded hover:bg-destructive/10">
                                                Extraer
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showSkuModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 animate-in fade-in duration-100">
            <div class="fixed inset-0 bg-neutral-950/50 backdrop-blur-sm" @click="showSkuModal = false"></div>
            
            <div class="relative w-full max-w-md bg-card border border-border rounded-md shadow-flat p-6 z-10 space-y-4">
                <div class="flex items-center justify-between mb-2 pb-2 border-b border-border">
                    <h2 class="text-sm font-bold text-foreground uppercase tracking-wide flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-lg text-primary">edit_square</span>
                        <span>Parámetros de Variante</span>
                    </h2>
                    <button type="button" @click="showSkuModal = false" class="p-1 rounded hover:bg-neutral-100 text-muted-foreground">
                        <span class="material-symbols-rounded text-base block">close</span>
                    </button>
                </div>

                <form @submit.prevent="updateSingleSku" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Código EAN Identificador (Inmutable)</label>
                        <input v-model="singleSkuForm.code" type="text" readonly disabled class="admin-input font-mono bg-neutral-100 dark:bg-neutral-800 text-muted-foreground cursor-not-allowed font-bold" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Nombre de Presentación *</label>
                        <input v-model="singleSkuForm.name" type="text" required class="admin-input" :class="{ 'border-error': singleSkuForm.errors.name }" />
                        <p v-if="singleSkuForm.errors.name" class="text-error text-xs font-medium mt-1">{{ singleSkuForm.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Precio Base *</label>
                            <input v-model.number="singleSkuForm.base_price" type="number" step="0.01" min="0" required class="admin-input font-mono text-right" :class="{ 'border-error': singleSkuForm.errors.base_price }" />
                            <p v-if="singleSkuForm.errors.base_price" class="text-error text-xs font-medium mt-1">{{ singleSkuForm.errors.base_price }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Factor *</label>
                            <input v-model.number="singleSkuForm.conversion_factor" type="number" step="0.001" min="0.001" required class="admin-input font-mono text-center" :class="{ 'border-error': singleSkuForm.errors.conversion_factor }" />
                            <p v-if="singleSkuForm.errors.conversion_factor" class="text-error text-xs font-medium mt-1">{{ singleSkuForm.errors.conversion_factor }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Peso (Kg) *</label>
                            <input v-model.number="singleSkuForm.weight" type="number" step="0.001" min="0" required class="admin-input font-mono text-center" :class="{ 'border-error': singleSkuForm.errors.weight }" />
                            <p v-if="singleSkuForm.errors.weight" class="text-error text-xs font-medium mt-1">{{ singleSkuForm.errors.weight }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block">Logotipo / Imagen de Variante (Opcional - Máx 2MB)</label>
                        <input type="file" accept="image/webp, image/jpeg, image/png" class="w-full text-xs text-muted-foreground file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-secondary-foreground" @input="singleSkuForm.image = $event.target.files[0]" />
                        <p  v-if="singleSkuForm.errors.image" class="text-error text-xs font-medium mt-1">{{ singleSkuForm.errors.image }}</p>
                    </div>

                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input v-model="singleSkuForm.is_active" type="checkbox" class="rounded border-input text-primary w-4 h-4" />
                            <span class="text-xs font-bold text-foreground uppercase tracking-wide">Variante Comercial Habilitada para Venta</span>
                        </label>
                    </div>
                    <p v-if="singleSkuForm.errors.sku" class="text-error text-xs font-bold mt-1 flex items-center gap-1">
                        <span class="material-symbols-rounded text-sm shrink-0">error</span>
                        <span>{{ singleSkuForm.errors.sku }}</span>
                    </p>

                    <div class="pt-4 border-t border-border flex items-center justify-end gap-3 shrink-0">
                        <button type="button" @click="showSkuModal = false" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                            Cancelar
                        </button>
                        <button type="button" @click="updateSingleSku" :disabled="singleSkuForm.processing" class="admin-btn-primary text-xs font-bold uppercase tracking-wider">
                            <span>{{ singleSkuForm.processing ? 'Sincronizando...' : 'Actualizar Variante' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>