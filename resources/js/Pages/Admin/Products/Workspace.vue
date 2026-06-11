<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { useForm, Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Save, ArrowLeft, Info, Package, AlertCircle, Plus, Trash2, ShieldAlert, Coins
} from 'lucide-vue-next';

const props = defineProps({
    product: Object,     // Null en creación, Object en edición
    brands: Array,
    categories: Array,
    idempKey: String
});

// Inicialización adaptativa de pestaña mediante parámetro Query URL (?tab=X)
const urlParams = new URLSearchParams(window.location.search);
const initialTab = urlParams.get('tab') ? parseInt(urlParams.get('tab')) : 1;
const activeTab = ref(initialTab);

// --- PESTAÑA 1: FORMULARIO DEL MAESTRO ---
const masterForm = useForm({
    name: props.product?.name || '',
    slug: props.product?.slug || '',
    brand_id: props.product?.brand_id || '',
    category_id: props.product?.category_id || '',
    description: props.product?.description || '',
    is_active: props.product ? props.product.is_active : true,
    is_alcoholic: props.product ? props.product.is_alcoholic : false,
    image: null,
    idempotencyKey: props.idempKey
});

// --- PESTAÑA 2: MATRIZ DE VARIANTES EN MEMORIA (HOJA DE CÁLCULO) ---
const localSkus = ref([]);

const addSkuRow = () => {
    localSkus.value.push({
        name: `${masterForm.name} `,
        code: '', // Vacío delega el cálculo automático EAN-13 al backend
        price: 0.00,
        weight: 0.000,
        conversionFactor: 1.000,
        isActive: true
    });
};

const removeSkuRow = (index) => {
    localSkus.value.splice(index, 1);
};

const submitBulkSkus = () => {
    if (localSkus.value.length === 0) return;
    
    router.post(route('admin.products.skus.store', props.product.id), {
        skus: localSkus.value
    }, {
        onSuccess: () => {
            localSkus.value = []; // Resetear hoja de cálculo tras persistencia exitosa
        }
    });
};

const deleteExistingSku = (id) => {
    if (confirm('¿Remover esta variante física del catálogo permanente?')) {
        router.delete(route('admin.products.skus.destroy', id));
    }
};

// --- ALGORITMO GUARDIÁN: INTERCEPCIÓN DE CAMBIOS SIN SINCRONIZAR ---
const isDirty = computed(() => {
    if (masterForm.isDirty) return true;
    if (localSkus.value.length > 0) return true;
    return false;
});

// Intercepta cierres de pestaña o recargas forzadas del navegador (F5 / Ctrl+R)
const handleBeforeUnload = (e) => {
    if (isDirty.value) {
        e.preventDefault();
        e.returnValue = 'Hay modificaciones sin sincronizar en el Workspace. ¿Desea salir?';
    }
};
window.addEventListener('beforeunload', handleBeforeUnload);

