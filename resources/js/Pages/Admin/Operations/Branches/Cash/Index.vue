<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    branch: {
        type: Object,
        required: true
    },
    currentCash: {
        type: Object,
        default: null
    },
    history: {
        type: Object,
        required: true
    }
});

const isCloseModalOpen = ref(false);

const openForm = useForm({
    opening_amount: 0.00
});

const closeForm = useForm({
    actual_amount: 0.00,
    closing_notes: ''
});

const submitOpen = () => {
    openForm.post(route('admin.operations.branches.cash.open', props.branch.id));
};

const submitClose = () => {
    closeForm.post(route('admin.operations.branches.cash.close', props.branch.id), {
        onSuccess: () => {
            isCloseModalOpen.value = false;
            closeForm.reset();
        }
    });
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Control de Caja Diaria — {{ branch.name }}
        </template>

        <div class="space-y-6">
            <!-- Estado Actual de la Caja del Día -->
            <div class="bg-card border border-border p-5 rounded-md shadow-flat">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-border pb-4">
                    <div>
                        <span class="text-xs font-bold text-muted-foreground uppercase">Estado Operativo de Hoy</span>
                        <div class="flex items-center gap-2 mt-1">
                            <span :class="currentCash ? 'badge-success text-sm' : 'badge-error text-sm'">
                                {{ currentCash ? 'Caja Abierta' : 'Caja Cerrada / No Aperturada' }}
                            </span>
                            <span v-if="currentCash" class="text-xs font-mono text-muted-foreground">
                                Aperturada: {{ new Date(currentCash.opened_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }} por {{ currentCash.opened_by?.first_name }} {{ currentCash.opened_by?.last_name }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.operations.branches.index')" class="px-3 py-2 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200">
                            ← Volver a Sucursales
                        </Link>

                        <!-- Botón de Cierre de Caja -->
                        <button 
                            v-if="currentCash" 
                            @click="isCloseModalOpen = true"
                            class="px-4 py-2 bg-destructive text-destructive-foreground text-xs font-bold uppercase rounded shadow-sm hover:bg-destructive/90"
                        >
                            Ejecutar Cierre y Arqueo
                        </button>
                    </div>
                </div>

                <!-- Formulario Rápido de Apertura si está cerrada -->
                <div v-if="!currentCash" class="pt-4 max-w-md">
                    <form @submit.prevent="submitOpen" class="flex items-end gap-3">
                        <div class="space-y-1.5 flex-1">
                            <label class="text-xs font-semibold text-muted-foreground uppercase">Fondo Inicial Base (Sencillo) *</label>
                            <input v-model.number="openForm.opening_amount" type="number" step="0.01" min="0" required class="admin-input font-mono" placeholder="0.00" />
                            <p v-if="openForm.errors.opening_amount" class="text-error text-xs font-medium">{{ openForm.errors.opening_amount }}</p>
                        </div>
                        <button type="submit" :disabled="openForm.processing" class="admin-btn-primary text-xs font-bold h-10 px-4">
                            {{ openForm.processing ? 'Aperturando...' : 'Aperturar Caja Hoy' }}
                        </button>
                    </form>
                </div>

                <!-- Métricas Rápidas si está abierta -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 font-mono">
                    <div class="p-3 bg-neutral-50 dark:bg-neutral-900/40 rounded border border-border">
                        <span class="text-[10px] text-muted-foreground uppercase block font-sans font-bold">Fondo Apertura</span>
                        <span class="text-base font-bold text-foreground">{{ Number(currentCash.opening_amount).toFixed(2) }}</span>
                    </div>
                    <div class="p-3 bg-neutral-50 dark:bg-neutral-900/40 rounded border border-border">
                        <span class="text-[10px] text-muted-foreground uppercase block font-sans font-bold">Cobros Registrados (Sistema)</span>
                        <span class="text-base font-bold text-primary">{{ Number(currentCash.expected_amount).toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Modal / Formulario de Cierre y Arqueo -->
            <div v-if="isCloseModalOpen" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                <div class="bg-card border border-border rounded-lg max-w-md w-full p-5 space-y-4 shadow-xl">
                    <div class="border-b border-border pb-2 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-foreground uppercase">Arqueo y Cierre de Caja</h3>
                        <button @click="isCloseModalOpen = false" class="text-muted-foreground hover:text-foreground">✕</button>
                    </div>

                    <form @submit.prevent="submitClose" class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-muted-foreground uppercase">Monto Físico Contado en Caja *</label>
                            <input v-model.number="closeForm.actual_amount" type="number" step="0.01" min="0" required class="admin-input font-mono" placeholder="0.00" />
                            <p v-if="closeForm.errors.actual_amount" class="text-error text-xs font-medium">{{ closeForm.errors.actual_amount }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-muted-foreground uppercase">Observaciones / Justificación de Descuadre</label>
                            <textarea v-model="closeForm.closing_notes" rows="3" class="admin-input" placeholder="Detalle si existe faltante o sobrante..."></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="isCloseModalOpen = false" class="px-3 py-2 bg-secondary text-secondary-foreground text-xs font-semibold rounded">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="closeForm.processing" class="px-4 py-2 bg-destructive text-destructive-foreground text-xs font-bold uppercase rounded">
                                {{ closeForm.processing ? 'Procesando...' : 'Confirmar Cierre' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Historial Contable -->
            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <div class="p-4 border-b border-border">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Historial Contable de Arqueos</h3>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Fecha</th>
                            <th class="admin-table-th">Aperturado Por</th>
                            <th class="admin-table-th">Cerrado Por</th>
                            <th class="admin-table-th font-mono">Fondo Inicial</th>
                            <th class="admin-table-th font-mono">Esperado</th>
                            <th class="admin-table-th font-mono">Físico Contado</th>
                            <th class="admin-table-th font-mono">Diferencia</th>
                            <th class="admin-table-th text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="history.data.length === 0">
                            <td colspan="8" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No hay registros históricos de caja para esta sucursal.
                            </td>
                        </tr>
                        <tr v-for="item in history.data" :key="item.id" class="admin-table-tr">
                            <td class="admin-table-td font-mono font-bold">{{ item.date }}</td>
                            <td class="admin-table-td text-xs">{{ item.opened_by?.first_name }} {{ item.opened_by?.last_name }}</td>
                            <td class="admin-table-td text-xs">{{ item.closed_by ? `${item.closed_by.first_name} ${item.closed_by.last_name}` : '—' }}</td>
                            <td class="admin-table-td font-mono text-xs">{{ Number(item.opening_amount).toFixed(2) }}</td>
                            <td class="admin-table-td font-mono text-xs">{{ Number(item.expected_amount).toFixed(2) }}</td>
                            <td class="admin-table-td font-mono text-xs">{{ item.actual_amount !== null ? Number(item.actual_amount).toFixed(2) : '—' }}</td>
                            <td class="admin-table-td font-mono text-xs font-bold">
                                <span v-if="item.difference === null">—</span>
                                <span v-else-if="Number(item.difference) === 0" class="text-success">0.00</span>
                                <span v-else-if="Number(item.difference) > 0" class="text-info">+{{ Number(item.difference).toFixed(2) }}</span>
                                <span v-else class="text-error">{{ Number(item.difference).toFixed(2) }}</span>
                            </td>
                            <td class="admin-table-td text-center">
                                <span :class="item.status === 'open' ? 'badge-success' : 'badge-info'">
                                    {{ item.status === 'open' ? 'Abierta' : 'Cerrada' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>