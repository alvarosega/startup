<script setup>
import { ref, watch } from 'vue';
import { router, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Admin/Pagination.vue';
import UserNavTabs from '@/Components/Admin/Users/UserNavTabs.vue';

const props = defineProps({
    staff: {
        type: Object,
        required: true,
    },
    branches: {
        type: Array,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const searchFilter = ref(props.filters.search || '');
const branchFilter = ref(props.filters.branch_id || '');
const roleFilter = ref(props.filters.role || '');
const statusFilter = ref(
    props.filters.is_active !== undefined && props.filters.is_active !== null
        ? String(props.filters.is_active)
        : ''
);

let debounceTimeout = null;

const applyFilters = () => {
    router.get(
        route('staff.index'),
        {
            search: searchFilter.value || undefined,
            branch_id: branchFilter.value || undefined,
            role: roleFilter.value || undefined,
            is_active: statusFilter.value !== '' ? statusFilter.value : undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const handleSearchInput = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

watch([branchFilter, roleFilter, statusFilter], () => {
    applyFilters();
});

const resetFilters = () => {
    searchFilter.value = '';
    branchFilter.value = '';
    roleFilter.value = '';
    statusFilter.value = '';
    applyFilters();
};

const statusForm = useForm({});
const toggleStatus = (operator) => {
    statusForm.patch(route('staff.toggle-status', operator.id), {
        preserveScroll: true,
    });
};

const roleBadges = {
    super_admin: { label: 'Maestro', class: 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20' },
    branch_manager: { label: 'Gerente', class: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20' },
    branch_warehouse: { label: 'Almacén', class: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20' },
    branch_logistics: { label: 'Despacho', class: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20' },
    branch_cashier: { label: 'Caja', class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' },
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase">
                        Personal y Operadores
                    </h1>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Administración centralizada de staff, roles y asignación de sucursales
                    </p>
                </div>
                <div>
                    <Link
                        :href="route('staff.create')"
                        class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2 px-3.5"
                    >
                        <span class="material-symbols-rounded text-[18px]">person_add</span>
                        <span>Nuevo Operador</span>
                    </Link>
                </div>
            </div>
        </template>

        <!-- Navegación Unificada del Ecosistema de Usuarios -->
        <UserNavTabs />

        <!-- Barra de Filtros de Alta Productividad Apple Glass -->
        <div class="glass-panel p-3.5 md:p-4 rounded-2xl shadow-sm border border-white/60 dark:border-white/10 mb-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 items-center">
                
                <!-- Buscador -->
                <div class="relative lg:col-span-2">
                    <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-[18px]">search</span>
                    <input
                        v-model="searchFilter"
                        @input="handleSearchInput"
                        type="text"
                        placeholder="Buscar por nombre, email o teléfono..."
                        class="admin-input pl-9 py-2 text-xs w-full"
                    />
                </div>

                <!-- Filtro Sucursal -->
                <div>
                    <select v-model="branchFilter" class="admin-input py-2 text-xs w-full">
                        <option value="">Todas las Sucursales</option>
                        <option value="global">Alcance Global (Maestro)</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">
                            {{ b.name }} ({{ b.city }})
                        </option>
                    </select>
                </div>

                <!-- Filtro Rol -->
                <div>
                    <select v-model="roleFilter" class="admin-input py-2 text-xs w-full">
                        <option value="">Todos los Roles</option>
                        <option v-for="r in roles" :key="r.id" :value="r.name">
                            {{ r.label }}
                        </option>
                    </select>
                </div>

                <!-- Filtro Estado & Reset -->
                <div class="flex items-center gap-2">
                    <select v-model="statusFilter" class="admin-input py-2 text-xs w-full">
                        <option value="">Todos los Estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Suspendidos</option>
                    </select>

                    <button
                        v-if="searchFilter || branchFilter || roleFilter || statusFilter !== ''"
                        @click="resetFilters"
                        title="Limpiar filtros"
                        class="p-2 text-muted-foreground hover:text-foreground border border-border/80 rounded-xl hover:bg-foreground/[0.06] transition-colors shrink-0"
                    >
                        <span class="material-symbols-rounded text-[18px] block">filter_alt_off</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla Densa de Operadores Apple Glass -->
        <div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-white/60 dark:border-white/10">
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-left border-collapse text-xs">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Operador</th>
                            <th class="admin-table-th">Sucursal Asignada</th>
                            <th class="admin-table-th">Roles Asignados</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th">Última Actividad</th>
                            <th class="admin-table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="op in staff.data"
                            :key="op.id"
                            class="admin-table-tr hover:bg-foreground/[0.03] transition-colors"
                        >
                            <!-- Operador -->
                            <td class="admin-table-td">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center font-bold text-xs shrink-0 select-none shadow-xs">
                                        {{ op.first_name?.[0]?.toUpperCase() }}{{ op.last_name?.[0]?.toUpperCase() }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-foreground block truncate">{{ op.full_name }}</span>
                                        <span class="text-[11px] font-mono text-muted-foreground block truncate">{{ op.email }}</span>
                                        <span v-if="op.phone" class="text-[10px] font-mono text-muted-foreground/80 block">{{ op.phone }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Sucursal -->
                            <td class="admin-table-td">
                                <div v-if="op.branch" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border border-border/70 bg-card/60 text-[11px] font-medium text-foreground backdrop-blur-sm">
                                    <span class="material-symbols-rounded text-[14px] text-primary">store</span>
                                    <span>{{ op.branch.name }}</span>
                                    <span class="text-muted-foreground text-[10px]">({{ op.branch.city }})</span>
                                </div>
                                <div v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border border-purple-500/25 bg-purple-500/10 text-[11px] font-bold text-purple-600 dark:text-purple-400">
                                    <span class="material-symbols-rounded text-[14px]">public</span>
                                    <span>Alcance Global</span>
                                </div>
                            </td>

                            <!-- Roles -->
                            <td class="admin-table-td">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="roleName in op.roles"
                                        :key="roleName"
                                        :class="roleBadges[roleName]?.class || 'bg-card text-foreground border-border'"
                                        class="px-2 py-0.5 rounded-lg text-[10px] font-bold border uppercase tracking-wider select-none shadow-xs"
                                    >
                                        {{ roleBadges[roleName]?.label || roleName }}
                                    </span>
                                </div>
                            </td>

                            <!-- Estado -->
                            <td class="admin-table-td text-center">
                                <button
                                    @click="toggleStatus(op)"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all border select-none active:scale-95 shadow-xs"
                                    :class="[
                                        op.is_active
                                            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/20'
                                            : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/30 hover:bg-rose-500/20'
                                    ]"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="op.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                    <span>{{ op.is_active ? 'Activo' : 'Suspendido' }}</span>
                                </button>
                            </td>

                            <!-- Actividad -->
                            <td class="admin-table-td font-mono text-[11px] text-muted-foreground">
                                <div v-if="op.last_login_at" class="flex flex-col">
                                    <span>{{ op.last_login_at }}</span>
                                    <span v-if="op.last_seen_at" class="text-[9px] text-muted-foreground/60">Visto {{ op.last_seen_at }}</span>
                                </div>
                                <span v-else class="text-muted-foreground/40 italic">Sin registro</span>
                            </td>

                            <!-- Acciones -->
                            <td class="admin-table-td text-right whitespace-nowrap">
                                <Link
                                    :href="route('staff.edit', op.id)"
                                    class="w-8 h-8 rounded-xl text-muted-foreground hover:text-foreground hover:bg-foreground/[0.06] transition-colors inline-flex items-center justify-center border border-border/60"
                                    title="Editar operador"
                                >
                                    <span class="material-symbols-rounded text-[18px]">edit</span>
                                </Link>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="staff.data.length === 0">
                            <td colspan="6" class="py-12 text-center text-muted-foreground">
                                <span class="material-symbols-rounded text-4xl mb-2 text-muted-foreground/40 block">person_off</span>
                                <p class="text-xs font-medium">No se encontraron operadores que coincidan con los filtros.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="border-t border-border/40 p-3 bg-card/30">
                <Pagination :meta="staff" />
            </div>
        </div>
    </AdminLayout>
</template>
