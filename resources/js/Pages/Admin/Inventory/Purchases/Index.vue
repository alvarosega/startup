<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link } from '@inertiajs/vue3';
    
    defineProps({ purchases: Object });

    const formatDate = (date) => new Date(date).toLocaleDateString('es-BO');
    const formatMoney = (amount) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(amount);
</script>

<template>
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Ingresos y Compras</h1>
                <p class="text-gray-400 text-sm">Historial de abastecimiento de proveedores.</p>
            </div>
            <Link :href="route('admin.purchases.create')" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded font-bold shadow transition">
                + Nueva Compra
            </Link>
        </div>

        <div class="bg-gray-800 rounded shadow overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-900 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3">Fecha / ID</th>
                        <th class="px-6 py-3">Proveedor</th>
                        <th class="px-6 py-3">Sucursal Destino</th>
                        <th class="px-6 py-3 text-center">Items</th>
                        <th class="px-6 py-3 text-right">Total</th>
                        <th class="px-6 py-3 text-right">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm text-gray-300">
                    <tr v-for="p in purchases.data" :key="p.id" class="hover:bg-gray-750">
                        <td class="px-6 py-4">
                            <div class="font-bold text-white">{{ formatDate(p.purchase_date) }}</div>
                            <div class="text-xs text-blue-400">#{{ p.document_number }}</div>
                        </td>
                        <td class="px-6 py-4 font-bold">{{ p.provider ? p.provider.commercial_name : '---' }}</td>
                        <td class="px-6 py-4">{{ p.branch ? p.branch.name : '---' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-gray-700 px-2 py-1 rounded text-xs font-bold">{{ p.inventory_lots_count }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-green-400 font-mono font-bold">
                            {{ formatMoney(p.total_amount) }}
                        </td>
                        <td class="px-6 py-4 text-right text-xs text-gray-500">
                            {{ p.user ? p.user.email : 'System' }}
                        </td>
                    </tr>
                    <tr v-if="purchases.data.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">No hay compras registradas.</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="px-6 py-4 bg-gray-850 border-t border-gray-700 flex justify-center" v-if="purchases.links.length > 3">
                <div class="flex gap-1">
                    <template v-for="(link, k) in purchases.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1 rounded text-xs" 
                              :class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-400'"/>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>