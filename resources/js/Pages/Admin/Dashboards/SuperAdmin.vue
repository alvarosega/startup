<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link } from '@inertiajs/vue3';

    // Recibimos las estadísticas enviadas por el DashboardController
    defineProps({ stats: Object });
</script>

<template>
    <AdminLayout>
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-bold text-white">Panel General</h1>
                <p class="text-gray-400">Visión global de la organización.</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ new Date().toLocaleDateString('es-BO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg relative overflow-hidden">
                <div class="absolute right-0 top-0 p-4 opacity-10 text-blue-500 text-6xl">👥</div>
                <h3 class="text-gray-400 text-xs uppercase font-bold tracking-wider">Usuarios Registrados</h3>
                <p class="text-4xl font-bold text-white mt-2">{{ stats.total_users }}</p>
                <div class="mt-4">
                    <Link :href="route('admin.users.index')" class="text-blue-400 text-sm hover:underline hover:text-white">
                        Gestionar Usuarios →
                    </Link>
                </div>
            </div>

            <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg relative overflow-hidden">
                <div class="absolute right-0 top-0 p-4 opacity-10 text-purple-500 text-6xl">🏢</div>
                <h3 class="text-gray-400 text-xs uppercase font-bold tracking-wider">Sucursales Activas</h3>
                <p class="text-4xl font-bold text-white mt-2">{{ stats.active_branches }}</p>
                <div class="mt-4">
                    <Link :href="route('admin.branches.index')" class="text-purple-400 text-sm hover:underline hover:text-white">
                        Ver Sucursales →
                    </Link>
                </div>
            </div>

            <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg relative overflow-hidden" 
                 :class="{'border-yellow-500/50': stats.pending_verifications > 0}">
                <div class="absolute right-0 top-0 p-4 opacity-10 text-yellow-500 text-6xl">🛡️</div>
                <h3 class="text-gray-400 text-xs uppercase font-bold tracking-wider">Validaciones Pendientes</h3>
                <p class="text-4xl font-bold text-white mt-2">{{ stats.pending_verifications }}</p>
                <div class="mt-4">
                    <span v-if="stats.pending_verifications > 0" class="text-yellow-500 text-sm font-bold animate-pulse">
                        ⚠️ Requiere atención
                    </span>
                    <span v-else class="text-green-500 text-sm">
                        ✅ Todo al día
                    </span>
                </div>
            </div>
        </div>

        <h2 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-2">Administración del Sistema</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <Link :href="route('admin.products.index')" class="flex items-center gap-3 bg-gray-800 p-4 rounded hover:bg-gray-700 transition border border-gray-700 group">
                <div class="bg-blue-900/30 p-2 rounded text-blue-400 group-hover:scale-110 transition">🏷️</div>
                <div>
                    <h4 class="font-bold text-gray-200 text-sm">Catálogo Maestro</h4>
                    <p class="text-xs text-gray-500">Productos y SKUs</p>
                </div>
            </Link>

            <Link :href="route('admin.users.index')" class="flex items-center gap-3 bg-gray-800 p-4 rounded hover:bg-gray-700 transition border border-gray-700 group">
                <div class="bg-green-900/30 p-2 rounded text-green-400 group-hover:scale-110 transition">👥</div>
                <div>
                    <h4 class="font-bold text-gray-200 text-sm">Roles y Permisos</h4>
                    <p class="text-xs text-gray-500">Seguridad</p>
                </div>
            </Link>
        </div>
    </AdminLayout>
</template>