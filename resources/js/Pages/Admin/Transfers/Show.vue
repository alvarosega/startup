<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    
    const props = defineProps({ transfer: Object });
    
    const receiveTransfer = () => {
        if (confirm(`¿Confirmas que has recibido y contado físicamente los items de la guía ${props.transfer.codigo_guia}?`)) {
            router.post(route('admin.transfers.receive', props.transfer.id));
        }
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Detalle de Transferencia</h1>
                        <p class="text-gray-400 text-sm">Guía: <span class="text-yellow-400 font-mono">{{ transfer.codigo_guia }}</span></p>
                    </div>
                    <Link :href="route('admin.transfers.index')" class="text-gray-400 hover:text-white">Volver</Link>
                </div>
    
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg mb-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center md:text-left">
                        <p class="text-xs text-gray-500 uppercase font-bold">Origen</p>
                        <p class="text-xl text-red-400 font-bold">{{ transfer.origin.name }}</p>
                        <p class="text-xs text-gray-400">{{ new Date(transfer.created_at).toLocaleString() }}</p>
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <div class="text-3xl mb-2">➡️</div>
                        <span v-if="transfer.estado === 'transito'" class="px-3 py-1 bg-yellow-900 text-yellow-300 rounded-full text-xs font-bold border border-yellow-600 animate-pulse">
                            EN TRÁNSITO
                        </span>
                        <span v-else class="px-3 py-1 bg-green-900 text-green-300 rounded-full text-xs font-bold border border-green-600">
                            RECIBIDO
                        </span>
                    </div>
                    <div class="text-center md:text-right">
                        <p class="text-xs text-gray-500 uppercase font-bold">Destino</p>
                        <p class="text-xl text-green-400 font-bold">{{ transfer.destination.name }}</p>
                        <p v-if="transfer.received_at" class="text-xs text-gray-400">{{ new Date(transfer.received_at).toLocaleString() }}</p>
                    </div>
                </div>
    
                <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden shadow-lg mb-6">
                    <div class="p-4 bg-gray-700 border-b border-gray-600">
                        <h3 class="font-bold text-white">Contenido del Envío</h3>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-gray-900 text-gray-400 text-xs uppercase">
                            <tr>
                                <th class="p-4">Producto</th>
                                <th class="p-4 text-center">Cantidad Enviada</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="item in transfer.items" :key="item.id" class="text-gray-300">
                                <td class="p-4">
                                    <span class="block font-bold text-white">{{ item.sku.producto.nombre }}</span>
                                    <span class="text-xs">{{ item.sku.nombre_presentacion }}</span>
                                </td>
                                <td class="p-4 text-center font-mono text-lg text-white">
                                    {{ item.cantidad_enviada }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
    
                <div v-if="transfer.estado === 'transito'" class="flex flex-col items-end space-y-2">
                    <div class="bg-yellow-900/20 border border-yellow-600 p-3 rounded text-yellow-200 text-xs max-w-md text-right">
                        ⚠️ Al confirmar, el stock se sumará automáticamente a la sucursal <strong>{{ transfer.destination.name }}</strong>.
                    </div>
                    <button @click="receiveTransfer" class="bg-green-600 hover:bg-green-500 text-white font-bold py-4 px-8 rounded-lg shadow-xl text-lg transition transform hover:scale-105">
                        ✅ Confirmar Recepción de Mercadería
                    </button>
                </div>
            </div>
        </AdminLayout>
    </template>