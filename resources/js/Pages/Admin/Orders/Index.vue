<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
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
const actionType = ref('approve'); // 'approve' o 'reject'

// Formularios independientes
const approveForm = useForm({
    bank_reference: ''
});

const rejectForm = useForm({
    rejection_reason: ''
});

// --- FUNCIONES DEL MODAL ---
const openReviewModal = (order) => {
    selectedOrder.value = order;
    approveForm.bank_reference = '';
    rejectForm.rejection_reason = '';
    actionType.value = 'approve';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedOrder.value = null;
        approveForm.reset();
        rejectForm.reset();
        approveForm.clearErrors();
        rejectForm.clearErrors();
    }, 200);
};

const currentProofUrl = computed(() => {
    if (!selectedOrder.value || !selectedOrder.value.proof_of_payment) return null;
    return `/storage/${selectedOrder.value.proof_of_payment}`;
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

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-BO', { 
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' 
    });
};
</script>

<template>
    <AdminLayout>
        <div class="p-6">
            <div class="flex justify-between items-end mb-6">
                <div>
                    <h1 class="text-2xl font-black text-foreground uppercase tracking-tight">Control de Órdenes</h1>
                    <p class="text-sm text-muted-foreground mt-1">Gestión de pagos y logística.</p>
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
                            <th class="px-6 py-4">Orden & Monto</th>
                            <th class="px-6 py-4">Cliente & Destino</th>
                            <th class="px-6 py-4">Estado Operativo</th>
                            <th class="px-6 py-4 text-right">Acción Requerida</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="order in orders.data" :key="order.id" class="hover:bg-muted/20 transition-colors">
                            
                            <td class="px-6 py-4 align-top">
                                <div class="font-mono font-black text-primary">{{ order.code }}</div>
                                <div class="text-xs text-muted-foreground mt-1">{{ formatDate(order.created_at) }}</div>
                                <div class="mt-2 font-black text-foreground text-sm">
                                    Bs {{ parseFloat(order.total_amount).toFixed(2) }}
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

                            <td class="px-6 py-4 align-middle">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border"
                                     :class="{
                                         'bg-warning/10 text-warning border-warning/20': order.status === 'pending_payment',
                                         'bg-primary/10 text-primary border-primary/20 animate-pulse': order.status === 'under_review',
                                         'bg-blue-500/10 text-blue-500 border-blue-500/20': order.status === 'preparing',
                                         'bg-purple-500/10 text-purple-500 border-purple-500/20': order.status === 'dispatched'
                                     }">
                                    <AlertTriangle v-if="order.status === 'pending_payment'" :size="14" />
                                    <Eye v-else-if="order.status === 'under_review'" :size="14" />
                                    <Package v-else-if="order.status === 'preparing'" :size="14" />
                                    <Truck v-else-if="order.status === 'dispatched'" :size="14" />
                                    
                                    <span class="uppercase tracking-wider">
                                        <template v-if="order.status === 'pending_payment'">Sin Pagar</template>
                                        <template v-else-if="order.status === 'under_review'">Revisar Pago</template>
                                        <template v-else-if="order.status === 'preparing'">Preparando</template>
                                        <template v-else-if="order.status === 'dispatched'">Despachado</template>
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 align-middle text-right">
                                <button v-if="order.status === 'under_review'" 
                                    @click="openReviewModal(order)"
                                    class="bg-primary hover:bg-primary/90 text-primary-foreground px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider inline-flex items-center gap-2 shadow-md transition-all active:scale-95">
                                    <Eye :size="14"/> Auditar Pago
                                </button>
                                
                                <button v-else-if="order.status === 'preparing'" 
                                    @click="dispatchOrder(order.id)"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider inline-flex items-center gap-2 shadow-md transition-all active:scale-95">
                                    <Package :size="14"/> Despachar
                                </button>
                                
                                <span v-else class="text-xs font-bold text-muted-foreground uppercase">
                                    <template v-if="order.status === 'pending_payment'">Esperando Cliente</template>
                                    <template v-else-if="order.status === 'dispatched'">En Ruta</template>
                                </span>
                            </td>
                        </tr>
                        
                        <tr v-if="orders.data.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-muted-foreground font-medium">
                                No hay órdenes activas que requieran atención en este momento.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-if="orders.links && orders.links.length > 3" class="flex justify-center mt-6 gap-1 overflow-x-auto">
                <Link v-for="(link, k) in orders.links" :key="k" 
                      :href="link.url || '#'" 
                      v-html="link.label"
                      class="px-3 py-1.5 text-xs rounded border transition-all font-medium text-center"
                      :class="link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-card text-muted-foreground border-border hover:bg-muted' + (!link.url ? ' opacity-40 pointer-events-none' : '')" />
            </div>

        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
            <div class="bg-card border border-border w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row max-h-[90vh]">
                
                <div class="w-full md:w-1/2 bg-muted p-6 flex flex-col items-center justify-center border-r border-border relative overflow-y-auto">
                    <div class="absolute top-4 left-4 bg-background/80 backdrop-blur px-3 py-1 rounded-lg text-xs font-black uppercase flex items-center gap-2 border border-border shadow-sm">
                        <FileImage :size="14" class="text-primary"/> Comprobante Adjunto
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
                            Auditoría <span class="text-primary">Financiera</span>
                        </h2>
                        <p class="text-sm font-bold text-muted-foreground">
                            Orden: <span class="text-foreground">#{{ selectedOrder?.code }}</span> | 
                            Monto: <span class="text-foreground text-lg">Bs {{ parseFloat(selectedOrder?.total_amount).toFixed(2) }}</span>
                        </p>
                    </div>

                    <div class="flex gap-2 mb-6 p-1 bg-muted rounded-xl">
                        <button @click="actionType = 'approve'" :class="['flex-1 py-2 text-xs font-black uppercase rounded-lg transition-all', actionType === 'approve' ? 'bg-background shadow text-foreground' : 'text-muted-foreground hover:text-foreground']">
                            Aprobar
                        </button>
                        <button @click="actionType = 'reject'" :class="['flex-1 py-2 text-xs font-black uppercase rounded-lg transition-all', actionType === 'reject' ? 'bg-background shadow text-destructive' : 'text-muted-foreground hover:text-foreground']">
                            Rechazar
                        </button>
                    </div>

                    <div v-if="actionType === 'approve'" class="flex-1 flex flex-col justify-between animate-in fade-in slide-in-from-right-4 duration-300">
                        <div>
                            <div class="bg-primary/5 border border-primary/20 p-4 rounded-xl mb-6 flex gap-3 items-start">
                                <CheckCircle class="text-primary shrink-0 mt-0.5" :size="16"/>
                                <p class="text-xs text-muted-foreground leading-relaxed">
                                    Confirma que <strong class="text-foreground">Bs {{ parseFloat(selectedOrder?.total_amount).toFixed(2) }}</strong> han ingresado a la cuenta. 
                                    Digita la referencia para cerrar el flujo contable.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Referencia Bancaria</label>
                                <input v-model="approveForm.bank_reference" type="text" placeholder="Ej: 00012345678" 
                                    class="w-full bg-background border border-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary font-mono"
                                    :class="{'border-destructive': approveForm.errors.bank_reference}">
                                <div v-if="approveForm.errors.bank_reference" class="text-destructive text-xs font-bold mt-1">{{ approveForm.errors.bank_reference }}</div>
                            </div>
                        </div>

                        <div class="mt-8 flex gap-3">
                            <button @click="closeModal" class="btn btn-ghost flex-1">Cancelar</button>
                            <button @click="submitApprove" :disabled="approveForm.processing" class="btn btn-primary flex-1 font-black shadow-lg shadow-primary/20 uppercase tracking-wider text-xs">
                                <span v-if="approveForm.processing">Procesando...</span>
                                <span v-else class="flex items-center justify-center gap-2">Aprobar Pago</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="actionType === 'reject'" class="flex-1 flex flex-col justify-between animate-in fade-in slide-in-from-left-4 duration-300">
                        <div>
                            <div class="bg-destructive/5 border border-destructive/20 p-4 rounded-xl mb-6 flex gap-3 items-start">
                                <AlertTriangle class="text-destructive shrink-0 mt-0.5" :size="16" />
                                <p class="text-xs text-destructive leading-relaxed font-bold">
                                    Al rechazar, el cliente recibirá una notificación en su panel y se le otorgarán 10 minutos para subir un comprobante válido.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Motivo del Rechazo (Visible para el cliente)</label>
                                <textarea v-model="rejectForm.rejection_reason" rows="4" placeholder="Ej: La imagen está borrosa o el monto depositado (Bs X) es incorrecto." 
                                    class="w-full bg-background border border-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-destructive focus:ring-1 focus:ring-destructive resize-none"
                                    :class="{'border-destructive': rejectForm.errors.rejection_reason}"></textarea>
                                <div v-if="rejectForm.errors.rejection_reason" class="text-destructive text-xs font-bold mt-1">{{ rejectForm.errors.rejection_reason }}</div>
                            </div>
                        </div>

                        <div class="mt-8 flex gap-3">
                            <button @click="closeModal" class="btn btn-ghost flex-1">Cancelar</button>
                            <button @click="submitReject" :disabled="rejectForm.processing" class="bg-destructive hover:bg-destructive/90 text-white rounded-xl flex-1 font-black shadow-lg shadow-destructive/20 uppercase text-xs tracking-wider transition-all active:scale-95">
                                <span v-if="rejectForm.processing">Procesando...</span>
                                <span v-else class="flex items-center justify-center gap-2">Confirmar Rechazo</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AdminLayout>
</template>