<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link } from '@inertiajs/vue3';
    
    defineProps({ transfers: Object });
    
    const statusClasses = {
        'transito': 'bg-yellow-900 text-yellow-300 border-yellow-700',
        'recibido': 'bg-green-900 text-green-300 border-green-700',
        'rechazado': 'bg-red-900 text-red-300 border-red-700'
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-white">Transferencias y Logística</h1>
                <Link :href="route('admin.transfers.create')" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded font-bold">
                    🚚 Nuevo Envío
                </Link>
            </div>
    
            <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden shadow-xl">
                <table class="w-full text-left">
                    <thead class="bg-gray-700 text-gray-300 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-6 py-4">Guía / Fecha</th>
                            <th class="px-6 py-4">Ruta</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4">Responsable</th>
                            <th class="px-6 py-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 text-gray-400">
                        <tr v-for="t in transfers.data" :key="t.id" class="hover:bg-gray-750">
                            <td class="px-6 py-4">
                                <span class="block text-white font-bold">{{ t.codigo_guia }}</span>
                                <span class="text-xs">{{ new Date(t.created_at).toLocaleDateString() }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-red-400">{{ t.origin.name }}</span>
                                    <span>➡️</span>
                                    <span class="text-green-400">{{ t.destination.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="`px-2 py-1 rounded text-xs font-bold border ${statusClasses[t.estado]}`">
                                    {{ t.estado.toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                Enviado por: {{ t.sender.first_name }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('admin.transfers.show', t.id)" class="text-blue-400 hover:text-white font-bold text-sm border border-blue-500/30 px-3 py-1 rounded hover:bg-blue-600">
                                    {{ t.estado === 'transito' ? '📥 Recibir' : '👁️ Ver Detalle' }}
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="transfers.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">No hay movimientos registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    </template>