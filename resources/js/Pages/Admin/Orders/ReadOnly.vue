<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue'; 
import { ArrowLeft, Archive, CheckCircle2, XCircle } from 'lucide-vue-next';

const props = defineProps({ order: Object });

// Función de utilidad para evitar errores de tipo y centralizar el formato
const formatMoney = (value) => {
    const num = parseFloat(value);
    return isNaN(num) ? '0.00' : num.toFixed(2);
};

const getStatusColor = (status) => {
    const map = {
        'dispatched': 'text-purple-500',
        'delivered': 'text-emerald-500',
        'completed': 'text-emerald-600',
        'cancelled': 'text-destructive',
        'returned': 'text-orange-500',
        'expired': 'text-destructive'
    };
    return map[status] || 'text-muted-foreground';
};
</script>

<template>
    <AdminLayout>
        <Head :title="'Auditoría ' + order.code" />
        <div class="p-6 max-w-4xl mx-auto">
            <Link :href="route('admin.orders.index')" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-muted-foreground hover:text-foreground mb-6 transition-colors">
                <ArrowLeft :size="16" /> Monitor Logístico
            </Link>

            <div class="bg-card border-2 border-border rounded-3xl p-8 shadow-xl">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h1 class="text-4xl font-black uppercase italic tracking-tighter text-foreground leading-none">
                            <span class="text-primary">#</span>{{ order.code }}
                        </h1>
                        <p class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] mt-2">
                            SOLICITANTE: {{ order.customer?.full_name || 'DESCONOCIDO' }}
                        </p>
                    </div>
                    <div class="px-5 py-2 rounded-xl border-2 font-black uppercase text-[10px] tracking-widest bg-muted/50" :class="getStatusColor(order.status)">
                        {{ order.status.replace('_', ' ') }}
                    </div>
                </div>

                <div class="flex items-center gap-3 mb-6 border-t border-border pt-8">
                    <Archive class="text-muted-foreground" :size="20" />
                    <h2 class="text-sm font-black uppercase tracking-widest text-foreground">Registro Inmutable de Auditoría</h2>
                </div>

                <div class="space-y-3 mb-8 bg-muted/30 p-6 rounded-3xl border border-border/50">
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center text-xs font-bold uppercase">
                        <div class="flex flex-col">
                            <span class="text-foreground tracking-tight">{{ item.product_name }}</span>
                            <span class="text-[9px] text-muted-foreground opacity-70">Cantidad: {{ item.quantity }} Unidades</span>
                        </div>
                        <span class="font-mono text-foreground bg-foreground/5 px-3 py-1 rounded-lg">
                            Bs {{ formatMoney(item.subtotal) }}
                        </span>
                    </div>

                    <div class="pt-6 mt-6 border-t-2 border-dashed border-border flex justify-between items-center">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Total Liquidación</span>
                            <span class="text-[9px] text-primary font-bold uppercase italic">{{ order.payment_method }} confirmation</span>
                        </div>
                        <span class="text-2xl font-mono font-black text-foreground">Bs {{ formatMoney(order.total_amount) }}</span>
                    </div>
                </div>

                <div class="text-center p-5 bg-muted/50 rounded-2xl border-2 border-dotted border-border group">
                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground group-hover:text-primary transition-colors">
                        Este registro ha sido sellado criptográficamente y no admite alteraciones manuales.
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>