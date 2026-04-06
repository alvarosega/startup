<script setup>
import { ref } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue'; 
import { ArrowLeft, ShieldCheck, XCircle } from 'lucide-vue-next';

const props = defineProps({ order: Object });

const actionType = ref('approve'); 
const approveForm = useForm({ bank_reference: '' });
const rejectForm = useForm({ rejection_reason: '' });

const submitApprove = () => approveForm.post(route('admin.orders.approve-payment', props.order.id));
const submitReject = () => rejectForm.post(route('admin.orders.reject-payment', props.order.id));
</script>

<template>
    <AdminLayout>
        <Head :title="'Validar Pago ' + order.code" />
        <div class="p-6 max-w-5xl mx-auto">
            <Link :href="route('admin.orders.index')" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-muted-foreground hover:text-foreground mb-6">
                <ArrowLeft :size="16" /> Monitor Logístico
            </Link>

            <div class="bg-card border-2 border-border rounded-3xl p-8 shadow-xl">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h1 class="text-3xl font-black uppercase italic tracking-tighter text-foreground"><span class="text-primary">#</span>{{ order.code }}</h1>
                        <p class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] mt-1">{{ order.customer.name }}</p>
                    </div>
                    <div class="px-4 py-2 rounded-xl bg-primary/10 text-primary border-2 border-primary/20 font-black uppercase text-[10px] tracking-widest animate-pulse">
                        ESPERANDO AUDITORÍA
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="rounded-2xl overflow-hidden border-2 border-border bg-muted flex items-center justify-center min-h-[400px]">
                        <img v-if="order.proof_url" :src="order.proof_url" class="max-w-full max-h-full object-contain" alt="Comprobante">
                        <div v-else class="text-muted-foreground font-black uppercase text-xs">Sin Comprobante</div>
                    </div>

                    <div class="flex flex-col justify-center">
                        <div class="bg-muted p-6 rounded-2xl border border-border mb-6 text-center">
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Monto a Verificar</p>
                            <p class="text-4xl font-black font-mono tracking-tighter text-foreground">
                                <span class="text-sm text-primary mr-1">Bs</span>{{ order.total_amount.toFixed(2) }}
                            </p>
                        </div>

                        <div class="flex gap-2 p-1 bg-muted rounded-2xl mb-6">
                            <button @click="actionType = 'approve'" :class="['flex-1 py-3 text-[10px] font-black uppercase rounded-xl transition-all', actionType === 'approve' ? 'bg-background shadow-xl text-foreground' : 'text-muted-foreground']">Aprobar</button>
                            <button @click="actionType = 'reject'" :class="['flex-1 py-3 text-[10px] font-black uppercase rounded-xl transition-all', actionType === 'reject' ? 'bg-background shadow-xl text-destructive' : 'text-muted-foreground']">Rechazar</button>
                        </div>

                        <div v-if="actionType === 'approve'" class="space-y-4 animate-in fade-in">
                            <input v-model="approveForm.bank_reference" type="text" placeholder="REF BANCARIA / ID TRANSACCIÓN" class="w-full bg-muted border-2 border-border rounded-2xl px-6 py-4 text-sm font-black focus:border-primary outline-none uppercase">
                            <button @click="submitApprove" :disabled="approveForm.processing" class="w-full py-4 bg-primary text-primary-foreground font-black uppercase text-xs rounded-2xl shadow-xl shadow-primary/20 active:scale-95 transition-transform flex justify-center items-center gap-2">
                                <ShieldCheck :size="16" /> Confirmar Ingreso
                            </button>
                        </div>

                        <div v-if="actionType === 'reject'" class="space-y-4 animate-in fade-in">
                            <textarea v-model="rejectForm.rejection_reason" placeholder="MOTIVO DEL RECHAZO" class="w-full bg-muted border-2 border-border rounded-2xl px-6 py-4 text-sm font-bold focus:border-destructive outline-none h-24 uppercase"></textarea>
                            <button @click="submitReject" :disabled="rejectForm.processing" class="w-full py-4 bg-destructive text-white font-black uppercase text-xs rounded-2xl shadow-xl shadow-destructive/20 active:scale-95 transition-transform flex justify-center items-center gap-2">
                                <XCircle :size="16" /> Rechazar Pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>