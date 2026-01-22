<script setup>
    import { ref, computed } from 'vue';
    import { router, useForm } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Eye, Check, X, Truck, Package, RefreshCw, AlertTriangle } from 'lucide-vue-next';
    
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
    
    // Formato de Moneda
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
                                     class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 cursor-pointer hover:shadow-md hover:-translate-y-1 transition-all duration-200 group">
                                    
                                    <div class="flex justify-between items-start mb-3">
                                        <span class="font-bold text-gray-800 text-sm">#{{ order.code }}</span>
                                        <span class="text-[10px] font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded">
                                            {{ new Date(order.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
                                            {{ order.user?.profile?.first_name?.[0] || 'C' }}
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-xs font-bold text-gray-700 truncate">{{ order.user?.profile?.first_name || 'Cliente' }}</p>
                                            <p class="text-[10px] text-gray-400 truncate">{{ order.delivery_data?.address }}</p>
                                        </div>
                                    </div>
    
                                    <div class="flex justify-between items-center pt-3 border-t border-gray-50">
                                        <span class="font-black text-green-600 text-sm">{{ money(order.total_amount) }}</span>
                                        <Eye :size="16" class="text-gray-300 group-hover:text-blue-500 transition-colors" />
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
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <div>
                            <h2 class="text-lg font-black text-gray-800">Orden #{{ selectedOrder.code }}</h2>
                            <p class="text-xs text-gray-500">{{ selectedOrder.user?.phone }} — {{ selectedOrder.delivery_data?.address }}</p>
                        </div>
                        <button @click="selectedOrder = null" class="p-2 hover:bg-gray-200 rounded-full transition"><X :size="20" class="text-gray-500"/></button>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Comprobante de Pago</h3>
                                <div v-if="selectedOrder.proof_of_payment" class="rounded-xl overflow-hidden border border-gray-200 shadow-sm group relative">
                                    <img :src="`/storage/${selectedOrder.proof_of_payment}`" class="w-full h-auto object-contain bg-gray-100">
                                    <a :href="`/storage/${selectedOrder.proof_of_payment}`" target="_blank" class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-white font-bold text-xs">
                                        Abrir Original
                                    </a>
                                </div>
                                <div v-else class="h-40 bg-gray-50 rounded-xl border border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400">
                                    <AlertTriangle :size="24" class="mb-2"/>
                                    <span class="text-xs">Sin comprobante adjunto</span>
                                </div>
                            </div>
    
                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Detalle del Pedido</h3>
                                <div class="space-y-3 mb-6">
                                    <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between text-sm">
                                        <div>
                                            <p class="font-bold text-gray-700">{{ item.sku?.product?.name }}</p>
                                            <p class="text-xs text-gray-400">{{ item.sku?.name }} x {{ item.quantity }}</p>
                                        </div>
                                        <span class="font-medium text-gray-600">{{ money(item.subtotal) }}</span>
                                    </div>
                                </div>
                                <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
                                    <span class="font-bold text-gray-800">Total a Pagar</span>
                                    <span class="text-xl font-black text-blue-600">{{ money(selectedOrder.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
    
                        <div v-if="showRejectForm" class="mt-6 bg-red-50 p-4 rounded-xl border border-red-100 animate-in fade-in slide-in-from-top-2">
                            <label class="block text-xs font-bold text-red-700 mb-2">Motivo del Rechazo:</label>
                            <textarea v-model="rejectReason" class="w-full text-sm border-red-200 rounded-lg focus:ring-red-500 focus:border-red-500 bg-white" rows="2" placeholder="Ej: Comprobante ilegible, monto incorrecto..."></textarea>
                            <div class="flex justify-end gap-2 mt-3">
                                <button @click="showRejectForm = false" class="text-xs font-bold text-gray-500 hover:text-gray-700 px-3 py-2">Cancelar</button>
                                <button @click="changeStatus('cancelled')" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-2 rounded-lg">Confirmar Rechazo</button>
                            </div>
                        </div>
                    </div>
    
                    <div v-if="!showRejectForm" class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                        
                        <template v-if="selectedOrder.status === 'review'">
                            <button @click="showRejectForm = true" 
                                    class="px-5 py-2.5 bg-white border border-red-200 text-red-600 rounded-xl text-sm font-bold hover:bg-red-50 transition">
                                Rechazar Pago
                            </button>
                            <button @click="changeStatus('confirmed')" 
                                    class="px-6 py-2.5 bg-green-600 text-white rounded-xl text-sm font-bold hover:bg-green-700 shadow-lg shadow-green-600/20 flex items-center gap-2 transition active:scale-95">
                                <Check :size="18"/> Aprobar Pago
                            </button>
                        </template>
    
                        <template v-if="selectedOrder.status === 'confirmed'">
                            <button @click="changeStatus('dispatched')" 
                                    class="px-6 py-2.5 bg-purple-600 text-white rounded-xl text-sm font-bold hover:bg-purple-700 shadow-lg shadow-purple-600/20 flex items-center gap-2 transition active:scale-95">
                                <Truck :size="18"/> Despachar Pedido
                            </button>
                        </template>
    
                        <template v-if="selectedOrder.status === 'dispatched'">
                            <button @click="changeStatus('completed')" 
                                    class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-lg shadow-blue-600/20 flex items-center gap-2 transition active:scale-95">
                                <Package :size="18"/> Confirmar Entrega
                            </button>
                        </template>
    
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