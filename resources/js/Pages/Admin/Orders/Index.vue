<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
// Asegúrate de tener tu layout de Admin
import AdminLayout from '@/Layouts/AdminLayout.vue'; 
import { 
    Search, Eye, CheckCircle, XCircle, Clock, Truck, 
    Package, Store, AlertTriangle, ShieldAlert, FileImage
} from 'lucide-vue-next';

const props = defineProps({
    orders: Object
});

// --- ESTADO DEL MODAL DE REVISIÓN ---
const isModalOpen = ref(false);
const selectedOrder = ref(null);
const reviewType = ref(null); // 'advance' o 'balance'
const actionType = ref('approve'); // 'approve' o 'reject'

// Formularios independientes para no mezclar datos en la petición
const approveForm = useForm({
    type: '',
    bank_reference: ''
});

const rejectForm = useForm({
    type: '',
    rejection_reason: ''
});

// --- FUNCIONES DEL MODAL ---
const openReviewModal = (order, type) => {
    selectedOrder.value = order;
    reviewType.value = type;
    approveForm.type = type;
    rejectForm.type = type;
    approveForm.bank_reference = '';
    rejectForm.rejection_reason = '';
    actionType.value = 'approve';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedOrder.value = null;
        reviewType.value = null;
        approveForm.reset();
        rejectForm.reset();
        approveForm.clearErrors();
        rejectForm.clearErrors();
    }, 200);
};

const currentProofUrl = computed(() => {
    if (!selectedOrder.value) return null;
    const path = reviewType.value === 'advance' 
        ? selectedOrder.value.advance_proof 
        : selectedOrder.value.balance_proof;
    return path ? `/storage/${path}` : null;
});

const amountUnderReview = computed(() => {
    if (!selectedOrder.value) return 0;
    return reviewType.value === 'advance' 
        ? selectedOrder.value.advance_amount 
        : selectedOrder.value.balance_amount;
});

// --- EJECUCIÓN DE ACTIONS ---
const submitApprove = () => {
    approveForm.post(route('admin.orders.approve-payment', selectedOrder.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    });
};

const submitReject = () => {
    rejectForm.post(route('admin.orders.reject-payment', selectedOrder.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    });
};

