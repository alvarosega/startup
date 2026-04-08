<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    ArrowLeft, CheckCircle2, XCircle, AlertTriangle, Image as ImageIcon,
    Receipt, Package, Building
} from 'lucide-vue-next';

const props = defineProps({
    order: Object,
    customer: Object,
    branch: Object,
    items: Array
});

// Modales y formularios
const showApproveModal = ref(false);
const showRejectModal = ref(false);

const approveForm = useForm({ bank_reference: '' });
const rejectForm = useForm({ rejection_reason: '', rejection_action: 'retry' });
const submitApprove = () => {
    approveForm.post(route('admin.orders.approve-payment', props.order.code), {
        onSuccess: () => showApproveModal.value = false
    });
};

const submitReject = () => {
    rejectForm.post(route('admin.orders.reject-payment', props.order.code), {
        onSuccess: () => showRejectModal.value = false
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="'Revisión de Pago #' + order.code" />
        
        <div class="p-6 max-w-6xl mx-auto">
            <Link :href="route('admin.orders.index')" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-muted-foreground hover:text-foreground mb-8">
                <ArrowLeft :size="16" /> Logística Central
            </Link>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-card border-2 border-border rounded-[2rem] p-6 shadow-xl relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-amber-500 animate-pulse"></div>
                        <h2 class="text-sm font-black uppercase tracking-widest text-foreground flex items-center gap-2 mb-6">
                            <ImageIcon :size="18" class="text-amber-500" /> Archivo Inyectado
                        </h2>
                        
                        <div class="bg-black/40 rounded-2xl border-2 border-dashed border-border aspect-[3/4] flex items-center justify-center overflow-hidden">
                            <img :src="order.proof_url" alt="Comprobante" class="w-full h-full object-contain hover:scale-110 transition-transform duration-500 cursor-zoom-in" />
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-6">
                    
                    <div class="bg-card border border-border rounded-[2rem] p-8 shadow-xl flex justify-between items-center">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-1">Orden Operativa</p>
                            <h1 class="text-4xl font-black text-foreground font-mono tracking-tighter">#{{ order.code }}</h1>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-1">Monto Declarado</p>
                            <p class="text-3xl font-black text-primary font-mono">Bs {{ order.total_amount.toFixed(2) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-muted/30 border border-border rounded-3xl p-6">
                            <Receipt :size="20" class="text-muted-foreground mb-4" />
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Remitente (Cliente)</p>
                            <p class="text-sm font-black uppercase text-foreground">{{ customer.name }}</p>
                            <p class="text-xs font-bold text-muted-foreground mt-1">{{ customer.phone }}</p>
                        </div>
                        <div class="bg-muted/30 border border-border rounded-3xl p-6">
                            <Building :size="20" class="text-muted-foreground mb-4" />
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Almacén Origen</p>
                            <p class="text-sm font-black uppercase text-foreground">{{ branch.name }}</p>
                            <p class="text-xs font-bold text-muted-foreground mt-1 uppercase">{{ order.delivery_type }}</p>
                        </div>
                    </div>

                    <div class="bg-card border-2 border-border rounded-[2rem] p-8 shadow-xl">
                        <h3 class="text-xs font-black uppercase tracking-widest text-foreground mb-6 flex items-center gap-2">
                            <CheckCircle2 :size="16" class="text-primary"/> Decisión Administrativa
                        </h3>
                        
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button @click="showApproveModal = true" class="flex-1 bg-primary hover:bg-primary/90 text-background py-4 rounded-2xl font-black uppercase text-xs tracking-widest transition-all active:scale-95 shadow-lg shadow-primary/20">
                                Validar Pago
                            </button>
                            <button @click="showRejectModal = true" class="flex-1 bg-transparent hover:bg-f1-red/10 text-f1-red border-2 border-f1-red/50 hover:border-f1-red py-4 rounded-2xl font-black uppercase text-xs tracking-widest transition-all active:scale-95">
                                Rechazar Archivo
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div v-if="showApproveModal" class="fixed inset-0 z-50 bg-background/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-card border-2 border-primary/30 rounded-[2rem] p-8 w-full max-w-md shadow-2xl">
                <h3 class="text-lg font-black uppercase italic tracking-tighter text-foreground mb-2">Validar Transferencia</h3>
                <p class="text-xs font-bold text-muted-foreground mb-6">El stock se deducirá físicamente del inventario y pasará a preparación.</p>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Referencia Bancaria / Nro de Recibo</label>
                        <input v-model="approveForm.bank_reference" type="text" class="w-full bg-muted border border-border rounded-xl px-4 py-3 text-sm font-bold focus:border-primary transition-colors" placeholder="Ej: 123456789">
                        <p v-if="approveForm.errors.bank_reference" class="text-[10px] font-black text-f1-red mt-2 uppercase tracking-widest">{{ approveForm.errors.bank_reference }}</p>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button @click="showApproveModal = false" class="flex-1 bg-transparent border border-border text-foreground py-3 rounded-xl font-black text-[10px] uppercase tracking-widest">Cancelar</button>
                        <button @click="submitApprove" :disabled="approveForm.processing" class="flex-1 bg-primary text-background py-3 rounded-xl font-black text-[10px] uppercase tracking-widest disabled:opacity-50">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showRejectModal" class="fixed inset-0 z-50 bg-background/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-card border-2 border-f1-red/30 rounded-[2rem] p-8 w-full max-w-md shadow-2xl">
                <h3 class="text-lg font-black uppercase italic tracking-tighter text-f1-red mb-2">Rechazar Comprobante</h3>
                <p class="text-xs font-bold text-muted-foreground mb-6">Indique el motivo y el destino de la orden operativa.</p>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Destino de la Orden</label>
                        <select v-model="rejectForm.rejection_action" class="w-full bg-muted border border-border rounded-xl px-4 py-3 text-xs font-black uppercase tracking-widest focus:border-f1-red transition-colors">
                            <option value="retry">Solicitar Re-envío (Mantiene Reserva)</option>
                            <option value="cancel">Cancelar Definitivamente (Libera Stock)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Motivo del Rechazo</label>
                        <textarea v-model="rejectForm.rejection_reason" rows="3" class="w-full bg-muted border border-border rounded-xl px-4 py-3 text-sm font-bold focus:border-f1-red transition-colors" placeholder="Ej: Imagen borrosa, monto incorrecto..."></textarea>
                        <p v-if="rejectForm.errors.rejection_reason" class="text-[10px] font-black text-f1-red mt-2 uppercase tracking-widest">{{ rejectForm.errors.rejection_reason }}</p>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button @click="showRejectModal = false" class="flex-1 bg-transparent border border-border text-foreground py-3 rounded-xl font-black text-[10px] uppercase tracking-widest">Volver</button>
                        <button @click="submitReject" :disabled="rejectForm.processing" class="flex-1 bg-f1-red text-white py-3 rounded-xl font-black text-[10px] uppercase tracking-widest disabled:opacity-50">Ejecutar Rechazo</button>
                    </div>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>