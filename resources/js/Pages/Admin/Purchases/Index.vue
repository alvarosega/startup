<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    
    const props = defineProps({
        purchases: Object // Paginado
    });
    
    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('es-BO', { year: 'numeric', month: 'short', day: 'numeric' });
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-white">Historial de Compras e Ingresos</h1>
                <Link :href="route('admin.purchases.create')" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded font-bold shadow transition">
                    + Nueva Compra
                </Link>
            </div>
    
            <div class="bg-gray-800 rounded shadow overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-900 text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="px-6 py-3">Fecha</th>
                            <th class="px-6 py-3">Documento</th>
                            <th class="px-6 py-3">Proveedor</th>
                            <th class="px-6 py-3">Destino</th>
                            <th class="px-6 py-3 text-right">Total (Bs)</th>
                            <th class="px-6 py-3 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 text-sm text-gray-300">
                        <tr v-for="purchase in purchases.data" :key="purchase.id" class="hover:bg-gray-750">
                            <td class="px-6 py-4">{{ formatDate(purchase.purchase_date) }}</td>
                            <td class="px-6 py-4 font-mono text-blue-300">{{ purchase.document_number }}</td>
                            <td class="px-6 py-4 font-bold text-white">{{ purchase.provider?.commercial_name }}</td>
                            <td class="px-6 py-4 text-gray-400">{{ purchase.branch?.name }}</td>
                            <td class="px-6 py-4 text-right font-mono text-green-400 font-bold">
                                {{ parseFloat(purchase.total_amount).toFixed(2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-green-900 text-green-300 text-xs px-2 py-1 rounded uppercase font-bold">
                                    {{ purchase.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="purchases.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No hay compras registradas.
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="px-6 py-4 bg-gray-850 border-t border-gray-700 flex justify-center" v-if="purchases.links && purchases.links.length > 3">
                    <div class="flex gap-1">
                        <template v-for="(link, k) in purchases.links" :key="k">
                            <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1 rounded text-xs transition" 
                                  :class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-400 hover:bg-gray-600'"/>
                        </template>
                    </div>
                </div>
            </div>
        </AdminLayout>
    </template>