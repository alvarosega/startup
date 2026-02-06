<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Eye, Check, Truck, Package, RefreshCw, 
    Store, MapPin, Phone, Calendar, X,
    AlertTriangle, DollarSign, User, Clock
} from 'lucide-vue-next';

const props = defineProps({
    orders: Object // { status: [order1, order2...] }
});

const columns = [
    { id: 'review', title: 'Verificación', icon: Eye, color: 'border-blue-500', bg: 'bg-blue-500/5', text: 'text-blue-600' },
    { id: 'confirmed', title: 'Preparación', icon: Package, color: 'border-indigo-500', bg: 'bg-indigo-500/5', text: 'text-indigo-600' },
    { id: 'dispatched', title: 'En Ruta', icon: Truck, color: 'border-purple-500', bg: 'bg-purple-500/5', text: 'text-purple-600' },
    { id: 'completed', title: 'Historial', icon: Check, color: 'border-emerald-500', bg: 'bg-emerald-500/5', text: 'text-emerald-600' }
];

const selectedOrder = ref(null);
const showRejectForm = ref(false);
const rejectReason = ref('');

// Función para mover estado (Lógica intacta)
const changeStatus = (newStatus) => {
    if (newStatus === 'cancelled' && !rejectReason.value) {
        alert('Debes indicar una razón para el rechazo.');
        return;
    }

    router.patch(route('admin.orders.status', selectedOrder.value.id), {
        status: newStatus,
        rejection_reason: rejectReason.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selectedOrder.value = null;
            showRejectForm.value = false;
            rejectReason.value = '';
        }
    });
};

const openModal = (order) => {
    selectedOrder.value = order;
    showRejectForm.value = false;
    rejectReason.value = '';
};

const money = (val) => parseFloat(val).toFixed(2);
</script>

