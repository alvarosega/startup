<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue'; 
import { ArrowLeft, Archive, CheckCircle2, XCircle } from 'lucide-vue-next';

const props = defineProps({ order: Object });

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
            <Link :href="route('admin.orders.index')" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-muted-foreground hover:text-foreground mb-6">
                <ArrowLeft :size="16" /> Monitor Logístico
            </Link>

            <div class="bg-card border-2 border-border rounded-3xl p-8 shadow-xl opacity-90">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h1 class="text-3xl font-black uppercase italic tracking-tighter text-foreground"><span class="text-primary">#</span>{{ order.code }}</h1>
                        <p class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] mt-1">{{ order.customer.name }}</p>
                    </div>
                    <div class="px-4 py-2 rounded-xl border-2 font-black uppercase text-[10px] tracking-widest bg-muted" :class="getStatusColor(order.status)">
                        ESTADO: {{ order.status.replace('_', ' ') }}
                    </div>
                </div>

                <div class="flex items-center gap-3 mb-6 border-t border-border pt-8">
                    <Archive class="text-muted-foreground" :size="20" />
                    <h2 class="text-sm font-black uppercase tracking-widest text-foreground">Registro Inmutable</h2>
                </div>

                <div class="space-y-2 mb-8 bg-muted p-6 rounded-2xl">
                    <div v-for="item in order.items" :key="item.name" class="flex justify-between text-xs font-bold uppercase text-muted-foreground">
                        <span>{{ item.quantity }}x {{ item.name }}</span>
                        <span class="font-mono">Bs {{ item.subtotal.toFixed(2) }}</span>
                    </div>
                    <div class="pt-4 mt-4 border-t border-border flex justify-between items-center">
                        <span class="text-[10px] font-black uppercase tracking-widest">Total Operación</span>
                        <span class="text-xl font-mono font-black text-foreground">Bs {{ order.total_amount.toFixed(2) }}</span>
                    </div>
                </div>

                <div class="text-center p-4 bg-muted/30 rounded-xl border border-border">
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Esta orden está archivada y no admite modificaciones.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>