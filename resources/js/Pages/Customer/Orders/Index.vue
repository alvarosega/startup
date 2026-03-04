<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { 
    Package, Calendar, ChevronRight, SearchX, 
    Clock, Receipt, CheckCircle2, Truck, XCircle, AlertTriangle 
} from 'lucide-vue-next';

defineProps({
    orders: Object 
});

const statusMap = {
    pending_payment: { 
        label: 'Pagar Ahora', 
        classes: 'bg-warning/10 text-warning border-warning/20 animate-pulse', 
        icon: AlertTriangle 
    },
    under_review: { 
        label: 'Validando Pago', 
        classes: 'bg-primary/10 text-primary border-primary/20', 
        icon: Receipt 
    },
    preparing: { 
        label: 'Preparando', 
        classes: 'bg-primary/10 text-primary border-primary/20', 
        icon: Package 
    },
    dispatched: { 
        label: 'En Camino', 
        classes: 'bg-purple-500/10 text-purple-500 border-purple-500/20', 
        icon: Truck 
    },
    completed: { 
        label: 'Entregado', 
        classes: 'bg-success/10 text-success border-success/20', 
        icon: CheckCircle2 
    },
    expired: { 
        label: 'Expirado', 
        classes: 'bg-destructive/10 text-destructive border-destructive/20', 
        icon: XCircle 
    },
    cancelled: { 
        label: 'Cancelado', 
        classes: 'bg-destructive/10 text-destructive border-destructive/20', 
        icon: XCircle 
    },
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-BO', { 
        year: 'numeric', month: 'short', day: 'numeric' 
    });
};
</script>

