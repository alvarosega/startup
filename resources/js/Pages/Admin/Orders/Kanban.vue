<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Eye, Check, Truck, Package, RefreshCw, 
    Store, MapPin, Phone, Calendar 
} from 'lucide-vue-next';

const props = defineProps({
    orders: Object // { status: [order1, order2...] }
});

const columns = [
    { id: 'review',        title: 'Verificar Pagos', color: 'bg-blue-50 border-blue-200 text-blue-700' },
    { id: 'confirmed',     title: 'Por Preparar',    color: 'bg-indigo-50 border-indigo-200 text-indigo-700' },
    { id: 'dispatched',    title: 'En Ruta',         color: 'bg-purple-50 border-purple-200 text-purple-700' },
    { id: 'completed',     title: 'Completados',     color: 'bg-green-50 border-green-200 text-green-700' }
];

const selectedOrder = ref(null);
const showRejectForm = ref(false);
const rejectReason = ref('');

// Función para mover estado
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

const money = (val) => `Bs ${parseFloat(val).toFixed(2)}`;
</script>

<template>
    <AdminLayout>
        <div class="h-[calc(100vh-100px)] flex flex-col p-6">
            
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Gestión de Pedidos</h1>
                    <p class="text-sm text-gray-500">Arrastra o haz clic para gestionar el flujo.</p>
                </div>
                <button @click="router.reload()" class="flex items-center gap-2 text-xs font-bold bg-white border border-gray-200 px-3 py-2 rounded-lg hover:bg-gray-50">
                    <RefreshCw :size="14"/> Actualizar
                </button>
            </div>

            <div class="flex-1 overflow-x-auto pb-4">
                <div class="flex h-full gap-5 min-w-max">
                    
                    <div v-for="col in columns" :key="col.id" class="w-80 flex flex-col rounded-xl bg-gray-100/50 border border-gray-200/60">
                        <div class="p-3 border-b border-gray-200/60 flex justify-between items-center" :class="col.color">
                            <span class="font-black text-xs uppercase tracking-wider">{{ col.title }}</span>
                            <span class="bg-white/40 px-2 py-0.5 rounded text-[10px] font-bold">{{ orders[col.id]?.length || 0 }}</span>
                        </div>

                        <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
                            <div v-for="order in orders[col.id]" :key="order.id" 
                                 @click="openModal(order)"
                                 class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 cursor-pointer hover:shadow-md hover:-translate-y-1 transition-all duration-200 group relative overflow-hidden">
                                
                                <div class="absolute left-0 top-0 bottom-0 w-1" 
                                     :class="order.delivery_type === 'delivery' ? 'bg-orange-400' : 'bg-blue-400'"></div>

                                <div class="flex justify-between items-start mb-2 pl-2">
                                    <span class="font-bold text-gray-800 text-sm">#{{ order.code }}</span>
                                    <span v-if="order.delivery_type === 'delivery'" class="text-[9px] font-bold uppercase bg-orange-50 text-orange-600 px-1.5 py-0.5 rounded border border-orange-100 flex items-center gap-1">
                                        <Truck :size="10" /> Delivery
                                    </span>
                                    <span v-else class="text-[9px] font-bold uppercase bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded border border-blue-100 flex items-center gap-1">
                                        <Store :size="10" /> Recojo
                                    </span>
                                </div>
                                
                                <div class="pl-2 mb-3">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-500">
                                            {{ order.user?.profile?.first_name?.[0] || 'C' }}
                                        </div>
                                        <p class="text-xs font-bold text-gray-700 truncate">{{ order.user?.profile?.first_name || 'Cliente' }}</p>
                                    </div>
                                    <p class="text-[10px] text-gray-400 flex items-center gap-1">
                                        <MapPin :size="10" />
                                        <span v-if="order.delivery_type === 'delivery'" class="truncate">{{ order.delivery_data?.address }}</span>
                                        <span v-else>Retira en Sucursal</span>
                                    </p>
                                </div>

                                <div class="flex justify-between items-center pt-2 border-t border-gray-50 pl-2">
                                    <span class="text-[10px] text-gray-400 flex items-center gap-1">
                                        <Calendar :size="10" /> {{ new Date(order.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                    </span>
                                    <span class="font-black text-green-600 text-xs">{{ money(order.total_amount) }}</span>
                                </div>
                            </div>
                            
                            <div v-if="!orders[col.id]?.length" class="text-center py-8 text-gray-300 text-xs italic">
                                Sin pedidos aquí
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div v-if="selectedOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click.self="selectedOrder = null">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
                
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div>
                            <h2 class="text-lg font-black text-gray-800">Orden #{{ selectedOrder.code }}</h2>
                            <p class="text-xs text-gray-500 flex items-center gap-2">
                                <span class="flex items-center gap-1"><Phone :size="12"/> {{ selectedOrder.delivery_data?.contact_phone }}</span>
                                <span>•</span>
                                <span>{{ new Date(selectedOrder.created_at).toLocaleString() }}</span>
                            </p>
                        </div>
                        <span v-if="selectedOrder.delivery_type === 'delivery'" class="px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-bold flex items-center gap-1">
                            <Truck :size="14" /> Delivery
                        </span>
                        <span v-else class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center gap-1">
                            <Store :size="14" /> Recojo
                        </span>
                    </div>
                    <button @click="selectedOrder = null" class="p-2 hover:bg-gray-200 rounded-full transition"><X :size="20" class="text-gray-500"/></button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <div class="space-y-6">
                            
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                    {{ selectedOrder.delivery_type === 'delivery' ? 'Datos de Envío' : 'Datos de Recojo' }}
                                </h3>
                                <div class="text-sm text-gray-700 space-y-1">
                                    <p v-if="selectedOrder.delivery_type === 'delivery'">
                                        <strong>Dirección:</strong> {{ selectedOrder.delivery_data?.address }}
                                    </p>
                                    <p v-if="selectedOrder.delivery_type === 'pickup'">
                                        <strong>Punto de Entrega:</strong> {{ selectedOrder.delivery_data?.address }} (Sucursal)
                                    </p>
                                    <p><strong>Referencia:</strong> {{ selectedOrder.delivery_data?.details || 'N/A' }}</p>
                                    <p><strong>Contacto:</strong> {{ selectedOrder.delivery_data?.contact_phone }}</p>
                                    
                                    <a v-if="selectedOrder.delivery_data?.coordinates" 
                                       :href="`https://www.google.com/maps/search/?api=1&query=${selectedOrder.delivery_data.coordinates.lat},${selectedOrder.delivery_data.coordinates.lng}`" 
                                       target="_blank"
                                       class="inline-flex items-center gap-1 text-blue-600 hover:underline text-xs mt-2 font-bold">
                                        <MapPin :size="12" /> Ver Ubicación en Mapa
                                    </a>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Comprobante de Pago</h3>
                                <div v-if="selectedOrder.proof_of_payment" class="rounded-xl overflow-hidden border border-gray-200 shadow-sm group relative h-64 bg-gray-100">
                                    <img :src="`/storage/${selectedOrder.proof_of_payment}`" class="w-full h-full object-contain">
                                    <a :href="`/storage/${selectedOrder.proof_of_payment}`" target="_blank" class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-white font-bold text-xs">
                                        Abrir Original
                                    </a>
                                </div>
                                <div v-else class="h-32 bg-gray-50 rounded-xl border border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400">
                                    <AlertTriangle :size="24" class="mb-2"/>
                                    <span class="text-xs">Sin comprobante</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Productos</h3>
                            <div class="space-y-3 mb-6 bg-white border border-gray-100 rounded-xl overflow-hidden">
                                <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between text-sm p-3 border-b border-gray-50 last:border-0">
                                    <div>
                                        <p class="font-bold text-gray-700">{{ item.sku?.product?.name }}</p>
                                        <p class="text-xs text-gray-400">{{ item.sku?.name }} x {{ item.quantity }}</p>
                                    </div>
                                    <span class="font-medium text-gray-600">{{ money(item.subtotal) }}</span>
                                </div>
                                <div class="p-3 bg-gray-50 flex justify-between items-center">
                                    <span class="font-bold text-gray-800">Total</span>
                                    <span class="text-lg font-black text-blue-600">{{ money(selectedOrder.total_amount) }}</span>
                                </div>
                            </div>

                            <div v-if="!showRejectForm" class="flex flex-col gap-3">
                                <template v-if="selectedOrder.status === 'review'">
                                    <button @click="changeStatus('confirmed')" class="w-full py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 flex justify-center items-center gap-2 shadow-lg shadow-green-200">
                                        <Check :size="18"/> Aprobar Pago y Stock
                                    </button>
                                    <button @click="showRejectForm = true" class="w-full py-3 bg-white border border-red-200 text-red-600 rounded-xl font-bold hover:bg-red-50">
                                        Rechazar Orden
                                    </button>
                                </template>

                                <template v-if="selectedOrder.status === 'confirmed'">
                                    <button @click="changeStatus('dispatched')" class="w-full py-3 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 flex justify-center items-center gap-2 shadow-lg shadow-purple-200">
                                        <Truck :size="18"/> Despachar Pedido
                                    </button>
                                </template>

                                <template v-if="selectedOrder.status === 'dispatched'">
                                    <button @click="changeStatus('completed')" class="w-full py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 flex justify-center items-center gap-2 shadow-lg shadow-blue-200">
                                        <Package :size="18"/> Confirmar Entrega Final
                                    </button>
                                </template>
                            </div>

                            <div v-if="showRejectForm" class="bg-red-50 p-4 rounded-xl border border-red-100 animate-in fade-in slide-in-from-bottom-2">
                                <label class="block text-xs font-bold text-red-700 mb-2">Motivo del Rechazo:</label>
                                <textarea v-model="rejectReason" class="w-full text-sm border-red-200 rounded-lg bg-white mb-3" rows="2"></textarea>
                                <div class="flex justify-end gap-2">
                                    <button @click="showRejectForm = false" class="text-xs font-bold text-gray-500 px-3 py-2">Cancelar</button>
                                    <button @click="changeStatus('cancelled')" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-2 rounded-lg">Confirmar</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
</style>