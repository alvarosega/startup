<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    sku_id: {
        type: String,
        required: true
    },
    lots: {
        type: Array,
        required: true
    }
});

const activeLotForSafety = ref(null);
const showSafetyModal = ref(false);

// Formulario 1: Transferencia de porción al fondo de seguridad ordinario
const safetyForm = useForm({
    inventory_lot_id: '',
    quantity: 0.001
});

// Formulario 2: Bloqueo/Aislamiento atómico de lote completo (Inyección booleana pura)
const quarantineForm = useForm({
    inventory_lot_id: '',
    is_quarantine: false
});

/**
 * Inicializa y despliega la ventana flotante para la transferencia de contingencia.
 */
const openSafetyModal = (lot) => {
    activeLotForSafety.value = lot;
    safetyForm.inventory_lot_id = lot.id;
    safetyForm.quantity = 0.001;
    showSafetyModal.value = true;
};

/**
 * Despacha el payload numérico hacia el backend y limpia estados locales.
 */
const submitSafetyTransfer = () => {
    safetyForm.post(route('admin.inventory.transfer-safety'), {
        preserveScroll: true,
        onSuccess: () => {
            showSafetyModal.value = false;
            activeLotForSafety.value = null;
            safetyForm.reset();
        }
    });
};

/**
 * Conmuta el flag de aislamiento del lote completo de forma directa.
 */
const toggleQuarantineIsolation = (lot) => {
    quarantineForm.inventory_lot_id = lot.id;
    quarantineForm.is_quarantine = !lot.is_quarantine;
    quarantineForm.post(route('admin.inventory.isolate-quarantine'), {
        preserveScroll: true
    });
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Estructura de Capas FIFO Activas
        </template>

        <div class="space-y-4">
            <div class="flex items-center justify-between bg-card p-4 border border-border rounded-md shadow-flat">
                <div class="text-xs text-muted-foreground font-medium">
                    Análisis cronológico de lotes no expirados asignados al SKU maestro.
                </div>
                <Link :href="route('admin.inventory.index')" class="px-3 py-1.5 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200">
                    Volver a Saldos
                </Link>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Código Lote</th>
                            <th class="admin-table-th">Fecha Entrada</th>
                            <th class="admin-table-th">Vencimiento (FEFO)</th>
                            <th class="admin-table-th text-right">Costo Unitario</th>
                            <th class="admin-table-th text-right">Cantidad Inicial</th>
                            <th class="admin-table-th text-right">Remanente Actual</th>
                            <th class="admin-table-th text-right">En Resguardo</th>
                            <th class="admin-table-th text-right">Comprometido</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Acciones de Capa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="lots.length === 0">
                            <td colspan="10" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se localizan capas de lote físicas vigentes o remanentes para este SKU.
                            </td>
                        </tr>
                        <tr v-for="lot in lots" :key="lot.id" class="admin-table-tr" :class="{ 'bg-error/5 text-error font-medium': lot.is_quarantine }">
                            <td class="admin-table-td font-mono text-xs font-bold text-foreground">
                                {{ lot.lot_code }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-muted-foreground">
                                {{ lot.created_at }}
                            </td>
                            <td class="admin-table-td font-mono text-xs" :class="lot.expiration_date ? 'text-foreground' : 'text-muted-foreground/40 italic'">
                                {{ lot.expiration_date || 'Sin Vencimiento' }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right font-bold">
                                {{ lot.cost_price.toFixed(2) }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right text-muted-foreground">
                                {{ lot.initial_quantity.toFixed(3) }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right font-bold text-foreground">
                                {{ lot.quantity.toFixed(3) }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right text-info font-bold">
                                {{ lot.safety_quantity.toFixed(3) }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right text-warning font-bold">
                                {{ lot.reserved_quantity.toFixed(3) }}
                            </td>
                            <td class="admin-table-td text-center">
                                <span :class="lot.is_quarantine ? 'badge-error' : 'badge-success'">
                                    {{ lot.is_quarantine ? 'CUARENTENA' : 'DISPONIBLE' }}
                                </span>
                            </td>
                            <td class="admin-table-td text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button 
                                        type="button" 
                                        :disabled="lot.is_quarantine || lot.quantity <= 0"
                                        @click="openSafetyModal(lot)" 
                                        class="px-2 py-0.5 bg-card text-foreground text-xs font-medium rounded border border-border hover:bg-neutral-100 disabled:opacity-30 disabled:cursor-not-allowed"
                                    >
                                        Resguardar
                                    </button>
                                    <button 
                                        type="button" 
                                        :disabled="quarantineForm.processing"
                                        @click="toggleQuarantineIsolation(lot)" 
                                        class="px-2 py-0.5 text-xs font-semibold rounded border transition-colors"
                                        :class="lot.is_quarantine ? 'bg-success/5 text-success border-success/20 hover:bg-success/10' : 'bg-destructive/5 text-destructive border-destructive/20 hover:bg-destructive/10'"
                                    >
                                        {{ lot.is_quarantine ? 'Liberar' : 'Aislar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showSafetyModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 animate-in fade-in duration-75">
            <div class="fixed inset-0 bg-neutral-950/50 backdrop-blur-sm" @click="showSafetyModal = false"></div>
            
            <div class="relative w-full max-w-sm bg-card border border-border rounded-md shadow-flat p-5 space-y-4 z-10">
                <div class="flex items-center justify-between border-b border-border pb-2">
                    <h2 class="text-xs font-bold text-foreground uppercase tracking-wide">
                        Resguardar Stock Ordinario (Lote: {{ activeLotForSafety?.lot_code }})
                    </h2>
                </div>

                <form @submit.prevent="submitSafetyTransfer" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Cantidad a Bloquear *</label>
                        <input 
                            v-model.number="safetyForm.quantity" 
                            type="number" 
                            step="0.001" 
                            :max="activeLotForSafety?.quantity" 
                            required 
                            class="admin-input font-mono" 
                            :class="{ 'border-error': safetyForm.errors.quantity }"
                        />
                        <p class="text-[10px] text-muted-foreground font-mono">Remanente máximo transferible: {{ activeLotForSafety?.quantity.toFixed(3) }}</p>
                        <p v-if="safetyForm.errors.quantity" class="text-error text-xs font-medium mt-1">{{ safetyForm.errors.quantity }}</p>
                    </div>

                    <div class="pt-2 border-t border-border flex items-center justify-end gap-2.5">
                        <button type="button" @click="showSafetyModal = false" class="px-3 py-1.5 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="safetyForm.processing" class="admin-btn-primary text-xs font-bold uppercase tracking-wider">
                            <span>{{ safetyForm.processing ? 'Transfiriendo...' : 'Confirmar Asignación' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>