<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue'; 
import { 
    Search, CheckCircle, Clock, Truck, 
    Store, User, ArrowRight, Package, Eye
} from 'lucide-vue-next';

const props = defineProps({
    orders: Object
});

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
                                         'bg-amber-500/10 text-amber-600 border-amber-500/20': order.status === 'pending',
                                         'bg-primary/10 text-primary border-primary/20 animate-pulse': order.status === 'payment_pending',
                                         'bg-blue-500/10 text-blue-500 border-blue-500/20': order.status === 'preparing',
                                         'bg-indigo-500/10 text-indigo-500 border-indigo-500/20': order.status === 'ready_for_dispatch',
                                         'bg-purple-500/10 text-purple-600 border-purple-500/20': order.status === 'dispatched',
                                         'bg-emerald-500/10 text-emerald-600 border-emerald-500/20': ['delivered', 'completed'].includes(order.status),
                                         'bg-f1-red/10 text-f1-red border-f1-red/20': ['cancelled', 'expired', 'returned'].includes(order.status)
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

                            <td class="px-6 py-5 text-right flex justify-end">
                                <Link v-if="order.status === 'payment_pending'" 
                                    :href="route('admin.orders.show', order.code)" 
                                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-primary-foreground px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-primary/20 transition-all active:scale-95">
                                    <CheckCircle :size="14"/> Auditar Pago
                                </Link>

                                <Link v-else-if="order.status === 'preparing'" 
                                    :href="route('admin.orders.show', order.code)"
                                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-500/20 transition-all active:scale-95">
                                    <Package :size="14"/> Preparar Pack
                                </Link>

                                <Link v-else-if="order.status === 'ready_for_dispatch'" 
                                    :href="route('admin.orders.show', order.code)"
                                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-indigo-500/20 transition-all active:scale-95">
                                    <ArrowRight :size="14"/> Ver Manifiesto
                                </Link>

                                <Link v-else
                                    :href="route('admin.orders.show', order.code)"
                                    class="inline-flex items-center gap-2 bg-muted hover:bg-muted-foreground/10 text-muted-foreground px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 border border-border">
                                    <Eye :size="14"/> Ver Detalles
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>