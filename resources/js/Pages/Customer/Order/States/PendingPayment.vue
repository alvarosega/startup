<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm, router, Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Clock, ArrowLeft, Receipt, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({ 
    order: Object, 
    payment_context: Object 
});

const timeRemaining = ref(Number(props.payment_context.seconds_remaining));
let timerInterval = null;

const formattedTime = computed(() => {
    if (timeRemaining.value <= 0) return "00:00";
    const m = Math.floor(timeRemaining.value / 60).toString().padStart(2, '0');
    const s = Math.floor(timeRemaining.value % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
});

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

const form = useForm({ proof: null });

const submitProof = () => {
    if (!form.proof) return;
    form.clearErrors();
    form.post(route('customer.order.upload-proof', props.order.id), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <ShopLayout>
        <Head :title="'Pago Pendiente #' + order.code" />
        
        <div class="max-w-2xl mx-auto p-6 min-h-[80vh]">
            <Link :href="route('customer.order.index')" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-foreground mb-8">
                <ArrowLeft :size="14" /> Volver al historial
            </Link>

            <div class="bg-card border border-border rounded-[2.5rem] p-8 shadow-2xl overflow-hidden relative">
                <div class="absolute top-0 left-0 w-full h-1 bg-amber-500 animate-pulse"></div>
                
                <div class="text-center mb-10">
                    <div class="bg-black/20 text-amber-500 text-5xl font-black py-8 px-12 rounded-[2.5rem] font-mono tracking-[0.2em] inline-block border-2 border-amber-500/20 italic mb-4">
                        {{ formattedTime }}
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Reserva de stock activa</p>
                </div>

                <div class="bg-muted/30 border border-border rounded-[2rem] p-8 space-y-8">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-48 h-48 bg-white p-3 rounded-3xl shadow-xl">
                            <img :src="payment_context.qr_image" class="w-full h-full object-contain" alt="QR">
                        </div>
                        <div class="text-center">
                            <p class="text-[9px] font-black uppercase text-muted-foreground tracking-widest mb-1">{{ payment_context.bank_name }}</p>
                            <p class="text-2xl font-black text-foreground font-mono">Bs {{ Number(order.total_amount).toFixed(2) }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-2">
                            <Receipt :size="14" class="text-primary" />
                            <span class="text-[10px] font-black uppercase tracking-widest">Inyectar Comprobante</span>
                        </div>

                        <div v-if="form.errors.proof" class="w-full p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl flex items-center gap-3">
                            <AlertTriangle :size="16" />
                            <span class="text-[10px] font-black uppercase tracking-widest">{{ form.errors.proof }}</span>
                        </div>

                        <div v-if="$page.props.errors?.error" class="w-full p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl flex items-center gap-3">
                            <AlertTriangle :size="16" />
                            <span class="text-[10px] font-black uppercase tracking-widest">{{ $page.props.errors.error }}</span>
                        </div>
                        
                        <input type="file" @change="e => form.proof = e.target.files[0]" 
                               class="block w-full text-[10px] text-muted-foreground file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-primary file:text-white hover:file:bg-primary/90 cursor-pointer">
                        
                        <button @click="submitProof" :disabled="!form.proof || form.processing" 
                                class="w-full py-5 bg-foreground text-background rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-xl disabled:opacity-30 active:scale-95 transition-all">
                            {{ form.processing ? 'Sincronizando...' : 'Confirmar Transferencia' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>