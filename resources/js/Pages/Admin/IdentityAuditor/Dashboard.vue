<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    defineProps({ pending_verifications: Array });
    </script>
    
    <template>
        <AdminLayout>
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-white">Cola de Verificación</h1>
                <span class="bg-blue-600 px-3 py-1 rounded-full text-sm">{{ pending_verifications.length }} pendientes</span>
            </div>
    
            <div class="bg-gray-800 rounded-lg overflow-hidden border border-gray-700">
                <table class="w-full text-left text-gray-400">
                    <thead class="bg-gray-700 text-gray-200 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">ID Usuario</th>
                            <th class="px-6 py-3">Fecha Solicitud</th>
                            <th class="px-6 py-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="item in pending_verifications" :key="item.id" class="hover:bg-gray-750">
                            <td class="px-6 py-4">#{{ item.user_id }}</td>
                            <td class="px-6 py-4">{{ item.created_at }}</td>
                            <td class="px-6 py-4">
                                <button class="text-blue-400 hover:text-blue-300 font-bold">Revisar</button>
                            </td>
                        </tr>
                        <tr v-if="pending_verifications.length === 0">
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                Todo limpio. No hay documentos pendientes.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    </template>