<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BranchSubNavTabs from '@/Components/Admin/Operations/BranchSubNavTabs.vue';
import Pagination from '@/Components/Admin/Pagination.vue';

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

// Cálculo reactivo de la discrepancia contable
const liveDifference = computed(() => {
    const actual = Number(closeForm.actual_amount) || 0;
    const expected = Number(props.currentCash?.expected_amount) || 0;
    return Number((actual - expected).toFixed(2));
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
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.operations.branches.index')"
                    class="p-2 rounded-xl text-muted-foreground hover:text-foreground hover:bg-foreground/[0.05] transition-all inline-flex items-center"
                    title="Volver a Sucursales"
                >
                    <span class="material-symbols-rounded text-[20px]">arrow_back</span>
                </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase">
                        Control de Caja Diaria — {{ branch.name }}
                    </h1>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Apertura de turno, conciliación de cobros en efectivo y arqueos de cierre
                    </p>
                </div>
            </div>
        </template>

        <!-- Barra Contextual de la Sucursal -->
        <BranchSubNavTabs :branch="branch" />

        <div class="space-y-6">
            <!-- Estado Actual del Turno de Hoy -->
            <div class="glass-card p-5 md:p-6 rounded-2xl border border-white/60 dark:border-white/10 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-border/50 pb-4">
                    <div>
                        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Estado del Turno de Hoy</span>
                        <div class="flex items-center gap-2 mt-1">
                            <span :class="currentCash ? 'badge-success text-xs py-1 px-3' : 'badge-error text-xs py-1 px-3'">
                                {{ currentCash ? 'Caja Abierta' : 'Caja Cerrada / No Aperturada' }}
                            </span>
                            <span v-if="currentCash" class="text-xs font-mono text-muted-foreground">
                                Apertura: {{ new Date(currentCash.opened_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }} por {{ currentCash.opened_by?.first_name }} {{ currentCash.opened_by?.last_name }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Botón de Cierre de Caja -->
                        <button 
                            v-if="currentCash" 
                            type="button"
                            @click="isCloseModalOpen = true"
                            class="px-4 py-2 bg-destructive text-destructive-foreground text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm hover:brightness-110 active:scale-95 transition-all inline-flex items-center gap-1.5 cursor-pointer"
                        >
                            <span class="material-symbols-rounded text-[18px]">lock_clock</span>
                            <span>Ejecutar Cierre y Arqueo</span>
                        </button>
                    </div>
                </div>

                <!-- Formulario de Apertura si está cerrada -->
                <div v-if="!currentCash" class="pt-5 max-w-lg">
                    <form @submit.prevent="submitOpen" class="flex flex-col sm:flex-row sm:items-end gap-3">
                        <div class="space-y-1.5 flex-1">
                            <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Fondo Inicial de Caja (Sencillo) *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-mono text-xs">Bs</span>
                                <input 
                                    v-model.number="openForm.opening_amount" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="admin-input font-mono pl-9 text-xs" 
                                    placeholder="0.00" 
                                />
                            </div>
                            <p v-if="openForm.errors.opening_amount" class="text-error text-[11px] font-medium">{{ openForm.errors.opening_amount }}</p>
                        </div>
                        <button 
                            type="submit" 
                            :disabled="openForm.processing" 
                            class="admin-btn-primary text-xs font-bold uppercase tracking-wider py-2.5 px-4 rounded-xl shadow-sm h-10 shrink-0"
                        >
                            <span class="material-symbols-rounded text-[18px]">lock_open</span>
                            <span>{{ openForm.processing ? 'Aperturando...' : 'Aperturar Caja Hoy' }}</span>
                        </button>
                    </form>
                </div>

                <!-- Métricas Rápidas si está abierta -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-5 font-mono">
                    <div class="p-4 bg-card/60 backdrop-blur-md rounded-xl border border-border/70 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-muted-foreground uppercase block font-sans font-bold tracking-wider">Fondo de Apertura</span>
                            <span class="text-xl font-black text-foreground mt-0.5 block">Bs {{ Number(currentCash.opening_amount).toFixed(2) }}</span>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-sans font-bold">
                            <span class="material-symbols-rounded text-[20px]">account_balance_wallet</span>
                        </div>
                    </div>

                    <div class="p-4 bg-card/60 backdrop-blur-md rounded-xl border border-border/70 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-muted-foreground uppercase block font-sans font-bold tracking-wider">Cobros Registrados (Sistema)</span>
                            <span class="text-xl font-black text-primary mt-0.5 block">Bs {{ Number(currentCash.expected_amount).toFixed(2) }}</span>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-sans font-bold">
                            <span class="material-symbols-rounded text-[20px]">receipt_long</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Frosted Glass de Cierre y Arqueo -->
            <div v-if="isCloseModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                <div class="glass-card max-w-md w-full p-6 rounded-2xl border border-white/60 dark:border-white/10 shadow-2xl space-y-4">
                    <div class="border-b border-border/50 pb-3 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-destructive text-[22px]">lock_clock</span>
                            <h3 class="text-sm font-bold text-foreground uppercase tracking-wider">Arqueo y Cierre de Turno</h3>
                        </div>
                        <button 
                            type="button" 
                            @click="isCloseModalOpen = false" 
                            class="p-1 rounded-lg text-muted-foreground hover:text-foreground transition-colors"
                        >
                            ✕
                        </button>
                    </div>

                    <form @submit.prevent="submitClose" class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Monto Físico Contado en Caja (Bs) *</label>
                            <input 
                                v-model.number="closeForm.actual_amount" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                required 
                                class="admin-input font-mono text-xs" 
                                placeholder="0.00" 
                            />
                            <p v-if="closeForm.errors.actual_amount" class="text-error text-[11px] font-medium">{{ closeForm.errors.actual_amount }}</p>
                        </div>

                        <!-- Panel de Discrepancia en Tiempo Real -->
                        <div class="p-3.5 rounded-xl bg-card/70 border border-border/80 flex items-center justify-between text-xs">
                            <span class="text-muted-foreground font-semibold">Balance de Caja:</span>
                            <div class="font-mono font-bold flex items-center gap-1.5">
                                <span v-if="liveDifference === 0" class="badge-success">Cuadrada (0.00 Bs)</span>
                                <span v-else-if="liveDifference > 0" class="badge-info">Sobrante (+{{ liveDifference.toFixed(2) }} Bs)</span>
                                <span v-else class="badge-error">Faltante ({{ liveDifference.toFixed(2) }} Bs)</span>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Observaciones / Justificación de Descuadre</label>
                            <textarea 
                                v-model="closeForm.closing_notes" 
                                rows="3" 
                                class="admin-input text-xs" 
                                placeholder="Detalles de arqueo, justificación de sobrantes o faltantes..."
                            ></textarea>
                        </div>

                        <div class="flex justify-end gap-2.5 pt-2 border-t border-border/40">
                            <button 
                                type="button" 
                                @click="isCloseModalOpen = false" 
                                class="px-3.5 py-2 rounded-xl border border-border/80 text-xs font-semibold text-muted-foreground hover:text-foreground hover:bg-foreground/[0.04] transition-all"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                :disabled="closeForm.processing" 
                                class="px-4 py-2 bg-destructive text-destructive-foreground text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm hover:brightness-110 active:scale-95 transition-all disabled:opacity-50"
                            >
                                {{ closeForm.processing ? 'Procesando...' : 'Confirmar Cierre' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Historial Contable de Arqueos -->
            <div class="glass-panel rounded-2xl border border-white/60 dark:border-white/10 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-border/50 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-rounded text-primary text-[18px]">history</span>
                        <span>Historial Contable de Turnos</span>
                    </h3>
                    <span class="text-[11px] text-muted-foreground font-mono">
                        {{ history.total || history.data?.length || 0 }} registros archivados
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th class="admin-table-th">Fecha</th>
                                <th class="admin-table-th">Apertura Por</th>
                                <th class="admin-table-th">Cierre Por</th>
                                <th class="admin-table-th font-mono">Fondo Inicial</th>
                                <th class="admin-table-th font-mono">Esperado</th>
                                <th class="admin-table-th font-mono">Físico Contado</th>
                                <th class="admin-table-th font-mono">Diferencia</th>
                                <th class="admin-table-th text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="history.data.length === 0">
                                <td colspan="8" class="admin-table-td text-center text-muted-foreground py-8 font-normal">
                                    No hay registros históricos de caja para esta sucursal.
                                </td>
                            </tr>
                            <tr v-for="item in history.data" :key="item.id" class="admin-table-tr">
                                <td class="admin-table-td font-mono font-bold text-xs">{{ item.date }}</td>
                                <td class="admin-table-td text-xs">{{ item.opened_by?.first_name }} {{ item.opened_by?.last_name }}</td>
                                <td class="admin-table-td text-xs">{{ item.closed_by ? `${item.closed_by.first_name} ${item.closed_by.last_name}` : '—' }}</td>
                                <td class="admin-table-td font-mono text-xs">Bs {{ Number(item.opening_amount).toFixed(2) }}</td>
                                <td class="admin-table-td font-mono text-xs">Bs {{ Number(item.expected_amount).toFixed(2) }}</td>
                                <td class="admin-table-td font-mono text-xs">{{ item.actual_amount !== null ? `Bs ${Number(item.actual_amount).toFixed(2)}` : '—' }}</td>
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

                <div v-if="history.links && history.links.length > 3" class="p-3 border-t border-border/50">
                    <Pagination :links="history.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