// Intercepta transiciones internas de la navegación SPA de Inertia
const removeInertiaGuard = router.on('before', (event) => {
    if (isDirty.value) {
        if (!confirm('Tiene transacciones pendientes en el canvas actual. ¿Desea descartar los cambios?')) {
            event.preventDefault();
        }
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
    removeInertiaGuard();
});

// --- SUBMIT DEL MAESTRO ---
const submitMaster = () => {
    if (props.product) {
        masterForm.transform((data) => ({ ...data, _method: 'PUT' }))
                  .post(route('admin.products.update', props.product.id));
    } else {
        masterForm.post(route('admin.products.store'));
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="product ? `Workspace - ${product.name}` : 'Workspace - Crear Maestro'" />

        <div class="space-y-6">
            <div class="flex items-center justify-between bg-card p-4 rounded-xl border border-border shadow-sm">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.products.index')" class="p-2 bg-muted text-muted-foreground hover:text-foreground rounded-lg transition-colors">
                        <ArrowLeft :size="16" />
                    </Link>
                    <div>
                        <h1 class="font-sans font-bold text-lg text-foreground">
                            {{ product ? 'Espacio de Trabajo / Edición' : 'Espacio de Trabajo / Registro Inicial' }}
                        </h1>
                        <p class="text-xs text-muted-foreground">Configuración molecular y empaquetado del producto</p>
                    </div>
                </div>

                <div v-if="isDirty" class="hidden sm:flex items-center gap-1.5 text-xs text-amber-500 font-semibold bg-amber-500/10 px-3 py-1.5 rounded-lg border border-amber-500/20 animate-pulse">
                    <ShieldAlert :size="14" />
                    Modificaciones sin sincronizar
                </div>
            </div>

            <div class="flex border-b border-border gap-2">
                <button @click="activeTab = 1" :class="activeTab === 1 ? 'border-primary text-primary font-semibold' : 'border-transparent text-muted-foreground hover:text-foreground'" class="px-4 py-2 text-sm border-b-2 transition-all duration-150 outline-none flex items-center gap-2">
                    <Info :size="16" />
                    1. Información General
                </button>
                <button :disabled="!product" @click="activeTab = 2" :class="[activeTab === 2 ? 'border-primary text-primary font-semibold' : 'border-transparent text-muted-foreground hover:text-foreground', !product && 'opacity-40 cursor-not-allowed']" class="px-4 py-2 text-sm border-b-2 transition-all duration-150 outline-none flex items-center gap-2">
                    <Package :size="16" />
                    2. Presentaciones y SKUs (Variantes)
                </button>
            </div>

            <div v-show="activeTab === 1" class="bg-card border border-border rounded-xl p-6 shadow-sm">
                <form @submit.prevent="submitMaster" class="space-y-5">
                    <div v-if="Object.keys(masterForm.errors).length" class="p-3 bg-destructive/10 border border-destructive/20 text-destructive rounded-lg space-y-1 text-sm">
                        <div v-for="(error, key) in masterForm.errors" :key="key" class="flex items-start gap-2">
                            <AlertCircle :size="16" class="shrink-0 mt-0.5" />
                            <span>{{ error }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Denominación del Producto Base *</label>
                            <input v-model="masterForm.name" type="text" required class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Pasillo / Categoría Raíz *</label>
                            <select v-model="masterForm.category_id" required class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none">
                                <option value="" disabled>Seleccione pasillo...</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Firma / Marca Comercial *</label>
                            <select v-model="masterForm.brand_id" required class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none">
                                <option value="" disabled>Seleccione marca...</option>
                                <option v-for="br in brands" :key="br.id" :value="br.id">{{ br.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-muted/20 p-4 rounded-xl border border-border/60">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="masterForm.is_active" type="checkbox" class="rounded border-border text-primary focus:ring-primary bg-background w-4 h-4" />
                            <span class="text-sm text-foreground select-none">Habilitado para distribución inmediata</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="masterForm.is_alcoholic" type="checkbox" class="rounded border-border text-primary focus:ring-primary bg-background w-4 h-4" />
                            <span class="text-sm text-foreground select-none">Exige control de edad (+18 licores)</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Imagen Representativa Principal (Max 2MB)</label>
                        <input type="file" @input="masterForm.image = $event.target.files[0]" accept="image/*" class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Ficha Informativa / Descripción Comercial</label>
                        <textarea v-model="masterForm.description" rows="3" class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none resize-none"></textarea>
                    </div>

                    <div class="border-t border-border pt-4 flex justify-end">
                        <button type="submit" :disabled="masterForm.processing" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 shadow-sm transition-colors">
                            <Save :size="16" />
                            Sincronizar Datos Base
                        </button>
                    </div>
                </form>
            </div>

            <div v-if="activeTab === 2 && product" class="space-y-6">
                <div class="bg-card p-4 border border-border rounded-xl shadow-sm space-y-3">
                    <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Variantes Activas en el Sistema</h3>
                    <div class="overflow-x-auto border border-border/60 rounded-lg">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-muted/50 font-semibold text-muted-foreground border-b border-border">
                                <tr>
                                    <th class="p-3">Descripción de Variante</th>
                                    <th class="p-3 font-mono w-40">Código EAN</th>
                                    <th class="p-3 text-right w-32">Precio Ref. Global</th>
                                    <th class="p-3 text-right w-24">Factor Conv.</th>
                                    <th class="p-3 text-right w-24">Peso (Kg)</th>
                                    <th class="p-3 text-center w-24">Estado</th>
                                    <th class="p-3 text-right w-24">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="sku in product.skus" :key="sku.id" class="hover:bg-muted/10">
                                    <td class="p-3 font-medium text-foreground">{{ sku.name }}</td>
                                    <td class="p-3 font-mono font-bold text-primary tracking-tight">{{ sku.code }}</td>
                                    <td class="p-3 text-right font-mono">{{ sku.base_price.toFixed(2) }}</td>
                                    <td class="p-3 text-right font-mono">{{ sku.conversion_factor.toFixed(3) }}</td>
                                    <td class="p-3 text-right font-mono">{{ sku.weight.toFixed(3) }}</td>
                                    <td class="p-3 text-center">
                                        <span :class="sku.is_active ? 'text-emerald-500' : 'text-destructive'" class="font-medium">
                                            {{ sku.is_active ? 'Activo' : 'Oculto' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right whitespace-nowrap space-x-1">
                                        <button type="button" class="inline-flex items-center gap-1 px-2 py-1 bg-primary/10 text-primary hover:bg-primary/20 rounded font-medium transition-colors">
                                            <Coins :size="12" />
                                            Precios
                                        </button>
                                        <button type="button" @click="deleteExistingSku(sku.id)" class="p-1 text-destructive hover:bg-destructive/10 rounded transition-colors">
                                            <Trash2 :size="13" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!product.skus || product.skus.length === 0">
                                    <td colspan="7" class="p-4 text-center text-muted-foreground italic">
                                        Este producto maestro no posee presentaciones dadas de alta.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-card p-6 border border-border rounded-xl shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-foreground uppercase tracking-wider">Hojas de Inyección Masiva</h2>
                            <p class="text-xs text-muted-foreground">Prepare y evalúe las variantes en memoria antes de persistir el bloque físico</p>
                        </div>
                        <button type="button" @click="addSkuRow" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-muted text-foreground hover:bg-muted/80 text-xs font-semibold rounded-lg border border-border transition-colors">
                            <Plus :size="14" />
                            Añadir Fila
                        </button>
                    </div>

                    <div v-if="localSkus.length > 0" class="overflow-x-auto border border-border rounded-lg">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-muted/30 font-semibold text-muted-foreground border-b border-border">
                                <tr>
                                    <th class="p-3">Nombre Específico / Empaque *</th>
                                    <th class="p-3 w-48">Código EAN (Opcional)</th>
                                    <th class="p-3 w-32 text-right">Precio Sugerido *</th>
                                    <th class="p-3 w-28 text-right">F. Conversión *</th>
                                    <th class="p-3 w-28 text-right">Peso Kg</th>
                                    <th class="p-3 w-20 text-center">Activo</th>
                                    <th class="p-3 w-12 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border bg-background/50">
                                <tr v-for="(row, index) in localSkus" :key="index">
                                    <td class="p-2">
                                        <input v-model="row.name" type="text" required class="w-full bg-background border border-border rounded px-2 py-1 text-xs focus:ring-1 focus:ring-primary outline-none" />
                                    </td>
                                    <td class="p-2">
                                        <input v-model="row.code" type="text" placeholder="Dejar vacío para EAN automático" class="w-full bg-background border border-border rounded px-2 py-1 text-xs font-mono focus:ring-1 focus:ring-primary outline-none" />
                                    </td>
                                    <td class="p-2">
                                        <input v-model.number="row.price" type="number" step="0.01" min="0" required class="w-full bg-background border border-border rounded px-2 py-1 text-xs text-right font-mono focus:ring-1 focus:ring-primary outline-none" />
                                    </td>
                                    <td class="p-2">
                                        <input v-model.number="row.conversionFactor" type="number" step="0.001" min="0.001" required class="w-full bg-background border border-border rounded px-2 py-1 text-xs text-right font-mono focus:ring-1 focus:ring-primary outline-none" />
                                    </td>
                                    <td class="p-2">
                                        <input v-model.number="row.weight" type="number" step="0.001" min="0" class="w-full bg-background border border-border rounded px-2 py-1 text-xs text-right font-mono focus:ring-1 focus:ring-primary outline-none" />
                                    </td>
                                    <td class="p-2 text-center">
                                        <input v-model="row.isActive" type="checkbox" class="rounded border-border text-primary focus:ring-primary w-4 h-4 bg-background" />
                                    </td>
                                    <td class="p-2 text-center">
                                        <button type="button" @click="removeSkuRow(index)" class="text-destructive hover:text-destructive/80 p-1">
                                            <Trash2 :size="14" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="p-8 border border-dashed border-border rounded-xl text-center text-xs text-muted-foreground">
                        No hay registros en la hoja temporal. Presione "Añadir Fila" para estructurar nuevas variantes físicas.
                    </div>

                    <div v-if="localSkus.length > 0" class="flex justify-end pt-2">
                        <button type="button" @click="submitBulkSkus" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 shadow-sm transition-colors">
                            <Save :size="16" />
                            Persistir Variantes en Catálogo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>