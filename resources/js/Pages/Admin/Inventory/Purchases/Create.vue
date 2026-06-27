<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    branches: {
        type: Array,
        required: true
    },
    providers: {
        type: Array,
        required: true
    },
    skus: {
        type: Array,
        required: true
    }
});

// Inicialización limpia bajo los parámetros estrictos de StorePurchaseRequest
const form = useForm({
    branch_id: '',
    provider_id: '',
    document_number: '',
    purchase_date: new Date().toISOString().split('T')[0],
    payment_type: 'CASH',
    status: 'PENDING',
    items: []
});

/**
 * Inserta una línea de variante comercial vacía al repetidor dinámico.
 */
const addRow = () => {
    form.items.push({
        sku_id: '',
        quantity: 0.001,
        cost_price: 0.00,
        lot_code: '',
        expiration_date: ''
    });
};

/**
 * Ppurga una fila específica de la matriz reactiva local.
 */
const removeRow = (index) => {
    form.items.splice(index, 1);
};

/**
 * Evalúa el subtotal en caliente por fila.
 */
const getItemSubtotal = (item) => {
    return (Number(item.quantity) || 0) * (Number(item.cost_price) || 0);
};

/**
 * Sumatoria agregada de control para verificación del operador de almacén.
 */
const grandTotal = computed(() => {
    return form.items.reduce((sum, item) => sum + getItemSubtotal(item), 0);
});

/**
 * Modifica condicionalmente el estado del payload a PENDING antes del despacho.
 */
const dispatchPending = () => {
    form.status = 'PENDING';
    form.post(route('admin.purchases.store'));
};

/**
 * Modifica condicionalmente el estado del payload a COMPLETED antes del despacho.
 */
