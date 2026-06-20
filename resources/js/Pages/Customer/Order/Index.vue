<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { 
    Package, Calendar, ChevronRight, ChevronLeft, FileText,
    Clock, Receipt, CheckCircle2, Truck, XCircle, AlertTriangle, CreditCard, Store 
} from 'lucide-vue-next';

defineProps({
    orders: Object // Soporta estado undefined por Inertia::defer
});

// MAPEO TÁCTICO DE ESTADOS (Usando variables semánticas)
const statusMap = {
    pending: { label: 'Esperando Pago', classes: 'bg-warning/10 text-warning border-warning/30 animate-pulse', icon: CreditCard },
    payment_pending: { label: 'Validando Pago', classes: 'bg-info/10 text-info border-info/30', icon: Receipt },
    rejected: { label: 'Pago Rechazado', classes: 'bg-destructive/10 text-destructive border-destructive/30', icon: AlertTriangle },
    confirmed: { label: 'Confirmado', classes: 'bg-success/10 text-success border-success/30', icon: CheckCircle2 },
    preparing: { label: 'Preparando', classes: 'bg-primary/10 text-primary border-primary/30', icon: Package },
    ready_for_dispatch: { label: 'Validado', classes: 'bg-primary/10 text-primary border-primary/30', icon: Package },
    dispatched: { label: 'En Camino', classes: 'bg-info/10 text-info border-info/30', icon: Truck },
    arrived: { label: 'En Destino', classes: 'bg-primary/10 text-primary border-primary/30 animate-pulse', icon: Truck },
    delivered: { label: 'Entregado', classes: 'bg-success/10 text-success border-success/30', icon: CheckCircle2 },
    cancelled: { label: 'Cancelado', classes: 'bg-destructive/10 text-destructive border-destructive/30 opacity-70', icon: XCircle },
    returned: { label: 'Devuelto', classes: 'bg-destructive/10 text-destructive border-destructive/30 opacity-70', icon: XCircle },
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-BO', { year: 'numeric', month: 'short', day: 'numeric' }) + 
           ' • ' + 
           date.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <ShopLayout :isProfileSection="true">
        <Head title="Registro de Misiones Operativas" />

        <div class="max-w-4xl mx-auto min-h-[75vh] py-8 pb-32 px-4 lg:px-0">
            
            <div class="flex items-center justify-between mb-10 pb-6 border-b border-border/20">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-foreground/5 rounded-2xl flex items-center justify-center text-primary border border-border/40 shadow-inner">
                        <FileText :size="28" stroke-width="2.5" />
                    </div>
                    <div>
                        <h1 class="font-black text-3xl md:text-4xl text-foreground uppercase tracking-tighter italic leading-none">
                            Historial <span class="text-primary">Operativo</span>
                        </h1>
                        <p class="text-[10px] md:text-xs text-foreground/40 font-mono font-black tracking-[0.3em] uppercase mt-2">
                            Registro de Transacciones y Logística
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="orders === undefined" class="space-y-6">
                <div v-for="i in 4" :key="i" class="product-card h-40 skeleton !rounded-3xl"></div>
            </div>

            <div v-else-if="orders.data && orders.data.length > 0" class="space-y-6">
                <Link v-for="order in orders.data" :key="order.id" :href="route('customer.order.show', order.code)"
                      class="block group product-card glass-manifest !rounded-3xl p-6 md:p-8 border-2 border-transparent transition-all duration-500 hover:-translate-y-1 hover:border-primary/40 active:scale-[0.98]"
                      :class="{ 'border-primary/30 bg-primary/5 shadow-f1-glow': ['pending', 'payment_pending', 'arrived'].includes(order.status) }">
                    
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        <div class="space-y-4 flex-1">
                            <div class="flex flex-wrap items-center gap-4">
                                <div class="flex flex-col">
                                    <span class="font-mono text-[10px] font-black text-foreground/40 uppercase tracking-widest mb-0.5">Orden REF</span>
                                    <h3 class="font-black text-2xl md:text-3xl text-foreground tracking-tighter italic leading-none">
                                        {{ order.code }}
                                    </h3>
                                </div>
                                <div class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest border flex items-center gap-2"
                                     :class="statusMap[order.status]?.classes || 'bg-foreground/5 text-foreground/50 border-border/40'">
                                    <component :is="statusMap[order.status]?.icon || Package" :size="14" stroke-width="2.5" />
                                    {{ statusMap[order.status]?.label || order.status }}
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 text-xs text-foreground/60 font-bold uppercase tracking-widest">
                                <div class="flex items-center gap-2 bg-foreground/5 px-3 py-1.5 rounded-lg border border-border/20 shadow-inner">
                                    <Calendar :size="14" class="text-primary"/> {{ formatDate(order.created_at) }}
                                </div>
                                <div class="flex items-center gap-2 bg-foreground/5 px-3 py-1.5 rounded-lg border border-border/20 shadow-inner">
                                    <component :is="order.delivery_type === 'pickup' ? Store : Truck" :size="14" class="text-primary"/> 
                                    {{ order.delivery_type === 'pickup' ? 'Retiro Local' : 'Despacho a Domicilio' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex w-full md:w-auto items-end md:items-center justify-between md:justify-end gap-6 border-t md:border-t-0 border-border/20 pt-4 md:pt-0">
                            <div class="flex flex-col text-left md:text-right">
                                <span class="text-[10px] uppercase font-black text-foreground/40 tracking-[0.2em] mb-1">Liquidación Final</span>
                                <span class="text-3xl font-black text-foreground font-mono tracking-tighter leading-none italic">
                                    <span class="text-sm mr-1 text-primary not-italic font-sans">Bs</span>{{ Number(order.total_amount).toFixed(2) }}
                                </span>
                            </div>
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-500 border border-border/40"
                                 :class="['pending', 'payment_pending', 'arrived'].includes(order.status) 
                                     ? 'bg-primary text-primary-foreground shadow-[0_0_15px_rgba(var(--primary-rgb),0.4)]' 
                                     : 'bg-foreground/5 text-foreground/40 group-hover:bg-primary group-hover:text-primary-foreground group-hover:border-primary'">
                                <ChevronRight :size="24" stroke-width="2.5" class="group-hover:translate-x-1 transition-transform" />
                            </div>
                        </div>
                    </div>
                </Link>

                <div class="flex items-center justify-center gap-4 pt-10 pb-4">
                    <Link v-if="orders.prev_page_url" :href="orders.prev_page_url" 
                          class="flex items-center gap-2 bg-foreground/5 border border-border/40 px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-[0.2em] text-foreground hover:bg-primary/10 hover:text-primary transition-all active:scale-95 shadow-sm">
                        <ChevronLeft :size="16" stroke-width="3" /> Recientes
                    </Link>
                    <span v-else class="flex items-center gap-2 bg-background border border-border/20 px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-[0.2em] text-foreground/20 cursor-not-allowed">
                        <ChevronLeft :size="16" stroke-width="3" /> Recientes
                    </span>

                    <Link v-if="orders.next_page_url" :href="orders.next_page_url" 
                          class="flex items-center gap-2 bg-foreground/5 border border-border/40 px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-[0.2em] text-foreground hover:bg-primary/10 hover:text-primary transition-all active:scale-95 shadow-sm">
                        Anteriores <ChevronRight :size="16" stroke-width="3" />
                    </Link>
                    <span v-else class="flex items-center gap-2 bg-background border border-border/20 px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-[0.2em] text-foreground/20 cursor-not-allowed">
                        Anteriores <ChevronRight :size="16" stroke-width="3" />
                    </span>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-32 text-center animate-in fade-in slide-in-from-bottom-4 duration-700 product-card glass-manifest border-dashed border-2">
                <div class="w-24 h-24 bg-foreground/5 rounded-full flex items-center justify-center mx-auto border border-border/40 mb-6">
                    <FileText :size="48" stroke-width="1.5" class="text-foreground/20" />
                </div>
                <h3 class="text-2xl font-black text-foreground mb-3 uppercase tracking-tighter italic">
                    Registro <span class="text-primary">Vacío</span>
                </h3>
                <p class="text-xs text-foreground/50 mb-10 max-w-sm mx-auto leading-relaxed font-bold tracking-wide">
                    No existen operaciones logísticas en su cuenta. Inicie una nueva requisición de hardware.
                </p>
                <Link :href="route('customer.index')" 
                      class="inline-flex items-center gap-3 bg-foreground text-background dark:bg-primary dark:text-primary-foreground px-8 py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-apple-soft transition-all hover:-translate-y-1 active:scale-95">
                    Terminal Central <ChevronRight :size="18" stroke-width="3"/>
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.glass-manifest {
    background-color: hsl(var(--card) / 0.5);
    backdrop-filter: blur(40px) saturate(200%);
    border-color: hsl(var(--border) / 0.4);
}
.dark .glass-manifest { background-color: hsl(var(--card) / 0.8); }
.shadow-f1-glow { box-shadow: 0 0 40px -10px hsl(var(--primary) / 0.2); }
</style>