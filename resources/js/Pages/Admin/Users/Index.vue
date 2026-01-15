<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch, computed } from 'vue';
    import { debounce } from 'lodash';
    
    // Iconos
    import { 
        Search, UserPlus, MapPin, Phone, Mail, 
        Shield, Edit, Trash2, MoreVertical, Building 
    } from 'lucide-vue-next';

    const props = defineProps({ 
        users: Object, // Paginado
        filters: Object,
        roles: Array,
        branches: Array
    });

    const formFilters = ref({
        search: props.filters.search || '',
        role_id: props.filters.role_id || '',
        branch_id: props.filters.branch_id || '',
    });

    // --- LÓGICA DE AGRUPACIÓN Y ORDENAMIENTO ---
    const groupedUsers = computed(() => {
        if (!props.users.data) return {};

        // 1. Agrupar por Sucursal
        const groups = props.users.data.reduce((acc, user) => {
            const branchName = user.branch || 'Sin Asignar (Oficina Central)';
            if (!acc[branchName]) acc[branchName] = [];
            acc[branchName].push(user);
            return acc;
        }, {});

        // 2. Ordenar dentro de cada grupo (Admin primero)
        // Define aquí la prioridad de tus roles (String exacto o ID)
        const rolePriority = {
            'Admin': 1,
            'Gerente': 2,
            'Administrador Sucursal': 2,
            'Vendedor': 3,
            'Almacenero': 4
        };

        Object.keys(groups).forEach(branch => {
            groups[branch].sort((a, b) => {
                const prioA = rolePriority[a.role] || 99;
                const prioB = rolePriority[b.role] || 99;
                return prioA - prioB;
            });
        });

        return groups;
    });

    // --- HELPERS VISUALES ---
    const getRoleColor = (roleName) => {
        if (!roleName) return 'bg-gray-700 text-gray-300 border-gray-600';
        const lower = roleName.toLowerCase();
        if (lower.includes('admin') || lower.includes('gerente')) return 'bg-purple-900/40 text-purple-300 border-purple-700';
        if (lower.includes('vendedor')) return 'bg-green-900/40 text-green-300 border-green-700';
        if (lower.includes('almacen')) return 'bg-orange-900/40 text-orange-300 border-orange-700';
        return 'bg-blue-900/40 text-blue-300 border-blue-700';
    };

    // --- FILTROS ---
    watch(formFilters, debounce(() => {
        router.get(route('admin.users.index'), formFilters.value, {
            preserveState: true, replace: true,
        });
    }, 300), { deep: true });

    const confirmDelete = (userId) => {
        if (confirm('¿Eliminar usuario y revocar accesos?')) {
            router.delete(route('admin.users.destroy', userId));
        }
    };

</script>