const dispatchCompleted = () => {
    form.status = 'COMPLETED';
    form.post(route('admin.purchases.store'));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Asiento de Abastecimiento de Mercadería
        </template>

        <div class="bg-card border border-border rounded-md shadow-flat p-5 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Sucursal Destino *</label>
                    <select v-model="form.branch_id" :disabled="form.items.length > 0" required class="admin-input" :class="{ 'border-error': form.errors.branch_id }">
                        <option value="" disabled>Seleccione nodo...</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                    <p v-if="form.errors.branch_id" class="text-error text-xs font-medium mt-1">{{ form.errors.branch_id }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Socio Comercial *</label>
                    <select v-model="form.provider_id" required class="admin-input" :class="{ 'border-error': form.errors.provider_id }">
                        <option value="" disabled>Seleccione proveedor...</option>
                        <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.company_name }}</option>
                    </select>
                    <p v-if="form.errors.provider_id" class="text-error text-xs font-medium mt-1">{{ form.errors.provider_id }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Nro. Documento Factura *</label>
                    <input v-model="form.document_number" type="text" required maxlength="32" class="admin-input font-mono" :class="{ 'border-error': form.errors.document_number }" placeholder="FAC45091" />
                    <p v-if="form.errors.document_number" class="text-error text-xs font-medium mt-1">{{ form.errors.document_number }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Fecha de Compra *</label>
                    <input v-model="form.purchase_date" type="date" required class="admin-input font-mono" :class="{ 'border-error': form.errors.purchase_date }" />
                    <p v-if="form.errors.purchase_date" class="text-error text-xs font-medium mt-1">{{ form.errors.purchase_date }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Término de Pago *</label>
                    <select v-model="form.payment_type" required class="admin-input">
                        <option value="CASH">CASH (Contado)</option>
                        <option value="CREDIT">CREDIT (Crédito)</option>
                    </select>
                </div>
            </div>

            <div class="space-y-3">
                <div class="border-b border-border pb-1.5 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Desglose de Artículos Recibidos</h3>
                    <button type="button" :disabled="!form.branch_id" @click="addRow" class="px-2 py-1 bg-secondary text-secondary-foreground border border-border text-xs font-bold uppercase tracking-wider hover:bg-neutral-200 rounded disabled:opacity-40 disabled:cursor-not-allowed">
                        + Agregar Línea
                    </button>
                </div>

                <p v-if="!form.branch_id" class="text-xs text-warning font-medium italic">
                    * Debe fijar la sucursal de destino en la cabecera antes de habilitar la inyección de artículos comerciales.
                </p>

                <div v-if="form.items.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border text-[10px] font-bold text-muted-foreground uppercase tracking-wider">
                                <th class="pb-2">Variante Comercial (SKU) *</th>
                                <th class="pb-2 w-24 text-right">Cantidad *</th>
                                <th class="pb-2 w-28 text-right">Costo Unitario *</th>
                                <th v-if="form.status === 'COMPLETED'" class="pb-2 w-32 pl-2">Código Lote *</th>
                                <th v-if="form.status === 'COMPLETED'" class="pb-2 w-32 pl-2">Expiración *</th>
                                <th class="pb-2 w-28 text-right">Subtotal</th>
                                <th class="pb-2 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in form.items" :key="index" class="border-b border-border/40 last:border-0 align-top">
                                <td class="py-2 pr-2">
                                    <select v-model="item.sku_id" required class="admin-input text-xs" :class="{ 'border-error': form.errors[`items.${index}.sku_id`] }">
                                        <option value="" disabled>Seleccione SKU...</option>
                                        <option v-for="s in skus" :key="s.id" :value="s.id">{{ s.display }}</option>
                                    </select>
                                    <p v-if="form.errors[`items.${index}.sku_id`]" class="text-error text-[10px] font-medium mt-0.5">Requerido</p>
                                </td>
                                <td class="py-2 pr-2">
                                    <input v-model.number="item.quantity" type="number" step="0.001" min="0.001" required class="admin-input text-xs font-mono text-right" :class="{ 'border-error': form.errors[`items.${index}.quantity`] }" />
                                    <p v-if="form.errors[`items.${index}.quantity`]" class="text-error text-[10px] font-medium mt-0.5">Mín 0.001</p>
                                </td>
                                <td class="py-2 pr-2">
                                    <input v-model.number="item.cost_price" type="number" step="0.01" min="0.00" required class="admin-input text-xs font-mono text-right" :class="{ 'border-error': form.errors[`items.${index}.cost_price`] }" />
                                    <p v-if="form.errors[`items.${index}.cost_price`]" class="text-error text-[10px] font-medium mt-0.5">Inválido</p>
                                </td>
                                
                                <td v-if="form.status === 'COMPLETED'" class="py-2 pr-2 pl-2">
                                    <input v-model="item.lot_code" type="text" maxlength="32" required class="admin-input text-xs font-mono" :class="{ 'border-error': form.errors[`items.${index}.lot_code`] }" placeholder="LOTE-MANUAL" />
                                    <p v-if="form.errors[`items.${index}.lot_code`]" class="text-error text-[10px] font-medium mt-0.5 max-w-[120px] truncate">{{ form.errors[`items.${index}.lot_code`] }}</p>
                                </td>
                                <td v-if="form.status === 'COMPLETED'" class="py-2 pr-2 pl-2">
                                    <input v-model="item.expiration_date" type="date" required class="admin-input text-xs font-mono" :class="{ 'border-error': form.errors[`items.${index}.expiration_date`] }" />
                                    <p v-if="form.errors[`items.${index}.expiration_date`]" class="text-error text-[10px] font-medium mt-0.5">Requerido</p>
                                </td>

                                <td class="py-2 font-mono text-xs text-right font-bold text-foreground/70 pt-3">
                                    {{ getItemSubtotal(item).toFixed(2) }}
                                </td>
                                <td class="py-2 text-center">
                                    <button type="button" @click="removeRow(index)" class="p-1 text-destructive hover:bg-destructive/5 rounded mt-1 block mx-auto">
                                        <span class="material-symbols-rounded text-base block">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-border font-bold">
                                <td :colspan="form.status === 'COMPLETED' ? 5 : 3" class="text-right text-xs uppercase tracking-wider text-muted-foreground pt-3">Valor Total de Carga:</td>
                                <td class="text-right font-mono text-sm text-foreground font-black pt-3">{{ grandTotal.toFixed(2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="pt-4 border-t border-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-neutral-50 p-3 rounded">
                <div class="text-xs text-muted-foreground font-medium">
                    * Al consolidar directo, el sistema inyectará stock en tiempo real FEFO en base a los códigos de lote asignados.
                </div>
                <div class="flex items-center justify-end gap-3 shrink-0">
                    <Link :href="route('admin.purchases.index')" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                        Cancelar
                    </Link>
                    <button type="button" :disabled="form.processing || form.items.length === 0" @click="form.status = 'PENDING'; dispatchPending()" class="px-3 py-2 bg-neutral-200 text-neutral-800 text-xs font-bold uppercase tracking-wider rounded hover:bg-neutral-300 transition-colors disabled:opacity-40">
                        Guardar Documental (PENDING)
                    </button>
                    <button type="button" :disabled="form.processing || form.items.length === 0" @click="form.status = 'COMPLETED'; dispatchCompleted()" class="admin-btn-primary text-xs font-bold uppercase tracking-wider">
                        Consolidar e Ingresar (COMPLETED)
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>