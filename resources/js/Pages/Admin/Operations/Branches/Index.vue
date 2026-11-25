<script setup>
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    branches: {
        type: Array,
        required: true
    }
});

const togglePause = (id) => {
    router.patch(route('admin.operations.branches.toggle-pause', id), {}, {
        preserveScroll: true
    });
};

const deleteBranch = (id) => {
    if (window.confirm('¿Está seguro de extraer este nodo de sucursal de la circulación logística?')) {
        router.delete(route('admin.operations.branches.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Nodos de Sucursales Logísticas
        </template>

        <div class="space-y-4">
            <div class="flex items-center justify-end bg-card p-4 border border-border rounded-md shadow-flat">
                <Link 
                    :href="route('admin.operations.branches.create')"
                    class="admin-btn-primary inline-flex items-center gap-1.5"
                >
                    <span class="material-symbols-rounded text-lg">add_location_alt</span>
                    <span>Nueva Sucursal</span>
                </Link>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Sucursal / Nodo</th>
                            <th class="admin-table-th">Ciudad</th>
                            <th class="admin-table-th">Personal Asignado</th>
                            <th class="admin-table-th text-center">Capacidad</th>
                            <th class="admin-table-th text-center">Recepción Online</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Operaciones y Ajustes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="branches.length === 0">
                            <td colspan="7" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No existen sucursales operativas materializadas en el sistema.
                            </td>
                        </tr>
                        <tr v-for="branch in branches" :key="branch.id" class="admin-table-tr">
                            <td class="admin-table-td font-bold">
                                <div>{{ branch.name }}</div>
                                <span v-if="branch.is_default" class="badge-info text-[10px] mt-0.5">Principal</span>
                            </td>
                            <td class="admin-table-td text-xs">
                                {{ branch.city }}
                            </td>
                            <td class="admin-table-td">
                                <Link 
                                    :href="route('staff.index', { branch_id: branch.id })"
                                    class="group flex flex-col hover:opacity-80 transition-opacity"
                                    title="Ver personal asignado a esta sucursal"
                                >
                                    <div class="flex items-center gap-1.5 font-semibold text-xs text-foreground group-hover:text-primary">
                                        <span class="material-symbols-rounded text-base text-muted-foreground group-hover:text-primary">badge</span>
                                        <span>{{ branch.staff_count }} {{ branch.staff_count === 1 ? 'operador' : 'operadores' }}</span>
                                    </div>
                                    <div v-if="branch.managers && branch.managers.length > 0" class="mt-1 flex flex-wrap gap-1">
                                        <span 
                                            v-for="(manager, idx) in branch.managers" 
                                            :key="idx" 
                                            class="inline-flex items-center text-[10px] font-medium bg-primary/10 text-primary border border-primary/20 px-1.5 py-0.5 rounded"
                                        >
                                            <span class="material-symbols-rounded text-[12px] mr-1">manage_accounts</span>
                                            {{ manager }}
                                        </span>
                                    </div>
                                    <div v-else class="text-[10px] text-muted-foreground italic mt-0.5">
                                        Sin gerente asignado
                                    </div>
                                </Link>
                            </td>
                            <td class="admin-table-td text-center font-mono text-xs">
                                {{ branch.max_active_orders ? `${branch.max_active_orders} pedidos` : 'Ilimitada' }}
                            </td>
                            <td class="admin-table-td text-center">
                                <button
                                    type="button"
                                    @click="togglePause(branch.id)"
                                    class="px-2 py-0.5 text-xs font-bold rounded cursor-pointer transition-colors"
                                    :class="branch.is_operational_paused ? 'bg-warning/10 text-warning border border-warning/30 hover:bg-warning/20' : 'bg-success/10 text-success border border-success/30 hover:bg-success/20'"
                                >
                                    {{ branch.is_operational_paused ? 'Pausado' : 'Recibiendo' }}
                                </button>
                            </td>
                            <td class="admin-table-td text-center">
                                <span :class="branch.is_active ? 'badge-success' : 'badge-error'">
                                    {{ branch.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="admin-table-td text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <Link
                                        :href="route('admin.operations.branches.cash.index', branch.id)"
                                        title="Control de Caja Diaria"
                                        class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200 dark:hover:bg-neutral-800"
                                    >
                                        Caja
                                    </Link>
                                    <Link
                                        :href="route('admin.operations.branches.schedules.index', branch.id)"
                                        title="Horarios Semanales"
                                        class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200 dark:hover:bg-neutral-800"
                                    >
                                        Horarios
                                    </Link>
                                    <Link
                                        :href="route('admin.operations.branches.schedule-exceptions.index', branch.id)"
                                        title="Excepciones y Feriados"
                                        class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200 dark:hover:bg-neutral-800"
                                    >
                                        Feriados
                                    </Link>
                                    <Link
                                        :href="route('admin.operations.branches.edit', branch.id)"
                                        class="px-2 py-1 bg-primary text-primary-foreground text-xs font-semibold rounded hover:bg-primary/90"
                                    >
                                        Configurar
                                    </Link>
                                    <button
                                        type="button"
                                        @click="deleteBranch(branch.id)"
                                        class="px-2 py-1 bg-destructive/5 text-destructive border border-destructive/20 text-xs font-semibold rounded hover:bg-destructive/10"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>