<template>
    <AdminLayout>
        <div class="h-[calc(100vh-80px)] flex flex-col pb-safe">
            
            <div class="px-4 py-4 md:px-8 border-b border-border bg-background/95 backdrop-blur-sm z-20 flex justify-between items-center shrink-0">
                <div>
                    <h1 class="text-2xl font-display font-black text-foreground tracking-tighter flex items-center gap-2">
                        Control de Pedidos
                    </h1>
                    <p class="text-xs text-muted-foreground font-medium hidden md:block">Flujo de despacho en tiempo real.</p>
                </div>
                <button @click="router.reload()" 
                        class="btn btn-outline btn-sm gap-2 border-primary/20 text-primary hover:bg-primary/5 hover:border-primary transition-all shadow-sm">
                    <RefreshCw :size="14" class="animate-spin-slow"/> 
                    <span class="hidden sm:inline">Sincronizar</span>
                </button>
            </div>

            <div class="flex-1 overflow-x-auto overflow-y-hidden bg-muted/10 p-4 md:p-6">
                <div class="flex h-full gap-4 min-w-max">
                    
                    <div v-for="col in columns" :key="col.id" 
                         class="w-[85vw] md:w-80 flex flex-col rounded-2xl border border-border bg-card shadow-sm snap-center h-full max-h-full">
                        
                        <div class="p-3 border-b border-border flex justify-between items-center shrink-0" 
                             :class="col.bg">
                            <div class="flex items-center gap-2">
                                <component :is="col.icon" :size="16" :class="col.text" stroke-width="2.5"/>
                                <span class="font-black text-xs uppercase tracking-wider text-foreground">{{ col.title }}</span>
                            </div>
                            <span class="bg-background border border-border px-2 py-0.5 rounded-md text-[10px] font-bold shadow-sm">
                                {{ orders[col.id]?.length || 0 }}
                            </span>
                        </div>

                        <div class="flex-1 overflow-y-auto p-3 space-y-3 scrollbar-thin">
                            
                            <div v-for="order in orders[col.id]" :key="order.id" 
                                 @click="openModal(order)"
                                 class="group relative bg-background rounded-xl p-4 border border-border shadow-sm cursor-pointer hover:shadow-md hover:border-primary/50 transition-all duration-300 active:scale-[0.98] overflow-hidden">
                                
                                <div class="absolute left-0 top-0 bottom-0 w-1 transition-colors duration-300" 
                                     :class="order.delivery_type === 'delivery' ? 'bg-orange-500' : 'bg-blue-500'"></div>

                                <div class="flex justify-between items-start mb-3 pl-2">
                                    <span class="font-mono font-bold text-xs text-muted-foreground group-hover:text-primary transition-colors">
                                        #{{ order.code }}
                                    </span>
                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded border flex items-center gap-1"
                                          :class="order.delivery_type === 'delivery' 
                                            ? 'bg-orange-50 text-orange-600 border-orange-100' 
                                            : 'bg-blue-50 text-blue-600 border-blue-100'">
                                        <component :is="order.delivery_type === 'delivery' ? Truck : Store" :size="10" />
                                        {{ order.delivery_type === 'delivery' ? 'Envío' : 'Recojo' }}
                                    </span>
                                </div>
                                
                                <div class="pl-2 mb-3">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 border border-gray-300 flex items-center justify-center text-[10px] font-bold text-gray-600">
                                            {{ order.user?.profile?.first_name?.[0] || 'C' }}
                                        </div>
                                        <p class="text-xs font-bold text-foreground truncate max-w-[120px]">
                                            {{ order.user?.profile?.first_name || 'Cliente' }}
                                        </p>
                                    </div>
                                    <p class="text-[10px] text-muted-foreground flex items-center gap-1 truncate">
                                        <MapPin :size="10" />
                                        <span class="truncate">
                                            {{ order.delivery_type === 'delivery' ? order.delivery_data?.address : 'Retira en Tienda' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="flex justify-between items-center pt-2 border-t border-border/50 pl-2">
                                    <span class="text-[10px] text-muted-foreground flex items-center gap-1 font-medium">
                                        <Clock :size="10" /> 
                                        {{ new Date(order.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                    </span>
                                    <div class="flex items-baseline text-foreground">
                                        <span class="text-[10px] mr-0.5">Bs</span>
                                        <span class="font-black text-sm">{{ money(order.total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="!orders[col.id]?.length" class="h-32 flex flex-col items-center justify-center text-muted-foreground/30 border-2 border-dashed border-border/50 rounded-xl">
                                <Package :size="24" class="mb-2 opacity-50"/>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Vacío</span>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        <Teleport to="body">
            <Transition enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0">
                <div v-if="selectedOrder" class="fixed inset-0 z-[9999] flex items-end md:items-center justify-center sm:p-4">
                    
                    <div class="absolute inset-0 bg-background/80 backdrop-blur-md" @click="selectedOrder = null"></div>

                    <div class="relative bg-card w-full max-w-2xl md:rounded-2xl rounded-t-2xl shadow-2xl border border-border flex flex-col max-h-[90vh] md:max-h-[85vh] animate-in slide-in-from-bottom-10 fade-in duration-300">
                        
                        <div class="px-5 py-4 border-b border-border flex justify-between items-center bg-muted/10 shrink-0">
                            <div class="flex flex-col gap-0.5">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-lg font-black text-foreground">Orden #{{ selectedOrder.code }}</h2>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider"
                                          :class="selectedOrder.delivery_type === 'delivery' ? 'bg-orange-100 text-orange-700 border-orange-200' : 'bg-blue-100 text-blue-700 border-blue-200'">
                                        {{ selectedOrder.delivery_type === 'delivery' ? 'Delivery' : 'Recojo' }}
                                    </span>
                                </div>
                                <p class="text-xs text-muted-foreground flex items-center gap-2">
                                    <span class="flex items-center gap-1"><Calendar :size="10"/> {{ new Date(selectedOrder.created_at).toLocaleString() }}</span>
                                </p>
                            </div>
                            <button @click="selectedOrder = null" class="btn btn-ghost btn-sm btn-square rounded-full">
                                <X :size="20" class="text-muted-foreground"/>
                            </button>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto p-5 md:p-6 custom-scrollbar">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                <div class="space-y-6">
                                    
                                    <div class="bg-muted/10 p-4 rounded-xl border border-border">
                                        <h3 class="text-[10px] font-black text-muted-foreground uppercase tracking-wider mb-3 flex items-center gap-1">
                                            <MapPin :size="12"/> {{ selectedOrder.delivery_type === 'delivery' ? 'Punto de Entrega' : 'Sucursal de Retiro' }}
                                        </h3>
                                        <div class="text-sm text-foreground space-y-2">
                                            <p class="font-medium">{{ selectedOrder.delivery_data?.address }}</p>
                                            <p class="text-xs text-muted-foreground" v-if="selectedOrder.delivery_data?.details">
                                                Ref: {{ selectedOrder.delivery_data?.details }}
                                            </p>
                                            
                                            <div class="flex items-center gap-2 pt-2">
                                                <a :href="`tel:${selectedOrder.delivery_data?.contact_phone}`" class="btn btn-xs btn-outline gap-1">
                                                    <Phone :size="12"/> {{ selectedOrder.delivery_data?.contact_phone }}
                                                </a>
                                                <a v-if="selectedOrder.delivery_data?.coordinates" 
                                                   :href="`https://www.google.com/maps/search/?api=1&query=${selectedOrder.delivery_data.coordinates.lat},${selectedOrder.delivery_data.coordinates.lng}`" 
                                                   target="_blank"
                                                   class="btn btn-xs btn-primary gap-1">
                                                    <MapPin :size="12"/> Mapa
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="text-[10px] font-black text-muted-foreground uppercase tracking-wider mb-2 flex items-center gap-1">
                                            <DollarSign :size="12"/> Comprobante
                                        </h3>
                                        <div v-if="selectedOrder.proof_of_payment" class="rounded-xl overflow-hidden border border-border shadow-sm group relative h-48 bg-black/5">
                                            <img :src="`/storage/${selectedOrder.proof_of_payment}`" class="w-full h-full object-contain">
                                            <a :href="`/storage/${selectedOrder.proof_of_payment}`" target="_blank" class="absolute inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300 text-white font-bold text-xs flex-col gap-1">
                                                <Eye :size="24"/> Ver Completo
                                            </a>
                                        </div>
                                        <div v-else class="h-24 bg-muted/20 rounded-xl border border-dashed border-border flex flex-col items-center justify-center text-muted-foreground">
                                            <AlertTriangle :size="20" class="mb-1 opacity-50"/>
                                            <span class="text-[10px] font-bold">Sin comprobante</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col h-full">
                                    <h3 class="text-[10px] font-black text-muted-foreground uppercase tracking-wider mb-3 flex items-center gap-1">
                                        <Package :size="12"/> Productos
                                    </h3>
                                    <div class="bg-card border border-border rounded-xl overflow-hidden mb-6 flex-1">
                                        <div class="max-h-[200px] overflow-y-auto scrollbar-thin">
                                            <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between items-center p-3 border-b border-border/50 last:border-0 hover:bg-muted/5 transition-colors">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-sm text-foreground">{{ item.sku?.product?.name }}</span>
                                                    <span class="text-[10px] text-muted-foreground font-mono">{{ item.sku?.name }} <span class="text-primary font-bold">x{{ item.quantity }}</span></span>
                                                </div>
                                                <span class="font-medium text-sm">Bs {{ money(item.subtotal) }}</span>
                                            </div>
                                        </div>
                                        <div class="p-3 bg-muted/20 border-t border-border flex justify-between items-center">
                                            <span class="text-xs font-bold uppercase text-muted-foreground">Total a Pagar</span>
                                            <span class="text-lg font-black text-primary">Bs {{ money(selectedOrder.total_amount) }}</span>
                                        </div>
                                    </div>

                                    <div v-if="!showRejectForm" class="flex flex-col gap-2 mt-auto">
                                        <template v-if="selectedOrder.status === 'review'">
                                            <button @click="changeStatus('confirmed')" class="btn btn-primary w-full shadow-lg shadow-primary/20">
                                                <Check :size="18" class="mr-2"/> Aprobar Pago
                                            </button>
                                            <button @click="showRejectForm = true" class="btn btn-outline border-error/30 text-error hover:bg-error/5 hover:border-error w-full">
                                                Rechazar
                                            </button>
                                        </template>

                                        <template v-if="selectedOrder.status === 'confirmed'">
                                            <button @click="changeStatus('dispatched')" class="btn btn-primary bg-purple-600 hover:bg-purple-700 w-full text-white border-none shadow-lg shadow-purple-500/20">
                                                <Truck :size="18" class="mr-2"/> Despachar
                                            </button>
                                        </template>

                                        <template v-if="selectedOrder.status === 'dispatched'">
                                            <button @click="changeStatus('completed')" class="btn btn-primary bg-emerald-600 hover:bg-emerald-700 w-full text-white border-none shadow-lg shadow-emerald-500/20">
                                                <Check :size="18" class="mr-2"/> Completar Entrega
                                            </button>
                                        </template>
                                    </div>

                                    <div v-if="showRejectForm" class="bg-error/5 p-4 rounded-xl border border-error/20 animate-in fade-in slide-in-from-bottom-2 mt-auto">
                                        <label class="block text-xs font-bold text-error mb-2">Motivo del Rechazo:</label>
                                        <textarea v-model="rejectReason" class="form-input w-full text-sm border-error/30 rounded-lg bg-white mb-3 min-h-[80px]" placeholder="Ej: Pago no recibido..."></textarea>
                                        <div class="flex justify-end gap-2">
                                            <button @click="showRejectForm = false" class="btn btn-xs btn-ghost text-muted-foreground">Cancelar</button>
                                            <button @click="changeStatus('cancelled')" class="btn btn-xs btn-error">Confirmar Rechazo</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
/* Scrollbar sutil para el modal y las columnas */
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: hsl(var(--muted-foreground)/0.2); border-radius: 10px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background: hsl(var(--muted-foreground)/0.4); }
</style>