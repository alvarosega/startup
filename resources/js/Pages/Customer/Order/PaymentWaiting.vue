<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ArrowLeft, Clock, Receipt, RefreshCw } from 'lucide-vue-next';

const props = defineProps({ 
    order: Object 
});

const isLoading = ref(false);

const manualRefresh = () => {
    isLoading.value = true;
    router.reload({
        only: ['order'], // Solo pide el objeto order nuevo
        onFinish: () => { isLoading.value = false; }
    });
};
</script>

<template>
    <ShopLayout>
        <Head :title="'Validando Pago #' + order.code" />
        
        <div class="max-w-2xl mx-auto p-6 min-h-[80vh]">
            <div class="flex justify-between items-start mb-8">
                <Link :href="route('customer.order.index')" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-foreground transition-colors">
                    <ArrowLeft :size="14" /> Volver al historial
                </Link>

                <button @click="manualRefresh" :disabled="isLoading" class="p-3 bg-muted rounded-full text-foreground hover:bg-muted/80 transition-all active:scale-90 disabled:opacity-50">
                    <RefreshCw :size="16" :class="{ 'animate-spin': isLoading }" />
                </button>
            </div>

            <div class="bg-card border border-border rounded-[2.5rem] p-8 shadow-2xl overflow-hidden relative text-center pt-16">
                <div class="absolute top-0 left-0 w-full h-1 bg-blue-500 animate-pulse"></div>
                
                <div class="w-24 h-24 bg-blue-500/10 rounded-[2rem] flex items-center justify-center mx-auto mb-8 border border-blue-500/20 shadow-inner">
                    <Clock :size="40" class="text-blue-500 animate-spin-slow" />
                </div>

                <h1 class="text-3xl font-black italic uppercase tracking-tighter text-foreground mb-2">Validación en Curso</h1>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-muted-foreground mb-10">Orden #{{ order.code }}</p>

                <div class="bg-muted/30 border border-border rounded-[2rem] p-8 max-w-md mx-auto mb-8 text-left">
                    <div class="flex items-start gap-4">
                        <Receipt class="text-blue-500 shrink-0 mt-1" :size="24" />
                        <div>
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground mb-2">Comprobante Encolado</h3>
                            <p class="text-[10px] font-bold text-muted-foreground leading-relaxed uppercase">
                                Tu documento financiero se encuentra en la bandeja del departamento administrativo. Usa el botón de refresco arriba para comprobar si ya fue aprobado.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-border/50 pt-8 flex justify-between items-center max-w-md mx-auto">
                    <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Monto Sujeto a Revisión</span>
                    <span class="text-xl font-black text-foreground font-mono">Bs {{ Number(order.total_amount).toFixed(2) }}</span>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 4s linear infinite;
}
</style>