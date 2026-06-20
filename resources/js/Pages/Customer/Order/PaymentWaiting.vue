<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ArrowLeft, Clock, Receipt, RefreshCw, ShieldCheck, Info } from 'lucide-vue-next';

const props = defineProps({ order: Object });
const isLoading = ref(false);

const manualRefresh = () => {
    isLoading.value = true;
    router.reload({
        only: ['order'],
        onFinish: () => { isLoading.value = false; }
    });
};
</script>

<template>
    <ShopLayout>
        <Head :title="'Validación de Pago #' + order.code" />
        
        <div class="max-w-2xl mx-auto p-6 min-h-[90vh] pb-32">
            
            <div class="flex justify-between items-center mb-10">
                <Link :href="route('customer.order.index')" 
                      class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-[0.2em] text-foreground/40 hover:text-primary transition-colors group">
                    <ArrowLeft :size="16" class="group-hover:-translate-x-1 transition-transform" /> 
                    Historial de Misiones
                </Link>

                <button @click="manualRefresh" :disabled="isLoading" 
                        class="flex items-center gap-2 px-4 py-2 bg-foreground/5 border border-border/10 rounded-full text-foreground/60 hover:text-primary hover:bg-primary/5 transition-all active:scale-95 disabled:opacity-30">
                    <span class="text-[10px] font-black uppercase tracking-widest">Sincronizar</span>
                    <RefreshCw :size="14" :class="{ 'animate-spin': isLoading }" />
                </button>
            </div>

            <div class="product-card glass-titanium !rounded-3xl border border-border/10 overflow-hidden relative shadow-f1-glow">
                <div class="h-1.5 w-full bg-info/20">
                    <div class="h-full bg-info shadow-[0_0_15px_rgba(var(--info-rgb),0.5)] animate-pulse-fast w-full"></div>
                </div>

                <div class="p-8 md:p-12 text-center">
                    <div class="relative w-28 h-28 mx-auto mb-8">
                        <div class="absolute inset-0 bg-info/10 rounded-3xl blur-2xl animate-pulse"></div>
                        <div class="relative w-full h-full bg-background/40 border border-info/20 rounded-3xl flex items-center justify-center shadow-inner">
                            <Clock :size="48" class="text-info animate-spin-slow" />
                        </div>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-black italic uppercase tracking-tighter text-foreground mb-3 leading-none">
                        Validación en Curso
                    </h1>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-foreground/5 rounded-lg border border-border/10 mb-12">
                        <span class="w-2 h-2 rounded-full bg-info animate-ping"></span>
                        <p class="text-xs font-mono font-black uppercase tracking-[0.2em] text-foreground/40">
                            ORD_REF: {{ order.code }}
                        </p>
                    </div>

                    <div class="product-card bg-background/40 border border-border/10 p-6 md:p-8 text-left max-w-lg mx-auto mb-10 relative group">
                        <div class="flex items-start gap-5">
                            <div class="w-12 h-12 rounded-2xl bg-info/10 flex items-center justify-center text-info shrink-0">
                                <Receipt :size="22" />
                            </div>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-widest text-foreground mb-2">Comprobante Encolado</h3>
                                <p class="text-xs font-bold text-foreground/50 leading-relaxed uppercase tracking-tight">
                                    Tu documento financiero ha sido inyectado al sistema y se encuentra en espera de validación humana. Este proceso suele tomar <span class="text-foreground font-black">5-15 minutos</span> en horario de despacho.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-border/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 max-w-lg mx-auto font-mono">
                        <div class="flex items-center gap-2 opacity-40">
                            <ShieldCheck :size="14" />
                            <span class="text-[10px] font-black uppercase tracking-widest">Integridad Verificada</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-[10px] font-black text-foreground/30 uppercase">Liquidación:</span>
                            <span class="text-2xl font-black text-foreground tracking-tighter italic">
                                <span class="text-sm text-primary mr-1 not-italic font-sans">Bs</span>{{ Number(order.total_amount).toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-center gap-3 text-foreground/30">
                <Info :size="14" />
                <p class="text-[10px] font-black uppercase tracking-widest leading-none">Recibirás una notificación táctica cuando el hardware sea liberado.</p>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.glass-titanium {
    background: linear-gradient(135deg, hsl(var(--card) / 0.8) 0%, hsl(var(--card) / 0.4) 100%);
    backdrop-filter: blur(40px) saturate(200%);
}
.shadow-f1-glow { box-shadow: 0 0 40px -10px hsl(var(--info) / 0.2); }
.animate-spin-slow { animation: spin 6s linear infinite; }
.animate-pulse-fast { animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
.product-card { animation: reveal 0.8s cubic-bezier(0.22, 1, 0.36, 1) backwards; }
@keyframes reveal { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
</style>