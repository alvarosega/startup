<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link } from '@inertiajs/vue3';
    
    defineProps({ transfers: Object });

    const formatDate = (date) => new Date(date).toLocaleDateString('es-BO', { 
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' 
    });
</script>

<template>
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Transferencias entre Sucursales</h1>
                <p class="text-gray-400 text-sm">Gestión de envíos y recepción de mercadería.</p>
            </div>
            <Link :href="route('admin.transfers.create')" class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded font-bold shadow transition">
                + Nueva Guía
            </Link>
        </div>

        <div class="bg-gray-800 rounded shadow overflow-hidden">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-gray-900 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3">Código</th>
                        <th class="px-6 py-3">Ruta (Origen ➜ Destino)</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3">Enviado</th>
                        <th class="px-6 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <tr v-for="t in transfers.data" :key="t.id" class="hover:bg-gray-750">
                        <td class="px-6 py-4 font-mono text-purple-300 font-bold">
                            {{ t.code }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-red-300 font-bold">{{ t.origin?.name }}</span>
                                <span class="text-gray-500 text-xs">➜</span>
                                <span class="text-green-300 font-bold">{{ t.destination?.name }}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Por: {{ t.sender?.email }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span v-if="t.status === 'in_transit'" class="inline-flex items-center gap-1 bg-yellow-900/50 text-yellow-400 px-2 py-1 rounded text-xs font-bold border border-yellow-700 animate-pulse">
                                🚚 En Tránsito
                            </span>
                            <span v-else class="inline-flex items-center gap-1 bg-green-900/50 text-green-400 px-2 py-1 rounded text-xs font-bold border border-green-700">
                                ✅ Recibido
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            {{ formatDate(t.created_at) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <Link :href="route('admin.transfers.show', t.id)" 
                                  class="text-blue-400 hover:text-white font-bold text-xs uppercase border border-blue-500/30 px-3 py-1.5 rounded hover:bg-blue-600 transition">
                                {{ t.status === 'in_transit' ? '📦 Recibir' : '👁️ Ver Detalle' }}
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="transfers.data.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No hay transferencias registradas.
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="px-6 py-4 bg-gray-850 border-t border-gray-700 flex justify-center" v-if="transfers.links.length > 3">
                <div class="flex gap-1">
                    <template v-for="(link, k) in transfers.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1 rounded text-xs" 
                              :class="link.active ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-400'"/>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>