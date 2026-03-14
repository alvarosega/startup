<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue'; 
import { 
    Search, Eye, CheckCircle, Clock, Truck, 
    Package, Store, AlertTriangle, FileImage, User
} from 'lucide-vue-next';

const props = defineProps({
    orders: Object
});

// --- ESTADO DEL MODAL DE REVISIÓN ---
const isModalOpen = ref(false);
const selectedOrder = ref(null);
const actionType = ref('approve'); 

const approveForm = useForm({ bank_reference: '' });
const rejectForm = useForm({ rejection_reason: '' });

const openReviewModal = (order) => {
    selectedOrder.value = order;
    approveForm.bank_reference = '';
    rejectForm.rejection_reason = '';
    actionType.value = 'approve';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedOrder.value = null;
    approveForm.reset();
    rejectForm.reset();
};

const currentProofUrl = computed(() => {
    if (!selectedOrder.value?.proof_of_payment) return null;
    return `/storage/${selectedOrder.value.proof_of_payment}`;
});

const submitApprove = () => {
    approveForm.post(route('admin.orders.approve-payment', selectedOrder.value.id), {
        onSuccess: () => closeModal()
    });
};

const submitReject = () => {
    rejectForm.post(route('admin.orders.reject-payment', selectedOrder.value.id), {
        onSuccess: () => closeModal()
    });
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
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-3xl font-black text-foreground uppercase tracking-tighter italic">
                        Logística <span class="text-primary">& Pagos</span>
                    </h1>
                    <p class="text-xs font-bold text-muted-foreground mt-1">Monitoreo de la cadena de suministro en tiempo real.</p>
                </div>
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="16"/>
                    <input type="text" placeholder="BUSCAR ORDEN..." class="pl-9 pr-4 py-2.5 bg-card border-2 border-border rounded-xl text-xs font-bold focus:border-primary w-64 transition-all">
                </div>
            </div>

            <div class="bg-card border-2 border-border rounded-3xl shadow-xl overflow-hidden">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-muted/30 border-b-2 border-border">
                            <th class="px-6 py-5 text-[10px] font-black uppercase text-muted-foreground tracking-widest">Información de Orden</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase text-muted-foreground tracking-widest">Destinatario</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase text-muted-foreground tracking-widest">Estado y Asignación</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase text-muted-foreground tracking-widest text-right">Acción Crítica</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="order in orders.data" :key="order.id" class="hover:bg-muted/10 transition-colors">
                            <td class="px-6 py-5">
                                <div class="font-mono font-black text-primary text-base">#{{ order.code }}</div>
                                <div class="text-[10px] text-muted-foreground font-bold mt-1">{{ formatDate(order.created_at) }}</div>
                                <div class="mt-3 inline-block px-3 py-1 bg-foreground text-background rounded-lg text-xs font-black italic">
                                    Bs {{ parseFloat(order.total_amount).toFixed(2) }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="font-black text-foreground text-sm uppercase">
                                    {{ order.customer.profile?.first_name }} {{ order.customer.profile?.last_name }}
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-muted-foreground font-bold mt-1">
                                    <Clock :size="12"/> {{ order.customer.phone }}
                                </div>
                                <div class="mt-3 flex items-center gap-2 text-[10px] font-black uppercase" 
                                     :class="order.delivery_type === 'delivery' ? 'text-blue-500' : 'text-purple-500'">
                                    <Truck v-if="order.delivery_type === 'delivery'" :size="14"/>
                                    <Store v-else :size="14"/>
                                    {{ order.delivery_type }} | {{ order.branch.name }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[10px] font-black border-2 uppercase tracking-tighter"
                                     :class="{
                                         'bg-yellow-500/10 text-yellow-600 border-yellow-500/20': order.status === 'pending_payment',
                                         'bg-primary/10 text-primary border-primary/20 animate-pulse': order.status === 'under_review',
                                         'bg-blue-500/10 text-blue-500 border-blue-500/20': order.status === 'preparing',
                                         'bg-purple-500/10 text-purple-600 border-purple-500/20': order.status === 'dispatched'
                                     }">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ order.status.replace('_', ' ') }}
                                </div>

                                <div v-if="order.driver" class="mt-3 flex items-center gap-2 p-2 bg-muted/50 rounded-xl border border-border">
                                    <div class="bg-foreground text-background p-1.5 rounded-lg">
                                        <User :size="14"/>
                                    </div>
                                    <div class="leading-none">
                                        <p class="text-[9px] font-black text-muted-foreground uppercase tracking-tighter">Repartidor</p>
                                        <p class="text-[11px] font-bold text-foreground">
                                            {{ order.driver.profile?.first_name }} {{ order.driver.profile?.last_name }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-5 text-right">
                                <button v-if="order.status === 'under_review'" 
                                    @click="openReviewModal(order)"
                                    class="bg-primary hover:bg-primary/90 text-primary-foreground px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-primary/20 transition-all active:scale-95 flex items-center gap-2 ml-auto">
                                    <CheckCircle :size="14"/> Auditar Pago
                                </button>
                                
                                <div v-else-if="order.status === 'preparing' && order.driver_id" 
                                     class="inline-flex flex-col items-center bg-gray-950 text-white p-3 rounded-2xl border-2 border-primary shadow-2xl">
                                    <p class="text-[8px] font-black text-primary uppercase tracking-[0.2em] mb-1">PIN DE RECOGIDA</p>
                                    <p class="text-2xl font-mono font-black tracking-[0.3em] leading-none text-white">{{ order.pickup_otp }}</p>
                                </div>

                                <div v-else-if="order.status === 'dispatched'" class="text-[10px] font-black text-purple-600 uppercase italic">
                                    En tránsito...
                                </div>

                                <span v-else class="text-[10px] font-bold text-muted-foreground uppercase opacity-40">Sin acciones</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-background/90 backdrop-blur-md">
            <div class="bg-card border-2 border-border w-full max-w-4xl rounded-[40px] shadow-2xl overflow-hidden flex flex-col md:flex-row h-[80vh]">
                <div class="w-full md:w-1/2 bg-muted p-8 flex items-center justify-center relative">
                    <img v-if="currentProofUrl" :src="currentProofUrl" class="max-w-full max-h-full rounded-2xl shadow-2xl object-contain border-4 border-white">
                </div>

                <div class="w-full md:w-1/2 p-10 flex flex-col justify-between">
                    <div>
                        <h2 class="text-3xl font-black uppercase italic leading-none mb-6">Validación <span class="text-primary">Financiera</span></h2>
                        
                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="bg-muted p-4 rounded-2xl">
                                <p class="text-[10px] font-black text-muted-foreground uppercase mb-1">Monto Esperado</p>
                                <p class="text-xl font-black">Bs {{ parseFloat(selectedOrder?.total_amount).toFixed(2) }}</p>
                            </div>
                            <div class="bg-muted p-4 rounded-2xl">
                                <p class="text-[10px] font-black text-muted-foreground uppercase mb-1">Código Orden</p>
                                <p class="text-xl font-black text-primary">#{{ selectedOrder?.code }}</p>
                            </div>
                        </div>

                        <div class="flex gap-2 p-1 bg-muted rounded-2xl mb-6">
                            <button @click="actionType = 'approve'" :class="['flex-1 py-3 text-[10px] font-black uppercase rounded-xl transition-all', actionType === 'approve' ? 'bg-background shadow-xl text-foreground' : 'text-muted-foreground']">Aprobar</button>
                            <button @click="actionType = 'reject'" :class="['flex-1 py-3 text-[10px] font-black uppercase rounded-xl transition-all', actionType === 'reject' ? 'bg-background shadow-xl text-destructive' : 'text-muted-foreground']">Rechazar</button>
                        </div>

                        <div v-if="actionType === 'approve'" class="space-y-4 animate-in fade-in slide-in-from-right-4">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-2">Referencia Bancaria / ID Transacción</label>
                            <input v-model="approveForm.bank_reference" type="text" class="w-full bg-muted border-2 border-border rounded-2xl px-6 py-4 text-sm font-black focus:border-primary outline-none">
                        </div>

                        <div v-if="actionType === 'reject'" class="space-y-4 animate-in fade-in slide-in-from-left-4">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-2">Motivo del rechazo (Cliente verá esto)</label>
                            <textarea v-model="rejectForm.rejection_reason" class="w-full bg-muted border-2 border-border rounded-2xl px-6 py-4 text-sm font-bold focus:border-destructive outline-none h-32"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button @click="closeModal" class="flex-1 py-4 font-black uppercase text-xs text-muted-foreground hover:text-foreground">Cerrar</button>
                        <button v-if="actionType === 'approve'" @click="submitApprove" :disabled="approveForm.processing" class="flex-[2] bg-primary text-background font-black uppercase text-xs rounded-2xl shadow-xl shadow-primary/20">Confirmar Depósito</button>
                        <button v-else @click="submitReject" :disabled="rejectForm.processing" class="flex-[2] bg-destructive text-white font-black uppercase text-xs rounded-2xl shadow-xl shadow-destructive/20">Rechazar Pago</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>