<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link } from '@inertiajs/vue3';
    
    defineProps({ transformations: Object });
</script>

<template>
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Historial de Transformaciones</h1>
                <p class="text-gray-400 text-sm">Registro de desagregación de Packs a Unidades.</p>
            </div>
            <Link :href="route('admin.transformations.create')" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded font-bold shadow transition">
                + Nueva Transformación
            </Link>
        </div>

        <div class="bg-gray-800 rounded shadow overflow-hidden">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-gray-900 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Sucursal</th>
                        <th class="px-6 py-3 text-right text-red-400">Consumido (Origen)</th>
                        <th class="px-6 py-3 text-center">➜</th>
                        <th class="px-6 py-3 text-green-400">Generado (Destino)</th>
                        <th class="px-6 py-3">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <tr v-for="t in transformations.data" :key="t.id" class="hover:bg-gray-750">
                        <td class="px-6 py-4 text-xs">
                            {{ new Date(t.created_at).toLocaleDateString() }}
                            <div class="text-gray-500">{{ new Date(t.created_at).toLocaleTimeString() }}</div>
                        </td>
                        <td class="px-6 py-4 font-bold">{{ t.branch?.name }}</td>
                        
                        <td class="px-6 py-4 text-right">
                            <div class="font-bold text-white">{{ parseFloat(t.quantity_removed) }} x</div>
                            <div class="text-xs text-red-300">{{ t.source_sku?.product?.name }}</div>
                            <div class="text-[10px] text-gray-500">{{ t.source_sku?.name }}</div>
                        </td>

                        <td class="px-6 py-4 text-center text-gray-500">➜</td>

                        <td class="px-6 py-4">
                            <div class="font-bold text-white">{{ parseFloat(t.quantity_added) }} x</div>
                            <div class="text-xs text-green-300">{{ t.destination_sku?.product?.name }}</div>
                            <div class="text-[10px] text-gray-500">{{ t.destination_sku?.name }}</div>
                        </td>

                        <td class="px-6 py-4 text-xs text-gray-400">
                            {{ t.user?.email }}
                            <div v-if="t.notes" class="mt-1 italic text-gray-600">"{{ t.notes }}"</div>
                        </td>
                    </tr>
                    
                    <tr v-if="transformations.data.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No hay transformaciones registradas.
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="px-6 py-4 bg-gray-850 border-t border-gray-700 flex justify-center" v-if="transformations.links.length > 3">
                <div class="flex gap-1">
                    <template v-for="(link, k) in transformations.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1 rounded text-xs transition" 
                              :class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-400'"/>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>