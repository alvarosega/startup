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

// Estructura adaptada al StorePurchaseRequest y su factoría de DTO
const form = useForm({
    branch_id: '',
    provider_id: '',
    document_number: '',
    purchase_date: new Date().toISOString().slice(0, 10), // Fecha actual por defecto en zona horaria local
    payment_type: 'CASH',
    status: 'PENDING',
    items: []
});

/**
 * Añade una línea reactiva limpia al arreglo dinámico de ítems.
 */
const addItemRow = () => {
    form.items.push({
        sku_id: '',
        quantity: 1.000,
        cost_price: 0.00,
        lot_code: '',
        expiration_date: ''
    });
};

/**
 * Remueve una línea de la matriz por su índice.
 */
const removeItemRow = (index) => {
    form.items.splice(index, 1);
};

/**
 * Procesa la sumatoria agregada de los costos en el cliente para control operativo.
 */
const totalOrderCost = computed(() => {
    return form.items.reduce((sum, item) => {
        const qty = parseFloat(item.quantity) || 0;
        const price = parseFloat(item.cost_price) || 0;
        return sum + (qty * price);
    }, 0);
});

/**
 * Evalúa las condiciones mutantes antes de despachar el payload.
 */
const submit = () => {
    // Si el estado es diferido (PENDING), limpiamos campos físicos para no enviar basura topológica
    if (form.status === 'PENDING') {
        form.items.forEach(item => {
            item.lot_code = null;
            item.expiration_date = null;
        });
    }
    form.post(route('admin.purchases.store'));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Asiento de Abastecimiento Inicial
        </template>

        <form @submit.prevent="submit" class="space-y-6 max-w-5xl">
            <div class="bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
                <div class="border-b border-border pb-2">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Datos de Origen y Control de Facturación</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Sucursal Destino *</label>
                        <select v-model="form.branch_id" required class="admin-input" :class="{ 'border-error': form.errors.branch_id }">
                            <option value="" disabled>Seleccione almacén...</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <p v-if="form.errors.branch_id" class="text-error text-xs font-medium mt-1">{{ form.errors.branch_id }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Socio Comercial (Proveedor) *</label>
                        <select v-model="form.provider_id" required class="admin-input" :class="{ 'border-error': form.errors.provider_id }">
                            <option value="" disabled>Seleccione proveedor...</option>
                            <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.company_name }}</option>
                        </select>
                        <p v-if="form.errors.provider_id" class="text-error text-xs font-medium mt-1">{{ form.errors.provider_id }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Número de Documento *</label>
                        <input v-model="form.document_number" type="text" required class="admin-input font-mono uppercase" :class="{ 'border-error': form.errors.document_number }" placeholder="FACT-8940" />
                        <p v-if="form.errors.document_number" class="text-error text-xs font-medium mt-1">{{ form.errors.document_number }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Fecha del Asiento *</label>
                        <input v-model="form.purchase_date" type="date" required class="admin-input" :class="{ 'border-error': form.errors.purchase_date }" />
                        <p v-if="form.errors.purchase_date" class="text-error text-xs font-medium mt-1">{{ form.errors.purchase_date }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Término Comercial de Pago *</label>
                        <select v-model="form.payment_type" required class="admin-input">
                            <option value="CASH">CASH (Contado)</option>
                            <option value="CREDIT">CREDIT (Crédito / Cuenta Corriente)</option>
                        </select>
                        <p v-if="form.errors.payment_type" class="text-error text-xs font-medium mt-1">{{ form.errors.payment_type }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Estado de Recepción Física *</label>
                        <select v-model="form.status" required class="admin-input">
                            <option value="PENDING">PENDING (Mercadería fuera de almacén / Flujo Documental)</option>
                            <option value="COMPLETED">COMPLETED (Consolidación Directa / Inyectar Capas FIFO)</option>
                        </select>
                        <p v-if="form.errors.status" class="text-error text-xs font-medium mt-1">{{ form.errors.status }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
                <div class="border-b border-border pb-2 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Líneas de Detalle de Adquisición</h3>
                    <button type="button" @click="addItemRow" class="px-2.5 py-1 bg-secondary text-secondary-foreground border border-border text-xs font-bold uppercase tracking-wider hover:bg-neutral-200 rounded">
                        + Añadir Fila
                    </button>
                </div>

                <div v-if="form.items.length === 0" class="text-xs text-muted-foreground text-center py-6 border border-dashed border-border rounded">
                    Ninguna línea inyectada. Es obligatorio registrar al menos un ítem para validar el asiento de compra.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border text-[10px] font-bold text-muted-foreground uppercase tracking-wider">
                                <th class="pb-2">Variante Comercial (SKU) *</th>
                                <th class="pb-2 w-28 text-center">Cantidad *</th>
                                <th class="pb-2 w-28 text-right">Costo Unitario *</th>
                                <th v-if="form.status === 'COMPLETED'" class="pb-2 w-32 pl-2">Código Lote *</th>
                                <th v-if="form.status === 'COMPLETED'" class="pb-2 w-36 pl-2">Vencimiento *</th>
                                <th class="pb-2 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in form.items" :key="index" class="border-b border-border/40 last:border-0">
                                <td class="py-2 pr-2">
                                    <select v-model="item.sku_id" required class="admin-input text-xs" :class="{ 'border-error': form.errors[`items.${index}.sku_id`] }">
                                        <option value="" disabled>Seleccione variante...</option>
                                        <option v-for="s in skus" :key="s.id" :value="s.id">{{ s.display }}</option>
                                    </select>
                                </td>
                                <td class="py-2 pr-2">
                                    <input v-model.number="item.quantity" type="number" step="0.001" min="0.001" required class="admin-input text-xs font-mono text-center" :class="{ 'border-error': form.errors[`items.${index}.quantity`] }" />
                                </td>
                                <td class="py-2 pr-2">
                                    <input v-model.number="item.cost_price" type="number" step="0.01" min="0" required class="admin-input text-xs font-mono text-right" :class="{ 'border-error': form.errors[`items.${index}.cost_price`] }" />
                                </td>
                                <td v-if="form.status === 'COMPLETED'" class="py-2 pr-2 pl-2">
                                    <input v-model="item.lot_code" type="text" required class="admin-input text-xs font-mono uppercase" :class="{ 'border-error': form.errors[`items.${index}.lot_code`] }" placeholder="LOTE-XYZ" />
                                </td>
                                <td v-if="form.status === 'COMPLETED'" class="py-2 pr-2 pl-2">
                                    <input v-model="item.expiration_date" type="date" required class="admin-input text-xs" :class="{ 'border-error': form.errors[`items.${index}.expiration_date`] }" />
                                </td>
                                <td class="py-2 text-center">
                                    <button type="button" @click="removeItemRow(index)" class="p-1 text-destructive hover:bg-destructive/5 rounded">
                                        <span class="material-symbols-rounded text-base block">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="form.items.length > 0" class="flex justify-end pt-3 border-t border-border/60">
                    <div class="text-right text-xs space-y-1 font-mono">
                        <span class="text-muted-foreground uppercase font-sans font-bold text-[10px] tracking-wider block">Valor de Carga Total Evaluado</span>
                        <span class="text-lg font-black text-foreground">{{ totalOrderCost.toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <Link :href="route('admin.purchases.index')" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                    Cancelar
                </Link>
                <button type="submit" :disabled="form.processing" class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider">
                    <span class="material-symbols-rounded text-base">layers</span>
                    <span>{{ form.processing ? 'Registrando Asiento...' : 'Procesar Asiento' }}</span>
                </button>
            </div>
        </form>
    </AdminLayout>
</template>