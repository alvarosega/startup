<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    
    const props = defineProps({
        transfer: Object,
        // items debe venir del controller con la relación sku cargada
    });
    
    // Inicializamos el formulario de recepción con los valores enviados
    const form = useForm({
        items: props.transfer.items.map(item => ({
            id: item.id,
            sku_name: item.sku?.name || 'Desconocido', // Solo visual
            qty_sent: item.qty_sent,
            
            // Si ya se recibió, mostramos lo recibido. Si no, prellenamos con lo enviado.
            qty_received: item.qty_received !== null ? item.qty_received : item.qty_sent
        }))
    });
    
    const submit = () => {
        if(confirm('¿Confirmar recepción? Las diferencias se devolverán automáticamente a la sucursal de origen.')) {
            // Enviamos array de { id, qty_received }
            form.post(route('admin.transfers.receive', props.transfer.id));
        }
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-5xl mx-auto">
            
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                        Guía <span class="font-mono text-purple-400">{{ transfer.code }}</span>
                    </h1>
                    <p class="text-gray-400 text-sm">Detalle de transferencia</p>
                </div>
                <div>
                    <span v-if="transfer.status === 'in_transit'" class="bg-yellow-600 text-white px-4 py-2 rounded font-bold shadow animate-pulse">
                        EN TRÁNSITO (Pendiente Recepción)
                    </span>
                    <span v-else class="bg-green-600 text-white px-4 py-2 rounded font-bold shadow">
                        PROCESADO / CERRADO
                    </span>
                </div>
            </div>

            <div v-if="$page.props.errors.error" class="bg-red-500 text-white p-4 rounded mb-6 font-bold">
                ⚠ {{ $page.props.errors.error }}
            </div>

            <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 shadow-lg">
                <div class="text-center md:text-left">
                    <p class="text-[10px] text-red-400 uppercase font-bold">Origen</p>
                    <p class="text-xl font-bold text-white">{{ transfer.origin?.name }}</p>
                </div>
                <div class="flex flex-col items-center justify-center">
                    <div class="text-2xl text-gray-600">➜</div>
                    <div class="text-[10px] text-gray-500">{{ new Date(transfer.created_at).toLocaleString() }}</div>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-[10px] text-green-400 uppercase font-bold">Destino</p>
                    <p class="text-xl font-bold text-white">{{ transfer.destination?.name }}</p>
                </div>
                <div v-if="transfer.notes" class="md:col-span-3 mt-2 pt-4 border-t border-gray-700 text-sm text-gray-400 italic text-center">
                    "{{ transfer.notes }}"
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-gray-800 rounded border border-gray-700 overflow-hidden shadow-xl">
                <div class="p-4 bg-gray-900 border-b border-gray-700 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-300 uppercase">Verificación de Mercadería</h3>
                </div>

                <table class="w-full text-left text-sm text-gray-300">
                    <thead class="bg-gray-900 text-gray-500 text-[10px] uppercase font-bold">
                        <tr>
                            <th class="px-6 py-3">Producto</th>
                            <th class="px-6 py-3 text-center">Enviado (A)</th>
                            <th class="px-6 py-3 text-center bg-gray-700/30 text-white">Recibido (B)</th>
                            <th class="px-6 py-3 text-right">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="(item, index) in form.items" :key="item.id" class="hover:bg-gray-750 transition">
                            
                            <td class="px-6 py-4 font-bold text-white">
                                {{ item.sku_name }}
                            </td>
                            
                            <td class="px-6 py-4 text-center text-gray-400 font-mono text-lg">
                                {{ item.qty_sent }}
                            </td>

                            <td class="px-6 py-4 text-center bg-gray-700/30">
                                <div v-if="transfer.status === 'in_transit'">
                                    <input v-model.number="item.qty_received" type="number" min="0" :max="item.qty_sent" 
                                           class="w-20 bg-gray-900 border border-blue-500 text-white font-bold text-center rounded p-1 focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                                <div v-else class="font-bold text-green-400 text-lg">
                                    {{ item.qty_received }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <span v-if="item.qty_sent - item.qty_received > 0" class="text-red-400 font-bold text-xs bg-red-900/20 px-2 py-1 rounded border border-red-800">
                                    ↩ {{ item.qty_sent - item.qty_received }} retornan
                                </span>
                                <span v-else class="text-green-500 text-xs">OK</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="p-6 bg-gray-850 border-t border-gray-700 flex justify-between items-center">
                    <Link :href="route('admin.transfers.index')" class="text-gray-400 hover:text-white font-bold text-sm transition">
                        ← Volver
                    </Link>

                    <button v-if="transfer.status === 'in_transit'" 
                            type="submit"
                            :disabled="form.processing"
                            class="bg-green-600 hover:bg-green-500 text-white font-bold py-3 px-8 rounded shadow-lg transition transform hover:scale-105 disabled:opacity-50">
                        <span v-if="form.processing">Procesando...</span>
                        <span v-else>Confirmar y Cerrar Guía</span>
                    </button>
                </div>
            </form>

        </div>
    </AdminLayout>
</template>