<template>
    <AdminLayout>
        
        <div class="flex flex-col gap-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-white tracking-tight">Equipo de Trabajo</h1>
                    <p class="text-gray-400 text-sm mt-1">Gestión de usuarios y permisos por sucursal</p>
                </div>
                <Link :href="route('admin.users.create')" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-lg font-bold shadow-lg shadow-blue-900/20 transition flex items-center gap-2">
                    <UserPlus :size="18" /> Nuevo Usuario
                </Link>
            </div>

            <div class="bg-gray-800 p-4 rounded-xl border border-gray-700 shadow-lg grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                    <Search :size="16" class="absolute left-3 top-3 text-gray-500" />
                    <input v-model="formFilters.search" type="text" placeholder="Buscar nombre, correo..." 
                           class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg pl-9 p-2.5 focus:border-blue-500 outline-none text-sm placeholder-gray-500 transition">
                </div>
                
                <div class="relative" v-if="branches.length > 1">
                    <Building :size="16" class="absolute left-3 top-3 text-gray-500" />
                    <select v-model="formFilters.branch_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg pl-9 p-2.5 focus:border-blue-500 outline-none text-sm appearance-none">
                        <option value="">Todas las Sucursales</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                    </select>
                </div>

                <div class="relative">
                    <Shield :size="16" class="absolute left-3 top-3 text-gray-500" />
                    <select v-model="formFilters.role_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg pl-9 p-2.5 focus:border-blue-500 outline-none text-sm appearance-none">
                        <option value="">Todos los Roles</option>
                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.display_name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="space-y-10">
            
            <template v-for="(groupUsers, branchName) in groupedUsers" :key="branchName">
                <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    
                    <div class="flex items-center gap-3 mb-4 px-2 border-b border-gray-700 pb-2">
                        <div class="p-2 bg-gray-700 rounded-lg text-blue-400">
                            <MapPin :size="20" />
                        </div>
                        <h2 class="text-xl font-bold text-white">{{ branchName }}</h2>
                        <span class="text-xs bg-gray-800 px-2 py-1 rounded text-gray-400 border border-gray-700">
                            {{ groupUsers.length }} Usuarios
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <div v-for="user in groupUsers" :key="user.id" 
                             class="group bg-gray-800 border border-gray-700 rounded-xl overflow-hidden hover:border-gray-500 transition-all duration-200 shadow-md hover:shadow-xl relative flex flex-col">
                            
                            <div class="absolute top-0 left-0 w-1 h-full" :class="user.is_active ? 'bg-green-500' : 'bg-red-500'"></div>

                            <div class="p-5 flex-1">
                                <div class="flex justify-between items-start mb-4">
                                    <span class="text-[10px] uppercase font-black tracking-wider px-2 py-1 rounded border"
                                          :class="getRoleColor(user.role)">
                                        {{ user.role }}
                                    </span>
                                    
                                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Link :href="route('admin.users.edit', user.id)" class="text-gray-400 hover:text-white p-1 hover:bg-gray-700 rounded">
                                            <Edit :size="16" />
                                        </Link>
                                        <button @click="confirmDelete(user.id)" class="text-gray-400 hover:text-red-400 p-1 hover:bg-gray-700 rounded">
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-700 to-gray-600 flex items-center justify-center text-white font-bold text-lg border-2 border-gray-600 shadow-inner">
                                        {{ user.full_name ? user.full_name.charAt(0).toUpperCase() : 'U' }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white text-lg leading-tight">{{ user.full_name }}</h3>
                                        <p v-if="!user.is_active" class="text-xs text-red-400 font-bold mt-0.5">● Acceso Revocado</p>
                                        <p v-else class="text-xs text-green-500 font-bold mt-0.5">● Activo</p>
                                    </div>
                                </div>

                                <div class="space-y-2 text-sm border-t border-gray-700/50 pt-3">
                                    <div class="flex items-center gap-3 text-gray-400">
                                        <Mail :size="14" /> <span class="truncate">{{ user.email }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-400">
                                        <Phone :size="14" /> <span>{{ user.phone || '---' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </template>

            <div v-if="users.data.length === 0" class="py-20 text-center border-2 border-dashed border-gray-700 rounded-xl">
                <p class="text-gray-500 text-lg">No se encontraron usuarios con estos filtros.</p>
                <button @click="formFilters = { search: '', role_id: '', branch_id: '' }" class="text-blue-400 hover:underline text-sm mt-2">
                    Limpiar filtros
                </button>
            </div>

            <div v-if="users.links && users.data.length > 0" class="flex justify-center pt-6">
                <div class="flex gap-1">
                    <Link v-for="(link, k) in users.links" :key="k" 
                          :href="link.url ?? '#'" 
                          v-html="link.label"
                          class="px-3 py-1.5 text-xs rounded-lg border transition-colors"
                          :class="link.active 
                            ? 'bg-blue-600 text-white border-blue-600 font-bold' 
                            : 'bg-gray-800 text-gray-400 border-gray-700 hover:bg-gray-700 hover:text-white'"
                          :preserve-state="true" />
                </div>
            </div>

        </div>
    </AdminLayout>
</template>