const dispatchOrder = (id) => {
    if (confirm("¿Confirmas que el paquete está listo para salir o ser entregado?")) {
        router.post(route('admin.orders.dispatch', id), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout>
        <div class="p-6">
            <div class="flex justify-between items-end mb-6">
                <div>
                    <h1 class="text-2xl font-black text-foreground uppercase tracking-tight">Control de Órdenes</h1>
                    <p class="text-sm text-muted-foreground mt-1">Gestión financiera y logística centralizada.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="16"/>
                        <input type="text" placeholder="Buscar #ORD..." class="pl-9 pr-4 py-2 bg-background border border-border rounded-lg text-sm focus:outline-none focus:border-primary w-64">
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/50 border-b border-border text-xs uppercase font-bold text-muted-foreground">
                        <tr>
                            <th class="px-6 py-4">Detalles de Orden</th>
                            <th class="px-6 py-4">Cliente & Logística</th>
                            <th class="px-6 py-4">Semáforo Financiero</th>
                            <th class="px-6 py-4 text-right">Acción Requerida</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="order in orders.data" :key="order.id" class="hover:bg-muted/20 transition-colors">
                            
                            <td class="px-6 py-4 align-top">
                                <div class="font-mono font-black text-primary">{{ order.code }}</div>
                                <div class="text-xs text-muted-foreground mt-1">{{ new Date(order.created_at).toLocaleString() }}</div>
                                <div class="mt-2 font-bold text-sm">
                                    Bs {{ parseFloat(order.total_amount).toFixed(2) }}
                                    <span v-if="order.payment_type === 'partial'" class="ml-2 text-[10px] bg-warning/10 text-warning px-2 py-0.5 rounded uppercase">Fraccionado</span>
                                    <span v-else class="ml-2 text-[10px] bg-success/10 text-success px-2 py-0.5 rounded uppercase">Total</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 align-top">
                                <div class="font-bold text-foreground">
                                    {{ order.customer.profile?.first_name || 'Sin Nombre' }} {{ order.customer.profile?.last_name || '' }}
                                </div>
                                <div class="text-xs text-muted-foreground mt-0.5">{{ order.customer.phone }}</div>
                                
                                <div class="mt-2 flex items-center gap-2 text-xs font-bold uppercase" :class="order.delivery_type === 'delivery' ? 'text-blue-500' : 'text-purple-500'">
                                    <Truck v-if="order.delivery_type === 'delivery'" :size="14"/>
                                    <Store v-else :size="14"/>
                                    {{ order.delivery_type === 'delivery' ? 'Delivery' : 'PickUp' }}
                                    <span class="text-muted-foreground ml-1">| {{ order.branch.name }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-xs font-bold">
                                        <span class="w-16 text-muted-foreground uppercase text-[10px]">Pago 1:</span>
                                        <div class="flex items-center gap-1.5 px-2 py-1 rounded"
                                            :class="{
                                                'bg-warning/20 text-warning animate-pulse': order.advance_status === 'under_review',
                                                'bg-success/20 text-success': order.advance_status === 'approved',
                                                'bg-destructive/20 text-destructive': order.advance_status === 'rejected',
                                                'bg-muted text-muted-foreground': order.advance_status === 'pending'
                                            }">
                                            <Clock v-if="order.advance_status === 'under_review'" :size="12"/>
                                            <CheckCircle v-else-if="order.advance_status === 'approved'" :size="12"/>
                                            <XCircle v-else-if="order.advance_status === 'rejected'" :size="12"/>
                                            <span class="uppercase tracking-wider text-[10px]">{{ order.advance_status }}</span>
                                        </div>
                                    </div>

                                    <div v-if="order.payment_type === 'partial'" class="flex items-center gap-2 text-xs font-bold">
                                        <span class="w-16 text-muted-foreground uppercase text-[10px]">Pago 2:</span>
                                        <div class="flex items-center gap-1.5 px-2 py-1 rounded"
                                            :class="{
                                                'bg-warning/20 text-warning animate-pulse': order.balance_status === 'under_review',
                                                'bg-success/20 text-success': order.balance_status === 'approved',
                                                'bg-destructive/20 text-destructive': order.balance_status === 'rejected',
                                                'bg-muted text-muted-foreground': ['none', 'pending'].includes(order.balance_status)
                                            }">
                                            <Clock v-if="order.balance_status === 'under_review'" :size="12"/>
                                            <CheckCircle v-else-if="order.balance_status === 'approved'" :size="12"/>
                                            <XCircle v-else-if="order.balance_status === 'rejected'" :size="12"/>
                                            <span class="uppercase tracking-wider text-[10px]">
                                                {{ order.balance_status === 'none' ? 'En Espera' : order.balance_status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 align-middle text-right">
                                <button v-if="order.advance_status === 'under_review'" 
                                    @click="openReviewModal(order, 'advance')"
                                    class="bg-primary hover:bg-primary/90 text-primary-foreground px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider inline-flex items-center gap-2 shadow-md">
                                    <Eye :size="14"/> Revisar Pago 1
                                </button>
                                
                                <button v-else-if="order.status === 'preparing'" 
                                    @click="dispatchOrder(order.id)"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider inline-flex items-center gap-2 shadow-md">
                                    <Package :size="14"/> Despachar
                                </button>

                                <button v-else-if="order.balance_status === 'under_review'" 
                                    @click="openReviewModal(order, 'balance')"
                                    class="bg-warning hover:bg-warning/90 text-warning-foreground px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider inline-flex items-center gap-2 shadow-md">
                                    <Eye :size="14"/> Revisar Saldo
                                </button>
                                
                                <span v-else class="text-xs font-bold text-muted-foreground uppercase">
                                    <template v-if="order.status === 'pending_payment'">Esperando Cliente</template>
                                    <template v-else-if="order.status === 'dispatched'">En Ruta</template>
                                </span>
                            </td>
                        </tr>
                        <tr v-if="orders.data.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-muted-foreground">
                                No hay órdenes activas que requieran atención en este momento.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
            <div class="bg-card border border-border w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row max-h-[90vh]">
                
                <div class="w-full md:w-1/2 bg-muted p-6 flex flex-col items-center justify-center border-r border-border relative overflow-y-auto">
                    <div class="absolute top-4 left-4 bg-background/80 backdrop-blur px-3 py-1 rounded-lg text-xs font-black uppercase flex items-center gap-2 border border-border">
                        <FileImage :size="14"/> Comprobante
                    </div>
                    <img v-if="currentProofUrl" :src="currentProofUrl" alt="Comprobante" class="max-w-full rounded-xl shadow-lg border border-border/50 object-contain max-h-full">
                    <div v-else class="text-muted-foreground flex flex-col items-center gap-2">
                        <ShieldAlert :size="48" class="opacity-20"/>
                        <p class="font-bold">Error al cargar la imagen</p>
                    </div>
                </div>

                <div class="w-full md:w-1/2 p-8 flex flex-col bg-background overflow-y-auto">
                    <div class="mb-8">
                        <h2 class="text-2xl font-black uppercase italic tracking-tight mb-1">
                            Auditoría de <span class="text-primary">Pago</span>
                        </h2>
                        <p class="text-sm font-bold text-muted-foreground">
                            Orden: <span class="text-foreground">{{ selectedOrder?.code }}</span> | 
                            Monto: <span class="text-foreground">Bs {{ parseFloat(amountUnderReview).toFixed(2) }}</span>
                        </p>
                    </div>

                    <div class="flex gap-2 mb-6 p-1 bg-muted rounded-xl">
                        <button @click="actionType = 'approve'" :class="['flex-1 py-2 text-xs font-black uppercase rounded-lg transition-colors', actionType === 'approve' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground']">
                            Aprobar Pago
                        </button>
                        <button @click="actionType = 'reject'" :class="['flex-1 py-2 text-xs font-black uppercase rounded-lg transition-colors', actionType === 'reject' ? 'bg-background shadow-sm text-destructive' : 'text-muted-foreground hover:text-foreground']">
                            Rechazar Comprobante
                        </button>
                    </div>

                    <div v-if="actionType === 'approve'" class="flex-1 flex flex-col justify-between">
                        <div>
                            <div class="bg-primary/5 border border-primary/20 p-4 rounded-xl mb-6 flex gap-3 items-start">
                                <AlertTriangle class="text-primary shrink-0" :size="16"/>
                                <p class="text-xs text-muted-foreground leading-relaxed">
                                    Confirma que el monto de <strong class="text-foreground">Bs {{ parseFloat(amountUnderReview).toFixed(2) }}</strong> ha ingresado a la cuenta bancaria. 
                                    La referencia es obligatoria para la conciliación contable.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Número de Referencia Bancaria</label>
                                <input v-model="approveForm.bank_reference" type="text" placeholder="Ej: 00012345678" 
                                    class="w-full bg-background border border-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary font-mono"
                                    :class="{'border-destructive': approveForm.errors.bank_reference}">
                                <div v-if="approveForm.errors.bank_reference" class="text-destructive text-xs font-bold mt-1">{{ approveForm.errors.bank_reference }}</div>
                            </div>
                        </div>

                        <div class="mt-8 flex gap-3">
                            <button @click="closeModal" class="btn btn-ghost flex-1">Cancelar</button>
                            <button @click="submitApprove" :disabled="approveForm.processing" class="btn btn-primary flex-1 font-black shadow-lg">
                                {{ approveForm.processing ? 'Procesando...' : 'Aprobar y Registrar' }}
                            </button>
                        </div>
                    </div>

                    <div v-if="actionType === 'reject'" class="flex-1 flex flex-col justify-between">
                        <div>
                            <div class="bg-destructive/5 border border-destructive/20 p-4 rounded-xl mb-6">
                                <p class="text-xs text-destructive leading-relaxed font-bold">
                                    Al rechazar, el cliente recibirá una notificación y se le otorgarán 10 minutos adicionales para subir un comprobante válido.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Motivo del Rechazo (Visible para el cliente)</label>
                                <textarea v-model="rejectForm.rejection_reason" rows="4" placeholder="Ej: La imagen está borrosa o el monto depositado es incorrecto." 
                                    class="w-full bg-background border border-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-destructive resize-none"
                                    :class="{'border-destructive': rejectForm.errors.rejection_reason}"></textarea>
                                <div v-if="rejectForm.errors.rejection_reason" class="text-destructive text-xs font-bold mt-1">{{ rejectForm.errors.rejection_reason }}</div>
                            </div>
                        </div>

                        <div class="mt-8 flex gap-3">
                            <button @click="closeModal" class="btn btn-ghost flex-1">Cancelar</button>
                            <button @click="submitReject" :disabled="rejectForm.processing" class="bg-destructive hover:bg-destructive/90 text-white rounded-xl flex-1 font-black shadow-lg uppercase text-xs tracking-wider">
                                {{ rejectForm.processing ? 'Procesando...' : 'Confirmar Rechazo' }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AdminLayout>
</template>
