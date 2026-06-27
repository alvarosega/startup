<script setup>
import { computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    branches: {
        type: Array,
        required: true
    },
    // Propiedad perezosa alimentada mediante recargas parciales de Inertia al fijar la sucursal
    available_lots: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    branch_id: '',
    reason: 'expiration',
    notes: '',
    items: []
});

/**
 * Gatilla la recarga parcial de Inertia para recuperar los lotes activos con stock ordinario > 0
 */
const fetchBranchLots = () => {
    form.items = []; // Resetea líneas ante mutaciones de nodo regional
    if (!form.branch_id) return;

    router.reload({
        only: ['available_lots'],
        data: { branch_id: form.branch_id },
        preserveState: true
    });
};

/**
 * Inyecta una línea de destrucción en blanco a la matriz reactiva.
 */
const addItemRow = () => {
    form.items.push({
        inventory_lot_id: '',
        quantity: 0.001,
        unit_cost: 0.00
    });
};

/**
 * Purga una línea específica del lote de destrucción.
 */
const removeItemRow = (index) => {
    form.items.splice(index, 1);
};

/**
 * Recupera el objeto de datos del lote seleccionado para calcular topes de validación local.
 */
const getSelectedLotData = (lotId) => {
    return props.available_lots.find(lot => lot.id === lotId) || null;
};

/**
 * Valida de forma estricta que la cantidad a destruir no supere el stock disponible del lote.
 */
const validateLotStockLimit = (item) => {
    const lot = getSelectedLotData(item.inventory_lot_id);
    if (!lot) return true;
    return Number(item.quantity) <= Number(lot.quantity);
};

/**
 * Sumatoria contable estimada de la merma a procesar.
 */
const totalEstimatedLoss = computed(() => {
    return form.items.reduce((sum, item) => sum + ((Number(item.quantity) || 0) * (Number(item.unit_cost) || 0)), 0);
});

/**
 * Valida la consistencia general del lote de mermas antes del envío.
 */
const isFormInvalid = computed(() => {
    if (form.items.length === 0) return true;
    return form.items.some(item => !item.inventory_lot_id || !validateLotStockLimit(item) || item.quantity <= 0);
});