<template>
    <ShopLayout>
        <Head title="Mis Pedidos" />

        <div class="container mx-auto px-4 py-8 pb-32 lg:pb-12 min-h-[80vh] max-w-3xl">
            
            <div class="flex items-center gap-4 mb-8">
                <div class="p-3 bg-primary/10 rounded-2xl text-primary border border-primary/20">
                    <Package :size="24" stroke-width="2.5" />
                </div>
                <div>
                    <h1 class="font-display font-black text-3xl text-foreground tracking-tight leading-none italic">
                        Mis <span class="text-primary">Pedidos</span>
                    </h1>
                    <p class="text-xs text-muted-foreground font-medium mt-1">Historial de compras</p>
                </div>
            </div>

            <div v-if="orders.data.length > 0" class="space-y-4">
                
                <div v-for="order in orders.data" :key="order.id" 
                     class="group relative bg-card rounded-2xl border transition-all duration-300 overflow-hidden hover:shadow-lg active:scale-[0.99]"
                     :class="order.status === 'pending_payment' 
                        ? 'border-warning/50 shadow-[0_0_15px_rgba(var(--warning-rgb),0.15)]' 
                        : 'border-border hover:border-primary/30'">
                    
                    <Link :href="route('customer.orders.show', order.id)" class="block p-5">
                        
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="font-mono text-xs text-muted-foreground">ID PEDIDO</span>
                                <h3 class="font-black text-lg text-foreground tracking-tight">#{{ order.code }}</h3>
                            </div>
                            
                            <div class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border flex items-center gap-1.5"
                                 :class="statusMap[order.status]?.classes || 'bg-muted text-muted-foreground'">
                                <component :is="statusMap[order.status]?.icon || Package" :size="12" stroke-width="3" />
                                {{ statusMap[order.status]?.label || order.status }}
                            </div>
                        </div>

                        <div class="flex items-center gap-4 text-xs text-muted-foreground font-medium mb-4">
                            <div class="flex items-center gap-1.5 bg-muted/30 px-2 py-1 rounded-lg">
                                <Calendar :size="14"/> {{ formatDate(order.created_at) }}
                            </div>
                            <div class="w-1 h-1 bg-border rounded-full"></div>
                            <span>{{ order.items_count }} Items</span>
                        </div>

                        <div class="flex items-center justify-between border-t border-border/50 pt-4 mt-2">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-wider">Total</p>
                                <p class="text-xl font-black text-foreground">Bs {{ parseFloat(order.total_amount).toFixed(2) }}</p>
                            </div>
                            
                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300"
                                 :class="order.status === 'pending_payment' 
                                    ? 'bg-warning text-warning-foreground shadow-lg shadow-warning/20' 
                                    : 'bg-muted text-muted-foreground group-hover:bg-primary group-hover:text-primary-foreground'">
                                <ChevronRight :size="20" stroke-width="3" />
                            </div>
                        </div>
                    </Link>

                    <div v-if="order.status === 'pending_payment'" class="bg-warning/10 px-5 py-3 border-t border-warning/20 flex justify-between items-center">
                        <span class="text-[10px] font-bold text-warning flex items-center gap-1.5 uppercase tracking-wider">
                            <Clock :size="12" /> Reserva expira pronto
                        </span>
                        <Link :href="route('customer.orders.show', order.id)" class="text-xs font-black text-warning hover:text-warning/80 flex items-center gap-1">
                            Subir Comprobante →
                        </Link>
                    </div>

                    <div v-else-if="order.status === 'under_review' && order.rejection_reason" class="bg-destructive/10 px-5 py-3 border-t border-destructive/20 flex flex-col gap-1">
                         <span class="text-[10px] font-bold text-destructive uppercase tracking-wider">Pago Anterior Rechazado:</span>
                         <span class="text-xs text-destructive-foreground line-clamp-1">{{ order.rejection_reason }}</span>
                    </div>

                    <div v-if="['preparing', 'dispatched'].includes(order.status)" class="px-5 py-4 bg-muted/20 border-t border-border/50 flex flex-col md:flex-row justify-between items-center gap-3">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                                    <Truck :size="20" />
                                </div>
                                <div v-if="order.driver_id" class="absolute -bottom-1 -right-1 w-4 h-4 bg-success border-2 border-card rounded-full animate-pulse"></div>
                            </div>
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-widest text-foreground">
                                    {{ order.driver_id ? 'Unidad Asignada' : 'Buscando Conductor...' }}
                                </p>
                                <p class="text-[10px] text-muted-foreground font-medium">
                                    {{ order.status === 'preparing' ? 'Preparando en sucursal' : 'Tu pedido está en camino' }}
                                </p>
                            </div>
                        </div>

                        <Link v-if="order.driver_id" :href="route('customer.orders.track', order.id)" 
                              class="w-full md:w-auto bg-foreground text-background px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary hover:text-primary-foreground transition-all flex items-center justify-center gap-2 shadow-sm">
                            Rastrear en Mapa <ChevronRight :size="14" stroke-width="3" />
                        </Link>
                    </div>

                </div>

                <div v-if="orders.links && orders.links.length > 3" class="flex justify-center mt-8 gap-1.5 overflow-x-auto py-2">
                    <Link v-for="(link, k) in orders.links" :key="k" 
                          :href="link.url || '#'" 
                          v-html="link.label"
                          class="px-4 py-2.5 text-xs rounded-xl border transition-all font-bold min-w-[40px] text-center"
                          :class="link.active 
                            ? 'bg-primary text-primary-foreground border-primary shadow-md' 
                            : 'bg-card text-muted-foreground border-border hover:bg-muted hover:text-foreground' + (!link.url ? ' opacity-40 pointer-events-none' : '')"
                    />
                </div>

            </div>

            <div v-else class="flex flex-col items-center justify-center py-20 text-center opacity-0 animate-in fade-in slide-in-from-bottom-4 duration-700 fill-mode-forwards">
                <div class="w-32 h-32 bg-muted/20 rounded-full flex items-center justify-center mb-6 border border-border shadow-inner">
                    <SearchX class="text-muted-foreground/50" :size="48" stroke-width="1.5" />
                </div>
                <h3 class="text-xl font-black text-foreground mb-2">No tienes pedidos</h3>
                <p class="text-sm text-muted-foreground mb-8 max-w-xs mx-auto leading-relaxed">
                    Aún no has realizado ninguna compra. Explora nuestro catálogo y equipa tu evento.
                </p>
                <Link :href="route('customer.shop.index')" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-primary-foreground px-8 py-3 rounded-full font-bold text-sm shadow-lg shadow-primary/20 transition-all hover:scale-105 active:scale-95">
                    Ir a la Tienda
                </Link>
            </div>

        </div>
    </ShopLayout>
</template>