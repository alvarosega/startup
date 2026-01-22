<script setup>
    import { ref } from 'vue';
    import { router } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Eye, Check, X, Truck, Package } from 'lucide-vue-next';
    
    const props = defineProps({
        orders: Object // Agrupadas por estado: { pending_proof: [], review: [], ... }
    });
    
    const columns = [
        { id: 'pending_proof', title: 'Esperando Foto', color: 'bg-yellow-50 border-yellow-200' },
        { id: 'review',        title: 'Por Revisar',    color: 'bg-blue-50 border-blue-200' },
        { id: 'confirmed',     title: 'En Preparación', color: 'bg-indigo-50 border-indigo-200' },
        { id: 'dispatched',    title: 'En Ruta',        color: 'bg-purple-50 border-purple-200' }
    ];
    
    const selectedOrder = ref(null); // Para el modal
    
    // Mover orden (Simple cambio de estado por botones para máxima compatibilidad)
    const changeStatus = (orderId, newStatus, reason = null) => {
        router.patch(route('admin.orders.status', orderId), {
            status: newStatus,
            rejection_reason: reason
        }, {
            preserveScroll: true,
            onSuccess: () => selectedOrder.value = null
        });
    };
    
    const openModal = (order) => selectedOrder.value = order;
    </script>
    
    <template>
        <AdminLayout>
            <div class="h-[calc(100vh-100px)] flex flex-col">
                <div class="flex justify-between items-center mb-4 px-4">
                    <h1 class="text-2xl font-black text-gray-800">Tablero Logístico</h1>
                    <button class="text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded font-bold" @click="router.reload()">
                        Actualizar
                    </button>
                </div>
    
                <div class="flex-1 overflow-x-auto overflow-y-hidden">
                    <div class="flex h-full gap-4 px-4 min-w-max">
                        
                        <div v-for="col in columns" :key="col.id" 
                             :class="['w-80 rounded-xl border flex flex-col', col.color]">
                            
                            <div class="p-3 font-bold text-gray-700 uppercase text-xs tracking-wider flex justify-between">
                                {{ col.title }}
                                <span class="bg-white/50 px-2 rounded">{{ orders[col.id]?.length || 0 }}</span>
                            </div>
    
                            <div class="flex-1 overflow-y-auto p-2 space-y-3 custom-scrollbar">
                                <div v-for="order in orders[col.id]" :key="order.id" 
                                     @click="openModal(order)"
                                     class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 cursor-pointer hover:shadow-md transition group">
                                    
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-black text-blue-900 text-sm">#{{ order.code }}</span>
                                        <span class="text-[10px] text-gray-400">{{ new Date(order.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                                    </div>
                                    
                                    <div class="text-xs text-gray-600 mb-2">
                                        <p class="font-bold">{{ order.user.name }}</p>
                                        <p class="truncate">{{ order.delivery_data.address }}</p>
                                    </div>
    
                                    <div class="flex justify-between items-center pt-2 border-t border-gray-50">
                                        <span class="font-bold text-green-600 text-sm">Bs {{ order.total_amount }}</span>
                                        <Eye :size="16" class="text-gray-300 group-hover:text-blue-500" />
                                    </div>
                                </div>
                            </div>
                        </div>
    
                    </div>
                </div>
            </div>
    
            <div v-if="selectedOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="selectedOrder = null">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="text-lg font-black text-gray-800">Orden #{{ selectedOrder.code }}</h2>
                        <button @click="selectedOrder = null"><X :size="20" class="text-gray-400 hover:text-red-500"/></button>
                    </div>
                    
                    <div class="p-6 max-h-[60vh] overflow-y-auto">
                        <div v-if="selectedOrder.proof_of_payment" class="mb-6">
                            <p class="text-xs font-bold text-gray-500 uppercase mb-2">Comprobante de Pago</p>
                            <img :src="`/storage/${selectedOrder.proof_of_payment}`" class="w-full rounded-lg border border-gray-200">
                        </div>
                        <div v-else class="mb-6 p-4 bg-gray-50 rounded text-center text-xs text-gray-500">
                            Sin comprobante adjunto aún.
                        </div>
    
                        <table class="w-full text-sm mb-4">
                            <thead><tr class="text-left text-xs text-gray-400"><th>Prod</th><th class="text-right">Cant</th></tr></thead>
                            <tbody>
                                <tr v-for="item in selectedOrder.items" :key="item.id" class="border-b border-gray-50">
                                    <td class="py-2">{{ item.sku_id }} (SKU)</td> <td class="py-2 text-right font-bold">{{ item.quantity }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
    
                    <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-2 justify-end">
                        
                        <template v-if="selectedOrder.status === 'review'">
                            <button @click="changeStatus(selectedOrder.id, 'cancelled', 'Comprobante inválido')" 
                                    class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-xs font-bold hover:bg-red-200">
                                Rechazar
                            </button>
                            <button @click="changeStatus(selectedOrder.id, 'confirmed')" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-xs font-bold hover:bg-green-700 flex items-center gap-1">
                                <Check :size="14"/> Aprobar Pago
                            </button>
                        </template>
    
                        <template v-if="selectedOrder.status === 'confirmed'">
                            <button @click="changeStatus(selectedOrder.id, 'dispatched')" 
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg text-xs font-bold hover:bg-purple-700 flex items-center gap-1">
                                <Truck :size="14"/> Despachar
                            </button>
                        </template>
    
                        <template v-if="selectedOrder.status === 'dispatched'">
                            <button @click="changeStatus(selectedOrder.id, 'completed')" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 flex items-center gap-1">
                                <Package :size="14"/> Entregar
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