const submit = () => {
    if (isFormInvalid.value) return;
    form.post(route('admin.removals.store'));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Declaración de Merma y Extracción de Almacén
        </template>

        <div class="bg-card border border-border rounded-md shadow-flat p-5 space-y-6">
            <form @submit.prevent="submit" class="space-y-5">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Sucursal Afectada *</label>
                        <select 
                            v-model="form.branch_id" 
                            required 
                            class="admin-input" 
                            :class="{ 'border-error': form.errors.branch_id }"
                            @change="fetchBranchLots"
                        >
                            <option value="" disabled>Seleccione sucursal de extracción...</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <p v-if="form.errors.branch_id" class="text-error text-[11px] font-medium mt-1">{{ form.errors.branch_id }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Motivo Técnico de Baja *</label>
                        <select v-model="form.reason" required class="admin-input">
                            <option value="expiration">EXPIRACIÓN (Producto Caducado)</option>
                            <option value="damage">DAMAGE (Rotura / Avería / Daño Físico)</option>
                            <option value="theft">THEFT (Pérdida No Justificada / Robo)</option>
                            <option value="internal_use">INTERNAL USE (Consumo Interno Operaciones)</option>
                            <option value="admin_error">ADMIN ERROR (Ajuste por Defecto de Entrada)</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Notas de Justificación y Soporte</label>
                    <textarea v-model="form.notes" rows="2" maxlength="1000" class="admin-input" :class="{ 'border-error': form.errors.notes }" placeholder="Detalle el rastro del incidente o nro de acta interna..."></textarea>
                    <p v-if="form.errors.notes" class="text-error text-[11px] font-medium mt-1">{{ form.errors.notes }}</p>
                </div>

                <div class="space-y-3">
                    <div class="border-b border-border pb-1.5 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Artículos y Capas a Destruir</h3>
                        <button 
                            type="button" 
                            :disabled="!form.branch_id" 
                            @click="addItemRow" 
                            class="px-2 py-1 bg-secondary text-secondary-foreground border border-border text-xs font-bold uppercase tracking-wider hover:bg-neutral-200 rounded disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            + Seleccionar Lote
                        </button>
                    </div>

                    <div v-if="form.items.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-border text-[10px] font-bold text-muted-foreground uppercase tracking-wider">
                                    <th class="pb-2">Lote Activo Disponible *</th>
                                    <th class="pb-2 w-36 text-right">Cant. Disponible</th>
                                    <th class="pb-2 w-32 text-right pl-3">Cant. Extraer *</th>
                                    <th class="pb-2 w-32 text-right pl-3">Costo Unit. Estimado *</th>
                                    <th class="pb-2 w-32 text-right">Subtotal Pérdida</th>
                                    <th class="pb-2 w-10 text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in form.items" :key="index" class="border-b border-border/40 last:border-0 align-top">
                                    <td class="py-2 pr-2">
                                        <select v-model="item.inventory_lot_id" required class="admin-input text-xs font-mono" :class="{ 'border-error': form.errors[`items.${index}.inventory_lot_id`] }">
                                            <option value="" disabled>Seleccione capa FIFO...</option>
                                            <option v-for="lot in available_lots" :key="lot.id" :value="lot.id">
                                                {{ lot.lot_code }} — [SKU: {{ lot.sku_code }}] {{ lot.product_name }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors[`items.${index}.inventory_lot_id`]" class="text-error text-[10px] font-medium mt-0.5">Inválido</p>
                                    </td>
                                    
                                    <td class="py-3 font-mono text-xs text-right text-muted-foreground pr-2 pt-4">
                                        {{ getSelectedLotData(item.inventory_lot_id)?.quantity.toFixed(3) || '0.000' }}
                                    </td>
                                    
                                    <td class="py-2 pl-3">
                                        <input v-model.number="item.quantity" type="number" step="0.001" min="0.001" required class="admin-input text-xs font-mono text-right" :class="{ 'border-error': form.errors[`items.${index}.quantity`] || !validateLotStockLimit(item) }" />
                                        <p v-if="!validateLotStockLimit(item)" class="text-error text-[10px] font-bold mt-0.5 flex items-center gap-0.5 justify-end">
                                            <span>Excede disponible.</span>
                                        </p>
                                    </td>
                                    
                                    <td class="py-2 pl-3">
                                        <input v-model.number="item.unit_cost" type="number" step="0.01" min="0.00" required class="admin-input text-xs font-mono text-right" :class="{ 'border-error': form.errors[`items.${index}.unit_cost`] }" />
                                    </td>

                                    <td class="py-3 font-mono text-xs text-right font-bold text-destructive/80 pt-4">
                                        {{ ((Number(item.quantity) || 0) * (Number(item.unit_cost) || 0)).toFixed(2) }}
                                    </td>
                                    
                                    <td class="py-2 text-center">
                                        <button type="button" @click="removeItemRow(index)" class="p-1 text-destructive hover:bg-destructive/5 rounded mt-1 block mx-auto">
                                            <span class="material-symbols-rounded text-base block">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-border font-bold">
                                    <td colspan="4" class="text-right text-xs uppercase tracking-wider text-muted-foreground pt-3">Valor Consolidado del Descarte:</td>
                                    <td class="text-right font-mono text-sm text-destructive font-black pt-3">{{ totalEstimatedLoss.toFixed(2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <Link :href="route('admin.removals.index')" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                        Cancelar
                    </Link>
                    <button 
                        type="submit" 
                        :disabled="isFormInvalid || form.processing" 
                        class="admin-btn-primary text-xs font-bold uppercase tracking-wider transition-opacity disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <span>{{ form.processing ? 'Sancionando Ajuste...' : 'Asentar Descuento Físico' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>