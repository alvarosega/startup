<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm, router, Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Clock, ArrowLeft, Receipt, AlertTriangle, CheckCircle2, ShieldCheck, Loader2 } from 'lucide-vue-next';

const props = defineProps({ 
    order: Object, 
    payment_context: Object 
});

// --- LÓGICA DE TELEMETRÍA TEMPORAL ---
const timeRemaining = ref(Number(props.payment_context.seconds_remaining));
let timerInterval = null;

const formattedTime = computed(() => {
    if (timeRemaining.value <= 0) return "00:00";
    const m = Math.floor(timeRemaining.value / 60).toString().padStart(2, '0');
    const s = Math.floor(timeRemaining.value % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
});

const isExpired = computed(() => timeRemaining.value <= 0);

onMounted(() => {
    timerInterval = setInterval(() => {
        if (timeRemaining.value > 0) timeRemaining.value--;
        else { 
            clearInterval(timerInterval); 
            router.reload({ preserveScroll: true }); 
        }
    }, 1000);
});

onUnmounted(() => { if (timerInterval) clearInterval(timerInterval); });

// --- LÓGICA DE TRANSACCIÓN ---
const form = useForm({ proof: null });
const isButtonDisabled = computed(() => {
    return !form.proof || form.processing || isExpired.value;
});

const submitProof = () => {
    if (isButtonDisabled.value) return; 
    form.clearErrors();
    form.post(route('customer.order.upload-proof', props.order.code), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <ShopLayout>
        <Head :title="'Protocolo de Pago #' + order.code" />
        
        <div class="max-w-2xl mx-auto p-6 min-h-[90vh] pb-32">
            
            <Link :href="route('customer.order.index')" 
                  class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-[0.2em] text-foreground/40 hover:text-primary transition-colors mb-10 group">
                <ArrowLeft :size="16" class="group-hover:-translate-x-1 transition-transform" /> 
                Regresar al Historial
            </Link>

            <div class="product-card glass-titanium !rounded-3xl border border-border/10 overflow-hidden relative shadow-f1-glow">
                
                <div class="h-1.5 w-full bg-background overflow-hidden">
                    <div class="h-full transition-all duration-1000"
                         :class="isExpired ? 'bg-destructive' : 'bg-amber-500 animate-pulse'"
                         :style="{ width: (timeRemaining / payment_context.seconds_remaining * 100) + '%' }">
                    </div>
                </div>

                <div class="p-8 md:p-10">
                    <div class="text-center mb-12">
                        <div class="inline-flex flex-col items-center">
                            <div class="bg-foreground/5 border-2 border-border/10 rounded-2xl px-8 py-6 mb-4 backdrop-blur-md">
                                <span class="text-5xl md:text-6xl font-black font-mono tracking-tighter italic"
                                      :class="isExpired ? 'text-destructive' : 'text-amber-500'">
                                    {{ formattedTime }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Clock :size="14" class="text-foreground/40" />
                                <span class="text-xs font-black uppercase tracking-[0.3em] text-foreground/40">
                                    Ventana de Reserva de Stock
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="product-card bg-background/40 border border-border/10 p-8 mb-10 text-center relative group">
                        <div class="absolute top-4 right-4 text-primary opacity-20 group-hover:opacity-100 transition-opacity">
                            <ShieldCheck :size="20" />
                        </div>

                        <div class="w-56 h-56 bg-white p-4 rounded-2xl shadow-apple-soft mx-auto mb-6 relative">
                            <img :src="payment_context.qr_image" 
                                 class="w-full h-full object-contain" 
                                 :class="{'opacity-20 grayscale': isExpired}"
                                 alt="QR_PAYMENT">
                            <div v-if="isExpired" class="absolute inset-0 flex items-center justify-center">
                                <span class="bg-destructive text-white text-[10px] font-black uppercase px-3 py-1 rounded-full">Expirado</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <p class="text-xs font-black uppercase text-foreground/40 tracking-widest">
                                {{ payment_context.bank_name || 'Transferencia Interbancaria' }}
                            </p>
                            <p class="text-3xl font-black text-foreground font-mono tracking-tighter italic">
                                <span class="text-sm text-primary mr-1 not-italic font-sans">Bs</span>{{ Number(order.total_amount).toFixed(2) }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-center gap-3 px-2">
                            <Receipt :size="16" class="text-primary" />
                            <h2 class="text-xs font-black uppercase tracking-[0.2em] text-foreground/60">Inyectar Comprobante de Pago</h2>
                        </div>

                        <div v-if="form.errors.proof || $page.props.errors?.error" 
                             class="product-card bg-destructive/10 border-destructive/20 p-4 flex gap-4 items-center animate-in slide-in-from-top-2">
                            <AlertTriangle class="text-destructive shrink-0" :size="20" />
                            <p class="text-xs font-black text-destructive uppercase leading-tight">
                                {{ form.errors.proof || $page.props.errors.error }}
                            </p>
                        </div>

                        <div class="relative group">
                            <input type="file" 
                                   @change="e => form.proof = e.target.files[0]" 
                                   :disabled="isExpired || form.processing"
                                   class="block w-full text-xs text-foreground/50
                                          file:mr-6 file:py-4 file:px-8
                                          file:rounded-2xl file:border-0
                                          file:text-xs file:font-black file:uppercase file:tracking-widest
                                          file:bg-foreground file:text-background
                                          hover:file:bg-primary hover:file:text-white
                                          cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed
                                          bg-foreground/5 rounded-2xl border border-border/10 transition-all group-hover:border-primary/30">
                        </div>

                        <button @click="submitProof" 
                                :disabled="isButtonDisabled" 
                                class="btn-primary w-full h-16 !rounded-2xl shadow-apple-soft group overflow-hidden">
                            <div class="flex items-center justify-center gap-3">
                                <template v-if="form.processing">
                                    <Loader2 class="animate-spin" :size="20" />
                                    <span class="text-xs font-black uppercase tracking-[0.2em]">Sincronizando...</span>
                                </template>
                                <template v-else>
                                    <CheckCircle2 :size="20" stroke-width="2.5" class="group-hover:scale-110 transition-transform" />
                                    <span class="text-xs font-black uppercase tracking-[0.2em]">Confirmar Transferencia</span>
                                </template>
                            </div>
                        </button>

                        <p v-if="isExpired" class="text-center text-[10px] font-black uppercase text-destructive tracking-widest animate-pulse">
                            La reserva ha caducado. El stock ha sido liberado al sistema.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.glass-titanium {
    background: linear-gradient(135deg, hsl(var(--card) / 0.8) 0%, hsl(var(--card) / 0.4) 100%);
    backdrop-filter: blur(40px) saturate(200%);
    -webkit-backdrop-filter: blur(40px) saturate(200%);
}

.shadow-f1-glow {
    box-shadow: 0 0 40px -10px hsl(var(--primary) / 0.2);
}

.btn-primary {
    @apply bg-foreground text-background transition-all active:scale-95 disabled:grayscale disabled:opacity-20;
}

.dark .btn-primary {
    @apply bg-primary text-white;
}

/* Animación de entrada para el módulo */
.product-card {
    animation: reveal 0.6s cubic-bezier(0.22, 1, 0.36, 1) backwards;
}

@keyframes reveal {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>