<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { 
    Package, Calendar, ChevronRight, ChevronLeft, FileText,
    Clock, Receipt, CheckCircle2, Truck, XCircle, AlertTriangle, CreditCard, Store 
} from 'lucide-vue-next';

defineProps({
    orders: Object
});

// Mapeo absoluto de los 11 estados de la base de datos
const statusMap = {
    pending: { label: 'Esperando Pago', classes: 'bg-amber-500/10 text-amber-500 border-amber-500/30 animate-pulse', icon: CreditCard },
    payment_pending: { label: 'Validando Pago', classes: 'bg-blue-400/10 text-blue-400 border-blue-400/30', icon: Receipt },
    rejected: { label: 'Pago Rechazado', classes: 'bg-red-500/10 text-red-500 border-red-500/30', icon: AlertTriangle },
    confirmed: { label: 'Confirmado', classes: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30', icon: CheckCircle2 },
    preparing: { label: 'Preparando', classes: 'bg-cyan-400/10 text-cyan-400 border-cyan-400/30', icon: Package },
    ready_for_dispatch: { label: 'Listo p/ Despacho', classes: 'bg-indigo-400/10 text-indigo-400 border-indigo-400/30', icon: Package },
    dispatched: { label: 'En Camino', classes: 'bg-purple-500/10 text-purple-500 border-purple-500/30', icon: Truck },
    arrived: { label: 'En Destino', classes: 'bg-fuchsia-500/10 text-fuchsia-500 border-fuchsia-500/30 animate-pulse', icon: Truck },
    delivered: { label: 'Entregado', classes: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30', icon: CheckCircle2 },
    cancelled: { label: 'Cancelado', classes: 'bg-f1-red/10 text-f1-red border-f1-red/30 opacity-70', icon: XCircle },
    returned: { label: 'Devuelto', classes: 'bg-orange-500/10 text-orange-500 border-orange-500/30 opacity-70', icon: XCircle },
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
        <Head title="Historial de Transacciones" />

        <div class="max-w-4xl mx-auto min-h-[70vh]">
            
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-white/10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center text-primary border border-white/10 shadow-inner">
                        <FileText :size="24" stroke-width="2" />
                    </div>
                    <div>
                        <h1 class="font-black text-2xl text-foreground uppercase tracking-tighter italic">
                            Historial <span class="text-primary">Operativo</span>
                        </h1>
                        <p class="text-[10px] text-foreground/40 font-mono tracking-widest uppercase mt-1">Registro de compras y logística</p>
                    </div>
                </div>
            </div>

            <div v-if="orders.data && orders.data.length > 0" class="space-y-4">
                <Link v-for="order in orders.data" :key="order.id" :href="route('customer.order.show', order.id)"
                      class="block group relative bg-surface/40 backdrop-blur-xl rounded-[24px] border transition-all duration-300 overflow-hidden hover:shadow-xl active:scale-[0.98]"
                      :class="['pending', 'arrived', 'rejected'].includes(order.status) 
                          ? 'border-primary/50 bg-primary/5' 
                          : 'border-white/10 hover:border-white/20'">
                    
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex flex-col">
                                <span class="font-mono text-[9px] text-foreground/40 uppercase tracking-widest mb-1">Orden de Servicio</span>
                                <h3 class="font-black text-xl text-foreground tracking-tighter italic">{{ order.code }}</h3>
                            </div>
                            
                            <div class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border flex items-center gap-1.5"
                                 :class="statusMap[order.status]?.classes || 'bg-white/5 text-foreground/50 border-white/10'">
                                <component :is="statusMap[order.status]?.icon || Package" :size="12" stroke-width="3" />
                                {{ statusMap[order.status]?.label || order.status }}
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-[10px] text-foreground/50 font-bold uppercase tracking-widest mb-6">
                            <div class="flex items-center gap-1.5 bg-black/20 px-2.5 py-1.5 rounded-lg border border-white/5">
                                <Calendar :size="12" class="text-primary"/> {{ formatDate(order.created_at) }}
                            </div>
                            <div class="flex items-center gap-1.5 bg-black/20 px-2.5 py-1.5 rounded-lg border border-white/5">
                                <component :is="order.delivery_type === 'pickup' ? Store : Truck" :size="12" class="text-primary"/> 
                                {{ order.delivery_type === 'pickup' ? 'Retiro Local' : 'Domicilio' }}
                            </div>
                        </div>

                        <div class="flex items-end justify-between border-t border-white/10 pt-5 mt-2">
                            <div class="flex flex-col">
                                <span class="text-[9px] uppercase font-black text-foreground/30 tracking-[0.2em] mb-1">Monto Total</span>
                                <span class="text-2xl font-black text-foreground font-mono tracking-tighter">
                                    <span class="text-sm mr-1 text-primary">Bs</span>{{ Number(order.total_amount).toFixed(2) }}
                                </span>
                            </div>
                            
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300"
                                 :class="['pending', 'arrived', 'rejected'].includes(order.status) 
                                     ? 'bg-primary text-white shadow-lg shadow-primary/30' 
                                     : 'bg-white/5 text-foreground/30 group-hover:bg-primary group-hover:text-white border border-white/5 group-hover:border-primary'">
                                <ChevronRight :size="24" stroke-width="2.5" />
                            </div>
                        </div>
                    </div>
                </Link>

                <div class="flex items-center justify-center gap-4 pt-8 pb-4">
                    <Link v-if="orders.prev_page_url" :href="orders.prev_page_url" 
                          class="flex items-center gap-2 bg-surface/40 border border-white/10 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-foreground hover:bg-white/5 transition-all">
                        <ChevronLeft :size="14" /> Recientes
                    </Link>
                    <span v-else class="flex items-center gap-2 border border-white/5 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-foreground/20 cursor-not-allowed">
                        <ChevronLeft :size="14" /> Recientes
                    </span>

                    <Link v-if="orders.next_page_url" :href="orders.next_page_url" 
                          class="flex items-center gap-2 bg-surface/40 border border-white/10 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-foreground hover:bg-white/5 transition-all">
                        Anteriores <ChevronRight :size="14" />
                    </Link>
                    <span v-else class="flex items-center gap-2 border border-white/5 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-foreground/20 cursor-not-allowed">
                        Anteriores <ChevronRight :size="14" />
                    </span>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-32 text-center animate-in fade-in slide-in-from-bottom-4 duration-700">
                <h3 class="text-2xl font-black text-foreground mb-2 uppercase tracking-tighter italic">Base de Datos <span class="text-primary">Vacía</span></h3>
                <p class="text-xs text-foreground/40 mb-10 max-w-sm mx-auto leading-relaxed font-bold">
                    No existen registros operativos en tu cuenta. Inicia una nueva requisición de productos.
                </p>
                <Link :href="route('customer.index')" class="inline-flex items-center gap-3 bg-primary text-white px-8 py-4 rounded-[20px] font-black text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-primary/20 transition-all hover:scale-105 active:scale-95">
                    Ir a la Terminal Central <ChevronRight :size="16" stroke-width="3"/>
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>