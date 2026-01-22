<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    
    defineProps({
        removals: Object, // Paginado
        pending_count: Number
    });
    
    const approve = (id) => {
        if (confirm('¿Estás seguro? Esto descontará el stock permanentemente.')) {
            router.post(route('admin.removals.approve', id));
        }
    };
    
    const reject = (id) => {
        if (confirm('¿Rechazar esta solicitud?')) {
            router.post(route('admin.removals.reject', id));
        }
    };
    
    const statusColors = {
        'pendiente': 'bg-yellow-900 text-yellow-300 border-yellow-700',
        'aprobado': 'bg-green-900 text-green-300 border-green-700',
        'rechazado': 'bg-red-900 text-red-300 border-red-700',
    };
    
    const statusLabels = {
        'pendiente': '⏳ Pendiente',
        'aprobado': '✅ Aprobado',
        'rechazado': '❌ Rechazado',
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-white">Bajas y Mermas</h1>
                    <p class="text-xs text-gray-400">Auditoría de pérdidas de inventario</p>
                </div>
                <Link :href="route('admin.removals.create')" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition transform hover:scale-105 flex items-center">
                    <span>⚠️ Reportar Nueva Baja</span>
                </Link>
            </div>
    
            <div v-if="pending_count > 0" class="mb-6 bg-yellow-900/30 border border-yellow-600 p-4 rounded-lg flex items-center gap-3">
                <span class="text-2xl">🔔</span>
                <div>
                    <p class="text-yellow-400 font-bold">Tienes {{ pending_count }} solicitudes pendientes</p>
                    <p class="text-xs text-yellow-200/70">Revisalas para mantener el stock actualizado.</p>
                </div>
            </div>
    
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-xl">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-700 text-gray-300 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-6 py-4">Fecha</th>
                            <th class="px-6 py-4">Producto</th>
                            <th class="px-6 py-4 text-center">Cant.</th>
                            <th class="px-6 py-4">Motivo / Usuario</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 text-gray-400">
                        <tr v-for="req in removals.data" :key="req.id" class="hover:bg-gray-750 transition">
                            <td class="px-6 py-4 text-xs">
                                {{ new Date(req.created_at).toLocaleDateString() }}
                                <span class="block text-gray-500">{{ new Date(req.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-white font-bold text-sm">{{ req.sku.producto.nombre }}</p>
                                <p class="text-xs text-gray-500">{{ req.sku.nombre_presentacion }}</p>
                                <p class="text-xs text-blue-400 mt-1">📍 {{ req.branch.name }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xl font-bold text-white">{{ req.cantidad }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-300 block">{{ req.motivo }}</span>
                                <span class="text-xs text-gray-500">Por: {{ req.requester.first_name }}</span>
                                <p v-if="req.observaciones" class="text-xs italic text-gray-500 mt-1">"{{ req.observaciones }}"</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="`px-2 py-1 rounded text-xs font-bold border ${statusColors[req.estado]}`">
                                    {{ statusLabels[req.estado] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div v-if="req.estado === 'pendiente'" class="flex justify-end gap-2">
                                    <button @click="approve(req.id)" class="bg-green-600 hover:bg-green-500 text-white p-2 rounded shadow text-xs font-bold" title="Aprobar y Descontar">
                                        ✅ Aprobar
                                    </button>
                                    <button @click="reject(req.id)" class="bg-gray-700 hover:bg-red-600 text-white p-2 rounded shadow text-xs font-bold transition" title="Rechazar">
                                        ❌
                                    </button>
                                </div>
                                <div v-else class="text-xs text-gray-600">
                                    Procesado el {{ new Date(req.processed_at).toLocaleDateString() }}
                                </div>
                            </td>
                        </tr>
                         <tr v-if="removals.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                No hay registros de bajas. ¡Qué buen control!
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                 <div class="px-6 py-4 border-t border-gray-700 flex justify-end gap-2" v-if="removals.data.length > 0">
                     <Link v-if="removals.prev_page_url" :href="removals.prev_page_url" class="px-3 py-1 bg-gray-700 text-white rounded text-xs">Anterior</Link>
                     <Link v-if="removals.next_page_url" :href="removals.next_page_url" class="px-3 py-1 bg-gray-700 text-white rounded text-xs">Siguiente</Link>
                </div>
            </div>
        </AdminLayout>
    </template>