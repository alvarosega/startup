<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import { debounce } from 'lodash'; // Asegúrate de tener lodash o implementa tu propio debounce
    
    const props = defineProps({ 
        users: Object, // Ahora es un objeto paginado, no solo Array
        filters: Object,
        roles: Array,
        branches: Array
    });
    
    // Estado reactivo para filtros
    const formFilters = ref({
        search: props.filters.search || '',
        role_id: props.filters.role_id || '',
        branch_id: props.filters.branch_id || '',
    });
    
    // Watch para aplicar filtros automáticamente
    watch(formFilters, debounce(() => {
        router.get(route('admin.users.index'), formFilters.value, {
            preserveState: true,
            replace: true,
        });
    }, 300), { deep: true });
    
    const confirmDelete = (userId) => {
        if (confirm('¿Estás seguro de eliminar este usuario? Esta acción es irreversible.')) {
            router.delete(route('admin.users.destroy', userId));
        }
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h1 class="text-3xl font-bold text-white">Gestión de Personal</h1>
                <Link :href="route('admin.users.create')" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition">
                    + Nuevo Usuario
                </Link>
            </div>
    
            <div class="bg-gray-800 p-4 rounded-xl border border-gray-700 mb-6 shadow-lg">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <input v-model="formFilters.search" type="text" placeholder="Buscar por nombre o teléfono..." 
                               class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-blue-500 outline-none text-sm">
                    </div>
                    <div>
                        <select v-model="formFilters.branch_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-blue-500 outline-none text-sm">
                            <option value="">Todas las Sucursales</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                        </select>
                    </div>
                    <div>
                        <select v-model="formFilters.role_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-blue-500 outline-none text-sm">
                            <option value="">Todos los Roles</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.display_name }}</option>
                        </select>
                    </div>
                </div>
            </div>
    
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-xl">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-700 text-gray-300 uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Usuario</th>
                            <th class="px-6 py-4">Rol</th>
                            <th class="px-6 py-4">Sucursal</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 text-gray-400">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-750 transition duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-white font-bold text-xs mr-3">
                                        {{ user.full_name[0] }}
                                    </div>
                                    <div>
                                        <p class="text-white font-bold text-sm">{{ user.full_name }}</p>
                                        <p class="text-xs text-gray-500">{{ user.phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-900/50 text-blue-300 px-2 py-1 rounded text-xs font-bold border border-blue-800">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ user.branch }}</td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="user.is_active" class="text-green-400 text-xs font-bold">Activo</span>
                                <span v-else class="text-red-400 text-xs font-bold">Inactivo</span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <Link :href="route('admin.users.edit', user.id)" class="text-blue-400 hover:text-blue-300 font-bold text-sm">Editar</Link>
                                <button @click="confirmDelete(user.id)" class="text-red-400 hover:text-red-300 font-bold text-sm">Eliminar</button>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                No se encontraron resultados.
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="px-6 py-4 border-t border-gray-700 flex justify-between items-center" v-if="users.links && users.data.length > 0">
                     <div class="text-xs text-gray-500">
                        Mostrando {{ users.from }} a {{ users.to }} de {{ users.total }}
                     </div>
                     <div class="space-x-1">
                        <Link v-for="(link, k) in users.links" :key="k" 
                              :href="link.url ?? '#'" 
                              v-html="link.label"
                              class="px-3 py-1 text-xs rounded border"
                              :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-700 text-gray-300 border-gray-600 hover:bg-gray-600'"
                              :preserve-state="true" />
                     </div>
                </div>
            </div>
        </AdminLayout>
    </template>